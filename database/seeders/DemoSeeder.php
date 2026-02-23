<?php

namespace Database\Seeders;

use App\Domains\Tenant\Services\TenantService;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    /**
     * Demo user credentials (untuk login percobaan).
     * Email: admin@hsmart.test
     * Password: password
     */
    public const DEMO_EMAIL = 'admin@hsmart.test';

    public const DEMO_PASSWORD = 'password';

    /**
     * Superadmin: manajemen semua tenant.
     * Email: superadmin@hsmart.test
     * Password: password
     */
    public const SUPERADMIN_EMAIL = 'superadmin@hsmart.test';

    public const SUPERADMIN_PASSWORD = 'password';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedDemoTenantUser();
        $this->seedSuperadmin();
    }

    private function seedDemoTenantUser(): void
    {
        if (User::where('email', self::DEMO_EMAIL)->exists()) {
            return;
        }

        $tenantService = app(TenantService::class);
        $tenant = $tenantService->create('Toko Demo HSMart');

        User::create([
            'name' => 'Admin Demo',
            'email' => self::DEMO_EMAIL,
            'password' => Hash::make(self::DEMO_PASSWORD),
            'email_verified_at' => now(),
            'tenant_id' => $tenant->id,
        ]);
    }

    private function seedSuperadmin(): void
    {
        if (User::where('email', self::SUPERADMIN_EMAIL)->exists()) {
            return;
        }

        User::create([
            'name' => 'Super Admin',
            'email' => self::SUPERADMIN_EMAIL,
            'password' => Hash::make(self::SUPERADMIN_PASSWORD),
            'email_verified_at' => now(),
            'tenant_id' => null,
        ]);
    }
}
