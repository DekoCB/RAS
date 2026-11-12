<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tarifa;
use App\Models\AnioEscolar;
use App\Models\ConceptoPago;
use App\Models\CronogramaPension;
use App\Models\PoliticaPago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TarifaController extends Controller
{
    public function index(Request $request)
    {
        $anios = AnioEscolar::orderByDesc('id')->get();
        $conceptos = ConceptoPago::where('activo', 1)->orderBy('nombre')->get();

        // Filtrar por año escolar (por defecto el activo)
        $anioSelId = $request->input('anio_id');
        $anioActivo = $anioSelId
            ? AnioEscolar::find($anioSelId)
            : (AnioEscolar::where('activo', true)->first() ?? AnioEscolar::orderByDesc('id')->first());

        $niveles = ['General', 'Inicial', 'Primaria', 'Secundaria'];

        $tarifasPorNivel = collect();
        $politica   = null;
        $cronograma = collect();
        if ($anioActivo) {
            $politica   = PoliticaPago::paraAnio($anioActivo->id);
            $cronograma = CronogramaPension::where('anio_escolar_id', $anioActivo->id)
                ->orderBy('mes')->get()->keyBy('mes');

            $todas = Tarifa::with(['conceptoPago'])
                ->where('anio_escolar_id', $anioActivo->id)
                ->orderBy('nivel')
                ->orderBy('concepto_pago_id')
                ->orderBy('mes')
                ->get();

            foreach ($niveles as $nivel) {
                $tarifasPorNivel[$nivel] = $todas->where('nivel', $nivel)->values();
            }
        }

        // Para la paginación legacy (no se usa en la nueva vista, pero se mantiene por compatibilidad)
        $tarifas = Tarifa::with(['anioEscolar', 'conceptoPago'])
            ->orderByDesc('id')
            ->paginate(10);

        return view('admin.tarifas.index', compact(
            'tarifas', 'tarifasPorNivel', 'anios', 'conceptos', 'anioActivo', 'niveles',
            'politica', 'cronograma'
        ));
    }

    public function create()
    {
        $anios = AnioEscolar::orderByDesc('id')->get();
        $conceptos = ConceptoPago::where('activo', 1)->orderBy('nombre')->get();

        return view('admin.tarifas.create', compact('anios', 'conceptos'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'anio_escolar_id' => ['required', 'exists:anios_escolares,id'],
            'concepto_pago_id' => ['required', 'exists:conceptos_pago,id'],
            'nivel' => ['required', 'string', 'max:30'],
            'mes' => ['required', 'integer', 'min:0', 'max:12'],
            'monto' => ['required', 'numeric', 'min:0'],
            'vigente_desde' => ['nullable', 'date'],
            'vigente_hasta' => ['nullable', 'date', 'after_or_equal:vigente_desde'],
            'activo' => ['required', 'boolean'],
        ]);

        $data['creado_por'] = Auth::id();

        $existe = Tarifa::where('anio_escolar_id', $data['anio_escolar_id'])
            ->where('concepto_pago_id', $data['concepto_pago_id'])
            ->where('nivel', $data['nivel'])
            ->where('mes', $data['mes'])
            ->exists();

        if ($existe) {
            return back()
                ->withErrors(['nivel' => 'Ya existe una tarifa para ese Año + Nivel + Concepto + Mes. Edita la existente.'])
                ->withInput();
        }

        Tarifa::create($data);

        return redirect()->route('admin-sistema.tarifas.index')
            ->with('success', 'Tarifa registrada correctamente.');
    }
    public function edit(Tarifa $tarifa)
    {
        $anios = AnioEscolar::orderByDesc('id')->get();
        $conceptos = ConceptoPago::where('activo', 1)->orderBy('nombre')->get();

        return view('admin.tarifas.edit', compact('tarifa', 'anios', 'conceptos'));
    }

    public function update(Request $request, Tarifa $tarifa)
    {
        $data = $request->validate([
            'anio_escolar_id' => ['required', 'exists:anios_escolares,id'],
            'concepto_pago_id' => ['required', 'exists:conceptos_pago,id'],
            'nivel' => ['required', 'string', 'max:30'],
            'mes' => ['required', 'integer', 'min:0', 'max:12'],
            'monto' => ['required', 'numeric', 'min:0'],
            'vigente_desde' => ['nullable', 'date'],
            'vigente_hasta' => ['nullable', 'date', 'after_or_equal:vigente_desde'],
            'activo' => ['required', 'boolean'],
        ]);

        // Evitar duplicados (excluyendo la tarifa actual)
        $existe = Tarifa::where('anio_escolar_id', $data['anio_escolar_id'])
            ->where('concepto_pago_id', $data['concepto_pago_id'])
            ->where('nivel', $data['nivel'])
            ->where('mes', $data['mes'])
            ->where('id', '!=', $tarifa->id)
            ->exists();

        if ($existe) {
            return back()
                ->withErrors(['nivel' => 'Ya existe una tarifa para ese Año + Nivel + Concepto + Mes.'])
                ->withInput();
        }

        $tarifa->update($data);

        return redirect()->route('admin-sistema.tarifas.index')
            ->with('success', 'Tarifa actualizada correctamente.');
    }

    public function destroy(Tarifa $tarifa)
    {
        $tarifa->delete();

        return redirect()->route('admin-sistema.tarifas.index')
            ->with('success', 'Tarifa eliminada correctamente.');
    }
}
