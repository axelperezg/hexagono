{{--
    A labeled text input for the public contact form, wired for both
    server-side validation ($errors / old()) and the client-side errors
    shown by resources/js/landing.js (the [data-error-for] paragraph).

    Any extra attribute (required, autocomplete, type override, etc.) is
    forwarded straight onto the <input>, so `required` also drives the
    visible "*" next to the label.
--}}
@props(['name', 'label', 'type' => 'text'])

<div>
    <label for="{{ $name }}" class="mb-2 block text-sm font-medium text-zinc-300">
        {{ $label }}
        @if ($attributes->has('required'))
            <span class="text-electric" aria-hidden="true">*</span>
        @endif
    </label>
    <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="{{ $type }}"
        value="{{ old($name) }}"
        aria-describedby="{{ $name }}-error"
        {{ $attributes->merge(['class' => 'w-full rounded-sm border border-white/15 bg-ink px-3 py-2.5 text-sm text-white outline-none transition focus:border-electric focus:ring-1 focus:ring-electric']) }}
    >
    <p id="{{ $name }}-error" data-error-for="{{ $name }}" class="mt-1.5 text-xs text-red-400 {{ $errors->has($name) ? '' : 'hidden' }}">
        {{ $errors->first($name) }}
    </p>
</div>
