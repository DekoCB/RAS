<?php

namespace App\Http\Controllers\Secretaria;

use App\Http\Controllers\Controller;
use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\User;
use App\Support\CarnetCode;
use Illuminate\Support\Str;

class ImprimirQrController extends Controller
{
    public function index()
    {
        return view('secretaria.imprimir_qr.index');
    }

    public function estudiantes()
    {
        $estudiantes = Estudiante::with(['matriculas' => function ($q) {
            $q->whereIn('estado', ['registrada', 'activa'])
                ->with(['grado', 'seccion'])
                ->latest();
        }])
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->get();

        // Auto-generar carnet_codigo para los que no tienen
        foreach ($estudiantes as $est) {
            if (empty($est->carnet_codigo)) {
                do {
                    $codigo = 'JP-' . strtoupper(Str::random(8));
                } while (Estudiante::where('carnet_codigo', $codigo)->exists());
                $est->carnet_codigo = $codigo;
                $est->save();
            }
        }

        $personas = $estudiantes->map(function ($e) {
            $matricula = $e->matriculas->first();
            $grado = $matricula?->grado?->nombre ?? '';
            $seccion = $matricula?->seccion?->nombre ?? '';
            $subtitulo = trim("$grado $seccion") ?: 'Estudiante';
            return [
                'nombre'    => strtoupper(trim($e->apellidos . ' ' . $e->nombres)),
                'codigo'    => strtoupper(trim($e->carnet_codigo)),
                'subtitulo' => $subtitulo,
                'rol'       => 'Estudiante',
                'qr'        => $this->generarQr($e->carnet_codigo),
            ];
        });

        return view('secretaria.imprimir_qr.print', [
            'tipo'    => 'Estudiantes',
            'personas' => $personas,
        ]);
    }

    public function docentes()
    {
        $docentes = Docente::orderBy('apellidos')->orderBy('nombres')->get();

        foreach ($docentes as $doc) {
            if (empty($doc->carnet_codigo)) {
                $doc->carnet_codigo = CarnetCode::generateDocenteCode($doc);
                $doc->save();
            }
        }

        $personas = $docentes->map(fn ($d) => [
            'nombre'    => strtoupper(trim($d->apellidos . ' ' . $d->nombres)),
            'codigo'    => strtoupper(trim($d->carnet_codigo)),
            'subtitulo' => $d->especialidad ?? 'Docente',
            'rol'       => 'Docente',
            'qr'        => $this->generarQr($d->carnet_codigo),
        ]);

        return view('secretaria.imprimir_qr.print', [
            'tipo'    => 'Docentes',
            'personas' => $personas,
        ]);
    }

    public function secretaria()
    {
        $users = User::whereNotNull('carnet_codigo')
            ->where('carnet_codigo', '!=', '')
            ->role(['Secretaria'])
            ->orderBy('name')
            ->get();

        // También incluir los que no tienen carnet_codigo
        $sinCarnet = User::role(['Secretaria'])
            ->where(function ($q) {
                $q->whereNull('carnet_codigo')->orWhere('carnet_codigo', '');
            })
            ->orderBy('name')
            ->get();

        foreach ($sinCarnet as $u) {
            $u->carnet_codigo = CarnetCode::generateUserCode($u);
            $u->save();
        }

        $todos = $users->merge($sinCarnet)->sortBy('name');

        $personas = $todos->map(fn ($u) => [
            'nombre'    => strtoupper(trim($u->name)),
            'codigo'    => strtoupper(trim($u->carnet_codigo)),
            'subtitulo' => 'Secretaria',
            'rol'       => 'Secretaria',
            'qr'        => $this->generarQr($u->carnet_codigo),
        ]);

        return view('secretaria.imprimir_qr.print', [
            'tipo'    => 'Secretaria',
            'personas' => $personas,
        ]);
    }

    public function auxiliar()
    {
        $users = User::whereNotNull('carnet_codigo')
            ->where('carnet_codigo', '!=', '')
            ->role(['Auxiliar'])
            ->orderBy('name')
            ->get();

        $sinCarnet = User::role(['Auxiliar'])
            ->where(function ($q) {
                $q->whereNull('carnet_codigo')->orWhere('carnet_codigo', '');
            })
            ->orderBy('name')
            ->get();

        foreach ($sinCarnet as $u) {
            $u->carnet_codigo = CarnetCode::generateUserCode($u);
            $u->save();
        }

        $todos = $users->merge($sinCarnet)->sortBy('name');

        $personas = $todos->map(fn ($u) => [
            'nombre'    => strtoupper(trim($u->name)),
            'codigo'    => strtoupper(trim($u->carnet_codigo)),
            'subtitulo' => 'Auxiliar',
            'rol'       => 'Auxiliar',
            'qr'        => $this->generarQr($u->carnet_codigo),
        ]);

        return view('secretaria.imprimir_qr.print', [
            'tipo'    => 'Auxiliar',
            'personas' => $personas,
        ]);
    }

    public function carnetUsuario(User $user): mixed
    {
        $rol = $user->getRoleNames()->first() ?? 'Usuario';

        // Para Docentes el código de carnet está en el modelo Docente
        if ($rol === 'Docente' && $user->docente) {
            $docente = $user->docente;
            if (empty($docente->carnet_codigo)) {
                $docente->carnet_codigo = CarnetCode::generateDocenteCode($docente);
                $docente->save();
            }
            $codigo  = $docente->carnet_codigo;
            $nombre  = strtoupper(trim($docente->apellidos . ' ' . $docente->nombres));
            $subtitulo = $docente->especialidad ?? 'Docente';
        } else {
            // Director, Secretaria, Auxiliar → código en User
            if (empty($user->carnet_codigo)) {
                $user->carnet_codigo = CarnetCode::generateUserCode($user);
                $user->save();
            }
            $codigo    = $user->carnet_codigo;
            $nombre    = strtoupper(trim($user->name));
            $subtitulo = $rol;
        }

        $personas = collect([[
            'nombre'    => $nombre,
            'codigo'    => strtoupper(trim($codigo)),
            'subtitulo' => $subtitulo,
            'rol'       => $rol,
            'qr'        => $this->generarQr($codigo),
        ]]);

        return view('secretaria.imprimir_qr.print', [
            'tipo'    => $rol,
            'personas' => $personas,
        ]);
    }

    private function generarQr(string $codigo): ?string
    {
        try {
            $svg = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
                ->size(200)
                ->margin(1)
                ->generate(strtoupper(trim($codigo)));
            return 'data:image/svg+xml;base64,' . base64_encode($svg);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
