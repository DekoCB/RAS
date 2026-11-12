@extends('layouts.mantis')

@section('title', 'Mi Perfil | Auxiliar')
@section('page-title', 'Mi Perfil')
@section('body-class', 'role-auxiliar')

@push('styles')
    <link rel="stylesheet" href="{{ url('css/auxiliar-dashboard.css') }}?v={{ time() }}">
    <style>
        .aux-password-wrap {
            position: relative;
        }

        .aux-password-wrap .form-control {
            padding-right: 3rem;
        }

        .aux-password-toggle {
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

        .aux-password-toggle:hover {
            color: #0f132a;
        }

        .aux-password-toggle i {
            width: 18px;
            height: 18px;
        }
    </style>
@endpush

@section('sidebar')
    @include('auxiliar.partials.sidebar')
@endsection

@section('content')
    <div class="card mt-2">
        <div class="card-header d-flex align-items-center gap-3">
            <span class="aux-card-icon"><i data-feather="user"></i></span>
            <div>
                <p class="aux-card-title">Datos de mi cuenta</p>
                <p class="aux-card-sub">Informaci&oacute;n del usuario activo.</p>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label text-muted small">Nombre completo</label>
                    <p class="fw-bold mb-0">{{ $user->name }}</p>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted small">Correo electr&oacute;nico</label>
                    <p class="fw-bold mb-0">{{ $user->email }}</p>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted small">DNI</label>
                    <p class="fw-bold mb-0">{{ $user->dni ?? '—' }}</p>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted small">Rol</label>
                    <p class="fw-bold mb-0">Auxiliar</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header d-flex align-items-center gap-3">
            <span class="aux-card-icon"><i data-feather="lock"></i></span>
            <div>
                <p class="aux-card-title">Seguridad</p>
                <p class="aux-card-sub">Cambia tu contrasena cuando lo necesites.</p>
            </div>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                @method('put')

                <div class="row g-3">
                    <div class="col-lg-4">
                        <label class="form-label">Contrasena actual</label>
                        <div class="aux-password-wrap">
                            <input
                                type="password"
                                name="current_password"
                                id="auxiliarCurrentPassword"
                                class="form-control @error('current_password', 'updatePassword') is-invalid @enderror"
                                required
                            >
                            <button
                                type="button"
                                class="aux-password-toggle"
                                data-target="#auxiliarCurrentPassword"
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
                        <div class="aux-password-wrap">
                            <input
                                type="password"
                                name="password"
                                id="auxiliarPassword"
                                class="form-control @error('password', 'updatePassword') is-invalid @enderror"
                                required
                            >
                            <button
                                type="button"
                                class="aux-password-toggle"
                                data-target="#auxiliarPassword"
                                aria-label="Mostrar u ocultar nueva contrasena"
                            >
                                <i data-feather="eye"></i>
                            </button>
                        </div>
                        @error('password', 'updatePassword')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-lg-4">
                        <label class="form-label">Confirmar contrasena</label>
                        <div class="aux-password-wrap">
                            <input
                                type="password"
                                name="password_confirmation"
                                id="auxiliarPasswordConfirmation"
                                class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror"
                                required
                            >
                            <button
                                type="button"
                                class="aux-password-toggle"
                                data-target="#auxiliarPasswordConfirmation"
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
                    <button type="submit" class="btn btn-primary">
                        <i data-feather="refresh-cw"></i>
                        Cambiar contrasena
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
            document.querySelectorAll('.aux-password-toggle').forEach(function (button) {
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
