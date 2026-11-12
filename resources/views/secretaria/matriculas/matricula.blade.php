@extends('layouts.mantis')

@section('title', 'Nueva Matricula | Secretaria')
@section('page-title', 'Nueva Matricula')
@section('body-class', 'role-secretaria')

@push('styles')
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
    <div class="card jp-soft-card wizard-top">
        <div class="card-body">
            <div class="wizard-steps">
                <div class="wizard-step is-active" data-step="1">
                    <div class="wizard-step__circle">1</div>
                    <span>Nivel y Grado</span>
                </div>
                <div class="wizard-step" data-step="2">
                    <div class="wizard-step__circle">2</div>
                    <span>Datos del Estudiante</span>
                </div>
                <div class="wizard-step" data-step="3">
                    <div class="wizard-step__circle">3</div>
                    <span>Pago de Matricula</span>
                </div>
                <div class="wizard-step" data-step="4">
                    <div class="wizard-step__circle">4</div>
                    <span>Resumen Final</span>
                </div>
                <div class="wizard-progress">
                    <span></span>
                </div>
            </div>
        </div>
    </div>

    <div class="card jp-soft-card matricula-intro">
        <div class="card-body">
            <div class="matricula-title">
                <span class="matricula-icon"><i data-feather="book-open"></i></span>
                <div>
                    <h4>Nueva Matricula</h4>
                    <p>Seleccione el nivel academico y grado para iniciar el proceso de matricula.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="wizard-body">
        <input type="hidden" id="nivel">
        <input type="hidden" id="grado">
        <input type="hidden" id="Secci?n">

        <!-- Paso 1 -->
        <div class="wizard-panel is-active" data-panel="1">
            <div class="card jp-soft-card matricula-card">
                <div class="card-body">
                    <div class="section-head">
                        <div class="section-title"><i data-feather="layers"></i> Nivel Academico</div>
                    </div>
                    <div class="section-line"></div>

                    <div class="select-grid">
                        <button type="button" class="select-card is-selected" data-level="inicial" data-value="Inicial">
                            <i data-feather="user"></i>
                            <span>INICIAL</span>
                        </button>
                        <button type="button" class="select-card" data-level="primaria" data-value="Primaria">
                            <i data-feather="book-open"></i>
                            <span>PRIMARIA</span>
                        </button>
                        <button type="button" class="select-card" data-level="secundaria" data-value="Secundaria">
                            <i data-feather="award"></i>
                            <span>SECUNDARIA</span>
                        </button>
                    </div>

                    <div class="section-head section-head--spaced">
                        <div class="section-title"><i data-feather="grid"></i> Grado y Secci?n</div>
                        <button class="btn btn-sm btn-outline-primary" type="button">Gestionar Secci?nes</button>
                    </div>
                    <div class="section-line"></div>

                    <div class="grade-group" data-group="inicial">
                        <button class="grade-card" data-grado="Inicial 2 a?os" data-Secci?n="A"><strong>2 a?os</strong><span>Secci?n A</span></button>
                        <button class="grade-card" data-grado="Inicial 2 a?os" data-Secci?n="B"><strong>2 a?os</strong><span>Secci?n B</span></button>
                        <button class="grade-card" data-grado="Inicial 3 a?os" data-Secci?n="A"><strong>3 a?os</strong><span>Secci?n A</span></button>
                        <button class="grade-card" data-grado="Inicial 3 a?os" data-Secci?n="B"><strong>3 a?os</strong><span>Secci?n B</span></button>
                        <button class="grade-card" data-grado="Inicial 4 a?os" data-Secci?n="A"><strong>4 a?os</strong><span>Secci?n A</span></button>
                        <button class="grade-card" data-grado="Inicial 4 a?os" data-Secci?n="B"><strong>4 a?os</strong><span>Secci?n B</span></button>
                        <button class="grade-card" data-grado="Inicial 5 a?os" data-Secci?n="A"><strong>5 a?os</strong><span>Secci?n A</span></button>
                        <button class="grade-card" data-grado="Inicial 5 a?os" data-Secci?n="B"><strong>5 a?os</strong><span>Secci?n B</span></button>
                    </div>

                    <div class="grade-group is-hidden" data-group="primaria">
                        <button class="grade-card" data-grado="Primaria 1ro" data-Secci?n="A"><strong>1ro</strong><span>Secci?n A</span></button>
                        <button class="grade-card" data-grado="Primaria 1ro" data-Secci?n="B"><strong>1ro</strong><span>Secci?n B</span></button>
                        <button class="grade-card" data-grado="Primaria 2do" data-Secci?n="A"><strong>2do</strong><span>Secci?n A</span></button>
                        <button class="grade-card" data-grado="Primaria 2do" data-Secci?n="B"><strong>2do</strong><span>Secci?n B</span></button>
                        <button class="grade-card" data-grado="Primaria 3ro" data-Secci?n="A"><strong>3ro</strong><span>Secci?n A</span></button>
                        <button class="grade-card" data-grado="Primaria 3ro" data-Secci?n="B"><strong>3ro</strong><span>Secci?n B</span></button>
                        <button class="grade-card" data-grado="Primaria 4to" data-Secci?n="A"><strong>4to</strong><span>Secci?n A</span></button>
                        <button class="grade-card" data-grado="Primaria 4to" data-Secci?n="B"><strong>4to</strong><span>Secci?n B</span></button>
                        <button class="grade-card" data-grado="Primaria 5to" data-Secci?n="A"><strong>5to</strong><span>Secci?n A</span></button>
                        <button class="grade-card" data-grado="Primaria 5to" data-Secci?n="B"><strong>5to</strong><span>Secci?n B</span></button>
                        <button class="grade-card" data-grado="Primaria 6to" data-Secci?n="A"><strong>6to</strong><span>Secci?n A</span></button>
                        <button class="grade-card" data-grado="Primaria 6to" data-Secci?n="B"><strong>6to</strong><span>Secci?n B</span></button>
                    </div>

                    <div class="grade-group is-hidden" data-group="secundaria">
                        <button class="grade-card" data-grado="Secundaria 1ro" data-Secci?n="?nica"><strong>1ro</strong><span>Secci?n ?nica</span></button>
                        <button class="grade-card" data-grado="Secundaria 2do" data-Secci?n="?nica"><strong>2do</strong><span>Secci?n ?nica</span></button>
                        <button class="grade-card" data-grado="Secundaria 3ro" data-Secci?n="?nica"><strong>3ro</strong><span>Secci?n ?nica</span></button>
                        <button class="grade-card" data-grado="Secundaria 4to" data-Secci?n="?nica"><strong>4to</strong><span>Secci?n ?nica</span></button>
                        <button class="grade-card" data-grado="Secundaria 5to" data-Secci?n="?nica"><strong>5to</strong><span>Secci?n ?nica</span></button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Paso 2 -->
        <div class="wizard-panel" data-panel="2">
            <div class="card jp-soft-card matricula-card">
                <div class="card-body form-grid">
                    <div>
                        <label class="form-label">Nombres</label>
                        <input class="form-control" type="text" placeholder="Nombres">
                    </div>
                    <div>
                        <label class="form-label">Apellidos</label>
                        <input class="form-control" type="text" placeholder="Apellidos">
                    </div>
                    <div>
                        <label class="form-label">DNI</label>
                        <input class="form-control" type="text" placeholder="Documento">
                    </div>
                    <div>
                        <label class="form-label">Fecha de nacimiento</label>
                        <input class="form-control" type="date">
                    </div>
                    <div>
                        <label class="form-label">Telefono apoderado</label>
                        <input class="form-control" type="text" placeholder="Celular">
                    </div>
                    <div>
                        <label class="form-label">Direccion</label>
                        <input class="form-control" type="text" placeholder="Direccion">
                    </div>
                </div>
            </div>
        </div>

        <!-- Paso 3 -->
        <div class="wizard-panel" data-panel="3">
            <div class="card jp-soft-card matricula-card">
                <div class="card-body">
                    <div class="select-grid">
                        <button type="button" class="select-card is-selected" data-pago="Efectivo">EFECTIVO</button>
                        <button type="button" class="select-card" data-pago="Transferencia">TRANSFERENCIA</button>
                        <button type="button" class="select-card" data-pago="Tarjeta">TARJETA</button>
                    </div>
                    <div class="form-grid mt-3">
                        <div>
                            <label class="form-label">Monto</label>
                            <input class="form-control" type="number" placeholder="0.00">
                        </div>
                        <div>
                            <label class="form-label">Fecha de pago</label>
                            <input class="form-control" type="date">
                        </div>
                        <div>
                            <label class="form-label">Referencia</label>
                            <input class="form-control" type="text" placeholder="Operacion / Voucher">
                        </div>
                        <div>
                            <label class="form-label">Observacion</label>
                            <input class="form-control" type="text" placeholder="Opcional">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Paso 4 -->
        <div class="wizard-panel" data-panel="4">
            <div class="card jp-soft-card matricula-card">
                <div class="card-body summary-grid">
                    <div>
                        <h6>Nivel</h6>
                        <p id="summary-nivel">-</p>
                    </div>
                    <div>
                        <h6>Grado</h6>
                        <p id="summary-grado">-</p>
                    </div>
                    <div>
                        <h6>Secci?n</h6>
                        <p id="summary-Secci?n">-</p>
                    </div>
                    <div class="summary-note">
                        Revisa los datos antes de confirmar la matricula.
                    </div>
                </div>
            </div>
        </div>

        <div class="wizard-actions">
            <button type="button" class="btn btn-outline-secondary" id="btn-prev">Anterior</button>
            <button type="button" class="btn btn-primary" id="btn-next">Siguiente</button>
        </div>
    </div>
