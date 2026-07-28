<?php

namespace App\Models;

use App\Models\Concerns\CrmModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeadFollowup extends Model
{
    use CrmModel, SoftDeletes;

    protected function casts(): array
    {
        return ['scheduled_at' => 'datetime', 'next_followup_at' => 'datetime', 'reminder_sent' => 'boolean'];
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
