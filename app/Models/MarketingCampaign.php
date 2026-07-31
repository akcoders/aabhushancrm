<?php

namespace App\Models;

use App\Models\Concerns\CrmModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MarketingCampaign extends Model
{
    use CrmModel, SoftDeletes;

    protected function casts(): array
    {
        return ['channels' => 'array', 'audience_rules' => 'array', 'scheduled_at' => 'datetime', 'provider_data' => 'array'];
    }

    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }

    public function exhibition()
    {
        return $this->belongsTo(Exhibition::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function recipients()
    {
        return $this->hasMany(CampaignRecipient::class);
    }
}
