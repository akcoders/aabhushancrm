<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VideoCallSale;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VideoCallSaleController extends Controller
{
    private function scope(Request $r)
    {
        return VideoCallSale::with(['customer:id,name,mobile', 'lead:id,name,mobile', 'staff:id,name', 'sale:id,invoice_number'])
            ->when($r->user()->role?->slug === 'sales-executive', fn ($q) => $q->where('staff_id', $r->user()->id));
    }
    public function index(Request $r) { return $this->scope($r)->latest('scheduled_at')->paginate(30); }
    public function store(Request $r)
    {
        $d = $r->validate(['title' => 'required|string|max:255', 'customer_id' => 'nullable|exists:customers,id', 'lead_id' => 'nullable|exists:leads,id', 'staff_id' => 'nullable|exists:users,id', 'scheduled_at' => 'required|date', 'notes' => 'nullable|string']);
        $staff = $r->user()->role?->slug === 'sales-executive' ? $r->user()->id : ($d['staff_id'] ?? $r->user()->id);
        $room = 'Kalasha-'.now()->format('Ymd').'-'.Str::upper(Str::random(14));
        return VideoCallSale::create($d + ['staff_id' => $staff, 'room_name' => $room, 'meeting_url' => 'https://'.config('integrations.jitsi.domain').'/'.$room, 'status' => 'Scheduled']);
    }
    public function show(Request $r, VideoCallSale $videoCallSale) { $this->owned($r, $videoCallSale); return $videoCallSale->load(['customer:id,name,mobile', 'lead:id,name,mobile', 'staff:id,name']); }
    public function update(Request $r, VideoCallSale $videoCallSale)
    {
        $this->owned($r, $videoCallSale);
        $d = $r->validate(['status' => 'sometimes|in:Scheduled,In Progress,Completed,Cancelled,No Show', 'outcome' => 'nullable|in:Interested,Follow-up Required,Sale Completed,Not Interested,No Show', 'notes' => 'nullable|string', 'sale_id' => 'nullable|exists:sales,id', 'scheduled_at' => 'sometimes|date']);
        if (($d['status'] ?? null) === 'In Progress' && !$videoCallSale->started_at) $d['started_at'] = now();
        if (in_array($d['status'] ?? null, ['Completed', 'Cancelled', 'No Show']) && !$videoCallSale->ended_at) $d['ended_at'] = now();
        $videoCallSale->update($d);
        return $videoCallSale;
    }
    public function destroy(Request $r, VideoCallSale $videoCallSale) { $this->owned($r, $videoCallSale); $videoCallSale->delete(); return response()->noContent(); }
    public function config() { return ['domain' => config('integrations.jitsi.domain')]; }
    private function owned(Request $r, VideoCallSale $call): void { abort_if($r->user()->role?->slug === 'sales-executive' && $call->staff_id !== $r->user()->id, 403); }
}
