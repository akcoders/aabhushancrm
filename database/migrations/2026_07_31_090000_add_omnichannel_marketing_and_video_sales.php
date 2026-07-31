<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marketing_campaigns', function (Blueprint $table) {
            $table->string('provider')->nullable()->after('channels');
            $table->string('template_name')->nullable()->after('provider');
            $table->string('template_language', 12)->default('en')->after('template_name');
            $table->string('media_url')->nullable()->after('message');
            $table->json('provider_data')->nullable();
        });
        Schema::table('campaign_recipients', function (Blueprint $table) {
            $table->string('external_message_id')->nullable()->index();
            $table->text('failure_reason')->nullable();
        });
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->string('channel')->index();
            $table->string('external_contact_id')->nullable()->index();
            $table->string('contact_name')->nullable();
            $table->string('contact_handle')->nullable()->index();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('Open')->index();
            $table->unsignedInteger('unread_count')->default(0);
            $table->timestamp('last_message_at')->nullable()->index();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
        Schema::create('conversation_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->string('external_message_id')->nullable()->unique();
            $table->string('direction')->index();
            $table->string('message_type')->default('text');
            $table->text('body')->nullable();
            $table->string('media_url')->nullable();
            $table->string('template_name')->nullable();
            $table->string('status')->default('Queued')->index();
            $table->foreignId('sent_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->json('provider_payload')->nullable();
            $table->timestamps();
        });
        Schema::create('ad_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('platform')->default('Instagram');
            $table->string('objective')->default('OUTCOME_ENGAGEMENT');
            $table->text('caption')->nullable();
            $table->string('media_url')->nullable();
            $table->string('destination_url')->nullable();
            $table->decimal('daily_budget', 14, 2);
            $table->json('audience')->nullable();
            $table->dateTime('scheduled_at');
            $table->dateTime('ends_at')->nullable();
            $table->string('status')->default('Draft')->index();
            $table->string('external_campaign_id')->nullable();
            $table->text('failure_reason')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('video_call_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('staff_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('room_name')->unique();
            $table->string('meeting_url');
            $table->dateTime('scheduled_at');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->string('status')->default('Scheduled')->index();
            $table->string('outcome')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('sale_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        foreach (['inbox', 'ads', 'video-calls'] as $module) {
            foreach (['view', 'create', 'update', 'delete'] as $action) {
                DB::table('permissions')->updateOrInsert(
                    ['slug' => "{$module}.{$action}"],
                    ['name' => ucfirst($action).' '.ucfirst($module), 'module' => $module, 'created_at' => now(), 'updated_at' => now()]
                );
            }
        }
        foreach (DB::table('roles')->whereIn('slug', ['super-admin', 'management', 'sales-manager'])->pluck('id') as $roleId) {
            foreach (DB::table('permissions')->whereIn('module', ['inbox', 'ads', 'video-calls'])->pluck('id') as $permissionId) {
                DB::table('permission_role')->insertOrIgnore(['role_id' => $roleId, 'permission_id' => $permissionId]);
            }
        }
        $salesRole = DB::table('roles')->where('slug', 'sales-executive')->value('id');
        if ($salesRole) {
            foreach (DB::table('permissions')->whereIn('slug', ['inbox.view', 'inbox.create', 'inbox.update', 'video-calls.view', 'video-calls.create', 'video-calls.update'])->pluck('id') as $permissionId) {
                DB::table('permission_role')->insertOrIgnore(['role_id' => $salesRole, 'permission_id' => $permissionId]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('video_call_sales');
        Schema::dropIfExists('ad_campaigns');
        Schema::dropIfExists('conversation_messages');
        Schema::dropIfExists('conversations');
        Schema::table('campaign_recipients', fn (Blueprint $table) => $table->dropColumn(['external_message_id', 'failure_reason']));
        Schema::table('marketing_campaigns', fn (Blueprint $table) => $table->dropColumn(['provider', 'template_name', 'template_language', 'media_url', 'provider_data']));
    }
};
