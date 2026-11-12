{{--
    Barra superior de los modulos. Ya no lleva cabecera de color: el titulo
    de la pantalla lo muestra la barra del layout, asi que aqui solo quedan
    los botones de navegacion y, cuando hace falta, el dato que identifica
    al registro que se esta viendo (nombre del alumno, del activo...).
--}}
@if (!empty($contexto) || !empty($acciones))
    <div class="mod-barra">
        @if (!empty($contexto))
            <span class="mod-barra__contexto">{{ $contexto }}</span>
        @endif

        @if (!empty($acciones))
            <span class="mod-barra__acciones">{!! $acciones !!}</span>
        @endif
    </div>
@endif

@if (session('status'))
    <div class="alert alert-info">{{ session('status') }}</div>
@endif

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@include('partials.mod-modal-script')
