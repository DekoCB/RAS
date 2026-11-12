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

class SecundariaPagosSeeder extends Seeder
{
    private const PAYMENT_MARKER = 'seed:secundaria_pagos:';
    private const PENSION_REGULAR = 290.00;
    private const PENSION_ESPECIAL = 310.00;
    private const LIBROS_SECUNDARIA = 290.00;

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
        '78430711' => ['matricula' => 260],
        '80849658' => ['matricula' => 'H', 'libros' => 290, 'pagos_pension' => [3 => 290]],
        '78454700' => ['matricula' => 260, 'libros' => 290, 'pagos_pension' => [3 => 290, 4 => 290]],
        '78072397' => ['matricula' => 360, 'libros' => 290, 'pagos_pension' => [3 => 290]],
        '80849679' => ['matricula' => 260],
        '78173159' => ['matricula' => 260],
        '78187412' => ['matricula' => 260, 'libros' => 290],
        '80849697' => ['matricula' => 360],
        '78179274' => ['matricula' => 260],
        '78536169' => ['matricula' => 260, 'libros' => 290, 'pagos_pension' => [3 => 290]],
        '78457336' => ['matricula' => 260],
        '81394049' => ['matricula' => 260],
        '78218437' => ['matricula' => 260, 'libros' => 290],
        '81551069' => ['matricula' => 'H'],
        '79283389' => [],
        '81140115' => ['matricula' => 360, 'libros' => 290, 'pagos_pension' => [3 => 290]],
        '77859581' => ['matricula' => 'H', 'pagos_pension' => [3 => 290]],
        '78208953' => ['matricula' => 260, 'libros' => 290, 'pagos_pension' => [3 => 290]],
        '78486964' => ['matricula' => 260, 'libros' => 290, 'pagos_pension' => [3 => 290]],
        '80850418' => ['matricula' => 160, 'libros' => 150],
        '78324605' => ['matricula' => 260, 'libros' => 290, 'pagos_pension' => [3 => 290, 4 => 290, 5 => 290]],
        '81413818' => ['matricula' => 260, 'libros' => 290],
        '78172277' => ['matricula' => 'H'],
        '78283787' => ['matricula' => 360, 'libros' => 290],
        '78353289' => ['matricula' => 260, 'libros' => 290, 'pagos_pension' => [3 => 290]],

        '77718723' => ['matricula' => 260, 'libros' => 290, 'pagos_pension' => [3 => 290]],
        '77789637' => ['matricula' => 260],
        '77761014' => ['matricula' => 260, 'libros' => 280],
        '80958701' => ['matricula' => 260, 'libros' => 290],
        '77844686' => ['matricula' => 260],
        '77874121' => ['matricula' => 'H'],
        '63140788' => ['matricula' => 260, 'libros' => 290, 'pagos_pension' => [3 => 290]],
        '80745601' => ['matricula' => 260],
        '80958703' => ['matricula' => 260],
        '63251889' => ['matricula' => 260, 'libros' => 140],
        '77942127' => ['matricula' => 'H'],
        '77882910' => ['matricula' => 260, 'libros' => 145],
        '78003303' => ['matricula' => 'H', 'libros' => 290, 'pagos_pension' => [3 => 290]],
        '63689744' => ['matricula' => 260, 'libros' => 290],
        '77950187' => ['matricula' => 260, 'libros' => 290, 'pagos_pension' => [3 => 290]],
        '80849835' => ['matricula' => 360, 'libros' => 290, 'pagos_pension' => [3 => 290]],
        '81110518' => ['matricula' => 'H'],
        '77933201' => ['matricula' => 'H'],
        '63077751' => ['matricula' => 'H', 'libros' => 290, 'pagos_pension' => [3 => 290]],
        '77941272' => ['matricula' => 260, 'libros' => 290, 'pagos_pension' => [3 => 290]],
        '63233995' => ['matricula' => 200, 'libros' => 100],
        '80849895' => ['matricula' => 260, 'pagos_pension' => [3 => 290]],
        '63776512' => ['matricula' => 260],
        '80850202' => ['matricula' => 260, 'libros' => 290],
        '63793993' => ['matricula' => 260, 'libros' => 290, 'pagos_pension' => [3 => 290]],
        '77759939' => ['matricula' => 260],
        '90415262' => [],

