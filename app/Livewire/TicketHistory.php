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
    public bool $showDetail = false;
    public ?int $selectedTicketId = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function setPeriod(string $period): void
    {
        $this->period = $period;
        $this->resetPage();
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

    private function getDateRange(): array
    {
        return match ($this->period) {
            'today' => ['start' => Carbon::today(), 'end' => Carbon::now()],
            'week' => ['start' => Carbon::now()->startOfWeek(), 'end' => Carbon::now()],
            'month' => ['start' => Carbon::now()->startOfMonth(), 'end' => Carbon::now()],
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

        $selectedTicket = $this->selectedTicketId ? Ticket::with(['user', 'items.product'])->find($this->selectedTicketId) : null;

        $totalRevenue = Ticket::whereBetween('created_at', [$range['start'], $range['end']])->sum('total');

        return view('livewire.ticket-history', [
            'tickets' => $tickets,
            'selectedTicket' => $selectedTicket,
            'totalRevenue' => $totalRevenue,
        ]);
    }
}
