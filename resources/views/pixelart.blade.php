{{--
    Pixel-art edition of the Hexágono Communication Intelligence landing page.

    Rendered at the "/pixelart" route (routes/web.php). Carries the same
    content as resources/views/welcome.blade.php — hero, servicios,
    metodología, sectores, contacto — restyled as 8-bit pixel art, with a
    pixel diagram per service (<x-pixel-diagram>) explaining how it works.

    Styles live in the "Pixel-art landing page" block of resources/css/app.css.
    It reuses resources/js/landing.js for the scroll reveals, the mobile nav
    and the no-reload contact form, so the element ids those rely on
    (mobile-nav-toggle, mobile-nav-panel, contact-form, contact-form-status)
    must stay in place. The form also works without JavaScript: it posts to
    route('contact.store'), which redirects back here with $errors / the
    "success" flash message.
--}}
@php
    $services = [
        [
            'title' => 'Estudios pre-test de campañas',
            'description' => 'Evaluamos conceptos, mensajes y piezas antes de su difusión: atención, comprensión, credibilidad, relevancia y motivación a la acción de las audiencias objetivo.',
            'icon' => 'eye',
            'diagram' => 'Las piezas de la campaña se prueban con la audiencia objetivo y se califican en cinco dimensiones para decidir si están listas para salir.',
        ],
        [
            'title' => 'Estudios post-test / evaluación de impacto',
            'description' => 'Medimos exposición, recordación, comprensión, atribución y cumplimiento de objetivos una vez concluida la campaña.',
            'icon' => 'chart',
            'diagram' => 'Un embudo que sigue a la campaña etapa por etapa, desde la exposición hasta el cumplimiento de sus objetivos.',
        ],
        [
            'title' => 'Investigación de opinión pública',
            'description' => 'Encuestas y estudios cualitativos —grupos de enfoque, entrevistas a profundidad, etnografías— para entender percepciones, prioridades y niveles de confianza ciudadana.',
            'icon' => 'chat',
            'diagram' => 'Fuentes cuantitativas (amarillo) y cualitativas (rosa) se analizan en conjunto para revelar percepciones, prioridades y confianza.',
        ],
        [
            'title' => 'Inteligencia de audiencias',
            'description' => 'Segmentación, perfiles y líneas base de comunicación para identificar a quién dirigirse y con qué mensaje antes de invertir en una campaña.',
            'icon' => 'users',
            'diagram' => 'Una población diversa se agrupa en segmentos con perfil propio, y cada segmento recibe el mensaje que le corresponde.',
        ],
        [
            'title' => 'Análisis e inteligencia digital',
            'description' => 'Escucha social, análisis de redes y comunidades, y seguimiento del clima digital para entender cómo se mueve una conversación en línea.',
            'icon' => 'network',
            'diagram' => 'Mapeamos las comunidades que conversan en línea y seguimos cómo evoluciona el clima digital a lo largo del tiempo.',
        ],
        [
            'title' => 'Consultoría en comunicación social y análisis de datos',
            'description' => 'Acompañamiento estratégico y desarrollo de herramientas propias para el análisis y visualización de datos de comunicación.',
            'icon' => 'grid',
            'diagram' => 'Los datos pasan por herramientas propias y visualizaciones hasta convertirse en estrategia, en un ciclo de acompañamiento continuo.',
        ],
    ];

    $steps = [
        ['title' => 'Diseño muestral', 'description' => 'Definimos universo, marco muestral y tamaño de muestra según el nivel de precisión requerido.', 'icon' => 'target'],
        ['title' => 'Levantamiento', 'description' => 'Aplicamos el instrumento en campo o en línea, con supervisión y controles de calidad continuos.', 'icon' => 'clipboard'],
        ['title' => 'Análisis', 'description' => 'Procesamos y analizamos los datos con métodos estadísticos apropiados al objetivo del estudio.', 'icon' => 'bars'],
        ['title' => 'Entrega de resultados', 'description' => 'Presentamos hallazgos y recomendaciones en reportes claros, listos para la toma de decisiones.', 'icon' => 'report'],
    ];

    $sectors = [
        'Secretarías y dependencias de la administración pública federal',
        'Organismos públicos descentralizados',
        'Entidades y organismos autónomos',
        'Programas y campañas de alcance nacional',
    ];

    $inputClasses = 'pixel-box w-full bg-pixel-night px-3 py-2 font-retro text-xl text-white outline-none [--pixel-border:var(--color-zinc-600)] focus:[--pixel-border:var(--color-pixel-yellow)]';
