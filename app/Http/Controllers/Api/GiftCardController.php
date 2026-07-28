<?php

namespace App\Http\Controllers\Api;

use App\Models\GiftCard;
use App\Services\GiftCardService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GiftCardController extends CrudController
{
    protected string $model = GiftCard::class;

    protected array $searchable = ['code'];

    protected array $filterable = ['status', 'customer_id'];

    protected array $with = ['customer', 'transactions'];

    protected function defaults(Request $r): array
    {
        return ['code' => 'GIFT-'.strtoupper(Str::random(10)), 'balance' => $r->original_amount, 'issued_by' => auth()->id()];
    }

    public function redeem(Request $r, GiftCard $giftCard, GiftCardService $service)
    {
        $d = $r->validate(['amount' => 'required|numeric|min:0.01', 'sale_id' => 'nullable|exists:sales,id']);

        return $service->redeem($giftCard, $d['amount'], $d['sale_id'] ?? null);
    }
}
