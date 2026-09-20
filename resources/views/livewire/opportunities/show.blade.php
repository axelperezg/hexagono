{{--
    Opportunity detail page with the interactions timeline. Full-page
    Livewire component, routed at /oportunidades/{opportunity}
    (routes/web.php).
--}}
<section class="w-full">
    <div class="mb-2">
        <flux:button size="sm" variant="ghost" icon="arrow-left" :href="route('opportunities.index')" wire:navigate>
            {{ __('Oportunidades') }}
        </flux:button>
    </div>

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <flux:heading size="xl">{{ $opportunity->title }}</flux:heading>
            <flux:text class="mt-1">{{ $opportunity->organization->name }}</flux:text>
        </div>

        <flux:select wire:model.live="pipeline_stage_id" class="sm:max-w-48" aria-label="{{ __('Etapa') }}">
            @foreach ($this->stages as $stage)
                <flux:select.option value="{{ $stage->id }}">{{ $stage->name }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

    <dl class="mb-8 grid grid-cols-1 gap-4 text-sm sm:grid-cols-3">
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
        @if ($opportunity->notes)
            <div class="sm:col-span-3">
                <dt class="text-zinc-500">{{ __('Notas') }}</dt>
                <dd class="whitespace-pre-line">{{ $opportunity->notes }}</dd>
            </div>
        @endif
    </dl>

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
