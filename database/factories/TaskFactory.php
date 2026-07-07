<?php

namespace Database\Factories;

use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'description' => fake()->sentence(),
            'frequency' => fake()->randomElement(['daily', 'weekly', 'monthly']),
            'due_date' => fake()->optional()->date(),
            'realization_time' => fake()->optional()->time('H:i'),
            'completed_at' => null,
        ];
    }
}