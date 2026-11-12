{{--
    Campos de un apoderado (mismos que pide Matricula).
    Variables: $postulante, $p (prefijo: 'apoderado' o 'apoderado2'), $obligatorio (bool).
--}}
@php
    $v   = fn ($campo) => old("{$p}_{$campo}", $postulante->{"{$p}_{$campo}"});
    $req = $obligatorio ? 'required' : '';
    $nac = old("{$p}_fecha_nacimiento", $postulante->{"{$p}_fecha_nacimiento"}?->toDateString());
@endphp
<div class="ad-field c-4">
    <label for="{{ $p }}_parentesco">Tipo de relacion</label>
    <select id="{{ $p }}_parentesco" name="{{ $p }}_parentesco" class="ad-select" {{ $req }}>
        <option value="">Seleccione...</option>
        @foreach (['Madre', 'Padre', 'Tutor'] as $op)
            <option value="{{ $op }}" @selected($v('parentesco') === $op)>{{ $op }}</option>
        @endforeach
    </select>
</div>
<div class="ad-field c-4">
    <label for="{{ $p }}_dni">Documento de identidad</label>
    <input id="{{ $p }}_dni" type="text" name="{{ $p }}_dni" class="ad-input" maxlength="20"
           placeholder="Ingrese DNI" value="{{ $v('dni') }}">
</div>
<div class="ad-field c-4">
    <label for="{{ $p }}_nombres">Nombres</label>
    <input id="{{ $p }}_nombres" type="text" name="{{ $p }}_nombres" class="ad-input" {{ $req }} maxlength="120"
           placeholder="Nombres completos" value="{{ $v('nombres') }}">
</div>

<div class="ad-field c-4">
    <label for="{{ $p }}_apellidos">Apellidos</label>
    <input id="{{ $p }}_apellidos" type="text" name="{{ $p }}_apellidos" class="ad-input" {{ $req }} maxlength="120"
           placeholder="Apellidos completos" value="{{ $v('apellidos') }}">
</div>
<div class="ad-field c-4">
    <label for="{{ $p }}_fecha_nacimiento">Fecha de nacimiento</label>
    <input id="{{ $p }}_fecha_nacimiento" type="date" name="{{ $p }}_fecha_nacimiento" class="ad-input" value="{{ $nac }}">
</div>
<div class="ad-field c-4">
    <label for="{{ $p }}_sexo">Sexo</label>
    <select id="{{ $p }}_sexo" name="{{ $p }}_sexo" class="ad-select">
        <option value="">Seleccione...</option>
        <option value="M" @selected($v('sexo') === 'M')>Masculino</option>
        <option value="F" @selected($v('sexo') === 'F')>Femenino</option>
    </select>
</div>

<div class="ad-field c-4">
    <label for="{{ $p }}_estado_civil">Estado civil</label>
    <select id="{{ $p }}_estado_civil" name="{{ $p }}_estado_civil" class="ad-select">
        <option value="">Seleccione...</option>
        @foreach (['Soltero(a)', 'Casado(a)', 'Conviviente'] as $op)
            <option value="{{ $op }}" @selected($v('estado_civil') === $op)>{{ $op }}</option>
        @endforeach
    </select>
</div>
<div class="ad-field c-4">
    <label for="{{ $p }}_ciudad">Ciudad</label>
    <input id="{{ $p }}_ciudad" type="text" name="{{ $p }}_ciudad" class="ad-input" maxlength="120"
           placeholder="Ciudad de residencia" value="{{ $v('ciudad') }}">
</div>
<div class="ad-field c-4">
    <label for="{{ $p }}_telefono">Telefono / celular</label>
    <input id="{{ $p }}_telefono" type="text" name="{{ $p }}_telefono" class="ad-input" maxlength="40"
           placeholder="Ingrese numero de telefono" value="{{ $v('telefono') }}">
</div>

<div class="ad-field c-8">
    <label for="{{ $p }}_direccion">Direccion</label>
    <input id="{{ $p }}_direccion" type="text" name="{{ $p }}_direccion" class="ad-input" maxlength="255"
           placeholder="Direccion completa" value="{{ $v('direccion') }}">
</div>
<div class="ad-field c-4">
    <label for="{{ $p }}_email">Correo electronico</label>
    <input id="{{ $p }}_email" type="email" name="{{ $p }}_email" class="ad-input" maxlength="150"
           placeholder="correo@ejemplo.com" value="{{ $v('email') }}">
</div>
