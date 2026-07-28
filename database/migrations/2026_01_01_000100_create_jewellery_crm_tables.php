<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personal_access_tokens', function (Blueprint $t) {
            $t->id();
            $t->morphs('tokenable');
            $t->string('name');
            $t->string('token', 64)->unique();
            $t->text('abilities')->nullable();
            $t->timestamp('last_used_at')->nullable();
            $t->timestamp('expires_at')->nullable()->index();
            $t->timestamps();
        });
        Schema::create('roles', function (Blueprint $t) {
            $t->id();
            $t->string('name')->unique();
            $t->string('slug')->unique();
            $t->timestamps();
        });
        Schema::create('permissions', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->string('module');
            $t->timestamps();
        });
        Schema::create('permission_role', function (Blueprint $t) {
            $t->foreignId('role_id')->constrained()->cascadeOnDelete();
            $t->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $t->primary(['role_id', 'permission_id']);
        });
        Schema::create('branches', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('code')->unique();
            $t->string('phone')->nullable();
            $t->string('email')->nullable();
            $t->text('address')->nullable();
            $t->string('city')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
            $t->softDeletes();
        });

        Schema::create('exhibitions', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('location');
            $t->date('start_date');
            $t->date('end_date');
            $t->string('stall_number')->nullable();
            $t->json('staff_ids')->nullable();
            $t->decimal('expense', 14, 2)->default(0);
            $t->string('public_token')->unique();
            $t->text('notes')->nullable();
            $t->string('status')->default('upcoming');
            $t->timestamps();
            $t->softDeletes();
        });
        Schema::create('leads', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('mobile', 30)->index();
            $t->string('email')->nullable()->index();
            $t->string('source')->default('Walk-in');
            $t->string('status')->default('New')->index();
            $t->string('priority')->default('Warm');
            $t->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('exhibition_id')->nullable()->constrained()->nullOnDelete();
            $t->decimal('budget_min', 14, 2)->nullable();
            $t->decimal('budget_max', 14, 2)->nullable();
            $t->string('occasion')->nullable();
            $t->date('expected_purchase_date')->nullable();
            $t->json('product_interests')->nullable();
            $t->json('tags')->nullable();
            $t->text('address')->nullable();
            $t->text('notes')->nullable();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
            $t->softDeletes();
        });
        Schema::create('lead_notes', function (Blueprint $t) {
            $t->id();
            $t->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->text('note');
            $t->json('attachments')->nullable();
            $t->timestamps();
        });
        Schema::create('lead_followups', function (Blueprint $t) {
            $t->id();
            $t->foreignId('lead_id')->nullable()->constrained()->cascadeOnDelete();
            $t->unsignedBigInteger('customer_id')->nullable()->index();
            $t->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $t->string('type');
            $t->dateTime('scheduled_at')->index();
            $t->string('status')->default('Pending');
            $t->boolean('reminder_sent')->default(false);
            $t->text('notes')->nullable();
            $t->text('outcome')->nullable();
            $t->dateTime('next_followup_at')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });
        Schema::create('lead_history', function (Blueprint $t) {
            $t->id();
            $t->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('action');
            $t->json('old_values')->nullable();
            $t->json('new_values')->nullable();
            $t->timestamps();
        });

        Schema::create('customers', function (Blueprint $t) {
            $t->id();
            $t->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $t->string('customer_code')->unique();
            $t->string('name');
            $t->string('mobile', 30)->index();
            $t->string('email')->nullable();
            $t->date('birthday')->nullable();
            $t->date('anniversary')->nullable();
            $t->text('address')->nullable();
            $t->string('city')->nullable();
            $t->string('category')->default('Normal');
            $t->integer('loyalty_balance')->default(0);
            $t->decimal('lifetime_value', 14, 2)->default(0);
            $t->json('product_interests')->nullable();
            $t->text('notes')->nullable();
            $t->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
            $t->softDeletes();
        });
        Schema::create('customer_family_members', function (Blueprint $t) {
            $t->id();
            $t->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('relation');
            $t->date('birthday')->nullable();
            $t->date('anniversary')->nullable();
            $t->string('phone')->nullable();
            $t->timestamps();
        });

        Schema::create('sales', function (Blueprint $t) {
            $t->id();
            $t->string('invoice_number')->unique();
            $t->foreignId('customer_id')->constrained();
            $t->foreignId('staff_id')->nullable()->constrained('users')->nullOnDelete();
            $t->date('sale_date');
            $t->decimal('subtotal', 14, 2)->default(0);
            $t->decimal('discount', 14, 2)->default(0);
            $t->decimal('tax', 14, 2)->default(0);
            $t->decimal('loyalty_discount', 14, 2)->default(0);
            $t->decimal('gift_card_discount', 14, 2)->default(0);
            $t->decimal('final_amount', 14, 2);
            $t->decimal('paid_amount', 14, 2)->default(0);
            $t->string('payment_status')->default('Pending');
            $t->integer('points_earned')->default(0);
            $t->integer('points_redeemed')->default(0);
            $t->decimal('commission_amount', 14, 2)->default(0);
            $t->text('notes')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });
        Schema::create('sale_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $t->string('product_category');
            $t->string('jewellery_type');
            $t->string('sku')->nullable();
            $t->string('metal_type');
            $t->string('purity')->nullable();
            $t->json('diamond_details')->nullable();
            $t->decimal('gross_weight', 10, 3)->default(0);
            $t->decimal('net_weight', 10, 3)->default(0);
            $t->decimal('stone_weight', 10, 3)->default(0);
            $t->decimal('metal_rate', 14, 2)->default(0);
            $t->decimal('making_charge', 14, 2)->default(0);
            $t->decimal('wastage', 8, 2)->default(0);
            $t->decimal('discount', 14, 2)->default(0);
            $t->decimal('tax', 14, 2)->default(0);
            $t->decimal('total', 14, 2);
            $t->timestamps();
        });
        Schema::create('payments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('sale_id')->nullable()->constrained()->cascadeOnDelete();
            $t->unsignedBigInteger('custom_order_id')->nullable()->index();
            $t->decimal('amount', 14, 2);
            $t->string('mode');
            $t->string('reference')->nullable();
            $t->dateTime('paid_at');
            $t->string('status')->default('Completed');
            $t->timestamps();
        });

        Schema::create('custom_orders', function (Blueprint $t) {
            $t->id();
            $t->string('order_number')->unique();
            $t->foreignId('customer_id')->constrained();
            $t->string('jewellery_type');
            $t->string('metal_type');
            $t->string('purity')->nullable();
            $t->decimal('approx_weight', 10, 3)->nullable();
            $t->decimal('estimated_amount', 14, 2);
            $t->decimal('advance_payment', 14, 2)->default(0);
            $t->date('due_date');
            $t->string('status')->default('New')->index();
            $t->string('vendor_name')->nullable();
            $t->string('design_reference')->nullable();
            $t->string('approval_status')->default('Pending');
            $t->text('internal_notes')->nullable();
            $t->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
            $t->softDeletes();
        });
        Schema::create('custom_order_status_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('custom_order_id')->constrained()->cascadeOnDelete();
            $t->string('from_status')->nullable();
            $t->string('to_status');
            $t->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $t->text('note')->nullable();
            $t->timestamps();
        });
        Schema::create('exhibition_leads', function (Blueprint $t) {
            $t->id();
            $t->foreignId('exhibition_id')->constrained()->cascadeOnDelete();
            $t->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $t->foreignId('captured_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
            $t->unique(['exhibition_id', 'lead_id']);
        });

        Schema::create('offers', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->string('type');
            $t->decimal('value', 14, 2)->default(0);
            $t->date('start_date');
            $t->date('end_date');
            $t->string('customer_type')->default('All');
            $t->string('product_category')->nullable();
            $t->string('coupon_code')->unique();
            $t->unsignedInteger('usage_limit')->nullable();
            $t->unsignedInteger('usage_count')->default(0);
            $t->string('status')->default('Active');
            $t->text('description')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });
        Schema::create('offer_usages', function (Blueprint $t) {
            $t->id();
            $t->foreignId('offer_id')->constrained();
            $t->foreignId('customer_id')->constrained();
            $t->foreignId('sale_id')->nullable()->constrained();
            $t->decimal('discount_amount', 14, 2)->default(0);
            $t->timestamp('used_at');
            $t->timestamps();
        });
        Schema::create('loyalty_points', function (Blueprint $t) {
            $t->id();
            $t->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $t->foreignId('sale_id')->nullable()->constrained()->nullOnDelete();
            $t->string('type');
            $t->integer('points');
            $t->integer('balance_after');
            $t->string('description');
            $t->date('expires_at')->nullable();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });
        Schema::create('gift_cards', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();
            $t->decimal('original_amount', 14, 2);
            $t->decimal('balance', 14, 2);
            $t->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $t->date('expiry_date');
            $t->string('status')->default('Active');
            $t->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
            $t->softDeletes();
        });
        Schema::create('gift_card_transactions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('gift_card_id')->constrained()->cascadeOnDelete();
            $t->foreignId('sale_id')->nullable()->constrained()->nullOnDelete();
            $t->string('type');
            $t->decimal('amount', 14, 2);
            $t->decimal('balance_after', 14, 2);
            $t->text('notes')->nullable();
            $t->timestamps();
        });

        Schema::create('tasks', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->text('description')->nullable();
            $t->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->dateTime('due_at')->index();
            $t->string('priority')->default('Medium');
            $t->string('status')->default('Pending');
            $t->nullableMorphs('related');
            $t->text('notes')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });
        Schema::create('communication_logs', function (Blueprint $t) {
            $t->id();
            $t->nullableMorphs('communicable');
            $t->string('type');
            $t->string('direction')->default('Outbound');
            $t->string('subject')->nullable();
            $t->text('content')->nullable();
            $t->string('status')->default('Logged');
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->dateTime('communicated_at');
            $t->timestamps();
        });
        Schema::create('activity_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('action');
            $t->nullableMorphs('subject');
            $t->json('properties')->nullable();
            $t->string('ip_address')->nullable();
            $t->text('user_agent')->nullable();
            $t->timestamps();
        });
        Schema::create('settings', function (Blueprint $t) {
            $t->id();
            $t->string('group')->default('general');
            $t->string('key')->unique();
            $t->json('value')->nullable();
            $t->string('type')->default('string');
            $t->boolean('is_public')->default(false);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['settings', 'activity_logs', 'communication_logs', 'tasks', 'gift_card_transactions', 'gift_cards', 'loyalty_points', 'offer_usages', 'offers', 'exhibition_leads', 'custom_order_status_logs', 'custom_orders', 'payments', 'sale_items', 'sales', 'customer_family_members', 'customers', 'lead_history', 'lead_followups', 'lead_notes', 'leads', 'exhibitions', 'branches', 'permission_role', 'permissions', 'roles', 'personal_access_tokens'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
