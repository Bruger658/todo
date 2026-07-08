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
    ]);

    $response->assertRedirect(route('tasks.index'));

    $this->assertDatabaseHas('tasks', [
        'title' => 'Trabajar',
        'description' => 'Todos los días',
        'frequency' => 'daily',
        'realization_time' => '08:30',
    ]);
});

it('shows tasks ordered with incomplete tasks first', function () {
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

it('shows task groups by frequency and nearest schedule first', function () {
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

it('shows the realization time field when editing a task', function () {
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

