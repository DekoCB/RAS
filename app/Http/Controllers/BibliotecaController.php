<?php

namespace App\Http\Controllers;

use App\Models\BibliotecaLectura;
use App\Models\BibliotecaLibro;
use App\Models\Estudiante;
use App\Models\Grado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * MODULO 7 - Biblioteca.
 *
 * RF-45: seguimiento de la lectura de los estudiantes por grado en los
 *        tres niveles.
 * RF-46: ficha de resumen con autor, tiempo de lectura, editorial y resumen.
 */
class BibliotecaController extends Controller
{
    public function index(Request $request)
    {
        $nivel   = $request->query('nivel');
        $gradoId = $request->query('grado_id');

        // Matricula guarda el grado directamente, asi que el filtro por grado
        // y por nivel se resuelve sobre grado_id sin pasar por seccion.
        $lecturas = BibliotecaLectura::with(['estudiante', 'libro'])
            ->when($gradoId, fn ($q) => $q->whereHas(
                'estudiante.matriculas',
                fn ($m) => $m->where('grado_id', $gradoId)
            ))
            ->when($nivel && !$gradoId, fn ($q) => $q->whereHas(
                'estudiante.matriculas',
                fn ($m) => $m->whereIn('grado_id', Grado::where('nivel', $nivel)->pluck('id'))
            ))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $resumen = [
            'total'      => BibliotecaLectura::count(),
            'en_curso'   => BibliotecaLectura::where('estado', 'en_curso')->count(),
            'terminadas' => BibliotecaLectura::where('estado', 'terminado')->count(),
            'libros'     => BibliotecaLibro::where('activo', true)->count(),
        ];

        return view('biblioteca.index', [
            'lecturas' => $lecturas,
            'resumen'  => $resumen,
            'grados'   => Grado::orderBy('orden')->get(),
            'nivel'    => $nivel,
            'gradoId'  => $gradoId,
        ]);
    }

    // ── Catalogo de libros ────────────────────────────────────────────

    public function libros(Request $request)
    {
        $buscar = trim((string) $request->query('q', ''));

        $libros = BibliotecaLibro::with('grado')
            ->when($buscar !== '', function ($q) use ($buscar) {
                $q->where(function ($sub) use ($buscar) {
                    $sub->where('titulo', 'like', "%{$buscar}%")
                        ->orWhere('autor', 'like', "%{$buscar}%")
                        ->orWhere('editorial', 'like', "%{$buscar}%");
                });
            })
            ->orderBy('titulo')
            ->paginate(15)
            ->withQueryString();

        return view('biblioteca.libros', [
            'libros' => $libros,
            'grados' => Grado::orderBy('orden')->get(),
            'buscar' => $buscar,
        ]);
    }

    public function guardarLibro(Request $request)
    {
        $data = $request->validate([
            'id'         => ['nullable', 'exists:biblioteca_libros,id'],
            'titulo'     => ['required', 'string', 'max:200'],
            'autor'      => ['nullable', 'string', 'max:150'],
            'editorial'  => ['nullable', 'string', 'max:150'],
            'codigo'     => ['nullable', 'string', 'max:60'],
            'grado_id'   => ['nullable', 'exists:grados,id'],
            'ejemplares' => ['required', 'integer', 'min:1', 'max:9999'],
        ]);

        // El codigo es unico: se valida contra el resto de filas, no contra si misma.
        if (!empty($data['codigo'])) {
            $duplicado = BibliotecaLibro::where('codigo', $data['codigo'])
                ->when($data['id'] ?? null, fn ($q, $id) => $q->where('id', '!=', $id))
                ->exists();

            if ($duplicado) {
                return back()->withInput()->withErrors(['codigo' => 'Ese codigo ya esta en uso.']);
            }
        }

        // El nivel se deriva del grado; validate() no devuelve las claves
        // opcionales ausentes, de ahi el ?? null.
        $gradoId = $data['grado_id'] ?? null;
        $nivel   = $gradoId ? Grado::find($gradoId)?->nivel : null;

        BibliotecaLibro::updateOrCreate(
            ['id' => $data['id'] ?? null],
            [
                'titulo'     => $data['titulo'],
                'autor'      => $data['autor'] ?? null,
                'editorial'  => $data['editorial'] ?? null,
                'codigo'     => $data['codigo'] ?? null,
                'grado_id'   => $gradoId,
                'nivel'      => $nivel,
                'ejemplares' => $data['ejemplares'],
                'activo'     => true,
            ]
        );

        return back()->with('status', 'Libro guardado.');
    }

    public function eliminarLibro(BibliotecaLibro $libro)
    {
        $libro->delete();

        return back()->with('status', 'Libro eliminado del catalogo.');
    }

    // ── Fichas de lectura (RF-45, RF-46) ──────────────────────────────

    public function crearLectura()
    {
        return view('biblioteca.lectura', [
            'lectura'     => new BibliotecaLectura(),
            'estudiantes' => $this->estudiantes(),
            'libros'      => BibliotecaLibro::where('activo', true)->orderBy('titulo')->get(),
        ]);
    }

    public function editarLectura(BibliotecaLectura $lectura)
    {
        return view('biblioteca.lectura', [
            'lectura'     => $lectura,
            'estudiantes' => $this->estudiantes(),
            'libros'      => BibliotecaLibro::where('activo', true)->orderBy('titulo')->get(),
        ]);
    }

    public function guardarLectura(Request $request, ?BibliotecaLectura $lectura = null)
    {
        $data = $request->validate([
            'estudiante_id'  => ['required', 'exists:estudiantes,id'],
            'libro_id'       => ['nullable', 'exists:biblioteca_libros,id'],
            'titulo'         => ['required', 'string', 'max:200'],
            'autor'          => ['nullable', 'string', 'max:150'],
            'editorial'      => ['nullable', 'string', 'max:150'],
            'tiempo_lectura' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'resumen'        => ['nullable', 'string'],
            'fecha_inicio'   => ['nullable', 'date'],
            'fecha_fin'      => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'estado'         => ['required', 'in:en_curso,terminado'],
        ]);

        $data['registrado_por'] = Auth::id();

        if ($lectura && $lectura->exists) {
            $lectura->update($data);
            $mensaje = 'Ficha de lectura actualizada.';
        } else {
            $lectura = BibliotecaLectura::create($data);
            $mensaje = 'Ficha de lectura registrada.';
        }

        return redirect()->route('biblioteca.index')->with('status', $mensaje);
    }

    public function eliminarLectura(BibliotecaLectura $lectura)
    {
        $lectura->delete();

        return back()->with('status', 'Ficha de lectura eliminada.');
    }

    private function estudiantes()
    {
        return Estudiante::where('estado', 'activo')
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->get(['id', 'nombres', 'apellidos', 'dni']);
    }
}
