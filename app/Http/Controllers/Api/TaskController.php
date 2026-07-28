<?php

namespace App\Http\Controllers\Api;

use App\Models\Task;
use App\Services\ActivityService;
use App\Services\StaffRewardService;
use Illuminate\Http\Request;

class TaskController extends CrudController
{
    protected string $model = Task::class;

    protected array $searchable = ['title', 'description'];

    protected array $filterable = ['status', 'priority', 'assigned_to'];

    protected array $with = ['assignee'];

    protected ?string $ownershipColumn = 'assigned_to';

    protected function defaults(Request $r): array
    {
        return ['created_by' => auth()->id()];
    }

    public function update(\App\Http\Requests\ModuleRequest $request, int $id, ActivityService $log)
    {
        $response = parent::update($request, $id, $log);
        $task = Task::findOrFail($id);
        if (strtolower($task->status) === 'completed') {
            if (!$task->completed_at) $task->updateQuietly(['completed_at' => now()]);
            app(StaffRewardService::class)->award($task);
        }
        return $response;
    }
}
