<div class="space-y-6">
    <x-slot:header>Dashboard</x-slot:header>

    <!-- Period Filter Pills -->
    <div class="flex gap-2 overflow-x-auto pb-2 no-scrollbar">
        @foreach(['today' => 'Hoy', 'week' => 'Semana', 'month' => 'Mes'] as $key => $label)
        <button wire:click="setPeriod('{{ $key }}')"
            class="px-5 py-2 rounded-full font-medium text-sm transition-all whitespace-nowrap
            {{ $period === $key ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'glass-pill text-slate-300 hover:bg-primary/20' }}">
            {{ $label }}
        </button>
        @endforeach
    </div>

    <!-- Primary Metric -->
    <div class="glass rounded-xl p-8 relative overflow-hidden">
        <div class="space-y-1">
            <p class="text-slate-400 font-medium">Ingresos totales</p>
            <h2 class="text-5xl font-bold text-white tracking-tight">€ {{ number_format($revenue, 2, ',', '.') }}</h2>
        </div>
        <div class="mt-4 flex items-center gap-2 {{ $netProfit >= 0 ? 'text-emerald-400' : 'text-rose-400' }} font-semibold">
            <span class="material-symbols-outlined text-sm font-bold">{{ $netProfit >= 0 ? 'trending_up' : 'trending_down' }}</span>
            <span class="text-sm">Beneficio neto: € {{ number_format($netProfit, 2, ',', '.') }}</span>
        </div>
        <div class="absolute -bottom-12 -right-12 h-48 w-48 bg-primary/20 blur-3xl rounded-full"></div>
    </div>

    <!-- Secondary Metrics Grid -->
    <div class="grid grid-cols-2 gap-4">
        <div class="glass p-5 rounded-xl space-y-2">
            <p class="text-slate-400 text-sm">Beneficio neto</p>
            <p class="text-2xl font-bold text-white">€ {{ number_format($netProfit, 2, ',', '.') }}</p>
        </div>
        <a href="{{ route('tickets') }}" class="glass p-5 rounded-xl space-y-2 block">
            <p class="text-slate-400 text-sm">Tickets</p>
            <p class="text-2xl font-bold text-white">{{ $ticketCount }}</p>
        </a>
        <div class="glass p-5 rounded-xl space-y-2">
            <p class="text-slate-400 text-sm">Ticket promedio</p>
            <p class="text-2xl font-bold text-white">€ {{ number_format($avgTicket, 2, ',', '.') }}</p>
        </div>
        <div class="glass p-5 rounded-xl space-y-2">
            <p class="text-slate-400 text-sm">Unidades vendidas</p>
            <p class="text-2xl font-bold text-white">{{ $unitsSold }}</p>
        </div>
    </div>

    <!-- Stock Alert -->
    @if($criticalProducts > 0)
    <a href="{{ route('stock') }}" class="glass-card rounded-xl p-4 flex items-center gap-4 border-red-500/20">
        <div class="size-10 rounded-full bg-red-500/20 flex items-center justify-center">
            <span class="material-symbols-outlined text-red-400">warning</span>
        </div>
        <div>
            <p class="font-semibold text-red-400">{{ $criticalProducts }} producto(s) con stock crítico</p>
            <p class="text-xs text-slate-400">Pulsa para ver el stock</p>
        </div>
    </a>
    @endif
</div>
