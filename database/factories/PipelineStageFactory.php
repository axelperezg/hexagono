<?php

namespace Database\Factories;

use App\Models\PipelineStage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PipelineStage>
 */
class PipelineStageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'position' => fake()->numberBetween(1, 20),
            'is_won' => false,
            'is_lost' => false,
        ];
    }

    /**
     * Indicate that the stage closes an opportunity as won.
     */
    public function won(): static
    {
        return $this->state(fn (array $attributes) => ['is_won' => true]);
    }

    /**
     * Indicate that the stage closes an opportunity as lost.
     */
    public function lost(): static
    {
        return $this->state(fn (array $attributes) => ['is_lost' => true]);
    }
}
