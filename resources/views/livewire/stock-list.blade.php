<div class="space-y-6">
    <x-slot:header>Stock</x-slot:header>

    <!-- Search -->
    <div class="relative flex items-center w-full">
        <span class="material-symbols-outlined absolute left-4 text-white/30">search</span>
        <input wire:model.live.debounce.300ms="search" type="text"
            class="w-full h-12 pl-12 pr-4 bg-white/5 border border-white/5 rounded-2xl focus:ring-1 focus:ring-primary/50 text-slate-100 placeholder:text-white/30 transition-all"
            placeholder="Buscar producto...">
    </div>

    <!-- Filter Pills -->
    <div class="flex gap-3 overflow-x-auto no-scrollbar">
        <button wire:click="$set('categoryFilter', '')"
            class="flex h-10 shrink-0 items-center justify-center gap-2 rounded-full px-5 font-medium transition-all
            {{ !$categoryFilter ? 'bg-primary text-white shadow-lg shadow-primary/40' : 'bg-white/5 border border-white/10 text-slate-300' }}">
            <span class="text-sm">Todos</span>
        </button>
        @foreach($categories as $cat)
        <button wire:click="$set('categoryFilter', '{{ $cat->id }}')"
            class="flex h-10 shrink-0 items-center justify-center gap-2 rounded-full px-5 font-medium transition-all
            {{ $categoryFilter == $cat->id ? 'bg-primary text-white shadow-lg shadow-primary/40' : 'bg-white/5 border border-white/10 text-slate-300' }}">
            <span class="material-symbols-outlined text-sm">{{ $cat->icon ?? 'category' }}</span>
            <span class="text-sm">{{ $cat->name }}</span>
        </button>
        @endforeach
    </div>

    <!-- Product List -->
    <div class="space-y-3">
        @forelse($products as $product)
        <div class="glass-card flex items-center justify-between p-4 rounded-2xl transition-all hover:bg-white/[0.06]">
            <div class="flex items-center gap-4">
                <div class="size-14 rounded-xl bg-primary/10 flex items-center justify-center overflow-hidden border border-primary/20">
                    @if($product->photo)
                        <img src="{{ asset('storage/' . $product->photo) }}" class="size-full object-cover" alt="">
                    @else
                        <span class="material-symbols-outlined text-primary text-3xl">{{ $product->category?->icon ?? 'set_meal' }}</span>
                    @endif
                </div>
                <div class="flex flex-col">
                    <div class="flex items-center gap-2">
                        <span class="size-2 rounded-full shadow-sm
                            {{ $product->stock_status === 'ok' ? 'bg-green-500 shadow-green-500/50' : ($product->stock_status === 'low' ? 'bg-amber-500 shadow-amber-500/50' : 'bg-red-500 shadow-red-500/50') }}"></span>
                        <p class="text-slate-100 font-semibold leading-tight">{{ $product->name }}</p>
                    </div>
                    <p class="text-white/40 text-[10px] font-bold uppercase tracking-widest mt-1">{{ $product->category?->name ?? 'Sin categoría' }}</p>
                </div>
            </div>
            <div class="flex items-center gap-5">
                <div class="text-right">
                    <p class="text-2xl font-bold text-slate-100 tracking-tight">{{ $product->stock }}</p>
                    <p class="text-[9px] text-white/40 font-bold uppercase tracking-tighter">Uds</p>
                </div>
                <a href="{{ route('stock.edit', ['productId' => $product->id]) }}"
                    class="flex h-9 px-4 items-center justify-center rounded-xl bg-white/5 border border-white/5 text-slate-200 text-xs font-bold hover:bg-primary hover:border-primary transition-all uppercase tracking-widest">
                    Edit
                </a>
            </div>
        </div>
        @empty
        <div class="glass-card rounded-xl p-8 text-center">
            <span class="material-symbols-outlined text-slate-500 text-4xl mb-2">inventory_2</span>
            <p class="text-slate-400">No hay productos</p>
        </div>
        @endforelse
    </div>

    {{ $products->links() }}

    <!-- FAB -->
    <a href="{{ route('stock.edit', ['productId' => 'new']) }}"
        class="fixed right-6 bottom-28 size-14 rounded-full bg-primary text-white shadow-2xl shadow-primary/50 flex items-center justify-center transition-all active:scale-95 z-40 border border-white/10">
        <span class="material-symbols-outlined text-3xl">add</span>
    </a>
</div>
