<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\CustomerImportantDate;
use App\Models\CustomOrder;
use App\Models\Exhibition;
use App\Models\FestivalCampaign;
use App\Models\GiftCard;
use App\Models\Lead;
use App\Models\LoyaltyPoint;
use App\Models\MarketingCampaign;
use App\Models\MessageTemplate;
use App\Models\Offer;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Sale;
use App\Models\Setting;
use App\Models\Task;
use App\Models\TaskRule;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $branch = Branch::create(['name' => 'Abhushan Flagship Showroom', 'code' => 'HQ', 'phone' => '+91 98765 43210', 'email' => 'showroom@jewellerycrm.test', 'address' => 'C-Scheme, Jaipur', 'city' => 'Jaipur']);
        $modules = ['dashboard', 'leads', 'followups', 'customers', 'sales', 'custom-orders', 'exhibitions', 'marketing', 'retention', 'offers', 'loyalty', 'gift-cards', 'tasks', 'reports', 'settings', 'staff'];
        $permissions = collect();
        foreach ($modules as $m) {
            foreach (['view', 'create', 'update', 'delete'] as $a) {
                $permissions->push(Permission::create(['name' => ucwords("$a ".str_replace('-', ' ', $m)), 'slug' => "$m.$a", 'module' => $m]));
            }
        }
        $roles = [];
        foreach (['Super Admin', 'Sales Manager', 'Sales Executive', 'Event Manager', 'Accountant'] as $name) {
            $roles[$name] = Role::create(['name' => $name, 'slug' => Str::slug($name), 'hierarchy_level' => [
                'Super Admin' => 1, 'Sales Manager' => 3, 'Sales Executive' => 4, 'Event Manager' => 3, 'Accountant' => 3,
            ][$name]]);
        }$roles['Super Admin']->permissions()->sync($permissions->pluck('id'));
        $roles['Sales Manager']->permissions()->sync($permissions->whereIn('module', ['dashboard', 'leads', 'followups', 'customers', 'sales', 'custom-orders', 'marketing', 'retention', 'offers', 'reports'])->pluck('id'));
        $roles['Sales Executive']->permissions()->sync($permissions
            ->whereIn('module', ['dashboard', 'leads', 'followups', 'customers', 'sales', 'retention', 'tasks'])
            ->reject(fn ($permission) => str_ends_with($permission->slug, '.delete'))
            ->pluck('id'));
        $roles['Event Manager']->permissions()->sync($permissions->whereIn('module', ['dashboard', 'exhibitions', 'marketing', 'offers', 'leads', 'followups', 'reports'])->pluck('id'));
        $roles['Accountant']->permissions()->sync($permissions->whereIn('module', ['dashboard', 'sales', 'loyalty', 'gift-cards', 'reports'])->pluck('id'));
        $allPermissions = Permission::query()->get();
        $roles['Front Office CRE'] = Role::firstOrCreate(['slug' => 'front-office-cre'], ['name' => 'Front Office CRE']);
        $roles['Front Office CRE']->permissions()->sync($allPermissions->whereIn('slug', [
            'dashboard.view', 'customers.view', 'customers.create', 'customers.update',
            'leads.view', 'leads.create', 'leads.update', 'followups.view', 'followups.create', 'followups.update',
            'tasks.view', 'tasks.update', 'notifications.view', 'notifications.update',
        ])->pluck('id'));
        $roles['Management'] = Role::firstOrCreate(['slug' => 'management'], ['name' => 'Management']);
        $roles['Management']->permissions()->sync($allPermissions
            ->whereNotIn('slug', ['settings.delete', 'staff.delete'])
            ->pluck('id'));
        $admin = User::create(['name' => 'Aarav Mehta', 'email' => 'admin@jewellerycrm.test', 'password' => Hash::make('Password@123'), 'role_id' => $roles['Super Admin']->id, 'branch_id' => $branch->id, 'phone' => '9876543210']);
        $staff = collect([$admin]);
        foreach ([['Kavya Sharma', 'manager@jewellerycrm.test', 'Sales Manager'], ['Riya Kapoor', 'sales@jewellerycrm.test', 'Sales Executive'], ['Kabir Singh', 'events@jewellerycrm.test', 'Event Manager'], ['Naina Shah', 'accounts@jewellerycrm.test', 'Accountant']] as [$n,$e,$r]) {
            $staff->push(User::create(['name' => $n, 'email' => $e, 'password' => Hash::make('Password@123'), 'role_id' => $roles[$r]->id, 'branch_id' => $branch->id]));
        }
        $events = Exhibition::factory(3)->create(['staff_ids' => [$staff[3]->id]]);
        foreach ([8, 4, 2] as $i => $monthsAgo) {
            $events[$i]->update(['start_date' => now()->subMonths($monthsAgo)->startOfMonth(), 'end_date' => now()->subMonths($monthsAgo)->startOfMonth()->addDays(3), 'status' => 'completed']);
        }
        $leads = Lead::factory(36)->make()->each(function ($lead, $i) use ($staff, $events, $admin) {
            $lead->assigned_to = $staff[1 + $i % 3]->id;
            $lead->created_by = $admin->id;
            if ($i < 10) {
                $lead->exhibition_id = $events[$i % 3]->id;
            }$lead->save();
            $lead->notes()->create(['user_id' => $lead->assigned_to, 'note' => fake()->randomElement(['Interested in a bridal consultation.', 'Shared catalogue on WhatsApp.', 'Requested a weekend showroom appointment.', 'Budget and design preferences discussed.'])]);
            $lead->followups()->create(['assigned_to' => $lead->assigned_to, 'type' => fake()->randomElement(['Call', 'WhatsApp', 'Visit', 'Meeting', 'Email']), 'scheduled_at' => fake()->dateTimeBetween('-5 days', '+10 days'), 'status' => fake()->randomElement(['Pending', 'Pending', 'Completed']), 'notes' => 'Follow up on selected designs']);
        });
        $customers = Customer::factory(15)->create()->each(function ($c, $i) use ($staff, $leads) {
            $c->update(['assigned_to' => $staff[1 + $i % 3]->id, 'lead_id' => $leads[$i]->id]);
            $leads[$i]->update(['status' => 'Converted']);
            $c->familyMembers()->create(['name' => fake()->name(), 'relation' => fake()->randomElement(['Spouse', 'Daughter', 'Son', 'Mother']), 'birthday' => fake()->date()]);
        });
        $eventVisitors = [[0, 20], [10, 28], [5, 25]];
        foreach ($eventVisitors as $eventIndex => [$from, $to]) {
            foreach (range($from, $to) as $leadIndex) {
                $lead = $leads[$leadIndex];
                $previousVisits = $lead->exhibitions()->count();
                $visitorType = $lead->customer ? 'Returning Customer' : ($previousVisits ? 'Returning Lead' : 'First Visit');
                $events[$eventIndex]->leads()->attach($lead->id, [
                    'captured_by' => $staff[3]->id, 'visitor_type' => $visitorType,
                    'visit_count' => fake()->numberBetween(1, 3), 'first_seen_at' => now()->subDays(90 - $eventIndex * 30),
                    'last_seen_at' => now()->subDays(88 - $eventIndex * 30), 'interest_snapshot' => json_encode($lead->product_interests),
                    'stated_budget' => $lead->budget_max, 'visit_notes' => fake()->randomElement(['Requested private bridal appointment', 'Revisited diamond collection', 'Interested in festive preview', 'Compared custom design options']),
                ]);
            }
        }
        foreach (range(1, 28) as $i) {
            $customer = $customers->random();
            $sale = Sale::factory()->create(['customer_id' => $customer->id, 'staff_id' => $staff[1 + $i % 3]->id]);
            $sale->items()->create(['product_category' => fake()->randomElement(['Gold', 'Diamond', 'Bridal', 'Polki']), 'jewellery_type' => fake()->randomElement(['Ring', 'Necklace', 'Bangles', 'Earrings']), 'metal_type' => fake()->randomElement(['Gold', 'Rose Gold', 'Platinum']), 'purity' => '22K', 'gross_weight' => fake()->randomFloat(3, 5, 60), 'net_weight' => fake()->randomFloat(3, 4, 55), 'stone_weight' => fake()->randomFloat(3, 0, 5), 'making_charge' => fake()->numberBetween(5000, 35000), 'total' => $sale->subtotal]);
            $sale->payments()->create(['amount' => $sale->paid_amount, 'mode' => fake()->randomElement(['Cash', 'UPI', 'Card', 'Bank Transfer']), 'paid_at' => $sale->sale_date, 'status' => 'Completed']);
            $points = (int) floor($sale->final_amount / 100);
            $customer->increment('loyalty_balance', $points);
            $customer->increment('lifetime_value', $sale->final_amount);
            LoyaltyPoint::create(['customer_id' => $customer->id, 'sale_id' => $sale->id, 'type' => 'Credit', 'points' => $points, 'balance_after' => $customer->fresh()->loyalty_balance, 'description' => 'Points earned on '.$sale->invoice_number]);
        }
        foreach (range(0, 11) as $i) {
            CustomOrder::factory()->create(['customer_id' => $customers[$i % 15]->id, 'assigned_to' => $staff[2]->id]);
        } $offers = Offer::factory(5)->create();
        $campaigns = collect([
            ['name' => 'Royal Expo Return Invitation', 'objective' => 'Increase Revisit', 'channels' => ['WhatsApp', 'Email'], 'offer_id' => $offers[0]->id, 'exhibition_id' => $events[1]->id, 'audience_rules' => ['audience' => 'returning-visitors'], 'segment_name' => 'Returning exhibition visitors', 'subject' => 'A private preview awaits you', 'message' => 'Hello {{name}}, your jewellery preferences are remembered. Visit our private preview and enjoy {{offer}}.', 'status' => 'Sent', 'sent_count' => 8, 'delivered_count' => 8, 'opened_count' => 6, 'clicked_count' => 4, 'replied_count' => 3, 'converted_count' => 2, 'attributed_revenue' => 385000],
            ['name' => 'VIP Diamond Preview', 'objective' => 'Cross-sell Diamond', 'channels' => ['WhatsApp'], 'offer_id' => $offers[1]->id, 'audience_rules' => ['audience' => 'vip'], 'segment_name' => 'VIP and HNI patrons', 'message' => 'Hello {{name}}, we have curated a private diamond edit around your taste. Reply YES for an appointment.', 'status' => 'Sent', 'sent_count' => 6, 'delivered_count' => 6, 'opened_count' => 5, 'clicked_count' => 3, 'replied_count' => 2, 'converted_count' => 1, 'attributed_revenue' => 240000],
            ['name' => 'Dormant Patron Trust Check-in', 'objective' => 'Win Back', 'channels' => ['Email'], 'audience_rules' => ['audience' => 'dormant', 'days' => 180], 'segment_name' => 'No purchase in 180 days', 'subject' => 'Your jewellery care consultation is on us', 'message' => 'Dear {{name}}, we would love to clean and inspect your jewellery with our compliments. No purchase expected.', 'status' => 'Draft'],
        ])->map(fn ($data) => MarketingCampaign::create($data + ['created_by' => $admin->id, 'estimated_audience' => $data['sent_count'] ?? 12]));
        foreach ($campaigns->where('status', 'Sent') as $campaign) {
            foreach ($customers->take($campaign->sent_count) as $customer) {
                $campaign->recipients()->create(['recipient_type' => Customer::class, 'recipient_id' => $customer->id, 'name' => $customer->name, 'mobile' => $customer->mobile, 'email' => $customer->email, 'channels' => $campaign->channels, 'status' => 'Delivered', 'sent_at' => now()->subDays(10), 'delivered_at' => now()->subDays(10)]);
            }
        }
        foreach (range(1, 8) as $i) {
            $amount = fake()->randomElement([5000, 10000, 25000, 50000]);
            GiftCard::create(['code' => 'GIFT-'.strtoupper(Str::random(8)), 'original_amount' => $amount, 'balance' => $amount, 'customer_id' => $customers->random()->id, 'expiry_date' => now()->addYear(), 'status' => 'Active', 'issued_by' => $admin->id]);
        }
        GiftCard::first()?->update(['expiry_date' => now()->addDays(12)]);
        foreach ($customers->take(5) as $i => $customer) {
            $customer->update(['language_preference' => ['English', 'Tamil', 'Telugu'][$i % 3], 'preferred_metal' => $i % 2 ? 'Gold' : 'Diamond', 'preferred_budget_min' => 50000, 'preferred_budget_max' => 300000]);
            CustomerImportantDate::create(['customer_id' => $customer->id, 'title' => $i % 2 ? 'Spouse Birthday' : 'Family Wedding', 'date_type' => $i % 2 ? 'spouse_birthday' : 'family_wedding', 'date_value' => now()->addDays(5 + $i * 4), 'relation_name' => $i % 2 ? 'Spouse' : 'Daughter', 'relation_type' => $i % 2 ? 'Spouse' : 'Child', 'notes' => 'Personal gifting opportunity']);
        }
        $templateBodies = [
            'birthday' => 'Hello {customer_name}, your birthday is in {days_remaining} days. We have selected {preferred_product} based on your preferences. {loyalty_points} loyalty points are available.',
            'anniversary' => 'Hello {customer_name}, your anniversary is in {days_remaining} days. We have curated {preferred_product} for you. {suggested_offer}. May we share a few design options?',
            'cleaning' => 'Hello {customer_name}, your complimentary cleaning and inspection for {last_product} is due soon. Would you like us to schedule an appointment at your convenience?',
            'winback' => 'Hello {customer_name}, it has been a while since we connected. A private edit of {preferred_product} is ready for you. {suggested_offer}. No purchase pressure, just personal service.',
            'gift_card' => 'Hello {customer_name}, you have ₹{gift_card_balance} available on your gift card. Let us help you choose {preferred_product} before it expires.',
            'loyalty' => 'Hello {customer_name}, you have {loyalty_points} loyalty points ready to redeem. Use them on your favorite {preferred_product}.',
            'festival' => 'Hello {customer_name}, for {occasion} we have selected {preferred_product} based on your taste and {budget_range}. {suggested_offer}.',
            'vip_invite' => 'Hello {customer_name}, a private preview of {preferred_product} has been reserved for you. Please share your preferred time and your consultant will assist you personally.',
        ];
        foreach ($templateBodies as $type => $body) {
            MessageTemplate::create(['title' => ucwords(str_replace('_', ' ', $type)).' English', 'message_type' => $type, 'language' => 'English', 'body' => $body, 'variables' => ['customer_name', 'days_remaining', 'preferred_product', 'suggested_offer'], 'is_active' => true]);
        }
        $rules = [
            ['New lead call within 24 hours', 'new_lead_24h', 'leads', 'age_hours', '24', 'lead_followup_call', 'urgent', 24, 0], ['Hot lead follow-up every 2 days', 'hot_lead_2d', 'leads', 'inactive_days', '2', 'sales_opportunity_call', 'urgent', 0, 2], ['Warm lead follow-up every 5 days', 'warm_lead_5d', 'leads', 'inactive_days', '5', 'lead_followup_call', 'high', 0, 5], ['Birthday call 15 days before', 'birthday_15d', 'customers', 'days_before', '15', 'birthday_call', 'medium', 0, 0], ['Anniversary call 30 days before', 'anniversary_30d', 'customers', 'days_before', '30', 'anniversary_call', 'medium', 0, 0], ['Feedback call 7 days after sale', 'sale_feedback_7d', 'sales', 'days_after', '7', 'customer_feedback_call', 'medium', 0, 7], ['Cleaning reminder after 6 months', 'cleaning_6m', 'sales', 'months_after', '6', 'cleaning_reminder_call', 'medium', 0, 180], ['Winback after 6 months', 'winback_6m', 'customers', 'inactive_days', '180', 'winback_call', 'medium', 0, 180], ['VIP relationship after 90 days', 'vip_90d', 'customers', 'inactive_days', '90', 'vip_relationship_call', 'high', 0, 90], ['Gift card expiry reminder', 'gift_expiry_15d', 'gift_cards', 'days_before', '15', 'gift_card_reminder_call', 'urgent', 0, 0], ['Loyalty reminder', 'loyalty_30d', 'loyalty', 'inactive_days', '90', 'loyalty_reminder_call', 'medium', 0, 0], ['Exhibition lead within 24 hours', 'exhibition_24h', 'exhibitions', 'age_hours', '24', 'exhibition_lead_followup_call', 'urgent', 24, 0], ['Partial payment daily', 'partial_payment', 'sales', 'payment_status', 'Partial', 'payment_pending_call', 'urgent', 24, 0], ['Custom order approval', 'custom_approval', 'custom_orders', 'status', 'Designing', 'custom_order_approval_call', 'high', 24, 0],
        ];
        foreach ($rules as [$name,$key,$module,$condition,$value,$type,$priority,$hours,$days]) {
            TaskRule::create(['rule_name' => $name, 'rule_key' => $key, 'module' => $module, 'condition_type' => $condition, 'condition_value' => $value, 'task_type' => $type, 'priority' => $priority, 'due_after_hours' => $hours, 'due_after_days' => $days]);
        }
        FestivalCampaign::create(['title' => 'Wedding Season Personal Edit', 'festival_name' => 'Wedding Season', 'start_date' => now()->subDays(5), 'end_date' => now()->addDays(45), 'customer_type' => 'All', 'product_category' => 'Bridal jewellery', 'offer_details' => 'Complimentary private bridal consultation', 'message_template_id' => MessageTemplate::where('message_type', 'festival')->value('id'), 'status' => 'Active']);
        foreach (range(1, 14) as $i) {
            Task::create(['title' => fake()->randomElement(['Confirm bridal appointment', 'Prepare design estimate', 'Call VIP customer', 'Review event lead list', 'Arrange delivery']), 'assigned_to' => $staff[1 + $i % 4]->id, 'created_by' => $admin->id, 'due_at' => fake()->dateTimeBetween('-3 days', '+12 days'), 'priority' => fake()->randomElement(['Low', 'Medium', 'High', 'Urgent']), 'status' => fake()->randomElement(['Pending', 'Pending', 'Completed'])]);
        }
        foreach (['company.name' => 'Kalasha Fine Jewels', 'company.currency' => 'INR', 'company.gst' => '08ABCDE1234F1Z5', 'loyalty.points_per_1000' => 10, 'invoice.prefix' => 'INV', 'tax.gst_rate' => 3] as $k => $v) {
            Setting::create(['group' => str($k)->before('.'), 'key' => $k, 'value' => $v, 'type' => gettype($v), 'is_public' => true]);
        }ActivityLog::create(['user_id' => $admin->id, 'action' => 'demo_data_seeded', 'properties' => ['version' => '1.0']]);
    }
}
