<?php

namespace Database\Factories;

use App\Models\Opportunity;
use App\Models\Organization;
use App\Models\PipelineStage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Opportunity>
 */
class OpportunityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'pipeline_stage_id' => PipelineStage::factory(),
            'user_id' => User::factory(),
            'title' => fake()->sentence(4),
            'estimated_amount' => fake()->randomFloat(2, 10000, 500000),
            'currency' => 'MXN',
            'expected_close_date' => fake()->dateTimeBetween('now', '+6 months'),
            'notes' => fake()->sentence(),
        ];
    }
}
