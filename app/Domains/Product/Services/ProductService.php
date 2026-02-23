<?php

namespace App\Domains\Product\Services;

use App\Domains\Product\Models\Product;
use App\Services\StockService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ProductService
{
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
     * Create a new product. Validates SKU and barcode uniqueness for tenant.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Product
    {
        $this->validateSkuUniqueness($data['sku'] ?? '', null);
        $this->validateBarcodeUniqueness($data['barcode'] ?? null, null);

        return DB::transaction(function () use ($data) {
            $data['tenant_id'] = tenant()->id;

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
     * Find product by ID (scoped to tenant).
     */
    public function find(int $id): ?Product
    {
        return Product::find($id);
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
