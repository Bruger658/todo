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

