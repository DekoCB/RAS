@extends('layouts.mantis')

@section('title', 'Mi Perfil | Secretaria')
@section('page-title', 'Mi Perfil')
@section('body-class', 'role-secretaria')

@push('styles')
    <link rel="stylesheet" href="{{ url('css/secretaria-dashboard.css') }}">
    <style>
        .jp-profile-password-wrap {
            position: relative;
        }

        .jp-profile-password-wrap .form-control {
            padding-right: 3rem;
        }

        .jp-profile-password-toggle {
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

        .jp-profile-password-toggle:hover {
            color: #18246e;
        }

        .jp-profile-password-toggle i {
            width: 18px;
            height: 18px;
        }
    </style>
@endpush

@section('sidebar')
    @include('secretaria.partials.sidebar')
@endsection

@section('content')
    <div class="row g-4">
        <div class="col-xl-7">
            <div class="card jp-soft-card jp-profile-card">
                <div class="card-header jp-profile-header">
                    <div>
                        <h5 class="mb-1 jp-section-title"><i data-feather="user"></i> Datos personales</h5>
                        <span class="text-muted small">Actualiza tu nombre, correo y foto.</span>
                    </div>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('patch')

                        @php
                            $avatarUrl = $user?->avatar_path
                                ? url('storage/' . ltrim($user->avatar_path, '/'))
                                : url('assets/images/user/avatar-default.svg');
                        @endphp

                        <div class="jp-profile-avatar-block mb-4">
                            <div class="jp-profile-avatar">
                                <img src="{{ $avatarUrl }}" alt="Foto de perfil">
                            </div>
                            <div class="jp-profile-avatar-info">
                                <label class="form-label mb-1">Foto de perfil</label>
                                <div class="jp-profile-file">
                                    <input type="file" name="avatar" id="avatarInput" accept="image/*">
                                    <label for="avatarInput">
                                        <i data-feather="upload"></i>
                                        Cambiar foto
                                    </label>
                                    <span class="jp-profile-file-hint">JPG, PNG o WEBP hasta 2MB.</span>
                                </div>
                                @error('avatar')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nombre</label>
                                <input type="text" name="name" class="form-control" value="{{ old('name', $user?->name) }}" required>
                                @error('name')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Correo</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email', $user?->email) }}" required>
                                @error('email')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-4">
                            <button class="btn jp-btn-incidencia jp-profile-save" type="submit">
                                <i data-feather="save"></i>
                                Guardar cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-5">
            <div class="card jp-soft-card jp-profile-card">
                <div class="card-header jp-profile-header">
                    <div>
                        <h5 class="mb-1 jp-section-title"><i data-feather="lock"></i> Seguridad</h5>
                        <span class="text-muted small">Actualiza tu contrasena cuando lo necesites.</span>
                    </div>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('password.update') }}">
                        @csrf
                        @method('put')

                        <div class="mb-3">
                            <label class="form-label">Contrasena actual</label>
                            <div class="jp-profile-password-wrap">
                                <input
                                    type="password"
                                    name="current_password"
                                    id="secretariaCurrentPassword"
                                    class="form-control"
                                    required
                                >
                                <button
                                    type="button"
                                    class="jp-profile-password-toggle"
                                    data-target="#secretariaCurrentPassword"
                                    aria-label="Mostrar u ocultar contrasena actual"
                                >
                                    <i data-feather="eye"></i>
                                </button>
                            </div>
                            @error('current_password', 'updatePassword')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nueva contrasena</label>
                            <div class="jp-profile-password-wrap">
                                <input
                                    type="password"
                                    name="password"
                                    id="secretariaPassword"
                                    class="form-control"
                                    required
                                >
                                <button
                                    type="button"
                                    class="jp-profile-password-toggle"
                                    data-target="#secretariaPassword"
                                    aria-label="Mostrar u ocultar nueva contrasena"
                                >
                                    <i data-feather="eye"></i>
                                </button>
                            </div>
                            @error('password', 'updatePassword')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Confirmar contrasena</label>
                            <div class="jp-profile-password-wrap">
                                <input
                                    type="password"
                                    name="password_confirmation"
                                    id="secretariaPasswordConfirmation"
                                    class="form-control"
                                    required
                                >
                                <button
                                    type="button"
                                    class="jp-profile-password-toggle"
                                    data-target="#secretariaPasswordConfirmation"
                                    aria-label="Mostrar u ocultar confirmacion de contrasena"
                                >
                                    <i data-feather="eye"></i>
                                </button>
                            </div>
                        </div>

                        <button class="btn jp-profile-danger" type="submit">
                            <i data-feather="refresh-cw"></i>
                            Cambiar contrasena
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
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.jp-profile-password-toggle').forEach(function (button) {
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
                        confirmButtonColor: '#18246e'
                    });
                }
            @endif
        });
    </script>
@endpush
