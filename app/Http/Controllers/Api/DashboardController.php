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
        $period = DB::getDriverName() === 'sqlite' ? "strftime('%Y-%m', sale_date)" : "DATE_FORMAT(sale_date, '%Y-%m')";
        $customers = Customer::all();
        $upcomingOccasions = $customers->filter(function ($c) {
            foreach ([$c->birthday, $c->anniversary] as $date) {
                if (! $date) {
                    continue;
                }$next = now()->setDate(now()->year, date('m', strtotime($date)), date('d', strtotime($date)));
                if ($next->isPast()) {
                    $next->addYear();
                }if ($next->diffInDays(now()) <= 30) {
                    return true;
                }
            }

            return false;
        })->count();
        $repeatVisitors = DB::table('exhibition_leads')->select('lead_id')->groupBy('lead_id')->havingRaw('COUNT(DISTINCT exhibition_id) > 1')->get()->count();
        $totalEventVisitors = DB::table('exhibition_leads')->distinct('lead_id')->count('lead_id');
        $delivered = MarketingCampaign::sum('delivered_count');
        $engaged = (int) MarketingCampaign::selectRaw('COALESCE(SUM(clicked_count + replied_count),0) total')->value('total');
        $staffPerformance = User::with('role')
            ->where('is_active', true)
            ->whereHas('role', fn ($q) => $q->whereIn('slug', ['sales-manager', 'sales-executive']))
            ->withCount([
                'assignedLeads',
                'assignedLeads as converted_leads_count' => fn ($q) => $q->where('status', 'Converted'),
                'assignedLeads as open_leads_count' => fn ($q) => $q->whereNotIn('status', ['Converted', 'Lost']),
                'assignedFollowups as pending_followups_count' => fn ($q) => $q->where('status', 'Pending')->where('scheduled_at', '>=', now()),
                'assignedFollowups as overdue_followups_count' => fn ($q) => $q->where('status', 'Pending')->where('scheduled_at', '<', now()),
                'assignedFollowups as completed_followups_count' => fn ($q) => $q->where('status', 'Completed'),
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

        return ['metrics' => ['total_leads' => Lead::count(), 'today_followups' => LeadFollowup::whereDate('scheduled_at', today())->count(), 'pending_followups' => LeadFollowup::where('status', 'Pending')->where('scheduled_at', '<', now())->count(), 'converted_leads' => Lead::where('status', 'Converted')->count(), 'lost_leads' => Lead::where('status', 'Lost')->count(), 'total_sales' => (float) Sale::sum('final_amount'), 'custom_orders_pending' => CustomOrder::where('status', 'Processing')->count(), 'exhibition_leads' => $totalEventVisitors, 'gift_cards_issued' => GiftCard::count(), 'loyalty_points' => (int) LoyaltyPoint::where('type', 'Credit')->sum('points'), 'overdue_tasks' => Task::where('status', 'Pending')->where('due_at', '<', now())->count()], 'marketing_pulse' => ['repeat_visitors' => $repeatVisitors, 'revisit_rate' => $totalEventVisitors ? round($repeatVisitors / $totalEventVisitors * 100, 1) : 0, 'dormant_customers' => Customer::whereDoesntHave('sales', fn ($q) => $q->where('sale_date', '>=', now()->subDays(180)))->count(), 'uncontacted_leads' => Lead::doesntHave('followups')->whereNotIn('status', ['Converted', 'Lost'])->count(), 'upcoming_occasions' => $upcomingOccasions, 'campaigns_sent' => MarketingCampaign::where('status', 'Sent')->count(), 'campaign_engagement_rate' => $delivered ? round($engaged / $delivered * 100, 1) : 0, 'campaign_revenue' => (float) MarketingCampaign::sum('attributed_revenue'), 'whatsapp_consent_rate' => $customers->count() ? round($customers->where('whatsapp_opt_in', true)->count() / $customers->count() * 100, 1) : 0], 'sales_chart' => Sale::selectRaw("$period period, SUM(final_amount) total")->groupBy('period')->orderBy('period')->limit(12)->get(), 'lead_sources' => Lead::select('source', DB::raw('count(*) total'))->groupBy('source')->get(), 'source_performance' => Lead::leftJoin('customers', 'customers.lead_id', '=', 'leads.id')->leftJoin('sales', 'sales.customer_id', '=', 'customers.id')->selectRaw('leads.source, COUNT(DISTINCT leads.id) leads, COUNT(DISTINCT customers.id) customers, COALESCE(SUM(sales.final_amount),0) revenue')->groupBy('leads.source')->orderByDesc('revenue')->get(), 'staff_performance' => $staffPerformance, 'recent_leads' => Lead::with('assignee')->latest()->limit(6)->get(), 'upcoming_followups' => LeadFollowup::with('lead', 'customer', 'assignee')->where('status', 'Pending')->orderBy('scheduled_at')->limit(6)->get()];
    }
}
