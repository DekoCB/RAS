<?php

namespace App\Http\Controllers\Auxiliar;

use App\Http\Controllers\Controller;
use App\Support\CarnetCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CarnetController extends Controller
{
    public function show(Request $request)
    {
        $user = Auth::user();

        if (empty($user->carnet_codigo)) {
            $user->carnet_codigo = CarnetCode::generateUserCode($user);
            $user->save();
        }

        return view('auxiliar.carnet', compact('user'));
    }
}
