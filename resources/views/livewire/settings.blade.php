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
                <div class="flex items-center gap-3">
                    <div class="size-9 rounded-full bg-primary/20 flex items-center justify-center">
                        <span class="material-symbols-outlined text-primary text-sm">person</span>
                    </div>
                    <div>
                        <p class="text-slate-100 font-medium text-sm">{{ $emp->name }}</p>
                        <p class="text-white/40 text-xs">{{ $emp->email }}</p>
                    </div>
                </div>
                <button wire:click="deleteEmployee({{ $emp->id }})" wire:confirm="¿Eliminar a {{ $emp->name }}?"
                    class="size-9 rounded-lg bg-red-500/10 flex items-center justify-center hover:bg-red-500/20 transition-all">
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
