<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerCategoryRule extends Model
{
    protected $guarded = ['id'];
    protected function casts(): array { return ['is_active' => 'boolean', 'minimum_purchase' => 'decimal:2', 'maximum_purchase' => 'decimal:2']; }
}
