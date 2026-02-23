<?php

namespace App\Services;

use App\Domains\Product\Models\Product;

class StockService
{
    /**
     * Decrease product stock by quantity. Fails if resulting stock would be negative.
     */
    public function decreaseStock(Product $product, int $quantity): void
    {
        $this->ensureNonNegativeStock($product->stock - $quantity, $product->name);

        $product->decrement('stock', $quantity);
    }

    /**
     * Increase product stock by quantity.
     */
    public function increaseStock(Product $product, int $quantity): void
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Quantity must be positive.');
        }

        $product->increment('stock', $quantity);
    }

    /**
     * Set product stock to a specific value (adjustment). New value must be >= 0.
     */
    public function adjustStock(Product $product, int $newQuantity): void
    {
        if ($newQuantity < 0) {
            throw new \InvalidArgumentException('Stock cannot be negative.');
        }

        $product->update(['stock' => $newQuantity]);
    }

    private function ensureNonNegativeStock(int $resultingStock, string $productName): void
    {
        if ($resultingStock < 0) {
            throw new \DomainException("Insufficient stock for {$productName}. Resulting stock would be {$resultingStock}.");
        }
    }
}
