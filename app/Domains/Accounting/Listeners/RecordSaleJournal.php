<?php

namespace App\Domains\Accounting\Listeners;

use App\Domains\Accounting\Services\JournalService;
use App\Domains\POS\Events\SaleCompleted;

class RecordSaleJournal
{
    public function __construct(
        protected JournalService $journalService
    ) {}

    public function handle(SaleCompleted $event): void
    {
        $this->journalService->recordSaleJournal($event->sale);
    }
}
