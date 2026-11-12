<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Grado;
use App\Models\HorarioInstitucional;
use App\Models\Institucion;
use App\Models\InstitucionFrase;
use App\Models\InstitucionValor;
use Illuminate\Http\Request;

/**
 * Datos institucionales editables: identidad, vision, mision, valores,
 * frases de cultura y horarios por nivel/grado.
 *
 * Cubre RF-03 (vision, mision, valores, cultura), RF-10 (contacto),
 * RF-29 (horarios de ingreso y salida) y RF-30 (recepcion de almuerzo).
 */
class InstitucionController extends Controller
{
    public function index()
    {
        $institucion = Institucion::actual();
        $valores     = InstitucionValor::orderBy('orden')->get();
        $frases      = InstitucionFrase::orderBy('orden')->get();
        $horarios    = HorarioInstitucional::with('grado')
            ->orderBy('nivel')
            ->orderByRaw('grado_id is null desc')
            ->get();
        $grados      = Grado::orderBy('orden')->get();

        return view('admin.institucion.index', compact(
            'institucion', 'valores', 'frases', 'horarios', 'grados'
        ));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'nombre'               => ['required', 'string', 'max:180'],
            'sigla'                => ['nullable', 'string', 'max:80'],
            'lema'                 => ['nullable', 'string', 'max:255'],
            'ciudad'               => ['nullable', 'string', 'max:120'],
            'vision'               => ['nullable', 'string'],
            'mision'               => ['nullable', 'string'],
            'telefono_central'     => ['nullable', 'string', 'max:40'],
            'telefono_secundaria'  => ['nullable', 'string', 'max:40'],
            'telefono_adicional'   => ['nullable', 'string', 'max:40'],
            'email'                => ['nullable', 'email', 'max:150'],
            'himno_titulo'         => ['nullable', 'string', 'max:150'],
            'himno_autor'          => ['nullable', 'string', 'max:150'],
            'video_institucional'  => ['nullable', 'string', 'max:255'],
            'cod_inicial'          => ['nullable', 'string', 'max:20'],
            'cod_primaria'         => ['nullable', 'string', 'max:20'],
            'cod_secundaria'       => ['nullable', 'string', 'max:20'],
        ]);

        $institucion = Institucion::actual();

        $institucion->fill($data);
        $institucion->codigos_modulares = array_filter([
            'Inicial'    => $request->input('cod_inicial'),
            'Primaria'   => $request->input('cod_primaria'),
            'Secundaria' => $request->input('cod_secundaria'),
        ]);
        $institucion->save();

        return back()->with('status', 'Datos institucionales actualizados.');
    }

    public function guardarValor(Request $request)
    {
        $data = $request->validate([
            'id'          => ['nullable', 'exists:institucion_valores,id'],
            'nombre'      => ['required', 'string', 'max:120'],
            'descripcion' => ['nullable', 'string'],
            'orden'       => ['nullable', 'integer', 'min:0'],
        ]);

        InstitucionValor::updateOrCreate(
            ['id' => $data['id'] ?? null],
            [
                'nombre'      => $data['nombre'],
                'descripcion' => $data['descripcion'] ?? null,
                'orden'       => $data['orden'] ?? 0,
                'activo'      => true,
            ]
        );

        return back()->with('status', 'Valor institucional guardado.');
    }

    public function eliminarValor(InstitucionValor $valor)
    {
        $valor->delete();

        return back()->with('status', 'Valor institucional eliminado.');
    }

    public function guardarFrase(Request $request)
    {
        $data = $request->validate([
            'id'    => ['nullable', 'exists:institucion_frases,id'],
            'frase' => ['required', 'string', 'max:255'],
            'area'  => ['nullable', 'string', 'max:120'],
            'orden' => ['nullable', 'integer', 'min:0'],
        ]);

        InstitucionFrase::updateOrCreate(
            ['id' => $data['id'] ?? null],
            [
                'frase'  => $data['frase'],
                'area'   => $data['area'] ?? null,
                'orden'  => $data['orden'] ?? 0,
                'activo' => true,
            ]
        );

        return back()->with('status', 'Frase de cultura guardada.');
    }

    public function eliminarFrase(InstitucionFrase $frase)
    {
        $frase->delete();

        return back()->with('status', 'Frase de cultura eliminada.');
    }

    /** RF-29 y RF-30. */
    public function guardarHorario(Request $request)
    {
        $data = $request->validate([
            'id'              => ['nullable', 'exists:horarios_institucionales,id'],
            'nivel'           => ['required', 'string', 'max:30'],
            'grado_id'        => ['nullable', 'exists:grados,id'],
            'hora_ingreso'    => ['nullable', 'date_format:H:i'],
            'hora_salida'     => ['nullable', 'date_format:H:i'],
            'almuerzo_inicio' => ['nullable', 'date_format:H:i'],
            'almuerzo_fin'    => ['nullable', 'date_format:H:i'],
            // Art. 153 del Reglamento Interno
            'tolerancia_minutos'    => ['nullable', 'integer', 'min:0', 'max:120'],
            'espera_maxima_minutos' => ['nullable', 'integer', 'min:0', 'max:120'],
        ]);

        $almuerzoInicio = $data['almuerzo_inicio'] ?? null;
        $almuerzoFin    = $data['almuerzo_fin'] ?? null;

        if ($almuerzoInicio && $almuerzoFin && $almuerzoFin <= $almuerzoInicio) {
            return back()->withInput()->withErrors([
                'almuerzo_fin' => 'El fin del almuerzo debe ser posterior al inicio.',
            ]);
        }

        HorarioInstitucional::updateOrCreate(
            ['id' => $data['id'] ?? null],
            [
                'nivel'           => $data['nivel'],
                'grado_id'        => $data['grado_id'] ?? null,
                'hora_ingreso'    => $data['hora_ingreso'] ?? null,
                'hora_salida'     => $data['hora_salida'] ?? null,
                'almuerzo_inicio' => $almuerzoInicio,
                'almuerzo_fin'    => $almuerzoFin,
                'tolerancia_minutos'    => $data['tolerancia_minutos'] ?? null,
                'espera_maxima_minutos' => $data['espera_maxima_minutos'] ?? null,
                'activo'          => true,
            ]
        );

        return back()->with('status', 'Horario guardado.');
    }

    public function eliminarHorario(HorarioInstitucional $horario)
    {
        $horario->delete();

        return back()->with('status', 'Horario eliminado.');
    }
}
