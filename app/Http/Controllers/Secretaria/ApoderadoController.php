<?php

namespace App\Http\Controllers\Secretaria;

use App\Http\Controllers\Controller;
use App\Models\Apoderado;
use App\Models\Estudiante;
use App\Models\Matricula;
use App\Services\AuditoriaService;
use Illuminate\Http\Request;

class ApoderadoController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:apoderado.ver')->only(['index', 'show']);
        $this->middleware('permission:apoderado.crear')->only(['create', 'store']);
        $this->middleware('permission:apoderado.editar')->only(['edit', 'update']);
        $this->middleware('permission:apoderado.eliminar')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $apoderados = Apoderado::with([
            'estudiantes' => function ($query) {
                $query->where(function ($studentQuery) {
                    $studentQuery->whereDoesntHave('matriculas')
                        ->orWhereHas('matriculas', function ($matriculaQuery) {
                            $matriculaQuery->where('estado', Matricula::ESTADO_REGISTRADA);
                        });
                });
            }
        ])
            ->where(function ($query) {
                $query->whereDoesntHave('estudiantes')
                    ->orWhereHas('estudiantes', function ($studentQuery) {
                        $studentQuery->where(function ($visibleStudentQuery) {
                            $visibleStudentQuery->whereDoesntHave('matriculas')
                                ->orWhereHas('matriculas', function ($matriculaQuery) {
                                    $matriculaQuery->where('estado', Matricula::ESTADO_REGISTRADA);
                                });
                        });
                    });
            })
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->paginate(15);

        return view('secretaria.apoderados.index', compact('apoderados'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $estudiantes = Estudiante::where(function ($query) {
            $query->whereDoesntHave('matriculas')
                ->orWhereHas('matriculas', function ($matriculaQuery) {
                    $matriculaQuery->where('estado', Matricula::ESTADO_REGISTRADA);
                });
        })
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->get();

        return view('secretaria.apoderados.create', compact('estudiantes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nombres' => ['required', 'string', 'max:255'],
            'apellidos' => ['required', 'string', 'max:255'],
            'dni' => ['nullable', 'string', 'max:20'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'estudiante_ids' => ['nullable', 'array'],
            'estudiante_ids.*' => ['integer', 'exists:estudiantes,id'],
        ]);

        $estudianteIds = $data['estudiante_ids'] ?? [];
        unset($data['estudiante_ids']);

        $apoderado = Apoderado::create($data);
        $apoderado->estudiantes()->sync($estudianteIds);

        AuditoriaService::log($request->user()?->id, 'crear', 'apoderados', $apoderado->id, $data, $request);

        return redirect()->route('secretaria.apoderados.index')->with('status', 'Apoderado creado.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Apoderado $apoderado)
    {
        return view('secretaria.apoderados.show', compact('apoderado'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Apoderado $apoderado)
    {
        $estudiantes = Estudiante::where(function ($query) {
            $query->whereDoesntHave('matriculas')
                ->orWhereHas('matriculas', function ($matriculaQuery) {
                    $matriculaQuery->where('estado', Matricula::ESTADO_REGISTRADA);
                });
        })
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->get();

        return view('secretaria.apoderados.edit', compact('apoderado', 'estudiantes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Apoderado $apoderado)
    {
        $data = $request->validate([
            'nombres' => ['required', 'string', 'max:255'],
            'apellidos' => ['required', 'string', 'max:255'],
            'dni' => ['nullable', 'string', 'max:20'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'estudiante_ids' => ['nullable', 'array'],
            'estudiante_ids.*' => ['integer', 'exists:estudiantes,id'],
        ]);

        $estudianteIds = $data['estudiante_ids'] ?? [];
        unset($data['estudiante_ids']);

        $apoderado->update($data);
        $apoderado->estudiantes()->sync($estudianteIds);

        AuditoriaService::log($request->user()?->id, 'editar', 'apoderados', $apoderado->id, $data, $request);

        return redirect()->route('secretaria.apoderados.index')->with('status', 'Apoderado actualizado.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Apoderado $apoderado)
    {
        $apoderado->estudiantes()->detach();
        $apoderado->delete();

        AuditoriaService::log($request->user()?->id, 'eliminar', 'apoderados', $apoderado->id, null, $request);

        return redirect()->route('secretaria.apoderados.index')->with('status', 'Apoderado eliminado.');
    }
}
