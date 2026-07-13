<?php

use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates a task with a frequency', function () {
    $response = $this->post(route('tasks.store'), [
        'title' => 'Trabajar',
        'description' => 'Todos los días',
        'frequency' => 'daily',
        'realization_time' => '08:30',
        'duration_days' => 3,
    ]);

    $response->assertRedirect(route('tasks.index'));

    $this->assertDatabaseHas('tasks', [
        'title' => 'Trabajar',
        'description' => 'Todos los días',
        'frequency' => 'daily',
        'realization_time' => '08:30',
        'duration_days' => 3,
    ]);
});

it('renders task cards in calendar order with incomplete tasks first', function () {
    $completedTask = Task::factory()->create([
        'title' => 'Completada',
        'frequency' => 'daily',
        'due_date' => now()->subDay()->toDateString(),
        'completed_at' => now(),
    ]);
    $pendingTask = Task::factory()->create([
        'title' => 'Pendiente',
        'frequency' => 'daily',
        'due_date' => now()->addDay()->toDateString(),
        'completed_at' => null,
    ]);

    $response = $this->get(route('tasks.index'));

    $response->assertSuccessful();
    $response->assertSeeInOrder([$pendingTask->title, $completedTask->title]);
});

it('shows calendar activity buttons grouped by frequency and nearest schedule first', function () {
    $weeklyTask = Task::factory()->create([
        'title' => 'Semanal cercana',
        'frequency' => 'weekly',
        'due_date' => now()->addDay()->toDateString(),
        'realization_time' => '09:00',
    ]);
    $monthlyTask = Task::factory()->create([
        'title' => 'Mensual cercana',
        'frequency' => 'monthly',
        'due_date' => now()->addDay()->toDateString(),
        'realization_time' => '09:00',
    ]);
    $laterDailyTask = Task::factory()->create([
        'title' => 'Diaria tarde',
        'frequency' => 'daily',
        'due_date' => now()->addDays(2)->toDateString(),
        'realization_time' => '18:00',
    ]);
    $nearerDailyTask = Task::factory()->create([
        'title' => 'Diaria cercana',
        'frequency' => 'daily',
        'due_date' => now()->addDay()->toDateString(),
        'realization_time' => '08:00',
    ]);

    $response = $this->get(route('tasks.index'));

    $response->assertSuccessful();
    $response->assertSeeInOrder([
        'Diarias',
        $nearerDailyTask->title,
        $laterDailyTask->title,
        'Semanales',
        $weeklyTask->title,
        'Mensuales',
        $monthlyTask->title,
    ]);
    $response->assertSee('data-task-card-open="task-card-'.$nearerDailyTask->id.'"', false);
});

it('shows current month calendar with recurring activity markers', function () {
    $this->travelTo('2026-07-13 10:00:00');

    Task::factory()->create([
        'title' => 'Rutina diaria',
        'frequency' => 'daily',
        'due_date' => '2026-07-10',
        'duration_days' => 4,
    ]);
    Task::factory()->create([
        'title' => 'Revisión semanal',
        'frequency' => 'weekly',
        'due_date' => '2026-07-06',
    ]);
    Task::factory()->create([
        'title' => 'Pago mensual',
        'frequency' => 'monthly',
        'due_date' => '2026-07-13',
    ]);

    $response = $this->get(route('tasks.index'));

    $response->assertSuccessful();
    $response->assertSee('Calendario de actividades');
    $response->assertSee('julio 2026');
    $response->assertSee('data-calendar-date="2026-07-13"', false);
    $response->assertSee('data-calendar-marker="daily"', false);
    $response->assertSee('data-calendar-marker="weekly"', false);
    $response->assertSee('data-calendar-marker="monthly"', false);
    $response->assertSee('data-task-card-open="task-card-'.Task::where('title', 'Rutina diaria')->value('id').'"', false);
    $response->assertSee('Rutina diaria');
    $response->assertSee('Revisión semanal');
    $response->assertSee('Pago mensual');
});

it('limits daily activity markers to the selected amount of days', function () {
    $this->travelTo('2026-07-13 10:00:00');

    Task::factory()->create([
        'title' => 'Rutina acotada',
        'frequency' => 'daily',
        'due_date' => '2026-07-10',
        'duration_days' => 2,
    ]);

    $response = $this->get(route('tasks.index'));

    $response->assertSuccessful();
    $response->assertSee('Duración:</span> 2 día(s)', false);
    $response->assertSee('data-calendar-task-date="2026-07-10"', false);
    $response->assertSee('data-calendar-task-date="2026-07-11"', false);
    $response->assertDontSee('data-calendar-task-date="2026-07-12"', false);
});

it('navigates the calendar to previous and next months', function () {
    $this->travelTo('2026-07-13 10:00:00');

    Task::factory()->create([
        'title' => 'Actividad de agosto',
        'frequency' => 'monthly',
        'due_date' => '2026-08-05',
    ]);

    $response = $this->get(route('tasks.index', ['month' => '2026-08']));

    $response->assertSuccessful();
    $response->assertSee('agosto 2026');
    $response->assertSee('aria-label="Ver mes anterior"', false);
    $response->assertSee('month=2026-07', false);
    $response->assertSee('aria-label="Ver mes siguiente"', false);
    $response->assertSee('month=2026-09', false);
    $response->assertSee('data-calendar-date="2026-08-05"', false);
    $response->assertSee('Actividad de agosto');
});

