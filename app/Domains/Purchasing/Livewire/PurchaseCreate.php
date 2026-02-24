<?php

namespace App\Domains\Purchasing\Livewire;

use App\Domains\Product\Models\Product;
use App\Domains\Purchasing\Models\Supplier;
use App\Domains\Purchasing\Services\PurchaseService;
use Livewire\Attributes\Computed;
use Livewire\Component;

class PurchaseCreate extends Component
{
    public int $supplierId = 0;

    public string $purchase_date = '';

    /**
     * @var array<int, array{product_id: int, qty: int, unit_cost: string}>
     */
    public array $items = [];

    public function mount(): void
    {
        $this->purchase_date = now()->format('Y-m-d');
        $this->addRow();
    }

    public function addRow(): void
    {
        $this->items[] = [
            'product_id' => 0,
            'qty' => 1,
            'unit_cost' => '0',
        ];
    }

    public function removeRow(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
        if (count($this->items) === 0) {
            $this->addRow();
        }
    }

    #[Computed]
    public function suppliers()
    {
        return Supplier::query()->orderBy('name')->get();
    }

    #[Computed]
    public function products()
    {
        return Product::query()->where('is_active', true)->orderBy('name')->get();
    }

    public function getTotalAmountProperty(): float
    {
        $total = 0.0;
        foreach ($this->items as $item) {
            $qty = (int) ($item['qty'] ?? 0);
            $unitCost = (float) ($item['unit_cost'] ?? 0);
            $total += $qty * $unitCost;
        }

        return round($total, 2);
    }

    public function submit(): void
    {
        $this->validate([
            'supplierId' => ['required', 'integer', 'exists:suppliers,id'],
            'purchase_date' => ['required', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
        ], [], [
            'supplierId' => 'Pemasok',
            'purchase_date' => 'Tanggal pembelian',
            'items.*.product_id' => 'Produk',
            'items.*.qty' => 'Jumlah',
            'items.*.unit_cost' => 'Harga beli',
        ]);

        $itemPayload = [];
        foreach ($this->items as $item) {
            if ((int) ($item['product_id'] ?? 0) <= 0) {
                continue;
            }
            $itemPayload[] = [
                'product_id' => (int) $item['product_id'],
                'qty' => (int) $item['qty'],
                'unit_cost' => (float) $item['unit_cost'],
            ];
        }
        if (count($itemPayload) === 0) {
            $this->addError('items', 'Minimal satu item dengan produk wajib diisi.');

            return;
        }

        try {
            $service = app(PurchaseService::class);
            $purchaseDate = \Carbon\CarbonImmutable::parse($this->purchase_date);
            $service->createPurchase($this->supplierId, $itemPayload, $purchaseDate);
            session()->flash('message', 'Pembelian berhasil dibuat.');
            $this->redirectRoute('purchasing.purchases.index', navigate: true);
        } catch (\DomainException|\InvalidArgumentException $e) {
            $this->addError('items', $e->getMessage());
        }
    }

    public function render()
    {
        return view('domains.purchasing.livewire.purchase-create')
            ->layout('layouts.app', ['title' => 'Pembelian Baru']);
    }
}
