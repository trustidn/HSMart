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
    @if(!empty($tenantName))
        <p class="meta">{{ 'Tenant' }}: {{ $tenantName }}</p>
    @endif
    <p class="meta">{{ 'Per' }} {{ $dateLabel }}@if($lowStockOnly) ({{ 'Hanya stok rendah' }})@endif</p>
    <table>
        <thead>
            <tr>
                <th>{{ 'SKU' }}</th>
                <th>{{ 'Nama' }}</th>
                <th class="num">{{ 'Stok' }}</th>
                <th class="num">{{ 'Stok min.' }}</th>
                <th>{{ 'Status' }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($products as $product)
                <tr>
                    <td>{{ $product->sku }}</td>
                    <td>{{ $product->name }}</td>
                    <td class="num">{{ $product->stock }}</td>
                    <td class="num">{{ $product->minimum_stock }}</td>
                    <td>{{ $product->isLowStock() ? 'Stok rendah' : 'OK' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">{{ $lowStockOnly ? 'Tidak ada produk dengan stok rendah.' : 'Tidak ada produk.' }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
