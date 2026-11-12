@extends('layouts.mantis')

@section('title', 'Matricula | Paso 1')
@section('page-title', 'Matricula')
@section('body-class', auth()->user()->hasRole('SuperAdmin') ? 'role-superadmin' : 'role-secretaria')

@push('styles')
    @if(auth()->user()->hasRole('SuperAdmin'))
        <link rel="stylesheet" href="{{ url('css/superadmin-dashboard.css') }}">
    @endif
    <link rel="stylesheet" href="{{ url('css/secretaria-dashboard.css') }}">
    <link rel="stylesheet" href="{{ url('css/matricula-wizard.css') }}">
@endpush

@section('sidebar')
    @if (auth()->user()?->hasRole('SuperAdmin'))
        @include('admin.partials.sidebar')
    @else
        @include('secretaria.partials.sidebar')
    @endif
@endsection

@section('content')
    @php
        $nivelSeleccionado = old('nivel', session('matricula.nivel', 'Inicial'));
        $gradoSeleccionado = old('grado', session('matricula.grado', ''));
        $seccionSeleccionada = old('seccion', session('matricula.seccion', ''));
        $periodoSeleccionado = old('periodo_academico_id', session('matricula.periodo_academico_id', $periodoActivo?->id));
    @endphp

    <div class="card jp-soft-card wizard-top">
        <div class="card-body">
            <div class="wizard-steps">
                <div class="wizard-step is-active">
                    <div class="wizard-step__circle">1</div><span>Nivel y Grado</span>
                </div>
                <div class="wizard-step">
                    <div class="wizard-step__circle">2</div><span>Datos del Estudiante</span>
                </div>
                <div class="wizard-step">
                    <div class="wizard-step__circle">3</div><span>Pago de Matricula</span>
                </div>
                <div class="wizard-step">
                    <div class="wizard-step__circle">4</div><span>Asignaci&oacute;n de Cursos</span>
                </div>
                <div class="wizard-step">
                    <div class="wizard-step__circle">5</div><span>Resumen Final</span>
                </div>
                <div class="wizard-progress"><span style="width:0%"></span></div>
            </div>
        </div>
    </div>

    @if (session('error'))
        <div class="alert alert-danger mt-3">
            {{ session('error') }}
        </div>
    @endif

    {{-- Viene desde Admision: los datos del expediente ya estan cargados --}}
    @if (session('status') && session('matricula.postulante_id'))
        <div class="alert alert-info mt-3 d-flex align-items-center gap-2">
            <i data-feather="user-check" style="width:18px;height:18px;flex-shrink:0"></i>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('secretaria.matriculas.paso1.guardar') }}">
        @csrf
        <input type="hidden" name="nivel" id="nivel" value="{{ $nivelSeleccionado }}">
        <input type="hidden" name="grado" id="grado" value="{{ $gradoSeleccionado }}">
        <input type="hidden" name="seccion" id="seccion" value="{{ $seccionSeleccionada }}">
        <input type="hidden" name="periodo_academico_id" id="periodo_academico_id"
            value="{{ $periodoSeleccionado }}">

        <div class="card jp-soft-card matricula-card">
            <div class="card-body">
                @include('secretaria.matriculas._buscar_admitido', [
                    'admitidos'     => $admitidos ?? collect(),
                    'postulanteSel' => old('postulante_id', session('matricula.postulante_id', 0)),
                    'ayuda'         => 'Busca al admitido por nombre, DNI o c&oacute;digo. Al elegirlo se marcan el nivel y el grado al que postul&oacute;; solo queda elegir la secci&oacute;n. En el paso 2 sus datos y los del apoderado vendr&aacute;n llenos.',
                ])

                <div class="section-head">
                    <div class="section-title"><i data-feather="calendar"></i> Periodo Acad&eacute;mico</div>
                </div>
                <div class="section-line"></div>

                <div class="select-grid">
                    @forelse ($periodos as $periodo)
                        @php
                            $rango = $periodo->fecha_inicio && $periodo->fecha_fin
                                ? \Illuminate\Support\Carbon::parse($periodo->fecha_inicio)->format('d/m/Y')
                                    . ' - ' . \Illuminate\Support\Carbon::parse($periodo->fecha_fin)->format('d/m/Y')
                                : null;
                        @endphp
                        <button type="button"
                            class="select-card period-card {{ ((string) $periodoSeleccionado === (string) $periodo->id) ? 'is-selected' : '' }}"
                            data-periodo-id="{{ $periodo->id }}">
                            <span>{{ $periodo->codigo }}</span>
                            <small>{{ $periodo->nombre }}</small>
                            @if ($rango)
                                <small>{{ $rango }}</small>
                            @endif
                        </button>
                    @empty
                        <div class="text-muted">No hay periodos configurados para este a&ntilde;o.</div>
                    @endforelse
                </div>
                <div class="text-muted small mt-2">
                    El periodo vacacional es opcional y no promueve de grado.
                </div>
                @error('periodo_academico_id')
                    <div class="text-danger mt-2">{{ $message }}</div>
                @enderror

                <div class="section-head">
        <div class="section-title"><i data-feather="layers"></i> Nivel Acad&eacute;mico</div>
                </div>
                <div class="section-line"></div>

                <div class="select-grid">
                    <button type="button" class="select-card {{ $nivelSeleccionado === 'Inicial' ? 'is-selected' : '' }}" data-level="inicial" data-value="Inicial">
                        <i data-feather="user"></i>
                        <span>INICIAL</span>
                    </button>
                    <button type="button" class="select-card {{ $nivelSeleccionado === 'Primaria' ? 'is-selected' : '' }}" data-level="primaria" data-value="Primaria">
                        <i data-feather="book-open"></i>
                        <span>PRIMARIA</span>
                    </button>
                    <button type="button" class="select-card {{ $nivelSeleccionado === 'Secundaria' ? 'is-selected' : '' }}" data-level="secundaria" data-value="Secundaria">
                        <i data-feather="award"></i>
                        <span>SECUNDARIA</span>
                    </button>
                </div>

                <div class="section-head section-head--spaced">
        <div class="section-title"><i data-feather="grid"></i> Grado y Secci&oacute;n</div>
                </div>
                <div class="section-line"></div>

                @forelse ($seccionesPorNivel as $nivel => $secciones)
                    @php $groupKey = strtolower($nivel); @endphp
                    <div class="grade-group {{ $nivelSeleccionado === $nivel ? '' : 'is-hidden' }}" data-group="{{ $groupKey }}">
                        @foreach ($secciones as $seccion)
                            @php $secNombre = strtoupper($seccion->nombre); @endphp
                            <button type="button" class="grade-card"
                                data-grado="{{ $seccion->grado?->nombre }}"
                                data-seccion="{{ $secNombre }}">
                                <strong>{{ $seccion->grado?->nombre }}</strong>
                                <span>Sección {{ $secNombre }}</span>
                            </button>
                        @endforeach
                    </div>
                @empty
                    <p class="text-muted mt-2">No hay secciones registradas. Ve a <strong>Gestionar Secciones</strong> para crear una.</p>
                @endforelse
            </div>
        </div>

        <div class="wizard-actions">
            <button type="submit" class="btn btn-primary">Continuar</button>
        </div>
    </form>

    @push('scripts')
        <script>
            const levelCards = document.querySelectorAll('[data-level]');
            const gradeGroups = document.querySelectorAll('.grade-group');
            const nivelInput = document.getElementById('nivel');
            const gradoInput = document.getElementById('grado');
            const seccionInput = document.getElementById('seccion');
            const periodoInput = document.getElementById('periodo_academico_id');
            const periodoCards = document.querySelectorAll('.period-card');
            const gradeCards = document.querySelectorAll('.grade-card');

            const normalize = (value) => (value ?? '').toString().trim().toLowerCase();

            const selectLevelCard = (card) => {
                levelCards.forEach(c => c.classList.remove('is-selected'));
                card.classList.add('is-selected');
                const level = card.dataset.level;
                nivelInput.value = card.dataset.value;
                gradeGroups.forEach(g => g.classList.toggle('is-hidden', g.dataset.group !== level));
            };

            periodoCards.forEach(card => {
                card.addEventListener('click', () => {
                    periodoCards.forEach(c => c.classList.remove('is-selected'));
                    card.classList.add('is-selected');
                    periodoInput.value = card.dataset.periodoId;
                });
            });

            if (periodoCards.length) {
                const selectedPeriodoCard = Array.from(periodoCards)
                    .find(card => card.dataset.periodoId === periodoInput.value);

                if (selectedPeriodoCard) {
                    periodoCards.forEach(c => c.classList.remove('is-selected'));
                    selectedPeriodoCard.classList.add('is-selected');
                } else {
                    periodoCards[0].classList.add('is-selected');
                    periodoInput.value = periodoCards[0].dataset.periodoId;
                }
            }

            levelCards.forEach(card => {
                card.addEventListener('click', () => {
                    if (card.classList.contains('is-locked')) return;
                    selectLevelCard(card);
                });
            });

            gradeCards.forEach(card => {
                card.addEventListener('click', () => {
                    if (card.classList.contains('is-locked')) return;
                    gradeCards.forEach(c => c.classList.remove('is-selected'));
                    card.classList.add('is-selected');
                    gradoInput.value = card.dataset.grado;
                    seccionInput.value = card.dataset.seccion;
                });
            });

            // "Cargar desde Admision": el nivel y el grado vienen del expediente y quedan
            // bloqueados; la secretaria solo elige la seccion dentro de ese grado.
            let admitidoActivo = null;
            const bloquearPorAdmitido = (a) => {
                admitidoActivo = a;
                levelCards.forEach(c => c.classList.toggle('is-locked', !!a));
                gradeCards.forEach(c => {
                    const propio = !!a && c.dataset.grado === a.grado;
                    c.classList.toggle('is-locked', !!a && !propio);
                });
            };
            const aplicarAdmitido = (a) => {
                const aviso = document.getElementById('admitidoAviso');
                if (aviso) aviso.style.display = 'none';
                bloquearPorAdmitido(a);
                if (!a) return;

                const nivelCard = Array.from(levelCards).find(c => normalize(c.dataset.value) === normalize(a.nivel));
                if (nivelCard) selectLevelCard(nivelCard);

                const delGrado = Array.from(gradeCards).filter(c => c.dataset.grado === a.grado);
                gradoInput.value = a.grado || '';
                const yaElegida = delGrado.find(c => c.classList.contains('is-selected'));
                if (!yaElegida) {
                    gradeCards.forEach(c => c.classList.remove('is-selected'));
                    seccionInput.value = '';
                    if (delGrado.length === 1) {
                        delGrado[0].classList.add('is-selected');
                        seccionInput.value = delGrado[0].dataset.seccion;
                    }
                }
                if (delGrado.length === 0 && aviso) {
                    aviso.textContent = 'No hay secciones registradas para ' + a.grado + ' en este año. Crea una en Gestionar Secciones.';
                    aviso.style.display = '';
                } else if (delGrado.length > 1 && aviso && !seccionInput.value) {
                    aviso.textContent = a.grado + ' tiene ' + delGrado.length + ' secciones: elige en cual se matricula.';
                    aviso.style.display = '';
                }
            };
            document.addEventListener('admitido:seleccionado', (e) => aplicarAdmitido(e.detail));

            // Restore de nivel desde sesión/old.
            const initialLevelCard = Array.from(levelCards)
                .find(card => normalize(card.dataset.value) === normalize(nivelInput.value));
            if (initialLevelCard) {
                selectLevelCard(initialLevelCard);
            } else if (levelCards.length) {
                selectLevelCard(levelCards[0]);
            }

            // Restore de grado + sección desde sesión/old.
            const selectedGradeCard = Array.from(gradeCards).find((card) =>
                card.dataset.grado === gradoInput.value
                && normalize(card.dataset.seccion) === normalize(seccionInput.value)
            );

            if (selectedGradeCard) {
                const group = selectedGradeCard.closest('.grade-group')?.dataset.group;
                if (group) {
                    const ownerLevelCard = Array.from(levelCards).find(card => card.dataset.level === group);
                    if (ownerLevelCard) selectLevelCard(ownerLevelCard);
                }

                gradeCards.forEach(c => c.classList.remove('is-selected'));
                selectedGradeCard.classList.add('is-selected');
            }

            // Si la pagina abre con un admitido ya elegido, aplicar el bloqueo desde el inicio.
            (function () {
                var pre = @json(($admitidos ?? collect())->first(fn ($a) => $a['id'] === (int) old('postulante_id', session('matricula.postulante_id', 0))));
                if (pre) aplicarAdmitido(pre);
            })();
        </script>
    @endpush
@endsection


