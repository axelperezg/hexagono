{{--
    Inbox for the messages submitted through the public contact form
    (resources/views/welcome.blade.php -> App\Http\Controllers\ContactController).
    Full-page Livewire component, routed at /mensajes-contacto (routes/web.php).
--}}
<section class="w-full">
    <div class="mb-6">
        <flux:heading size="xl">{{ __('Mensajes de contacto') }}</flux:heading>
        <flux:text class="mt-1">{{ __('Solicitudes recibidas a través del formulario del sitio público.') }}</flux:text>
    </div>

    <div class="mb-4 flex flex-col gap-3 sm:flex-row">
        <flux:input
            wire:model.live.debounce.400ms="search"
            icon="magnifying-glass"
            placeholder="{{ __('Buscar por nombre, correo o institución') }}"
            class="sm:max-w-xs"
        />

        <flux:select wire:model.live="studyType" placeholder="{{ __('Todos los tipos de estudio') }}" class="sm:max-w-xs">
            <flux:select.option value="">{{ __('Todos los tipos de estudio') }}</flux:select.option>
            @foreach ($this->studyTypes as $type)
                <flux:select.option value="{{ $type->value }}">{{ $type->label() }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

    @if ($messages->isEmpty())
        <flux:callout icon="inbox" heading="{{ __('Sin resultados') }}" text="{{ __('No hay mensajes que coincidan con estos filtros.') }}" />
    @else
        <flux:table :paginate="$messages">
            <flux:table.columns>
                <flux:table.column>{{ __('Remitente') }}</flux:table.column>
                <flux:table.column>{{ __('Institución') }}</flux:table.column>
                <flux:table.column>{{ __('Tipo de estudio') }}</flux:table.column>
                <flux:table.column>{{ __('Recibido') }}</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($messages as $message)
                    <flux:table.row :key="$message->id">
                        <flux:table.cell>
                            <div class="font-medium text-zinc-800 dark:text-white">{{ $message->name }}</div>
                            <div class="text-zinc-500">{{ $message->email }}</div>
                        </flux:table.cell>
                        <flux:table.cell>{{ $message->institution ?? '—' }}</flux:table.cell>
                        <flux:table.cell class="py-0">
                            <flux:badge size="sm" color="blue">{{ $message->study_type->label() }}</flux:badge>
                        </flux:table.cell>
                        <flux:table.cell class="whitespace-nowrap text-zinc-500" title="{{ $message->created_at->format('d/m/Y H:i') }}">
                            {{ $message->created_at->diffForHumans() }}
                        </flux:table.cell>
                        <flux:table.cell class="py-0">
                            <flux:button size="sm" variant="ghost" icon="eye" wire:click="show({{ $message->id }})">
                                {{ __('Ver') }}
                            </flux:button>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    @endif

    {{-- Detail modal, populated by ContactMessages\Index::show() --}}
    <flux:modal name="mensaje-detalle" class="w-full max-w-lg">
        @if ($this->selectedMessage)
            <div class="space-y-5">
                <div>
                    <flux:heading size="lg">{{ $this->selectedMessage->name }}</flux:heading>
                    <flux:text class="mt-1">{{ $this->selectedMessage->created_at->format('d/m/Y H:i') }}</flux:text>
                </div>

                <dl class="grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="text-zinc-500">{{ __('Correo') }}</dt>
                        <dd>
                            <a href="mailto:{{ $this->selectedMessage->email }}" class="text-blue-600 dark:text-blue-400">
                                {{ $this->selectedMessage->email }}
                            </a>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-zinc-500">{{ __('Teléfono') }}</dt>
                        <dd>{{ $this->selectedMessage->phone ?? __('No especificado') }}</dd>
                    </div>
                    <div>
                        <dt class="text-zinc-500">{{ __('Institución') }}</dt>
                        <dd>{{ $this->selectedMessage->institution ?? __('No especificada') }}</dd>
                    </div>
                    <div>
                        <dt class="text-zinc-500">{{ __('Tipo de estudio') }}</dt>
                        <dd><flux:badge size="sm" color="blue">{{ $this->selectedMessage->study_type->label() }}</flux:badge></dd>
                    </div>
                </dl>

                <div>
                    <flux:text class="mb-1">{{ __('Mensaje') }}</flux:text>
                    <p class="rounded-lg bg-zinc-50 p-4 text-sm whitespace-pre-line dark:bg-zinc-700/50">
                        {{ $this->selectedMessage->message }}
                    </p>
                </div>
            </div>
        @endif
    </flux:modal>
</section>
