<?php

namespace App\Models;

use App\Models\Concerns\CrmModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PrivilegeCard extends Model
{
    use CrmModel, SoftDeletes;

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'issued_at' => 'date',
            'expires_at' => 'date',
        ];
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function issuer()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }
}
