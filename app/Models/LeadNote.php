<?php

namespace App\Models;

use App\Models\Concerns\CrmModel;
use Illuminate\Database\Eloquent\Model;

class LeadNote extends Model
{
    use CrmModel;

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
