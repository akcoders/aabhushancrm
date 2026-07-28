<?php

namespace App\Models;

use App\Models\Concerns\CrmModel;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use CrmModel;

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }
}
