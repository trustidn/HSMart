<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        h1 { font-size: 16px; margin-bottom: 4px; }
        .meta { color: #666; margin-bottom: 16px; }
        .total { font-size: 14px; font-weight: bold; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }
        th { background: #f5f5f5; font-weight: bold; }
        td.num { text-align: right; }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <p class="meta">{{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} – {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}</p>
    <p class="total">{{ __('Total') }}: {{ number_format($total, 0, ',', '.') }} ({{ $count }} {{ __('transactions') }})</p>
    <table>
        <thead>
            <tr>
                <th>{{ __('Invoice') }}</th>
                <th>{{ __('Date') }}</th>
                <th>{{ __('Customer') }}</th>
                <th class="num">{{ __('Total') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($sales as $sale)
                <tr>
                    <td>{{ $sale->sale_number }}</td>
                    <td>{{ $sale->sale_date?->format('d/m/Y') ?? '—' }}</td>
                    <td>{{ $sale->customer_name ?? '—' }}</td>
                    <td class="num">{{ number_format($sale->total_amount, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">{{ __('No sales in this period.') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
