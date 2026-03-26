<div class="space-y-6">
    <x-slot:header>Importar Factura</x-slot:header>

    @if(session('message'))
    <div class="glass-card rounded-2xl p-4 border-emerald-500/20 flex items-center gap-3">
        <span class="material-symbols-outlined text-emerald-400">check_circle</span>
        <p class="text-emerald-400 font-medium text-sm">{{ session('message') }}</p>
    </div>
    @endif

    @if(session('error'))
    <div class="glass-card rounded-2xl p-4 border-red-500/20 flex items-center gap-3">
        <span class="material-symbols-outlined text-red-400">error</span>
        <p class="text-red-400 font-medium text-sm">{{ session('error') }}</p>
    </div>
    @endif

    <!-- Progress Steps -->
    <div class="flex items-center justify-center gap-2">
        @foreach([1 => 'Subir', 2 => 'Datos', 3 => 'Productos'] as $num => $label)
        <div class="flex items-center gap-2">
            <div class="size-8 rounded-full flex items-center justify-center text-xs font-bold transition-all
                {{ $step >= $num ? 'bg-primary text-white' : 'bg-white/5 text-white/30' }}">
                {{ $num }}
            </div>
            <span class="text-xs {{ $step >= $num ? 'text-slate-200' : 'text-white/30' }}">{{ $label }}</span>
            @if($num < 3)
            <div class="w-8 h-px {{ $step > $num ? 'bg-primary' : 'bg-white/10' }}"></div>
            @endif
        </div>
        @endforeach
    </div>

    <!-- Step 1: Upload -->
    @if($step === 1)
    <div class="glass-card rounded-2xl p-6 space-y-6">
        <div class="text-center space-y-2">
            <span class="material-symbols-outlined text-primary text-5xl">document_scanner</span>
            <h3 class="text-lg font-bold text-slate-100">Sube la factura del proveedor</h3>
            <p class="text-white/40 text-sm">PDF o imagen (JPG, PNG). La IA extraera los datos automaticamente.</p>
        </div>

        <label class="block cursor-pointer">
            <div class="border-2 border-dashed border-white/10 rounded-2xl p-8 text-center hover:border-primary/40 transition-all">
                <span class="material-symbols-outlined text-white/20 text-4xl">cloud_upload</span>
                <p class="text-white/40 text-sm mt-2">Pulsa para seleccionar o hacer foto</p>
                <p class="text-white/20 text-xs mt-1">PDF, JPG, PNG (max 10MB)</p>
                <input type="file" wire:model="invoiceFile" class="hidden" accept=".pdf,.jpg,.jpeg,.png,.webp,image/*" capture="environment">
            </div>
        </label>

        <div wire:loading wire:target="invoiceFile" class="flex items-center justify-center gap-2 text-primary">
            <span class="material-symbols-outlined animate-spin">progress_activity</span>
            <span class="text-sm font-medium">Subiendo archivo...</span>
        </div>

        @if($processing)
        <div class="flex flex-col items-center justify-center gap-3 text-primary py-4">
            <span class="material-symbols-outlined animate-spin text-3xl">progress_activity</span>
            <span class="text-sm font-medium">Analizando factura con IA...</span>
            <span class="text-xs text-white/30">Esto puede tardar unos segundos</span>
        </div>
        @endif
    </div>
    @endif

    <!-- Step 2: Invoice Details -->
    @if($step === 2)
    <div class="glass-card rounded-2xl p-5 space-y-4">
        <h3 class="font-bold text-slate-100">Datos de la factura</h3>

        <!-- Type -->
        <div class="grid grid-cols-2 gap-3">
            <button wire:click="$set('invoiceType', 'purchase')" type="button"
                class="h-12 rounded-xl font-medium transition-all flex items-center justify-center gap-2
                {{ $invoiceType === 'purchase' ? 'bg-primary text-white' : 'bg-white/5 border border-white/10 text-slate-300' }}">
                <span class="material-symbols-outlined text-sm">local_shipping</span>
                Compra
            </button>
            <button wire:click="$set('invoiceType', 'service')" type="button"
                class="h-12 rounded-xl font-medium transition-all flex items-center justify-center gap-2
                {{ $invoiceType === 'service' ? 'bg-primary text-white' : 'bg-white/5 border border-white/10 text-slate-300' }}">
                <span class="material-symbols-outlined text-sm">build</span>
                Servicio
            </button>
        </div>

        <!-- Supplier -->
        <div class="space-y-2">
            <label class="text-xs font-bold uppercase tracking-widest text-white/40">Proveedor</label>
            <select wire:model="supplier_id"
                class="w-full h-12 px-4 bg-white/5 border border-white/5 rounded-xl focus:ring-1 focus:ring-primary/50 text-slate-100">
                <option value="">Nuevo proveedor</option>
                @foreach($suppliers as $sup)
                <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                @endforeach
            </select>
            @if(!$supplier_id)
            <input wire:model="newSupplierName" type="text"
                class="w-full h-12 px-4 bg-white/5 border border-white/5 rounded-xl focus:ring-1 focus:ring-primary/50 text-slate-100 placeholder:text-white/20"
                placeholder="Nombre del proveedor">
            @endif
        </div>

        <!-- Invoice number + date -->
        <div class="grid grid-cols-2 gap-3">
            <div class="space-y-2">
                <label class="text-xs font-bold uppercase tracking-widest text-white/40">N° Factura</label>
                <input wire:model="invoiceNumber" type="text"
                    class="w-full h-12 px-4 bg-white/5 border border-white/5 rounded-xl focus:ring-1 focus:ring-primary/50 text-slate-100 placeholder:text-white/20"
                    placeholder="Opcional">
            </div>
            <div class="space-y-2">
                <label class="text-xs font-bold uppercase tracking-widest text-white/40">Fecha</label>
                <input wire:model="invoiceDate" type="date"
                    class="w-full h-12 px-4 bg-white/5 border border-white/5 rounded-xl focus:ring-1 focus:ring-primary/50 text-slate-100">
            </div>
        </div>

        <!-- Concept -->
        <div class="space-y-2">
            <label class="text-xs font-bold uppercase tracking-widest text-white/40">Concepto</label>
            <input wire:model="concept" type="text"
                class="w-full h-12 px-4 bg-white/5 border border-white/5 rounded-xl focus:ring-1 focus:ring-primary/50 text-slate-100 placeholder:text-white/20"
                placeholder="Descripcion de la factura">
        </div>

        <!-- Extra costs -->
        <div class="space-y-2">
            <label class="text-xs font-bold uppercase tracking-widest text-white/40">Costes extra (transporte, etc.)</label>
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-white/30 text-sm font-bold">&euro;</span>
                <input wire:model="extraCosts" type="number" step="0.01"
                    class="w-full h-12 pl-8 pr-4 bg-white/5 border border-white/5 rounded-xl focus:ring-1 focus:ring-primary/50 text-slate-100"
                    placeholder="0.00">
            </div>
        </div>

        <!-- Resumen factura (extraído por IA) -->
        @if(!empty($invoiceSummary))
        <div class="bg-white/5 rounded-xl p-3 space-y-2">
            <p class="text-xs font-bold uppercase tracking-widest text-white/40">Resumen de la factura (IA)</p>
            <div class="grid grid-cols-2 gap-x-4 gap-y-1 text-xs">
                @if(($invoiceSummary['subtotal_products'] ?? 0) > 0)
                <span class="text-white/50">Subtotal productos</span>
                <span class="text-white font-semibold text-right">&euro; {{ number_format($invoiceSummary['subtotal_products'], 2, ',', '.') }}</span>
                @endif
                @if(($invoiceSummary['discount_amount'] ?? 0) > 0)
                <span class="text-white/50">Descuento ({{ $invoiceSummary['discount_percentage'] ?? 0 }}%)</span>
                <span class="text-rose-400 font-semibold text-right">-&euro; {{ number_format($invoiceSummary['discount_amount'], 2, ',', '.') }}</span>
                @endif
                @if(($invoiceSummary['transport_cost'] ?? 0) > 0)
                <span class="text-white/50">Transporte</span>
                <span class="text-amber-400 font-semibold text-right">&euro; {{ number_format($invoiceSummary['transport_cost'], 2, ',', '.') }}</span>
                @endif
                @if(!empty($invoiceSummary['transport_detail']))
                <span class="text-white/30 col-span-2 text-[10px]">{{ $invoiceSummary['transport_detail'] }}</span>
                @endif
                @if(($invoiceSummary['vat_rate'] ?? 0) > 0)
                <span class="text-white/50">IVA ({{ $invoiceSummary['vat_rate'] }}%)</span>
                <span class="text-white font-semibold text-right">&euro; {{ number_format($invoiceSummary['vat_amount'] ?? 0, 2, ',', '.') }}</span>
                @endif
                @if(($invoiceSummary['total'] ?? 0) > 0)
                <span class="text-white/50 font-bold">Total factura</span>
                <span class="text-primary font-bold text-right">&euro; {{ number_format($invoiceSummary['total'], 2, ',', '.') }}</span>
                @endif
            </div>
        </div>
        @endif

        <!-- Items preview -->
        <div class="pt-2 border-t border-white/5">
            <p class="text-white/40 text-xs">{{ count($items) }} productos detectados en la factura</p>
        </div>

        <div class="flex gap-3">
            <button wire:click="$set('step', 1)"
                class="flex-1 h-12 rounded-xl bg-white/5 border border-white/5 text-slate-300 font-semibold transition-all">
                Atras
            </button>
            <button wire:click="goToStep3"
                class="flex-1 h-12 rounded-xl bg-primary text-white font-semibold shadow-lg shadow-primary/30 transition-all active:scale-[0.98]">
                {{ $invoiceType === 'service' ? 'Importar' : 'Revisar productos' }}
            </button>
        </div>
    </div>
    @endif

    <!-- Step 3: Review Products -->
    @if($step === 3)
    <div class="space-y-4">
        <div class="glass-card rounded-2xl p-4">
            <div class="flex items-center justify-between">
                <h3 class="font-bold text-slate-100">Revisar productos</h3>
                <span class="text-xs text-white/40">{{ count($items) }} productos</span>
            </div>
        </div>

        <div class="space-y-3 max-h-[55vh] overflow-y-auto">
            @foreach($items as $index => $item)
            <div class="glass-card rounded-xl p-4 space-y-2">
                <div class="flex items-start justify-between gap-2">
                    <div class="flex-1 min-w-0">
                        <p class="text-slate-100 font-semibold text-sm truncate">{{ $item['name'] }}</p>
                        <p class="text-white/40 text-xs">
                            {{ $item['code'] ? $item['code'] . ' · ' : '' }}{{ $item['quantity'] }} uds x &euro; {{ number_format($item['unit_cost'], 2, ',', '.') }}
                        </p>
                    </div>
                    <p class="font-bold text-slate-100 shrink-0">&euro; {{ number_format($item['total'], 2, ',', '.') }}</p>
                    <button wire:click="removeItem({{ $index }})"
                        class="size-8 rounded-lg bg-rose-500/10 border border-rose-500/20 flex items-center justify-center text-rose-400 hover:text-rose-300 shrink-0 transition-all"
                        title="Eliminar producto">
                        <span class="material-symbols-outlined text-sm">close</span>
                    </button>
                </div>

                <!-- Match status -->
                <div class="flex items-center justify-between">
                    @if($item['matched_product_id'] && !$item['is_new'])
                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-emerald-500/10 text-emerald-400 text-xs font-medium">
                        <span class="material-symbols-outlined text-xs">link</span>
                        Producto existente — actualizar stock
                    </span>
                    @else
                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-blue-500/10 text-blue-400 text-xs font-medium">
                        <span class="material-symbols-outlined text-xs">add_circle</span>
                        Nuevo — {{ ucfirst(str_replace('-', ' ', $item['category'] ?? 'sin categoría')) }}
                    </span>
                    @endif
                    @if($item['matched_product_id'])
                    <button wire:click="toggleNewProduct({{ $index }})" class="text-xs text-white/40 hover:text-white/60">
                        {{ $item['is_new'] ? 'Vincular existente' : 'Crear nuevo' }}
                    </button>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        <!-- Total -->
        <div class="glass-card rounded-2xl p-4">
            <div class="flex justify-between items-center">
                <span class="text-white/50">Total factura</span>
                <span class="text-2xl font-bold text-primary">
                    &euro; {{ number_format(array_sum(array_column($items, 'total')) + (float) $extraCosts, 2, ',', '.') }}
                </span>
            </div>
            @if((float) $extraCosts > 0)
            <p class="text-white/40 text-xs mt-1">Incluye &euro; {{ number_format((float) $extraCosts, 2, ',', '.') }} de costes extra</p>
            @endif
        </div>

        <div class="flex gap-3">
            <button wire:click="$set('step', 2)"
                class="flex-1 h-12 rounded-xl bg-white/5 border border-white/5 text-slate-300 font-semibold transition-all">
                Atras
            </button>
            <button wire:click="importInvoice"
                class="flex-1 h-12 rounded-xl bg-primary text-white font-semibold shadow-lg shadow-primary/30 transition-all active:scale-[0.98]">
                <span wire:loading.remove wire:target="importInvoice">Importar factura</span>
                <span wire:loading wire:target="importInvoice" class="material-symbols-outlined animate-spin">progress_activity</span>
            </button>
        </div>
    </div>
    @endif
</div>
