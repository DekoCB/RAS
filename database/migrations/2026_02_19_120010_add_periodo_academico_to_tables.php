<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('matriculas', 'periodo_academico_id')) {
            Schema::table('matriculas', function (Blueprint $table) {
                $table->foreignId('periodo_academico_id')
                    ->nullable()
                    ->after('anio_escolar_id')
                    ->constrained('periodos_academicos')
                    ->cascadeOnDelete();
            });
        }

        $this->addIndexIfMissing('matriculas', 'matriculas_anio_escolar_id_index', function (Blueprint $table) {
            $table->index('anio_escolar_id');
        });
        $this->addIndexIfMissing('matriculas', 'matriculas_estudiante_id_index', function (Blueprint $table) {
            $table->index('estudiante_id');
        });

        $this->dropIndexIfExists('matriculas', 'matriculas_anio_escolar_id_estudiante_id_unique', function (Blueprint $table) {
            $table->dropUnique(['anio_escolar_id', 'estudiante_id']);
        });

        if (!Schema::hasColumn('seccion_curso', 'periodo_academico_id')) {
            Schema::table('seccion_curso', function (Blueprint $table) {
                $table->foreignId('periodo_academico_id')
                    ->nullable()
                    ->after('anio_escolar_id')
                    ->constrained('periodos_academicos')
                    ->cascadeOnDelete();
            });
        }

        $this->addIndexIfMissing('seccion_curso', 'seccion_curso_anio_escolar_id_index', function (Blueprint $table) {
            $table->index('anio_escolar_id');
        });
        $this->addIndexIfMissing('seccion_curso', 'seccion_curso_seccion_id_index', function (Blueprint $table) {
            $table->index('seccion_id');
        });
        $this->addIndexIfMissing('seccion_curso', 'seccion_curso_curso_id_index', function (Blueprint $table) {
            $table->index('curso_id');
        });

        $this->dropIndexIfExists('seccion_curso', 'seccion_curso_anio_escolar_id_seccion_id_curso_id_unique', function (Blueprint $table) {
            $table->dropUnique(['anio_escolar_id', 'seccion_id', 'curso_id']);
        });

        if (!Schema::hasColumn('notas', 'periodo_academico_id')) {
            Schema::table('notas', function (Blueprint $table) {
                $table->foreignId('periodo_academico_id')
                    ->nullable()
                    ->after('anio_escolar_id')
                    ->constrained('periodos_academicos')
                    ->cascadeOnDelete();
            });
        }

        $this->addIndexIfMissing('notas', 'notas_anio_escolar_id_index', function (Blueprint $table) {
            $table->index('anio_escolar_id');
        });
        $this->addIndexIfMissing('notas', 'notas_seccion_id_index', function (Blueprint $table) {
            $table->index('seccion_id');
        });
        $this->addIndexIfMissing('notas', 'notas_curso_id_index', function (Blueprint $table) {
            $table->index('curso_id');
        });
        $this->addIndexIfMissing('notas', 'notas_estudiante_id_index', function (Blueprint $table) {
            $table->index('estudiante_id');
        });

        $this->dropIndexIfExists('notas', 'notas_unq_bimestre', function (Blueprint $table) {
            $table->dropUnique('notas_unq_bimestre');
        });

        if (!Schema::hasColumn('notas_cierres', 'periodo_academico_id')) {
            Schema::table('notas_cierres', function (Blueprint $table) {
                $table->foreignId('periodo_academico_id')
                    ->nullable()
                    ->after('anio_escolar_id')
                    ->constrained('periodos_academicos')
                    ->cascadeOnDelete();
            });
        }

        $this->addIndexIfMissing('notas_cierres', 'notas_cierres_anio_escolar_id_index', function (Blueprint $table) {
            $table->index('anio_escolar_id');
        });
        $this->addIndexIfMissing('notas_cierres', 'notas_cierres_curso_id_index', function (Blueprint $table) {
            $table->index('curso_id');
        });

        $this->dropIndexIfExists('notas_cierres', 'notas_cierres_unq', function (Blueprint $table) {
            $table->dropUnique('notas_cierres_unq');
        });

        if (!Schema::hasColumn('deudas', 'periodo_academico_id')) {
            Schema::table('deudas', function (Blueprint $table) {
                $table->foreignId('periodo_academico_id')
                    ->nullable()
                    ->after('anio_escolar_id')
                    ->constrained('periodos_academicos')
                    ->cascadeOnDelete();
            });
        }

        if (!Schema::hasColumn('pagos', 'periodo_academico_id')) {
            Schema::table('pagos', function (Blueprint $table) {
                $table->foreignId('periodo_academico_id')
                    ->nullable()
                    ->after('anio_escolar_id')
                    ->constrained('periodos_academicos')
                    ->cascadeOnDelete();
            });
        }

        $anios = DB::table('anios_escolares')->get();
        foreach ($anios as $anio) {
            $existe = DB::table('periodos_academicos')
                ->where('anio_escolar_id', $anio->id)
                ->exists();

            if ($existe) {
                continue;
            }

            $codigoBase = trim((string) $anio->nombre);
            $now = now();
            $anioCalendario = (int) preg_replace('/\D+/', '', (string) $anio->nombre);
            if ($anioCalendario < 2000) {
                $anioCalendario = (int) date('Y');
            }
            $finVacacional = \Carbon\Carbon::create($anioCalendario, 2, 1)->endOfMonth()->toDateString();

            DB::table('periodos_academicos')->insert([
                [
                    'anio_escolar_id' => $anio->id,
                    'codigo' => $codigoBase . '-0',
                    'nombre' => 'Vacacional',
                    'fecha_inicio' => "{$anioCalendario}-01-01",
                    'fecha_fin' => $finVacacional,
                    'es_vacacional' => true,
                    'orden' => 0,
                    'activo' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'anio_escolar_id' => $anio->id,
                    'codigo' => $codigoBase . '-1',
                    'nombre' => 'Periodo 1',
                    'fecha_inicio' => "{$anioCalendario}-03-01",
                    'fecha_fin' => "{$anioCalendario}-07-31",
                    'es_vacacional' => false,
                    'orden' => 1,
                    'activo' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'anio_escolar_id' => $anio->id,
                    'codigo' => $codigoBase . '-2',
                    'nombre' => 'Periodo 2',
                    'fecha_inicio' => "{$anioCalendario}-08-01",
                    'fecha_fin' => "{$anioCalendario}-12-31",
                    'es_vacacional' => false,
                    'orden' => 2,
                    'activo' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ]);
        }

        $periodos = DB::table('periodos_academicos')
            ->where('codigo', 'like', '%-1')
            ->get(['id', 'anio_escolar_id']);

        foreach ($periodos as $periodo) {
            DB::table('matriculas')
                ->where('anio_escolar_id', $periodo->anio_escolar_id)
                ->whereNull('periodo_academico_id')
                ->update(['periodo_academico_id' => $periodo->id]);

            DB::table('seccion_curso')
                ->where('anio_escolar_id', $periodo->anio_escolar_id)
                ->whereNull('periodo_academico_id')
                ->update(['periodo_academico_id' => $periodo->id]);

            DB::table('notas')
                ->where('anio_escolar_id', $periodo->anio_escolar_id)
                ->whereNull('periodo_academico_id')
                ->update(['periodo_academico_id' => $periodo->id]);

            DB::table('notas_cierres')
                ->where('anio_escolar_id', $periodo->anio_escolar_id)
                ->whereNull('periodo_academico_id')
                ->update(['periodo_academico_id' => $periodo->id]);

            DB::table('deudas')
                ->where('anio_escolar_id', $periodo->anio_escolar_id)
                ->whereNull('periodo_academico_id')
                ->update(['periodo_academico_id' => $periodo->id]);

            DB::table('pagos')
                ->where('anio_escolar_id', $periodo->anio_escolar_id)
                ->whereNull('periodo_academico_id')
                ->update(['periodo_academico_id' => $periodo->id]);
        }

        $this->addIndexIfMissing('matriculas', 'matriculas_anio_periodo_estudiante_unique', function (Blueprint $table) {
            $table->unique(
                ['anio_escolar_id', 'periodo_academico_id', 'estudiante_id'],
                'matriculas_anio_periodo_estudiante_unique'
            );
        });

        $this->addIndexIfMissing('seccion_curso', 'seccion_curso_anio_periodo_seccion_curso_unique', function (Blueprint $table) {
            $table->unique(
                ['anio_escolar_id', 'periodo_academico_id', 'seccion_id', 'curso_id'],
                'seccion_curso_anio_periodo_seccion_curso_unique'
            );
        });

        $this->addIndexIfMissing('notas', 'notas_unq_periodo_academico', function (Blueprint $table) {
            $table->unique(
                ['anio_escolar_id', 'periodo_academico_id', 'seccion_id', 'curso_id', 'estudiante_id', 'periodo'],
                'notas_unq_periodo_academico'
            );
        });

        $this->addIndexIfMissing('notas_cierres', 'notas_cierres_unq_periodo_academico', function (Blueprint $table) {
            $table->unique(
                ['anio_escolar_id', 'periodo_academico_id', 'periodo', 'curso_id'],
                'notas_cierres_unq_periodo_academico'
            );
        });
    }

    public function down(): void
    {
        try {
            Schema::table('notas_cierres', function (Blueprint $table) {
                $table->dropUnique('notas_cierres_unq_periodo_academico');
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('notas', function (Blueprint $table) {
                $table->dropUnique('notas_unq_periodo_academico');
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('seccion_curso', function (Blueprint $table) {
                $table->dropUnique('seccion_curso_anio_periodo_seccion_curso_unique');
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('matriculas', function (Blueprint $table) {
                $table->dropUnique('matriculas_anio_periodo_estudiante_unique');
            });
        } catch (\Exception $e) {}

        foreach (['pagos', 'deudas'] as $t) {
            try {
                Schema::table($t, function (Blueprint $table) {
                    $table->dropForeign(['periodo_academico_id']);
                    $table->dropColumn('periodo_academico_id');
                });
            } catch (\Exception $e) {}
        }

        try {
            Schema::table('notas_cierres', function (Blueprint $table) {
                $table->dropForeign(['periodo_academico_id']);
                $table->dropColumn('periodo_academico_id');
            });
        } catch (\Exception $e) {}
        try {
            Schema::table('notas_cierres', function (Blueprint $table) {
                $table->unique(['anio_escolar_id', 'periodo', 'curso_id'], 'notas_cierres_unq');
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('notas', function (Blueprint $table) {
                $table->dropForeign(['periodo_academico_id']);
                $table->dropColumn('periodo_academico_id');
            });
        } catch (\Exception $e) {}
        try {
            Schema::table('notas', function (Blueprint $table) {
                $table->unique(
                    ['anio_escolar_id', 'seccion_id', 'curso_id', 'estudiante_id', 'periodo'],
                    'notas_unq_bimestre'
                );
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('seccion_curso', function (Blueprint $table) {
                $table->dropForeign(['periodo_academico_id']);
                $table->dropColumn('periodo_academico_id');
            });
        } catch (\Exception $e) {}
        try {
            Schema::table('seccion_curso', function (Blueprint $table) {
                $table->unique(['anio_escolar_id', 'seccion_id', 'curso_id']);
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('matriculas', function (Blueprint $table) {
                $table->dropForeign(['periodo_academico_id']);
                $table->dropColumn('periodo_academico_id');
            });
        } catch (\Exception $e) {}
        try {
            Schema::table('matriculas', function (Blueprint $table) {
                $table->unique(['anio_escolar_id', 'estudiante_id']);
            });
        } catch (\Exception $e) {}
    }

    private function addIndexIfMissing(string $table, string $indexName, callable $callback): void
    {
        if ($this->indexExists($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $table) use ($callback) {
            $callback($table);
        });
    }

    private function dropIndexIfExists(string $table, string $indexName, callable $callback): void
    {
        if (!$this->indexExists($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $table) use ($callback) {
            $callback($table);
        });
    }

    private function indexExists(string $table, string $indexName): bool
    {
        if (Schema::hasIndex($table, $indexName)) {
            return true;
        }

        if (DB::getDriverName() === 'mysql') {
            $result = DB::selectOne(
                'SELECT COUNT(1) AS count FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ?',
                [$table, $indexName]
            );

            return ((int) ($result->count ?? 0)) > 0;
        }

        return false;
    }
};