@endphp
<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- SEO --}}
    <title>Hexágono Communication Intelligence — Edición pixel art</title>
    <meta name="description" content="Hexágono Communication Intelligence es una firma mexicana de investigación de mercados especializada en estudios pre-test y post-test de campañas de comunicación social para gobierno federal, dependencias y organismos públicos.">
    <link rel="canonical" href="{{ route('home') }}">

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="stylesheet" href="https://fonts.bunny.net/css?family=press-start-2p:400|vt323:400&display=swap">

    @vite(['resources/css/app.css', 'resources/js/landing.js'])
</head>
<body class="pixel-starfield bg-pixel-night font-retro text-xl text-zinc-200 antialiased selection:bg-pixel-yellow selection:text-pixel-night">
    <x-pixel-sprites />

    {{-- ============================== HEADER ============================== --}}
    <header class="sticky top-0 z-50 border-b-4 border-black bg-pixel-night/95">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-4 lg:px-8">
            <a href="#inicio" class="flex items-center gap-3">
                <x-pixel-hexagon :grid="15" class="h-9 w-9" />
                <span class="flex flex-col gap-1 leading-none">
                    <span class="font-pixel text-xs text-white">HEXÁGONO</span>
                    <span class="font-pixel text-[7px] text-electric">COMMUNICATION INTELLIGENCE</span>
                </span>
            </a>

            <nav aria-label="Navegación principal" class="hidden items-center gap-8 font-pixel text-[10px] text-zinc-300 md:flex">
                <a href="#servicios" class="hover:text-pixel-yellow">Servicios</a>
                <a href="#metodologia" class="hover:text-pixel-yellow">Metodología</a>
                <a href="#sectores" class="hover:text-pixel-yellow">Sectores</a>
                <a href="#contacto" class="hover:text-pixel-yellow">Contacto</a>
            </nav>

            <a href="{{ route('home') }}" class="hidden font-retro text-lg text-zinc-400 hover:text-white lg:inline">
                ← Versión clásica
            </a>

            <button
                id="mobile-nav-toggle"
                type="button"
                class="pixel-box inline-flex items-center justify-center bg-pixel-panel p-2 text-zinc-200 md:hidden"
                aria-controls="mobile-nav-panel"
                aria-expanded="false"
            >
                <span class="sr-only">Abrir menú de navegación</span>
                <svg class="h-5 w-5" viewBox="0 0 8 7" shape-rendering="crispEdges" fill="currentColor" aria-hidden="true">
                    <rect x="0" y="0" width="8" height="1" />
                    <rect x="0" y="3" width="8" height="1" />
                    <rect x="0" y="6" width="8" height="1" />
                </svg>
            </button>
        </div>

        {{-- Mobile nav panel, toggled by resources/js/landing.js --}}
        <div id="mobile-nav-panel" class="hidden border-t-4 border-black bg-pixel-night px-4 py-6 md:hidden">
            <nav aria-label="Navegación móvil" class="flex flex-col gap-5 font-pixel text-xs text-zinc-300">
                <a href="#servicios" class="hover:text-pixel-yellow">▸ Servicios</a>
                <a href="#metodologia" class="hover:text-pixel-yellow">▸ Metodología</a>
                <a href="#sectores" class="hover:text-pixel-yellow">▸ Sectores</a>
                <a href="#contacto" class="text-pixel-yellow">▸ Contacto</a>
                <a href="{{ route('home') }}" class="font-retro text-xl text-zinc-400">← Versión clásica</a>
            </nav>
        </div>
    </header>

    <main>
        {{-- ============================== HERO ============================== --}}
        <section id="inicio" class="pixel-scanlines relative overflow-hidden">
            <div class="mx-auto grid max-w-7xl grid-cols-1 items-center gap-12 px-4 pt-16 pb-20 sm:pt-24 lg:grid-cols-5 lg:px-8">
                <div class="lg:col-span-3">
                    <p class="mb-6 font-pixel text-[10px] leading-relaxed text-pixel-yellow uppercase">
                        Investigación de mercados · Comunicación social · Gobierno
                    </p>

                    <h1 class="font-pixel text-2xl leading-snug text-white sm:text-4xl sm:leading-snug">
                        Evidencia rigurosa para decisiones que importan.<span class="pixel-blink text-electric" aria-hidden="true">_</span>
                    </h1>

                    <p class="mt-8 max-w-2xl text-2xl leading-snug text-zinc-300">
                        Diseñamos y ejecutamos estudios pre-test y post-test de campañas de comunicación social,
                        con estándares metodológicos exigidos por instituciones de gobierno federal y organismos públicos.
                    </p>

                    <div class="mt-10 flex flex-wrap items-center gap-6">
                        <a href="#contacto" class="pixel-btn inline-flex items-center gap-3 bg-pixel-yellow px-5 py-4 font-pixel text-xs text-pixel-night">
                            Solicitar información ▸
                        </a>
                        <a href="#metodologia" class="font-pixel text-[10px] text-zinc-300 underline decoration-4 decoration-electric underline-offset-8 hover:text-white">
                            Conoce nuestra metodología
                        </a>
                    </div>
                </div>

                {{-- Decorative pixel brand mark floating over a "level select" of the two study types --}}
                <div class="flex flex-col items-center gap-8 lg:col-span-2" aria-hidden="true">
                    <x-pixel-hexagon :grid="25" class="pixel-float h-44 w-44 sm:h-56 sm:w-56" />
                    <div class="pixel-box-shadow grid w-full max-w-sm grid-cols-2 gap-4 bg-pixel-panel p-4 font-pixel text-[9px] [--pixel-border:var(--color-electric)]">
                        <div>
                            <p class="text-zinc-400">NIVEL 1</p>
                            <p class="mt-2 text-white">PRE-TEST</p>
                            <p class="mt-2 font-retro text-lg text-pixel-yellow">¿Está lista para salir?</p>
                        </div>
                        <div>
                            <p class="text-zinc-400">NIVEL 2</p>
                            <p class="mt-2 text-white">POST-TEST</p>
                            <p class="mt-2 font-retro text-lg text-pixel-green">¿Funcionó?</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="pixel-divider"></div>
        </section>

        {{-- ============================== SERVICIOS ============================== --}}
        <section id="servicios" class="py-20 sm:py-28">
            <div class="mx-auto max-w-7xl px-4 lg:px-8">
                <div class="reveal max-w-3xl">
                    <p class="font-pixel text-[10px] text-pixel-yellow uppercase">▸ Servicios</p>
                    <h2 class="mt-5 font-pixel text-xl leading-snug text-white sm:text-2xl sm:leading-snug">
                        Estudios diseñados para sostener decisiones públicas.
                    </h2>
                    <p class="mt-5 text-2xl text-zinc-400">
                        Cada servicio incluye un diagrama que explica, paso a paso, cómo funciona.
                    </p>
                </div>

                <div class="mt-14 grid grid-cols-1 gap-10 lg:grid-cols-2">
                    @foreach ($services as $index => $service)
                        <article
                            class="reveal pixel-box-shadow flex flex-col gap-5 bg-pixel-panel/70 p-4 sm:p-8"
                            style="transition-delay: {{ ($index % 2) * 75 }}ms"
                        >
                            <div class="flex items-start gap-4">
                                <span class="pixel-box flex h-12 w-12 shrink-0 items-center justify-center bg-pixel-night [--pixel-border:var(--color-electric)]">
                                    <x-service-icon :name="$service['icon']" class="h-7 w-7 text-pixel-yellow" />
                                </span>
                                <div>
                                    <p class="font-pixel text-[9px] text-zinc-500">{{ sprintf('SERVICIO %02d', $index + 1) }}</p>
                                    <h3 class="mt-2 font-pixel text-sm leading-relaxed text-white">{{ $service['title'] }}</h3>
                                </div>
                            </div>

                            <p class="text-2xl leading-snug text-zinc-300">{{ $service['description'] }}</p>

                            <figure class="mt-auto">
                                <div class="pixel-box bg-pixel-night p-2 sm:p-4">
                                    <x-pixel-diagram :name="$service['icon']" :label="$service['diagram']" />
                                </div>
                                <figcaption class="mt-4 flex gap-2 text-xl leading-snug text-zinc-400">
                                    <span class="text-electric" aria-hidden="true">▸</span>
                                    <span><span class="font-pixel text-[9px] text-electric">CÓMO FUNCIONA:</span> {{ $service['diagram'] }}</span>
                                </figcaption>
                            </figure>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <div class="pixel-divider"></div>

        {{-- ============================== METODOLOGÍA ============================== --}}
        <section id="metodologia" class="pixel-scanlines py-20 sm:py-28">
            <div class="mx-auto max-w-7xl px-4 lg:px-8">
                <div class="reveal max-w-3xl">
                    <p class="font-pixel text-[10px] text-pixel-yellow uppercase">▸ Metodología</p>
                    <h2 class="mt-5 font-pixel text-xl leading-snug text-white sm:text-2xl sm:leading-snug">
                        Un proceso trazable, de principio a fin.
                    </h2>
                </div>

                {{-- The four steps as consecutive "levels" joined by pixel arrows --}}
                <ol class="mt-14 grid grid-cols-1 gap-12 sm:grid-cols-2 lg:grid-cols-4 lg:gap-10">
                    @foreach ($steps as $index => $step)
                        <li class="reveal relative" style="transition-delay: {{ $index * 75 }}ms">
                            <div class="pixel-box-shadow h-full bg-pixel-panel p-6 [--pixel-border:var(--color-electric)]">
                                <div class="flex items-center justify-between">
                                    <span class="font-pixel text-[10px] text-pixel-yellow">{{ sprintf('NIVEL %02d', $index + 1) }}</span>
                                    <x-service-icon :name="$step['icon']" class="h-7 w-7 text-white" />
                                </div>
                                <h3 class="mt-5 font-pixel text-xs leading-relaxed text-white">{{ $step['title'] }}</h3>
                                <p class="mt-3 text-xl leading-snug text-zinc-400">{{ $step['description'] }}</p>

                                {{-- Progress meter: how far along the study this step is --}}
                                <div class="mt-5 flex gap-1" aria-hidden="true">
                                    @for ($block = 0; $block < 4; $block++)
                                        <span class="h-3 flex-1 {{ $block <= $index ? 'bg-pixel-green' : 'bg-pixel-night' }}"></span>
                                    @endfor
                                </div>
                            </div>

                            @unless ($loop->last)
                                <svg class="absolute top-1/2 -right-9 hidden h-6 w-7 -translate-y-1/2 text-pixel-yellow lg:block" aria-hidden="true">
                                    <use href="#px-arrow-right" class="pixel-nudge" />
                                </svg>
                                <svg class="absolute -bottom-10 left-1/2 h-7 w-6 -translate-x-1/2 text-pixel-yellow sm:hidden" aria-hidden="true">
                                    <use href="#px-arrow-down" />
                                </svg>
                            @endunless
                        </li>
                    @endforeach
                </ol>

                {{-- Dos preguntas guían cada estudio: pre-test y post-test, antes y después de la campaña --}}
                <div class="mt-20 grid grid-cols-1 items-stretch gap-6 lg:grid-cols-[1fr_auto_1fr]">
                    <div class="reveal pixel-box-shadow bg-pixel-night p-6 sm:p-8 [--pixel-border:var(--color-pixel-yellow)]">
                        <span class="inline-block bg-pixel-yellow px-3 py-2 font-pixel text-[10px] text-pixel-night">PRE TEST</span>
                        <p class="mt-3 font-pixel text-[9px] text-zinc-500">ANTES DE LA CAMPAÑA</p>
                        <h3 class="mt-5 font-pixel text-sm leading-relaxed text-white">¿Está lista para salir?</h3>
                        <ul class="mt-5 space-y-2 text-xl text-zinc-300">
                            @foreach (['Comprensión del mensaje', 'Impacto y aceptación', 'Credibilidad y relevancia', 'Atribución y riesgos'] as $item)
                                <li class="flex items-center gap-3"><span class="h-2 w-2 shrink-0 bg-pixel-yellow"></span>{{ $item }}</li>
                            @endforeach
                        </ul>
                    </div>

                    {{-- The campaign itself, sitting between both studies on the timeline --}}
                    <div class="reveal flex flex-row items-center justify-center gap-4 lg:flex-col" style="transition-delay: 75ms">
                        <svg class="h-6 w-7 rotate-90 text-zinc-500 lg:rotate-0" aria-hidden="true"><use href="#px-arrow-right" /></svg>
                        <div class="pixel-box bg-electric px-4 py-5 text-center text-pixel-night">
                            <svg class="mx-auto h-8 w-9" viewBox="0 0 9 8" aria-hidden="true"><use href="#px-chat" /></svg>
                            <p class="mt-3 font-pixel text-[9px] leading-relaxed">CAMPAÑA<br>EN AIRE</p>
                        </div>
                        <svg class="h-6 w-7 rotate-90 text-zinc-500 lg:rotate-0" aria-hidden="true"><use href="#px-arrow-right" /></svg>
                    </div>

                    <div class="reveal pixel-box-shadow bg-pixel-night p-6 sm:p-8 [--pixel-border:var(--color-pixel-green)]" style="transition-delay: 150ms">
                        <span class="inline-block bg-pixel-green px-3 py-2 font-pixel text-[10px] text-pixel-night">POST TEST</span>
                        <p class="mt-3 font-pixel text-[9px] text-zinc-500">DESPUÉS DE LA CAMPAÑA</p>
                        <h3 class="mt-5 font-pixel text-sm leading-relaxed text-white">¿Funcionó?</h3>
                        <ul class="mt-5 space-y-2 text-xl text-zinc-300">
                            @foreach (['Exposición y recordación', 'Comprensión y atribución', 'Credibilidad y relevancia', 'Objetivos y metas'] as $item)
                                <li class="flex items-center gap-3"><span class="h-2 w-2 shrink-0 bg-pixel-green"></span>{{ $item }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                {{-- Técnicas y herramientas de investigación disponibles --}}
                <div class="reveal mt-14">
                    <p class="font-pixel text-[9px] text-zinc-500">INVENTARIO DE TÉCNICAS</p>
                    <div class="mt-5 flex flex-wrap gap-4">
                        @foreach (['Cualitativa', 'Cuantitativa', 'Digital', 'Desk research', 'Ciencias del comportamiento'] as $technique)
                            <span class="pixel-box bg-pixel-panel px-4 py-1 text-xl text-zinc-200">{{ $technique }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <div class="pixel-divider"></div>

        {{-- ============================== SECTORES ============================== --}}
        <section id="sectores" class="py-20 sm:py-28">
            <div class="mx-auto max-w-7xl px-4 lg:px-8">
                <div class="grid grid-cols-1 gap-14 lg:grid-cols-3">
                    <div class="reveal lg:col-span-1">
                        <p class="font-pixel text-[10px] text-pixel-yellow uppercase">▸ Sectores</p>
                        <h2 class="mt-5 font-pixel text-xl leading-snug text-white sm:text-2xl sm:leading-snug">
                            Un interlocutor técnico para el sector público.
                        </h2>
                    </div>

                    <div class="reveal lg:col-span-2">
                        <p class="max-w-3xl text-2xl leading-snug text-zinc-300">
                            Trabajamos con instituciones de gobierno federal, dependencias y organismos públicos
                            que requieren evidencia técnica confiable para evaluar y mejorar su comunicación social.
                            Operamos bajo los estándares de confidencialidad, trazabilidad metodológica y entrega
                            documentada que exige el sector público.
                        </p>

                        <ul class="mt-10 grid grid-cols-1 gap-x-8 gap-y-4 text-xl text-zinc-200 sm:grid-cols-2">
                            @foreach ($sectors as $sector)
                                <li class="flex items-center gap-3">
                                    <svg class="h-4 w-4 shrink-0 text-pixel-yellow" viewBox="0 0 8 7" aria-hidden="true"><use href="#px-arrow-right" /></svg>
                                    {{ $sector }}
                                </li>
                            @endforeach
                        </ul>

                        {{-- The three public-sector standards, as pixel "badges" --}}
                        <ul class="mt-12 grid grid-cols-1 gap-6 sm:grid-cols-3">
                            @foreach ([['Confidencialidad', 'px-lock', 'text-pixel-yellow'], ['Trazabilidad metodológica', 'px-path', 'text-pixel-pink'], ['Entrega documentada', 'px-doc', 'text-pixel-green']] as [$standard, $symbol, $color])
                                <li class="pixel-box flex items-center gap-4 bg-pixel-panel p-4">
                                    <svg class="h-9 w-8 shrink-0 {{ $color }}" aria-hidden="true"><use href="#{{ $symbol }}" /></svg>
                                    <span class="font-pixel text-[9px] leading-relaxed text-white">{{ $standard }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <div class="pixel-divider"></div>

        {{-- ============================== CONTACTO ============================== --}}
        <section id="contacto" class="pixel-scanlines py-20 sm:py-28">
            <div class="mx-auto max-w-7xl px-4 lg:px-8">
                <div class="grid grid-cols-1 gap-14 lg:grid-cols-5">
                    {{-- Intro + contact details --}}
                    <div class="reveal lg:col-span-2">
                        <p class="font-pixel text-[10px] text-pixel-yellow uppercase">▸ Contacto</p>
                        <h2 class="mt-5 font-pixel text-xl leading-snug text-white sm:text-2xl sm:leading-snug">
                            Solicita información sobre tu estudio.
                        </h2>
                        <p class="mt-6 text-2xl leading-snug text-zinc-300">
                            Cuéntanos el objetivo de tu campaña o el tipo de estudio que necesitas.
                            Un miembro de nuestro equipo te contactará para definir alcance y metodología.
                        </p>

                        <dl class="mt-10 space-y-4 text-xl text-zinc-300">
                            <div class="flex items-center gap-3">
                                <dt class="font-pixel text-[9px] text-zinc-500">CORREO</dt>
                                <dd><a href="mailto:comercial@hexagono-ci.com" class="text-pixel-yellow underline decoration-2 underline-offset-4 hover:text-white">comercial@hexagono-ci.com</a></dd>
                            </div>
                            <div class="flex items-center gap-3">
                                <dt class="font-pixel text-[9px] text-zinc-500">UBICACIÓN</dt>
                                <dd>Ciudad de México, México</dd>
                            </div>
                        </dl>
                    </div>

                    {{-- Form card --}}
                    <div class="reveal lg:col-span-3">
                        {{-- Non-JS fallback: flashed on a normal (non-fetch) redirect back --}}
                        @if (session('success'))
                            <div role="status" class="pixel-box mb-8 bg-pixel-panel px-4 py-3 text-xl text-pixel-green">
                                {{ session('success') }}
                            </div>
                        @endif

                        {{-- JS-driven status box, populated by resources/js/landing.js --}}
                        <div id="contact-form-status" role="status" aria-live="polite" class="hidden mb-8 border-4 bg-pixel-panel px-4 py-3 text-xl"></div>

                        <form id="contact-form" method="POST" action="{{ route('contact.store') }}" class="pixel-box-shadow bg-pixel-panel p-6 sm:p-8 [--pixel-border:var(--color-electric)]" novalidate>
                            @csrf

                            {{--
                                Anti-spam signals, checked by
                                StoreContactMessageRequest::looksLikeSpam() — the
                                same honeypot and encrypted render timestamp as
                                the classic landing page (welcome.blade.php).
                            --}}
                            <div class="absolute -left-[9999px] top-auto" aria-hidden="true">
                                <label for="website">Sitio web</label>
                                <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                            </div>
                            <input type="hidden" name="rendered_at" value="{{ encrypt(now()->timestamp) }}">

                            <div class="grid grid-cols-1 gap-7 sm:grid-cols-2">
                                @foreach ([
                                    ['name' => 'name', 'label' => 'Nombre completo', 'type' => 'text', 'required' => true, 'autocomplete' => 'name'],
                                    ['name' => 'institution', 'label' => 'Institución / Dependencia', 'type' => 'text', 'required' => false, 'autocomplete' => 'organization'],
                                    ['name' => 'email', 'label' => 'Correo electrónico', 'type' => 'email', 'required' => true, 'autocomplete' => 'email'],
                                    ['name' => 'phone', 'label' => 'Teléfono (opcional)', 'type' => 'tel', 'required' => false, 'autocomplete' => 'tel'],
                                ] as $field)
                                    <div>
                                        <label for="{{ $field['name'] }}" class="mb-3 block font-pixel text-[9px] leading-relaxed text-zinc-300">
                                            {{ $field['label'] }}
                                            @if ($field['required'])
                                                <span class="text-pixel-yellow" aria-hidden="true">*</span>
                                            @endif
                                        </label>
                                        <input
                                            id="{{ $field['name'] }}"
                                            name="{{ $field['name'] }}"
                                            type="{{ $field['type'] }}"
                                            value="{{ old($field['name']) }}"
                                            autocomplete="{{ $field['autocomplete'] }}"
                                            aria-describedby="{{ $field['name'] }}-error"
                                            @required($field['required'])
                                            class="{{ $inputClasses }}"
                                        >
                                        <p id="{{ $field['name'] }}-error" data-error-for="{{ $field['name'] }}" class="mt-2 text-lg text-red-400 {{ $errors->has($field['name']) ? '' : 'hidden' }}">
                                            {{ $errors->first($field['name']) }}
                                        </p>
                                    </div>
                                @endforeach

                                <div class="sm:col-span-2">
                                    <label for="study_type" class="mb-3 block font-pixel text-[9px] leading-relaxed text-zinc-300">
                                        Tipo de estudio <span class="text-pixel-yellow" aria-hidden="true">*</span>
                                    </label>
                                    <select
                                        id="study_type"
                                        name="study_type"
                                        required
                                        aria-describedby="study_type-error"
                                        class="{{ $inputClasses }}"
                                    >
                                        <option value="" disabled {{ old('study_type') ? '' : 'selected' }}>Selecciona una opción</option>
                                        @foreach (\App\Enums\StudyType::cases() as $studyType)
                                            <option value="{{ $studyType->value }}" @selected(old('study_type') === $studyType->value)>
                                                {{ $studyType->label() }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <p id="study_type-error" data-error-for="study_type" class="mt-2 text-lg text-red-400 {{ $errors->has('study_type') ? '' : 'hidden' }}">
                                        {{ $errors->first('study_type') }}
                                    </p>
                                </div>

                                <div class="sm:col-span-2">
                                    <label for="message" class="mb-3 block font-pixel text-[9px] leading-relaxed text-zinc-300">
                                        Mensaje <span class="text-pixel-yellow" aria-hidden="true">*</span>
                                    </label>
                                    <textarea
                                        id="message"
                                        name="message"
                                        rows="4"
                                        required
                                        maxlength="2000"
                                        aria-describedby="message-error"
                                        class="{{ $inputClasses }}"
                                    >{{ old('message') }}</textarea>
                                    <p id="message-error" data-error-for="message" class="mt-2 text-lg text-red-400 {{ $errors->has('message') ? '' : 'hidden' }}">
                                        {{ $errors->first('message') }}
                                    </p>
                                </div>
                            </div>

                            <button
                                type="submit"
                                class="pixel-btn mt-10 inline-flex w-full items-center justify-center bg-pixel-yellow px-6 py-4 font-pixel text-xs text-pixel-night disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto"
                            >
                                Enviar solicitud
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </main>

    {{-- ============================== FOOTER ============================== --}}
    <footer class="border-t-4 border-black bg-pixel-night py-10">
        <div class="mx-auto flex max-w-7xl flex-col gap-8 px-4 sm:flex-row sm:items-center sm:justify-between lg:px-8">
            <div class="flex items-center gap-3">
                <x-pixel-hexagon :grid="15" class="h-7 w-7" />
                <span class="flex flex-col gap-1 leading-none">
                    <span class="font-pixel text-[9px] text-zinc-300">HEXÁGONO</span>
                    <span class="font-pixel text-[6px] text-zinc-500">COMMUNICATION INTELLIGENCE</span>
                </span>
            </div>

            <nav aria-label="Redes sociales" class="flex items-center gap-5 text-zinc-500">
                <a href="#" class="hover:text-white" aria-label="LinkedIn de Hexágono Communication Intelligence">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M4.98 3.5a2.5 2.5 0 1 1 0 5 2.5 2.5 0 0 1 0-5ZM3 9h4v12H3V9Zm7 0h3.8v1.7h.05c.53-1 1.83-2.05 3.77-2.05 4.03 0 4.78 2.65 4.78 6.1V21h-4v-5.6c0-1.34-.02-3.06-1.87-3.06-1.87 0-2.16 1.46-2.16 2.96V21h-4V9Z"/></svg>
                </a>
                <a href="#" class="hover:text-white" aria-label="X (Twitter) de Hexágono Communication Intelligence">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.9 3H21l-6.56 7.5L22 21h-6.13l-4.8-6.28L5.6 21H3.5l7-8L2 3h6.28l4.34 5.74L18.9 3Zm-1.07 16h1.17L7.14 4.86H5.9L17.83 19Z"/></svg>
                </a>
            </nav>

            <p class="text-lg text-zinc-500">
                © {{ now()->year }} Hexágono Communication Intelligence. Todos los derechos reservados.
                <a href="#" class="ml-1 underline hover:text-zinc-300">Aviso de privacidad</a>
            </p>
        </div>
    </footer>
</body>
</html>
