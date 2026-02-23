<?php

namespace App\Domains\Product\Livewire;

use App\Domains\Product\Models\Product;
use App\Domains\Product\Services\ProductService;
use Livewire\Component;

class ProductForm extends Component
{
    public ?int $productId = null;

    public string $sku = '';

    public string $barcode = '';

    public string $name = '';

    public string $cost_price = '0';

    public string $sell_price = '0';

    public string $minimum_stock = '0';

    public bool $is_active = true;

    public function mount(?int $productId = null): void
    {
        if ($productId !== null) {
            $product = app(ProductService::class)->find($productId);
            if ($product === null) {
                abort(404);
            }
            $this->productId = $product->id;
            $this->sku = $product->sku;
            $this->barcode = $product->barcode ?? '';
            $this->name = $product->name;
            $this->cost_price = (string) $product->cost_price;
            $this->sell_price = (string) $product->sell_price;
            $this->minimum_stock = (string) $product->minimum_stock;
            $this->is_active = $product->is_active;
        }
    }

    public function save(): void
    {
        $validated = $this->validate([
            'sku' => ['required', 'string', 'max:255'],
            'barcode' => ['nullable', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'sell_price' => ['required', 'numeric', 'min:0'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        $validated['is_active'] = $this->is_active;

        $service = app(ProductService::class);

        try {
            if ($this->productId !== null) {
                $product = Product::findOrFail($this->productId);
                $service->update($product, $validated);
                $this->redirectRoute('products.index', navigate: true);
            } else {
                $service->create($validated);
                $this->redirectRoute('products.index', navigate: true);
            }
        } catch (\InvalidArgumentException $e) {
            $this->addError('sku', $e->getMessage());
        }
    }

    public function render()
    {
        $title = $this->productId ? __('Edit Product') : __('New Product');

        return view('domains.product.livewire.product-form')
            ->layout('layouts.app', ['title' => $title]);
    }
}
