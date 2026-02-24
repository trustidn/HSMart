<?php

namespace App\Domains\Product\Livewire;

use App\Domains\Product\Services\ProductService;
use Illuminate\Support\Facades\Route;
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

    public function render()
    {
        return view('domains.product.livewire.product-detail')
            ->layout('layouts.app', ['title' => $this->product?->name ?? __('Product')]);
    }
}
