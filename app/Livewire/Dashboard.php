<?php

namespace App\Livewire;

use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Ticket;
use App\Models\TicketItem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Dashboard')]
class Dashboard extends Component
{
    public string $period = 'today';

    public function render()
    {
        $range = $this->getDateRange();

        // Load all tickets in range
        $allTickets = Ticket::whereBetween('created_at', [$range['start'], $range['end']])->get();

        // Load all ticket items with relations
        $ticketItems = TicketItem::with(['product.category'])
            ->whereHas('ticket', fn($q) => $q->whereBetween('created_at', [$range['start'], $range['end']]))
            ->get();

        // Basic metrics
        $revenue     = $allTickets->sum('total');
        $ticketCount = $allTickets->count();
        $avgTicket   = $ticketCount > 0 ? $revenue / $ticketCount : 0;
        $unitsSold   = $ticketItems->sum('quantity');
        $maxTicket   = $allTickets->max('total') ?? 0;
        $lastTicket  = $allTickets->sortByDesc('created_at')->first();

        // Costs & profit
        $purchaseCosts = Invoice::where('type', 'purchase')
            ->whereBetween('invoice_date', [$range['start'], $range['end']])
            ->sum('total');
        $serviceCosts = Invoice::where('type', 'service')
            ->whereBetween('invoice_date', [$range['start'], $range['end']])
            ->sum('total');
        // Gastos operativos: base (sin IVA) y total (con IVA)
        $operationalBase = Expense::whereBetween('date', [$range['start'], $range['end']])->sum('amount');
        $operationalTax  = Expense::whereBetween('date', [$range['start'], $range['end']])->sum('tax_amount');
        $operationalCosts = $operationalBase; // Para beneficio se usa la base (IVA es deducible)
        $operationalTotal = $operationalBase + $operationalTax; // Total con IVA (lo que realmente sale de caja)

        // IVA repercutido (cobrado en ventas)
        $ivaRepercutido = $allTickets->sum('tax_amount');
        // IVA soportado (pagado en gastos operativos)
        $ivaSoportado = $operationalTax;
        // Balance fiscal: lo que hay que pagar a Hacienda
        $ivaBalance = $ivaRepercutido - $ivaSoportado;

        // Beneficio neto usando base imponible (sin IVA = coste real)
        $netProfit = $revenue - $purchaseCosts - $serviceCosts - $operationalBase;
        $marginPct = $revenue > 0 ? round(($netProfit / $revenue) * 100, 1) : 0;

        // Beneficio bruto con IVA incluido (lo que realmente paga de caja)
        $netProfitWithTax = $revenue - $purchaseCosts - $serviceCosts - $operationalTotal;
        $marginPctWithTax = $revenue > 0 ? round(($netProfitWithTax / $revenue) * 100, 1) : 0;

        // Gastos operativos por categoría para el dashboard
        $expensesByCategory = Expense::with('category')
            ->whereBetween('date', [$range['start'], $range['end']])
            ->get()
            ->groupBy('expense_category_id')
            ->map(fn($items) => [
                'name'  => $items->first()->category?->name ?? 'Sin categoría',
                'icon'  => $items->first()->category?->icon ?? 'receipt',
                'base'  => $items->sum('amount'),
                'tax'   => $items->sum('tax_amount'),
                'total' => $items->sum('amount') + $items->sum('tax_amount'),
            ])
            ->sortByDesc('total')
            ->values();

        // Stock & inventory
        $criticalProducts  = Product::whereColumn('stock', '<=', 'min_stock')->count();
        $inventoryValue    = Product::selectRaw('SUM(stock * cost_price) as total')->value('total') ?? 0;
        $activeProductIds  = $ticketItems->pluck('product_id')->unique()->filter();
        $inactiveProducts  = Product::where('stock', '>', 0)
            ->whereNotIn('id', $activeProductIds)
            ->count();

        // Top 5 products by quantity
        $topProductsByQty = $ticketItems->groupBy('product_id')
            ->map(fn($items) => [
                'name'  => $items->first()->product?->name ?? 'Desconocido',
                'total' => $items->sum('quantity'),
            ])
            ->sortByDesc('total')
            ->take(5)
            ->values();

        // Top 5 products by revenue
        $topProductsByRevenue = $ticketItems->groupBy('product_id')
            ->map(fn($items) => [
                'name'  => $items->first()->product?->name ?? 'Desconocido',
                'total' => $items->sum('subtotal'),
            ])
            ->sortByDesc('total')
            ->take(5)
            ->values();

        // Sales by category
        $salesByCategory = $ticketItems->groupBy(fn($item) => $item->product?->category?->name ?? 'Sin categoría')
            ->map(fn($items, $name) => [
                'name'    => $name,
                'units'   => $items->sum('quantity'),
                'revenue' => $items->sum('subtotal'),
            ])
            ->sortByDesc('revenue')
            ->values();

        // Peak hour (PHP-side, DB-agnostic)
        $peakHour = null;
        if ($allTickets->isNotEmpty()) {
            $peakHour = $allTickets->groupBy(fn($t) => (int) $t->created_at->format('H'))
                ->sortByDesc(fn($g) => $g->count())
                ->keys()
                ->first();
        }

        // Best day of week (week/month only)
        $bestDay = null;
        if ($this->period !== 'today' && $allTickets->isNotEmpty()) {
            $dayNames     = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
            $bestDayIndex = $allTickets->groupBy(fn($t) => $t->created_at->dayOfWeek)
                ->sortByDesc(fn($g) => $g->count())
                ->keys()
                ->first();
            $bestDay = $dayNames[$bestDayIndex] ?? null;
        }

        // Real cost calculation (independent expense period from Settings)
        $realCostData = $this->calculateRealCosts();
        $costPerUnit = $realCostData['costPerUnit'];
        $totalUnitsInStock = $realCostData['totalUnitsInStock'];
        $expensePeriodLabel = $realCostData['expensePeriodLabel'];

        $totalRealCost = $ticketItems->sum(function ($item) use ($costPerUnit) {
            $productCost = (float) ($item->product?->cost_price ?? 0);
            return ($productCost + $costPerUnit) * $item->quantity;
        });

        $realMarginPct = $revenue > 0 ? round(($revenue - $totalRealCost) / $revenue * 100, 1) : 0;
        $targetMarginPct = (float) Setting::get('target_margin_percentage', 30);
        $priceAdjustmentActive = Setting::get('price_adjustment_active', '0') === '1';

        // Peak hour suggestion
        $showPeakSuggestion = $peakHour !== null
            && $realMarginPct < $targetMarginPct
            && $ticketCount >= 3;
        $suggestedAdjustment = round($targetMarginPct - $realMarginPct, 1);

        Log::info('Dashboard rendered', ['period' => $this->period, 'revenue' => $revenue, 'realMarginPct' => $realMarginPct]);

        return view('livewire.dashboard', compact(
            'revenue', 'netProfit', 'netProfitWithTax', 'ticketCount', 'avgTicket', 'unitsSold',
            'maxTicket', 'lastTicket', 'purchaseCosts', 'serviceCosts',
            'operationalCosts', 'operationalTotal', 'operationalTax', 'marginPct', 'marginPctWithTax',
            'ivaRepercutido', 'ivaSoportado', 'ivaBalance',
            'criticalProducts', 'inventoryValue', 'inactiveProducts',
            'topProductsByQty', 'topProductsByRevenue', 'salesByCategory',
            'expensesByCategory', 'peakHour', 'bestDay',
            'realMarginPct', 'targetMarginPct', 'costPerUnit', 'totalUnitsInStock',
            'expensePeriodLabel', 'showPeakSuggestion', 'suggestedAdjustment', 'priceAdjustmentActive'
        ));
    }

