<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Descuento;
use App\Models\AnioEscolar;
use App\Models\ConceptoPago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DescuentoController extends Controller
{
    public function index()
    {
        $descuentos = Descuento::with(['anioEscolar', 'conceptoPago'])
            ->orderByDesc('id')
            ->paginate(10);

        return view('admin.descuentos.index', compact('descuentos'));
    }

    public function create()
    {
        $anios = AnioEscolar::orderByDesc('id')->get();
        $conceptos = ConceptoPago::where('activo', 1)->orderBy('nombre')->get();

        return view('admin.descuentos.create', compact('anios', 'conceptos'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'anio_escolar_id' => ['nullable', 'exists:anios_escolares,id'],
            'concepto_pago_id' => ['required', 'exists:conceptos_pago,id'],
            'nivel' => ['nullable', 'string', 'max:30'],

            'tipo' => ['required', 'string', 'max:30'],
            'aplica_a' => ['nullable', 'string', 'max:30'],

            'porcentaje' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'monto_fijo' => ['nullable', 'numeric', 'min:0'],
            'dia_limite' => ['nullable', 'integer', 'min:1', 'max:31'],

            'activo' => ['required', 'boolean'],
        ]);

        // Validación: debe existir porcentaje o monto fijo
        if (empty($data['porcentaje']) && empty($data['monto_fijo'])) {
            return back()->withErrors([
                'porcentaje' => 'Debes ingresar porcentaje o monto fijo.'
            ])->withInput();
        }

        $data['creado_por'] = Auth::id();

        Descuento::create($data);

        return redirect()->route('admin-sistema.descuentos.index')
            ->with('success', 'Descuento registrado correctamente.');
    }

    public function edit(Descuento $descuento)
    {
        $anios = AnioEscolar::orderByDesc('id')->get();
        $conceptos = ConceptoPago::where('activo', 1)->orderBy('nombre')->get();

        return view('admin.descuentos.edit', compact('descuento', 'anios', 'conceptos'));
    }

    public function update(Request $request, Descuento $descuento)
    {
        $data = $request->validate([
            'anio_escolar_id' => ['nullable', 'exists:anios_escolares,id'],
            'concepto_pago_id' => ['required', 'exists:conceptos_pago,id'],
            'nivel' => ['nullable', 'string', 'max:30'],

            'tipo' => ['required', 'string', 'max:30'],
            'aplica_a' => ['nullable', 'string', 'max:30'],

            'porcentaje' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'monto_fijo' => ['nullable', 'numeric', 'min:0'],
            'dia_limite' => ['nullable', 'integer', 'min:1', 'max:31'],

            'activo' => ['required', 'boolean'],
        ]);

        if (empty($data['porcentaje']) && empty($data['monto_fijo'])) {
            return back()->withErrors([
                'porcentaje' => 'Debes ingresar porcentaje o monto fijo.'
            ])->withInput();
        }

        $descuento->update($data);

        return redirect()->route('admin-sistema.descuentos.index')
            ->with('success', 'Descuento actualizado correctamente.');
    }

    public function destroy(Descuento $descuento)
    {
        $descuento->delete();

        return redirect()->route('admin-sistema.descuentos.index')
            ->with('success', 'Descuento eliminado correctamente.');
    }
}