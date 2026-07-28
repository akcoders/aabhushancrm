<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

class ActivityService
{
    public function log(string $action, ?Model $subject = null, array $properties = []): void
    {
        ActivityLog::create(['user_id' => auth()->id(), 'action' => $action, 'subject_type' => $subject?->getMorphClass(), 'subject_id' => $subject?->getKey(), 'properties' => $properties, 'ip_address' => request()->ip(), 'user_agent' => request()->userAgent()]);
    }
}
