<?php

namespace App\Domains\Purchasing\Listeners;

use App\Domains\Product\Models\Product;
use App\Domains\Purchasing\Events\PurchaseCompleted;
use App\Services\StockService;

class AddPurchaseStock
{
    public function __construct(
        protected StockService $stockService
    ) {}

    public function handle(PurchaseCompleted $event): void
    {
        $purchase = $event->purchase;
        foreach ($purchase->items as $item) {
            $product = Product::withoutGlobalScopes()->find($item->product_id);
            if ($product) {
                $this->stockService->increaseStock($product, $item->qty);
            }
        }
    }
}
