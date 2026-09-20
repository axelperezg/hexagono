{{--
    CRM module to create, edit and delete opportunities. Full-page
    Livewire component, routed at /oportunidades (routes/web.php).
--}}
<section class="w-full">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl">{{ __('Oportunidades') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Acercamientos con potencial de negocio y su etapa en el pipeline.') }}</flux:text>
        </div>

        <div class="flex gap-2">
            <flux:button icon="view-columns" :href="route('opportunities.board')" wire:navigate>
                {{ __('Ver tablero') }}
            </flux:button>

            <flux:button variant="primary" icon="plus" wire:click="createOpportunity">
                {{ __('Nueva oportunidad') }}
            </flux:button>
        </div>
    </div>

    <div class="mb-4 flex flex-col gap-3 sm:flex-row">
        <flux:input
            wire:model.live.debounce.400ms="search"
            icon="magnifying-glass"
            placeholder="{{ __('Buscar por título u organización') }}"
            class="sm:max-w-xs"
        />

        <flux:select wire:model.live="stageFilter" class="sm:max-w-48">
            <flux:select.option value="">{{ __('Todas las etapas') }}</flux:select.option>
            @foreach ($this->stages as $stage)
                <flux:select.option value="{{ $stage->id }}">{{ $stage->name }}</flux:select.option>
            @endforeach
        </flux:select>
        <flux:select wire:model.live="tagFilter" class="sm:max-w-48" aria-label="{{ __('Etiqueta') }}">
            <flux:select.option value="">{{ __('Todas las etiquetas') }}</flux:select.option>
            @foreach ($this->availableTags as $tag)
                <flux:select.option value="{{ $tag->id }}">{{ $tag->name }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

    @if ($opportunities->isEmpty())
        <flux:callout icon="briefcase" heading="{{ __('Sin resultados') }}" text="{{ __('No hay oportunidades que coincidan con los filtros.') }}" />
    @else
        <flux:table :paginate="$opportunities">
            <flux:table.columns>
                <flux:table.column>{{ __('Oportunidad') }}</flux:table.column>
                <flux:table.column>{{ __('Etapa') }}</flux:table.column>
                <flux:table.column>{{ __('Monto estimado') }}</flux:table.column>
                <flux:table.column>{{ __('Responsable') }}</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($opportunities as $opportunity)
                    <flux:table.row :key="$opportunity->id">
                        <flux:table.cell>
                            <a href="{{ route('opportunities.show', $opportunity) }}" wire:navigate class="font-medium text-zinc-800 hover:underline dark:text-white">
                                {{ $opportunity->title }}
                            </a>
                            <div class="text-zinc-500">{{ $opportunity->organization->name }}</div>
                            @if ($opportunity->tags->isNotEmpty())
                                <div class="mt-1 flex flex-wrap gap-1">
                                    @foreach ($opportunity->tags as $tag)
                                        <flux:badge size="sm" color="blue">{{ $tag->name }}</flux:badge>
                                    @endforeach
                                </div>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell class="py-0">
                            <flux:badge size="sm" :color="$opportunity->stage->is_won ? 'green' : ($opportunity->stage->is_lost ? 'red' : 'zinc')">
                                {{ $opportunity->stage->name }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell class="whitespace-nowrap">
                            @if ($opportunity->estimated_amount !== null)
                                ${{ number_format((float) $opportunity->estimated_amount, 2) }} {{ $opportunity->currency }}
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>{{ $opportunity->owner?->name }}</flux:table.cell>
                        <flux:table.cell class="py-0">
                            <div class="flex justify-end gap-1">
                                <flux:button size="sm" variant="ghost" icon="eye" :href="route('opportunities.show', $opportunity)" wire:navigate>
                                    {{ __('Ver') }}
                                </flux:button>
                                <flux:button size="sm" variant="ghost" icon="pencil" wire:click="editOpportunity({{ $opportunity->id }})">
                                    {{ __('Editar') }}
                                </flux:button>
                                @if (auth()->user()->isAdmin())
                                    <flux:button size="sm" variant="ghost" icon="trash" wire:click="confirmDelete({{ $opportunity->id }})">
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

    {{-- Create/edit form, populated by Index::createOpportunity() / Index::editOpportunity() --}}
    <flux:modal name="opportunity-form" class="w-full max-w-lg">
        <form wire:submit="save" class="space-y-6">
            <flux:heading size="lg">
                {{ $editingOpportunityId ? __('Editar oportunidad') : __('Nueva oportunidad') }}
            </flux:heading>

            <flux:field>
                <flux:label>{{ __('Título') }}</flux:label>
                <flux:input wire:model="title" autocomplete="off" />
                <flux:error name="title" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Organización') }}</flux:label>
                <flux:select wire:model="organization_id" placeholder="{{ __('Selecciona una organización') }}">
                    @foreach ($this->organizations as $organization)
                        <flux:select.option value="{{ $organization->id }}">{{ $organization->name }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:error name="organization_id" />
            </flux:field>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <flux:field>
                    <flux:label>{{ __('Etapa') }}</flux:label>
                    <flux:select wire:model="pipeline_stage_id">
                        @foreach ($this->stages as $stage)
                            <flux:select.option value="{{ $stage->id }}">{{ $stage->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="pipeline_stage_id" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Responsable') }}</flux:label>
                    <flux:select wire:model="user_id">
                        @foreach ($this->users as $user)
                            <flux:select.option value="{{ $user->id }}">{{ $user->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="user_id" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Monto estimado') }}</flux:label>
                    <flux:input type="number" step="0.01" min="0" wire:model="estimated_amount" />
                    <flux:error name="estimated_amount" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Moneda') }}</flux:label>
                    <flux:select wire:model="currency">
                        @foreach (\App\Livewire\Opportunities\Index::CURRENCIES as $currencyOption)
                            <flux:select.option value="{{ $currencyOption }}">{{ $currencyOption }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="currency" />
                </flux:field>
            </div>

            <flux:field>
                <flux:label>{{ __('Fecha estimada de cierre') }}</flux:label>
                <flux:input type="date" wire:model="expected_close_date" />
                <flux:error name="expected_close_date" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Notas') }}</flux:label>
                <flux:textarea wire:model="notes" rows="3" />
                <flux:error name="notes" />
            </flux:field>

            @if ($this->availableTags->isNotEmpty())
                <flux:checkbox.group wire:model="tagIds" label="{{ __('Etiquetas') }}">
                    @foreach ($this->availableTags as $tag)
                        <flux:checkbox value="{{ $tag->id }}" label="{{ $tag->name }}" />
                    @endforeach
                </flux:checkbox.group>
                <flux:error name="tagIds.*" />
            @endif

            <flux:field>
                <flux:label>{{ __('Nuevas etiquetas') }}</flux:label>
                <flux:input wire:model="newTags" placeholder="{{ __('Sepáralas con comas') }}" />
                <flux:error name="newTags" />
            </flux:field>

            <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                <flux:modal.close>
                    <flux:button variant="filled">{{ __('Cancelar') }}</flux:button>
                </flux:modal.close>

                <flux:button variant="primary" type="submit">
                    {{ $editingOpportunityId ? __('Guardar cambios') : __('Crear oportunidad') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Delete confirmation, populated by Index::confirmDelete() --}}
    <flux:modal name="confirm-opportunity-delete" class="w-full max-w-lg">
        @if ($this->deletingOpportunity)
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ __('¿Eliminar :title?', ['title' => $this->deletingOpportunity->title]) }}</flux:heading>
                    <flux:subheading>
                        {{ __('La oportunidad dejará de mostrarse en el CRM. Sus interacciones se conservan en la base de datos.') }}
                    </flux:subheading>
                </div>

                <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                    <flux:modal.close>
                        <flux:button variant="filled">{{ __('Cancelar') }}</flux:button>
                    </flux:modal.close>

                    <flux:button variant="danger" wire:click="delete">
                        {{ __('Eliminar oportunidad') }}
                    </flux:button>
                </div>
            </div>
        @endif
    </flux:modal>
</section>
