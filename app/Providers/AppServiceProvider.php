<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(fn ($user, $ability) => $user->role?->slug === 'super-admin' ? true : null);
        foreach (['dashboard', 'leads', 'followups', 'customers', 'sales', 'custom-orders', 'exhibitions', 'marketing', 'retention', 'offers', 'loyalty', 'gift-cards', 'tasks', 'reports', 'settings', 'staff'] as $module) {
            foreach (['view', 'create', 'update', 'delete'] as $action) {
                Gate::define("$module.$action", fn ($user) => $user->hasPermission("$module.$action"));
            }
        }
    }
}
