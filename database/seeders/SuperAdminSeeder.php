<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    private const SUPERADMINS = [
        [
            'name'  => 'Super Admin',
            'email' => 'admin@sistemaderaul.test',
            'dni'   => '23705407',
        ],
        [
            'name'  => 'Sayuri Gozar',
            'email' => 'gozarsayuri@gmail.com',
            'dni'   => '73329112',
        ],
        [
            'name'     => 'Administrador',
            'email'    => 'admin@reyschool.edu.pe',
            'dni'      => 'admin',
            'password' => 'admin123',
        ],
    ];

    public function run(): void
    {
        foreach (self::SUPERADMINS as $data) {
            // Sin password explicito se mantiene la convencion: el DNI es la clave
            $clave = $data['password'] ?? $data['dni'];

            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'     => $data['name'],
                    'dni'      => $data['dni'],
                    'password' => Hash::make($clave),
                ]
            );

            $user->update([
                'dni'      => $data['dni'],
                'password' => Hash::make($clave),
            ]);

            if (!$user->hasRole('SuperAdmin')) {
                $user->assignRole('SuperAdmin');
            }
        }
    }
}
