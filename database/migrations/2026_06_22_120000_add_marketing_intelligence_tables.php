<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->boolean('whatsapp_opt_in')->default(true);
            $table->boolean('email_opt_in')->default(true);
            $table->timestamp('last_engaged_at')->nullable()->index();
        });
        Schema::table('customers', function (Blueprint $table) {
            $table->boolean('whatsapp_opt_in')->default(true);
            $table->boolean('email_opt_in')->default(true);
            $table->timestamp('last_engaged_at')->nullable()->index();
        });
        Schema::table('exhibition_leads', function (Blueprint $table) {
            $table->string('visitor_type')->default('First Visit')->index();
            $table->unsignedInteger('visit_count')->default(1);
            $table->timestamp('first_seen_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->json('interest_snapshot')->nullable();
            $table->decimal('stated_budget', 14, 2)->nullable();
            $table->text('visit_notes')->nullable();
        });
        Schema::create('marketing_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('objective')->default('Revisit');
            $table->json('channels');
            $table->foreignId('offer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('exhibition_id')->nullable()->constrained()->nullOnDelete();
            $table->json('audience_rules')->nullable();
            $table->string('segment_name')->nullable();
            $table->string('subject')->nullable();
            $table->text('message');
            $table->dateTime('scheduled_at')->nullable();
            $table->string('status')->default('Draft')->index();
            $table->unsignedInteger('estimated_audience')->default(0);
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('delivered_count')->default(0);
            $table->unsignedInteger('opened_count')->default(0);
            $table->unsignedInteger('clicked_count')->default(0);
            $table->unsignedInteger('replied_count')->default(0);
            $table->unsignedInteger('converted_count')->default(0);
            $table->decimal('attributed_revenue', 14, 2)->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('campaign_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('marketing_campaign_id')->constrained()->cascadeOnDelete();
            $table->nullableMorphs('recipient');
            $table->string('name');
            $table->string('mobile')->nullable();
            $table->string('email')->nullable();
            $table->json('channels');
            $table->string('status')->default('Queued')->index();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('clicked_at')->nullable();
            $table->timestamp('replied_at')->nullable();
            $table->timestamp('converted_at')->nullable();
            $table->foreignId('sale_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('revenue', 14, 2)->default(0);
            $table->timestamps();
            $table->unique(['marketing_campaign_id', 'recipient_type', 'recipient_id'], 'campaign_recipient_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_recipients');
        Schema::dropIfExists('marketing_campaigns');
        Schema::table('exhibition_leads', fn (Blueprint $table) => $table->dropColumn(['visitor_type', 'visit_count', 'first_seen_at', 'last_seen_at', 'interest_snapshot', 'stated_budget', 'visit_notes']));
        Schema::table('customers', fn (Blueprint $table) => $table->dropColumn(['whatsapp_opt_in', 'email_opt_in', 'last_engaged_at']));
        Schema::table('leads', fn (Blueprint $table) => $table->dropColumn(['whatsapp_opt_in', 'email_opt_in', 'last_engaged_at']));
    }
};