        '63069151' => ['matricula' => 260, 'libros' => 290, 'pagos_pension' => [3 => 290]],
        '77558903' => ['matricula' => 260],
        '62952203' => ['matricula' => 260, 'libros' => 290, 'pagos_pension' => [3 => 290]],
        '63069234' => [],
        '77453334' => ['matricula' => 'H'],
        '63323514' => ['matricula' => 160],
        '77410549' => ['matricula' => 260],
        '63689749' => ['matricula' => 260],
        '63140757' => ['matricula' => 'H', 'libros' => 290, 'pagos_pension' => [3 => 290, 4 => 290]],
        '62952184' => ['matricula' => 260, 'libros' => 290, 'pagos_pension' => [3 => 290]],
        '62822701' => ['matricula' => 'H', 'pagos_pension' => [3 => 290]],
        '63069134' => ['matricula' => 260, 'libros' => 140],
        '62634389' => ['matricula' => 260],
        '61866407' => ['matricula' => 260, 'libros' => 290],
        '77584287' => ['matricula' => 260, 'libros' => 290],
        '62829368' => ['matricula' => 'H', 'pagos_pension' => [3 => 290]],
        '63080909' => ['matricula' => 'H'],
        '63283469' => ['matricula' => 260, 'libros' => 290, 'pagos_pension' => [3 => 290]],
        '62777046' => ['matricula' => 260, 'libros' => 290, 'pagos_pension' => [3 => 290]],
        '62863798' => ['matricula' => 'H', 'libros' => 290],
        '77003878' => ['matricula' => 360, 'libros' => 100],

        '62634467' => ['matricula' => 260, 'libros' => 290, 'pagos_pension' => [3 => 290]],
        '62634422' => ['matricula' => 260, 'libros' => 290],
        '62634269' => ['matricula' => 'H'],
        '76492624' => ['matricula' => 260, 'libros' => 290],
        '62634461' => ['matricula' => 'H', 'libros' => 290, 'pagos_pension' => [3 => 290]],
        '62634495' => ['matricula' => 260, 'libros' => 100],
        '62315930' => ['matricula' => 260, 'libros' => 290],
        '62473075' => ['matricula' => 'H', 'libros' => 290, 'pagos_pension' => [3 => 290]],
        '62634258' => ['matricula' => 150],
        '62825073' => ['matricula' => 260, 'libros' => 290],
        '62646407' => ['matricula' => 260],
        '008918138' => ['matricula' => 260],
        '62243886' => ['matricula' => 260],
        '62470587' => ['matricula' => 260, 'libros' => 290, 'pagos_pension' => [3 => 290]],
        '62372119' => ['matricula' => 260, 'libros' => 290],
        '62315928' => ['matricula' => 260, 'libros' => 290, 'pagos_pension' => [3 => 290]],
        '62634310' => ['matricula' => 'H', 'libros' => 290, 'pagos_pension' => [3 => 290]],
        '62171228' => ['matricula' => 'H', 'libros' => 290, 'pagos_pension' => [3 => 290]],
        '62214364' => ['matricula' => 260, 'libros' => 290],
        '74511646' => ['matricula' => 260],
        '60097126' => ['matricula' => 260, 'libros' => 290],
        '62703545' => ['matricula' => 260, 'libros' => 290],
        '81128648' => ['matricula' => 260, 'libros' => 290],
        '62952205' => ['matricula' => 260],
        '61646467' => ['matricula' => 260],
        '74261209' => ['matricula' => 260, 'libros' => 290],

