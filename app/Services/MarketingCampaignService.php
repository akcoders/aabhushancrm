<?php

namespace App\Services;

use App\Models\CommunicationLog;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\MarketingCampaign;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MarketingCampaignService
{
    public function audience(array $rules, array $channels): Collection
    {
        $type = $rules['audience'] ?? 'customers';
        if (in_array($type, ['leads', 'event-visitors', 'returning-visitors'])) {
            $q = Lead::query()->with('customer');
            if ($type === 'event-visitors' && ! empty($rules['exhibition_id'])) {
                $q->whereHas('exhibitions', fn ($x) => $x->where('exhibitions.id', $rules['exhibition_id']));
            }if ($type === 'returning-visitors') {
                $q->has('exhibitions', '>=', 2);
            }if (! empty($rules['status'])) {
                $q->where('status', $rules['status']);
            }if (! empty($rules['interest'])) {
                $q->whereJsonContains('product_interests', $rules['interest']);
            }

return $this->consented($q, $channels)->limit(5000)->get();
        }$q = Customer::query()->with('lead');
        if ($type === 'vip') {
            $q->whereIn('category', ['VIP', 'HNI']);
        }if ($type === 'dormant') {
            $q->whereDoesntHave('sales', fn ($x) => $x->where('sale_date', '>=', now()->subDays((int) ($rules['days'] ?? 180))));
        }if ($type === 'birthday') {
            $q->whereMonth('birthday', now()->addMonth()->month);
        }if ($type === 'anniversary') {
            $q->whereMonth('anniversary', now()->addMonth()->month);
        }if (! empty($rules['category'])) {
            $q->where('category', $rules['category']);
        }if (! empty($rules['interest'])) {
            $q->whereJsonContains('product_interests', $rules['interest']);
        }if (! empty($rules['min_lifetime_value'])) {
            $q->where('lifetime_value', '>=', $rules['min_lifetime_value']);
        }

return $this->consented($q, $channels)->limit(5000)->get();
    }

    private function consented(Builder $q, array $channels): Builder
    {
        return $q->where(function ($x) use ($channels) {
            foreach ($channels as $i => $channel) {
                $method = $i ? 'orWhere' : 'where';
                if ($channel === 'WhatsApp') {
                    $x->{$method}(fn ($y) => $y->where('whatsapp_opt_in', true)->whereNotNull('mobile'));
                }if ($channel === 'Email') {
                    $x->{$method}(fn ($y) => $y->where('email_opt_in', true)->whereNotNull('email'));
                }
            }
        });
    }

    public function preview(array $rules, array $channels): array
    {
        $audience = $this->audience($rules, $channels);

        return ['count' => $audience->count(), 'recipients' => $audience->take(25)->map(fn ($x) => ['id' => $x->id, 'type' => class_basename($x), 'name' => $x->name, 'mobile' => $x->mobile, 'email' => $x->email, 'category' => $x->category ?? null, 'interests' => $x->product_interests])->values()];
    }

    public function prepare(MarketingCampaign $campaign): MarketingCampaign
    {
        return DB::transaction(function () use ($campaign) {
            $campaign->recipients()->delete();
            $people = $this->audience($campaign->audience_rules ?? [], $campaign->channels);
            foreach ($people as $person) {
                $channels = collect($campaign->channels)->filter(fn ($c) => ($c === 'WhatsApp' && $person->whatsapp_opt_in && $person->mobile) || ($c === 'Email' && $person->email_opt_in && $person->email))->values()->all();
                $campaign->recipients()->create(['recipient_type' => get_class($person), 'recipient_id' => $person->id, 'name' => $person->name, 'mobile' => $person->mobile, 'email' => $person->email, 'channels' => $channels]);
            }$campaign->update(['estimated_audience' => $people->count()]);

            return $campaign->fresh('recipients');
        });
    }

    public function launch(MarketingCampaign $campaign): MarketingCampaign
    {
        return DB::transaction(function () use ($campaign) {
            if (! $campaign->recipients()->exists()) {
                $this->prepare($campaign);
            }$now = now();
            foreach ($campaign->recipients()->with('recipient')->get() as $recipient) {
                $recipient->update(['status' => 'Delivered', 'sent_at' => $now, 'delivered_at' => $now]);
                foreach ($recipient->channels as $channel) {
                    CommunicationLog::create(['communicable_type' => $recipient->recipient_type, 'communicable_id' => $recipient->recipient_id, 'type' => $channel, 'direction' => 'Outbound', 'subject' => $campaign->subject, 'content' => $campaign->message, 'status' => 'Delivered', 'user_id' => auth()->id(), 'communicated_at' => $now]);
                }$recipient->recipient?->update(['last_engaged_at' => $now]);
            }$count = $campaign->recipients()->count();
            $campaign->update(['status' => 'Sent', 'sent_count' => $count, 'delivered_count' => $count]);

            return $campaign->fresh(['offer', 'exhibition', 'recipients']);
        });
    }
}
