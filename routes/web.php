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

    Route::get('/run-migrations', function () {
        try {
            \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            return response()->json([
                'status' => 'done',
                'output' => \Illuminate\Support\Facades\Artisan::output(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    });

    Route::get('/fix-subscription-items', function () {
        try {
            \Illuminate\Support\Facades\DB::statement(
                'ALTER TABLE subscription_items ALTER COLUMN subscription_id TYPE VARCHAR(255) USING subscription_id::VARCHAR'
            );
            return response()->json(['status' => 'fixed']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    });

    Route::get('/fix-all-columns', function () {
        try {
            \Illuminate\Support\Facades\DB::statement(
                'ALTER TABLE subscriptions ALTER COLUMN id TYPE VARCHAR(255) USING id::VARCHAR'
            );
            \Illuminate\Support\Facades\DB::statement(
                'ALTER TABLE subscription_items ALTER COLUMN subscription_id TYPE VARCHAR(255) USING subscription_id::VARCHAR'
            );
            return response()->json(['status' => 'all fixed']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    });

    Route::get('/reset-subscriptions', function () {
        \Illuminate\Support\Facades\DB::statement('TRUNCATE TABLE subscription_items CASCADE');
        \Illuminate\Support\Facades\DB::statement('TRUNCATE TABLE subscriptions CASCADE');
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE subscriptions ALTER COLUMN id TYPE VARCHAR(255) USING id::VARCHAR');
        \Illuminate\Support\Facades\DB::statement('ALTER SEQUENCE subscriptions_id_seq RESTART WITH 1');
        return response()->json(['status' => 'reset done']);
    });

    Route::get('/rebuild-cashier-tables', function () {
        try {
            // Drop และสร้างใหม่
            \Illuminate\Support\Facades\DB::statement('DROP TABLE IF EXISTS subscription_items CASCADE');
            \Illuminate\Support\Facades\DB::statement('DROP TABLE IF EXISTS subscriptions CASCADE');

            // สร้าง subscriptions ใหม่ด้วย string id
            \Illuminate\Support\Facades\DB::statement('
                CREATE TABLE subscriptions (
                    id VARCHAR(255) PRIMARY KEY,
                    user_id VARCHAR(255) NOT NULL,
                    type VARCHAR(255) NOT NULL,
                    stripe_id VARCHAR(255) NOT NULL UNIQUE,
                    stripe_status VARCHAR(255) NOT NULL,
                    stripe_price VARCHAR(255),
                    quantity INTEGER,
                    trial_ends_at TIMESTAMP,
                    ends_at TIMESTAMP,
                    created_at TIMESTAMP,
                    updated_at TIMESTAMP
                )
            ');

            // สร้าง subscription_items ใหม่
            \Illuminate\Support\Facades\DB::statement('
                CREATE TABLE subscription_items (
                    id BIGSERIAL PRIMARY KEY,
                    subscription_id VARCHAR(255) NOT NULL REFERENCES subscriptions(id) ON DELETE CASCADE,
                    stripe_id VARCHAR(255) NOT NULL UNIQUE,
                    stripe_product VARCHAR(255),
                    stripe_price VARCHAR(255) NOT NULL,
                    quantity INTEGER,
                    created_at TIMESTAMP,
                    updated_at TIMESTAMP
                )
            ');

            return response()->json(['status' => 'tables rebuilt successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    });
}

require __DIR__.'/auth.php';
