<?php

namespace App\Models;

use App\Models\Concerns\CrmModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use CrmModel, SoftDeletes;

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function notes()
    {
        return $this->hasMany(LeadNote::class)->latest();
    }

    public function followups()
    {
        return $this->hasMany(LeadFollowup::class)->latest('scheduled_at');
    }

    public function history()
    {
        return $this->hasMany(LeadHistory::class)->latest();
    }

    public function customer()
    {
        return $this->hasOne(Customer::class);
    }

    public function exhibition()
    {
        return $this->belongsTo(Exhibition::class);
    }

    public function exhibitions()
    {
        return $this->belongsToMany(Exhibition::class, 'exhibition_leads')
            ->withPivot(['visitor_type', 'visit_count', 'first_seen_at', 'last_seen_at', 'interest_snapshot', 'stated_budget', 'visit_notes'])
            ->withTimestamps();
    }

    public function campaigns()
    {
        return $this->morphMany(CampaignRecipient::class, 'recipient');
    }
}
