<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sede;
use App\Models\SedeDocumento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * Sedes o locales del colegio y sus documentos publicables.
 *
 * Cubre RF-08 (pagina por local con portada y PDF descargables),
 * RF-09 (lista de utiles por nivel), RF-10 (contacto y ubicacion)
 * y RF-20 (reglamentos vigentes por anio).
 */
class SedeController extends Controller
{
    private const NIVELES = ['Inicial', 'Primaria', 'Secundaria'];

    public function index()
    {
        $sedes = Sede::withCount('documentos')->orderBy('orden')->get();

        return view('admin.sedes.index', [
            'sedes'   => $sedes,
            'niveles' => self::NIVELES,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validar($request);

        $sede = new Sede($data);
        $sede->imagen_portada = $this->guardarPortada($request);
        $sede->save();

        return back()->with('status', 'Sede registrada.');
    }

    public function update(Request $request, Sede $sede)
    {
        $data = $this->validar($request);

        $sede->fill($data);

        if ($nueva = $this->guardarPortada($request)) {
            $this->borrarArchivo($sede->imagen_portada);
            $sede->imagen_portada = $nueva;
        }

        $sede->save();

        return back()->with('status', 'Sede actualizada.');
    }

    public function destroy(Sede $sede)
    {
        // Los documentos se borran en cascada; hay que limpiar sus archivos antes.
        foreach ($sede->documentos as $documento) {
            $this->borrarArchivo($documento->archivo);
        }
        $this->borrarArchivo($sede->imagen_portada);

        $sede->delete();

        return back()->with('status', 'Sede eliminada.');
    }

    public function documentos(Sede $sede)
    {
        $documentos = $sede->documentos()->orderByDesc('anio')->orderBy('tipo')->get();

        return view('admin.sedes.documentos', [
            'sede'       => $sede,
            'documentos' => $documentos,
            'tipos'      => SedeDocumento::TIPOS,
            'niveles'    => self::NIVELES,
        ]);
    }

    public function subirDocumento(Request $request, Sede $sede)
    {
        $data = $request->validate([
            'tipo'      => ['required', 'string', 'in:' . implode(',', array_keys(SedeDocumento::TIPOS))],
            'titulo'    => ['required', 'string', 'max:180'],
            'anio'      => ['required', 'integer', 'min:2000', 'max:2100'],
            'nivel'     => ['nullable', 'string', 'in:' . implode(',', self::NIVELES)],
            'archivo'   => ['required', 'file', 'mimes:pdf', 'max:10240'],
            'publicado' => ['nullable', 'boolean'],
        ]);

        $sede->documentos()->create([
            'tipo'       => $data['tipo'],
            'titulo'     => $data['titulo'],
            'anio'       => $data['anio'],
            'nivel'      => $data['nivel'] ?? null,
            'archivo'    => $request->file('archivo')->store('sedes/documentos', 'public'),
            'publicado'  => $request->boolean('publicado', true),
            'subido_por' => Auth::id(),
        ]);

        return back()->with('status', 'Documento publicado.');
    }

    public function eliminarDocumento(SedeDocumento $documento)
    {
        $this->borrarArchivo($documento->archivo);
        $documento->delete();

        return back()->with('status', 'Documento eliminado.');
    }

    private function validar(Request $request): array
    {
        $data = $request->validate([
            'nombre'     => ['required', 'string', 'max:120'],
            'direccion'  => ['required', 'string', 'max:255'],
            'referencia' => ['nullable', 'string', 'max:255'],
            'telefono'   => ['nullable', 'string', 'max:40'],
            'niveles'    => ['nullable', 'array'],
            'niveles.*'  => ['string', 'in:' . implode(',', self::NIVELES)],
            'mapa_url'   => ['nullable', 'url', 'max:500'],
            'orden'      => ['nullable', 'integer', 'min:0'],
            'activo'     => ['nullable', 'boolean'],
            'portada'    => ['nullable', 'image', 'max:4096'],
        ]);

        $data['niveles'] = $data['niveles'] ?? [];
        $data['orden']   = $data['orden'] ?? 0;
        $data['activo']  = $request->boolean('activo', true);

        unset($data['portada']);

        return $data;
    }

    private function guardarPortada(Request $request): ?string
    {
        return $request->hasFile('portada')
            ? $request->file('portada')->store('sedes', 'public')
            : null;
    }

    private function borrarArchivo(?string $ruta): void
    {
        if ($ruta && Storage::disk('public')->exists($ruta)) {
            Storage::disk('public')->delete($ruta);
        }
    }
}
