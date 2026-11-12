<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('horarios_escolares', function (Blueprint $table) {
            $table->id();
            $table->string('nivel')->unique();
            $table->time('hora_inicio');
            $table->unsignedInteger('duracion_clase_min');
            $table->unsignedInteger('recreo_min')->default(0);
            $table->unsignedInteger('clases_por_dia')->default(1);
            $table->unsignedInteger('recreo_despues_de')->nullable();
            $table->timestamps();
        });

        $now = now();
        DB::table('horarios_escolares')->insert([
            [
                'nivel' => 'Inicial',
                'hora_inicio' => '08:00:00',
                'duracion_clase_min' => 90,
                'recreo_min' => 30,
                'clases_por_dia' => 3,
                'recreo_despues_de' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'nivel' => 'Primaria',
                'hora_inicio' => '08:00:00',
                'duracion_clase_min' => 90,
                'recreo_min' => 30,
                'clases_por_dia' => 3,
                'recreo_despues_de' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'nivel' => 'Secundaria',
                'hora_inicio' => '08:00:00',
                'duracion_clase_min' => 90,
                'recreo_min' => 30,
                'clases_por_dia' => 4,
                'recreo_despues_de' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('horarios_escolares');
    }
};
