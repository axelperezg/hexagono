{{--
    CRM module to create, edit and delete organizations. Full-page
    Livewire component, routed at /organizaciones (routes/web.php).
--}}
<section class="w-full">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl">{{ __('Organizaciones') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Empresas e instituciones con las que hay un acercamiento.') }}</flux:text>
        </div>

        <flux:button variant="primary" icon="plus" wire:click="createOrganization">
            {{ __('Nueva organización') }}
        </flux:button>
    </div>

    <div class="mb-4">
        <flux:input
            wire:model.live.debounce.400ms="search"
            icon="magnifying-glass"
            placeholder="{{ __('Buscar por nombre o sector') }}"
            class="sm:max-w-xs"
        />
    </div>

    @if ($organizations->isEmpty())
        <flux:callout icon="building-office" heading="{{ __('Sin resultados') }}" text="{{ __('No hay organizaciones que coincidan con esta búsqueda.') }}" />
    @else
        <flux:table :paginate="$organizations">
            <flux:table.columns>
                <flux:table.column>{{ __('Organización') }}</flux:table.column>
                <flux:table.column>{{ __('Contactos') }}</flux:table.column>
                <flux:table.column>{{ __('Oportunidades') }}</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($organizations as $organization)
                    <flux:table.row :key="$organization->id">
                        <flux:table.cell>
                            <div class="font-medium text-zinc-800 dark:text-white">{{ $organization->name }}</div>
                            <div class="text-zinc-500">{{ $organization->sector }}</div>
                        </flux:table.cell>
                        <flux:table.cell>{{ $organization->contacts_count }}</flux:table.cell>
                        <flux:table.cell>{{ $organization->opportunities_count }}</flux:table.cell>
                        <flux:table.cell class="py-0">
                            <div class="flex justify-end gap-1">
                                <flux:button size="sm" variant="ghost" icon="pencil" wire:click="editOrganization({{ $organization->id }})">
                                    {{ __('Editar') }}
                                </flux:button>
                                @if (auth()->user()->isAdmin())
                                    <flux:button size="sm" variant="ghost" icon="trash" wire:click="confirmDelete({{ $organization->id }})">
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

    {{-- Create/edit form, populated by Index::createOrganization() / Index::editOrganization() --}}
    <flux:modal name="organization-form" class="w-full max-w-lg">
        <form wire:submit="save" class="space-y-6">
            <flux:heading size="lg">
                {{ $editingOrganizationId ? __('Editar organización') : __('Nueva organización') }}
            </flux:heading>

            <flux:field>
                <flux:label>{{ __('Nombre') }}</flux:label>
                <flux:input wire:model="name" />
                <flux:error name="name" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Sector') }}</flux:label>
                <flux:input wire:model="sector" />
                <flux:error name="sector" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Sitio web') }}</flux:label>
                <flux:input type="url" wire:model="website" placeholder="https://" />
                <flux:error name="website" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Teléfono') }}</flux:label>
                <flux:input wire:model="phone" />
                <flux:error name="phone" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Notas') }}</flux:label>
                <flux:textarea wire:model="notes" rows="3" />
                <flux:error name="notes" />
            </flux:field>

            <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                <flux:modal.close>
                    <flux:button variant="filled">{{ __('Cancelar') }}</flux:button>
                </flux:modal.close>

                <flux:button variant="primary" type="submit">
                    {{ $editingOrganizationId ? __('Guardar cambios') : __('Crear organización') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Delete confirmation, populated by Index::confirmDelete() --}}
    <flux:modal name="confirm-organization-delete" class="w-full max-w-lg">
        @if ($this->deletingOrganization)
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ __('¿Eliminar :name?', ['name' => $this->deletingOrganization->name]) }}</flux:heading>
                    <flux:subheading>
                        {{ __('La organización dejará de mostrarse en el CRM. Sus contactos y oportunidades se conservan en la base de datos.') }}
                    </flux:subheading>
                </div>

                <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                    <flux:modal.close>
                        <flux:button variant="filled">{{ __('Cancelar') }}</flux:button>
                    </flux:modal.close>

                    <flux:button variant="danger" wire:click="delete">
                        {{ __('Eliminar organización') }}
                    </flux:button>
                </div>
            </div>
        @endif
    </flux:modal>
</section>
