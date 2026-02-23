<?php

namespace App\Domains\Accounting\Listeners;

use App\Domains\Accounting\Services\JournalService;
use App\Domains\Purchasing\Events\PurchaseCompleted;

class RecordPurchaseJournal
{
    public function __construct(
        protected JournalService $journalService
    ) {}

    public function handle(PurchaseCompleted $event): void
    {
        $this->journalService->recordPurchaseJournal($event->purchase);
    }
}
