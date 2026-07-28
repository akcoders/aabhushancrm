<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $t) {
            $t->string('gender', 20)->nullable();
            $t->string('language_preference', 20)->default('English');
            $t->string('preferred_metal')->nullable();
            $t->decimal('preferred_budget_min', 14, 2)->nullable();
            $t->decimal('preferred_budget_max', 14, 2)->nullable();
            $t->boolean('contact_valid')->default(true);
        });
        Schema::table('leads', function (Blueprint $t) {
            $t->boolean('contact_valid')->default(true);
        });
        Schema::table('customer_family_members', function (Blueprint $t) {
            $t->string('jewellery_interest')->nullable();
            $t->text('notes')->nullable();
        });
        Schema::table('tasks', function (Blueprint $t) {
            $t->string('task_code')->nullable()->unique();
            $t->string('task_type')->nullable()->index();
            $t->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('sale_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('custom_order_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('exhibition_id')->nullable()->constrained()->nullOnDelete();
            $t->text('reason')->nullable();
            $t->text('suggested_action')->nullable();
            $t->string('suggested_product')->nullable();
            $t->string('suggested_offer')->nullable();
            $t->text('call_script')->nullable();
            $t->text('whatsapp_message')->nullable();
            $t->text('whatsapp_url')->nullable();
            $t->boolean('auto_generated')->default(false)->index();
            $t->string('generated_by_rule')->nullable();
            $t->timestamp('completed_at')->nullable();
            $t->text('skipped_reason')->nullable();
            $t->string('outcome')->nullable();
            $t->unsignedTinyInteger('no_response_count')->default(0);
        });
        Schema::create('customer_important_dates', function (Blueprint $t) {
            $t->id();
            $t->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $t->string('title');
            $t->string('date_type')->index();
            $t->date('date_value');
            $t->string('relation_name')->nullable();
            $t->string('relation_type')->nullable();
            $t->text('notes')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
        Schema::create('message_templates', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->string('message_type')->index();
            $t->string('language')->default('English')->index();
            $t->text('body');
            $t->json('variables')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
        Schema::create('customer_retention_scores', function (Blueprint $t) {
            $t->id();
            $t->foreignId('customer_id')->unique()->constrained()->cascadeOnDelete();
            $t->unsignedTinyInteger('score');
            $t->json('score_reason')->nullable();
            $t->text('suggested_action')->nullable();
            $t->string('suggested_offer')->nullable();
            $t->string('suggested_product')->nullable();
            $t->timestamp('calculated_at');
            $t->timestamps();
        });
        Schema::create('retention_messages', function (Blueprint $t) {
            $t->id();
            $t->foreignId('customer_id')->nullable()->constrained()->cascadeOnDelete();
            $t->foreignId('lead_id')->nullable()->constrained()->cascadeOnDelete();
            $t->foreignId('event_id')->nullable()->constrained('exhibitions')->nullOnDelete();
            $t->foreignId('smart_task_id')->nullable()->constrained('tasks')->nullOnDelete();
            $t->string('message_type')->index();
            $t->text('reason');
            $t->date('occasion_date')->nullable()->index();
            $t->integer('days_remaining')->nullable();
            $t->string('suggested_product')->nullable();
            $t->string('suggested_offer')->nullable();
            $t->text('generated_message');
            $t->text('whatsapp_url')->nullable();
            $t->string('status')->default('pending')->index();
            $t->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('generated_at');
            $t->timestamp('contacted_at')->nullable();
            $t->timestamps();
            $t->index(['customer_id', 'message_type', 'occasion_date']);
        });
        Schema::create('cleaning_reminders', function (Blueprint $t) {
            $t->id();
            $t->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $t->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $t->string('product_name');
            $t->date('purchase_date');
            $t->date('reminder_date')->index();
            $t->string('status')->default('pending');
            $t->text('message');
            $t->timestamps();
            $t->unique(['sale_id', 'reminder_date']);
        });
        Schema::create('festival_campaigns', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->string('festival_name')->index();
            $t->date('start_date');
            $t->date('end_date');
            $t->string('customer_type')->default('All');
            $t->string('product_category')->nullable();
            $t->text('offer_details')->nullable();
            $t->foreignId('message_template_id')->nullable()->constrained()->nullOnDelete();
            $t->string('status')->default('Draft')->index();
            $t->timestamps();
        });
        Schema::create('task_rules', function (Blueprint $t) {
            $t->id();
            $t->string('rule_name');
            $t->string('rule_key')->unique();
            $t->string('module')->index();
            $t->string('condition_type');
            $t->string('condition_value')->nullable();
            $t->string('task_type');
            $t->string('priority');
            $t->integer('due_after_hours')->default(0);
            $t->integer('due_after_days')->default(0);
            $t->boolean('is_active')->default(true)->index();
            $t->timestamps();
        });
        Schema::create('task_outcomes', function (Blueprint $t) {
            $t->id();
            $t->foreignId('smart_task_id')->constrained('tasks')->cascadeOnDelete();
            $t->string('outcome');
            $t->text('notes')->nullable();
            $t->string('next_action_type')->nullable();
            $t->dateTime('next_action_date')->nullable();
            $t->boolean('next_action_created')->default(false);
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_outcomes');
        Schema::dropIfExists('task_rules');
        Schema::dropIfExists('festival_campaigns');
        Schema::dropIfExists('cleaning_reminders');
        Schema::dropIfExists('retention_messages');
        Schema::dropIfExists('customer_retention_scores');
        Schema::dropIfExists('message_templates');
        Schema::dropIfExists('customer_important_dates');
        Schema::table('tasks', fn (Blueprint $t) => $t->dropColumn(['task_code', 'task_type', 'customer_id', 'lead_id', 'sale_id', 'custom_order_id', 'exhibition_id', 'reason', 'suggested_action', 'suggested_product', 'suggested_offer', 'call_script', 'whatsapp_message', 'whatsapp_url', 'auto_generated', 'generated_by_rule', 'completed_at', 'skipped_reason', 'outcome', 'no_response_count']));
        Schema::table('customer_family_members', fn (Blueprint $t) => $t->dropColumn(['jewellery_interest', 'notes']));
        Schema::table('customers', fn (Blueprint $t) => $t->dropColumn(['gender', 'language_preference', 'preferred_metal', 'preferred_budget_min', 'preferred_budget_max', 'contact_valid']));
    }
};
