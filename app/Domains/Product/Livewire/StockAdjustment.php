<?php

namespace App\Domains\Product\Livewire;

use App\Domains\Product\Models\Product;
use App\Domains\Product\Services\ProductService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class StockAdjustment extends Component
{
    public ?int $productId = null;

    public string $newQuantity = '0';

    public bool $showModal = false;

    #[On('open-stock-adjustment')]
    public function openModal(int $productId): void
    {
        $product = app(ProductService::class)->find($productId);
        if ($product === null) {
            return;
        }
        $this->productId = $productId;
        $this->newQuantity = (string) $product->stock;
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->productId = null;
        $this->newQuantity = '0';
        $this->resetValidation();
    }

    public function adjust(): void
    {
        $this->validate([
            'newQuantity' => ['required', 'integer', 'min:0'],
        ]);

        $product = app(ProductService::class)->find($this->productId);
        if ($product === null) {
            $this->closeModal();

            return;
        }

        try {
            app(ProductService::class)->adjustStock($product, (int) $this->newQuantity);
            $this->closeModal();
            $this->dispatch('stock-adjusted');
        } catch (\InvalidArgumentException $e) {
            $this->addError('newQuantity', $e->getMessage());
        }
    }

    #[Computed]
    public function product(): ?Product
    {
        if ($this->productId === null) {
            return null;
        }

        return app(ProductService::class)->find($this->productId);
    }

    public function render()
    {
        return view('domains.product.livewire.stock-adjustment');
    }
}
