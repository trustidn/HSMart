<?php

namespace App\Domains\Reporting\Services;

use App\Domains\POS\Models\Sale;
use App\Domains\Product\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Ringkasan omzet: total penjualan dalam periode.
     *
     * @return array{total: float, count: int, from: string, to: string}
     */
    public function getRingkasanOmzet(string $from, string $to): array
    {
        $query = Sale::query()
            ->where('status', 'completed')
            ->whereBetween('sale_date', [$from, $to]);

        $total = (float) $query->clone()->sum('total_amount');
        $count = $query->clone()->count();

        return [
            'total' => round($total, 2),
            'count' => $count,
            'from' => $from,
            'to' => $to,
        ];
    }

    /**
     * Laporan penjualan: daftar penjualan dalam periode (paginated).
     *
     * @return LengthAwarePaginator<Sale>
     */
    public function getLaporanPenjualan(string $from, string $to, int $perPage = 15): LengthAwarePaginator
    {
        return Sale::query()
            ->with('items.product')
            ->where('status', 'completed')
            ->whereBetween('sale_date', [$from, $to])
            ->orderByDesc('sale_date')
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    /**
     * Semua penjualan dalam periode untuk export (tanpa pagination, max 5000).
     *
     * @return \Illuminate\Support\Collection<int, Sale>
     */
    public function getLaporanPenjualanForExport(string $from, string $to): Collection
    {
        return Sale::query()
            ->where('status', 'completed')
            ->whereBetween('sale_date', [$from, $to])
            ->orderByDesc('sale_date')
            ->orderByDesc('id')
            ->limit(5000)
            ->get();
    }

    /**
     * Top produk by quantity terjual dalam periode.
     *
     * @return Collection<int, object{product_id: int, product_name: string, product_sku: string, total_qty: int, total_revenue: float}>
     */
    public function getTopProduk(string $from, string $to, int $limit = 10): Collection
    {
        $tenant = tenant();
        if ($tenant === null) {
            return collect();
        }

        return DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->where('sales.tenant_id', $tenant->id)
            ->where('sales.status', 'completed')
            ->whereBetween('sales.sale_date', [$from, $to])
            ->selectRaw('
                products.id as product_id,
                products.name as product_name,
                products.sku as product_sku,
                SUM(sale_items.qty) as total_qty,
                SUM(sale_items.subtotal) as total_revenue
            ')
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderByDesc('total_qty')
            ->limit($limit)
            ->get()
            ->map(function ($row) {
                $row->total_qty = (int) $row->total_qty;
                $row->total_revenue = round((float) $row->total_revenue, 2);

                return $row;
            });
    }

    /**
     * Laporan stok: produk dengan stock saat ini (optional filter low stock only).
     *
     * @return Collection<int, Product>
     */
    public function getLaporanStok(bool $lowStockOnly = false): Collection
    {
        $query = Product::query()->orderBy('name');

        if ($lowStockOnly) {
            $query->whereRaw('minimum_stock > 0 AND stock <= minimum_stock');
        }

        return $query->get();
    }

    /**
     * Laba rugi sederhana: Revenue (penjualan) - COGS (qty * cost_price produk saat ini).
     *
     * @return array{from: string, to: string, revenue: float, cogs: float, gross_profit: float}
     */
    public function getLabaRugi(string $from, string $to): array
    {
        $sales = Sale::query()
            ->with('items.product')
            ->where('status', 'completed')
            ->whereBetween('sale_date', [$from, $to])
            ->get();

        $revenue = round((float) $sales->sum('total_amount'), 2);
        $cogs = 0.0;

        foreach ($sales as $sale) {
            foreach ($sale->items as $item) {
                $product = $item->product;
                if ($product) {
                    $cogs += $item->qty * (float) $product->cost_price;
                }
            }
        }
        $cogs = round($cogs, 2);
        $grossProfit = round($revenue - $cogs, 2);

        return [
            'from' => $from,
            'to' => $to,
            'revenue' => $revenue,
            'cogs' => $cogs,
            'gross_profit' => $grossProfit,
        ];
    }
}
