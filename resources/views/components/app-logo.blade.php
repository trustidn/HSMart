@props([
    'sidebar' => false,
])

@php
    $tenant = tenant();
    if ($tenant?->setting) {
        $brandName = $tenant->setting->store_name ?: $tenant->name;
        $logoUrl = $tenant->setting->logo_path
            ? \Illuminate\Support\Facades\Storage::disk('public')->url($tenant->setting->logo_path)
            : null;
    } else {
        $platform = \App\Domains\Platform\Models\PlatformSetting::current();
        $brandName = $platform->app_name ?: config('app.name', 'HSMart');
        $logoUrl = $platform->logoUrl();
    }
@endphp

@if($sidebar)
    <flux:sidebar.brand name="{{ $brandName }}" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center overflow-hidden rounded-md bg-accent-content text-accent-foreground">
            @if($logoUrl)
                <img src="{{ $logoUrl }}" alt="" class="size-full object-contain" />
            @else
                <x-app-logo-icon class="size-5 fill-current text-white dark:text-black" />
            @endif
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand name="{{ $brandName }}" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center overflow-hidden rounded-md bg-accent-content text-accent-foreground">
            @if($logoUrl)
                <img src="{{ $logoUrl }}" alt="" class="size-full object-contain" />
            @else
                <x-app-logo-icon class="size-5 fill-current text-white dark:text-black" />
            @endif
        </x-slot>
    </flux:brand>
@endif
