<?php

namespace App\Models;

use App\Models\Concerns\CrmModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdCampaign extends Model
{
    use CrmModel, SoftDeletes;
    protected function casts(): array { return ['audience' => 'array', 'scheduled_at' => 'datetime', 'ends_at' => 'datetime']; }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
}
