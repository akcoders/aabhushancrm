<?php

namespace App\Console\Commands;

use App\Models\AppNotification;
use App\Models\Lead;
use App\Models\LeadFollowup;
use App\Models\Task;
use App\Models\User;
use Illuminate\Console\Command;

class GenerateDailyNotifications extends Command
{
    protected $signature = 'crm:daily-notifications';
    protected $description = 'Generate daily in-app summaries for staff and management';

    public function handle(): int
    {
        User::where('is_active', true)->each(function (User $user) {
            $tasks = Task::where('assigned_to', $user->id)->whereIn('status', ['Pending', 'pending', 'In Progress', 'in_progress'])->whereDate('due_at', '<=', today())->count();
            $followups = LeadFollowup::where('assigned_to', $user->id)->where('status', 'Pending')->whereDate('scheduled_at', '<=', today())->count();
            $key = 'daily-'.today()->toDateString();
            AppNotification::firstOrCreate(
                ['user_id' => $user->id, 'type' => $key],
                ['title' => 'Your daily CRM summary', 'message' => "{$tasks} tasks and {$followups} follow-ups require attention today.", 'data' => ['tasks' => $tasks, 'followups' => $followups]]
            );
            if (in_array($user->role?->slug, ['super-admin', 'management', 'sales-manager'])) {
                AppNotification::firstOrCreate(
                    ['user_id' => $user->id, 'type' => 'management-'.$key],
                    ['title' => 'Management daily alert', 'message' => Lead::where('status', 'New')->count().' new leads; '.LeadFollowup::where('status', 'Pending')->where('scheduled_at', '<', now())->count().' overdue follow-ups.']
                );
            }
        });
        $this->info('Daily notifications generated.');
        return self::SUCCESS;
    }
}
