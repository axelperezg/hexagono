{{--
    Landing page for Hexágono Research.

    Single Blade view (no SPA) rendered at the "/" route (routes/web.php).
    Sections, top to bottom: header, hero, servicios, metodología, sectores,
    formulario de contacto, footer. Styled with Tailwind CSS (resources/css/app.css)
    and progressively enhanced with vanilla JS (resources/js/landing.js) for the
    hero canvas, scroll reveals, the mobile nav and the no-reload contact form.

    The contact form still works with JavaScript disabled: it posts to
    route('contact.store') and this same view re-renders with $errors / the
    "success" flash message.
--}}
<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- SEO --}}
    <title>Hexágono Research — Investigación de mercados y evaluación de campañas de comunicación social</title>
    <meta name="description" content="Hexágono Research es una firma mexicana de investigación de mercados especializada en estudios pre-test y post-test de campañas de comunicación social para gobierno federal, dependencias y organismos públicos.">
    <meta name="robots" content="index, follow">

    {{-- Open Graph --}}
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Hexágono Research">
    <meta property="og:title" content="Hexágono Research — Evidencia rigurosa para decisiones que importan">
    <meta property="og:description" content="Estudios pre-test y post-test de campañas de comunicación social, investigación de opinión pública y consultoría de datos para instituciones de gobierno.">
    <meta property="og:url" content="{{ url('/') }}">
    <meta name="twitter:card" content="summary">

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    @fonts

    @vite(['resources/css/app.css', 'resources/js/landing.js'])
