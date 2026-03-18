<div class="space-y-6">
    <x-slot:header>Gastos</x-slot:header>

    <!-- Entrada de peces -->
    <a href="{{ route('invoices.import') }}"
        class="glass-card rounded-2xl p-4 flex items-center gap-4 border border-primary/20">
        <div class="size-12 rounded-xl bg-primary/20 flex items-center justify-center shrink-0">
            <span class="material-symbols-outlined text-primary text-2xl">set_meal</span>
        </div>
        <div class="flex-1">
            <p class="font-semibold text-white">Entrada de peces</p>
            <p class="text-xs text-white/40">Registrar nueva entrada de stock</p>
        </div>
        <span class="material-symbols-outlined text-white/30">chevron_right</span>
    </a>

    <!-- Period Filter -->
    <div class="flex gap-2 overflow-x-auto no-scrollbar">
        @foreach(['week' => 'Semana', 'month' => 'Mes', 'year' => 'Año', 'all' => 'Todo'] as $key => $label)
        <button wire:click="setPeriod('{{ $key }}')"
            class="px-5 py-2 rounded-full font-medium text-sm transition-all whitespace-nowrap
            {{ $period === $key ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'bg-white/5 border border-white/10 text-slate-300' }}">
            {{ $label }}
        </button>
        @endforeach
    </div>

    <!-- Total -->
    <div class="glass rounded-xl p-6 relative overflow-hidden">
        <p class="text-xs font-bold uppercase tracking-widest text-white/40 mb-1">Total gastos operativos</p>
        <h2 class="text-4xl font-bold text-white tracking-tight">€ {{ number_format($total, 2, ',', '.') }}</h2>
        <div class="absolute -bottom-10 -right-10 h-40 w-40 bg-primary/10 blur-3xl rounded-full"></div>
    </div>

    <!-- Acciones -->
    <div class="flex items-center gap-3">
        <button wire:click="openAdd"
            class="flex-1 h-12 rounded-xl bg-primary text-white font-semibold shadow-lg shadow-primary/30 flex items-center justify-center gap-2 transition-all active:scale-95">
            <span class="material-symbols-outlined">add</span>
            Añadir gasto
        </button>
        <button wire:click="openCategoryModal"
            class="size-12 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center text-white/60 hover:text-white transition-all"
            title="Gestionar categorías">
            <span class="material-symbols-outlined">settings</span>
        </button>
    </div>

    <!-- Lista de gastos -->
    <div class="space-y-3">
        @forelse($expenses as $expense)
        <div class="glass-card rounded-2xl p-4 flex items-center gap-4">
            <div class="size-11 rounded-xl bg-primary/10 border border-primary/20 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-primary text-xl">{{ $expense->category?->icon ?? 'receipt' }}</span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-slate-100 font-semibold truncate">{{ $expense->concept }}</p>
                <p class="text-xs text-white/40 mt-0.5">
                    {{ $expense->category?->name ?? '—' }} · {{ $expense->date->format('d/m/Y') }}
                    @if($expense->notes)
                        · {{ Str::limit($expense->notes, 40) }}
                    @endif
                </p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <p class="text-lg font-bold text-white">€ {{ number_format($expense->amount, 2, ',', '.') }}</p>
                <button wire:click="openEdit({{ $expense->id }})"
                    class="size-8 rounded-lg bg-white/5 flex items-center justify-center text-white/40 hover:text-white transition-all">
                    <span class="material-symbols-outlined text-base">edit</span>
                </button>
                <button wire:click="deleteExpense({{ $expense->id }})"
                    wire:confirm="¿Eliminar este gasto?"
                    class="size-8 rounded-lg bg-white/5 flex items-center justify-center text-white/40 hover:text-rose-400 transition-all">
                    <span class="material-symbols-outlined text-base">delete</span>
                </button>
            </div>
        </div>
        @empty
        <div class="glass-card rounded-2xl p-8 text-center">
            <span class="material-symbols-outlined text-slate-500 text-4xl mb-2 block">receipt_long</span>
            <p class="text-slate-400">No hay gastos en este periodo</p>
        </div>
        @endforelse
    </div>

    {{ $expenses->links() }}

    <!-- ── Modal añadir/editar gasto ── -->
    @if($showAddModal)
    <div class="fixed inset-0 z-50 flex items-end justify-center">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" wire:click="closeModal"></div>
        <div class="relative w-full max-w-lg bg-[#171121] border border-white/10 rounded-t-3xl p-5 space-y-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-white">{{ $editingId ? 'Editar gasto' : 'Nuevo gasto' }}</h3>
                <button wire:click="closeModal" class="text-white/40 hover:text-white">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <div class="space-y-3">
                <div>
                    <label class="text-xs font-bold uppercase tracking-widest text-white/40 mb-1 block">Categoría</label>
                    <select wire:model="categoryId"
                        class="w-full h-12 px-4 bg-white/5 border border-white/10 rounded-xl text-slate-100 focus:ring-1 focus:ring-primary/50">
                        <option value="">Seleccionar categoría...</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    @error('categoryId') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="text-xs font-bold uppercase tracking-widest text-white/40 mb-1 block">Concepto</label>
                    <input wire:model="concept" type="text" placeholder="Ej: Factura luz febrero"
                        class="w-full h-12 px-4 bg-white/5 border border-white/10 rounded-xl text-slate-100 placeholder:text-white/30 focus:ring-1 focus:ring-primary/50">
                    @error('concept') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-bold uppercase tracking-widest text-white/40 mb-1 block">Importe (€)</label>
                        <input wire:model="amount" type="number" step="0.01" min="0" placeholder="0.00"
                            class="w-full h-12 px-4 bg-white/5 border border-white/10 rounded-xl text-slate-100 placeholder:text-white/30 focus:ring-1 focus:ring-primary/50">
                        @error('amount') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-xs font-bold uppercase tracking-widest text-white/40 mb-1 block">Fecha</label>
                        <input wire:model="date" type="date"
                            class="w-full h-12 px-4 bg-white/5 border border-white/10 rounded-xl text-slate-100 focus:ring-1 focus:ring-primary/50">
                        @error('date') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="text-xs font-bold uppercase tracking-widest text-white/40 mb-1 block">Notas (opcional)</label>
                    <textarea wire:model="notes" rows="2" placeholder="Observaciones..."
                        class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-slate-100 placeholder:text-white/30 focus:ring-1 focus:ring-primary/50 resize-none"></textarea>
                    @error('notes') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <button wire:click="saveExpense"
                class="w-full h-12 rounded-xl bg-primary text-white font-semibold shadow-lg shadow-primary/30 transition-all active:scale-95">
                {{ $editingId ? 'Guardar cambios' : 'Añadir gasto' }}
            </button>
        </div>
    </div>
    @endif

    <!-- ── Modal gestionar categorías ── -->
    @if($showCategoryModal)
    <div class="fixed inset-0 z-50 flex items-end justify-center">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" wire:click="closeCategoryModal"></div>
        <div class="relative w-full max-w-lg bg-[#171121] border border-white/10 rounded-t-3xl p-5 space-y-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-white">Gestionar categorías</h3>
                <button wire:click="closeCategoryModal" class="text-white/40 hover:text-white">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            @error('deleteCategory')
            <p class="text-rose-400 text-sm bg-rose-500/10 rounded-xl px-4 py-2">{{ $message }}</p>
            @enderror

            <div class="space-y-2">
                @foreach($categories as $cat)
                <div class="flex items-center gap-3 glass-card rounded-xl px-4 py-3">
                    <span class="material-symbols-outlined text-primary text-xl">{{ $cat->icon }}</span>
                    <span class="flex-1 text-slate-200 text-sm">{{ $cat->name }}</span>
                    <button wire:click="deleteCategory({{ $cat->id }})"
                        class="text-white/30 hover:text-rose-400 transition-all">
                        <span class="material-symbols-outlined text-base">delete</span>
                    </button>
                </div>
                @endforeach
            </div>

            <div class="border-t border-white/10 pt-4 space-y-3">
                <p class="text-xs font-bold uppercase tracking-widest text-white/40">Nueva categoría</p>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs text-white/40 mb-1 block">Nombre</label>
                        <input wire:model="newCategoryName" type="text" placeholder="Ej: Publicidad"
                            class="w-full h-11 px-3 bg-white/5 border border-white/10 rounded-xl text-slate-100 placeholder:text-white/30 focus:ring-1 focus:ring-primary/50 text-sm">
                        @error('newCategoryName') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-xs text-white/40 mb-1 block">Icono Material</label>
                        <input wire:model="newCategoryIcon" type="text" placeholder="Ej: campaign"
                            class="w-full h-11 px-3 bg-white/5 border border-white/10 rounded-xl text-slate-100 placeholder:text-white/30 focus:ring-1 focus:ring-primary/50 text-sm">
                    </div>
                </div>
                <button wire:click="saveCategory"
                    class="w-full h-11 rounded-xl bg-primary/80 text-white font-semibold text-sm transition-all active:scale-95">
                    Añadir categoría
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
