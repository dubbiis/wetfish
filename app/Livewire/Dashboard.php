<?php

namespace App\Livewire;

use App\Models\AiUsageLog;
use App\Models\Expense;
use App\Models\RecurringExpense;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Setting;
use App\Models\StockLoss;
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
        // Auto-generar gastos fijos pendientes (throttled 1x/día)
        RecurringExpense::generatePendingExpenses();

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

        // Costs & profit — facturas de compra (desglosado)
        $purchaseInvoices = Invoice::where('type', 'purchase')
            ->whereBetween('invoice_date', [$range['start'], $range['end']]);
        $purchaseCosts       = (clone $purchaseInvoices)->sum('total');
        $purchaseProductCost = (clone $purchaseInvoices)->sum('subtotal_products');
        $purchaseTransport   = (clone $purchaseInvoices)->sum('transport_cost');
        $purchaseVat         = (clone $purchaseInvoices)->sum('vat_amount');

        $serviceCosts = Invoice::where('type', 'service')
            ->whereBetween('invoice_date', [$range['start'], $range['end']])
            ->sum('total');

        // Gastos operativos: base (sin IVA) y total (con IVA)
        $operationalBase = Expense::whereBetween('date', [$range['start'], $range['end']])->sum('amount');
        $operationalTax  = Expense::whereBetween('date', [$range['start'], $range['end']])->sum('tax_amount');
        $operationalCosts = $operationalBase;
        $operationalTotal = $operationalBase + $operationalTax;

        // IVA repercutido (cobrado en ventas)
        $ivaRepercutido = $allTickets->sum('tax_amount');
        // IVA soportado = IVA de gastos operativos + IVA de facturas de compra
        $ivaSoportado = $operationalTax + $purchaseVat;
        // Balance fiscal
        $ivaBalance = $ivaRepercutido - $ivaSoportado;

        // Beneficio neto usando base imponible (sin IVA = coste real)
        $netProfit = $revenue - $purchaseCosts - $serviceCosts - $operationalBase;
        $marginPct = $revenue > 0 ? round(($netProfit / $revenue) * 100, 1) : 0;

        // Beneficio con IVA incluido (flujo de caja real)
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
        $criticalProductList = Product::with('category')->whereColumn('stock', '<=', 'min_stock')->orderBy('stock')->get();
        $inventoryValue    = Product::selectRaw('SUM(stock * cost_price) as total')->value('total') ?? 0;

        // Merma en el período
        $lossUnits = (int) StockLoss::whereBetween('date', [$range['start'], $range['end']])->sum('quantity');
        $lossCost  = (float) StockLoss::whereBetween('date', [$range['start'], $range['end']])->sum('total_cost');
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

        // AI usage stats (always current month)
        $aiMonthStart = Carbon::now()->startOfMonth();
        $aiUsageMonth = AiUsageLog::where('created_at', '>=', $aiMonthStart)->get();
        $aiTokensIn   = $aiUsageMonth->sum('tokens_input');
        $aiTokensOut  = $aiUsageMonth->sum('tokens_output');
        $aiCostMonth  = $aiUsageMonth->sum('cost_eur');
        $aiCallsMonth = $aiUsageMonth->count();

        // Ventas diarias para sparkline (últimos 7 días o según período)
        $dailySales = $allTickets->groupBy(fn($t) => $t->created_at->format('Y-m-d'))
            ->map(fn($day) => round($day->sum('total'), 2))
            ->toArray();
        // Rellenar días sin ventas con 0
        $period_start = $range['start']->copy();
        $period_end = min($range['end']->copy(), now());
        $days = [];
        $current = $period_start->copy();
        $maxDays = 30; // limitar a 30 barras máx
        $totalDays = $period_start->diffInDays($period_end);
        if ($totalDays > $maxDays) {
            $current = $period_end->copy()->subDays($maxDays);
        }
        while ($current->lte($period_end)) {
            $key = $current->format('Y-m-d');
            $days[$key] = $dailySales[$key] ?? 0;
            $current->addDay();
        }
        $sparklineData = array_values($days);
        $sparklineLabels = array_map(fn($d) => \Carbon\Carbon::parse($d)->format('d'), array_keys($days));

        Log::info('Dashboard rendered', ['period' => $this->period, 'revenue' => $revenue, 'realMarginPct' => $realMarginPct]);

        return view('livewire.dashboard', compact(
            'revenue', 'netProfit', 'netProfitWithTax', 'ticketCount', 'avgTicket', 'unitsSold',
            'maxTicket', 'lastTicket', 'purchaseCosts', 'purchaseTransport', 'purchaseVat', 'serviceCosts',
            'operationalCosts', 'operationalTotal', 'operationalTax', 'marginPct', 'marginPctWithTax',
            'ivaRepercutido', 'ivaSoportado', 'ivaBalance',
            'criticalProducts', 'criticalProductList', 'inventoryValue', 'inactiveProducts', 'lossUnits', 'lossCost',
            'topProductsByQty', 'topProductsByRevenue', 'salesByCategory',
            'expensesByCategory', 'peakHour', 'bestDay',
            'realMarginPct', 'targetMarginPct', 'costPerUnit', 'totalUnitsInStock',
            'expensePeriodLabel', 'showPeakSuggestion', 'suggestedAdjustment', 'priceAdjustmentActive',
            'aiTokensIn', 'aiTokensOut', 'aiCostMonth', 'aiCallsMonth',
            'sparklineData', 'sparklineLabels'
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

        // Gastos operativos (luz, agua, etc.) + transporte de facturas de compra
        $totalExpenses = Expense::whereBetween('date', [$expenseRange['start'], $expenseRange['end']])->sum('amount');
        $totalTransport = Invoice::where('type', 'purchase')
            ->whereBetween('invoice_date', [$expenseRange['start'], $expenseRange['end']])
            ->sum('transport_cost');

        $totalCosts = $totalExpenses + $totalTransport;
        $totalUnitsInStock = Product::where('stock', '>', 0)->sum('stock');
        $costPerUnit = $totalUnitsInStock > 0 ? round($totalCosts / $totalUnitsInStock, 4) : 0;

        $expensePeriodLabel = match ($expensePeriod) {
            '3months' => '3 meses',
            '6months' => '6 meses',
            default   => 'Mes actual',
        };

        return compact('totalExpenses', 'totalTransport', 'totalCosts', 'totalUnitsInStock', 'costPerUnit', 'expensePeriodLabel');
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
