<?php

namespace App\Livewire;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Gastos')]
class Expenses extends Component
{
    use WithPagination;

    public string $period = 'month';

    // Modal añadir/editar gasto
    public bool   $showAddModal = false;
    public ?int   $editingId    = null;
    public string $categoryId   = '';
    public string $concept      = '';
    public string $amount       = '';
    public string $date         = '';
    public string $notes        = '';

    // Modal gestionar categorías
    public bool   $showCategoryModal = false;
    public string $newCategoryName   = '';
    public string $newCategoryIcon   = 'receipt';

    public function setPeriod(string $period): void
    {
        $this->period = $period;
        $this->resetPage();
    }

    public function openAdd(): void
    {
        $this->reset('editingId', 'categoryId', 'concept', 'amount', 'notes');
        $this->date         = now()->toDateString();
        $this->showAddModal = true;
    }

    public function openEdit(int $id): void
    {
        $expense          = Expense::findOrFail($id);
        $this->editingId  = $id;
        $this->categoryId = (string) $expense->expense_category_id;
        $this->concept    = $expense->concept;
        $this->amount     = (string) $expense->amount;
        $this->date       = $expense->date->toDateString();
        $this->notes      = $expense->notes ?? '';
        $this->showAddModal = true;
    }

    public function closeModal(): void
    {
        $this->showAddModal = false;
        $this->resetValidation();
    }

    public function saveExpense(): void
    {
        $data = $this->validate([
            'categoryId' => 'required|exists:expense_categories,id',
            'concept'    => 'required|string|max:255',
            'amount'     => 'required|numeric|min:0.01',
            'date'       => 'required|date',
            'notes'      => 'nullable|string|max:500',
        ]);

        $payload = [
            'expense_category_id' => $data['categoryId'],
            'concept'             => $data['concept'],
            'amount'              => $data['amount'],
            'date'                => $data['date'],
            'notes'               => $data['notes'] ?: null,
        ];

        if ($this->editingId) {
            Expense::findOrFail($this->editingId)->update($payload);
            Log::info('Expense updated', ['id' => $this->editingId]);
        } else {
            Expense::create($payload);
            Log::info('Expense created', ['concept' => $payload['concept']]);
        }

        $this->closeModal();
    }

    public function deleteExpense(int $id): void
    {
        Expense::findOrFail($id)->delete();
        Log::info('Expense deleted', ['id' => $id]);
    }

    public function openCategoryModal(): void
    {
        $this->reset('newCategoryName', 'newCategoryIcon');
        $this->newCategoryIcon   = 'receipt';
        $this->showCategoryModal = true;
    }

    public function closeCategoryModal(): void
    {
        $this->showCategoryModal = false;
        $this->resetValidation();
    }

    public function saveCategory(): void
    {
        $this->validate([
            'newCategoryName' => 'required|string|max:100|unique:expense_categories,name',
            'newCategoryIcon' => 'required|string|max:100',
        ]);

        ExpenseCategory::create([
            'name' => $this->newCategoryName,
            'icon' => $this->newCategoryIcon,
        ]);

        Log::info('ExpenseCategory created', ['name' => $this->newCategoryName]);
        $this->newCategoryName = '';
        $this->newCategoryIcon = 'receipt';
    }

    public function deleteCategory(int $id): void
    {
        $cat = ExpenseCategory::withCount('expenses')->findOrFail($id);
        if ($cat->expenses_count > 0) {
            $this->addError('deleteCategory', 'No se puede eliminar una categoría con gastos asociados.');
            return;
        }
        $cat->delete();
        Log::info('ExpenseCategory deleted', ['id' => $id]);
    }

    public function render()
    {
        $range = $this->getDateRange();

        $expenses = Expense::with('category')
            ->whereBetween('date', [$range['start'], $range['end']])
            ->orderByDesc('date')
            ->paginate(20);

        $total = Expense::whereBetween('date', [$range['start'], $range['end']])->sum('amount');

        $categories = ExpenseCategory::orderBy('name')->get();

        return view('livewire.expenses', compact('expenses', 'total', 'categories'));
    }

    private function getDateRange(): array
    {
        return match ($this->period) {
            'week'  => ['start' => Carbon::now()->startOfWeek(), 'end' => Carbon::now()],
            'month' => ['start' => Carbon::now()->startOfMonth(), 'end' => Carbon::now()],
            'year'  => ['start' => Carbon::now()->startOfYear(), 'end' => Carbon::now()],
            'all'   => ['start' => Carbon::create(2020, 1, 1), 'end' => Carbon::now()],
            default => ['start' => Carbon::now()->startOfMonth(), 'end' => Carbon::now()],
        };
    }
}
