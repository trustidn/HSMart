<?php

namespace App\Domains\Tenant\Services;

use App\Domains\Accounting\Services\JournalService;
use App\Domains\Subscription\Services\SubscriptionService;
use App\Domains\Tenant\Models\Tenant;
use App\Domains\Tenant\Models\TenantSetting;
use Illuminate\Support\Str;

class TenantService
{
    /**
     * Create a new tenant with default setting and trial subscription.
     */
    public function create(string $name): Tenant
    {
        $slug = Str::slug($name);
        $tenant = Tenant::create([
            'name' => $name,
            'slug' => $this->ensureUniqueSlug($slug),
        ]);

        $tenant->setting()->create([
            'store_name' => $name,
            'currency' => 'IDR',
            'timezone' => 'Asia/Jakarta',
        ]);

        app(SubscriptionService::class)->startTrial($tenant);
        app(JournalService::class)->ensureDefaultAccounts($tenant);

        return $tenant;
    }

    /**
     * Update tenant settings (white-label).
     *
     * @param  array<string, mixed>  $data
     */
    public function updateSettings(Tenant $tenant, array $data): TenantSetting
    {
        $setting = $tenant->setting ?? $tenant->setting()->make();
        $setting->fill($data);
        $setting->tenant_id = $tenant->id;
        $setting->save();

        return $setting;
    }

    /**
     * Update tenant (name, slug). Superadmin only.
     *
     * @param  array{name?: string, slug?: string}  $data
     */
    public function update(Tenant $tenant, array $data): Tenant
    {
        if (isset($data['slug'])) {
            $data['slug'] = $this->ensureUniqueSlugExcept($data['slug'], $tenant->id);
        }
        $tenant->update($data);

        return $tenant->fresh();
    }

    /**
     * Delete tenant. Users belonging to this tenant will have tenant_id set to null (nullOnDelete).
     */
    public function delete(Tenant $tenant): void
    {
        $tenant->delete();
    }

    /**
     * Get or create setting for tenant.
     */
    public function getSetting(Tenant $tenant): TenantSetting
    {
        return $tenant->setting ?? $tenant->setting()->create([
            'store_name' => $tenant->name,
            'currency' => 'IDR',
            'timezone' => 'Asia/Jakarta',
        ]);
    }

    private function ensureUniqueSlug(string $slug): string
    {
        return $this->ensureUniqueSlugExcept($slug, null);
    }

    private function ensureUniqueSlugExcept(string $slug, ?int $exceptTenantId): string
    {
        $base = $slug;
        $counter = 0;
        $query = Tenant::where('slug', $slug);
        if ($exceptTenantId !== null) {
            $query->where('id', '!=', $exceptTenantId);
        }
        while ($query->exists()) {
            $counter++;
            $slug = $base.'-'.$counter;
            $query = Tenant::where('slug', $slug);
            if ($exceptTenantId !== null) {
                $query->where('id', '!=', $exceptTenantId);
            }
        }

        return $slug;
    }
}
