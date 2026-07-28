<?php

namespace App\Models;

use App\Models\Concerns\CrmModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use CrmModel, SoftDeletes;

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function familyMembers()
    {
        return $this->hasMany(CustomerFamilyMember::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class)->latest('sale_date');
    }

    public function customOrders()
    {
        return $this->hasMany(CustomOrder::class)->latest();
    }

    public function loyaltyPoints()
    {
        return $this->hasMany(LoyaltyPoint::class)->latest();
    }

    public function giftCards()
    {
        return $this->hasMany(GiftCard::class);
    }

    public function privilegeCards()
    {
        return $this->hasMany(PrivilegeCard::class)->latest();
    }

    public function campaigns()
    {
        return $this->morphMany(CampaignRecipient::class, 'recipient');
    }

    public function importantDates()
    {
        return $this->hasMany(CustomerImportantDate::class);
    }

    public function retentionScore()
    {
        return $this->hasOne(CustomerRetentionScore::class);
    }

    public function retentionMessages()
    {
        return $this->hasMany(RetentionMessage::class);
    }

    public function smartTasks()
    {
        return $this->hasMany(Task::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
