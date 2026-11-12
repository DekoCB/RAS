@extends('layouts.mantis')

@section('title', 'Paso 5 | Matricula')
@section('page-title', 'Resumen Final')
@section('body-class', auth()->user()->hasRole('SuperAdmin') ? 'role-superadmin' : 'role-secretaria')

@push('styles')
    @if(auth()->user()->hasRole('SuperAdmin'))
        <link rel="stylesheet" href="{{ url('css/superadmin-dashboard.css') }}">
    @endif
    <link rel="stylesheet" href="{{ url('css/secretaria-dashboard.css') }}">
    <link rel="stylesheet" href="{{ url('css/matricula-wizard.css') }}">
    <style>
        .btn-wa {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .55rem 1.25rem;
            background: linear-gradient(135deg, #25d366, #128c7e);
            color: #fff;
            border: 0;
            border-radius: 12px;
            font-weight: 700;
            font-size: .88rem;
            text-decoration: none;
            box-shadow: 0 6px 16px rgba(37,211,102,.30);
            transition: all .18s ease;
            cursor: pointer;
        }
        .btn-wa:hover { background: linear-gradient(135deg,#1ebe5d,#0e7a6e); color:#fff; transform:translateY(-1px); }
        .btn-wa--disabled {
            background: #e2e8f0 !important;
            color: #94a3b8 !important;
            box-shadow: none !important;
            cursor: not-allowed !important;
            transform: none !important;
        }
        .btn-wa svg { width: 18px; height: 18px; flex-shrink: 0; }
        .btn-nota {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .55rem 1.25rem;
            background: linear-gradient(135deg, #162061, #1f2e8e);
            color: #fff;
            border: 0;
            border-radius: 12px;
            font-weight: 700;
            font-size: .88rem;
            text-decoration: none;
            box-shadow: 0 6px 16px rgba(22, 32, 97,.22);
            transition: all .18s ease;
        }
        .btn-nota:hover { background: linear-gradient(135deg, #18246e, #1b287b); color: #fff; transform: translateY(-1px); }
        .btn-nota svg { width: 18px; height: 18px; flex-shrink: 0; }
    </style>
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
                <div class="wizard-step">
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
                <div class="wizard-step is-active">
                    <div class="wizard-step__circle">5</div><span>Resumen Final</span>
                </div>
                <div class="wizard-progress"><span style="width:100%"></span></div>
            </div>
        </div>
    </div>

    @php
        $nivel = session('matricula.nivel', '-');
    @endphp

    <div class="card jp-soft-card matricula-card">
        <div class="card-body">
            <div class="matricula-intro">
                <div class="matricula-title">
                    <span class="matricula-icon"><i data-feather="check-circle"></i></span>
                    <div>
                        <h4 class="mb-1">Resumen Final de Matr&iacute;cula</h4>
                        <p class="mb-0 text-muted">Verifica los datos antes de finalizar el registro.</p>
                    </div>
                </div>
            </div>

            @if (session('error') && !session('matricula_finalize_error'))
                <div class="alert alert-danger mt-3">
                    {{ session('error') }}
                </div>
            @endif

            <div class="info-banner">
                <div class="info-banner__icon"><i data-feather="info"></i></div>
                <div>
                    <div class="info-banner__text">
                        <strong>Nivel Seleccionado:</strong>
                        {{ $nivel }} - {{ $matricula->grado?->nombre ?? '-' }} - {{ $matricula->seccion?->nombre ?? '-' }}
                    </div>
                    <div class="info-banner__meta">
                        Periodo Acad&eacute;mico:
                        {{ $matricula->periodoAcademico?->codigo ?? $matricula->anioEscolar?->nombre ?? date('Y') }}
                        @if ($matricula->periodoAcademico?->nombre)
                            - {{ $matricula->periodoAcademico->nombre }}
                        @endif
                    </div>
                </div>
            </div>

            <div class="section-head">
                <div class="section-title"><i data-feather="user"></i> Datos del Alumno</div>
            </div>
            <div class="section-line"></div>
            <div class="form-card">
                <div class="summary-grid">
                    <div><strong>Nombre:</strong> {{ $matricula->estudiante?->nombres }} {{ $matricula->estudiante?->apellidos }}</div>
                    <div><strong>DNI:</strong> {{ $matricula->estudiante?->dni ?? '-' }}</div>
                    <div><strong>Sexo:</strong> {{ $matricula->estudiante?->sexo ?? '-' }}</div>
                    <div><strong>Fecha Nacimiento:</strong> {{ $matricula->estudiante?->fecha_nacimiento ?? '-' }}</div>
                    <div><strong>Ciudad:</strong> {{ $matricula->estudiante?->ciudad ?? '-' }}</div>
                    <div><strong>Direcci&oacute;n:</strong> {{ $matricula->estudiante?->direccion ?? '-' }}</div>
                </div>
            </div>

            @if (!empty($credenciales))
                <div class="section-head section-head--spaced">
                    <div class="section-title"><i data-feather="lock"></i> Credenciales de Acceso</div>
                </div>
                <div class="section-line"></div>
                <div class="form-card">
                    <div class="summary-grid">
                        <div><strong>Usuario:</strong> {{ $credenciales['email'] }}</div>
                        <div><strong>Contrasena temporal:</strong> {{ $credenciales['password'] }}</div>
                    </div>
                    <div class="form-note">
                        Entrega estas credenciales al alumno y solicita que cambie la contrasena en su primer ingreso.
                    </div>
                </div>
            @endif

            <div class="section-head section-head--spaced">
                <div class="section-title"><i data-feather="user-check"></i> Datos del Apoderado</div>
            </div>
            <div class="section-line"></div>
            <div class="form-card">
                <div class="summary-grid">
                    <div><strong>Nombre:</strong> {{ $apoderado?->nombres }} {{ $apoderado?->apellidos }}</div>
                    <div><strong>DNI:</strong> {{ $apoderado?->dni ?? '-' }}</div>
                    <div><strong>Tel&eacute;fono:</strong> {{ $apoderado?->telefono ?? '-' }}</div>
                    <div><strong>Email:</strong> {{ $apoderado?->email ?? '-' }}</div>
                    <div><strong>Ciudad:</strong> {{ $apoderado?->ciudad ?? '-' }}</div>
                    <div><strong>Direcci&oacute;n:</strong> {{ $apoderado?->direccion ?? '-' }}</div>
                </div>
            </div>

            <div class="section-head section-head--spaced">
                <div class="section-title"><i data-feather="book"></i> Datos Acad&eacute;micos</div>
            </div>
            <div class="section-line"></div>
            <div class="form-card">
                <div class="summary-grid">
                    <div><strong>Situaci&oacute;n:</strong> {{ $matricula->situacion_alumno ?? '-' }}</div>
                    <div><strong>Procedencia:</strong> {{ $matricula->institucion_procedencia ?? '-' }}</div>
                    <div><strong>Repitente:</strong> {{ $matricula->repitente ? 'Si' : 'No' }}</div>
                    <div><strong>Grado:</strong> {{ $matricula->grado?->nombre ?? '-' }}</div>
                    <div><strong>Secci&oacute;n:</strong> {{ $matricula->seccion?->nombre ?? '-' }}</div>
                    <div><strong>Periodo:</strong>
                        {{ $matricula->periodoAcademico?->codigo ?? '-' }}
                        @if ($matricula->periodoAcademico?->nombre)
                            - {{ $matricula->periodoAcademico->nombre }}
                        @endif
                    </div>
                    <div><strong>Estado:</strong> {{ $matricula->estado ?? '-' }}</div>
                </div>
                <div class="mt-3">
                    <strong>Cursos asignados:</strong>
                    <div class="d-flex flex-wrap gap-2 mt-2">
                        @forelse ($cursosAsignados as $curso)
                            <span class="badge bg-primary">{{ $curso->nombre }}</span>
                        @empty
                            <span class="text-muted">Sin cursos asignados.</span>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="section-head section-head--spaced">
                <div class="section-title"><i data-feather="credit-card"></i> Pago de Matr&iacute;cula</div>
            </div>
            <div class="section-line"></div>
            <div class="form-card">
                @php
                    $deudasPendientes = $matricula->deudas->whereIn('estado', ['pendiente', 'parcial']);
                    $totalDeudaPendiente = $deudasPendientes->sum('saldo');
                    $totalDescuentos = $matricula->descuentos->sum('monto');
                @endphp

                <div class="summary-grid">
                    <div><strong>Monto pagado:</strong> S/ {{ number_format($pago?->monto ?? 0, 2) }}</div>
                    <div><strong>M&eacute;todo:</strong> {{ $pago?->metodo ?? '-' }}</div>
                    <div><strong>Fecha Pago:</strong> {{ $pago?->fecha_pago ?? '-' }}</div>
                    <div><strong>Estado:</strong> {{ $pago?->estado ?? '-' }}</div>
                    <div><strong>Comentario:</strong> {{ $pago?->comentario ?? '-' }}</div>
                </div>

                <div class="mt-3">
                    {{-- Resumen colapsable de deudas --}}
                    <div class="d-flex align-items-center justify-content-between mb-2" style="cursor:pointer;" data-bs-toggle="collapse" data-bs-target="#detalleDeudas" aria-expanded="false">
                        <div>
                            @if ($totalDescuentos > 0)
                                <small class="text-muted me-3">Descuentos: - S/ {{ number_format((float) $totalDescuentos, 2) }}</small>
                            @endif
                            <strong>Deuda total pendiente: <span class="text-danger">S/ {{ number_format((float) $totalDeudaPendiente, 2) }}</span></strong>
                            <small class="text-muted ms-2">({{ $deudasPendientes->count() }} conceptos)</small>
                        </div>
                        <span class="text-muted" style="font-size:0.85rem;" id="deudas-toggle-label">▼ Ver detalle</span>
                    </div>

                    <div class="collapse" id="detalleDeudas">
                        <div class="table-responsive">
                            <table class="table jp-table mb-0" style="font-size:0.875rem;">
                                <thead>
                                    <tr>
                                        <th>Concepto</th>
                                        <th>Monto</th>
                                        <th>Estado</th>
                                        <th>Saldo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($deudasPendientes->sortBy('id') as $deuda)
                                        <tr>
                                            <td>{{ $deuda->periodo }}</td>
                                            <td>S/ {{ number_format((float) $deuda->monto, 2) }}</td>
                                            <td>
                                                <span class="badge bg-{{ $deuda->estado === 'pendiente' ? 'warning text-dark' : 'info' }}">
                                                    {{ ucfirst($deuda->estado) }}
                                                </span>
                                            </td>
                                            <td>S/ {{ number_format((float) $deuda->saldo, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">No hay deudas pendientes.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if ($matricula->descuentos->isNotEmpty())
                            <div class="mt-2 mb-1">
                                <small class="fw-bold">Descuentos aplicados:</small>
                                @foreach ($matricula->descuentos as $descuento)
                                    <small class="text-muted ms-2">{{ $descuento->descripcion ?? $descuento->tipo }}: - S/ {{ number_format((float) $descuento->monto, 2) }}</small>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        var el = document.getElementById('detalleDeudas');
                        var lbl = document.getElementById('deudas-toggle-label');
                        if (el && lbl) {
                            el.addEventListener('show.bs.collapse', function () { lbl.textContent = '▲ Ocultar detalle'; });
                            el.addEventListener('hide.bs.collapse', function () { lbl.textContent = '▼ Ver detalle'; });
                        }
                    });
                </script>
            </div>

            @php
                // ── Construir link de WhatsApp ──────────────────────────────
                $telRaw    = $apoderado?->telefono ?? '';
                $telLimpio = preg_replace('/\D/', '', $telRaw);

                // Añadir prefijo Perú (+51) si el número tiene 9 dígitos (formato local)
                if (strlen($telLimpio) === 9) {
                    $telWa = '51' . $telLimpio;
                } else {
                    $telWa = $telLimpio; // ya incluye prefijo o vacío
                }

                $nombreAlumno = trim(($matricula->estudiante?->nombres ?? '') . ' ' . ($matricula->estudiante?->apellidos ?? ''));
                $gradoNombre  = $matricula->grado?->nombre ?? '-';
                $seccionNombre = $matricula->seccion?->nombre ?? '-';
                $periodoLabel = $matricula->periodoAcademico?->codigo ?? $matricula->anioEscolar?->nombre ?? date('Y');
                if ($matricula->periodoAcademico?->nombre) {
                    $periodoLabel .= ' - ' . $matricula->periodoAcademico->nombre;
                }

                $fechaFormateada = $matricula->fecha_matricula
                    ? \Carbon\Carbon::parse($matricula->fecha_matricula)->format('d/m/Y')
                    : now('America/Lima')->format('d/m/Y');

                $msg  = "*MATRICULA REGISTRADA*\n";
                $msg .= "*R.A.S*\n";
                $msg .= "_Fecha: {$fechaFormateada}_\n\n";

                $msg .= "*Alumno:* {$nombreAlumno}\n";
                $msg .= "*DNI:* " . ($matricula->estudiante?->dni ?? '-') . "\n";
                $msg .= "*Grado:* {$gradoNombre}, Seccion {$seccionNombre}\n";
                $msg .= "*Periodo:* {$periodoLabel}\n";

                if (!empty($credenciales['email']) && !empty($credenciales['password'])) {
                    $msg .= "\n*Credenciales de acceso:*\n";
                    $msg .= "  Usuario: *" . $credenciales['email'] . "*\n";
                    $msg .= "  Contrasena: *" . $credenciales['password'] . "*\n";
                    $msg .= "_Cambie su contrasena en su primer ingreso._\n";
                }

                $msg .= "\nPara consultas comuniquese con secretaria.\n";
                $msg .= "*R.A.S*";

                $waLink      = 'https://wa.me/' . $telWa . '?text=' . rawurlencode($msg);
                $tieneTelefono = strlen($telWa) >= 9;
            @endphp

            <div class="wizard-actions">
                <a href="{{ route('secretaria.matriculas.paso4') }}" class="btn btn-secondary">Volver</a>

                {{-- Botón Nota de Venta --}}
                <a href="{{ route('secretaria.matriculas.nota-venta', $matricula) }}"
                   target="_blank" rel="noopener" class="btn-nota">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <polyline points="10 9 9 9 8 9"></polyline>
                    </svg>
                    Ver Nota de Venta
                </a>

                {{-- Botón WhatsApp --}}
                @if ($tieneTelefono)
                    <a href="{{ $waLink }}" target="_blank" rel="noopener" class="btn-wa">
                        <svg viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.570-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.890-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                        Enviar por WhatsApp
                    </a>
                @else
                    <button type="button" class="btn-wa btn-wa--disabled" disabled
                        title="El apoderado no tiene teléfono registrado">
                        <svg viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.570-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.890-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                        Sin teléfono registrado
                    </button>
                @endif

                <form method="POST" action="{{ route('secretaria.matriculas.paso5.finalizar') }}" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-primary">Finalizar</button>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ url('assets/js/plugins/sweetalert2.all.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const finalizeErrorMessage = @json(session('matricula_finalize_error'));
            if (!finalizeErrorMessage) return;

            if (window.Swal) {
                Swal.fire({
                    icon: 'error',
                    title: 'No se pudo finalizar',
                    text: finalizeErrorMessage,
                    confirmButtonText: 'Aceptar',
                });
            } else {
                alert(finalizeErrorMessage);
            }
        });
    </script>
@endpush
