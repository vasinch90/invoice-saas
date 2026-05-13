<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\InvoiceController;
use Laravel\Cashier\Http\Controllers\WebhookController;

// เพิ่มเฉพาะ testing environment
if (app()->environment('testing')) {
    Route::middleware('auth')->group(function () {
        Route::resource('clients', \App\Http\Controllers\ClientController::class)
            ->except(['show']);
        Route::resource('invoices', \App\Http\Controllers\InvoiceController::class);
        Route::get('invoices/{invoice}/pdf', [\App\Http\Controllers\InvoiceController::class, 'pdf'])
            ->name('invoices.pdf');
    });
}

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

require __DIR__.'/auth.php';
