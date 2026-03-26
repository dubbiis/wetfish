<?php

namespace App\Livewire;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\RecurringExpense;
use App\Services\InvoiceVisionService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Gastos')]
class Expenses extends Component
{
    use WithPagination, WithFileUploads;

    public string $period = 'month';

    // Modal añadir/editar gasto
    public bool   $showAddModal = false;
    public ?int   $editingId    = null;
    public string $categoryId   = '';
    public string $concept      = '';
    public string $amount       = '';
    public string $taxRate      = '21';
    public string $date         = '';
    public string $notes        = '';

    // IA scan
    public $expenseFile;
    public bool $processingExpense = false;
    public ?float $aiTaxAmount = null; // IVA real extraído por IA
    public ?float $aiTotal = null;     // Total real extraído por IA

    // Modal gestionar categorías
    public bool   $showCategoryModal = false;
    public string $newCategoryName   = '';
    public string $newCategoryIcon   = 'receipt';

    // Modal gastos fijos recurrentes
    public bool   $showRecurringModal   = false;
    public ?int   $editingRecurringId   = null;
    public string $recurringCategoryId  = '';
    public string $recurringConcept     = '';
    public string $recurringAmount      = '';
    public string $recurringTaxRate     = '0';
    public string $recurringFrequency   = 'monthly';

    public function setPeriod(string $period): void
    {
        $this->period = $period;
        $this->resetPage();
    }

    public function openAdd(): void
    {
        $this->reset('editingId', 'categoryId', 'concept', 'amount', 'taxRate', 'notes');
        $this->taxRate      = '21';
        $this->date         = now()->toDateString();
        $this->showAddModal = true;
    }

    public function updatedExpenseFile(): void
    {
        $this->validate(['expenseFile' => 'required|file|mimes:pdf,jpg,jpeg,png,webp|max:10240']);
        $this->parseExpenseWithVision();
    }

    private function parseExpenseWithVision(): void
    {
        $this->processingExpense = true;

        try {
            $path = $this->expenseFile->getRealPath();
            $mimeType = $this->expenseFile->getMimeType();

            $service = app(InvoiceVisionService::class);
            $data = $service->extractExpense($path, $mimeType);

            // Usar total y tax_amount reales de la factura
            $this->aiTaxAmount = isset($data['tax_amount']) ? (float) $data['tax_amount'] : null;
            $this->aiTotal = isset($data['total']) ? (float) $data['total'] : null;

            // Calcular base = total - IVA (más fiable que la base que devuelve la IA)
            if ($this->aiTotal !== null && $this->aiTaxAmount !== null) {
                $this->amount = (string) round($this->aiTotal - $this->aiTaxAmount, 2);
            } else {
                $this->amount = (string) ($data['base_amount'] ?? '');
            }

            $this->concept = $data['concept'] ?? '';
            $this->taxRate = (string) ($data['tax_rate'] ?? '21');
            $this->date    = $data['date'] ?? now()->toDateString();

            // Try to match category by hint
            $hint = $data['category_hint'] ?? '';
            if ($hint) {
                $categories = ExpenseCategory::all();
                $match = $categories->first(fn($cat) => str_contains(
                    mb_strtolower($cat->name),
                    mb_strtolower($hint)
                ));
                if ($match) {
                    $this->categoryId = (string) $match->id;
                }
            }

            $this->showAddModal = true;
            Log::info('Expense parsed with AI', ['concept' => $this->concept, 'amount' => $this->amount]);
        } catch (\Exception $e) {
            Log::error('Expense AI parse error', ['error' => $e->getMessage()]);
            session()->flash('error', $e->getMessage());
        } finally {
            $this->processingExpense = false;
            $this->expenseFile = null;
        }
    }

    public function openEdit(int $id): void
    {
        $expense          = Expense::findOrFail($id);
        $this->editingId  = $id;
        $this->categoryId = (string) $expense->expense_category_id;
        $this->concept    = $expense->concept;
        $this->amount     = (string) $expense->amount;
        $this->taxRate    = (string) $expense->tax_rate;
        $this->date       = $expense->date->toDateString();
        $this->notes      = $expense->notes ?? '';
        $this->showAddModal = true;
    }

    public function closeModal(): void
    {
        $this->showAddModal = false;
        $this->aiTaxAmount = null;
        $this->aiTotal = null;
        $this->resetValidation();
    }

