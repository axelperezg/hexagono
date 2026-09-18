{{--
    Admin module to create, edit, view and delete application users.
    Full-page Livewire component, routed at /usuarios (routes/web.php),
    restricted to the Admin role via the "admin" middleware alias.
--}}
<section class="w-full">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl">{{ __('Usuarios') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Administra las cuentas que pueden acceder al panel interno.') }}</flux:text>
        </div>

        <flux:button variant="primary" icon="plus" wire:click="createUser">
            {{ __('Nuevo usuario') }}
        </flux:button>
    </div>

    <div class="mb-4">
        <flux:input
            wire:model.live.debounce.400ms="search"
            icon="magnifying-glass"
            placeholder="{{ __('Buscar por nombre o correo') }}"
            class="sm:max-w-xs"
        />
    </div>

    @if ($users->isEmpty())
        <flux:callout icon="users" heading="{{ __('Sin resultados') }}" text="{{ __('No hay usuarios que coincidan con esta búsqueda.') }}" />
    @else
        <flux:table :paginate="$users">
            <flux:table.columns>
                <flux:table.column>{{ __('Usuario') }}</flux:table.column>
                <flux:table.column>{{ __('Rol') }}</flux:table.column>
                <flux:table.column>{{ __('Creado') }}</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($users as $user)
                    <flux:table.row :key="$user->id">
                        <flux:table.cell>
                            <div class="font-medium text-zinc-800 dark:text-white">{{ $user->name }}</div>
                            <div class="text-zinc-500">{{ $user->email }}</div>
                        </flux:table.cell>
                        <flux:table.cell class="py-0">
                            <flux:badge size="sm" :color="$user->role === \App\Enums\UserRole::Admin ? 'blue' : 'zinc'">
                                {{ $user->role->label() }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell class="whitespace-nowrap text-zinc-500" title="{{ $user->created_at->format('d/m/Y H:i') }}">
                            {{ $user->created_at->diffForHumans() }}
                        </flux:table.cell>
                        <flux:table.cell class="py-0">
                            <div class="flex justify-end gap-1">
                                <flux:button size="sm" variant="ghost" icon="eye" wire:click="show({{ $user->id }})">
                                    {{ __('Ver') }}
                                </flux:button>
                                <flux:button size="sm" variant="ghost" icon="pencil" wire:click="editUser({{ $user->id }})">
                                    {{ __('Editar') }}
                                </flux:button>
                                <flux:button size="sm" variant="ghost" icon="trash" wire:click="confirmDelete({{ $user->id }})">
                                    {{ __('Eliminar') }}
                                </flux:button>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    @endif

    {{-- Create/edit form, populated by Index::createUser() / Index::editUser() --}}
    <flux:modal name="user-form" class="w-full max-w-lg">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">
                    {{ $editingUserId ? __('Editar usuario') : __('Nuevo usuario') }}
                </flux:heading>
            </div>

            <flux:field>
                <flux:label>{{ __('Nombre') }}</flux:label>
                <flux:input wire:model="name" autocomplete="name" />
                <flux:error name="name" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Correo electrónico') }}</flux:label>
                <flux:input type="email" wire:model="email" autocomplete="email" />
                <flux:error name="email" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Rol') }}</flux:label>
                <flux:select wire:model="role">
                    @foreach ($this->roles as $roleOption)
                        <flux:select.option value="{{ $roleOption->value }}">{{ $roleOption->label() }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:error name="role" />
            </flux:field>

            <flux:field>
                <flux:label>
                    {{ $editingUserId ? __('Nueva contraseña (opcional)') : __('Contraseña') }}
                </flux:label>
                <flux:input type="password" wire:model="password" autocomplete="new-password" viewable />
                <flux:description>
                    @if ($editingUserId)
                        {{ __('Déjala en blanco para conservar la contraseña actual.') }}
                    @endif
                </flux:description>
                <flux:error name="password" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Confirmar contraseña') }}</flux:label>
                <flux:input type="password" wire:model="password_confirmation" autocomplete="new-password" viewable />
            </flux:field>

            <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                <flux:modal.close>
                    <flux:button variant="filled">{{ __('Cancelar') }}</flux:button>
                </flux:modal.close>

                <flux:button variant="primary" type="submit">
                    {{ $editingUserId ? __('Guardar cambios') : __('Crear usuario') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Read-only detail modal, populated by Index::show() --}}
    <flux:modal name="user-detalle" class="w-full max-w-lg">
        @if ($this->viewingUser)
            <div class="space-y-5">
                <div>
                    <flux:heading size="lg">{{ $this->viewingUser->name }}</flux:heading>
                    <flux:text class="mt-1">{{ __('Creado el') }} {{ $this->viewingUser->created_at->format('d/m/Y H:i') }}</flux:text>
                </div>

                <dl class="grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="text-zinc-500">{{ __('Correo') }}</dt>
                        <dd>
                            <a href="mailto:{{ $this->viewingUser->email }}" class="text-blue-600 dark:text-blue-400">
                                {{ $this->viewingUser->email }}
                            </a>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-zinc-500">{{ __('Rol') }}</dt>
                        <dd>
                            <flux:badge size="sm" :color="$this->viewingUser->role === \App\Enums\UserRole::Admin ? 'blue' : 'zinc'">
                                {{ $this->viewingUser->role->label() }}
                            </flux:badge>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-zinc-500">{{ __('Correo verificado') }}</dt>
                        <dd>{{ $this->viewingUser->email_verified_at ? __('Sí') : __('No') }}</dd>
                    </div>
                </dl>
            </div>
        @endif
    </flux:modal>

    {{-- Delete confirmation, populated by Index::confirmDelete() --}}
    <flux:modal name="confirm-user-delete" class="w-full max-w-lg">
        @if ($this->deletingUser)
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ __('¿Eliminar a :name?', ['name' => $this->deletingUser->name]) }}</flux:heading>
                    <flux:subheading>
                        {{ __('Esta acción no se puede deshacer. El usuario perderá acceso al panel de inmediato.') }}
                    </flux:subheading>
                </div>

                <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                    <flux:modal.close>
                        <flux:button variant="filled">{{ __('Cancelar') }}</flux:button>
                    </flux:modal.close>

                    <flux:button variant="danger" wire:click="delete">
                        {{ __('Eliminar usuario') }}
                    </flux:button>
                </div>
            </div>
        @endif
    </flux:modal>
</section>
