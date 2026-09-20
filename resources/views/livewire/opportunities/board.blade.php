{{--
    Kanban view of the pipeline: one column per stage, cards draggable
    between columns with wire:sort. Full-page Livewire component, routed
    at /oportunidades/tablero (routes/web.php).
--}}
<section class="w-full">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl">{{ __('Tablero de oportunidades') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Arrastra las tarjetas entre columnas para cambiar de etapa.') }}</flux:text>
        </div>

        <flux:button icon="list-bullet" :href="route('opportunities.index')" wire:navigate>
            {{ __('Ver lista') }}
        </flux:button>
    </div>

    <div class="mb-4 flex flex-col gap-3 sm:flex-row">
        <flux:input
            wire:model.live.debounce.400ms="search"
            icon="magnifying-glass"
            placeholder="{{ __('Buscar por título u organización') }}"
            class="sm:max-w-xs"
        />

        <flux:select wire:model.live="owner" class="sm:max-w-48" aria-label="{{ __('Responsable') }}">
            <flux:select.option value="all">{{ __('Todas las oportunidades') }}</flux:select.option>
            <flux:select.option value="mine">{{ __('Mis oportunidades') }}</flux:select.option>
        </flux:select>
    </div>

    <div class="-mx-2 flex items-start gap-4 overflow-x-auto px-2 pb-4">
        @foreach ($columns as $column)
            <div wire:key="stage-{{ $column['stage']->id }}" class="w-72 shrink-0 rounded-lg bg-zinc-100 p-3 dark:bg-zinc-900">
                <div class="mb-3 px-1">
                    <div class="flex items-center justify-between gap-2">
                        <span class="flex items-center gap-2 font-medium text-zinc-800 dark:text-white">
                            <span @class([
                                'size-2 rounded-full',
                                'bg-green-500' => $column['stage']->is_won,
                                'bg-red-500' => $column['stage']->is_lost,
                                'bg-zinc-400' => ! $column['stage']->is_won && ! $column['stage']->is_lost,
                            ])></span>
                            {{ $column['stage']->name }}
                        </span>
                        <flux:badge size="sm">{{ $column['cards']->count() }}</flux:badge>
                    </div>
                    @foreach ($column['totals'] as $currency => $total)
                        <div class="text-xs text-zinc-500">${{ number_format($total, 2) }} {{ $currency }}</div>
                    @endforeach
                </div>

                <ul
                    wire:sort="moveOpportunity"
                    wire:sort:group="opportunities"
                    wire:sort:group-id="{{ $column['stage']->id }}"
                    class="min-h-16 space-y-2"
                >
                    @foreach ($column['cards'] as $opportunity)
                        <li
                            wire:key="opportunity-{{ $opportunity->id }}"
                            wire:sort:item="{{ $opportunity->id }}"
                            class="cursor-grab rounded-md border border-zinc-200 bg-white p-3 shadow-xs dark:border-zinc-700 dark:bg-zinc-800"
                        >
                            <a
                                href="{{ route('opportunities.show', $opportunity) }}"
                                wire:navigate
                                wire:sort:ignore
                                class="font-medium text-zinc-800 hover:underline dark:text-white"
                            >
                                {{ $opportunity->title }}
                            </a>
                            <div class="mt-0.5 text-sm text-zinc-500">{{ $opportunity->organization->name }}</div>

                            @if ($opportunity->estimated_amount !== null)
                                <div class="mt-2 text-sm text-zinc-700 dark:text-zinc-300">
                                    ${{ number_format((float) $opportunity->estimated_amount, 2) }} {{ $opportunity->currency }}
                                </div>
                            @endif

                            @if ($opportunity->tags->isNotEmpty())
                                <div class="mt-2 flex flex-wrap gap-1">
                                    @foreach ($opportunity->tags as $tag)
                                        <flux:badge size="sm" color="blue">{{ $tag->name }}</flux:badge>
                                    @endforeach
                                </div>
                            @endif

                            @if ($opportunity->owner)
                                <div class="mt-2 text-xs text-zinc-500">{{ $opportunity->owner->name }}</div>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </div>
</section>
