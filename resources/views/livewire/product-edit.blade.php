<div class="space-y-6">
    <x-slot:header>{{ $isNew ? 'Nuevo Producto' : 'Editar Producto' }}</x-slot:header>

    <form wire:submit="save" class="space-y-5">
        <!-- Photo -->
        <div class="flex justify-center">
            <label class="relative cursor-pointer group">
                <div class="size-28 rounded-2xl bg-white/5 border-2 border-dashed border-white/10 flex items-center justify-center overflow-hidden group-hover:border-primary/50 transition-all">
                    @if($photo)
                        <img src="{{ $photo->temporaryUrl() }}" class="size-full object-cover">
                    @elseif($existingPhoto)
                        <img src="{{ asset('storage/' . $existingPhoto) }}" class="size-full object-cover">
                    @else
                        <div class="text-center">
                            <span class="material-symbols-outlined text-white/20 text-4xl">add_a_photo</span>
                            <p class="text-[10px] text-white/30 mt-1">Foto</p>
                        </div>
                    @endif
                </div>
                <input type="file" wire:model="photo" class="hidden" accept="image/*">
            </label>
        </div>
        @error('photo') <p class="text-red-400 text-xs text-center">{{ $message }}</p> @enderror

        <!-- Name -->
        <div class="space-y-2">
            <label class="text-xs font-bold uppercase tracking-widest text-white/40">Nombre *</label>
            <input wire:model="name" type="text"
                class="w-full h-12 px-4 bg-white/5 border border-white/5 rounded-xl focus:ring-1 focus:ring-primary/50 text-slate-100 placeholder:text-white/20 transition-all"
                placeholder="Ej: Guppy Cobra Rojo">
            @error('name') <p class="text-red-400 text-xs">{{ $message }}</p> @enderror
        </div>

        <!-- Code + Category -->
        <div class="grid grid-cols-2 gap-4">
            <div class="space-y-2">
                <label class="text-xs font-bold uppercase tracking-widest text-white/40">Codigo</label>
                <input wire:model="code" type="text"
                    class="w-full h-12 px-4 bg-white/5 border border-white/5 rounded-xl focus:ring-1 focus:ring-primary/50 text-slate-100 placeholder:text-white/20 transition-all"
                    placeholder="SKU">
            </div>
            <div class="space-y-2">
                <label class="text-xs font-bold uppercase tracking-widest text-white/40">Categoria</label>
                <select wire:model="category_id"
                    class="w-full h-12 px-4 bg-white/5 border border-white/5 rounded-xl focus:ring-1 focus:ring-primary/50 text-slate-100 transition-all">
                    <option value="">Sin categoria</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Prices -->
        <div class="glass-card rounded-2xl p-4 space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-widest text-white/40">Precios</h3>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label class="text-xs text-white/50">Coste</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-white/30 text-sm font-bold">&euro;</span>
                        <input wire:model.live.debounce.300ms="cost_price" type="number" step="0.01"
                            class="w-full h-12 pl-8 pr-4 bg-white/5 border border-white/5 rounded-xl focus:ring-1 focus:ring-primary/50 text-slate-100 transition-all"
                            placeholder="0.00">
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="text-xs text-white/50">Venta</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-white/30 text-sm font-bold">&euro;</span>
                        <input wire:model.live.debounce.300ms="sale_price" type="number" step="0.01"
                            class="w-full h-12 pl-8 pr-4 bg-white/5 border border-white/5 rounded-xl focus:ring-1 focus:ring-primary/50 text-slate-100 transition-all {{ $auto_margin ? 'opacity-50' : '' }}"
                            placeholder="0.00" {{ $auto_margin ? 'readonly' : '' }}>
                    </div>
                </div>
            </div>

            <!-- Coste real + Margen -->
            @if($cost_price > 0)
            <div class="bg-white/5 rounded-xl p-3 space-y-2">
                @if($costPerUnit > 0)
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-white/40">Coste real</p>
                    <div class="flex items-center justify-between mt-1">
                        <span class="text-lg font-bold text-amber-400">&euro; {{ number_format($realCost, 2, ',', '.') }}</span>
                        <span class="text-xs text-white/30">compra {{ number_format((float)$cost_price, 2, ',', '.') }} + gastos {{ number_format($costPerUnit, 2, ',', '.') }}</span>
                    </div>
                    <p class="text-[10px] text-white/30">Incluye luz, agua, alquiler y otros gastos repartidos entre las {{ \App\Models\Product::where('stock', '>', 0)->sum('stock') }} uds en stock</p>
                </div>
                @endif

                @if($sale_price > 0)
                @php
                    $marginBase = $realCost > 0 ? $realCost : (float)$cost_price;
                    $profitPerUnit = (float)$sale_price - $marginBase;
                    $marginPctCalc = $marginBase > 0 ? round($profitPerUnit / (float)$sale_price * 100, 1) : 0;
                @endphp
                <div class="pt-2 border-t border-white/5">
                    <div class="flex items-center justify-between">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-white/40">Margen por unidad</p>
                        <span class="text-xs font-bold {{ $profitPerUnit > 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                            {{ $marginPctCalc }}%
                        </span>
                    </div>
                    <div class="flex items-center justify-between mt-1">
                        <span class="text-lg font-bold {{ $profitPerUnit > 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                            {{ $profitPerUnit >= 0 ? '+' : '' }}&euro; {{ number_format($profitPerUnit, 2, ',', '.') }}
                        </span>
                        <span class="text-xs text-white/30">venta {{ number_format((float)$sale_price, 2, ',', '.') }} - coste {{ number_format($marginBase, 2, ',', '.') }}</span>
                    </div>
                    @if($profitPerUnit <= 0)
                    <p class="text-[10px] text-rose-400 mt-1 flex items-center gap-1">
                        <span class="material-symbols-outlined text-xs">warning</span>
                        Estás perdiendo dinero con este producto
                    </p>
                    @endif
                </div>
                @endif
            </div>
            @endif

            <!-- Auto margin toggle -->
            <label class="flex items-center gap-3 cursor-pointer">
                <div class="relative">
                    <input type="checkbox" wire:model.live="auto_margin" class="sr-only" id="auto_margin_toggle">
                    <div class="w-11 h-6 rounded-full transition-colors {{ $auto_margin ? 'bg-primary' : 'bg-white/10' }}"></div>
                    <div class="absolute left-0.5 top-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform {{ $auto_margin ? 'translate-x-5' : '' }}"></div>
                </div>
                <span class="text-sm text-slate-300">Margen automatico ({{ \App\Models\Setting::get('auto_margin_percentage', 30) }}%)</span>
            </label>
        </div>

        <!-- Stock -->
        <div class="glass-card rounded-2xl p-4 space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-widest text-white/40">Stock</h3>
            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label class="text-xs text-white/50">Cantidad actual</label>
                    <input wire:model="stock" type="number"
                        class="w-full h-12 px-4 bg-white/5 border border-white/5 rounded-xl focus:ring-1 focus:ring-primary/50 text-slate-100 transition-all text-center text-xl font-bold"
                        min="0">
                </div>
                <div class="space-y-2">
                    <label class="text-xs text-white/50">Stock minimo</label>
                    <input wire:model="min_stock" type="number"
                        class="w-full h-12 px-4 bg-white/5 border border-white/5 rounded-xl focus:ring-1 focus:ring-primary/50 text-slate-100 transition-all text-center text-xl font-bold"
                        min="0">
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex gap-3 pt-2">
            <a href="{{ route('stock') }}"
                class="flex-1 h-12 flex items-center justify-center rounded-xl bg-white/5 border border-white/5 text-slate-300 font-semibold transition-all hover:bg-white/10">
                Cancelar
            </a>
            <button type="submit"
                class="flex-1 h-12 flex items-center justify-center rounded-xl bg-primary text-white font-semibold shadow-lg shadow-primary/30 transition-all active:scale-[0.98]">
                <span wire:loading.remove wire:target="save">{{ $isNew ? 'Crear' : 'Guardar' }}</span>
                <span wire:loading wire:target="save" class="material-symbols-outlined animate-spin text-xl">progress_activity</span>
            </button>
        </div>

        <!-- Delete -->
        @if(!$isNew)
        <button type="button" wire:click="delete" wire:confirm="¿Eliminar este producto?"
            class="w-full h-10 flex items-center justify-center gap-2 rounded-xl text-red-400 text-sm font-medium hover:bg-red-500/10 transition-all">
            <span class="material-symbols-outlined text-sm">delete</span>
            Eliminar producto
        </button>
        @endif
    </form>
</div>
