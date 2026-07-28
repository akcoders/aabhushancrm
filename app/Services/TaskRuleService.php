<?php

namespace App\Services;

use App\Models\TaskRule;

class TaskRuleService
{
    public function getActiveRules()
    {
        return TaskRule::where('is_active', true)->get()->groupBy('module');
    }

    public function applyLeadRules()
    {
        return app(SmartTaskEngineService::class)->scanLeads();
    }

    public function applyCustomerRules()
    {
        return app(SmartTaskEngineService::class)->scanCustomers();
    }

    public function applySalesRules()
    {
        return app(SmartTaskEngineService::class)->scanSales();
    }

    public function applyCustomOrderRules()
    {
        return app(SmartTaskEngineService::class)->scanCustomOrders();
    }

    public function applyExhibitionRules()
    {
        return app(SmartTaskEngineService::class)->scanExhibitions();
    }
}
