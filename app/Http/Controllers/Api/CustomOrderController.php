<?php

namespace App\Http\Controllers\Api;

use App\Models\CustomOrder;
use Illuminate\Http\Request;

class CustomOrderController extends CrudController
{
    protected string $model = CustomOrder::class;

    protected array $searchable = ['order_number', 'jewellery_type', 'vendor_name'];

    protected array $filterable = ['status', 'approval_status', 'customer_id', 'assigned_to'];

    protected array $with = ['customer', 'assignee'];

    protected function detailWith(): array
    {
        return ['customer', 'assignee', 'statusLogs'];
    }

    protected function defaults(Request $r): array
    {
        return ['order_number' => 'ORD-'.now()->format('ym').'-'.str_pad((string) (CustomOrder::withTrashed()->count() + 1), 5, '0', STR_PAD_LEFT), 'status' => $r->input('status', 'Processing')];
    }

    public function status(Request $r, CustomOrder $customOrder)
    {
        $d = $r->validate(['status' => 'required|in:Processing,Cancelled,Order Ready', 'note' => 'nullable']);
        $old = $customOrder->status;
        $customOrder->update(['status' => $d['status']]);
        $customOrder->statusLogs()->create(['from_status' => $old, 'to_status' => $d['status'], 'changed_by' => auth()->id(), 'note' => $d['note'] ?? null]);

        return $customOrder->load('statusLogs');
    }
}
