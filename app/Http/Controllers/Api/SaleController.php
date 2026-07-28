<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ModuleRequest;
use App\Http\Resources\CrmResource;
use App\Models\Sale;
use App\Services\SaleService;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function index(Request $r)
    {
        $q = Sale::with('customer', 'staff', 'items')
            ->when($this->isSalesExecutive(), fn ($query) => $query->where('staff_id', auth()->id()))
            ->when($r->search, fn ($q, $s) => $q->where(fn ($search) => $search->where('invoice_number', 'like', "%$s%")->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%$s%"))))
            ->when($r->payment_status, fn ($q, $v) => $q->where('payment_status', $v));

        return CrmResource::collection($q->latest('sale_date')->paginate(15));
    }

    public function store(ModuleRequest $r, SaleService $s)
    {
        if ($this->isSalesExecutive()) {
            abort_unless(\App\Models\Customer::whereKey($r->validated('customer_id'))->where('assigned_to', auth()->id())->exists(), 403, 'This customer is assigned to another salesperson.');
        }
        $extra = $r->only(['payments', 'discount', 'tax', 'final_amount', 'staff_id', 'notes']);
        if ($this->isSalesExecutive()) $extra['staff_id'] = auth()->id();
        return response()->json($s->create($r->validated() + $extra), 201);
    }

    public function show(Sale $sale)
    {
        $this->authorizeOwned($sale);
        return $sale->load('customer', 'staff', 'items', 'payments');
    }

    public function destroy(Sale $sale)
    {
        $this->authorizeOwned($sale);
        $sale->delete();

        return ['message' => 'Sale deleted'];
    }

    private function isSalesExecutive(): bool
    {
        return auth()->user()?->role?->slug === 'sales-executive';
    }

    private function authorizeOwned(Sale $sale): void
    {
        if ($this->isSalesExecutive()) {
            abort_unless((int) $sale->staff_id === (int) auth()->id(), 403, 'This sale belongs to another salesperson.');
        }
    }
}
