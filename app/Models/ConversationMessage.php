<?php

namespace App\Models;

use App\Models\Concerns\CrmModel;
use Illuminate\Database\Eloquent\Model;

class ConversationMessage extends Model
{
    use CrmModel;
    protected function casts(): array { return ['sent_at' => 'datetime', 'delivered_at' => 'datetime', 'read_at' => 'datetime', 'provider_payload' => 'array']; }
    public function conversation() { return $this->belongsTo(Conversation::class); }
    public function sender() { return $this->belongsTo(User::class, 'sent_by'); }
}
