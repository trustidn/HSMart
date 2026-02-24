<?php

namespace App\Domains\Reporting\Http;

use App\Domains\Reporting\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportController
{
    public function __construct(
        private ReportService $reportService
    ) {}

    public function salesPdf(Request $request): Response
    {
        $from = $request->input('date_from', now()->startOfMonth()->format('Y-m-d'));
        $to = $request->input('date_to', now()->format('Y-m-d'));

        $summary = $this->reportService->getRingkasanOmzet($from, $to);
        $sales = $this->reportService->getLaporanPenjualanForExport($from, $to);

        $pdf = Pdf::loadView('domains.reporting.export.sales-pdf', [
            'title' => __('Sales report'),
            'tenantName' => tenant()?->name ?? '',
            'dateFrom' => $from,
            'dateTo' => $to,
            'total' => $summary['total'],
            'count' => $summary['count'],
            'sales' => $sales,
        ]);

        $filename = 'sales-report-'.Carbon::parse($from)->format('Y-m-d').'-'.Carbon::parse($to)->format('Y-m-d').'.pdf';

        return $pdf->download($filename);
    }

    public function salesExcel(Request $request): StreamedResponse
    {
        $from = $request->input('date_from', now()->startOfMonth()->format('Y-m-d'));
        $to = $request->input('date_to', now()->format('Y-m-d'));

        $summary = $this->reportService->getRingkasanOmzet($from, $to);
        $sales = $this->reportService->getLaporanPenjualanForExport($from, $to);

        $filename = 'sales-report-'.Carbon::parse($from)->format('Y-m-d').'-'.Carbon::parse($to)->format('Y-m-d').'.csv';

        $tenantName = tenant()?->name ?? '';

        return response()->streamDownload(function () use ($tenantName, $summary, $sales) {
            $out = fopen('php://output', 'w');
            $delim = ';';
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel
            fputcsv($out, [__('Sales report')], $delim);
            fputcsv($out, [__('Tenant'), $tenantName], $delim);
            fputcsv($out, [__('Period'), $summary['from'].' – '.$summary['to']], $delim);
            fputcsv($out, [__('Total'), (string) $summary['total'], __('transactions'), (string) $summary['count']], $delim);
            fputcsv($out, [], $delim);
            fputcsv($out, [__('Invoice'), __('Date'), __('Customer'), __('Total')], $delim);
            foreach ($sales as $sale) {
                fputcsv($out, [
                    $sale->sale_number,
                    $sale->sale_date?->format('Y-m-d') ?? '',
                    $sale->customer_name ?? '',
                    (string) $sale->total_amount,
                ], $delim);
            }
            fputcsv($out, [], $delim);
            fputcsv($out, [__('Total'), '', '', (string) $summary['total']], $delim);
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function topProductsPdf(Request $request): Response
    {
        $from = $request->input('date_from', now()->startOfMonth()->format('Y-m-d'));
        $to = $request->input('date_to', now()->format('Y-m-d'));

        $rows = $this->reportService->getTopProduk($from, $to, 500);

        $pdf = Pdf::loadView('domains.reporting.export.top-products-pdf', [
            'title' => __('Top products'),
            'tenantName' => tenant()?->name ?? '',
            'dateFrom' => $from,
            'dateTo' => $to,
            'rows' => $rows,
        ]);

        $filename = 'top-products-'.Carbon::parse($from)->format('Y-m-d').'-'.Carbon::parse($to)->format('Y-m-d').'.pdf';

        return $pdf->download($filename);
    }

    public function topProductsExcel(Request $request): StreamedResponse
    {
        $from = $request->input('date_from', now()->startOfMonth()->format('Y-m-d'));
        $to = $request->input('date_to', now()->format('Y-m-d'));

        $rows = $this->reportService->getTopProduk($from, $to, 500);

        $filename = 'top-products-'.Carbon::parse($from)->format('Y-m-d').'-'.Carbon::parse($to)->format('Y-m-d').'.csv';

        $tenantName = tenant()?->name ?? '';

        return response()->streamDownload(function () use ($tenantName, $from, $to, $rows) {
            $out = fopen('php://output', 'w');
            $delim = ';';
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, [__('Top products')], $delim);
            fputcsv($out, [__('Tenant'), $tenantName], $delim);
            fputcsv($out, [__('Period'), $from.' – '.$to], $delim);
            fputcsv($out, [], $delim);
            fputcsv($out, [__('Product'), __('SKU'), __('Qty sold'), __('Revenue')], $delim);
            foreach ($rows as $row) {
                fputcsv($out, [
                    $row->product_name ?? '',
                    $row->product_sku ?? '',
                    (string) ($row->total_qty ?? 0),
                    (string) ($row->total_revenue ?? 0),
                ], $delim);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function stockPdf(Request $request): Response
    {
        $lowStockOnly = $request->boolean('low_stock_only');
        $products = $this->reportService->getLaporanStok($lowStockOnly);

        $pdf = Pdf::loadView('domains.reporting.export.stock-pdf', [
            'title' => __('Stock report'),
            'tenantName' => tenant()?->name ?? '',
            'dateLabel' => now()->format('d/m/Y'),
            'lowStockOnly' => $lowStockOnly,
            'products' => $products,
        ]);

        $filename = 'stock-report-'.now()->format('Y-m-d').'.pdf';

        return $pdf->download($filename);
    }

    public function stockExcel(Request $request): StreamedResponse
    {
        $lowStockOnly = $request->boolean('low_stock_only');
        $products = $this->reportService->getLaporanStok($lowStockOnly);

        $filename = 'stock-report-'.now()->format('Y-m-d').'.csv';

        $tenantName = tenant()?->name ?? '';

        return response()->streamDownload(function () use ($tenantName, $products) {
            $out = fopen('php://output', 'w');
            $delim = ';';
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, [__('Stock report')], $delim);
            fputcsv($out, [__('Tenant'), $tenantName], $delim);
            fputcsv($out, [__('Date'), now()->format('Y-m-d')], $delim);
            fputcsv($out, [], $delim);
            fputcsv($out, [__('SKU'), __('Name'), __('Stock'), __('Min. stock'), __('Status')], $delim);
            foreach ($products as $p) {
                fputcsv($out, [
                    $p->sku,
                    $p->name,
                    (string) $p->stock,
                    (string) $p->minimum_stock,
                    $p->isLowStock() ? __('Low stock') : __('OK'),
                ], $delim);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}
