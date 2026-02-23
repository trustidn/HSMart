<?php

namespace App\Domains\POS\Livewire;

use App\Domains\POS\Models\Payment;
use App\Domains\POS\Services\SaleService;
use App\Domains\Product\Models\Product;
use Livewire\Component;

class PosPage extends Component
{
    /** @var array<int, array{product_id: int, name: string, sku: string, qty: int, unit_price: float, subtotal: float}> */
    public array $cart = [];

    public string $barcodeInput = '';

    public string $customerName = '';

    public string $paymentMethod = Payment::METHOD_CASH;

    public function addByBarcode(): void
    {
        $code = trim($this->barcodeInput);
        if ($code === '') {
            return;
        }

        $product = Product::where('barcode', $code)->orWhere('sku', $code)->first();
        if (! $product) {
            $this->addError('barcodeInput', __('Product not found'));

            return;
        }
        if (! $product->is_active) {
            $this->addError('barcodeInput', __('Product is inactive'));

            return;
        }

        $this->addToCart($product, 1);
        $this->barcodeInput = '';
        $this->resetErrorBag('barcodeInput');
    }

    public function addToCart(Product $product, int $qty = 1): void
    {
        foreach ($this->cart as $i => $row) {
            if ((int) $row['product_id'] === $product->id) {
                $newQty = (int) $row['qty'] + $qty;
                $this->cart[$i]['qty'] = $newQty;
                $this->cart[$i]['subtotal'] = round($newQty * (float) $row['unit_price'], 2);

                return;
            }
        }

        $this->cart[] = [
            'product_id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'qty' => $qty,
            'unit_price' => (float) $product->sell_price,
            'subtotal' => round($qty * (float) $product->sell_price, 2),
        ];
    }

    public function updateCartQty(int $index, int $qty): void
    {
        if (! isset($this->cart[$index])) {
            return;
        }
        if ($qty <= 0) {
            unset($this->cart[$index]);
            $this->cart = array_values($this->cart);

            return;
        }
        $this->cart[$index]['qty'] = $qty;
        $this->cart[$index]['subtotal'] = round($qty * (float) $this->cart[$index]['unit_price'], 2);
    }

    public function removeFromCart(int $index): void
    {
        if (isset($this->cart[$index])) {
            unset($this->cart[$index]);
            $this->cart = array_values($this->cart);
        }
    }

    public function updatedCart($value, string $key): void
    {
        if (preg_match('/^(\d+)\.qty$/', $key, $m)) {
            $index = (int) $m[1];
            if (! isset($this->cart[$index])) {
                return;
            }
            $qty = (int) $value;
            if ($qty <= 0) {
                $this->removeFromCart($index);

                return;
            }
            $this->cart[$index]['qty'] = $qty;
            $this->cart[$index]['subtotal'] = round($qty * (float) $this->cart[$index]['unit_price'], 2);
        }
    }

    public function getTotalProperty(): float
    {
        return round(array_sum(array_column($this->cart, 'subtotal')), 2);
    }

    public function checkout(): void
    {
        if ($this->cart === []) {
            $this->addError('cart', __('Cart is empty'));

            return;
        }

        $items = [];
        foreach ($this->cart as $row) {
            $items[] = [
                'product_id' => (int) $row['product_id'],
                'qty' => (int) $row['qty'],
                'unit_price' => (float) $row['unit_price'],
            ];
        }

        try {
            app(SaleService::class)->createSale(
                customerName: $this->customerName ?: null,
                items: $items,
                amount: $this->total,
                paymentMethod: $this->paymentMethod
            );
            $this->cart = [];
            $this->customerName = '';
            session()->flash('sale-completed', true);
            $this->dispatch('sale-completed');
        } catch (\DomainException|\InvalidArgumentException $e) {
            $this->addError('checkout', $e->getMessage());
        }
    }

    public function render()
    {
        return view('domains.pos.livewire.pos-page');
    }
}