it('shows the due date before the realization time when a task has no description', function () {
    Task::factory()->create([
        'title' => 'Session con Laura',
        'description' => null,
        'frequency' => 'daily',
        'due_date' => '2026-07-13',
        'realization_time' => '18:15',
    ]);

    $response = $this->get(route('tasks.index'));

    $response->assertSuccessful();
    $response->assertSeeInOrder([
        'Session con Laura',
        'Fecha: 13/07/2026',
        'Hora: 18:15',
    ]);
});

it('marks overdue pending tasks in red automatically', function () {
    $this->travelTo('2026-07-08 10:00:00');

    $overdueTask = Task::factory()->create([
        'title' => 'Actividad atrasada',
        'frequency' => 'daily',
        'due_date' => '2026-07-08',
        'realization_time' => '09:00',
        'completed_at' => null,
    ]);

    $response = $this->get(route('tasks.index'));

    $response->assertSuccessful();
    $response->assertSee($overdueTask->title);
    $response->assertSee('Pendiente');
    $response->assertSee('border-rose-400/40', false);
});

it('keeps tasks pending until their realization time passes', function () {
    $previousTimezone = date_default_timezone_get();

    config(['app.timezone' => 'America/Argentina/Buenos_Aires']);
    date_default_timezone_set(config('app.timezone'));
    $this->travelTo('2026-07-08 15:30:00');

    $task = Task::factory()->create([
        'title' => 'Actividad de las cinco',
        'frequency' => 'daily',
        'due_date' => '2026-07-08',
        'realization_time' => '17:00',
        'completed_at' => null,
    ]);

    $response = $this->get(route('tasks.index'));

    $response->assertSuccessful();
    $response->assertSee($task->title);
    $response->assertDontSee('Pendiente');
    $response->assertDontSee('border-rose-400/40', false);
    date_default_timezone_set($previousTimezone);
});

it('does not mark completed overdue tasks as pending', function () {
    $this->travelTo('2026-07-08 10:00:00');

    Task::factory()->create([
        'title' => 'Actividad completada atrasada',
        'frequency' => 'daily',
        'due_date' => '2026-07-08',
        'realization_time' => '09:00',
        'completed_at' => now(),
    ]);

    $response = $this->get(route('tasks.index'));

    $response->assertSuccessful();
    $response->assertDontSee('Pendiente');
    $response->assertDontSee('border-rose-400/40', false);
});

it('shows the realization time field when editing a task from its calendar card', function () {
    $task = Task::factory()->create([
        'frequency' => 'daily',
        'realization_time' => '08:30',
    ]);

    $response = $this->get(route('tasks.index'));

    $response->assertSuccessful();
    $response->assertSee('Hora: 08:30');
    $response->assertSee('name="realization_time"', false);
    $response->assertSee('value="08:30"', false);
});

it('updates a task', function () {
    $task = Task::factory()->create([
        'title' => 'Original',
        'description' => 'Detalle original',
        'frequency' => 'daily',
        'due_date' => now()->addDay()->toDateString(),
        'realization_time' => '08:30',
    ]);

    $response = $this->from(route('tasks.index'))->put(route('tasks.update', $task), [
        'title' => 'Actualizada',
        'description' => 'Detalle actualizado',
        'frequency' => 'weekly',
        'due_date' => now()->addWeek()->toDateString(),
        'realization_time' => '08:30',
    ]);

    $response->assertRedirect(route('tasks.index'));

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'title' => 'Actualizada',
        'description' => 'Detalle actualizado',
        'frequency' => 'weekly',
        'due_date' => now()->addWeek()->toDateString(),
        'realization_time' => '08:30',
    ]);
});

it('requires a valid frequency when creating a task', function () {
    $response = $this->from(route('tasks.index'))->post(route('tasks.store'), [
        'title' => 'Trabajar',
        'description' => 'Todos los días',
    ]);

    $response->assertRedirect(route('tasks.index'));
    $response->assertSessionHasErrors('frequency');

    expect(Task::query()->count())->toBe(0);
});

it('asks whether to delete or keep a pending task when marking it done', function () {
    $task = Task::factory()->create([
        'title' => 'Comprar leche',
        'frequency' => 'daily',
        'completed_at' => null,
        'due_date' => now()->toDateString(),
    ]);

    $response = $this->get(route('tasks.index'));

    $response->assertSuccessful();
    $response->assertSee('data-task-card-open="task-card-'.$task->id.'"', false);
    $response->assertSee('data-completion-choice-open="completion-choice-'.$task->id.'"', false);
    $response->assertSee('¿Qué querés hacer con “Comprar leche”?');
    $response->assertSee('Eliminar actividad');
    $response->assertSee('Guardar hecha');
});

it('shows reopen for tasks kept as completed', function () {
    $task = Task::factory()->create([
        'title' => 'Lavar platos',
        'frequency' => 'daily',
        'completed_at' => null,
    ]);

    $this->patch(route('tasks.toggle', $task))
        ->assertRedirect(route('tasks.index'));

    $response = $this->get(route('tasks.index'));

    $response->assertSuccessful();
    $response->assertSee('Reabrir');
    $response->assertDontSee('completion-choice-'.$task->id, false);
}); 

