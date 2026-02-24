<?php

namespace App\Domains\Reporting\Livewire;

use App\Domains\Reporting\Services\ReportService;
use App\Domains\Tenant\Models\Tenant;
use Carbon\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ReportDashboard extends Component
{
    public const PRESET_TODAY = 'today';

    public const PRESET_WEEK = 'week';

    public const PRESET_MONTH = 'month';

    public const PRESET_CUSTOM = 'custom';

    public string $date_from = '';

    public string $date_to = '';

    /** @var 'today'|'week'|'month'|'custom' */
    public string $date_preset = self::PRESET_MONTH;

    public bool $low_stock_only = false;

    /**
     * Tenant ID from initial load so Livewire update requests (which may not run tenant middleware) still have tenant context.
     */
    public ?int $tenant_id = null;

    public function mount(): void
    {
        $this->tenant_id = tenant()?->id;
        $this->applyPreset($this->date_preset);
    }

    /**
     * Ensure tenant is in the container (Livewire update requests may not run tenant middleware).
     */
    protected function ensureTenant(): void
    {
        if (function_exists('tenant') && tenant() !== null) {
            return;
        }
        if ($this->tenant_id !== null) {
            $tenant = Tenant::find($this->tenant_id);
            if ($tenant !== null) {
                app()->instance('tenant', $tenant);
            }
        }
    }

    public function applyPreset(string $preset): void
    {
        $this->date_preset = $preset;

        $now = Carbon::now();

        if ($preset === self::PRESET_TODAY) {
            $this->date_from = $now->format('Y-m-d');
            $this->date_to = $now->format('Y-m-d');
        } elseif ($preset === self::PRESET_WEEK) {
            $this->date_from = $now->copy()->startOfWeek()->format('Y-m-d');
            $this->date_to = $now->format('Y-m-d');
        } elseif ($preset === self::PRESET_MONTH) {
            $this->date_from = $now->copy()->startOfMonth()->format('Y-m-d');
            $this->date_to = $now->format('Y-m-d');
        } elseif ($preset === self::PRESET_CUSTOM) {
            // Keep current range; if empty (e.g. first time), default to this month
            if ($this->date_from === '' || $this->date_to === '') {
                $this->date_from = $now->copy()->startOfMonth()->format('Y-m-d');
                $this->date_to = $now->format('Y-m-d');
            }
        }
    }

    public function setCustomPreset(): void
    {
        $this->date_preset = self::PRESET_CUSTOM;
    }

    #[Computed]
    public function ringkasanOmzet(): array
    {
        $this->ensureTenant();

        return app(ReportService::class)->getRingkasanOmzet($this->date_from, $this->date_to);
    }

    #[Computed]
    public function laporanPenjualan()
    {
        $this->ensureTenant();

        return app(ReportService::class)->getLaporanPenjualan($this->date_from, $this->date_to, 10);
    }

    #[Computed]
    public function topProduk()
    {
        $this->ensureTenant();

        return app(ReportService::class)->getTopProduk($this->date_from, $this->date_to, 10);
    }

    #[Computed]
    public function laporanStok()
    {
        $this->ensureTenant();

        return app(ReportService::class)->getLaporanStok($this->low_stock_only);
    }

    #[Computed]
    public function labaRugi(): array
    {
        $this->ensureTenant();

        return app(ReportService::class)->getLabaRugi($this->date_from, $this->date_to);
    }

    public function render()
    {
        return view('domains.reporting.livewire.report-dashboard')
            ->layout('layouts.app', ['title' => 'Laporan']);
    }
}
