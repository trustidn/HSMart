<?php

namespace App\Domains\Purchasing\Listeners;

use App\Domains\Purchasing\Events\PurchaseCompleted;
use App\Services\StockService;

class AddPurchaseStock
{
    public function __construct(
        protected StockService $stockService
    ) {}

    public function handle(PurchaseCompleted $event): void
    {
        foreach ($event->purchase->items as $item) {
            $this->stockService->increaseStock($item->product, $item->qty);
        }
    }
}
