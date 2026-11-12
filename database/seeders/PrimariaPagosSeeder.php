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

class PrimariaPagosSeeder extends Seeder
{
    private const PAYMENT_MARKER = 'seed:primaria_pagos:';
    private const PENSION_REGULAR = 240.00;
    private const PENSION_ESPECIAL = 260.00;
    private const LIBROS_PRIMARIA = 280.00;

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
        '91781213' => ['matricula' => 260, 'libros' => 40],
        '91263550' => ['matricula' => 260],
        '91635387' => ['matricula' => 260, 'pagos_pension' => [3 => 240]],
        '91434108' => ['matricula' => 260, 'libros' => 280, 'pagos_pension' => [3 => 240]],
        '91494599' => ['matricula' => 'H+', 'libros' => 280, 'pagos_pension' => [3 => 240]],
        '91672271' => ['matricula' => 260],
        '91598327' => ['matricula' => 'H+', 'pagos_pension' => [3 => 240]],
        '91577710' => ['matricula' => 260],
        '91426414' => ['matricula' => '360 H', 'libros' => 280, 'pagos_pension' => [3 => 240]],
        '91329814' => ['matricula' => 260, 'libros' => 280, 'pagos_pension' => [3 => 240]],
        '91258811' => ['matricula' => 140],
        '91584435' => ['matricula' => '360 H'],
        '91519509' => ['matricula' => 260, 'libros' => 140],
        '91655906' => ['matricula' => '360 H-'],
        '91381329' => ['matricula' => 260],
        '91582399' => ['matricula' => 260],
        '91254207' => ['matricula' => 260, 'libros' => 280, 'pagos_pension' => [3 => 240]],
        '91437648' => ['matricula' => 260, 'libros' => 280, 'pagos_pension' => [3 => 240]],
        '91800316' => ['pago_completo_anual' => true],

        '78524483' => ['matricula' => 260, 'libros' => 280],
        '91415063' => ['matricula' => 260, 'libros' => 280],
        '91581493' => ['matricula' => 260, 'libros' => 280, 'pagos_pension' => [3 => 240]],
        '91653879' => ['matricula' => 260, 'libros' => 280],
        '91498422' => ['matricula' => 'H+', 'libros' => 280],
        '91639897' => ['matricula' => 260, 'libros' => 280, 'pagos_pension' => [3 => 160]],
        '91726810' => ['matricula' => '360 H', 'libros' => 280],
        '90048670' => ['matricula' => 260, 'pagos_pension' => [3 => 240]],

        '91214447' => ['matricula' => '360 H'],
        '91002309' => ['matricula' => 260],
        '91044267' => ['matricula' => 260],
        '90812119' => ['matricula' => 260, 'libros' => 280, 'pagos_pension' => [3 => 240]],
        '90813942' => ['matricula' => 260],
        '91229804' => [],
        '90779599' => ['matricula' => 260, 'libros' => 280],
        '90987131' => ['matricula' => '360H'],
        '90999745' => ['matricula' => 260, 'libros' => 250],
        '90776715' => ['matricula' => 260, 'libros' => 90],
        '90853900' => ['matricula' => 'H', 'libros' => 280, 'pago_completo_anual' => true],
        '90856828' => ['matricula' => 260],
        '91167904' => ['matricula' => 260, 'libros' => 280, 'pagos_pension' => [3 => 240]],
        '90894031' => ['matricula' => 260],
        '90831112' => ['matricula' => 'H', 'libros' => 40],
        '91270192' => ['matricula' => 260, 'libros' => 280, 'pagos_pension' => [3 => 240]],
        '90730257' => ['matricula' => 260, 'libros' => 280, 'pagos_pension' => [3 => 240]],
        '91227610' => ['matricula' => 240, 'libros' => 240],
        '90869997' => ['matricula' => 260, 'libros' => 280],
        '90823475' => ['matricula' => 260, 'pagos_pension' => [3 => 240]],
        '91220252' => ['matricula' => 'H', 'matricula_total' => 260, 'matricula_pagado' => 0],

        '91299700' => ['matricula' => 360, 'libros' => 280, 'pagos_pension' => [3 => 240]],
        '91055944' => ['matricula' => 260],
        '91236981' => ['matricula' => 360],
        '91186738' => ['matricula' => 260],
        '91117007' => ['matricula' => 260],
        '90208761' => ['matricula' => 260],
        '90644954' => ['matricula' => 260, 'pagos_pension' => [3 => 240]],
        '90621970' => [],
        '90460451' => ['matricula' => 'H', 'pagos_pension' => [3 => 240]],
        '90867608' => ['matricula' => 260],
        '90876616' => ['matricula' => 260],
        '90575438' => ['matricula' => 260],
        '91094943' => ['matricula' => 260, 'libros' => 280],

