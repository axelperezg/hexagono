<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\TaskAction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TaskAction>
 */
class TaskActionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'task_id' => Task::factory(),
            'description' => fake()->sentence(),
            'performed_at' => fake()->dateTimeBetween('-1 week', 'now'),
        ];
    }
}
