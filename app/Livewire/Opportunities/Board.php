<?php

namespace App\Livewire\Opportunities;

use App\Models\Opportunity;
use App\Models\PipelineStage;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Kanban view of the pipeline (routed at /oportunidades/tablero): one
 * column per stage, with opportunities as cards that can be dragged
 * between columns (changing their stage, which is recorded in the stage
 * history) and reordered inside a column. Any authenticated user can
 * move cards.
 */
#[Title('Tablero de oportunidades')]
class Board extends Component
{
    #[Url(as: 'buscar', history: true)]
    public string $search = '';

    /**
     * Owner filter: "all" (default) or "mine".
     */
    #[Url(as: 'responsable', history: true)]
    public string $owner = 'all';

    /**
     * Handle a card dropped by wire:sort: put the opportunity in the
     * destination stage at the given zero-based position among the cards
     * currently shown in that column.
     */
    public function moveOpportunity(int $opportunityId, int $position, int $stageId): void
    {
        $opportunity = Opportunity::whereHas('organization')->findOrFail($opportunityId);
        $stage = PipelineStage::findOrFail($stageId);

        DB::transaction(function () use ($opportunity, $stage, $position) {
            $stageChanged = $opportunity->pipeline_stage_id !== $stage->id;

            if ($stageChanged) {
                $opportunity->update(['pipeline_stage_id' => $stage->id]);
            }

            $orderedIds = $this->boardQuery()
                ->where('pipeline_stage_id', $stage->id)
                ->whereKeyNot($opportunity->id)
                ->orderBy('board_position')
                ->orderByDesc('id')
                ->pluck('id')
                ->all();

            array_splice($orderedIds, min($position, count($orderedIds)), 0, [$opportunity->id]);

            foreach ($orderedIds as $index => $id) {
                Opportunity::whereKey($id)->where('board_position', '!=', $index)->update(['board_position' => $index]);
            }

            if ($stageChanged) {
                Flux::toast(variant: 'success', text: __('Movida a :stage.', ['stage' => $stage->name]));
            }
        });
    }

    public function render(): View
    {
        $cards = $this->boardQuery()
            ->with(['organization', 'owner', 'tags'])
            ->orderBy('board_position')
            ->orderByDesc('id')
            ->get()
            ->groupBy('pipeline_stage_id');

        $columns = PipelineStage::orderBy('position')->get()->map(function (PipelineStage $stage) use ($cards) {
            $stageCards = $cards->get($stage->id, collect());

            return [
                'stage' => $stage,
                'cards' => $stageCards,
                'totals' => $stageCards
                    ->whereNotNull('estimated_amount')
                    ->groupBy('currency')
                    ->map(fn ($group) => (float) $group->sum('estimated_amount')),
            ];
        });

        return view('livewire.opportunities.board', ['columns' => $columns]);
    }

    /**
     * Opportunities shown on the board, with the active filters applied.
     *
     * @return Builder<Opportunity>
     */
    private function boardQuery(): Builder
    {
        return Opportunity::query()
            ->whereHas('organization')
            ->when($this->owner === 'mine', fn ($query) => $query->where('user_id', auth()->id()))
            ->when(
                $this->search !== '',
                fn ($query) => $query->where(
                    fn ($query) => $query
                        ->whereLike('title', "%{$this->search}%")
                        ->orWhereHas('organization', fn ($query) => $query->whereLike('name', "%{$this->search}%"))
                )
            );
    }
}
