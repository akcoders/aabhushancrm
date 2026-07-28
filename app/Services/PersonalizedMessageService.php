<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\MessageTemplate;
use App\Models\Offer;

class PersonalizedMessageService
{
    public function generateMessage(Customer $customer, string $type, array $context = []): string
    {
        $data = $this->data($customer, $context);
        $template = $this->chooseBestTemplate($type, $customer->language_preference ?? 'English');
        if ($template) {
            return $this->replaceTemplateVariables($template->body, $data);
        }$days = $data['days_remaining'];
        $occasion = $data['occasion'];
        $product = $data['preferred_product'];
        $offer = $data['suggested_offer'];

        return match (strtolower($customer->language_preference ?? 'english')) {
            'hindi' => "नमस्ते {$customer->name} जी, {$occasion} {$days} दिनों में है। आपके पसंद के अनुसार हमने {$product} चुना है। {$offer}। क्या हम डिज़ाइन भेजें?",
            'tamil' => "வணக்கம் {$customer->name}, உங்கள் {$occasion} {$days} நாட்களில் வருகிறது. உங்கள் விருப்பத்திற்கு ஏற்ப நாம் {$product} தேர்ந்தெடுத்துள்ளோம். {$offer}. சில வடிவமைப்புகளை பகிர்ந்திடலாமா?",
            'telugu' => "హలో {$customer->name}, మీ {$occasion} {$days} రోజుల్లో వస్తోంది. మీ అభిరుచికి తగ్గట్టు మేము {$product} ఎంచుకున్నాము. {$offer}. కొన్ని డిజైన్లను పంపవలసిందా?",
            'english' => "Hello {$customer->name}, your {$occasion} is in {$days} days. Based on your preferences, we selected {$product} for you. {$offer}. May we share a few curated designs?",
            default => "Hello {$customer->name}, your {$occasion} is in {$days} days. Based on your preferences, we selected {$product} for you. {$offer}. May we share a few curated designs?"
        };
    }

    public function replaceTemplateVariables(string $template, array $data): string
    {
        foreach ($data as $key => $value) {
            $template = str_replace(['{'.$key.'}', '{{'.$key.'}}'], (string) ($value ?? ''), $template);
        }

return preg_replace('/\{\{?[^}]+\}?\}/', '', $template);
    }

    public function chooseBestTemplate(string $type, string $language): ?MessageTemplate
    {
        return MessageTemplate::where('message_type', $type)->where('language', $language)->where('is_active', true)->first() ?? MessageTemplate::where('message_type', $type)->where('is_active', true)->first();
    }

    public function suggestProduct(Customer $customer, array $context = []): string
    {
        $event = strtolower(($context['occasion'] ?? '').' '.($context['relation'] ?? ''));
        if (str_contains($event, 'wife') || str_contains($event, 'anniversary')) {
            return 'diamond pendant or couple ring';
        }if (str_contains($event, 'child') || str_contains($event, 'daughter')) {
            return 'delicate gold pendant or gift card';
        }$interest = $context['preferred_product'] ?? $customer->product_interests[0] ?? $customer->sales->first()?->items->first()?->jewellery_type;

        return $interest ?: 'curated gold and diamond collection';
    }

    public function suggestOffer(Customer $customer): string
    {
        $gift = (float) $customer->giftCards()->where('status', 'Active')->sum('balance');
        if ($gift > 0) {
            return 'Gift card balance ₹'.number_format($gift).' available';
        }if ($customer->loyalty_balance > 0) {
            return number_format($customer->loyalty_balance).' loyalty points available';
        }$offer = Offer::where('status', 'Active')->whereDate('end_date', '>=', today())->first();

        return $offer ? "{$offer->title} ({$offer->coupon_code})" : 'Complimentary private consultation and jewellery cleaning';
    }

    public function generateCallScript(Customer $customer, string $taskType, array $context = []): string
    {
        return $this->generateMessage($customer, $context['message_type'] ?? $this->typeFromTask($taskType), $context).' Pehle unki requirement suniye, phir appointment ya selected designs share karne ki permission lijiye.';
    }

    public function generateWhatsAppMessage(Customer $customer, string $taskType, array $context = []): string
    {
        return $this->generateMessage($customer, $context['message_type'] ?? $this->typeFromTask($taskType), $context);
    }

    public function generateWhatsAppUrl(string $mobile, string $message): string
    {
        $number = preg_replace('/\D+/', '', $mobile);
        if (strlen($number) === 10) {
            $number = '91'.$number;
        }

return 'https://wa.me/'.$number.'?text='.rawurlencode($message);
    }

    private function typeFromTask(string $task): string
    {
        return match (true) {
            str_contains($task, 'birthday') => 'birthday',str_contains($task, 'anniversary') => 'anniversary',str_contains($task, 'cleaning') => 'cleaning',str_contains($task, 'gift_card') => 'gift_card',str_contains($task, 'loyalty') => 'loyalty',str_contains($task, 'festival') => 'festival',str_contains($task, 'vip') => 'vip_invite',default => 'winback'
        };
    }

    private function data(Customer $c, array $context): array
    {
        $c->loadMissing(['sales.items', 'giftCards']);
        $last = $c->sales->first();
        $product = $this->suggestProduct($c, $context);
        $offer = $context['suggested_offer'] ?? $this->suggestOffer($c);

        return ['customer_name' => $c->name, 'occasion' => $context['occasion'] ?? 'special occasion', 'days_remaining' => $context['days_remaining'] ?? 0, 'last_product' => $last?->items->first()?->jewellery_type ?? '', 'preferred_product' => $product, 'preferred_metal' => $c->preferred_metal ?? 'gold or diamond', 'budget_range' => $c->preferred_budget_min ? '₹'.number_format($c->preferred_budget_min).' - ₹'.number_format($c->preferred_budget_max) : 'your preferred range', 'loyalty_points' => $c->loyalty_balance, 'gift_card_balance' => (float) $c->giftCards->where('status', 'Active')->sum('balance'), 'offer_amount' => $offer, 'suggested_offer' => $offer, 'showroom_name' => 'Abhushan Jewels', 'staff_name' => $c->assignee?->name ?? 'your jewellery consultant', 'family_member_name' => $context['family_member_name'] ?? '', 'relation' => $context['relation'] ?? '', 'last_purchase_date' => $last?->sale_date ?? ''];
    }
}
