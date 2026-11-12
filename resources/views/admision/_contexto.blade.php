{{-- Chip con el postulante que se esta trabajando (asistente y expediente). --}}
@php
    $ctxIniciales = mb_strtoupper(mb_substr($postulante->nombres, 0, 1) . mb_substr($postulante->apellidos, 0, 1));
    $ctxNivel = strtolower($postulante->nivel ?? '');
@endphp
<span class="ad-context">
    <span class="ad-avatar ad-avatar--{{ $ctxNivel }}">{{ $ctxIniciales }}</span>
    <span>
        <span class="ad-context__name">{{ $postulante->nombreCompleto() }}</span><br>
        <span class="ad-context__meta">
            <span class="ad-code">{{ $postulante->codigo }}</span>
            &nbsp;{{ $postulante->grado?->nombre ?? $postulante->nivel }}
        </span>
    </span>
</span>
