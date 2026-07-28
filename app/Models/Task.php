<?php

namespace App\Models;

use App\Models\Concerns\CrmModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use CrmModel, SoftDeletes;

    protected function casts(): array
    {
        return ['due_at' => 'datetime', 'completed_at' => 'datetime', 'auto_generated' => 'boolean'];
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function related()
    {
        return $this->morphTo();
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function customOrder()
    {
        return $this->belongsTo(CustomOrder::class);
    }

    public function exhibition()
    {
        return $this->belongsTo(Exhibition::class);
    }

    public function outcomes()
    {
        return $this->hasMany(TaskOutcome::class, 'smart_task_id')->latest();
    }
}
