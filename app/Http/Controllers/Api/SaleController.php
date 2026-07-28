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
        $q = Sale::with('customer', 'staff', 'items')->when($r->search, fn ($q, $s) => $q->where('invoice_number', 'like', "%$s%")->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%$s%")))->when($r->payment_status, fn ($q, $v) => $q->where('payment_status', $v));

        return CrmResource::collection($q->latest('sale_date')->paginate(15));
    }

    public function store(ModuleRequest $r, SaleService $s)
    {
        return response()->json($s->create($r->validated() + $r->only(['payments', 'discount', 'tax', 'final_amount', 'staff_id', 'notes'])), 201);
    }

    public function show(Sale $sale)
    {
        return $sale->load('customer', 'staff', 'items', 'payments');
    }

    public function destroy(Sale $sale)
    {
        $sale->delete();

        return ['message' => 'Sale deleted'];
    }
}
