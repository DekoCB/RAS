<?php

namespace Database\Seeders;

use App\Models\Activo;
use App\Models\Sede;
use Illuminate\Database\Seeder;

/**
 * Inventario de bienes del colegio (RF-70) con su historial de mantenimiento.
 * Local 1 = Inicial; Local 2 = Primaria y Secundaria. Idempotente por codigo.
 */
class InmobiliarioDemoSeeder extends Seeder
{
    /**
     * [codigo, nombre, categoria, sede (1|2), ubicacion, fecha, valor, estado, observacion, mantenimientos]
     * mantenimientos: [[fecha, tipo, responsable, costo, detalle], ...]
     */
    private const ACTIVOS = [
        // ── Inmuebles ─────────────────────────────────────────────────
        ['INM-001', 'Local 1 - Sede Inicial', 'inmueble', 1, 'Ciudad de Dios, Km 16, Mz. A, Lt. 9, Zona 3', '2015-01-15', 380000, 'operativo',
            'Terreno y construccion de un piso. 6 aulas, patio techado, SS.HH. y direccion.',
            [['2026-01-20', 'preventivo', 'Constructora Arequipa SAC', 4500, 'Pintado general de fachada y aulas antes del inicio de clases.'],
             ['2025-07-10', 'correctivo', 'Gasfiteria Mamani', 650, 'Reparacion de tuberia de desague en SS.HH. de ninos.']]],
        ['INM-002', 'Local 2 - Sede Primaria y Secundaria', 'inmueble', 2, 'Asoc. Ciudad de Dios, Zona 4, Comite 21, Mz. C, Lt. 15', '2018-03-01', 620000, 'operativo',
            'Edificio de tres pisos. 11 aulas, laboratorio, biblioteca, comedor y losa deportiva.',
            [['2026-01-12', 'preventivo', 'Constructora Arequipa SAC', 7800, 'Impermeabilizacion de techo y pintado de aulas.'],
             ['2025-11-05', 'preventivo', 'Electricista R. Quispe', 1200, 'Revision de tablero electrico general y cambio de llaves termicas.']]],

        // ── Mobiliario ────────────────────────────────────────────────
        ['MOB-001', 'Mesas y sillas de Inicial (juego de 30)', 'mobiliario', 1, 'Aulas de 3, 4 y 5 anos', '2022-02-10', 6000, 'operativo', 'Mesas hexagonales de melamina y sillas de polipropileno.', []],
        ['MOB-002', 'Carpetas unipersonales Primaria (120 unidades)', 'mobiliario', 2, 'Aulas 1ro a 6to de Primaria', '2021-02-15', 18000, 'operativo', null,
            [['2026-02-03', 'correctivo', 'Carpinteria Flores', 480, 'Cambio de 12 tableros astillados y ajuste de patas.']]],
        ['MOB-003', 'Carpetas unipersonales Secundaria (100 unidades)', 'mobiliario', 2, 'Aulas 1ro a 5to de Secundaria', '2021-02-15', 15000, 'operativo', null, []],
        ['MOB-004', 'Pizarras acrilicas 2.40 x 1.20 (17 unidades)', 'mobiliario', 2, 'Todas las aulas del Local 2', '2019-02-20', 5100, 'operativo', null, []],
        ['MOB-005', 'Pizarras acrilicas 1.80 x 1.20 (4 unidades)', 'mobiliario', 1, 'Aulas del Local 1', '2019-02-20', 900, 'operativo', null, []],
        ['MOB-006', 'Estantes de biblioteca (8 modulos)', 'mobiliario', 2, 'Biblioteca - 2do piso', '2020-06-01', 3200, 'operativo', null, []],
        ['MOB-007', 'Mesas y bancas del comedor Realino (10 juegos)', 'mobiliario', 2, 'Comedor - 1er piso', '2023-03-05', 4500, 'operativo', null, []],
        ['MOB-008', 'Escritorios y sillas de direccion y secretaria', 'mobiliario', 2, 'Oficinas administrativas', '2018-03-10', 2400, 'operativo', null, []],
        ['MOB-009', 'Lockers metalicos para docentes (2 modulos de 12)', 'mobiliario', 2, 'Sala de profesores', '2022-08-12', 1900, 'mantenimiento', 'Una puerta con chapa forzada pendiente de cambio.',
            [['2026-09-08', 'correctivo', 'Cerrajeria Central', 0, 'Cambio de chapa del casillero 7 - en espera del repuesto.']]],

        // ── Equipos ───────────────────────────────────────────────────
        ['EQP-001', 'Proyector multimedia Epson X49 - Aula 1ro Sec.', 'equipo', 2, 'Aula 1ro de Secundaria', '2023-02-25', 2100, 'operativo', null,
            [['2026-03-14', 'preventivo', 'Servitec', 80, 'Limpieza de filtros y lente.']]],
        ['EQP-002', 'Proyector multimedia Epson X49 - Aula 2do Sec.', 'equipo', 2, 'Aula 2do de Secundaria', '2023-02-25', 2100, 'operativo', null, []],
        ['EQP-003', 'Proyector multimedia Epson X49 - Aula 3ro Sec.', 'equipo', 2, 'Aula 3ro de Secundaria', '2023-02-25', 2100, 'mantenimiento', 'Lampara con poca luminosidad.',
            [['2026-09-05', 'correctivo', 'Servitec', 350, 'Cambio de lampara. Equipo en taller.']]],
        ['EQP-004', 'Proyector multimedia Epson X49 - Sala de usos multiples', 'equipo', 2, 'SUM - 3er piso', '2023-02-25', 2100, 'operativo', null, []],
        ['EQP-005', 'Computadoras de escritorio Lenovo (20 unidades)', 'equipo', 2, 'Laboratorio de computo - 2do piso', '2022-01-20', 48000, 'operativo', 'Core i5, 8 GB RAM, monitor 21".',
            [['2026-02-17', 'preventivo', 'Servitec', 600, 'Mantenimiento preventivo anual: limpieza interna y actualizacion de sistema.'],
             ['2025-08-22', 'correctivo', 'Servitec', 280, 'Cambio de fuente de poder en equipo N.o 14.']]],
        ['EQP-006', 'Laptop HP 15 - Direccion', 'equipo', 2, 'Direccion', '2024-04-02', 2800, 'operativo', null, []],
        ['EQP-007', 'Laptop HP 15 - Secretaria', 'equipo', 2, 'Secretaria', '2024-04-02', 2800, 'operativo', null, []],
        ['EQP-008', 'Impresora multifuncional Epson L3250 - Secretaria', 'equipo', 2, 'Secretaria', '2024-04-02', 950, 'operativo', null,
            [['2026-06-10', 'preventivo', 'Servitec', 60, 'Limpieza de cabezales y recarga de tinta.']]],
        ['EQP-009', 'Fotocopiadora Ricoh MP 2014', 'equipo', 2, 'Secretaria', '2020-09-15', 3900, 'operativo', null,
            [['2026-07-22', 'correctivo', 'Ricoh Servicio Tecnico', 420, 'Cambio de rodillo de arrastre y limpieza general.'],
             ['2025-12-03', 'preventivo', 'Ricoh Servicio Tecnico', 150, 'Mantenimiento preventivo semestral.']]],
        ['EQP-010', 'Equipo de sonido y microfonos inalambricos', 'equipo', 2, 'Patio principal / SUM', '2021-11-08', 3200, 'operativo', 'Consola, 2 parlantes activos y 2 microfonos.', []],
        ['EQP-011', 'Televisor Smart TV 55" - Inicial', 'equipo', 1, 'Aula de 5 anos', '2023-05-19', 1800, 'operativo', null, []],
        ['EQP-012', 'Refrigeradora y cocina industrial del comedor', 'equipo', 2, 'Cocina del comedor Realino', '2023-03-05', 5600, 'operativo', null,
            [['2026-04-08', 'preventivo', 'Gas Sur', 120, 'Revision de instalacion de gas y quemadores.']]],
        ['EQP-013', 'Camaras de seguridad (8) y grabador DVR - Local 2', 'equipo', 2, 'Ingreso, pasillos y patio', '2022-10-30', 4200, 'operativo', null, []],
        ['EQP-014', 'Camaras de seguridad (4) y grabador DVR - Local 1', 'equipo', 1, 'Ingreso y patio', '2022-10-30', 2300, 'operativo', null, []],
        ['EQP-015', 'Extintores PQS 6 kg (10 unidades)', 'equipo', 2, 'Pasillos, laboratorio, cocina', '2024-03-01', 900, 'operativo', 'Recarga anual coordinada con Defensa Civil.',
            [['2026-03-01', 'preventivo', 'Extintores Arequipa', 300, 'Recarga anual y cambio de manometros.']]],
        ['EQP-016', 'Extintores PQS 6 kg (4 unidades)', 'equipo', 1, 'Pasillo y cocina', '2024-03-01', 360, 'operativo', null,
            [['2026-03-01', 'preventivo', 'Extintores Arequipa', 120, 'Recarga anual.']]],
        ['EQP-017', 'Proyector Epson S8 (antiguo)', 'equipo', 2, 'Almacen', '2016-04-10', 1500, 'baja', 'Dado de baja: lampara y placa danadas, reparacion no rentable.',
            [['2025-06-14', 'correctivo', 'Servitec', 0, 'Diagnostico: placa principal danada. Se recomienda baja.']]],
        ['EQP-018', 'Juegos infantiles de patio (resbaladera, columpios)', 'equipo', 1, 'Patio del Local 1', '2019-08-20', 4800, 'operativo', null,
            [['2026-02-24', 'preventivo', 'Metalmecanica Condori', 350, 'Lijado, pintado anticorrosivo y ajuste de pernos.']]],

        // ── Vehiculos ─────────────────────────────────────────────────
        ['VEH-001', 'Combi Toyota Hiace 2016 - movilidad escolar', 'vehiculo', 2, 'Cochera del Local 2', '2019-12-10', 62000, 'operativo', 'Placa V4K-812. SOAT y revision tecnica al dia.',
            [['2026-08-18', 'preventivo', 'Taller Toyota Sur', 480, 'Cambio de aceite, filtros y revision de frenos (95,000 km).'],
             ['2026-05-06', 'correctivo', 'Llantas del Sur', 1100, 'Cambio de 4 neumaticos.'],
             ['2026-01-27', 'preventivo', 'Revisiones Tecnicas del Peru', 90, 'Revision tecnica vehicular anual - aprobada.']]],
    ];

    public function run(): void
    {
        $sedes = Sede::orderBy('orden')->pluck('id')->values();
        $creados = 0;

        foreach (self::ACTIVOS as [$codigo, $nombre, $categoria, $sedeN, $ubicacion, $fecha, $valor, $estado, $obs, $mantenimientos]) {
            if (Activo::where('codigo', $codigo)->exists()) {
                continue;
            }

            $activo = Activo::create([
                'codigo'            => $codigo,
                'nombre'            => $nombre,
                'categoria'         => $categoria,
                'sede_id'           => $sedes[$sedeN - 1] ?? null,
                'ubicacion'         => $ubicacion,
                'fecha_adquisicion' => $fecha,
                'valor'             => $valor,
                'estado'            => $estado,
                'observacion'       => $obs,
            ]);

            foreach ($mantenimientos as [$mFecha, $tipo, $responsable, $costo, $detalle]) {
                $activo->mantenimientos()->create([
                    'fecha'       => $mFecha,
                    'tipo'        => $tipo,
                    'responsable' => $responsable,
                    'costo'       => $costo,
                    'detalle'     => $detalle,
                ]);
            }

            $creados++;
        }

        $this->command->info("Inmobiliario: {$creados} activos registrados.");
    }
}
