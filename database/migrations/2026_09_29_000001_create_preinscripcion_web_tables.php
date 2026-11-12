<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pre-inscripcion desde el sitio web publico.
 *
 * La familia llena sus datos en la portada y el expediente aterriza en el
 * modulo de Admision listo para que secretaria lo continue: no se matricula
 * a nadie desde la web, solo queda preparado para matricular.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Que anio escolar tiene la campana de admision abierta al publico.
        Schema::table('anios_escolares', function (Blueprint $table) {
            $table->boolean('admision_abierta')->default(false)->after('activo');
            $table->date('admision_cierre')->nullable()->after('admision_abierta');
            $table->string('admision_mensaje')->nullable()->after('admision_cierre');
        });

        Schema::table('postulantes', function (Blueprint $table) {
            // presencial | web
            $table->string('origen', 20)->default('presencial')->after('estado');
            $table->string('token_seguimiento', 40)->nullable()->unique()->after('origen');
            $table->timestamp('preinscrito_at')->nullable()->after('token_seguimiento');
            $table->boolean('acepta_terminos')->default(false)->after('preinscrito_at');
        });
    }

    public function down(): void
    {
        Schema::table('postulantes', function (Blueprint $table) {
            $table->dropUnique(['token_seguimiento']);
            $table->dropColumn(['origen', 'token_seguimiento', 'preinscrito_at', 'acepta_terminos']);
        });

        Schema::table('anios_escolares', function (Blueprint $table) {
            $table->dropColumn(['admision_abierta', 'admision_cierre', 'admision_mensaje']);
        });
    }
};
