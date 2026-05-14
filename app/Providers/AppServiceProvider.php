<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use Laravel\Cashier\Cashier;
use Laravel\Cashier\SubscriptionItem;

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
        Cashier::useCustomerModel(\App\Models\Tenant::class);

        SubscriptionItem::resolveRelationUsing('subscription', function ($model) {
            return $model->belongsTo(\Laravel\Cashier\Subscription::class, 'subscription_id', 'id');
        });
        
        if (app()->environment('production')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        \Stancl\Tenancy\Middleware\InitializeTenancyByDomain::$onFail = function () {
            return redirect(config('app.url'));
        };
    }
}
