@extends('layouts.mantis')

@section('title', 'Panel Estudiante | Sistema de Raul')
@section('page-title', 'Panel Estudiante')
@section('body-class', 'role-estudiante')

@push('styles')
    <link rel="stylesheet" href="{{ url('css/secretaria-dashboard.css') }}">
    <style>
        /* ===== Variables de marca ===== */
        :root {
            --jp-blue:    #213095;
            --jp-blue-d:  #1c2a81;
            --jp-blue-dd: #172167;
            --jp-amarillo: #23339e;
            --jp-cyan:    #22329c;
            --jp-cyan-d:  #1f2e8e;
        }

        /* ===== Fondo de página con toque cálido ===== */
        body.role-estudiante .pc-content {
            max-width: 1100px;
            margin: 0 auto;
        }
        body.role-estudiante {
            background: linear-gradient(160deg, #f8fbff 0%, #f0fbff 50%, #fff 100%);
            min-height: 100vh;
        }

        /* ===== Hero del Estudiante ===== */
        .est-hero {
            background: linear-gradient(135deg,
                var(--jp-blue-dd) 0%,
                var(--jp-blue)    40%,
                #23339e           75%,
                var(--jp-amarillo) 100%);
            border-radius: 24px;
            padding: 32px 36px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 28px;
            position: relative;
            overflow: hidden;
            box-shadow:
                0 24px 50px rgba(33, 48, 149, 0.35),
                0 6px 16px  rgba(33, 48, 149, 0.2),
                inset 0 1px 0 rgba(255,255,255,0.15);
        }
        /* Círculos decorativos más vivos */
        .est-hero::before {
            content: '';
            position: absolute;
            top: -60px; right: -40px;
            width: 280px; height: 280px;
            background: radial-gradient(circle, rgba(35, 51, 158,0.22) 0%, transparent 70%);
            border-radius: 50%;
        }
        .est-hero::after {
            content: '';
            position: absolute;
            bottom: -80px; left: 50%;
            width: 220px; height: 220px;
            background: radial-gradient(circle, rgba(255,255,255,0.12) 0%, transparent 70%);
            border-radius: 50%;
        }
        /* Tercer adorno amarillo */
        .est-hero__deco {
            position: absolute;
            top: 10px; left: 30%;
            width: 120px; height: 120px;
            background: radial-gradient(circle, rgba(35, 51, 158,0.15) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .est-hero__left { display: flex; align-items: center; gap: 20px; z-index: 1; min-width: 0; }

        .est-hero__avatar {
            width: 70px; height: 70px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(255,255,255,0.35), rgba(255,255,255,0.15));
            border: 2.5px solid rgba(255,255,255,0.6);
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 1.7rem; font-weight: 800; color: #fff;
            flex-shrink: 0;
            text-transform: uppercase;
            box-shadow: 0 4px 14px rgba(0,0,0,0.18);
        }

        .est-hero__greeting {
            font-size: 0.78rem; font-weight: 700;
            color: rgba(255,255,255,0.85);
            text-transform: uppercase; letter-spacing: 1.2px;
            margin: 0 0 4px;
        }
        .est-hero__name {
            font-size: 1.5rem; font-weight: 900;
            color: #fff; margin: 0 0 5px;
            line-height: 1.2;
            text-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
        .est-hero__sub {
            font-size: 0.83rem;
            color: rgba(255,255,255,0.88);
            margin: 0;
        }

        /* Stats en hero: fondo amarillo-dorado */
        .est-hero__stats {
            display: flex; gap: 10px; flex-wrap: wrap;
            z-index: 1; flex-shrink: 0;
        }
        .est-hero__stat {
            background: rgba(0,0,0,0.22);
            border: 1.5px solid rgba(255,255,255,0.28);
            border-radius: 14px;
            padding: 12px 18px;
            text-align: center; min-width: 85px;
            backdrop-filter: blur(6px);
            transition: background 0.2s;
        }
        .est-hero__stat:hover {
            background: rgba(35, 51, 158,0.28);
            border-color: rgba(35, 51, 158,0.6);
        }
        .est-hero__stat-num {
            display: block; font-size: 1.45rem; font-weight: 900;
            color: #fff; line-height: 1;
            text-shadow: 0 1px 4px rgba(0,0,0,0.2);
        }
        .est-hero__stat-label {
            display: block; font-size: 0.7rem;
            color: rgba(255,255,255,0.85);
            font-weight: 700; margin-top: 4px;
            text-transform: uppercase; letter-spacing: 0.5px;
        }

        /* ===== Aviso sin perfil ===== */
        .est-welcome-msg {
            display: flex; align-items: center; gap: 12px;
            background: #cdf1ff;
            border: 1.5px solid #23339e;
            border-radius: 14px; padding: 14px 18px;
            margin-bottom: 24px;
        }
        .est-welcome-msg__icon { color: var(--jp-cyan-d); flex-shrink: 0; }
        .est-welcome-msg__icon i { width: 18px; height: 18px; }
        .est-welcome-msg p { margin: 0; font-size: 0.88rem; color: #151f5f; font-weight: 600; }

        /* ===== Grid ===== */
        .est-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: clamp(16px, 2.5vw, 24px);
        }

        /* ===== Card rediseñada ===== */
        .est-card {
            position: relative; overflow: hidden;
            border-radius: 20px; padding: 0;
            background: #fff;
            border: 1.5px solid rgba(0,0,0,0.07);
            box-shadow: 0 6px 20px rgba(0,0,0,0.07);
            text-decoration: none;
            color: #06081a;
            display: flex; flex-direction: column;
            animation: est-fade 0.55s ease forwards;
            animation-delay: var(--delay, 0s);
            opacity: 0;
            transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
            cursor: pointer;
        }
        .est-card:hover {
            transform: translateY(-6px) scale(1.01);
            box-shadow:
                0 20px 40px rgba(33, 48, 149, 0.16),
                0 4px 10px  rgba(0,0,0,0.06);
            border-color: var(--card-accent, #213095);
            text-decoration: none;
            color: #06081a;
        }

        /* Barra superior más gruesa y vivaz */
        .est-card__bar {
            height: 6px;
            background: var(--card-accent, #213095);
            border-radius: 20px 20px 0 0;
        }

        .est-card__body {
            padding: 22px 22px 18px;
            display: flex; flex-direction: column;
            gap: 14px; flex: 1;
        }

        .est-card__top {
            display: flex; align-items: flex-start;
            justify-content: space-between; gap: 12px;
        }

        /* Ícono con fondo más saturado */
        .est-card__icon {
            width: 54px; height: 54px;
            border-radius: 15px;
            display: inline-flex; align-items: center; justify-content: center;
            background: var(--card-accent-soft, #f0f7ff);
            color: var(--card-accent, #213095);
            flex-shrink: 0;
            border: 1.5px solid var(--card-accent, #213095);
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .est-card:hover .est-card__icon {
            transform: scale(1.08) rotate(-3deg);
            box-shadow: 0 6px 16px rgba(0,0,0,0.14);
        }
        .est-card__icon i { width: 23px; height: 23px; }

        .est-card__stat-wrap { text-align: right; }
        .est-card__stat {
            font-size: 2rem; font-weight: 900;
            color: var(--card-accent, #213095);
            line-height: 1;
            display: block;
        }
        .est-card__stat-label {
            font-size: 0.7rem; font-weight: 700;
            color: #aaa; text-transform: uppercase;
            letter-spacing: 0.6px; margin-top: 2px;
            display: block;
        }

        .est-card__title {
            font-family: "Poppins", sans-serif;
            font-size: 1.05rem; font-weight: 800;
            color: #06081a; margin: 0;
        }
        .est-card__desc {
            font-size: 0.84rem; color: #505b6b;
            margin: 0; line-height: 1.55;
        }

        .est-card__footer {
            margin-top: auto;
            display: flex; align-items: center; justify-content: space-between;
            padding: 11px 22px;
            border-top: 1.5px solid rgba(0,0,0,0.05);
            background: #fafafa;
            border-radius: 0 0 20px 20px;
        }
        .est-card__cta {
            font-size: 0.82rem; font-weight: 700;
            color: var(--card-accent, #213095);
            display: inline-flex; align-items: center; gap: 5px;
            transition: gap 0.2s;
        }
        .est-card:hover .est-card__cta { gap: 8px; }
        .est-card__cta i { width: 13px; height: 13px; }

        .est-card__badge {
            font-size: 0.72rem; font-weight: 700;
            padding: 4px 12px; border-radius: 999px;
            background: var(--card-accent-soft, #f0f7ff);
            color: var(--card-accent, #213095);
            border: 1.5px solid var(--card-accent, #213095);
        }

        /* ===== Card Logout — rojo-amarillo vibrante ===== */
        .est-card--logout {
            background: linear-gradient(135deg,
                var(--jp-blue-dd) 0%,
                var(--jp-blue)    55%,
                #23339e           100%);
            border: none;
            box-shadow:
                0 16px 36px rgba(33, 48, 149, 0.38),
                0 4px 12px  rgba(33, 48, 149, 0.2);
        }
        .est-card--logout .est-card__bar {
            background: rgba(255,255,255,0.25);
        }
        .est-card--logout .est-card__body { gap: 10px; }
        .est-card--logout .est-card__icon {
            background: rgba(255,255,255,0.22);
            border-color: rgba(255,255,255,0.4);
            color: #fff;
        }
        .est-card--logout:hover .est-card__icon {
            background: rgba(255,255,255,0.32);
        }
        .est-card--logout .est-card__title { color: #fff; font-size: 1.1rem; }
        .est-card--logout .est-card__desc  { color: rgba(255,255,255,0.85); }
        .est-card--logout .est-card__footer {
            background: rgba(0,0,0,0.18);
            border-top-color: rgba(255,255,255,0.18);
        }
        .est-card--logout .est-card__cta {
            color: #fff;
            font-size: 0.88rem;
            background: rgba(255,255,255,0.2);
            padding: 8px 20px; border-radius: 999px;
            border: 1.5px solid rgba(255,255,255,0.35);
            width: 100%; justify-content: center;
            transition: background 0.2s, border-color 0.2s;
        }
        .est-card--logout:hover .est-card__cta {
            background: rgba(255,255,255,0.3);
            border-color: rgba(255,255,255,0.55);
        }
        .est-card--logout:hover {
            transform: translateY(-6px) scale(1.01);
            box-shadow:
                0 28px 48px rgba(33, 48, 149, 0.45),
                0 6px 16px  rgba(33, 48, 149, 0.25);
        }

        /* Deuda con saldo pendiente */
        .est-deuda-alert {
            display: inline-flex; align-items: center; gap: 5px;
            background: #e2eefe; color: #1c2a81;
            border: 1.5px solid rgba(28, 42, 129,0.3);
            border-radius: 999px; padding: 4px 11px;
            font-size: 0.72rem; font-weight: 800;
        }
        .est-deuda-alert i { width: 11px; height: 11px; }

        @keyframes est-fade {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 575px) {
            .est-hero { flex-direction: column; padding: 24px 20px; }
            .est-hero__stats { justify-content: center; width: 100%; }
            .est-hero__left { flex-direction: column; text-align: center; }
        }
    </style>
@endpush

@section('sidebar')
@endsection

@section('content')
    @php
        $nombreEstudiante = $estudiante
            ? trim($estudiante->nombres . ' ' . $estudiante->apellidos)
            : (auth()->user()->name ?? 'Estudiante');

        $iniciales = collect(explode(' ', $nombreEstudiante))
            ->take(2)
            ->map(fn($p) => mb_strtoupper(mb_substr($p, 0, 1)))
            ->implode('');

        $cards = [
            [
                'title'   => 'Asistencias',
                'desc'    => 'Consulta tu historial y estado de asistencia diaria.',
                'icon'    => 'calendar',
                'url'     => route('estudiante.asistencias.index'),
                'accent'  => '#213095',
                'soft'    => '#f0f6ff',
                'stat'    => (string) $resumen['asistencias'],
                'meta'    => 'Registradas',
                'cta'     => 'Ver historial',
            ],
            [
                'title'   => 'Notas',
                'desc'    => 'Revisa tus evaluaciones, calificaciones y promedios.',
                'icon'    => 'edit-3',
                'url'     => route('estudiante.notas.index'),
                'accent'  => '#16a34a',
                'soft'    => '#f0fdf4',
                'stat'    => (string) $resumen['notas'],
                'meta'    => 'Disponibles',
                'cta'     => 'Ver notas',
            ],
            [
                'title'   => 'Deudas',
                'desc'    => 'Observa tus pensiones pendientes y fechas de vencimiento.',
                'icon'    => 'credit-card',
                'url'     => route('estudiante.deudas.index'),
                'accent'  => '#22329c',
                'soft'    => '#ebfaff',
                'stat'    => (string) $resumen['deudas'],
                'meta'    => 'Pendientes',
                'cta'     => 'Ver deudas',
                'alert'   => $resumen['deudas'] > 0,
                'saldo'   => $resumen['saldo'] ?? 0,
            ],
            [
                'title'   => 'Cambiar contraseña',
                'desc'    => 'Actualiza tu clave de acceso para mayor seguridad.',
                'icon'    => 'lock',
                'url'     => route('estudiante.password'),
                'accent'  => '#1c2a81',
                'soft'    => '#f0f6ff',
                'stat'    => null,
                'meta'    => 'Seguridad',
                'cta'     => 'Actualizar',
            ],
        ];
    @endphp

    @if (!$estudiante)
        <div class="est-welcome-msg">
            <div class="est-welcome-msg__icon"><i data-feather="alert-triangle"></i></div>
            <p>No tienes un perfil de estudiante asociado. Comunícate con Secretaría para que lo vinculen.</p>
        </div>
    @endif

    {{-- Hero Banner --}}
    <div class="est-hero">
        <div class="est-hero__deco"></div>
        <div class="est-hero__left">
            <div class="est-hero__avatar">{{ $iniciales }}</div>
            <div>
                <p class="est-hero__greeting">Bienvenido de vuelta</p>
                <h1 class="est-hero__name">{{ $nombreEstudiante }}</h1>
                <p class="est-hero__sub">
                    <i data-feather="shield" style="width:13px;height:13px;display:inline;"></i>
                    Portal del Estudiante &mdash; R.A.S
                </p>
            </div>
        </div>
        <div class="est-hero__stats">
            <div class="est-hero__stat">
                <span class="est-hero__stat-num">{{ number_format($resumen['asistencias']) }}</span>
                <span class="est-hero__stat-label">Asistencias</span>
            </div>
            <div class="est-hero__stat">
                <span class="est-hero__stat-num">{{ number_format($resumen['notas']) }}</span>
                <span class="est-hero__stat-label">Notas</span>
            </div>
            <div class="est-hero__stat">
                <span class="est-hero__stat-num">{{ number_format($resumen['deudas']) }}</span>
                <span class="est-hero__stat-label">Deudas</span>
            </div>
        </div>
    </div>

    {{-- Cards Grid --}}
    <div class="est-grid">

        @foreach ($cards as $index => $card)
            <a class="est-card" href="{{ $card['url'] }}"
               style="--card-accent: {{ $card['accent'] }}; --card-accent-soft: {{ $card['soft'] }}; --delay: {{ $index * 0.08 }}s;">
                <div class="est-card__bar"></div>
                <div class="est-card__body">
                    <div class="est-card__top">
                        <div class="est-card__icon">
                            <i data-feather="{{ $card['icon'] }}"></i>
                        </div>
                        @if ($card['stat'] !== null)
                            <div class="est-card__stat-wrap">
                                <span class="est-card__stat">{{ $card['stat'] }}</span>
                                <span class="est-card__stat-label">{{ $card['meta'] }}</span>
                            </div>
                        @else
                            <span class="est-card__badge">{{ $card['meta'] }}</span>
                        @endif
                    </div>
                    <div>
                        <p class="est-card__title">{{ $card['title'] }}</p>
                        <p class="est-card__desc">{{ $card['desc'] }}</p>
                    </div>
                </div>
                <div class="est-card__footer">
                    <span class="est-card__cta">
                        {{ $card['cta'] }}
                        <i data-feather="arrow-right"></i>
                    </span>
                    @if (!empty($card['alert']) && $card['alert'])
                        <span class="est-deuda-alert">
                            <i data-feather="alert-circle"></i>
                            S/ {{ number_format($card['saldo'], 2) }}
                        </span>
                    @endif
                </div>
            </a>
        @endforeach

        {{-- Logout --}}
        <form method="POST" action="{{ route('logout') }}" style="display:contents;">
            @csrf
            <button type="submit" class="est-card est-card--logout"
                    style="--delay: {{ count($cards) * 0.08 }}s; border: none; text-align: left; width: 100%;">
                <div class="est-card__bar"></div>
                <div class="est-card__body">
                    <div class="est-card__top">
                        <div class="est-card__icon">
                            <i data-feather="log-out"></i>
                        </div>
                    </div>
                    <div>
                        <p class="est-card__title">Cerrar sesión</p>
                        <p class="est-card__desc">Finaliza tu sesión de forma segura.</p>
                    </div>
                </div>
                <div class="est-card__footer">
                    <span class="est-card__cta">
                        <i data-feather="log-out"></i>
                        Salir ahora
                    </span>
                </div>
            </button>
        </form>

    </div>
@endsection
