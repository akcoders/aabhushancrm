<?php

namespace App\Models;

use App\Models\Concerns\CrmModel;
use Illuminate\Database\Eloquent\Model;

class CleaningReminder extends Model
{
    use CrmModel;

    protected function casts(): array
    {
        return ['purchase_date' => 'date', 'reminder_date' => 'date'];
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }
}
