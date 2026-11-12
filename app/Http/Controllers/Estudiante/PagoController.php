<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use App\Models\Deuda;
use App\Models\Estudiante;
use App\Models\Pago;
use App\Models\Voucher;
use Illuminate\Http\Request;

class PagoController extends Controller
{
    public function create(Request $request)
    {
        $user = $request->user();
        $estudiante = $user ? Estudiante::where('user_id', $user->id)->first() : null;

        $deudas = collect();
        $pagos = collect();

        if ($estudiante) {
            $deudas = Deuda::whereHas('matricula', function ($query) use ($estudiante) {
                $query->where('estudiante_id', $estudiante->id);
            })
                ->with('concepto')
                ->whereIn('estado', ['pendiente', 'parcial'])
                ->orderBy('fecha_vencimiento')
                ->get();

            $pagos = Pago::where('usuario_registra_id', $user->id)
                ->with('deudas.concepto')
                ->orderByDesc('fecha_pago')
                ->take(10)
                ->get();
        }

        return view('estudiante.pagos.create', compact('estudiante', 'deudas', 'pagos'));
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $estudiante = $user ? Estudiante::where('user_id', $user->id)->first() : null;

        if (!$estudiante) {
            return back()->withErrors(['deuda_id' => 'No tienes un perfil de estudiante asociado.']);
        }

        $data = $request->validate([
            'deuda_id' => ['required', 'exists:deudas,id'],
            'monto' => ['required', 'numeric', 'min:0.01'],
            'fecha_pago' => ['required', 'date'],
            'comentario' => ['nullable', 'string', 'max:255'],
            'archivo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $deuda = Deuda::where('id', $data['deuda_id'])
            ->whereHas('matricula', function ($query) use ($estudiante) {
                $query->where('estudiante_id', $estudiante->id);
            })
            ->first();

        if (!$deuda) {
            return back()->withErrors(['deuda_id' => 'La deuda seleccionada no es valida.']);
        }

        if ($data['monto'] > $deuda->saldo) {
            return back()->withErrors(['monto' => 'El monto no puede ser mayor al saldo de la deuda.']);
        }

        $pago = Pago::create([
            'anio_escolar_id' => $deuda->anio_escolar_id,
            'periodo_academico_id' => $deuda->periodo_academico_id,
            'monto' => $data['monto'],
            'fecha_pago' => $data['fecha_pago'],
            'metodo' => 'virtual',
            'estado' => 'pendiente',
            'usuario_registra_id' => $user->id,
            'comentario' => $data['comentario'] ?? null,
        ]);

        $pago->deudas()->syncWithoutDetaching([
            $deuda->id => ['monto_aplicado' => $data['monto']],
        ]);

        if (!empty($data['archivo'])) {
            $path = $data['archivo']->store('vouchers');
            Voucher::create([
                'deuda_id' => $deuda->id,
                'pago_id' => $pago->id,
                'archivo_path' => $path,
                'estado' => 'pendiente',
            ]);
        }

        return redirect()
            ->route('estudiante.pagos.create')
            ->with('status', 'Pago registrado. Pendiente de confirmacion.');
    }
}
