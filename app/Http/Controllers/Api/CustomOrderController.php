<?php

namespace App\Http\Controllers\Api;

use App\Models\CustomOrder;
use App\Http\Requests\ModuleRequest;
use App\Services\ActivityService;
use Illuminate\Http\Request;

class CustomOrderController extends CrudController
{
    protected string $model = CustomOrder::class;

    protected array $searchable = ['order_number', 'jewellery_type', 'vendor_name'];

    protected array $filterable = ['status', 'approval_status', 'customer_id', 'assigned_to'];

    protected array $with = ['customer', 'assignee'];

    protected ?string $ownershipColumn = 'assigned_to';

    protected function detailWith(): array
    {
        return ['customer', 'assignee', 'statusLogs'];
    }

    protected function defaults(Request $r): array
    {
        return ['order_number' => 'ORD-'.now()->format('ym').'-'.str_pad((string) (CustomOrder::withTrashed()->count() + 1), 5, '0', STR_PAD_LEFT), 'status' => $r->input('status', 'Processing')]
            + ($this->isSalesExecutive() ? ['assigned_to' => auth()->id()] : []);
    }

    public function store(ModuleRequest $request, ActivityService $log)
    {
        $this->authorizeCustomer($request->validated('customer_id'));
        return parent::store($request, $log);
    }

    public function update(ModuleRequest $request, int $id, ActivityService $log)
    {
        $this->authorizeCustomer($request->validated('customer_id'));
        return parent::update($request, $id, $log);
    }

    public function status(Request $r, CustomOrder $customOrder)
    {
        $this->authorizeOwned($customOrder);
        $d = $r->validate(['status' => 'required|in:Processing,Cancelled,Order Ready', 'note' => 'nullable']);
        $old = $customOrder->status;
        $customOrder->update(['status' => $d['status']]);
        $customOrder->statusLogs()->create(['from_status' => $old, 'to_status' => $d['status'], 'changed_by' => auth()->id(), 'note' => $d['note'] ?? null]);

        return $customOrder->load('statusLogs');
    }

    private function authorizeCustomer(int $customerId): void
    {
        if ($this->isSalesExecutive()) {
            abort_unless(\App\Models\Customer::whereKey($customerId)->where('assigned_to', auth()->id())->exists(), 403, 'This customer is assigned to another salesperson.');
        }
    }
}
