{{--
    CRM catalog to create, edit and delete sectors. Full-page Livewire
    component, routed at /sectores (routes/web.php).
--}}
<section class="w-full">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl">{{ __('Sectores') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Catálogo de sectores al que pertenecen las organizaciones.') }}</flux:text>
        </div>

        <flux:button variant="primary" icon="plus" wire:click="createSector">
            {{ __('Nuevo sector') }}
        </flux:button>
    </div>

    <div class="mb-4">
        <flux:input
            wire:model.live.debounce.400ms="search"
            icon="magnifying-glass"
            placeholder="{{ __('Buscar por nombre') }}"
            class="sm:max-w-xs"
        />
    </div>

    @if ($sectors->isEmpty())
        <flux:callout icon="tag" heading="{{ __('Sin resultados') }}" text="{{ __('No hay sectores que coincidan con esta búsqueda.') }}" />
    @else
        <flux:table :paginate="$sectors">
            <flux:table.columns>
                <flux:table.column>{{ __('Sector') }}</flux:table.column>
                <flux:table.column>{{ __('Organizaciones') }}</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($sectors as $sector)
                    <flux:table.row :key="$sector->id">
                        <flux:table.cell class="font-medium text-zinc-800 dark:text-white">{{ $sector->name }}</flux:table.cell>
                        <flux:table.cell>{{ $sector->organizations_count }}</flux:table.cell>
                        <flux:table.cell class="py-0">
                            <div class="flex justify-end gap-1">
                                <flux:button size="sm" variant="ghost" icon="pencil" wire:click="editSector({{ $sector->id }})">
                                    {{ __('Editar') }}
                                </flux:button>
                                @if (auth()->user()->isAdmin())
                                    <flux:button size="sm" variant="ghost" icon="trash" wire:click="confirmDelete({{ $sector->id }})">
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

    {{-- Create/edit form, populated by Index::createSector() / Index::editSector() --}}
    <flux:modal name="sector-form" class="w-full max-w-lg">
        <form wire:submit="save" class="space-y-6">
            <flux:heading size="lg">
                {{ $editingSectorId ? __('Editar sector') : __('Nuevo sector') }}
            </flux:heading>

            <flux:field>
                <flux:label>{{ __('Nombre') }}</flux:label>
                <flux:input wire:model="name" />
                <flux:error name="name" />
            </flux:field>

            <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                <flux:modal.close>
                    <flux:button variant="filled">{{ __('Cancelar') }}</flux:button>
                </flux:modal.close>

                <flux:button variant="primary" type="submit">
                    {{ $editingSectorId ? __('Guardar cambios') : __('Crear sector') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Delete confirmation, populated by Index::confirmDelete() --}}
    <flux:modal name="confirm-sector-delete" class="w-full max-w-lg">
        @if ($this->deletingSector)
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ __('¿Eliminar :name?', ['name' => $this->deletingSector->name]) }}</flux:heading>
                    <flux:subheading>
                        {{ __('Solo se puede eliminar un sector que ninguna organización tenga asignado.') }}
                    </flux:subheading>
                </div>

                <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                    <flux:modal.close>
                        <flux:button variant="filled">{{ __('Cancelar') }}</flux:button>
                    </flux:modal.close>

                    <flux:button variant="danger" wire:click="delete">
                        {{ __('Eliminar sector') }}
                    </flux:button>
                </div>
            </div>
        @endif
    </flux:modal>
</section>
