<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FestivalCampaign;
use Illuminate\Http\Request;

class FestivalCampaignController extends Controller
{
    public function index(Request $r)
    {
        return FestivalCampaign::with('template')->when($r->status, fn ($q, $v) => $q->where('status', $v))->latest()->paginate(20);
    }

    public function store(Request $r)
    {
        return response()->json(FestivalCampaign::create($this->data($r)), 201);
    }

    public function update(Request $r, FestivalCampaign $festivalCampaign)
    {
        $festivalCampaign->update($this->data($r, true));

        return $festivalCampaign->load('template');
    }

    public function destroy(FestivalCampaign $festivalCampaign)
    {
        $festivalCampaign->delete();

        return ['message' => 'Festival campaign deleted'];
    }

    private function data(Request $r, bool $partial = false)
    {
        return $r->validate(['title' => ($partial ? 'sometimes|' : '').'required', 'festival_name' => ($partial ? 'sometimes|' : '').'required', 'start_date' => ($partial ? 'sometimes|' : '').'required|date', 'end_date' => ($partial ? 'sometimes|' : '').'required|date', 'customer_type' => 'nullable', 'product_category' => 'nullable', 'offer_details' => 'nullable', 'message_template_id' => 'nullable|exists:message_templates,id', 'status' => 'nullable|in:Draft,Active,Completed,Cancelled']);
    }
}
