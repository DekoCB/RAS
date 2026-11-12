<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Detalle nota</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-2">
                <p><strong>Estudiante:</strong> {{ $nota->estudiante->apellidos }}</p>
                <p><strong>Curso:</strong> {{ $nota->curso->nombre }}</p>
                <p><strong>Periodo:</strong> {{ $nota->periodo }}</p>
                <p><strong>Valor:</strong> {{ $nota->valor }}</p>
                <p><strong>Estado:</strong> {{ ucfirst($nota->estado) }}</p>
            </div>
        </div>
    </div>
</x-app-layout>