    public function setPeriod(string $period): void
    {
        $this->period = $period;
    }

    private function calculateRealCosts(): array
    {
        $expensePeriod = Setting::get('expense_calculation_period', 'month');
        $expenseRange = match ($expensePeriod) {
            '3months' => ['start' => Carbon::now()->subMonths(3), 'end' => Carbon::now()],
            '6months' => ['start' => Carbon::now()->subMonths(6), 'end' => Carbon::now()],
            default   => ['start' => Carbon::now()->startOfMonth(), 'end' => Carbon::now()],
        };

        $totalExpenses = Expense::whereBetween('date', [$expenseRange['start'], $expenseRange['end']])->sum('amount');
        $totalUnitsInStock = Product::where('stock', '>', 0)->sum('stock');
        $costPerUnit = $totalUnitsInStock > 0 ? round($totalExpenses / $totalUnitsInStock, 4) : 0;

        $expensePeriodLabel = match ($expensePeriod) {
            '3months' => '3 meses',
            '6months' => '6 meses',
            default   => 'Mes actual',
        };

        return compact('totalExpenses', 'totalUnitsInStock', 'costPerUnit', 'expensePeriodLabel');
    }

    private function getDateRange(): array
    {
        return match ($this->period) {
            'today' => ['start' => Carbon::today(), 'end' => Carbon::now()],
            'week'  => ['start' => Carbon::now()->startOfWeek(), 'end' => Carbon::now()],
            'month' => ['start' => Carbon::now()->startOfMonth(), 'end' => Carbon::now()],
            default => ['start' => Carbon::today(), 'end' => Carbon::now()],
        };
    }
}
