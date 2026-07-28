<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Factories\HasFactory;

trait CrmModel
{
    use HasFactory;

    public function initializeCrmModel(): void
    {
        $this->guard(['id']);
    }

    protected function casts(): array
    {
        return ['product_interests' => 'array', 'tags' => 'array', 'staff_ids' => 'array', 'diamond_details' => 'array', 'attachments' => 'array', 'properties' => 'array', 'value' => 'array'];
    }
}
