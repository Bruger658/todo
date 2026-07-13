<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function index(Request $request): View
    {   
        $tasks = Task::query()
            ->orderByRaw("case frequency when 'daily' then 1 when 'weekly' then 2 when 'monthly' then 3 else 4 end")
            ->orderByRaw('completed_at is not null')
            ->orderByRaw('due_date is null')
            ->orderBy('due_date')
            ->orderByRaw('realization_time is null')
            ->orderBy('realization_time')
            ->latest()
            ->get();

        return view('tasks.index', [
            'tasks' => $tasks,
            'tasksByFrequency' => $tasks->groupBy('frequency'),
            'frequencies' => $this->frequencies(),
            'calendar' => $this->calendar($tasks, $request->string('month')->toString()),          
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
        $task->forceFill([
            'completed_at' => $task->isCompleted() ? null : now(),
        ])->save();

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

    /**
     * @param  Collection<int, Task>  $tasks
     * @return array{
     *     monthLabel: string,
     *     previousMonth: string,
     *     currentMonth: string,
     *     nextMonth: string,
     *     weekdays: array<int, string>,
     *     weeks: array<int, array<int, array{date: Carbon, isCurrentMonth: bool, isToday: bool, markers: array<string, array<int, Task>>}>>
     * }
     */
    private function calendar(Collection $tasks, ?string $selectedMonth): array
    {
         $monthStart = $this->calendarMonthStart($selectedMonth);
        $monthEnd = $monthStart->copy()->endOfMonth();
        $calendarStart = $monthStart->copy()->startOfWeek(Carbon::MONDAY);
        $calendarEnd = $monthEnd->copy()->endOfWeek(Carbon::SUNDAY);
        $days = [];

        for ($date = $calendarStart->copy(); $date->lte($calendarEnd); $date->addDay()) {
            $day = $date->copy();

            $days[] = [
                'date' => $day,
                'isCurrentMonth' => $day->isSameMonth($monthStart),
                'isToday' => $day->isToday(),
                'markers' => $this->markersForDate($tasks, $day, $monthStart, $monthEnd),
            ];
        }

        return [
            'monthLabel' => $this->spanishMonthLabel($monthStart),
            'previousMonth' => $monthStart->copy()->subMonthNoOverflow()->format('Y-m'),
            'currentMonth' => now()->format('Y-m'),
            'nextMonth' => $monthStart->copy()->addMonthNoOverflow()->format('Y-m'),
            'weekdays' => ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
            'weeks' => array_chunk($days, 7),
        ];
    }

    private function calendarMonthStart(?string $selectedMonth): Carbon
    {
        if ($selectedMonth === null || $selectedMonth === '' || preg_match('/^\d{4}-\d{2}$/', $selectedMonth) !== 1) {
            return now()->startOfMonth();
        }

        $month = Carbon::createFromFormat('!Y-m', $selectedMonth);

        if ($month === false || $month->format('Y-m') !== $selectedMonth) {
            return now()->startOfMonth();
        }

        return $month->startOfMonth();
    }

    /**
     * @param  Collection<int, Task>  $tasks
     * @return array<string, array<int, Task>>
     */
    private function markersForDate(Collection $tasks, Carbon $date, Carbon $monthStart, Carbon $monthEnd): array
    {
        $markers = [
            'daily' => [],
            'weekly' => [],
            'monthly' => [],
        ];

        foreach ($tasks as $task) {
            if ($task->due_date === null || ! $date->betweenIncluded($monthStart, $monthEnd)) {
                continue;
            }

            if ($this->taskOccursOnDate($task, $date)) {
                 $markers[$task->frequency][] = $task;
            }
        }

        return array_filter($markers);
    }

    private function taskOccursOnDate(Task $task, Carbon $date): bool
    {
        $dueDate = $task->due_date->copy()->startOfDay();

        if ($date->lt($dueDate)) {
            return false;
        }

        return match ($task->frequency) {
            'daily' => $dueDate->diffInDays($date) < ($task->duration_days ?? 1),
            'weekly' => $dueDate->diffInDays($date) % 7 === 0,
            'monthly' => (int) $dueDate->format('d') === (int) $date->format('d'),
            default => false,
        };
    }

    private function spanishMonthLabel(Carbon $date): string
    {
        $months = [
            1 => 'enero',
            2 => 'febrero',
            3 => 'marzo',
            4 => 'abril',
            5 => 'mayo',
            6 => 'junio',
            7 => 'julio',
            8 => 'agosto',
            9 => 'septiembre',
            10 => 'octubre',
            11 => 'noviembre',
            12 => 'diciembre',
        ];

        return $months[(int) $date->format('n')] . ' ' . $date->format('Y');
    }
}