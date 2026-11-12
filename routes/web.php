<?php

use App\Http\Controllers\DashboardRedirectController;
use App\Http\Controllers\SitioWebController;
use App\Http\Controllers\ArchivadorController;
use App\Http\Controllers\PreinscripcionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AsistenciaDocenteReporteController;
use App\Http\Controllers\Docente\AsistenciaController;
use App\Http\Controllers\Docente\HorarioController as DocenteHorarioController;
use App\Http\Controllers\Docente\IncidenciaController;
use App\Http\Controllers\Docente\NotaController;
use App\Http\Controllers\Director\DocenteCursoController;
use App\Http\Controllers\Director\HorarioEscolarController;
use App\Http\Controllers\Director\NotasController as DirectorNotasController;
use App\Http\Controllers\Director\PersonalController as DirectorPersonalController;
use App\Http\Controllers\Estudiante\DashboardController as EstudianteDashboardController;
use App\Http\Controllers\Estudiante\AsistenciaController as EstudianteAsistenciaController;
use App\Http\Controllers\Estudiante\CarnetController as EstudianteCarnetController;
use App\Http\Controllers\Estudiante\NotaController as EstudianteNotaController;
use App\Http\Controllers\Estudiante\DeudaController as EstudianteDeudaController;
use App\Http\Controllers\Estudiante\PagoController as EstudiantePagoController;
use App\Http\Controllers\Estudiante\CuentaController as EstudianteCuentaController;
use App\Http\Controllers\Docente\DashboardController as DocenteDashboardController;
use App\Http\Controllers\Auxiliar\DashboardController as AuxiliarDashboardController;
use App\Http\Controllers\Auxiliar\AsistenciaController as AuxiliarAsistenciaController;
use App\Http\Controllers\Auxiliar\CarnetController as AuxiliarCarnetController;
use App\Http\Controllers\Secretaria\ApoderadoController;
use App\Http\Controllers\Secretaria\EstudianteController;
use App\Http\Controllers\Secretaria\IncidenciaController as SecretariaIncidenciaController;
use App\Http\Controllers\Secretaria\MatriculaController;
use App\Http\Controllers\Secretaria\PagoController;
use App\Http\Controllers\Secretaria\ProductoController as SecretariaProductoController;
use App\Http\Controllers\Secretaria\AsistenciaController as SecretariaAsistenciaController;
use App\Http\Controllers\Secretaria\ReporteController;
use App\Http\Controllers\Secretaria\PersonalSecretariaController;
use App\Http\Controllers\Secretaria\DashboardController as SecretariaDashboardController;
use App\Http\Controllers\Admin\UsuarioController as AdminUsuarioController;
use App\Http\Controllers\Admin\PermisoController as AdminPermisoController;
use App\Http\Controllers\Admin\AnioEscolarController as AdminAnioEscolarController;
use App\Http\Controllers\Admin\NivelGradoController as AdminNivelGradoController;
use App\Http\Controllers\Admin\CursoController as AdminCursoController;
use App\Http\Controllers\Admin\AsignacionDocenteController as AdminAsignacionDocenteController;
use App\Http\Controllers\Admin\ReporteGlobalController as AdminReporteGlobalController;
use App\Http\Controllers\Admin\AuditoriaController as AdminAuditoriaController;
use App\Http\Controllers\Admin\BackupController as AdminBackupController;
use App\Http\Controllers\Admin\SoporteController as AdminSoporteController;
use App\Http\Controllers\Admin\ConfiguracionController as AdminConfiguracionController;
use App\Http\Controllers\Admin\TarifaController as AdminTarifaController;
use App\Http\Controllers\Admin\PoliticaPagoController as AdminPoliticaPagoController;
use App\Http\Controllers\Admin\DescuentoController as AdminDescuentoController;
use App\Http\Controllers\Admin\FinanzasController as AdminFinanzasController;
use App\Http\Controllers\Secretaria\AsistenciaDocenteController;
use App\Http\Controllers\Secretaria\ImprimirQrController;
use App\Http\Controllers\Secretaria\RetiroController;
use App\Http\Controllers\Secretaria\CajaController;
use App\Http\Controllers\Admin\PeriodoController as AdminPeriodoController;
use App\Http\Controllers\Admin\InstitucionController;
use App\Http\Controllers\Admin\SedeController;
use App\Http\Controllers\AcademiaController;
use App\Http\Controllers\AcademicoController;
use App\Http\Controllers\AdmisionController;
use App\Http\Controllers\DocumentoController;
use App\Http\Controllers\PastoralController;
use App\Http\Controllers\PatrimonioController;
use App\Http\Controllers\PsicologiaController;
use App\Http\Controllers\ReunionController;
use App\Http\Controllers\BibliotecaController;
use App\Http\Controllers\ComedorController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', [SitioWebController::class, 'inicio'])->name('sitio.inicio');

// Pre-inscripcion publica: la familia deja sus datos y el expediente entra a
// Admision listo para que secretaria lo continue (nadie se matricula por aqui).
Route::prefix('preinscripcion')->name('preinscripcion.')->group(function () {
    Route::get('/', [PreinscripcionController::class, 'formulario'])->name('formulario');
    Route::post('/', [PreinscripcionController::class, 'guardar'])
        ->middleware('throttle:10,60')
        ->name('guardar');
    Route::get('consulta', [PreinscripcionController::class, 'consulta'])
        ->middleware('throttle:30,60')
        ->name('consulta');
    Route::get('{token}', [PreinscripcionController::class, 'constancia'])
        ->where('token', '[a-z0-9]{32}')
        ->name('constancia');
});

Route::get('/dashboard', DashboardRedirectController::class)
    ->middleware(['auth'])
    ->name('dashboard');

