<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CommunicationLog;
use App\Models\LeadFollowup;
use App\Models\Task;
use App\Models\TaskOutcome;
use App\Services\SmartTaskEngineService;
use App\Services\StaffRewardService;
use Illuminate\Http\Request;

class SmartTaskController extends Controller
{
    private array $with = ['assignee', 'customer.retentionScore', 'customer.sales.items', 'customer.giftCards', 'lead.assignee', 'sale.items', 'customOrder', 'exhibition', 'outcomes.creator'];

    public function index(Request $r)
    {
        $q = Task::with($this->with)->whereNotNull('task_type')->when($r->status, fn ($q, $v) => $q->where('status', $v))->when($r->priority, fn ($q, $v) => $q->where('priority', $v))->when($r->type, fn ($q, $v) => $q->where('task_type', $v))->when($r->assigned_to, fn ($q, $v) => $q->where('assigned_to', $v))->when($r->search, fn ($q, $v) => $q->where(fn ($x) => $x->where('title', 'like', "%$v%")->orWhere('reason', 'like', "%$v%")));

        return $q->orderByRaw("CASE priority WHEN 'urgent' THEN 1 WHEN 'high' THEN 2 WHEN 'medium' THEN 3 ELSE 4 END")->orderBy('due_at')->paginate(min((int) $r->input('per_page', 30), 100));
    }

    public function today()
    {
        return $this->scoped(fn ($q) => $q->whereDate('due_at', today()));
    }

    public function overdue()
    {
        return $this->scoped(fn ($q) => $q->where('due_at', '<', now())->whereIn('status', ['pending', 'in_progress']));
    }

    public function upcoming()
    {
        return $this->scoped(fn ($q) => $q->whereBetween('due_at', [now(), now()->addDays(7)]));
    }

    public function highPriority()
    {
        return $this->scoped(fn ($q) => $q->whereIn('priority', ['urgent', 'high']));
    }

    public function myTasks(Request $r)
    {
        return $this->scoped(fn ($q) => $q->where('assigned_to', $r->user()->id)->whereIn('status', ['pending', 'in_progress']));
    }

    public function show(Task $smartTask)
    {
        return $smartTask->load($this->with);
    }

    public function start(Task $smartTask)
    {
        $smartTask->update(['status' => 'in_progress']);

        return $smartTask->fresh($this->with);
    }

    public function complete(Request $r, Task $smartTask, SmartTaskEngineService $engine, StaffRewardService $rewards)
    {
        $d = $r->validate(['outcome' => 'required|in:Interested,Not Interested,Call Later,Purchased,Visit Scheduled,WhatsApp Sent,No Response,Wrong Number', 'notes' => 'nullable', 'next_action_date' => 'nullable|date']);
        $smartTask->update(['status' => 'completed', 'completed_at' => now(), 'outcome' => $d['outcome']]);
        $rewards->award($smartTask);
        $next = $this->nextAction($smartTask, $d, $engine);
        $outcome = TaskOutcome::create(['smart_task_id' => $smartTask->id, 'outcome' => $d['outcome'], 'notes' => $d['notes'] ?? null, 'next_action_type' => $next?->task_type, 'next_action_date' => $next?->due_at ?? ($d['next_action_date'] ?? null), 'next_action_created' => (bool) $next, 'created_by' => auth()->id()]);
        if ($d['outcome'] === 'No Response') {
            $smartTask->increment('no_response_count');
        }if ($d['outcome'] === 'Not Interested' && $smartTask->lead) {
            $smartTask->lead->update(['priority' => 'Cold']);
        }if ($d['outcome'] === 'Wrong Number') {
            if ($smartTask->customer) {
                $smartTask->customer->update(['contact_valid' => false]);
            }if ($smartTask->lead) {
                $smartTask->lead->update(['contact_valid' => false]);
            }
        }

return ['message' => 'Task completed and outcome recorded', 'task' => $smartTask->fresh($this->with), 'outcome' => $outcome, 'next_task' => $next];
    }

    public function skip(Request $r, Task $smartTask)
    {
        $d = $r->validate(['reason' => 'required|string']);
        $smartTask->update(['status' => 'skipped', 'skipped_reason' => $d['reason']]);

        return $smartTask;
    }

    public function reschedule(Request $r, Task $smartTask)
    {
        $d = $r->validate(['due_at' => 'required|date|after:now']);
        $smartTask->update(['due_at' => $d['due_at'], 'status' => 'pending']);

        return $smartTask;
    }

