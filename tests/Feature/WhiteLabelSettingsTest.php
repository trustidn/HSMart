<?php

use App\Domains\Subscription\Models\Subscription;
use App\Domains\Tenant\Models\Tenant;
use App\Domains\Tenant\Models\TenantSetting;
use App\Domains\Tenant\Services\TenantService;
use App\Models\User;

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->tenant->setting()->create([
        'store_name' => $this->tenant->name,
        'currency' => 'IDR',
        'timezone' => 'Asia/Jakarta',
    ]);
    Subscription::factory()->active()->create(['tenant_id' => $this->tenant->id]);
    $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);
});

test('tenant user can access white-label settings page', function () {
    $response = $this->actingAs($this->user)->get(route('settings.white-label'));

    $response->assertOk();
});

test('TenantService updateSettings updates store name and colors', function () {
    $service = app(TenantService::class);
    $service->updateSettings($this->tenant, [
        'store_name' => 'Toko Baru',
        'primary_color' => '#2563eb',
        'secondary_color' => '#64748b',
        'currency' => 'IDR',
        'timezone' => 'Asia/Jakarta',
    ]);

    $setting = TenantSetting::where('tenant_id', $this->tenant->id)->first();
    expect($setting->store_name)->toBe('Toko Baru')
        ->and($setting->primary_color)->toBe('#2563eb')
        ->and($setting->secondary_color)->toBe('#64748b');
});
