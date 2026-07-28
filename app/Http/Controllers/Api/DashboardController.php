<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomOrder;
use App\Models\GiftCard;
use App\Models\Lead;
use App\Models\LeadFollowup;
use App\Models\LoyaltyPoint;
use App\Models\MarketingCampaign;
use App\Models\Sale;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $user = auth()->user();
        $executiveId = $user->role?->slug === 'sales-executive' ? $user->id : null;
        $period = DB::getDriverName() === 'sqlite' ? "strftime('%Y-%m', sale_date)" : "DATE_FORMAT(sale_date, '%Y-%m')";

        $leadQuery = Lead::query()->when($executiveId, fn ($query) => $query->where('assigned_to', $executiveId));
        $followupQuery = LeadFollowup::query()->when($executiveId, fn ($query) => $query->where('assigned_to', $executiveId));
        $customerQuery = Customer::query()->when($executiveId, fn ($query) => $query->where('assigned_to', $executiveId));
        $saleQuery = Sale::query()->when($executiveId, fn ($query) => $query->where('staff_id', $executiveId));
        $taskQuery = Task::query()->when($executiveId, fn ($query) => $query->where('assigned_to', $executiveId));

        $customers = (clone $customerQuery)->get();
        $upcomingOccasions = $customers->filter(function ($customer) {
            foreach ([$customer->birthday, $customer->anniversary] as $date) {
                if (!$date) continue;
                $next = now()->setDate(now()->year, date('m', strtotime($date)), date('d', strtotime($date)));
                if ($next->isPast()) $next->addYear();
                if ($next->diffInDays(now()) <= 30) return true;
            }
            return false;
        })->count();

        $eventVisitors = DB::table('exhibition_leads')
            ->when($executiveId, fn ($query) => $query->join('leads', 'leads.id', '=', 'exhibition_leads.lead_id')->where('leads.assigned_to', $executiveId));
        $repeatVisitors = (clone $eventVisitors)->select('exhibition_leads.lead_id')->groupBy('exhibition_leads.lead_id')->havingRaw('COUNT(DISTINCT exhibition_leads.exhibition_id) > 1')->get()->count();
        $totalEventVisitors = (clone $eventVisitors)->distinct('exhibition_leads.lead_id')->count('exhibition_leads.lead_id');

        $delivered = $executiveId ? 0 : MarketingCampaign::sum('delivered_count');
        $engaged = $executiveId ? 0 : (int) MarketingCampaign::selectRaw('COALESCE(SUM(clicked_count + replied_count),0) total')->value('total');
        $staffPerformance = User::with('role')
            ->where('is_active', true)
            ->whereHas('role', fn ($query) => $query->whereIn('slug', ['sales-manager', 'sales-executive']))
            ->when($executiveId, fn ($query) => $query->where('id', $executiveId))
            ->withCount([
                'assignedLeads',
                'assignedLeads as converted_leads_count' => fn ($query) => $query->where('status', 'Converted'),
                'assignedLeads as open_leads_count' => fn ($query) => $query->whereNotIn('status', ['Converted', 'Lost']),
                'assignedFollowups as pending_followups_count' => fn ($query) => $query->where('status', 'Pending')->where('scheduled_at', '>=', now()),
                'assignedFollowups as overdue_followups_count' => fn ($query) => $query->where('status', 'Pending')->where('scheduled_at', '<', now()),
                'assignedFollowups as completed_followups_count' => fn ($query) => $query->where('status', 'Completed'),
                'sales as sales_count',
            ])
            ->withSum('sales as sales_revenue', 'final_amount')
            ->get()
            ->map(function ($staff) {
                $staff->setAttribute('conversion_rate', $staff->assigned_leads_count ? round($staff->converted_leads_count / $staff->assigned_leads_count * 100, 1) : 0);
                $staff->setAttribute('sales_revenue', (float) ($staff->sales_revenue ?? 0));
                return $staff;
            })
            ->sortByDesc(fn ($staff) => [$staff->conversion_rate, $staff->converted_leads_count])
            ->values();

        $salesChart = Sale::selectRaw("$period period, SUM(final_amount) total")
            ->when($executiveId, fn ($query) => $query->where('staff_id', $executiveId))
            ->groupBy('period')->orderBy('period')->limit(12)->get();
        $leadSources = Lead::select('source', DB::raw('count(*) total'))
            ->when($executiveId, fn ($query) => $query->where('assigned_to', $executiveId))
            ->groupBy('source')->get();
        $sourcePerformance = Lead::leftJoin('customers', 'customers.lead_id', '=', 'leads.id')
            ->leftJoin('sales', 'sales.customer_id', '=', 'customers.id')
            ->when($executiveId, fn ($query) => $query->where('leads.assigned_to', $executiveId)->where(fn ($sales) => $sales->whereNull('sales.staff_id')->orWhere('sales.staff_id', $executiveId)))
            ->selectRaw('leads.source, COUNT(DISTINCT leads.id) leads, COUNT(DISTINCT customers.id) customers, COALESCE(SUM(sales.final_amount),0) revenue')
            ->groupBy('leads.source')->orderByDesc('revenue')->get();

        return [
            'scope' => $executiveId ? 'personal' : 'global',
            'metrics' => [
                'total_leads' => (clone $leadQuery)->count(),
                'today_followups' => (clone $followupQuery)->whereDate('scheduled_at', today())->count(),
                'pending_followups' => (clone $followupQuery)->where('status', 'Pending')->where('scheduled_at', '<', now())->count(),
                'converted_leads' => (clone $leadQuery)->where('status', 'Converted')->count(),
                'lost_leads' => (clone $leadQuery)->where('status', 'Lost')->count(),
                'total_sales' => (float) (clone $saleQuery)->sum('final_amount'),
                'custom_orders_pending' => CustomOrder::where('status', 'Processing')->when($executiveId, fn ($query) => $query->where('assigned_to', $executiveId))->count(),
                'exhibition_leads' => $totalEventVisitors,
                'gift_cards_issued' => GiftCard::when($executiveId, fn ($query) => $query->whereHas('customer', fn ($customer) => $customer->where('assigned_to', $executiveId)))->count(),
                'loyalty_points' => (int) LoyaltyPoint::where('type', 'Credit')->when($executiveId, fn ($query) => $query->whereHas('customer', fn ($customer) => $customer->where('assigned_to', $executiveId)))->sum('points'),
                'overdue_tasks' => (clone $taskQuery)->whereIn('status', ['Pending', 'pending', 'In Progress', 'in_progress'])->where('due_at', '<', now())->count(),
            ],
            'marketing_pulse' => [
                'repeat_visitors' => $repeatVisitors,
                'revisit_rate' => $totalEventVisitors ? round($repeatVisitors / $totalEventVisitors * 100, 1) : 0,
                'dormant_customers' => (clone $customerQuery)->whereDoesntHave('sales', fn ($query) => $query->where('sale_date', '>=', now()->subDays(180)))->count(),
                'uncontacted_leads' => (clone $leadQuery)->doesntHave('followups')->whereNotIn('status', ['Converted', 'Lost'])->count(),
                'upcoming_occasions' => $upcomingOccasions,
                'campaigns_sent' => $executiveId ? 0 : MarketingCampaign::where('status', 'Sent')->count(),
                'campaign_engagement_rate' => $delivered ? round($engaged / $delivered * 100, 1) : 0,
                'campaign_revenue' => $executiveId ? 0 : (float) MarketingCampaign::sum('attributed_revenue'),
                'whatsapp_consent_rate' => $customers->count() ? round($customers->where('whatsapp_opt_in', true)->count() / $customers->count() * 100, 1) : 0,
            ],
            'sales_chart' => $salesChart,
            'lead_sources' => $leadSources,
            'source_performance' => $sourcePerformance,
            'staff_performance' => $staffPerformance,
            'recent_leads' => Lead::with('assignee')->when($executiveId, fn ($query) => $query->where('assigned_to', $executiveId))->latest()->limit(6)->get(),
            'upcoming_followups' => LeadFollowup::with('lead', 'customer', 'assignee')->where('status', 'Pending')->when($executiveId, fn ($query) => $query->where('assigned_to', $executiveId))->orderBy('scheduled_at')->limit(6)->get(),
        ];
    }
}
