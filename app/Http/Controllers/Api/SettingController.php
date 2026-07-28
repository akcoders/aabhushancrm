<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

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

    public function createRole(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:roles,name',
            'hierarchy_level' => 'required|integer|min:1|max:100',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);
        $role = Role::create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']).'-'.Str::lower(Str::random(4)),
            'hierarchy_level' => $data['hierarchy_level'],
        ]);
        $role->permissions()->sync($data['permissions'] ?? []);
        return response()->json($role->load('permissions'), 201);
    }

    public function updateRole(Request $request, Role $role)
    {
        abort_if($role->slug === 'super-admin', 422, 'The Super Admin role hierarchy cannot be changed.');
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('roles', 'name')->ignore($role)],
            'hierarchy_level' => 'required|integer|min:2|max:100',
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id',
        ]);
        $role->update(['name' => $data['name'], 'hierarchy_level' => $data['hierarchy_level']]);
        $role->permissions()->sync($data['permissions']);
        return $role->load('permissions');
    }
}
