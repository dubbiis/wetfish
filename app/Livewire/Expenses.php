<?php

namespace App\Livewire;

use App\Models\Invoice;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Carbon;

#[Layout('layouts.app')]
#[Title('Gastos')]
class Expenses extends Component
{
    use WithPagination;

    public string $search = '';
    public string $typeFilter = '';
    public string $period = 'month';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function setType(string $type): void
    {
        $this->typeFilter = $type;
        $this->resetPage();
    }

    public function setPeriod(string $period): void
    {
        $this->period = $period;
        $this->resetPage();
    }

    private function getDateRange(): array
    {
        return match ($this->period) {
            'week' => ['start' => Carbon::now()->startOfWeek(), 'end' => Carbon::now()],
            'month' => ['start' => Carbon::now()->startOfMonth(), 'end' => Carbon::now()],
            'year' => ['start' => Carbon::now()->startOfYear(), 'end' => Carbon::now()],
            'all' => ['start' => Carbon::create(2020, 1, 1), 'end' => Carbon::now()],
            default => ['start' => Carbon::now()->startOfMonth(), 'end' => Carbon::now()],
        };
    }

    public function render()
    {
        $range = $this->getDateRange();

        $invoices = Invoice::with('supplier')
            ->whereBetween('invoice_date', [$range['start'], $range['end']])
            ->when($this->typeFilter, fn($q) => $q->where('type', $this->typeFilter))
            ->when($this->search, fn($q) => $q->where('concept', 'like', "%{$this->search}%")
                ->orWhere('invoice_number', 'like', "%{$this->search}%")
                ->orWhereHas('supplier', fn($s) => $s->where('name', 'like', "%{$this->search}%")))
            ->orderByDesc('invoice_date')
            ->paginate(20);

        $purchaseTotal = Invoice::where('type', 'purchase')
            ->whereBetween('invoice_date', [$range['start'], $range['end']])
            ->sum('total');

        $serviceTotal = Invoice::where('type', 'service')
            ->whereBetween('invoice_date', [$range['start'], $range['end']])
            ->sum('total');

        return view('livewire.expenses', [
            'invoices' => $invoices,
            'purchaseTotal' => $purchaseTotal,
            'serviceTotal' => $serviceTotal,
        ]);
    }
}
