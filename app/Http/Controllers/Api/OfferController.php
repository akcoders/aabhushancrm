<?php

namespace App\Http\Controllers\Api;

use App\Models\Offer;

class OfferController extends CrudController
{
    protected string $model = Offer::class;

    protected array $searchable = ['title'];

    protected array $filterable = ['status', 'type', 'customer_type'];

    protected array $with = ['usages'];
}
