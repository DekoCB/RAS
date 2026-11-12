@extends('layouts.mantis')

@section('title', 'Admision - Paso 4 | R.A.S')
@section('page-title', 'Derecho de admision')
@section('body-class', 'role-superadmin')

@include('admision._layout_head')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
@php $pagado = $postulante->pagoValidado(); @endphp
<div class="ad-page">
    <div class="ad-toolbar">
        @include('admision._contexto')
        <div class="ad-toolbar__actions">
            <a href="{{ route('admision.index') }}" class="ad-btn ad-btn--ghost">
                <i data-feather="log-out"></i> Guardar y salir
            </a>
        </div>
    </div>

    @include('admision._stepper')
    @include('admision._alertas')

    <section class="ad-card">
        <div class="ad-card__head">
            <div class="ad-card__title">
                <span class="ad-card__icon ad-card__icon--gold"><i data-feather="credit-card"></i></span>
                <div>
                    <h2>Registrar pago</h2>
                    <p>El monto viene del tarifario (cuota de ingreso, Art. 54) y se puede ajustar si corresponde.</p>
                </div>
            </div>
            <div class="ad-card__aside">
                @if ($pagado)
                    <span class="ad-pill ad-pill--ok">Derecho pagado</span>
                @else
                    <span class="ad-pill ad-pill--warn">Pendiente de validar</span>
                @endif
            </div>
        </div>

        <div class="ad-card__body">
            <div class="ad-amount">
                <span class="ad-amount__icon"><i data-feather="dollar-sign"></i></span>
                <div>
                    <div class="ad-amount__lbl">Cuota de ingreso</div>
                    <div class="ad-amount__val"><small>S/</small> {{ number_format($tarifa, 2) }}</div>
                </div>
                <div class="ad-amount__note">Por unica vez al ingreso<br>del estudiante al colegio</div>
            </div>

            <form method="POST" action="{{ route('admision.pagos.registrar', $postulante) }}"
                  enctype="multipart/form-data" class="ad-grid">
                @csrf
                <div class="ad-field c-2">
                    <label for="monto">Monto (S/)</label>
                    <input id="monto" type="number" step="0.01" min="0" name="monto" class="ad-input" required
                           value="{{ old('monto', $tarifa) }}">
                </div>
                <div class="ad-field c-2">
                    <label for="metodo">Metodo de pago</label>
                    <select id="metodo" name="metodo" class="ad-select" required>
                        @foreach ($metodos as $clave => $etiqueta)
                            <option value="{{ $clave }}">{{ $etiqueta }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="ad-field c-3">
                    <label for="numero_operacion">Numero de operacion</label>
                    <input id="numero_operacion" type="text" name="numero_operacion" class="ad-input" maxlength="60"
                           placeholder="Opcional">
                </div>
                <div class="ad-field c-2">
                    <label for="fecha">Fecha</label>
                    <input id="fecha" type="date" name="fecha" class="ad-input" required
                           value="{{ now()->toDateString() }}">
                </div>
                <div class="ad-field c-3">
                    <label for="voucher">Voucher</label>
                    <input id="voucher" type="file" name="voucher" class="ad-input"
                           accept=".pdf,.jpg,.jpeg,.png,.webp">
                </div>
                <div class="ad-field c-9">
                    <label for="observacion">Observacion</label>
                    <input id="observacion" type="text" name="observacion" class="ad-input" maxlength="255"
                           placeholder="Opcional">
                </div>
                <div class="ad-field ad-field--end c-3">
                    <button type="submit" class="ad-btn ad-btn--navy ad-btn--block">
                        <i data-feather="plus"></i> Registrar pago
                    </button>
                </div>
            </form>
        </div>
    </section>

    <section class="ad-card">
        <div class="ad-card__head">
            <div class="ad-card__title">
                <span class="ad-card__icon"><i data-feather="list"></i></span>
                <div>
                    <h2>Pagos registrados</h2>
                    <p>El expediente avanza cuando hay al menos un pago validado.</p>
                </div>
            </div>
        </div>

        <div class="ad-table-wrap">
            <table class="ad-table">
                <thead>
                    <tr>
                        <th>Fecha</th><th>Monto</th><th>Metodo</th><th>Operacion</th>
                        <th>Voucher</th><th>Estado</th><th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($postulante->pagos as $pago)
                        <tr>
                            <td>{{ $pago->fecha->format('d/m/Y') }}</td>
                            <td class="ad-strong">S/ {{ number_format($pago->monto, 2) }}</td>
                            <td>{{ \App\Models\PostulantePago::METODOS[$pago->metodo] ?? $pago->metodo }}</td>
                            <td class="ad-muted">{{ $pago->numero_operacion ?: '--' }}</td>
                            <td>
                                @if ($pago->voucher)
                                    <a href="{{ asset('storage/' . $pago->voucher) }}" target="_blank" rel="noopener"
                                       class="ad-link">Ver voucher</a>
                                @else
                                    <span class="ad-muted">--</span>
                                @endif
                            </td>
                            <td>
                                @if ($pago->estado === 'validado')
                                    <span class="ad-pill ad-pill--ok">Validado</span>
                                @elseif ($pago->estado === 'rechazado')
                                    <span class="ad-pill ad-pill--danger">Rechazado</span>
                                @else
                                    <span class="ad-pill ad-pill--warn">Pendiente</span>
                                @endif
                            </td>
                            <td class="ad-right">
                                @can('admision.evaluar')
                                    @if ($pago->estado === 'pendiente')
                                        <span class="ad-actions">
                                            <form method="POST" action="{{ route('admision.pagos.validar', $pago) }}" class="ad-inline">
                                                @csrf
                                                <input type="hidden" name="accion" value="validado">
                                                <button class="ad-btn ad-btn--navy ad-btn--sm"><i data-feather="check"></i> Validar</button>
                                            </form>
                                            <form method="POST" action="{{ route('admision.pagos.validar', $pago) }}" class="ad-inline">
                                                @csrf
                                                <input type="hidden" name="accion" value="rechazado">
                                                <button class="ad-btn ad-btn--danger ad-btn--sm"><i data-feather="x"></i> Rechazar</button>
                                            </form>
                                        </span>
                                    @endif
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="ad-empty">
                                <span class="ad-empty__icon"><i data-feather="credit-card"></i></span>
                                <strong>Sin pagos registrados</strong>
                                Registra el pago de la cuota de ingreso para continuar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="ad-card__foot">
            <a href="{{ route('admision.paso3', $postulante) }}" class="ad-btn ad-btn--ghost">
                <i data-feather="arrow-left"></i> Volver
            </a>
            <form method="POST" action="{{ route('admision.paso4.continuar', $postulante) }}">
                @csrf
                <button type="submit" class="ad-btn ad-btn--navy">
                    Continuar al paso 5 <i data-feather="arrow-right"></i>
                </button>
            </form>
        </div>
    </section>
</div>
@endsection
