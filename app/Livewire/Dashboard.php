<?php

namespace App\Livewire;

use App\Models\Ticket;
use App\Models\Invoice;
use App\Models\Product;
use Illuminate\Support\Carbon;
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

        $tickets = Ticket::whereBetween('created_at', [$range['start'], $range['end']]);
        $revenue = (clone $tickets)->sum('total');
        $ticketCount = (clone $tickets)->count();
        $avgTicket = $ticketCount > 0 ? $revenue / $ticketCount : 0;
        $unitsSold = \App\Models\TicketItem::whereHas('ticket', fn($q) => $q->whereBetween('created_at', [$range['start'], $range['end']]))->sum('quantity');

        $purchaseCosts = Invoice::where('type', 'purchase')->whereBetween('invoice_date', [$range['start'], $range['end']])->sum('total');
        $serviceCosts = Invoice::where('type', 'service')->whereBetween('invoice_date', [$range['start'], $range['end']])->sum('total');
        $netProfit = $revenue - $purchaseCosts - $serviceCosts;

        $criticalProducts = Product::whereColumn('stock', '<=', 'min_stock')->count();

        return view('livewire.dashboard', [
            'revenue' => $revenue,
            'netProfit' => $netProfit,
            'ticketCount' => $ticketCount,
            'avgTicket' => $avgTicket,
            'unitsSold' => $unitsSold,
            'criticalProducts' => $criticalProducts,
        ]);
    }

    public function setPeriod(string $period): void
    {
        $this->period = $period;
    }

    private function getDateRange(): array
    {
        return match ($this->period) {
            'today' => ['start' => Carbon::today(), 'end' => Carbon::now()],
            'week' => ['start' => Carbon::now()->startOfWeek(), 'end' => Carbon::now()],
            'month' => ['start' => Carbon::now()->startOfMonth(), 'end' => Carbon::now()],
            default => ['start' => Carbon::today(), 'end' => Carbon::now()],
        };
    }
}
