<?php

namespace App\Models;

use App\Models\Concerns\CrmModel;
use Illuminate\Database\Eloquent\Model;

class TaskOutcome extends Model
{
    use CrmModel;

    protected function casts(): array
    {
        return ['next_action_date' => 'datetime', 'next_action_created' => 'boolean'];
    }

    public function task()
    {
        return $this->belongsTo(Task::class, 'smart_task_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
