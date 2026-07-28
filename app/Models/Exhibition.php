<?php

namespace App\Models;

use App\Models\Concerns\CrmModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Exhibition extends Model
{
    use CrmModel, SoftDeletes;

    public function leads()
    {
        return $this->belongsToMany(Lead::class, 'exhibition_leads')
            ->withPivot(['visitor_type', 'visit_count', 'first_seen_at', 'last_seen_at', 'interest_snapshot', 'stated_budget', 'visit_notes', 'captured_by'])
            ->withTimestamps();
    }

    public function campaigns()
    {
        return $this->hasMany(MarketingCampaign::class);
    }
}
