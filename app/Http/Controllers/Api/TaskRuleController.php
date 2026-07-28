<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TaskRule;
use Illuminate\Http\Request;

class TaskRuleController extends Controller
{
    public function index(Request $r)
    {
        return TaskRule::when($r->module, fn ($q, $v) => $q->where('module', $v))->orderBy('module')->paginate(50);
    }

    public function store(Request $r)
    {
        return response()->json(TaskRule::create($this->data($r)), 201);
    }

    public function update(Request $r, TaskRule $taskRule)
    {
        $taskRule->update($this->data($r, true));

        return $taskRule;
    }

    public function destroy(TaskRule $taskRule)
    {
        $taskRule->delete();

        return ['message' => 'Rule deleted'];
    }

    public function toggle(TaskRule $taskRule)
    {
        $taskRule->update(['is_active' => ! $taskRule->is_active]);

        return $taskRule;
    }

    private function data(Request $r, bool $partial = false)
    {
        return $r->validate(['rule_name' => ($partial ? 'sometimes|' : '').'required', 'rule_key' => ($partial ? 'sometimes|' : '').'required|unique:task_rules,rule_key'.($partial ? ','.$r->route('taskRule')->id : ''), 'module' => ($partial ? 'sometimes|' : '').'required', 'condition_type' => ($partial ? 'sometimes|' : '').'required', 'condition_value' => 'nullable', 'task_type' => ($partial ? 'sometimes|' : '').'required', 'priority' => ($partial ? 'sometimes|' : '').'required|in:urgent,high,medium,low', 'due_after_hours' => 'integer|min:0', 'due_after_days' => 'integer|min:0', 'is_active' => 'boolean']);
    }
}
