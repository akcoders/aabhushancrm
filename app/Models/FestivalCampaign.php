<?php

namespace App\Models;

use App\Models\Concerns\CrmModel;
use Illuminate\Database\Eloquent\Model;

class FestivalCampaign extends Model
{
    use CrmModel;

    protected function casts(): array
    {
        return ['start_date' => 'date', 'end_date' => 'date'];
    }

    public function template()
    {
        return $this->belongsTo(MessageTemplate::class, 'message_template_id');
    }
}
