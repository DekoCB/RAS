<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            CatalogoSeeder::class,
            ProductosSeeder::class,
            UniformeProductosSeeder::class,
            RolesAndPermissionsSeeder::class,
            RolesRasSeeder::class,
            InstitucionSeeder::class,
            TarifarioRasSeeder::class,
            SuperAdminSeeder::class,
            DirectorSeeder::class,
            SecretariaSeeder::class,
            DocenteSeeder::class,
            InicialDosAniosSeeder::class,
            InicialTresAniosSeeder::class,
            InicialCuatroAniosSeeder::class,
            InicialCincoAniosSeeder::class,
            PrimariaSeeder::class,
            SecundariaSeeder::class,
            TarifasSeeder::class,
            ReglamentoInternoSeeder::class,
            DeudaSeeder::class,
        ]);
    }
}