Route::middleware(['auth', 'role:SuperAdmin'])
    ->prefix('admin-sistema')
    ->name('admin-sistema.')
    ->group(function () {
        Route::get('/dashboard', [AdminReporteGlobalController::class, 'index'])->name('dashboard');
        Route::get('reportes', [AdminReporteGlobalController::class, 'reportes'])->name('reportes.index');
        Route::get('reportes-globales', [AdminReporteGlobalController::class, 'reportes'])->name('reportes-globales.index');
        Route::get('reportes/pagos-excel', [AdminReporteGlobalController::class, 'exportarPagosExcel'])
            ->name('reportes.pagos.excel');
        Route::get('reportes/pagos-excel-unica', [AdminReporteGlobalController::class, 'exportarPagosExcelUnica'])
            ->name('reportes.pagos.excel.unica');
        Route::get('perfil', function () {
            return view('admin.perfil', ['user' => Auth::user()]);
        })->name('perfil');
        Route::get('usuarios', [AdminUsuarioController::class, 'index'])->name('usuarios.index');
        Route::post('usuarios', [AdminUsuarioController::class, 'store'])->name('usuarios.store');
        Route::put('usuarios/{user}', [AdminUsuarioController::class, 'update'])->name('usuarios.update');
        Route::patch('usuarios/{user}/toggle-estado', [AdminUsuarioController::class, 'toggleEstado'])->name('usuarios.toggle-estado');
        Route::get('usuarios/{user}/carnet', [ImprimirQrController::class, 'carnetUsuario'])->name('usuarios.carnet.individual');
        Route::get('permisos', [AdminPermisoController::class, 'index'])->name('permisos.index');
        Route::post('permisos', [AdminPermisoController::class, 'store'])->name('permisos.store');
        Route::post('permisos/{permission}/roles', [AdminPermisoController::class, 'roles'])->name('permisos.roles');
        Route::get('anios-escolares', [AdminAnioEscolarController::class, 'index'])->name('anios-escolares.index');
        Route::post('anios-escolares', [AdminAnioEscolarController::class, 'store'])->name('anios-escolares.store');
        Route::post('anios-escolares/{anioEscolar}/activar', [AdminAnioEscolarController::class, 'activar'])
            ->name('anios-escolares.activar');
        Route::post('anios-escolares/{anioEscolar}/admision', [AdminAnioEscolarController::class, 'campanaAdmision'])
            ->name('anios-escolares.admision');
        Route::get('gestionar-secciones', [MatriculaController::class, 'directorSecciones'])->name('gestionar-secciones.index');
        Route::post('gestionar-secciones', [MatriculaController::class, 'guardarSeccionModal'])->name('gestionar-secciones.guardar');
        Route::post('gestionar-secciones/{seccion}/cursos', [MatriculaController::class, 'guardarCursosSeccionDirector'])->name('gestionar-secciones.cursos.guardar');
        Route::put('gestionar-secciones/{seccion}', [MatriculaController::class, 'actualizarSeccionDirector'])->name('gestionar-secciones.update');
        Route::delete('gestionar-secciones/{seccion}', [MatriculaController::class, 'eliminarSeccionDirector'])->name('gestionar-secciones.destroy');
        Route::get('cursos', [AdminCursoController::class, 'index'])->name('cursos.index');
        Route::post('cursos', [AdminCursoController::class, 'store'])->name('cursos.store');
        Route::put('cursos/{curso}', [AdminCursoController::class, 'update'])->name('cursos.update');
        Route::delete('cursos/{curso}', [AdminCursoController::class, 'destroy'])->name('cursos.destroy');
        Route::get('asignacion-docente', [AdminAsignacionDocenteController::class, 'index'])
            ->name('asignacion-docente.index');
        Route::post('asignacion-docente', [AdminAsignacionDocenteController::class, 'store'])
            ->name('asignacion-docente.store');
        Route::get('asistencias-docentes', [AsistenciaDocenteReporteController::class, 'indexAdmin'])
            ->name('asistencias-docentes.index');
        Route::get('asistencias-docentes/excel', [AsistenciaDocenteReporteController::class, 'exportAdmin'])
            ->name('asistencias-docentes.excel');
        Route::get('qr-docentes', [AsistenciaDocenteController::class, 'index'])
            ->name('qr-docentes.index');
        Route::post('qr-docentes/scan', [AsistenciaDocenteController::class, 'scan'])
            ->name('qr-docentes.scan');
        Route::get('qr-docentes/historial', [AsistenciaDocenteController::class, 'historial'])
            ->name('qr-docentes.historial');
        Route::get('imprimir-qr', [ImprimirQrController::class, 'index'])->name('imprimir-qr.index');
        Route::get('imprimir-qr/estudiantes', [ImprimirQrController::class, 'estudiantes'])->name('imprimir-qr.estudiantes');
        Route::get('imprimir-qr/docentes', [ImprimirQrController::class, 'docentes'])->name('imprimir-qr.docentes');
        Route::get('imprimir-qr/secretaria', [ImprimirQrController::class, 'secretaria'])->name('imprimir-qr.secretaria');
        Route::get('imprimir-qr/auxiliar', [ImprimirQrController::class, 'auxiliar'])->name('imprimir-qr.auxiliar');
        Route::get('horarios', [HorarioEscolarController::class, 'index'])->name('horarios.index');
        Route::get('horarios/lista', [HorarioEscolarController::class, 'lista'])->name('horarios.lista');
        Route::get('horarios/lista/excel', [HorarioEscolarController::class, 'listaExcel'])->name('horarios.lista.excel');
        Route::post('horarios/programar', [HorarioEscolarController::class, 'programar'])->name('horarios.programar');
        Route::post('horarios/{horario}', [HorarioEscolarController::class, 'update'])
            ->whereNumber('horario')
            ->name('horarios.update');
        Route::get('auditoria', [AdminAuditoriaController::class, 'index'])
            ->name('auditoria.index');
        Route::get('backups', [AdminBackupController::class, 'index'])
            ->name('backups.index');
        Route::get('soporte', [AdminSoporteController::class, 'index'])
            ->name('soporte.index');
        Route::get('configuracion', [AdminConfiguracionController::class, 'index'])
            ->name('configuracion.index');
        Route::get('matriculas', [MatriculaController::class, 'index'])->name('matriculas.index');
        Route::get('matriculas/pre-matriculas', [MatriculaController::class, 'preMatriculas'])->name('matriculas.pre-matriculas');
        Route::get('matriculas/paso-1', [MatriculaController::class, 'paso1'])->name('matriculas.paso1');
        Route::post('matriculas/paso-1', [MatriculaController::class, 'guardarPaso1'])->name('matriculas.paso1.guardar');
        Route::get('matriculas/paso-2', [MatriculaController::class, 'paso2'])->name('matriculas.paso2');
        Route::post('matriculas/paso-2', [MatriculaController::class, 'guardarPaso2'])->name('matriculas.paso2.guardar');
        Route::get('matriculas/paso-3', [MatriculaController::class, 'paso3'])->name('matriculas.paso3');
        Route::post('matriculas/paso-3', [MatriculaController::class, 'guardarPaso3'])->name('matriculas.paso3.guardar');
        Route::get('matriculas/paso-4', [MatriculaController::class, 'paso4'])->name('matriculas.paso4');
        Route::post('matriculas/paso-4', [MatriculaController::class, 'guardarPaso4'])->name('matriculas.paso4.guardar');
        Route::get('matriculas/paso-5', [MatriculaController::class, 'paso5'])->name('matriculas.paso5');
        Route::post('matriculas/paso-5/finalizar', [MatriculaController::class, 'finalizarPaso5'])->name('matriculas.paso5.finalizar');
        Route::get('matriculas/exportar', [MatriculaController::class, 'exportarMatriculas'])->name('matriculas.exportar');
        Route::get('matriculas/buscar-apoderado', [MatriculaController::class, 'buscarApoderado'])->name('matriculas.buscar-apoderado');
        Route::post('matriculas/secciones', [MatriculaController::class, 'guardarSeccionModal'])->name('matriculas.secciones.guardar');
        Route::get('matriculas/{matricula}/resumen', [MatriculaController::class, 'resumen'])->name('matriculas.resumen');
        Route::get('matriculas/{matricula}/nota-venta', [MatriculaController::class, 'notaVenta'])->name('matriculas.nota-venta');
        Route::post('matriculas/{matricula}/credenciales', [MatriculaController::class, 'resetCredenciales'])->name('matriculas.credenciales');
        Route::post('matriculas/{matricula}/credenciales-personalizadas', [MatriculaController::class, 'setCredenciales'])->name('matriculas.credenciales.set');
        Route::resource('tarifas', AdminTarifaController::class)->only([
            'index',
            'create',
            'store',
            'edit',
            'update',
            'destroy'
        ]);
        Route::put('tarifas/politicas/{anio}', [AdminPoliticaPagoController::class, 'update'])
            ->name('tarifas.politicas.update');
        Route::resource('descuentos', AdminDescuentoController::class)->only([
            'index',
            'create',
            'store',
            'edit',
            'update',
            'destroy'
        ]);

        // Finanzas
        Route::get('finanzas/ingresos', [AdminFinanzasController::class, 'ingresos'])
            ->name('finanzas.ingresos');
        Route::get('finanzas/ingresos/excel', [AdminFinanzasController::class, 'exportarIngresosExcel'])
            ->name('finanzas.ingresos.excel');
        Route::get('finanzas/egresos', [AdminFinanzasController::class, 'egresos'])
            ->name('finanzas.egresos');
        Route::post('finanzas/egresos', [AdminFinanzasController::class, 'registrarEgreso'])
            ->name('finanzas.egresos.store');
        Route::get('finanzas/egresos/csv', [AdminFinanzasController::class, 'exportarEgresosCsv'])
            ->name('finanzas.egresos.csv');
        Route::patch('finanzas/egresos/{egreso}/anular', [AdminFinanzasController::class, 'anularEgreso'])
            ->name('finanzas.egresos.anular');
        Route::get('finanzas/flujo-caja', [AdminFinanzasController::class, 'flujoCaja'])
            ->name('finanzas.flujo-caja');
        // Cierre de notas
        Route::get('notas/cierre', [DirectorNotasController::class, 'index'])->name('notas.cierre');
        Route::post('notas/estadisticas', [DirectorNotasController::class, 'estadisticas'])->name('notas.estadisticas');
        Route::post('notas/faltantes', [DirectorNotasController::class, 'faltantes'])->name('notas.faltantes');
        Route::post('notas/cerrar', [DirectorNotasController::class, 'cerrar'])->name('notas.cerrar');
        Route::post('notas/reabrir', [DirectorNotasController::class, 'reabrir'])->name('notas.reabrir');
        Route::get('notas/historial', [DirectorNotasController::class, 'historial'])->name('notas.historial');
        Route::get('notas/exportar', [DirectorNotasController::class, 'exportar'])->name('notas.exportar');
        Route::post('notas/estado-cierre', [DirectorNotasController::class, 'estadoCierre'])->name('notas.estado-cierre');
        // Productos
        Route::get('productos', [SecretariaProductoController::class, 'index'])->name('productos.index');
        Route::get('productos/categorias', [SecretariaProductoController::class, 'categorias'])->name('productos.categorias');
        Route::get('productos/movimientos', [SecretariaProductoController::class, 'movimientos'])->name('productos.movimientos');
        Route::get('productos/crear', [SecretariaProductoController::class, 'create'])->name('productos.create');
        Route::post('productos', [SecretariaProductoController::class, 'store'])->name('productos.store');
        Route::post('productos/ventas', [SecretariaProductoController::class, 'storeVenta'])->name('productos.ventas.store');
        Route::get('productos/ventas/{venta}/voucher', [SecretariaProductoController::class, 'voucher'])->name('productos.ventas.voucher');
        Route::put('productos/{producto}', [SecretariaProductoController::class, 'update'])->name('productos.update');
        Route::delete('productos/{producto}', [SecretariaProductoController::class, 'destroy'])->name('productos.destroy');
        Route::post('productos/categorias', [SecretariaProductoController::class, 'storeCategoria'])->name('productos.categorias.store');
        Route::patch('productos/categorias/{categoria}', [SecretariaProductoController::class, 'updateCategoria'])->name('productos.categorias.update');
        Route::delete('productos/categorias/{categoria}', [SecretariaProductoController::class, 'destroyCategoria'])->name('productos.categorias.destroy');
        Route::get('productos/buscar', [SecretariaProductoController::class, 'buscar'])->name('productos.buscar');
        Route::get('productos/buscar-alumno', [SecretariaProductoController::class, 'buscarAlumno'])->name('productos.buscar-alumno');
        // Incidencias
        Route::resource('incidencias', SecretariaIncidenciaController::class)->only(['index', 'store', 'update', 'destroy']);
        // Caja
        Route::get('caja', [CajaController::class, 'index'])->name('caja.index');
        Route::post('caja/abrir', [CajaController::class, 'abrir'])->name('caja.abrir');
        Route::get('caja/historial', [CajaController::class, 'historial'])->name('caja.historial');
        Route::get('caja/historial-secretarias', [CajaController::class, 'historialGlobal'])->name('caja.historial-secretarias');
        Route::get('caja/{caja}', [CajaController::class, 'show'])->name('caja.show');
        Route::get('caja/{caja}/cierre', [CajaController::class, 'cierre'])->name('caja.cierre');
        Route::post('caja/{caja}/cerrar', [CajaController::class, 'cerrar'])->name('caja.cerrar');
        // Pagos
        Route::get('pagos/buscar-estudiante', [PagoController::class, 'buscarEstudiante'])->name('pagos.buscar-estudiante');
        Route::get('pagos/deudas-estudiante', [PagoController::class, 'deudasEstudiante'])->name('pagos.deudas-estudiante');
        Route::get('pagos/historial-estudiante', [PagoController::class, 'historialEstudiante'])->name('pagos.historial-estudiante');
        Route::get('pagos/{pago}/voucher', [PagoController::class, 'voucher'])->name('pagos.voucher');
        Route::patch('pagos/{pago}/anular', [PagoController::class, 'anular'])->name('pagos.anular');
        Route::delete('pagos/{pago}/vouchers/{voucher}', [PagoController::class, 'destroyVoucher'])->name('pagos.vouchers.destroy');
        Route::resource('pagos', PagoController::class);
        Route::get('reportes/pagos-dia', [ReporteController::class, 'pagosDelDia'])->name('reportes.pagos-dia');
        Route::get('reportes/pagos-dia-excel', [ReporteController::class, 'pagosDelDiaExcel'])->name('reportes.pagos-dia.excel');
        // Retiros
        Route::get('retiros', [RetiroController::class, 'index'])->name('retiros.index');
        Route::get('retiros/create', [RetiroController::class, 'create'])->name('retiros.create');
        Route::post('retiros', [RetiroController::class, 'store'])->name('retiros.store');
        Route::get('retiros/{matricula}', [RetiroController::class, 'show'])->name('retiros.show');
        // Gestión de Periodos Académicos
        Route::get('periodos', [AdminPeriodoController::class, 'index'])->name('periodos.index');
        Route::get('periodos/tarifa-vacacional', [AdminPeriodoController::class, 'tarifaVacacional'])->name('periodos.tarifa-vacacional');
        Route::post('periodos/tarifa-vacacional', [AdminPeriodoController::class, 'guardarTarifaVacacional'])->name('periodos.tarifa-vacacional.guardar');
        Route::get('periodos/{periodo}/edit', [AdminPeriodoController::class, 'edit'])->name('periodos.edit');
        Route::put('periodos/{periodo}', [AdminPeriodoController::class, 'update'])->name('periodos.update');
        // Caja
        Route::get('caja', [CajaController::class, 'index'])->name('caja.index');
        Route::post('caja/abrir', [CajaController::class, 'abrir'])->name('caja.abrir');
        Route::get('caja/{caja}/cierre', [CajaController::class, 'cierre'])->name('caja.cierre');
        Route::post('caja/{caja}/cerrar', [CajaController::class, 'cerrar'])->name('caja.cerrar');
        Route::get('caja/historial', [CajaController::class, 'historial'])->name('caja.historial');
    });

