<?php

namespace App\Models;

use App\Models\Concerns\CrmModel;
use Illuminate\Database\Eloquent\Model;

class CommunicationLog extends Model
{
    use CrmModel;

    protected function casts(): array
    {
        return ['communicated_at' => 'datetime'];
    }

    public function communicable()
    {
        return $this->morphTo();
    }
}
