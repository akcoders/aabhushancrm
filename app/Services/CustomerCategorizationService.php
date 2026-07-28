<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerCategoryRule;

class CustomerCategorizationService
{
    public function categorize(Customer $customer): Customer
    {
        if ($customer->category_override) return $customer;
        $value = (float) $customer->lifetime_value;
        $rule = CustomerCategoryRule::where('is_active', true)
            ->where('minimum_purchase', '<=', $value)
            ->where(fn ($q) => $q->whereNull('maximum_purchase')->orWhere('maximum_purchase', '>=', $value))
            ->orderByDesc('priority')->first();
        if ($rule && $customer->category !== $rule->category) $customer->updateQuietly(['category' => $rule->category]);
        return $customer->refresh();
    }
}
