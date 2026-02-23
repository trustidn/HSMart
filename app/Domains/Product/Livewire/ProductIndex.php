<?php

namespace App\Domains\Product\Livewire;

use App\Domains\Product\Models\Product;
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
        $query = Product::query()
            ->orderBy('name');

        if ($this->search !== '') {
            $term = '%'.$this->search.'%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('sku', 'like', $term)
                    ->orWhere('barcode', 'like', $term);
            });
        }

        return $query->paginate(10);
    }

    public function openAdjustStock(int $productId): void
    {
        $this->dispatch('open-stock-adjustment', productId: $productId);
    }

    #[On('stock-adjusted')]
    public function onStockAdjusted(): void
    {
        // Force re-render so products list refreshes.
    }

    public function render()
    {
        return view('domains.product.livewire.product-index')
            ->layout('layouts.app', ['title' => __('Products')]);
    }
}
