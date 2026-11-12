{{--
    Pre-matriculas: lo que las familias envian desde el formulario de
    Admision del sitio web. Se usa en la pantalla de Matriculas y en la
    pantalla propia de Pre-matriculas (Alumnado > Pre-matriculas).
    Variables: $preMatriculas (paginador de Postulante), $prePendientes.
--}}
    {{-- ── Pre-matrículas (formulario web de Admisión) ──────────── --}}
    @php
        $preMatriculas = $preMatriculas ?? collect();
        $prePendientes = $prePendientes ?? 0;
        $puedeVerAdmision = auth()->user()?->can('admision.ver');
        $estadosPre = \App\Models\Postulante::ESTADOS;
    @endphp
    <div class="card jp-attendance-card mt-3">
        <div class="card-header bg-white border-0 d-flex align-items-center justify-content-between flex-wrap gap-2 pt-3">
            <div class="d-flex align-items-center gap-2">
                <i data-feather="globe" style="width:18px;height:18px;color:#1c297e;"></i>
                <h6 class="mb-0 fw-bold" style="color:#162061;">Pre-matrículas desde la web</h6>
                <span class="badge" style="background:#fff4de;color:#a86a00;font-size:.74rem;font-weight:700;border-radius:999px;padding:.3rem .7rem;">
                    {{ $prePendientes }} por atender
                </span>
            </div>
            <span class="small text-muted">
                Inscripciones que las familias envían desde el formulario de Admisión del sitio web.
            </span>
        </div>
        <div class="table-responsive">
            <table class="table jp-attendance-table mb-0">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Postulante</th>
                        <th>DNI</th>
                        <th>Nivel/Grado</th>
                        <th>Sede</th>
                        <th>Apoderado</th>
                        <th>Enviado</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($preMatriculas as $p)
                        @php
                            $preDatos = [
                                'codigo'      => $p->codigo,
                                'estado'      => $estadosPre[$p->estado] ?? $p->estado,
                                'anio'        => $p->anioEscolar?->nombre ?? '—',
                                'alumno'      => trim($p->nombres . ' ' . $p->apellidos),
                                'dni'         => $p->dni,
                                'nacimiento'  => $p->fecha_nacimiento?->format('d/m/Y') ?? '—',
                                'sexo'        => $p->sexo === 'F' ? 'Femenino' : ($p->sexo === 'M' ? 'Masculino' : '—'),
                                'nivelGrado'  => trim(($p->nivel ?? '') . ' · ' . ($p->grado?->nombre ?? '—'), ' ·'),
                                'sede'        => $p->sede?->nombre ?? '—',
                                'direccion'   => $p->direccion ?: '—',
                                'procedencia' => $p->colegio_procedencia ?: '—',
                                'apoderado'   => trim($p->apoderado_nombres . ' ' . $p->apoderado_apellidos) ?: '—',
                                'parentesco'  => $p->apoderado_parentesco ?: '—',
                                'apoDni'      => $p->apoderado_dni ?: '—',
                                'telefono'    => $p->apoderado_telefono ?: '—',
                                'email'       => $p->apoderado_email ?: '—',
                                'apoDireccion' => $p->apoderado_direccion ?: '—',
                                'observacion' => $p->observacion ?: '—',
                                'enviado'     => $p->preinscrito_at?->format('d/m/Y H:i') ?? $p->created_at?->format('d/m/Y H:i') ?? '—',
                            ];
                        @endphp
                        <tr>
                            <td><span class="font-monospace small fw-semibold">{{ $p->codigo }}</span></td>
                            <td>
                                <div style="font-weight:700;font-size:.88rem;">{{ $p->nombres }} {{ $p->apellidos }}</div>
                            </td>
                            <td><span class="font-monospace small">{{ $p->dni }}</span></td>
                            <td>{{ $p->grado?->nombre ?? $p->nivel ?? '-' }}</td>
                            <td><span class="small">{{ $p->sede?->nombre ?? '-' }}</span></td>
                            <td>
                                <div class="small fw-semibold">{{ trim($p->apoderado_nombres . ' ' . $p->apoderado_apellidos) ?: '-' }}</div>
                                <div class="small text-muted">{{ $p->apoderado_telefono ?: '-' }}</div>
                            </td>
                            <td>
                                <span class="small text-muted">
                                    {{ $p->preinscrito_at?->format('d/m/Y') ?? $p->created_at?->format('d/m/Y') ?? '-' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge" style="border-radius:999px;padding:.35rem .7rem;font-weight:700;
                                    {{ $p->estado === 'admitido'
                                        ? 'background:#dcfce7;color:#15803d;'
                                        : (in_array($p->estado, ['no_admitido', 'desistido'], true)
                                            ? 'background:#fee2e2;color:#b91c1c;'
                                            : 'background:#fff4de;color:#a86a00;') }}">
                                    {{ $estadosPre[$p->estado] ?? $p->estado }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button type="button" class="btn jp-table-action jp-pre-detalle"
                                        data-bs-toggle="modal" data-bs-target="#preMatriculaModal"
                                        data-pre="{{ json_encode($preDatos, JSON_UNESCAPED_UNICODE) }}" title="Ver datos enviados">
                                        <i data-feather="eye"></i>
                                    </button>
                                    @if($puedeVerAdmision)
                                        <a href="{{ route('admision.expediente', $p) }}" class="btn jp-table-action"
                                           title="Abrir expediente en Admisión">
                                            <i data-feather="external-link"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i data-feather="inbox" style="width:36px;height:36px;opacity:.3;display:block;margin:0 auto 8px;"></i>
                                Todavía no llegan pre-matrículas desde el sitio web.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white border-0">
            {{ $preMatriculas->links() }}
        </div>
    </div>

{{-- Detalle de lo que la familia llenó en el formulario web --}}
<div class="modal fade jp-info-modal" id="preMatriculaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title d-flex align-items-center gap-2">
                    <i data-feather="globe"></i>
                    Pre-matrícula web
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="preMatriculaBody">
                <div class="text-center text-muted py-4">Selecciona una pre-matrícula.</div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Detalle de una pre-matricula web: lo que la familia llenó en el sitio.
        const preModal = document.getElementById('preMatriculaModal');
        if (preModal) {
            const preFila = (etiqueta, valor) => `
                <div class="col-md-6 mb-2">
                    <div class="small text-muted fw-bold text-uppercase" style="font-size:.68rem;letter-spacing:.03em;">${etiqueta}</div>
                    <div style="font-size:.9rem;font-weight:600;color:#0f132a;">${valor || '—'}</div>
                </div>`;

            preModal.addEventListener('show.bs.modal', (event) => {
                const button = event.relatedTarget;
                const body = document.getElementById('preMatriculaBody');
                if (!body || !button) return;

                let d = {};
                try {
                    d = JSON.parse(button.getAttribute('data-pre') || '{}');
                } catch (e) {
                    d = {};
                }

                body.innerHTML = `
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                        <span class="font-monospace fw-bold" style="color:#162061;">${d.codigo || ''}</span>
                        <span class="badge" style="background:#e2eefe;color:#1c297e;border-radius:999px;padding:.35rem .75rem;font-weight:700;">${d.estado || ''}</span>
                    </div>
                    <h6 class="fw-bold mb-2" style="color:#162061;">Postulante</h6>
                    <div class="row">
                        ${preFila('Nombre', d.alumno)}
                        ${preFila('DNI', d.dni)}
                        ${preFila('Fecha de nacimiento', d.nacimiento)}
                        ${preFila('Sexo', d.sexo)}
                        ${preFila('Nivel / Grado', d.nivelGrado)}
                        ${preFila('Sede', d.sede)}
                        ${preFila('Dirección', d.direccion)}
                        ${preFila('Colegio de procedencia', d.procedencia)}
                    </div>
                    <hr>
                    <h6 class="fw-bold mb-2" style="color:#162061;">Apoderado</h6>
                    <div class="row">
                        ${preFila('Nombre', d.apoderado)}
                        ${preFila('Parentesco', d.parentesco)}
                        ${preFila('DNI', d.apoDni)}
                        ${preFila('Teléfono', d.telefono)}
                        ${preFila('Correo', d.email)}
                        ${preFila('Dirección', d.apoDireccion)}
                    </div>
                    <hr>
                    <div class="row">
                        ${preFila('Año de admisión', d.anio)}
                        ${preFila('Enviado el', d.enviado)}
                        ${preFila('Observación', d.observacion)}
                    </div>`;

                if (window.feather) {
                    window.feather.replace();
                }
            });
        }
    });
</script>
@endpush
