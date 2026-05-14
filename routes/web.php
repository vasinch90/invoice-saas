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
    Route::get('/setup-demo', function () {
        // สร้าง tenant
        $tenant = \App\Models\Tenant::firstOrCreate(
            ['id' => 'demo'],
            ['name' => 'Demo Company', 'email' => 'demo@example.com']
        );

        // สร้าง domain
        $tenant->domains()->firstOrCreate([
            'domain' => request()->getHost(),
        ]);

        // รัน tenant migrations
        \Illuminate\Support\Facades\Artisan::call('tenants:migrate', [
            '--tenants' => ['demo'],
        ]);

        // สร้าง user ใน tenant context
        $tenant->run(function () {
            \App\Models\User::firstOrCreate(
                ['email' => 'admin@demo.com'],
                [
                    'name'     => 'Admin',
                    'password' => bcrypt('password123'),
                ]
            );
        });

        return response()->json([
            'status'   => 'success',
            'tenant'   => 'demo',
            'domain'   => request()->getHost(),
            'email'    => 'admin@demo.com',
            'password' => 'password123',
        ]);
    });
}

require __DIR__.'/auth.php';
