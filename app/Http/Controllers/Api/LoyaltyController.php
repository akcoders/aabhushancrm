<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\LoyaltyPoint;
use App\Services\LoyaltyService;
use Illuminate\Http\Request;

class LoyaltyController extends Controller
{
    public function index(Request $r)
    {
        return LoyaltyPoint::with('customer')->when($r->customer_id, fn ($q, $v) => $q->where('customer_id', $v))->latest()->paginate(20);
    }

    public function wallet(Customer $customer)
    {
        return ['customer' => $customer, 'balance' => $customer->loyalty_balance, 'history' => $customer->loyaltyPoints()->paginate(20)];
    }

    public function adjust(Request $r, Customer $customer, LoyaltyService $service)
    {
        $d = $r->validate(['points' => 'required|integer|not_in:0', 'description' => 'required|string']);

        return response()->json($service->adjust($customer, $d['points'], $d['description']), 201);
    }
}
