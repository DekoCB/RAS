<?php

namespace Database\Seeders;

use App\Models\Docente;
use App\Models\User;
use Illuminate\Database\Seeder;

class DocenteSyncSeeder extends Seeder
{
    public function run(): void
    {
        $usuariosDocentes = User::role('Docente')->get();

        foreach ($usuariosDocentes as $user) {
            if (Docente::where('user_id', $user->id)->exists()) {
                continue;
            }

            $nombreCompleto = trim((string) $user->name);
            $partes = preg_split('/\s+/', $nombreCompleto) ?: [];
            $nombres = array_shift($partes) ?: 'Docente';
            $apellidos = $partes ? implode(' ', $partes) : 'Sin Apellido';

            $docente = Docente::create([
                'user_id' => $user->id,
                'nombres' => $nombres,
                'apellidos' => $apellidos,
                'estado' => 'activo',
            ]);

            $docente->codigo = 'DOC-' . str_pad((string) $docente->id, 5, '0', STR_PAD_LEFT);
            $docente->save();
        }
    }
}
