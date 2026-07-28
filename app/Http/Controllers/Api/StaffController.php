<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Task;
use App\Models\LeadFollowup;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    public function index(Request $r)
    {
        return User::with('role', 'branch')
            ->when($r->boolean('sales_only'), fn ($q) => $q->where('is_active', true)->whereHas('role', fn ($role) => $role->whereIn('slug', ['sales-manager', 'sales-executive'])))
            ->when($r->search, fn ($q, $v) => $q->where(fn ($x) => $x->where('name', 'like', "%$v%")->orWhere('email', 'like', "%$v%")))
            ->orderBy('name')
            ->paginate(min((int) $r->input('per_page', 20), 100));
    }

    public function store(Request $r)
    {
        $d = $r->validate(['name' => 'required', 'email' => 'required|email|unique:users', 'password' => 'required|min:8', 'phone' => 'nullable', 'role_id' => 'required|exists:roles,id', 'branch_id' => 'nullable|exists:branches,id']);

        return response()->json(User::create($d)->load('role', 'branch'), 201);
    }

    public function show(User $staff)
    {
        return $staff->load('role.permissions', 'branch');
    }

    public function performance(User $staff)
    {
        $tasks = Task::with(['lead:id,name', 'customer:id,name'])
            ->where('assigned_to', $staff->id)
            ->latest('due_at')
            ->limit(100)
            ->get();
        $followups = LeadFollowup::with(['lead:id,name', 'customer:id,name'])
            ->where('assigned_to', $staff->id)
            ->latest('scheduled_at')
            ->limit(100)
            ->get();
        $completedTasks = $tasks->filter(fn ($task) => strtolower($task->status) === 'completed');
        $timelyTasks = $completedTasks->filter(fn ($task) => $task->completed_at && $task->completed_at->lte($task->due_at));
        $completedFollowups = $followups->filter(fn ($followup) => strtolower($followup->status) === 'completed');
        $timelyFollowups = $completedFollowups->filter(fn ($followup) => $followup->updated_at->lte($followup->scheduled_at));
        $totalCompleted = $completedTasks->count() + $completedFollowups->count();
        $totalTimely = $timelyTasks->count() + $timelyFollowups->count();

        return [
            'staff' => $staff->load('role', 'branch'),
            'summary' => [
                'assigned_actions' => $tasks->count() + $followups->count(),
                'pending_actions' => $tasks->filter(fn ($task) => in_array(strtolower($task->status), ['pending', 'in progress', 'in_progress']))->count()
                    + $followups->where('status', 'Pending')->count(),
                'overdue_actions' => $tasks->filter(fn ($task) => in_array(strtolower($task->status), ['pending', 'in progress', 'in_progress']) && $task->due_at->isPast())->count()
                    + $followups->filter(fn ($followup) => $followup->status === 'Pending' && $followup->scheduled_at->isPast())->count(),
                'completed_actions' => $totalCompleted,
                'timely_actions' => $totalTimely,
                'late_actions' => max(0, $totalCompleted - $totalTimely),
                'timely_rate' => $totalCompleted ? round($totalTimely / $totalCompleted * 100, 1) : 0,
                'reward_points_available' => (int) $staff->reward_points,
                'reward_points_earned' => (int) Task::where('assigned_to', $staff->id)->whereNotNull('rewarded_at')->sum('reward_points'),
                'assigned_leads' => $staff->assignedLeads()->count(),
                'converted_leads' => $staff->assignedLeads()->where('status', 'Converted')->count(),
                'sales_count' => $staff->sales()->count(),
                'sales_revenue' => (float) $staff->sales()->sum('final_amount'),
            ],
            'tasks' => $tasks,
            'followups' => $followups,
            'redemptions' => $staff->rewardRedemptions()->with('reward')->latest()->get(),
        ];
    }

    public function update(Request $r, User $staff)
    {
        $d = $r->validate(['name' => 'sometimes|required', 'email' => ['sometimes', 'email', Rule::unique('users')->ignore($staff)], 'password' => 'nullable|min:8', 'phone' => 'nullable', 'role_id' => 'sometimes|exists:roles,id', 'branch_id' => 'nullable|exists:branches,id', 'is_active' => 'boolean']);
        if (empty($d['password'])) {
            unset($d['password']);
        }$staff->update($d);

        return $staff->load('role', 'branch');
    }

    public function destroy(User $staff)
    {
        $staff->update(['is_active' => false]);
        $staff->tokens()->delete();

        return ['message' => 'Staff account deactivated'];
    }
}
