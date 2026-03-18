<div class="space-y-4">
    <x-slot:header>Tareas de {{ $employee->name }}</x-slot:header>

    <!-- Volver -->
    <a href="{{ route('settings') }}" class="inline-flex items-center gap-1 text-sm text-white/40 hover:text-white/60 transition-colors">
        <span class="material-symbols-outlined text-sm">arrow_back</span>
        Volver a ajustes
    </a>

    <!-- Filtros -->
    <div class="flex gap-2 overflow-x-auto no-scrollbar">
        @foreach(['all' => 'Todas', 'pending' => 'Pendientes', 'in_progress' => 'En progreso', 'completed' => 'Completadas'] as $key => $label)
        <button wire:click="$set('statusFilter', '{{ $key }}')"
            class="px-4 py-2 rounded-full font-medium text-sm transition-all whitespace-nowrap
            {{ $statusFilter === $key ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'bg-white/5 border border-white/10 text-slate-300' }}">
            {{ $label }}
        </button>
        @endforeach
    </div>

    <!-- Resumen -->
    <div class="grid grid-cols-3 gap-3">
        <div class="glass-card rounded-2xl p-3 text-center">
            <p class="text-white/40 text-[10px] font-bold uppercase tracking-widest">Pendientes</p>
            <p class="text-xl font-bold text-amber-400 mt-1">{{ $tasks->where('status', 'pending')->count() }}</p>
        </div>
        <div class="glass-card rounded-2xl p-3 text-center">
            <p class="text-white/40 text-[10px] font-bold uppercase tracking-widest">En progreso</p>
            <p class="text-xl font-bold text-blue-400 mt-1">{{ $tasks->where('status', 'in_progress')->count() }}</p>
        </div>
        <div class="glass-card rounded-2xl p-3 text-center">
            <p class="text-white/40 text-[10px] font-bold uppercase tracking-widest">Completadas</p>
            <p class="text-xl font-bold text-emerald-400 mt-1">{{ $tasks->where('status', 'completed')->count() }}</p>
        </div>
    </div>

    <!-- Lista de tareas -->
    <div class="space-y-2">
        @forelse($tasks as $task)
        <div class="glass-card rounded-2xl p-4 space-y-2">
            <div class="flex items-start justify-between gap-3">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        @php
                            $statusColors = [
                                'pending' => 'bg-amber-500/20 text-amber-400',
                                'in_progress' => 'bg-blue-500/20 text-blue-400',
                                'completed' => 'bg-emerald-500/20 text-emerald-400',
                            ];
                            $statusLabels = [
                                'pending' => 'Pendiente',
                                'in_progress' => 'En progreso',
                                'completed' => 'Completada',
                            ];
                        @endphp
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $statusColors[$task->status] }}">
                            {{ $statusLabels[$task->status] }}
                        </span>
                        @if($task->due_date)
                        <span class="text-[10px] text-white/30 flex items-center gap-0.5">
                            <span class="material-symbols-outlined text-[10px]">calendar_today</span>
                            {{ $task->due_date->format('d/m/Y') }}
                        </span>
                        @endif
                    </div>
                    <h4 class="text-slate-100 font-semibold mt-1 {{ $task->status === 'completed' ? 'line-through opacity-50' : '' }}">
                        {{ $task->title }}
                    </h4>
                    @if($task->description)
                    <p class="text-white/40 text-sm mt-1">{{ $task->description }}</p>
                    @endif
                </div>
                <div class="flex items-center gap-1 shrink-0">
                    <button wire:click="openForm({{ $task->id }})"
                        class="size-8 rounded-lg bg-white/5 flex items-center justify-center hover:bg-white/10 transition-all">
                        <span class="material-symbols-outlined text-slate-300 text-sm">edit</span>
                    </button>
                    <button wire:click="deleteTask({{ $task->id }})" wire:confirm="¿Eliminar esta tarea?"
                        class="size-8 rounded-lg bg-white/5 flex items-center justify-center hover:bg-red-500/20 transition-all">
                        <span class="material-symbols-outlined text-red-400 text-sm">delete</span>
                    </button>
                </div>
            </div>
        </div>
        @empty
        <div class="glass-card rounded-xl p-8 text-center">
            <span class="material-symbols-outlined text-slate-500 text-4xl mb-2">task</span>
            <p class="text-slate-400">No hay tareas</p>
        </div>
        @endforelse
    </div>

    <!-- FAB nueva tarea -->
    <button wire:click="openForm"
        class="fixed right-6 bottom-28 size-14 rounded-full bg-primary text-white shadow-2xl shadow-primary/40 flex items-center justify-center transition-all active:scale-90 z-40">
        <span class="material-symbols-outlined text-2xl">add</span>
    </button>

    <!-- Modal formulario -->
    @if($showForm)
    <div class="fixed inset-0 z-50 flex items-end justify-center">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" wire:click="closeForm"></div>
        <div class="relative w-full max-w-lg bg-background-dark border border-white/10 rounded-t-3xl flex flex-col max-h-[85vh]">
            <!-- Header fijo -->
            <div class="flex items-center justify-between p-5 pb-4 shrink-0">
                <h3 class="text-xl font-bold text-white">{{ $editingTaskId ? 'Editar tarea' : 'Nueva tarea' }}</h3>
                <button wire:click="closeForm" class="size-10 rounded-full bg-white/5 flex items-center justify-center">
                    <span class="material-symbols-outlined text-slate-300">close</span>
                </button>
            </div>

            <!-- Contenido scrollable -->
            <div class="overflow-y-auto flex-1 px-5 space-y-3">
                <div>
                    <label class="text-xs font-bold uppercase tracking-widest text-white/40 mb-1 block">Título</label>
                    <input wire:model="title" type="text"
                        class="w-full h-12 px-4 bg-white/5 border border-white/5 rounded-xl focus:ring-1 focus:ring-primary/50 text-slate-100 placeholder:text-white/30"
                        placeholder="Ej: Limpiar acuarios zona A">
                    @error('title') <span class="text-red-400 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="text-xs font-bold uppercase tracking-widest text-white/40 mb-1 block">Descripción</label>
                    <textarea wire:model="description" rows="3"
                        class="w-full px-4 py-3 bg-white/5 border border-white/5 rounded-xl focus:ring-1 focus:ring-primary/50 text-slate-100 placeholder:text-white/30 resize-none"
                        placeholder="Detalles de la tarea..."></textarea>
                </div>

                <div class="pb-2">
                    <label class="text-xs font-bold uppercase tracking-widest text-white/40 mb-1 block">Fecha límite</label>
                    <input wire:model="dueDate" type="date"
                        class="w-full h-12 px-4 bg-white/5 border border-white/5 rounded-xl focus:ring-1 focus:ring-primary/50 text-slate-100">
                </div>
            </div>

            <!-- Botón fijo al fondo -->
            <div class="p-5 pt-4 shrink-0">
                <button wire:click="save"
                    class="w-full h-12 rounded-xl bg-primary text-white font-semibold shadow-lg shadow-primary/30 transition-all active:scale-[0.98]">
                    {{ $editingTaskId ? 'Guardar cambios' : 'Crear tarea' }}
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
