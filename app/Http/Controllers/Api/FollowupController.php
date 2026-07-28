<?php

namespace App\Http\Controllers\Api;

use App\Models\LeadFollowup;

class FollowupController extends CrudController
{
    protected string $model = LeadFollowup::class;

    protected array $searchable = ['type', 'notes', 'outcome'];

    protected array $filterable = ['status', 'type', 'assigned_to', 'lead_id', 'customer_id'];

    protected array $with = ['lead', 'customer', 'assignee'];
}
