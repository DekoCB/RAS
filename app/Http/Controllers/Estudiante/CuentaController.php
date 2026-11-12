<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CuentaController extends Controller
{
    public function password(Request $request)
    {
        return view('estudiante.password');
    }
}
