<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SecretariaSeeder extends Seeder
{
    public function run(): void
    {
        $secretarias = [
            [
                'email'    => 'mirellacantalicioperez@gmail.com',
                'name'     => 'Fresia Mirella Cantalicio Perez',
                'dni'      => '71202328',
            ],
            [
                'email'    => 'katissazehnder15@gmail.com',
                'name'     => 'Yehidy Katissa Rodriguez Zehnder',
                'dni'      => '77498515',
            ],
        ];

        foreach ($secretarias as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'     => $data['name'],
                    'dni'      => $data['dni'],
                    'password' => Hash::make($data['dni']),
                ]
            );

            $user->update([
                'name'     => $data['name'],
                'dni'      => $data['dni'],
                'password' => Hash::make($data['dni']),
            ]);

            if (!$user->hasRole('Secretaria')) {
                $user->assignRole('Secretaria');
            }
        }
    }
}
