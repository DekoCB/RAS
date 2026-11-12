<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Detalle incidencia</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-2">
                <p><strong>Fecha:</strong> {{ $incidencia->fecha_reporte }}</p>
                <p><strong>Estudiante:</strong> {{ $incidencia->estudiante->apellidos }}</p>
                <p><strong>Estado:</strong> {{ ucfirst($incidencia->estado) }}</p>
                <p><strong>Descripcion:</strong> {{ $incidencia->descripcion }}</p>
            </div>
        </div>
    </div>
</x-app-layout>
