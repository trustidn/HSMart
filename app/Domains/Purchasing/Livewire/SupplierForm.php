<?php

namespace App\Domains\Purchasing\Livewire;

use App\Domains\Purchasing\Models\Supplier;
use App\Domains\Purchasing\Services\SupplierService;
use Livewire\Component;

class SupplierForm extends Component
{
    public ?int $supplierId = null;

    public string $name = '';

    public string $contact = '';

    public string $phone = '';

    public string $address = '';

    public function mount(?int $supplierId = null): void
    {
        if ($supplierId !== null) {
            $supplier = Supplier::find($supplierId);
            if ($supplier === null) {
                abort(404);
            }
            $this->supplierId = $supplier->id;
            $this->name = $supplier->name;
            $this->contact = $supplier->contact ?? '';
            $this->phone = $supplier->phone ?? '';
            $this->address = $supplier->address ?? '';
        }
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
        ]);

        $service = app(SupplierService::class);
        if ($this->supplierId !== null) {
            $supplier = Supplier::findOrFail($this->supplierId);
            $service->update($supplier, $validated);
        } else {
            $service->create($validated);
        }
        $this->redirectRoute('purchasing.suppliers.index', navigate: true);
    }

    public function render()
    {
        return view('domains.purchasing.livewire.supplier-form');
    }
}
