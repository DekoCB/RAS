@extends('layouts.mantis')

@section('title', 'Registrar notas | Docente')
@section('page-title', 'Registrar notas')
@section('body-class', 'role-docente')

@push('styles')
    <link rel="stylesheet" href="{{ url('css/docente-dashboard.css') }}">
    <style>
        .jp-concept-pill {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .35rem .6rem;
            border-radius: 999px;
            background: rgba(0, 0, 0, .04);
            font-size: .85rem;
            margin-right: .4rem;
            margin-bottom: .4rem;
            white-space: nowrap;
        }

        .jp-final-badge {
            font-weight: 700;
        }

        .jp-input-note {
            min-width: 110px;
        }

        .jp-concept-table th,
        .jp-concept-table td {
            vertical-align: middle;
        }

        .jp-modal-row {
            display: grid;
            grid-template-columns: 1fr 140px 44px;
            gap: .5rem;
            align-items: center;
            margin-bottom: .6rem;
        }

        .jp-modal-row input {
            height: 38px;
        }
    </style>
@endpush

@section('sidebar')
    @include('docente.partials.sidebar')
@endsection

@section('content')
    <div class="card jp-docente-filter mt-2">
        <div class="card-body d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <h5 class="jp-docente-filter-title">Secciones asignadas</h5>
                <p class="jp-docente-filter-sub">
                    Estas secciones corresponden al periodo academico avigente.
                </p>
                <div class="jp-docente-filter-meta">
                    <span class="jp-docente-filter-pill">
                        <i data-feather="calendar"></i>
                        A&ntilde;o {{ $anioId ? $anios->firstWhere('id', (int) $anioId)?->nombre ?? $anioId : date('Y') }}
                    </span>
                    <span class="jp-docente-filter-pill is-accent">
                        <i data-feather="bookmark"></i>
                        {{ $periodosAcademicos->firstWhere('id', (int) $periodoAcademicoId)?->nombre ?? 'Periodo actual' }}
                    </span>
                </div>
            </div>
            <a href="{{ route('docente.notas.index') }}" class="jp-docente-history-btn">
                <i data-feather="clock"></i>
                Ver historial
            </a>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success mt-3">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger mt-3">
            <strong>Revisa la informaci&oacute;n:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($bloqueado)
        <div class="alert alert-danger mt-3">
            La evaluaci&oacute;n seleccionada est&aacute; cerrada. No se permiten cambios.
        </div>
    @endif

    @if ($seccionId)
        <div class="d-flex justify-content-end mt-2">
            <a href="{{ route('docente.notas.create') }}" class="btn btn-outline-secondary">
                Cambiar secci&oacute;n
            </a>
        </div>
    @endif

    @if (!$seccionId)
        <div class="row mt-4 g-4">
            @forelse ($secciones as $seccion)
                <div class="col-md-6 col-lg-4">
                    <a href="{{ route('docente.notas.create', ['anio_escolar_id' => $anioId, 'periodo_academico_id' => $periodoAcademicoId, 'seccion_id' => $seccion->id]) }}"
                        class="text-decoration-none">
                        <div class="card jp-course-card h-100 border-0 shadow-sm hover-lift">
                            <div class="card-body p-4 text-center">
                                <div class="jp-check-icon-lg mb-3 mx-auto bg-soft-primary text-primary">
                                    <i data-feather="users" style="width: 32px; height: 32px;"></i>
                                </div>
                                <h5 class="fw-bold text-dark mb-1">{{ $seccion->grado->nombre }} - {{ $seccion->nombre }}
                                </h5>
                                <p class="text-muted mb-0 small">Nivel: {{ $seccion->grado->nivel ?? 'General' }}</p>
                            </div>
                        </div>
                    </a>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <i data-feather="info" class="me-2"></i>
                        No tienes secciones asignadas.
                    </div>
                </div>
            @endforelse
        </div>
    @endif

    @if ($seccionId)
        <div class="card jp-search-card mt-3">
            <div class="card-body">
                <form method="GET" action="{{ route('docente.notas.create') }}">
                    <input type="hidden" name="anio_escolar_id" value="{{ $anioId }}">
                    <input type="hidden" name="seccion_id" value="{{ $seccionId }}">
                    <div class="row g-3 align-items-end">
                        <div class="col-lg-4">
                            <label class="form-label">Curso</label>
                            <select name="curso_id" class="form-select" id="curso_id">
                                <option value="">Selecciona un curso</option>
                                @foreach ($cursos as $curso)
                                    <option value="{{ $curso->id }}" @selected((string) $cursoId === (string) $curso->id)>
                                        {{ $curso->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-4">
                            <label class="form-label">Periodo acad&eacute;mico</label>
                            <select name="periodo_academico_id" class="form-select" id="periodo_academico_id">
                                @foreach ($periodosAcademicos as $periodoAcademico)
                                    <option value="{{ $periodoAcademico->id }}" @selected((string) $periodoAcademicoId === (string) $periodoAcademico->id)>
                                        {{ $periodoAcademico->codigo }} - {{ $periodoAcademico->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-3">
                            <label class="form-label">Evaluaci&oacute;n</label>
                            <select name="periodo" class="form-select" id="periodo">
                                <option value="">Selecciona</option>
                                @foreach ($periodos as $item)
                                    <option value="{{ $item }}" @selected($periodo === $item)>{{ $item }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-1 d-grid">
                            <button class="btn jp-btn-search" type="submit">Cargar alumnos</button>
                        </div>
                    </div>
                </form>

                @if ($seccionId && $cursoId && $periodoAcademicoId && $periodo)
                    <div class="mt-3">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <span class="text-muted">Conceptos:</span>
                                <span id="conceptosPreview" class="ms-2"></span>
                            </div>
                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal"
                                data-bs-target="#modalConceptos" @if ($bloqueado) disabled @endif
                                id="btnOpenConceptos">
                                + Agregar concepto
                            </button>
                        </div>
                        <small class="text-muted d-block mt-1">
                            Si hay conceptos configurados, el registro se hace por conceptos y se calcula la nota final
                            autom&aacute;ticamente.
                        </small>
                    </div>
                @endif
            </div>
        </div>
    @endif

    @if ($seccionId && $cursoId && $periodo && $estudiantes->isEmpty())
        <div class="alert alert-warning mt-3">
            No hay estudiantes matriculados en la secci&oacute;n seleccionada.
        </div>
    @endif

    @if ($estudiantes->isNotEmpty())
        @php
            $disabled = $bloqueado ? 'disabled' : '';
        @endphp

        {{-- IMPORTANTE:
            Por defecto apunta a store (modo simple).
            JS lo cambiará a docente.notas.detalles.save cuando existan conceptos.
        --}}
        <form method="POST" action="{{ route('docente.notas.store') }}" class="mt-3" id="formNotas">
            @csrf
            <input type="hidden" name="anio_escolar_id" value="{{ $anioId }}" id="anio_escolar_id">
            <input type="hidden" name="periodo_academico_id" value="{{ $periodoAcademicoId }}"
                id="hidden_periodo_academico_id">
            <input type="hidden" name="seccion_id" value="{{ $seccionId }}" id="hidden_seccion_id">
            <input type="hidden" name="curso_id" value="{{ $cursoId }}" id="hidden_curso_id">
            <input type="hidden" name="periodo" value="{{ $periodo }}" id="hidden_periodo">

            <div class="card jp-soft-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                        <div>
                            <h6 class="mb-1">Listado de estudiantes</h6>
                            <p class="text-muted mb-0" id="tableSubtitle">
                                Registra la nota de evaluaci&oacute;n por estudiante (0 - 20).
                            </p>
                        </div>

                    </div>

                    <div class="table-responsive">
                        <table class="table jp-table mb-0 align-middle jp-concept-table" id="tablaNotas">
                            <thead>
                                <tr id="theadRow">
                                    <th style="width: 60px;">#</th>
                                    <th>Alumno</th>
                                    <th style="width: 160px;">DNI</th>
                                    <th style="width: 160px;" id="thNotaSimple">Nota</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyNotas">
                                @foreach ($estudiantes as $estudiante)
                                    @php
                                        $nota = $notasExistentes->get($estudiante->id);
                                    @endphp
                                    <tr data-estudiante-id="{{ $estudiante->id }}">
                                        <td>{{ $loop->iteration }}</td>
                                        <td class="fw-bold">
                                            {{ $estudiante->apellidos }}, {{ $estudiante->nombres }}
                                        </td>
                                        <td>{{ $estudiante->dni ?? '-' }}</td>
                                        <td class="tdNotaSimple">
                                            <input type="hidden" name="estudiantes[]" value="{{ $estudiante->id }}">
                                            <input name="notas[{{ $estudiante->id }}]" type="number" min="0"
                                                max="20" step="0.01"
                                                class="form-control jp-input-note inputNotaSimple"
                                                value="{{ old('notas.' . $estudiante->id, $nota?->valor) }}"
                                                {{ $disabled }} />
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <button class="btn jp-btn-incidencia" type="submit" {{ $disabled }} id="btnGuardarNotas">
                            <i data-feather="save"></i>
                            Guardar notas
                        </button>
                    </div>
                </div>
            </div>
        </form>
    @endif

    {{-- MODAL: Configurar conceptos --}}
    <div class="modal fade" id="modalConceptos" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Configurar conceptos - {{ $periodo ?? '' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-3">
                        Agrega conceptos (FastTest, Simulacro, etc.) y su porcentaje.
                        El <strong>Examen</strong> se ajusta autom&aacute;ticamente: <strong>Examen = 100% - total de
                            conceptos</strong>.
                    </p>

                    <div id="conceptosContainer"></div>

                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <button type="button" class="btn btn-outline-secondary" id="btnAddConcepto">
                            + Agregar concepto
                        </button>

                        <div class="text-end">
                            <div class="fw-bold">Total: <span id="totalPorcentaje">0</span>%</div>
                            <small class="text-muted">Debe sumar 100%</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnGuardarConceptos"
                        @if ($bloqueado) disabled @endif>
                        Guardar conceptos
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function() {
            const URL_GET_CONCEPTOS = @json(route('docente.notas.conceptos'));
            const URL_SAVE_CONCEPTOS = @json(route('docente.notas.conceptos.save'));
            const URL_SAVE_DETALLES = @json(route('docente.notas.detalles.save'));

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            const anioId = document.getElementById('anio_escolar_id')?.value;
            const periodoAcademicoId = document.getElementById('hidden_periodo_academico_id')?.value;
            const seccionId = document.getElementById('hidden_seccion_id')?.value;
            const cursoId = document.getElementById('hidden_curso_id')?.value;
            const periodo = document.getElementById('hidden_periodo')?.value;

            const conceptosPreview = document.getElementById('conceptosPreview');
            const formNotas = document.getElementById('formNotas');
            const theadRow = document.getElementById('theadRow');
            const tbodyNotas = document.getElementById('tbodyNotas');
            const tableSubtitle = document.getElementById('tableSubtitle');

            const conceptosContainer = document.getElementById('conceptosContainer');
            const btnAddConcepto = document.getElementById('btnAddConcepto');
            const btnGuardarConceptos = document.getElementById('btnGuardarConceptos');
            const totalPorcentaje = document.getElementById('totalPorcentaje');
            const examenPorcentaje = document.getElementById('examenPorcentaje');

            let conceptos = [];
            let modoActual = 'simple'; // 'simple' | 'conceptos'

            // =============================
            // Toasts (pequeños)
            // =============================
            function toastOk(msg) {
                if (window.Swal) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: msg,
                        showConfirmButton: false,
                        timer: 1600,
                        timerProgressBar: true
                    });
                } else {
                    console.log('[OK]', msg);
                }
            }

            function toastErr(msg) {
                if (window.Swal) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'error',
                        title: msg,
                        showConfirmButton: false,
                        timer: 2500,
                        timerProgressBar: true
                    });
                } else {
                    console.error('[ERROR]', msg);
                }
            }

            // ✅ Modal bonito para “Notas guardadas”
            function modalNotasGuardadas(msg) {
                if (window.Swal) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Listo!',
                        text: msg || 'Notas guardadas correctamente.',
                        confirmButtonText: 'OK'
                    });
                } else {
                    console.log('[OK]', msg);
                }
            }

            function buildQuery(params) {
                return new URLSearchParams(params).toString();
            }

            function escapeHtml(str) {
                return (str ?? '').toString()
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;')
                    .replaceAll("'", '&#039;');
            }

            // =============================
            // Conceptos: cargar y preview
            // =============================
            async function cargarConceptos() {
                if (!anioId || !periodoAcademicoId || !seccionId || !cursoId || !periodo) return;

                const qs = buildQuery({
                    anio_escolar_id: anioId,
                    periodo_academico_id: periodoAcademicoId,
                    seccion_id: seccionId,
                    curso_id: cursoId,
                    periodo: periodo,
                });

                const res = await fetch(`${URL_GET_CONCEPTOS}?${qs}`, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                if (!res.ok) return;

                conceptos = await res.json();
                renderConceptosPreview();

                if (conceptos.length > 0) activarModoConceptos();
                else activarModoSimple();
            }

            function renderConceptosPreview() {
                if (!conceptosPreview) return;

                if (!conceptos || conceptos.length === 0) {
                    conceptosPreview.innerHTML = `<span class="text-muted">No configurado</span>`;
                    return;
                }

                conceptosPreview.innerHTML = conceptos.map(c => `
            <span class="jp-concept-pill">
                <strong>${escapeHtml(c.nombre)}</strong> (${Number(c.porcentaje).toFixed(0)}%)
            </span>
        `).join('');
            }

            // =============================
            // Tabla: Simple / Conceptos
            // =============================
            function activarModoSimple() {
                modoActual = 'simple';
                if (tableSubtitle) tableSubtitle.textContent =
                'Registra la nota de evaluación por estudiante (0 - 20).';

                if (formNotas) formNotas.setAttribute('action', @json(route('docente.notas.store')));

                limpiarCabeceraConceptos();
                agregarThSimple();

                document.querySelectorAll('.tdConceptos, .tdFinal').forEach(td => td.remove());

                document.querySelectorAll('.tdNotaSimple').forEach(td => td.style.display = '');
                document.querySelectorAll('.inputNotaSimple').forEach(inp => inp.disabled = false);
            }

            function activarModoConceptos() {
                if (!conceptos || conceptos.length === 0) {
                    activarModoSimple();
                    return;
                }

                modoActual = 'conceptos';
                if (tableSubtitle) tableSubtitle.textContent =
                    'Registra notas por concepto. La nota final se calcula automáticamente.';

                if (formNotas) formNotas.setAttribute('action', URL_SAVE_DETALLES);

                document.querySelectorAll('.tdConceptos, .tdFinal').forEach(td => td.remove());

                limpiarCabeceraConceptos();
                agregarThConceptosYFinal();

                document.querySelectorAll('.tdNotaSimple').forEach(td => td.style.display = 'none');
                document.querySelectorAll('.inputNotaSimple').forEach(inp => inp.disabled = true);

                const rows = tbodyNotas.querySelectorAll('tr[data-estudiante-id]');

                rows.forEach(row => {
                    const estId = row.getAttribute('data-estudiante-id');

                    conceptos.forEach(c => {
                        const td = document.createElement('td');
                        td.className = 'tdConceptos';
                        td.innerHTML = `
                    <input
                        type="number"
                        min="0"
                        max="20"
                        step="0.01"
                        class="form-control jp-input-note inputConcepto"
                        name="notas[${estId}][${c.id}]"
                        data-estudiante="${estId}"
                        data-porcentaje="${c.porcentaje}"
                        value=""
                        ${@json($bloqueado) ? 'disabled' : ''}
                    />
                `;
                        row.appendChild(td);
                    });

                    const tdFinal = document.createElement('td');
                    tdFinal.className = 'tdFinal';
                    tdFinal.innerHTML = `<span class="jp-final-badge" data-final="${estId}">0.00</span>`;
                    row.appendChild(tdFinal);
                });

                document.querySelectorAll('.inputConcepto').forEach(inp => {
                    inp.addEventListener('input', () => recalcularFinal(inp.getAttribute('data-estudiante')));
                });

                setTimeout(() => {
                    rows.forEach(r => recalcularFinal(r.getAttribute('data-estudiante-id')));
                }, 0);
            }

            function limpiarCabeceraConceptos() {
                while (theadRow.children.length > 3) {
                    theadRow.removeChild(theadRow.lastElementChild);
                }
            }

            function agregarThSimple() {
                if (document.getElementById('thNotaSimple')) return;
                const th = document.createElement('th');
                th.id = 'thNotaSimple';
                th.style.width = '160px';
                th.textContent = 'Nota';
                theadRow.appendChild(th);
            }

            function agregarThConceptosYFinal() {
                conceptos.forEach(c => {
                    const th = document.createElement('th');
                    th.className = 'thConcepto';
                    th.style.minWidth = '140px';
                    th.innerHTML =
                        `${escapeHtml(c.nombre)} <span class="text-muted">(${Number(c.porcentaje).toFixed(0)}%)</span>`;
                    theadRow.appendChild(th);
                });

                const thFinal = document.createElement('th');
                thFinal.className = 'thFinal';
                thFinal.style.width = '120px';
                thFinal.textContent = 'Final';
                theadRow.appendChild(thFinal);
            }

            function recalcularFinal(estId) {
                let sum = 0;

                document.querySelectorAll(`.inputConcepto[data-estudiante="${estId}"]`).forEach(inp => {
                    const raw = inp.value;
                    if (raw === '' || raw === null) return;

                    const p = parseFloat(inp.getAttribute('data-porcentaje') || '0') / 100.0;
                    const v = parseFloat(raw);

                    if (!Number.isFinite(v) || !Number.isFinite(p)) return;

                    sum += (v * p);
                });

                const el = document.querySelector(`[data-final="${estId}"]`);
                if (el) el.textContent = sum.toFixed(2);
            }

            // =============================
            // Modal: examen automático
            // =============================
            function renderModalConceptos() {
                conceptosContainer.innerHTML = '';

                const examenRow = document.createElement('div');
                examenRow.className = 'jp-modal-row';
                examenRow.innerHTML = `
            <input type="text" class="form-control" value="Examen" disabled>
            <input type="number" class="form-control" value="${(examenPorcentaje?.textContent || '100')}" disabled>
            <button type="button" class="btn btn-outline-secondary" disabled title="Fijo">-</button>
        `;
                conceptosContainer.appendChild(examenRow);

                const conceptosUsuario = (conceptos || []).filter(c => (c.nombre || '').toLowerCase() !== 'examen');

                if (conceptosUsuario.length === 0) {
                    addConceptoRow('', '');
                } else {
                    conceptosUsuario.forEach(c => addConceptoRow(c.nombre, c.porcentaje));
                }

                recalcularTotalesModal();
            }

            function addConceptoRow(nombre, porcentaje) {
                const row = document.createElement('div');
                row.className = 'jp-modal-row jp-concept-user-row';
                row.innerHTML = `
            <input type="text" class="form-control inputNombre" placeholder="Ej: FastTest" value="${escapeHtml(nombre)}">
            <input type="number" class="form-control inputPorcentaje" placeholder="%" min="0" max="100" step="0.01" value="${porcentaje ?? ''}">
            <button type="button" class="btn btn-outline-danger btnRemove" title="Quitar">&times;</button>
        `;

                row.querySelector('.btnRemove').addEventListener('click', () => {
                    row.remove();
                    recalcularTotalesModal();
                });

                row.querySelector('.inputPorcentaje').addEventListener('input', recalcularTotalesModal);

                row.querySelector('.inputNombre').addEventListener('input', () => {
                    const v = row.querySelector('.inputNombre').value.trim().toLowerCase();
                    if (v === 'examen') {
                        row.querySelector('.inputNombre').value = '';
                        toastErr(
                        'No puedes crear un concepto llamado "Examen". Ese se genera automáticamente.');
                    }
                });

                conceptosContainer.appendChild(row);
            }

            function recalcularTotalesModal() {
                let total = 0;
                conceptosContainer.querySelectorAll('.jp-concept-user-row .inputPorcentaje').forEach(i => {
                    total += parseFloat(i.value || '0');
                });

                total = Math.max(0, total);
                totalPorcentaje.textContent = total.toFixed(2);

                let examen = 100 - total;
                if (examen < 0) examen = 0;
                examenPorcentaje.textContent = examen.toFixed(2);

                if (total > 100) {
                    totalPorcentaje.style.color = 'red';
                    examenPorcentaje.style.color = 'red';
                } else {
                    totalPorcentaje.style.color = '';
                    examenPorcentaje.style.color = '';
                }
            }

            async function guardarConceptos() {
                const rows = conceptosContainer.querySelectorAll('.jp-concept-user-row');
                const payloadConceptos = [];

                rows.forEach(r => {
                    const nombre = r.querySelector('.inputNombre')?.value?.trim();
                    const porcentajeStr = r.querySelector('.inputPorcentaje')?.value;
                    const porcentaje = (porcentajeStr === '' || porcentajeStr === null) ? 0 : parseFloat(
                        porcentajeStr);

                    if (nombre) payloadConceptos.push({
                        nombre,
                        porcentaje
                    });
                });

                const total = payloadConceptos.reduce((acc, c) => acc + (parseFloat(c.porcentaje) || 0), 0);
                if (total > 100.00) {
                    toastErr(`Los conceptos no pueden superar 100%. Actualmente suman ${total.toFixed(2)}%.`);
                    return;
                }

                const body = {
                    anio_escolar_id: anioId,
                    periodo_academico_id: periodoAcademicoId,
                    seccion_id: seccionId,
                    curso_id: cursoId,
                    periodo: periodo,
                    conceptos: payloadConceptos
                };

                const res = await fetch(URL_SAVE_CONCEPTOS, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify(body)
                });

                const json = await res.json().catch(() => ({}));

                if (!res.ok) {
                    toastErr(json?.message || 'No se pudo guardar conceptos.');
                    return;
                }

                toastOk('Conceptos guardados');
                await cargarConceptos();

                const modalEl = document.getElementById('modalConceptos');
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
            }

            // =============================
            // Guardar NOTAS por AJAX (modal bonito)
            // =============================
            if (formNotas) {
                formNotas.addEventListener('submit', async (e) => {
                    if (modoActual !== 'conceptos') return; // modo simple sigue normal

                    e.preventDefault();

                    const payload = {
                        anio_escolar_id: Number(anioId),
                        periodo_academico_id: Number(periodoAcademicoId),
                        seccion_id: Number(seccionId),
                        curso_id: Number(cursoId),
                        periodo: periodo,
                        notas: {}
                    };

                    document.querySelectorAll('.inputConcepto').forEach(inp => {
                        const name = inp.getAttribute('name'); // notas[1][55]
                        const match = name.match(/^notas\[(\d+)\]\[(\d+)\]$/);
                        if (!match) return;

                        const estId = match[1];
                        const conId = match[2];
                        const val = inp.value;

                        if (!payload.notas[estId]) payload.notas[estId] = {};
                        payload.notas[estId][conId] = (val === '' ? null : Number(val));
                    });

                    try {
                        const res = await fetch(URL_SAVE_DETALLES, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                            },
                            body: JSON.stringify(payload)
                        });

                        const json = await res.json().catch(() => ({}));

                        if (!res.ok) {
                            toastErr(json?.message || 'No se pudo guardar.');
                            return;
                        }

                        // ✅ ALERTA BONITA
                        modalNotasGuardadas(json?.message || 'Notas guardadas correctamente');

                    } catch (err) {
                        toastErr('Error de conexión al guardar.');
                    }
                });
            }

            // Eventos modal
            if (btnAddConcepto) btnAddConcepto.addEventListener('click', () => addConceptoRow('', ''));
            if (btnGuardarConceptos) btnGuardarConceptos.addEventListener('click', guardarConceptos);

            const modalConceptosEl = document.getElementById('modalConceptos');
            if (modalConceptosEl) modalConceptosEl.addEventListener('show.bs.modal', renderModalConceptos);

            // Init
            cargarConceptos().catch(() => {});
        })();
    </script>
@endpush
