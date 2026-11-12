<?php

namespace App\Http\Controllers\Secretaria;

use App\Http\Controllers\Controller;
use App\Models\AnioEscolar;
use App\Models\AsistenciaSecretaria;
use App\Models\User;
use App\Support\CarnetCode;
use Illuminate\Support\Facades\Auth;

class SecretariaCarnetController extends Controller
{
    public function miCarnet()
    {
        /** @var User $user */
        $user = Auth::user();

        if (empty($user->carnet_codigo)) {
            $user->carnet_codigo = CarnetCode::generateUserCode($user);
            $user->save();
        }

        return view('secretaria.mi_carnet.index', compact('user'));
    }

    public function misAsistencias()
    {
        /** @var User $user */
        $user = Auth::user();
        $anio = AnioEscolar::where('activo', true)->first();

        $asistencias = AsistenciaSecretaria::where('user_id', $user->id)
            ->when($anio, fn($q) => $q->where('anio_escolar_id', $anio->id))
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->paginate(20);

        return view('secretaria.mis_asistencias.index', compact('user', 'asistencias', 'anio'));
    }
}
