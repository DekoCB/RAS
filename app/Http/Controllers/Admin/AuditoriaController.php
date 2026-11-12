<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditoriaLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AuditoriaController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditoriaLog::query()->with('user')->orderByDesc('fecha');

        $buscar = trim((string) $request->input('buscar'));
        if ($buscar !== '') {
            $query->where(function ($q) use ($buscar) {
                $q->where('accion', 'like', "%{$buscar}%")
                    ->orWhere('tabla', 'like', "%{$buscar}%")
                    ->orWhere('ip', 'like', "%{$buscar}%")
                    ->orWhere('user_agent', 'like', "%{$buscar}%")
                    ->orWhere('registro_id', 'like', "%{$buscar}%")
                    ->orWhereHas('user', function ($userQuery) use ($buscar) {
                        $userQuery->where('name', 'like', "%{$buscar}%")
                            ->orWhere('email', 'like', "%{$buscar}%");
                    });
            });
        }

        if ($request->filled('accion')) {
            $query->where('accion', $request->input('accion'));
        }

        if ($request->filled('tabla')) {
            $query->where('tabla', $request->input('tabla'));
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        $desde = $request->input('desde');
        $hasta = $request->input('hasta');
        if ($desde || $hasta) {
            $inicio = $desde ? Carbon::parse($desde)->startOfDay() : Carbon::minValue();
            $fin = $hasta ? Carbon::parse($hasta)->endOfDay() : Carbon::maxValue();
            $query->whereBetween('fecha', [$inicio, $fin]);
        }

        $logs = $query->paginate(15)->withQueryString();

        $acciones = AuditoriaLog::query()
            ->select('accion')
            ->distinct()
            ->orderBy('accion')
            ->pluck('accion');

        $tablas = AuditoriaLog::query()
            ->select('tabla')
            ->distinct()
            ->orderBy('tabla')
            ->pluck('tabla');

        $usuarios = User::query()->orderBy('name')->get();

        $totalLogs = AuditoriaLog::count();
        $logsHoy = AuditoriaLog::whereDate('fecha', Carbon::today())->count();
        $logsSemana = AuditoriaLog::where('fecha', '>=', Carbon::now()->subDays(6)->startOfDay())->count();

        return view('admin.auditoria.index', [
            'logs' => $logs,
            'acciones' => $acciones,
            'tablas' => $tablas,
            'usuarios' => $usuarios,
            'totalLogs' => $totalLogs,
            'logsHoy' => $logsHoy,
            'logsSemana' => $logsSemana,
        ]);
    }
}
