<?php

namespace App\Models;

use App\Models\Concerns\CrmModel;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use CrmModel;
    protected function casts(): array { return ['last_message_at' => 'datetime', 'metadata' => 'array']; }
    public function messages() { return $this->hasMany(ConversationMessage::class); }
    public function customer() { return $this->belongsTo(Customer::class); }
    public function lead() { return $this->belongsTo(Lead::class); }
    public function assignee() { return $this->belongsTo(User::class, 'assigned_to'); }
}
