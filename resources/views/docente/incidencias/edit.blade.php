<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Actualizar incidencia</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('docente.incidencias.update', $incidencia) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="grid gap-4">
                        <div>
                            <label class="text-sm text-gray-600">Estado</label>
                            <select name="estado" class="mt-1 w-full rounded border-gray-300">
                                @foreach (['abierta','en_proceso','cerrada'] as $estado)
                                    <option value="{{ $estado }}" @selected($incidencia->estado === $estado)>{{ ucfirst($estado) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-sm text-gray-600">Seguimiento</label>
                            <textarea name="seguimiento" class="mt-1 w-full rounded border-gray-300">{{ $incidencia->seguimiento }}</textarea>
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
