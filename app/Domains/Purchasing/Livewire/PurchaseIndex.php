<?php

namespace App\Domains\Purchasing\Livewire;

use App\Domains\Purchasing\Models\Purchase;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class PurchaseIndex extends Component
{
    use WithPagination;

    public string $search = '';

    #[Computed]
    public function purchases()
    {
        $query = Purchase::query()
            ->with('supplier')
            ->orderByDesc('purchase_date')
            ->orderByDesc('id');

        if ($this->search !== '') {
            $term = '%'.$this->search.'%';
            $query->where(function ($q) use ($term) {
                $q->where('purchase_number', 'like', $term)
                    ->orWhereHas('supplier', fn ($q) => $q->where('name', 'like', $term));
            });
        }

        return $query->paginate(10);
    }

    public function render()
    {
        return view('domains.purchasing.livewire.purchase-index');
    }
}
