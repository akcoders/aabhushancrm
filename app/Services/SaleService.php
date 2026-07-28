<?php

namespace App\Services;

use App\Models\Sale;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class SaleService
{
    public function __construct(private LoyaltyService $loyalty, private CustomerCategorizationService $categorization) {}

    public function create(array $data): Sale
    {
        return DB::transaction(function () use ($data) {
            $items = Arr::pull($data, 'items', []);
            $payments = Arr::pull($data, 'payments', []);
            $data['invoice_number'] = $data['invoice_number'] ?? 'INV-'.now()->format('Ym').'-'.str_pad((string) (Sale::withTrashed()->count() + 1), 5, '0', STR_PAD_LEFT);
            $data['subtotal'] = $data['subtotal'] ?? collect($items)->sum('total');
            $data['final_amount'] = $data['final_amount'] ?? $data['subtotal'] - ($data['discount'] ?? 0) + ($data['tax'] ?? 0);
            $data['paid_amount'] = collect($payments)->sum('amount');
            $data['payment_status'] = $data['paid_amount'] >= $data['final_amount'] ? 'Paid' : ($data['paid_amount'] > 0 ? 'Partial' : 'Pending');
            $sale = Sale::create($data);
            $sale->items()->createMany($items);
            foreach ($payments as $p) {
                $sale->payments()->create($p + ['paid_at' => $p['paid_at'] ?? now(), 'status' => 'Completed']);
            }$points = $this->loyalty->earn($sale->customer, $sale);
            $sale->update(['points_earned' => $points]);
            $sale->customer()->increment('lifetime_value', $sale->final_amount);
            $this->categorization->categorize($sale->customer->refresh());

            return $sale->load('customer', 'items', 'payments', 'staff');
        });
    }
}
