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
            .welcome-accent { --welcome-primary: {{ $primary }}; }
            .welcome-bg {
                background-color: #0c0c0c;
                background-image:
                    radial-gradient(ellipse 80% 50% at 50% -20%, color-mix(in srgb, var(--welcome-primary) 25%, transparent), transparent),
                    radial-gradient(ellipse 60% 40% at 100% 50%, color-mix(in srgb, var(--welcome-primary) 12%, transparent), transparent),
                    radial-gradient(ellipse 60% 40% at 0% 80%, color-mix(in srgb, var(--welcome-primary) 8%, transparent), transparent);
            }
            .welcome-bg-grid {
                background-image: linear-gradient(rgba(255,255,255,.03) 1px, transparent 1px),
                    linear-gradient(90deg, rgba(255,255,255,.03) 1px, transparent 1px);
                background-size: 64px 64px;
            }
            .welcome-orb {
                position: absolute;
                border-radius: 50%;
                filter: blur(80px);
                opacity: 0.4;
                animation: welcome-float 20s ease-in-out infinite;
            }
            .welcome-orb-1 { width: 400px; height: 400px; top: -100px; left: -100px; background: var(--welcome-primary); animation-delay: 0s; }
            .welcome-orb-2 { width: 300px; height: 300px; bottom: -50px; right: -50px; background: var(--welcome-primary); animation-delay: -7s; }
            .welcome-orb-3 { width: 200px; height: 200px; top: 50%; left: 10%; background: var(--welcome-primary); animation-delay: -14s; }
            @keyframes welcome-float {
                0%, 100% { transform: translate(0, 0) scale(1); }
                33% { transform: translate(30px, -20px) scale(1.05); }
                66% { transform: translate(-20px, 20px) scale(0.95); }
            }
        </style>
    </head>
    <body class="welcome-accent min-h-screen text-zinc-100 antialiased flex flex-col overflow-x-hidden">
        <div class="welcome-bg welcome-bg-grid fixed inset-0 -z-10">
            <span class="welcome-orb welcome-orb-1" style="background: {{ $primary }};"></span>
            <span class="welcome-orb welcome-orb-2" style="background: {{ $primary }};"></span>
            <span class="welcome-orb welcome-orb-3" style="background: {{ $primary }};"></span>
        </div>

        <header class="flex w-full items-center justify-end gap-4 p-6 sm:p-8">
            @auth
                <a href="{{ url('/dashboard') }}" class="rounded-lg px-4 py-2 text-sm font-medium transition hover:opacity-90 text-white" style="background-color: {{ $primary }}">
                    Dasbor
                </a>
            @else
                <a href="{{ route('login') }}" class="text-sm font-medium text-zinc-400 transition hover:text-white" wire:navigate>Masuk</a>
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="rounded-lg px-4 py-2 text-sm font-medium text-white transition hover:opacity-90" style="background-color: {{ $primary }}" wire:navigate>Daftar</a>
                @endif
            @endauth
        </header>

        <main class="flex flex-1 flex-col items-center justify-center px-6 pb-20 pt-4 sm:px-10">
            <div class="w-full max-w-2xl flex flex-col items-center gap-10 text-center">
                <a href="{{ route('home') }}" class="flex flex-col items-center gap-5 no-underline">
                    @if($logoUrl)
                        <img src="{{ $logoUrl }}" alt="{{ $appName }}" class="h-20 w-auto object-contain drop-shadow-lg" />
                    @else
                        <span class="flex h-24 w-24 items-center justify-center rounded-2xl text-4xl font-semibold text-white shadow-lg" style="background-color: {{ $primary }}">{{ strtoupper(mb_substr($appName, 0, 1)) }}</span>
                    @endif
                    <span class="text-3xl sm:text-4xl font-bold tracking-tight text-white">{{ $appName }}</span>
                </a>

                <p class="text-lg text-zinc-400 max-w-md">
                    Sistem manajemen toko dan kasir untuk operasional harian. Kelola produk, transaksi, laporan, dan langganan dalam satu platform.
                </p>

                <ul class="grid grid-cols-2 gap-4 sm:grid-cols-4 w-full max-w-xl text-left sm:text-center">
                    <li class="flex flex-col items-center gap-2 rounded-xl border border-white/5 bg-white/5 px-4 py-4 backdrop-blur-sm">
                        <span class="flex h-10 w-10 items-center justify-center rounded-lg text-white text-lg font-bold" style="background-color: {{ $primary }}">K</span>
                        <span class="text-sm font-medium text-zinc-300">Kasir & POS</span>
                        <span class="text-xs text-zinc-500">Transaksi cepat</span>
                    </li>
                    <li class="flex flex-col items-center gap-2 rounded-xl border border-white/5 bg-white/5 px-4 py-4 backdrop-blur-sm">
                        <span class="flex h-10 w-10 items-center justify-center rounded-lg text-white text-lg font-bold" style="background-color: {{ $primary }}">P</span>
                        <span class="text-sm font-medium text-zinc-300">Produk & Stok</span>
                        <span class="text-xs text-zinc-500">Kelola inventori</span>
                    </li>
                    <li class="flex flex-col items-center gap-2 rounded-xl border border-white/5 bg-white/5 px-4 py-4 backdrop-blur-sm">
                        <span class="flex h-10 w-10 items-center justify-center rounded-lg text-white text-lg font-bold" style="background-color: {{ $primary }}">L</span>
                        <span class="text-sm font-medium text-zinc-300">Laporan</span>
                        <span class="text-xs text-zinc-500">Penjualan & stok</span>
                    </li>
                    <li class="flex flex-col items-center gap-2 rounded-xl border border-white/5 bg-white/5 px-4 py-4 backdrop-blur-sm">
                        <span class="flex h-10 w-10 items-center justify-center rounded-lg text-white text-lg font-bold" style="background-color: {{ $primary }}">S</span>
                        <span class="text-sm font-medium text-zinc-300">Langganan</span>
                        <span class="text-xs text-zinc-500">Multi-tenant</span>
                    </li>
                </ul>

                <div class="flex w-full flex-col gap-3 sm:flex-row sm:justify-center sm:gap-4">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="inline-flex justify-center rounded-xl px-6 py-3 text-sm font-semibold text-white shadow-lg transition hover:opacity-90" style="background-color: {{ $primary }}">
                            Buka Dasbor
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="inline-flex justify-center rounded-xl px-6 py-3 text-sm font-semibold text-white shadow-lg transition hover:opacity-90" style="background-color: {{ $primary }}" wire:navigate>
                            Masuk
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="inline-flex justify-center rounded-xl border border-white/20 bg-white/5 px-6 py-3 text-sm font-semibold text-white backdrop-blur-sm transition hover:bg-white/10" wire:navigate>
                                Daftar Akun
                            </a>
                        @endif
                    @endauth
                </div>
            </div>
        </main>

        <footer class="py-6 text-center text-xs text-zinc-500">
            {{ $appName }} — Manajemen toko & kasir
        </footer>
        @fluxScripts
    </body>
</html>
