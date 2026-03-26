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
    <a href="{{ route('tickets') }}" class="glass rounded-xl p-6 relative overflow-hidden block">
        <p class="text-xs font-bold uppercase tracking-widest text-white/40 mb-4">Ventas</p>
        <div class="space-y-1">
            <p class="text-slate-400 text-sm">Ingresos totales</p>
            <h2 class="text-5xl font-bold text-white tracking-tight">&euro; {{ number_format($revenue, 2, ',', '.') }}</h2>
        </div>
        <div class="mt-3 flex items-center gap-2 {{ $netProfit >= 0 ? 'text-emerald-400' : 'text-rose-400' }} font-semibold">
            <span class="material-symbols-outlined text-sm">{{ $netProfit >= 0 ? 'trending_up' : 'trending_down' }}</span>
            <span class="text-sm">Beneficio neto: &euro; {{ number_format($netProfit, 2, ',', '.') }} ({{ $marginPct }}%)</span>
        </div>
        <!-- Sparkline ventas diarias -->
        @if(count($sparklineData) > 1)
        @php $sparkMax = max($sparklineData) ?: 1; @endphp
        <div class="mt-4 flex items-end gap-[2px] h-12">
            @foreach($sparklineData as $i => $val)
            <div class="flex-1 rounded-t transition-all {{ $val > 0 ? 'bg-primary/40' : 'bg-white/5' }}"
                style="height: {{ max(round($val / $sparkMax * 100), 4) }}%"
                title="{{ $sparklineLabels[$i] ?? '' }}: € {{ number_format($val, 2, ',', '.') }}"></div>
            @endforeach
        </div>
        <div class="flex justify-between mt-1">
            <span class="text-[9px] text-white/20">{{ $sparklineLabels[0] ?? '' }}</span>
            <span class="text-[9px] text-white/20">{{ end($sparklineLabels) ?: '' }}</span>
        </div>
        @endif
        <div class="absolute -bottom-12 -right-12 h-48 w-48 bg-primary/20 blur-3xl rounded-full"></div>
    </a>

    <div class="grid grid-cols-2 gap-3">
        <a href="{{ route('tickets') }}" class="glass p-4 rounded-xl space-y-1 block">
            <p class="text-xs text-slate-400">Tickets</p>
            <p class="text-2xl font-bold text-white">{{ $ticketCount }}</p>
        </a>
        <a href="{{ route('tickets') }}" class="glass p-4 rounded-xl space-y-1 block">
            <p class="text-xs text-slate-400">Ticket promedio</p>
            <p class="text-2xl font-bold text-white">€ {{ number_format($avgTicket, 2, ',', '.') }}</p>
        </a>
        <a href="{{ route('stock') }}" class="glass p-4 rounded-xl space-y-1 block">
            <p class="text-xs text-slate-400">Unidades vendidas</p>
            <p class="text-2xl font-bold text-white">{{ $unitsSold }}</p>
        </a>
        <a href="{{ route('tickets') }}" class="glass p-4 rounded-xl space-y-1 block">
            <p class="text-xs text-slate-400">Mejor ticket</p>
            <p class="text-2xl font-bold text-white">€ {{ number_format($maxTicket, 2, ',', '.') }}</p>
        </a>
    </div>

    <!-- ── Sección 1b: Margen Real vs Objetivo ── -->
    <div class="glass-card rounded-2xl p-4 space-y-3">
        <div class="flex items-center justify-between">
            <p class="text-xs font-bold uppercase tracking-widest text-white/40">Margen Real</p>
            @if($priceAdjustmentActive)
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-amber-400/10 text-amber-400 border border-amber-400/20">
                Ajuste activo
            </span>
            @endif
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div class="space-y-1">
                <p class="text-xs text-slate-400">Margen real</p>
                <p class="text-3xl font-bold {{ $realMarginPct >= $targetMarginPct ? 'text-emerald-400' : ($realMarginPct >= $targetMarginPct * 0.7 ? 'text-amber-400' : 'text-rose-400') }}">
                    {{ $realMarginPct }}%
                </p>
            </div>
            <div class="space-y-1">
                <p class="text-xs text-slate-400">Margen objetivo</p>
                <p class="text-3xl font-bold text-primary">{{ $targetMarginPct }}%</p>
            </div>
        </div>

        <!-- Barra de progreso -->
        @php $progressPct = $targetMarginPct > 0 ? min(($realMarginPct / $targetMarginPct) * 100, 100) : 0; @endphp
        <div class="h-2 bg-white/5 rounded-full overflow-hidden">
            <div class="h-full rounded-full transition-all {{ $realMarginPct >= $targetMarginPct ? 'bg-emerald-400' : ($realMarginPct >= $targetMarginPct * 0.7 ? 'bg-amber-400' : 'bg-rose-400') }}"
                 style="width: {{ max($progressPct, 0) }}%"></div>
        </div>

        <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-500">
            <span>Coste op./unidad: € {{ number_format($costPerUnit, 4, ',', '.') }}</span>
            <span>Unidades: {{ number_format($totalUnitsInStock) }}</span>
            <span>Período: {{ $expensePeriodLabel }}</span>
        </div>
    </div>

    <!-- Sugerencia hora pico -->
    @if($showPeakSuggestion)
    <div class="glass-card rounded-2xl p-4 border border-amber-400/20 space-y-3">
        <div class="flex items-start gap-3">
            <span class="material-symbols-outlined text-amber-400 text-xl mt-0.5">trending_up</span>
            <div class="flex-1">
                <p class="text-sm font-semibold text-white">Hora pico detectada ({{ str_pad($peakHour, 2, '0', STR_PAD_LEFT) }}:00h)</p>
                <p class="text-xs text-slate-400 mt-1">
                    Margen real <span class="text-amber-400 font-semibold">{{ $realMarginPct }}%</span>
                    — Objetivo <span class="text-primary font-semibold">{{ $targetMarginPct }}%</span>
                    — Subir <span class="text-white font-semibold">+{{ $suggestedAdjustment }}%</span>
                </p>
            </div>
        </div>
        <a href="{{ route('settings') }}"
            class="flex items-center justify-center gap-2 h-10 w-full rounded-xl bg-amber-400/10 border border-amber-400/20 text-amber-400 text-sm font-semibold transition-all active:scale-[0.98]">
            <span class="material-symbols-outlined text-base">tune</span>
            Ajustar precios
        </a>
    </div>
    @endif

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

        <!-- Gráfico de barras horizontal -->
        @php
            $costMax = max($revenue, $purchaseCosts, $serviceCosts, $operationalCosts) ?: 1;
        @endphp
        <div class="space-y-3 mb-4">
            <div>
                <div class="flex justify-between text-xs mb-1">
                    <span class="text-emerald-400">Ingresos</span>
                    <span class="text-emerald-400 font-bold">&euro; {{ number_format($revenue, 2, ',', '.') }}</span>
                </div>
                <div class="h-3 bg-white/5 rounded-full overflow-hidden">
                    <div class="h-full bg-emerald-500/60 rounded-full transition-all" style="width: {{ round($revenue / $costMax * 100) }}%"></div>
                </div>
            </div>
            <div>
                <div class="flex justify-between text-xs mb-1">
                    <span class="text-rose-400">Compras</span>
                    <span class="text-rose-400 font-bold">&euro; {{ number_format($purchaseCosts, 2, ',', '.') }}</span>
                </div>
                <div class="h-3 bg-white/5 rounded-full overflow-hidden">
                    <div class="h-full bg-rose-500/60 rounded-full transition-all" style="width: {{ round($purchaseCosts / $costMax * 100) }}%"></div>
                </div>
            </div>
            <div>
                <div class="flex justify-between text-xs mb-1">
                    <span class="text-orange-400">Servicios</span>
                    <span class="text-orange-400 font-bold">&euro; {{ number_format($serviceCosts, 2, ',', '.') }}</span>
                </div>
                <div class="h-3 bg-white/5 rounded-full overflow-hidden">
                    <div class="h-full bg-orange-500/60 rounded-full transition-all" style="width: {{ round($serviceCosts / $costMax * 100) }}%"></div>
                </div>
            </div>
            <div>
                <div class="flex justify-between text-xs mb-1">
                    <span class="text-amber-400">Gastos operativos</span>
                    <span class="text-amber-400 font-bold">&euro; {{ number_format($operationalCosts, 2, ',', '.') }}</span>
                </div>
                <div class="h-3 bg-white/5 rounded-full overflow-hidden">
                    <div class="h-full bg-amber-500/60 rounded-full transition-all" style="width: {{ round($operationalCosts / $costMax * 100) }}%"></div>
                </div>
            </div>
        </div>

        <!-- Doble margen -->
        <div class="mt-4 pt-3 border-t border-white/5 grid grid-cols-2 gap-3">
            <div class="bg-white/5 rounded-xl p-3 space-y-1">
                <p class="text-[10px] font-bold uppercase tracking-widest text-white/40">Beneficio (sin IVA)</p>
                <p class="text-lg font-bold {{ $netProfit >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">&euro; {{ number_format($netProfit, 2, ',', '.') }}</p>
                <p class="text-xs text-slate-500">Margen {{ $marginPct }}%</p>
            </div>
            <div class="bg-white/5 rounded-xl p-3 space-y-1">
                <p class="text-[10px] font-bold uppercase tracking-widest text-white/40">Beneficio (con IVA)</p>
                <p class="text-lg font-bold {{ $netProfitWithTax >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">&euro; {{ number_format($netProfitWithTax, 2, ',', '.') }}</p>
                <p class="text-xs text-slate-500">Margen {{ $marginPctWithTax }}%</p>
            </div>
        </div>
    </div>

    <!-- ── Sección 4a: Resumen Fiscal (IVA) ── -->
    <div class="glass-card rounded-2xl p-4">
        <div class="flex items-center justify-between mb-4">
            <p class="text-xs font-bold uppercase tracking-widest text-white/40">Resumen Fiscal</p>
            <a href="{{ route('fiscal') }}" class="text-xs text-primary font-medium hover:text-primary/80 transition-all">Ver modelos fiscales →</a>
        </div>
        <div class="grid grid-cols-3 gap-3">
            <div class="space-y-1">
                <p class="text-xs text-slate-400">IVA cobrado</p>
                <p class="text-lg font-bold text-emerald-400">&euro; {{ number_format($ivaRepercutido, 2, ',', '.') }}</p>
                <p class="text-[10px] text-slate-500">Repercutido</p>
            </div>
            <div class="space-y-1">
                <p class="text-xs text-slate-400">IVA pagado</p>
                <p class="text-lg font-bold text-rose-400">&euro; {{ number_format($ivaSoportado, 2, ',', '.') }}</p>
                <p class="text-[10px] text-slate-500">Soportado</p>
            </div>
            <div class="space-y-1">
                <p class="text-xs text-slate-400">A pagar</p>
                <p class="text-lg font-bold {{ $ivaBalance >= 0 ? 'text-amber-400' : 'text-emerald-400' }}">&euro; {{ number_format($ivaBalance, 2, ',', '.') }}</p>
                <p class="text-[10px] text-slate-500">{{ $ivaBalance >= 0 ? 'Debes' : 'A favor' }}</p>
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
                            <div class="text-right">
                                <span class="text-sm font-bold text-white">&euro; {{ number_format($exp['total'], 2, ',', '.') }}</span>
                                <span class="text-[10px] text-white/30 block">base {{ number_format($exp['base'], 2, ',', '.') }} + IVA {{ number_format($exp['tax'], 2, ',', '.') }}</span>
                            </div>
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
    <a href="{{ route('stock') }}" class="glass-card rounded-2xl p-4 block">
        <p class="text-xs font-bold uppercase tracking-widest text-white/40 mb-4">Inventario</p>
        <div class="grid grid-cols-2 gap-3">
            <div class="space-y-1">
                <p class="text-xs text-slate-400">Valor total</p>
                <p class="text-lg font-bold text-white">&euro; {{ number_format($inventoryValue, 0, ',', '.') }}</p>
            </div>
            <div class="space-y-1">
                <p class="text-xs text-slate-400">Stock cr&iacute;tico</p>
                <p class="text-lg font-bold {{ $criticalProducts > 0 ? 'text-rose-400' : 'text-emerald-400' }}">{{ $criticalProducts }}</p>
            </div>
            <div class="space-y-1">
                <p class="text-xs text-slate-400">Sin movimiento</p>
                <p class="text-lg font-bold text-slate-300">{{ $inactiveProducts }}</p>
            </div>
            <div class="space-y-1">
                <p class="text-xs text-slate-400 flex items-center gap-1">
                    <span class="material-symbols-outlined text-xs">heart_broken</span> Merma
                </p>
                <p class="text-lg font-bold {{ $lossUnits > 0 ? 'text-rose-400' : 'text-emerald-400' }}">
                    {{ $lossUnits }} uds
                </p>
                @if($lossCost > 0)
                <p class="text-[10px] text-rose-400/60">&euro; {{ number_format($lossCost, 2, ',', '.') }} perdidos</p>
                @endif
            </div>
        </div>
        @if($criticalProducts > 0)
        <a href="{{ route('stock') }}" class="mt-4 flex items-center gap-2 text-rose-400 text-sm font-medium">
            <span class="material-symbols-outlined text-base">warning</span>
            Ver productos con stock crítico
        </a>
        @endif
    </a>

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

    <!-- ── Sección 7: Uso de IA ── -->
    <div class="glass-card rounded-2xl p-4 space-y-3">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">smart_toy</span>
                <h3 class="text-sm font-bold uppercase tracking-widest text-white/50">Uso de IA</h3>
            </div>
            <span class="text-xs text-white/30">{{ now()->translatedFormat('F Y') }}</span>
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div class="bg-white/5 rounded-xl p-3 space-y-1">
                <p class="text-xs text-white/40">Llamadas</p>
                <p class="text-lg font-bold text-white">{{ $aiCallsMonth }}</p>
            </div>
            <div class="bg-white/5 rounded-xl p-3 space-y-1">
                <p class="text-xs text-white/40">Coste mes</p>
                <p class="text-lg font-bold {{ $aiCostMonth > 1 ? 'text-amber-400' : 'text-emerald-400' }}">
                    {{ number_format($aiCostMonth, 4, ',', '.') }} &euro;
                </p>
            </div>
            <div class="bg-white/5 rounded-xl p-3 space-y-1">
                <p class="text-xs text-white/40">Tokens entrada</p>
                <p class="text-sm font-semibold text-white">{{ number_format($aiTokensIn, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white/5 rounded-xl p-3 space-y-1">
                <p class="text-xs text-white/40">Tokens salida</p>
                <p class="text-sm font-semibold text-white">{{ number_format($aiTokensOut, 0, ',', '.') }}</p>
            </div>
        </div>

        @if($aiCallsMonth > 0)
        <p class="text-xs text-white/30 text-center">
            Media: {{ number_format(($aiTokensIn + $aiTokensOut) / max($aiCallsMonth, 1), 0, ',', '.') }} tokens/llamada
            · {{ number_format($aiCostMonth / max($aiCallsMonth, 1), 4, ',', '.') }} &euro;/llamada
        </p>
        @else
        <p class="text-xs text-white/30 text-center">Sin uso de IA este mes</p>
        @endif
    </div>
</div>
