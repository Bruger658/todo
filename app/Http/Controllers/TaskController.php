<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function index(): View
    {
        $tasks = Task::query()
            ->orderByRaw('completed_at is not null')
            ->orderBy('due_date')
            ->latest()
            ->get()
            ->groupBy('frequency');

        return view('tasks.index', [
            'tasksByFrequency' => $tasks,
            'frequencies' => $this->frequencies(),
        ]);
    }

    public function store(StoreTaskRequest $request): RedirectResponse
    {
        Task::query()->create($request->validated());

        return redirect()->route('tasks.index')->with('status', 'Actividad agregada correctamente.');
    }

    public function update(UpdateTaskRequest $request, Task $task): RedirectResponse
    {
        $task->update($request->validated());

        return redirect()->route('tasks.index')->with('status', 'Actividad actualizada correctamente.');
    }

    public function toggle(Task $task): RedirectResponse
    {
        $task->update([
            'completed_at' => $task->isCompleted() ? null : now(),
        ]);

        return redirect()->route('tasks.index')->with('status', 'Estado de la actividad actualizado.');
    }

    public function destroy(Task $task): RedirectResponse
    {
        $task->delete();

        return redirect()->route('tasks.index')->with('status', 'Actividad eliminada correctamente.');
    }

    /**
     * @return array<string, string>
     */
    private function frequencies(): array
    {
        return [
            'daily' => 'Diarias',
            'weekly' => 'Semanales',
            'monthly' => 'Mensuales',
        ];
    }
}