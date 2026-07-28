<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    public function index(Request $r)
    {
        return User::with('role', 'branch')
            ->when($r->boolean('sales_only'), fn ($q) => $q->where('is_active', true)->whereHas('role', fn ($role) => $role->whereIn('slug', ['sales-manager', 'sales-executive'])))
            ->when($r->search, fn ($q, $v) => $q->where(fn ($x) => $x->where('name', 'like', "%$v%")->orWhere('email', 'like', "%$v%")))
            ->orderBy('name')
            ->paginate(min((int) $r->input('per_page', 20), 100));
    }

    public function store(Request $r)
    {
        $d = $r->validate(['name' => 'required', 'email' => 'required|email|unique:users', 'password' => 'required|min:8', 'phone' => 'nullable', 'role_id' => 'required|exists:roles,id', 'branch_id' => 'nullable|exists:branches,id']);

        return response()->json(User::create($d)->load('role', 'branch'), 201);
    }

    public function show(User $staff)
    {
        return $staff->load('role.permissions', 'branch');
    }

    public function update(Request $r, User $staff)
    {
        $d = $r->validate(['name' => 'sometimes|required', 'email' => ['sometimes', 'email', Rule::unique('users')->ignore($staff)], 'password' => 'nullable|min:8', 'phone' => 'nullable', 'role_id' => 'sometimes|exists:roles,id', 'branch_id' => 'nullable|exists:branches,id', 'is_active' => 'boolean']);
        if (empty($d['password'])) {
            unset($d['password']);
        }$staff->update($d);

        return $staff->load('role', 'branch');
    }

    public function destroy(User $staff)
    {
        $staff->update(['is_active' => false]);
        $staff->tokens()->delete();

        return ['message' => 'Staff account deactivated'];
    }
}
