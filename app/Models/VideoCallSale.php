<?php

namespace App\Models;

use App\Models\Concerns\CrmModel;
use Illuminate\Database\Eloquent\Model;

class VideoCallSale extends Model
{
    use CrmModel;
    protected function casts(): array { return ['scheduled_at' => 'datetime', 'started_at' => 'datetime', 'ended_at' => 'datetime']; }
    public function customer() { return $this->belongsTo(Customer::class); }
    public function lead() { return $this->belongsTo(Lead::class); }
    public function staff() { return $this->belongsTo(User::class); }
    public function sale() { return $this->belongsTo(Sale::class); }
}
