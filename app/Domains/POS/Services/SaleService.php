<?php

namespace App\Domains\POS\Services;

use App\Domains\POS\Events\SaleCompleted;
use App\Domains\POS\Models\Payment;
use App\Domains\POS\Models\Sale;
use App\Domains\POS\Models\SaleItem;
use App\Domains\Product\Models\Product;
use App\Domains\Subscription\Services\SubscriptionService;
use Illuminate\Support\Facades\DB;

class SaleService
{
    /**
     * Create a new sale with items and payment. Validates stock; dispatches SaleCompleted (listener deducts stock).
     *
     * @param  array<int, array{product_id: int, qty: int, unit_price: float}>  $items
     */
    public function createSale(?string $customerName, array $items, float $amount, string $paymentMethod = Payment::METHOD_CASH, ?string $paymentReference = null): Sale
    {
        if (! app(SubscriptionService::class)->canCreateSale(tenant())) {
            throw new \DomainException(__('Subscription has expired. You cannot create new sales.'));
        }
        $this->validateStock($items);

        return DB::transaction(function () use ($customerName, $items, $amount, $paymentMethod, $paymentReference) {
            $sale = Sale::create([
                'tenant_id' => tenant()->id,
                'sale_number' => $this->generateSaleNumber(),
                'sale_date' => now()->toDateString(),
                'customer_name' => $customerName,
                'total_amount' => 0,
                'status' => 'completed',
            ]);

            $total = 0.0;
            foreach ($items as $item) {
                $product = Product::find($item['product_id']);
                $qty = (int) $item['qty'];
                $unitPrice = (float) $item['unit_price'];
                $subtotal = round($qty * $unitPrice, 2);
                $total += $subtotal;

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'qty' => $qty,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ]);
            }

            $sale->update(['total_amount' => round($total, 2)]);

            Payment::create([
                'sale_id' => $sale->id,
                'amount' => round($amount, 2),
                'method' => $paymentMethod,
                'paid_at' => now(),
                'reference' => $paymentReference,
            ]);

            event(new SaleCompleted($sale));

            return $sale->fresh();
        });
    }

    /**
     * @param  array<int, array{product_id: int, qty: int}>  $items
     */
    private function validateStock(array $items): void
    {
        foreach ($items as $item) {
            $product = Product::find($item['product_id']);
            if (! $product) {
                throw new \InvalidArgumentException("Product not found: {$item['product_id']}");
            }
            if ($product->stock < (int) $item['qty']) {
                throw new \DomainException("Insufficient stock for {$product->name}. Available: {$product->stock}, requested: {$item['qty']}");
            }
        }
    }

    private function generateSaleNumber(): string
    {
        $count = Sale::where('tenant_id', tenant()->id)->count();

        return 'INV-'.str_pad((string) ($count + 1), 6, '0', STR_PAD_LEFT);
    }
}
