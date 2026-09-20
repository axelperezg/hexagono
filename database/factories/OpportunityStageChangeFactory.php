<?php

namespace Database\Factories;

use App\Models\Opportunity;
use App\Models\OpportunityStageChange;
use App\Models\PipelineStage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OpportunityStageChange>
 */
class OpportunityStageChangeFactory extends Factory
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
            'from_stage_id' => null,
            'to_stage_id' => PipelineStage::factory(),
            'user_id' => User::factory(),
        ];
    }
}
