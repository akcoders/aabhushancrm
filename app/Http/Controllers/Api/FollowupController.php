<?php

namespace App\Http\Controllers\Api;

use App\Models\LeadFollowup;
use Illuminate\Http\Request;

class FollowupController extends CrudController
{
    protected string $model = LeadFollowup::class;

    protected array $searchable = ['type', 'notes', 'outcome'];

    protected array $filterable = ['status', 'type', 'assigned_to', 'lead_id', 'customer_id'];

    protected array $with = ['lead', 'customer', 'assignee'];

    protected ?string $ownershipColumn = 'assigned_to';

    protected function defaults(Request $request): array
    {
        return $this->isSalesExecutive() ? ['assigned_to' => auth()->id()] : [];
    }
}
