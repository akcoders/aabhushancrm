<?php

namespace App\Models;

use App\Models\Concerns\CrmModel;
use Illuminate\Database\Eloquent\Model;

class CustomerRetentionScore extends Model
{
    use CrmModel;

    protected function casts(): array
    {
        return ['score_reason' => 'array', 'calculated_at' => 'datetime'];
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
