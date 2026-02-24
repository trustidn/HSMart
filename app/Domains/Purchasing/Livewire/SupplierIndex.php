<?php

namespace App\Domains\Purchasing\Livewire;

use App\Domains\Purchasing\Models\Supplier;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class SupplierIndex extends Component
{
    use WithPagination;

    public string $search = '';

    #[Computed]
    public function suppliers()
    {
        $query = Supplier::query()->orderBy('name');

        if ($this->search !== '') {
            $term = '%'.$this->search.'%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('contact', 'like', $term)
                    ->orWhere('phone', 'like', $term);
            });
        }

        return $query->paginate(10);
    }

    public function render()
    {
        return view('domains.purchasing.livewire.supplier-index')
            ->layout('layouts.app', ['title' => 'Pemasok']);
    }
}
