@php
    $codigoEstudiante = trim((string) ($estudiante->codigo ?? ''));
    if ($codigoEstudiante === '' || $codigoEstudiante === '-') {
        $codigoEstudiante = $estudiante->carnet_codigo
            ? trim((string) $estudiante->carnet_codigo)
            : 'ALU-' . str_pad((string) $estudiante->id, 5, '0', STR_PAD_LEFT);
    }
    $fmtFecha = fn($f) => $f ? \Carbon\Carbon::parse($f)->format('d/m/Y') : '-';
@endphp

<style>
.di-wrap   { padding: .25rem .25rem .5rem; }
.di-hero   { display:flex; align-items:center; gap:1rem; padding:.75rem 1rem 1rem; border-bottom:1.5px solid #d4ecfd; margin-bottom:.75rem; }
.di-avatar { width:52px;height:52px;border-radius:50%;background:linear-gradient(135deg,#1c297e,#213196);color:#fff;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
.di-name   { font-size:1.05rem;font-weight:800;color:#1c1917;line-height:1.2; }
.di-sub    { font-size:.78rem;color:#78716c;display:flex;align-items:center;gap:.3rem;margin-top:.15rem; }
.di-sub svg{ width:13px;height:13px; }
.di-sec    { margin-bottom:.6rem; }
.di-sec-title { font-size:.7rem;font-weight:700;letter-spacing:.07em;color:#1c297e;text-transform:uppercase;display:flex;align-items:center;gap:.4rem;margin-bottom:.45rem;padding:.25rem .75rem;background:#f5faff;border-radius:.4rem; }
.di-sec-title svg{ width:13px;height:13px; }
.di-grid   { display:grid;grid-template-columns:repeat(3,1fr);gap:.3rem .75rem;padding:0 .25rem; }
.di-item   { display:flex;flex-direction:column;gap:.05rem; }
.di-item span { font-size:.67rem;font-weight:600;letter-spacing:.04em;color:#a8a29e;text-transform:uppercase; }
.di-item strong { font-size:.82rem;color:#1c1917;font-weight:600;word-break:break-word; }
.di-apo-wrap { border:1.5px solid #d4ecfd;border-radius:.65rem;overflow:hidden;margin-bottom:.6rem; }
.di-apo-head { display:flex;align-items:center;gap:.5rem;padding:.45rem .75rem;background:linear-gradient(135deg,#f5faff,#eaf6fe);border-bottom:1.5px solid #d4ecfd; }
.di-apo-head.secundario { background:linear-gradient(135deg,#f0f4ff,#e8edff);border-bottom-color:#c7d2fe; }
.di-apo-title { font-size:.75rem;font-weight:700;color:#1c1917;text-transform:uppercase;letter-spacing:.04em; }
.di-badge  { font-size:.67rem;padding:.15em .55em;border-radius:2rem;font-weight:700; }
/* Hermano badges */
.hermano-badge { display:inline-flex;align-items:center;justify-content:center;font-size:.64rem;font-weight:800;padding:.13em .48em;border-radius:2rem;letter-spacing:.02em;line-height:1; }
.hermano-badge.hm  { background:#c3f0fe;color:#162064;border:1px solid #b8c1fd; }
.hermano-badge.hp  { background:#dbeafe;color:#1b2779;border:1px solid #bfdbfe; }
.hermano-badge.hm0 { background:#dcfce7;color:#166534;border:1px solid #bbf7d0; }
/* Familia section */
.di-fam-wrap { margin-bottom:.6rem; }
.di-herm-row { display:flex;align-items:center;gap:.6rem;padding:.45rem .75rem;border-bottom:1px solid #eaf6fe; }
.di-herm-row:last-child { border-bottom:none; }
.di-herm-avatar { width:28px;height:28px;border-radius:50%;background:linear-gradient(135deg,#1c297e,#213196);color:#1c297e;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
.di-herm-info { flex:1;min-width:0; }
.di-herm-name { font-size:.82rem;font-weight:700;color:#1c1917;white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }
.di-herm-sub  { font-size:.7rem;color:#78716c;margin-top:.05rem; }
</style>

<div class="di-wrap">

    {{-- ── Hero ── --}}
    <div class="di-hero">
        <div class="di-avatar"><i data-feather="user" style="width:22px;height:22px;"></i></div>
        <div>
            <div class="di-name">
                {{ $estudiante->apellidos }}, {{ $estudiante->nombres }}
                @if ($miTag)
                    @php $clsMap = ['H-'=>'hm','H+'=>'hp','H'=>'hm0']; @endphp
                    <span class="hermano-badge {{ $clsMap[$miTag] ?? 'hm0' }} ms-1">{{ $miTag }}</span>
                @endif
            </div>
            <div class="di-sub"><i data-feather="credit-card"></i> DNI: <strong>{{ $estudiante->dni ?? '-' }}</strong>
                &nbsp;·&nbsp;<i data-feather="hash"></i> Código: <strong>{{ $codigoEstudiante }}</strong>
            </div>
        </div>
    </div>

    {{-- ── Fila: Datos personales + Matrícula lado a lado ── --}}
    <div class="row g-3 px-1 mb-1">

        {{-- Datos personales --}}
        <div class="col-md-6">
            <div class="di-sec">
                <div class="di-sec-title"><i data-feather="info"></i> Datos personales</div>
                <div class="di-grid">
                    <div class="di-item"><span>Fecha nacimiento</span><strong>{{ $fmtFecha($estudiante->fecha_nacimiento) }}</strong></div>
                    <div class="di-item"><span>Sexo</span><strong>{{ $estudiante->sexo ?? '-' }}</strong></div>
                    <div class="di-item"><span>Estado</span><strong>{{ ucfirst($estudiante->estado ?? 'activo') }}</strong></div>
                    <div class="di-item"><span>Ciudad</span><strong>{{ $estudiante->ciudad ?? '-' }}</strong></div>
                    <div class="di-item" style="grid-column:span 2;"><span>Dirección</span><strong>{{ $estudiante->direccion ?? '-' }}</strong></div>
                </div>
            </div>
        </div>

        {{-- Última matrícula --}}
        <div class="col-md-6">
            <div class="di-sec">
                <div class="di-sec-title"><i data-feather="book-open"></i> Última matrícula</div>
                <div class="di-grid">
                    <div class="di-item"><span>Año escolar</span><strong>{{ $matricula?->anioEscolar?->nombre ?? '-' }}</strong></div>
                    <div class="di-item"><span>Grado</span><strong>{{ $matricula?->grado?->nombre ?? '-' }}</strong></div>
                    <div class="di-item"><span>Sección</span><strong>{{ $matricula?->seccion?->nombre ?? '-' }}</strong></div>
                    <div class="di-item"><span>Estado</span><strong>{{ $matricula?->estado ?? '-' }}</strong></div>
                    <div class="di-item"><span>Fecha matrícula</span><strong>{{ $fmtFecha($matricula?->fecha_matricula) }}</strong></div>
                </div>
            </div>
        </div>

    </div>

    {{-- ── Apoderados ── --}}
    @forelse ($apoderados as $apo)
        @php
            $esPrincipal = (bool) $apo->pivot->principal;
            $relacion    = strtoupper($apo->pivot->relacion ?? '-');
        @endphp
        <div class="di-apo-wrap">
            <div class="di-apo-head {{ $esPrincipal ? '' : 'secundario' }}">
                <i data-feather="{{ $esPrincipal ? 'user-check' : 'user' }}" style="width:14px;height:14px;color:{{ $esPrincipal ? '#1c297e' : '#2d3b94' }};flex-shrink:0;"></i>
                <span class="di-apo-title">{{ $esPrincipal ? 'Apoderado principal' : 'Segundo apoderado' }}</span>
                <span class="di-badge ms-1" style="background:{{ $esPrincipal ? '#dcfce7' : '#e0e7ff' }};color:{{ $esPrincipal ? '#166534' : '#222d73' }};">
                    {{ $relacion }}
                </span>
            </div>
            <div class="di-grid p-2" style="grid-template-columns:repeat(3,1fr);">
                <div class="di-item" style="grid-column:span 2;"><span>Apellidos y nombres</span><strong>{{ trim(($apo->apellidos ?? '') . ' ' . ($apo->nombres ?? '')) ?: '-' }}</strong></div>
                <div class="di-item"><span>DNI</span><strong>{{ $apo->dni ?? '-' }}</strong></div>
                <div class="di-item"><span>Fecha nacimiento</span><strong>{{ $fmtFecha($apo->fecha_nacimiento) }}</strong></div>
                <div class="di-item"><span>Sexo</span><strong>{{ $apo->sexo ?? '-' }}</strong></div>
                <div class="di-item"><span>Estado civil</span><strong>{{ $apo->estado_civil ?? '-' }}</strong></div>
                <div class="di-item"><span>Ciudad</span><strong>{{ $apo->ciudad ?? '-' }}</strong></div>
                <div class="di-item"><span>Teléfono</span><strong>{{ $apo->telefono ?? '-' }}</strong></div>
                <div class="di-item"><span>Email</span><strong>{{ $apo->email ?? '-' }}</strong></div>
                <div class="di-item" style="grid-column:span 2;"><span>Dirección</span><strong>{{ $apo->direccion ?? '-' }}</strong></div>
            </div>
        </div>
    @empty
        <div class="di-sec px-1">
            <div class="di-sec-title"><i data-feather="user-check"></i> Apoderado</div>
            <p class="text-muted small mb-0 px-1">No hay apoderados registrados para este alumno.</p>
        </div>
    @endforelse

    {{-- ── Familia: Hermanos matriculados ── --}}
    @if ($hermanos->isNotEmpty())
        <div class="di-fam-wrap">
            <div class="di-sec-title"><i data-feather="users"></i> Familia — Hermanos matriculados</div>
            <div class="di-apo-wrap">
                @foreach ($hermanos as $h)
                    @php
                        $hM  = $h->hermano_matricula;
                        $tag = $h->hermano_tag;
                        $cls = ['H-'=>'hm','H+'=>'hp','H'=>'hm0'][$tag] ?? 'hm0';
                    @endphp
                    <div class="di-herm-row">
                        <div class="di-herm-avatar"><i data-feather="user" style="width:14px;height:14px;"></i></div>
                        <div class="di-herm-info">
                            <div class="di-herm-name">{{ $h->apellidos }}, {{ $h->nombres }}</div>
                            <div class="di-herm-sub">
                                DNI: {{ $h->dni ?? '-' }}
                                @if ($hM)
                                    &nbsp;·&nbsp;{{ $hM->grado?->nombre ?? '-' }}
                                    &nbsp;·&nbsp;{{ $hM->anioEscolar?->nombre ?? '-' }}
                                @else
                                    &nbsp;·&nbsp;<em>Sin matrícula activa</em>
                                @endif
                            </div>
                        </div>
                        @if ($tag)
                            <span class="hermano-badge {{ $cls }}">{{ $tag }}</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</div>
