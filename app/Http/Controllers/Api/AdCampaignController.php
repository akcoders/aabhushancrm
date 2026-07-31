<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdCampaign;
use App\Services\MetaMarketingService;
use Illuminate\Http\Request;

class AdCampaignController extends Controller
{
    public function index(Request $r) { return AdCampaign::with('creator:id,name')->latest()->paginate(30); }
    public function store(Request $r)
    {
        $d = $r->validate(['name' => 'required|string|max:255', 'platform' => 'required|in:Instagram,Facebook,Both', 'objective' => 'required|string|max:80', 'caption' => 'nullable|string', 'media' => 'nullable|image|max:10240', 'destination_url' => 'nullable|url', 'daily_budget' => 'required|numeric|min:1', 'audience' => 'nullable|array', 'scheduled_at' => 'required|date', 'ends_at' => 'nullable|date|after:scheduled_at']);
        if ($r->file('media')) $d['media_url'] = url('/storage/'.$r->file('media')->store('ad-creatives', 'public'));
        unset($d['media']);
        return AdCampaign::create($d + ['created_by' => $r->user()->id]);
    }
    public function update(Request $r, AdCampaign $adCampaign)
    {
        abort_unless($adCampaign->status === 'Draft', 422, 'Only draft campaigns can be edited.');
        $adCampaign->update($r->validate(['name' => 'sometimes|string|max:255', 'caption' => 'nullable|string', 'daily_budget' => 'sometimes|numeric|min:1', 'audience' => 'nullable|array', 'scheduled_at' => 'sometimes|date', 'ends_at' => 'nullable|date', 'status' => 'sometimes|in:Draft,Cancelled']));
        return $adCampaign;
    }
    public function publish(AdCampaign $adCampaign, MetaMarketingService $meta)
    {
        try {
            $result = $meta->createAdCampaign($adCampaign);
            $adCampaign->update(['status' => 'Scheduled', 'external_campaign_id' => $result['id'] ?? null, 'failure_reason' => null]);
        } catch (\Throwable $e) {
            $adCampaign->update(['status' => 'Failed', 'failure_reason' => $e->getMessage()]);
            throw $e;
        }
        return $adCampaign;
    }
    public function destroy(AdCampaign $adCampaign) { abort_unless(in_array($adCampaign->status, ['Draft', 'Cancelled', 'Failed']), 422); $adCampaign->delete(); return response()->noContent(); }
}
