{{--
    Horizontal bar chart of the pipeline funnel, drawn with plain HTML/CSS.
    $data is a list of ['name', 'count', 'amount' (MXN), 'isWon', 'isLost'] in
    pipeline order. One bar per stage (at most 24px thick, rounded at its end),
    the count at the tip of each bar, a tooltip with the MXN amount on hover or
    focus, and a visually hidden table repeating the data for screen readers.
--}}
@props(['data', 'label'])

@php
    $peak = max(array_column($data, 'count') ?: [0]);
@endphp

<div role="group" aria-label="{{ $label }}" class="mt-4 space-y-2">
    @foreach ($data as $stage)
        @php($width = $peak > 0 ? $stage['count'] / $peak * 85 : 0)
        <div tabindex="0" class="group relative flex items-center gap-3 outline-none">
            <div class="w-36 shrink-0 truncate text-right text-sm text-zinc-700 dark:text-zinc-300" title="{{ $stage['name'] }}">
                {{ $stage['name'] }}
                @if ($stage['isWon'])
                    <span class="text-xs text-zinc-500">· {{ __('Ganada') }}</span>
                @elseif ($stage['isLost'])
                    <span class="text-xs text-zinc-500">· {{ __('Perdida') }}</span>
                @endif
            </div>

            <div class="flex min-w-0 flex-1 items-center gap-2">
                <div class="h-6 rounded-r bg-[#2a78d6] transition group-hover:brightness-110 group-focus:brightness-110 dark:bg-[#3987e5]" style="width: {{ $width }}%"></div>
                <span class="text-sm font-medium tabular-nums text-zinc-800 dark:text-white">{{ $stage['count'] }}</span>
            </div>

            <div role="tooltip" class="pointer-events-none absolute -top-8 left-40 z-10 hidden whitespace-nowrap rounded-md bg-zinc-900 px-2 py-1 text-xs text-white group-hover:block group-focus:block dark:bg-zinc-100 dark:text-zinc-900">
                {{ $stage['name'] }}: {{ $stage['count'] }} {{ $stage['count'] === 1 ? __('oportunidad') : __('oportunidades') }} · ${{ number_format($stage['amount']) }} MXN
            </div>
        </div>
    @endforeach

    <table class="sr-only">
        <caption>{{ $label }}</caption>
        <thead>
            <tr>
                <th scope="col">{{ __('Etapa') }}</th>
                <th scope="col">{{ __('Oportunidades') }}</th>
                <th scope="col">{{ __('Monto') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $stage)
                <tr>
                    <th scope="row">{{ $stage['name'] }}</th>
                    <td>{{ $stage['count'] }}</td>
                    <td>${{ number_format($stage['amount']) }} MXN</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
