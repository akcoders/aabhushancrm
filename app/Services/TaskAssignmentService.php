<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Lead;
use App\Models\User;

class TaskAssignmentService
{
    public function assignToLeadOwner(Lead $lead): ?int
    {
        return $lead->assigned_to ?? $this->assignToLeastBusyStaff();
    }

    public function assignToCustomerOwner(Customer $customer): ?int
    {
        return $customer->assigned_to ?? $this->assignToLeastBusyStaff();
    }

    public function assignToLeastBusyStaff(): ?int
    {
        return User::where('is_active', true)->withCount(['tasks as open_tasks_count' => fn ($q) => $q->whereIn('status', ['pending', 'in_progress', 'Pending', 'In Progress'])])->orderBy('open_tasks_count')->value('id');
    }

    public function assignToSalesExecutive(): ?int
    {
        return User::whereHas('role', fn ($q) => $q->whereIn('slug', ['sales-executive', 'sales-manager']))->where('is_active', true)->first()?->id ?? $this->assignToLeastBusyStaff();
    }

    public function assignToEventManager(): ?int
    {
        return User::whereHas('role', fn ($q) => $q->where('slug', 'event-manager'))->where('is_active', true)->value('id') ?? $this->assignToLeastBusyStaff();
    }

    public function reassignOverdueTasks(): int
    {
        return 0;
    }
}
