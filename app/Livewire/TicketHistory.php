<?php

namespace App\Livewire;

use App\Models\Ticket;
use App\Models\Setting;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Carbon;

#[Layout('layouts.app')]
#[Title('Tickets')]
class TicketHistory extends Component
{
    use WithPagination;

    public string $search = '';
    public string $period = 'today';
    public string $dateFrom = '';
    public string $dateTo = '';

    // Detail
    public bool $showDetail = false;
    public ?int $selectedTicketId = null;

    // Multi-select
    public array $selected = [];
    public bool $selectAll = false;

    public function mount(): void
    {
        $this->dateFrom = Carbon::today()->format('Y-m-d');
        $this->dateTo = Carbon::today()->format('Y-m-d');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function setPeriod(string $period): void
    {
        $this->period = $period;
        $this->resetPage();
        $this->selected = [];
        $this->selectAll = false;
    }

    public function viewTicket(int $id): void
    {
        $this->selectedTicketId = $id;
        $this->showDetail = true;
    }

    public function closeDetail(): void
    {
        $this->showDetail = false;
        $this->selectedTicketId = null;
    }

    public function toggleSelect(int $id): void
    {
        if (in_array($id, $this->selected)) {
            $this->selected = array_values(array_diff($this->selected, [$id]));
        } else {
            $this->selected[] = $id;
        }
    }

    public function toggleSelectAll(): void
    {
        $this->selectAll = !$this->selectAll;
        if ($this->selectAll) {
            $range = $this->getDateRange();
            $this->selected = Ticket::whereBetween('created_at', [$range['start'], $range['end']])
                ->pluck('id')->toArray();
        } else {
            $this->selected = [];
        }
    }

    public function clearSelection(): void
    {
        $this->selected = [];
        $this->selectAll = false;
    }

    public function getWhatsappUrl(int $ticketId): string
    {
        $ticket = Ticket::with(['items.product', 'user'])->find($ticketId);
        if (!$ticket) return '';

        $business = Setting::get('business_name', 'WetFish');
        $phone = Setting::get('business_phone', '');
        $cif = Setting::get('business_cif', '');

        $text = "🐟 *{$business}*\n";
        if ($cif) $text .= "CIF: {$cif}\n";
        $text .= "Ticket #{$ticket->id}\n";
        $text .= "Fecha: " . $ticket->created_at->format('d/m/Y H:i') . "\n";
        $text .= "Vendedor: " . ($ticket->user?->name ?? '-') . "\n";
        $text .= "─────────────\n";

        foreach ($ticket->items as $item) {
            $name = $item->product?->name ?? 'Producto';
            $text .= "{$item->quantity}x {$name} — €" . number_format($item->subtotal, 2, ',', '.') . "\n";
        }

        $text .= "─────────────\n";
        $text .= "Subtotal: €" . number_format($ticket->subtotal, 2, ',', '.') . "\n";
        if ($ticket->discount_value > 0) {
            $text .= "Descuento: -€" . number_format($ticket->discount_value, 2, ',', '.') . "\n";
        }
        $text .= "IVA ({$ticket->tax_rate}%): €" . number_format($ticket->tax_amount, 2, ',', '.') . "\n";
        $text .= "*TOTAL: €" . number_format($ticket->total, 2, ',', '.') . "*\n";
        $text .= "\nGracias por su compra 🙏";

        return 'https://wa.me/?text=' . urlencode($text);
    }

    public function getExportUrl(): string
    {
        if (empty($this->selected)) return '';
        $ids = implode(',', $this->selected);
        return route('tickets.export', ['ids' => $ids]);
    }

    public function getShareWhatsappUrl(): string
    {
        if (empty($this->selected)) return '';

        $tickets = Ticket::with(['items.product', 'user'])
            ->whereIn('id', $this->selected)
            ->orderByDesc('created_at')
            ->get();

        if ($tickets->isEmpty()) return '';

        $business = Setting::get('business_name', 'WetFish');
        $cif = Setting::get('business_cif', '');

        $text = "🐟 *{$business}*\n";
        if ($cif) $text .= "CIF: {$cif}\n";
        $text .= "─────────────\n\n";

        foreach ($tickets as $ticket) {
            $text .= "*Ticket #{$ticket->id}*\n";
            $text .= "Fecha: " . $ticket->created_at->format('d/m/Y H:i') . "\n";
            $text .= "Vendedor: " . ($ticket->user?->name ?? '-') . "\n";

            foreach ($ticket->items as $item) {
                $name = $item->product?->name ?? 'Producto';
                $text .= "  {$item->quantity}x {$name} — €" . number_format($item->subtotal, 2, ',', '.') . "\n";
            }

            $text .= "*Total: €" . number_format($ticket->total, 2, ',', '.') . "*\n\n";
        }

        $total = $tickets->sum('total');
        if ($tickets->count() > 1) {
            $text .= "─────────────\n";
            $text .= "*TOTAL ({$tickets->count()} tickets): €" . number_format($total, 2, ',', '.') . "*\n";
        }

        return 'https://wa.me/?text=' . urlencode($text);
    }

    private function getDateRange(): array
    {
        return match ($this->period) {
            'today' => ['start' => Carbon::today(), 'end' => Carbon::now()],
            'week' => ['start' => Carbon::now()->startOfWeek(), 'end' => Carbon::now()],
            'month' => ['start' => Carbon::now()->startOfMonth(), 'end' => Carbon::now()],
            'custom' => [
                'start' => Carbon::parse($this->dateFrom)->startOfDay(),
                'end' => Carbon::parse($this->dateTo)->endOfDay(),
            ],
            'all' => ['start' => Carbon::create(2020, 1, 1), 'end' => Carbon::now()],
            default => ['start' => Carbon::today(), 'end' => Carbon::now()],
        };
    }

    public function render()
    {
        $range = $this->getDateRange();

        $tickets = Ticket::with(['user', 'items.product'])
            ->whereBetween('created_at', [$range['start'], $range['end']])
            ->when($this->search, fn($q) => $q->where('id', 'like', "%{$this->search}%")
                ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$this->search}%")))
            ->orderByDesc('created_at')
            ->paginate(20);

        $selectedTicket = $this->selectedTicketId
            ? Ticket::with(['user', 'items.product'])->find($this->selectedTicketId)
            : null;

        $totalRevenue = Ticket::whereBetween('created_at', [$range['start'], $range['end']])->sum('total');
        $ticketCount = Ticket::whereBetween('created_at', [$range['start'], $range['end']])->count();

        return view('livewire.ticket-history', [
            'tickets' => $tickets,
            'selectedTicket' => $selectedTicket,
            'totalRevenue' => $totalRevenue,
            'ticketCount' => $ticketCount,
        ]);
    }
}
