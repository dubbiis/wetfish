<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\Category;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Stock')]
class StockList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $categoryFilter = '';
    public string $stockFilter = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $products = Product::with('category')
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%")->orWhere('code', 'like', "%{$this->search}%"))
            ->when($this->categoryFilter, fn($q) => $q->where('category_id', $this->categoryFilter))
            ->when($this->stockFilter === 'ok', fn($q) => $q->whereColumn('stock', '>', 'min_stock'))
            ->when($this->stockFilter === 'low', fn($q) => $q->whereColumn('stock', '<=', 'min_stock')->where('stock', '>', 0))
            ->when($this->stockFilter === 'critical', fn($q) => $q->where('stock', '<=', 0))
            ->orderBy('name')
            ->paginate(20);

        return view('livewire.stock-list', [
            'products' => $products,
            'categories' => Category::all(),
        ]);
    }
}
