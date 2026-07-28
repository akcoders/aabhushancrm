<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->unsignedSmallInteger('hierarchy_level')->default(50)->after('slug');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('reporting_manager_id')->nullable()->after('role_id')->constrained('users')->nullOnDelete();
        });

        foreach ([
            'super-admin' => 1,
            'management' => 2,
            'sales-manager' => 3,
            'event-manager' => 3,
            'accountant' => 3,
            'sales-executive' => 4,
            'front-office-cre' => 4,
        ] as $slug => $level) {
            DB::table('roles')->where('slug', $slug)->update(['hierarchy_level' => $level]);
        }
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $table) => $table->dropConstrainedForeignId('reporting_manager_id'));
        Schema::table('roles', fn (Blueprint $table) => $table->dropColumn('hierarchy_level'));
    }
};
