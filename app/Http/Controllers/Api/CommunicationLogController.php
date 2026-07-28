<?php

namespace App\Http\Controllers\Api;

use App\Models\CommunicationLog;
use Illuminate\Http\Request;

class CommunicationLogController extends CrudController
{
    protected string $model = CommunicationLog::class;

    protected array $searchable = ['subject', 'content'];

    protected array $filterable = ['type', 'direction', 'status', 'communicable_type', 'communicable_id'];

    protected function defaults(Request $r): array
    {
        return ['user_id' => auth()->id(), 'communicated_at' => $r->communicated_at ?? now()];
    }
}
