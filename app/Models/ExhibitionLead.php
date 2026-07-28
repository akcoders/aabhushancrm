<?php

namespace App\Models;

use App\Models\Concerns\CrmModel;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ExhibitionLead extends Pivot
{
    use CrmModel;

    protected $table = 'exhibition_leads';
}
