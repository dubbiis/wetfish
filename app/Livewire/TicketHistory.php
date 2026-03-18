<?php

namespace App\Livewire;

use App\Models\Ticket;
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

    public function getExportUrl(): string
    {
        if (empty($this->selected)) return '';
        $ids = implode(',', $this->selected);
        return route('tickets.export', ['ids' => $ids]);
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
