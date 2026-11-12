{{--
    Buscador de admitidos de Admision sin matricula (nombre, DNI o codigo).
    Variables: $admitidos (coleccion de arrays id/codigo/nombre/nivel/grado/datos),
               $postulanteSel (id preseleccionado o 0), $ayuda (texto a la derecha).
    Al elegir uno emite el evento "admitido:seleccionado" con el registro en detail
    (null cuando se quita la seleccion); cada paso decide que hacer con el.
--}}
@php
    $admitidos = $admitidos ?? collect();
    $postulanteSel = (int) ($postulanteSel ?? 0);
    $seleccionado = $admitidos->first(fn ($a) => $a['id'] === $postulanteSel);
@endphp
<style>
    .ba-wrap { position: relative; }
    .ba-input { padding-left: 38px !important; }
    .ba-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; color: #94a3b8; pointer-events: none; }
    .ba-lista {
        position: absolute; left: 0; right: 0; top: calc(100% + 4px); z-index: 30;
        background: #fff; border: 1px solid #d3dae6; border-radius: 10px;
        box-shadow: 0 10px 28px -12px rgba(15,19,42,.3); max-height: 280px; overflow-y: auto; display: none;
    }
    .ba-item { display: flex; align-items: center; gap: 10px; padding: 9px 12px; cursor: pointer; border-bottom: 1px solid #eef2f7; }
    .ba-item:last-child { border-bottom: none; }
    .ba-item:hover, .ba-item.is-activo { background: #f3f6fd; }
    .ba-item__nombre { font-weight: 700; color: #0f132a; font-size: .88rem; }
    .ba-item__meta { font-size: .75rem; color: #64748b; }
    .ba-item__codigo { margin-left: auto; font-family: ui-monospace, Consolas, monospace; font-size: .72rem; font-weight: 700; color: #151f5e; background: #e4eaf8; padding: 3px 8px; border-radius: 6px; white-space: nowrap; }
    .ba-vacio { padding: 12px; font-size: .85rem; color: #64748b; }
    .ba-chip {
        display: none; align-items: center; gap: 10px; margin-top: 10px;
        background: #fff; border: 1px solid #e8cd8c; border-radius: 10px; padding: 8px 12px; font-size: .86rem;
    }
    .ba-chip.is-visible { display: flex; }
    .ba-chip strong { color: #0f132a; }
    .ba-chip button { margin-left: auto; background: none; border: none; color: #b91c1c; font-weight: 700; cursor: pointer; font-size: .8rem; }
</style>

<div class="form-card" style="border:1.5px dashed #c9992e;background:#fdf8ec;margin-bottom:22px">
    <input type="hidden" name="postulante_id" id="postulanteId" value="{{ $seleccionado ? $seleccionado['id'] : '' }}">

    @if ($admitidos->isEmpty())
        <div class="d-flex flex-wrap align-items-center gap-2" style="font-size:.88rem">
            <i data-feather="user-check" style="width:16px;height:16px;color:#a87c1e"></i>
            <strong>Cargar desde Admisi&oacute;n:</strong>
            <span class="text-muted">
                no hay postulantes admitidos pendientes de matr&iacute;cula. Aparecen aqu&iacute; cuando en su
                expediente de Admisi&oacute;n se publica el resultado como <em>Admitido</em>.
            </span>
            <a href="{{ route('admision.index') }}" class="ms-auto" style="font-weight:700;color:#172269">Ir a Admisi&oacute;n</a>
        </div>
    @else
        <div class="d-flex flex-wrap align-items-end gap-3">
            <div style="flex:1 1 380px">
                <label class="form-label" for="buscarAdmitido">
                    <i data-feather="user-check" style="width:15px;height:15px;vertical-align:-3px"></i>
                    Cargar desde Admisi&oacute;n
                    <span class="text-muted fw-normal">({{ $admitidos->count() }} admitidos sin matr&iacute;cula)</span>
                </label>
                <div class="ba-wrap">
                    <i data-feather="search" class="ba-icon"></i>
                    <input id="buscarAdmitido" type="text" class="form-control ba-input" autocomplete="off"
                           placeholder="Escribe el nombre, DNI o c&oacute;digo del admitido...">
                    <div id="baLista" class="ba-lista"></div>
                </div>
                <div id="baChip" class="ba-chip {{ $seleccionado ? 'is-visible' : '' }}">
                    <i data-feather="check-circle" style="width:16px;height:16px;color:#15803d"></i>
                    <span id="baChipTexto">
                        @if ($seleccionado)
                            <strong>{{ $seleccionado['nombre'] }}</strong> &middot; {{ $seleccionado['codigo'] }} &middot; {{ $seleccionado['grado'] ?? $seleccionado['nivel'] }}
                        @endif
                    </span>
                    <button type="button" id="baQuitar">Quitar</button>
                </div>
            </div>
            <div class="text-muted" style="font-size:.85rem;flex:1 1 260px">{!! $ayuda ?? '' !!}</div>
        </div>
        <div id="admitidoAviso" class="alert alert-warning py-2 mt-3 mb-0" style="display:none;font-size:.85rem"></div>

        <script>
            (function () {
                var datos = @json($admitidos->map(fn ($a) => $a + ['dni' => $a['datos']['alumno_dni'] ?? ''])->values());
                var input = document.getElementById('buscarAdmitido');
                var lista = document.getElementById('baLista');
                var hidden = document.getElementById('postulanteId');
                var chip = document.getElementById('baChip');
                var chipTexto = document.getElementById('baChipTexto');
                var activo = -1, visibles = [];

                var esc = function (t) { return (t || '').toString().replace(/[&<>"]/g, function (c) { return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]; }); };
                var norm = function (t) {
                    return (t || '').toString().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                };
                var emitir = function (item) {
                    document.dispatchEvent(new CustomEvent('admitido:seleccionado', { detail: item }));
                };
                var cerrar = function () { lista.style.display = 'none'; activo = -1; };

                var pintar = function () {
                    var q = norm(input.value.trim());
                    visibles = !q ? datos.slice(0, 15) : datos.filter(function (a) {
                        return norm(a.nombre).indexOf(q) !== -1 || norm(a.codigo).indexOf(q) !== -1 || norm(a.dni).indexOf(q) !== -1;
                    }).slice(0, 15);
                    if (!visibles.length) {
                        lista.innerHTML = '<div class="ba-vacio">Sin coincidencias para "' + esc(input.value) + '".</div>';
                    } else {
                        lista.innerHTML = visibles.map(function (a, i) {
                            return '<div class="ba-item" data-i="' + i + '">'
                                + '<div><div class="ba-item__nombre">' + esc(a.nombre) + '</div>'
                                + '<div class="ba-item__meta">' + (a.dni ? 'DNI ' + esc(a.dni) + ' · ' : '') + esc(a.grado || a.nivel) + '</div></div>'
                                + '<span class="ba-item__codigo">' + esc(a.codigo) + '</span></div>';
                        }).join('');
                    }
                    lista.style.display = 'block';
                    activo = -1;
                };

                var elegir = function (a) {
                    hidden.value = a.id;
                    chipTexto.innerHTML = '<strong>' + esc(a.nombre) + '</strong> · ' + esc(a.codigo) + ' · ' + esc(a.grado || a.nivel);
                    chip.classList.add('is-visible');
                    input.value = '';
                    cerrar();
                    emitir(a);
                };

                input.addEventListener('focus', pintar);
                input.addEventListener('input', pintar);
                input.addEventListener('keydown', function (e) {
                    if (lista.style.display === 'none') return;
                    var items = lista.querySelectorAll('.ba-item');
                    if (e.key === 'ArrowDown') { e.preventDefault(); activo = Math.min(activo + 1, items.length - 1); }
                    else if (e.key === 'ArrowUp') { e.preventDefault(); activo = Math.max(activo - 1, 0); }
                    else if (e.key === 'Enter') { e.preventDefault(); if (activo >= 0 && visibles[activo]) elegir(visibles[activo]); return; }
                    else if (e.key === 'Escape') { cerrar(); return; }
                    else return;
                    items.forEach(function (el, i) { el.classList.toggle('is-activo', i === activo); });
                });
                lista.addEventListener('mousedown', function (e) {
                    var el = e.target.closest('.ba-item');
                    if (el) { e.preventDefault(); elegir(visibles[parseInt(el.dataset.i, 10)]); }
                });
                document.addEventListener('click', function (e) {
                    if (!e.target.closest('.ba-wrap')) cerrar();
                });
                document.getElementById('baQuitar').addEventListener('click', function () {
                    hidden.value = '';
                    chip.classList.remove('is-visible');
                    emitir(null);
                });
            })();
        </script>
    @endif
</div>
