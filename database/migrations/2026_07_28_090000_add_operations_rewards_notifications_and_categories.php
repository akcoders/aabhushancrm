<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('reward_points')->default(0);
            $table->json('notification_preferences')->nullable();
        });
        Schema::table('customers', function (Blueprint $table) {
            $table->boolean('category_override')->default(false);
        });
        Schema::table('tasks', function (Blueprint $table) {
            $table->unsignedInteger('reward_points')->default(10);
            $table->timestamp('rewarded_at')->nullable();
        });
        Schema::table('offers', function (Blueprint $table) {
            $table->dropUnique('offers_coupon_code_unique');
            $table->string('coupon_code')->nullable()->change();
        });

        Schema::create('customer_category_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category');
            $table->decimal('minimum_purchase', 14, 2)->default(0);
            $table->decimal('maximum_purchase', 14, 2)->nullable();
            $table->unsignedInteger('priority')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('reward_catalogs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedInteger('points_required');
            $table->unsignedInteger('stock')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('reward_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reward_catalog_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('points');
            $table->string('status')->default('Requested');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();
        });
        Schema::create('app_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type')->default('info');
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'read_at']);
        });

        DB::table('custom_orders')->whereIn('status', ['New', 'Designing', 'Approved', 'In Production'])->update(['status' => 'Processing']);
        DB::table('custom_orders')->whereIn('status', ['Ready', 'Delivered'])->update(['status' => 'Order Ready']);
        DB::table('customer_category_rules')->insert([
            ['name' => 'Normal customers', 'category' => 'Normal', 'minimum_purchase' => 0, 'maximum_purchase' => 49999.99, 'priority' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Premium customers', 'category' => 'Premium', 'minimum_purchase' => 50000, 'maximum_purchase' => 99999.99, 'priority' => 2, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'HNI customers', 'category' => 'HNI', 'minimum_purchase' => 100000, 'maximum_purchase' => null, 'priority' => 3, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
        DB::table('reward_catalogs')->insert([
            ['name' => 'Movie Ticket', 'description' => 'One standard movie ticket voucher', 'points_required' => 250, 'stock' => null, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => '₹500 Shopping Voucher', 'description' => 'Manager-approved shopping voucher', 'points_required' => 500, 'stock' => null, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Team Recognition Award', 'description' => 'Monthly achievement recognition', 'points_required' => 1000, 'stock' => null, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        foreach (['rewards', 'notifications'] as $module) {
            foreach (['view', 'create', 'update', 'delete'] as $action) {
                DB::table('permissions')->updateOrInsert(
                    ['slug' => "{$module}.{$action}"],
                    ['name' => ucfirst($action).' '.ucfirst($module), 'module' => $module, 'updated_at' => now(), 'created_at' => now()]
                );
            }
        }
        DB::table('roles')->updateOrInsert(
            ['slug' => 'front-office-cre'],
            ['name' => 'Front Office CRE', 'created_at' => now(), 'updated_at' => now()]
        );
        $creId = DB::table('roles')->where('slug', 'front-office-cre')->value('id');
        $crePermissions = DB::table('permissions')->whereIn('slug', [
            'dashboard.view', 'customers.view', 'customers.create', 'customers.update',
            'leads.view', 'leads.create', 'leads.update', 'followups.view', 'followups.create', 'followups.update',
            'tasks.view', 'tasks.update', 'notifications.view',
        ])->pluck('id');
        foreach ($crePermissions as $permissionId) {
            DB::table('permission_role')->insertOrIgnore(['role_id' => $creId, 'permission_id' => $permissionId]);
        }
        $adminId = DB::table('roles')->where('slug', 'super-admin')->value('id');
        if ($adminId) {
            foreach (DB::table('permissions')->whereIn('module', ['rewards', 'notifications'])->pluck('id') as $permissionId) {
                DB::table('permission_role')->insertOrIgnore(['role_id' => $adminId, 'permission_id' => $permissionId]);
            }
        }
        foreach ([
            'sales-manager' => ['rewards.view', 'rewards.update', 'notifications.view', 'notifications.update'],
            'sales-executive' => ['rewards.view', 'rewards.create', 'notifications.view', 'notifications.update'],
            'event-manager' => ['rewards.view', 'rewards.create', 'notifications.view', 'notifications.update'],
            'accountant' => ['rewards.view', 'rewards.create', 'notifications.view', 'notifications.update'],
        ] as $roleSlug => $slugs) {
            $roleId = DB::table('roles')->where('slug', $roleSlug)->value('id');
            if (!$roleId) continue;
            foreach (DB::table('permissions')->whereIn('slug', $slugs)->pluck('id') as $permissionId) {
                DB::table('permission_role')->insertOrIgnore(['role_id' => $roleId, 'permission_id' => $permissionId]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('app_notifications');
        Schema::dropIfExists('reward_redemptions');
        Schema::dropIfExists('reward_catalogs');
        Schema::dropIfExists('customer_category_rules');
        Schema::table('tasks', fn (Blueprint $table) => $table->dropColumn(['reward_points', 'rewarded_at']));
        Schema::table('customers', fn (Blueprint $table) => $table->dropColumn('category_override'));
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn(['reward_points', 'notification_preferences']));
    }
};
