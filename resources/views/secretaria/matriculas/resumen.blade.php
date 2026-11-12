@php
    $estudiante = $matricula->estudiante;
    $apoderado = $estudiante?->apoderados
        ?->firstWhere('pivot.principal', true)
        ?? $estudiante?->apoderados?->first();
    $deuda = $matricula->deudas->sortByDesc('id')->first();
    $totalCargos = $matricula->cargos->sum('subtotal');
    $totalDescuentos = $matricula->descuentos->sum('monto');
    $totalFinal = max($totalCargos - $totalDescuentos, 0);
@endphp

<div class="jp-info-wrap">
    <div class="jp-info-hero">
        <div class="jp-info-avatar">
            <i data-feather="user"></i>
        </div>
        <div>
            <div class="jp-info-name">
                {{ $estudiante?->apellidos }} {{ $estudiante?->nombres }}
            </div>
            <div class="jp-info-sub">
                <i data-feather="credit-card"></i>
                DNI: {{ $estudiante?->dni ?? '-' }}
            </div>
            <div class="jp-info-sub">
                <i data-feather="calendar"></i>
                Matricula: {{ $matricula->fecha_matricula ?? '-' }}
            </div>
        </div>
    </div>

    <div class="jp-info-section">
        <div class="jp-info-section-title">
            <i data-feather="book-open"></i>
            Datos acad&eacute;micos
        </div>
        <div class="jp-info-grid">
            <div class="jp-info-item">
                <span>Nivel</span>
                <strong>{{ $matricula->grado?->nivel ?? '-' }}</strong>
            </div>
            <div class="jp-info-item">
                <span>Grado</span>
                <strong>{{ $matricula->grado?->nombre ?? '-' }}</strong>
            </div>
            <div class="jp-info-item">
                <span>Secci&oacute;n</span>
                <strong>{{ $matricula->seccion?->nombre ?? '-' }}</strong>
            </div>
            <div class="jp-info-item">
                <span>Periodo</span>
                <strong>
                    {{ $matricula->periodoAcademico?->codigo ?? '-' }}
                    @if ($matricula->periodoAcademico?->nombre)
                        - {{ $matricula->periodoAcademico->nombre }}
                    @endif
                </strong>
            </div>
            <div class="jp-info-item">
                <span>Estado</span>
                <strong>{{ $matricula->estado ?? '-' }}</strong>
            </div>
        </div>
    </div>

    @php
        $usuarioLogin = $credenciales['email'] ?? ($estudiante?->dni ?: null);
        $passwordTemporal = $credenciales['password'] ?? null;
    @endphp

    @if ($estudiante)
        <div class="jp-info-section">
            <div class="jp-info-section-title">
                <i data-feather="lock"></i>
                Credenciales de acceso
            </div>
            <div class="jp-info-grid">
                <div class="jp-info-item">
                    <span>Usuario</span>
                    <strong>{{ $usuarioLogin ?? '-' }}</strong>
                </div>
                <div class="jp-info-item">
                    <span>Contrasena temporal</span>
                    <strong>{{ $passwordTemporal ?? 'No disponible' }}</strong>
                </div>
            </div>
            @if ($estudiante?->dni)
                <div class="mt-2 d-flex flex-wrap gap-2">
                    <button type="button"
                        class="btn btn-sm btn-warning text-white jp-cred-reset"
                        data-url="{{ route('secretaria.matriculas.credenciales', $matricula) }}">
                        Generar nueva contrasena
                    </button>
                    <button type="button"
                        class="btn btn-sm btn-outline-secondary jp-cred-custom"
                        data-url="{{ route('secretaria.matriculas.credenciales.set', $matricula) }}"
                        data-email="{{ $usuarioLogin ?? '' }}">
                        Cambiar contrasena
                    </button>
                </div>
            @endif
        </div>
    @endif

    <div class="jp-info-section">
        <div class="jp-info-section-title">
            <i data-feather="user-check"></i>
            Apoderado principal
        </div>
        <div class="jp-info-grid">
            <div class="jp-info-item">
                <span>Nombre</span>
                <strong>{{ $apoderado?->apellidos }} {{ $apoderado?->nombres }}</strong>
            </div>
            <div class="jp-info-item">
                <span>DNI</span>
                <strong>{{ $apoderado?->dni ?? '-' }}</strong>
            </div>
            <div class="jp-info-item">
                <span>Tel&eacute;fono</span>
                <strong>{{ $apoderado?->telefono ?? '-' }}</strong>
            </div>
            <div class="jp-info-item">
                <span>Email</span>
                <strong>{{ $apoderado?->email ?? '-' }}</strong>
            </div>
        </div>
    </div>

    <div class="jp-info-section">
        <div class="jp-info-section-title">
            <i data-feather="dollar-sign"></i>
            Resumen de pagos
        </div>

        <div class="table-responsive">
            <table class="table jp-table mb-0">
                <thead>
                    <tr>
                        <th>Concepto</th>
                        <th>Cantidad</th>
                        <th>Precio</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($matricula->cargos as $cargo)
                        <tr>
                            <td>{{ $cargo->concepto }}</td>
                            <td>{{ $cargo->cantidad }}</td>
                            <td>S/ {{ number_format((float) $cargo->precio_unitario, 2) }}</td>
                            <td>S/ {{ number_format((float) $cargo->subtotal, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">No hay cargos registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($matricula->descuentos->isNotEmpty())
            <div class="mt-3">
                <div class="fw-bold mb-2">Descuentos aplicados</div>
                <ul class="mb-0">
                    @foreach ($matricula->descuentos as $descuento)
                        <li>
                            {{ $descuento->descripcion ?? $descuento->tipo }}:
                            - S/ {{ number_format((float) $descuento->monto, 2) }}
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mt-3">
            <div><strong>Subtotal:</strong> S/ {{ number_format((float) $totalCargos, 2) }}</div>
            <div><strong>Descuentos:</strong> S/ {{ number_format((float) $totalDescuentos, 2) }}</div>
            <div><strong>Total:</strong> S/ {{ number_format((float) $totalFinal, 2) }}</div>
            <div><strong>Saldo:</strong> S/ {{ number_format((float) ($deuda?->saldo ?? 0), 2) }}</div>
        </div>
    </div>
</div>
