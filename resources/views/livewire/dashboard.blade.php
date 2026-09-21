{{--
    CRM dashboard filtered by fiscal year. Full-page Livewire component,
    routed at /dashboard (routes/web.php).
--}}
<section class="w-full">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl">{{ __('Dashboard') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Resumen del ejercicio fiscal :year.', ['year' => $this->fiscalYear]) }}</flux:text>
        </div>

        <flux:select wire:model.live="fiscalYearFilter" class="sm:max-w-40" aria-label="{{ __('Ejercicio fiscal') }}">
            @foreach (\App\Models\Opportunity::fiscalYears() as $year)
                <flux:select.option value="{{ $year }}">{{ __('Ejercicio :year', ['year' => $year]) }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
            <div class="text-sm text-zinc-500">{{ __('Oportunidades') }}</div>
            <div class="mt-1 text-4xl font-semibold text-zinc-800 dark:text-white">{{ number_format($this->opportunityStats['total']) }}</div>
        </div>

        <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
            <div class="text-sm text-zinc-500">{{ __('Oportunidades ganadas') }}</div>
            <div class="mt-1 text-4xl font-semibold text-zinc-800 dark:text-white">{{ number_format($this->opportunityStats['won']) }}</div>
            @if ($this->opportunityStats['total'] > 0)
                <div class="mt-1 text-sm text-zinc-500">
                    {{ __(':percent% del total', ['percent' => round($this->opportunityStats['won'] / $this->opportunityStats['total'] * 100)]) }}
                </div>
            @endif
        </div>

        <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
            <div class="text-sm text-zinc-500">{{ __('Monto estimado') }}</div>
            <div class="mt-1 text-4xl font-semibold text-zinc-800 dark:text-white">${{ number_format($this->opportunityStats['amount']) }}</div>
            <div class="mt-1 text-sm text-zinc-500">MXN</div>
        </div>

        <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
            <div class="text-sm text-zinc-500">{{ __('Monto ganado') }}</div>
            <div class="mt-1 text-4xl font-semibold text-zinc-800 dark:text-white">${{ number_format($this->opportunityStats['wonAmount']) }}</div>
            <div class="mt-1 text-sm text-zinc-500">MXN</div>
        </div>
    </div>

    @if ($this->opportunityStats['otherCurrency'] > 0)
        <flux:text class="-mt-2 mb-6 text-sm">
            {{ trans_choice(':count oportunidad en otra moneda no se incluye en los montos.|:count oportunidades en otra moneda no se incluyen en los montos.', $this->opportunityStats['otherCurrency']) }}
        </flux:text>
    @endif

    <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
        <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
            <flux:heading size="lg">{{ __('Oportunidades por mes') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Según su fecha límite de cierre en :year.', ['year' => $this->fiscalYear]) }}</flux:text>

            <x-column-chart
                :data="$this->chartData($this->opportunityStats['byMonth'])"
                :label="__('Oportunidades por mes de fecha límite')"
                :singular="__('oportunidad')"
                :plural="__('oportunidades')"
            />

            @if ($this->opportunityStats['outsideYear'] > 0)
                <flux:text class="mt-3 text-sm">
                    {{ trans_choice(':count oportunidad del ejercicio no tiene fecha límite en :year.|:count oportunidades del ejercicio no tienen fecha límite en :year.', $this->opportunityStats['outsideYear'], ['year' => $this->fiscalYear]) }}
                </flux:text>
            @endif
        </div>

        <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
            <flux:heading size="lg">{{ __('Monto estimado por mes') }}</flux:heading>
            <flux:text class="mt-1">{{ __('En MXN, según la fecha límite de cierre en :year.', ['year' => $this->fiscalYear]) }}</flux:text>

            <x-column-chart
                format="currency"
                :data="$this->chartData($this->opportunityStats['amountByMonth'])"
                :label="__('Monto estimado en MXN por mes de fecha límite')"
            />
        </div>

        <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
            <flux:heading size="lg">{{ __('Embudo por etapa') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Oportunidades del ejercicio :year en cada etapa del pipeline.', ['year' => $this->fiscalYear]) }}</flux:text>

            <x-funnel-chart :data="$this->funnel($this->opportunityStats['byStage'])" :label="__('Oportunidades por etapa del pipeline')" />
        </div>

        <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
            <flux:heading size="lg">{{ __('Tareas pendientes') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Por mes de su fecha de término en :year.', ['year' => $this->fiscalYear]) }}</flux:text>

            <x-column-chart
                color="orange"
                :data="$this->chartData($this->taskStats['byMonth'])"
                :label="__('Tareas pendientes por mes de término')"
                :singular="__('tarea')"
                :plural="__('tareas')"
            />

            <flux:text class="mt-3 flex items-center gap-2 text-sm">
                {{ trans_choice(':count tarea pendiente|:count tareas pendientes', $this->taskStats['total']) }}
                @if ($this->taskStats['overdue'] > 0)
                    <flux:badge size="sm" color="red" icon="exclamation-triangle">
                        {{ trans_choice(':count vencida|:count vencidas', $this->taskStats['overdue']) }}
                    </flux:badge>
                @endif
            </flux:text>
        </div>
    </div>
</section>
