@extends('layouts.mantis')

@section('title', 'Mi Perfil | Docente')
@section('page-title', 'Mi Perfil')
@section('body-class', 'role-docente')

@push('styles')
    <link rel="stylesheet" href="{{ url('css/docente-dashboard.css') }}">
    <style>
        /* â”€â”€ Hero de perfil â”€â”€ */
        .doc-profile-hero {
            border-radius: var(--doc-radius);
            background: var(--doc-gradient);
            box-shadow: var(--doc-shadow-lg);
            overflow: hidden;
            position: relative;
            margin-bottom: 24px;
        }
        .doc-profile-hero::before {
            content: '';
            position: absolute; inset: 0;
            background:
                radial-gradient(ellipse at 80% 20%, rgba(255,255,255,.13) 0%, transparent 55%),
                radial-gradient(ellipse at 10% 90%, rgba(0,0,0,.12) 0%, transparent 45%);
            pointer-events: none;
        }
        .doc-profile-hero__inner {
            position: relative; z-index: 1;
            padding: 28px 32px;
            display: flex; align-items: center; gap: 22px;
            flex-wrap: wrap;
        }
        /* Avatar grande del hero */
        .doc-profile-hero__avatar {
            width: 80px; height: 80px; border-radius: 50%;
            object-fit: cover; flex-shrink: 0;
            border: 3px solid rgba(255,255,255,.5);
            box-shadow: 0 8px 24px rgba(0,0,0,.2);
        }
        .doc-profile-hero__avatar-placeholder {
            width: 80px; height: 80px; border-radius: 50%;
            background: rgba(255,255,255,.22);
            border: 3px solid rgba(255,255,255,.45);
            display: inline-flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            font-size: 2rem; font-weight: 900; color: #fff;
            box-shadow: 0 8px 24px rgba(0,0,0,.2);
        }
        .doc-profile-hero__info { flex: 1; min-width: 0; }
        .doc-profile-hero__name {
            font-size: 1.45rem; font-weight: 900; color: #fff; margin: 0 0 4px;
            text-shadow: 0 2px 8px rgba(0,0,0,.15);
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .doc-profile-hero__email {
            font-size: .87rem; color: rgba(255,255,255,.82); margin: 0 0 10px;
        }
        .doc-profile-hero__pills { display: flex; flex-wrap: wrap; gap: 8px; }
        .doc-profile-hero__pill {
            display: inline-flex; align-items: center; gap: 6px;
            background: rgba(0,0,0,.18); border: 1px solid rgba(255,255,255,.22);
            color: #fff; padding: 5px 12px; border-radius: 999px;
            font-size: .76rem; font-weight: 700;
        }
        .doc-profile-hero__pill.is-light {
            background: rgba(255,255,255,.2); border-color: rgba(255,255,255,.35);
        }
        .doc-profile-hero__pill i { width: 12px; height: 12px; }

        /* â”€â”€ Cards de secciÃ³n â”€â”€ */
        .doc-profile-card {
            background: #fff;
            border: 1px solid var(--doc-border);
            border-radius: var(--doc-radius);
            box-shadow: var(--doc-shadow-sm);
            overflow: hidden;
        }
        .doc-profile-card__header {
            display: flex; align-items: center; gap: 12px;
            padding: 18px 24px 16px;
            border-bottom: 1px solid var(--doc-border);
            background: linear-gradient(180deg, #f5faff 0%, #fff 100%);
        }
        .doc-profile-card__icon {
            width: 40px; height: 40px; border-radius: 13px;
            display: inline-flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .doc-profile-card__icon i { width: 18px; height: 18px; color: #fff; }
        .doc-profile-card__icon--primary { background: var(--doc-gradient); box-shadow: 0 6px 16px rgba(33, 49, 150,.28); }
        .doc-profile-card__icon--security { background: linear-gradient(135deg,#192151,#213197); box-shadow: 0 6px 16px rgba(33,49,151,.25); }
        .doc-profile-card__title { font-weight: 800; color: var(--doc-ink); margin: 0 0 2px; font-size: 1rem; }
        .doc-profile-card__sub { color: var(--doc-muted); font-size: .82rem; margin: 0; }
        .doc-profile-card__body { padding: 24px; }

        /* â”€â”€ Avatar upload block â”€â”€ */
        .doc-profile-avatar-wrap {
            display: flex; align-items: center; gap: 20px;
            padding: 18px 20px;
            background: linear-gradient(135deg, #f5faff 0%, #ebf6ff 100%);
            border: 1px solid var(--doc-border);
            border-radius: 16px;
            margin-bottom: 24px;
        }
        .doc-profile-avatar-img {
            width: 72px; height: 72px; border-radius: 50%;
            object-fit: cover; flex-shrink: 0;
            border: 3px solid var(--doc-border);
            box-shadow: 0 4px 14px rgba(33, 49, 150,.15);
        }
        .doc-profile-avatar-placeholder {
            width: 72px; height: 72px; border-radius: 50%;
            background: var(--doc-gradient);
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 1.7rem; font-weight: 900; color: #fff; flex-shrink: 0;
            border: 3px solid rgba(33, 49, 150,.2);
            box-shadow: 0 4px 14px rgba(33, 49, 150,.2);
        }
        .doc-profile-avatar-label {
            font-weight: 700; color: var(--doc-ink); font-size: .9rem; margin: 0 0 4px;
        }
        .doc-profile-avatar-hint {
            font-size: .78rem; color: var(--doc-muted); margin: 0 0 10px;
        }
        /* File input custom */
        .doc-profile-file-input { display: none; }
        .doc-profile-file-btn {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 7px 16px; border-radius: 999px; cursor: pointer;
            font-size: .8rem; font-weight: 700;
            background: var(--doc-gradient); color: #fff; border: none;
            box-shadow: 0 6px 14px rgba(33, 49, 150,.25);
            transition: opacity .2s, transform .2s;
        }
        .doc-profile-file-btn:hover { opacity: .88; transform: translateY(-1px); }
        .doc-profile-file-btn i { width: 13px; height: 13px; }

        /* â”€â”€ Form labels â”€â”€ */
        .doc-profile-card__body .form-label {
            font-size: .82rem; font-weight: 700; color: var(--doc-muted);
            margin-bottom: 6px;
        }
        .doc-profile-card__body .form-control,
        .doc-profile-card__body .form-select {
            border-radius: 12px;
            border: 1px solid #d4ecfd;
            font-size: .9rem;
            padding: 10px 14px;
            transition: border-color .2s, box-shadow .2s;
        }
        .doc-profile-card__body .form-control:focus,
        .doc-profile-card__body .form-select:focus {
            border-color: #23339e;
            box-shadow: 0 0 0 3px rgba(35, 51, 158,.12);
        }

        /* â”€â”€ Password input wrapper â”€â”€ */
        .doc-profile-pw-wrap {
            position: relative;
        }
        .doc-profile-pw-wrap .form-control {
            padding-right: 44px;
        }
        .doc-profile-pw-toggle {
            position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
            background: none; border: none; cursor: pointer;
            color: var(--doc-muted); padding: 4px;
            display: inline-flex; align-items: center;
            transition: color .18s;
        }
        .doc-profile-pw-toggle:hover { color: var(--doc-sky-500); }
        .doc-profile-pw-toggle i { width: 16px; height: 16px; }

        /* â”€â”€ Botones â”€â”€ */
        .doc-profile-btn-save {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 11px 24px; border-radius: 999px;
            font-weight: 700; font-size: .9rem;
            color: #fff; background: var(--doc-gradient); border: none;
            box-shadow: 0 8px 20px rgba(33, 49, 150,.28);
            transition: opacity .2s, transform .2s; cursor: pointer;
        }
        .doc-profile-btn-save:hover { opacity: .9; transform: translateY(-1px); color: #fff; }
        .doc-profile-btn-save i { width: 16px; height: 16px; }

        .doc-profile-btn-security {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 11px 24px; border-radius: 999px;
            font-weight: 700; font-size: .9rem;
            color: #fff; background: linear-gradient(135deg,#192151,#213197); border: none;
            box-shadow: 0 8px 20px rgba(33,49,151,.25);
            transition: opacity .2s, transform .2s; cursor: pointer;
        }
        .doc-profile-btn-security:hover { opacity: .9; transform: translateY(-1px); color: #fff; }
        .doc-profile-btn-security i { width: 16px; height: 16px; }

        /* â”€â”€ Alerta de Ã©xito â”€â”€ */
        .doc-profile-success {
            display: flex; align-items: center; gap: 10px;
            padding: 12px 16px; border-radius: 12px;
            background: rgba(22,163,74,.08); border: 1px solid rgba(22,163,74,.22);
            color: #15803d; font-size: .86rem; font-weight: 600;
            margin-bottom: 16px;
        }
        .doc-profile-success i { width: 16px; height: 16px; flex-shrink: 0; }

        /* â”€â”€ Separador de secciÃ³n â”€â”€ */
        .doc-profile-divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--doc-border), transparent);
            margin: 20px 0;
        }

        @media (max-width: 576px) {
            .doc-profile-hero__inner { padding: 20px; }
            .doc-profile-avatar-wrap { flex-direction: column; align-items: flex-start; gap: 14px; }
        }
    </style>
@endpush

@section('sidebar')
    @include('docente.partials.sidebar')
@endsection

@section('content')

    @php
        $hasAvatar   = !empty($user?->avatar_path);
        $avatarUrl   = $hasAvatar ? url('storage/' . ltrim($user->avatar_path, '/')) : null;
        $initial     = strtoupper(substr($user?->name ?? 'D', 0, 1));
        $userName    = $user?->name ?? 'Docente';
        $userEmail   = $user?->email ?? '';
    @endphp

    {{-- â”€â”€ HERO â”€â”€ --}}
    <div class="doc-profile-hero">
        <div class="doc-profile-hero__inner">
            @if($hasAvatar)
                <img src="{{ $avatarUrl }}" alt="Avatar" class="doc-profile-hero__avatar">
            @else
                <div class="doc-profile-hero__avatar-placeholder">{{ $initial }}</div>
            @endif
            <div class="doc-profile-hero__info">
                <h1 class="doc-profile-hero__name">{{ $userName }}</h1>
                <p class="doc-profile-hero__email">{{ $userEmail }}</p>
                <div class="doc-profile-hero__pills">
                    <span class="doc-profile-hero__pill is-light">
                        <i data-feather="book-open"></i>
                        Docente
                    </span>
                    <span class="doc-profile-hero__pill">
                        <i data-feather="shield"></i>
                        Cuenta activa
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">

        {{-- â”€â”€ DATOS PERSONALES â”€â”€ --}}
        <div class="col-xl-7">
            <div class="doc-profile-card">
                <div class="doc-profile-card__header">
                    <div class="doc-profile-card__icon doc-profile-card__icon--primary">
                        <i data-feather="user"></i>
                    </div>
                    <div>
                        <p class="doc-profile-card__title">Datos personales</p>
                        <p class="doc-profile-card__sub">Actualiza tu nombre, correo y foto de perfil</p>
                    </div>
                </div>
                <div class="doc-profile-card__body">

                    @if(session('status') === 'profile-updated')
                        <div class="doc-profile-success">
                            <i data-feather="check-circle"></i>
                            Perfil actualizado correctamente.
                        </div>
                    @endif

                    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('patch')

                        {{-- Avatar upload --}}
                        <div class="doc-profile-avatar-wrap">
                            @if($hasAvatar)
                                <img src="{{ $avatarUrl }}" alt="Avatar" class="doc-profile-avatar-img" id="avatarPreview">
                            @else
                                <div class="doc-profile-avatar-placeholder" id="avatarPlaceholder">{{ $initial }}</div>
                                <img src="" alt="Avatar" class="doc-profile-avatar-img d-none" id="avatarPreview">
                            @endif
                            <div>
                                <p class="doc-profile-avatar-label">Foto de perfil</p>
                                <p class="doc-profile-avatar-hint">JPG, PNG o WEBP &mdash; mÃ¡ximo 2 MB</p>
                                <input type="file" name="avatar" id="avatarInput"
                                       accept="image/*" class="doc-profile-file-input">
                                <label for="avatarInput" class="doc-profile-file-btn">
                                    <i data-feather="upload"></i>
                                    Cambiar foto
                                </label>
                                @error('avatar')
                                    <div class="mt-1"><small class="text-danger">{{ $message }}</small></div>
                                @enderror
                            </div>
                        </div>

                        <div class="doc-profile-divider"></div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nombre completo</label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name', $user?->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Correo electrÃ³nico</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email', $user?->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="doc-profile-btn-save">
                                <i data-feather="save"></i>
                                Guardar cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- â”€â”€ SEGURIDAD â”€â”€ --}}
        <div class="col-xl-5">
            <div class="doc-profile-card">
                <div class="doc-profile-card__header">
                    <div class="doc-profile-card__icon doc-profile-card__icon--security">
                        <i data-feather="lock"></i>
                    </div>
                    <div>
                        <p class="doc-profile-card__title">Seguridad</p>
                        <p class="doc-profile-card__sub">Cambia tu contraseÃ±a cuando lo necesites</p>
                    </div>
                </div>
                <div class="doc-profile-card__body">


                    <form method="POST" action="{{ route('password.update') }}">
                        @csrf
                        @method('put')

                        <div class="mb-3">
                            <label class="form-label">ContraseÃ±a actual</label>
                            <div class="doc-profile-pw-wrap">
                                <input type="password" name="current_password"
                                       class="form-control @error('current_password', 'updatePassword') is-invalid @enderror"
                                       placeholder="â€¢â€¢â€¢â€¢â€¢â€¢â€¢â€¢" required>
                                <button type="button" class="doc-profile-pw-toggle" data-target="current_password">
                                    <i data-feather="eye"></i>
                                </button>
                            </div>
                            @error('current_password', 'updatePassword')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nueva contraseÃ±a</label>
                            <div class="doc-profile-pw-wrap">
                                <input type="password" name="password"
                                       class="form-control @error('password', 'updatePassword') is-invalid @enderror"
                                       placeholder="â€¢â€¢â€¢â€¢â€¢â€¢â€¢â€¢" required>
                                <button type="button" class="doc-profile-pw-toggle" data-target="password">
                                    <i data-feather="eye"></i>
                                </button>
                            </div>
                            @error('password', 'updatePassword')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Confirmar contraseÃ±a</label>
                            <div class="doc-profile-pw-wrap">
                                <input type="password" name="password_confirmation"
                                       class="form-control" placeholder="â€¢â€¢â€¢â€¢â€¢â€¢â€¢â€¢" required>
                                <button type="button" class="doc-profile-pw-toggle" data-target="password_confirmation">
                                    <i data-feather="eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="doc-profile-divider"></div>

                        <button type="submit" class="doc-profile-btn-security">
                            <i data-feather="refresh-cw"></i>
                            Cambiar contraseÃ±a
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
<script src="{{ url('assets/js/plugins/sweetalert2.all.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    /* â”€â”€ Preview de avatar al seleccionar archivo â”€â”€ */
    const avatarInput       = document.getElementById('avatarInput');
    const avatarPreview     = document.getElementById('avatarPreview');
    const avatarPlaceholder = document.getElementById('avatarPlaceholder');

    if (avatarInput && avatarPreview) {
        avatarInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = (ev) => {
                avatarPreview.src = ev.target.result;
                avatarPreview.classList.remove('d-none');
                if (avatarPlaceholder) avatarPlaceholder.classList.add('d-none');
            };
            reader.readAsDataURL(file);
        });
    }

    /* â”€â”€ Toggle mostrar/ocultar contraseÃ±a â”€â”€ */
    document.querySelectorAll('.doc-profile-pw-toggle').forEach((btn) => {
        btn.addEventListener('click', () => {
            const targetName = btn.getAttribute('data-target');
            const input = btn.closest('form').querySelector(`[name="${targetName}"]`);
            if (!input) return;
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            const icon = btn.querySelector('i');
            if (icon) {
                icon.setAttribute('data-feather', isPassword ? 'eye-off' : 'eye');
                if (window.feather) feather.replace();
            }
        });
    });

    @if(session('status') === 'password-updated')
    if (window.Swal) {
        Swal.fire({
            icon: 'success',
            title: 'Contrasena actualizada',
            text: 'La contrasena se cambio correctamente.',
            confirmButtonText: 'Aceptar',
            confirmButtonColor: '#213196'
        });
    }
    @endif
});
</script>
@endpush

