<?php

namespace App\Livewire;

use App\Models\Opportunity;
use App\Models\PipelineStage;
use App\Models\Task;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * CRM dashboard (routed at /dashboard), filtered by fiscal year (the
 * current year by default): opportunity totals, opportunities by month of
 * their expected close date, and pending tasks by month of their end date.
 */
#[Title('Dashboard')]
class Dashboard extends Component
{
    #[Url(as: 'ejercicio', history: true)]
    public string $fiscalYearFilter = '';

    /**
     * Default the fiscal year filter to the current year.
     */
    public function mount(): void
    {
        if ($this->fiscalYearFilter === '') {
            $this->fiscalYearFilter = (string) $this->currentFiscalYear();
        }
    }

    /**
     * The selected fiscal year; anything outside the supported range (for
     * instance a hand-edited URL) falls back to the current year.
     */
    #[Computed]
    public function fiscalYear(): int
    {
        $year = (int) $this->fiscalYearFilter;

        return in_array($year, Opportunity::fiscalYears(), true) ? $year : $this->currentFiscalYear();
    }

    /**
     * Opportunities of the fiscal year: how many there are, how many were
     * won, and how many fall in each month of their expected close date.
     * Those without a close date in the fiscal year are counted apart.
     * Amounts only add up opportunities expressed in MXN; the others are
     * counted in otherCurrency.
     *
     * @return array{total: int, won: int, byMonth: array<int, int>, outsideYear: int, amount: float, wonAmount: float, amountByMonth: array<int, float>, otherCurrency: int}
     */
    #[Computed]
    public function opportunityStats(): array
    {
        $year = $this->fiscalYear();

        $opportunities = Opportunity::query()
            ->whereHas('organization')
            ->where('fiscal_year', $year)
            ->get(['id', 'pipeline_stage_id', 'expected_close_date', 'estimated_amount', 'currency']);

        $wonStageIds = PipelineStage::where('is_won', true)->pluck('id')->all();

        $byMonth = array_fill(1, 12, 0);
        $amountByMonth = array_fill(1, 12, 0.0);
        $outsideYear = 0;
        $amount = 0.0;
        $wonAmount = 0.0;
        $otherCurrency = 0;

        foreach ($opportunities as $opportunity) {
            $inYear = $opportunity->expected_close_date?->year === $year;
            $mxnAmount = $opportunity->currency === 'MXN' ? (float) $opportunity->estimated_amount : 0.0;

            if ($opportunity->currency !== 'MXN') {
                $otherCurrency++;
            }

            $amount += $mxnAmount;

            if (in_array($opportunity->pipeline_stage_id, $wonStageIds)) {
                $wonAmount += $mxnAmount;
            }

            if ($inYear) {
                $byMonth[$opportunity->expected_close_date->month]++;
                $amountByMonth[$opportunity->expected_close_date->month] += $mxnAmount;
            } else {
                $outsideYear++;
            }
        }

        return [
            'total' => $opportunities->count(),
            'won' => $opportunities->whereIn('pipeline_stage_id', $wonStageIds)->count(),
            'byMonth' => $byMonth,
            'outsideYear' => $outsideYear,
            'amount' => $amount,
            'wonAmount' => $wonAmount,
            'amountByMonth' => $amountByMonth,
            'otherCurrency' => $otherCurrency,
        ];
    }

    /**
     * Pending tasks ending in the fiscal year, by month of their end date,
     * and how many of them are already overdue.
     *
     * @return array{total: int, overdue: int, byMonth: array<int, int>}
     */
    #[Computed]
    public function taskStats(): array
    {
        $year = $this->fiscalYear();

        $tasks = Task::query()
            ->pending()
            ->whereBetween('end_date', ["{$year}-01-01", "{$year}-12-31"])
            ->where(
                fn ($query) => $query
                    ->whereNull('opportunity_id')
                    ->orWhereHas('opportunity.organization')
            )
            ->get(['id', 'end_date']);

        $byMonth = array_fill(1, 12, 0);

        foreach ($tasks as $task) {
            $byMonth[$task->end_date->month]++;
        }

        return [
            'total' => $tasks->count(),
            'overdue' => $tasks->filter(fn (Task $task) => $task->end_date->isBefore(today()))->count(),
            'byMonth' => $byMonth,
        ];
    }

    /**
     * Turn a month => count map into the rows the column chart expects.
     *
     * @param  array<int, int|float>  $byMonth
     * @return array<int, array{label: string, name: string, value: int|float}>
     */
    public function chartData(array $byMonth): array
    {
        return collect($byMonth)
            ->map(function (int|float $count, int $month) {
                $date = Carbon::create(2000, $month, 1);
                $date->locale('es');

                return [
                    'label' => ucfirst($date->translatedFormat('M')),
                    'name' => ucfirst($date->translatedFormat('F')),
                    'value' => $count,
                ];
            })
            ->values()
            ->all();
    }

    public function render(): View
    {
        return view('livewire.dashboard');
    }

    private function currentFiscalYear(): int
    {
        return min(max(now()->year, Opportunity::FISCAL_YEAR_MIN), Opportunity::FISCAL_YEAR_MAX);
    }
}
