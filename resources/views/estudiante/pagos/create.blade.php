@extends('layouts.mantis')

@section('title', 'Pago Virtual | Estudiante')
@section('page-title', 'Pago Virtual')
@section('body-class', 'role-estudiante')

@push('styles')
    <link rel="stylesheet" href="{{ url('css/secretaria-dashboard.css') }}">
@endpush

@section('sidebar')
    @include('estudiante.partials.sidebar')
@endsection

@section('content')
    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="jp-pay-wrap">
        <div class="jp-pay-header">
            <div>
                <a class="btn jp-attendance-back jp-attendance-back--top" href="{{ route('estudiante.dashboard') }}">
                    <i data-feather="arrow-left"></i>
                    Volver al panel
                </a>
                <h5 class="jp-pay-title">Pago virtual</h5>
                <p class="jp-pay-sub">Selecciona una deuda y registra tu pago con el voucher.</p>
            </div>
        </div>

        <div class="jp-pay-section">
            <h6 class="jp-pay-section__title">Selecciona una deuda</h6>
            <div class="jp-pay-grid">
                @forelse ($deudas as $deuda)
                    @php
                        $vence = $deuda->fecha_vencimiento ? \Carbon\Carbon::parse($deuda->fecha_vencimiento)->locale('es') : null;
                        $isSelected = (string) request('deuda_id') === (string) $deuda->id;
                    @endphp
                    <button type="button" class="jp-pay-card {{ $isSelected ? 'is-selected' : '' }}"
                        data-id="{{ $deuda->id }}"
                        data-saldo="{{ number_format((float) $deuda->saldo, 2, '.', '') }}"
                        data-vencimiento="{{ $vence ? $vence->translatedFormat('d M Y') : 'Sin fecha' }}">
                        <div class="jp-pay-card__title">{{ $deuda->concepto?->nombre ?? 'Concepto' }}</div>
                        <div class="jp-pay-card__meta">
                            <span>Periodo: {{ $deuda->periodo ?? '-' }}</span>
                            <span>Vence: {{ $vence ? $vence->translatedFormat('d M Y') : 'Sin fecha' }}</span>
                        </div>
                        <div class="jp-pay-card__saldo">S/ {{ number_format((float) $deuda->saldo, 2) }}</div>
                    </button>
                @empty
                    <div class="jp-pay-empty">No tienes deudas pendientes.</div>
                @endforelse
            </div>
        </div>

        <div class="jp-pay-form-card">
            <div class="jp-pay-form-header">
                <h6>Registrar pago</h6>
                <div class="jp-pay-form-badges">
                    <span>Saldo seleccionado: <strong id="jpSaldoLabel">--</strong></span>
                    <span>Vence: <strong id="jpVenceLabel">--</strong></span>
                </div>
            </div>
            <form method="POST" action="{{ route('estudiante.pagos.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="row g-3">
                    <div class="col-lg-5">
                        <label class="form-label">Deuda a pagar</label>
                        <select id="deudaSelect" name="deuda_id" class="form-select @error('deuda_id') is-invalid @enderror" required>
                            <option value="">Seleccionar deuda</option>
                            @foreach ($deudas as $deuda)
                                <option value="{{ $deuda->id }}" @selected((string) request('deuda_id') === (string) $deuda->id)>
                                    {{ $deuda->concepto?->nombre ?? 'Concepto' }} - S/ {{ number_format((float) $deuda->saldo, 2) }}
                                </option>
                            @endforeach
                        </select>
                        @error('deuda_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-lg-3">
                        <label class="form-label">Monto</label>
                        <input id="montoInput" type="number" step="0.01" name="monto"
                            class="form-control @error('monto') is-invalid @enderror"
                            placeholder="0.00" required>
                        @error('monto')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-lg-4">
                        <label class="form-label">Fecha de pago</label>
                        <input type="date" name="fecha_pago"
                            class="form-control @error('fecha_pago') is-invalid @enderror"
                            value="{{ old('fecha_pago', now()->toDateString()) }}" required>
                        @error('fecha_pago')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label">Comentario (opcional)</label>
                        <input type="text" name="comentario" class="form-control" placeholder="Referencia del pago">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Voucher (opcional)</label>
                        <div class="jp-pay-upload">
                            <input type="file" name="archivo" id="voucherInput" accept=".jpg,.jpeg,.png,.pdf">
                            <div class="jp-pay-upload__content">
                                <i data-feather="upload-cloud"></i>
                                <div>
                                    <strong>Sube tu voucher</strong>
                                    <span>JPG, PNG o PDF (max 5MB)</span>
                                </div>
                            </div>
                        </div>
                        <div class="jp-pay-preview" id="voucherPreview"></div>
                        @error('archivo')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="mt-3 d-flex justify-content-end">
                    <button type="submit" class="btn jp-pay-submit">Registrar pago</button>
                </div>
            </form>
        </div>

        <div class="jp-pay-history">
            <div class="jp-pay-history__header">
                <h6 class="mb-0">Mis pagos recientes</h6>
            </div>
            <div class="table-responsive">
                <table class="table jp-table mb-0">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Monto</th>
                            <th>Estado</th>
                            <th>Concepto</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pagos as $pago)
                            @php
                                $estado = strtolower($pago->estado ?? '');
                                $badge = match ($estado) {
                                    'confirmado' => 'bg-success',
                                    'pendiente' => 'bg-warning text-dark',
                                    'anulado' => 'bg-danger',
                                    default => 'bg-secondary',
                                };
                                $concepto = $pago->deudas->first()?->concepto?->nombre ?? '---';
                            @endphp
                            <tr>
                                <td>{{ optional($pago->fecha_pago)->format('Y-m-d') ?? '-' }}</td>
                                <td>S/ {{ number_format((float) $pago->monto, 2) }}</td>
                                <td><span class="badge {{ $badge }}">{{ ucfirst($estado ?: '-') }}</span></td>
                                <td>{{ $concepto }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">Sin pagos registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const cards = Array.from(document.querySelectorAll('.jp-pay-card'));
            const select = document.getElementById('deudaSelect');
            const montoInput = document.getElementById('montoInput');
            const saldoLabel = document.getElementById('jpSaldoLabel');
            const venceLabel = document.getElementById('jpVenceLabel');
            const voucherInput = document.getElementById('voucherInput');
            const voucherPreview = document.getElementById('voucherPreview');

            function setSelected(id, saldo, venc) {
                if (select) {
                    select.value = id || '';
                }
                if (saldoLabel) {
                    saldoLabel.textContent = saldo ? `S/ ${parseFloat(saldo).toFixed(2)}` : '--';
                }
                if (venceLabel) {
                    venceLabel.textContent = venc || '--';
                }
                if (montoInput && saldo) {
                    montoInput.value = parseFloat(saldo).toFixed(2);
                }
                cards.forEach(card => {
                    card.classList.toggle('is-selected', card.dataset.id === id);
                });
            }

            cards.forEach(card => {
                card.addEventListener('click', () => {
                    setSelected(card.dataset.id, card.dataset.saldo, card.dataset.vencimiento);
                });
            });

            if (select) {
                select.addEventListener('change', () => {
                    const option = select.selectedOptions[0];
                    const id = option?.value;
                    const card = cards.find(item => item.dataset.id === id);
                    if (card) {
                        setSelected(card.dataset.id, card.dataset.saldo, card.dataset.vencimiento);
                    } else {
                        setSelected('', '', '');
                    }
                });
            }

            if (cards.length) {
                const preselected = cards.find(card => card.classList.contains('is-selected'));
                if (preselected) {
                    setSelected(preselected.dataset.id, preselected.dataset.saldo, preselected.dataset.vencimiento);
                }
            }

            if (voucherInput && voucherPreview) {
                voucherInput.addEventListener('change', () => {
                    const file = voucherInput.files?.[0];
                    if (!file) {
                        voucherPreview.innerHTML = '';
                        return;
                    }
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = e => {
                            voucherPreview.innerHTML = `<img src="${e.target.result}" alt="Voucher">`;
                        };
                        reader.readAsDataURL(file);
                    } else {
                        voucherPreview.innerHTML = `<div class="jp-pay-preview__file">${file.name}</div>`;
                    }
                });
            }
        });
    </script>
@endsection
