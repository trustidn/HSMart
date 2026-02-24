<?php

namespace App\Domains\Platform\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class PlatformSetting extends Model
{
    protected $fillable = [
        'app_name',
        'logo_path',
        'primary_color',
        'secondary_color',
    ];

    /**
     * Get the current platform settings (singleton row). Creates default if none.
     */
    public static function current(): self
    {
        return Cache::remember('platform_setting', 3600, function () {
            return self::query()->firstOrCreate([], [
                'app_name' => config('app.name', 'HSMart'),
                'primary_color' => '#0f766e',
                'secondary_color' => '#134e4a',
            ]);
        });
    }

    /**
     * Clear cached platform setting (call after update).
     */
    public static function clearCache(): void
    {
        Cache::forget('platform_setting');
    }

    public function logoUrl(): ?string
    {
        if (! $this->logo_path) {
            return null;
        }

        return Storage::disk('public')->exists($this->logo_path)
            ? Storage::disk('public')->url($this->logo_path)
            : null;
    }
}
