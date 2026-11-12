@php
    $nombre = $institucion->nombre ?: 'R.A.S';
    $sigla = $institucion->sigla ?: 'R.A.S';
    $codigos = $institucion->codigos_modulares ?? [];

    $telefonos = array_values(array_filter([
        $institucion->telefono_central,
        $institucion->telefono_secundaria,
        $institucion->telefono_adicional,
    ]));

    // Texto de apoyo por nivel para las tarjetas.
    $descripcionNivel = [
        'Inicial' => 'Primeros pasos en un entorno seguro y afectuoso, donde el juego, la exploración y la formación en valores despiertan el gusto por aprender.',
        'Primaria' => 'Bases sólidas en comunicación, matemática, ciencia y fe, con acompañamiento personalizado y proyectos que despiertan la curiosidad.',
        'Secundaria' => 'Formación preuniversitaria con liderazgo, investigación y tecnología, preparando estudiantes íntegros para la vida y el servicio.',
    ];

    $ordenNiveles = ['Inicial', 'Primaria', 'Secundaria'];
@endphp
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $nombre }}{{ $institucion->ciudad ? ' | ' . $institucion->ciudad : '' }}</title>
    <meta name="description"
        content="{{ $institucion->lema ?: 'Educación integral con formación en valores.' }}">

    <link rel="icon" type="image/png" href="{{ url('images/favicon-ras.png') }}">
    <link rel="apple-touch-icon" href="{{ url('images/favicon-ras.png') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="stylesheet"
        href="https://fonts.bunny.net/css?family=montserrat:400,500,600,700,800|playfair+display:600,700,700i&display=swap">
    <link rel="stylesheet" href="{{ url('css/sitio.css') }}?v={{ filemtime(public_path('css/sitio.css')) }}">
</head>

