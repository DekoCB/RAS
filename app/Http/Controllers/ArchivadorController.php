<?php

namespace App\Http\Controllers;

use App\Models\Archivo;
use App\Models\Carpeta;
use App\Support\VistaPreviaArchivo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Archivador del colegio.
 *
 * Carpetas creadas libremente por la direccion, con subcarpetas, y subida
 * de carpetas enteras del PC conservando su estructura.
 *
 * Los archivos se guardan en el disco privado (storage/app), nunca bajo
 * public/, y solo se sirven por las rutas de este controlador, que estan
 * detras de la sesion y del rol.
 */
class ArchivadorController extends Controller
{
    /** El navegador manda las carpetas en tandas; ver el script de la vista. */
    private const DISCO = 'local';

    /** Extensiones que no se aceptan por el riesgo que suponen en el servidor. */
    private const PROHIBIDAS = [
        'php', 'phtml', 'php3', 'php4', 'php5', 'phar', 'exe', 'msi', 'bat',
        'cmd', 'com', 'scr', 'sh', 'bash', 'ps1', 'vbs', 'js', 'jar', 'dll',
    ];

    public function index(Request $request, ?Carpeta $carpeta = null)
    {
        $buscar = trim((string) $request->query('q', ''));

        // Buscar recorre todo el archivador; sin busqueda se ve una carpeta.
        if ($buscar !== '') {
            $archivos = Archivo::with('carpeta')
                ->where('nombre', 'like', "%{$buscar}%")
                ->orderBy('nombre')
                ->paginate(50)
                ->withQueryString();

            $subcarpetas = Carpeta::where('nombre', 'like', "%{$buscar}%")
                ->orderBy('nombre')
                ->get();
        } else {
            $archivos = Archivo::with('autor')
                ->where('carpeta_id', $carpeta?->id)
                ->orderBy('nombre')
                ->paginate(50);

            $subcarpetas = Carpeta::where('parent_id', $carpeta?->id)
                ->withCount(['subcarpetas', 'archivos'])
                ->orderBy('nombre')
                ->get();
        }

        return view('archivador.index', [
            'carpeta'     => $carpeta,
            'cadena'      => $carpeta ? $carpeta->cadena() : [],
            'subcarpetas' => $subcarpetas,
            'archivos'    => $archivos,
            'buscar'      => $buscar,
            'resumen'     => [
                'carpetas' => Carpeta::count(),
                'archivos' => Archivo::count(),
                'peso'     => (int) Archivo::sum('tamano'),
            ],
            'limiteMb'    => $this->limiteMb(),
        ]);
    }

    // ── Carpetas ──────────────────────────────────────────────────────

