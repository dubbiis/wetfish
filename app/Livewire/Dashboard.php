<?php

namespace App\Livewire;

use App\Models\Invoice;
use App\Models\Product;
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
        $netProfit    = $revenue - $purchaseCosts - $serviceCosts;
        $marginPct    = $revenue > 0 ? round(($netProfit / $revenue) * 100, 1) : 0;

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

        Log::info('Dashboard rendered', ['period' => $this->period, 'revenue' => $revenue]);

        return view('livewire.dashboard', compact(
            'revenue', 'netProfit', 'ticketCount', 'avgTicket', 'unitsSold',
            'maxTicket', 'lastTicket', 'purchaseCosts', 'serviceCosts', 'marginPct',
            'criticalProducts', 'inventoryValue', 'inactiveProducts',
            'topProductsByQty', 'topProductsByRevenue', 'salesByCategory',
            'peakHour', 'bestDay'
        ));
    }

    public function setPeriod(string $period): void
    {
        $this->period = $period;
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
