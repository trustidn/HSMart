<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        h1 { font-size: 16px; margin-bottom: 4px; }
        .meta { color: #666; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }
        th { background: #f5f5f5; font-weight: bold; }
        td.num { text-align: right; }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <p class="meta">{{ __('As of') }} {{ $dateLabel }}@if($lowStockOnly) ({{ __('Low stock only') }})@endif</p>
    <table>
        <thead>
            <tr>
                <th>{{ __('SKU') }}</th>
                <th>{{ __('Name') }}</th>
                <th class="num">{{ __('Stock') }}</th>
                <th class="num">{{ __('Min. stock') }}</th>
                <th>{{ __('Status') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($products as $product)
                <tr>
                    <td>{{ $product->sku }}</td>
                    <td>{{ $product->name }}</td>
                    <td class="num">{{ $product->stock }}</td>
                    <td class="num">{{ $product->minimum_stock }}</td>
                    <td>{{ $product->isLowStock() ? __('Low stock') : __('OK') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">{{ $lowStockOnly ? __('No products with low stock.') : __('No products.') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
