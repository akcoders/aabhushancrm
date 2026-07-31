<?php

namespace App\Services;

use App\Models\AdCampaign;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class MetaMarketingService
{
    public function configured(): bool { return filled(config('integrations.meta.access_token')); }
    private function url(string $path): string { return 'https://graph.facebook.com/'.config('integrations.meta.graph_version').'/'.ltrim($path, '/'); }

    public function sendInstagramMessage(string $recipientId, string $text): array
    {
        abort_unless($this->configured() && config('integrations.meta.page_id'), 422, 'Meta Instagram messaging is not configured.');
        $r = Http::post($this->url(config('integrations.meta.page_id').'/messages'), [
            'recipient' => ['id' => $recipientId], 'message' => ['text' => $text],
            'messaging_type' => 'RESPONSE', 'access_token' => config('integrations.meta.access_token'),
        ]);
        if ($r->failed()) throw new RuntimeException($r->json('error.message') ?: $r->body());
        return $r->json();
    }

    public function createAdCampaign(AdCampaign $campaign): array
    {
        abort_unless($this->configured() && config('integrations.meta.ad_account_id'), 422, 'Meta Ads is not configured.');
        $r = Http::asForm()->post($this->url('act_'.preg_replace('/^act_/', '', config('integrations.meta.ad_account_id')).'/campaigns'), [
            'name' => $campaign->name, 'objective' => $campaign->objective, 'status' => 'PAUSED',
            'special_ad_categories' => '[]', 'access_token' => config('integrations.meta.access_token'),
        ]);
        if ($r->failed()) throw new RuntimeException($r->json('error.message') ?: $r->body());
        return $r->json();
    }
}
