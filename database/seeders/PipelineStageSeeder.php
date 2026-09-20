<?php

namespace Database\Seeders;

use App\Models\PipelineStage;
use Illuminate\Database\Seeder;

class PipelineStageSeeder extends Seeder
{
    /**
     * Seed the default sales pipeline stages. Safe to re-run.
     */
    public function run(): void
    {
        $stages = [
            ['name' => 'Prospecto'],
            ['name' => 'Primer contacto'],
            ['name' => 'Demo'],
            ['name' => 'Propuesta'],
            ['name' => 'Negociación'],
            ['name' => 'Ganado', 'is_won' => true],
            ['name' => 'Perdido', 'is_lost' => true],
        ];

        foreach ($stages as $position => $stage) {
            PipelineStage::updateOrCreate(
                ['name' => $stage['name']],
                ['position' => $position + 1, 'is_won' => $stage['is_won'] ?? false, 'is_lost' => $stage['is_lost'] ?? false],
            );
        }
    }
}
