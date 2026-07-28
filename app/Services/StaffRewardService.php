<?php

namespace App\Services;

use App\Models\Task;
use Illuminate\Support\Facades\DB;

class StaffRewardService
{
    public function award(Task $task): void
    {
        if (!$task->assigned_to || $task->rewarded_at || !$task->reward_points) return;
        DB::transaction(function () use ($task) {
            $locked = Task::lockForUpdate()->find($task->id);
            if ($locked->rewarded_at) return;
            $locked->assignee()->increment('reward_points', $locked->reward_points);
            $locked->update(['rewarded_at' => now()]);
        });
    }
}
