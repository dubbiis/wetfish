<?php

namespace App\Livewire;

use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Category;
use App\Models\Setting;
use Illuminate\Support\Carbon;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Editar Producto')]
class ProductEdit extends Component
{
    use WithFileUploads;

    public ?Product $product = null;
    public bool $isNew = false;

    public string $name = '';
    public string $code = '';
    public ?int $category_id = null;
    public string $cost_price = '';
    public string $sale_price = '';
    public int $stock = 0;
    public int $min_stock = 5;
    public bool $auto_margin = false;
    public $photo;
    public ?string $existingPhoto = null;

    public function mount(string $productId = 'new'): void
    {
        if ($productId === 'new') {
            $this->isNew = true;
            $marginPct = Setting::get('auto_margin_percentage', 30);
            $this->auto_margin = false;
        } else {
            $this->product = Product::findOrFail($productId);
            $this->name = $this->product->name;
            $this->code = $this->product->code ?? '';
            $this->category_id = $this->product->category_id;
            $this->cost_price = $this->product->cost_price;
            $this->sale_price = $this->product->sale_price;
            $this->stock = $this->product->stock;
            $this->min_stock = $this->product->min_stock;
            $this->auto_margin = $this->product->auto_margin;
            $this->existingPhoto = $this->product->photo;
        }
    }

    public function updatedCostPrice(): void
    {
        $this->calculateAutoMargin();
    }

    public function updatedAutoMargin(): void
    {
        $this->calculateAutoMargin();
    }

    private function calculateAutoMargin(): void
    {
        if ($this->auto_margin && is_numeric($this->cost_price) && $this->cost_price > 0) {
            $marginPct = Setting::get('auto_margin_percentage', 30);
            // Usar coste real (compra + gastos operativos) para el margen
            $costPerUnit = $this->getCostPerUnit();
            $realCost = (float) $this->cost_price + $costPerUnit;
            $this->sale_price = number_format($realCost * (1 + $marginPct / 100), 2, '.', '');
        }
    }

    private function getCostPerUnit(): float
    {
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
        return $totalUnits > 0 ? round(($totalExpenses + $totalTransport) / $totalUnits, 2) : 0;
    }

    public function save(): void
    {
        $rules = [
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:100',
            'category_id' => 'nullable|exists:categories,id',
            'cost_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:0',
            'auto_margin' => 'boolean',
            'photo' => 'nullable|image|max:2048',
        ];

        $validated = $this->validate($rules);

        $data = [
            'name' => $this->name,
            'code' => $this->code ?: null,
            'category_id' => $this->category_id,
            'cost_price' => $this->cost_price,
            'sale_price' => $this->sale_price,
            'stock' => $this->stock,
            'min_stock' => $this->min_stock,
            'auto_margin' => $this->auto_margin,
        ];

        // Manejar base_sale_price con ajuste global activo
        if (Setting::get('price_adjustment_active', '0') === '1') {
            $pct = (float) Setting::get('price_adjustment_percentage', 0);
            $data['base_sale_price'] = $data['sale_price'];
            $data['sale_price'] = round($data['sale_price'] * (1 + $pct / 100), 2);
        } else {
            $data['base_sale_price'] = $data['sale_price'];
        }

        if ($this->photo) {
            $data['photo'] = $this->photo->store('products', 'public');
        }

        if ($this->isNew) {
            Product::create($data);
        } else {
            $this->product->update($data);
        }

        session()->flash('message', $this->isNew ? 'Producto creado' : 'Producto actualizado');
        $this->redirect(route('stock'), navigate: true);
    }

    public function delete(): void
    {
        if ($this->product) {
            $this->product->delete();
            session()->flash('message', 'Producto eliminado');
            $this->redirect(route('stock'), navigate: true);
        }
    }

    public function render()
    {
        // Calcular coste operativo por unidad (misma lógica que Dashboard)
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

        $realCost = round((float) $this->cost_price + $costPerUnit, 2);

        return view('livewire.product-edit', [
            'categories' => Category::all(),
            'costPerUnit' => $costPerUnit,
            'realCost' => $realCost,
        ]);
    }
}
