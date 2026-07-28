<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Lead;
use App\Models\LeadHistory;
use Illuminate\Support\Facades\DB;

class LeadService
{
    public function duplicates(string $mobile, ?string $email = null, ?int $except = null)
    {
        return Lead::query()->when($except, fn ($q) => $q->whereKeyNot($except))->where(fn ($q) => $q->where('mobile', $mobile)->when($email, fn ($x) => $x->orWhere('email', $email)))->get();
    }

    public function convert(Lead $lead): Customer
    {
        return DB::transaction(function () use ($lead) {
            $customer = Customer::firstOrCreate(['lead_id' => $lead->id], ['customer_code' => 'CUS-'.str_pad((string) ($lead->id), 6, '0', STR_PAD_LEFT), 'name' => $lead->name, 'mobile' => $lead->mobile, 'email' => $lead->email, 'address' => $lead->address, 'product_interests' => $lead->product_interests, 'notes' => $lead->notes, 'assigned_to' => $lead->assigned_to]);
            $old = $lead->status;
            $lead->update(['status' => 'Converted']);
            LeadHistory::create(['lead_id' => $lead->id, 'user_id' => auth()->id(), 'action' => 'converted', 'old_values' => ['status' => $old], 'new_values' => ['status' => 'Converted', 'customer_id' => $customer->id]]);

            return $customer;
        });
    }
}