    public function saveExpense(): void
    {
        $data = $this->validate([
            'categoryId' => 'required|exists:expense_categories,id',
            'concept'    => 'required|string|max:255',
            'amount'     => 'required|numeric|min:0.01',
            'taxRate'    => 'required|numeric|min:0|max:100',
            'date'       => 'required|date',
            'notes'      => 'nullable|string|max:500',
        ]);

        $base = (float) $data['amount'];
        $rate = (float) $data['taxRate'];
        // Usar IVA real de la IA si existe (facturas con IVA mixto), si no recalcular
        $taxAmount = $this->aiTaxAmount !== null
            ? $this->aiTaxAmount
            : round($base * $rate / 100, 2);

        $payload = [
            'expense_category_id' => $data['categoryId'],
            'concept'             => $data['concept'],
            'amount'              => $base,
            'tax_rate'            => $rate,
            'tax_amount'          => $taxAmount,
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

    // --- Gastos fijos recurrentes ---

    public function openAddRecurring(): void
    {
        $this->reset('editingRecurringId', 'recurringCategoryId', 'recurringConcept', 'recurringAmount', 'recurringTaxRate', 'recurringFrequency');
        $this->recurringTaxRate    = '0';
        $this->recurringFrequency  = 'monthly';
        $this->showRecurringModal  = true;
    }

    public function openEditRecurring(int $id): void
    {
        $rec = RecurringExpense::findOrFail($id);
        $this->editingRecurringId  = $id;
        $this->recurringCategoryId = (string) $rec->expense_category_id;
        $this->recurringConcept    = $rec->concept;
        $this->recurringAmount     = (string) $rec->amount;
        $this->recurringTaxRate    = (string) $rec->tax_rate;
        $this->recurringFrequency  = $rec->frequency;
        $this->showRecurringModal  = true;
    }

    public function closeRecurringModal(): void
    {
        $this->showRecurringModal = false;
        $this->resetValidation();
    }

    public function saveRecurring(): void
    {
        $data = $this->validate([
            'recurringCategoryId' => 'required|exists:expense_categories,id',
            'recurringConcept'    => 'required|string|max:255',
            'recurringAmount'     => 'required|numeric|min:0.01',
            'recurringTaxRate'    => 'required|numeric|min:0|max:100',
            'recurringFrequency'  => 'required|in:monthly,quarterly,annual',
        ]);

        $payload = [
            'expense_category_id' => $data['recurringCategoryId'],
            'concept'             => $data['recurringConcept'],
            'amount'              => (float) $data['recurringAmount'],
            'tax_rate'            => (float) $data['recurringTaxRate'],
            'frequency'           => $data['recurringFrequency'],
        ];

        if ($this->editingRecurringId) {
            RecurringExpense::findOrFail($this->editingRecurringId)->update($payload);
            Log::info('RecurringExpense updated', ['id' => $this->editingRecurringId]);
        } else {
            RecurringExpense::create($payload);
            Log::info('RecurringExpense created', ['concept' => $payload['concept']]);
        }

        $this->closeRecurringModal();
    }

    public function toggleRecurring(int $id): void
    {
        $rec = RecurringExpense::findOrFail($id);
        $rec->update(['is_active' => !$rec->is_active]);
        Log::info('RecurringExpense toggled', ['id' => $id, 'active' => !$rec->is_active]);
    }

    public function deleteRecurring(int $id): void
    {
        RecurringExpense::findOrFail($id)->delete();
        Log::info('RecurringExpense deleted', ['id' => $id]);
    }

    public function render()
    {
        // Auto-generar gastos fijos pendientes (throttled 1x/día)
        RecurringExpense::generatePendingExpenses();

        $range = $this->getDateRange();

        $expenses = Expense::with('category')
            ->whereBetween('date', [$range['start'], $range['end']])
            ->orderByDesc('date')
            ->paginate(20);

        $totalBase = Expense::whereBetween('date', [$range['start'], $range['end']])->sum('amount');
        $totalTax  = Expense::whereBetween('date', [$range['start'], $range['end']])->sum('tax_amount');
        $total     = $totalBase + $totalTax;

        $categories = ExpenseCategory::orderBy('name')->get();
        $recurringExpenses = RecurringExpense::with('category')
            ->orderByDesc('is_active')
            ->orderBy('concept')
            ->get();

        return view('livewire.expenses', compact('expenses', 'total', 'totalBase', 'totalTax', 'categories', 'recurringExpenses'));
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
