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
        <h2 class="text-4xl font-bold text-white tracking-tight">&euro; {{ number_format($total, 2, ',', '.') }}</h2>
        <div class="flex gap-4 mt-2 text-xs">
            <span class="text-slate-400">Base: <span class="text-white font-semibold">&euro; {{ number_format($totalBase, 2, ',', '.') }}</span></span>
            <span class="text-slate-400">IVA: <span class="text-amber-400 font-semibold">&euro; {{ number_format($totalTax, 2, ',', '.') }}</span></span>
        </div>
        <div class="absolute -bottom-10 -right-10 h-40 w-40 bg-primary/10 blur-3xl rounded-full"></div>
    </div>

    <!-- Acciones -->
    <div class="flex items-center gap-3">
        <button wire:click="openAdd"
            class="flex-1 h-12 rounded-xl bg-primary text-white font-semibold shadow-lg shadow-primary/30 flex items-center justify-center gap-2 transition-all active:scale-95">
            <span class="material-symbols-outlined">add</span>
            Añadir gasto
        </button>
        <label class="size-12 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400 hover:text-amber-300 transition-all cursor-pointer"
            title="Escanear factura con IA">
            <span class="material-symbols-outlined">document_scanner</span>
            <input type="file" wire:model="expenseFile" class="hidden" accept=".pdf,.jpg,.jpeg,.png,.webp,image/*" capture="environment">
        </label>
        <button wire:click="openCategoryModal"
            class="size-12 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center text-white/60 hover:text-white transition-all"
            title="Gestionar categorías">
            <span class="material-symbols-outlined">settings</span>
        </button>
    </div>

    <!-- Loading IA -->
    @if($processingExpense)
    <div class="glass-card rounded-2xl p-6 flex flex-col items-center justify-center gap-3 text-amber-400">
        <span class="material-symbols-outlined animate-spin text-3xl">progress_activity</span>
        <span class="text-sm font-medium">Analizando factura con IA...</span>
        <span class="text-xs text-white/30">Los campos se rellenaran automaticamente</span>
    </div>
    @endif

    <div wire:loading wire:target="expenseFile" class="glass-card rounded-2xl p-4 flex items-center justify-center gap-2 text-amber-400">
        <span class="material-symbols-outlined animate-spin">progress_activity</span>
        <span class="text-sm font-medium">Subiendo archivo...</span>
    </div>

    <!-- Gastos Fijos Recurrentes -->
    <div x-data="{ open: false }">
        <button @click="open = !open"
            class="w-full glass-card rounded-2xl p-4 flex items-center justify-between transition-all">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-primary">repeat</span>
                <span class="text-white font-semibold">Gastos fijos</span>
                <span class="px-2 py-0.5 rounded-full bg-primary/20 text-primary text-xs font-bold">{{ $recurringExpenses->count() }}</span>
            </div>
            <span class="material-symbols-outlined text-white/30 transition-transform" :class="open ? 'rotate-180' : ''">expand_more</span>
        </button>

        <div x-show="open" x-collapse class="space-y-2 mt-2">
            @forelse($recurringExpenses as $rec)
            <div class="glass-card rounded-xl p-4 flex items-center gap-3 {{ !$rec->is_active ? 'opacity-40' : '' }}">
                <div class="size-10 rounded-lg bg-primary/10 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-primary text-lg">{{ $rec->category?->icon ?? 'receipt' }}</span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-slate-100 font-semibold text-sm truncate">{{ $rec->concept }}</p>
                    <p class="text-xs text-white/40">
                        {{ $rec->category?->name ?? '—' }} ·
                        &euro; {{ number_format($rec->amount, 2, ',', '.') }}
                        @if($rec->tax_rate > 0)
                            + {{ number_format($rec->tax_rate, 0) }}% IVA
                        @endif
                        · <span class="text-primary/80">{{ $rec->frequency_label }}</span>
                    </p>
                </div>
                <div class="flex items-center gap-1 shrink-0">
                    <button wire:click="toggleRecurring({{ $rec->id }})"
                        class="size-8 rounded-lg flex items-center justify-center transition-all {{ $rec->is_active ? 'bg-emerald-500/10 text-emerald-400' : 'bg-white/5 text-white/30' }}"
                        title="{{ $rec->is_active ? 'Desactivar' : 'Activar' }}">
                        <span class="material-symbols-outlined text-base">{{ $rec->is_active ? 'toggle_on' : 'toggle_off' }}</span>
                    </button>
                    <button wire:click="openEditRecurring({{ $rec->id }})"
                        class="size-8 rounded-lg bg-white/5 flex items-center justify-center text-white/40 hover:text-white transition-all">
                        <span class="material-symbols-outlined text-base">edit</span>
                    </button>
                    <button wire:click="deleteRecurring({{ $rec->id }})"
                        wire:confirm="¿Eliminar este gasto fijo?"
                        class="size-8 rounded-lg bg-white/5 flex items-center justify-center text-white/40 hover:text-rose-400 transition-all">
                        <span class="material-symbols-outlined text-base">delete</span>
                    </button>
                </div>
            </div>
            @empty
            <p class="text-slate-500 text-sm text-center py-2">No hay gastos fijos configurados</p>
            @endforelse

            <button wire:click="openAddRecurring"
                class="w-full h-10 rounded-xl border border-dashed border-white/10 text-white/40 text-sm font-medium flex items-center justify-center gap-2 hover:text-white hover:border-primary/30 transition-all">
                <span class="material-symbols-outlined text-base">add</span>
                Añadir gasto fijo
            </button>
        </div>
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
                    @if($expense->recurring_expense_id)
                        <span class="material-symbols-outlined text-xs text-primary/60 align-middle">repeat</span>
                    @endif
                    {{ $expense->category?->name ?? '—' }} · {{ $expense->date->format('d/m/Y') }}
                    @if($expense->notes)
                        · {{ Str::limit($expense->notes, 40) }}
                    @endif
                </p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <div class="text-right">
                    <p class="text-lg font-bold text-white">&euro; {{ number_format($expense->total, 2, ',', '.') }}</p>
                    <p class="text-[10px] text-white/30">{{ number_format($expense->tax_rate, 0) }}% IVA</p>
                </div>
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
    <div class="fixed inset-0 z-[60] flex items-end justify-center pb-24">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" wire:click="closeModal"></div>
        <div class="relative w-full max-w-lg bg-[#171121] border border-white/10 rounded-t-3xl flex flex-col max-h-[85vh]">
            <!-- Header fijo -->
            <div class="flex items-center justify-between p-5 pb-4 shrink-0">
                <h3 class="text-lg font-bold text-white">{{ $editingId ? 'Editar gasto' : 'Nuevo gasto' }}</h3>
                <button wire:click="closeModal" class="text-white/40 hover:text-white">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <!-- Contenido scrollable -->
            <div class="overflow-y-auto flex-1 px-5 space-y-3">
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

                <div class="grid grid-cols-3 gap-3" x-data="{
                    base: $wire.entangle('amount'),
                    rate: $wire.entangle('taxRate'),
                    get taxAmt() { return (parseFloat(this.base) || 0) * (parseFloat(this.rate) || 0) / 100; },
                    get total() { return (parseFloat(this.base) || 0) + this.taxAmt; }
                }">
                    <div>
                        <label class="text-xs font-bold uppercase tracking-widest text-white/40 mb-1 block">Base (&euro;)</label>
                        <input wire:model.live="amount" type="number" step="0.01" min="0" placeholder="0.00"
                            class="w-full h-12 px-4 bg-white/5 border border-white/10 rounded-xl text-slate-100 placeholder:text-white/30 focus:ring-1 focus:ring-primary/50">
                        @error('amount') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-xs font-bold uppercase tracking-widest text-white/40 mb-1 block">IVA %</label>
                        <input wire:model.live="taxRate" type="number" step="1" min="0" max="100"
                            class="w-full h-12 px-4 bg-white/5 border border-white/10 rounded-xl text-slate-100 text-center focus:ring-1 focus:ring-primary/50">
                        @error('taxRate') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-xs font-bold uppercase tracking-widest text-white/40 mb-1 block">Total</label>
                        <div class="w-full h-12 px-4 bg-white/5 border border-white/5 rounded-xl flex items-center text-emerald-400 font-bold">
                            &euro; <span x-text="total.toFixed(2)" class="ml-1"></span>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="text-xs font-bold uppercase tracking-widest text-white/40 mb-1 block">Fecha</label>
                    <input wire:model="date" type="date"
                        class="w-full h-12 px-4 bg-white/5 border border-white/10 rounded-xl text-slate-100 focus:ring-1 focus:ring-primary/50">
                    @error('date') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="pb-2">
                    <label class="text-xs font-bold uppercase tracking-widest text-white/40 mb-1 block">Notas (opcional)</label>
                    <textarea wire:model="notes" rows="2" placeholder="Observaciones..."
                        class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-slate-100 placeholder:text-white/30 focus:ring-1 focus:ring-primary/50 resize-none"></textarea>
                    @error('notes') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Botón fijo al fondo -->
            <div class="p-5 pt-4 shrink-0">
                <button wire:click="saveExpense"
                    class="w-full h-12 rounded-xl bg-primary text-white font-semibold shadow-lg shadow-primary/30 transition-all active:scale-95">
                    {{ $editingId ? 'Guardar cambios' : 'Añadir gasto' }}
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- ── Modal gestionar categorías ── -->
    @if($showCategoryModal)
    <div class="fixed inset-0 z-[60] flex items-end justify-center pb-24">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" wire:click="closeCategoryModal"></div>
        <div class="relative w-full max-w-lg bg-[#171121] border border-white/10 rounded-t-3xl flex flex-col max-h-[85vh]">
            <!-- Header fijo -->
            <div class="flex items-center justify-between p-5 pb-4 shrink-0">
                <h3 class="text-lg font-bold text-white">Gestionar categorías</h3>
                <button wire:click="closeCategoryModal" class="text-white/40 hover:text-white">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <!-- Contenido scrollable -->
            <div class="overflow-y-auto flex-1 px-5 space-y-2">
                @error('deleteCategory')
                <p class="text-rose-400 text-sm bg-rose-500/10 rounded-xl px-4 py-2">{{ $message }}</p>
                @enderror

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

                <div class="border-t border-white/10 pt-4 space-y-3 pb-2">
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
                </div>
            </div>

            <!-- Botón fijo al fondo -->
            <div class="p-5 pt-4 shrink-0">
                <button wire:click="saveCategory"
                    class="w-full h-12 rounded-xl bg-primary/80 text-white font-semibold transition-all active:scale-95">
                    Añadir categoría
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- ── Modal gastos fijos recurrentes ── -->
    @if($showRecurringModal)
    <div class="fixed inset-0 z-[60] flex items-end justify-center pb-24">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" wire:click="closeRecurringModal"></div>
        <div class="relative w-full max-w-lg bg-[#171121] border border-white/10 rounded-t-3xl flex flex-col max-h-[85vh]">
            <div class="flex items-center justify-between p-5 pb-4 shrink-0">
                <h3 class="text-lg font-bold text-white">{{ $editingRecurringId ? 'Editar gasto fijo' : 'Nuevo gasto fijo' }}</h3>
                <button wire:click="closeRecurringModal" class="text-white/40 hover:text-white">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <div class="overflow-y-auto flex-1 px-5 space-y-3">
                <div>
                    <label class="text-xs font-bold uppercase tracking-widest text-white/40 mb-1 block">Categoría</label>
                    <select wire:model="recurringCategoryId"
                        class="w-full h-12 px-4 bg-white/5 border border-white/10 rounded-xl text-slate-100 focus:ring-1 focus:ring-primary/50">
                        <option value="">Seleccionar...</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    @error('recurringCategoryId') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="text-xs font-bold uppercase tracking-widest text-white/40 mb-1 block">Concepto</label>
                    <input wire:model="recurringConcept" type="text" placeholder="Ej: Cuota autónomo"
                        class="w-full h-12 px-4 bg-white/5 border border-white/10 rounded-xl text-slate-100 placeholder:text-white/30 focus:ring-1 focus:ring-primary/50">
                    @error('recurringConcept') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-bold uppercase tracking-widest text-white/40 mb-1 block">Base (&euro;)</label>
                        <input wire:model="recurringAmount" type="number" step="0.01" min="0" placeholder="0.00"
                            class="w-full h-12 px-4 bg-white/5 border border-white/10 rounded-xl text-slate-100 placeholder:text-white/30 focus:ring-1 focus:ring-primary/50">
                        @error('recurringAmount') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-xs font-bold uppercase tracking-widest text-white/40 mb-1 block">IVA %</label>
                        <input wire:model="recurringTaxRate" type="number" step="1" min="0" max="100"
                            class="w-full h-12 px-4 bg-white/5 border border-white/10 rounded-xl text-slate-100 text-center focus:ring-1 focus:ring-primary/50">
                        @error('recurringTaxRate') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="pb-2">
                    <label class="text-xs font-bold uppercase tracking-widest text-white/40 mb-1 block">Frecuencia</label>
                    <div class="grid grid-cols-3 gap-2">
                        @foreach(['monthly' => 'Mensual', 'quarterly' => 'Trimestral', 'annual' => 'Anual'] as $val => $lbl)
                        <button type="button" wire:click="$set('recurringFrequency', '{{ $val }}')"
                            class="h-11 rounded-xl font-medium text-sm transition-all
                            {{ $recurringFrequency === $val ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'bg-white/5 border border-white/10 text-slate-300' }}">
                            {{ $lbl }}
                        </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="p-5 pt-4 shrink-0">
                <button wire:click="saveRecurring"
                    class="w-full h-12 rounded-xl bg-primary text-white font-semibold shadow-lg shadow-primary/30 transition-all active:scale-95">
                    {{ $editingRecurringId ? 'Guardar cambios' : 'Crear gasto fijo' }}
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
