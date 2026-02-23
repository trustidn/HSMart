<?php

namespace App\Domains\Purchasing\Services;

use App\Domains\Product\Models\Product;
use App\Domains\Purchasing\Events\PurchaseCompleted;
use App\Domains\Purchasing\Models\Purchase;
use App\Domains\Purchasing\Models\PurchaseItem;
use App\Domains\Purchasing\Models\Supplier;
use Illuminate\Support\Facades\DB;

class PurchaseService
{
    /**
     * Create a purchase with items. Dispatches PurchaseCompleted (listeners add stock + journal).
     *
     * @param  array<int, array{product_id: int, qty: int, unit_cost: float}>  $items
     */
    public function createPurchase(int $supplierId, array $items, ?\DateTimeInterface $purchaseDate = null): Purchase
    {
        $purchaseDate = $purchaseDate ?? now();
        $dateString = $purchaseDate instanceof \DateTimeInterface
            ? $purchaseDate->format('Y-m-d')
            : $purchaseDate;

        return DB::transaction(function () use ($supplierId, $items, $dateString) {
            $supplier = Supplier::find($supplierId);
            if (! $supplier) {
                throw new \InvalidArgumentException(__('Supplier not found.'));
            }

            $purchase = Purchase::create([
                'tenant_id' => tenant()->id,
                'supplier_id' => $supplier->id,
                'purchase_number' => $this->generatePurchaseNumber(),
                'purchase_date' => $dateString,
                'total_amount' => 0,
                'status' => 'completed',
            ]);

            $total = 0.0;
            foreach ($items as $item) {
                $product = Product::find($item['product_id']);
                if (! $product) {
                    throw new \InvalidArgumentException("Product not found: {$item['product_id']}");
                }
                $qty = (int) $item['qty'];
                $unitCost = (float) $item['unit_cost'];
                $subtotal = round($qty * $unitCost, 2);
                $total += $subtotal;

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $product->id,
                    'qty' => $qty,
                    'unit_cost' => $unitCost,
                    'subtotal' => $subtotal,
                ]);
            }

            $purchase->update(['total_amount' => round($total, 2)]);

            event(new PurchaseCompleted($purchase));

            return $purchase->fresh();
        });
    }

    private function generatePurchaseNumber(): string
    {
        $count = Purchase::where('tenant_id', tenant()->id)->count();

        return 'PO-'.str_pad((string) ($count + 1), 6, '0', STR_PAD_LEFT);
    }
}
