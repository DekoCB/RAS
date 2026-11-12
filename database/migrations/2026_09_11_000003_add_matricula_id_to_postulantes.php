<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Enlaza el expediente de admision con la matricula que se genero a partir
 * de el, para que un admitido no se matricule dos veces y desde Admision se
 * pueda saltar a su matricula. Si la matricula se borra (borrador abandonado)
 * el enlace se limpia y el postulante vuelve a poder matricularse.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('postulantes', function (Blueprint $table) {
            $table->foreignId('matricula_id')->nullable()->after('resultado_canal')
                ->constrained('matriculas')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('postulantes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('matricula_id');
        });
    }
};