Route::middleware(['auth', 'role:Director|Secretaria|SuperAdmin'])
    ->prefix('gestionar-secciones')
    ->name('gestionar-secciones.')
    ->group(function () {
        Route::get('/', [MatriculaController::class, 'directorSecciones'])->name('index');
        Route::post('/', [MatriculaController::class, 'guardarSeccionModal'])->name('guardar');
        Route::post('/{seccion}/cursos', [MatriculaController::class, 'guardarCursosSeccionDirector'])->name('cursos.guardar');
        Route::put('/{seccion}', [MatriculaController::class, 'actualizarSeccionDirector'])->name('update');
        Route::delete('/{seccion}', [MatriculaController::class, 'eliminarSeccionDirector'])->name('destroy');
    });

Route::middleware(['auth', 'role:Director|SuperAdmin'])
    ->prefix('director')
    ->name('director.')
    ->group(function () {
        Route::view('/dashboard', 'dashboards.director')->name('dashboard');
        Route::get('personal', [DirectorPersonalController::class, 'index'])->name('personal.index');
        Route::post('personal/docentes', [DirectorPersonalController::class, 'storeDocente'])
            ->name('personal.docentes.store');
        Route::post('personal/secretarias', [DirectorPersonalController::class, 'storeSecretaria'])
            ->name('personal.secretarias.store');
        Route::put('personal/{user}', [DirectorPersonalController::class, 'update'])
            ->name('personal.update');
        Route::get('horarios', [HorarioEscolarController::class, 'index'])->name('horarios.index');
        Route::get('horarios/lista', [HorarioEscolarController::class, 'lista'])->name('horarios.lista');
        Route::get('horarios/lista/excel', [HorarioEscolarController::class, 'listaExcel'])
            ->name('horarios.lista.excel');
        Route::post('horarios/programar', [HorarioEscolarController::class, 'programar'])->name('horarios.programar');
        Route::post('horarios/{horario}', [HorarioEscolarController::class, 'update'])
            ->whereNumber('horario')
            ->name('horarios.update');
        Route::get('docentes/asignaciones', [DocenteCursoController::class, 'index'])->name('docentes.asignaciones');
        Route::post('docentes/asignaciones', [DocenteCursoController::class, 'store'])->name('docentes.asignaciones.guardar');
        Route::get('asistencias-docentes', [AsistenciaDocenteReporteController::class, 'indexDirector'])
            ->name('asistencias-docentes.index');
        Route::get('asistencias-docentes/excel', [AsistenciaDocenteReporteController::class, 'exportDirector'])
            ->name('asistencias-docentes.excel');
        Route::get('qr-docentes', [AsistenciaDocenteController::class, 'index'])
            ->name('qr-docentes.index');
        Route::post('qr-docentes/scan', [AsistenciaDocenteController::class, 'scan'])
            ->name('qr-docentes.scan');
        Route::get('qr-docentes/historial', [AsistenciaDocenteController::class, 'historial'])
            ->name('qr-docentes.historial');
        Route::get('imprimir-qr', [ImprimirQrController::class, 'index'])->name('imprimir-qr.index');
        Route::get('imprimir-qr/estudiantes', [ImprimirQrController::class, 'estudiantes'])->name('imprimir-qr.estudiantes');
        Route::get('imprimir-qr/docentes', [ImprimirQrController::class, 'docentes'])->name('imprimir-qr.docentes');
        Route::get('imprimir-qr/secretaria', [ImprimirQrController::class, 'secretaria'])->name('imprimir-qr.secretaria');
        Route::get('imprimir-qr/auxiliar', [ImprimirQrController::class, 'auxiliar'])->name('imprimir-qr.auxiliar');
        Route::get('notas/cierre', [DirectorNotasController::class, 'index'])->name('notas.cierre');
        Route::post('notas/estadisticas', [DirectorNotasController::class, 'estadisticas'])->name('notas.estadisticas');
        Route::post('notas/faltantes', [DirectorNotasController::class, 'faltantes'])->name('notas.faltantes');
        Route::post('notas/cerrar', [DirectorNotasController::class, 'cerrar'])->name('notas.cerrar');
        Route::post('notas/reabrir', [DirectorNotasController::class, 'reabrir'])->name('notas.reabrir');
        Route::get('notas/historial', [DirectorNotasController::class, 'historial'])->name('notas.historial');
        Route::get('notas/exportar', [DirectorNotasController::class, 'exportar'])->name('notas.exportar');
        Route::post('notas/estado-cierre', [DirectorNotasController::class, 'estadoCierre'])->name('notas.estado-cierre');
    });

