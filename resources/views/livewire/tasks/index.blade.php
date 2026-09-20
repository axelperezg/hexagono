{{--
    CRM follow-up agenda: tasks across all opportunities. Full-page
    Livewire component, routed at /tareas (routes/web.php).
--}}
<section class="w-full">
    <div class="mb-6">
        <flux:heading size="xl">{{ __('Tareas') }}</flux:heading>
        <flux:text class="mt-1">{{ __('Seguimientos pendientes de todas las oportunidades.') }}</flux:text>
    </div>

    <div class="mb-4 flex flex-col gap-3 sm:flex-row">
        <flux:select wire:model.live="assignee" class="sm:max-w-48" aria-label="{{ __('Responsable') }}">
            <flux:select.option value="mine">{{ __('Mis tareas') }}</flux:select.option>
            <flux:select.option value="all">{{ __('Todos los responsables') }}</flux:select.option>
            @foreach ($this->users as $user)
                <flux:select.option value="{{ $user->id }}">{{ $user->name }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select wire:model.live="status" class="sm:max-w-48" aria-label="{{ __('Estado') }}">
            <flux:select.option value="pending">{{ __('Pendientes') }}</flux:select.option>
            <flux:select.option value="completed">{{ __('Completadas') }}</flux:select.option>
            <flux:select.option value="all">{{ __('Todas') }}</flux:select.option>
        </flux:select>
    </div>

    @if ($tasks->isEmpty())
        <flux:callout icon="clipboard-document-check" heading="{{ __('Sin tareas') }}" text="{{ __('No hay tareas que coincidan con los filtros.') }}" />
    @else
        <flux:table :paginate="$tasks">
            <flux:table.columns>
                <flux:table.column></flux:table.column>
                <flux:table.column>{{ __('Tarea') }}</flux:table.column>
                <flux:table.column>{{ __('Vence') }}</flux:table.column>
                <flux:table.column>{{ __('Responsable') }}</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($tasks as $task)
                    <flux:table.row :key="$task->id">
                        <flux:table.cell class="w-8">
                            <flux:checkbox
                                :checked="$task->isCompleted()"
                                wire:click="toggleTask({{ $task->id }})"
                                aria-label="{{ __('Marcar como completada') }}"
                            />
                        </flux:table.cell>
                        <flux:table.cell>
                            <div @class(['font-medium text-zinc-800 dark:text-white', 'line-through opacity-60' => $task->isCompleted()])>{{ $task->title }}</div>
                            <a href="{{ route('opportunities.show', $task->opportunity) }}" wire:navigate class="text-zinc-500 hover:underline">
                                {{ $task->opportunity->title }} · {{ $task->opportunity->organization->name }}
                            </a>
                        </flux:table.cell>
                        <flux:table.cell class="whitespace-nowrap">
                            {{ $task->due_date->format('d/m/Y') }}
                            @if ($task->isOverdue())
                                <flux:badge size="sm" color="red">{{ __('Vencida') }}</flux:badge>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>{{ $task->assignee?->name }}</flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    @endif
</section>
