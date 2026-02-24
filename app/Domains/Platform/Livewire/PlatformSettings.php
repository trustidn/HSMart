<?php

namespace App\Domains\Platform\Livewire;

use App\Domains\Platform\Models\PlatformSetting;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class PlatformSettings extends Component
{
    use WithFileUploads;

    public string $app_name = '';

    public $logo = null;

    public ?string $primary_color = null;

    public ?string $secondary_color = null;

    public function mount(): void
    {
        $s = PlatformSetting::current();
        $this->app_name = $s->app_name;
        $this->primary_color = $s->primary_color ?? '#0f766e';
        $this->secondary_color = $s->secondary_color ?? '#134e4a';
    }

    public function save(): void
    {
        $this->validate([
            'app_name' => ['required', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'primary_color' => ['nullable', 'string', 'max:20'],
            'secondary_color' => ['nullable', 'string', 'max:20'],
        ]);

        $setting = PlatformSetting::current();
        $data = [
            'app_name' => $this->app_name,
            'primary_color' => $this->primary_color ?: null,
            'secondary_color' => $this->secondary_color ?: null,
        ];

        if ($this->logo) {
            if ($setting->logo_path) {
                Storage::disk('public')->delete($setting->logo_path);
            }
            $data['logo_path'] = $this->logo->store('platform', 'public');
        }

        $setting->update($data);
        PlatformSetting::clearCache();

        $this->redirectRoute('admin.platform-settings', navigate: true);
    }

    public function removeLogo(): void
    {
        $setting = PlatformSetting::current();
        if ($setting->logo_path) {
            Storage::disk('public')->delete($setting->logo_path);
            $setting->update(['logo_path' => null]);
            PlatformSetting::clearCache();
        }
        $this->redirectRoute('admin.platform-settings', navigate: true);
    }

    public function render()
    {
        return view('domains.platform.livewire.platform-settings')
            ->layout('layouts.app', ['title' => 'Pengaturan Platform']);
    }
}
