<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Services\InteraktService;
use App\Services\MetaMarketingService;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    private function scope(Request $r)
    {
        return Conversation::query()->with(['customer:id,name,mobile', 'lead:id,name,mobile', 'assignee:id,name'])
            ->when($r->user()->role?->slug === 'sales-executive', fn ($q) => $q->where('assigned_to', $r->user()->id));
    }

    public function index(Request $r)
    {
        return $this->scope($r)->with(['messages' => fn ($q) => $q->latest()->limit(1)])
            ->when($r->channel, fn ($q, $v) => $q->where('channel', $v))
            ->when($r->search, fn ($q, $v) => $q->where(fn ($x) => $x->where('contact_name', 'like', "%$v%")->orWhere('contact_handle', 'like', "%$v%")))
            ->latest('last_message_at')->paginate(30);
    }

    public function show(Request $r, Conversation $conversation)
    {
        $this->authorizeOwned($r, $conversation);
        $conversation->update(['unread_count' => 0]);
        return $conversation->load(['messages.sender:id,name', 'customer:id,name,mobile', 'lead:id,name,mobile', 'assignee:id,name']);
    }

    public function send(Request $r, Conversation $conversation, InteraktService $interakt, MetaMarketingService $meta)
    {
        $this->authorizeOwned($r, $conversation);
        $d = $r->validate(['body' => 'nullable|string|max:4000', 'template_name' => 'nullable|string|max:255', 'language' => 'nullable|string|max:12', 'media' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf,mp4|max:10240']);
        abort_if(blank($d['body'] ?? null) && blank($d['template_name'] ?? null), 422, 'Enter a message or approved template.');
        $mediaUrl = $r->file('media') ? url('/storage/'.$r->file('media')->store('marketing-media', 'public')) : null;
        $message = $conversation->messages()->create(['direction' => 'Outbound', 'message_type' => $mediaUrl ? 'media' : 'text', 'body' => $d['body'] ?? null, 'media_url' => $mediaUrl, 'template_name' => $d['template_name'] ?? null, 'status' => 'Queued', 'sent_by' => $r->user()->id]);
        try {
            if ($conversation->channel === 'WhatsApp') {
                abort_if(blank($d['template_name'] ?? null), 422, 'Interakt outbound messages require an approved WhatsApp template name.');
                $result = $interakt->sendTemplate($conversation->contact_handle, $d['template_name'], $d['language'] ?? 'en', filled($d['body'] ?? null) ? [$d['body']] : [], $mediaUrl, 'conversation:'.$conversation->id);
            } else {
                $result = $meta->sendInstagramMessage($conversation->external_contact_id, $d['body'] ?? '');
            }
            $message->update(['external_message_id' => $result['id'] ?? $result['message_id'] ?? null, 'status' => 'Sent', 'sent_at' => now(), 'provider_payload' => $result]);
            $conversation->update(['last_message_at' => now()]);
        } catch (\Throwable $e) {
            $message->update(['status' => 'Failed', 'failure_reason' => $e->getMessage()]);
            throw $e;
        }
        return $message->fresh('sender:id,name');
    }

    public function update(Request $r, Conversation $conversation)
    {
        $this->authorizeOwned($r, $conversation);
        $conversation->update($r->validate(['assigned_to' => 'nullable|exists:users,id', 'status' => 'sometimes|in:Open,Pending,Resolved']));
        return $conversation->fresh('assignee:id,name');
    }

    private function authorizeOwned(Request $r, Conversation $conversation): void
    {
        abort_if($r->user()->role?->slug === 'sales-executive' && $conversation->assigned_to !== $r->user()->id, 403);
    }
}
