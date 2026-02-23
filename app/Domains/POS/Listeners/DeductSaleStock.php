<?php

namespace App\Domains\POS\Listeners;

use App\Domains\POS\Events\SaleCompleted;
use App\Services\StockService;

class DeductSaleStock
{
    public function __construct(
        protected StockService $stockService
    ) {}

    public function handle(SaleCompleted $event): void
    {
        foreach ($event->sale->items as $item) {
            $this->stockService->decreaseStock($item->product, $item->qty);
        }
    }
}
