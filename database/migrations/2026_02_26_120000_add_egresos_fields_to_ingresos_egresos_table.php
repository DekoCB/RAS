<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ingresos_egresos', function (Blueprint $table) {
            $table->string('subtipo', 80)->nullable()->after('tipo');
            $table->string('categoria', 80)->nullable()->after('subtipo');
            $table->string('metodo_pago', 40)->nullable()->after('categoria');
            $table->string('tipo_comprobante', 40)->nullable()->after('metodo_pago');
            $table->string('nro_comprobante', 100)->nullable()->after('tipo_comprobante');
            $table->string('docente_curso')->nullable()->after('nro_comprobante');
            $table->text('observaciones')->nullable()->after('docente_curso');
            $table->string('estado', 20)->default('registrado')->after('observaciones');
            $table->string('anulado_motivo')->nullable()->after('estado');
            $table->foreignId('anulado_por')->nullable()->after('anulado_motivo')->constrained('users')->nullOnDelete();
            $table->timestamp('anulado_en')->nullable()->after('anulado_por');
            $table->foreignId('anio_escolar_id')->nullable()->after('usuario_id')->constrained('anios_escolares')->nullOnDelete();

            $table->index(['tipo', 'fecha'], 'ingresos_egresos_tipo_fecha_idx');
            $table->index(['tipo', 'estado'], 'ingresos_egresos_tipo_estado_idx');
            $table->index(['tipo', 'categoria'], 'ingresos_egresos_tipo_categoria_idx');
        });
    }

    public function down(): void
    {
        Schema::table('ingresos_egresos', function (Blueprint $table) {
            $table->dropIndex('ingresos_egresos_tipo_fecha_idx');
            $table->dropIndex('ingresos_egresos_tipo_estado_idx');
            $table->dropIndex('ingresos_egresos_tipo_categoria_idx');

            $table->dropConstrainedForeignId('anulado_por');
            $table->dropConstrainedForeignId('anio_escolar_id');

            $table->dropColumn([
                'subtipo',
                'categoria',
                'metodo_pago',
                'tipo_comprobante',
                'nro_comprobante',
                'docente_curso',
                'observaciones',
                'estado',
                'anulado_motivo',
                'anulado_en',
            ]);
        });
    }
};
