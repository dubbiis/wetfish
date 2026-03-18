<?php

namespace App\Livewire;

use App\Models\Task;
use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Tareas del empleado')]
class EmployeeTasks extends Component
{
    public User $employee;

    // Formulario nueva tarea
    public string $title = '';
    public string $description = '';
    public string $dueDate = '';

    // Filtro
    public string $statusFilter = 'all';

    // Edición
    public bool $showForm = false;
    public ?int $editingTaskId = null;

    public function mount(User $employee): void
    {
        if ($employee->role !== 'employee') {
            abort(404);
        }
    }

    public function openForm(?int $taskId = null): void
    {
        if ($taskId) {
            $task = Task::findOrFail($taskId);
            $this->editingTaskId = $task->id;
            $this->title = $task->title;
            $this->description = $task->description ?? '';
            $this->dueDate = $task->due_date?->format('Y-m-d') ?? '';
        } else {
            $this->editingTaskId = null;
            $this->title = '';
            $this->description = '';
            $this->dueDate = '';
        }
        $this->showForm = true;
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->editingTaskId = null;
        $this->title = '';
        $this->description = '';
        $this->dueDate = '';
    }

    public function save(): void
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'dueDate' => 'nullable|date',
        ]);

        $data = [
            'title' => $this->title,
            'description' => $this->description ?: null,
            'due_date' => $this->dueDate ?: null,
        ];

        if ($this->editingTaskId) {
            Task::where('id', $this->editingTaskId)->update($data);
        } else {
            Task::create(array_merge($data, [
                'assigned_to' => $this->employee->id,
                'created_by' => auth()->id(),
                'status' => 'pending',
            ]));
        }

        $this->closeForm();
    }

    public function deleteTask(int $id): void
    {
        Task::where('id', $id)->where('assigned_to', $this->employee->id)->delete();
    }

    public function render()
    {
        $tasks = Task::where('assigned_to', $this->employee->id)
            ->when($this->statusFilter !== 'all', fn($q) => $q->where('status', $this->statusFilter))
            ->orderByRaw("CASE status WHEN 'in_progress' THEN 0 WHEN 'pending' THEN 1 WHEN 'completed' THEN 2 END")
            ->orderBy('due_date')
            ->get();

        return view('livewire.employee-tasks', [
            'tasks' => $tasks,
        ]);
    }
}
