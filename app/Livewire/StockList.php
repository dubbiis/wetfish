<?php

namespace App\Livewire;

use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Category;
use App\Models\Setting;
use Illuminate\Support\Carbon;
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

        // Coste operativo por unidad
        $expensePeriod = Setting::get('expense_calculation_period', 'month');
        $expenseRange = match ($expensePeriod) {
            '3months' => ['start' => Carbon::now()->subMonths(3), 'end' => Carbon::now()],
            '6months' => ['start' => Carbon::now()->subMonths(6), 'end' => Carbon::now()],
            default   => ['start' => Carbon::now()->startOfMonth(), 'end' => Carbon::now()],
        };
        $totalExpenses = (float) Expense::whereBetween('date', [$expenseRange['start'], $expenseRange['end']])->sum('amount');
        $totalTransport = (float) Invoice::where('type', 'purchase')
            ->whereBetween('invoice_date', [$expenseRange['start'], $expenseRange['end']])
            ->sum('transport_cost');
        $totalUnits = (int) Product::where('stock', '>', 0)->sum('stock');
        $costPerUnit = $totalUnits > 0 ? round(($totalExpenses + $totalTransport) / $totalUnits, 2) : 0;

        return view('livewire.stock-list', [
            'products' => $products,
            'categories' => Category::all(),
            'costPerUnit' => $costPerUnit,
        ]);
    }
}
