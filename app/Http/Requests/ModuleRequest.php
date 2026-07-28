<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ModuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = match ($this->route()?->getName()) {
            'leads.store','leads.update' => ['name' => 'required|string|max:120', 'mobile' => 'required|string|max:30', 'email' => 'nullable|email', 'status' => 'nullable|in:New,Contacted,Interested,Follow-up,Converted,Lost', 'priority' => 'nullable|in:Hot,Warm,Cold', 'assigned_to' => 'required|exists:users,id'],
            'followups.store','followups.update' => ['type' => 'required|in:Call,WhatsApp,Visit,Meeting,Email', 'scheduled_at' => 'required|date', 'status' => 'nullable|in:Pending,Completed,Cancelled,Overdue'],
            'customers.store','customers.update' => ['name' => 'required|string|max:120', 'mobile' => 'required|string|max:30', 'email' => 'nullable|email', 'category' => 'nullable|in:Normal,Premium,VIP,HNI', 'category_override' => 'nullable|boolean', 'birthday' => 'nullable|date', 'anniversary' => 'nullable|date', 'city' => 'nullable|string|max:100', 'address' => 'nullable|string', 'notes' => 'nullable|string'],
            'sales.store' => ['customer_id' => 'required|exists:customers,id', 'sale_date' => 'required|date', 'items' => 'required|array|min:1', 'items.*.product_category' => 'required|string|max:120', 'items.*.jewellery_type' => 'required', 'items.*.metal_type' => 'required', 'items.*.total' => 'required|numeric|min:0'],
            'custom-orders.store','custom-orders.update' => ['customer_id' => 'required|exists:customers,id', 'jewellery_type' => 'required', 'metal_type' => 'required', 'estimated_amount' => 'required|numeric|min:0', 'due_date' => 'required|date', 'status' => 'nullable|in:Processing,Cancelled,Order Ready', 'purity' => 'nullable', 'approx_weight' => 'nullable|numeric', 'advance_payment' => 'nullable|numeric|min:0', 'vendor_name' => 'nullable', 'approval_status' => 'nullable', 'internal_notes' => 'nullable'],
            'exhibitions.store','exhibitions.update' => ['name' => 'required', 'location' => 'required', 'start_date' => 'required|date', 'end_date' => 'required|date|after_or_equal:start_date'],
            'offers.store','offers.update' => ['title' => 'required', 'type' => 'required', 'start_date' => 'required|date', 'end_date' => 'required|date|after_or_equal:start_date', 'value' => 'nullable|numeric|min:0', 'customer_type' => 'nullable', 'product_category' => 'nullable', 'usage_limit' => 'nullable|integer|min:1', 'status' => 'nullable|in:Active,Inactive', 'description' => 'nullable|string'],
            'gift-cards.store' => ['original_amount' => 'required|numeric|min:1', 'expiry_date' => 'required|date|after:today'],
            'tasks.store','tasks.update' => ['title' => 'required', 'description' => 'nullable|string', 'assigned_to' => 'nullable|exists:users,id', 'due_at' => 'required|date', 'reward_points' => 'nullable|integer|min:0|max:10000', 'priority' => 'nullable|in:Low,Medium,High,Urgent', 'status' => 'nullable|in:Pending,In Progress,Completed,Cancelled', 'notes' => 'nullable|string'],
            default => [],
        };

        return $rules;
    }
}
