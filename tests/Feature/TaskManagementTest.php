<?php

use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates a task with a frequency', function () {
    $response = $this->post(route('tasks.store'), [
        'title' => 'Trabajar',
        'description' => 'Todos los días',
        'frequency' => 'daily',
    ]);

    $response->assertRedirect(route('tasks.index'));

    $this->assertDatabaseHas('tasks', [
        'title' => 'Trabajar',
        'description' => 'Todos los días',
        'frequency' => 'daily',
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