    public function crearCarpeta(Request $request, ?Carpeta $carpeta = null)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:120'],
        ]);

        $nombre = $this->limpiarNombre($data['nombre']);

        if ($nombre === '') {
            return back()->withErrors(['nombre' => 'El nombre de la carpeta no es valido.']);
        }

        if ($this->carpetaHermanaExiste($carpeta?->id, $nombre)) {
            return back()->withErrors(['nombre' => "Ya existe una carpeta llamada \"{$nombre}\" aqui."]);
        }

        Carpeta::create([
            'nombre'     => $nombre,
            'parent_id'  => $carpeta?->id,
            'creado_por' => Auth::id(),
        ]);

        return back()->with('status', "Carpeta \"{$nombre}\" creada.");
    }

    public function renombrarCarpeta(Request $request, Carpeta $carpeta)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:120'],
        ]);

        $nombre = $this->limpiarNombre($data['nombre']);

        if ($nombre === '') {
            return back()->withErrors(['nombre' => 'El nombre de la carpeta no es valido.']);
        }

        if ($this->carpetaHermanaExiste($carpeta->parent_id, $nombre, $carpeta->id)) {
            return back()->withErrors(['nombre' => "Ya existe una carpeta llamada \"{$nombre}\" aqui."]);
        }

        $carpeta->update(['nombre' => $nombre]);

        return back()->with('status', 'Carpeta renombrada.');
    }

    public function eliminarCarpeta(Carpeta $carpeta)
    {
        $padre = $carpeta->parent_id;

        // Los archivos de toda la rama salen del disco antes de que la
        // cascada de la base borre las filas.
        foreach (Archivo::whereIn('carpeta_id', $carpeta->ramaIds())->get() as $archivo) {
            Storage::disk(self::DISCO)->delete($archivo->ruta);
        }

        $nombre = $carpeta->nombre;
        $carpeta->delete();

        return redirect()
            ->route('archivador.index', $padre ? ['carpeta' => $padre] : [])
            ->with('status', "Carpeta \"{$nombre}\" eliminada con todo su contenido.");
    }

    // ── Archivos ──────────────────────────────────────────────────────

    /**
     * Recibe una tanda de archivos. La vista las manda de a pocos porque
     * `max_file_uploads` de PHP corta los envios grandes (aqui, 20).
     *
     * `rutas[]` llega en paralelo a `archivos[]`: trae la ruta relativa de
     * cada archivo dentro de la carpeta que el usuario eligio, y es lo que
     * permite recrear el arbol.
     */
    public function subir(Request $request, ?Carpeta $carpeta = null): JsonResponse
    {
        $request->validate([
            'archivos'   => ['required', 'array'],
            'archivos.*' => ['required', 'file', 'max:' . ($this->limiteMb() * 1024)],
            'rutas'      => ['nullable', 'array'],
        ]);

        $rutas = $request->input('rutas', []);
        $guardados = 0;
        $rechazados = [];

        foreach ($request->file('archivos') as $i => $subido) {
            $nombre = $subido->getClientOriginalName();
            $extension = strtolower($subido->getClientOriginalExtension());

            if (in_array($extension, self::PROHIBIDAS, true)) {
                $rechazados[] = $nombre . ' (tipo no permitido)';
                continue;
            }

            // La ruta relativa decide en que subcarpeta cae el archivo.
            $destino = isset($rutas[$i]) && $rutas[$i] !== ''
                ? $this->carpetaDeRuta((string) $rutas[$i], $carpeta)
                : $carpeta;

            $ruta = $subido->store('archivador', self::DISCO);

            Archivo::create([
                'carpeta_id' => $destino?->id,
                'nombre'     => $this->nombreLibre($destino?->id, $nombre),
                'ruta'       => $ruta,
                'extension'  => $extension,
                'mime'       => $subido->getClientMimeType(),
                'tamano'     => $subido->getSize(),
                'subido_por' => Auth::id(),
            ]);

            $guardados++;
        }

        return response()->json([
            'guardados'  => $guardados,
            'rechazados' => $rechazados,
        ]);
    }

    public function descargar(Archivo $archivo): StreamedResponse
    {
        abort_unless(Storage::disk(self::DISCO)->exists($archivo->ruta), 404);

        return Storage::disk(self::DISCO)->download($archivo->ruta, $archivo->nombre);
    }

    /**
     * Vista previa de cualquier archivo. Lo que el navegador muestra solo
     * (PDF, imagen, video, audio) se sirve por la ruta `ver`; del resto se
     * devuelve el contenido ya convertido a HTML.
     */
    public function previsualizar(Archivo $archivo): JsonResponse
    {
        if (!Storage::disk(self::DISCO)->exists($archivo->ruta)) {
            return response()->json([
                'modo'  => 'no',
                'aviso' => 'El archivo ya no esta en el servidor.',
            ]);
        }

        $vista = VistaPreviaArchivo::generar(
            $archivo,
            Storage::disk(self::DISCO)->path($archivo->ruta)
        );

        return response()->json($vista + [
            'nombre' => $archivo->nombre,
            'peso'   => $archivo->pesoLegible(),
            'tipo'   => $archivo->tipo(),
            'url'    => route('archivador.archivos.ver', $archivo),
            'bajar'  => route('archivador.archivos.descargar', $archivo),
        ]);
    }

    /** Abre el archivo en el navegador (PDF, imagen, video) en vez de bajarlo. */
    public function ver(Archivo $archivo): StreamedResponse
    {
        abort_unless(Storage::disk(self::DISCO)->exists($archivo->ruta), 404);
        abort_unless($archivo->seVePorDentro(), 404);

        return Storage::disk(self::DISCO)->response($archivo->ruta, $archivo->nombre, [
            'Content-Type'        => $archivo->mime ?: 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="' . $archivo->nombre . '"',
        ]);
    }

    public function renombrarArchivo(Request $request, Archivo $archivo)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:180'],
        ]);

        $nombre = $this->limpiarNombre($data['nombre']);

        if ($nombre === '') {
            return back()->withErrors(['nombre' => 'El nombre del archivo no es valido.']);
        }

        // La extension real manda: renombrar no cambia el tipo del archivo.
        if ($archivo->extension && !Str::endsWith(strtolower($nombre), '.' . $archivo->extension)) {
            $nombre .= '.' . $archivo->extension;
        }

        $archivo->update(['nombre' => $nombre]);

        return back()->with('status', 'Archivo renombrado.');
    }

    public function eliminarArchivo(Archivo $archivo)
    {
        Storage::disk(self::DISCO)->delete($archivo->ruta);
        $nombre = $archivo->nombre;
        $archivo->delete();

        return back()->with('status', "\"{$nombre}\" eliminado.");
    }

    // ── Apoyo ─────────────────────────────────────────────────────────

    /**
     * Convierte "2026/Actas/enero/acta.pdf" en carpetas reales y devuelve
     * la ultima, creando por el camino lo que falte.
     */
    private function carpetaDeRuta(string $rutaRelativa, ?Carpeta $base): ?Carpeta
    {
        $partes = array_values(array_filter(
            array_map([$this, 'limpiarNombre'], explode('/', str_replace('\\', '/', $rutaRelativa))),
            fn ($p) => $p !== ''
        ));

        // El ultimo tramo es el archivo, no una carpeta.
        array_pop($partes);

        $actual = $base;

        foreach ($partes as $nombre) {
            $actual = Carpeta::firstOrCreate(
                ['parent_id' => $actual?->id, 'nombre' => $nombre],
                ['creado_por' => Auth::id()]
            );
        }

        return $actual;
    }

    /**
     * Un nombre repetido en la misma carpeta pasa a "acta (2).pdf" en vez de
     * confundirse con el que ya estaba.
     */
    private function nombreLibre(?int $carpetaId, string $nombre): string
    {
        $nombre = $this->limpiarNombre($nombre) ?: 'archivo';

        if (!$this->archivoHermanoExiste($carpetaId, $nombre)) {
            return $nombre;
        }

        $extension = pathinfo($nombre, PATHINFO_EXTENSION);
        $base = $extension ? Str::beforeLast($nombre, '.' . $extension) : $nombre;
        $sufijo = $extension ? '.' . $extension : '';

        for ($i = 2; $i < 500; $i++) {
            $intento = "{$base} ({$i}){$sufijo}";

            if (!$this->archivoHermanoExiste($carpetaId, $intento)) {
                return $intento;
            }
        }

        return "{$base} (" . Str::random(6) . "){$sufijo}";
    }

    private function archivoHermanoExiste(?int $carpetaId, string $nombre): bool
    {
        return Archivo::where('carpeta_id', $carpetaId)->where('nombre', $nombre)->exists();
    }

    private function carpetaHermanaExiste(?int $parentId, string $nombre, ?int $excepto = null): bool
    {
        return Carpeta::where('parent_id', $parentId)
            ->where('nombre', $nombre)
            ->when($excepto, fn ($q) => $q->where('id', '!=', $excepto))
            ->exists();
    }

    /**
     * El nombre lo escribe (o lo trae) el usuario, asi que se le quitan los
     * separadores y los ".." antes de tocar nada.
     */
    private function limpiarNombre(string $nombre): string
    {
        $nombre = str_replace(['/', '\\', "\0"], '', trim($nombre));
        $nombre = preg_replace('/\.{2,}/', '.', $nombre);
        $nombre = trim($nombre, '. ');

        return mb_substr($nombre, 0, 180);
    }

    /** Tope por archivo: lo que PHP acepte, sin pasar de 40 MB. */
    private function limiteMb(): int
    {
        $aMb = function (string $valor): int {
            $valor = trim($valor);
            $unidad = strtolower(substr($valor, -1));
            $numero = (int) $valor;

            return match ($unidad) {
                'g' => $numero * 1024,
                'm' => $numero,
                'k' => (int) ceil($numero / 1024),
                default => (int) ceil($numero / 1048576),
            };
        };

        return max(1, min(
            40,
            $aMb((string) ini_get('upload_max_filesize')),
            $aMb((string) ini_get('post_max_size'))
        ));
    }
}