Route::middleware(['auth', 'role:Secretaria|SuperAdmin', 'cleanup.matricula.draft'])
    ->prefix('secretaria')
    ->name('secretaria.')
    ->group(function () {
        Route::get('/dashboard', [SecretariaDashboardController::class, 'index'])->name('dashboard');
        Route::get('perfil', function () {
            return view('secretaria.perfil', ['user' => Auth::user()]);
        })->name('perfil');
        Route::resource('usuarios', PersonalSecretariaController::class)->only(['index', 'store']);
        Route::put('usuarios/{docente}/credenciales', [PersonalSecretariaController::class, 'updateCredenciales'])
            ->name('usuarios.credenciales');
        Route::get('asistencias', [SecretariaAsistenciaController::class, 'index'])->name('asistencias.index');
        Route::resource('estudiantes', EstudianteController::class);
        Route::get('estudiantes/{estudiante}/pagos', [EstudianteController::class, 'pagos'])
            ->name('estudiantes.pagos');
        Route::get('estudiantes/{estudiante}/carnet', [EstudianteController::class, 'carnet'])
            ->name('estudiantes.carnet');
        Route::get('estudiantes/{estudiante}/info', [EstudianteController::class, 'info'])
            ->name('estudiantes.info');
        Route::resource('incidencias', SecretariaIncidenciaController::class)->only(['index', 'create', 'store']);
        Route::get('productos', [SecretariaProductoController::class, 'index'])->name('productos.index');
        Route::get('productos/categorias', [SecretariaProductoController::class, 'categorias'])->name('productos.categorias');
        Route::get('productos/movimientos', [SecretariaProductoController::class, 'movimientos'])->name('productos.movimientos');
        Route::get('productos/crear', [SecretariaProductoController::class, 'create'])->name('productos.create');
        Route::post('productos', [SecretariaProductoController::class, 'store'])->name('productos.store');
        Route::post('productos/ventas', [SecretariaProductoController::class, 'storeVenta'])->name('productos.ventas.store');
        Route::get('productos/ventas/{venta}/voucher', [SecretariaProductoController::class, 'voucher'])
            ->name('productos.ventas.voucher');
        Route::get('productos/buscar-alumno', [SecretariaProductoController::class, 'buscarAlumno'])->name('productos.buscar-alumno');
        Route::put('productos/{producto}', [SecretariaProductoController::class, 'update'])->name('productos.update');
        Route::delete('productos/{producto}', [SecretariaProductoController::class, 'destroy'])->name('productos.destroy');
        Route::post('productos/categorias', [SecretariaProductoController::class, 'storeCategoria'])->name('productos.categorias.store');
        Route::patch('productos/categorias/{categoria}', [SecretariaProductoController::class, 'updateCategoria'])->name('productos.categorias.update');
        Route::delete('productos/categorias/{categoria}', [SecretariaProductoController::class, 'destroyCategoria'])->name('productos.categorias.destroy');
        Route::resource('apoderados', ApoderadoController::class);
        Route::get('matriculas', [MatriculaController::class, 'index'])->name('matriculas.index');
        Route::get('matriculas/pre-matriculas', [MatriculaController::class, 'preMatriculas'])->name('matriculas.pre-matriculas');
        Route::get('matriculas/paso-1', [MatriculaController::class, 'paso1'])->name('matriculas.paso1');
        Route::post('matriculas/paso-1', [MatriculaController::class, 'guardarPaso1'])->name('matriculas.paso1.guardar');
        Route::get('matriculas/paso-2', [MatriculaController::class, 'paso2'])->name('matriculas.paso2');
        Route::post('matriculas/paso-2', [MatriculaController::class, 'guardarPaso2'])->name('matriculas.paso2.guardar');
        Route::get('matriculas/paso-3', [MatriculaController::class, 'paso3'])->name('matriculas.paso3');
        Route::post('matriculas/paso-3', [MatriculaController::class, 'guardarPaso3'])->name('matriculas.paso3.guardar');
        Route::get('matriculas/paso-4', [MatriculaController::class, 'paso4'])->name('matriculas.paso4');
        Route::post('matriculas/paso-4', [MatriculaController::class, 'guardarPaso4'])->name('matriculas.paso4.guardar');
        Route::get('matriculas/paso-5', [MatriculaController::class, 'paso5'])->name('matriculas.paso5');
        Route::post('matriculas/paso-5/finalizar', [MatriculaController::class, 'finalizarPaso5'])
            ->name('matriculas.paso5.finalizar');
        Route::get('matriculas/exportar', [MatriculaController::class, 'exportarMatriculas'])->name('matriculas.exportar');
        Route::get('matriculas/{matricula}/resumen', [MatriculaController::class, 'resumen'])->name('matriculas.resumen');
        Route::get('matriculas/{matricula}/nota-venta', [MatriculaController::class, 'notaVenta'])->name('matriculas.nota-venta');
        Route::post('matriculas/{matricula}/credenciales', [MatriculaController::class, 'resetCredenciales'])
            ->name('matriculas.credenciales');
        Route::post('matriculas/{matricula}/credenciales-personalizadas', [MatriculaController::class, 'setCredenciales'])
            ->name('matriculas.credenciales.set');
        Route::post('matriculas/secciones', [MatriculaController::class, 'guardarSeccionModal'])->name('matriculas.secciones.guardar');
        Route::get('matriculas/buscar-apoderado', [MatriculaController::class, 'buscarApoderado'])->name('matriculas.buscar-apoderado');

        // Retiros
        Route::get('retiros', [RetiroController::class, 'index'])->name('retiros.index');
        Route::get('retiros/create', [RetiroController::class, 'create'])->name('retiros.create');
        Route::post('retiros', [RetiroController::class, 'store'])->name('retiros.store');
        Route::get('retiros/{matricula}', [RetiroController::class, 'show'])->name('retiros.show');

        Route::get('pagos/buscar-estudiante', [PagoController::class, 'buscarEstudiante'])->name('pagos.buscar-estudiante');
        Route::get('pagos/deudas-estudiante', [PagoController::class, 'deudasEstudiante'])->name('pagos.deudas-estudiante');
        Route::get('pagos/historial-estudiante', [PagoController::class, 'historialEstudiante'])->name('pagos.historial-estudiante');
        Route::get('pagos/{pago}/voucher', [PagoController::class, 'voucher'])->name('pagos.voucher');
        Route::patch('pagos/{pago}/anular', [PagoController::class, 'anular'])->name('pagos.anular');
        Route::delete('pagos/{pago}/vouchers/{voucher}', [PagoController::class, 'destroyVoucher'])->name('pagos.vouchers.destroy');
        Route::resource('pagos', PagoController::class);
        Route::get('caja', [CajaController::class, 'index'])->name('caja.index');
        Route::post('caja/abrir', [CajaController::class, 'abrir'])->name('caja.abrir');
        Route::get('caja/historial', [CajaController::class, 'historial'])->name('caja.historial');
        Route::get('caja/{caja}', [CajaController::class, 'show'])->name('caja.show');
        Route::get('caja/{caja}/cierre', [CajaController::class, 'cierre'])->name('caja.cierre');
        Route::post('caja/{caja}/cerrar', [CajaController::class, 'cerrar'])->name('caja.cerrar');
        Route::get('reportes/pagos-dia', [ReporteController::class, 'pagosDelDia'])->name('reportes.pagos-dia');
        Route::get('reportes/pagos-dia-excel', [ReporteController::class, 'pagosDelDiaExcel'])
            ->name('reportes.pagos-dia.excel');
        Route::get('reportes/morosos', [ReporteController::class, 'morosos'])->name('reportes.morosos');
        Route::get('reportes/morosos-excel', [ReporteController::class, 'morososExcel'])
            ->name('reportes.morosos.excel');
        Route::get('reportes/pensiones', [ReporteController::class, 'pensionesMensuales'])->name('reportes.pensiones');
        Route::get('reportes/pensiones-excel', [ReporteController::class, 'pensionesExcel'])->name('reportes.pensiones.excel');
        Route::get('reportes/alumno', [ReporteController::class, 'alumno'])->name('reportes.alumno');
        Route::get('reportes/alumno-pagos-excel', [ReporteController::class, 'alumnoPagosExcel'])
            ->name('reportes.alumno.pagos.excel');
        Route::get('reportes/alumno-deudas-excel', [ReporteController::class, 'alumnoDeudasExcel'])
            ->name('reportes.alumno.deudas.excel');
        Route::get('productos/buscar', [SecretariaProductoController::class, 'buscar'])
            ->name('productos.buscar');


        Route::get('asistencias-docentes', [AsistenciaDocenteController::class, 'index'])
            ->name('asistencias-docentes.index');

        Route::post('asistencias-docentes/scan', [AsistenciaDocenteController::class, 'scan'])
            ->name('asistencias-docentes.scan');

        Route::get('asistencias-docentes/historial', [AsistenciaDocenteController::class, 'historial'])
            ->name('asistencias-docentes.historial');
        // Secretaria tambien registra la asistencia por QR: suele ser quien
        // esta en la puerta al inicio de la jornada.
        Route::get('qr-docentes', [AsistenciaDocenteController::class, 'index'])
            ->name('qr-docentes.index');
        Route::post('qr-docentes/scan', [AsistenciaDocenteController::class, 'scan'])
            ->name('qr-docentes.scan');
        Route::get('qr-docentes/historial', [AsistenciaDocenteController::class, 'historial'])
            ->name('qr-docentes.historial');
        Route::get('imprimir-qr', [ImprimirQrController::class, 'index'])->name('imprimir-qr.index');
        Route::get('imprimir-qr/estudiantes', [ImprimirQrController::class, 'estudiantes'])->name('imprimir-qr.estudiantes');
        Route::get('imprimir-qr/docentes', [ImprimirQrController::class, 'docentes'])->name('imprimir-qr.docentes');
        Route::get('imprimir-qr/secretaria', [ImprimirQrController::class, 'secretaria'])->name('imprimir-qr.secretaria');
        Route::get('imprimir-qr/auxiliar', [ImprimirQrController::class, 'auxiliar'])->name('imprimir-qr.auxiliar');
        Route::get('usuarios/{docente}/carnet', [PersonalSecretariaController::class, 'carnet'])
            ->name('usuarios.carnet');
        Route::get('usuarios/{docente}/carnet-modal', [PersonalSecretariaController::class, 'carnetModal'])
            ->name('usuarios.carnet.modal');

        Route::get('mi-carnet', [\App\Http\Controllers\Secretaria\SecretariaCarnetController::class, 'miCarnet'])
            ->name('mi-carnet');
        Route::get('mis-asistencias', [\App\Http\Controllers\Secretaria\SecretariaCarnetController::class, 'misAsistencias'])
            ->name('mis-asistencias');
    });

