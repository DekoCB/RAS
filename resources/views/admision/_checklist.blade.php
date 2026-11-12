{{--
    Checklist de requisitos (RF-14). Cada fila sube su propio archivo: al elegirlo
    se envia solo. Variables: $postulante, $puedeSubir (bool),
    $soloExistentesOpcionales (bool: en el expediente se ocultan los opcionales no cargados).
--}}
@php
    $clObligatorios = \App\Models\PostulanteDocumento::OBLIGATORIOS;
    $clOpcionales   = \App\Models\PostulanteDocumento::OPCIONALES;
    $clPorTipo      = $postulante->documentos->keyBy('tipo');
    $puedeSubir     = $puedeSubir ?? false;
    $soloExistentesOpcionales = $soloExistentesOpcionales ?? false;
@endphp

<ul class="ad-check">
    @foreach ($clObligatorios + $clOpcionales as $clave => $etiqueta)
        @php
            $doc = $clPorTipo[$clave] ?? null;
            $esObligatorio = isset($clObligatorios[$clave]);
            if (!$doc && !$esObligatorio && $soloExistentesOpcionales && !$puedeSubir) continue;
            $estadoCls = $doc ? 'is-done' : ($esObligatorio ? 'is-missing' : '');
        @endphp
        <li class="ad-check__item {{ $estadoCls }}">
            <span class="ad-check__state">
                @if ($doc)
                    <i data-feather="check"></i>
                @elseif ($esObligatorio)
                    <i data-feather="file-text"></i>
                @else
                    <i data-feather="minus"></i>
                @endif
            </span>
            <div>
                <div class="ad-check__name">
                    {{ $etiqueta }}
                    <small>{{ $esObligatorio ? 'Requisito del reglamento' : 'Opcional' }}</small>
                </div>
                <div class="ad-check__meta">
                    @if ($doc)
                        {{ $doc->nombre_original }} &middot; {{ $doc->created_at->format('d/m/Y H:i') }}
                    @else
                        {{ $esObligatorio ? 'Pendiente · se puede cargar despues' : 'No cargado' }}
                        @if ($puedeSubir) &middot; PDF o imagen, hasta 8 MB @endif
                    @endif
                </div>
            </div>
            <span class="ad-check__acts">
                @if ($doc)
                    <a href="{{ asset('storage/' . $doc->archivo) }}" target="_blank" rel="noopener"
                       class="ad-btn ad-btn--ghost ad-btn--sm"><i data-feather="eye"></i> Ver</a>
                @endif

                @if ($puedeSubir)
                    <form method="POST" action="{{ route('admision.documentos.subir', $postulante) }}"
                          enctype="multipart/form-data" class="ad-inline ad-subir" data-subir>
                        @csrf
                        <input type="hidden" name="tipo" value="{{ $clave }}">
                        <input type="file" name="archivo" accept=".pdf,.jpg,.jpeg,.png,.webp" hidden>
                        <button type="button" class="ad-btn ad-btn--sm {{ $doc ? 'ad-btn--soft' : 'ad-btn--navy' }}">
                            <i data-feather="{{ $doc ? 'refresh-cw' : 'upload' }}"></i>
                            {{ $doc ? 'Reemplazar' : 'Subir' }}
                        </button>
                    </form>

                    @if ($doc)
                        <form method="POST" action="{{ route('admision.documentos.eliminar', $doc) }}"
                              onsubmit="return confirm('Eliminar este documento?')" class="ad-inline">
                            @csrf
                            @method('DELETE')
                            <button class="ad-btn ad-btn--danger ad-btn--sm" title="Eliminar"><i data-feather="trash-2"></i></button>
                        </form>
                    @endif
                @endif
            </span>
        </li>
    @endforeach
</ul>

@if ($puedeSubir)
    @once
        @push('scripts')
        <script>
            // El boton abre el selector; al elegir el archivo la fila se envia sola.
            document.querySelectorAll('form[data-subir]').forEach(function (form) {
                var input = form.querySelector('input[type=file]');
                var boton = form.querySelector('button');
                boton.addEventListener('click', function () { input.click(); });
                input.addEventListener('change', function () {
                    if (!input.files.length) return;
                    if (input.files[0].size > 8 * 1024 * 1024) {
                        alert('El archivo supera los 8 MB.');
                        input.value = '';
                        return;
                    }
                    boton.disabled = true;
                    boton.innerHTML = 'Subiendo...';
                    form.submit();
                });
            });
        </script>
        @endpush
    @endonce
@endif
