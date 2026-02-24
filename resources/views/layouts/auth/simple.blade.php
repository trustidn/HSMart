@php
    $platformSetting = $platformSetting ?? \App\Domains\Platform\Models\PlatformSetting::current();
    $appName = $platformSetting->app_name;
    $logoUrl = $platformSetting->logoUrl();
    $primary = $platformSetting->primary_color ?? '#0f766e';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
        <style>
            .auth-accent { --auth-primary: {{ $primary }}; }
            .auth-btn-primary { background-color: {{ $primary }} !important; }
            .auth-btn-primary:hover { opacity: 0.9; }
        </style>
    </head>
    <body class="auth-accent min-h-screen bg-zinc-50 dark:bg-zinc-950 antialiased flex flex-col">
        <div class="flex min-h-svh flex-col items-center justify-center gap-8 p-6 md:p-10">
            <div class="flex w-full max-w-sm flex-col gap-6">
                <a href="{{ route('home') }}" class="flex flex-col items-center gap-3 font-medium no-underline" wire:navigate>
                    @if($logoUrl)
                        <img src="{{ $logoUrl }}" alt="{{ $appName }}" class="h-12 w-auto object-contain" />
                    @else
                        <span class="flex h-12 w-12 items-center justify-center rounded-xl text-lg font-semibold text-white" style="background-color: {{ $primary }}">{{ strtoupper(mb_substr($appName, 0, 1)) }}</span>
                    @endif
                    <span class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ $appName }}</span>
                </a>
                <div class="flex flex-col gap-6 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-6 shadow-sm">
                    {{ $slot }}
                </div>
            </div>
        </div>
        @fluxScripts
    </body>
</html>
