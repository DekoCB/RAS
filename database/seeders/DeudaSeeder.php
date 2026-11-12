<?php

namespace Database\Seeders;

use App\Models\AnioEscolar;
use App\Models\ConceptoPago;
use App\Models\CronogramaPension;
use App\Models\Deuda;
use App\Models\InventarioProducto;
use App\Models\Matricula;
use App\Models\PeriodoAcademico;
use App\Models\Tarifa;
use Illuminate\Database\Seeder;

class DeudaSeeder extends Seeder
{
    // ─── Matrícula / Gastos Internos ─────────────────────────────────────────
    // Fallbacks si el tarifario del año no tiene la tarifa; los montos reales
    // salen de la tabla tarifas (TarifasSeeder / Reglamento Interno).
    private const MONTO_MATRICULA     = 220.00; // primer hijo (del tarifario matricula)
    private const MONTO_GASTOS        = 100.00; // hermanos (del tarifario matricula_hermano)

    /**
     * Lee el precio de libros desde InventarioProducto (Bazar).
     * Regla especial: Inicial 2 anios paga S/ 40.00.
     * Si no hay coincidencia exacta en Bazar, usa fallback por nivel.
     */
    private function precioLibros(Matricula $matricula): float
    {
        $nivel = (string) ($matricula->grado?->nivel ?? '');
        $nombreGrado = strtolower((string) ($matricula->grado?->nombre ?? ''));

        if ($nivel === 'Inicial' && str_contains($nombreGrado, '2')) {
            $producto = InventarioProducto::where('sku', 'LIB-INICIAL-2')->first();
            return $producto ? (float) $producto->precio : 40.00;
        }

        if ($nivel === 'Inicial') {
            $producto = InventarioProducto::where('sku', 'LIB-INICIAL')->first();
            return $producto ? (float) $producto->precio : 230.00;
        }

        if ($nivel === 'Primaria') {
            $producto = InventarioProducto::where('sku', 'LIB-PRIMARIA')->first();
            return $producto ? (float) $producto->precio : 280.00;
        }

        if ($nivel === 'Secundaria') {
            $producto = InventarioProducto::where('sku', 'LIB-SECUNDARIA')->first();
            return $producto ? (float) $producto->precio : 290.00;
        }

        $producto = InventarioProducto::where('nombre', 'LIKE', '%Libros%' . $nivel . '%')
            ->orWhere('nombre', 'LIKE', '%' . $nivel . '%Libros%')
            ->first();

        if ($producto) {
            return (float) $producto->precio;
        }

        // Fallback si no hay producto en el Bazar
        return match($nivel) {
            'Inicial'    => 230.00,
            'Primaria'   => 280.00,
            'Secundaria' => 290.00,
            default      => 0.00,
        };
    }

