<div class="space-y-6">
    <x-slot:header>Gastos</x-slot:header>

    <!-- Period Filter -->
    <div class="flex gap-2 overflow-x-auto no-scrollbar">
        @foreach(['week' => 'Semana', 'month' => 'Mes', 'year' => 'Año', 'all' => 'Todo'] as $key => $label)
        <button wire:click="setPeriod('{{ $key }}')"
            class="px-5 py-2 rounded-full font-medium text-sm transition-all whitespace-nowrap
            {{ $period === $key ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'bg-white/5 border border-white/10 text-slate-300' }}">
            {{ $label }}
        </button>
        @endforeach
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 gap-4">
        <div class="glass-card rounded-2xl p-4 space-y-1">
            <p class="text-white/40 text-xs font-bold uppercase tracking-widest">Compras</p>
            <p class="text-2xl font-bold text-white">&euro; {{ number_format($purchaseTotal, 2, ',', '.') }}</p>
        </div>
        <div class="glass-card rounded-2xl p-4 space-y-1">
            <p class="text-white/40 text-xs font-bold uppercase tracking-widest">Servicios</p>
            <p class="text-2xl font-bold text-white">&euro; {{ number_format($serviceTotal, 2, ',', '.') }}</p>
        </div>
    </div>

    <!-- Type Pills -->
    <div class="flex gap-3">
        <button wire:click="setType('')"
            class="flex h-10 shrink-0 items-center justify-center gap-2 rounded-full px-5 font-medium transition-all
            {{ !$typeFilter ? 'bg-primary text-white shadow-lg shadow-primary/40' : 'bg-white/5 border border-white/10 text-slate-300' }}">
            <span class="text-sm">Todos</span>
        </button>
        <button wire:click="setType('purchase')"
            class="flex h-10 shrink-0 items-center justify-center gap-2 rounded-full px-5 font-medium transition-all
            {{ $typeFilter === 'purchase' ? 'bg-primary text-white shadow-lg shadow-primary/40' : 'bg-white/5 border border-white/10 text-slate-300' }}">
            <span class="material-symbols-outlined text-sm">local_shipping</span>
            <span class="text-sm">Compras</span>
        </button>
        <button wire:click="setType('service')"
            class="flex h-10 shrink-0 items-center justify-center gap-2 rounded-full px-5 font-medium transition-all
            {{ $typeFilter === 'service' ? 'bg-primary text-white shadow-lg shadow-primary/40' : 'bg-white/5 border border-white/10 text-slate-300' }}">
            <span class="material-symbols-outlined text-sm">build</span>
            <span class="text-sm">Servicios</span>
        </button>
    </div>

    <!-- Search -->
    <div class="relative flex items-center w-full">
        <span class="material-symbols-outlined absolute left-4 text-white/30">search</span>
        <input wire:model.live.debounce.300ms="search" type="text"
            class="w-full h-12 pl-12 pr-4 bg-white/5 border border-white/5 rounded-2xl focus:ring-1 focus:ring-primary/50 text-slate-100 placeholder:text-white/30 transition-all"
            placeholder="Buscar factura...">
    </div>

    <!-- Invoice List -->
    <div class="space-y-3">
        @forelse($invoices as $invoice)
        <div class="glass-card flex items-center justify-between p-4 rounded-2xl">
            <div class="flex items-center gap-4">
                <div class="size-12 rounded-xl flex items-center justify-center border
                    {{ $invoice->type === 'purchase' ? 'bg-blue-500/10 border-blue-500/20' : 'bg-amber-500/10 border-amber-500/20' }}">
                    <span class="material-symbols-outlined text-xl {{ $invoice->type === 'purchase' ? 'text-blue-400' : 'text-amber-400' }}">
                        {{ $invoice->type === 'purchase' ? 'local_shipping' : 'build' }}
                    </span>
                </div>
                <div>
                    <p class="text-slate-100 font-semibold leading-tight">{{ $invoice->concept ?: $invoice->supplier?->name ?? 'Sin concepto' }}</p>
                    <p class="text-white/40 text-xs mt-0.5">
                        {{ $invoice->invoice_number ? '#' . $invoice->invoice_number . ' · ' : '' }}{{ $invoice->invoice_date->format('d/m/Y') }}
                        @if($invoice->supplier)
                        &middot; {{ $invoice->supplier->name }}
                        @endif
                    </p>
                </div>
            </div>
            <p class="text-xl font-bold text-slate-100">&euro; {{ number_format($invoice->total, 2, ',', '.') }}</p>
        </div>
        @empty
        <div class="glass-card rounded-xl p-8 text-center">
            <span class="material-symbols-outlined text-slate-500 text-4xl mb-2">receipt_long</span>
            <p class="text-slate-400">No hay gastos en este periodo</p>
        </div>
        @endforelse
    </div>

    {{ $invoices->links() }}

    <!-- FAB Import -->
    <a href="{{ route('invoices.import') }}"
        class="fixed right-6 bottom-28 size-14 rounded-full bg-primary text-white shadow-2xl shadow-primary/50 flex items-center justify-center transition-all active:scale-95 z-40 border border-white/10">
        <span class="material-symbols-outlined text-3xl">upload_file</span>
    </a>
</div>
