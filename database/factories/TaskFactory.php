<?php

namespace Database\Factories;

use App\Models\Opportunity;
use App\Models\Task;
use App\Models\User;
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
            'opportunity_id' => Opportunity::factory(),
            'user_id' => User::factory(),
            'title' => fake()->sentence(4),
            'notes' => null,
            'due_date' => fake()->dateTimeBetween('+1 day', '+1 month'),
            'completed_at' => null,
        ];
    }

    /**
     * Indicate that the task has been completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => ['completed_at' => now()]);
    }

    /**
     * Indicate that the task is pending and its due date has passed.
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => ['due_date' => today()->subDays(3)]);
    }
}
