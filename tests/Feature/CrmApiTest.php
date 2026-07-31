<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Exhibition;
use App\Models\Lead;
use App\Models\RetentionMessage;
use App\Models\Task;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Branch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CrmApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_login_and_view_database_backed_dashboard(): void
    {
        $this->seed();
        $login = $this->postJson('/api/auth/login', ['email' => 'admin@jewellerycrm.test', 'password' => 'Password@123'])->assertOk()->assertJsonStructure(['token', 'user' => ['role']]);
        $this->withToken($login->json('token'))->getJson('/api/dashboard')->assertOk()->assertJsonPath('metrics.total_leads', 36)->assertJsonStructure(['sales_chart', 'lead_sources', 'staff_performance']);
    }

    public function test_authenticated_user_can_create_update_and_delete_a_lead(): void
    {
        $this->seed();
        $token = User::first()->createToken('test')->plainTextToken;
        $salesperson = User::whereHas('role', fn ($query) => $query->where('slug', 'sales-executive'))->firstOrFail();
        $created = $this->withToken($token)->postJson('/api/leads', ['name' => 'Test Patron', 'mobile' => '9999900000', 'email' => 'patron@example.test', 'source' => 'Website', 'status' => 'New', 'priority' => 'Hot', 'assigned_to' => $salesperson->id])->assertCreated()->assertJsonPath('data.name', 'Test Patron')->assertJsonPath('data.assignee.id', $salesperson->id);
        $id = $created->json('data.id');
        $this->withToken($token)->putJson("/api/leads/$id", ['name' => 'Test Patron', 'mobile' => '9999900000', 'status' => 'Interested', 'priority' => 'Hot', 'assigned_to' => $salesperson->id])->assertOk()->assertJsonPath('data.status', 'Interested');
        $this->withToken($token)->deleteJson("/api/leads/$id")->assertOk();
        $this->assertSoftDeleted('leads', ['id' => $id]);
    }

    public function test_returning_event_visitor_is_recognized_without_duplicate_lead(): void
    {
        $this->seed();
        $event = Exhibition::first();
        $lead = $event->leads()->first();
        $before = Lead::count();
        $visitsBefore = DB::table('exhibition_leads')->count();

        $this->postJson("/api/events/{$event->public_token}/capture", [
            'name' => $lead->name, 'mobile' => $lead->mobile, 'email' => $lead->email,
            'product_interests' => ['Diamond jewellery'], 'whatsapp_opt_in' => true,
        ])->assertCreated()->assertJsonPath('recognized', true)->assertJsonPath('lead_id', $lead->id);

        $this->assertSame($before, Lead::count());
        $this->assertDatabaseCount('exhibition_leads', $visitsBefore);
    }

    public function test_campaign_can_preview_consenting_audience_and_launch(): void
    {
        $this->seed();
        $token = User::first()->createToken('test')->plainTextToken;
        $preview = $this->withToken($token)->postJson('/api/marketing/preview', [
            'channels' => ['WhatsApp'], 'audience_rules' => ['audience' => 'returning-visitors'],
        ])->assertOk();
        $this->assertGreaterThan(0, $preview->json('count'));

        $campaign = $this->withToken($token)->postJson('/api/marketing-campaigns', [
            'name' => 'Return Visit Test', 'objective' => 'Revisit', 'channels' => ['WhatsApp'],
            'audience_rules' => ['audience' => 'returning-visitors'], 'message' => 'Welcome back {{name}}',
        ])->assertCreated();
        $this->withToken($token)->postJson('/api/marketing-campaigns/'.$campaign->json('id').'/launch')
            ->assertOk()->assertJsonPath('campaign.status', 'Sent');
    }

    public function test_retention_scan_personalizes_messages_and_prevents_duplicates(): void
    {
        $this->seed();
        $this->artisan('retention:scan')->assertSuccessful();
        $messages = RetentionMessage::count();
        $tasks = Task::whereNotNull('task_type')->count();
        $message = RetentionMessage::with('customer')->firstOrFail();
        $this->assertStringContainsString($message->customer->name, $message->generated_message);
        $this->assertStringStartsWith('https://wa.me/', $message->whatsapp_url);

        $this->artisan('retention:scan')->assertSuccessful();
        $this->assertSame($messages, RetentionMessage::count());
        $this->assertSame($tasks, Task::whereNotNull('task_type')->count());
    }

    public function test_smart_task_has_complete_guidance_and_outcome_creates_next_action(): void
    {
        $this->seed();
        $this->artisan('smart-tasks:generate')->assertSuccessful();
        $generatedCount = Task::whereNotNull('task_type')->count();
        $this->artisan('smart-tasks:generate')->assertSuccessful();
        $this->assertSame($generatedCount, Task::whereNotNull('task_type')->count());
        $task = Task::whereNotNull('customer_id')->whereIn('status', ['pending', 'in_progress'])->firstOrFail();
        $this->assertNotEmpty($task->reason);
        $this->assertNotEmpty($task->call_script);
        $this->assertNotEmpty($task->whatsapp_message);
        $this->assertNotEmpty($task->suggested_product);
        $this->assertNotEmpty($task->suggested_offer);

        $token = User::first()->createToken('test')->plainTextToken;
        $this->withToken($token)->postJson("/api/smart-tasks/{$task->id}/complete", [
            'outcome' => 'Interested', 'notes' => 'Customer asked for selected designs.',
        ])->assertOk()->assertJsonPath('task.status', 'completed')->assertJsonPath('outcome.next_action_created', true);
        $this->assertDatabaseHas('task_outcomes', ['smart_task_id' => $task->id, 'outcome' => 'Interested']);
    }

    public function test_dashboard_reports_salesperson_conversion_and_followup_performance(): void
    {
        $this->seed();
        $token = User::first()->createToken('test')->plainTextToken;

        $this->withToken($token)->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonStructure(['staff_performance' => [[
                'id', 'name', 'role', 'assigned_leads_count', 'open_leads_count',
                'converted_leads_count', 'conversion_rate', 'pending_followups_count',
                'overdue_followups_count', 'completed_followups_count', 'sales_count',
                'sales_revenue',
            ]]]);
    }

    public function test_privilege_card_can_be_issued_to_customer_and_loaded_on_profile(): void
    {
        $this->seed();
        $token = User::first()->createToken('test')->plainTextToken;
        $customer = Customer::firstOrFail();

        $created = $this->withToken($token)->postJson('/api/privilege-cards', [
            'customer_id' => $customer->id,
            'tier' => 'Diamond',
            'amount' => 250000,
            'issued_at' => today()->toDateString(),
            'expires_at' => today()->addYear()->toDateString(),
            'status' => 'Active',
            'notes' => 'Private preview and priority service.',
        ])->assertCreated()
            ->assertJsonPath('customer.id', $customer->id)
            ->assertJsonPath('tier', 'Diamond')
            ->assertJsonPath('amount', '250000.00');

        $this->assertMatchesRegularExpression('/^\d{16}$/', $created->json('card_number'));

        $this->withToken($token)->getJson("/api/customers/{$customer->id}")
            ->assertOk()
            ->assertJsonPath('data.privilege_cards.0.card_number', $created->json('card_number'));
    }

    public function test_customer_is_automatically_categorized_as_hni_after_high_value_sale(): void
    {
        $this->seed();
        $admin = User::firstOrFail();
        $customer = Customer::where('lifetime_value', 0)->first() ?? Customer::firstOrFail();
        $customer->update(['lifetime_value' => 0, 'category' => 'Normal', 'category_override' => false]);
        $this->withToken($admin->createToken('test')->plainTextToken)->postJson('/api/sales', [
            'customer_id' => $customer->id, 'sale_date' => today()->toDateString(),
            'items' => [['product_category' => 'Gold', 'jewellery_type' => 'Ring', 'metal_type' => 'Gold', 'total' => 100001]],
            'subtotal' => 100001, 'final_amount' => 100001, 'payments' => [],
        ])->assertCreated();
        $this->assertSame('HNI', $customer->refresh()->category);
    }

    public function test_manual_task_completion_awards_staff_points_only_once(): void
    {
        $this->seed();
        $admin = User::firstOrFail();
        $staff = User::whereHas('role', fn ($query) => $query->where('slug', 'sales-executive'))->firstOrFail();
        $task = Task::create(['title' => 'Reward test', 'assigned_to' => $staff->id, 'created_by' => $admin->id, 'due_at' => now()->addHour(), 'status' => 'Pending', 'priority' => 'Medium', 'reward_points' => 25]);
        $token = $admin->createToken('test')->plainTextToken;
        $payload = ['title' => $task->title, 'assigned_to' => $staff->id, 'due_at' => $task->due_at, 'status' => 'Completed', 'priority' => 'Medium', 'reward_points' => 25];
        $this->withToken($token)->putJson("/api/tasks/{$task->id}", $payload)->assertOk();
        $this->withToken($token)->putJson("/api/tasks/{$task->id}", $payload)->assertOk();
        $this->assertSame(25, $staff->refresh()->reward_points);
    }

    public function test_front_office_cre_can_manage_customers_but_cannot_view_sales(): void
    {
        $this->seed();
        $role = Role::where('slug', 'front-office-cre')->firstOrFail();
        $cre = User::factory()->create(['role_id' => $role->id]);
        $token = $cre->createToken('test')->plainTextToken;
        $this->withToken($token)->getJson('/api/customers')->assertOk();
        $this->withToken($token)->getJson('/api/sales')->assertForbidden();
    }

    public function test_daily_notification_command_creates_user_summaries_without_duplicates(): void
    {
        $this->seed();
        $this->artisan('crm:daily-notifications')->assertSuccessful();
        $count = \App\Models\AppNotification::count();
        $this->assertGreaterThan(0, $count);
        $this->artisan('crm:daily-notifications')->assertSuccessful();
        $this->assertSame($count, \App\Models\AppNotification::count());
    }

    public function test_customer_category_cannot_be_manually_selected(): void
    {
        $this->seed();
        $admin = User::firstOrFail();
        $created = $this->withToken($admin->createToken('test')->plainTextToken)->postJson('/api/customers', [
            'name' => 'Automatic Category Customer',
            'mobile' => '9876500011',
            'category' => 'HNI',
            'category_override' => true,
        ])->assertCreated();

        $this->assertSame('Normal', Customer::findOrFail($created->json('data.id'))->category);
    }

    public function test_manager_can_view_complete_staff_performance_profile(): void
    {
        $this->seed();
        $admin = User::firstOrFail();
        $staff = User::whereHas('role', fn ($query) => $query->where('slug', 'sales-executive'))->firstOrFail();

        $this->withToken($admin->createToken('test')->plainTextToken)
            ->getJson("/api/staff/{$staff->id}/performance")
            ->assertOk()
            ->assertJsonPath('staff.id', $staff->id)
            ->assertJsonStructure([
                'summary' => [
                    'assigned_actions', 'pending_actions', 'overdue_actions', 'completed_actions',
                    'timely_actions', 'late_actions', 'timely_rate', 'reward_points_available',
                    'reward_points_earned', 'assigned_leads', 'converted_leads', 'sales_count', 'sales_revenue',
                ],
                'tasks', 'followups', 'redemptions',
            ]);
    }

    public function test_admin_can_create_role_with_hierarchy_and_permissions(): void
    {
        $this->seed();
        $admin = User::firstOrFail();
        $permissions = Permission::whereIn('slug', ['dashboard.view', 'customers.view'])->pluck('id')->all();

        $this->withToken($admin->createToken('test')->plainTextToken)
            ->postJson('/api/settings/roles', [
                'name' => 'Customer Care Officer',
                'hierarchy_level' => 5,
                'permissions' => $permissions,
            ])
            ->assertCreated()
            ->assertJsonPath('name', 'Customer Care Officer')
            ->assertJsonPath('hierarchy_level', 5)
            ->assertJsonCount(2, 'permissions');
    }

    public function test_staff_creation_requires_a_higher_level_reporting_manager(): void
    {
        $this->seed();
        $admin = User::whereHas('role', fn ($query) => $query->where('slug', 'super-admin'))->firstOrFail();
        $executiveRole = Role::where('slug', 'sales-executive')->firstOrFail();
        $manager = User::whereHas('role', fn ($query) => $query->where('slug', 'sales-manager'))->firstOrFail();
        $token = $admin->createToken('test')->plainTextToken;
        $payload = [
            'name' => 'New Salesperson', 'email' => 'new-salesperson@example.test',
            'password' => 'Password@123', 'role_id' => $executiveRole->id,
        ];

        $this->withToken($token)->postJson('/api/staff', $payload)->assertUnprocessable();
        $this->withToken($token)->postJson('/api/staff', $payload + ['reporting_manager_id' => $manager->id])
            ->assertCreated()
            ->assertJsonPath('reporting_manager.id', $manager->id);
    }

    public function test_admin_can_create_update_and_safely_delete_branches(): void
    {
        $this->seed();
        $admin = User::firstOrFail();
        $token = $admin->createToken('test')->plainTextToken;
        $created = $this->withToken($token)->postJson('/api/settings/branches', [
            'name' => 'New City Showroom', 'code' => 'NCS', 'city' => 'Udaipur',
            'phone' => '9876500022', 'is_active' => true,
        ])->assertCreated()->assertJsonPath('code', 'NCS');
        $branchId = $created->json('id');

        $this->withToken($token)->putJson("/api/settings/branches/{$branchId}", [
            'name' => 'New City Flagship', 'code' => 'NCS', 'city' => 'Udaipur',
            'phone' => '9876500022', 'is_active' => false,
        ])->assertOk()->assertJsonPath('name', 'New City Flagship')->assertJsonPath('is_active', false);

        $this->withToken($token)->deleteJson("/api/settings/branches/{$branchId}")->assertOk();
        $this->assertSoftDeleted('branches', ['id' => $branchId]);

        $assignedBranch = Branch::firstOrFail();
        $this->withToken($token)->deleteJson("/api/settings/branches/{$assignedBranch->id}")->assertUnprocessable();
    }

    public function test_sales_executive_has_operational_permissions_and_can_load_dashboard(): void
    {
        $this->seed();
        $executive = User::whereHas('role', fn ($query) => $query->where('slug', 'sales-executive'))->firstOrFail();
        $this->assertTrue($executive->hasPermission('dashboard.view'));
        $this->assertTrue($executive->hasPermission('leads.view'));
        $this->assertTrue($executive->hasPermission('customers.view'));
        $this->assertTrue($executive->hasPermission('sales.create'));
        $this->assertFalse($executive->hasPermission('sales.delete'));

        $token = $executive->createToken('test')->plainTextToken;
        $this->withToken($token)
            ->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonPath('scope', 'personal')
            ->assertJsonPath('metrics.total_leads', Lead::where('assigned_to', $executive->id)->count())
            ->assertJsonPath('staff_performance.0.id', $executive->id)
            ->assertJsonCount(1, 'staff_performance');

        $leadList = $this->withToken($token)->getJson('/api/leads?per_page=100')->assertOk()->json('data');
        $this->assertNotEmpty($leadList);
        $this->assertTrue(collect($leadList)->every(fn ($lead) => $lead['assigned_to'] === $executive->id));
        $otherLead = Lead::where('assigned_to', '!=', $executive->id)->firstOrFail();
        $this->withToken($token)->getJson("/api/leads/{$otherLead->id}")->assertForbidden();

        $customerList = $this->withToken($token)->getJson('/api/customers?per_page=100')->assertOk()->json('data');
        $this->assertNotEmpty($customerList);
        $this->assertTrue(collect($customerList)->every(fn ($customer) => $customer['assigned_to'] === $executive->id));
        $otherCustomer = Customer::where('assigned_to', '!=', $executive->id)->firstOrFail();
        $this->withToken($token)->getJson("/api/customers/{$otherCustomer->id}")->assertForbidden();

        $followups = $this->withToken($token)->getJson('/api/followups?per_page=100')->assertOk()->json('data');
        $this->assertTrue(collect($followups)->every(fn ($followup) => $followup['assigned_to'] === $executive->id));
        $otherFollowup = \App\Models\LeadFollowup::where('assigned_to', '!=', $executive->id)->firstOrFail();
        $this->withToken($token)->getJson("/api/followups/{$otherFollowup->id}")->assertForbidden();

        $tasks = $this->withToken($token)->getJson('/api/tasks?per_page=100')->assertOk()->json('data');
        $this->assertTrue(collect($tasks)->every(fn ($task) => $task['assigned_to'] === $executive->id));
        $otherTask = Task::where('assigned_to', '!=', $executive->id)->firstOrFail();
        $this->withToken($token)->getJson("/api/tasks/{$otherTask->id}")->assertForbidden();

        $sales = $this->withToken($token)->getJson('/api/sales?per_page=100')->assertOk()->json('data');
        $this->assertNotEmpty($sales);
        $this->assertTrue(collect($sales)->every(fn ($sale) => $sale['staff_id'] === $executive->id));
        $otherSale = \App\Models\Sale::where('staff_id', '!=', $executive->id)->firstOrFail();
        $this->withToken($token)->getJson("/api/sales/{$otherSale->id}")->assertForbidden();

        Task::create(['title' => 'My smart task', 'task_type' => 'lead_followup_call', 'assigned_to' => $executive->id, 'created_by' => User::first()->id, 'due_at' => now(), 'status' => 'pending', 'priority' => 'medium']);
        $otherSmartTask = Task::create(['title' => 'Other smart task', 'task_type' => 'lead_followup_call', 'assigned_to' => User::whereKeyNot($executive->id)->first()->id, 'created_by' => User::first()->id, 'due_at' => now(), 'status' => 'pending', 'priority' => 'medium']);
        $smartTasks = $this->withToken($token)->getJson('/api/smart-tasks?per_page=100')->assertOk()->json('data');
        $this->assertTrue(collect($smartTasks)->every(fn ($task) => $task['assigned_to'] === $executive->id));
        $this->withToken($token)->getJson("/api/smart-tasks/{$otherSmartTask->id}")->assertForbidden();
    }

    public function test_interakt_reply_webhook_creates_linked_inbox_conversation(): void
    {
        $this->seed();
        $customer = Customer::whereNotNull('mobile')->firstOrFail();
        $this->postJson('/api/webhooks/interakt', [
            'type' => 'message_received',
            'data' => [
                'customer' => ['id' => 'interakt-contact-1', 'channel_phone_number' => '+91'.$customer->mobile, 'name' => $customer->name],
                'message' => ['id' => 'wa-message-1', 'message' => 'Please show me diamond options.'],
            ],
        ])->assertOk();
        $this->assertDatabaseHas('conversations', ['channel' => 'WhatsApp', 'customer_id' => $customer->id, 'unread_count' => 1]);
        $this->assertDatabaseHas('conversation_messages', ['external_message_id' => 'wa-message-1', 'direction' => 'Inbound']);
    }

    public function test_sales_executive_can_schedule_their_own_video_sale(): void
    {
        $this->seed();
        $executive = User::whereHas('role', fn ($q) => $q->where('slug', 'sales-executive'))->firstOrFail();
        $token = $executive->createToken('video-test')->plainTextToken;
        $created = $this->withToken($token)->postJson('/api/video-call-sales', [
            'title' => 'Diamond selection consultation', 'scheduled_at' => now()->addDay()->toDateTimeString(),
        ])->assertCreated();
        $this->assertSame($executive->id, $created->json('staff_id'));
        $this->assertStringStartsWith('https://meet.jit.si/Kalasha-', $created->json('meeting_url'));
    }
}
