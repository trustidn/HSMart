<?php

use App\Domains\Product\Livewire\ProductForm;
use App\Domains\Product\Livewire\ProductIndex;
use App\Domains\Tenant\Livewire\Admin\TenantList;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified', 'tenant', 'require.tenant', 'subscription'])
    ->name('dashboard');

Route::view('subscription/expired', 'subscription-expired')
    ->middleware(['auth', 'verified', 'tenant', 'require.tenant'])
    ->name('subscription.expired');

Route::middleware(['auth', 'verified', 'tenant', 'require.tenant', 'subscription'])->group(function () {
    Route::livewire('products', ProductIndex::class)->name('products.index');
    Route::livewire('products/create', ProductForm::class)->name('products.create');
    Route::livewire('products/{productId}/edit', ProductForm::class)->name('products.edit');
});

Route::middleware(['auth', 'verified', 'tenant', 'superadmin'])->group(function () {
    Route::livewire('admin/tenants', TenantList::class)->name('admin.tenants');
});

require __DIR__.'/settings.php';
