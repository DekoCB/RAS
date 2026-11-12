@extends('layouts.mantis')

@section('title', 'Modalidad docente | R.A.S')
@section('page-title', 'Modalidad docente por grado')
@section('body-class', 'role-superadmin tema-academico')

@push('styles')
    <link rel="stylesheet" href="{{ url('css/superadmin-dashboard.css') }}?v={{ filemtime(public_path('css/superadmin-dashboard.css')) }}">
    <link rel="stylesheet" href="{{ url('css/modulos.css') }}?v={{ filemtime(public_path('css/modulos.css')) }}">
    <link rel="stylesheet" href="{{ url('css/tema-academico.css') }}?v={{ filemtime(public_path('css/tema-academico.css')) }}">
@endpush

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
    @include('partials.modulo')

    <div class="mod-card">
        <div class="mod-card__head">
            <div>
                <h5>Modalidad por grado</h5>
                <small>{{ $grados->whereNotNull('modalidad')->count() }} de {{ $grados->count() }} grados definidos.</small>
            </div>
        </div>
        <div class="mod-table-wrap">
            <table class="mod-table">
                <thead><tr><th>Nivel</th><th>Grado</th><th>Modalidad actual</th><th>Cambiar</th></tr></thead>
                <tbody>
                    @foreach ($grados as $g)
                        <tr>
                            <td>{{ $g->nivel }}</td>
                            <td>{{ $g->nombre }}</td>
                            <td>
                                @if ($g->modalidad === 'unidocencia')
                                    <span class="mod-chip">Unidocencia</span>
                                @elseif ($g->modalidad === 'polidocencia')
                                    <span class="mod-chip mod-chip--ok">Polidocencia</span>
                                @else
                                    <span class="mod-chip mod-chip--pend">Sin definir</span>
                                @endif
                            </td>
                            <td>
                                @can('academico.gestionar')
                                    <form method="POST" action="{{ route('academico.modalidades.guardar', $g) }}"
                                          class="mod-form d-flex gap-2" style="max-width:340px">
                                        @csrf
                                        @method('PUT')
                                        <select name="modalidad" class="form-select">
                                            <option value="unidocencia" @selected($g->modalidad === 'unidocencia')>Unidocencia</option>
                                            <option value="polidocencia" @selected($g->modalidad === 'polidocencia')>Polidocencia</option>
                                        </select>
                                        <button type="submit" class="mod-btn mod-btn-sm">Guardar</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
