<?php

namespace App\Domains\POS\Events;

use App\Domains\POS\Models\Sale;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SaleCompleted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Sale $sale
    ) {}
}
