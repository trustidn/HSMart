<?php

namespace Database\Seeders;

/**
 * Seeder untuk user dan data lengkap keperluan test/demo.
 *
 * Jalankan: php artisan db:seed (atau php artisan db:seed --class=TestDataSeeder)
 *
 * User & password:
 * - Superadmin: superadmin@hsmart.test / password
 * - Owner tenant demo: admin@hsmart.test / password
 * - Member tenant demo: member@hsmart.test / password
 */
use App\Domains\Product\Models\Product;
use App\Domains\Purchasing\Models\Supplier;
use App\Domains\Purchasing\Services\PurchaseService;
use App\Domains\Tenant\Models\Tenant;
use App\Domains\Tenant\Services\TenantService;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestDataSeeder extends Seeder
{
    /**
     * Credentials untuk keperluan test dan login manual.
     */
    public const SUPERADMIN_EMAIL = 'superadmin@hsmart.test';

    public const SUPERADMIN_PASSWORD = 'password';

    public const DEMO_OWNER_EMAIL = 'admin@hsmart.test';

    public const DEMO_OWNER_PASSWORD = 'password';

    public const DEMO_MEMBER_EMAIL = 'member@hsmart.test';

    public const DEMO_MEMBER_PASSWORD = 'password';

    public const DEMO_TENANT_NAME = 'Toko Demo HSMart';

    public function run(): void
    {
        $this->seedSuperadmin();
        $demoTenant = $this->seedDemoTenant();
        $this->seedDemoTenantUsers($demoTenant);
        $this->seedDemoProductsAndSuppliers($demoTenant);
        $this->seedDemoSalesAndPurchase($demoTenant);
    }

    private function seedSuperadmin(): User
    {
        $user = User::firstOrCreate(
            ['email' => self::SUPERADMIN_EMAIL],
            [
                'name' => 'Super Admin',
                'password' => Hash::make(self::SUPERADMIN_PASSWORD),
                'email_verified_at' => now(),
                'tenant_id' => null,
            ]
        );

        $this->command->info('Superadmin: '.self::SUPERADMIN_EMAIL.' / '.self::SUPERADMIN_PASSWORD);

        return $user;
    }

    private function seedDemoTenant(): Tenant
    {
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'toko-demo-hsmart'],
            ['name' => self::DEMO_TENANT_NAME]
        );

        $setting = app(TenantService::class)->getSetting($tenant);
        $setting->update([
            'store_name' => self::DEMO_TENANT_NAME,
            'currency' => 'IDR',
            'timezone' => 'Asia/Jakarta',
        ]);

        if (! $tenant->subscriptions()->current()->exists()) {
            app(\App\Domains\Subscription\Services\SubscriptionService::class)->startTrial($tenant);
        }

        app(\App\Domains\Accounting\Services\JournalService::class)->ensureDefaultAccounts($tenant);

        $this->command->info('Tenant: '.$tenant->name);

        return $tenant->fresh();
    }

    private function seedDemoTenantUsers(Tenant $tenant): void
    {
        $owner = User::firstOrCreate(
            ['email' => self::DEMO_OWNER_EMAIL],
            [
                'name' => 'Admin Demo',
                'password' => Hash::make(self::DEMO_OWNER_PASSWORD),
                'email_verified_at' => now(),
                'tenant_id' => $tenant->id,
                'is_tenant_owner' => true,
            ]
        );
        if (! $owner->is_tenant_owner) {
            $owner->update(['tenant_id' => $tenant->id, 'is_tenant_owner' => true]);
        }

        User::firstOrCreate(
            ['email' => self::DEMO_MEMBER_EMAIL],
            [
                'name' => 'Member Demo',
                'password' => Hash::make(self::DEMO_MEMBER_PASSWORD),
                'email_verified_at' => now(),
                'tenant_id' => $tenant->id,
                'is_tenant_owner' => false,
            ]
        );

        $this->command->info('Users: '.self::DEMO_OWNER_EMAIL.' (owner), '.self::DEMO_MEMBER_EMAIL.' (member)');
    }

    private function seedDemoProductsAndSuppliers(Tenant $tenant): void
    {
        if (Product::where('tenant_id', $tenant->id)->exists()) {
            $this->command->info('Products/suppliers already exist for demo tenant, skipping.');

            return;
        }

        $products = [
            ['sku' => 'SKU-001', 'name' => 'Beras Premium 5kg', 'cost_price' => 55000, 'sell_price' => 65000, 'stock' => 50],
            ['sku' => 'SKU-002', 'name' => 'Minyak Goreng 2L', 'cost_price' => 28000, 'sell_price' => 32000, 'stock' => 40],
            ['sku' => 'SKU-003', 'name' => 'Gula Pasir 1kg', 'cost_price' => 15000, 'sell_price' => 18000, 'stock' => 60],
            ['sku' => 'SKU-004', 'name' => 'Telur 1kg', 'cost_price' => 22000, 'sell_price' => 26000, 'stock' => 30],
            ['sku' => 'SKU-005', 'name' => 'Kopi Sachet 10x', 'cost_price' => 12000, 'sell_price' => 15000, 'stock' => 45],
            ['sku' => 'SKU-006', 'name' => 'Sabun Mandi', 'cost_price' => 5000, 'sell_price' => 7000, 'stock' => 80],
            ['sku' => 'SKU-007', 'name' => 'Pasta Gigi', 'cost_price' => 8000, 'sell_price' => 10000, 'stock' => 55],
        ];

        foreach ($products as $p) {
            Product::create([
                'tenant_id' => $tenant->id,
                'sku' => $p['sku'],
                'name' => $p['name'],
                'cost_price' => $p['cost_price'],
                'sell_price' => $p['sell_price'],
                'stock' => $p['stock'],
                'minimum_stock' => 5,
                'is_active' => true,
            ]);
        }

        Supplier::create([
            'tenant_id' => $tenant->id,
            'name' => 'CV Sumber Pangan',
            'contact' => 'Budi',
            'phone' => '08123456789',
            'address' => 'Jl. Raya Pasar Induk No. 1',
        ]);
        Supplier::create([
            'tenant_id' => $tenant->id,
            'name' => 'Toko Grosir Sejahtera',
            'contact' => 'Siti',
            'phone' => '08234567890',
        ]);

        $this->command->info('Created '.count($products).' products and 2 suppliers.');
    }

    private function seedDemoSalesAndPurchase(Tenant $tenant): void
    {
        $saleCount = \App\Domains\POS\Models\Sale::where('tenant_id', $tenant->id)->count();
        if ($saleCount > 0) {
            $this->command->info('Sales/purchases already exist for demo tenant, skipping.');

            return;
        }

        app()->instance('tenant', $tenant);

        $productIds = Product::where('tenant_id', $tenant->id)->pluck('id')->toArray();
        if (count($productIds) < 2) {
            $this->command->warn('Need at least 2 products for demo sales.');

            return;
        }

        $saleService = app(\App\Domains\POS\Services\SaleService::class);
        $p1 = Product::where('tenant_id', $tenant->id)->where('sku', 'SKU-001')->first();
        $p2 = Product::where('tenant_id', $tenant->id)->where('sku', 'SKU-002')->first();
        if ($p1 && $p2) {
            $saleService->createSale('Pelanggan Umum', [
                ['product_id' => $p1->id, 'qty' => 2, 'unit_price' => 65000],
                ['product_id' => $p2->id, 'qty' => 1, 'unit_price' => 32000],
            ], 162000);
            $saleService->createSale('Budi', [
                ['product_id' => $p1->id, 'qty' => 1, 'unit_price' => 65000],
            ], 65000);
        }

        $supplier = Supplier::where('tenant_id', $tenant->id)->first();
        if ($supplier && $p1) {
            app(PurchaseService::class)->createPurchase($supplier->id, [
                ['product_id' => $p1->id, 'qty' => 20, 'unit_cost' => 55000],
            ]);
        }

        $this->command->info('Created demo sales and 1 purchase.');
    }
}
