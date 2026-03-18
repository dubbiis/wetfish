<div class="space-y-4">
    <x-slot:header>Mis Tareas</x-slot:header>

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
        <div class="glass-card rounded-2xl p-4 space-y-3">
            <div class="flex items-start justify-between gap-3">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
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
                        <span class="text-[10px] flex items-center gap-0.5
                            {{ $task->due_date->isPast() && $task->status !== 'completed' ? 'text-red-400' : 'text-white/30' }}">
                            <span class="material-symbols-outlined text-[10px]">calendar_today</span>
                            {{ $task->due_date->format('d/m/Y') }}
                            @if($task->due_date->isPast() && $task->status !== 'completed')
                                (vencida)
                            @endif
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
            </div>

            <!-- Botones de estado -->
            <div class="flex gap-2">
                @if($task->status !== 'pending')
                <button wire:click="updateStatus({{ $task->id }}, 'pending')"
                    class="flex-1 h-9 rounded-lg bg-amber-500/10 border border-amber-500/20 text-amber-400 text-xs font-bold flex items-center justify-center gap-1 transition-all hover:bg-amber-500/20">
                    <span class="material-symbols-outlined text-sm">schedule</span>
                    Pendiente
                </button>
                @endif
                @if($task->status !== 'in_progress')
                <button wire:click="updateStatus({{ $task->id }}, 'in_progress')"
                    class="flex-1 h-9 rounded-lg bg-blue-500/10 border border-blue-500/20 text-blue-400 text-xs font-bold flex items-center justify-center gap-1 transition-all hover:bg-blue-500/20">
                    <span class="material-symbols-outlined text-sm">play_arrow</span>
                    En progreso
                </button>
                @endif
                @if($task->status !== 'completed')
                <button wire:click="updateStatus({{ $task->id }}, 'completed')"
                    class="flex-1 h-9 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-bold flex items-center justify-center gap-1 transition-all hover:bg-emerald-500/20">
                    <span class="material-symbols-outlined text-sm">check_circle</span>
                    Completada
                </button>
                @endif
            </div>
        </div>
        @empty
        <div class="glass-card rounded-xl p-8 text-center">
            <span class="material-symbols-outlined text-slate-500 text-4xl mb-2">task_alt</span>
            <p class="text-slate-400">No tienes tareas asignadas</p>
        </div>
        @endforelse
    </div>
</div>
