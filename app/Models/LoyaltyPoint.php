<?php

namespace App\Models;

use App\Models\Concerns\CrmModel;
use Illuminate\Database\Eloquent\Model;

class LoyaltyPoint extends Model
{
    use CrmModel;

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
