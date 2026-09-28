{{--
    The Hexágono brand mark redrawn as pixel art for
    resources/views/pixelart.blade.php: a pointy-top hexagon rasterized
    onto a square grid, with an outline ring, a dark body and a smaller
    inner hexagon. Usage: <x-pixel-hexagon :grid="21" class="h-40 w-40" />
--}}
@props(['grid' => 21])

@php
    $center = ($grid - 1) / 2;
    $radius = $grid / 2;

    /**
     * Whether the grid cell at ($column, $row) falls inside a pointy-top
     * hexagon of the given radius centered on the grid.
     */
    $isInsideHexagon = function (int $column, int $row, float $hexRadius) use ($center): bool {
        $horizontalDistance = abs($column - $center);
        $verticalDistance = abs($row - $center);

        return $horizontalDistance <= $hexRadius * sqrt(3) / 2
            && $verticalDistance + $horizontalDistance / sqrt(3) <= $hexRadius;
    };
@endphp

<svg {{ $attributes->merge(['viewBox' => "0 0 {$grid} {$grid}", 'shape-rendering' => 'crispEdges', 'aria-hidden' => 'true']) }}>
    @for ($row = 0; $row < $grid; $row++)
        @for ($column = 0; $column < $grid; $column++)
            @if ($isInsideHexagon($column, $row, $radius * 0.36))
                <rect x="{{ $column }}" y="{{ $row }}" width="1" height="1" class="fill-pixel-yellow" />
            @elseif ($isInsideHexagon($column, $row, $radius * 0.8))
                <rect x="{{ $column }}" y="{{ $row }}" width="1" height="1" class="{{ ($column + $row) % 5 === 0 ? 'fill-pixel-panel' : 'fill-pixel-night' }}" />
            @elseif ($isInsideHexagon($column, $row, $radius))
                <rect x="{{ $column }}" y="{{ $row }}" width="1" height="1" class="fill-electric" />
            @endif
        @endfor
    @endfor
</svg>
