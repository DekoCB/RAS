<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardRedirectController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->hasRole('SuperAdmin')) {
            return redirect()->route('admin-sistema.dashboard');
        }

        if ($user->hasRole('Director')) {
            return redirect()->route('director.dashboard');
        }

        if ($user->hasRole('Secretaria')) {
            return redirect()->route('secretaria.dashboard');
        }

        if ($user->hasRole('Auxiliar')) {
            return redirect()->route('auxiliar.dashboard');
        }

        if ($user->hasRole('Docente')) {
            return redirect()->route('docente.dashboard');
        }

        if ($user->hasRole('Estudiante')) {
            return redirect()->route('estudiante.dashboard');
        }

        abort(403, 'Tu usuario no tiene un rol asignado. Contacta con administracion.');
    }
}
