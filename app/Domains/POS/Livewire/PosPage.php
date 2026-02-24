<?php

namespace App\Domains\POS\Livewire;

use App\Domains\POS\Models\Payment;
use App\Domains\POS\Services\SaleService;
use App\Domains\Product\Models\Product;
use App\Domains\Tenant\Models\Tenant;
use Livewire\Component;

class PosPage extends Component
{
    /** @var array<int, array{product_id: int, name: string, sku: string, qty: int, unit_price: float, subtotal: float}> */
    public array $cart = [];

    public string $barcodeInput = '';

    public string $customerName = '';

    public string $paymentMethod = Payment::METHOD_CASH;

    public bool $showPaymentModal = false;

    public string $amountPaid = '';

    /** @var array{store_name: string, date: string, items: array<int, array{name: string, qty: int, unit_price: float, subtotal: float}>, total: float, amount_paid: float, change: float, receipt_footer: string}|null */
    public ?array $lastReceipt = null;

    /**
     * Products for the main grid (click to add to cart). Active products for current tenant.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Product>
     */
    public function getPosProductsProperty(): \Illuminate\Database\Eloquent\Collection
    {
        $tenant = $this->resolveTenant();
        if ($tenant === null) {
            return collect();
        }

        return Product::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->limit(200)
            ->get();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Product>
     */
    public function getProductSearchResultsProperty(): \Illuminate\Database\Eloquent\Collection
    {
        $query = trim($this->barcodeInput);
        if (strlen($query) < 1) {
            return collect();
        }

        $tenant = $this->resolveTenant();
        if ($tenant === null) {
            return collect();
        }

        $term = '%'.addcslashes($query, '%_').'%';

        return Product::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('sku', 'like', $term)
                    ->orWhere('barcode', 'like', $term);
            })
            ->orderBy('name')
            ->limit(10)
            ->get();
    }

    /**
     * When barcode/SKU input changes: if it exactly matches one product's barcode or SKU, add to cart automatically.
     * Name search (partial match) keeps showing the dropdown and does not auto-add.
     */
    public function updatedBarcodeInput(): void
    {
        $code = trim($this->barcodeInput);
        if ($code === '') {
            return;
        }

        $tenant = $this->resolveTenant();
        if ($tenant === null) {
            return;
        }

        $product = Product::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->where(function ($q) use ($code) {
                $q->where('barcode', $code)->orWhere('sku', $code);
            })
            ->first();

        if ($product !== null) {
            $this->addToCart($product, 1);
            $this->barcodeInput = '';
            $this->resetErrorBag('barcodeInput');
            $this->dispatch('focus-pos-barcode');
        }
    }

    public function selectProduct(int $id): void
    {
        $tenant = $this->resolveTenant();
        if ($tenant === null) {
            return;
        }

        $product = Product::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->find($id);

        if ($product !== null) {
            $this->addToCart($product, 1);
            $this->barcodeInput = '';
            $this->resetErrorBag('barcodeInput');
            $this->dispatch('focus-pos-barcode');
        }
    }

    public function addByBarcode(): void
    {
        $code = trim($this->barcodeInput);
        if ($code === '') {
            return;
        }

        $tenant = $this->resolveTenant();
        if ($tenant === null) {
            $this->addError('barcodeInput', 'Tenant tidak ditemukan');

            return;
        }

        $product = Product::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where(function ($q) use ($code) {
                $q->where('barcode', $code)->orWhere('sku', $code);
            })
            ->first();
        if (! $product) {
            $this->addError('barcodeInput', 'Produk tidak ditemukan');

            return;
        }
        if (! $product->is_active) {
            $this->addError('barcodeInput', 'Produk tidak aktif');

            return;
        }

        $this->addToCart($product, 1);
        $this->barcodeInput = '';
        $this->resetErrorBag('barcodeInput');
        $this->dispatch('focus-pos-barcode');
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

    public function getStoreNameProperty(): string
    {
        $tenant = $this->resolveTenant();

        return $tenant?->setting?->store_name ?? config('app.name');
    }

    public function getChangeProperty(): float
    {
        $paid = (float) preg_replace('/[^0-9.]/', '', str_replace(',', '.', $this->amountPaid));

        return max(0, round($paid - $this->total, 2));
    }

    public function openPaymentModal(): void
    {
        $this->lastReceipt = null;
        $this->resetErrorBag('checkout');
        $this->resetErrorBag('cart');
        if ($this->cart === []) {
            $this->addError('cart', 'Keranjang kosong');

            return;
        }
        $this->amountPaid = (string) (int) $this->total;
        $this->showPaymentModal = true;
    }

    public function closePaymentModal(): void
    {
        $this->showPaymentModal = false;
        $this->amountPaid = '';
    }

    public function confirmAndPay(): void
    {
        $this->validate([
            'amountPaid' => ['required'],
        ]);
        $paid = (float) preg_replace('/[^0-9.]/', '', str_replace(',', '.', $this->amountPaid));
        if ($paid < $this->total) {
            $this->addError('amountPaid', 'Jumlah bayar minimal harus sama dengan total.');

            return;
        }
        $change = round($paid - $this->total, 2);
        $tenant = $this->resolveTenant();
        $storeName = $tenant?->setting?->store_name ?? config('app.name');
        $receiptItems = [];
        foreach ($this->cart as $row) {
            $receiptItems[] = [
                'name' => $row['name'],
                'qty' => (int) $row['qty'],
                'unit_price' => (float) $row['unit_price'],
                'subtotal' => (float) $row['subtotal'],
            ];
        }
        $receiptFooter = $tenant?->setting?->receipt_footer ?? '';

        $receiptData = [
            'store_name' => $storeName,
            'date' => now()->format('d/m/Y H:i'),
            'items' => $receiptItems,
            'total' => $this->total,
            'amount_paid' => $paid,
            'change' => $change,
            'receipt_footer' => $receiptFooter,
        ];

        try {
            $this->checkout();
            $this->lastReceipt = $receiptData;
            $this->showPaymentModal = false;
            $this->amountPaid = '';
            $this->dispatch('focus-pos-barcode');
        } catch (\DomainException|\InvalidArgumentException $e) {
            $this->addError('checkout', $e->getMessage());
        }
    }

    public function checkout(): void
    {
        if ($this->cart === []) {
            throw new \InvalidArgumentException('Keranjang kosong');
        }

        $items = [];
        foreach ($this->cart as $row) {
            $items[] = [
                'product_id' => (int) $row['product_id'],
                'qty' => (int) $row['qty'],
                'unit_price' => (float) $row['unit_price'],
            ];
        }

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
    }

    private function resolveTenant(): ?Tenant
    {
        if (tenant() !== null) {
            return tenant();
        }
        $user = auth()->user();
        if ($user?->tenant_id !== null) {
            return Tenant::find($user->tenant_id);
        }

        return null;
    }

    public function render()
    {
        return view('domains.pos.livewire.pos-page')
            ->layout('layouts.app', ['title' => 'Kasir']);
    }
}
