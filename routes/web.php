<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified', 'tenant', 'subscription'])
    ->name('dashboard');

Route::view('subscription/expired', 'subscription-expired')
    ->middleware(['auth', 'verified', 'tenant'])
    ->name('subscription.expired');

require __DIR__.'/settings.php';
