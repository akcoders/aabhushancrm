<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\LeadFollowup;
use App\Models\RetentionMessage;
use App\Services\RetentionEngineService;
use App\Services\RetentionScoreService;
use Illuminate\Http\Request;

class RetentionController extends Controller
{
    public function index(Request $r)
    {
        $q = RetentionMessage::with('customer.retentionScore', 'lead', 'assignee', 'task')
            ->when($r->user()->role?->slug === 'sales-executive', fn ($query) => $query->where('assigned_to', $r->user()->id))
            ->when($r->status, fn ($q, $v) => $q->where('status', $v))->when($r->type, fn ($q, $v) => $q->where('message_type', $v))->when($r->range, function ($q, $v) {
            $days = match ($v) {
                'today' => 0,'7' => 7,'15' => 15,'30' => 30,default => null
            };
            if ($days !== null) {
                $q->whereBetween('occasion_date', [today(), today()->addDays($days)]);
            }
        })->when($r->search, fn ($q, $v) => $q->whereHas('customer', fn ($x) => $x->where('name', 'like', "%$v%")->orWhere('mobile', 'like', "%$v%")));

        return $q->orderByRaw('CASE WHEN days_remaining IS NULL THEN 999 ELSE days_remaining END')->latest()->paginate(min((int) $r->input('per_page', 20), 100));
    }

    public function today()
    {
        return $this->scopeDays(0);
    }

    public function upcoming()
    {
        return $this->scopeDays(30);
    }

    public function winback()
    {
        return $this->type('winback');
    }

    public function vip()
    {
        return RetentionMessage::with('customer.retentionScore', 'assignee')->whereHas('customer', fn ($q) => $q->whereIn('category', ['VIP', 'HNI']))->where('status', 'pending')->paginate(20);
    }

    public function action(Request $r, RetentionMessage $message, string $action)
    {
        abort_unless(in_array($action, ['copied', 'whatsapp_opened', 'contacted', 'ignored']), 422);
        $message->update(['status' => $action, 'contacted_at' => $action === 'contacted' ? now() : $message->contacted_at]);

        return $message->fresh(['customer', 'task']);
    }

    public function createFollowup(RetentionMessage $message)
    {
        $leadId = $message->lead_id ?? $message->customer?->lead_id;
        abort_unless($leadId, 422, 'Customer has no linked lead.');
        $f = LeadFollowup::create(['lead_id' => $leadId, 'customer_id' => $message->customer_id, 'assigned_to' => $message->assigned_to, 'type' => 'Call', 'scheduled_at' => now()->addDays(2), 'status' => 'Pending', 'notes' => $message->reason]);

        return response()->json($f, 201);
    }

    public function scan(RetentionEngineService $service)
    {
        return ['message' => 'Retention scan completed', 'results' => $service->run()];
    }

    public function profile(Customer $customer, RetentionScoreService $scores)
    {
        $score = $scores->calculate($customer);

        return $customer->load(['lead.exhibitions', 'familyMembers', 'importantDates', 'sales.items', 'customOrders', 'loyaltyPoints', 'giftCards.transactions', 'retentionMessages.task', 'smartTasks.outcomes'])->setAttribute('retention_score', $score);
    }

    private function scopeDays(int $days)
    {
        return RetentionMessage::with('customer.retentionScore', 'assignee', 'task')->where('status', 'pending')->where(function ($q) use ($days) {
            $q->whereBetween('occasion_date', [today(), today()->addDays($days)])->orWhereNull('occasion_date');
        })->paginate(20);
    }

    private function type(string $type)
    {
        return RetentionMessage::with('customer.retentionScore','assignee','task')->where('message_type',$type)->where('status','pending')->paginate(20);
    }
}