Route::middleware(['auth', 'role:Docente'])
    ->prefix('docente')
    ->name('docente.')
    ->group(function () {

        Route::get('/dashboard', [DocenteDashboardController::class, 'index'])->name('dashboard');

        Route::get('perfil', function () {
            return view('docente.perfil', ['user' => Auth::user()]);
        })->name('perfil');

        // ✅ Asistencia (incluye QR)
        Route::get('asistencias/qr', [AsistenciaController::class, 'qr'])->name('asistencias.qr');
        Route::post('asistencias/scan', [AsistenciaController::class, 'scan'])->name('asistencias.scan');

        Route::resource('asistencias', AsistenciaController::class)->only(['index', 'create', 'store']);
        Route::get('asistencias/historial', [AsistenciaController::class, 'historial'])->name('asistencias.historial');

        // ✅ Notas (con conceptos y detalles)
        Route::get('notas/conceptos', [NotaController::class, 'getConceptos'])->name('notas.conceptos');
        Route::post('notas/conceptos', [NotaController::class, 'saveConceptos'])->name('notas.conceptos.save');
        Route::post('notas/guardar-detalles', [NotaController::class, 'saveDetalles'])->name('notas.detalles.save');
        Route::resource('notas', NotaController::class);

        // ✅ Incidencias
        Route::resource('incidencias', IncidenciaController::class);

        // ✅ Horarios
        Route::get('horarios', [DocenteHorarioController::class, 'index'])->name('horarios.index');
        Route::get('horarios/excel', [DocenteHorarioController::class, 'excel'])->name('horarios.excel');

        Route::get('asistencias/qr', [AsistenciaController::class, 'qr'])->name('asistencias.qr');
        Route::post('asistencias/scan', [AsistenciaController::class, 'scan'])->name('asistencias.scan');
        Route::get('carnet', [\App\Http\Controllers\Docente\CarnetController::class, 'show'])
            ->name('carnet.show');
        Route::get('mis-asistencias', [\App\Http\Controllers\Docente\AsistenciaDocenteController::class, 'index'])
            ->name('asistencia-docente.index');
    });

