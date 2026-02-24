<?php

namespace App\Domains\Product\Livewire;

use App\Domains\Product\Services\ProductService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class ProductForm extends Component
{
    use WithFileUploads;

    public ?int $productId = null;

    public string $sku = '';

    public string $barcode = '';

    public string $name = '';

    public string $sell_price = '0';

    public string $minimum_stock = '0';

    public bool $is_active = true;

    /** @var \Illuminate\Http\UploadedFile|null */
    public $photo = null;

    public function mount(?int $productId = null): void
    {
        $id = $productId ?? Route::current()?->parameter('productId');
        if ($id !== null) {
            $product = app(ProductService::class)->find((int) $id);
            if ($product === null) {
                abort(404);
            }
            $this->productId = $product->id;
            $this->sku = $product->sku;
            $this->barcode = $product->barcode ?? '';
            $this->name = $product->name;
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
            'sell_price' => ['required', 'numeric', 'min:0'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ]);

        $validated['is_active'] = $this->is_active;

        if ($this->photo) {
            if ($this->productId !== null) {
                $existing = app(ProductService::class)->find($this->productId);
                if ($existing?->image_path && Storage::disk('public')->exists($existing->image_path)) {
                    Storage::disk('public')->delete($existing->image_path);
                }
            }
            $validated['image_path'] = $this->photo->store('products', 'public');
        }

        $service = app(ProductService::class);

        try {
            if ($this->productId !== null) {
                $product = $service->find($this->productId);
                if ($product === null) {
                    $this->addError('sku', 'Produk tidak ditemukan. Mungkin sudah dihapus atau Anda tidak punya akses.');

                    return;
                }
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

    public function getProductProperty(): ?\App\Domains\Product\Models\Product
    {
        if ($this->productId === null) {
            return null;
        }

        return app(ProductService::class)->find($this->productId);
    }

    public function render()
    {
        $title = $this->productId ? 'Ubah Produk' : 'Produk Baru';

        return view('domains.product.livewire.product-form')
            ->layout('layouts.app', ['title' => $title]);
    }
}
