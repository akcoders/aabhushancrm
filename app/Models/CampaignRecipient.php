<?php

namespace App\Models;

use App\Models\Concerns\CrmModel;
use Illuminate\Database\Eloquent\Model;

class CampaignRecipient extends Model
{
    use CrmModel;

    protected function casts(): array
    {
        return ['channels' => 'array', 'sent_at' => 'datetime', 'delivered_at' => 'datetime', 'opened_at' => 'datetime', 'clicked_at' => 'datetime', 'replied_at' => 'datetime', 'converted_at' => 'datetime'];
    }

    public function campaign()
    {
        return $this->belongsTo(MarketingCampaign::class, 'marketing_campaign_id');
    }

    public function recipient()
    {
        return $this->morphTo();
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }
}
