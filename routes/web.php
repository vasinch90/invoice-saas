<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\InvoiceController;
use Laravel\Cashier\Http\Controllers\WebhookController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('clients', ClientController::class)
        ->except(['show']);
    Route::resource('invoices', InvoiceController::class);
    Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])
        ->name('invoices.pdf');
});

Route::post('stripe/webhook', [WebhookController::class, 'handleWebhook']);

if (app()->environment('production')) {
    Route::get('/sync-subscription', function () {
        $tenant = \App\Models\Tenant::find('demo');
        $stripe = new \Stripe\StripeClient(config('cashier.secret'));
        $subscriptions = $stripe->subscriptions->all([
            'customer' => $tenant->stripe_id
        ]);

        foreach ($subscriptions->data as $sub) {
            \Laravel\Cashier\Subscription::updateOrCreate(
                ['stripe_id' => $sub->id],
                [
                    'user_id'       => $tenant->id,
                    'type'          => 'default',
                    'stripe_status' => $sub->status,
                    'stripe_price'  => $sub->items->data[0]->price->id,
                    'quantity'      => 1,
                    'trial_ends_at' => $sub->trial_end
                        ? \Carbon\Carbon::createFromTimestamp($sub->trial_end)
                        : null,
                    'ends_at'       => null,
                ]
            );
        }

        return response()->json([
            'status' => 'synced',
            'subscriptions' => $subscriptions->count(),
            'stripe_id' => $tenant->stripe_id,
        ]);
    });

    Route::get('/debug-plans', function () {
        try {
            $tenant = \App\Models\Tenant::find('demo');
            $sub = $tenant->subscriptions()->first();
            return response()->json([
                'tenant'       => $tenant,
                'stripe_id'    => $tenant->stripe_id,
                'subscription' => $sub,
                'subscribed'   => $tenant->subscribed('default'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    });
}

require __DIR__.'/auth.php';
