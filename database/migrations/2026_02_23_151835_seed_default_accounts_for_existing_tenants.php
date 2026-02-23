<?php

use App\Domains\Accounting\Models\Account;
use App\Domains\Tenant\Models\Tenant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const DEFAULTS = [
        ['code' => '1100', 'name' => 'Kas', 'type' => 'asset'],
        ['code' => '1200', 'name' => 'Persediaan Barang', 'type' => 'asset'],
        ['code' => '2100', 'name' => 'Hutang Usaha', 'type' => 'liability'],
        ['code' => '4100', 'name' => 'Penjualan', 'type' => 'revenue'],
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tenantIdsWithAccounts = DB::table('accounts')->distinct()->pluck('tenant_id')->all();
        $tenantsWithoutAccounts = Tenant::whereNotIn('id', $tenantIdsWithAccounts)->get();

        foreach ($tenantsWithoutAccounts as $tenant) {
            foreach (self::DEFAULTS as $row) {
                Account::withoutGlobalScopes()->create([
                    'tenant_id' => $tenant->id,
                    'code' => $row['code'],
                    'name' => $row['name'],
                    'type' => $row['type'],
                    'is_active' => true,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optional: remove accounts created by this migration (risky if journals exist)
    }
};
