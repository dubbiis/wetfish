<div class="space-y-6">
    <x-slot:header>Estimaciones Fiscales</x-slot:header>

    <!-- Año -->
    <div class="flex items-center justify-center gap-3">
        <button wire:click="setYear({{ $selectedYear - 1 }})"
            class="size-10 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center text-white/60 hover:text-white transition-all">
            <span class="material-symbols-outlined">chevron_left</span>
        </button>
        <span class="text-2xl font-bold text-white w-20 text-center">{{ $selectedYear }}</span>
        <button wire:click="setYear({{ $selectedYear + 1 }})"
            class="size-10 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center text-white/60 hover:text-white transition-all">
            <span class="material-symbols-outlined">chevron_right</span>
        </button>
    </div>

    <!-- Trimestres -->
    <div class="grid grid-cols-4 gap-2">
        @foreach([1 => 'T1', 2 => 'T2', 3 => 'T3', 4 => 'T4'] as $q => $label)
        <button wire:click="setQuarter({{ $q }})"
            class="h-10 rounded-xl font-semibold text-sm transition-all
            {{ $selectedQuarter === $q ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'bg-white/5 border border-white/10 text-slate-300' }}">
            {{ $label }}
        </button>
        @endforeach
    </div>

    <p class="text-center text-white/40 text-xs font-medium">{{ $quarterLabel }} {{ $selectedYear }}</p>

    <!-- Resumen rápido -->
    <div class="grid grid-cols-3 gap-3">
        <div class="glass rounded-xl p-3 text-center">
            <p class="text-[10px] font-bold uppercase tracking-widest text-white/40 mb-1">303</p>
            <p class="text-lg font-bold {{ $modelo303['resultado'] > 0 ? 'text-rose-400' : 'text-emerald-400' }}">
                &euro; {{ number_format(abs($modelo303['resultado']), 2, ',', '.') }}
            </p>
            <p class="text-[10px] {{ $modelo303['resultado'] > 0 ? 'text-rose-400/60' : 'text-emerald-400/60' }}">
                {{ $modelo303['resultado'] > 0 ? 'A pagar' : ($modelo303['resultado'] < 0 ? 'A favor' : 'Neutro') }}
            </p>
        </div>
        <div class="glass rounded-xl p-3 text-center">
            <p class="text-[10px] font-bold uppercase tracking-widest text-white/40 mb-1">130</p>
            <p class="text-lg font-bold {{ $modelo130['cuota'] > 0 ? 'text-rose-400' : 'text-emerald-400' }}">
                &euro; {{ number_format($modelo130['cuota'], 2, ',', '.') }}
            </p>
            <p class="text-[10px] {{ $modelo130['cuota'] > 0 ? 'text-rose-400/60' : 'text-emerald-400/60' }}">
                {{ $modelo130['cuota'] > 0 ? 'A pagar' : 'Sin cuota' }}
            </p>
        </div>
        <div class="glass rounded-xl p-3 text-center">
            <p class="text-[10px] font-bold uppercase tracking-widest text-white/40 mb-1">Renta</p>
            <p class="text-lg font-bold text-amber-400">
                &euro; {{ number_format(abs($renta['restanteRenta']), 2, ',', '.') }}
            </p>
            <p class="text-[10px] text-amber-400/60">
                {{ $renta['restanteRenta'] > 0 ? 'Estimado' : ($renta['restanteRenta'] < 0 ? 'A favor' : '—') }}
            </p>
        </div>
    </div>

    <!-- Modelo 303 -->
    <div class="glass-card rounded-2xl p-5 space-y-4">
        <div class="flex items-center gap-3">
            <div class="size-10 rounded-xl bg-blue-500/10 border border-blue-500/20 flex items-center justify-center">
                <span class="material-symbols-outlined text-blue-400">account_balance</span>
            </div>
            <div>
                <h3 class="text-white font-bold">Modelo 303 — IVA Trimestral</h3>
                <p class="text-xs text-white/40">{{ $quarterLabel }} {{ $selectedYear }}</p>
            </div>
        </div>

        <div class="space-y-2">
            <div class="flex items-center justify-between py-2 border-b border-white/5">
                <span class="text-sm text-slate-300">IVA cobrado a clientes (repercutido)</span>
                <span class="text-sm font-bold text-emerald-400">&euro; {{ number_format($modelo303['ivaRepercutido'], 2, ',', '.') }}</span>
            </div>
            <div class="flex items-center justify-between py-2 border-b border-white/5">
                <span class="text-sm text-slate-300">IVA pagado en gastos operativos</span>
                <span class="text-sm font-bold text-rose-400">- &euro; {{ number_format($modelo303['ivaSoportadoExpenses'], 2, ',', '.') }}</span>
            </div>
            <div class="flex items-center justify-between py-2 border-b border-white/5">
                <span class="text-sm text-slate-300">IVA pagado en facturas proveedor</span>
                <span class="text-sm font-bold text-rose-400">- &euro; {{ number_format($modelo303['ivaSoportadoInvoices'], 2, ',', '.') }}</span>
            </div>
            <div class="flex items-center justify-between py-3 rounded-xl px-3 {{ $modelo303['resultado'] > 0 ? 'bg-rose-500/10' : 'bg-emerald-500/10' }}">
                <span class="text-sm font-bold {{ $modelo303['resultado'] > 0 ? 'text-rose-400' : 'text-emerald-400' }}">
                    {{ $modelo303['resultado'] > 0 ? 'A ingresar en Hacienda' : 'A compensar / devolver' }}
                </span>
                <span class="text-lg font-bold {{ $modelo303['resultado'] > 0 ? 'text-rose-400' : 'text-emerald-400' }}">
                    &euro; {{ number_format(abs($modelo303['resultado']), 2, ',', '.') }}
                </span>
            </div>
        </div>
    </div>

    <!-- Modelo 130 -->
    <div class="glass-card rounded-2xl p-5 space-y-4">
        <div class="flex items-center gap-3">
            <div class="size-10 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center">
                <span class="material-symbols-outlined text-amber-400">receipt_long</span>
            </div>
            <div>
                <h3 class="text-white font-bold">Modelo 130 — Pago fraccionado IRPF</h3>
                <p class="text-xs text-white/40">20% sobre el beneficio del trimestre</p>
            </div>
        </div>

        <div class="space-y-2">
            <div class="flex items-center justify-between py-2 border-b border-white/5">
                <span class="text-sm text-slate-300">Ingresos (base, sin IVA)</span>
                <span class="text-sm font-bold text-emerald-400">&euro; {{ number_format($modelo130['revenueBase'], 2, ',', '.') }}</span>
            </div>
            <div class="flex items-center justify-between py-2 border-b border-white/5">
                <span class="text-sm text-slate-300">Gastos operativos deducibles</span>
                <span class="text-sm font-bold text-rose-400">- &euro; {{ number_format($modelo130['deductibleExpenses'], 2, ',', '.') }}</span>
            </div>
            <div class="flex items-center justify-between py-2 border-b border-white/5">
                <span class="text-sm text-slate-300">Compras a proveedores deducibles</span>
                <span class="text-sm font-bold text-rose-400">- &euro; {{ number_format($modelo130['deductibleInvoices'], 2, ',', '.') }}</span>
            </div>
            <div class="flex items-center justify-between py-2 border-b border-white/5">
                <span class="text-sm text-slate-300">Base imponible (beneficio)</span>
                <span class="text-sm font-bold {{ $modelo130['baseImponible'] > 0 ? 'text-white' : 'text-emerald-400' }}">
                    &euro; {{ number_format($modelo130['baseImponible'], 2, ',', '.') }}
                </span>
            </div>
            <div class="flex items-center justify-between py-3 rounded-xl px-3 {{ $modelo130['cuota'] > 0 ? 'bg-rose-500/10' : 'bg-emerald-500/10' }}">
                <span class="text-sm font-bold {{ $modelo130['cuota'] > 0 ? 'text-rose-400' : 'text-emerald-400' }}">
                    Cuota (20%)
                </span>
                <span class="text-lg font-bold {{ $modelo130['cuota'] > 0 ? 'text-rose-400' : 'text-emerald-400' }}">
                    &euro; {{ number_format($modelo130['cuota'], 2, ',', '.') }}
                </span>
            </div>
        </div>
    </div>

    <!-- Renta Anual -->
    <div class="glass-card rounded-2xl p-5 space-y-4">
        <div class="flex items-center gap-3">
            <div class="size-10 rounded-xl bg-violet-500/10 border border-violet-500/20 flex items-center justify-center">
                <span class="material-symbols-outlined text-violet-400">calculate</span>
            </div>
            <div>
                <h3 class="text-white font-bold">Estimación Renta {{ $selectedYear }}</h3>
                <p class="text-xs text-white/40">IRPF anual por tramos</p>
            </div>
        </div>

        <div class="space-y-2">
            <div class="flex items-center justify-between py-2 border-b border-white/5">
                <span class="text-sm text-slate-300">Ingresos anuales (base)</span>
                <span class="text-sm font-bold text-emerald-400">&euro; {{ number_format($renta['totalRevenue'], 2, ',', '.') }}</span>
            </div>
            <div class="flex items-center justify-between py-2 border-b border-white/5">
                <span class="text-sm text-slate-300">Gastos deducibles anuales</span>
                <span class="text-sm font-bold text-rose-400">- &euro; {{ number_format($renta['totalDeductible'], 2, ',', '.') }}</span>
            </div>
            <div class="flex items-center justify-between py-2 border-b border-white/5">
                <span class="text-sm text-slate-300">Base imponible anual</span>
                <span class="text-sm font-bold text-white">&euro; {{ number_format($renta['baseImponible'], 2, ',', '.') }}</span>
            </div>
            <div class="flex items-center justify-between py-2 border-b border-white/5">
                <span class="text-sm text-slate-300">IRPF estimado (por tramos)</span>
                <span class="text-sm font-bold text-amber-400">&euro; {{ number_format($renta['irpfEstimado'], 2, ',', '.') }}</span>
            </div>
            <div class="flex items-center justify-between py-2 border-b border-white/5">
                <span class="text-sm text-slate-300">Pagos a cuenta (Modelo 130)</span>
                <span class="text-sm font-bold text-emerald-400">- &euro; {{ number_format($renta['modelo130Paid'], 2, ',', '.') }}</span>
            </div>
            <div class="flex items-center justify-between py-3 rounded-xl px-3 {{ $renta['restanteRenta'] > 0 ? 'bg-amber-500/10' : 'bg-emerald-500/10' }}">
                <span class="text-sm font-bold {{ $renta['restanteRenta'] > 0 ? 'text-amber-400' : 'text-emerald-400' }}">
                    {{ $renta['restanteRenta'] > 0 ? 'Estimación a pagar en Renta' : 'Estimación a devolver' }}
                </span>
                <span class="text-lg font-bold {{ $renta['restanteRenta'] > 0 ? 'text-amber-400' : 'text-emerald-400' }}">
                    &euro; {{ number_format(abs($renta['restanteRenta']), 2, ',', '.') }}
                </span>
            </div>
        </div>

        <!-- Disclaimer -->
        <div class="bg-amber-500/5 border border-amber-500/10 rounded-xl p-3">
            <p class="text-[11px] text-amber-400/70 flex items-start gap-2">
                <span class="material-symbols-outlined text-sm mt-0.5 shrink-0">warning</span>
                <span>Estimación simplificada basada en tramos generales del IRPF. No incluye deducciones personales, familiares ni autonómicas. Consultar con gestor o asesor fiscal para la declaración definitiva.</span>
            </p>
        </div>
    </div>

    <!-- Tramos IRPF (info) -->
    <div class="glass-card rounded-2xl p-4" x-data="{ open: false }">
        <button @click="open = !open" class="flex items-center justify-between w-full">
            <span class="text-sm text-white/60 font-medium flex items-center gap-2">
                <span class="material-symbols-outlined text-base">info</span>
                Tramos IRPF aplicados
            </span>
            <span class="material-symbols-outlined text-white/30 transition-transform text-sm" :class="open ? 'rotate-180' : ''">expand_more</span>
        </button>
        <div x-show="open" x-collapse class="mt-3 space-y-1">
            <div class="flex justify-between text-xs text-white/40 py-1 border-b border-white/5">
                <span>Hasta 12.450 &euro;</span><span>19%</span>
            </div>
            <div class="flex justify-between text-xs text-white/40 py-1 border-b border-white/5">
                <span>12.450 — 20.200 &euro;</span><span>24%</span>
            </div>
            <div class="flex justify-between text-xs text-white/40 py-1 border-b border-white/5">
                <span>20.200 — 35.200 &euro;</span><span>30%</span>
            </div>
            <div class="flex justify-between text-xs text-white/40 py-1 border-b border-white/5">
                <span>35.200 — 60.000 &euro;</span><span>37%</span>
            </div>
            <div class="flex justify-between text-xs text-white/40 py-1 border-b border-white/5">
                <span>60.000 — 300.000 &euro;</span><span>45%</span>
            </div>
            <div class="flex justify-between text-xs text-white/40 py-1">
                <span>Más de 300.000 &euro;</span><span>47%</span>
            </div>
        </div>
    </div>
</div>