    public function createFollowup(Task $smartTask)
    {
        $leadId = $smartTask->lead_id ?? $smartTask->customer?->lead_id;
        abort_unless($leadId, 422, 'No linked lead for follow-up.');

        return response()->json(LeadFollowup::create(['lead_id' => $leadId, 'customer_id' => $smartTask->customer_id, 'assigned_to' => $smartTask->assigned_to, 'type' => 'Call', 'scheduled_at' => now()->addDays(2), 'status' => 'Pending', 'notes' => $smartTask->reason]), 201);
    }

    public function whatsapp(Task $smartTask)
    {
        $subject = $smartTask->customer ?? $smartTask->lead;
        abort_unless($subject, 422, 'Task has no contact.');
        CommunicationLog::create(['communicable_type' => get_class($subject), 'communicable_id' => $subject->id, 'type' => 'WhatsApp', 'direction' => 'Outbound', 'subject' => 'Smart task message', 'content' => $smartTask->whatsapp_message, 'status' => 'Opened', 'user_id' => auth()->id(), 'communicated_at' => now()]);
        if ($smartTask->status === 'pending') {
            $smartTask->update(['status' => 'in_progress']);
        }

return ['message' => 'WhatsApp click logged', 'url' => $smartTask->whatsapp_url];
    }

    public function generate(SmartTaskEngineService $engine)
    {
        return ['message' => 'Smart task generation completed', 'results' => $engine->scanAndCreateTasks()];
    }

    public function dashboard(Request $r)
    {
        $base = Task::whereNotNull('task_type');
        $personal = $r->user()->role?->slug === 'sales-executive';
        if ($personal) $base->where('assigned_to', $r->user()->id);

        return ['summary' => ['today' => (clone $base)->whereDate('due_at', today())->whereIn('status', ['pending', 'in_progress'])->count(), 'overdue' => (clone $base)->where('due_at', '<', now())->whereIn('status', ['pending', 'in_progress'])->count(), 'urgent' => (clone $base)->where('priority', 'urgent')->whereIn('status', ['pending', 'in_progress'])->count(), 'high' => (clone $base)->where('priority', 'high')->whereIn('status', ['pending', 'in_progress'])->count(), 'mine' => (clone $base)->where('assigned_to', $r->user()->id)->whereIn('status', ['pending', 'in_progress'])->count(), 'completed_today' => (clone $base)->whereDate('completed_at', today())->count()], 'groups' => Task::with($this->with)->whereNotNull('task_type')->when($personal, fn ($query) => $query->where('assigned_to', $r->user()->id))->whereIn('status', ['pending', 'in_progress'])->whereDate('due_at', '<=', today())->orderByRaw("CASE priority WHEN 'urgent' THEN 1 WHEN 'high' THEN 2 ELSE 3 END")->get()->groupBy('task_type')];
    }

    private function scoped($callback)
    {
        $q = Task::with($this->with)->whereNotNull('task_type');
        $callback($q);

        return $q->orderByRaw("CASE priority WHEN 'urgent' THEN 1 WHEN 'high' THEN 2 WHEN 'medium' THEN 3 ELSE 4 END")->orderBy('due_at')->paginate(30);
    }

    private function nextAction(Task $task, array $d, SmartTaskEngineService $engine): ?Task
    {
        $days = match ($d['outcome']) {
            'Interested','WhatsApp Sent' => 2,'No Response' => 1,'Not Interested' => 30,'Purchased' => 1,'Visit Scheduled' => null,'Call Later' => null,default => null
        };
        $due = $d['next_action_date'] ?? ($days !== null ? now()->addDays($days) : null);
        if (! $due) {
            return null;
        }$type = match ($d['outcome']) {
            'Purchased' => 'post_purchase_feedback_call','Visit Scheduled' => 'showroom_visit_reminder','No Response' => 'lead_followup_call',default => $task->task_type
        };

        return $engine->createTaskIfNotExists(['task_type' => $type, 'customer_id' => $task->customer_id, 'lead_id' => $task->lead_id, 'sale_id' => $task->sale_id, 'assigned_to' => $task->assigned_to, 'priority' => $d['outcome'] === 'Not Interested' ? 'low' : ($task->no_response_count >= 2 ? 'low' : $task->priority), 'reason' => "Next action after outcome: {$d['outcome']}", 'due_at' => $due, 'suggested_product' => $task->suggested_product, 'suggested_offer' => $task->suggested_offer]);
    }
}
