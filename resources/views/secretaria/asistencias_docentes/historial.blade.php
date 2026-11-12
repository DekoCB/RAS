@extends('layouts.mantis')

@section('title', 'Historial Asistencia Docentes | Secretaría')
@section('page-title', 'Historial Asistencia Docentes')
@section('body-class', 'role-secretaria')

@section('sidebar')
    @include('secretaria.partials.sidebar')
@endsection

@section('content')
    <div class="card mt-2">
        <div class="card-body">
            <form class="row g-2 align-items-end" method="GET">
                <div class="col-md-3">
                    <label class="form-label">Fecha</label>
                    <input type="date" name="fecha" value="{{ $fecha }}" class="form-control">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Tipo</label>
                    <select name="tipo" class="form-select">
                        <option value="">-- Todos --</option>
                        <option value="ENTRADA" @selected($tipo==='ENTRADA')>ENTRADA</option>
                        <option value="SALIDA" @selected($tipo==='SALIDA')>SALIDA</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Buscar (nombre/dni/código)</label>
                    <input type="text" name="q" value="{{ $q }}" class="form-control" placeholder="Ej: Perez / 12345678">
                </div>

                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-primary w-100" type="submit">
                        <i data-feather="search"></i> Filtrar
                    </button>
                    <a class="btn btn-outline-secondary w-100" href="{{ route('secretaria.asistencias-docentes.historial') }}">
                        Limpiar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-body table-responsive">
            <table class="table table-sm align-middle">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Docente</th>
                        <th>DNI</th>
                        <th>Código</th>
                        <th>Registrado</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($asistencias as $a)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($a->fecha)->format('d/m/Y') }}</td>
                        <td><span class="badge bg-secondary">{{ $a->tipo }}</span></td>
                        <td>{{ $a->docente?->apellidos }} {{ $a->docente?->nombres }}</td>
                        <td>{{ $a->docente?->dni }}</td>
                        <td>{{ $a->docente?->codigo }}</td>
                        <td class="text-muted small">{{ $a->created_at?->timezone('America/Lima')->format('H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">No hay registros.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>

            {{ $asistencias->links() }}
        </div>
    </div>
@endsection
