<?php

namespace App\Models;

use App\Models\Concerns\CrmModel;
use Illuminate\Database\Eloquent\Model;

class CustomerImportantDate extends Model
{
    use CrmModel;

    protected function casts(): array
    {
        return ['date_value' => 'date', 'is_active' => 'boolean'];
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
