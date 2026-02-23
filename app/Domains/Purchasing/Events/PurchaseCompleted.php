<?php

namespace App\Domains\Purchasing\Events;

use App\Domains\Purchasing\Models\Purchase;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PurchaseCompleted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Purchase $purchase
    ) {}
}
