@php
    $codigoEstudiante = trim((string) ($estudiante->codigo ?? ''));
    if ($codigoEstudiante === '' || $codigoEstudiante === '-') {
        $codigoEstudiante = $estudiante->carnet_codigo
            ? trim((string) $estudiante->carnet_codigo)
            : 'ALU-' . str_pad((string) $estudiante->id, 5, '0', STR_PAD_LEFT);
    }
@endphp

<div class="jp-info-wrap">
    <div class="jp-info-hero">
        <div class="jp-info-avatar">
            <i data-feather="dollar-sign"></i>
        </div>
        <div>
            <div class="jp-info-name">
                {{ $estudiante->apellidos }} {{ $estudiante->nombres }}
            </div>
            <div class="jp-info-sub">
                <i data-feather="credit-card"></i>
                DNI: {{ $estudiante->dni ?? '-' }}
            </div>
            <div class="jp-info-sub">
                <i data-feather="hash"></i>
                Codigo: {{ $codigoEstudiante }}
            </div>

            @if(!empty($matricula))
                <div class="jp-info-sub">
                    <i data-feather="bookmark"></i>
                    {{ $matricula->anioEscolar?->nombre ?? '-' }} ·
                    {{ $matricula->grado?->nombre ?? '-' }}
                    ({{ $matricula->grado?->nivel ?? '-' }})
                </div>
            @endif
        </div>
    </div>

    {{-- ✅ RESUMEN DE DEUDAS --}}
    <div class="jp-info-section">
        <div class="jp-info-section-title">
            <i data-feather="alert-circle"></i>
            Resumen de deudas
        </div>

        <div class="jp-info-grid">
            <div class="jp-info-item">
                <span>Deuda total</span>
                <strong>S/ {{ number_format($deudaTotal ?? 0, 2) }}</strong>
            </div>
            <div class="jp-info-item">
                <span>Saldo pendiente</span>
                <strong>S/ {{ number_format($saldoPendiente ?? 0, 2) }}</strong>
            </div>
            <div class="jp-info-item">
                <span>Vencido</span>
                <strong>S/ {{ number_format($vencido ?? 0, 2) }}</strong>
            </div>
            <div class="jp-info-item">
                <span>Deudas</span>
                <strong>{{ $cantidadDeudas ?? (isset($deudas) ? $deudas->count() : 0) }}</strong>
            </div>
        </div>
    </div>

    {{-- ✅ TABLA DE DEUDAS --}}
    <div class="jp-info-section">
        <div class="jp-info-section-title">
            <i data-feather="file-text"></i>
            Deudas
        </div>

        <div class="table-responsive">
            <table class="table jp-table mb-0">
                <thead>
                    <tr>
                        <th>Periodo</th>
                        <th>Concepto</th>
                        <th>Vence</th>
                        <th>Monto</th>
                        <th>Pagado</th>
                        <th>Saldo</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($deudas ?? collect() as $deuda)
                        @php
                            $estado = strtolower($deuda->estado ?? 'pendiente');

                            $vence = $deuda->fecha_vencimiento
                                ? \Carbon\Carbon::parse($deuda->fecha_vencimiento)
                                : null;

                            $esVencida = $vence
                                && in_array($estado, ['pendiente','parcial'], true)
                                && $vence->lt(\Carbon\Carbon::today());

                            $estadoClass = $estado === 'pagado'
                                ? 'is-activo'
                                : (in_array($estado, ['anulado', 'cancelado'], true)
                                    ? 'is-inactivo'
                                    : ($esVencida ? 'is-inactivo' : 'is-pendiente'));

                            // ✅ Pagado real (monto - saldo)
                            $monto = (float) ($deuda->monto ?? 0);
                            $saldo = (float) ($deuda->saldo ?? 0);
                            $pagado = max($monto - $saldo, 0);
                        @endphp

                        <tr>
                            <td>{{ $deuda->periodo ?? '-' }}</td>
                            <td>{{ $deuda->concepto?->nombre ?? '-' }}</td>
                            <td>{{ $vence ? $vence->format('d/m/Y') : '-' }}</td>
                            <td>S/ {{ number_format($monto, 2) }}</td>
                            <td>S/ {{ number_format($pagado, 2) }}</td>
                            <td>S/ {{ number_format($saldo, 2) }}</td>
                            <td>
                                <span class="jp-status-pill {{ $estadoClass }}">
                                    {{ strtoupper($esVencida ? 'VENCIDO' : $estado) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">
                                No hay deudas registradas para este estudiante.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ✅ RESUMEN DE PAGOS --}}
    <div class="jp-info-section">
        <div class="jp-info-section-title">
            <i data-feather="bar-chart-2"></i>
            Resumen de pagos
        </div>
        <div class="jp-info-grid">
            <div class="jp-info-item">
                <span>Pagos registrados</span>
                <strong>{{ $pagos->count() }}</strong>
            </div>
            <div class="jp-info-item">
                <span>Monto total</span>
                <strong>S/ {{ number_format($totalPagado ?? 0, 2) }}</strong>
            </div>
            <div class="jp-info-item">
                <span>Ultimo pago</span>
                <strong>{{ $ultimoPago?->fecha_pago?->format('d/m/Y') ?? '-' }}</strong>
            </div>
            <div class="jp-info-item">
                <span>Metodo</span>
                <strong>{{ ($metodos ?? collect())->isNotEmpty() ? $metodos->join(', ') : '-' }}</strong>
            </div>
        </div>
    </div>

    {{-- ✅ HISTORIAL DE PAGOS --}}
    <div class="jp-info-section">
        <div class="jp-info-section-title">
            <i data-feather="list"></i>
            Pagos
        </div>
        <div class="table-responsive">
            <table class="table jp-table mb-0">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Concepto</th>
                        <th>Monto</th>
                        <th>Metodo</th>
                        <th>Estado</th>
                        <th>Registrado por</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pagos ?? collect() as $pago)
                        @php
                            $deudasAlumno = $pago->deudas ?? collect();
                            $montoAplicadoPago = (float) $deudasAlumno->sum(function ($deudaItem) {
                                return (float) ($deudaItem->pivot->monto_aplicado ?? 0);
                            });

                            $concepto = $deudasAlumno->count() > 1
                                ? 'Varios'
                                : ($deudasAlumno->first()?->concepto?->nombre ?? '-');

                            $estado = strtolower($pago->estado ?? 'pendiente');
                            $estadoClass = in_array($estado, ['pagado','aprobado','confirmado'], true)
                                ? 'is-activo'
                                : ($estado === 'pendiente' ? 'is-pendiente' : 'is-inactivo');
                        @endphp
                        <tr>
                            <td>{{ $pago->fecha_pago?->format('d/m/Y') ?? '-' }}</td>
                            <td>{{ $concepto }}</td>
                            <td>S/ {{ number_format($montoAplicadoPago > 0 ? $montoAplicadoPago : (float)($pago->monto ?? 0), 2) }}</td>
                            <td>{{ $pago->metodo ?? '-' }}</td>
                            <td>
                                <span class="jp-status-pill {{ $estadoClass }}">
                                    {{ strtoupper($estado) }}
                                </span>
                            </td>
                            <td>{{ $pago->registradoPor?->name ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">No hay pagos registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
