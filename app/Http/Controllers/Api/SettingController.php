<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        return ['settings' => Setting::all()->groupBy('group'), 'branches' => Branch::all(), 'roles' => Role::with('permissions')->get(), 'permissions' => Permission::orderBy('module')->get()];
    }

    public function update(Request $r)
    {
        $data = $r->validate(['settings' => 'required|array']);
        foreach ($data['settings'] as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['group' => str_contains($key, '.') ? str($key)->before('.')->toString() : 'general', 'value' => $value, 'type' => gettype($value)]);
        }

return ['message' => 'Settings saved', 'settings' => Setting::all()->groupBy('group')];
    }

    public function branch(Request $r)
    {
        $d = $r->validate(['name' => 'required', 'code' => 'required|unique:branches,code', 'phone' => 'nullable', 'email' => 'nullable|email', 'address' => 'nullable']);

        return response()->json(Branch::create($d), 201);
    }

    public function rolePermissions(Request $r, Role $role)
    {
        $d = $r->validate(['permissions' => 'required|array', 'permissions.*' => 'exists:permissions,id']);
        $role->permissions()->sync($d['permissions']);

        return $role->load('permissions');
    }
}
