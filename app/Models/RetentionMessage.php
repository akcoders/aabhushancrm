<?php

namespace App\Models;

use App\Models\Concerns\CrmModel;
use Illuminate\Database\Eloquent\Model;

class RetentionMessage extends Model
{
    use CrmModel;

    protected function casts(): array
    {
        return ['occasion_date' => 'date', 'generated_at' => 'datetime', 'contacted_at' => 'datetime'];
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function task()
    {
        return $this->belongsTo(Task::class, 'smart_task_id');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
