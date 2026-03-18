<div class="space-y-4">
    <x-slot:header>Tickets</x-slot:header>

    <!-- Period Filter -->
    <div class="flex gap-2 overflow-x-auto no-scrollbar">
        @foreach(['today' => 'Hoy', 'week' => 'Semana', 'month' => 'Mes', 'custom' => 'Personalizado', 'all' => 'Todo'] as $key => $label)
        <button wire:click="setPeriod('{{ $key }}')"
            class="px-4 py-2 rounded-full font-medium text-sm transition-all whitespace-nowrap
            {{ $period === $key ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'bg-white/5 border border-white/10 text-slate-300' }}">
            {{ $label }}
        </button>
        @endforeach
    </div>

    <!-- Custom date range -->
    @if($period === 'custom')
    <div class="flex gap-3">
        <input wire:model.live="dateFrom" type="date"
            class="flex-1 h-10 px-3 bg-white/5 border border-white/5 rounded-xl text-sm text-slate-100 focus:ring-1 focus:ring-primary/50">
        <input wire:model.live="dateTo" type="date"
            class="flex-1 h-10 px-3 bg-white/5 border border-white/5 rounded-xl text-sm text-slate-100 focus:ring-1 focus:ring-primary/50">
    </div>
    @endif

    <!-- Summary -->
    <div class="grid grid-cols-2 gap-3">
        <div class="glass-card rounded-2xl p-4">
            <p class="text-white/40 text-xs font-bold uppercase tracking-widest">Total</p>
            <p class="text-2xl font-bold text-white mt-1">&euro; {{ number_format($totalRevenue, 2, ',', '.') }}</p>
        </div>
        <div class="glass-card rounded-2xl p-4">
            <p class="text-white/40 text-xs font-bold uppercase tracking-widest">Tickets</p>
            <p class="text-2xl font-bold text-white mt-1">{{ $ticketCount }}</p>
        </div>
    </div>

    <!-- Search -->
    <div class="relative">
        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-white/30">search</span>
        <input wire:model.live.debounce.300ms="search" type="text"
            class="w-full h-12 pl-12 pr-4 bg-white/5 border border-white/5 rounded-2xl focus:ring-1 focus:ring-primary/50 text-slate-100 placeholder:text-white/30"
            placeholder="Buscar por # ticket o vendedor...">
    </div>

    <!-- Selection toolbar -->
    @if(count($selected) > 0)
    <div class="glass-card rounded-2xl p-3 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <button wire:click="clearSelection" class="size-8 rounded-lg bg-white/5 flex items-center justify-center">
                <span class="material-symbols-outlined text-slate-300 text-sm">close</span>
            </button>
            <span class="text-sm text-slate-200 font-medium">{{ count($selected) }} seleccionados</span>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ $this->getExportUrl() }}" target="_blank"
                class="h-8 px-3 rounded-lg bg-primary/20 text-primary text-xs font-bold flex items-center gap-1 hover:bg-primary/30 transition-all">
                <span class="material-symbols-outlined text-sm">download</span>
                PDF
            </a>
            <a href="{{ $this->getShareWhatsappUrl() }}" target="_blank"
                class="h-8 px-3 rounded-lg bg-[#25D366]/20 text-[#25D366] text-xs font-bold flex items-center gap-1 hover:bg-[#25D366]/30 transition-all">
                <span class="material-symbols-outlined text-sm">share</span>
                WhatsApp
            </a>
        </div>
    </div>
    @endif

    <!-- Select all toggle -->
    <div class="flex items-center justify-between px-1">
        <button wire:click="toggleSelectAll"
            class="flex items-center gap-2 text-xs text-white/40 hover:text-white/60 transition-colors">
            <div class="size-5 rounded border flex items-center justify-center transition-all
                {{ $selectAll ? 'bg-primary border-primary' : 'border-white/20' }}">
                @if($selectAll)
                <span class="material-symbols-outlined text-white text-xs">check</span>
                @endif
            </div>
            Seleccionar todos
        </button>
        <span class="text-xs text-white/30">{{ $ticketCount }} tickets</span>
    </div>

    <!-- Ticket List -->
    <div class="space-y-2">
        @forelse($tickets as $ticket)
        <div class="glass-card flex items-center gap-3 p-3 rounded-2xl transition-all hover:bg-white/[0.06]">
            <!-- Checkbox -->
            <button wire:click="toggleSelect({{ $ticket->id }})"
                class="size-6 shrink-0 rounded border flex items-center justify-center transition-all
                {{ in_array($ticket->id, $selected) ? 'bg-primary border-primary' : 'border-white/20' }}">
                @if(in_array($ticket->id, $selected))
                <span class="material-symbols-outlined text-white text-xs">check</span>
                @endif
            </button>

            <!-- Ticket info (clickable) -->
            <button wire:click="viewTicket({{ $ticket->id }})" class="flex-1 flex items-center justify-between text-left min-w-0">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="size-10 shrink-0 rounded-xl bg-primary/10 flex items-center justify-center border border-primary/20">
                        <span class="text-primary font-bold text-xs">#{{ $ticket->id }}</span>
                    </div>
                    <div class="min-w-0">
                        <p class="text-slate-100 font-medium text-sm truncate">{{ $ticket->user?->name ?? 'Usuario' }}</p>
                        <p class="text-white/40 text-xs">{{ $ticket->created_at->format('d/m H:i') }} &middot; {{ $ticket->items->sum('quantity') }} uds</p>
                    </div>
                </div>
                <p class="text-lg font-bold text-slate-100 shrink-0 ml-2">&euro; {{ number_format($ticket->total, 2, ',', '.') }}</p>
            </button>
        </div>
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
    <div class="fixed inset-0 z-50 flex items-end justify-center">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" wire:click="closeDetail"></div>
        <div class="relative w-full max-w-lg bg-background-dark border border-white/10 rounded-t-3xl p-5 space-y-4 max-h-[85vh] overflow-y-auto">
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

            <!-- Business info -->
            <div class="text-center py-2 border-b border-white/5">
                <p class="font-bold text-slate-200">{{ \App\Models\Setting::get('business_name', 'WetFish') }}</p>
                @if(\App\Models\Setting::get('business_cif'))
                <p class="text-white/40 text-xs">CIF: {{ \App\Models\Setting::get('business_cif') }}</p>
                @endif
                @if(\App\Models\Setting::get('business_address'))
                <p class="text-white/40 text-xs">{{ \App\Models\Setting::get('business_address') }}</p>
                @endif
                @if(\App\Models\Setting::get('business_phone'))
                <p class="text-white/40 text-xs">Tel: {{ \App\Models\Setting::get('business_phone') }}</p>
                @endif
            </div>

            <!-- Items -->
            <div class="space-y-2">
                @foreach($selectedTicket->items as $item)
                <div class="flex items-center justify-between py-2 border-b border-white/5">
                    <div>
                        <p class="text-slate-100 font-medium text-sm">{{ $item->product?->name ?? 'Producto eliminado' }}</p>
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
                    <span class="text-red-400">-&euro; {{ number_format($selectedTicket->discount_value, 2, ',', '.') }}</span>
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
            <div class="flex items-center gap-3 py-2 border-t border-white/5">
                <div class="size-8 rounded-full bg-primary/20 flex items-center justify-center">
                    <span class="material-symbols-outlined text-primary text-sm">person</span>
                </div>
                <span class="text-sm text-slate-300">{{ $selectedTicket->user?->name ?? 'Usuario' }}</span>
            </div>

            <!-- Actions -->
            <div class="flex gap-3 pt-2">
                <a href="{{ $this->getWhatsappUrl($selectedTicket->id) }}" target="_blank"
                    class="flex-1 h-12 rounded-xl bg-[#25D366]/20 border border-[#25D366]/30 text-[#25D366] font-semibold flex items-center justify-center gap-2 transition-all hover:bg-[#25D366]/30">
                    <span class="material-symbols-outlined text-xl">share</span>
                    WhatsApp
                </a>
                <a href="{{ route('tickets.export', ['ids' => $selectedTicket->id]) }}" target="_blank"
                    class="flex-1 h-12 rounded-xl bg-white/5 border border-white/10 text-slate-200 font-semibold flex items-center justify-center gap-2 transition-all hover:bg-white/10">
                    <span class="material-symbols-outlined text-xl">download</span>
                    PDF
                </a>
            </div>
        </div>
    </div>
    @endif
</div>
