{{--
    Modal de registro de los modulos.

    Reemplaza a los formularios que antes vivian dentro de una mod-card
    siempre visible: ahora la pantalla solo muestra el boton y el formulario
    se abre en una ventana.

        <x-mod-modal id="doc-nuevo" titulo="Registrar documento"
                     subtitulo="PDF, imagen, Word, Excel o video. Hasta 20 MB."
                     boton="Registrar documento">
            <form method="POST" action="..." class="mod-form row g-3"> ... </form>
        </x-mod-modal>

    Si la validacion falla, partials/modulo vuelve a abrir el modal que se
    envio (ver el script de reapertura que hay alli).
--}}
@props([
    'id',
    'titulo',
    'subtitulo' => null,
    'boton' => null,
    'ancho' => 'modal-lg',
])

{{-- Sin subtitulo el texto de la izquierda solo repetiria la etiqueta del
     boton, asi que en ese caso se deja unicamente el boton. --}}
<div class="mod-launcher {{ $subtitulo ? '' : 'mod-launcher--solo' }}">
    @if ($subtitulo)
        <div class="mod-launcher__texto">
            <h5>{{ $titulo }}</h5>
            <small>{{ $subtitulo }}</small>
        </div>
    @endif
    <button type="button" class="mod-btn mod-btn--nuevo" data-bs-toggle="modal" data-bs-target="#{{ $id }}">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <line x1="5" y1="12" x2="19" y2="12"></line>
        </svg>
        {{ $boton ?? $titulo }}
    </button>
</div>

<div class="modal fade mod-modal" id="{{ $id }}" tabindex="-1"
     aria-labelledby="{{ $id }}-titulo" aria-hidden="true">
    <div class="modal-dialog {{ $ancho }} modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 id="{{ $id }}-titulo">{{ $titulo }}</h5>
                    @if ($subtitulo)
                        <small>{{ $subtitulo }}</small>
                    @endif
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
