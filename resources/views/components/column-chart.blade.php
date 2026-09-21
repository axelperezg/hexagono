{{--
    Single-series column chart drawn with plain HTML/CSS (no chart library).
    $data is a list of ['label' => short x label, 'name' => tooltip name,
    'value' => int|float]. With format="currency" values are MXN amounts,
    otherwise counts named by $singular / $plural. Columns are at most 24px wide with a rounded top, the
    tallest one carries its value, hovering or focusing a column shows a
    tooltip, and a visually hidden table repeats the data for screen readers.
--}}
@props(['data', 'label', 'singular' => '', 'plural' => '', 'color' => 'blue', 'format' => 'number'])

@php
    $isCurrency = $format === 'currency';
    $peak = max(array_column($data, 'value') ?: [0]);
    $rawStep = $peak / 4;
    $magnitude = $rawStep > 0 ? 10 ** floor(log10($rawStep)) : 1;
    $step = collect([1, 2, 5, 10])->map(fn (int $factor) => $factor * $magnitude)->first(fn (float|int $candidate) => $rawStep <= $candidate) ?? 1;
    $step = $isCurrency ? max($step, 1) : max((int) $step, 1);
    $top = max($step, (int) ceil($peak / $step) * $step);
    $ticks = range(0, $top, $step);
    $barClass = [
        'blue' => 'bg-[#2a78d6] dark:bg-[#3987e5]',
        'orange' => 'bg-[#eb6834] dark:bg-[#d95926]',
    ][$color];
    $compact = fn (float|int $value): string => match (true) {
        $value >= 1_000_000 => '$'.round($value / 1_000_000, 1).'M',
        $value >= 1_000 => '$'.round($value / 1_000, 1).'K',
        default => '$'.round($value),
    };
    $formatTick = fn (float|int $value): string => $isCurrency ? $compact($value) : number_format($value);
    $formatValue = fn (float|int $value): string => $isCurrency
        ? '$'.number_format($value).' MXN'
        : $value.' '.($value === 1 ? $singular : $plural);
@endphp

<div role="group" aria-label="{{ $label }}">
    <div class="flex gap-2 pt-6">
        <div class="relative h-48 w-12 shrink-0">
            @foreach ($ticks as $tick)
                <span class="absolute right-0 translate-y-1/2 text-xs tabular-nums text-zinc-500" style="bottom: {{ $tick / $top * 100 }}%">{{ $formatTick($tick) }}</span>
            @endforeach
        </div>

        <div class="min-w-0 flex-1">
            <div class="relative h-48 border-b border-zinc-200 dark:border-zinc-700">
                @foreach ($ticks as $tick)
                    @if ($tick > 0)
                        <div class="absolute inset-x-0 border-t border-zinc-200 dark:border-zinc-700" style="bottom: {{ $tick / $top * 100 }}%"></div>
                    @endif
                @endforeach

                <div class="absolute inset-0 flex gap-1 px-1">
                    @foreach ($data as $column)
                        @php($height = $column['value'] / $top * 100)
                        <div tabindex="0" class="group relative flex h-full min-w-0 flex-1 items-end justify-center outline-none">
                            <div class="{{ $barClass }} relative w-full max-w-6 rounded-t transition group-hover:brightness-110 group-focus:brightness-110" style="height: {{ $height }}%">
                                @if ($column['value'] > 0 && $column['value'] === $peak)
                                    <span class="absolute -top-5 left-1/2 -translate-x-1/2 text-xs font-medium tabular-nums text-zinc-800 dark:text-white">{{ $isCurrency ? $compact($column['value']) : $column['value'] }}</span>
                                @endif
                            </div>

                            <div role="tooltip" class="pointer-events-none absolute left-1/2 z-10 mb-1 hidden -translate-x-1/2 whitespace-nowrap rounded-md bg-zinc-900 px-2 py-1 text-xs text-white group-hover:block group-focus:block dark:bg-zinc-100 dark:text-zinc-900" style="bottom: {{ $height }}%">
                                {{ $column['name'] }}: {{ $formatValue($column['value']) }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="mt-1 flex gap-1 px-1" aria-hidden="true">
                @foreach ($data as $column)
                    <span class="min-w-0 flex-1 truncate text-center text-xs text-zinc-500">{{ $column['label'] }}</span>
                @endforeach
            </div>
        </div>
    </div>

    <table class="sr-only">
        <caption>{{ $label }}</caption>
        <thead>
            <tr>
                <th scope="col">{{ __('Mes') }}</th>
                <th scope="col">{{ $isCurrency ? __('Monto') : ucfirst($plural) }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $column)
                <tr>
                    <th scope="row">{{ $column['name'] }}</th>
                    <td>{{ $formatValue($column['value']) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
