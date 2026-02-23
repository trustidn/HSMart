<?php

namespace App\Domains\Reporting\Livewire;

use App\Domains\Reporting\Services\ReportService;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ReportDashboard extends Component
{
    public string $date_from = '';

    public string $date_to = '';

    public bool $low_stock_only = false;

    public function mount(): void
    {
        $this->date_to = now()->format('Y-m-d');
        $this->date_from = now()->subDays(30)->format('Y-m-d');
    }

    #[Computed]
    public function ringkasanOmzet(): array
    {
        return app(ReportService::class)->getRingkasanOmzet($this->date_from, $this->date_to);
    }

    #[Computed]
    public function laporanPenjualan()
    {
        return app(ReportService::class)->getLaporanPenjualan($this->date_from, $this->date_to, 10);
    }

    #[Computed]
    public function topProduk()
    {
        return app(ReportService::class)->getTopProduk($this->date_from, $this->date_to, 10);
    }

    #[Computed]
    public function laporanStok()
    {
        return app(ReportService::class)->getLaporanStok($this->low_stock_only);
    }

    #[Computed]
    public function labaRugi(): array
    {
        return app(ReportService::class)->getLabaRugi($this->date_from, $this->date_to);
    }

    public function render()
    {
        return view('domains.reporting.livewire.report-dashboard')
            ->layout('layouts.app', ['title' => __('Reports')]);
    }
}
