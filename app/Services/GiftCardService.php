<?php

namespace App\Services;

use App\Models\GiftCard;
use Illuminate\Validation\ValidationException;

class GiftCardService
{
    public function redeem(GiftCard $card, float $amount, ?int $saleId = null): GiftCard
    {
        if ($card->status !== 'Active' || $card->expiry_date < today()->toDateString() || $amount <= 0 || $amount > $card->balance) {
            throw ValidationException::withMessages(['amount' => 'Gift card is invalid or has insufficient balance.']);
        }$balance = $card->balance - $amount;
        $card->update(['balance' => $balance, 'status' => $balance <= 0 ? 'Used' : 'Active']);
        $card->transactions()->create(['sale_id' => $saleId, 'type' => 'Redemption', 'amount' => $amount, 'balance_after' => $balance]);

        return $card->fresh('transactions');
    }
}
