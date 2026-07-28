<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PrivilegeCard;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PrivilegeCardController extends Controller
{
    public function index(Request $request)
    {
        $cards = PrivilegeCard::with(['customer', 'issuer'])
            ->when($request->string('search')->toString(), function ($query, $search) {
                $query->where(function ($nested) use ($search) {
                    $nested->where('card_number', 'like', "%{$search}%")
                        ->orWhereHas('customer', fn ($customer) => $customer
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('mobile', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('tier'), fn ($query) => $query->where('tier', $request->input('tier')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->when($request->filled('customer_id'), fn ($query) => $query->where('customer_id', $request->integer('customer_id')))
            ->latest()
            ->paginate(min((int) $request->input('per_page', 24), 100));

        return $cards;
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $card = PrivilegeCard::create($data + [
            'card_number' => $this->newCardNumber(),
            'issued_by' => $request->user()?->id,
        ]);

        return response()->json($card->load(['customer', 'issuer']), 201);
    }

    public function show(PrivilegeCard $privilegeCard)
    {
        return $privilegeCard->load(['customer', 'issuer']);
    }

    public function update(Request $request, PrivilegeCard $privilegeCard)
    {
        $privilegeCard->update($this->validated($request));

        return $privilegeCard->load(['customer', 'issuer']);
    }

    public function destroy(PrivilegeCard $privilegeCard)
    {
        $privilegeCard->delete();

        return ['message' => 'Privilege card removed successfully'];
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'tier' => ['required', Rule::in(['Silver', 'Gold', 'Platinum', 'Diamond'])],
            'amount' => ['required', 'numeric', 'min:0'],
            'issued_at' => ['required', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:issued_at'],
            'status' => ['required', Rule::in(['Active', 'Suspended', 'Expired', 'Cancelled'])],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
    }

    private function newCardNumber(): string
    {
        do {
            $number = '9'.implode('', array_map(
                fn () => (string) random_int(0, 9),
                range(1, 15)
            ));
        } while (PrivilegeCard::withTrashed()->where('card_number', $number)->exists());

        return $number;
    }
}
