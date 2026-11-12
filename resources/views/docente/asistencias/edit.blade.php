<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Editar asistencia</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('docente.asistencias.update', $asistencia) }}" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="text-sm text-gray-600">Estado</label>
                        <select name="estado" class="mt-1 w-full rounded border-gray-300">
                            @foreach (['presente','ausente','tarde','justificado'] as $estado)
                                <option value="{{ $estado }}" @selected($asistencia->estado === $estado)>{{ ucfirst($estado) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex justify-end">
                        <button class="px-4 py-2 rounded bg-emerald-600 text-white">Actualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
