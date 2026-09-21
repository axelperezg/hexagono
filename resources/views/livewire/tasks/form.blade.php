{{--
    Create/edit modal for tasks, opened by Form::createTask() / Form::editTask()
    (via the "create-task" / "edit-task" events). Shared by /tareas and the
    opportunity page.
--}}
<div>
    <flux:modal name="task-form" class="w-full max-w-2xl">
        <form wire:submit="save" class="space-y-6">
            <flux:heading size="lg">
                {{ $editingTaskId ? __('Editar tarea') : __('Nueva tarea') }}
            </flux:heading>

            <flux:field>
                <flux:label>{{ __('Concepto') }}</flux:label>
                <flux:input wire:model="concept" autocomplete="off" />
                <flux:error name="concept" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Responsable') }}</flux:label>
                <flux:select wire:model="user_id" placeholder="{{ __('Sin responsable') }}">
                    @foreach ($this->users as $user)
                        <flux:select.option value="{{ $user->id }}">{{ $user->name }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:error name="user_id" />
            </flux:field>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <flux:field>
                    <flux:label>{{ __('Fecha de inicio') }}</flux:label>
                    <flux:input type="date" wire:model="start_date" />
                    <flux:error name="start_date" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Fecha de término') }}</flux:label>
                    <flux:input type="date" wire:model="end_date" />
                    <flux:error name="end_date" />
                </flux:field>
            </div>

            <div class="space-y-3">
                <flux:field variant="inline">
                    <flux:checkbox wire:model.live="hasOpportunity" />
                    <flux:label>{{ __('Relacionada con una oportunidad') }}</flux:label>
                </flux:field>

                @if ($hasOpportunity)
                    <flux:field>
                        <flux:select wire:model="opportunity_id" placeholder="{{ __('Selecciona una oportunidad') }}" aria-label="{{ __('Oportunidad') }}">
                            @foreach ($this->opportunities as $opportunity)
                                <flux:select.option value="{{ $opportunity->id }}">{{ $opportunity->title }} · {{ $opportunity->organization->name }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="opportunity_id" />
                    </flux:field>
                @endif
            </div>

            <flux:field>
                <flux:label>{{ __('Notas') }}</flux:label>
                <flux:textarea wire:model="notes" rows="3" />
                <flux:error name="notes" />
            </flux:field>

            <div class="space-y-2">
                <flux:label>{{ __('Acciones llevadas a cabo') }}</flux:label>

                @foreach ($taskActions as $index => $action)
                    <div wire:key="task-action-{{ $index }}" class="space-y-1">
                        <div class="flex items-start gap-2">
                            <flux:input wire:model="taskActions.{{ $index }}.description" autocomplete="off" placeholder="{{ __('Acción') }}" aria-label="{{ __('Acción llevada a cabo') }}" />

                            <flux:input type="date" wire:model="taskActions.{{ $index }}.performed_at" class="max-w-40" aria-label="{{ __('Fecha de la acción') }}" />

                            <flux:button variant="ghost" icon="trash" wire:click="removeAction({{ $index }})" aria-label="{{ __('Quitar acción') }}" />
                        </div>
                        <flux:error name="taskActions.{{ $index }}.description" />
                        <flux:error name="taskActions.{{ $index }}.performed_at" />
                    </div>
                @endforeach

                <flux:button size="sm" variant="subtle" icon="plus" wire:click="addAction">
                    {{ __('Agregar acción') }}
                </flux:button>
            </div>

            <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                <flux:modal.close>
                    <flux:button variant="filled">{{ __('Cancelar') }}</flux:button>
                </flux:modal.close>

                <flux:button variant="primary" type="submit">
                    {{ $editingTaskId ? __('Guardar cambios') : __('Crear tarea') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
