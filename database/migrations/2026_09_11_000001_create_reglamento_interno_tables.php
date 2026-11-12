<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Datos del Reglamento Interno que el sistema no podia representar:
 *
 *  - Art. 153: tolerancia de ingreso y tiempo maximo de espera a la salida.
 *  - Art. 54/61 y anexos: oportunidades de pago (matricula, cuota de ingreso)
 *    y numero de cuotas de pension, por anio escolar.
 *  - Art. 61: cronograma oficial de vencimiento de cada pension.
 *  - Art. 69: parametros de la formula del interes moratorio.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('horarios_institucionales', function (Blueprint $table) {
            $table->unsignedSmallInteger('tolerancia_minutos')->nullable()->after('hora_ingreso');
            $table->unsignedSmallInteger('espera_maxima_minutos')->nullable()->after('hora_salida');
        });

        Schema::create('politicas_pago', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anio_escolar_id')->unique()->constrained('anios_escolares')->cascadeOnDelete();

            // Pension: numero de cuotas y meses que cubre (Art. 61)
            $table->unsignedTinyInteger('pension_cuotas')->default(10);
            $table->unsignedTinyInteger('pension_mes_inicio')->default(3);
            $table->unsignedTinyInteger('pension_mes_fin')->default(12);

            // Oportunidades de pago
            $table->date('matricula_desde')->nullable();
            $table->date('matricula_hasta')->nullable();
            $table->date('cuota_ingreso_desde')->nullable();
            $table->date('cuota_ingreso_hasta')->nullable();

            // Art. 69: interes diario = pension * tasa_anual * porcentaje / divisor
            $table->decimal('mora_porcentaje', 5, 2)->default(15.00);
            $table->decimal('mora_tasa_anual', 6, 2)->nullable(); // tasa maxima BCR (%), se actualiza a mano
            $table->unsignedSmallInteger('mora_divisor')->default(360);

            $table->text('observaciones')->nullable();
            $table->timestamps();
        });

        Schema::create('cronograma_pensiones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anio_escolar_id')->constrained('anios_escolares')->cascadeOnDelete();
            $table->unsignedTinyInteger('mes');
            $table->date('fecha_vencimiento');
            $table->timestamps();

            $table->unique(['anio_escolar_id', 'mes']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cronograma_pensiones');
        Schema::dropIfExists('politicas_pago');

        Schema::table('horarios_institucionales', function (Blueprint $table) {
            $table->dropColumn(['tolerancia_minutos', 'espera_maxima_minutos']);
        });
    }
};
