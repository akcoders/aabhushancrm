<?php

namespace App\Models;

use App\Models\Concerns\CrmModel;
use Illuminate\Database\Eloquent\Model;

class LeadHistory extends Model
{
    use CrmModel;

    protected $table = 'lead_history';

    protected function casts(): array
    {
        return ['old_values' => 'array', 'new_values' => 'array'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
