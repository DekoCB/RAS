@extends('layouts.mantis')

@section('title', 'Cambiar contrasena | Estudiante')
@section('page-title', 'Cambiar contrasena')
@section('body-class', 'role-estudiante')

@push('styles')
    <link rel="stylesheet" href="{{ url('css/secretaria-dashboard.css') }}">
    <link rel="stylesheet" href="{{ url('css/estudiante-modules.css') }}">
    <style>
        .est-password-wrap {
            position: relative;
        }

        .est-password-wrap .form-control {
            padding-right: 3rem;
        }

        .est-password-toggle {
            position: absolute;
            top: 50%;
            right: 0.85rem;
            transform: translateY(-50%);
            border: 0;
            background: transparent;
            color: #64748b;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .est-password-toggle:hover {
            color: #0f132a;
        }

        .est-password-toggle i {
            width: 18px;
            height: 18px;
        }
    </style>
@endpush

@section('sidebar')
    @include('estudiante.partials.sidebar')
@endsection

@section('content')
    <a class="est-back-link" href="{{ route('estudiante.dashboard') }}">
        <i data-feather="arrow-left"></i> Volver al panel
    </a>

    <div class="est-module-hero">
        <div class="est-module-hero__left">
            <div class="est-module-hero__icon">
                <i data-feather="lock"></i>
            </div>
            <div>
                <h1 class="est-module-hero__title">Cambiar contrasena</h1>
                <p class="est-module-hero__sub">Actualiza tu clave para mantener tu cuenta segura.</p>
            </div>
        </div>
    </div>

    <div class="est-password-card">
        <div class="est-section-card__header">
            <div>
                <div class="est-section-card__title">Nueva contrasena</div>
                <div class="est-section-card__sub">Ingresa tu contrasena actual y la nueva clave dos veces</div>
            </div>
        </div>
        <div style="padding: 20px 24px 28px;">
            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                @method('put')

                <div class="row g-3">
                    <div class="col-lg-4">
                        <label class="form-label">Contrasena actual</label>
                        <div class="est-password-wrap">
                            <input
                                type="password"
                                name="current_password"
                                id="estudianteCurrentPassword"
                                autocomplete="current-password"
                                class="form-control @error('current_password', 'updatePassword') is-invalid @enderror"
                                required
                            >
                            <button
                                type="button"
                                class="est-password-toggle"
                                data-target="#estudianteCurrentPassword"
                                aria-label="Mostrar u ocultar contrasena actual"
                            >
                                <i data-feather="eye"></i>
                            </button>
                        </div>
                        @error('current_password', 'updatePassword')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-lg-4">
                        <label class="form-label">Nueva contrasena</label>
                        <div class="est-password-wrap">
                            <input
                                type="password"
                                name="password"
                                id="estudiantePassword"
                                autocomplete="new-password"
                                class="form-control @error('password', 'updatePassword') is-invalid @enderror"
                                required
                            >
                            <button
                                type="button"
                                class="est-password-toggle"
                                data-target="#estudiantePassword"
                                aria-label="Mostrar u ocultar nueva contrasena"
                            >
                                <i data-feather="eye"></i>
                            </button>
                        </div>
                        @error('password', 'updatePassword')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <div class="est-password-hint">Minimo 8 caracteres.</div>
                    </div>

                    <div class="col-lg-4">
                        <label class="form-label">Confirmar contrasena</label>
                        <div class="est-password-wrap">
                            <input
                                type="password"
                                name="password_confirmation"
                                id="estudiantePasswordConfirmation"
                                autocomplete="new-password"
                                class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror"
                                required
                            >
                            <button
                                type="button"
                                class="est-password-toggle"
                                data-target="#estudiantePasswordConfirmation"
                                aria-label="Mostrar u ocultar confirmacion de contrasena"
                            >
                                <i data-feather="eye"></i>
                            </button>
                        </div>
                        @error('password_confirmation', 'updatePassword')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-end">
                    <button type="submit" class="est-btn-primary">
                        <i data-feather="save" style="width:15px;height:15px;"></i>
                        Actualizar contrasena
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ url('assets/js/plugins/sweetalert2.all.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.est-password-toggle').forEach(function (button) {
                button.addEventListener('click', function () {
                    var targetSelector = button.getAttribute('data-target');
                    var input = targetSelector ? document.querySelector(targetSelector) : null;

                    if (!input) {
                        return;
                    }

                    var showing = input.type === 'text';
                    input.type = showing ? 'password' : 'text';

                    var icon = button.querySelector('i');
                    if (icon) {
                        icon.setAttribute('data-feather', showing ? 'eye' : 'eye-off');
                        if (window.feather) {
                            feather.replace();
                        }
                    }
                });
            });

            @if (session('status') === 'password-updated')
                if (window.Swal) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Contrasena actualizada',
                        text: 'La contrasena se cambio correctamente.',
                        confirmButtonText: 'Aceptar',
                        confirmButtonColor: '#0f132a'
                    });
                }
            @endif
        });
    </script>
@endpush