</head>
<body class="bg-ink font-sans text-zinc-100 antialiased selection:bg-electric selection:text-ink">

    {{-- ============================== HEADER ============================== --}}
    <header id="site-header" class="fixed inset-x-0 top-0 z-50">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-5 lg:px-8">
            <a href="#inicio" class="flex items-center gap-2.5">
                <x-app-hexagon-mark class="h-6 w-6 text-electric" />
                <span class="text-sm font-semibold tracking-[0.2em] text-white">HEXÁGONO RESEARCH</span>
            </a>

            <nav aria-label="Navegación principal" class="hidden items-center gap-10 text-sm text-zinc-300 md:flex">
                <a href="#servicios" class="underline-draw hover:text-white">Servicios</a>
                <a href="#metodologia" class="underline-draw hover:text-white">Metodología</a>
                <a href="#sectores" class="underline-draw hover:text-white">Sectores</a>
                <a href="#contacto" class="underline-draw hover:text-white">Contacto</a>
            </nav>

            <a
                href="#contacto"
                class="hidden rounded-sm border border-electric/40 px-4 py-2 text-sm font-medium text-electric transition hover:border-electric hover:bg-electric/10 md:inline-block"
            >
                Solicitar información
            </a>

            <button
                id="mobile-nav-toggle"
                type="button"
                class="inline-flex items-center justify-center rounded-sm p-2 text-zinc-300 hover:text-white md:hidden"
                aria-controls="mobile-nav-panel"
                aria-expanded="false"
            >
                <span class="sr-only">Abrir menú de navegación</span>
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path stroke-linecap="round" d="M3.5 6.5h17M3.5 12h17M3.5 17.5h17" />
                </svg>
            </button>
        </div>

        {{-- Mobile nav panel, toggled by resources/js/landing.js --}}
        <div id="mobile-nav-panel" class="hidden border-t border-white/10 bg-ink/95 px-6 py-6 backdrop-blur md:hidden">
            <nav aria-label="Navegación móvil" class="flex flex-col gap-5 text-base text-zinc-300">
                <a href="#servicios" class="hover:text-white">Servicios</a>
                <a href="#metodologia" class="hover:text-white">Metodología</a>
                <a href="#sectores" class="hover:text-white">Sectores</a>
                <a href="#contacto" class="font-medium text-electric">Contacto</a>
            </nav>
        </div>
    </header>

    <main>
        {{-- ============================== HERO ============================== --}}
        <section id="inicio" class="relative isolate flex min-h-screen items-center overflow-hidden border-b border-white/10">
            {{-- Animated node network, purely decorative --}}
            <canvas id="network-canvas" class="absolute inset-0 -z-10 h-full w-full" aria-hidden="true"></canvas>
            <div class="pointer-events-none absolute inset-0 -z-10 bg-gradient-to-b from-ink/20 via-ink/70 to-ink"></div>

            <div class="mx-auto max-w-5xl px-6 pt-32 pb-24 lg:px-8">
                <p class="mb-6 text-xs font-medium tracking-[0.3em] text-electric uppercase">
                    Investigación de mercados · Comunicación social · Gobierno
                </p>

                <h1 class="max-w-4xl text-4xl font-semibold tracking-tight text-white sm:text-6xl">
                    Evidencia rigurosa para decisiones que importan.
                </h1>

                <p class="mt-6 max-w-2xl text-lg leading-relaxed text-zinc-400">
                    Diseñamos y ejecutamos estudios pre-test y post-test de campañas de comunicación social,
                    con estándares metodológicos exigidos por instituciones de gobierno federal y organismos públicos.
                </p>

                <div class="mt-10 flex flex-wrap items-center gap-4">
                    <a
                        href="#contacto"
                        class="inline-flex items-center gap-2 rounded-sm bg-electric px-6 py-3 text-sm font-semibold text-ink transition hover:bg-white"
                    >
                        Solicitar información
                        <svg class="h-4 w-4" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.5 8h9M8.5 3.5 13 8l-4.5 4.5" />
                        </svg>
                    </a>
                    <a href="#metodologia" class="underline-draw text-sm font-medium text-zinc-300 hover:text-white">
                        Conoce nuestra metodología
                    </a>
                </div>
            </div>
        </section>

        {{-- ============================== SERVICIOS ============================== --}}
        <section id="servicios" class="border-b border-white/10 py-24 sm:py-32">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="reveal max-w-2xl">
                    <p class="text-xs font-medium tracking-[0.3em] text-electric uppercase">Servicios</p>
                    <h2 class="mt-4 text-3xl font-semibold tracking-tight text-white sm:text-4xl">
                        Estudios diseñados para sostener decisiones públicas.
                    </h2>
                </div>

                <div class="mt-16 grid grid-cols-1 gap-px overflow-hidden rounded-sm border border-white/10 bg-white/10 sm:grid-cols-2">
                    @foreach ([
                        [
                            'title' => 'Estudios pre-test de campañas',
                            'description' => 'Evaluamos conceptos, mensajes y piezas antes de su difusión: comprensión, recordación potencial y reacción de las audiencias objetivo.',
                            'icon' => 'eye',
                        ],
                        [
                            'title' => 'Estudios post-test / evaluación de impacto',
                            'description' => 'Medimos alcance, recordación, comprensión del mensaje y cambios de percepción una vez concluida la campaña.',
                            'icon' => 'chart',
                        ],
                        [
                            'title' => 'Investigación de opinión pública',
                            'description' => 'Encuestas y estudios cualitativos para entender percepciones, prioridades y niveles de confianza ciudadana.',
                            'icon' => 'chat',
                        ],
                        [
                            'title' => 'Consultoría en comunicación social y análisis de datos',
                            'description' => 'Acompañamiento estratégico y desarrollo de herramientas propias para el análisis y visualización de datos de comunicación.',
                            'icon' => 'grid',
                        ],
                    ] as $index => $service)
                        <article
                            class="reveal group relative flex flex-col gap-4 bg-ink p-8 transition-colors hover:bg-panel"
                            style="transition-delay: {{ $index * 75 }}ms"
                        >
                            <x-service-icon :name="$service['icon']" class="h-7 w-7 text-electric" />
                            <h3 class="text-lg font-semibold text-white">{{ $service['title'] }}</h3>
                            <p class="text-sm leading-relaxed text-zinc-400">{{ $service['description'] }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ============================== METODOLOGÍA ============================== --}}
        <section id="metodologia" class="grid-backdrop border-b border-white/10 bg-panel/40 py-24 sm:py-32">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="reveal max-w-2xl">
                    <p class="text-xs font-medium tracking-[0.3em] text-electric uppercase">Metodología</p>
                    <h2 class="mt-4 text-3xl font-semibold tracking-tight text-white sm:text-4xl">
                        Un proceso trazable, de principio a fin.
                    </h2>
                </div>

                <ol class="mt-16 grid grid-cols-1 gap-10 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ([
                        ['title' => 'Diseño muestral', 'description' => 'Definimos universo, marco muestral y tamaño de muestra según el nivel de precisión requerido.', 'icon' => 'target'],
                        ['title' => 'Levantamiento', 'description' => 'Aplicamos el instrumento en campo o en línea, con supervisión y controles de calidad continuos.', 'icon' => 'clipboard'],
                        ['title' => 'Análisis', 'description' => 'Procesamos y analizamos los datos con métodos estadísticos apropiados al objetivo del estudio.', 'icon' => 'bars'],
                        ['title' => 'Entrega de resultados', 'description' => 'Presentamos hallazgos y recomendaciones en reportes claros, listos para la toma de decisiones.', 'icon' => 'report'],
                    ] as $index => $step)
                        <li class="reveal relative" style="transition-delay: {{ $index * 75 }}ms">
                            <span class="text-sm font-mono text-electric">{{ sprintf('%02d', $index + 1) }}</span>
                            <x-service-icon :name="$step['icon']" class="mt-4 h-7 w-7 text-white" />
                            <h3 class="mt-4 text-base font-semibold text-white">{{ $step['title'] }}</h3>
                            <p class="mt-2 text-sm leading-relaxed text-zinc-400">{{ $step['description'] }}</p>
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>

        {{-- ============================== SECTORES ============================== --}}
        <section id="sectores" class="border-b border-white/10 py-24 sm:py-32">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="grid grid-cols-1 gap-16 lg:grid-cols-3">
                    <div class="reveal lg:col-span-1">
                        <p class="text-xs font-medium tracking-[0.3em] text-electric uppercase">Sectores</p>
                        <h2 class="mt-4 text-3xl font-semibold tracking-tight text-white sm:text-4xl">
                            Un interlocutor técnico para el sector público.
                        </h2>
                    </div>

                    <div class="reveal lg:col-span-2">
                        <p class="max-w-3xl text-lg leading-relaxed text-zinc-400">
                            Trabajamos con instituciones de gobierno federal, dependencias y organismos públicos
                            que requieren evidencia técnica confiable para evaluar y mejorar su comunicación social.
                            Operamos bajo los estándares de confidencialidad, trazabilidad metodológica y entrega
                            documentada que exige el sector público.
                        </p>

                        <ul class="mt-10 grid grid-cols-1 gap-x-8 gap-y-4 text-sm text-zinc-300 sm:grid-cols-2">
                            <li class="flex items-center gap-3">
                                <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-electric"></span>
                                Secretarías y dependencias de la administración pública federal
                            </li>
                            <li class="flex items-center gap-3">
                                <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-electric"></span>
                                Organismos públicos descentralizados
                            </li>
                            <li class="flex items-center gap-3">
                                <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-electric"></span>
                                Entidades y organismos autónomos
                            </li>
                            <li class="flex items-center gap-3">
                                <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-electric"></span>
                                Programas y campañas de alcance nacional
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        {{-- ============================== CONTACTO ============================== --}}
        <section id="contacto" class="py-24 sm:py-32">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="grid grid-cols-1 gap-16 lg:grid-cols-5">
                    {{-- Intro + contact details --}}
                    <div class="reveal lg:col-span-2">
                        <p class="text-xs font-medium tracking-[0.3em] text-electric uppercase">Contacto</p>
                        <h2 class="mt-4 text-3xl font-semibold tracking-tight text-white sm:text-4xl">
                            Solicita información sobre tu estudio.
                        </h2>
                        <p class="mt-6 text-sm leading-relaxed text-zinc-400">
                            Cuéntanos el objetivo de tu campaña o el tipo de estudio que necesitas.
                            Un miembro de nuestro equipo te contactará para definir alcance y metodología.
                        </p>

                        <dl class="mt-10 space-y-4 text-sm text-zinc-400">
                            <div class="flex items-center gap-3">
                                <dt class="sr-only">Correo</dt>
                                <dd><a href="mailto:contacto@hexagonoresearch.mx" class="underline-draw text-zinc-300 hover:text-white">contacto@hexagonoresearch.mx</a></dd>
                            </div>
                            <div class="flex items-center gap-3">
                                <dt class="sr-only">Teléfono</dt>
                                <dd>+52 (55) 0000 0000</dd>
                            </div>
                            <div class="flex items-center gap-3">
                                <dt class="sr-only">Ubicación</dt>
                                <dd>Ciudad de México, México</dd>
                            </div>
                        </dl>
                    </div>

                    {{-- Form card --}}
                    <div class="reveal lg:col-span-3">
                        {{-- Non-JS fallback: flashed on a normal (non-fetch) redirect back --}}
                        @if (session('success'))
                            <div role="status" class="mb-6 rounded-sm border border-electric/30 bg-electric/5 px-4 py-3 text-sm text-electric">
                                {{ session('success') }}
                            </div>
                        @endif

                        {{-- JS-driven status box, populated by resources/js/landing.js --}}
                        <div id="contact-form-status" role="status" aria-live="polite" class="hidden mb-6 rounded-sm border px-4 py-3 text-sm"></div>

                        <form id="contact-form" method="POST" action="{{ route('contact.store') }}" class="rounded-sm border border-white/10 bg-panel p-6 sm:p-8" novalidate>
                            @csrf

                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <x-contact-field name="name" label="Nombre completo" required autocomplete="name" />
                                <x-contact-field name="institution" label="Institución / Dependencia" autocomplete="organization" />
                                <x-contact-field name="email" label="Correo electrónico" type="email" required autocomplete="email" />
                                <x-contact-field name="phone" label="Teléfono (opcional)" type="tel" autocomplete="tel" />

                                <div class="sm:col-span-2">
                                    <label for="study_type" class="mb-2 block text-sm font-medium text-zinc-300">
                                        Tipo de estudio <span class="text-electric" aria-hidden="true">*</span>
                                    </label>
                                    <select
                                        id="study_type"
                                        name="study_type"
                                        required
                                        aria-describedby="study_type-error"
                                        class="w-full rounded-sm border border-white/15 bg-ink px-3 py-2.5 text-sm text-white outline-none transition focus:border-electric focus:ring-1 focus:ring-electric"
                                    >
                                        <option value="" disabled {{ old('study_type') ? '' : 'selected' }}>Selecciona una opción</option>
                                        @foreach (\App\Enums\StudyType::cases() as $studyType)
                                            <option value="{{ $studyType->value }}" @selected(old('study_type') === $studyType->value)>
                                                {{ $studyType->label() }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <p id="study_type-error" data-error-for="study_type" class="mt-1.5 text-xs text-red-400 {{ $errors->has('study_type') ? '' : 'hidden' }}">
                                        {{ $errors->first('study_type') }}
                                    </p>
                                </div>

                                <div class="sm:col-span-2">
                                    <label for="message" class="mb-2 block text-sm font-medium text-zinc-300">
                                        Mensaje <span class="text-electric" aria-hidden="true">*</span>
                                    </label>
                                    <textarea
                                        id="message"
                                        name="message"
                                        rows="4"
                                        required
                                        maxlength="2000"
                                        aria-describedby="message-error"
                                        class="w-full rounded-sm border border-white/15 bg-ink px-3 py-2.5 text-sm text-white outline-none transition focus:border-electric focus:ring-1 focus:ring-electric"
                                    >{{ old('message') }}</textarea>
                                    <p id="message-error" data-error-for="message" class="mt-1.5 text-xs text-red-400 {{ $errors->has('message') ? '' : 'hidden' }}">
                                        {{ $errors->first('message') }}
                                    </p>
                                </div>
                            </div>

                            <button
                                type="submit"
                                class="mt-8 inline-flex w-full items-center justify-center rounded-sm bg-electric px-6 py-3 text-sm font-semibold text-ink transition hover:bg-white disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto"
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
    <footer class="border-t border-white/10 py-12">
        <div class="mx-auto flex max-w-7xl flex-col gap-8 px-6 sm:flex-row sm:items-center sm:justify-between lg:px-8">
            <div class="flex items-center gap-2.5">
                <x-app-hexagon-mark class="h-5 w-5 text-electric" />
                <span class="text-xs font-medium tracking-[0.2em] text-zinc-400">HEXÁGONO RESEARCH</span>
            </div>

            <nav aria-label="Redes sociales" class="flex items-center gap-5 text-zinc-500">
                <a href="#" class="hover:text-white" aria-label="LinkedIn de Hexágono Research">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M4.98 3.5a2.5 2.5 0 1 1 0 5 2.5 2.5 0 0 1 0-5ZM3 9h4v12H3V9Zm7 0h3.8v1.7h.05c.53-1 1.83-2.05 3.77-2.05 4.03 0 4.78 2.65 4.78 6.1V21h-4v-5.6c0-1.34-.02-3.06-1.87-3.06-1.87 0-2.16 1.46-2.16 2.96V21h-4V9Z"/></svg>
                </a>
                <a href="#" class="hover:text-white" aria-label="X (Twitter) de Hexágono Research">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.9 3H21l-6.56 7.5L22 21h-6.13l-4.8-6.28L5.6 21H3.5l7-8L2 3h6.28l4.34 5.74L18.9 3Zm-1.07 16h1.17L7.14 4.86H5.9L17.83 19Z"/></svg>
                </a>
            </nav>

            <p class="text-xs text-zinc-500">
                © {{ now()->year }} Hexágono Research. Todos los derechos reservados.
                <a href="#" class="underline-draw ml-1 hover:text-zinc-300">Aviso de privacidad</a>
            </p>
        </div>
    </footer>

</body>
</html>