        '61949070' => ['matricula' => 260, 'libros' => 290],
        '61798039' => ['matricula' => 260, 'libros' => 290, 'pagos_pension' => [3 => 290, 4 => 160]],
        '61910293' => ['matricula' => 260, 'libros' => 290, 'pago_completo_anual' => true],
        '62078973' => ['matricula' => 'H'],
        '61235579' => ['matricula' => 260, 'libros' => 240],
        '61647168' => ['matricula' => 'H'],
        '61866346' => ['matricula' => 140],
        '61567934' => ['matricula' => 260],
        '61656426' => ['matricula' => 'H', 'libros' => 290, 'pagos_pension' => [3 => 290]],
        '61949090' => ['matricula' => 'H', 'libros' => 100],
        '61741596' => ['matricula' => 260, 'libros' => 290, 'pagos_pension' => [3 => 290]],
        '61739654' => ['matricula' => 'H', 'libros' => 100],
        '60584374' => ['matricula' => 260, 'libros' => 290],
        '61739655' => [],
        '62315947' => ['matricula' => 'H100'],
        '61967824' => ['matricula' => 260],
        '61771475' => ['libros' => 100],
    ];

    public function run(): void
    {
        $anio = AnioEscolar::where('activo', true)->first()
            ?? AnioEscolar::orderByDesc('fecha_inicio')->first();

        if (! $anio) {
            $this->command?->warn('No hay anio escolar activo para registrar pagos de Secundaria.');
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
            $this->command?->warn('No se encontraron matriculas de Secundaria para registrar pagos.');
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

                $matriculaInfo = $this->resolverMatricula($data);
                $montoLibrosPagado = $this->resolverPagoLibros($data);

                $matricula->update([
                    'observacion' => $matriculaInfo['observacion'],
                ]);

                $deudaMatricula = $this->crearDeuda(
                    $anio->id,
                    $periodoMatricula?->id,
                    $matricula->id,
                    $conceptoGastos->id,
                    'Matricula (Gastos Internos) ' . $anioNum,
                    $matriculaInfo['total'],
                    $anioNum . '-03-01'
                );

                $this->registrarPago(
                    $deudaMatricula,
                    $matriculaInfo['pagado'],
                    $fechaPago,
                    'matricula',
                    $registrador->id,
                    $anio->id,
                    $periodoMatricula?->id
                );

            }
        });

        $this->command?->info('Pagos iniciales de Secundaria registrados.');
    }

    private function resolverMatricula(array $data): array
    {
        $raw = strtoupper(str_replace(' ', '', trim((string) ($data['matricula'] ?? ''))));
        $observacion = null;
        $total = 260.00;
        $pagado = 0.00;

        if ($raw !== '') {
            if (preg_match('/(\d+(?:\.\d+)?)/', $raw, $match)) {
                $total = (float) $match[1];
                $pagado = $total;
            }

            if (str_contains($raw, 'H-')) {
                $observacion = 'H- hermano menor';
                if (! preg_match('/\d/', $raw)) {
                    // Sin número: otro hermano carga el monto combinado, este no paga
                    $total = 0.00;
                    $pagado = 0.00;
                }
            } elseif (str_contains($raw, 'H+')) {
                $observacion = 'H+ hermano mayor';
                if (! preg_match('/\d/', $raw)) {
                    // Sin número: otro hermano carga el monto combinado, este no paga
                    $total = 0.00;
                    $pagado = 0.00;
                }
            } elseif (str_contains($raw, 'H')) {
                $observacion = 'H hermano';
                if (! preg_match('/\d/', $raw)) {
                    // Sin número: otro hermano carga el monto combinado, este no paga
                    $total = 0.00;
                    $pagado = 0.00;
                }
            } elseif ($pagado <= 0 && is_numeric($raw)) {
                $pagado = (float) $raw;
            }
        }

        if (array_key_exists('matricula_total', $data)) {
            $total = (float) $data['matricula_total'];
        }

        if (array_key_exists('matricula_pagado', $data)) {
            $pagado = (float) $data['matricula_pagado'];
        }

        if (! empty($data['pago_completo_anual']) && $pagado <= 0) {
            $pagado = $total;
        }

        return [
            'total' => round(max(0, $total), 2),
            'pagado' => round(max(0, $pagado), 2),
            'observacion' => $observacion,
        ];
    }

    private function resolverPagoLibros(array $data): float
    {
        $monto = round(max(0, (float) ($data['libros'] ?? 0)), 2);

        if (! empty($data['pago_completo_anual']) && $monto <= 0) {
            return self::LIBROS_SECUNDARIA;
        }

        return $monto;
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
