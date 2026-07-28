<?php

namespace App\Models;

use App\Models\Concerns\CrmModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Offer extends Model
{
    use CrmModel, SoftDeletes;

    public function usages()
    {
        return $this->hasMany(OfferUsage::class);
    }
}
