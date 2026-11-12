@extends('layouts.mantis')

@section('title', 'Usuarios | SuperAdmin')
@section('page-title', 'Usuarios del sistema')
@section('body-class', 'role-superadmin')

@push('styles')
    <link rel="stylesheet" href="{{ url('css/superadmin-dashboard.css') }}?v={{ filemtime(public_path('css/superadmin-dashboard.css')) }}">
<style>
/* ── Wrapper ──────────────────────────────────────────── */
.usr-wrap { width: 100%; }

/* ── Header banner ────────────────────────────────────── */
.usr-header {
    background: linear-gradient(120deg, #162061 0%, #18246e 55%, #1c297e 100%);
    border-radius: 18px;
    padding: 1.4rem 1.8rem;
    display: flex; align-items: center; justify-content: space-between; gap: 1rem;
    flex-wrap: wrap;
    box-shadow: 0 12px 30px rgba(22, 32, 97,.30);
    margin-bottom: 1.5rem;
    position: relative; overflow: hidden;
}
.usr-header::after {
    content: '';
    position: absolute; right: -30px; top: -30px;
    width: 140px; height: 140px;
    background: rgba(255,255,255,.06); border-radius: 50%;
    pointer-events: none;
}
.usr-header-left h4 {
    color: #fff; font-weight: 800; font-size: 1.25rem; margin: 0 0 .25rem;
    display: flex; align-items: center; gap: .55rem;
}
.usr-header-left h4 i { width: 20px; height: 20px; }
.usr-header-left p { color: rgba(255,255,255,.75); font-size: .82rem; margin: 0; }
.usr-header-btn {
    display: inline-flex; align-items: center; gap: .5rem;
    padding: .6rem 1.3rem;
    background: rgba(255,255,255,.18);
    border: 1.5px solid rgba(255,255,255,.35);
    border-radius: 12px; color: #fff; font-weight: 700; font-size: .88rem;
    cursor: pointer; transition: all .2s ease; text-decoration: none;
    backdrop-filter: blur(4px);
}
.usr-header-btn:hover { background: rgba(255,255,255,.28); color: #fff; transform: translateY(-1px); }
.usr-header-btn i { width: 16px; height: 16px; }
.usr-header-btn.is-hidden { display: none !important; }

/* ── Tabs ─────────────────────────────────────────────── */
.usr-tabs {
    display: flex; gap: .5rem; flex-wrap: wrap;
    background: #fff; border-radius: 14px;
    padding: .5rem; margin-bottom: 1.2rem;
    box-shadow: 0 2px 12px rgba(0,0,0,.06);
    border: 1px solid #e8ebf0;
}
.usr-tab {
    display: inline-flex; align-items: center; gap: .5rem;
    padding: .55rem 1.1rem;
    border-radius: 10px; font-weight: 600; font-size: .84rem;
    color: #64748b; text-decoration: none;
    transition: all .2s ease; border: 1.5px solid transparent;
}
.usr-tab i { width: 15px; height: 15px; }
.usr-tab .usr-tab-count {
    background: #f1f5f9; color: #64748b;
    border-radius: 999px; padding: .1rem .5rem;
    font-size: .72rem; font-weight: 800; min-width: 22px; text-align: center;
}
.usr-tab:hover { background: #f2f7fe; color: #162061; border-color: #cae0fe; }
.usr-tab.is-active {
    background: linear-gradient(135deg, #162061, #1c297e);
    color: #fff; border-color: transparent;
    box-shadow: 0 6px 14px rgba(22, 32, 97,.30);
}
.usr-tab.is-active .usr-tab-count { background: rgba(255,255,255,.25); color: #fff; }

/* ── Card ─────────────────────────────────────────────── */
.usr-card {
    background: #fff; border-radius: 18px;
    border: 1px solid #e8ebf0;
    box-shadow: 0 4px 20px rgba(22, 32, 97,.08);
    overflow: hidden;
}
.usr-card-header {
    padding: 1.1rem 1.4rem;
    display: flex; align-items: center; justify-content: space-between; gap: 1rem;
    flex-wrap: wrap;
    border-bottom: 1px solid #f2f7fe;
    background: #fff;
}
.usr-card-title {
    display: flex; align-items: center; gap: .5rem;
    font-weight: 800; font-size: .95rem; color: #162061; margin: 0;
}
.usr-card-title i { width: 18px; height: 18px; }

/* ── Search ───────────────────────────────────────────── */
.usr-search-form {
    display: flex; align-items: center; gap: .5rem;
}
.usr-search-wrap {
    display: flex; align-items: center;
    background: #f8fafc; border: 1.5px solid #e2e8f0;
    border-radius: 10px; overflow: hidden;
    transition: border-color .18s ease;
    min-width: 220px;
}
.usr-search-wrap:focus-within { border-color: #1c297e; background: #fff; }
.usr-search-wrap .usr-search-icon {
    padding: 0 .75rem; color: #94a3b8;
    display: flex; align-items: center;
}
.usr-search-wrap .usr-search-icon i { width: 15px; height: 15px; }
.usr-search-wrap input {
    border: 0; background: transparent; outline: none;
    font-size: .84rem; color: #334155; padding: .55rem 0;
    width: 100%;
}
.usr-search-btn {
    display: inline-flex; align-items: center; gap: .35rem;
    padding: .5rem 1rem;
    background: linear-gradient(135deg, #1c297e, #1f2e8e);
    color: #fff; border: 0; border-radius: 10px;
    font-weight: 700; font-size: .82rem; cursor: pointer;
    transition: all .18s ease; white-space: nowrap;
}
.usr-search-btn:hover { opacity: .9; transform: translateY(-1px); }
.usr-search-btn i { width: 13px; height: 13px; }

/* ── Table ────────────────────────────────────────────── */
.usr-table { width: 100%; border-collapse: collapse; }
.usr-table thead th {
    background: #f5f9ff; color: #162061;
    font-size: .72rem; text-transform: uppercase;
    letter-spacing: .06em; font-weight: 800;
    padding: .75rem 1.2rem;
    border-bottom: 1.5px solid #cae0fe;
    white-space: nowrap;
}
.usr-table tbody td {
    padding: .85rem 1.2rem;
    vertical-align: middle;
    border-bottom: 1px solid #f2f7fe;
    font-size: .87rem; color: #334155;
}
.usr-table tbody tr:last-child td { border-bottom: 0; }
.usr-table tbody tr:hover td { background: #f8fbff; }

/* ── Avatar ───────────────────────────────────────────── */
.usr-avatar {
    width: 36px; height: 36px; border-radius: 50%;
    display: inline-flex; align-items: center; justify-content: center;
    font-weight: 800; font-size: .82rem; color: #fff; flex-shrink: 0;
}
.usr-avatar.av-director  { background: linear-gradient(135deg, #162061, #1c297e); }
.usr-avatar.av-secretaria{ background: linear-gradient(135deg, #1f2e8d, #25359c); }
.usr-avatar.av-docente   { background: linear-gradient(135deg, #15803d, #22c55e); }
.usr-avatar.av-estudiante{ background: linear-gradient(135deg, #9333ea, #c084fc); }
.usr-avatar.av-auxiliar  { background: linear-gradient(135deg, #0f766e, #2dd4bf); }
.usr-avatar.av-default   { background: linear-gradient(135deg, #64748b, #94a3b8); }

.usr-user-name { font-weight: 700; color: #1e293b; font-size: .88rem; }
.usr-user-email { font-size: .78rem; color: #94a3b8; }

/* ── Role badges ──────────────────────────────────────── */
.usr-badge {
    display: inline-flex; align-items: center; gap: .3rem;
    padding: .28rem .75rem; border-radius: 999px;
    font-size: .72rem; font-weight: 800; letter-spacing: .03em;
    text-transform: uppercase;
}
.usr-badge-director   { background: #e2eefe; color: #1c297e; border: 1px solid rgba(28, 41, 126,.2); }
.usr-badge-secretaria { background: #dbeafe; color: #1f2e8d; border: 1px solid rgba(31,46,141,.2); }
.usr-badge-docente    { background: #dcfce7; color: #15803d; border: 1px solid rgba(21,128,61,.2); }
.usr-badge-estudiante { background: #f3e8ff; color: #9333ea; border: 1px solid rgba(147,51,234,.2); }
.usr-badge-auxiliar   { background: #ccfbf1; color: #0f766e; border: 1px solid rgba(15,118,110,.2); }
.usr-badge-default    { background: #f1f5f9; color: #475569; border: 1px solid rgba(71,85,105,.2); }

/* ── Date ─────────────────────────────────────────────── */
.usr-date { font-size: .8rem; color: #94a3b8; white-space: nowrap; }

/* ── Actions ───────────────────────────────────────────── */
.usr-action-btn {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    padding: .42rem .8rem;
    border: 1px solid #cae0fe;
    border-radius: 9px;
    background: #f5f9ff;
    color: #18246e;
    font-size: .78rem;
    font-weight: 700;
    cursor: pointer;
    transition: all .18s ease;
}
.usr-action-btn:hover {
    background: #e2eefe;
    border-color: #c8cffc;
    transform: translateY(-1px);
}
.usr-action-btn i {
    width: 13px;
    height: 13px;
}

/* ── Botón Anular / Reactivar ─────────────────────────── */
.usr-btn-anular {
    display: inline-flex; align-items: center; gap: .35rem;
    padding: .42rem .8rem;
    border: 1px solid #c8cffc; border-radius: 9px;
    background: #e2eefe; color: #1c297e;
    font-size: .78rem; font-weight: 700; cursor: pointer;
    transition: all .18s ease;
}
.usr-btn-anular:hover { background: #cae0fe; border-color: #a7b2f8; transform: translateY(-1px); }
.usr-btn-anular i { width: 13px; height: 13px; }

.usr-btn-reactivar {
    display: inline-flex; align-items: center; gap: .35rem;
    padding: .42rem .8rem;
    border: 1px solid #86efac; border-radius: 9px;
    background: #dcfce7; color: #15803d;
    font-size: .78rem; font-weight: 700; cursor: pointer;
    transition: all .18s ease;
}
.usr-btn-reactivar:hover { background: #bbf7d0; border-color: #4ade80; transform: translateY(-1px); }
.usr-btn-reactivar i { width: 13px; height: 13px; }

/* Badge Retirado */
.usr-badge-retirado {
    display: inline-flex; align-items: center; gap: .3rem;
    padding: .2rem .6rem; border-radius: 999px;
    font-size: .68rem; font-weight: 800; letter-spacing: .03em;
    text-transform: uppercase;
    background: #f1f5f9; color: #64748b; border: 1px solid #cbd5e1;
}

/* Fila de docente inactivo */
.usr-row-inactivo td { opacity: .55; }
.usr-row-inactivo:hover td { background: #f8fafc !important; opacity: .7; }

/* ── Empty state ──────────────────────────────────────── */
.usr-empty {
    text-align: center; padding: 3rem 1rem; color: #cbd5e1;
}
.usr-empty i { width: 48px; height: 48px; opacity: .3; }
.usr-empty p { margin-top: .75rem; font-size: .9rem; }

/* ── Alert notice ─────────────────────────────────────── */
.usr-notice {
    display: flex; align-items: center; gap: .75rem;
    background: linear-gradient(135deg, #edf8ff, #c7effe);
    border: 1px solid #ccd2fe; border-radius: 14px;
    padding: 1rem 1.3rem; margin-bottom: 1rem;
    color: #18246e; font-size: .88rem;
}
.usr-notice i { width: 18px; height: 18px; flex-shrink: 0; color: #213196; }

/* ── Pagination ───────────────────────────────────────── */
.usr-pagination { padding: .75rem 1.2rem; border-top: 1px solid #f2f7fe; }

/* ── Modal ────────────────────────────────────────────── */
.usr-modal .modal-content { border-radius: 18px; overflow: hidden; border: 0; }
.usr-modal .modal-header {
    background: linear-gradient(120deg, #162061 0%, #18246e 55%, #1c297e 100%);
    border-bottom: 0; padding: 1.2rem 1.5rem;
}
.usr-modal .modal-title { color: #fff; font-weight: 800; font-size: 1rem; display: flex; align-items: center; gap: .5rem; }
.usr-modal .modal-title i { width: 18px; height: 18px; }
.usr-modal .btn-close { filter: invert(1) grayscale(100%); opacity: .9; }
.usr-modal .modal-body { padding: 1.5rem; }
.usr-modal .modal-footer { padding: .9rem 1.5rem; border-top: 1px solid #e8ebf0; background: #fafafa; }

.usr-section-label {
    font-size: .7rem; font-weight: 800; text-transform: uppercase;
    letter-spacing: .07em; color: #1c297e;
    display: flex; align-items: center; gap: .4rem;
    margin: .25rem 0 .75rem;
}
.usr-section-label::after {
    content: ''; flex: 1; height: 1px; background: #cae0fe;
}
.usr-section-label i { width: 12px; height: 12px; }

.usr-modal .form-label { font-size: .8rem; font-weight: 700; color: #374151; margin-bottom: .35rem; }
.usr-modal .form-control,
.usr-modal .form-select {
    border-radius: 10px; border: 1.5px solid #e2e8f0;
    min-height: 44px; font-size: .88rem;
    transition: border-color .18s ease, box-shadow .18s ease;
}
.usr-modal .form-control:focus,
.usr-modal .form-select:focus {
    border-color: #1c297e; box-shadow: 0 0 0 3px rgba(28, 41, 126,.12);
}
.usr-modal-cancel {
    background: #f1f5f9; border: 1.5px solid #e2e8f0; color: #475569;
    border-radius: 10px; font-weight: 700; padding: .55rem 1.2rem;
    font-size: .88rem; cursor: pointer; transition: all .18s ease;
    display: inline-flex; align-items: center; gap: .4rem;
}
.usr-modal-cancel:hover { background: #e2e8f0; color: #334155; }
.usr-modal-submit {
    background: linear-gradient(135deg, #1c297e, #1f2e8e);
    border: 0; color: #fff; border-radius: 10px;
    font-weight: 700; padding: .55rem 1.5rem; font-size: .88rem;
    cursor: pointer; transition: all .18s ease;
    display: inline-flex; align-items: center; gap: .4rem;
    box-shadow: 0 6px 14px rgba(28, 41, 126,.25);
}
.usr-modal-submit:hover { opacity: .9; transform: translateY(-1px); }
.usr-modal-submit i, .usr-modal-cancel i { width: 15px; height: 15px; }
</style>
@endpush

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
@php
    $activeTab = request('tab', 'directores');
    $tabsDisponibles = ['directores', 'secretarias', 'docentes', 'estudiantes', 'auxiliares'];
    if (!in_array($activeTab, $tabsDisponibles, true)) {
        $activeTab = 'directores';
    }

    $tabCounts = [
        'directores'  => $directores->total(),
        'secretarias' => $secretarias->total(),
        'docentes'    => $docentes->total(),
        'estudiantes' => $estudiantes->total(),
        'auxiliares'  => $auxiliares->total(),
    ];

    $avatarClass = [
        'directores'  => 'av-director',
        'secretarias' => 'av-secretaria',
        'docentes'    => 'av-docente',
        'estudiantes' => 'av-estudiante',
        'auxiliares'  => 'av-auxiliar',
    ];

    $badgeClass = [
        'Director'   => 'usr-badge-director',
        'Secretaria' => 'usr-badge-secretaria',
        'Docente'    => 'usr-badge-docente',
        'Estudiante' => 'usr-badge-estudiante',
        'Auxiliar'   => 'usr-badge-auxiliar',
    ];
@endphp

<div class="usr-wrap">

    {{-- ── Header ──────────────────────────────────────────────── --}}
    <div class="usr-header">
        <div class="usr-header-left">
            <h4><i data-feather="users"></i> Gestión de Usuarios</h4>
            <p>Administra los usuarios del sistema por rol</p>
        </div>
        @if ($activeTab !== 'estudiantes')
            <button class="usr-header-btn" data-bs-toggle="modal"
                data-bs-target="{{ $activeTab === 'docentes' ? '#modalDocente' : '#modalUsuario' }}"
                @if($activeTab === 'auxiliares') data-preset-role="Auxiliar" @endif>
                <i data-feather="user-plus"></i>
                @if($activeTab === 'docentes') Nuevo Docente
                @elseif($activeTab === 'auxiliares') Nuevo Auxiliar
                @else Nuevo Usuario @endif
            </button>
        @endif
    </div>

    @if (session('status'))
        <div class="alert alert-success d-flex align-items-center gap-2 mb-3" style="border-radius:12px;">
            <i data-feather="check-circle" style="width:16px;height:16px;"></i>
            {{ session('status') }}
        </div>
    @endif

    @if (session('password_temporal'))
        <div class="alert alert-warning d-flex align-items-center gap-2 mb-3" style="border-radius:12px;">
            <i data-feather="key" style="width:16px;height:16px;"></i>
            <div>
                Nueva contraseña generada para <strong>{{ session('password_temporal_user') ?? 'el usuario' }}</strong>:
                <strong>{{ session('password_temporal') }}</strong>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger d-flex align-items-start gap-2 mb-3" style="border-radius:12px;">
            <i data-feather="alert-circle" style="width:16px;height:16px; margin-top:2px;"></i>
            <div>
                <strong>No se pudo guardar:</strong>
                <div>{{ $errors->first() }}</div>
            </div>
        </div>
    @endif

    {{-- ── Tabs ────────────────────────────────────────────────── --}}
    <div class="usr-tabs">
        @php
            $tabsConfig = [
                'directores'  => ['icon' => 'briefcase',  'label' => 'Directores'],
                'secretarias' => ['icon' => 'users',      'label' => 'Secretarias'],
                'docentes'    => ['icon' => 'book',       'label' => 'Docentes'],
                'estudiantes' => ['icon' => 'book-open',  'label' => 'Estudiantes'],
                'auxiliares'  => ['icon' => 'user-check', 'label' => 'Auxiliares'],
            ];
        @endphp
        @foreach ($tabsConfig as $key => $cfg)
            <a class="usr-tab {{ $activeTab === $key ? 'is-active' : '' }}"
               href="{{ route('admin-sistema.usuarios.index', ['tab' => $key]) }}">
                <i data-feather="{{ $cfg['icon'] }}"></i>
                {{ $cfg['label'] }}
                <span class="usr-tab-count">{{ $tabCounts[$key] }}</span>
            </a>
        @endforeach
    </div>

    {{-- ── Aviso Estudiantes ────────────────────────────────────── --}}
    @if ($activeTab === 'estudiantes')
        <div class="usr-notice">
            <i data-feather="info"></i>
            <span>Los estudiantes se registran automáticamente al completar el proceso de <strong>matrícula</strong>. No es posible crearlos manualmente desde aquí.</span>
        </div>
    @endif

    {{-- ── Tabla dinámica ──────────────────────────────────────── --}}
    @php
        $tabData = [
            'directores'  => ['col' => $directores,  'q' => $qDirector,   'qName' => 'q_director',   'icon' => 'briefcase',  'placeholder' => 'Buscar director...'],
            'secretarias' => ['col' => $secretarias, 'q' => $qSecretaria, 'qName' => 'q_secretaria', 'icon' => 'users',      'placeholder' => 'Buscar secretaria...'],
            'docentes'    => ['col' => $docentes,    'q' => $qDocente,    'qName' => 'q_docente',    'icon' => 'book',       'placeholder' => 'Buscar docente...'],
            'estudiantes' => ['col' => $estudiantes, 'q' => $qEstudiante, 'qName' => 'q_estudiante', 'icon' => 'book-open',  'placeholder' => 'Buscar estudiante...'],
            'auxiliares'  => ['col' => $auxiliares,  'q' => $qAuxiliar,   'qName' => 'q_auxiliar',   'icon' => 'user-check', 'placeholder' => 'Buscar auxiliar...'],
        ];
        $td = $tabData[$activeTab];
        $avCls = $avatarClass[$activeTab];
        $tabLabel = $tabsConfig[$activeTab]['label'];
    @endphp

    <div class="usr-card">
        {{-- Header del card --}}
        <div class="usr-card-header">
            <h6 class="usr-card-title">
                <i data-feather="{{ $tabsConfig[$activeTab]['icon'] }}"></i>
                {{ $tabLabel }}
            </h6>
            <form method="GET" action="{{ route('admin-sistema.usuarios.index') }}" class="usr-search-form">
                <input type="hidden" name="tab" value="{{ $activeTab }}">
                <div class="usr-search-wrap">
                    <span class="usr-search-icon"><i data-feather="search"></i></span>
                    <input type="text" name="{{ $td['qName'] }}" placeholder="{{ $td['placeholder'] }}"
                           value="{{ $td['q'] }}">
                </div>
                <button type="submit" class="usr-search-btn">
                    <i data-feather="search"></i> Buscar
                </button>
                @if ($td['q'])
                    <a href="{{ route('admin-sistema.usuarios.index', ['tab' => $activeTab]) }}"
                       class="usr-search-btn" style="background:linear-gradient(135deg,#64748b,#94a3b8);">
                        <i data-feather="x"></i>
                    </a>
                @endif
            </form>
        </div>

        {{-- Tabla --}}
        <div class="table-responsive">
            <table class="usr-table">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Correo electrónico</th>
                        <th>Rol</th>
                        <th>Registro</th>
                        @if (in_array($activeTab, ['directores', 'secretarias', 'docentes', 'auxiliares'], true))
                            <th>Acciones</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse ($td['col'] as $usuario)
                        @php
                            $inicial = strtoupper(substr($usuario->name ?? 'U', 0, 1));
                            $rolNombre = $usuario->roles->first()?->name ?? '—';
                            $bdgCls = $badgeClass[$rolNombre] ?? 'usr-badge-default';
                            $esDocenteInactivo = $activeTab === 'docentes' && $usuario->docente?->estado === 'inactivo';
                        @endphp
                        <tr class="{{ $esDocenteInactivo ? 'usr-row-inactivo' : '' }}">
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="usr-avatar {{ $avCls }}">{{ $inicial }}</div>
                                    <div>
                                        <span class="usr-user-name">{{ $usuario->name }}</span>
                                        @if ($esDocenteInactivo)
                                            <span class="usr-badge-retirado ms-1"><i data-feather="user-x" style="width:10px;height:10px;"></i> Retirado</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="usr-user-email">{{ $usuario->email }}</td>
                            <td>
                                <span class="usr-badge {{ $bdgCls }}">{{ $rolNombre }}</span>
                            </td>
                            <td class="usr-date">{{ $usuario->created_at?->format('d/m/Y') }}</td>
                            @if (in_array($activeTab, ['directores', 'secretarias', 'docentes', 'auxiliares'], true))
                                <td>
                                    <div style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;">
                                        <button type="button"
                                                class="usr-action-btn"
                                                data-bs-toggle="modal"
                                                data-bs-target="{{ $activeTab === 'docentes' ? '#modalEditarDocente' : '#modalEditarSecretaria' }}"
                                                data-edit-url="{{ route('admin-sistema.usuarios.update', $usuario) }}"
                                                data-tab="{{ $activeTab }}"
                                                data-id="{{ $usuario->id }}"
                                                data-role="{{ $rolNombre }}"
                                                data-registro="{{ $usuario->created_at?->format('d/m/Y H:i') }}"
                                                data-name="{{ $usuario->name }}"
                                                data-email="{{ $usuario->email }}"
                                                data-dni="{{ $usuario->dni }}"
                                                data-doc-nombres="{{ $usuario->docente?->nombres }}"
                                                data-doc-apellidos="{{ $usuario->docente?->apellidos }}"
                                                data-doc-dni="{{ $usuario->docente?->dni }}"
                                                data-doc-fecha-nacimiento="{{ $usuario->docente?->fecha_nacimiento ? \Illuminate\Support\Carbon::parse($usuario->docente->fecha_nacimiento)->format('Y-m-d') : '' }}"
                                                data-doc-sexo="{{ $usuario->docente?->sexo }}"
                                                data-doc-telefono="{{ $usuario->docente?->telefono }}"
                                                data-doc-direccion="{{ $usuario->docente?->direccion }}"
                                                data-doc-ciudad="{{ $usuario->docente?->ciudad }}"
                                                data-doc-grado-academico="{{ $usuario->docente?->grado_academico }}"
                                                data-doc-especialidad="{{ $usuario->docente?->especialidad }}"
                                                data-doc-tipo-contrato="{{ $usuario->docente?->tipo_contrato }}"
                                                data-doc-fecha-ingreso="{{ $usuario->docente?->fecha_ingreso ? \Illuminate\Support\Carbon::parse($usuario->docente->fecha_ingreso)->format('Y-m-d') : '' }}"
                                                data-doc-estado="{{ $usuario->docente?->estado }}">
                                            <i data-feather="eye"></i>
                                            Ver / Editar
                                        </button>
                                        @if ($activeTab === 'docentes')
                                            @if ($esDocenteInactivo)
                                                <button type="button"
                                                        class="usr-btn-reactivar btn-toggle-estado"
                                                        data-toggle-url="{{ route('admin-sistema.usuarios.toggle-estado', $usuario) }}"
                                                        data-nombre="{{ $usuario->name }}"
                                                        data-accion="reactivar">
                                                    <i data-feather="user-check"></i>
                                                    Reactivar
                                                </button>
                                            @else
                                                <a href="{{ route('admin-sistema.usuarios.carnet.individual', $usuario) }}"
                                                   target="_blank"
                                                   class="usr-action-btn"
                                                   style="background:#1f2e8d;color:#fff;border-color:#1f2e8d;text-decoration:none;">
                                                    <i data-feather="credit-card"></i>
                                                    Carnet
                                                </a>
                                                <button type="button"
                                                        class="usr-btn-anular btn-toggle-estado"
                                                        data-toggle-url="{{ route('admin-sistema.usuarios.toggle-estado', $usuario) }}"
                                                        data-nombre="{{ $usuario->name }}"
                                                        data-accion="anular">
                                                    <i data-feather="user-minus"></i>
                                                    Anular
                                                </button>
                                            @endif
                                        @else
                                            <a href="{{ route('admin-sistema.usuarios.carnet.individual', $usuario) }}"
                                               target="_blank"
                                               class="usr-action-btn"
                                               style="background:#1f2e8d;color:#fff;border-color:#1f2e8d;text-decoration:none;">
                                                <i data-feather="credit-card"></i>
                                                Carnet
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ in_array($activeTab, ['directores', 'secretarias', 'docentes', 'auxiliares'], true) ? 5 : 4 }}">
                                <div class="usr-empty">
                                    <i data-feather="{{ $tabsConfig[$activeTab]['icon'] }}"></i>
                                    <p>No hay {{ strtolower($tabLabel) }} registrados{{ $activeTab === 'secretarias' ? 'as' : '' }}.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($td['col']->hasPages())
            <div class="usr-pagination jp-table-pagination">
                {{ $td['col']->appends(request()->query())->links() }}
            </div>
        @endif
    </div>

    {{-- ── Modal: Usuario genérico ─────────────────────────────── --}}
    <div class="modal fade usr-modal" id="modalUsuario" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i data-feather="user-plus"></i>
                        Registrar Usuario
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('admin-sistema.usuarios.store') }}">
                    @csrf
                    <input type="hidden" name="tab" id="modalUsuarioTab" value="{{ $activeTab }}">
                    <div class="modal-body">
                        <div class="usr-section-label"><i data-feather="lock"></i> Datos de acceso</div>
                        <div class="row g-3 mb-3">
                            <div class="col-12">
                                <label class="form-label">Nombre completo</label>
                                <input type="text" name="name" class="form-control" placeholder="Ej: Juan Pérez" required />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Correo electrónico</label>
                                <input type="email" name="email" class="form-control" placeholder="correo@ejemplo.com" required />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">DNI</label>
                                <input type="text" name="dni" class="form-control" placeholder="Ej: 12345678" required />
                            </div>
                        </div>
                        <div class="usr-section-label"><i data-feather="shield"></i> Rol del sistema</div>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Rol</label>
                                <select name="role" class="form-select" required>
                                    <option value="">— Selecciona un rol —</option>
                                    @foreach ($roles->whereNotIn('name', ['Estudiante', 'Docente', 'SuperAdmin']) as $item)
                                        <option value="{{ $item->name }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer d-flex gap-2 justify-content-end">
                        <button type="button" class="usr-modal-cancel" data-bs-dismiss="modal">
                            <i data-feather="x"></i> Cancelar
                        </button>
                        <button type="submit" class="usr-modal-submit">
                            <i data-feather="save"></i> Guardar usuario
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ── Modal: Ver/Editar Usuario (Director/Secretaria) ─────────────── --}}
    <div class="modal fade usr-modal" id="modalEditarSecretaria" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i data-feather="user-check"></i>
                        Detalle de Usuario
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="formEditarSecretaria" action="{{ route('admin-sistema.usuarios.update', $secretarias->first()?->id ?? 0) }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="tab" id="editSecretariaTab" value="secretarias">
                    <div class="modal-body">
                        <div class="usr-section-label"><i data-feather="database"></i> Datos del sistema</div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label">ID</label>
                                <input type="text" id="editSecretariaId" class="form-control" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Rol</label>
                                <input type="text" id="editSecretariaRol" class="form-control" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Registro</label>
                                <input type="text" id="editSecretariaRegistro" class="form-control" readonly>
                            </div>
                        </div>

                        <div class="usr-section-label"><i data-feather="user"></i> Información general</div>
                        <div class="row g-3 mb-3">
                            <div class="col-12">
                                <label class="form-label">Nombre completo</label>
                                <input type="text" name="name" id="editSecretariaName" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Usuario (correo)</label>
                                <input type="email" name="email" id="editSecretariaEmail" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">DNI</label>
                                <input type="text" name="dni" id="editSecretariaDni" class="form-control" required>
                            </div>
                        </div>

                        <div class="usr-section-label"><i data-feather="lock"></i> Contraseña</div>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Nueva contraseña (opcional)</label>
                                <input type="password" name="password" id="editSecretariaPassword" class="form-control" minlength="8"
                                       placeholder="Deja en blanco para mantener la actual">
                                <small class="text-muted d-block mt-1">
                                    Por seguridad, la contraseña actual no se puede visualizar porque está cifrada.
                                </small>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Confirmar nueva contraseña</label>
                                <input type="password" name="password_confirmation" id="editSecretariaPasswordConfirm" class="form-control" minlength="8"
                                       placeholder="Repite la nueva contraseña">
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="1" id="editSecretariaGenerarPassword" name="generar_password">
                                    <label class="form-check-label" for="editSecretariaGenerarPassword">
                                        Generar nueva contraseña automáticamente y mostrarla al guardar
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer d-flex gap-2 justify-content-end">
                        <button type="button" class="usr-modal-cancel" data-bs-dismiss="modal">
                            <i data-feather="x"></i> Cancelar
                        </button>
                        <button type="submit" class="usr-modal-submit">
                            <i data-feather="save"></i> Guardar cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ── Modal: Ver/Editar Docente ───────────────────────────────────── --}}
    <div class="modal fade usr-modal" id="modalEditarDocente" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i data-feather="user-check"></i>
                        Detalle de Docente
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="formEditarDocente" action="{{ route('admin-sistema.usuarios.update', $docentes->first()?->id ?? 0) }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="tab" value="docentes">
                    <div class="modal-body">
                        <div class="usr-section-label"><i data-feather="database"></i> Datos del sistema</div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label">ID</label>
                                <input type="text" id="editDocenteId" class="form-control" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Rol</label>
                                <input type="text" id="editDocenteRol" class="form-control" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Registro</label>
                                <input type="text" id="editDocenteRegistro" class="form-control" readonly>
                            </div>
                        </div>

                        <div class="usr-section-label"><i data-feather="lock"></i> Acceso</div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Usuario (correo)</label>
                                <input type="email" name="email" id="editDocenteEmail" class="form-control" required>
                            </div>
                            <div class="col-md-6 d-flex align-items-end">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="1" id="editDocenteGenerarPassword" name="generar_password">
                                    <label class="form-check-label" for="editDocenteGenerarPassword">
                                        Generar nueva contraseña automática
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nueva contraseña (opcional)</label>
                                <input type="password" name="password" id="editDocentePassword" class="form-control" minlength="8"
                                       placeholder="Deja en blanco para mantener la actual">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Confirmar nueva contraseña</label>
                                <input type="password" name="password_confirmation" id="editDocentePasswordConfirm" class="form-control" minlength="8"
                                       placeholder="Repite la nueva contraseña">
                            </div>
                        </div>

                        <div class="usr-section-label"><i data-feather="user"></i> Datos personales</div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Nombres</label>
                                <input type="text" name="nombres" id="editDocenteNombres" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Apellidos</label>
                                <input type="text" name="apellidos" id="editDocenteApellidos" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">DNI</label>
                                <input type="text" name="dni" id="editDocenteDni" class="form-control" maxlength="20" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Fecha de nacimiento</label>
                                <input type="date" name="fecha_nacimiento" id="editDocenteFechaNacimiento" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Sexo</label>
                                <select name="sexo" id="editDocenteSexo" class="form-select">
                                    <option value="">— Seleccionar —</option>
                                    <option value="M">Masculino</option>
                                    <option value="F">Femenino</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Teléfono</label>
                                <input type="text" name="telefono" id="editDocenteTelefono" class="form-control" maxlength="15">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Ciudad</label>
                                <input type="text" name="ciudad" id="editDocenteCiudad" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Dirección</label>
                                <input type="text" name="direccion" id="editDocenteDireccion" class="form-control">
                            </div>
                        </div>

                        <div class="usr-section-label"><i data-feather="award"></i> Datos profesionales</div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Grado académico</label>
                                <input type="text" name="grado_academico" id="editDocenteGradoAcademico" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Especialidad</label>
                                <input type="text" name="especialidad" id="editDocenteEspecialidad" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Tipo de contrato</label>
                                <select name="tipo_contrato" id="editDocenteTipoContrato" class="form-select">
                                    <option value="">— Seleccionar —</option>
                                    <option value="Nombrado">Nombrado</option>
                                    <option value="Contratado">Contratado</option>
                                    <option value="Suplente">Suplente</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Fecha de ingreso</label>
                                <input type="date" name="fecha_ingreso" id="editDocenteFechaIngreso" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Estado</label>
                                <select name="estado_docente" id="editDocenteEstado" class="form-select">
                                    <option value="activo">Activo</option>
                                    <option value="inactivo">Inactivo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer d-flex gap-2 justify-content-end">
                        <button type="button" class="usr-modal-cancel" data-bs-dismiss="modal">
                            <i data-feather="x"></i> Cancelar
                        </button>
                        <button type="submit" class="usr-modal-submit">
                            <i data-feather="save"></i> Guardar cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ── Modal: Docente ───────────────────────────────────────── --}}
    <div class="modal fade usr-modal" id="modalDocente" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i data-feather="user-plus"></i>
                        Registrar Docente
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('admin-sistema.usuarios.store') }}">
                    @csrf
                    <input type="hidden" name="role" value="Docente">
                    <div class="modal-body">

                        <div class="usr-section-label"><i data-feather="lock"></i> Datos de acceso</div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Correo electrónico</label>
                                <input type="email" name="email" class="form-control" placeholder="correo@ejemplo.com" required />
                            </div>
                            <div class="col-md-6 d-flex align-items-end">
                                <div class="alert alert-light border w-100 mb-0 py-2">
                                    La contraseña inicial será el DNI del docente.
                                </div></div>
                        </div>

                        <div class="usr-section-label"><i data-feather="user"></i> Datos personales</div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Nombres</label>
                                <input type="text" name="nombres" class="form-control" placeholder="Ej: Juan Carlos" required />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Apellidos</label>
                                <input type="text" name="apellidos" class="form-control" placeholder="Ej: Pérez García" required />
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">DNI</label>
                                <input type="text" name="dni" class="form-control" maxlength="20" placeholder="12345678" required />
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Teléfono</label>
                                <input type="text" name="telefono" class="form-control" maxlength="15" placeholder="987654321" />
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Sexo</label>
                                <select name="sexo" class="form-select">
                                    <option value="">— Seleccionar —</option>
                                    <option value="M">Masculino</option>
                                    <option value="F">Femenino</option>
                                </select>
                            </div>
                        </div>

                        <div class="usr-section-label"><i data-feather="award"></i> Datos profesionales</div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Especialidad</label>
                                <input type="text" name="especialidad" class="form-control" placeholder="Ej: Matemáticas" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tipo de contrato</label>
                                <select name="tipo_contrato" class="form-select">
                                    <option value="">— Seleccionar —</option>
                                    <option value="Nombrado">Nombrado</option>
                                    <option value="Contratado">Contratado</option>
                                    <option value="Suplente">Suplente</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer d-flex gap-2 justify-content-end">
                        <button type="button" class="usr-modal-cancel" data-bs-dismiss="modal">
                            <i data-feather="x"></i> Cancelar
                        </button>
                        <button type="submit" class="usr-modal-submit">
                            <i data-feather="save"></i> Registrar Docente
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ── Modal: Confirmar Anular / Reactivar Docente ──────────────────── --}}
    <div class="modal fade usr-modal" id="modalConfirmarToggle" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="toggleModalTitle">
                        <i data-feather="alert-triangle"></i>
                        Confirmar acción
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="toggleModalBody"></div>
                <div class="modal-footer d-flex gap-2 justify-content-end">
                    <button type="button" class="usr-modal-cancel" data-bs-dismiss="modal">
                        <i data-feather="x"></i> Cancelar
                    </button>
                    <button type="button" class="btn" id="toggleModalBtn">Confirmar</button>
                </div>
            </div>
        </div>
    </div>

@push('scripts')
<script>
    document.querySelectorAll('.modal').forEach(function(el) {
        el.addEventListener('shown.bs.modal', function() { feather.replace(); });
    });

    const modalEditarSecretaria = document.getElementById('modalEditarSecretaria');
    if (modalEditarSecretaria) {
        modalEditarSecretaria.addEventListener('show.bs.modal', function (event) {
            const trigger = event.relatedTarget;
            if (!trigger) return;

            const form = document.getElementById('formEditarSecretaria');
            const tabInput = document.getElementById('editSecretariaTab');
            const idInput = document.getElementById('editSecretariaId');
            const rolInput = document.getElementById('editSecretariaRol');
            const registroInput = document.getElementById('editSecretariaRegistro');
            const nameInput = document.getElementById('editSecretariaName');
            const emailInput = document.getElementById('editSecretariaEmail');
            const dniInput = document.getElementById('editSecretariaDni');
            const passwordInput = document.getElementById('editSecretariaPassword');
            const passwordConfirmInput = document.getElementById('editSecretariaPasswordConfirm');
            const generarPasswordInput = document.getElementById('editSecretariaGenerarPassword');

            const togglePasswordFields = function () {
                const generar = !!generarPasswordInput?.checked;
                if (passwordInput) {
                    passwordInput.value = '';
                    passwordInput.disabled = generar;
                }
                if (passwordConfirmInput) {
                    passwordConfirmInput.value = '';
                    passwordConfirmInput.disabled = generar;
                }
            };

            if (form) {
                form.action = trigger.getAttribute('data-edit-url') || form.action;
            }
            if (tabInput) {
                tabInput.value = trigger.getAttribute('data-tab') || 'secretarias';
            }
            if (idInput) {
                idInput.value = trigger.getAttribute('data-id') || '';
            }
            if (rolInput) {
                rolInput.value = trigger.getAttribute('data-role') || '';
            }
            if (registroInput) {
                registroInput.value = trigger.getAttribute('data-registro') || '';
            }
            if (nameInput) {
                nameInput.value = trigger.getAttribute('data-name') || '';
            }
            if (emailInput) {
                emailInput.value = trigger.getAttribute('data-email') || '';
            }
            if (dniInput) {
                dniInput.value = trigger.getAttribute('data-dni') || '';
            }
            if (generarPasswordInput) {
                generarPasswordInput.checked = false;
                generarPasswordInput.onchange = togglePasswordFields;
            }
            togglePasswordFields();
        });
    }

    // Pre-seleccionar rol cuando se abre el modal desde un tab con preset
    const modalUsuario = document.getElementById('modalUsuario');
    if (modalUsuario) {
        modalUsuario.addEventListener('show.bs.modal', function (event) {
            const trigger = event.relatedTarget;
            const presetRole = trigger ? trigger.getAttribute('data-preset-role') : null;
            const roleSelect = modalUsuario.querySelector('select[name="role"]');
            const tabInput = document.getElementById('modalUsuarioTab');
            if (roleSelect && presetRole) {
                roleSelect.value = presetRole;
                roleSelect.closest('.row')?.classList.add('d-none');
                if (tabInput) tabInput.value = 'auxiliares';
            } else if (roleSelect) {
                roleSelect.value = '';
                roleSelect.closest('.row')?.classList.remove('d-none');
                if (tabInput) tabInput.value = '{{ $activeTab }}';
            }
        });
    }

    const modalEditarDocente = document.getElementById('modalEditarDocente');
    if (modalEditarDocente) {
        modalEditarDocente.addEventListener('show.bs.modal', function (event) {
            const trigger = event.relatedTarget;
            if (!trigger) return;

            const form = document.getElementById('formEditarDocente');
            const idInput = document.getElementById('editDocenteId');
            const rolInput = document.getElementById('editDocenteRol');
            const registroInput = document.getElementById('editDocenteRegistro');
            const emailInput = document.getElementById('editDocenteEmail');
            const passwordInput = document.getElementById('editDocentePassword');
            const passwordConfirmInput = document.getElementById('editDocentePasswordConfirm');
            const generarPasswordInput = document.getElementById('editDocenteGenerarPassword');
            const nombresInput = document.getElementById('editDocenteNombres');
            const apellidosInput = document.getElementById('editDocenteApellidos');
            const dniInput = document.getElementById('editDocenteDni');
            const fechaNacimientoInput = document.getElementById('editDocenteFechaNacimiento');
            const sexoInput = document.getElementById('editDocenteSexo');
            const telefonoInput = document.getElementById('editDocenteTelefono');
            const ciudadInput = document.getElementById('editDocenteCiudad');
            const direccionInput = document.getElementById('editDocenteDireccion');
            const gradoAcademicoInput = document.getElementById('editDocenteGradoAcademico');
            const especialidadInput = document.getElementById('editDocenteEspecialidad');
            const tipoContratoInput = document.getElementById('editDocenteTipoContrato');
            const fechaIngresoInput = document.getElementById('editDocenteFechaIngreso');
            const estadoInput = document.getElementById('editDocenteEstado');

            const togglePasswordFields = function () {
                const generar = !!generarPasswordInput?.checked;
                if (passwordInput) {
                    passwordInput.value = '';
                    passwordInput.disabled = generar;
                }
                if (passwordConfirmInput) {
                    passwordConfirmInput.value = '';
                    passwordConfirmInput.disabled = generar;
                }
            };

            if (form) {
                form.action = trigger.getAttribute('data-edit-url') || form.action;
            }
            if (idInput) idInput.value = trigger.getAttribute('data-id') || '';
            if (rolInput) rolInput.value = trigger.getAttribute('data-role') || '';
            if (registroInput) registroInput.value = trigger.getAttribute('data-registro') || '';
            if (emailInput) emailInput.value = trigger.getAttribute('data-email') || '';
            if (nombresInput) nombresInput.value = trigger.getAttribute('data-doc-nombres') || '';
            if (apellidosInput) apellidosInput.value = trigger.getAttribute('data-doc-apellidos') || '';
            if (dniInput) dniInput.value = trigger.getAttribute('data-doc-dni') || '';
            if (fechaNacimientoInput) fechaNacimientoInput.value = trigger.getAttribute('data-doc-fecha-nacimiento') || '';
            if (sexoInput) sexoInput.value = trigger.getAttribute('data-doc-sexo') || '';
            if (telefonoInput) telefonoInput.value = trigger.getAttribute('data-doc-telefono') || '';
            if (ciudadInput) ciudadInput.value = trigger.getAttribute('data-doc-ciudad') || '';
            if (direccionInput) direccionInput.value = trigger.getAttribute('data-doc-direccion') || '';
            if (gradoAcademicoInput) gradoAcademicoInput.value = trigger.getAttribute('data-doc-grado-academico') || '';
            if (especialidadInput) especialidadInput.value = trigger.getAttribute('data-doc-especialidad') || '';
            if (tipoContratoInput) tipoContratoInput.value = trigger.getAttribute('data-doc-tipo-contrato') || '';
            if (fechaIngresoInput) fechaIngresoInput.value = trigger.getAttribute('data-doc-fecha-ingreso') || '';
            if (estadoInput) estadoInput.value = trigger.getAttribute('data-doc-estado') || 'activo';

            if (generarPasswordInput) {
                generarPasswordInput.checked = false;
                generarPasswordInput.onchange = togglePasswordFields;
            }
            togglePasswordFields();
        });
    }

    // ── Anular / Reactivar Docente ───────────────────────────────────────
    (function () {
        const modalEl = document.getElementById('modalConfirmarToggle');
        if (!modalEl) return;

        const modal     = new bootstrap.Modal(modalEl);
        const titleEl   = document.getElementById('toggleModalTitle');
        const bodyEl    = document.getElementById('toggleModalBody');
        const btnOk     = document.getElementById('toggleModalBtn');

        let pendingUrl  = null;

        document.addEventListener('click', function (e) {
            const btn = e.target.closest('.btn-toggle-estado');
            if (!btn) return;

            pendingUrl   = btn.dataset.toggleUrl;
            const nombre = btn.dataset.nombre;
            const accion = btn.dataset.accion;

            if (accion === 'anular') {
                titleEl.textContent = 'Anular Docente';
                bodyEl.innerHTML    = `<p>¿Seguro que deseas <strong>anular</strong> al docente <strong>${nombre}</strong>?</p>
                    <p class="text-muted small mb-0">Ya no figurará en carnets ni en los selectores de cursos y horarios.</p>`;
                btnOk.className     = 'btn btn-danger';
                btnOk.innerHTML     = '<i data-feather="user-minus" style="width:15px;height:15px;margin-right:5px;"></i>Sí, anular';
            } else {
                titleEl.textContent = 'Reactivar Docente';
                bodyEl.innerHTML    = `<p>¿Deseas <strong>reactivar</strong> al docente <strong>${nombre}</strong>?</p>
                    <p class="text-muted small mb-0">Volverá a aparecer en carnets y asignaciones de cursos.</p>`;
                btnOk.className     = 'btn btn-success';
                btnOk.innerHTML     = '<i data-feather="user-check" style="width:15px;height:15px;margin-right:5px;"></i>Sí, reactivar';
            }

            feather.replace();
            modal.show();
        });

        btnOk.addEventListener('click', function () {
            if (!pendingUrl) return;
            btnOk.disabled = true;

            fetch(pendingUrl, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
            })
            .then(r => r.json())
            .then(data => {
                modal.hide();
                if (data.ok) {
                    // Mostrar toast y recargar
                    const toast = document.createElement('div');
                    toast.className = 'position-fixed bottom-0 end-0 p-3';
                    toast.style.zIndex = 9999;
                    toast.innerHTML = `
                        <div class="toast show align-items-center text-white ${data.estado === 'inactivo' ? 'bg-danger' : 'bg-success'} border-0" role="alert">
                            <div class="d-flex">
                                <div class="toast-body fw-semibold">${data.msg}</div>
                                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                            </div>
                        </div>`;
                    document.body.appendChild(toast);
                    setTimeout(() => location.reload(), 1400);
                } else {
                    alert(data.error || 'Error al procesar la solicitud.');
                    btnOk.disabled = false;
                }
            })
            .catch(() => {
                alert('Error de conexión. Intente de nuevo.');
                btnOk.disabled = false;
            });
        });
    })();
</script>
@endpush

@endsection

