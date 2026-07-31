<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CampaignRecipient;
use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Models\Customer;
use App\Models\Lead;
use Illuminate\Http\Request;

class InteraktWebhookController extends Controller
{
    public function __invoke(Request $r)
    {
        $secret = config('integrations.interakt.webhook_secret');
        abort_if($secret && ! hash_equals($secret, (string) ($r->header('X-Interakt-Secret') ?: $r->query('secret'))), 401);
        $type = $r->input('type');
        $data = $r->input('data', []);
        $rawMessage = $data['message'] ?? [];
        $externalId = $rawMessage['id'] ?? null;
        if ($type === 'message_received') $this->received($data, $externalId);
        elseif ($externalId) $this->status($externalId, $type, $rawMessage);
        return response()->json(['received' => true]);
    }

    private function received(array $data, ?string $externalId): void
    {
        if ($externalId && ConversationMessage::where('external_message_id', $externalId)->exists()) return;
        $customerData = $data['customer'] ?? [];
        $message = $data['message'] ?? [];
        $mobile = $customerData['channel_phone_number'] ?? $customerData['phone_number'] ?? null;
        $digits = preg_replace('/\D+/', '', (string) $mobile);
        $customer = Customer::whereRaw("REPLACE(REPLACE(REPLACE(mobile, '+', ''), ' ', ''), '-', '') LIKE ?", ['%'.substr($digits, -10)])->first();
        $lead = $customer?->lead ?: Lead::whereRaw("REPLACE(REPLACE(REPLACE(mobile, '+', ''), ' ', ''), '-', '') LIKE ?", ['%'.substr($digits, -10)])->first();
        $conversation = Conversation::firstOrCreate(
            ['channel' => 'WhatsApp', 'contact_handle' => $mobile],
            ['external_contact_id' => $customerData['id'] ?? null, 'contact_name' => $customerData['name'] ?? data_get($customerData, 'traits.name'), 'customer_id' => $customer?->id, 'lead_id' => $lead?->id, 'assigned_to' => $customer?->assigned_to ?? $lead?->assigned_to]
        );
        $body = $message['message'] ?? $message['text'] ?? null;
        if (is_string($body) && str_starts_with(trim($body), '{')) $body = data_get(json_decode($body, true), 'text.body', $body);
        $conversation->messages()->create(['external_message_id' => $externalId, 'direction' => 'Inbound', 'message_type' => filled($message['media_url'] ?? null) ? 'media' : 'text', 'body' => is_string($body) ? $body : json_encode($body), 'media_url' => $message['media_url'] ?? null, 'status' => 'Received', 'sent_at' => now(), 'provider_payload' => $data]);
        $conversation->update(['unread_count' => $conversation->unread_count + 1, 'last_message_at' => now(), 'status' => 'Open']);
    }

    private function status(string $externalId, ?string $type, array $payload): void
    {
        $status = str_contains((string) $type, 'delivered') ? 'Delivered' : (str_contains((string) $type, 'read') ? 'Read' : (str_contains((string) $type, 'failed') ? 'Failed' : 'Sent'));
        $message = ConversationMessage::where('external_message_id', $externalId)->first();
        $message?->update(['status' => $status, 'delivered_at' => $status === 'Delivered' ? now() : $message?->delivered_at, 'read_at' => $status === 'Read' ? now() : null, 'failure_reason' => $payload['channel_failure_reason'] ?? null]);
        CampaignRecipient::where('external_message_id', $externalId)->update(['status' => $status, 'delivered_at' => in_array($status, ['Delivered', 'Read']) ? now() : null, 'failure_reason' => $payload['channel_failure_reason'] ?? null]);
    }
}
