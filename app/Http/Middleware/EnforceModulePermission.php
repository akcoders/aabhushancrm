<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceModulePermission
{
    public function handle(Request $request, Closure $next): Response
    {
        $segment = $request->segment(2);
        $module = match ($segment) {
            'auth' => null,
            'smart-work', 'smart-tasks', 'task-rules', 'tasks' => 'tasks',
            'marketing-campaigns', 'campaign-recipients', 'communications', 'message-templates', 'festival-campaigns' => 'marketing',
            'conversations' => 'inbox',
            'ad-campaigns' => 'ads',
            'video-call-sales' => 'video-calls',
            'retention' => 'retention',
            'privilege-cards' => 'customers',
            'customer-important-dates' => 'customers',
            'customer-category-rules' => 'settings',
            'rewards', 'reward-redemptions' => 'rewards',
            'notifications' => 'notifications',
            default => $segment,
        };
        if (!$module) return $next($request);
        $action = match ($request->method()) {
            'GET', 'HEAD' => 'view',
            'POST' => collect(['complete', 'status', 'decide', '/read', 'read-all', 'reschedule', 'skip', 'start'])->contains(fn ($word) => str_contains($request->path(), $word)) ? 'update' : 'create',
            'PUT', 'PATCH' => 'update',
            'DELETE' => 'delete',
            default => 'view',
        };
        abort_unless($request->user()?->hasPermission("{$module}.{$action}"), 403, 'Your role cannot perform this action.');
        return $next($request);
    }
}
