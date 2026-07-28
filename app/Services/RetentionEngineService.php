<?php

namespace App\Services;

use App\Models\CleaningReminder;
use App\Models\Customer;
use App\Models\FestivalCampaign;
use App\Models\GiftCard;
use App\Models\RetentionMessage;
use App\Models\Sale;
use Carbon\Carbon;

class RetentionEngineService
{
    public function __construct(private PersonalizedMessageService $messages, private RetentionScoreService $scores, private SmartTaskEngineService $tasks) {}

    public function run(): array
    {
        $scores = 0;
        Customer::with(['sales.items', 'giftCards', 'importantDates'])->each(function ($c) use (&$scores) {
            $this->calculateCustomerRetentionScore($c);
            $scores++;
        });

        return ['scores' => $scores, 'important_dates' => $this->scanUpcomingImportantDates(), 'cleaning' => $this->detectCleaningReminders(), 'lost' => $this->detectLostCustomers(), 'loyalty' => $this->detectLoyaltyReminders(), 'gift_cards' => $this->detectGiftCardReminders(), 'festivals' => $this->detectFestivalCampaigns()];
    }

    public function scanUpcomingImportantDates(): int
    {
        $count = 0;
        Customer::with(['importantDates', 'familyMembers', 'sales.items', 'giftCards', 'assignee'])->each(function ($c) use (&$count) {
            $dates = collect([['title' => 'Birthday', 'type' => 'birthday', 'date' => $c->birthday], ['title' => 'Anniversary', 'type' => 'anniversary', 'date' => $c->anniversary]]);
            foreach ($c->importantDates as $d) {
                $dates->push(['title' => $d->title, 'type' => $d->date_type, 'date' => $d->date_value, 'family_member_name' => $d->relation_name, 'relation' => $d->relation_type]);
            }foreach ($c->familyMembers as $f) {
                $dates->push(['title' => "{$f->name}'s Birthday", 'type' => 'family_event', 'date' => $f->birthday, 'family_member_name' => $f->name, 'relation' => $f->relation]);
                if ($f->anniversary) {
                    $dates->push(['title' => "{$f->name}'s Anniversary", 'type' => 'family_event', 'date' => $f->anniversary, 'family_member_name' => $f->name, 'relation' => $f->relation]);
                }
            }foreach ($dates as $d) {
                if (! $d['date']) {
                    continue;
                }$days = $this->daysToAnnual($d['date']);
                if ($days > 30) {
                    continue;
                }$context = ['occasion' => $d['title'], 'days_remaining' => $days, 'family_member_name' => $d['family_member_name'] ?? '', 'relation' => $d['relation'] ?? ''];
                if ($this->createMessage($c, $d['type'], $d['title']." in {$days} days", Carbon::parse($d['date'])->setYear(now()->year), $days, $context)) {
                    $count++;
                }
            }
        });

        return $count;
    }

    public function generateRetentionMessages(): array
    {
        return ['dates' => $this->scanUpcomingImportantDates(), 'winback' => $this->detectLostCustomers(), 'cleaning' => $this->detectCleaningReminders(), 'loyalty' => $this->detectLoyaltyReminders(), 'gift_cards' => $this->detectGiftCardReminders(), 'festivals' => $this->detectFestivalCampaigns()];
    }

    public function calculateCustomerRetentionScore(Customer $customer)
    {
        return $this->scores->calculate($customer);
    }

    public function createAutoTasks(): array
    {
        return $this->tasks->scanAndCreateTasks();
    }

    public function generateWhatsAppUrl(string $mobile, string $message): string
    {
        return $this->messages->generateWhatsAppUrl($mobile, $message);
    }

    public function detectLostCustomers(): int
    {
        $count = 0;
        Customer::with(['sales.items', 'giftCards', 'assignee'])->each(function ($c) use (&$count) {
            $last = $c->sales->first();
            $days = $last ? now()->diffInDays($last->sale_date) : 999;
            if ($days < 180) {
                return;
            }$reason = $days >= 365 ? 'Inactive customer: no purchase in 12 months' : 'Winback opportunity: no purchase in 6 months';
            if ($c->loyalty_balance) {
                $reason .= "; {$c->loyalty_balance} points available";
            }if ($c->giftCards->where('status', 'Active')->sum('balance')) {
                $reason .= '; unused gift card balance';
            }if ($this->createMessage($c, 'winback', $reason, null, null, ['occasion' => 'a private welcome-back consultation'])) {
                $count++;
            }
        });

        return $count;
    }

    public function detectCleaningReminders(): int
    {
        $count = 0;
        Sale::with(['customer.sales.items', 'customer.giftCards', 'items'])->each(function ($sale) use (&$count) {
            foreach ([6, 12] as $months) {
                $date = Carbon::parse($sale->sale_date)->addMonths($months);
                $product = $sale->items->pluck('jewellery_type')->join(', ') ?: 'jewellery';
                $message = "Your {$product} is due for its complimentary {$months}-month cleaning and inspection.";
                $reminder = CleaningReminder::where('sale_id', $sale->id)->whereDate('reminder_date', $date->toDateString())->first()
                    ?? CleaningReminder::create(['sale_id' => $sale->id, 'reminder_date' => $date->toDateString(), 'customer_id' => $sale->customer_id, 'product_name' => $product, 'purchase_date' => $sale->sale_date, 'status' => 'pending', 'message' => $message]);
                if ($date->lte(now()->addDays(30)) && $reminder->status === 'pending') {
                    if ($this->createMessage($sale->customer, 'cleaning', $message, $date, max(0, now()->diffInDays($date, false)), ['occasion' => 'complimentary jewellery cleaning', 'preferred_product' => $product])) {
                        $count++;
                    }
                }
            }
        });

        return $count;
    }