Route::middleware(['auth', 'role:Auxiliar'])
    ->prefix('auxiliar')
    ->name('auxiliar.')
    ->group(function () {

        Route::get('/dashboard', [AuxiliarDashboardController::class, 'index'])->name('dashboard');

        Route::get('perfil', function () {
            return view('auxiliar.perfil', ['user' => Auth::user()]);
        })->name('perfil');

        // Asistencias (incluye QR)
        Route::get('asistencias/qr', [AuxiliarAsistenciaController::class, 'qr'])->name('asistencias.qr');
        Route::post('asistencias/scan', [AuxiliarAsistenciaController::class, 'scan'])->name('asistencias.scan');
        Route::resource('asistencias', AuxiliarAsistenciaController::class)->only(['index', 'create', 'store']);
        Route::get('asistencias/historial', [AuxiliarAsistenciaController::class, 'historial'])->name('asistencias.historial');

        // Carnet
        Route::get('carnet', [AuxiliarCarnetController::class, 'show'])->name('carnet.show');

        // Mis Asistencias
        Route::get('mis-asistencias', [\App\Http\Controllers\Auxiliar\MisAsistenciasController::class, 'index'])->name('mis-asistencias.index');
    });

Route::middleware(['auth', 'role:Estudiante'])
    ->prefix('estudiante')
    ->name('estudiante.')
    ->group(function () {
        Route::get('/dashboard', [EstudianteDashboardController::class, 'index'])->name('dashboard');
        Route::get('/carnet/descargar', [EstudianteCarnetController::class, 'descargar'])->name('carnet.descargar');
        Route::get('/asistencias', [EstudianteAsistenciaController::class, 'index'])->name('asistencias.index');
        Route::get('/notas', [EstudianteNotaController::class, 'index'])->name('notas.index');
        Route::get('/deudas', [EstudianteDeudaController::class, 'index'])->name('deudas.index');
        Route::get('/contrasena', [EstudianteCuentaController::class, 'password'])->name('password');
        Route::get('/pagos/virtual', [EstudiantePagoController::class, 'create'])->name('pagos.create');
        Route::post('/pagos/virtual', [EstudiantePagoController::class, 'store'])->name('pagos.store');
        Route::get('/notas/detalle', [EstudianteNotaController::class, 'detalle'])
            ->name('notas.detalle');
        Route::get('/notas/detalle-todo', [EstudianteNotaController::class, 'detalleTodo'])
            ->name('notas.detalleTodo');
    });

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';

Route::fallback(function () {
    return redirect()->route('login');
});


/*
|--------------------------------------------------------------------------
| Modulos del documento de requerimientos v1.0
|--------------------------------------------------------------------------
| Se agrupan por permiso y no por rol, porque son transversales: los usa
| mas de un perfil (direccion, secretaria, docentes, bibliotecario...).
*/

// Datos institucionales y horarios (RF-03, RF-10, RF-29, RF-30)
Route::middleware(['auth', 'permission:institucion.gestionar'])
    ->prefix('institucion')
    ->name('institucion.')
    ->group(function () {
        Route::get('/', [InstitucionController::class, 'index'])->name('index');
        Route::put('/', [InstitucionController::class, 'update'])->name('update');
        Route::post('valores', [InstitucionController::class, 'guardarValor'])->name('valores.guardar');
        Route::delete('valores/{valor}', [InstitucionController::class, 'eliminarValor'])->name('valores.eliminar');
        Route::post('frases', [InstitucionController::class, 'guardarFrase'])->name('frases.guardar');
        Route::delete('frases/{frase}', [InstitucionController::class, 'eliminarFrase'])->name('frases.eliminar');
        Route::post('horarios', [InstitucionController::class, 'guardarHorario'])->name('horarios.guardar');
        Route::delete('horarios/{horario}', [InstitucionController::class, 'eliminarHorario'])->name('horarios.eliminar');
    });

// Sedes o locales y sus documentos publicables (RF-08, RF-09, RF-10, RF-20)
Route::middleware(['auth', 'permission:sede.ver'])
    ->prefix('sedes')
    ->name('sedes.')
    ->group(function () {
        Route::get('/', [SedeController::class, 'index'])->name('index');
        Route::get('{sede}/documentos', [SedeController::class, 'documentos'])->name('documentos');

        Route::middleware('permission:sede.gestionar')->group(function () {
            Route::post('/', [SedeController::class, 'store'])->name('store');
            Route::put('{sede}', [SedeController::class, 'update'])->name('update');
            Route::delete('{sede}', [SedeController::class, 'destroy'])->name('destroy');
        });

        Route::middleware('permission:documento.gestionar')->group(function () {
            Route::post('{sede}/documentos', [SedeController::class, 'subirDocumento'])->name('documentos.subir');
            Route::delete('documentos/{documento}', [SedeController::class, 'eliminarDocumento'])->name('documentos.eliminar');
        });
    });

// MODULO 7 - Biblioteca (RF-45, RF-46)
Route::middleware(['auth', 'permission:biblioteca.ver'])
    ->prefix('biblioteca')
    ->name('biblioteca.')
    ->group(function () {
        Route::get('/', [BibliotecaController::class, 'index'])->name('index');
        Route::get('libros', [BibliotecaController::class, 'libros'])->name('libros');

        Route::middleware('permission:biblioteca.gestionar')->group(function () {
            Route::post('libros', [BibliotecaController::class, 'guardarLibro'])->name('libros.guardar');
            Route::delete('libros/{libro}', [BibliotecaController::class, 'eliminarLibro'])->name('libros.eliminar');
        });

        Route::middleware('permission:biblioteca.lectura_registrar')->group(function () {
            Route::get('lecturas/nueva', [BibliotecaController::class, 'crearLectura'])->name('lecturas.crear');
            Route::post('lecturas', [BibliotecaController::class, 'guardarLectura'])->name('lecturas.guardar');
            Route::get('lecturas/{lectura}/editar', [BibliotecaController::class, 'editarLectura'])->name('lecturas.editar');
            Route::put('lecturas/{lectura}', [BibliotecaController::class, 'guardarLectura'])->name('lecturas.actualizar');
            Route::delete('lecturas/{lectura}', [BibliotecaController::class, 'eliminarLectura'])->name('lecturas.eliminar');
        });
    });

