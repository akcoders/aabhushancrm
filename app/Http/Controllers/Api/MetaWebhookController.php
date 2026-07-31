<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\ConversationMessage;
use Illuminate\Http\Request;

class MetaWebhookController extends Controller
{
    public function verify(Request $r)
    {
        abort_unless($r->query('hub_verify_token') === config('integrations.meta.webhook_verify_token'), 403);
        return response($r->query('hub_challenge'));
    }

    public function receive(Request $r)
    {
        foreach ($r->input('entry', []) as $entry) foreach ($entry['messaging'] ?? [] as $event) {
            $message = $event['message'] ?? null;
            if (!$message || isset($message['is_echo'])) continue;
            $sender = data_get($event, 'sender.id');
            $conversation = Conversation::firstOrCreate(['channel' => 'Instagram', 'external_contact_id' => $sender], ['contact_handle' => $sender, 'contact_name' => 'Instagram customer']);
            if (!empty($message['mid']) && ConversationMessage::where('external_message_id', $message['mid'])->exists()) continue;
            $attachment = data_get($message, 'attachments.0');
            $conversation->messages()->create(['external_message_id' => $message['mid'] ?? null, 'direction' => 'Inbound', 'message_type' => $attachment ? ($attachment['type'] ?? 'media') : 'text', 'body' => $message['text'] ?? null, 'media_url' => data_get($attachment, 'payload.url'), 'status' => 'Received', 'sent_at' => now(), 'provider_payload' => $event]);
            $conversation->update(['unread_count' => $conversation->unread_count + 1, 'last_message_at' => now(), 'status' => 'Open']);
        }
        return response()->json(['received' => true]);
    }
}
