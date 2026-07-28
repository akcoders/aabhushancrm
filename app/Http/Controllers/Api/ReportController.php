<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomOrder;
use App\Models\GiftCard;
use App\Models\Lead;
use App\Models\LeadFollowup;
use App\Models\LoyaltyPoint;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $r, string $type)
    {
        $from = $r->date('from') ?? now()->startOfMonth();
        $to = $r->date('to') ?? now();
        $data = match ($type) {
            'lead-conversion' => Lead::select('source', DB::raw('count(*) total'), DB::raw("sum(case when status='Converted' then 1 else 0 end) converted"))->whereBetween('created_at', [$from, $to])->groupBy('source')->get(),'staff-performance' => Sale::join('users', 'users.id', '=', 'sales.staff_id')->select('users.name', DB::raw('count(sales.id) sales_count'), DB::raw('sum(final_amount) revenue'), DB::raw('sum(commission_amount) commission'))->whereBetween('sale_date', [$from, $to])->groupBy('users.id', 'users.name')->get(),'sales' => Sale::with('customer', 'staff')->whereBetween('sale_date', [$from, $to])->get(),'followups' => LeadFollowup::with('lead', 'customer', 'assignee')->whereBetween('scheduled_at', [$from, $to])->get(),'lost-leads' => Lead::with('assignee')->where('status', 'Lost')->whereBetween('updated_at', [$from, $to])->get(),'customers' => Customer::withSum('sales', 'final_amount')->orderByDesc('sales_sum_final_amount')->get(),'loyalty' => LoyaltyPoint::with('customer')->whereBetween('created_at', [$from, $to])->get(),'gift-cards' => GiftCard::with('customer')->get(),'custom-orders' => CustomOrder::with('customer')->whereBetween('created_at', [$from, $to])->get(),default => []
        };

        return ['type' => $type, 'from' => (string) $from, 'to' => (string) $to, 'data' => $data];
    }
}
