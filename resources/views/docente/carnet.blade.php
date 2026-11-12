@extends('layouts.mantis')

@section('title', 'Mi Carnet | Docente')
@section('page-title', 'Mi Carnet')
@section('body-class', 'role-docente')

@push('styles')
    <link rel="stylesheet" href="{{ url('css/docente-dashboard.css') }}">
    <style>
        .qr-carnet {
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,.08);
            border: 1px solid #e5e7eb;
            width: 340px;
        }
        .qr-carnet__header {
            padding: .65rem 1rem;
            display: flex;
            align-items: center;
            gap: .5rem;
            border-bottom: 3px solid #059669;
        }
        .qr-carnet__logo { width: 32px; height: 32px; object-fit: contain; flex-shrink: 0; }
        .qr-carnet__school { font-size: .75rem; font-weight: 700; color: #374151; line-height: 1.2; }
        .qr-carnet__city   { font-size: .65rem; color: #6b7280; }
        .qr-carnet__rol-badge {
            margin-left: auto;
            background: #059669;
            color: #fff;
            font-size: .65rem;
            font-weight: 800;
            padding: .2rem .65rem;
            border-radius: 20px;
            letter-spacing: .04em;
            white-space: nowrap;
        }
        .qr-carnet__body {
            padding: 1rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .qr-carnet__qr {
            flex-shrink: 0;
            width: 110px; height: 110px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            background: #fff;
            overflow: hidden;
        }
        .qr-carnet__qr svg { width: 110px !important; height: 110px !important; display: block; }
        .qr-carnet__info { flex: 1; min-width: 0; }
        .qr-carnet__name {
            font-size: .85rem;
            font-weight: 800;
            color: #111427;
            line-height: 1.25;
            margin-bottom: .35rem;
            word-break: break-word;
        }
        .qr-carnet__sub  { font-size: .72rem; color: #6b7280; margin-bottom: .45rem; }
        .qr-carnet__code {
            font-size: .65rem;
            font-family: 'Courier New', monospace;
            background: #f3f4f6;
            color: #374151;
            padding: .22rem .45rem;
            border-radius: 4px;
            display: inline-block;
            word-break: break-all;
            font-weight: 700;
        }
        .qr-carnet__footer {
            padding: .4rem 1rem;
            background: #f9fafb;
            border-top: 1px solid #f3f4f6;
            font-size: .62rem;
            color: #9ca3af;
            text-align: center;
        }

        @media print {
            @page { margin: 0.8cm; }
            body * { visibility: hidden !important; }
            #printArea, #printArea * { visibility: visible !important; }
            #printArea {
                position: fixed;
                left: 0; top: 0;
                width: 100% !important;
                display: flex;
                justify-content: center;
                padding-top: 1cm;
            }
            .no-print { display: none !important; }
            .qr-carnet { box-shadow: none !important; border: 1px solid #d1d5db !important; }
        }
    </style>
@endpush

@section('sidebar')
    @include('docente.partials.sidebar')
@endsection

@section('content')
@php
    $anio   = now()->year;
    $nombre = trim(($docente->apellidos ?? '') . ' ' . ($docente->nombres ?? '')) ?: '-';
    $dni    = $docente->dni    ?? '-';
    $codigo = $docente->codigo ?? '-';
@endphp

    <div class="d-flex justify-content-end mb-3 no-print">
        <button class="btn btn-outline-secondary" onclick="window.print()">
            <i data-feather="printer"></i> Imprimir / Guardar PDF
        </button>
    </div>

    <div class="d-flex justify-content-center">
        <div id="printArea">
            <div class="qr-carnet">

                {{-- Cabecera --}}
                <div class="qr-carnet__header">
                    <div>
                        <div class="qr-carnet__school">R.A.S</div>
                    </div>
                    <span class="qr-carnet__rol-badge">DOCENTE</span>
                </div>

                {{-- Cuerpo --}}
                <div class="qr-carnet__body">
                    <div class="qr-carnet__qr">
                        {!! QrCode::size(110)->margin(1)->generate($docente->carnet_codigo) !!}
                    </div>
                    <div class="qr-carnet__info">
                        <div class="qr-carnet__name">{{ $nombre }}</div>
                        <div class="qr-carnet__sub">DNI: {{ $dni }} &bull; Cód: {{ $codigo }}</div>
                        <span class="qr-carnet__code">{{ $docente->carnet_codigo }}</span>
                    </div>
                </div>

                {{-- Pie --}}
                <div class="qr-carnet__footer">
                    Identificación Docente &bull; {{ $anio }} &bull; R.A.S
                </div>

            </div>
        </div>
    </div>

@endsection
