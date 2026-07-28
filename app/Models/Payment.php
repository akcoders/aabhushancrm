<?php

namespace App\Models;

use App\Models\Concerns\CrmModel;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use CrmModel;

    protected function casts(): array
    {
        return ['paid_at' => 'datetime'];
    }
}