// MODULO 8 - Comedor Realino (RF-47, RF-48, RF-49)
Route::middleware(['auth', 'permission:comedor.ver'])
    ->prefix('comedor')
    ->name('comedor.')
    ->group(function () {
        Route::get('/', [ComedorController::class, 'index'])->name('index');
        Route::get('evidencias', [ComedorController::class, 'evidencias'])->name('evidencias');

        Route::middleware('permission:comedor.registrar')->group(function () {
            Route::post('registros', [ComedorController::class, 'registrar'])->name('registrar');
            Route::delete('registros/{registro}', [ComedorController::class, 'eliminar'])->name('eliminar');
        });

        Route::middleware('permission:comedor.cobrar')->group(function () {
            Route::post('registros/{registro}/cobrar', [ComedorController::class, 'cobrar'])->name('cobrar');
        });

        Route::middleware('permission:comedor.evidencia')->group(function () {
            Route::post('evidencias', [ComedorController::class, 'subirEvidencia'])->name('evidencias.subir');
            Route::delete('evidencias/{evidencia}', [ComedorController::class, 'eliminarEvidencia'])->name('evidencias.eliminar');
        });
    });

// MODULO 2 - Admision (RF-11 a RF-20)
Route::middleware(['auth', 'permission:admision.ver'])
    ->prefix('admision')
    ->name('admision.')
    ->group(function () {
        Route::get('/', [AdmisionController::class, 'index'])->name('index');
        Route::get('examenes', [AdmisionController::class, 'examenes'])->name('examenes');
        Route::get('{postulante}/expediente', [AdmisionController::class, 'expediente'])->name('expediente');

        Route::middleware('permission:admision.registrar')->group(function () {
            // Asistente de 5 pasos
            Route::get('nuevo', [AdmisionController::class, 'crear'])->name('crear');
            Route::post('nuevo', [AdmisionController::class, 'guardarPaso1'])->name('paso1.guardar');
            Route::put('{postulante}/paso1', [AdmisionController::class, 'guardarPaso1'])->name('paso1.actualizar');

            Route::get('{postulante}/paso2', [AdmisionController::class, 'paso2'])->name('paso2');
            Route::put('{postulante}/paso2', [AdmisionController::class, 'guardarPaso2'])->name('paso2.guardar');

            Route::get('{postulante}/paso3', [AdmisionController::class, 'paso3'])->name('paso3');
            Route::post('{postulante}/documentos', [AdmisionController::class, 'subirDocumento'])->name('documentos.subir');
            Route::delete('documentos/{documento}', [AdmisionController::class, 'eliminarDocumento'])->name('documentos.eliminar');
            Route::post('{postulante}/paso3/continuar', [AdmisionController::class, 'continuarPaso3'])->name('paso3.continuar');

            Route::get('{postulante}/paso4', [AdmisionController::class, 'paso4'])->name('paso4');
            Route::post('{postulante}/pagos', [AdmisionController::class, 'registrarPago'])->name('pagos.registrar');
            Route::post('{postulante}/paso4/continuar', [AdmisionController::class, 'continuarPaso4'])->name('paso4.continuar');

            Route::get('{postulante}/paso5', [AdmisionController::class, 'paso5'])->name('paso5');
            Route::post('{postulante}/etapas', [AdmisionController::class, 'guardarEtapa'])->name('etapas.guardar');
            Route::post('{postulante}/finalizar', [AdmisionController::class, 'finalizar'])->name('finalizar');

            Route::delete('{postulante}', [AdmisionController::class, 'eliminar'])->name('eliminar');
        });

        Route::middleware('permission:admision.evaluar')->group(function () {
            Route::post('pagos/{pago}/validar', [AdmisionController::class, 'validarPago'])->name('pagos.validar');
            Route::post('{postulante}/examen', [AdmisionController::class, 'registrarExamen'])->name('examen.registrar');
            Route::post('examenes', [AdmisionController::class, 'guardarExamen'])->name('examenes.guardar');
            Route::delete('examenes/{examen}', [AdmisionController::class, 'eliminarExamen'])->name('examenes.eliminar');
        });

        // Admitido -> asistente de matricula con los datos del expediente precargados
        Route::middleware('role:Secretaria|SuperAdmin')->group(function () {
            Route::post('{postulante}/matricular', [AdmisionController::class, 'matricular'])->name('matricular');
        });

        Route::middleware('permission:admision.resultado')->group(function () {
            Route::post('{postulante}/resultado', [AdmisionController::class, 'publicarResultado'])->name('resultado');
        });
    });

// Archivador: carpetas libres de la direccion (subida de carpetas completas)
Route::middleware(['auth', 'role:SuperAdmin|Director'])
    ->prefix('archivador')
    ->name('archivador.')
    ->group(function () {
        Route::get('/', [ArchivadorController::class, 'index'])->name('index');
        Route::get('carpeta/{carpeta}', [ArchivadorController::class, 'index'])->name('carpeta');

        Route::post('carpetas', [ArchivadorController::class, 'crearCarpeta'])->name('carpetas.crear');
        Route::post('carpetas/{carpeta}/subcarpeta', [ArchivadorController::class, 'crearCarpeta'])
            ->name('carpetas.crear.dentro');
        Route::put('carpetas/{carpeta}', [ArchivadorController::class, 'renombrarCarpeta'])
            ->name('carpetas.renombrar');
        Route::delete('carpetas/{carpeta}', [ArchivadorController::class, 'eliminarCarpeta'])
            ->name('carpetas.eliminar');

        Route::post('subir', [ArchivadorController::class, 'subir'])->name('subir');
        Route::post('carpeta/{carpeta}/subir', [ArchivadorController::class, 'subir'])->name('subir.dentro');

        Route::get('archivo/{archivo}/ver', [ArchivadorController::class, 'ver'])->name('archivos.ver');
        Route::get('archivo/{archivo}/previsualizar', [ArchivadorController::class, 'previsualizar'])
            ->name('archivos.previsualizar');
        Route::get('archivo/{archivo}', [ArchivadorController::class, 'descargar'])->name('archivos.descargar');
        Route::put('archivo/{archivo}', [ArchivadorController::class, 'renombrarArchivo'])
            ->name('archivos.renombrar');
        Route::delete('archivo/{archivo}', [ArchivadorController::class, 'eliminarArchivo'])
            ->name('archivos.eliminar');
    });

// MODULOS 6, 12, 13 y 14 - Repositorio documental compartido
// (RF-41, RF-57, RF-61, RF-62, RF-63, RF-65, RF-70, RF-71, RF-72)
Route::middleware(['auth', 'permission:documento.ver'])
    ->prefix('documentos')
    ->name('documentos.')
    ->group(function () {
        Route::get('cumpleanos', [DocumentoController::class, 'cumpleanos'])->name('cumpleanos');
        Route::get('{modulo}', [DocumentoController::class, 'index'])->name('index');

        Route::middleware('permission:documento.gestionar')->group(function () {
            Route::post('{modulo}', [DocumentoController::class, 'store'])->name('store');
            Route::delete('archivo/{documento}', [DocumentoController::class, 'destroy'])->name('destroy');
        });
    });

