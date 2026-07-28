<?php

namespace App\Models;

use App\Models\Concerns\CrmModel;
use Illuminate\Database\Eloquent\Model;

class TaskRule extends Model
{
    use CrmModel;

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
