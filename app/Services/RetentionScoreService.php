<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerRetentionScore;

class RetentionScoreService
{
    public function calculateScore(Customer $customer): int
    {
        $customer->loadMissing(['sales', 'giftCards', 'importantDates']);
        $score = 20;
        $last = $customer->sales->first();
        $days = $last ? now()->diffInDays($last->sale_date) : 999;
        if ($days >= 180) {
            $score += 20;
        }if ($days >= 365) {
            $score += 10;
        }if ($customer->lifetime_value >= 200000) {
            $score += 15;
        }if (in_array($customer->category, ['VIP', 'HNI'])) {
            $score += 15;
        }if ($customer->loyalty_balance > 0) {
            $score += 10;
        }if ($customer->giftCards->where('status', 'Active')->sum('balance') > 0) {
            $score += 10;
        }if ($this->hasUpcomingOccasion($customer)) {
            $score += 15;
        }

return min(100, $score);
    }

    public function getScoreReasons(Customer $customer): array
    {
        $reasons = [];
        $last = $customer->sales()->latest('sale_date')->first();
        if (! $last || now()->diffInDays($last->sale_date) >= 180) {
            $reasons[] = 'Inactive for 6+ months';
        }if ($customer->loyalty_balance > 0) {
            $reasons[] = "{$customer->loyalty_balance} loyalty points unused";
        }if ($customer->giftCards()->where('status', 'Active')->sum('balance') > 0) {
            $reasons[] = 'Active gift card balance';
        }if (in_array($customer->category, ['VIP', 'HNI'])) {
            $reasons[] = "{$customer->category} relationship";
        }if ($this->hasUpcomingOccasion($customer)) {
            $reasons[] = 'Important occasion approaching';
        }

return $reasons ?: ['Maintain regular relationship'];
    }

    public function getSuggestedAction(Customer $customer): string
    {
        $score = $this->calculateScore($customer);

        return $score >= 80 ? 'Call today with a personalized appointment invitation' : ($score >= 60 ? 'Contact within 3 days' : 'Nurture with useful jewellery care content');
    }

    public function getSuggestedOffer(Customer $customer): string
    {
        return app(PersonalizedMessageService::class)->suggestOffer($customer);
    }

    public function getSuggestedProduct(Customer $customer): string
    {
        return app(PersonalizedMessageService::class)->suggestProduct($customer);
    }

    public function calculate(Customer $customer): CustomerRetentionScore
    {
        return CustomerRetentionScore::updateOrCreate(['customer_id' => $customer->id], ['score' => $this->calculateScore($customer), 'score_reason' => $this->getScoreReasons($customer), 'suggested_action' => $this->getSuggestedAction($customer), 'suggested_offer' => $this->getSuggestedOffer($customer), 'suggested_product' => $this->getSuggestedProduct($customer), 'calculated_at' => now()]);
    }

    private function hasUpcomingOccasion(Customer $c): bool
    {
        foreach (array_filter([$c->birthday, $c->anniversary, ...$c->importantDates->pluck('date_value')->all()]) as $date) {
            $next = now()->setDate(now()->year, date('m', strtotime($date)), date('d', strtotime($date)));
            if ($next->isPast()) {
                $next->addYear();
            }if (now()->diffInDays($next) <= 30) {
                return true;
            }
        }

return false;
    }
}
