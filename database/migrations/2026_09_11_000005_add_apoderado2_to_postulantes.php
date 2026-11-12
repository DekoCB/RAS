<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Segundo apoderado opcional en Admision, con los mismos campos que en Matricula. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('postulantes', function (Blueprint $table) {
            $table->string('apoderado2_parentesco', 20)->nullable()->after('apoderado_email');
            $table->string('apoderado2_dni', 20)->nullable()->after('apoderado2_parentesco');
            $table->string('apoderado2_nombres')->nullable()->after('apoderado2_dni');
            $table->string('apoderado2_apellidos')->nullable()->after('apoderado2_nombres');
            $table->date('apoderado2_fecha_nacimiento')->nullable()->after('apoderado2_apellidos');
            $table->string('apoderado2_sexo', 1)->nullable()->after('apoderado2_fecha_nacimiento');
            $table->string('apoderado2_estado_civil', 30)->nullable()->after('apoderado2_sexo');
            $table->string('apoderado2_ciudad', 120)->nullable()->after('apoderado2_estado_civil');
            $table->string('apoderado2_direccion')->nullable()->after('apoderado2_ciudad');
            $table->string('apoderado2_telefono', 40)->nullable()->after('apoderado2_direccion');
            $table->string('apoderado2_email', 150)->nullable()->after('apoderado2_telefono');
        });
    }

    public function down(): void
    {
        Schema::table('postulantes', function (Blueprint $table) {
            $table->dropColumn([
                'apoderado2_parentesco', 'apoderado2_dni', 'apoderado2_nombres', 'apoderado2_apellidos',
                'apoderado2_fecha_nacimiento', 'apoderado2_sexo', 'apoderado2_estado_civil',
                'apoderado2_ciudad', 'apoderado2_direccion', 'apoderado2_telefono', 'apoderado2_email',
            ]);
        });
    }
};