    private const MESES_PENSION    = [3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
    private const NOMBRES_MESES    = [
        3  => 'MARZO',
        4  => 'ABRIL',
        5  => 'MAYO',
        6  => 'JUNIO',
        7  => 'JULIO',
        8  => 'AGOSTO',
        9  => 'SEPTIEMBRE',
        10 => 'OCTUBRE',
        11 => 'NOVIEMBRE',
        12 => 'DICIEMBRE',
    ];

    public function run(): void
    {
        $anio = AnioEscolar::where('activo', true)->first()
            ?? AnioEscolar::orderByDesc('fecha_inicio')->first();

        if (!$anio) {
            $this->command->warn('No hay AnioEscolar activo.');
            return;
        }

        $anioNum = (int) preg_replace('/\D+/', '', (string) $anio->nombre);
        if ($anioNum < 2000) $anioNum = (int) date('Y');

        // ── Conceptos de pago ────────────────────────────────────────────────
        $conceptoGastos = ConceptoPago::firstOrCreate(
            ['tipo' => 'gastos_internos'],
            ['nombre' => 'Matrícula (Gastos Internos)', 'monto_base' => self::MONTO_GASTOS, 'activo' => true]
        );

        $conceptoPension = ConceptoPago::where('tipo', 'mensual')->first()
            ?? ConceptoPago::whereIn('nombre', ['Pensión', 'Pension'])->first();

        $conceptoLibros = ConceptoPago::firstOrCreate(
            ['nombre' => 'Libros'],
            ['tipo' => 'libros', 'monto_base' => 0, 'activo' => true]
        );

        if (!$conceptoPension) {
            $this->command->warn('No se encontró el concepto de Pensión. Ejecuta primero CatalogoSeeder.');
            return;
        }

        // ── Tarifas de pensión por nivel y mes (las siembra TarifasSeeder) ───
        $tarifasPension = Tarifa::where('anio_escolar_id', $anio->id)
            ->where('concepto_pago_id', $conceptoPension->id)
            ->where('activo', true)
            ->get()
            ->groupBy('nivel')
            ->map(fn ($grupo) => $grupo->keyBy('mes'));

        if ($tarifasPension->isEmpty()) {
            $this->command->warn('No hay tarifas de Pensión para el año activo. Ejecuta primero TarifasSeeder.');
            return;
        }

        $conceptoMatricula = ConceptoPago::where('tipo', 'matricula')->first();
        $montoMatricula = (float) (Tarifa::where('anio_escolar_id', $anio->id)
            ->where('concepto_pago_id', $conceptoMatricula?->id)
            ->where('activo', true)
            ->where('mes', 0)
            ->value('monto') ?? self::MONTO_MATRICULA);

        // ── Periodos académicos ──────────────────────────────────────────────
        $periodoVacacional = PeriodoAcademico::where('anio_escolar_id', $anio->id)->where('orden', 0)->first();
        $periodo1          = PeriodoAcademico::where('anio_escolar_id', $anio->id)->where('orden', 1)->first();
        $periodo2          = PeriodoAcademico::where('anio_escolar_id', $anio->id)->where('orden', 2)->first();
        $periodoMatricula  = $periodoVacacional ?? $periodo1; // Matrícula/Gastos van al período inicial

        // ── Cargar todas las matrículas activas con su estudiante+apoderado ──
        $todasMatriculas = Matricula::with([
            'estudiante.apoderados' => fn ($q) => $q->wherePivot('principal', true),
            'grado',
        ])
            ->where('anio_escolar_id', $anio->id)
            ->where('estado', '!=', 'retirada')
            ->get();

        // ── Agrupar por apoderado principal ──────────────────────────────────
        $familias      = []; // [apoderado_id => [Matricula, ...]]
        $sinApoderado  = []; // matrículas sin apoderado registrado

        foreach ($todasMatriculas as $matricula) {
            $apoderado = $matricula->estudiante->apoderados->first();
            if ($apoderado) {
                $familias[$apoderado->id][] = $matricula;
            } else {
                $sinApoderado[] = $matricula;
            }
        }

        // ── Procesar cada familia ─────────────────────────────────────────────
        $totalFamilias = 0;

        foreach ($familias as $matriculasFamily) {
            $this->procesarFamilia(
                $matriculasFamily,
                $anio->id,
                $anioNum,
                $periodoMatricula?->id,
                $conceptoGastos,
                $montoMatricula
            );
            $totalFamilias++;
        }

        // Estudiantes sin apoderado → 1 hijo solo, sin descuento
        foreach ($sinApoderado as $matricula) {
            $this->procesarFamilia(
                [$matricula],
                $anio->id,
                $anioNum,
                $periodoMatricula?->id,
                $conceptoGastos,
                $montoMatricula
            );
            $totalFamilias++;
        }

        $this->command->info("Matrícula + Gastos Internos generados para {$totalFamilias} familias ({$anio->nombre}).");

        // ── Pensiones y Libros por alumno ─────────────────────────────────────
        $countPensiones = 0;

        foreach ($todasMatriculas as $matricula) {
            $nivel = $matricula->grado?->nivel;

            if (!$nivel || !isset($tarifasPension[$nivel])) {
                continue;
            }

            $tarifas = $tarifasPension[$nivel];

            // Pensiones mensuales (Marzo - Diciembre)
            foreach (self::MESES_PENSION as $mes) {
                $periodoLabel = 'Pensión ' . self::NOMBRES_MESES[$mes] . ' ' . $anioNum;

                $existe = Deuda::where('matricula_id', $matricula->id)
                    ->where('concepto_pago_id', $conceptoPension->id)
                    ->where('periodo', $periodoLabel)
                    ->exists();

                if ($existe) continue;

                // Tarifa del mes si existe; si no, la general (mes=0)
                $monto = (float) (($tarifas[$mes] ?? $tarifas[0] ?? null)?->monto ?? 0);
                if ($monto <= 0) continue;

                $periodoId = $mes <= 7 ? $periodo1?->id : $periodo2?->id;
                $fechaVenc = CronogramaPension::fechaVencimiento($anio->id, $anioNum, $mes)->toDateString();

                Deuda::create([
                    'anio_escolar_id'      => $anio->id,
                    'periodo_academico_id' => $periodoId,
                    'matricula_id'         => $matricula->id,
                    'concepto_pago_id'     => $conceptoPension->id,
                    'periodo'              => $periodoLabel,
                    'monto'                => $monto,
                    'saldo'                => $monto,
                    'fecha_vencimiento'    => $fechaVenc,
                    'estado'               => 'pendiente',
                ]);
            }

            // Libros (una sola deuda por matrícula)
            $existeLibros = Deuda::where('matricula_id', $matricula->id)
                ->where('concepto_pago_id', $conceptoLibros->id)
                ->exists();

            if (!$existeLibros) {
                $montoLibros = $this->precioLibros($matricula);
                if ($montoLibros > 0) {
                    Deuda::create([
                        'anio_escolar_id'      => $anio->id,
                        'periodo_academico_id' => $periodo1?->id,
                        'matricula_id'         => $matricula->id,
                        'concepto_pago_id'     => $conceptoLibros->id,
                        'periodo'              => 'Libros ' . $anioNum,
                        'monto'                => $montoLibros,
                        'saldo'                => $montoLibros,
                        'fecha_vencimiento'    => $anioNum . '-03-05',
                        'estado'               => 'pendiente',
                    ]);
                }
            }

            $countPensiones++;
        }

        $this->command->info("Pensiones + Libros generados para {$countPensiones} alumnos ({$anio->nombre}).");
    }

    /**
     * Genera las deudas de Matrícula (1 por familia) y Gastos Internos (1 por hijo).
     * Aplica 5% de descuento al hijo menor cuando hay más de 1 hijo.
     *
     * @param  Matricula[]  $matriculasFamily
     */
    private function procesarFamilia(
        array $matriculasFamily,
        int $anioId,
        int $anioNum,
        ?int $periodoId,
        ConceptoPago $conceptoGastos,
        float $montoMatricula = self::MONTO_MATRICULA
    ): void {
        $familia = collect($matriculasFamily);

        // Ordenar por fecha_nacimiento ASC: el primero es el mayor, el último el menor
        $ordenada = $familia->sortBy(function (Matricula $m) {
            // Sin fecha de nacimiento → se trata como el mayor (va al inicio)
            return $m->estudiante->fecha_nacimiento ?? '1900-01-01';
        })->values();

        $hijoMayor = $ordenada->first();

        // ── Deuda única: Matrícula (Gastos Internos) ─────────────────────────
        //   Hijo mayor: S/.260 (S/.160 matrícula familiar + S/.100 gastos)
        //   Hijos siguientes: S/.100
        foreach ($ordenada as $matricula) {
            $existeGastos = Deuda::where('matricula_id', $matricula->id)
                ->where('concepto_pago_id', $conceptoGastos->id)
                ->exists();

            if ($existeGastos) continue;

            // Todos pagan tarifa completa; la secretaria ajusta manualmente si es hermano
            $monto = $montoMatricula;

            Deuda::create([
                'anio_escolar_id'      => $anioId,
                'periodo_academico_id' => $periodoId,
                'matricula_id'         => $matricula->id,
                'concepto_pago_id'     => $conceptoGastos->id,
                'periodo'              => 'Matrícula (Gastos Internos) ' . $anioNum,
                'monto'                => $monto,
                'saldo'                => $monto,
                'fecha_vencimiento'    => $anioNum . '-03-01',
                'estado'               => 'pendiente',
            ]);
        }
    }
}
