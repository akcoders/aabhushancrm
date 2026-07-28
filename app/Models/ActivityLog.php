<?php

namespace App\Models;

use App\Models\Concerns\CrmModel;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use CrmModel;

    public function subject()
    {
        return $this->morphTo();
    }
}
