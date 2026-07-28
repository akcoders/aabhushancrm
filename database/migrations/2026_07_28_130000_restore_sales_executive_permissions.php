<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $roleId = DB::table('roles')->where('slug', 'sales-executive')->value('id');
        if (!$roleId) return;

        $permissionIds = DB::table('permissions')
            ->whereIn('module', ['dashboard', 'leads', 'followups', 'customers', 'sales', 'retention', 'tasks', 'rewards', 'notifications'])
            ->where('slug', 'not like', '%.delete')
            ->pluck('id');

        foreach ($permissionIds as $permissionId) {
            DB::table('permission_role')->insertOrIgnore([
                'role_id' => $roleId,
                'permission_id' => $permissionId,
            ]);
        }
    }

    public function down(): void
    {
        // Do not remove role permissions during rollback.
    }
};
