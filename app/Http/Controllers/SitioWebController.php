<?php

namespace App\Http\Controllers;

use App\Models\AnioEscolar;
use App\Models\HorarioInstitucional;
use App\Models\Institucion;
use App\Models\InstitucionFrase;
use App\Models\InstitucionValor;
use App\Models\Sede;
use App\Support\BannerAdmision;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\File;

/**
 * Portada publica del colegio. Toda la informacion sale de las tablas
 * institucionales (RF-03, RF-04, RF-10), asi que lo que el panel edite
 * se refleja aqui sin tocar la vista.
 */
class SitioWebController extends Controller
{
    public function inicio(): View
    {
        $institucion = Institucion::first() ?? new Institucion(['nombre' => 'R.A.S']);

        // Campana de admision publicada: define si el bloque de pre-inscripcion
        // invita a postular o solo muestra los datos de contacto.
        $campanaAdmision = AnioEscolar::campanaAdmision();
        $bannerAdmision  = $campanaAdmision ? BannerAdmision::ruta() : null;

        $valores = InstitucionValor::where('activo', true)
            ->orderBy('orden')
            ->get();

        $frases = InstitucionFrase::where('activo', true)
            ->orderBy('orden')
            ->get();

        $sedes = Sede::where('activo', true)
            ->orderBy('orden')
            ->get();

        $horarios = HorarioInstitucional::where('activo', true)
            ->with('grado')
            ->get();

        // Horario general por nivel (las filas sin grado) y salidas escalonadas.
        $horariosNivel = $horarios->whereNull('grado_id')->keyBy('nivel');

        $salidas = $horarios->filter(fn ($h) => $h->grado_id && $h->hora_salida)
            ->sortBy(fn ($h) => $h->hora_salida)
            ->values();

        $fotos = collect(File::exists(public_path('images/galeria')) ? File::files(public_path('images/galeria')) : [])
            ->filter(fn ($archivo) => in_array(strtolower($archivo->getExtension()), ['jpg', 'jpeg', 'png', 'webp']))
            ->sortBy(fn ($archivo) => $archivo->getFilename())
            ->map(fn ($archivo) => 'images/galeria/' . $archivo->getFilename())
            ->values();

        // Videos institucionales publicados en TikTok (@ras). Se
        // incrustan con el embed oficial de TikTok en vez de subir el
        // archivo de video al servidor.
        $videosTiktok = [
            '7682123687289457938',
            '7671122190204538119',
            '7679537786722028818',
            '7620617682273979668',
            '7616539674604997896',
            '7610274362003868936',
            '7610207669688864018',
        ];

        return view('sitio.inicio', compact(
            'institucion',
            'campanaAdmision',
            'bannerAdmision',
            'valores',
            'frases',
            'sedes',
            'horariosNivel',
            'salidas',
            'fotos',
            'videosTiktok'
        ));
    }
}
