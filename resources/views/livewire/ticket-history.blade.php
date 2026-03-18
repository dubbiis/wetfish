<div class="space-y-6">
    <x-slot:header>Tickets</x-slot:header>

    <!-- Period Filter -->
    <div class="flex gap-2 overflow-x-auto no-scrollbar">
        @foreach(['today' => 'Hoy', 'week' => 'Semana', 'month' => 'Mes', 'all' => 'Todo'] as $key => $label)
        <button wire:click="setPeriod('{{ $key }}')"
            class="px-5 py-2 rounded-full font-medium text-sm transition-all whitespace-nowrap
            {{ $period === $key ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'bg-white/5 border border-white/10 text-slate-300' }}">
            {{ $label }}
        </button>
        @endforeach
    </div>

    <!-- Revenue Summary -->
    <div class="glass-card rounded-2xl p-5 flex items-center justify-between">
        <div>
            <p class="text-white/40 text-xs font-bold uppercase tracking-widest">Total periodo</p>
            <p class="text-3xl font-bold text-white tracking-tight mt-1">&euro; {{ number_format($totalRevenue, 2, ',', '.') }}</p>
        </div>
        <div class="size-12 rounded-xl bg-primary/10 flex items-center justify-center border border-primary/20">
            <span class="material-symbols-outlined text-primary text-2xl">receipt_long</span>
        </div>
    </div>

    <!-- Search -->
    <div class="relative flex items-center w-full">
        <span class="material-symbols-outlined absolute left-4 text-white/30">search</span>
        <input wire:model.live.debounce.300ms="search" type="text"
            class="w-full h-12 pl-12 pr-4 bg-white/5 border border-white/5 rounded-2xl focus:ring-1 focus:ring-primary/50 text-slate-100 placeholder:text-white/30 transition-all"
            placeholder="Buscar por # ticket o vendedor...">
    </div>

    <!-- Ticket List -->
    <div class="space-y-3">
        @forelse($tickets as $ticket)
        <button wire:click="viewTicket({{ $ticket->id }})"
            class="w-full glass-card flex items-center justify-between p-4 rounded-2xl transition-all hover:bg-white/[0.06] text-left">
            <div class="flex items-center gap-4">
                <div class="size-12 rounded-xl bg-primary/10 flex items-center justify-center border border-primary/20">
                    <span class="text-primary font-bold text-sm">#{{ $ticket->id }}</span>
                </div>
                <div>
                    <p class="text-slate-100 font-semibold">{{ $ticket->user?->name ?? 'Usuario' }}</p>
                    <p class="text-white/40 text-xs">{{ $ticket->created_at->format('d/m/Y H:i') }} &middot; {{ $ticket->items->count() }} uds</p>
                </div>
            </div>
            <div class="text-right">
                <p class="text-xl font-bold text-slate-100">&euro; {{ number_format($ticket->total, 2, ',', '.') }}</p>
            </div>
        </button>
        @empty
        <div class="glass-card rounded-xl p-8 text-center">
            <span class="material-symbols-outlined text-slate-500 text-4xl mb-2">receipt</span>
            <p class="text-slate-400">No hay tickets en este periodo</p>
        </div>
        @endforelse
    </div>

    {{ $tickets->links() }}

    <!-- Ticket Detail Modal -->
    @if($showDetail && $selectedTicket)
    <div class="fixed inset-0 z-50 flex items-end justify-center" x-data x-transition>
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" wire:click="closeDetail"></div>
        <div class="relative w-full max-w-lg bg-background-dark border border-white/10 rounded-t-3xl p-6 space-y-5 max-h-[85vh] overflow-y-auto">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-bold text-white">Ticket #{{ $selectedTicket->id }}</h3>
                    <p class="text-white/40 text-sm">{{ $selectedTicket->created_at->format('d/m/Y H:i') }}</p>
                </div>
                <button wire:click="closeDetail" class="size-10 rounded-full bg-white/5 flex items-center justify-center">
                    <span class="material-symbols-outlined text-slate-300">close</span>
                </button>
            </div>

            <!-- Items -->
            <div class="space-y-3">
                @foreach($selectedTicket->items as $item)
                <div class="flex items-center justify-between py-2 border-b border-white/5">
                    <div>
                        <p class="text-slate-100 font-medium">{{ $item->product?->name ?? 'Producto eliminado' }}</p>
                        <p class="text-white/40 text-xs">{{ $item->quantity }} x &euro; {{ number_format($item->unit_price, 2, ',', '.') }}</p>
                    </div>
                    <p class="text-slate-100 font-semibold">&euro; {{ number_format($item->subtotal, 2, ',', '.') }}</p>
                </div>
                @endforeach
            </div>

            <!-- Totals -->
            <div class="space-y-2 pt-2 border-t border-white/10">
                <div class="flex justify-between text-sm">
                    <span class="text-white/50">Subtotal</span>
                    <span class="text-slate-200">&euro; {{ number_format($selectedTicket->subtotal, 2, ',', '.') }}</span>
                </div>
                @if($selectedTicket->discount_value > 0)
                <div class="flex justify-between text-sm">
                    <span class="text-white/50">Descuento</span>
                    <span class="text-red-400">-{{ $selectedTicket->discount_type === 'percentage' ? $selectedTicket->discount_value . '%' : '€ ' . number_format($selectedTicket->discount_value, 2, ',', '.') }}</span>
                </div>
                @endif
                <div class="flex justify-between text-sm">
                    <span class="text-white/50">IVA ({{ $selectedTicket->tax_rate }}%)</span>
                    <span class="text-slate-200">&euro; {{ number_format($selectedTicket->tax_amount, 2, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-lg font-bold pt-2 border-t border-white/10">
                    <span class="text-white">Total</span>
                    <span class="text-primary">&euro; {{ number_format($selectedTicket->total, 2, ',', '.') }}</span>
                </div>
            </div>

            <!-- Seller -->
            <div class="flex items-center gap-3 pt-2">
                <div class="size-8 rounded-full bg-primary/20 flex items-center justify-center">
                    <span class="material-symbols-outlined text-primary text-sm">person</span>
                </div>
                <span class="text-sm text-slate-300">{{ $selectedTicket->user?->name ?? 'Usuario' }}</span>
            </div>
        </div>
    </div>
    @endif
</div>
