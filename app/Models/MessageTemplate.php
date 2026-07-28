<?php

namespace App\Models;

use App\Models\Concerns\CrmModel;
use Illuminate\Database\Eloquent\Model;

class MessageTemplate extends Model
{
    use CrmModel;

    protected function casts(): array
    {
        return ['variables' => 'array', 'is_active' => 'boolean'];
    }
}
