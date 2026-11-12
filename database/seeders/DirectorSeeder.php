<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DirectorSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'jeanpiaget_oxa@hotmail.com'],
            [
                'name'     => 'Olivia Yanina Sillo Jimenez',
                'dni'      => '20099909',
                'password' => Hash::make('20099909'),
            ]
        );

        $user->update([
            'name'     => 'Olivia Yanina Sillo Jimenez',
            'dni'      => '20099909',
            'password' => Hash::make('20099909'),
        ]);

        if (!$user->hasRole('Director')) {
            $user->assignRole('Director');
        }
    }
}
