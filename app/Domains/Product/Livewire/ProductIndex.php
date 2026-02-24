<?php

namespace App\Domains\Product\Livewire;

use App\Domains\Product\Services\ProductService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class ProductIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public function mount(): void
    {
        //
    }

    #[Computed]
    public function products()
    {
        return app(ProductService::class)->listForCurrentTenant($this->search, 10);
    }

    public function openAdjustStock(int $productId): void
    {
        $this->dispatch('open-stock-adjustment', productId: $productId)->to(StockAdjustment::class);
    }

    #[On('stock-adjusted')]
    public function onStockAdjusted(): void
    {
        // Force re-render so products list refreshes.
    }

    public function render()
    {
        return view('domains.product.livewire.product-index')
            ->layout('layouts.app', ['title' => 'Produk']);
    }
}