        '90196139' => ['matricula' => 160],
        '90206751' => ['matricula' => 260, 'libros' => 280, 'pagos_pension' => [3 => 240]],
        '90356622' => ['matricula' => 260, 'libros' => 40],
        '90249888' => ['matricula' => 260, 'libros' => 280],
        '90545309' => ['matricula' => 260],
        '90677007' => ['matricula' => 360, 'libros' => 280, 'pagos_pension' => [3 => 240, 4 => 240]],
        '90383747' => ['matricula' => 260],
        '90621729' => ['matricula' => 260, 'libros' => 280, 'pagos_pension' => [3 => 240]],
        '90537518' => ['matricula' => 260],
        '90523556' => ['matricula' => 'H', 'libros' => 280, 'pagos_pension' => [3 => 240]],
        '90550912' => ['matricula' => 360],
        '90401832' => ['matricula' => 260],
        '90041642' => ['matricula' => 260, 'libros' => 280, 'pagos_pension' => [3 => 240]],
        '90499483' => ['matricula' => 260, 'libros' => 200],
        '90658567' => ['matricula' => 260, 'libros' => 280],
        '90558226' => ['matricula' => 260],

        '79776663' => ['matricula' => 260, 'libros' => 40],
        '90655557' => ['matricula' => 360],
        '90526730' => ['matricula' => 260, 'libros' => 280, 'pagos_pension' => [3 => 240]],
        '90301112' => ['matricula' => 260, 'libros' => 280],
        '90520601' => ['matricula' => 260],
        '90399752' => ['matricula' => 'H'],
        '90133156' => ['matricula' => 140],
        '90262252' => ['matricula' => 360, 'libros' => 280],
        '91713108' => ['matricula' => 260],
        '90367083' => ['matricula' => 260, 'pagos_pension' => [3 => 240]],
        '90040470' => ['matricula' => 'H', 'libros' => 280, 'pagos_pension' => [3 => 240]],
        '79383704' => ['matricula' => 360],
        '90313057' => ['matricula' => 260],
        '90575207' => ['matricula' => 260],
        '90603722' => ['matricula' => 260, 'libros' => 280, 'pagos_pension' => [3 => 240]],

        '79732591' => ['matricula' => 260, 'libros' => 280],
        '90012663' => ['matricula' => 360, 'libros' => 280, 'pagos_pension' => [3 => 240]],
        '79791318' => ['matricula' => 260, 'libros' => 280],
        '79723121' => ['matricula' => 160],
        '79644456' => ['matricula' => 'H', 'libros' => 280, 'pagos_pension' => [3 => 240]],
        '90117101' => ['matricula' => 260, 'libros' => 280],
        '79756399' => ['matricula' => 260, 'libros' => 280],
        '90043884' => ['matricula' => 260],
        '79713804' => ['matricula' => 'H', 'libros' => 280, 'pago_completo_anual' => true],
        '79671551' => ['matricula' => 260, 'libros' => 280],
        '79680613' => ['matricula' => 360, 'pagos_pension' => [3 => 240]],
        '90121453' => ['matricula' => 360, 'pagos_pension' => [3 => 240]],
        '79915441' => ['matricula' => 260, 'libros' => 180, 'pagos_pension' => [3 => 240]],
        '79899528' => [],
        '79973356' => ['matricula' => 260, 'pagos_pension' => [3 => 240]],
        '79708326' => ['matricula' => 260],
        '79982787' => ['matricula' => 360, 'pagos_pension' => [3 => 240]],

        '90026836' => ['matricula' => 'H', 'libros' => 280],
        '90066292' => ['matricula' => 260, 'libros' => 280],
        '79705045' => ['matricula' => 260],
        '79770341' => ['matricula' => 260],
        '79634507' => ['matricula' => 240, 'libros' => 280],
        '79728749' => ['matricula' => 260, 'libros' => 280],
        '79971382' => ['matricula' => 260, 'libros' => 240],
        '79928303' => ['matricula' => 'H', 'pagos_pension' => [3 => 240]],
        '79929752' => [],
        '78087426' => ['matricula' => 260, 'libros' => 280],
        '79859783' => ['matricula' => 260, 'libros' => 280, 'pagos_pension' => [3 => 240]],
        '79829367' => ['matricula' => 'H', 'libros' => 280],
        '79790961' => ['matricula' => 260, 'pagos_pension' => [3 => 240]],
        '90015616' => ['matricula' => 'H', 'libros' => 280],
        '90113137' => ['matricula' => 'H', 'libros' => 280, 'pagos_pension' => [3 => 240, 4 => 240, 5 => 240, 6 => 240, 7 => 260]],
        '79359385' => ['matricula' => 260, 'libros' => 268],

