<?php

namespace App\Domains\POS\Listeners;

use App\Domains\POS\Events\SaleCompleted;
use App\Domains\Product\Models\Product;
use App\Services\StockService;

class DeductSaleStock
{
    public function __construct(
        protected StockService $stockService
    ) {}

    public function handle(SaleCompleted $event): void
    {
        foreach ($event->sale->items as $item) {
            $product = Product::withoutGlobalScopes()->find($item->product_id);
            if ($product !== null) {
                $this->stockService->decreaseStock($product, $item->qty);
            }
        }
    }
}
