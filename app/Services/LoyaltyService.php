<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\LoyaltyPoint;
use App\Models\Sale;
use App\Models\Setting;

class LoyaltyService
{
    public function adjust(Customer $c, int $points, string $description, ?Sale $sale = null): LoyaltyPoint
    {
        $balance = max(0, $c->loyalty_balance + $points);
        $c->update(['loyalty_balance' => $balance]);

        return LoyaltyPoint::create(['customer_id' => $c->id, 'sale_id' => $sale?->id, 'type' => $points >= 0 ? 'Credit' : 'Debit', 'points' => $points, 'balance_after' => $balance, 'description' => $description, 'created_by' => auth()->id()]);
    }

    public function earn(Customer $c, Sale $s): int
    {
        $rate = (float) (Setting::where('key', 'loyalty.points_per_1000')->value('value') ?? 10);
        $points = (int) floor($s->final_amount / 1000 * $rate);
        if ($points) {
            $this->adjust($c, $points, "Earned on {$s->invoice_number}", $s);
        }

return $points;
    }
}