@endsection

@push('scripts')
<script>
  const panels = Array.from(document.querySelectorAll('.wizard-panel'));
  const steps = Array.from(document.querySelectorAll('.wizard-step'));
  const progress = document.querySelector('.wizard-progress span');
  const btnPrev = document.getElementById('btn-prev');
  const btnNext = document.getElementById('btn-next');

  let current = 0;

  function render() {
    panels.forEach((p, i) => p.classList.toggle('is-active', i === current));
    steps.forEach((s, i) => s.classList.toggle('is-active', i <= current));
    const pct = (current / (steps.length - 1)) * 100;
    progress.style.width = pct + '%';
    btnPrev.disabled = current === 0;
    btnNext.textContent = current === steps.length - 1 ? 'Finalizar' : 'Siguiente';
    updateSummary();
  }

  btnNext.addEventListener('click', () => {
    if (current < panels.length - 1) {
      current++;
      render();
    }
  });

  btnPrev.addEventListener('click', () => {
    if (current > 0) {
      current--;
      render();
    }
  });

  const levelCards = document.querySelectorAll('[data-level]');
  const gradeGroups = document.querySelectorAll('.grade-group');

  levelCards.forEach(card => {
    card.addEventListener('click', () => {
      levelCards.forEach(c => c.classList.remove('is-selected'));
      card.classList.add('is-selected');
      const level = card.dataset.level;
      document.getElementById('nivel').value = card.dataset.value;
      gradeGroups.forEach(g => g.classList.toggle('is-hidden', g.dataset.group !== level));
    });
  });

  const gradeCards = document.querySelectorAll('.grade-card');
  gradeCards.forEach(card => {
    card.addEventListener('click', () => {
      gradeCards.forEach(c => c.classList.remove('is-selected'));
      card.classList.add('is-selected');
      document.getElementById('grado').value = card.dataset.grado;
      document.getElementById('Secci?n').value = card.dataset.Secci?n;
    });
  });

  function updateSummary() {
    document.getElementById('summary-nivel').textContent = document.getElementById('nivel').value || '-';
    document.getElementById('summary-grado').textContent = document.getElementById('grado').value || '-';
    document.getElementById('summary-Secci?n').textContent = document.getElementById('Secci?n').value || '-';
  }

  render();
</script>
@endpush

