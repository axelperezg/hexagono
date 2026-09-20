<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Give opportunities created before stage history existed an initial
     * history entry for their current stage.
     */
    public function up(): void
    {
        DB::table('opportunities')
            ->whereNotExists(fn ($query) => $query
                ->select(DB::raw(1))
                ->from('opportunity_stage_changes')
                ->whereColumn('opportunity_stage_changes.opportunity_id', 'opportunities.id'))
            ->orderBy('id')
            ->each(fn (object $opportunity) => DB::table('opportunity_stage_changes')->insert([
                'opportunity_id' => $opportunity->id,
                'from_stage_id' => null,
                'to_stage_id' => $opportunity->pipeline_stage_id,
                'user_id' => $opportunity->user_id,
                'created_at' => $opportunity->created_at,
                'updated_at' => $opportunity->created_at,
            ]));
    }

    /**
     * The backfilled rows are indistinguishable from real history, so
     * there is nothing safe to undo.
     */
    public function down(): void
    {
        //
    }
};
