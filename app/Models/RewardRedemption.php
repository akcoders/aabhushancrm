<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RewardRedemption extends Model
{
    protected $guarded = ['id'];
    protected function casts(): array { return ['decided_at' => 'datetime']; }
    public function user() { return $this->belongsTo(User::class); }
    public function reward() { return $this->belongsTo(RewardCatalog::class, 'reward_catalog_id'); }
    public function approver() { return $this->belongsTo(User::class, 'approved_by'); }
}