        '79358311' => ['matricula' => 260],
        '79434186' => ['matricula' => 260, 'libros' => 280, 'pagos_pension' => [3 => 240]],
        '79395195' => ['matricula' => 'H'],
        '79367182' => ['matricula' => 260, 'libros' => 90],
        '79124202' => ['matricula' => 260],
        '79417271' => ['matricula' => 260],
        '79329885' => ['matricula' => 260, 'libros' => 280, 'pagos_pension' => [3 => 240]],
        '79331989' => ['matricula' => 260, 'libros' => 36, 'pagos_pension' => [3 => 240]],
        '79531491' => ['matricula' => 360, 'libros' => 280, 'pagos_pension' => [3 => 240, 4 => 240, 5 => 170]],
        '79362958' => ['matricula' => 260, 'libros' => 280, 'pagos_pension' => [3 => 240]],
        '79258732' => ['matricula' => 260, 'libros' => 140],
        '79105140' => ['matricula' => 260, 'pago_completo_anual' => true],
        '79108904' => [],
        '79496760' => ['matricula' => 160],
        '79114499' => ['matricula' => 260, 'libros' => 240],
        '79281989' => ['matricula' => 'H', 'libros' => 280],
        '79325241' => ['matricula' => 260],
        '79439868' => ['matricula' => 260, 'libros' => 280],
        '78793100' => ['matricula' => 260],
        '78381117' => ['matricula' => 260],

        '79005843' => ['matricula' => 260, 'libros' => 280, 'pagos_pension' => [3 => 240, 4 => 240]],
        '78540403' => ['matricula' => 260, 'libros' => 280, 'pagos_pension' => [3 => 240]],
        '78759777' => ['matricula' => 260, 'libros' => 280, 'pagos_pension' => [3 => 240]],
        '78765279' => ['matricula' => 360, 'pagos_pension' => [3 => 240]],
        '78976094' => ['matricula' => 260],
        '78782247' => ['matricula' => 'H', 'pagos_pension' => [3 => 240]],
        '81249050' => [],
        '78928865' => ['matricula' => 260, 'libros' => 280, 'pagos_pension' => [3 => 240]],
        '79027101' => ['matricula' => 260],
        '78840918' => ['matricula' => 'H'],
        '78998815' => ['matricula' => 260],
        '78612220' => ['matricula' => 260, 'libros' => 280, 'pagos_pension' => [3 => 240, 4 => 240, 5 => 240, 6 => 40]],
        '78728519' => ['matricula' => 'H', 'libros' => 280, 'pagos_pension' => [3 => 240]],
        '78913237' => ['matricula' => 260, 'libros' => 280, 'pagos_pension' => [3 => 240]],
        '78856909' => ['matricula' => 'H'],
        '79028895' => [],
        '78845350' => ['matricula' => 260, 'libros' => 280],
        '78532536' => ['matricula' => 260, 'libros' => 180],
        '78879399' => ['matricula' => 260, 'libros' => 280, 'pagos_pension' => [3 => 240]],
        '78819628' => ['matricula' => 'H'],
        '79030221' => ['matricula' => 260],
        '80897106' => ['matricula' => 260, 'libros' => 280],
        '78957860' => ['matricula' => 260, 'libros' => 280, 'pagos_pension' => [3 => 240]],
        '78721537' => ['matricula' => 360, 'libros' => 280, 'pagos_pension' => [3 => 240]],
        '78504684' => ['matricula' => 260],
        '81455458' => ['matricula' => 'H', 'pagos_pension' => [3 => 240]],
        '81589763' => ['matricula' => 260],
        '78943347' => ['matricula' => 260, 'libros' => 240],
        '78753828' => ['matricula' => 260, 'libros' => 280],
        '78940707' => ['matricula' => 260],
        '78763620' => ['matricula' => 'H', 'libros' => 280, 'pagos_pension' => [3 => 216, 4 => 216, 5 => 216, 6 => 216, 7 => 236]],
        '79091059' => ['matricula' => 260],
    ];

    public function run(): void
    {
        $anio = AnioEscolar::where('activo', true)->first()
            ?? AnioEscolar::orderByDesc('fecha_inicio')->first();

        if (! $anio) {
            $this->command?->warn('No hay anio escolar activo para registrar pagos de Primaria.');
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
            ->with(['estudiante', 'grado', 'seccion'])
            ->where('anio_escolar_id', $anio->id)
            ->whereHas('estudiante', fn ($query) => $query->whereIn('dni', array_keys(self::ALUMNOS)))
            ->get()
            ->keyBy(fn (Matricula $matricula) => (string) ($matricula->estudiante->dni ?? ''));

        if ($matriculas->isEmpty()) {
            $this->command?->warn('No se encontraron matriculas de Primaria para registrar pagos.');
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

        $this->command?->info('Pagos iniciales de Primaria registrados.');
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
            return self::LIBROS_PRIMARIA;
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
