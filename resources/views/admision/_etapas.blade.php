{{--
    Cronograma de etapas del proceso (RF-15, RF-16, RF-19) como linea de tiempo.
    Cada etapa se maneja con lo minimo: estado y fecha programada.
--}}
<div class="ad-tl">
    @foreach ($etapas as $clave => $titulo)
        @php
            $registro = $postulante->etapas->firstWhere('etapa', $clave);
            $estadoActual = $registro->estado ?? 'pendiente';
            $pillEtapa = match ($estadoActual) {
                'completada' => 'ad-pill--ok',
                'no_asistio' => 'ad-pill--warn',
                'programada' => 'ad-pill--navy',
                default      => 'ad-pill--mute',
            };
        @endphp

        <div class="ad-tl__item is-{{ $estadoActual }}">
            <span class="ad-tl__dot">
                @if ($estadoActual === 'completada')
                    <i data-feather="check"></i>
                @elseif ($estadoActual === 'programada')
                    <i data-feather="calendar"></i>
                @else
                    {{ $loop->iteration }}
                @endif
            </span>

            <form method="POST" action="{{ route('admision.etapas.guardar', $postulante) }}" class="ad-tl__card ad-tl__row">
                @csrf
                <input type="hidden" name="etapa" value="{{ $clave }}">

                <div class="ad-tl__info">
                    <div class="ad-tl__name">{{ $titulo }}</div>
                    <div class="ad-tl__when">
                        @if ($registro?->fecha_programada)
                            Programada para el {{ $registro->fecha_programada->format('d/m/Y H:i') }}
                        @else
                            Sin programar
                        @endif
                    </div>
                </div>

                <span class="ad-pill {{ $pillEtapa }}">{{ $estados[$estadoActual] ?? $estadoActual }}</span>

                <div class="ad-field">
                    <label for="estado_{{ $clave }}">Estado</label>
                    <select id="estado_{{ $clave }}" name="estado" class="ad-select">
                        @foreach ($estados as $ec => $et)
                            <option value="{{ $ec }}" @selected($estadoActual === $ec)>{{ $et }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="ad-field">
                    <label for="prog_{{ $clave }}">Fecha programada</label>
                    <input id="prog_{{ $clave }}" type="datetime-local" name="fecha_programada" class="ad-input"
                           value="{{ $registro?->fecha_programada?->format('Y-m-d\TH:i') }}">
                </div>
                <div class="ad-field ad-field--end">
                    <button type="submit" class="ad-btn ad-btn--navy ad-btn--sm">
                        <i data-feather="save"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    @endforeach
</div>
