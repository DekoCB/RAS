<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Paso 1: Agregar columna mes (si aún no existe por ejecución parcial anterior)
        if (!Schema::hasColumn('tarifas', 'mes')) {
            Schema::table('tarifas', function (Blueprint $table) {
                $table->tinyInteger('mes')->unsigned()->default(0)->after('nivel');
            });
        }

        // Paso 2: Crear nuevo índice único (con mes) ANTES de eliminar el anterior
        // Esto es necesario porque MySQL requiere mantener un índice para la FK en anio_escolar_id
        $indexExists = DB::getDriverName() === 'mysql'
            ? collect(DB::select("SHOW INDEX FROM tarifas WHERE Key_name = 'uq_tarifas_anio_concepto_nivel_mes'"))->isNotEmpty()
            : Schema::hasIndex('tarifas', 'uq_tarifas_anio_concepto_nivel_mes');

        if (!$indexExists) {
            Schema::table('tarifas', function (Blueprint $table) {
                $table->unique(
                    ['anio_escolar_id', 'concepto_pago_id', 'nivel', 'mes'],
                    'uq_tarifas_anio_concepto_nivel_mes'
                );
            });
        }

        // Paso 3: Ahora sí eliminar el índice antiguo (el nuevo ya cubre las FKs)
        $oldIndexExists = DB::getDriverName() === 'mysql'
            ? collect(DB::select("SHOW INDEX FROM tarifas WHERE Key_name = 'uq_tarifas_anio_concepto_nivel'"))->isNotEmpty()
            : Schema::hasIndex('tarifas', 'uq_tarifas_anio_concepto_nivel');

        if ($oldIndexExists) {
            Schema::table('tarifas', function (Blueprint $table) {
                $table->dropUnique('uq_tarifas_anio_concepto_nivel');
            });
        }
    }

    public function down(): void
    {
        // Restaurar índice antiguo primero
        $oldExists = DB::getDriverName() === 'mysql'
            ? collect(DB::select("SHOW INDEX FROM tarifas WHERE Key_name = 'uq_tarifas_anio_concepto_nivel'"))->isNotEmpty()
            : Schema::hasIndex('tarifas', 'uq_tarifas_anio_concepto_nivel');

        if (!$oldExists) {
            // Eliminar duplicados (anio+concepto+nivel) antes de crear el constraint
            if (DB::getDriverName() === 'mysql') {
                DB::statement('
                    DELETE t1 FROM tarifas t1
                    INNER JOIN tarifas t2
                    WHERE t1.id > t2.id
                      AND t1.anio_escolar_id = t2.anio_escolar_id
                      AND t1.concepto_pago_id = t2.concepto_pago_id
                      AND t1.nivel = t2.nivel
                ');
            }

            Schema::table('tarifas', function (Blueprint $table) {
                $table->unique(
                    ['anio_escolar_id', 'concepto_pago_id', 'nivel'],
                    'uq_tarifas_anio_concepto_nivel'
                );
            });
        }

        // Luego eliminar nuevo índice
        $newExists = DB::getDriverName() === 'mysql'
            ? collect(DB::select("SHOW INDEX FROM tarifas WHERE Key_name = 'uq_tarifas_anio_concepto_nivel_mes'"))->isNotEmpty()
            : Schema::hasIndex('tarifas', 'uq_tarifas_anio_concepto_nivel_mes');
        if ($newExists) {
            Schema::table('tarifas', function (Blueprint $table) {
                $table->dropUnique('uq_tarifas_anio_concepto_nivel_mes');
            });
        }

        // Finalmente eliminar columna
        if (Schema::hasColumn('tarifas', 'mes')) {
            Schema::table('tarifas', function (Blueprint $table) {
                $table->dropColumn('mes');
            });
        }
    }
};
