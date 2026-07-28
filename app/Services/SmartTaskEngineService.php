<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomOrder;
use App\Models\Exhibition;
use App\Models\FestivalCampaign;
use App\Models\GiftCard;
use App\Models\Lead;
use App\Models\Sale;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class SmartTaskEngineService
{
    public function __construct(private PersonalizedMessageService $messages, private TaskAssignmentService $assignment) {}

    public function scanAndCreateTasks(): array
    {
        return ['leads' => $this->scanLeads(), 'customers' => $this->scanCustomers(), 'sales' => $this->scanSales(), 'custom_orders' => $this->scanCustomOrders(), 'exhibitions' => $this->scanExhibitions(), 'loyalty' => $this->scanLoyalty(), 'gift_cards' => $this->scanGiftCards(), 'festivals' => $this->scanFestivalCampaigns()];
    }

    public function scanLeads(): int
    {
        $count = 0;
        Lead::with(['followups', 'assignee'])->whereNotIn('status', ['Converted', 'Lost'])->each(function ($lead) use (&$count) {
            $last = $lead->followups->max('scheduled_at') ?? $lead->created_at;
            $days = now()->diffInDays($last);
            $type = null;
            $priority = 'medium';
            $reason = '';
            if ($lead->source === 'Exhibition' && $days >= 1) {
                $type = 'exhibition_lead_followup_call';
                $priority = 'urgent';
                $reason = 'Exhibition lead not contacted within 24 hours';
            } elseif ($lead->priority === 'Hot' && $days >= 2) {
                $type = 'sales_opportunity_call';
                $priority = 'urgent';
                $reason = 'Hot lead has no follow-up for 2 days';
            } elseif ($lead->priority === 'Warm' && $days >= 5) {
                $type = 'lead_followup_call';
                $priority = 'high';
                $reason = 'Warm lead needs follow-up after 5 days';
            } elseif ($lead->priority === 'Cold' && $days >= 15) {
                $type = 'lead_followup_call';
                $priority = 'low';
                $reason = 'Cold lead is ready for a soft follow-up';
            } elseif ($lead->status === 'New' && now()->diffInHours($lead->created_at) >= 24) {
                $type = 'lead_followup_call';
                $priority = 'urgent';
                $reason = 'New lead not contacted within 24 hours';
            }if ($type && $this->createTaskIfNotExists(['task_type' => $type, 'lead_id' => $lead->id, 'assigned_to' => $this->assignment->assignToLeadOwner($lead), 'priority' => $priority, 'reason' => $reason, 'due_at' => now(), 'suggested_product' => $lead->product_interests[0] ?? 'curated jewellery matching the enquiry', 'suggested_offer' => 'Personal consultation based on stated budget'])) {
                $count++;
            }
        });

        return $count;
    }

    public function scanCustomers(): int
    {
        $count = 0;
        Customer::with(['sales.items', 'giftCards', 'importantDates', 'familyMembers', 'assignee'])->each(function ($c) use (&$count) {
            foreach ([['birthday', $c->birthday, 15], ['anniversary', $c->anniversary, 30]] as [$type,$date,$window]) {
                if ($date && ($days = $this->daysToAnnual($date)) <= $window) {
                    $priority = $days <= 7 ? 'high' : 'medium';
                    if ($this->customerTask($c, $type.'_call', ucfirst($type)." in {$days} days", $priority, now(), ['occasion' => $type, 'days_remaining' => $days, 'message_type' => $type])) {
                        $count++;
                    }
                }
            }foreach ($c->familyMembers as $family) {
                if ($family->birthday && ($days = $this->daysToAnnual($family->birthday)) <= 15) {
                    if ($this->customerTask($c, 'family_event_call', "{$family->relation} {$family->name}'s birthday in {$days} days", $days <= 7 ? 'high' : 'medium', now(), ['occasion' => 'family birthday', 'days_remaining' => $days, 'family_member_name' => $family->name, 'relation' => $family->relation])) {
                        $count++;
                    }
                }
            }$last = $c->sales->first();
            $inactive = $last ? now()->diffInDays($last->sale_date) : 999;
            if ($inactive >= 365) {
                if ($this->customerTask($c, 'inactive_customer_call', 'No purchase in the last 12 months', in_array($c->category, ['VIP', 'HNI']) ? 'high' : 'medium')) {
                    $count++;
                }
            } elseif ($inactive >= 180) {
                if ($this->customerTask($c, 'winback_call', 'No purchase in the last 6 months', 'medium')) {
                    $count++;
                }
            }if (in_array($c->category, ['VIP', 'HNI']) && $inactive >= 90) {
                if ($this->customerTask($c, 'vip_relationship_call', "{$c->category} customer inactive for 90+ days", 'high')) {
                    $count++;
                }
            }
        });

        return $count;
    }

    public function scanSales(): int
    {
        $count = 0;
        Sale::with(['customer.sales.items', 'items', 'staff'])->each(function ($sale) use (&$count) {
            $days = (int) floor(now()->startOfDay()->diffInDays(Carbon::parse($sale->sale_date)->startOfDay()));
            if ($days === 1 && $this->customerTask($sale->customer, 'post_purchase_feedback_call', 'Purchase completed yesterday - thank-you call', 'medium', now()->addHours(2), [], ['sale_id' => $sale->id, 'assigned_to' => $sale->staff_id])) {
                $count++;
            }if ($days === 7 && $this->customerTask($sale->customer, 'customer_feedback_call', 'Purchase completed 7 days ago - feedback due', 'medium', now(), [], ['sale_id' => $sale->id, 'assigned_to' => $sale->staff_id])) {
                $count++;
            }if ($sale->payment_status === 'Partial' && $this->customerTask($sale->customer, 'payment_pending_call', 'Partial payment is pending', 'urgent', now(), [], ['sale_id' => $sale->id, 'assigned_to' => $sale->staff_id])) {
                $count++;
            }if ($sale->final_amount >= 500000 && $days <= 30 && $this->customerTask($sale->customer, 'vip_relationship_call', 'High-value purchase relationship follow-up', 'high', now()->addDay(), [], ['sale_id' => $sale->id])) {
                $count++;
            }
        });

        return $count;
    }

    public function scanCustomOrders(): int
    {
        $count = 0;
        CustomOrder::with('customer')->whereNotIn('status', ['Cancelled'])->each(function ($o) use (&$count) {
            [$type,$reason,$priority] = match ($o->status) {
                'Processing' => ['custom_order_update_call', 'Share order progress and maintain trust', 'medium'],'Order Ready' => ['delivery_confirmation_call', 'Order is ready - schedule collection or delivery', 'urgent'],default => ['custom_order_update_call', 'Customer update is due', 'medium']
            };
            if ($this->customerTask($o->customer, $type, $reason, $priority, now(), [], ['custom_order_id' => $o->id, 'assigned_to' => $o->assigned_to])) {
                $count++;
            }
        });

        return $count;
    }

    public function scanExhibitions(): int
    {
        $count = 0;
        Exhibition::with('leads')->where('end_date', '<=', today())->each(function ($event) use (&$count) {
            foreach ($event->leads as $lead) {
                if (! in_array($lead->status, ['Converted', 'Lost']) && $this->createTaskIfNotExists(['task_type' => 'exhibition_lead_followup_call', 'lead_id' => $lead->id, 'exhibition_id' => $event->id, 'assigned_to' => $lead->assigned_to ?? $this->assignment->assignToEventManager(), 'priority' => $lead->product_interests && in_array('Bridal jewellery', $lead->product_interests) ? 'high' : 'urgent', 'reason' => "Pending follow-up from completed exhibition {$event->name}", 'due_at' => now(), 'suggested_product' => $lead->product_interests[0] ?? 'event collection'])) {
                    $count++;
                }
            }
        });

        return $count;
    }

    public function scanLoyalty(): int
    {
        $count = 0;
        Customer::where('loyalty_balance', '>', 0)->with('sales')->each(function ($c) use (&$count) {
            $last = $c->sales->first();
            if (! $last || now()->diffInDays($last->sale_date) >= 90) {
                if ($this->customerTask($c, 'loyalty_reminder_call', "{$c->loyalty_balance} loyalty points are available", 'medium')) {
                    $count++;
                }
            }
        });

        return $count;
    }

    public function scanGiftCards(): int
    {
        $count = 0;
        GiftCard::with('customer')->where('balance', '>', 0)->where('status', 'Active')->each(function ($card) use (&$count) {
            if (! $card->customer) {
                return;
            }$days = (int) floor(now()->startOfDay()->diffInDays(Carbon::parse($card->expiry_date)->startOfDay(), false));
            if ($days <= 30 && $days >= 0) {
                if ($this->customerTask($card->customer, 'gift_card_reminder_call', 'Gift card balance ₹'.number_format($card->balance)." expires in {$days} days", $days <= 15 ? 'urgent' : 'high', now(), ['occasion' => 'gift card expiry', 'days_remaining' => $days])) {
                    $count++;
                }
            }
        });

        return $count;
    }

    public function scanFestivalCampaigns(): int
    {
        $count = 0;
        FestivalCampaign::where('status', 'Active')->whereDate('start_date', '<=', today())->whereDate('end_date', '>=', today())->each(function ($f) use (&$count) {
            $q = Customer::query();
            if ($f->customer_type !== 'All') {
                $q->where('category', $f->customer_type);
            }if ($f->product_category) {
                $q->whereJsonContains('product_interests', $f->product_category);
            }$q->limit(500)->each(function ($c) use ($f, &$count) {
                if ($this->customerTask($c, 'festival_offer_call', "{$f->festival_name} campaign opportunity", 'medium', now(), ['occasion' => $f->festival_name, 'message_type' => 'festival', 'suggested_offer' => $f->offer_details])) {
                    $count++;
                }
            });
        });

        return $count;
    }

    public function createTaskIfNotExists(array $data): ?Task
    {
        $due = Carbon::parse($data['due_at'] ?? now());
        $exists = Task::where('task_type', $data['task_type'])->when($data['customer_id'] ?? null, fn ($q, $id) => $q->where('customer_id', $id))->when($data['lead_id'] ?? null, fn ($q, $id) => $q->where('lead_id', $id))->where('reason', $data['reason'])->whereDate('due_at', $due)->whereIn('status', ['pending', 'in_progress', 'Pending', 'In Progress'])->exists();
        if ($exists) {
            return null;
        }$customer = isset($data['customer_id']) ? Customer::find($data['customer_id']) : null;
        $lead = isset($data['lead_id']) ? Lead::find($data['lead_id']) : null;
        $product = $data['suggested_product'] ?? ($customer ? $this->messages->suggestProduct($customer) : ($lead?->product_interests[0] ?? 'relevant jewellery collection'));
        $offer = $data['suggested_offer'] ?? ($customer ? $this->messages->suggestOffer($customer) : 'Personal consultation within stated budget');
        $context = ['occasion' => $data['reason'], 'suggested_offer' => $offer];
        $script = $customer ? $this->messages->generateCallScript($customer, $data['task_type'], $context) : "Hello {$lead?->name} ji, aapne {$product} mein interest dikhaya tha. Main aapki requirement samajhkar selected options share karna chahta/chahti hoon. Kya abhi baat kar sakte hain?";
        $message = $customer ? $this->messages->generateWhatsAppMessage($customer, $data['task_type'], $context) : "Hello {$lead?->name} ji, aapke {$product} interest ke hisaab se kuch selected options ready hain. Kya main designs aur details share kar du?";
        $mobile = $customer?->mobile ?? $lead?->mobile;
        $task = Task::create($data + ['task_code' => 'ST-'.now()->format('ymd').'-'.str_pad((string) (Task::withTrashed()->count() + 1), 5, '0', STR_PAD_LEFT), 'title' => $this->generateTaskTitle($data['task_type'], $customer?->name ?? $lead?->name), 'description' => $data['reason'], 'suggested_action' => $data['suggested_action'] ?? 'Call, understand response, then record outcome and next action', 'suggested_product' => $product, 'suggested_offer' => $offer, 'call_script' => $script, 'whatsapp_message' => $message, 'whatsapp_url' => $mobile ? $this->messages->generateWhatsAppUrl($mobile, $message) : null, 'auto_generated' => true, 'generated_by_rule' => $data['generated_by_rule'] ?? $data['task_type'], 'status' => 'pending', 'due_at' => $due, 'created_by' => auth()->id()]);

        return $task;
    }

    public function calculatePriority(string $type, array $context = []): string
    {
        return $context['priority'] ?? (str_contains($type, 'payment') || str_contains($type, 'exhibition') ? 'urgent' : 'medium');
    }

    public function assignTaskToStaff(Model $subject): ?int
    {
        return $subject instanceof Lead ? $this->assignment->assignToLeadOwner($subject) : ($subject instanceof Customer ? $this->assignment->assignToCustomerOwner($subject) : $this->assignment->assignToLeastBusyStaff());
    }

    public function generateTaskTitle(string $type, ?string $name = null): string
    {
        return ucwords(str_replace('_', ' ', $type)).($name ? " - {$name}" : '');
    }

    public function generateTaskReason(string $type): string
    {
        return ucwords(str_replace('_', ' ', $type)).' detected by CRM data';
    }

    public function generateSuggestedAction(): string
    {
        return 'Call customer, listen first, record outcome, and schedule the agreed next action';
    }

    public function generateCallScript(Customer $c, string $type, array $context = []): string
    {
        return $this->messages->generateCallScript($c, $type, $context);
    }

    public function generateWhatsAppMessage(Customer $c, string $type, array $context = []): string
    {
        return $this->messages->generateWhatsAppMessage($c, $type, $context);
    }

    public function generateWhatsAppUrl(string $mobile, string $message): string
    {
        return $this->messages->generateWhatsAppUrl($mobile, $message);
    }

    private function customerTask(Customer $c, string $type, string $reason, string $priority = 'medium', $due = null, array $context = [], array $extra = []): ?Task
    {
        return $this->createTaskIfNotExists(['task_type' => $type, 'customer_id' => $c->id, 'assigned_to' => $extra['assigned_to'] ?? $this->assignment->assignToCustomerOwner($c), 'priority' => $priority, 'reason' => $reason, 'due_at' => $due ?? now(), 'suggested_product' => $this->messages->suggestProduct($c, $context), 'suggested_offer' => $context['suggested_offer'] ?? $this->messages->suggestOffer($c)] + $extra);
    }

    private function daysToAnnual($date): int
    {
        $d = Carbon::parse($date)->setYear(now()->year);
        if ($d->isPast()) {
            $d->addYear();
        }

        return now()->startOfDay()->diffInDays($d->startOfDay());
    }
}
