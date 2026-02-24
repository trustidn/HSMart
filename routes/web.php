<?php

use App\Domains\Platform\Livewire\PlatformSettings;
use App\Domains\POS\Livewire\PosPage;
use App\Domains\Product\Livewire\ProductDetail;
use App\Domains\Product\Livewire\ProductForm;
use App\Domains\Product\Livewire\ProductIndex;
use App\Domains\Purchasing\Livewire\PurchaseCreate;
use App\Domains\Purchasing\Livewire\PurchaseIndex;
use App\Domains\Purchasing\Livewire\SupplierForm;
use App\Domains\Purchasing\Livewire\SupplierIndex;
use App\Domains\Reporting\Http\ReportExportController;
use App\Domains\Reporting\Livewire\ReportDashboard;
use App\Domains\Settings\Livewire\WhiteLabelSettings;
use App\Domains\Subscription\Livewire\Admin\PlanForm;
use App\Domains\Subscription\Livewire\Admin\PlanIndex;
use App\Domains\Subscription\Livewire\SubscriptionPage;
use App\Domains\Tenant\Livewire\Admin\TenantForm;
use App\Domains\Tenant\Livewire\Admin\TenantList;
use App\Domains\Tenant\Livewire\Admin\UserForm;
use App\Domains\Tenant\Livewire\Admin\UserList;
use App\Domains\Tenant\Livewire\TenantUserForm;
use App\Domains\Tenant\Livewire\TenantUserList;
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

Route::middleware(['auth', 'verified', 'tenant', 'require.tenant', 'tenant.owner'])->group(function () {
    Route::livewire('team/users', TenantUserList::class)->name('users.index');
    Route::livewire('team/users/create', TenantUserForm::class)->name('users.create');
});
Route::middleware(['auth', 'verified', 'tenant', 'require.tenant', 'tenant.edit.self.or.owner'])->group(function () {
    Route::livewire('team/users/{userId}/edit', TenantUserForm::class)->name('users.edit');
});

Route::middleware(['auth', 'verified', 'tenant', 'require.tenant'])->group(function () {
    Route::livewire('subscription', SubscriptionPage::class)->name('subscription.index');
    Route::livewire('products', ProductIndex::class)->name('products.index');
    Route::livewire('products/create', ProductForm::class)->name('products.create');
    Route::livewire('products/{productId}', ProductDetail::class)->name('products.show');
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
    Route::prefix('reports/export')->name('reports.export.')->group(function () {
        Route::get('sales/pdf', [ReportExportController::class, 'salesPdf'])->name('sales.pdf');
        Route::get('sales/excel', [ReportExportController::class, 'salesExcel'])->name('sales.excel');
        Route::get('top-products/pdf', [ReportExportController::class, 'topProductsPdf'])->name('top-products.pdf');
        Route::get('top-products/excel', [ReportExportController::class, 'topProductsExcel'])->name('top-products.excel');
        Route::get('stock/pdf', [ReportExportController::class, 'stockPdf'])->name('stock.pdf');
        Route::get('stock/excel', [ReportExportController::class, 'stockExcel'])->name('stock.excel');
    });
    Route::livewire('settings/white-label', WhiteLabelSettings::class)->name('settings.white-label');
});

Route::middleware(['auth', 'verified', 'tenant', 'superadmin'])->group(function () {
    Route::livewire('admin/platform-settings', PlatformSettings::class)->name('admin.platform-settings');
    Route::livewire('admin/plans', PlanIndex::class)->name('admin.plans');
    Route::livewire('admin/plans/create', PlanForm::class)->name('admin.plans.create');
    Route::livewire('admin/plans/{planId}/edit', PlanForm::class)->name('admin.plans.edit');
    Route::livewire('admin/tenants', TenantList::class)->name('admin.tenants');
    Route::livewire('admin/tenants/create', TenantForm::class)->name('admin.tenants.create');
    Route::livewire('admin/tenants/{tenantId}/edit', TenantForm::class)->name('admin.tenants.edit');
    Route::livewire('admin/users', UserList::class)->name('admin.users');
    Route::livewire('admin/users/create', UserForm::class)->name('admin.users.create');
    Route::livewire('admin/users/{userId}/edit', UserForm::class)->name('admin.users.edit');
});

require __DIR__.'/settings.php';
