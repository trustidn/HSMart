<?php

namespace App\Domains\Settings\Livewire;

use App\Domains\Tenant\Services\TenantService;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class WhiteLabelSettings extends Component
{
    use WithFileUploads;

    public string $store_name = '';

    public $logo = null;

    public ?string $primary_color = null;

    public ?string $secondary_color = null;

    public string $receipt_footer = '';

    public string $currency = 'IDR';

    public string $timezone = 'Asia/Jakarta';

    public function mount(): void
    {
        $t = tenant();
        if (! $t) {
            abort(403);
        }
        $setting = app(TenantService::class)->getSetting($t);
        $this->store_name = $setting->store_name ?? $t->name;
        $this->primary_color = $setting->primary_color;
        $this->secondary_color = $setting->secondary_color;
        $this->receipt_footer = $setting->receipt_footer ?? '';
        $this->currency = $setting->currency ?? 'IDR';
        $this->timezone = $setting->timezone ?? 'Asia/Jakarta';
    }

    public function save(): void
    {
        $t = tenant();
        if (! $t) {
            abort(403);
        }

        $validated = $this->validate([
            'store_name' => ['required', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'primary_color' => ['nullable', 'string', 'max:20'],
            'secondary_color' => ['nullable', 'string', 'max:20'],
            'receipt_footer' => ['nullable', 'string', 'max:500'],
            'currency' => ['required', 'string', 'max:10'],
            'timezone' => ['required', 'string', 'max:50'],
        ]);

        $data = [
            'store_name' => $validated['store_name'],
            'primary_color' => $validated['primary_color'] ?: null,
            'secondary_color' => $validated['secondary_color'] ?: null,
            'receipt_footer' => $validated['receipt_footer'] ?: null,
            'currency' => $validated['currency'],
            'timezone' => $validated['timezone'],
        ];

        if ($this->logo) {
            $path = 'tenant-logos/'.$t->id;
            Storage::disk('public')->deleteDirectory($path);
            $stored = $this->logo->store($path, 'public');
            $data['logo_path'] = $stored;
        }

        app(TenantService::class)->updateSettings($t, $data);
        $this->redirectRoute('settings.white-label', navigate: true);
    }

    public function removeLogo(): void
    {
        $t = tenant();
        if (! $t || ! $t->setting?->logo_path) {
            return;
        }
        Storage::disk('public')->delete($t->setting->logo_path);
        app(TenantService::class)->updateSettings($t, ['logo_path' => null]);
        $this->redirectRoute('settings.white-label', navigate: true);
    }

    public function render()
    {
        return view('domains.settings.livewire.white-label-settings')
            ->layout('layouts.app', ['title' => __('Store settings')]);
    }
}
