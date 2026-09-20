<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Models\Sector;
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
            'acronym' => fake()->lexify('???'),
            'sector_id' => Sector::factory(),
            'website' => fake()->url(),
            'phone' => fake()->numerify('55########'),
            'notes' => fake()->sentence(),
        ];
    }
}
