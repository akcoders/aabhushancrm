<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MessageTemplate;
use Illuminate\Http\Request;

class MessageTemplateController extends Controller
{
    public function index(Request $r)
    {
        return MessageTemplate::when($r->type, fn ($q, $v) => $q->where('message_type', $v))->when($r->language, fn ($q, $v) => $q->where('language', $v))->latest()->paginate(30);
    }

    public function store(Request $r)
    {
        return response()->json(MessageTemplate::create($this->data($r)), 201);
    }

    public function update(Request $r, MessageTemplate $messageTemplate)
    {
        $messageTemplate->update($this->data($r, true));

        return $messageTemplate;
    }

    public function destroy(MessageTemplate $messageTemplate)
    {
        $messageTemplate->delete();

        return ['message' => 'Template deleted'];
    }

    private function data(Request $r, bool $partial = false)
    {
        return $r->validate(['title' => ($partial ? 'sometimes|' : '').'required', 'message_type' => ($partial ? 'sometimes|' : '').'required', 'language' => ($partial ? 'sometimes|' : '').'required|in:English,Tamil,Telugu', 'body' => ($partial ? 'sometimes|' : '').'required', 'variables' => 'nullable|array', 'is_active' => 'boolean']);
    }
}
