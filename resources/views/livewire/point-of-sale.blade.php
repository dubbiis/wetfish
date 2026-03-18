<div class="space-y-4">
    <x-slot:header>Punto de Venta</x-slot:header>

    @if($showSuccess)
    <!-- Success Screen -->
    <div class="flex flex-col items-center justify-center py-12 space-y-6">
        <div class="size-24 rounded-full bg-emerald-500/20 flex items-center justify-center border border-emerald-500/30">
            <span class="material-symbols-outlined text-emerald-400 text-5xl">check_circle</span>
        </div>
        <div class="text-center">
            <h2 class="text-2xl font-bold text-white">Venta completada</h2>
            <p class="text-white/40 mt-1">Ticket #{{ $lastTicketId }}</p>
        </div>
        <div class="flex gap-3 w-full max-w-xs">
            <button wire:click="newSale"
                class="flex-1 h-12 rounded-xl bg-primary text-white font-semibold shadow-lg shadow-primary/30 transition-all active:scale-[0.98]">
                Nueva venta
            </button>
        </div>
    </div>
    @else
    <!-- Search Products -->
    <div class="relative">
        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-white/30">search</span>
        <input wire:model.live.debounce.200ms="search" type="text"
            class="w-full h-12 pl-12 pr-4 bg-white/5 border border-white/5 rounded-2xl focus:ring-1 focus:ring-primary/50 text-slate-100 placeholder:text-white/30 transition-all"
            placeholder="Buscar producto..." autofocus>
    </div>

    <!-- Category Pills -->
    <div class="flex gap-2 overflow-x-auto no-scrollbar">
        <button wire:click="$set('categoryFilter', '')"
            class="px-4 py-1.5 rounded-full text-xs font-medium transition-all whitespace-nowrap
            {{ !$categoryFilter ? 'bg-primary text-white' : 'bg-white/5 border border-white/10 text-slate-400' }}">
            Todos
        </button>
        @foreach($categories as $cat)
        <button wire:click="$set('categoryFilter', '{{ $cat->id }}')"
            class="px-4 py-1.5 rounded-full text-xs font-medium transition-all whitespace-nowrap
            {{ $categoryFilter == $cat->id ? 'bg-primary text-white' : 'bg-white/5 border border-white/10 text-slate-400' }}">
            {{ $cat->name }}
        </button>
        @endforeach
    </div>

    <!-- Product Results -->
    @if($search || $categoryFilter)
    <div class="space-y-2 max-h-48 overflow-y-auto">
        @forelse($products as $product)
        <button wire:click="addToCart({{ $product->id }})"
            class="w-full flex items-center justify-between p-3 bg-white/5 rounded-xl hover:bg-white/10 transition-all text-left">
            <div class="flex items-center gap-3">
                <div class="size-10 rounded-lg bg-primary/10 flex items-center justify-center border border-primary/20 overflow-hidden">
                    @if($product->photo)
                        <img src="{{ asset('storage/' . $product->photo) }}" class="size-full object-cover">
                    @else
                        <span class="material-symbols-outlined text-primary text-lg">{{ $product->category?->icon ?? 'set_meal' }}</span>
                    @endif
                </div>
                <div>
                    <p class="text-slate-100 font-medium text-sm">{{ $product->name }}</p>
                    <p class="text-white/30 text-xs">Stock: {{ $product->stock }}</p>
                </div>
            </div>
            <p class="text-primary font-bold">&euro; {{ number_format($product->sale_price, 2, ',', '.') }}</p>
        </button>
        @empty
        <p class="text-center text-white/30 text-sm py-4">Sin resultados</p>
        @endforelse
    </div>
    @endif

    <!-- Cart -->
    <div class="glass-card rounded-2xl overflow-hidden">
        <div class="p-4 border-b border-white/5 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">shopping_cart</span>
                <h3 class="font-bold text-slate-100">Carrito</h3>
                <span class="text-xs text-white/30">({{ count($cart) }})</span>
            </div>
            @if(count($cart) > 0)
            <button wire:click="clearCart" class="text-xs text-red-400 hover:text-red-300 transition-colors">Vaciar</button>
            @endif
        </div>

        @if(count($cart) > 0)
        <div class="divide-y divide-white/5 max-h-64 overflow-y-auto">
            @foreach($cart as $index => $item)
            <div class="p-3 flex items-center gap-3">
                <div class="flex-1 min-w-0">
                    <p class="text-slate-100 text-sm font-medium truncate">{{ $item['name'] }}</p>
                    <p class="text-white/30 text-xs">&euro; {{ number_format($item['price'], 2, ',', '.') }} /ud</p>
                </div>
                <div class="flex items-center gap-1">
                    <button wire:click="decrementQty({{ $index }})"
                        class="size-8 rounded-lg bg-white/5 flex items-center justify-center text-slate-300 hover:bg-white/10 transition-all">
                        <span class="material-symbols-outlined text-sm">remove</span>
                    </button>
                    <span class="w-8 text-center font-bold text-slate-100">{{ $item['quantity'] }}</span>
                    <button wire:click="incrementQty({{ $index }})"
                        class="size-8 rounded-lg bg-white/5 flex items-center justify-center text-slate-300 hover:bg-white/10 transition-all">
                        <span class="material-symbols-outlined text-sm">add</span>
                    </button>
                </div>
                <p class="w-20 text-right font-bold text-slate-100">&euro; {{ number_format($item['subtotal'], 2, ',', '.') }}</p>
                <button wire:click="removeFromCart({{ $index }})"
                    class="size-8 rounded-lg flex items-center justify-center text-red-400 hover:bg-red-500/10 transition-all">
                    <span class="material-symbols-outlined text-sm">close</span>
                </button>
            </div>
            @endforeach
        </div>

        <!-- Discount -->
        <div class="p-3 border-t border-white/5">
            <div class="flex items-center gap-2">
                <select wire:model.live="discountType"
                    class="h-9 px-3 bg-white/5 border border-white/5 rounded-lg text-xs text-slate-300 focus:ring-1 focus:ring-primary/50">
                    <option value="none">Sin descuento</option>
                    <option value="percentage">% Descuento</option>
                    <option value="fixed">&euro; Fijo</option>
                </select>
                @if($discountType !== 'none')
                <input wire:model.live.debounce.300ms="discountValue" type="number" step="0.01" min="0"
                    class="h-9 w-24 px-3 bg-white/5 border border-white/5 rounded-lg text-sm text-slate-100 text-center focus:ring-1 focus:ring-primary/50">
                @endif
            </div>
        </div>

        <!-- Totals -->
        <div class="p-4 border-t border-white/5 space-y-2">
            <div class="flex justify-between text-sm">
                <span class="text-white/50">Subtotal</span>
                <span class="text-slate-200">&euro; {{ number_format($this->subtotal, 2, ',', '.') }}</span>
            </div>
            @if($this->discountAmount > 0)
            <div class="flex justify-between text-sm">
                <span class="text-white/50">Descuento</span>
                <span class="text-red-400">-&euro; {{ number_format($this->discountAmount, 2, ',', '.') }}</span>
            </div>
            @endif
            <div class="flex justify-between text-sm">
                <span class="text-white/50">IVA ({{ $this->taxRate }}%)</span>
                <span class="text-slate-200">&euro; {{ number_format($this->taxAmount, 2, ',', '.') }}</span>
            </div>
            <div class="flex justify-between text-xl font-bold pt-2 border-t border-white/10">
                <span class="text-white">Total</span>
                <span class="text-primary">&euro; {{ number_format($this->total, 2, ',', '.') }}</span>
            </div>
        </div>
        @else
        <div class="p-8 text-center">
            <span class="material-symbols-outlined text-white/10 text-5xl">add_shopping_cart</span>
            <p class="text-white/30 text-sm mt-2">Busca y añade productos</p>
        </div>
        @endif
    </div>

    <!-- Checkout Button -->
    @if(count($cart) > 0)
    <button wire:click="checkout" wire:confirm="¿Confirmar venta por € {{ number_format($this->total, 2, ',', '.') }}?"
        class="w-full h-14 rounded-2xl bg-primary text-white text-lg font-bold shadow-2xl shadow-primary/40 transition-all active:scale-[0.98] flex items-center justify-center gap-2">
        <span wire:loading.remove wire:target="checkout">
            <span class="material-symbols-outlined">point_of_sale</span>
        </span>
        <span wire:loading.remove wire:target="checkout">Cobrar &euro; {{ number_format($this->total, 2, ',', '.') }}</span>
        <span wire:loading wire:target="checkout" class="material-symbols-outlined animate-spin">progress_activity</span>
    </button>
    @endif
    @endif
</div>
