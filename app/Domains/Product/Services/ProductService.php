<?php

namespace App\Domains\Product\Services;

use App\Domains\Product\Models\Product;
use App\Domains\Tenant\Models\Tenant;
use App\Services\StockService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ProductService
{
    private function resolveTenant(): ?Tenant
    {
        if (tenant() instanceof Tenant) {
            return tenant();
        }
        $user = auth()->user();
        if ($user?->tenant_id !== null) {
            return Tenant::find($user->tenant_id);
        }

        return null;
    }

    public function __construct(
        protected StockService $stockService
    ) {}

    /**
     * Paginated list of products for current tenant.
     *
     * @return LengthAwarePaginator<Product>
     */
    public function list(int $perPage = 15): LengthAwarePaginator
    {
        return Product::query()
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Paginated list of products for current tenant (resolves tenant from auth when tenant() is null).
     * Use this in Livewire so the list still loads after nested component updates.
     *
     * @return LengthAwarePaginator<Product>
     */
    public function listForCurrentTenant(string $search = '', int $perPage = 10): LengthAwarePaginator
    {
        $tenant = $this->resolveTenant();
        if ($tenant === null) {
            return new LengthAwarePaginator([], 0, $perPage);
        }

        $query = Product::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->orderBy('name');

        if ($search !== '') {
            $term = '%'.addcslashes($search, '%_').'%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('sku', 'like', $term)
                    ->orWhere('barcode', 'like', $term);
            });
        }

        return $query->paginate($perPage);
    }

    /**
     * Create a new product. Validates SKU and barcode uniqueness for tenant.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Product
    {
        $data = $this->normalizeProductData($data);
        $this->validateSkuUniqueness($data['sku'] ?? '', null);
        $this->validateBarcodeUniqueness($data['barcode'] ?? null, null);

        $tenant = $this->resolveTenant();
        if ($tenant === null) {
            throw new \RuntimeException('Tenant context required to create product.');
        }

        return DB::transaction(function () use ($data, $tenant) {
            $data['tenant_id'] = $tenant->id;

            return Product::create($data);
        });
    }

    /**
     * Update product. Validates SKU and barcode uniqueness excluding current product.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Product $product, array $data): Product
    {
        $data = $this->normalizeProductData($data);
        $this->validateSkuUniqueness($data['sku'] ?? $product->sku, $product->id);
        $this->validateBarcodeUniqueness($data['barcode'] ?? $product->barcode, $product->id);

        $product->update($data);

        return $product->fresh();
    }

    /**
     * Adjust stock to a new quantity via StockService.
     */
    public function adjustStock(Product $product, int $newQuantity): void
    {
        $this->stockService->adjustStock($product, $newQuantity);
    }

    /**
     * Find product by ID (scoped to tenant). Resolves tenant from auth when tenant() is null (e.g. Livewire context).
     */
    public function find(int $id): ?Product
    {
        $tenant = $this->resolveTenant();
        if ($tenant === null) {
            return null;
        }

        return Product::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->find($id);
    }

    /**
     * Find product by SKU (scoped to tenant).
     */
    public function findBySku(string $sku): ?Product
    {
        return Product::where('sku', $sku)->first();
    }

    /**
     * Find product by barcode (scoped to tenant).
     */
    public function findByBarcode(?string $barcode): ?Product
    {
        if ($barcode === null || $barcode === '') {
            return null;
        }

        return Product::where('barcode', $barcode)->first();
    }

    private function validateSkuUniqueness(string $sku, ?int $excludeProductId): void
    {
        $query = Product::where('sku', $sku);
        if ($excludeProductId !== null) {
            $query->where('id', '!=', $excludeProductId);
        }
        if ($query->exists()) {
            throw new \InvalidArgumentException("SKU \"{$sku}\" already exists for this tenant.");
        }
    }

    /**
     * Normalize product data: empty barcode string becomes null so the unique
     * constraint (tenant_id, barcode) allows multiple rows with no barcode.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizeProductData(array $data): array
    {
        if (array_key_exists('barcode', $data) && $data['barcode'] === '') {
            $data['barcode'] = null;
        }

        return $data;
    }

    private function validateBarcodeUniqueness(?string $barcode, ?int $excludeProductId): void
    {
        if ($barcode === null || $barcode === '') {
            return;
        }
        $query = Product::where('barcode', $barcode);
        if ($excludeProductId !== null) {
            $query->where('id', '!=', $excludeProductId);
        }
        if ($query->exists()) {
            throw new \InvalidArgumentException("Barcode \"{$barcode}\" already exists for this tenant.");
        }
    }
}
