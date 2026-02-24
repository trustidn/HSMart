<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>{{ $title ?? config('app.name') }}</title>

@php
    $faviconUrl = null;
    if (tenant()?->setting?->logo_path) {
        $faviconUrl = \Illuminate\Support\Facades\Storage::disk('public')->url(tenant()->setting->logo_path);
    }
    $faviconUrl = $faviconUrl ?: asset('favicon-default.svg');
@endphp
<link rel="icon" href="{{ $faviconUrl }}">
<link rel="apple-touch-icon" href="{{ $faviconUrl }}">

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700&display=swap" rel="stylesheet" />

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