    public function detectLoyaltyReminders(): int
    {
        $count = 0;
        Customer::with(['sales.items', 'giftCards'])->where('loyalty_balance', '>', 0)->each(function ($c) use (&$count) {
            $last = $c->sales->first();
            if (! $last || now()->diffInDays($last->sale_date) >= 90) {
                if ($this->createMessage($c, 'loyalty', "{$c->loyalty_balance} loyalty points are available", null, null, ['occasion' => 'loyalty benefit reminder'])) {
                    $count++;
                }
            }
        });

        return $count;
    }

    public function detectGiftCardReminders(): int
    {
        $count = 0;
        GiftCard::with('customer.sales.items', 'customer.giftCards')->where('balance', '>', 0)->where('status', 'Active')->each(function ($g) use (&$count) {
            if (! $g->customer) {
                return;
            }$days = (int) floor(now()->startOfDay()->diffInDays(Carbon::parse($g->expiry_date)->startOfDay(), false));
            if ($days <= 45 && $days >= 0) {
                if ($this->createMessage($g->customer, 'gift_card', 'Gift card balance ₹'.number_format($g->balance)." expires in {$days} days", $g->expiry_date, $days, ['occasion' => 'gift card expiry'])) {
                    $count++;
                }
            }
        });

        return $count;
    }

    public function detectFestivalCampaigns(): int
    {
        $count = 0;
        FestivalCampaign::where('status', 'Active')->whereDate('start_date', '<=', today()->addDays(30))->whereDate('end_date', '>=', today())->each(function ($f) use (&$count) {
            $q = Customer::with(['sales.items', 'giftCards']);
            if ($f->customer_type !== 'All') {
                $q->where('category', $f->customer_type);
            }if ($f->product_category) {
                $q->whereJsonContains('product_interests', $f->product_category);
            }$q->limit(500)->each(function ($c) use ($f, &$count) {
                $days = max(0, now()->diffInDays($f->start_date, false));
                if ($this->createMessage($c, 'festival', "{$f->festival_name} personalized campaign", $f->start_date, $days, ['occasion' => $f->festival_name, 'suggested_offer' => $f->offer_details])) {
                    $count++;
                }
            });
        });

        return $count;
    }

    private function createMessage(Customer $c, string $type, string $reason, $date = null, ?int $days = null, array $context = []): ?RetentionMessage
    {
        $occasion = $date ? Carbon::parse($date)->toDateString() : null;
        $exists = RetentionMessage::where('customer_id', $c->id)->where('message_type', $type)->where('reason', $reason)->when($occasion, fn ($q) => $q->whereDate('occasion_date', $occasion))->whereIn('status', ['pending', 'copied', 'whatsapp_opened'])->exists();
        if ($exists) {
            return null;
        }$product = $this->messages->suggestProduct($c, $context);
        $offer = $context['suggested_offer'] ?? $this->messages->suggestOffer($c);
        $context += ['days_remaining' => $days ?? 0, 'suggested_offer' => $offer, 'preferred_product' => $product];
        $message = $this->messages->generateMessage($c, $type, $context);
        $record = RetentionMessage::create(['customer_id' => $c->id, 'message_type' => $type, 'reason' => $reason, 'occasion_date' => $occasion, 'days_remaining' => $days, 'suggested_product' => $product, 'suggested_offer' => $offer, 'generated_message' => $message, 'whatsapp_url' => $this->generateWhatsAppUrl($c->mobile, $message), 'status' => 'pending', 'assigned_to' => $c->assigned_to, 'generated_at' => now()]);
        $task = $this->tasks->createTaskIfNotExists(['task_type' => $this->taskType($type), 'customer_id' => $c->id, 'assigned_to' => $c->assigned_to, 'priority' => $this->priority($type, $days), 'reason' => $reason, 'due_at' => $days !== null && $days > 7 ? now()->addDays(max(0, $days - 7)) : now(), 'suggested_product' => $product, 'suggested_offer' => $offer]);
        if ($task) {
            $record->update(['smart_task_id' => $task->id]);
        }

        return $record;
    }

    private function taskType(string $type): string
    {
        return match ($type) {
            'birthday' => 'birthday_call','anniversary' => 'anniversary_call','family_event' => 'family_event_call','cleaning' => 'cleaning_reminder_call','loyalty' => 'loyalty_reminder_call','gift_card' => 'gift_card_reminder_call','festival' => 'festival_offer_call',default => 'winback_call'
        };
    }

    private function priority(string $type, ?int $days): string
    {
        return in_array($type, ['gift_card']) && $days !== null && $days <= 15 ? 'urgent' : (in_array($type, ['birthday', 'anniversary', 'family_event']) && $days !== null && $days <= 7 ? 'high' : 'medium');
    }

    private function daysToAnnual($date): int
    {
        $d = Carbon::parse($date)->setYear(now()->year);
        if ($d->isPast()) {
            $d->addYear();
        }

        return now()->startOfDay()->diffInDays($d->startOfDay());
    }
}
