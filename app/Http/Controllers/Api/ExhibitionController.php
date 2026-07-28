<?php

namespace App\Http\Controllers\Api;

use App\Models\Exhibition;
use App\Models\Lead;
use App\Models\Sale;
use App\Services\TaskAssignmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ExhibitionController extends CrudController
{
    protected string $model = Exhibition::class;

    protected array $searchable = ['name', 'location', 'stall_number'];

    protected array $filterable = ['status'];

    protected function defaults(Request $r): array
    {
        return ['public_token' => Str::random(32)];
    }

    public function show(int $id)
    {
        $exhibition = Exhibition::findOrFail($id);
        $exhibition->load(['leads.assignee', 'leads.customer.sales.items', 'leads.exhibitions', 'campaigns']);
        $leadIds = $exhibition->leads->pluck('id');
        $customerIds = $exhibition->leads->pluck('customer.id')->filter();
        $sales = Sale::with('customer', 'items')->whereIn('customer_id', $customerIds)->whereDate('sale_date', '>=', $exhibition->start_date)->get();
        $visitors = $exhibition->leads->map(function ($lead) {
            $sales = $lead->customer?->sales ?? collect();
            $lead->setAttribute('is_returning', $lead->pivot->visitor_type !== 'First Visit' || $lead->exhibitions->count() > 1);
            $lead->setAttribute('event_history_count', $lead->exhibitions->count());
            $lead->setAttribute('purchase_count', $sales->count());
            $lead->setAttribute('lifetime_value', (float) $sales->sum('final_amount'));
            $lead->setAttribute('last_purchase_at', $sales->max('sale_date'));

            return $lead;
        });
        $statusCounts = $visitors->groupBy('status')->map->count();
        $converted = $visitors->where('status', 'Converted')->count();
        $revenue = (float) $sales->sum('final_amount');
        $returning = $visitors->filter(fn ($lead) => $lead->pivot->visitor_type !== 'First Visit')->count();
        $exhibition->setRelation('leads', $visitors);
        $exhibition->setAttribute('analytics', ['visitors' => $visitors->count(), 'first_time' => $visitors->count() - $returning, 'returning' => $returning, 'return_rate' => $visitors->count() ? round($returning / $visitors->count() * 100, 1) : 0, 'converted' => $converted, 'conversion_rate' => $visitors->count() ? round($converted / $visitors->count() * 100, 1) : 0, 'sales_count' => $sales->count(), 'revenue' => $revenue, 'expense' => (float) $exhibition->expense, 'profit' => $revenue - (float) $exhibition->expense, 'roi' => $exhibition->expense > 0 ? round(($revenue - $exhibition->expense) / $exhibition->expense * 100, 1) : 0, 'followups' => DB::table('lead_followups')->whereIn('lead_id', $leadIds)->count(), 'pending_followups' => DB::table('lead_followups')->whereIn('lead_id', $leadIds)->where('status', 'Pending')->count(), 'campaigns' => $exhibition->campaigns->count(), 'pipeline' => $statusCounts]);
        $exhibition->setAttribute('sales', $sales);
        $exhibition->setAttribute('marketing_opportunities', ['hot_unconverted' => $visitors->where('priority', 'Hot')->where('status', '!=', 'Converted')->count(), 'returning_not_converted' => $visitors->where('is_returning', true)->where('status', '!=', 'Converted')->count(), 'without_followup' => $visitors->filter(fn ($l) => ! DB::table('lead_followups')->where('lead_id', $l->id)->exists())->count(), 'high_value_customers' => $visitors->filter(fn ($l) => $l->lifetime_value >= 200000)->count()]);

        return $exhibition;
    }

    public function capture(Request $r, string $token, TaskAssignmentService $assignment)
    {
        $event = Exhibition::where('public_token', $token)->firstOrFail();
        $d = $r->validate(['name' => 'required', 'mobile' => 'required', 'email' => 'nullable|email', 'product_interests' => 'nullable|array', 'budget_min' => 'nullable|numeric', 'budget_max' => 'nullable|numeric', 'whatsapp_opt_in' => 'nullable|boolean', 'email_opt_in' => 'nullable|boolean', 'visit_notes' => 'nullable|string']);
        $mobile = substr(preg_replace('/\D+/', '', $d['mobile']), -10);
        $email = $d['email'] ?? null;
        $lead = Lead::whereRaw("REPLACE(REPLACE(REPLACE(mobile,' ',''),'-',''),'+91','') LIKE ?", ["%$mobile"])->when($email, fn ($q) => $q->orWhere('email', $email))->first();
        $recognized = (bool) $lead;
        if (! $lead) {
            $lead = Lead::create(['name' => $d['name'], 'mobile' => $mobile, 'email' => $email, 'source' => 'Exhibition', 'status' => 'New', 'priority' => 'Warm', 'assigned_to' => $assignment->assignToSalesExecutive(), 'exhibition_id' => $event->id, 'product_interests' => $d['product_interests'] ?? [], 'budget_min' => $d['budget_min'] ?? null, 'budget_max' => $d['budget_max'] ?? null, 'whatsapp_opt_in' => $d['whatsapp_opt_in'] ?? true, 'email_opt_in' => $d['email_opt_in'] ?? true, 'last_engaged_at' => now()]);
        } else {
            $lead->update(['name' => $d['name'] ?: $lead->name, 'email' => $email ?: $lead->email, 'product_interests' => $d['product_interests'] ?? $lead->product_interests, 'budget_min' => $d['budget_min'] ?? $lead->budget_min, 'budget_max' => $d['budget_max'] ?? $lead->budget_max, 'whatsapp_opt_in' => $d['whatsapp_opt_in'] ?? $lead->whatsapp_opt_in, 'email_opt_in' => $d['email_opt_in'] ?? $lead->email_opt_in, 'last_engaged_at' => now()]);
        }$pastEvents = $lead->exhibitions()->where('exhibitions.id', '!=', $event->id)->count();
        $type = $lead->customer ? 'Returning Customer' : ($pastEvents ? 'Returning Lead' : 'First Visit');
        $existing = $event->leads()->where('leads.id', $lead->id)->first();
        $pivot = ['visitor_type' => $type, 'last_seen_at' => now(), 'interest_snapshot' => json_encode($d['product_interests'] ?? $lead->product_interests), 'stated_budget' => $d['budget_max'] ?? null, 'visit_notes' => $d['visit_notes'] ?? null];
        if ($existing) {
            $pivot['visit_count'] = $existing->pivot->visit_count + 1;
            $event->leads()->updateExistingPivot($lead->id, $pivot);
        } else {
            $pivot += ['visit_count' => 1, 'first_seen_at' => now()];
            $event->leads()->attach($lead->id, $pivot);
        }

        return response()->json(['message' => $recognized ? 'Welcome back! We have your preferences and your consultant can continue your journey.' : 'Thank you! Your private jewellery profile has been created.', 'lead_id' => $lead->id, 'recognized' => $recognized, 'visitor_type' => $type, 'journey_url' => "/leads/{$lead->id}/journey"], 201);
    }

    public function roi(Exhibition $exhibition)
    {
        $customerIds = $exhibition->leads()->with('customer')->get()->pluck('customer.id')->filter();
        $sales = Sale::whereIn('customer_id', $customerIds)->whereDate('sale_date', '>=', $exhibition->start_date)->sum('final_amount');

        return ['expense' => (float) $exhibition->expense, 'revenue' => (float) $sales, 'roi' => $exhibition->expense > 0 ? round(($sales - $exhibition->expense) / $exhibition->expense * 100, 2) : 0, 'leads' => $exhibition->leads()->count()];
    }
}
