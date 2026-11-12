<?php

namespace App\Http\Controllers;

use App\Models\Docente;
use App\Models\Documento;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * Repositorio documental compartido.
 *
 * Un mismo controlador atiende a Coordinacion Pedagogica (RF-41),
 * Recursos Humanos (RF-57, RF-61, RF-62, RF-63), Padres de familia
 * (RF-65), Documentos legales (RF-72), Defensa Civil (RF-71),
 * Inmobiliario (RF-70) y Pastoral, separados por el segmento `modulo`
 * de la ruta.
 */
class DocumentoController extends Controller
{
    public function index(Request $request, string $modulo)
    {
        $this->validarModulo($modulo);

        $categoria = $request->query('categoria');
        $anio      = $request->query('anio');
        $buscar    = trim((string) $request->query('q', ''));

        $documentos = Documento::delModulo($modulo)
            ->with('docente')
            ->when($categoria, fn ($q) => $q->where('categoria', $categoria))
            ->when($anio, fn ($q) => $q->where('anio', $anio))
            ->when($buscar !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('titulo', 'like', "%{$buscar}%")
                ->orWhere('descripcion', 'like', "%{$buscar}%")
                ->orWhere('nombre_original', 'like', "%{$buscar}%")))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        // Anios con documentos, para el filtro
        $anios = Documento::delModulo($modulo)
            ->whereNotNull('anio')->distinct()->orderByDesc('anio')->pluck('anio');

        // Cuantos documentos hay en cada carpeta
        $conteo = Documento::delModulo($modulo)
            ->selectRaw('categoria, count(*) as total')
            ->groupBy('categoria')
            ->pluck('total', 'categoria');

        return view('documentos.index', [
            'modulo'      => $modulo,
            'titulo'      => Documento::MODULOS[$modulo],
            'documentos'  => $documentos,
            'categorias'  => Documento::categoriasDe($modulo),
            'conteo'      => $conteo,
            'categoria'   => $categoria,
            'anio'        => $anio,
            'anios'       => $anios,
            'buscar'      => $buscar,
            'total'       => Documento::delModulo($modulo)->count(),
            'docentes'    => $modulo === 'rrhh' ? $this->docentes() : collect(),
        ]);
    }

    public function store(Request $request, string $modulo)
    {
        $this->validarModulo($modulo);

        $data = $request->validate([
            'categoria'    => ['required', 'string', 'in:' . implode(',', array_keys(Documento::categoriasDe($modulo)))],
            'titulo'       => ['required', 'string', 'max:180'],
            'descripcion'  => ['nullable', 'string'],
            'nivel'        => ['nullable', 'string', 'in:Inicial,Primaria,Secundaria'],
            'anio'         => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'docente_id'   => ['nullable', 'exists:docentes,id'],
            'confidencial' => ['nullable', 'boolean'],
            'archivo'      => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp,doc,docx,xls,xlsx,mp4', 'max:20480'],
        ]);

        $archivo = $request->file('archivo');

        Documento::create([
            'modulo'          => $modulo,
            'categoria'       => $data['categoria'],
            'titulo'          => $data['titulo'],
            'descripcion'     => $data['descripcion'] ?? null,
            'nivel'           => $data['nivel'] ?? null,
            'anio'            => $data['anio'] ?? now()->year,
            'docente_id'      => $data['docente_id'] ?? null,
            'confidencial'    => $request->boolean('confidencial'),
            'archivo'         => $archivo ? $archivo->store('documentos/' . $modulo, 'public') : null,
            'nombre_original' => $archivo?->getClientOriginalName(),
            'subido_por'      => Auth::id(),
        ]);

        return back()->with('status', 'Documento registrado.');
    }

    public function destroy(Documento $documento)
    {
        $modulo = $documento->modulo;

        if ($documento->archivo && Storage::disk('public')->exists($documento->archivo)) {
            Storage::disk('public')->delete($documento->archivo);
        }

        $documento->delete();

        return redirect()->route('documentos.index', $modulo)->with('status', 'Documento eliminado.');
    }

    /** RF-58: cumpleanos de los docentes, ordenados por el mes que viene. */
    public function cumpleanos()
    {
        $docentes = Docente::whereNotNull('fecha_nacimiento')
            ->where('estado', 'activo')
            ->orderByRaw('MONTH(fecha_nacimiento), DAY(fecha_nacimiento)')
            ->get(['id', 'nombres', 'apellidos', 'fecha_nacimiento', 'especialidad']);

        // Docente no castea fechas, asi que se agrupa parseando la cadena
        // en vez de anadir un cast que afectaria a las vistas existentes.
        return view('documentos.cumpleanos', [
            'docentes' => $docentes->groupBy(fn ($d) => (int) Carbon::parse($d->fecha_nacimiento)->format('n')),
            'meses'    => [
                1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                9 => 'Setiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
            ],
        ]);
    }

    private function validarModulo(string $modulo): void
    {
        abort_unless(array_key_exists($modulo, Documento::MODULOS), 404);
    }

    private function docentes()
    {
        return Docente::where('estado', 'activo')
            ->orderBy('apellidos')
            ->get(['id', 'nombres', 'apellidos']);
    }
}
