<?php

namespace App\Domains\Product\Livewire;

use App\Domains\Product\Services\ProductService;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class ProductDetail extends Component
{
    public int $productId;

    public function mount(int $productId): void
    {
        $id = $productId ?? Route::current()?->parameter('productId');
        $product = app(ProductService::class)->find((int) $id);
        if ($product === null) {
            abort(404);
        }
        $this->productId = $product->id;
    }

    public function getProductProperty(): ?\App\Domains\Product\Models\Product
    {
        return app(ProductService::class)->find($this->productId);
    }

    /**
     * Sales stats for this product: total quantity sold and total sales value.
     *
     * @return array{total_qty: int, total_value: float}
     */
    #[Computed]
    public function salesStats(): array
    {
        $product = $this->product;
        if ($product === null) {
            return ['total_qty' => 0, 'total_value' => 0.0];
        }

        $row = $product->saleItems()
            ->selectRaw('COALESCE(SUM(qty), 0) as total_qty, COALESCE(SUM(subtotal), 0) as total_value')
            ->first();

        return [
            'total_qty' => (int) ($row->total_qty ?? 0),
            'total_value' => (float) ($row->total_value ?? 0),
        ];
    }

    /**
     * Purchase items for this product (with purchase loaded for date).
     */
    public function getPurchaseItemsProperty()
    {
        $product = $this->product;
        if ($product === null) {
            return collect();
        }

        return $product->purchaseItems()
            ->with(['purchase' => fn ($q) => $q->withoutGlobalScopes()->select('id', 'purchase_number', 'purchase_date', 'tenant_id')])
            ->orderByDesc('id')
            ->limit(50)
            ->get();
    }

    public function openAdjustStock(int $productId): void
    {
        $this->dispatch('open-stock-adjustment', productId: $productId)->to(StockAdjustment::class);
    }

    #[On('stock-adjusted')]
    public function onStockAdjusted(): void
    {
        // Re-render so product stock and stats stay in sync.
    }

    public function render()
    {
        return view('domains.product.livewire.product-detail')
            ->layout('layouts.app', ['title' => $this->product?->name ?? __('Product')]);
    }
}
