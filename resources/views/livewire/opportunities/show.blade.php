{{--
    Opportunity detail page with its tasks, interactions timeline and stage
    history. Full-page Livewire component, routed at
    /oportunidades/{opportunity} (routes/web.php).
--}}
<section class="w-full">
    <div class="mb-2">
        <flux:button size="sm" variant="ghost" icon="arrow-left" :href="route('opportunities.index')" wire:navigate>
            {{ __('Oportunidades') }}
        </flux:button>
    </div>

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="flex items-center gap-4">
            <flux:avatar
                size="lg"
                :src="$opportunity->organization->logoUrl()"
                :name="$opportunity->organization->name"
                alt="{{ __('Logo de :name', ['name' => $opportunity->organization->name]) }}"
            />
            <div>
                <flux:heading size="xl">{{ $opportunity->title }}</flux:heading>
                <flux:text class="mt-1">{{ $opportunity->organization->name }}</flux:text>
            </div>
        </div>

        <flux:select wire:model.live="pipeline_stage_id" class="sm:max-w-48" aria-label="{{ __('Etapa') }}">
            @foreach ($this->stages as $stage)
                <flux:select.option value="{{ $stage->id }}">{{ $stage->name }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

    <dl class="mb-8 grid grid-cols-1 gap-4 text-sm sm:grid-cols-4">
        <div>
            <dt class="text-zinc-500">{{ __('Ejercicio fiscal') }}</dt>
            <dd>{{ $opportunity->fiscal_year }}</dd>
        </div>
        <div>
            <dt class="text-zinc-500">{{ __('Monto estimado') }}</dt>
            <dd>
                @if ($opportunity->estimated_amount !== null)
                    ${{ number_format((float) $opportunity->estimated_amount, 2) }} {{ $opportunity->currency }}
                @else
                    —
                @endif
            </dd>
        </div>
        <div>
            <dt class="text-zinc-500">{{ __('Cierre estimado') }}</dt>
            <dd>{{ $opportunity->expected_close_date?->format('d/m/Y') ?? '—' }}</dd>
        </div>
        <div>
            <dt class="text-zinc-500">{{ __('Responsable') }}</dt>
            <dd>{{ $opportunity->owner?->name ?? '—' }}</dd>
        </div>
        <div>
            <dt class="text-zinc-500">{{ __('Partida') }}</dt>
            <dd>{{ $opportunity->budget_item?->label() ?? '—' }}</dd>
        </div>
        <div>
            <dt class="text-zinc-500">{{ __('Campaña') }}</dt>
            <dd>{{ $opportunity->campaign ?? '—' }}</dd>
        </div>
        <div>
            <dt class="text-zinc-500">{{ __('Versión') }}</dt>
            <dd>{{ $opportunity->version ?? '—' }}</dd>
        </div>
        <div>
            <dt class="text-zinc-500">{{ __('Prioridad') }}</dt>
            <dd>
                @if ($opportunity->priority)
                    <flux:badge size="sm" :color="$opportunity->priority->color()">{{ $opportunity->priority->label() }}</flux:badge>
                @else
                    —
                @endif
            </dd>
        </div>
        @if ($opportunity->notes)
            <div class="sm:col-span-4">
                <dt class="text-zinc-500">{{ __('Notas') }}</dt>
                <dd class="whitespace-pre-line">{{ $opportunity->notes }}</dd>
            </div>
        @endif
    </dl>

    {{-- Follow-up tasks --}}
    <div class="mb-4 flex items-center justify-between">
        <flux:heading size="lg">{{ __('Tareas') }}</flux:heading>

        <flux:button icon="plus" wire:click="$dispatch('create-task')">
            {{ __('Nueva tarea') }}
        </flux:button>
    </div>

    @if ($this->tasks->isEmpty())
        <flux:callout class="mb-8" icon="clipboard-document-check" heading="{{ __('Sin tareas') }}" text="{{ __('Agenda el siguiente seguimiento de este acercamiento.') }}" />
    @else
        <ul class="mb-8 space-y-2">
            @foreach ($this->tasks as $task)
                <li wire:key="task-{{ $task->id }}" class="flex items-start justify-between gap-4 rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                    <div class="flex items-start gap-3">
                        <flux:checkbox
                            :checked="$task->isCompleted()"
                            wire:click="toggleTask({{ $task->id }})"
                            aria-label="{{ __('Marcar como completada') }}"
                        />
                        <div>
                            <div @class(['font-medium text-zinc-800 dark:text-white', 'line-through opacity-60' => $task->isCompleted()])>{{ $task->concept }}</div>
                            <div class="flex flex-wrap items-center gap-2 text-sm text-zinc-500">
                                <span>{{ $task->start_date->format('d/m/Y') }} – {{ $task->end_date->format('d/m/Y') }}</span>
                                @if ($task->isOverdue())
                                    <flux:badge size="sm" color="red">{{ __('Vencida') }}</flux:badge>
                                @endif
                                @if ($task->assignee)
                                    <span>· {{ $task->assignee->name }}</span>
                                @endif
                            </div>
                            @if ($task->notes)
                                <p class="mt-1 whitespace-pre-line text-sm text-zinc-600 dark:text-zinc-300">{{ $task->notes }}</p>
                            @endif
                            @if ($task->actions->isNotEmpty())
                                <ul class="mt-2 space-y-1 text-sm text-zinc-600 dark:text-zinc-300">
                                    @foreach ($task->actions as $action)
                                        <li wire:key="task-{{ $task->id }}-action-{{ $action->id }}">
                                            <span class="text-zinc-500">{{ $action->performed_at->format('d/m/Y') }}</span> · {{ $action->description }}
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>

                    <div class="flex shrink-0 gap-1">
                        <flux:button size="sm" variant="ghost" icon="pencil" wire:click="$dispatch('edit-task', { taskId: {{ $task->id }} })" aria-label="{{ __('Editar') }}" />
                        @if (auth()->user()->isAdmin())
                            <flux:button
                                size="sm"
                                variant="ghost"
                                icon="trash"
                                wire:click="deleteTask({{ $task->id }})"
                                wire:confirm="{{ __('¿Eliminar esta tarea?') }}"
                                aria-label="{{ __('Eliminar') }}"
                            />
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>
    @endif

    {{-- Interactions --}}
    <div class="mb-4 flex items-center justify-between">
        <flux:heading size="lg">{{ __('Interacciones') }}</flux:heading>

        <flux:button variant="primary" icon="plus" wire:click="createInteraction">
            {{ __('Registrar interacción') }}
        </flux:button>
    </div>

    @if ($this->interactions->isEmpty())
        <flux:callout icon="chat-bubble-left-right" heading="{{ __('Sin interacciones') }}" text="{{ __('Registra la primera llamada, reunión o correo de este acercamiento.') }}" />
    @else
        <ul class="space-y-4">
            @foreach ($this->interactions as $interaction)
                <li wire:key="interaction-{{ $interaction->id }}" class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-start gap-3">
                            <flux:icon :name="$interaction->type->icon()" class="mt-0.5 size-5 text-zinc-500" />
                            <div>
                                <div class="font-medium text-zinc-800 dark:text-white">{{ $interaction->subject }}</div>
                                <div class="text-sm text-zinc-500">
                                    {{ $interaction->type->label() }}
                                    · {{ $interaction->occurred_at->format('d/m/Y H:i') }}
                                    @if ($interaction->user)
                                        · {{ $interaction->user->name }}
                                    @endif
                                </div>
                            </div>
                        </div>

                        @if (auth()->user()->isAdmin())
                            <flux:button
                                size="sm"
                                variant="ghost"
                                icon="trash"
                                wire:click="deleteInteraction({{ $interaction->id }})"
                                wire:confirm="{{ __('¿Eliminar esta interacción?') }}"
                                aria-label="{{ __('Eliminar') }}"
                            />
                        @endif
                    </div>

                    @if ($interaction->contacts->isNotEmpty())
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach ($interaction->contacts as $contact)
                                <flux:badge size="sm">{{ $contact->name }}</flux:badge>
                            @endforeach
                        </div>
                    @endif

                    @if ($interaction->notes)
                        <p class="mt-3 whitespace-pre-line text-sm text-zinc-600 dark:text-zinc-300">{{ $interaction->notes }}</p>
                    @endif
                </li>
            @endforeach
        </ul>
    @endif

    {{-- Stage history --}}
    <flux:heading size="lg" class="mb-4 mt-8">{{ __('Historial de etapas') }}</flux:heading>

    <ol class="space-y-2 text-sm">
        @foreach ($this->stageChanges as $change)
            <li wire:key="stage-change-{{ $change->id }}" class="text-zinc-600 dark:text-zinc-300">
                <span class="text-zinc-500">{{ $change->created_at->format('d/m/Y H:i') }}</span>
                ·
                @if ($change->fromStage)
                    {{ $change->fromStage->name }} → <span class="font-medium text-zinc-800 dark:text-white">{{ $change->toStage->name }}</span>
                @else
                    {{ __('Creada en') }} <span class="font-medium text-zinc-800 dark:text-white">{{ $change->toStage->name }}</span>
                @endif
                @if ($change->user)
                    <span class="text-zinc-500">· {{ $change->user->name }}</span>
                @endif
            </li>
        @endforeach
    </ol>

    {{-- Task form, shared with /tareas and opened through the create-task / edit-task events --}}
    <livewire:tasks.form :opportunity-id="$opportunity->id" />

    {{-- Log-interaction form, opened by Show::createInteraction() --}}
    <flux:modal name="interaction-form" class="w-full max-w-lg">
        <form wire:submit="saveInteraction" class="space-y-6">
            <flux:heading size="lg">{{ __('Registrar interacción') }}</flux:heading>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <flux:field>
                    <flux:label>{{ __('Tipo') }}</flux:label>
                    <flux:select wire:model="type">
                        @foreach ($this->interactionTypes as $typeOption)
                            <flux:select.option value="{{ $typeOption->value }}">{{ $typeOption->label() }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="type" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Fecha y hora') }}</flux:label>
                    <flux:input type="datetime-local" wire:model="occurred_at" />
                    <flux:error name="occurred_at" />
                </flux:field>
            </div>

            <flux:field>
                <flux:label>{{ __('Asunto') }}</flux:label>
                <flux:input wire:model="subject" autocomplete="off" />
                <flux:error name="subject" />
            </flux:field>

            @if ($this->contacts->isNotEmpty())
                <flux:checkbox.group wire:model="contactIds" label="{{ __('Contactos involucrados') }}">
                    @foreach ($this->contacts as $contact)
                        <flux:checkbox value="{{ $contact->id }}" label="{{ $contact->name }}" />
                    @endforeach
                </flux:checkbox.group>
                <flux:error name="contactIds.*" />
            @endif

            <flux:field>
                <flux:label>{{ __('Notas') }}</flux:label>
                <flux:textarea wire:model="notes" rows="3" />
                <flux:error name="notes" />
            </flux:field>

            <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                <flux:modal.close>
                    <flux:button variant="filled">{{ __('Cancelar') }}</flux:button>
                </flux:modal.close>

                <flux:button variant="primary" type="submit">{{ __('Registrar') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</section>
