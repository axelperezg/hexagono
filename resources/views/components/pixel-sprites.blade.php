{{--
    Hidden SVG sprite sheet for the pixel-art landing page
    (resources/views/pixelart.blade.php). Render it once per page; every
    <x-pixel-diagram> references these symbols with <use href="#px-...">.

    Each symbol is drawn on a tiny integer grid (one unit = one "pixel")
    and filled with currentColor, so a <use> element picks its color from
    a text-* class. Dark details use the page's night color.
--}}
<svg width="0" height="0" class="absolute" aria-hidden="true" focusable="false">
    <defs>
        <symbol id="px-arrow-right" viewBox="0 0 8 7">
            <rect x="0" y="3" width="5" height="1" fill="currentColor" />
            <rect x="5" y="1" width="1" height="5" fill="currentColor" />
            <rect x="6" y="2" width="1" height="3" fill="currentColor" />
            <rect x="7" y="3" width="1" height="1" fill="currentColor" />
        </symbol>

        <symbol id="px-arrow-down" viewBox="0 0 7 8">
            <rect x="3" y="0" width="1" height="5" fill="currentColor" />
            <rect x="1" y="5" width="5" height="1" fill="currentColor" />
            <rect x="2" y="6" width="3" height="1" fill="currentColor" />
            <rect x="3" y="7" width="1" height="1" fill="currentColor" />
        </symbol>

        <symbol id="px-arrow-up" viewBox="0 0 7 8">
            <rect x="3" y="0" width="1" height="1" fill="currentColor" />
            <rect x="2" y="1" width="3" height="1" fill="currentColor" />
            <rect x="1" y="2" width="5" height="1" fill="currentColor" />
            <rect x="3" y="3" width="1" height="5" fill="currentColor" />
        </symbol>

        <symbol id="px-person" viewBox="0 0 7 9">
            <rect x="2" y="0" width="3" height="3" fill="currentColor" />
            <rect x="1" y="4" width="5" height="3" fill="currentColor" />
            <rect x="1" y="7" width="2" height="2" fill="currentColor" />
            <rect x="4" y="7" width="2" height="2" fill="currentColor" />
        </symbol>

        <symbol id="px-doc" viewBox="0 0 7 9">
            <rect x="0" y="0" width="7" height="9" fill="currentColor" />
            <rect x="1" y="1" width="5" height="7" fill="#0b0f1f" />
            <rect x="2" y="3" width="3" height="1" fill="currentColor" />
            <rect x="2" y="5" width="3" height="1" fill="currentColor" />
        </symbol>

        <symbol id="px-chat" viewBox="0 0 9 8">
            <rect x="0" y="0" width="9" height="5" fill="currentColor" />
            <rect x="1" y="5" width="2" height="1" fill="currentColor" />
            <rect x="1" y="6" width="1" height="1" fill="currentColor" />
            <rect x="2" y="2" width="1" height="1" fill="#0b0f1f" />
            <rect x="4" y="2" width="1" height="1" fill="#0b0f1f" />
            <rect x="6" y="2" width="1" height="1" fill="#0b0f1f" />
        </symbol>

        <symbol id="px-check" viewBox="0 0 8 7">
            <rect x="0" y="3" width="1" height="2" fill="currentColor" />
            <rect x="1" y="4" width="1" height="2" fill="currentColor" />
            <rect x="2" y="5" width="1" height="2" fill="currentColor" />
            <rect x="3" y="4" width="1" height="2" fill="currentColor" />
            <rect x="4" y="3" width="1" height="2" fill="currentColor" />
            <rect x="5" y="2" width="1" height="2" fill="currentColor" />
            <rect x="6" y="1" width="1" height="2" fill="currentColor" />
            <rect x="7" y="0" width="1" height="2" fill="currentColor" />
        </symbol>

        <symbol id="px-lock" viewBox="0 0 7 9">
            <rect x="2" y="0" width="3" height="1" fill="currentColor" />
            <rect x="1" y="1" width="1" height="3" fill="currentColor" />
            <rect x="5" y="1" width="1" height="3" fill="currentColor" />
            <rect x="0" y="4" width="7" height="5" fill="currentColor" />
            <rect x="3" y="5" width="1" height="2" fill="#0b0f1f" />
        </symbol>

        <symbol id="px-path" viewBox="0 0 9 9">
            <rect x="0" y="0" width="3" height="3" fill="currentColor" />
            <rect x="1" y="3" width="1" height="3" fill="currentColor" />
            <rect x="1" y="5" width="4" height="1" fill="currentColor" />
            <rect x="4" y="3" width="1" height="3" fill="currentColor" />
            <rect x="4" y="3" width="3" height="1" fill="currentColor" />
            <rect x="6" y="4" width="1" height="2" fill="currentColor" />
            <rect x="6" y="6" width="3" height="3" fill="currentColor" />
        </symbol>
    </defs>
</svg>
