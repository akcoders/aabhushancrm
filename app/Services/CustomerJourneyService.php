<?php

namespace App\Services;

use App\Models\CommunicationLog;
use App\Models\Lead;

class CustomerJourneyService
{
    public function forLead(Lead $lead): array
    {
        $lead->loadMissing(['customer.sales.items', 'customer.customOrders.statusLogs', 'customer.loyaltyPoints', 'customer.giftCards', 'exhibitions', 'followups.assignee', 'notes.user', 'campaigns.campaign.offer']);
        $customer = $lead->customer;
        $sales = $customer?->sales ?? collect();
        $events = $lead->exhibitions->map(fn ($event) => ['id' => $event->id, 'name' => $event->name, 'location' => $event->location, 'start_date' => $event->start_date, 'visitor_type' => $event->pivot->visitor_type, 'visit_count' => $event->pivot->visit_count, 'interests' => $event->pivot->interest_snapshot]);
        $communications = CommunicationLog::where(fn ($q) => $q->where(fn ($x) => $x->where('communicable_type', Lead::class)->where('communicable_id', $lead->id))->when($customer, fn ($x) => $x->orWhere(fn ($y) => $y->where('communicable_type', get_class($customer))->where('communicable_id', $customer->id))))->latest('communicated_at')->get();

        return ['identity' => ['lead_id' => $lead->id, 'customer_id' => $customer?->id, 'name' => $customer?->name ?? $lead->name, 'mobile' => $customer?->mobile ?? $lead->mobile, 'email' => $customer?->email ?? $lead->email, 'category' => $customer?->category ?? 'Lead', 'status' => $lead->status, 'whatsapp_opt_in' => (bool) ($customer?->whatsapp_opt_in ?? $lead->whatsapp_opt_in), 'email_opt_in' => (bool) ($customer?->email_opt_in ?? $lead->email_opt_in)], 'summary' => ['event_visits' => $events->sum('visit_count'), 'events_attended' => $events->count(), 'purchases' => $sales->count(), 'lifetime_value' => (float) $sales->sum('final_amount'), 'custom_orders' => $customer?->customOrders->count() ?? 0, 'followups' => $lead->followups->count(), 'campaigns_received' => $lead->campaigns->count() + ($customer?->campaigns()->count() ?? 0), 'last_purchase' => $sales->max('sale_date'), 'last_engagement' => $communications->max('communicated_at') ?? $lead->last_engaged_at], 'events' => $events, 'sales' => $sales, 'custom_orders' => $customer?->customOrders ?? [], 'followups' => $lead->followups, 'communications' => $communications, 'campaigns' => $lead->campaigns];
    }
}
