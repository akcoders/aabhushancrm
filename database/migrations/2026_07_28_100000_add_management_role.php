<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('roles')->updateOrInsert(
            ['slug' => 'management'],
            ['name' => 'Management', 'created_at' => now(), 'updated_at' => now()]
        );
        $roleId = DB::table('roles')->where('slug', 'management')->value('id');
        $permissions = DB::table('permissions')
            ->whereNotIn('slug', ['settings.delete', 'staff.delete'])
            ->pluck('id');
        foreach ($permissions as $permissionId) {
            DB::table('permission_role')->insertOrIgnore(['role_id' => $roleId, 'permission_id' => $permissionId]);
        }
    }

    public function down(): void
    {
        $roleId = DB::table('roles')->where('slug', 'management')->value('id');
        if ($roleId) DB::table('permission_role')->where('role_id', $roleId)->delete();
        DB::table('roles')->where('slug', 'management')->delete();
    }
};
