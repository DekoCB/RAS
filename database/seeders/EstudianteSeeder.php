<?php

namespace Database\Seeders;

use App\Models\Estudiante;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EstudianteSeeder extends Seeder
{
    private const ESTUDIANTE_DNI = '00000001';

    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'estudiante@ras.test'],
            [
                'name' => 'Estudiante Demo',
                'dni' => self::ESTUDIANTE_DNI,
                'password' => Hash::make(self::ESTUDIANTE_DNI),
            ]
        );

        $user->update([
            'dni' => self::ESTUDIANTE_DNI,
            'password' => Hash::make(self::ESTUDIANTE_DNI),
        ]);

        if (!$user->hasRole('Estudiante')) {
            $user->assignRole('Estudiante');
        }

        Estudiante::firstOrCreate(
            ['user_id' => $user->id],
            [
                'codigo' => 'EST-001',
                'nombres' => 'Estudiante',
                'apellidos' => 'Demo',
                'dni' => self::ESTUDIANTE_DNI,
                'estado' => 'activo',
            ]
        );
    }
}