// Reuniones, capacitaciones y cartas de compromiso
// (RF-43, RF-44, RF-60, RF-64, RF-65, RF-66)
Route::middleware(['auth', 'permission:reunion.ver'])
    ->prefix('reuniones')
    ->name('reuniones.')
    ->group(function () {
        Route::get('/', [ReunionController::class, 'index'])->name('index');
        Route::get('capacitaciones', [ReunionController::class, 'capacitaciones'])->name('capacitaciones');
        Route::get('compromisos', [ReunionController::class, 'compromisos'])->name('compromisos');

        Route::middleware('permission:reunion.gestionar')->group(function () {
            Route::post('/', [ReunionController::class, 'store'])->name('store');
            Route::put('{reunion}', [ReunionController::class, 'update'])->name('update');
            Route::delete('{reunion}', [ReunionController::class, 'destroy'])->name('destroy');
            Route::post('{reunion}/notificar', [ReunionController::class, 'notificar'])->name('notificar');

            Route::post('capacitaciones', [ReunionController::class, 'guardarCapacitacion'])
                ->name('capacitaciones.guardar');
            Route::delete('capacitaciones/{capacitacion}', [ReunionController::class, 'eliminarCapacitacion'])
                ->name('capacitaciones.eliminar');

            Route::post('compromisos', [ReunionController::class, 'guardarCompromiso'])
                ->name('compromisos.guardar');
            Route::delete('compromisos/{carta}', [ReunionController::class, 'eliminarCompromiso'])
                ->name('compromisos.eliminar');
        });
    });

// MODULO 9 - Psicologia (RF-50, RF-51). Cerrado por confidencialidad (RNF-05).
Route::middleware(['auth', 'permission:psicologia.ver'])
    ->prefix('psicologia')
    ->name('psicologia.')
    ->group(function () {
        Route::get('/', [PsicologiaController::class, 'index'])->name('index');

        Route::middleware('permission:psicologia.gestionar')->group(function () {
            Route::post('/', [PsicologiaController::class, 'store'])->name('store');
            Route::put('{atencion}', [PsicologiaController::class, 'update'])->name('update');
            Route::delete('{atencion}', [PsicologiaController::class, 'destroy'])->name('destroy');
        });
    });

// MODULO 10 - Real Academia (RF-52 a RF-55)
Route::middleware(['auth', 'permission:academia.ver'])
    ->prefix('academia')
    ->name('academia.')
    ->group(function () {
        Route::get('/', [AcademiaController::class, 'index'])->name('index');
        Route::get('horarios', [AcademiaController::class, 'horarios'])->name('horarios');
        Route::get('{alumno}/expediente', [AcademiaController::class, 'alumno'])->name('alumno');

        Route::middleware('permission:academia.gestionar')->group(function () {
            Route::post('/', [AcademiaController::class, 'store'])->name('store');
            Route::put('{alumno}', [AcademiaController::class, 'update'])->name('update');
            Route::delete('{alumno}', [AcademiaController::class, 'destroy'])->name('destroy');

            Route::post('{alumno}/asistencia', [AcademiaController::class, 'registrarAsistencia'])->name('asistencia');
            Route::post('{alumno}/evaluacion', [AcademiaController::class, 'registrarEvaluacion'])->name('evaluacion');
            Route::post('{alumno}/material', [AcademiaController::class, 'registrarMaterial'])->name('material');

            Route::post('horarios', [AcademiaController::class, 'guardarHorario'])->name('horarios.guardar');
            Route::delete('horarios/{horario}', [AcademiaController::class, 'eliminarHorario'])->name('horarios.eliminar');
        });
    });

// MODULO 11 - Pastoral (RF-56)
Route::middleware(['auth', 'permission:pastoral.ver'])
    ->prefix('pastoral')
    ->name('pastoral.')
    ->group(function () {
        Route::get('/', [PastoralController::class, 'index'])->name('index');

        Route::middleware('permission:pastoral.gestionar')->group(function () {
            Route::post('/', [PastoralController::class, 'store'])->name('store');
            Route::delete('{registro}', [PastoralController::class, 'destroy'])->name('destroy');
        });
    });

// MODULO 14 - Inmobiliario y Defensa Civil (RF-70, RF-71)
Route::middleware(['auth', 'permission:patrimonio.ver'])
    ->prefix('patrimonio')
    ->name('patrimonio.')
    ->group(function () {
        Route::get('activos', [PatrimonioController::class, 'activos'])->name('activos');
        Route::get('activos/{activo}/mantenimientos', [PatrimonioController::class, 'mantenimientos'])
            ->name('mantenimientos');
        Route::get('defensa-civil', [PatrimonioController::class, 'defensaCivil'])->name('defensa-civil');

        Route::middleware('permission:patrimonio.gestionar')->group(function () {
            Route::post('activos', [PatrimonioController::class, 'guardarActivo'])->name('activos.guardar');
            Route::delete('activos/{activo}', [PatrimonioController::class, 'eliminarActivo'])->name('activos.eliminar');
            Route::post('activos/{activo}/mantenimientos', [PatrimonioController::class, 'guardarMantenimiento'])
                ->name('mantenimientos.guardar');
            Route::delete('mantenimientos/{mantenimiento}', [PatrimonioController::class, 'eliminarMantenimiento'])
                ->name('mantenimientos.eliminar');

            Route::post('defensa-civil', [PatrimonioController::class, 'guardarDefensaCivil'])
                ->name('defensa-civil.guardar');
            Route::delete('defensa-civil/{actividad}', [PatrimonioController::class, 'eliminarDefensaCivil'])
                ->name('defensa-civil.eliminar');
        });
    });

// MODULO 5 - Academico: lo que faltaba (RF-31, RF-34, RF-35, RF-37, RF-38, RF-39)
Route::middleware(['auth', 'permission:academico.ver'])
    ->prefix('academico')
    ->name('academico.')
    ->group(function () {
        Route::get('modalidades', [AcademicoController::class, 'modalidades'])->name('modalidades');
        Route::get('informes', [AcademicoController::class, 'informes'])->name('informes');
        Route::get('rubricas', [AcademicoController::class, 'rubricas'])->name('rubricas');
        Route::get('evaluaciones', [AcademicoController::class, 'evaluaciones'])->name('evaluaciones');
        Route::get('evaluaciones/{evaluacion}/ranking', [AcademicoController::class, 'ranking'])->name('ranking');
        Route::get('recuperaciones', [AcademicoController::class, 'recuperaciones'])->name('recuperaciones');
        Route::get('proyectos', [AcademicoController::class, 'proyectos'])->name('proyectos');

        Route::middleware('permission:academico.gestionar')->group(function () {
            Route::put('modalidades/{grado}', [AcademicoController::class, 'guardarModalidad'])
                ->name('modalidades.guardar');

            Route::post('informes', [AcademicoController::class, 'guardarInforme'])->name('informes.guardar');
            Route::delete('informes/{informe}', [AcademicoController::class, 'eliminarInforme'])->name('informes.eliminar');

            Route::post('rubricas', [AcademicoController::class, 'guardarRubrica'])->name('rubricas.guardar');
            Route::delete('rubricas/{rubrica}', [AcademicoController::class, 'eliminarRubrica'])->name('rubricas.eliminar');

            Route::post('evaluaciones', [AcademicoController::class, 'guardarEvaluacion'])->name('evaluaciones.guardar');
            Route::delete('evaluaciones/{evaluacion}', [AcademicoController::class, 'eliminarEvaluacion'])
                ->name('evaluaciones.eliminar');
            Route::post('evaluaciones/{evaluacion}/resultado', [AcademicoController::class, 'guardarResultado'])
                ->name('resultado.guardar');

            Route::post('recuperaciones', [AcademicoController::class, 'guardarRecuperacion'])
                ->name('recuperaciones.guardar');
            Route::delete('recuperaciones/{recuperacion}', [AcademicoController::class, 'eliminarRecuperacion'])
                ->name('recuperaciones.eliminar');

            Route::post('proyectos', [AcademicoController::class, 'guardarProyecto'])->name('proyectos.guardar');
            Route::delete('proyectos/{proyecto}', [AcademicoController::class, 'eliminarProyecto'])
                ->name('proyectos.eliminar');
        });
    });
