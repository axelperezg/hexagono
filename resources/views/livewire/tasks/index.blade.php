{{--
    CRM task module: create, edit and delete tasks, related to an
    opportunity or not. Full-page Livewire component, routed at /tareas
    (routes/web.php).
--}}
<section class="w-full">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl">{{ __('Tareas') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Seguimientos y trabajo del equipo, con o sin oportunidad asociada.') }}</flux:text>
        </div>

        <flux:button variant="primary" icon="plus" wire:click="$dispatch('create-task')">
            {{ __('Nueva tarea') }}
        </flux:button>
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
                <flux:table.column>{{ __('Inicio') }}</flux:table.column>
                <flux:table.column>{{ __('Término') }}</flux:table.column>
                <flux:table.column>{{ __('Responsable') }}</flux:table.column>
                <flux:table.column></flux:table.column>
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
                            <div @class(['font-medium text-zinc-800 dark:text-white', 'line-through opacity-60' => $task->isCompleted()])>{{ $task->concept }}</div>
                            @if ($task->opportunity)
                                <a href="{{ route('opportunities.show', $task->opportunity) }}" wire:navigate class="text-zinc-500 hover:underline">
                                    {{ $task->opportunity->title }} · {{ $task->opportunity->organization->name }}
                                </a>
                            @else
                                <div class="text-zinc-500">{{ __('Sin oportunidad') }}</div>
                            @endif
                            @if ($task->actions->isNotEmpty())
                                <div class="text-xs text-zinc-500">{{ trans_choice(':count acción|:count acciones', $task->actions->count()) }}</div>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell class="whitespace-nowrap">{{ $task->start_date->format('d/m/Y') }}</flux:table.cell>
                        <flux:table.cell class="whitespace-nowrap">
                            {{ $task->end_date->format('d/m/Y') }}
                            @if ($task->isOverdue())
                                <flux:badge size="sm" color="red">{{ __('Vencida') }}</flux:badge>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>{{ $task->assignee?->name }}</flux:table.cell>
                        <flux:table.cell class="py-0">
                            <div class="flex justify-end gap-1">
                                <flux:button size="sm" variant="ghost" icon="pencil" wire:click="$dispatch('edit-task', { taskId: {{ $task->id }} })">
                                    {{ __('Editar') }}
                                </flux:button>
                                @if (auth()->user()->isAdmin())
                                    <flux:button
                                        size="sm"
                                        variant="ghost"
                                        icon="trash"
                                        wire:click="deleteTask({{ $task->id }})"
                                        wire:confirm="{{ __('¿Eliminar esta tarea?') }}"
                                    >
                                        {{ __('Eliminar') }}
                                    </flux:button>
                                @endif
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    @endif

    <livewire:tasks.form />
</section>
