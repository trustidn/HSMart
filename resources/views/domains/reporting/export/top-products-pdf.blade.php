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
    <p class="meta">{{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} – {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}</p>
    <table>
        <thead>
            <tr>
                <th>{{ 'Produk' }}</th>
                <th>{{ 'SKU' }}</th>
                <th class="num">{{ 'Jumlah terjual' }}</th>
                <th class="num">{{ 'Pendapatan' }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>{{ $row->product_name ?? '—' }}</td>
                    <td>{{ $row->product_sku ?? '—' }}</td>
                    <td class="num">{{ number_format($row->total_qty ?? 0, 0, ',', '.') }}</td>
                    <td class="num">{{ number_format($row->total_revenue ?? 0, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">{{ 'Tidak ada penjualan dalam periode ini.' }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
