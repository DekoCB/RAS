<?php

namespace App\Http\Controllers\Secretaria;

use App\Http\Controllers\Controller;
use App\Models\Deuda;
use App\Models\InventarioCategoria;
use App\Models\InventarioMovimiento;
use App\Models\InventarioProducto;
use App\Models\InventarioVenta;
use App\Models\InventarioVentaDetalle;
use App\Models\Estudiante;
use App\Models\User;
use App\Services\AuditoriaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProductoController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:inventario.ver')->only(['index', 'categorias']);
        $this->middleware('permission:inventario.movimiento')->only([
            'movimientos',
            'create',
            'store',
            'update',
            'destroy',
            'storeVenta',
            'voucher',
            'storeCategoria',
            'updateCategoria',
            'destroyCategoria',
        ]);
    }

    private function routePrefix(): string
    {
        /** @var User|null $user */
        $user = Auth::user();
        return $user?->hasRole('SuperAdmin') ? 'admin-sistema.' : 'secretaria.';
    }

    public function index(Request $request)
    {
        $q = $request->input('q');
        $categoriaId = $request->input('categoria');

        $query = InventarioProducto::with('categoria');

        if ($q) {
            $query->where(function ($sub) use ($q) {
                $sub->where('nombre', 'like', "%{$q}%")
                    ->orWhere('sku', 'like', "%{$q}%");
            });
        }

        if ($categoriaId) {
            $query->where('categoria_id', $categoriaId);
        }

        $productos = $query
            ->orderBy('nombre')
            ->paginate(12)
            ->withQueryString();

        $categorias = InventarioCategoria::query()
            ->orderBy('nombre')
            ->get();

        $estudiantes = Estudiante::query()
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->get(['id', 'nombres', 'apellidos', 'dni']);

        return view('secretaria.productos.index', compact('productos', 'categorias', 'estudiantes'));
    }

    public function categorias()
    {
        $categorias = InventarioCategoria::query()
            ->leftJoin('inventario_productos as productos', 'inventario_categorias.id', '=', 'productos.categoria_id')
            ->select('inventario_categorias.*')
            ->selectRaw('COUNT(productos.id) as productos_count')
            ->selectRaw('COALESCE(SUM(productos.stock), 0) as stock_total')
            ->selectRaw('COALESCE(SUM(productos.stock * productos.precio), 0) as valor_total')
            ->groupBy('inventario_categorias.id')
            ->orderBy('inventario_categorias.nombre')
            ->get();

        return view('secretaria.productos.categorias', compact('categorias'));
    }

    public function movimientos()
    {
        $movimientos = InventarioMovimiento::with(['producto', 'usuario'])
            ->orderByDesc('fecha_movimiento')
            ->paginate(15);

        return view('secretaria.productos.movimientos', compact('movimientos'));
    }

    public function create()
    {
        $categorias = InventarioCategoria::orderBy('nombre')->get();

        return view('secretaria.productos.create', compact('categorias'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'categoria_id' => ['required', 'exists:inventario_categorias,id'],
            'sku' => ['nullable', 'string', 'max:80'],
            'stock' => ['required', 'integer', 'min:0'],
            'precio' => ['required', 'numeric', 'min:0'],
            'descripcion' => ['nullable', 'string'],
            'imagen_url' => ['nullable', 'url'],
            'imagen' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'destacado' => ['nullable'],
            'activo' => ['nullable'],
        ]);

        $data['destacado'] = $request->boolean('destacado');
        $data['activo'] = $request->boolean('activo', true);
        unset($data['imagen']);

        $categoria = InventarioCategoria::find($data['categoria_id']);
        $data['tipo'] = $categoria?->nombre ?? $request->input('tipo', 'Producto');

        if ($request->hasFile('imagen')) {
            $path = $request->file('imagen')->store('productos', 'public');
            $data['imagen_url'] = 'storage/' . $path;
        }

        $producto = InventarioProducto::create($data);

        AuditoriaService::log($request->user()?->id, 'crear', 'inventario_productos', $producto->id, $data, $request);

        return redirect()->route($this->routePrefix() . 'productos.index')->with('status', 'Producto registrado.');
    }

    public function update(Request $request, InventarioProducto $producto)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'categoria_id' => ['required', 'exists:inventario_categorias,id'],
            'sku' => ['nullable', 'string', 'max:80'],
            'stock' => ['required', 'integer', 'min:0'],
            'precio' => ['required', 'numeric', 'min:0'],
            'descripcion' => ['nullable', 'string'],
            'imagen_url' => ['nullable', 'url'],
            'imagen' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'destacado' => ['nullable'],
            'activo' => ['nullable'],
        ]);

        $data['destacado'] = $request->boolean('destacado');
        $data['activo'] = $request->boolean('activo', true);
        unset($data['imagen']);

        $categoria = InventarioCategoria::find($data['categoria_id']);
        $data['tipo'] = $categoria?->nombre ?? $producto->tipo ?? $request->input('tipo', 'Producto');

        if ($request->hasFile('imagen')) {
            $path = $request->file('imagen')->store('productos', 'public');
            $data['imagen_url'] = 'storage/' . $path;
        }

        $precioAnterior = (float) $producto->precio;
        $nombreAnterior = $producto->nombre;
        $producto->update($data);

        $nuevoPrecio = (float) $data['precio'];
        $nombreNuevo  = $data['nombre'];

        if ($nuevoPrecio !== $precioAnterior) {
            // ── Estrategia 1: deudas creadas desde el carrito del Bazar
            //    periodo = "Producto: {nombre_anterior}" o "Producto: {nombre_nuevo}"
            $periodosExactos = array_unique(array_filter([
                'Producto: ' . $nombreAnterior,
                'Producto: ' . $nombreNuevo,
                $nombreAnterior,
                $nombreNuevo,
            ]));

            // ── Estrategia 2: deudas creadas por el seeder/matrícula con ConceptoPago tipo 'libros'
            //    periodo = "Libros 2026", "Libros 2025", etc.
            //    Filtra por nivel si el nombre del producto lo indica (Inicial/Primaria/Secundaria)
            $conceptoLibros = \App\Models\ConceptoPago::where('tipo', 'libros')->first();
            $nivelProducto  = null;
            foreach (['Inicial', 'Primaria', 'Secundaria'] as $nivel) {
                if (stripos($nombreNuevo, $nivel) !== false || stripos($nombreAnterior, $nivel) !== false) {
                    $nivelProducto = $nivel;
                    break;
                }
            }

            Deuda::where('estado', 'pendiente')
                ->whereColumn('saldo', 'monto')
                ->where(function ($q) use ($periodosExactos, $conceptoLibros, $nivelProducto) {
                    // Coincidencia exacta por periodo (Bazar)
                    $q->whereIn('periodo', $periodosExactos);

                    // Coincidencia por ConceptoPago tipo 'libros' (seeder/matrícula)
                    if ($conceptoLibros) {
                        $q->orWhere(function ($sub) use ($conceptoLibros, $nivelProducto) {
                            $sub->where('concepto_pago_id', $conceptoLibros->id)
                                ->where('periodo', 'LIKE', 'Libros%');

                            // Si el producto indica un nivel, filtrar solo ese nivel
                            if ($nivelProducto) {
                                $sub->whereHas('matricula.grado', fn ($g) =>
                                    $g->where('nivel', $nivelProducto)
                                );
                            }
                        });
                    }
                })
                ->update([
                    'monto' => $nuevoPrecio,
                    'saldo' => $nuevoPrecio,
                ]);
        }

        AuditoriaService::log($request->user()?->id, 'editar', 'inventario_productos', $producto->id, $data, $request);

        return redirect()->route($this->routePrefix() . 'productos.index')->with('status', 'Producto actualizado.');
    }

    public function destroy(Request $request, InventarioProducto $producto)
    {
        $producto->delete();

        AuditoriaService::log($request->user()?->id, 'eliminar', 'inventario_productos', $producto->id, null, $request);

        return redirect()->route($this->routePrefix() . 'productos.index')->with('status', 'Producto eliminado.');
    }

    public function buscarAlumno(Request $request)
    {
        $dni = trim((string) $request->input('dni', ''));
        if ($dni === '') {
            return response()->json(['found' => false, 'message' => 'Ingrese un DNI.']);
        }

        $estudiante = Estudiante::where('dni', $dni)->first(['id', 'nombres', 'apellidos', 'dni']);
        if (!$estudiante) {
            return response()->json(['found' => false, 'message' => 'No se encontró ningún alumno con ese DNI.']);
        }

        return response()->json([
            'found'    => true,
            'id'       => $estudiante->id,
            'nombre'   => trim($estudiante->apellidos . ' ' . $estudiante->nombres),
            'dni'      => $estudiante->dni,
        ]);
    }

    public function storeVenta(Request $request)
    {
        $data = $request->validate([
            'producto_id'          => ['required', 'exists:inventario_productos,id'],
            'cantidad'             => ['required', 'integer', 'min:1'],
            'tipo_comprador'       => ['required', 'in:alumno,externo'],
            'estudiante_id'        => ['required_if:tipo_comprador,alumno', 'nullable', 'exists:estudiantes,id'],
            'comprador_nombre'     => ['required_if:tipo_comprador,externo', 'nullable', 'string', 'max:120'],
            'comprador_documento'  => ['nullable', 'string', 'max:20'],
            'observacion'          => ['nullable', 'string', 'max:255'],
            'pagos'                => ['required', 'array', 'min:1'],
            'pagos.*.metodo'       => ['required', 'in:Efectivo,Yape,Transferencia,Plin,BancoNacion'],
            'pagos.*.monto'        => ['required', 'numeric', 'min:0.01'],
            'pagos.*.numero_operacion' => ['nullable', 'string', 'max:60'],
        ]);

        $venta = DB::transaction(function () use ($data, $request) {
            $producto = InventarioProducto::query()
                ->where('id', $data['producto_id'])
                ->lockForUpdate()
                ->firstOrFail();

            if ($data['cantidad'] > $producto->stock) {
                throw ValidationException::withMessages([
                    'cantidad' => 'Stock insuficiente para esta venta.',
                ]);
            }

            $subtotal    = round($producto->precio * $data['cantidad'], 2);
            $totalPagado = round(array_sum(array_column($data['pagos'], 'monto')), 2);

            if ($totalPagado > $subtotal) {
                throw ValidationException::withMessages([
                    'pagos' => "El total de pagos (S/ {$totalPagado}) supera el total de la venta (S/ {$subtotal}).",
                ]);
            }

            $estudiante = null;
            if ($data['tipo_comprador'] === 'alumno') {
                $estudiante = Estudiante::find($data['estudiante_id']);
            }

            $compradorNombre    = $data['tipo_comprador'] === 'alumno'
                ? trim(($estudiante->apellidos ?? '') . ' ' . ($estudiante->nombres ?? ''))
                : $data['comprador_nombre'];
            $compradorDocumento = $data['tipo_comprador'] === 'alumno'
                ? $estudiante?->dni
                : $data['comprador_documento'];

            // Método principal = primer pago; si hay varios → "Mixto"
            $metodosPago   = array_column($data['pagos'], 'metodo');
            $metodoPrincipal = count(array_unique($metodosPago)) === 1 ? $metodosPago[0] : 'Mixto';

            $voucherCodigo = $this->generarVoucherCodigo();

            $venta = InventarioVenta::create([
                'fecha_venta'          => now(),
                'tipo_comprador'       => $data['tipo_comprador'],
                'estudiante_id'        => $data['tipo_comprador'] === 'alumno' ? $data['estudiante_id'] : null,
                'comprador_nombre'     => $compradorNombre,
                'comprador_documento'  => $compradorDocumento,
                'total'                => $subtotal,
                'monto_pagado'         => $totalPagado,
                'metodo_pago'          => $metodoPrincipal,
                'pagos_detalle'        => json_encode($data['pagos']),
                'estado'               => $totalPagado < $subtotal ? 'parcial' : 'pagado',
                'voucher_codigo'       => $voucherCodigo,
                'usuario_id'           => $request->user()?->id,
                'observacion'          => $data['observacion'] ?? null,
            ]);

            InventarioVentaDetalle::create([
                'inventario_venta_id'   => $venta->id,
                'inventario_producto_id' => $producto->id,
                'cantidad'              => $data['cantidad'],
                'precio_unitario'       => $producto->precio,
                'subtotal'              => $subtotal,
            ]);

            $producto->decrement('stock', $data['cantidad']);

            InventarioMovimiento::create([
                'inventario_producto_id' => $producto->id,
                'tipo'                   => 'salida',
                'cantidad'               => $data['cantidad'],
                'costo_unitario'         => $producto->precio,
                'referencia'             => 'Venta #' . $venta->id,
                'usuario_id'             => $request->user()?->id,
                'fecha_movimiento'       => now()->toDateString(),
            ]);

            AuditoriaService::log($request->user()?->id, 'crear', 'inventario_ventas', $venta->id, $data, $request);

            return $venta;
        });

        return redirect()
            ->route($this->routePrefix() . 'productos.ventas.voucher', $venta)
            ->with('status', 'Venta registrada.');
    }

    public function voucher(InventarioVenta $venta)
    {
        $venta->load(['detalles.producto', 'estudiante', 'usuario']);

        return view('secretaria.productos.voucher', compact('venta'));
    }

    private function generarVoucherCodigo(): string
    {
        $hoy = now();
        $prefijo = 'V-' . $hoy->format('Ymd');
        $secuencia = InventarioVenta::whereDate('fecha_venta', $hoy)->count() + 1;

        return $prefijo . '-' . str_pad((string) $secuencia, 4, '0', STR_PAD_LEFT);
    }

    public function storeCategoria(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:80'],
            'activo' => ['nullable'],
        ]);

        $data['activo'] = $request->boolean('activo', true);
        $data['slug'] = Str::slug($data['nombre']);

        $categoria = InventarioCategoria::create($data);

        AuditoriaService::log($request->user()?->id, 'crear', 'inventario_categorias', $categoria->id, $data, $request);

        return redirect()->route($this->routePrefix() . 'productos.categorias')->with('status', 'Categoria registrada.');
    }

    public function updateCategoria(Request $request, InventarioCategoria $categoria)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:80'],
            'activo' => ['nullable'],
        ]);

        $data['activo'] = $request->boolean('activo', true);
        $data['slug'] = Str::slug($data['nombre']);

        $categoria->update($data);

        InventarioProducto::where('categoria_id', $categoria->id)
            ->update(['tipo' => $categoria->nombre]);

        AuditoriaService::log($request->user()?->id, 'editar', 'inventario_categorias', $categoria->id, $data, $request);

        return redirect()->route($this->routePrefix() . 'productos.categorias')->with('status', 'Categoria actualizada.');
    }

    public function destroyCategoria(Request $request, InventarioCategoria $categoria)
    {
        if ($categoria->productos()->exists()) {
            return redirect()
                ->route($this->routePrefix() . 'productos.categorias')
                ->with('status', 'No se puede eliminar una categoria con productos asociados.');
        }

        $categoria->delete();

        AuditoriaService::log($request->user()?->id, 'eliminar', 'inventario_categorias', $categoria->id, null, $request);

        return redirect()->route($this->routePrefix() . 'productos.categorias')->with('status', 'Categoria eliminada.');
    }

    public function buscar(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        $categoriaId = $request->input('categoria');

        $query = InventarioProducto::query()
            ->with('categoria')
            ->where('activo', 1);

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('nombre', 'like', "%{$q}%")
                    ->orWhere('sku', 'like', "%{$q}%");
            });
        }

        if ($categoriaId) {
            $query->where('categoria_id', $categoriaId);
        }

        $productos = $query->orderBy('nombre')
            ->limit(30)
            ->get(['id', 'nombre', 'sku', 'stock', 'precio', 'categoria_id']);

        return response()->json($productos->map(function ($p) {
            return [
                'id' => $p->id,
                'nombre' => $p->nombre,
                'sku' => $p->sku,
                'stock' => (int) $p->stock,
                'precio' => (float) $p->precio,
                'categoria' => $p->categoria?->nombre,
            ];
        }));
    }
}
