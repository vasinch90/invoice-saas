<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\SubscriptionController;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    Route::middleware('auth')->group(function () {
        Route::resource('clients', ClientController::class)->except(['show']);
        Route::resource('invoices', InvoiceController::class);
        Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])
            ->name('invoices.pdf');

        Route::prefix('subscription')->name('subscription.')->group(function () {
            Route::get('plans', [SubscriptionController::class, 'plans'])->name('plans');
            Route::post('checkout', [SubscriptionController::class, 'checkout'])->name('checkout');
            Route::get('success', [SubscriptionController::class, 'success'])->name('success');
            Route::post('cancel', [SubscriptionController::class, 'cancel'])->name('cancel');
            Route::get('billing', [SubscriptionController::class, 'billing'])->name('billing');
        });
    });
});
