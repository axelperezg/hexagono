{{--
    Minimal line-art icon set for the "Servicios" and "Metodología" sections
    of the landing page. Usage: <x-service-icon name="eye" class="h-7 w-7" />
--}}
@props(['name'])

<svg {{ $attributes->merge(['viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '1.5', 'aria-hidden' => 'true']) }}>
    @switch($name)
        @case('eye')
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12Z" />
            <circle cx="12" cy="12" r="2.75" />
            @break

        @case('chart')
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 20V10M11 20V4M18 20v-7" />
            @break

        @case('chat')
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 5.5h16v10H9l-4 4v-4H4v-10Z" />
            @break

        @case('grid')
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4h7v7H4V4Zm9 0h7v7h-7V4ZM4 13h7v7H4v-7Zm9 0h7v7h-7v-7Z" />
            @break

        @case('target')
            <circle cx="12" cy="12" r="8.5" />
            <circle cx="12" cy="12" r="4.5" />
            <circle cx="12" cy="12" r="0.75" fill="currentColor" stroke="none" />
            @break

        @case('clipboard')
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 4.5h8a1 1 0 0 1 1 1V6h1.5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-13a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1H7v-.5a1 1 0 0 1 1-1Z" />
            <path stroke-linecap="round" d="M8.5 12h7M8.5 15.5h7" />
            @break

        @case('bars')
            <path stroke-linecap="round" d="M4 20V9M10.5 20V4M17 20v-6" />
            @break

        @case('report')
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 3.5h9l3.5 3.5V20a.5.5 0 0 1-.5.5H6a.5.5 0 0 1-.5-.5V4a.5.5 0 0 1 .5-.5Z" />
            <path stroke-linecap="round" d="M9 12.5h6M9 15.5h6M14.5 3.5V7h3.5" />
            @break
    @endswitch
</svg>
