<?php

namespace App\Http\Middleware;

use App\Models\Apoderado;
use App\Models\Deuda;
use App\Models\Estudiante;
use App\Models\Matricula;
use App\Models\MatriculaCargo;
use App\Models\MatriculaDescuento;
use App\Models\Pago;
use App\Models\Voucher;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CleanupMatriculaDraft
{
    public function handle(Request $request, Closure $next): Response
    {
        $this->cleanupAbandonedDraft($request);

        return $next($request);
    }

    private function cleanupAbandonedDraft(Request $request): void
    {
        $routeName = $request->route()?->getName();
        $matriculaId = (int) $request->session()->get('matricula.id', 0);
        if ($this->isWizardRoute($routeName) || $this->isWizardSupportRoute($request, $routeName, $matriculaId)) {
            return;
        }

        if ($matriculaId <= 0) {
            return;
        }

        $matricula = Matricula::find($matriculaId);
        if (!$matricula) {
            $this->forgetWizardSession($request);
            return;
        }

        if ($matricula->estado === Matricula::ESTADO_REGISTRADA) {
            $this->forgetWizardSession($request);
            return;
        }

        if ($matricula->estado !== Matricula::ESTADO_EN_PROCESO) {
            return;
        }

        $estudianteId = (int) $request->session()->get('matricula.estudiante_id', 0);
        $apoderadoId = (int) $request->session()->get('matricula.apoderado_id', 0);
        $estudianteCreadoEnWizard = (bool) $request->session()->get('matricula.estudiante_creado_en_wizard', false);
        $apoderadoCreadoEnWizard = (bool) $request->session()->get('matricula.apoderado_creado_en_wizard', false);

        DB::transaction(function () use (
            $matriculaId,
            $estudianteId,
            $apoderadoId,
            $estudianteCreadoEnWizard,
            $apoderadoCreadoEnWizard
        ) {
            $deudaIds = Deuda::where('matricula_id', $matriculaId)->pluck('id');
            $pagoIds = collect();

            if ($deudaIds->isNotEmpty()) {
                $pagoIds = DB::table('deuda_pagos')
                    ->whereIn('deuda_id', $deudaIds)
                    ->pluck('pago_id')
                    ->unique();

                Voucher::whereIn('deuda_id', $deudaIds)->delete();
                if ($pagoIds->isNotEmpty()) {
                    Voucher::whereIn('pago_id', $pagoIds)->delete();
                }

                Deuda::whereIn('id', $deudaIds)->delete();
            }

            MatriculaCargo::where('matricula_id', $matriculaId)->delete();
            MatriculaDescuento::where('matricula_id', $matriculaId)->delete();
            Matricula::whereKey($matriculaId)->delete();

            if ($pagoIds->isNotEmpty()) {
                Pago::whereIn('id', $pagoIds)
                    ->whereDoesntHave('deudas')
                    ->delete();
            }

            $estudiante = $estudianteId ? Estudiante::find($estudianteId) : null;
            if ($estudiante) {
                $tieneMatriculas = Matricula::where('estudiante_id', $estudiante->id)->exists();
                $esEstudianteBorrable = $estudianteCreadoEnWizard || $estudiante->estado === 'en_proceso';

                if ($esEstudianteBorrable && !$tieneMatriculas) {
                    $estudiante->delete();
                }
            }

            if ($apoderadoId) {
                $apoderado = Apoderado::find($apoderadoId);
                if ($apoderado) {
                    $tieneEstudiantes = $apoderado->estudiantes()->exists();

                    if ($apoderadoCreadoEnWizard && !$tieneEstudiantes) {
                        $apoderado->delete();
                    }
                }
            }
        });

        $this->forgetWizardSession($request);
    }

    private function isWizardRoute(?string $routeName): bool
    {
        if (!$routeName) {
            return false;
        }

        return str_starts_with($routeName, 'secretaria.matriculas.paso');
    }

    private function isWizardSupportRoute(Request $request, ?string $routeName, int $matriculaId): bool
    {
        if (!$routeName) {
            return false;
        }

        if (in_array($routeName, [
            // Paso 3 usa esta ruta AJAX para buscar productos del carrito.
            'secretaria.productos.buscar',
        ], true)) {
            return true;
        }

        // Soporte del wizard (Paso 5): rutas de la matricula actual que se abren
        // desde el resumen (nota de venta, resumen, credenciales, etc.).
        if ($matriculaId > 0 && str_starts_with($routeName, 'secretaria.matriculas.')) {
            $param = $request->route('matricula');
            $routeMatriculaId = is_object($param) ? (int) ($param->id ?? 0) : (int) $param;

            return $routeMatriculaId > 0 && $routeMatriculaId === $matriculaId;
        }

        return false;
    }

    private function forgetWizardSession(Request $request): void
    {
        $request->session()->forget([
            'matricula.id',
            'matricula.nivel',
            'matricula.grado',
            'matricula.seccion',
            'matricula.anio_escolar_id',
            'matricula.periodo_academico_id',
            'matricula.estudiante_id',
            'matricula.apoderado_id',
            'matricula.deuda_matricula_id',
            'matricula.pago_id',
            'matricula.estudiante_credenciales',
            'matricula.return_to',
            'matricula.return_label',
            'matricula.estudiante_creado_en_wizard',
            'matricula.apoderado_creado_en_wizard',
            'matricula.paso2_form',
            'matricula.paso3_form',
            'matricula.postulante_id',
        ]);
    }
}
