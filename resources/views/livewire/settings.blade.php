<div class="space-y-6">
    <x-slot:header>Configuracion</x-slot:header>

    <!-- Business Info -->
    <div class="glass-card rounded-2xl p-5 space-y-4">
        <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-primary">store</span>
            <h3 class="font-bold text-slate-100">Datos del negocio</h3>
        </div>

        <div class="space-y-3">
            <input wire:model="business_name" type="text"
                class="w-full h-12 px-4 bg-white/5 border border-white/5 rounded-xl focus:ring-1 focus:ring-primary/50 text-slate-100 placeholder:text-white/20"
                placeholder="Nombre del negocio">
            <input wire:model="business_cif" type="text"
                class="w-full h-12 px-4 bg-white/5 border border-white/5 rounded-xl focus:ring-1 focus:ring-primary/50 text-slate-100 placeholder:text-white/20"
                placeholder="CIF / NIF">
            <input wire:model="business_address" type="text"
                class="w-full h-12 px-4 bg-white/5 border border-white/5 rounded-xl focus:ring-1 focus:ring-primary/50 text-slate-100 placeholder:text-white/20"
                placeholder="Direccion">
            <div class="grid grid-cols-2 gap-3">
                <input wire:model="business_phone" type="text"
                    class="w-full h-12 px-4 bg-white/5 border border-white/5 rounded-xl focus:ring-1 focus:ring-primary/50 text-slate-100 placeholder:text-white/20"
                    placeholder="Telefono">
                <input wire:model="business_email" type="email"
                    class="w-full h-12 px-4 bg-white/5 border border-white/5 rounded-xl focus:ring-1 focus:ring-primary/50 text-slate-100 placeholder:text-white/20"
                    placeholder="Email">
            </div>
        </div>

        <button wire:click="saveBusiness"
            class="w-full h-12 rounded-xl bg-primary text-white font-semibold shadow-lg shadow-primary/30 transition-all active:scale-[0.98]">
            Guardar datos
        </button>
        @if(session('business_saved'))
        <p class="text-emerald-400 text-sm text-center">Datos guardados correctamente</p>
        @endif
    </div>

    <!-- Pricing -->
    <div class="glass-card rounded-2xl p-5 space-y-4">
        <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-primary">percent</span>
            <h3 class="font-bold text-slate-100">Precios e impuestos</h3>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div class="space-y-2">
                <label class="text-xs font-bold uppercase tracking-widest text-white/40">IVA (%)</label>
                <input wire:model="tax_rate" type="number" step="0.1"
                    class="w-full h-12 px-4 bg-white/5 border border-white/5 rounded-xl focus:ring-1 focus:ring-primary/50 text-slate-100 text-center text-xl font-bold">
            </div>
            <div class="space-y-2">
                <label class="text-xs font-bold uppercase tracking-widest text-white/40">Margen auto (%)</label>
                <input wire:model="auto_margin_percentage" type="number" step="1"
                    class="w-full h-12 px-4 bg-white/5 border border-white/5 rounded-xl focus:ring-1 focus:ring-primary/50 text-slate-100 text-center text-xl font-bold">
            </div>
        </div>

        <button wire:click="savePricing"
            class="w-full h-12 rounded-xl bg-primary text-white font-semibold shadow-lg shadow-primary/30 transition-all active:scale-[0.98]">
            Guardar precios
        </button>
        @if(session('pricing_saved'))
        <p class="text-emerald-400 text-sm text-center">Configuracion guardada</p>
        @endif
    </div>

    <!-- Margen y coste real -->
    <div class="glass-card rounded-2xl p-5 space-y-4">
        <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-primary">analytics</span>
            <h3 class="font-bold text-slate-100">Margen y coste real</h3>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div class="space-y-2">
                <label class="text-xs font-bold uppercase tracking-widest text-white/40">Margen objetivo (%)</label>
                <input wire:model="target_margin_percentage" type="number" step="1"
                    class="w-full h-12 px-4 bg-white/5 border border-white/5 rounded-xl focus:ring-1 focus:ring-primary/50 text-slate-100 text-center text-xl font-bold">
            </div>
            <div class="space-y-2">
                <label class="text-xs font-bold uppercase tracking-widest text-white/40">Período cálculo</label>
                <select wire:model="expense_calculation_period"
                    class="w-full h-12 px-4 bg-white/5 border border-white/5 rounded-xl focus:ring-1 focus:ring-primary/50 text-slate-100">
                    <option value="month">Mes actual</option>
                    <option value="3months">3 meses</option>
                    <option value="6months">6 meses</option>
                </select>
            </div>
        </div>

        <!-- Info coste real -->
        @php $realCost = $this->realCostInfo; @endphp
        <div class="bg-white/5 rounded-xl p-3 space-y-1">
            <div class="flex justify-between text-sm">
                <span class="text-slate-400">Gastos operativos (período)</span>
                <span class="text-white font-semibold">&euro; {{ number_format($realCost['totalExpenses'], 2, ',', '.') }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-slate-400">Unidades en stock</span>
                <span class="text-white font-semibold">{{ number_format($realCost['totalUnits']) }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-slate-400">Coste operativo / unidad</span>
                <span class="text-amber-400 font-semibold">&euro; {{ number_format($realCost['costPerUnit'], 4, ',', '.') }}</span>
            </div>
        </div>

        <button wire:click="saveMarginSettings"
            class="w-full h-12 rounded-xl bg-primary text-white font-semibold shadow-lg shadow-primary/30 transition-all active:scale-[0.98]">
            Guardar configuración
        </button>
        @if(session('margin_saved'))
        <p class="text-emerald-400 text-sm text-center">Configuración de margen guardada</p>
        @endif
    </div>

    <!-- Ajuste de precios -->
    <div class="glass-card rounded-2xl p-5 space-y-4">
        <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-primary">tune</span>
            <h3 class="font-bold text-slate-100">Ajuste de precios</h3>
        </div>

        <!-- Estado actual -->
        <div class="flex items-center gap-2">
            @if($price_adjustment_active)
            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-sm font-medium bg-amber-400/10 text-amber-400 border border-amber-400/20">
                <span class="material-symbols-outlined text-sm">trending_up</span>
                Ajuste activo: {{ $price_adjustment_percentage > 0 ? '+' : '' }}{{ $price_adjustment_percentage }}%
            </span>
            @else
            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-sm font-medium bg-white/5 text-slate-500 border border-white/10">
                Sin ajuste
            </span>
            @endif
        </div>

        <!-- Control porcentaje -->
        <div class="space-y-2">
            <label class="text-xs font-bold uppercase tracking-widest text-white/40">Porcentaje de ajuste</label>
            <input wire:model.live="price_adjustment_percentage" type="number" step="0.5" min="-50" max="200"
                class="w-full h-12 px-4 bg-white/5 border border-white/5 rounded-xl focus:ring-1 focus:ring-primary/50 text-slate-100 text-center text-xl font-bold">
            <p class="text-white/30 text-xs text-center">Todos los precios se multiplicarán por {{ number_format(1 + (float)$price_adjustment_percentage / 100, 3, ',', '.') }}</p>
        </div>

        <!-- Preview productos -->
        @php $preview = $this->previewProducts; @endphp
        @if(count($preview) > 0)
        <div class="space-y-2">
            <p class="text-xs font-bold uppercase tracking-widest text-white/40">Vista previa</p>
            @foreach($preview as $p)
            <div class="flex items-center justify-between bg-white/5 rounded-xl px-3 py-2">
                <span class="text-slate-300 text-sm truncate flex-1">{{ $p['name'] }}</span>
                <div class="flex items-center gap-2 text-sm shrink-0">
                    <span class="text-white/40">&euro; {{ number_format($p['base'], 2, ',', '.') }}</span>
                    <span class="material-symbols-outlined text-white/20 text-xs">arrow_forward</span>
                    <span class="font-bold {{ $p['adjusted'] > $p['base'] ? 'text-emerald-400' : ($p['adjusted'] < $p['base'] ? 'text-rose-400' : 'text-white') }}">
                        &euro; {{ number_format($p['adjusted'], 2, ',', '.') }}
                    </span>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        <!-- Botones -->
        <div class="space-y-2">
            <button wire:click="applyPriceAdjustment"
                wire:confirm="¿Aplicar ajuste de {{ $price_adjustment_percentage }}% a TODOS los productos? Los precios de venta cambiarán en la base de datos."
                class="w-full h-12 rounded-xl bg-primary text-white font-semibold shadow-lg shadow-primary/30 transition-all active:scale-[0.98]">
                Aplicar ajuste
            </button>

            @if($price_adjustment_active)
            <button wire:click="revertPrices"
                wire:confirm="¿Restaurar todos los precios a su valor original? Se deshará el ajuste de {{ $price_adjustment_percentage }}%."
                class="w-full h-12 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 font-semibold transition-all active:scale-[0.98]">
                Restaurar precios originales
            </button>
            @endif
        </div>

        @if(session('prices_reverted'))
        <p class="text-emerald-400 text-sm text-center">Precios restaurados a su valor original</p>
        @endif
    </div>

    <!-- Employees -->
    <div class="glass-card rounded-2xl p-5 space-y-4">
        <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-primary">group</span>
            <h3 class="font-bold text-slate-100">Empleados</h3>
        </div>

        <!-- Existing employees -->
        <div class="space-y-2">
            @foreach($employees as $emp)
            <div class="flex items-center justify-between p-3 bg-white/5 rounded-xl">
                <a href="{{ route('employee.tasks', $emp) }}" class="flex items-center gap-3 flex-1 min-w-0">
                    <div class="size-9 rounded-full bg-primary/20 flex items-center justify-center">
                        <span class="material-symbols-outlined text-primary text-sm">person</span>
                    </div>
                    <div class="min-w-0">
                        <p class="text-slate-100 font-medium text-sm">{{ $emp->name }}</p>
                        <p class="text-white/40 text-xs">{{ $emp->email }}</p>
                    </div>
                    <span class="material-symbols-outlined text-white/20 text-sm ml-auto">chevron_right</span>
                </a>
                <button wire:click="deleteEmployee({{ $emp->id }})" wire:confirm="¿Eliminar a {{ $emp->name }}?"
                    class="size-9 rounded-lg bg-red-500/10 flex items-center justify-center hover:bg-red-500/20 transition-all shrink-0 ml-2">
                    <span class="material-symbols-outlined text-red-400 text-sm">delete</span>
                </button>
            </div>
            @endforeach
        </div>

        <!-- New employee form -->
        <div class="space-y-3 pt-2 border-t border-white/5">
            <p class="text-xs font-bold uppercase tracking-widest text-white/40">Nuevo empleado</p>
            <input wire:model="newEmployeeName" type="text"
                class="w-full h-12 px-4 bg-white/5 border border-white/5 rounded-xl focus:ring-1 focus:ring-primary/50 text-slate-100 placeholder:text-white/20"
                placeholder="Nombre">
            @error('newEmployeeName') <p class="text-red-400 text-xs">{{ $message }}</p> @enderror

            <input wire:model="newEmployeeEmail" type="email"
                class="w-full h-12 px-4 bg-white/5 border border-white/5 rounded-xl focus:ring-1 focus:ring-primary/50 text-slate-100 placeholder:text-white/20"
                placeholder="Email">
            @error('newEmployeeEmail') <p class="text-red-400 text-xs">{{ $message }}</p> @enderror

            <input wire:model="newEmployeePassword" type="password"
                class="w-full h-12 px-4 bg-white/5 border border-white/5 rounded-xl focus:ring-1 focus:ring-primary/50 text-slate-100 placeholder:text-white/20"
                placeholder="Contraseña">
            @error('newEmployeePassword') <p class="text-red-400 text-xs">{{ $message }}</p> @enderror

            <button wire:click="createEmployee"
                class="w-full h-12 rounded-xl bg-white/5 border border-white/10 text-slate-200 font-semibold transition-all hover:bg-primary hover:border-primary active:scale-[0.98]">
                Crear empleado
            </button>
            @if(session('employee_created'))
            <p class="text-emerald-400 text-sm text-center">Empleado creado correctamente</p>
            @endif
        </div>
    </div>

    <!-- Logout -->
    <button wire:click="logout"
        class="w-full h-12 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 font-semibold flex items-center justify-center gap-2 transition-all hover:bg-red-500/20 active:scale-[0.98]">
        <span class="material-symbols-outlined text-xl">logout</span>
        Cerrar sesion
    </button>
</div>
