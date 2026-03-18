<?php

namespace App\Livewire;

use App\Models\Task;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Mis Tareas')]
class MyTasks extends Component
{
    public string $statusFilter = 'all';

    public function updateStatus(int $taskId, string $status): void
    {
        Task::where('id', $taskId)
            ->where('assigned_to', auth()->id())
            ->update(['status' => $status]);
    }

    public function render()
    {
        $tasks = Task::where('assigned_to', auth()->id())
            ->when($this->statusFilter !== 'all', fn($q) => $q->where('status', $this->statusFilter))
            ->orderByRaw("CASE status WHEN 'in_progress' THEN 0 WHEN 'pending' THEN 1 WHEN 'completed' THEN 2 END")
            ->orderBy('due_date')
            ->get();

        return view('livewire.my-tasks', [
            'tasks' => $tasks,
        ]);
    }
}
