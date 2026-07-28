<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CampaignRecipient;
use App\Models\MarketingCampaign;
use App\Models\Sale;
use App\Services\MarketingCampaignService;
use Illuminate\Http\Request;

class MarketingCampaignController extends Controller
{
    public function index(Request $r)
    {
        return MarketingCampaign::with('offer', 'exhibition', 'creator')->withCount('recipients')->when($r->search, fn ($q, $v) => $q->where('name', 'like', "%$v%"))->latest()->paginate(20);
    }

    public function store(Request $r, MarketingCampaignService $service)
    {
        $d = $r->validate(['name' => 'required', 'objective' => 'required', 'channels' => 'required|array|min:1', 'channels.*' => 'in:WhatsApp,Email', 'offer_id' => 'nullable|exists:offers,id', 'exhibition_id' => 'nullable|exists:exhibitions,id', 'audience_rules' => 'required|array', 'segment_name' => 'nullable', 'subject' => 'nullable', 'message' => 'required', 'scheduled_at' => 'nullable|date']);
        $campaign = MarketingCampaign::create($d + ['created_by' => auth()->id()]);
        $service->prepare($campaign);

        return response()->json($campaign->fresh(['offer', 'exhibition', 'recipients']), 201);
    }

    public function show(MarketingCampaign $marketingCampaign)
    {
        return $marketingCampaign->load(['offer', 'exhibition', 'creator', 'recipients.recipient'])->loadCount('recipients');
    }

    public function update(Request $r, MarketingCampaign $marketingCampaign, MarketingCampaignService $service)
    {
        abort_if($marketingCampaign->status === 'Sent', 422, 'Sent campaigns cannot be edited.');
        $d = $r->validate(['name' => 'sometimes|required', 'objective' => 'sometimes|required', 'channels' => 'sometimes|array|min:1', 'offer_id' => 'nullable|exists:offers,id', 'exhibition_id' => 'nullable|exists:exhibitions,id', 'audience_rules' => 'sometimes|array', 'segment_name' => 'nullable', 'subject' => 'nullable', 'message' => 'sometimes|required', 'scheduled_at' => 'nullable|date', 'status' => 'sometimes|in:Draft,Scheduled,Paused']);
        $marketingCampaign->update($d);
        $service->prepare($marketingCampaign);

        return $marketingCampaign->fresh(['offer', 'exhibition', 'recipients']);
    }

    public function destroy(MarketingCampaign $marketingCampaign)
    {
        abort_if($marketingCampaign->status === 'Sent', 422, 'Sent campaigns must be retained for reporting.');
        $marketingCampaign->delete();

        return ['message' => 'Campaign deleted'];
    }

    public function preview(Request $r, MarketingCampaignService $service)
    {
        $d = $r->validate(['audience_rules' => 'required|array', 'channels' => 'required|array|min:1']);

        return $service->preview($d['audience_rules'], $d['channels']);
    }

    public function launch(MarketingCampaign $marketingCampaign, MarketingCampaignService $service)
    {
        return ['message' => 'Campaign dispatched to consenting recipients.', 'campaign' => $service->launch($marketingCampaign)];
    }

    public function dashboard()
    {
        return ['summary' => ['total_campaigns' => MarketingCampaign::count(), 'sent' => MarketingCampaign::where('status', 'Sent')->count(), 'delivered' => MarketingCampaign::sum('delivered_count'), 'replies' => MarketingCampaign::sum('replied_count'), 'conversions' => MarketingCampaign::sum('converted_count'), 'revenue' => (float) MarketingCampaign::sum('attributed_revenue')], 'campaigns' => MarketingCampaign::with('offer', 'exhibition')->withCount('recipients')->latest()->limit(8)->get()];
    }

    public function engagement(Request $request, CampaignRecipient $campaignRecipient)
    {
        $data = $request->validate(['event' => 'required|in:opened,clicked,replied,converted', 'sale_id' => 'nullable|exists:sales,id']);
        $field = $data['event'].'_at';
        $updates = [$field => now(), 'status' => ucfirst($data['event'])];
        if ($data['event'] === 'converted' && ! empty($data['sale_id'])) {
            $sale = Sale::find($data['sale_id']);
            $updates += ['sale_id' => $sale->id, 'revenue' => $sale->final_amount];
        }
        $campaignRecipient->update($updates);
        $campaign = $campaignRecipient->campaign;
        $campaign->update([
            'opened_count' => $campaign->recipients()->whereNotNull('opened_at')->count(),
            'clicked_count' => $campaign->recipients()->whereNotNull('clicked_at')->count(),
            'replied_count' => $campaign->recipients()->whereNotNull('replied_at')->count(),
            'converted_count' => $campaign->recipients()->whereNotNull('converted_at')->count(),
            'attributed_revenue' => $campaign->recipients()->sum('revenue'),
        ]);

        return ['message' => 'Engagement recorded', 'recipient' => $campaignRecipient->fresh(), 'campaign' => $campaign->fresh()];
    }
}
