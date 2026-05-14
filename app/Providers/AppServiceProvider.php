<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use Laravel\Cashier\Cashier;
use Laravel\Cashier\Subscription;
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

        // บอก Cashier ว่า id เป็น string
        Subscription::creating(function ($model) {
            $model->incrementing = false;
            $model->keyType = 'string';
        });

        // override items relationship
        Subscription::resolveRelationUsing('items', function ($model) {
            return $model->hasMany(SubscriptionItem::class, 'subscription_id', 'id');
        });

        if (app()->environment('production')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        \Stancl\Tenancy\Middleware\InitializeTenancyByDomain::$onFail = function () {
            return redirect(config('app.url'));
        };
    }
}