<body class="sw">

    {{-- ══════════ Barra de contacto ══════════ --}}
    <div class="sw-topbar">
        <div class="sw-shell">
            <div class="sw-topbar__list">
                @foreach ($telefonos as $telefono)
                    <a class="sw-topbar__item" href="tel:{{ preg_replace('/\s+/', '', $telefono) }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round">
                            <path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2 4.2 2 2 0 0 1 4 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.1a2 2 0 0 1 2.1-.5c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"/>
                        </svg>
                        {{ $telefono }}
                    </a>
                @endforeach

                @if ($institucion->email)
                    <a class="sw-topbar__item" href="mailto:{{ $institucion->email }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round">
                            <rect x="2" y="4" width="20" height="16" rx="2"/>
                            <path d="m22 7-10 6L2 7"/>
                        </svg>
                        {{ $institucion->email }}
                    </a>
                @endif

                @if ($institucion->ciudad)
                    <span class="sw-topbar__item">
                        <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round">
                            <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                        {{ $institucion->ciudad }}, Perú
                    </span>
                @endif
            </div>

            @if ($institucion->himno_titulo)
                <span class="sw-topbar__lema">{{ $institucion->himno_titulo }}</span>
            @endif
        </div>
    </div>

    {{-- ══════════ Cabecera ══════════ --}}
    <header class="sw-header" id="swHeader">
        <div class="sw-shell">
            <a class="sw-brand" href="#inicio">
                <x-brand-logo :alt="$nombre" />
                <span>
                    <span class="sw-brand__name">{{ $sigla }}</span>
                    <span class="sw-brand__sub">Institución Educativa Privada</span>
                </span>
            </a>

            <nav class="sw-nav" id="swNav">
                <a class="sw-nav__link" href="#nosotros">Nosotros</a>
                <a class="sw-nav__link" href="#niveles">Niveles</a>
                <a class="sw-nav__link" href="#valores">Valores</a>
                <a class="sw-nav__link" href="#conocenos">Conócenos</a>
                <a class="sw-nav__link" href="#sedes">Sedes</a>
                <a class="sw-nav__link" href="#horarios">Horarios</a>
                <a class="sw-nav__link" href="#admision">Admisión</a>
                <a class="sw-btn sw-btn--navy" href="{{ route('login') }}">Intranet</a>
            </nav>

            <button class="sw-burger" id="swBurger" type="button" aria-label="Abrir menú" aria-expanded="false"
                aria-controls="swNav">
                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round">
                    <path d="M3 6h18M3 12h18M3 18h18"/>
                </svg>
            </button>
        </div>
    </header>

    {{-- ══════════ Portada ══════════ --}}
    <section class="sw-hero" id="inicio">
        <div class="sw-shell">
            <div>
                <span class="sw-eyebrow sw-eyebrow--light">
                    {{ $institucion->ciudad ? $institucion->ciudad . ' - Perú' : 'Perú' }}
                </span>

                <h1 class="sw-hero__title">
                    Formamos una generación de <span>líderes íntegros</span>
                </h1>

                @if ($institucion->lema)
                    <p class="sw-hero__lema">{{ $institucion->lema }}</p>
                @endif

                <div class="sw-hero__actions">
                    <a class="sw-btn sw-btn--gold"
                        href="{{ $campanaAdmision ? route('preinscripcion.formulario') : '#admision' }}">
                        {{ $campanaAdmision ? 'Inscríbete ' . $campanaAdmision->nombre : 'Proceso de admisión' }}
                        <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor"
                            stroke-width="2.4" stroke-linecap="round">
                            <path d="M5 12h14M13 6l6 6-6 6"/>
                        </svg>
                    </a>
                    <a class="sw-btn sw-btn--ghost" href="#nosotros">Conoce el colegio</a>
                </div>

                <div class="sw-hero__tags">
                    @foreach ($ordenNiveles as $nivel)
                        <span class="sw-tag">{{ $nivel }}</span>
                    @endforeach
                    <span class="sw-tag">Formación bíblica</span>
                    <span class="sw-tag">Preuniversitario</span>
                </div>
            </div>

            <div class="sw-hero__crest">
                <div class="sw-crest">
                    <span class="sw-crest__ring"></span>
                    <x-brand-logo :alt="'Escudo de ' . $nombre" class="" />
                </div>
            </div>
        </div>
    </section>

    {{-- ══════════ Cifras ══════════ --}}
    <div class="sw-stats">
        <div class="sw-shell">
            <div class="sw-stats__grid">
                <div class="sw-stat">
                    <span class="sw-stat__num">3</span>
                    <span class="sw-stat__label">Niveles educativos</span>
                </div>
                <div class="sw-stat">
                    <span class="sw-stat__num">{{ $sedes->count() ?: 2 }}</span>
                    <span class="sw-stat__label">Locales</span>
                </div>
                <div class="sw-stat">
                    <span class="sw-stat__num">{{ $valores->count() }}</span>
                    <span class="sw-stat__label">Valores institucionales</span>
                </div>
                <div class="sw-stat">
                    <span class="sw-stat__num">100<small>%</small></span>
                    <span class="sw-stat__label">Formación en valores</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════ Mision y vision ══════════ --}}
    <section class="sw-section" id="nosotros">
        <div class="sw-shell">
            <div class="sw-section__head sw-reveal">
                <span class="sw-eyebrow">Nuestra identidad</span>
                <h2>Una educación que forma la mente y el corazón</h2>
                <p>
                    En {{ $nombre }} acompañamos a cada estudiante con exigencia académica y
                    formación espiritual, para que crezca como una persona íntegra, capaz de servir
                    a su familia y a su comunidad.
                </p>
            </div>

            <div class="sw-mv">
                @if ($institucion->mision)
                    <article class="sw-mv__card sw-reveal">
                        <div class="sw-mv__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                                <circle cx="12" cy="12" r="6"/>
                                <circle cx="12" cy="12" r="2"/>
                            </svg>
                        </div>
                        <h3>Misión</h3>
                        <p>{{ $institucion->mision }}</p>
                    </article>
                @endif

                @if ($institucion->vision)
                    <article class="sw-mv__card sw-reveal">
                        <div class="sw-mv__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="2">
                                <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </div>
                        <h3>Visión</h3>
                        <p>{{ $institucion->vision }}</p>
                    </article>
                @endif
            </div>
        </div>
    </section>

    {{-- ══════════ Niveles ══════════ --}}
    <section class="sw-section" id="niveles" style="background:#fff;border-block:1px solid var(--sw-line);">
        <div class="sw-shell">
            <div class="sw-section__head sw-reveal">
                <span class="sw-eyebrow">Niveles educativos</span>
                <h2>Un camino completo, de los primeros años a la universidad</h2>
                <p>Acompañamos al estudiante durante toda su etapa escolar con una propuesta continua y coherente.</p>
            </div>

            <div class="sw-niveles">
                @foreach ($ordenNiveles as $i => $nivel)
                    @php
                        $horario = $horariosNivel[$nivel] ?? null;
                        $codigo = $codigos[$nivel] ?? null;
                    @endphp
                    <article class="sw-nivel sw-reveal">
                        <div class="sw-nivel__badge">{{ sprintf('%02d', $i + 1) }}</div>
                        <h3>{{ $nivel }}</h3>
                        <p>{{ $descripcionNivel[$nivel] ?? '' }}</p>

                        <dl class="sw-nivel__meta">
                            @if ($codigo)
                                <div class="sw-nivel__row">
                                    <dt>Código modular</dt>
                                    <dd>{{ $codigo }}</dd>
                                </div>
                            @endif
                            @if ($horario?->hora_ingreso)
                                <div class="sw-nivel__row">
                                    <dt>Ingreso</dt>
                                    <dd>{{ substr($horario->hora_ingreso, 0, 5) }}</dd>
                                </div>
                            @endif
                            @if ($horario?->almuerzo_inicio)
                                <div class="sw-nivel__row">
                                    <dt>Almuerzo</dt>
                                    <dd>{{ substr($horario->almuerzo_inicio, 0, 5) }} -
                                        {{ substr($horario->almuerzo_fin ?? '', 0, 5) }}</dd>
                                </div>
                            @endif
                            <div class="sw-nivel__row">
                                <dt>Sede</dt>
                                <dd>
                                    {{ $sedes->first(fn ($s) => $s->atiendeNivel($nivel))?->nombre ?? 'Por confirmar' }}
                                </dd>
                            </div>
                        </dl>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ══════════ Valores ══════════ --}}
    <div class="sw-valores-wrap">
        <section class="sw-section" id="valores">
            <div class="sw-shell">
                <div class="sw-section__head sw-reveal">
                    <span class="sw-eyebrow sw-eyebrow--light">Nuestros valores</span>
                    <h2>Lo que sostiene a la familia R.A.S</h2>
                    <p>Principios que se viven a diario en el aula, en casa y en la comunidad.</p>
                </div>

                <div class="sw-valores">
                    @foreach ($valores as $i => $valor)
                        <article class="sw-valor sw-reveal">
                            <span class="sw-valor__n">{{ sprintf('%02d', $i + 1) }}</span>
                            <h3>{{ $valor->nombre }}</h3>
                            @if ($valor->descripcion)
                                <p>{{ $valor->descripcion }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    </div>

    {{-- ══════════ Conocenos: fotos, videos e himno ══════════ --}}
    <section class="sw-section" id="conocenos" style="background:#fff;border-block:1px solid var(--sw-line);">
        <div class="sw-shell">
            <div class="sw-section__head sw-reveal">
                <span class="sw-eyebrow">Conócenos</span>
                <h2>Vive la experiencia R.A.S</h2>
                <p>Fotos, videos y el himno que nos representan como familia {{ $sigla }}.</p>
            </div>

            <div class="sw-tabs sw-reveal" role="tablist">
                <button class="sw-tab is-active" type="button" data-tab="fotos" role="tab" aria-selected="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="5" width="18" height="14" rx="2"/>
                        <circle cx="9" cy="10" r="2"/>
                        <path d="m21 16-4.5-4.5a2 2 0 0 0-2.8 0L5 20"/>
                    </svg>
                    Fotos
                </button>
                <button class="sw-tab" type="button" data-tab="videos" role="tab" aria-selected="false">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="5" width="15" height="14" rx="2"/>
                        <path d="m17 10 5-3v10l-5-3"/>
                    </svg>
                    Videos
                </button>
                <button class="sw-tab" type="button" data-tab="himno" role="tab" aria-selected="false">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 18V5l12-2v13"/>
                        <circle cx="6" cy="18" r="3"/>
                        <circle cx="18" cy="16" r="3"/>
                    </svg>
                    Himno
                </button>
            </div>

            {{-- Fotos --}}
            <div class="sw-tabpanel is-active" id="tab-fotos" role="tabpanel">
                <div class="sw-gallery">
                    @forelse (($fotos ?? []) as $foto)
                        <figure class="sw-gallery__item">
                            <img src="{{ asset($foto) }}" alt="Foto de {{ $nombre }}" loading="lazy">
                        </figure>
                    @empty
                        @for ($i = 0; $i < 6; $i++)
                            <figure class="sw-gallery__item sw-gallery__item--vacio">
                                <svg viewBox="0 0 24 24" fill="none" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="5" width="18" height="14" rx="2"/>
                                    <circle cx="9" cy="10" r="2"/>
                                    <path d="m21 16-4.5-4.5a2 2 0 0 0-2.8 0L5 20"/>
                                </svg>
                                <span>Foto próximamente</span>
                            </figure>
                        @endfor
                    @endforelse
                </div>
            </div>

            {{-- Videos --}}
            <div class="sw-tabpanel" id="tab-videos" role="tabpanel">
                @forelse (($videosTiktok ?? []) as $videoId)
                    @if ($loop->first)
                        <div class="sw-tiktok-grid">
                    @endif

                    <blockquote class="tiktok-embed" cite="https://www.tiktok.com/@ras/video/{{ $videoId }}"
                        data-video-id="{{ $videoId }}" style="max-width: 325px; min-width: 260px;">
                        <section></section>
                    </blockquote>

                    @if ($loop->last)
                        </div>
                    @endif
                @empty
                    <div class="sw-empty">
                        <svg viewBox="0 0 24 24" fill="none" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="5" width="15" height="14" rx="2"/>
                            <path d="m17 10 5-3v10l-5-3"/>
                        </svg>
                        <span>Videos próximamente</span>
                    </div>
                @endforelse
            </div>

            {{-- Himno --}}
            <div class="sw-tabpanel" id="tab-himno" role="tabpanel">
                <div class="sw-himno">
                    <h3>{{ $institucion->himno_titulo ?: 'Himno R.A.S' }}</h3>
                    @if ($institucion->himno_autor)
                        <p class="sw-himno__autor">Letra: {{ $institucion->himno_autor }}</p>
                    @endif

                    <div class="sw-himno__letra">
                        <p>
                            JESUCRISTO El gran maestro, hermoso<br>
                            Templo del saber, eres Señor y<br>
                            Maestro guardián de las letras doradas<br>
                            defendemos con justicia, sabiduría y verdad
                        </p>
                        <p class="sw-himno__coro">
                            ¡¡Adelante R.A.S<br>
                            Que la meta cerca está<br>
                            El triunfar es su destino<br>
                            Generación tras generación<br>
                            Será la Historia la que Ganará!!
                        </p>
                        <p>
                            Tu nombre R.A.S puesta a gran altura<br>
                            para que nos pueda guiar, alumbrando<br>
                            Por el valle oscuro, aunque sea dura<br>
                            ¡No temeré! A la cumbre sabemos llegar
                        </p>
                        <p class="sw-himno__coro">
                            ¡Adelante! R.A.S<br>
                            Que la meta cerca está<br>
                            El triunfar es su destino<br>
                            Generación tras generación<br>
                            Será la Historia la que Ganará.
                        </p>
                        <p>
                            ¡Oh! Gran Señor, Mártir del saber,<br>
                            Lucharemos cuando tu Espíritu<br>
                            Nos recordará que tu Reino será siempre Primero<br>
                            Y el futuro así nos juzgará
                        </p>
                        <p class="sw-himno__coro">
                            ¡¡Adelante R.A.S<br>
                            Que la meta cerca está<br>
                            El triunfar es su destino<br>
                            Generación tras generación<br>
                            Será la Historia la que Ganará!!
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ══════════ Frases institucionales ══════════ --}}
    @if ($frases->isNotEmpty())
        <div class="sw-frases" aria-label="Frases institucionales">
            <div class="sw-frases__track">
                @foreach ($frases->concat($frases) as $frase)
                    <span class="sw-frase">
                        {{ $frase->frase }}
                        @if ($frase->area)
                            <small>{{ $frase->area }}</small>
                        @endif
                    </span>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ══════════ Sedes ══════════ --}}
    <section class="sw-section" id="sedes">
        <div class="sw-shell">
            <div class="sw-section__head sw-reveal">
                <span class="sw-eyebrow">Nuestros locales</span>
                <h2>Dónde encontrarnos</h2>
                <p>Contamos con locales diferenciados para acompañar mejor a cada etapa escolar.</p>
            </div>

            <div class="sw-sedes">
                @foreach ($sedes as $sede)
                    <article class="sw-sede sw-reveal">
                        <div class="sw-sede__top">
                            <h3>{{ $sede->nombre }}</h3>
                            @if ($sede->referencia)
                                <span class="sw-sede__ref">{{ $sede->referencia }}</span>
                            @endif
                        </div>

                        @if ($sede->direccion)
                            <div class="sw-sede__line">
                                <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round">
                                    <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0z"/>
                                    <circle cx="12" cy="10" r="3"/>
                                </svg>
                                <span>{{ $sede->direccion }}@if ($institucion->ciudad), {{ $institucion->ciudad }}@endif</span>
                            </div>
                        @endif

                        @if ($sede->telefono)
                            <div class="sw-sede__line">
                                <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round">
                                    <path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2 4.2 2 2 0 0 1 4 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.1a2 2 0 0 1 2.1-.5c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"/>
                                </svg>
                                <a href="tel:{{ preg_replace('/\s+/', '', $sede->telefono) }}">{{ $sede->telefono }}</a>
                            </div>
                        @endif

                        @if (!empty($sede->niveles))
                            <div class="sw-chips">
                                @foreach ($sede->niveles as $nivel)
                                    <span class="sw-chip">{{ $nivel }}</span>
                                @endforeach
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ══════════ Horarios ══════════ --}}
    <section class="sw-section" id="horarios" style="background:#fff;border-block:1px solid var(--sw-line);">
        <div class="sw-shell">
            <div class="sw-section__head sw-reveal">
                <span class="sw-eyebrow">Jornada escolar</span>
                <h2>Horarios de ingreso y salida</h2>
                <p>La salida de Primaria es escalonada, grado por grado, para una entrega ordenada y segura.</p>
            </div>

            <div class="sw-horarios">
                <div class="sw-panel sw-reveal">
                    <div class="sw-panel__head">
                        <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round">
                            <circle cx="12" cy="12" r="9"/>
                            <path d="M12 7v5l3 2"/>
                        </svg>
                        <h3>Ingreso y almuerzo</h3>
                    </div>
                    <table class="sw-table">
                        <thead>
                            <tr>
                                <th>Nivel</th>
                                <th>Ingreso</th>
                                <th>Almuerzo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($horariosNivel as $nivel => $horario)
                                <tr>
                                    <td>{{ $nivel }}</td>
                                    <td>{{ $horario->hora_ingreso ? substr($horario->hora_ingreso, 0, 5) : '-' }}</td>
                                    <td>
                                        @if ($horario->almuerzo_inicio)
                                            {{ substr($horario->almuerzo_inicio, 0, 5) }} -
                                            {{ substr($horario->almuerzo_fin ?? '', 0, 5) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3">Horarios por publicar.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="sw-panel sw-reveal">
                    <div class="sw-panel__head">
                        <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                            <path d="m16 17 5-5-5-5M21 12H9"/>
                        </svg>
                        <h3>Salida escalonada</h3>
                    </div>
                    <table class="sw-table">
                        <thead>
                            <tr>
                                <th>Grado</th>
                                <th>Salida</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($salidas as $horario)
                                <tr>
                                    <td>{{ $horario->grado?->nombre ?? $horario->nivel }}</td>
                                    <td>{{ substr($horario->hora_salida, 0, 5) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="2">Horarios por publicar.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    @if ($salidas->isNotEmpty())
                        <p class="sw-nota">
                            De 6to de Primaria a 5to de Secundaria la salida es a las 3:30 p.m.
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- ══════════ Admision ══════════ --}}
    <section class="sw-section" id="admision">
        <div class="sw-shell">
            <div class="sw-cta sw-reveal">
                <span class="sw-eyebrow sw-eyebrow--light" style="justify-content:center;">
                    Admisión {{ $campanaAdmision->nombre ?? '' }}
                </span>
                @if ($campanaAdmision)
                    <h2>Reserva la vacante de tu hijo para el {{ $campanaAdmision->nombre }}</h2>
                    <p>
                        Deja los datos en el formulario y tu solicitud queda registrada en nuestro sistema de
                        admisión. No es una matrícula: secretaría te contactará para continuar el proceso y
                        firmar la matrícula en el colegio.
                        @if ($campanaAdmision->admision_cierre)
                            Recibimos solicitudes hasta el
                            {{ $campanaAdmision->admision_cierre->format('d/m/Y') }}.
                        @endif
                    </p>
                @else
                    <h2>Postula a {{ $sigla }}</h2>
                    <p>
                        Comunícate con nosotros para conocer las vacantes disponibles, los requisitos de
                        matrícula y agendar una visita a nuestros locales.
                    </p>
                @endif
                <div class="sw-cta__actions">
                    @if ($campanaAdmision)
                        <a class="sw-btn sw-btn--gold" href="{{ route('preinscripcion.formulario') }}">
                            Inscribirme en línea
                        </a>
                        <a class="sw-btn sw-btn--ghost" href="{{ route('preinscripcion.consulta') }}">
                            Consultar mi inscripción
                        </a>
                    @endif
                    @if (!empty($telefonos[0]))
                        <a class="sw-btn sw-btn--gold"
                            href="https://wa.me/51{{ preg_replace('/\D+/', '', $telefonos[0]) }}" target="_blank"
                            rel="noopener">
                            Escríbenos por WhatsApp
                        </a>
                        <a class="sw-btn sw-btn--ghost"
                            href="tel:{{ preg_replace('/\s+/', '', $telefonos[0]) }}">
                            Llamar {{ $telefonos[0] }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- ══════════ Pie ══════════ --}}
    <footer class="sw-footer">
        <div class="sw-shell">
            <div class="sw-footer__grid">
                <div>
                    <a class="sw-brand" href="#inicio">
                        <x-brand-logo :alt="$nombre" />
                        <span>
                            <span class="sw-brand__name">{{ $sigla }}</span>
                            <span class="sw-brand__sub">Institución Educativa Privada</span>
                        </span>
                    </a>
                    <p>{{ $nombre }}@if ($institucion->ciudad) - {{ $institucion->ciudad }}, Perú @endif</p>
                    @if ($institucion->himno_titulo)
                        <p style="color:var(--sw-gold-300);font-style:italic;">
                            {{ $institucion->himno_titulo }}@if ($institucion->himno_autor), letra de {{ $institucion->himno_autor }}@endif
                        </p>
                    @endif
                </div>

                <div>
                    <h4>Códigos modulares</h4>
                    <ul>
                        @forelse ($codigos as $nivel => $codigo)
                            <li>{{ $nivel }}: <strong>{{ $codigo }}</strong></li>
                        @empty
                            <li>Por publicar</li>
                        @endforelse
                    </ul>
                </div>

                <div>
                    <h4>Contacto</h4>
                    <ul>
                        @foreach ($telefonos as $telefono)
                            <li><a href="tel:{{ preg_replace('/\s+/', '', $telefono) }}">{{ $telefono }}</a></li>
                        @endforeach
                        @if ($institucion->email)
                            <li><a href="mailto:{{ $institucion->email }}">{{ $institucion->email }}</a></li>
                        @endif
                        <li><a href="{{ route('login') }}">Intranet</a></li>
                    </ul>
                </div>
            </div>

            <div class="sw-footer__bottom">
                <span>&copy; {{ date('Y') }} {{ $nombre }}. Todos los derechos reservados.</span>
                <span>{{ $institucion->lema }}</span>
            </div>
        </div>
    </footer>

    {{-- ══════════ Aviso de admision al entrar ══════════ --}}
    @if ($campanaAdmision)
        <div class="sw-modal" id="swAviso" role="dialog" aria-modal="true" aria-labelledby="swAvisoTitulo" hidden>
            <div class="sw-modal__card">
                <button type="button" class="sw-modal__cerrar" id="swAvisoCerrar" aria-label="Cerrar aviso">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor"
                        stroke-width="2.2" stroke-linecap="round">
                        <path d="M18 6 6 18M6 6l12 12"/>
                    </svg>
                </button>

                @if ($bannerAdmision)
                    <div class="sw-modal__banner">
                        <img src="{{ url($bannerAdmision) }}" alt="Admisión {{ $campanaAdmision->nombre }}">
                    </div>
                @else
                    <div class="sw-modal__banner sw-modal__banner--dibujado">
                        <img class="sw-modal__escudo" src="{{ url('images/logo-ras.png') }}" alt="{{ $nombre }}">
                        <h2 class="sw-modal__anio serif">Admisión {{ $campanaAdmision->nombre }}</h2>
                        <p class="sw-modal__lema">{{ $institucion->lema ?: 'Formando líderes íntegros' }}</p>
                    </div>
                @endif

                <div class="sw-modal__body">
                    <h3 class="sw-modal__titulo serif" id="swAvisoTitulo">
                        Vacantes abiertas para el {{ $campanaAdmision->nombre }}
                    </h3>
                    <p class="sw-modal__texto">
                        Inscribe a tu hijo en línea y reserva su vacante en pocos minutos.
                        La matrícula se firma después, aquí en el colegio.
                    </p>

                    @if ($campanaAdmision->admision_cierre)
                        <span class="sw-modal__cierre">
                            Hasta el {{ $campanaAdmision->admision_cierre->format('d/m/Y') }}
                        </span>
                    @endif

                    <div class="sw-modal__acciones">
                        <a class="sw-btn sw-btn--gold" href="{{ route('preinscripcion.formulario') }}">
                            Inscribirme ahora
                        </a>
                        <button type="button" class="sw-modal__luego" id="swAvisoLuego">Ahora no, gracias</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if (!empty($videosTiktok))
        <script async src="https://www.tiktok.com/embed.js"></script>
    @endif

    <script>
        // Menu movil
        var burger = document.getElementById('swBurger');
        var nav = document.getElementById('swNav');

        burger.addEventListener('click', function () {
            var abierto = nav.classList.toggle('is-open');
            burger.setAttribute('aria-expanded', abierto ? 'true' : 'false');
        });

        nav.addEventListener('click', function (e) {
            if (e.target.closest('a')) {
                nav.classList.remove('is-open');
                burger.setAttribute('aria-expanded', 'false');
            }
        });

        // Pestanas de Conocenos (fotos / videos / himno)
        var tabs = document.querySelectorAll('.sw-tab');
        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                var destino = tab.getAttribute('data-tab');

                tabs.forEach(function (t) {
                    t.classList.remove('is-active');
                    t.setAttribute('aria-selected', 'false');
                });
                tab.classList.add('is-active');
                tab.setAttribute('aria-selected', 'true');

                document.querySelectorAll('.sw-tabpanel').forEach(function (panel) {
                    panel.classList.toggle('is-active', panel.id === 'tab-' + destino);
                });
            });
        });

        // Sombra de la cabecera al hacer scroll
        var header = document.getElementById('swHeader');
        var alScroll = function () {
            header.classList.toggle('is-stuck', window.scrollY > 12);
        };
        alScroll();
        window.addEventListener('scroll', alScroll, { passive: true });

        // Aparicion progresiva de las secciones
        var reveals = document.querySelectorAll('.sw-reveal');

        if ('IntersectionObserver' in window) {
            var observador = new IntersectionObserver(function (entradas) {
                entradas.forEach(function (entrada) {
                    if (entrada.isIntersecting) {
                        entrada.target.classList.add('is-in');
                        observador.unobserve(entrada.target);
                    }
                });
            }, { threshold: 0.12, rootMargin: '0px 0px -60px' });

            reveals.forEach(function (el) { observador.observe(el); });
        } else {
            reveals.forEach(function (el) { el.classList.add('is-in'); });
        }

        // Aviso de admision: se abre solo al entrar a la portada.
        (function () {
            var aviso = document.getElementById('swAviso');
            if (!aviso) return;

            var campana = @json($campanaAdmision?->nombre);
            var ultimoFoco = null;

            // Se borra la marca de versiones anteriores, que dejaba el aviso
            // callado un dia entero.
            try { localStorage.removeItem('swAviso:' + campana); } catch (e) {}

            function abrir() {
                ultimoFoco = document.activeElement;
                aviso.hidden = false;
                document.body.style.overflow = 'hidden';

                // Un cuadro que reserva su espacio antes de animar. El foco
                // espera a que sea visible, o el navegador lo ignora.
                requestAnimationFrame(function () {
                    aviso.classList.add('is-open');
                    // El foco espera un frame mas: recien ahi es visible.
                    requestAnimationFrame(function () {
                        aviso.querySelector('.sw-btn').focus();
                    });
                });
            }

            function cerrar() {
                aviso.classList.remove('is-open');
                document.body.style.overflow = '';
                setTimeout(function () { aviso.hidden = true; }, 300);
                if (ultimoFoco) ultimoFoco.focus();
            }

            document.getElementById('swAvisoCerrar').addEventListener('click', cerrar);
            document.getElementById('swAvisoLuego').addEventListener('click', cerrar);

            // Clic en el fondo, fuera de la tarjeta.
            aviso.addEventListener('click', function (e) {
                if (e.target === aviso) cerrar();
            });

            document.addEventListener('keydown', function (e) {
                if (!aviso.classList.contains('is-open')) return;

                if (e.key === 'Escape') {
                    cerrar();
                    return;
                }

                // El tabulador no se escapa del cuadro mientras esta abierto.
                if (e.key !== 'Tab') return;

                var focos = aviso.querySelectorAll('a[href], button');
                var primero = focos[0];
                var ultimo = focos[focos.length - 1];

                if (e.shiftKey && document.activeElement === primero) {
                    e.preventDefault();
                    ultimo.focus();
                } else if (!e.shiftKey && document.activeElement === ultimo) {
                    e.preventDefault();
                    primero.focus();
                }
            });

            // La campana se anuncia en cada visita a la portada, sin recordar
            // cierres anteriores: el colegio quiere que siempre se vea.
            setTimeout(abrir, 900);
        })();
    </script>
</body>

</html>
