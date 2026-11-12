<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Editar nota</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('docente.notas.update', $nota) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    @if ($errors->any())
                        <div class="rounded bg-red-100 text-red-700 px-4 py-2 text-sm">
                            Revisa los campos marcados.
                        </div>
                    @endif

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="text-sm text-gray-600">Ano escolar</label>
                            <select name="anio_escolar_id" class="mt-1 w-full rounded border-gray-300" required>
                                @foreach ($anios as $anio)
                                    <option value="{{ $anio->id }}" @selected($nota->anio_escolar_id == $anio->id)>{{ $anio->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-sm text-gray-600">Curso</label>
                            <select name="curso_id" class="mt-1 w-full rounded border-gray-300" required>
                                @foreach ($cursos as $curso)
                                    <option value="{{ $curso->id }}" @selected($nota->curso_id == $curso->id)>{{ $curso->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-sm text-gray-600">Estudiante</label>
                            <select name="estudiante_id" class="mt-1 w-full rounded border-gray-300" required>
                                @foreach ($estudiantes as $estudiante)
                                    <option value="{{ $estudiante->id }}" @selected($nota->estudiante_id == $estudiante->id)>{{ $estudiante->apellidos }}, {{ $estudiante->nombres }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-sm text-gray-600">Periodo</label>
                            <input name="periodo" value="{{ $nota->periodo }}" class="mt-1 w-full rounded border-gray-300" required />
                        </div>
                        <div>
                            <label class="text-sm text-gray-600">Valor</label>
                            <input name="valor" type="number" step="0.01" value="{{ $nota->valor }}" class="mt-1 w-full rounded border-gray-300" />
                        </div>
                        <div>
                            <label class="text-sm text-gray-600">Estado</label>
                            <select name="estado" class="mt-1 w-full rounded border-gray-300">
                                @foreach (['abierta','cerrada'] as $estado)
                                    <option value="{{ $estado }}" @selected($nota->estado === $estado)>{{ ucfirst($estado) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button class="px-4 py-2 rounded bg-emerald-600 text-white">Actualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
