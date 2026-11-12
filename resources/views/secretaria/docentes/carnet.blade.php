@extends('layouts.mantis')

@section('title', 'Carnet Docente | Secretaria')
@section('page-title', 'Carnet Docente')
@section('body-class', 'role-secretaria')

@push('styles')
    <link rel="stylesheet" href="{{ url('css/secretaria-dashboard.css') }}">
    <style>
        .jp-carnet-wrap { max-width: 520px; margin: 0 auto; }
        .jp-carnet-card { border-radius: 16px; overflow: hidden; }
        .jp-carnet-head { padding: 18px 18px; background: linear-gradient(135deg, #151f5e, #1c2a81); color:#fff; }
        .jp-carnet-body { padding: 18px; background:#fff; }
        .jp-qr-box { display:flex; justify-content:center; padding: 14px; border:1px dashed #e5e7eb; border-radius:14px; }
        @media print {
            .no-print { display:none !important; }
            body { background:#fff !important; }
        }
    </style>
@endpush

@section('sidebar')
    @include('secretaria.partials.sidebar')
@endsection

@section('content')
    <div class="jp-carnet-wrap mt-3">
        <div class="card jp-carnet-card shadow-sm">
            <div class="jp-carnet-head">
                <div class="d-flex align-items-center justify-content-between gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <div>
                            <div style="font-weight:700; line-height:1.1;">R.A.S</div>
                            <div style="opacity:.9; font-size:.9rem;">Carnet de Docente</div>
                        </div>
                    </div>

                    <button class="btn btn-light no-print" onclick="window.print()">
                        Imprimir
                    </button>
                </div>
            </div>

            <div class="jp-carnet-body">
                <div class="mb-2">
                    <div class="text-muted small">Docente</div>
                    <div class="fw-bold" style="font-size:1.1rem;">
                        {{ $docente->apellidos }} {{ $docente->nombres }}
                    </div>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <div class="text-muted small">DNI</div>
                        <div class="fw-semibold">{{ $docente->dni ?? '-' }}</div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted small">Código</div>
                        <div class="fw-semibold">{{ $docente->codigo ?? '-' }}</div>
                    </div>
                </div>

                <div class="jp-qr-box">
                    {!! QrCode::size(210)->margin(1)->generate($docente->carnet_codigo) !!}
                </div>

                <div class="text-center mt-2">
                    <div class="text-muted small">Código QR</div>
                    <div class="fw-semibold">{{ $docente->carnet_codigo }}</div>
                </div>
            </div>
        </div>
    </div>
@endsection