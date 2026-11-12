<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * El apoderado en Admision pide los mismos datos que en Matricula, para que
 * al matricular a un admitido no falte nada por completar.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('postulantes', function (Blueprint $table) {
            $table->date('apoderado_fecha_nacimiento')->nullable()->after('apoderado_parentesco');
            $table->string('apoderado_sexo', 1)->nullable()->after('apoderado_fecha_nacimiento');
            $table->string('apoderado_estado_civil', 30)->nullable()->after('apoderado_sexo');
            $table->string('apoderado_ciudad', 120)->nullable()->after('apoderado_estado_civil');
            $table->string('apoderado_direccion')->nullable()->after('apoderado_ciudad');
        });
    }

    public function down(): void
    {
        Schema::table('postulantes', function (Blueprint $table) {
            $table->dropColumn([
                'apoderado_fecha_nacimiento', 'apoderado_sexo', 'apoderado_estado_civil',
                'apoderado_ciudad', 'apoderado_direccion',
            ]);
        });
    }
};
