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

    <!-- ── Sección 1: Métricas de ventas ── -->
    <div class="glass rounded-xl p-6 relative overflow-hidden">
        <p class="text-xs font-bold uppercase tracking-widest text-white/40 mb-4">Ventas</p>
        <div class="space-y-1">
            <p class="text-slate-400 text-sm">Ingresos totales</p>
            <h2 class="text-5xl font-bold text-white tracking-tight">€ {{ number_format($revenue, 2, ',', '.') }}</h2>
        </div>
        <div class="mt-3 flex items-center gap-2 {{ $netProfit >= 0 ? 'text-emerald-400' : 'text-rose-400' }} font-semibold">
            <span class="material-symbols-outlined text-sm">{{ $netProfit >= 0 ? 'trending_up' : 'trending_down' }}</span>
            <span class="text-sm">Beneficio neto: € {{ number_format($netProfit, 2, ',', '.') }} ({{ $marginPct }}%)</span>
        </div>
        <div class="absolute -bottom-12 -right-12 h-48 w-48 bg-primary/20 blur-3xl rounded-full"></div>
    </div>

    <div class="grid grid-cols-2 gap-3">
        <div class="glass p-4 rounded-xl space-y-1">
            <p class="text-xs text-slate-400">Tickets</p>
            <p class="text-2xl font-bold text-white">{{ $ticketCount }}</p>
        </div>
        <div class="glass p-4 rounded-xl space-y-1">
            <p class="text-xs text-slate-400">Ticket promedio</p>
            <p class="text-2xl font-bold text-white">€ {{ number_format($avgTicket, 2, ',', '.') }}</p>
        </div>
        <div class="glass p-4 rounded-xl space-y-1">
            <p class="text-xs text-slate-400">Unidades vendidas</p>
            <p class="text-2xl font-bold text-white">{{ $unitsSold }}</p>
        </div>
        <div class="glass p-4 rounded-xl space-y-1">
            <p class="text-xs text-slate-400">Mejor ticket</p>
            <p class="text-2xl font-bold text-white">€ {{ number_format($maxTicket, 2, ',', '.') }}</p>
        </div>
    </div>

    <!-- ── Sección 2: Top Productos ── -->
    <div class="glass-card rounded-2xl p-4" x-data="{ tab: 'qty' }">
        <p class="text-xs font-bold uppercase tracking-widest text-white/40 mb-3">Top Productos</p>

        <div class="flex gap-2 mb-4">
            <button @click="tab = 'qty'"
                :class="tab === 'qty' ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'glass-pill text-slate-400 hover:bg-primary/20'"
                class="px-4 py-1.5 rounded-full text-xs font-medium transition-all">
                Por cantidad
            </button>
            <button @click="tab = 'revenue'"
                :class="tab === 'revenue' ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'glass-pill text-slate-400 hover:bg-primary/20'"
                class="px-4 py-1.5 rounded-full text-xs font-medium transition-all">
                Por ingresos
            </button>
        </div>

        <!-- Por cantidad -->
        <div x-show="tab === 'qty'" class="space-y-3">
            @if($topProductsByQty->isEmpty())
                <p class="text-slate-500 text-sm text-center py-4">Sin ventas en este período</p>
            @else
                @php $maxQty = $topProductsByQty->first()['total'] ?? 1; @endphp
                @foreach($topProductsByQty as $i => $product)
                    @php $pct = $maxQty > 0 ? ($product['total'] / $maxQty) * 100 : 0; @endphp
                    <div class="space-y-1">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-slate-200 truncate flex-1 mr-2">{{ $product['name'] }}</span>
                            <span class="text-sm font-bold text-white whitespace-nowrap">{{ $product['total'] }} uds</span>
                        </div>
                        <div class="h-1.5 bg-white/5 rounded-full overflow-hidden">
                            <div class="h-full bg-primary rounded-full transition-all" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>

        <!-- Por ingresos -->
        <div x-show="tab === 'revenue'" x-cloak class="space-y-3">
            @if($topProductsByRevenue->isEmpty())
                <p class="text-slate-500 text-sm text-center py-4">Sin ventas en este período</p>
            @else
                @php $maxRev = $topProductsByRevenue->first()['total'] ?? 1; @endphp
                @foreach($topProductsByRevenue as $product)
                    @php $pct = $maxRev > 0 ? ($product['total'] / $maxRev) * 100 : 0; @endphp
                    <div class="space-y-1">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-slate-200 truncate flex-1 mr-2">{{ $product['name'] }}</span>
                            <span class="text-sm font-bold text-white whitespace-nowrap">€ {{ number_format($product['total'], 2, ',', '.') }}</span>
                        </div>
                        <div class="h-1.5 bg-white/5 rounded-full overflow-hidden">
                            <div class="h-full bg-primary rounded-full transition-all" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>

    <!-- ── Sección 3: Por Categoría ── -->
    <div class="glass-card rounded-2xl p-4">
        <p class="text-xs font-bold uppercase tracking-widest text-white/40 mb-3">Ventas por Categoría</p>
        @if($salesByCategory->isEmpty())
            <p class="text-slate-500 text-sm text-center py-4">Sin ventas en este período</p>
        @else
            @php $maxCatRev = $salesByCategory->first()['revenue'] ?? 1; @endphp
            <div class="space-y-3">
                @foreach($salesByCategory as $data)
                    @php $pct = $maxCatRev > 0 ? ($data['revenue'] / $maxCatRev) * 100 : 0; @endphp
                    <div class="space-y-1">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-slate-200 truncate flex-1 mr-2">{{ $data['name'] }}</span>
                            <div class="text-right whitespace-nowrap">
                                <span class="text-sm font-bold text-white">€ {{ number_format($data['revenue'], 2, ',', '.') }}</span>
                                <span class="text-xs text-slate-500 ml-2">{{ $data['units'] }} uds</span>
                            </div>
                        </div>
                        <div class="h-1.5 bg-white/5 rounded-full overflow-hidden">
                            <div class="h-full bg-primary/70 rounded-full transition-all" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- ── Sección 4: Costes vs Ingresos ── -->
    <div class="glass-card rounded-2xl p-4">
        <p class="text-xs font-bold uppercase tracking-widest text-white/40 mb-4">Costes vs Ingresos</p>
        <div class="grid grid-cols-2 gap-3">
            <div class="space-y-1">
                <p class="text-xs text-slate-400">Ingresos</p>
                <p class="text-xl font-bold text-emerald-400">€ {{ number_format($revenue, 2, ',', '.') }}</p>
            </div>
            <div class="space-y-1">
                <p class="text-xs text-slate-400">Margen neto</p>
                <p class="text-xl font-bold {{ $marginPct >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">{{ $marginPct }}%</p>
            </div>
            <div class="space-y-1">
                <p class="text-xs text-slate-400">Compras</p>
                <p class="text-xl font-bold text-rose-400">€ {{ number_format($purchaseCosts, 2, ',', '.') }}</p>
            </div>
            <div class="space-y-1">
                <p class="text-xs text-slate-400">Servicios</p>
                <p class="text-xl font-bold text-orange-400">€ {{ number_format($serviceCosts, 2, ',', '.') }}</p>
            </div>
            <div class="space-y-1 col-span-2 pt-2 border-t border-white/5">
                <p class="text-xs text-slate-400">Gastos operativos</p>
                <p class="text-xl font-bold text-amber-400">€ {{ number_format($operationalCosts, 2, ',', '.') }}</p>
            </div>
        </div>
    </div>

    <!-- ── Sección 4b: Gastos Operativos ── -->
    <div class="glass-card rounded-2xl p-4">
        <div class="flex items-center justify-between mb-4">
            <p class="text-xs font-bold uppercase tracking-widest text-white/40">Gastos Operativos</p>
            <a href="{{ route('expenses') }}" class="text-xs text-primary font-medium">Ver todos</a>
        </div>
        @if($expensesByCategory->isEmpty())
            <p class="text-slate-500 text-sm text-center py-4">Sin gastos registrados en este período</p>
        @else
            @php $maxExp = $expensesByCategory->first()['total'] ?? 1; @endphp
            <div class="space-y-3">
                @foreach($expensesByCategory as $exp)
                    @php $pct = $maxExp > 0 ? ($exp['total'] / $maxExp) * 100 : 0; @endphp
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-amber-400 text-base">{{ $exp['icon'] }}</span>
                            <span class="text-sm text-slate-200 flex-1">{{ $exp['name'] }}</span>
                            <span class="text-sm font-bold text-white">€ {{ number_format($exp['total'], 2, ',', '.') }}</span>
                        </div>
                        <div class="h-1.5 bg-white/5 rounded-full overflow-hidden">
                            <div class="h-full bg-amber-400/70 rounded-full" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- ── Sección 5: Inventario ── -->
    <div class="glass-card rounded-2xl p-4">
        <p class="text-xs font-bold uppercase tracking-widest text-white/40 mb-4">Inventario</p>
        <div class="grid grid-cols-3 gap-3">
            <div class="space-y-1">
                <p class="text-xs text-slate-400">Valor total</p>
                <p class="text-lg font-bold text-white">€ {{ number_format($inventoryValue, 0, ',', '.') }}</p>
            </div>
            <div class="space-y-1">
                <p class="text-xs text-slate-400">Stock crítico</p>
                <p class="text-lg font-bold {{ $criticalProducts > 0 ? 'text-rose-400' : 'text-emerald-400' }}">{{ $criticalProducts }}</p>
            </div>
            <div class="space-y-1">
                <p class="text-xs text-slate-400">Sin movimiento</p>
                <p class="text-lg font-bold text-slate-300">{{ $inactiveProducts }}</p>
            </div>
        </div>
        @if($criticalProducts > 0)
        <a href="{{ route('stock') }}" class="mt-4 flex items-center gap-2 text-rose-400 text-sm font-medium">
            <span class="material-symbols-outlined text-base">warning</span>
            Ver productos con stock crítico
        </a>
        @endif
    </div>

    <!-- ── Sección 6: Actividad ── -->
    <div class="glass-card rounded-2xl p-4 pb-safe">
        <p class="text-xs font-bold uppercase tracking-widest text-white/40 mb-4">Actividad</p>
        <div class="grid grid-cols-2 gap-3">
            @if($peakHour !== null)
            <div class="space-y-1">
                <p class="text-xs text-slate-400">Hora punta</p>
                <p class="text-xl font-bold text-white">{{ str_pad($peakHour, 2, '0', STR_PAD_LEFT) }}:00 h</p>
            </div>
            @endif
            @if($bestDay)
            <div class="space-y-1">
                <p class="text-xs text-slate-400">Mejor día</p>
                <p class="text-xl font-bold text-white">{{ $bestDay }}</p>
            </div>
            @endif
            @if($lastTicket)
            <div class="space-y-1 col-span-2">
                <p class="text-xs text-slate-400">Último ticket</p>
                <p class="text-sm font-semibold text-slate-300">
                    € {{ number_format($lastTicket->total, 2, ',', '.') }}
                    <span class="text-slate-500 font-normal ml-1">— {{ $lastTicket->created_at->diffForHumans() }}</span>
                </p>
            </div>
            @endif
            @if(!$peakHour && !$lastTicket)
            <p class="col-span-2 text-slate-500 text-sm text-center py-2">Sin actividad en este período</p>
            @endif
        </div>
    </div>
</div>
