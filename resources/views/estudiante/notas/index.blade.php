@extends('layouts.mantis')

@section('title', 'Notas | Estudiante')
@section('page-title', 'Notas')
@section('body-class', 'role-estudiante')

@push('styles')
    <link rel="stylesheet" href="{{ url('css/secretaria-dashboard.css') }}">
    <link rel="stylesheet" href="{{ url('css/estudiante-modules.css') }}">
@endpush

@section('sidebar')
    @include('estudiante.partials.sidebar')
@endsection

@section('content')
    {{-- Botón volver --}}
    <a class="est-back-link" href="{{ route('estudiante.dashboard') }}">
        <i data-feather="arrow-left"></i> Volver al panel
    </a>

    {{-- Hero --}}
    <div class="est-module-hero">
        <div class="est-module-hero__left">
            <div class="est-module-hero__icon">
                <i data-feather="book-open"></i>
            </div>
            <div>
                <h1 class="est-module-hero__title">Mis notas</h1>
                <p class="est-module-hero__sub">Revisa tus cursos y el avance por evaluación.</p>
            </div>
        </div>
        <div class="est-module-hero__right">
            <form method="GET" class="est-filter-form">
                <div>
                    <div class="form-label">Año escolar y período</div>
                    <div>
                        <select name="anio_escolar_id" class="form-select">
                            @foreach ($anios as $anio)
                                <option value="{{ $anio->id }}" @selected((string) $anioId === (string) $anio->id)>
                                    {{ $anio->nombre }}
                                </option>
                            @endforeach
                        </select>
                        <select name="periodo_academico_id" class="form-select">
                            @foreach ($periodosAcademicos as $periodoAcademico)
                                <option value="{{ $periodoAcademico->id }}"
                                    @selected((string) $periodoAcademicoId === (string) $periodoAcademico->id)>
                                    {{ $periodoAcademico->codigo }} - {{ $periodoAcademico->nombre }}
                                </option>
                            @endforeach
                        </select>
                        <button class="est-btn-filter" type="submit">
                            <i data-feather="search" style="width:14px;height:14px;display:inline;"></i>
                            Ver
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Botón Ver Detalle --}}
    <div class="est-section-card" style="margin-bottom:16px;">
        <div class="est-section-card__header">
            <div>
                <div class="est-section-card__title">Detalle de evaluaciones</div>
                <div class="est-section-card__sub">Consulta el desglose por tipo de evaluación</div>
            </div>
            <div style="display:flex; gap:8px;">
                <button type="button" class="est-btn-primary" id="btnVerDetalleTodo">
                    <i data-feather="eye" style="width:15px;height:15px;"></i> Ver detalle
                </button>
                <button type="button" class="est-btn-outline d-none" id="btnOcultarDetalleTodo">
                    <i data-feather="eye-off" style="width:15px;height:15px;"></i> Ocultar
                </button>
            </div>
        </div>
        <div id="panelDetalleTodo" class="d-none" style="padding:16px 20px;">
            <div id="detalleTodoBody">
                <div class="text-center" style="padding:24px; color:#9ca3af; font-size:.86rem;">
                    Presiona "Ver detalle" para cargar las evaluaciones.
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla de notas --}}
    <div class="est-section-card">
        <div class="est-section-card__header">
            <div>
                <div class="est-section-card__title">Resumen de notas</div>
                <div class="est-section-card__sub">Promedio por asignatura y período</div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="est-table">
                <thead>
                    <tr>
                        <th>Asignatura</th>
                        @foreach ($periodos as $periodo)
                            <th class="text-center">
                                {{ str_replace(['Mensual ', 'Bimestre '], ['M', 'B'], $periodo) }}
                            </th>
                        @endforeach
                        <th class="text-center">Promedio</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($cursosNotas as $fila)
                        @php $promedio = $fila['promedio']; @endphp
                        <tr>
                            <td style="font-weight:600;">{{ $fila['curso'] }}</td>
                            @foreach ($periodos as $periodo)
                                @php
                                    $valor = $fila['valores'][$periodo] ?? null;
                                    $tieneNota = $valor !== null && $valor !== '';
                                @endphp
                                <td class="text-center" style="color:#374151; font-weight:500;">
                                    {{ $tieneNota ? number_format((float) $valor, 2) : '--' }}
                                </td>
                            @endforeach
                            <td class="text-center">
                                <span style="font-weight:900; color:#213095; font-size:.95rem;">
                                    {{ $promedio !== null ? number_format((float) $promedio, 2) : '--' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr class="est-empty">
                            <td colspan="{{ 2 + count($periodos) }}">No hay notas registradas para este año.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
<script>
const btnVer     = document.getElementById('btnVerDetalleTodo');
const btnOcultar = document.getElementById('btnOcultarDetalleTodo');
const panel      = document.getElementById('panelDetalleTodo');
const bodyEl     = document.getElementById('detalleTodoBody');

btnVer?.addEventListener('click', async () => {
    panel.classList.remove('d-none');
    btnOcultar.classList.remove('d-none');
    bodyEl.innerHTML = `<div class="text-center" style="padding:24px;color:#9ca3af;font-size:.86rem;">Cargando...</div>`;

    const params = new URLSearchParams({
        anio_escolar_id: "{{ $anioId }}",
        periodo_academico_id: "{{ $periodoAcademicoId }}",
    });

    const url = `{{ route('estudiante.notas.detalleTodo') }}?` + params.toString();

    try {
        const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        if (!res.ok) throw new Error('No se pudo cargar el detalle.');

        const data = await res.json();

        if (!data.cursos || data.cursos.length === 0) {
            bodyEl.innerHTML = `<div class="text-center" style="padding:24px;color:#9ca3af;font-size:.86rem;">No hay notas registradas.</div>`;
            return;
        }

        bodyEl.innerHTML = data.cursos.map((c, idx) => {
            const colId = `curso_det_${idx}`;

            const bloques = (c.evaluaciones || []).map(ev => {
                const finalTxt = (ev.final !== null && ev.final !== undefined && ev.final !== '')
                    ? Number(ev.final).toFixed(2) : '--';

                const filas = (ev.items || []).length
                    ? ev.items.map(it => {
                        const peso  = it.peso  !== null && it.peso  !== undefined ? Number(it.peso).toFixed(0)  + '%' : '--';
                        const nota  = it.nota  !== null && it.nota  !== undefined && it.nota  !== '' ? Number(it.nota).toFixed(2) : '--';
                        const fecha = it.fecha ? it.fecha : '--';
                        return `<tr>
                            <td>${it.tipo ?? ''}</td>
                            <td class="text-center">${peso}</td>
                            <td class="text-center">${nota}</td>
                            <td class="text-center">${fecha}</td>
                        </tr>`;
                    }).join('')
                    : `<tr><td colspan="4" class="text-center" style="color:#9ca3af;padding:16px;">Sin registros</td></tr>`;

                return `
                    <div style="margin-bottom:12px;border-radius:12px;overflow:hidden;border:1.5px solid rgba(33, 48, 149,.18);">
                        <div style="background:linear-gradient(90deg,#172167,#213095 55%,#23339e);color:#fff;padding:10px 14px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;">
                            <strong style="font-size:.88rem;">${ev.periodo}</strong>
                            <span style="background:rgba(255,255,255,.2);border:1px solid rgba(255,255,255,.38);color:#fff;font-size:.75rem;font-weight:700;padding:4px 12px;border-radius:999px;">
                                Promedio: ${finalTxt}
                            </span>
                        </div>
                        <div class="table-responsive">
                            <table class="est-table" style="margin:0;">
                                <thead><tr>
                                    <th>Tipo</th>
                                    <th class="text-center">Peso</th>
                                    <th class="text-center">Nota</th>
                                    <th class="text-center">Fecha de carga</th>
                                </tr></thead>
                                <tbody>${filas}</tbody>
                            </table>
                        </div>
                    </div>`;
            }).join('');

            return `
                <div style="margin-bottom:12px;">
                    <button style="width:100%;text-align:left;background:#f5faff;border:1.5px solid rgba(33, 48, 149,.18);border-radius:12px;padding:12px 16px;font-weight:800;color:#06081a;font-size:.88rem;cursor:pointer;"
                            type="button" data-bs-toggle="collapse" data-bs-target="#${colId}">
                        ${c.curso}
                    </button>
                    <div class="collapse show" id="${colId}">
                        <div style="margin-top:8px;">${bloques}</div>
                    </div>
                </div>`;
        }).join('');

    } catch (err) {
        bodyEl.innerHTML = `<div class="text-center text-danger" style="padding:24px;">${err.message}</div>`;
    }
});

btnOcultar?.addEventListener('click', () => {
    panel.classList.add('d-none');
    btnOcultar.classList.add('d-none');
});
</script>
@endpush
