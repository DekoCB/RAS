<?php

namespace App\Http\Controllers;

use App\Models\AnioEscolar;
use App\Models\Grado;
use App\Models\Institucion;
use App\Models\Postulante;
use App\Models\Sede;
use App\Support\BannerAdmision;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Pre-inscripcion publica (sitio web).
 *
 * La familia deja sus datos desde la portada y el expediente entra al modulo
 * de Admision en estado "preinscrito": nadie queda matriculado por la web,
 * pero secretaria recibe el caso listo para continuar el proceso.
 */
class PreinscripcionController extends Controller
{
    private const NIVELES = ['Inicial', 'Primaria', 'Secundaria'];

    /** Formulario publico. */
    public function formulario()
    {
        return view('sitio.preinscripcion', [
            'institucion' => $this->institucion(),
            'campana'     => AnioEscolar::campanaAdmision(),
            'niveles'     => self::NIVELES,
            'sedes'       => Sede::where('activo', true)->orderBy('orden')->get(),
            'grados'      => Grado::orderBy('orden')->get(),
            'banner'      => BannerAdmision::ruta(),
        ]);
    }

    public function guardar(Request $request)
    {
        $campana = AnioEscolar::campanaAdmision();

        if (!$campana) {
            return back()->withInput()->withErrors([
                'general' => 'Por ahora no hay una campana de admision abierta. Comunicate con el colegio.',
            ]);
        }

        // Trampa para bots: el campo va oculto por CSS, una persona nunca lo llena.
        if (trim((string) $request->input('website')) !== '') {
            return redirect()->route('preinscripcion.formulario');
        }

        $data = $this->validar($request);

        if ($error = $this->sedeAtiendeNivel($data['sede_id'] ?? null, $data['nivel'])) {
            return back()->withInput()->withErrors(['sede_id' => $error]);
        }

        $this->rechazarDuplicado($campana, $data['dni']);

        $postulante = DB::transaction(function () use ($campana, $data) {
            $postulante = Postulante::create($data + [
                'anio_escolar_id'   => $campana->id,
                'codigo'            => Postulante::siguienteCodigo($campana->id, $campana->nombre),
                'estado'            => 'preinscrito',
                'origen'            => 'web',
                'token_seguimiento' => Str::lower(Str::random(32)),
                'preinscrito_at'    => now(),
                // Los datos del alumno y del apoderado ya vienen completos:
                // secretaria retoma el asistente en el paso de documentos.
                'paso_actual'       => 3,
            ]);

            foreach (array_keys(Postulante::ETAPAS) as $etapa) {
                $postulante->etapas()->create(['etapa' => $etapa, 'estado' => 'pendiente']);
            }

            return $postulante;
        });

        return redirect()->route('preinscripcion.constancia', $postulante->token_seguimiento);
    }

    /** Constancia con el codigo de seguimiento. */
    public function constancia(string $token)
    {
        $postulante = Postulante::with(['sede', 'grado', 'anioEscolar'])
            ->where('token_seguimiento', $token)
            ->firstOrFail();

        return view('sitio.preinscripcion_constancia', [
            'institucion' => $this->institucion(),
            'postulante'  => $postulante,
        ]);
    }

    /** Consulta del estado con codigo + DNI del postulante. */
    public function consulta(Request $request)
    {
        $codigo = trim((string) $request->query('codigo', ''));
        $dni    = trim((string) $request->query('dni', ''));
        $buscado = $codigo !== '' && $dni !== '';

        $postulante = $buscado
            ? Postulante::with(['sede', 'grado', 'anioEscolar'])
                ->where('codigo', $codigo)
                ->where('dni', $dni)
                ->first()
            : null;

        return view('sitio.preinscripcion_consulta', [
            'institucion' => $this->institucion(),
            'postulante'  => $postulante,
            'codigo'      => $codigo,
            'dni'         => $dni,
            'buscado'     => $buscado,
        ]);
    }

    // -- Apoyo ---------------------------------------------------------

    private function validar(Request $request): array
    {
        $data = $request->validate([
            // Postulante
            'nombres'             => ['required', 'string', 'max:120'],
            'apellidos'           => ['required', 'string', 'max:120'],
            'dni'                 => ['required', 'digits:8'],
            'fecha_nacimiento'    => ['required', 'date', 'before:today'],
            'sexo'                => ['required', 'in:M,F'],
            'direccion'           => ['nullable', 'string', 'max:255'],
            'colegio_procedencia' => ['nullable', 'string', 'max:180'],
            'nivel'               => ['required', 'in:' . implode(',', self::NIVELES)],
            'grado_id'            => ['required', 'exists:grados,id'],
            'sede_id'             => ['required', 'exists:sedes,id'],

            // Apoderado que presenta la pre-inscripcion
            'apoderado_parentesco' => ['required', 'in:Madre,Padre,Tutor'],
            'apoderado_nombres'    => ['required', 'string', 'max:120'],
            'apoderado_apellidos'  => ['required', 'string', 'max:120'],
            'apoderado_dni'        => ['required', 'digits:8'],
            'apoderado_telefono'   => ['required', 'string', 'max:40'],
            'apoderado_email'      => ['required', 'email', 'max:150'],
            'apoderado_direccion'  => ['nullable', 'string', 'max:255'],

            'observacion'     => ['nullable', 'string', 'max:500'],
            'acepta_terminos' => ['accepted'],
        ], [
            'acepta_terminos.accepted' => 'Debes aceptar el tratamiento de datos para continuar.',
            'dni.digits'               => 'El DNI del postulante debe tener 8 digitos.',
            'apoderado_dni.digits'     => 'El DNI del apoderado debe tener 8 digitos.',
        ]);

        $data['acepta_terminos'] = true;

        // El grado elegido tiene que pertenecer al nivel marcado.
        $grado = Grado::find($data['grado_id']);

        if ($grado && $grado->nivel !== $data['nivel']) {
            throw ValidationException::withMessages([
                'grado_id' => 'El grado elegido no corresponde al nivel ' . $data['nivel'] . '.',
            ]);
        }

        foreach (['direccion', 'colegio_procedencia', 'apoderado_direccion', 'observacion'] as $campo) {
            $data[$campo] = $data[$campo] ?? null;
        }

        return $data;
    }

    /** Una misma persona no se pre-inscribe dos veces al mismo anio. */
    private function rechazarDuplicado(AnioEscolar $campana, string $dni): void
    {
        $existente = Postulante::where('anio_escolar_id', $campana->id)
            ->where('dni', $dni)
            ->first();

        if (!$existente) {
            return;
        }

        throw ValidationException::withMessages([
            'dni' => "Ya existe una postulacion para el DNI {$dni} en el {$campana->nombre} "
                . "(codigo {$existente->codigo}). Comunicate con el colegio si necesitas modificarla.",
        ]);
    }

    private function sedeAtiendeNivel(?int $sedeId, string $nivel): ?string
    {
        if (!$sedeId) {
            return null;
        }

        $sede = Sede::find($sedeId);

        return $sede && !$sede->atiendeNivel($nivel)
            ? $sede->nombre . ' no atiende el nivel ' . $nivel . '.'
            : null;
    }

    private function institucion(): Institucion
    {
        return Institucion::first() ?? new Institucion(['nombre' => 'Real School']);
    }
}
