@extends('layouts.mantis')

@section('title', 'Mis Asistencias | Secretaria')
@section('page-title', 'Mis Asistencias')
@section('body-class', 'role-secretaria')

@push('styles')
    <link rel="stylesheet" href="{{ url('css/secretaria-dashboard.css') }}">
    <style>
        .jp-asist-badge-entrada { background: #d1fae5; color: #065f46; font-weight: 600;
                                   padding: 3px 10px; border-radius: 20px; font-size: .8rem; }
        .jp-asist-badge-salida  { background: #e2eefe; color: #18246e; font-weight: 600;
                                   padding: 3px 10px; border-radius: 20px; font-size: .8rem; }
        .jp-empty-state { padding: 60px 20px; text-align: center; color: #9ca3af; }
        .jp-empty-state i { width: 48px; height: 48px; opacity: .4; margin-bottom: 12px; }
    </style>
@endpush

@section('sidebar')
    @include('secretaria.partials.sidebar')
@endsection

@section('content')
<div class="row mt-2">

    {{-- Resumen --}}
    <div class="col-12 mb-3">
        <div class="row g-3">
            <div class="col-sm-4">
                <div class="card text-center py-3 shadow-sm">
                    <div style="font-size:1.8rem; font-weight:700; color:#065f46;">
                        {{ $asistencias->where('tipo','ENTRADA')->count() }}
                    </div>
                    <div class="text-muted small">Entradas registradas</div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="card text-center py-3 shadow-sm">
                    <div style="font-size:1.8rem; font-weight:700; color:#18246e;">
                        {{ $asistencias->where('tipo','SALIDA')->count() }}
                    </div>
                    <div class="text-muted small">Salidas registradas</div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="card text-center py-3 shadow-sm">
                    <div style="font-size:1.8rem; font-weight:700; color:#1f2e8d;">
                        {{ $anio?->anio ?? '—' }}
                    </div>
                    <div class="text-muted small">Año escolar activo</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-body p-0">
                @if($asistencias->isEmpty())
                    <div class="jp-empty-state">
                        <div><i data-feather="calendar"></i></div>
                        <p class="mb-0">No tienes asistencias registradas aún.</p>
                        <p class="small">Escanea tu carnet QR para registrar tu entrada o salida.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Fecha</th>
                                    <th>Día</th>
                                    <th>Tipo</th>
                                    <th>Registrado por</th>
                                    <th>Hora registro</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($asistencias as $asist)
                                <tr>
                                    <td class="ps-3 fw-semibold">
                                        {{ \Carbon\Carbon::parse($asist->fecha)->format('d/m/Y') }}
                                    </td>
                                    <td class="text-muted small">
                                        {{ \Carbon\Carbon::parse($asist->fecha)->locale('es')->dayName }}
                                    </td>
                                    <td>
                                        @if($asist->tipo === 'ENTRADA')
                                            <span class="jp-asist-badge-entrada">ENTRADA</span>
                                        @else
                                            <span class="jp-asist-badge-salida">SALIDA</span>
                                        @endif
                                    </td>
                                    <td class="text-muted small">
                                        {{ $asist->registrador?->name ?? '—' }}
                                    </td>
                                    <td class="text-muted small">
                                        {{ $asist->created_at->format('H:i') }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($asistencias->hasPages())
                        <div class="px-3 py-2">
                            {{ $asistencias->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>

</div>
@endsection
