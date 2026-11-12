<?php

namespace Database\Seeders;

use App\Models\AnioEscolar;
use App\Models\ConceptoPago;
use App\Models\Deuda;
use App\Models\Matricula;
use App\Models\Pago;
use App\Models\PeriodoAcademico;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class InicialCuatroAniosPagosSeeder extends Seeder
{
    private const PAYMENT_MARKER = 'seed:inicial_cuatro_anios_pagos:';
    private const PENSION_REGULAR = 240.00;
    private const PENSION_ESPECIAL = 260.00;
    private const LIBROS_INICIAL = 230.00;

    private const MESES = [
        3 => 'MARZO',
        4 => 'ABRIL',
        5 => 'MAYO',
        6 => 'JUNIO',
        7 => 'JULIO',
        8 => 'AGOSTO',
        9 => 'SEPTIEMBRE',
        10 => 'OCTUBRE',
        11 => 'NOVIEMBRE',
        12 => 'DICIEMBRE',
    ];

    private const ALUMNOS = [
        '92670094' => [
            'obs_matricula' => 'H hermano del medio',
            'matricula_total' => 340.00,
            'matricula_pagado' => 340.00,
            'libros_pagado' => 230.00,
            'pagos_pension' => [3 => 80.00],
        ],
        '92616344' => [
            'obs_matricula' => 'H- hermano menor',
            'matricula_total' => 360.00,
            'matricula_pagado' => 360.00,
            'pagos_pension' => [3 => 240.00],
        ],
        '92578173' => [
            'matricula_total' => 260.00,
            'matricula_pagado' => 260.00,
        ],
        '92444723' => [
            'obs_matricula' => 'H hermano del medio',
            'matricula_total' => 340.00,
            'matricula_pagado' => 340.00,
            'libros_pagado' => 230.00,
            'pagos_pension' => [3 => 240.00],
        ],
        '92544750' => [
            'matricula_total' => 260.00,
        ],
        '92425657' => [
            'matricula_total' => 260.00,
            'matricula_pagado' => 260.00,
            'pagos_pension' => [3 => 240.00],
        ],
        '92683498' => [
            'obs_matricula' => 'H- hermano menor',
            'matricula_total' => 360.00,
            'matricula_pagado' => 360.00,
        ],
        '92796606' => [
            'matricula_total' => 140.00,
            'matricula_pagado' => 140.00,
            'libros_pagado' => 230.00,
            'pagos_pension' => [3 => 240.00],
        ],
        '92335569' => [
            'obs_matricula' => 'H- hermano menor',
            'matricula_total' => 360.00,
            'matricula_pagado' => 360.00,
            'pagos_pension' => [3 => 240.00],
        ],
        '92566993' => [
            'matricula_total' => 260.00,
            'matricula_pagado' => 260.00,
            'libros_pagado' => 230.00,
            'pagos_pension' => [3 => 240.00],
        ],
        '92480305' => [
            'obs_matricula' => 'H- hermano menor',
            'matricula_total' => 360.00,
            'matricula_pagado' => 360.00,
        ],
        '92503524' => [
            'obs_matricula' => 'H- hermano menor',
            'matricula_total' => 360.00,
            'matricula_pagado' => 360.00,
            'libros_pagado' => 230.00,
        ],
        '92535554' => [
            'matricula_total' => 260.00,
            'matricula_pagado' => 260.00,
            'libros_pagado' => 230.00,
            'pagos_pension' => [3 => 240.00],
        ],
        '92454723' => [
            'matricula_total' => 260.00,
            'matricula_pagado' => 260.00,
            'libros_pagado' => 230.00,
            'pagos_pension' => [3 => 240.00],
        ],
        '92546756' => [
            'matricula_total' => 260.00,
            'matricula_pagado' => 260.00,
        ],
        '92310644' => [
            'obs_matricula' => 'H- hermano menor',
            'matricula_total' => 360.00,
            'matricula_pagado' => 360.00,
        ],
    ];

    public function run(): void
    {
        $anio = AnioEscolar::where('activo', true)->first()
            ?? AnioEscolar::orderByDesc('fecha_inicio')->first();

        if (! $anio) {
            $this->command?->warn('No hay anio escolar activo para registrar pagos de Inicial 4 anos.');
            return;
        }

        [$periodoMatricula, $periodo1, $periodo2] = $this->asegurarPeriodos($anio);

        $conceptoGastos = ConceptoPago::firstOrCreate(
            ['tipo' => 'gastos_internos'],
            ['nombre' => 'Matricula (Gastos Internos)', 'monto_base' => 100.00, 'activo' => true]
        );

        $conceptoPension = ConceptoPago::where('tipo', 'mensual')->first()
            ?? ConceptoPago::where('tipo', 'pension')->first()
            ?? ConceptoPago::whereIn('nombre', ['Pension', 'Pension Escolar'])->first();

        $conceptoLibros = ConceptoPago::firstOrCreate(
            ['nombre' => 'Libros'],
            ['tipo' => 'libros', 'monto_base' => 0, 'activo' => true]
        );

        if (! $conceptoPension) {
            $this->command?->warn('No se encontro el concepto de pension. Ejecuta primero CatalogoSeeder.');
            return;
        }

        $registrador = User::query()->orderBy('id')->first();
        if (! $registrador) {
            $this->command?->warn('No existe un usuario para registrar los pagos del seeder.');
            return;
        }

        $matriculas = Matricula::query()
            ->with('estudiante')
            ->where('anio_escolar_id', $anio->id)
            ->whereHas('estudiante', fn ($query) => $query->whereIn('dni', array_keys(self::ALUMNOS)))
            ->get()
            ->keyBy(fn (Matricula $matricula) => (string) ($matricula->estudiante->dni ?? ''));

        if ($matriculas->isEmpty()) {
            $this->command?->warn('No se encontraron matriculas de Inicial 4 anos para registrar pagos.');
            return;
        }

        DB::transaction(function () use (
            $anio,
            $periodoMatricula,
            $periodo1,
            $periodo2,
            $conceptoGastos,
            $conceptoPension,
            $conceptoLibros,
            $registrador,
            $matriculas
        ) {
            $this->limpiarDatosPrevios(
                $matriculas->pluck('id')->all(),
                [$conceptoGastos->id, $conceptoPension->id, $conceptoLibros->id]
            );

            $anioNum = $this->anioCalendario($anio);
            $fechaPago = Carbon::today()->toDateString();

            foreach (self::ALUMNOS as $dni => $data) {
                /** @var Matricula|null $matricula */
                $matricula = $matriculas->get($dni);
                if (! $matricula) {
                    $this->command?->warn("No se encontro la matricula del estudiante {$dni}.");
                    continue;
                }

                $matricula->update([
                    'observacion' => $data['obs_matricula'] ?? null,
                ]);

                $deudaMatricula = $this->crearDeuda(
                    $anio->id,
                    $periodoMatricula?->id,
                    $matricula->id,
                    $conceptoGastos->id,
                    'Matricula (Gastos Internos) ' . $anioNum,
                    (float) ($data['matricula_total'] ?? 260.00),
                    $anioNum . '-03-01'
                );

                $this->registrarPago(
                    $deudaMatricula,
                    (float) ($data['matricula_pagado'] ?? 0),
                    $fechaPago,
                    'matricula',
                    $registrador->id,
                    $anio->id,
                    $periodoMatricula?->id
                );

            }
        });

        $this->command?->info('Pagos iniciales de Inicial 4 anos registrados.');
    }

    private function asegurarPeriodos(AnioEscolar $anio): array
    {
        if (! PeriodoAcademico::where('anio_escolar_id', $anio->id)->exists()) {
            $anioNum = $this->anioCalendario($anio);
            $ahora = now();

            PeriodoAcademico::insert([
                [
                    'anio_escolar_id' => $anio->id,
                    'codigo' => $anioNum . '-0',
                    'nombre' => 'Vacacional',
                    'fecha_inicio' => "{$anioNum}-01-01",
                    'fecha_fin' => "{$anioNum}-02-28",
                    'es_vacacional' => true,
                    'orden' => 0,
                    'activo' => false,
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                ],
                [
                    'anio_escolar_id' => $anio->id,
                    'codigo' => $anioNum . '-1',
                    'nombre' => 'Periodo 1',
                    'fecha_inicio' => "{$anioNum}-03-01",
                    'fecha_fin' => "{$anioNum}-07-31",
                    'es_vacacional' => false,
                    'orden' => 1,
                    'activo' => true,
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                ],
                [
                    'anio_escolar_id' => $anio->id,
                    'codigo' => $anioNum . '-2',
                    'nombre' => 'Periodo 2',
                    'fecha_inicio' => "{$anioNum}-08-01",
                    'fecha_fin' => "{$anioNum}-12-31",
                    'es_vacacional' => false,
                    'orden' => 2,
                    'activo' => false,
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                ],
            ]);
        }

        $periodoMatricula = PeriodoAcademico::where('anio_escolar_id', $anio->id)
            ->where('orden', 0)
            ->first()
            ?? PeriodoAcademico::where('anio_escolar_id', $anio->id)->orderBy('orden')->first();

        $periodo1 = PeriodoAcademico::where('anio_escolar_id', $anio->id)
            ->where('orden', 1)
            ->first()
            ?? $periodoMatricula;

        $periodo2 = PeriodoAcademico::where('anio_escolar_id', $anio->id)
            ->where('orden', 2)
            ->first()
            ?? $periodo1
            ?? $periodoMatricula;

        return [$periodoMatricula, $periodo1, $periodo2];
    }

    private function limpiarDatosPrevios(array $matriculaIds, array $conceptoIds): void
    {
        Pago::where('comentario', 'like', self::PAYMENT_MARKER . '%')->delete();

        if (empty($matriculaIds) || empty($conceptoIds)) {
            return;
        }

        Deuda::whereIn('matricula_id', $matriculaIds)
            ->whereIn('concepto_pago_id', $conceptoIds)
            ->delete();
    }

    private function crearDeuda(
        ?int $anioId,
        ?int $periodoId,
        int $matriculaId,
        int $conceptoId,
        string $periodo,
        float $monto,
        ?string $fechaVencimiento
    ): ?Deuda {
        $monto = round(max(0, $monto), 2);
        if (! $anioId || $monto <= 0) {
            return null;
        }

        return Deuda::create([
            'anio_escolar_id' => $anioId,
            'periodo_academico_id' => $periodoId,
            'matricula_id' => $matriculaId,
            'concepto_pago_id' => $conceptoId,
            'periodo' => $periodo,
            'monto' => $monto,
            'saldo' => $monto,
            'fecha_vencimiento' => $fechaVencimiento,
            'estado' => 'pendiente',
        ]);
    }

    private function registrarPago(
        ?Deuda $deuda,
        float $montoPagado,
        string $fechaPago,
        string $codigoPago,
        int $usuarioRegistraId,
        int $anioId,
        ?int $periodoId
    ): void {
        if (! $deuda) {
            return;
        }

        $montoPagado = round(max(0, $montoPagado), 2);
        if ($montoPagado <= 0) {
            return;
        }

        $montoAplicado = min($montoPagado, (float) $deuda->monto);
        $marker = self::PAYMENT_MARKER . $deuda->matricula_id . ':' . $codigoPago;

        $pago = Pago::create([
            'anio_escolar_id' => $anioId,
            'periodo_academico_id' => $periodoId,
            'monto' => $montoAplicado,
            'fecha_pago' => $fechaPago,
            'metodo' => 'efectivo',
            'estado' => 'confirmado',
            'usuario_registra_id' => $usuarioRegistraId,
            'comentario' => $marker,
        ]);

        $ahora = now();
        DB::table('deuda_pagos')->insert([
            'deuda_id' => $deuda->id,
            'pago_id' => $pago->id,
            'monto_aplicado' => $montoAplicado,
            'created_at' => $ahora,
            'updated_at' => $ahora,
        ]);

        $nuevoSaldo = round(max(0, (float) $deuda->monto - $montoAplicado), 2);

        $deuda->update([
            'saldo' => $nuevoSaldo,
            'estado' => $nuevoSaldo <= 0 ? 'pagado' : 'parcial',
        ]);
    }

    private function anioCalendario(AnioEscolar $anio): int
    {
        $anioNum = (int) preg_replace('/\D+/', '', (string) $anio->nombre);

        return $anioNum >= 2000 ? $anioNum : (int) date('Y');
    }

}
