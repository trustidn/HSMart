<?php

use App\Domains\POS\Livewire\PosPage;
use App\Domains\Product\Livewire\ProductForm;
use App\Domains\Product\Livewire\ProductIndex;
use App\Domains\Purchasing\Livewire\PurchaseCreate;
use App\Domains\Purchasing\Livewire\PurchaseIndex;
use App\Domains\Purchasing\Livewire\SupplierForm;
use App\Domains\Purchasing\Livewire\SupplierIndex;
use App\Domains\Reporting\Livewire\ReportDashboard;
use App\Domains\Settings\Livewire\WhiteLabelSettings;
use App\Domains\Tenant\Livewire\Admin\TenantList;
use App\Livewire\Dashboard;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::livewire('dashboard', Dashboard::class)
    ->middleware(['auth', 'verified', 'tenant'])
    ->name('dashboard');

Route::view('subscription/expired', 'subscription-expired')
    ->middleware(['auth', 'verified', 'tenant', 'require.tenant'])
    ->name('subscription.expired');

Route::middleware(['auth', 'verified', 'tenant', 'require.tenant'])->group(function () {
    Route::livewire('products', ProductIndex::class)->name('products.index');
    Route::livewire('products/create', ProductForm::class)->name('products.create');
    Route::livewire('products/{productId}/edit', ProductForm::class)->name('products.edit');
    Route::livewire('pos', PosPage::class)->name('pos')->middleware('subscription');
    Route::prefix('purchasing')->name('purchasing.')->group(function () {
        Route::livewire('suppliers', SupplierIndex::class)->name('suppliers.index');
        Route::livewire('suppliers/create', SupplierForm::class)->name('suppliers.create');
        Route::livewire('suppliers/{supplierId}/edit', SupplierForm::class)->name('suppliers.edit');
        Route::livewire('purchases', PurchaseIndex::class)->name('purchases.index');
        Route::livewire('purchases/create', PurchaseCreate::class)->name('purchases.create')->middleware('subscription');
    });
    Route::livewire('reports', ReportDashboard::class)->name('reports');
    Route::livewire('settings/white-label', WhiteLabelSettings::class)->name('settings.white-label');
});

Route::middleware(['auth', 'verified', 'tenant', 'superadmin'])->group(function () {
    Route::livewire('admin/tenants', TenantList::class)->name('admin.tenants');
});

require __DIR__.'/settings.php';
