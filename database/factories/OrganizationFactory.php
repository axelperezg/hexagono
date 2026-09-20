<?php

namespace Database\Factories;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Organization>
 */
class OrganizationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company(),
            'sector' => fake()->randomElement(['Gobierno', 'Educación', 'Salud', 'Consultoría', 'Medios']),
            'website' => fake()->url(),
            'phone' => fake()->numerify('55########'),
            'notes' => fake()->sentence(),
        ];
    }
}
