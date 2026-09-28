{{--
    Pixel-art explainer diagrams for the "Servicios" section of
    resources/views/pixelart.blade.php, one per service, keyed by the same
    names <x-service-icon> uses. Usage: <x-pixel-diagram name="eye" />

    Every diagram is a 320-unit-wide SVG drawn on whole-number coordinates
    with crispEdges rendering, reusing the symbols from <x-pixel-sprites>
    (which must be rendered once on the page). Labels use the VT323 retro
    font so they stay narrow enough to read on phones.
--}}
@props(['name', 'label'])

<svg
    {{ $attributes->merge(['class' => 'pixel-diagram h-auto w-full']) }}
    viewBox="0 0 320 {{ match ($name) { 'eye' => 160, 'chart' => 150, 'chat' => 150, 'users' => 156, 'network' => 156, default => 140 } }}"
    shape-rendering="crispEdges"
    role="img"
>
    <title>{{ $label }}</title>

    @switch($name)
        {{-- Pre-test: pieces are shown to the audience and scored before launch --}}
        @case('eye')
            <use href="#px-doc" x="12" y="50" width="21" height="27" class="text-zinc-500" />
            <use href="#px-doc" x="20" y="43" width="21" height="27" class="text-zinc-300" />
            <use href="#px-doc" x="28" y="36" width="21" height="27" class="text-pixel-yellow" />
            <text x="34" y="100" text-anchor="middle" class="fill-zinc-300">PIEZAS</text>

            <use href="#px-arrow-right" x="60" y="54" width="24" height="21" class="text-electric pixel-nudge" />

            <use href="#px-person" x="94" y="48" width="14" height="18" class="text-pixel-pink" />
            <use href="#px-person" x="112" y="44" width="14" height="18" class="text-pixel-green" />
            <use href="#px-person" x="130" y="48" width="14" height="18" class="text-electric" />
            <rect x="92" y="70" width="54" height="3" class="fill-zinc-600" />
            <text x="119" y="100" text-anchor="middle" class="fill-zinc-300">AUDIENCIA</text>

            <use href="#px-arrow-right" x="154" y="54" width="24" height="21" class="text-electric pixel-nudge" />

            @foreach ([['Atención', 44], ['Comprensión', 38], ['Credibilidad', 30], ['Relevancia', 40], ['Motivación', 24]] as $index => [$metric, $barWidth])
                <text x="186" y="{{ 24 + $index * 18 }}" class="fill-zinc-300">{{ $metric }}</text>
                <rect x="266" y="{{ 15 + $index * 18 }}" width="48" height="10" class="fill-pixel-panel" />
                <rect x="266" y="{{ 15 + $index * 18 }}" width="{{ $barWidth }}" height="10" class="fill-pixel-yellow" />
            @endforeach

            <rect x="10" y="126" width="304" height="26" class="fill-pixel-panel" />
            <rect x="10" y="126" width="4" height="26" class="fill-pixel-yellow" />
            <use href="#px-check" x="24" y="132" width="16" height="14" class="text-pixel-green" />
            <text x="48" y="144" class="fill-white">VEREDICTO: ¿ESTÁ LISTA PARA SALIR?</text>
            @break

        {{-- Post-test: a funnel of what the finished campaign achieved --}}
        @case('chart')
            @foreach ([['Campaña', 200], ['Exposición', 172], ['Recordación', 144], ['Comprensión', 116], ['Atribución', 88], ['Objetivos', 60]] as $index => [$stage, $barWidth])
                <text x="10" y="{{ 22 + $index * 22 }}" class="fill-zinc-300">{{ $stage }}</text>
                <rect
                    x="{{ 212 - $barWidth / 2 }}"
                    y="{{ 10 + $index * 22 }}"
                    width="{{ $barWidth }}"
                    height="16"
                    class="{{ $index === 5 ? 'fill-pixel-green' : ($index % 2 === 0 ? 'fill-electric' : 'fill-electric/70') }}"
                />
            @endforeach
            <use href="#px-check" x="204" y="121" width="16" height="14" class="text-pixel-night" />
            @break

        {{-- Opinion research: quantitative + qualitative inputs merge into insights --}}
        @case('chat')
            @foreach ([['Encuestas', 'fill-pixel-yellow'], ['Grupos de enfoque', 'fill-pixel-pink'], ['Entrevistas', 'fill-pixel-pink'], ['Etnografías', 'fill-pixel-pink']] as $index => [$source, $color])
                <rect x="8" y="{{ 10 + $index * 34 }}" width="124" height="26" class="fill-pixel-panel" />
                <rect x="8" y="{{ 10 + $index * 34 }}" width="4" height="26" class="{{ $color }}" />
                <text x="17" y="{{ 27 + $index * 34 }}" class="fill-zinc-200">{{ $source }}</text>
                <rect x="132" y="{{ 22 + $index * 34 }}" width="8" height="2" class="fill-zinc-500" />
            @endforeach
            <rect x="140" y="22" width="2" height="104" class="fill-zinc-500" />
            <use href="#px-arrow-right" x="142" y="67" width="12" height="11" class="text-electric pixel-nudge" />

            <rect x="154" y="54" width="56" height="38" class="fill-electric" />
            <use href="#px-chat" x="173" y="58" width="18" height="16" class="text-pixel-night" />
            <text x="182" y="87" text-anchor="middle" class="fill-pixel-night">ANÁLISIS</text>

            <rect x="210" y="72" width="6" height="2" class="fill-zinc-500" />
            <rect x="214" y="30" width="2" height="88" class="fill-zinc-500" />
            @foreach (['Percepciones', 'Prioridades', 'Confianza'] as $index => $insight)
                <rect x="216" y="{{ 30 + $index * 44 }}" width="8" height="2" class="fill-zinc-500" />
                <rect x="224" y="{{ 18 + $index * 44 }}" width="90" height="26" class="fill-pixel-panel" />
                <rect x="310" y="{{ 18 + $index * 44 }}" width="4" height="26" class="fill-pixel-green" />
                <text x="232" y="{{ 35 + $index * 44 }}" class="fill-white">{{ $insight }}</text>
            @endforeach
            @break

        {{-- Audience intelligence: a mixed population split into segments, each with its message --}}
        @case('users')
            @php
                $segmentColors = ['text-pixel-yellow', 'text-pixel-pink', 'text-pixel-green'];
                $populationPattern = [0, 2, 1, 0, 1, 0, 2, 1, 2, 1, 0, 2];
            @endphp
            <rect x="8" y="16" width="100" height="104" class="fill-pixel-panel" />
            @foreach ($populationPattern as $index => $segment)
                <use
                    href="#px-person"
                    x="{{ 18 + ($index % 4) * 22 }}"
                    y="{{ 26 + intdiv($index, 4) * 30 }}"
                    width="14"
                    height="18"
                    class="{{ $segmentColors[$segment] }}"
                />
            @endforeach
            <text x="58" y="140" text-anchor="middle" class="fill-zinc-300">POBLACIÓN</text>

            <use href="#px-arrow-right" x="116" y="58" width="24" height="21" class="text-electric pixel-nudge" />

            @foreach (['A', 'B', 'C'] as $index => $segmentName)
                <rect x="148" y="{{ 8 + $index * 46 }}" width="166" height="38" class="fill-pixel-panel" />
                @for ($member = 0; $member < 3; $member++)
                    <use href="#px-person" x="{{ 156 + $member * 16 }}" y="{{ 18 + $index * 46 }}" width="14" height="18" class="{{ $segmentColors[$index] }}" />
                @endfor
                <use href="#px-chat" x="212" y="{{ 14 + $index * 46 }}" width="18" height="16" class="{{ $segmentColors[$index] }}" />
                <text x="236" y="{{ 24 + $index * 46 }}" class="fill-white">Segmento {{ $segmentName }}</text>
                <text x="236" y="{{ 38 + $index * 46 }}" class="fill-zinc-400">Mensaje {{ $segmentName }}</text>
            @endforeach
            @break

        {{-- Digital intelligence: listening to a network of communities and tracking the climate --}}
        @case('network')
            @php
                $hub = [72, 66];
                $nodes = [
                    [[22, 24], 'fill-pixel-pink'], [[48, 14], 'fill-pixel-pink'], [[14, 60], 'fill-pixel-pink'],
                    [[122, 20], 'fill-pixel-yellow'], [[134, 58], 'fill-pixel-yellow'], [[108, 44], 'fill-pixel-yellow'],
                    [[30, 108], 'fill-pixel-green'], [[76, 118], 'fill-pixel-green'], [[124, 104], 'fill-pixel-green'],
                ];
            @endphp
            @foreach ($nodes as [[$nodeX, $nodeY], $color])
                <line x1="{{ $hub[0] + 5 }}" y1="{{ $hub[1] + 5 }}" x2="{{ $nodeX + 3 }}" y2="{{ $nodeY + 3 }}" class="stroke-zinc-600" stroke-width="2" />
            @endforeach
            <line x1="25" y1="27" x2="51" y2="17" class="stroke-pixel-pink/60" stroke-width="2" />
            <line x1="125" y1="23" x2="111" y2="47" class="stroke-pixel-yellow/60" stroke-width="2" />
            <line x1="33" y1="111" x2="79" y2="121" class="stroke-pixel-green/60" stroke-width="2" />
            @foreach ($nodes as [[$nodeX, $nodeY], $color])
                <rect x="{{ $nodeX }}" y="{{ $nodeY }}" width="7" height="7" class="{{ $color }}" />
            @endforeach
            <rect x="{{ $hub[0] }}" y="{{ $hub[1] }}" width="11" height="11" class="fill-electric pixel-blink-slow" />
            <use href="#px-chat" x="84" y="44" width="18" height="16" class="text-zinc-300" />
            <text x="76" y="146" text-anchor="middle" class="fill-zinc-300">REDES Y COMUNIDADES</text>

            <rect x="170" y="14" width="2" height="112" class="fill-zinc-500" />
            <rect x="170" y="124" width="144" height="2" class="fill-zinc-500" />
            @for ($gridLine = 0; $gridLine < 3; $gridLine++)
                <rect x="172" y="{{ 34 + $gridLine * 30 }}" width="142" height="1" class="fill-pixel-panel" />
            @endfor
            <path d="M172 100 H190 V88 H208 V96 H226 V72 H244 V62 H262 V78 H280 V48 H298 V36 H314" fill="none" class="stroke-pixel-pink" stroke-width="3" />
            <rect x="295" y="33" width="6" height="6" class="fill-white pixel-blink" />
            <text x="242" y="146" text-anchor="middle" class="fill-zinc-300">CLIMA DIGITAL</text>
            @break

        {{-- Consulting: data turned into tools, visualizations and strategy, in a loop --}}
        @default
            @php
                $dataColors = ['fill-electric', 'fill-pixel-yellow', 'fill-pixel-pink', 'fill-pixel-green', 'fill-zinc-500'];
            @endphp

            <rect x="10" y="10" width="56" height="56" class="fill-pixel-panel" />
            @for ($cell = 0; $cell < 16; $cell++)
                <rect x="{{ 16 + ($cell % 4) * 12 }}" y="{{ 16 + intdiv($cell, 4) * 12 }}" width="8" height="8" class="{{ $dataColors[($cell * 7) % 5] }}" />
            @endfor
            <text x="38" y="86" text-anchor="middle" font-size="12" class="fill-zinc-300">DATOS</text>

            <use href="#px-arrow-right" x="70" y="30" width="16" height="14" class="text-electric pixel-nudge" />

            <rect x="92" y="10" width="56" height="56" class="fill-pixel-panel" />
            <rect x="108" y="26" width="24" height="24" class="fill-pixel-yellow" />
            <rect x="116" y="18" width="8" height="40" class="fill-pixel-yellow" />
            <rect x="100" y="34" width="40" height="8" class="fill-pixel-yellow" />
            <rect x="116" y="34" width="8" height="8" class="fill-pixel-panel" />
            <text x="120" y="86" text-anchor="middle" font-size="12" class="fill-zinc-300">HERRAMIENTAS</text>

            <use href="#px-arrow-right" x="152" y="30" width="16" height="14" class="text-electric pixel-nudge" />

            <rect x="174" y="10" width="56" height="56" class="fill-pixel-panel" />
            @foreach ([20, 32, 14, 40] as $index => $barHeight)
                <rect x="{{ 182 + $index * 11 }}" y="{{ 58 - $barHeight }}" width="8" height="{{ $barHeight }}" class="{{ $index === 3 ? 'fill-pixel-pink' : 'fill-electric' }}" />
            @endforeach
            <text x="202" y="86" text-anchor="middle" font-size="12" class="fill-zinc-300">VISUALIZACIÓN</text>

            <use href="#px-arrow-right" x="234" y="30" width="16" height="14" class="text-electric pixel-nudge" />

            <rect x="256" y="10" width="56" height="56" class="fill-pixel-panel" />
            <rect x="266" y="20" width="36" height="36" class="fill-pixel-green" />
            <rect x="272" y="26" width="24" height="24" class="fill-pixel-panel" />
            <rect x="278" y="32" width="12" height="12" class="fill-pixel-green" />
            <text x="284" y="86" text-anchor="middle" font-size="12" class="fill-zinc-300">ESTRATEGIA</text>

            <rect x="283" y="96" width="2" height="18" class="fill-zinc-500" />
            <rect x="37" y="112" width="248" height="2" class="fill-zinc-500" />
            <use href="#px-arrow-up" x="31" y="94" width="14" height="16" class="text-zinc-500" />
            <text x="160" y="130" text-anchor="middle" class="fill-zinc-400">ACOMPAÑAMIENTO CONTINUO</text>
    @endswitch
</svg>
