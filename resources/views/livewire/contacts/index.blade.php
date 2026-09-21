{{--
    CRM module to create, edit and delete contacts. Full-page Livewire
    component, routed at /contactos (routes/web.php).
--}}
<section class="w-full">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl">{{ __('Contactos') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Personas dentro de cada organización.') }}</flux:text>
        </div>

        <flux:button variant="primary" icon="plus" wire:click="createContact">
            {{ __('Nuevo contacto') }}
        </flux:button>
    </div>

    <div class="mb-4">
        <flux:input
            wire:model.live.debounce.400ms="search"
            icon="magnifying-glass"
            placeholder="{{ __('Buscar por nombre, correo, cargo u organización') }}"
            class="sm:max-w-sm"
        />
    </div>

    @if ($contacts->isEmpty())
        <flux:callout icon="user-circle" heading="{{ __('Sin resultados') }}" text="{{ __('No hay contactos que coincidan con esta búsqueda.') }}" />
    @else
        <flux:table :paginate="$contacts">
            <flux:table.columns>
                <flux:table.column>{{ __('Contacto') }}</flux:table.column>
                <flux:table.column>{{ __('Organización') }}</flux:table.column>
                <flux:table.column>{{ __('Teléfonos') }}</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($contacts as $contact)
                    <flux:table.row :key="$contact->id">
                        <flux:table.cell>
                            <div class="flex items-center gap-2 font-medium text-zinc-800 dark:text-white">
                                {{ $contact->name }}
                                @if ($contact->is_primary)
                                    <flux:badge size="sm" color="blue">{{ __('Principal') }}</flux:badge>
                                @endif
                            </div>
                            <div class="text-zinc-500">{{ $contact->position }}</div>
                            @if ($contact->email)
                                <a href="mailto:{{ $contact->email }}" class="text-blue-600 dark:text-blue-400">{{ $contact->email }}</a>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            {{ $contact->organization->name }}
                            @if ($contact->address)
                                <div class="text-zinc-500">{{ $contact->address }}</div>
                            @endif
                            @if ($contact->maps_url)
                                <a href="{{ $contact->maps_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 text-blue-600 dark:text-blue-400">
                                    <flux:icon name="map-pin" variant="micro" />
                                    {{ __('Ver en mapa') }}
                                </a>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell class="whitespace-nowrap">
                            @foreach ($contact->phones as $phone)
                                <div>
                                    <span class="text-zinc-500">{{ $phone->type->label() }}:</span>
                                    {{ $phone->number }}
                                </div>
                            @endforeach
                        </flux:table.cell>
                        <flux:table.cell class="py-0">
                            <div class="flex justify-end gap-1">
                                <flux:button size="sm" variant="ghost" icon="pencil" wire:click="editContact({{ $contact->id }})">
                                    {{ __('Editar') }}
                                </flux:button>
                                @if (auth()->user()->isAdmin())
                                    <flux:button size="sm" variant="ghost" icon="trash" wire:click="confirmDelete({{ $contact->id }})">
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

    {{-- Create/edit form, populated by Index::createContact() / Index::editContact() --}}
    <flux:modal name="contact-form" class="w-full max-w-lg">
        <form wire:submit="save" class="space-y-6">
            <flux:heading size="lg">
                {{ $editingContactId ? __('Editar contacto') : __('Nuevo contacto') }}
            </flux:heading>

            <flux:field>
                <flux:label>{{ __('Organización') }}</flux:label>
                <flux:select wire:model="organization_id" placeholder="{{ __('Selecciona una organización') }}">
                    @foreach ($this->organizations as $organization)
                        <flux:select.option value="{{ $organization->id }}">{{ $organization->name }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:error name="organization_id" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Nombre') }}</flux:label>
                <flux:input wire:model="name" autocomplete="off" />
                <flux:error name="name" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Cargo') }}</flux:label>
                <flux:input wire:model="position" autocomplete="off" />
                <flux:error name="position" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Correo electrónico') }}</flux:label>
                <flux:input type="email" wire:model="email" autocomplete="off" />
                <flux:error name="email" />
            </flux:field>

            <div class="space-y-2">
                <flux:label>{{ __('Teléfonos') }}</flux:label>

                @foreach ($phones as $index => $phone)
                    <div wire:key="phone-{{ $index }}" class="space-y-1">
                        <div class="flex items-start gap-2">
                            <flux:select wire:model="phones.{{ $index }}.type" class="max-w-36" aria-label="{{ __('Tipo de teléfono') }}">
                                @foreach (\App\Enums\PhoneType::cases() as $type)
                                    <flux:select.option value="{{ $type->value }}">{{ $type->label() }}</flux:select.option>
                                @endforeach
                            </flux:select>

                            <flux:input wire:model="phones.{{ $index }}.number" autocomplete="off" placeholder="{{ __('Número') }}" aria-label="{{ __('Número de teléfono') }}" />

                            <flux:button variant="ghost" icon="trash" wire:click="removePhone({{ $index }})" aria-label="{{ __('Quitar teléfono') }}" />
                        </div>
                        <flux:error name="phones.{{ $index }}.type" />
                        <flux:error name="phones.{{ $index }}.number" />
                    </div>
                @endforeach

                <flux:button size="sm" variant="subtle" icon="plus" wire:click="addPhone">
                    {{ __('Agregar teléfono') }}
                </flux:button>
            </div>

            <flux:field>
                <flux:label>{{ __('Dirección') }}</flux:label>
                <flux:textarea wire:model="address" rows="2" />
                <flux:error name="address" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Ubicación en Google Maps (URL)') }}</flux:label>
                <flux:input type="url" wire:model="maps_url" placeholder="https://maps.app.goo.gl/..." />
                <flux:error name="maps_url" />
            </flux:field>

            <flux:field variant="inline">
                <flux:checkbox wire:model="is_primary" />
                <flux:label>{{ __('Contacto principal de la organización') }}</flux:label>
                <flux:error name="is_primary" />
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
                    {{ $editingContactId ? __('Guardar cambios') : __('Crear contacto') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Delete confirmation, populated by Index::confirmDelete() --}}
    <flux:modal name="confirm-contact-delete" class="w-full max-w-lg">
        @if ($this->deletingContact)
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ __('¿Eliminar a :name?', ['name' => $this->deletingContact->name]) }}</flux:heading>
                    <flux:subheading>
                        {{ __('El contacto dejará de mostrarse en el CRM. Sus interacciones se conservan.') }}
                    </flux:subheading>
                </div>

                <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                    <flux:modal.close>
                        <flux:button variant="filled">{{ __('Cancelar') }}</flux:button>
                    </flux:modal.close>

                    <flux:button variant="danger" wire:click="delete">
                        {{ __('Eliminar contacto') }}
                    </flux:button>
                </div>
            </div>
        @endif
    </flux:modal>
</section>
