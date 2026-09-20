<?php

namespace Database\Factories;

use App\Enums\InteractionType;
use App\Models\Interaction;
use App\Models\Opportunity;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Interaction>
 */
class InteractionFactory extends Factory
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
            'type' => fake()->randomElement(InteractionType::cases()),
            'subject' => fake()->sentence(5),
            'notes' => fake()->paragraph(),
            'occurred_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
