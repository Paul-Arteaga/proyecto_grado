<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

// Controllers
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\PermisoController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\UsuarioRolController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\DisponibilidadController;
use App\Http\Controllers\ReservaController;
use App\Http\Controllers\PromocionController;
use App\Http\Controllers\ContratoController;
use App\Http\Controllers\DevolucionController;
use App\Http\Controllers\MantenimientoController;
use App\Http\Controllers\CotizacionController;
use App\Http\Controllers\TarifaController;
use App\Http\Controllers\DocumentoController;
use App\Http\Controllers\AccesorioController;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\VehiculoController;

/**
 * ==== Públicas: Landing / Login / Registro ====
 */
Route::get('/', [AuthController::class, 'welcome'])->name('home');                 // welcome
Route::post('/login', [AuthController::class, 'login'])->name('sendLogin');       // login

// Registro (form + store). El store muestra animación y luego redirige
Route::get('/registro', [AuthController::class, 'showRegister'])->name('register.show');
Route::post('/registro', [AuthController::class, 'storeRegister'])->name('register.store');

// Logout (desde zona autenticada)
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/**
 * ==== Público: media estática ====
 */
Route::get('/media/{path}', function (string $path) {
    $path = ltrim($path, '/');
    abort_unless(Storage::disk('public')->exists($path), 404);
    return Storage::disk('public')->response($path);
})->where('path', '.*');

/**
 * ==== Público opcional: auto-registro de cliente ====
 */
Route::get('/registro-cliente', [ClienteController::class, 'createPublic'])->name('registrarCliente.createPublic');
Route::post('/registro-cliente', [ClienteController::class, 'storePublic'])->name('guardarCliente.storePublic');

/**
 * ==== Privadas (protegidas con auth) ====
 * Todo lo que va dentro de este grupo requiere sesión iniciada
 */
Route::middleware(['auth'])->group(function () {

    /** Index (dashboard interno) */
    Route::prefix('/index')->group(function () {
        Route::get('/', [IndexController::class, 'index'])->name('mostrar.index');
    });

    /** Categoría */
    Route::prefix('/categoria')->group(function () {
        Route::get('/', [CategoriaController::class, 'index'])->name('categoria.index');
        Route::get('/create', [CategoriaController::class, 'create'])->name('categoria.create');
        Route::post('/', [CategoriaController::class, 'store'])->name('categoria.store');
        Route::get('/{categoria}/edit', [CategoriaController::class, 'edit'])->name('categoria.edit');
        Route::patch('/{categoria}', [CategoriaController::class, 'update'])->name('categoria.update');
        Route::patch('/{categoria}/vehiculos', [CategoriaController::class, 'syncVehiculos'])->name('categoria.syncVehiculos');
        Route::patch('/{categoria}/tarifas', [CategoriaController::class, 'syncTarifas'])->name('categoria.syncTarifas');
        Route::post('/{categoria}/verificar-compatibilidad', [CategoriaController::class, 'verificarCompatibilidad'])->name('categoria.verificarCompatibilidad');
        Route::patch('/{categoria}/estado', [CategoriaController::class, 'toggle'])->name('categoria.toggle');
    });

    /** Disponibilidad + Vehículos */
    Route::prefix('/disponibilidad')->group(function () {
        Route::get('/', [DisponibilidadController::class, 'index'])->name('disp.index');
        Route::get('/buscar', [DisponibilidadController::class, 'search'])->name('disp.search');
        Route::post('/asignar', [DisponibilidadController::class, 'asignarVehiculo'])->name('disp.asignar');

        Route::get('/vehiculo/create', [VehiculoController::class, 'create'])->name('disp.vehiculo.create');
        Route::post('/vehiculo', [VehiculoController::class, 'store'])->name('disp.vehiculo.store');
        Route::get('/vehiculo/{vehiculo}/edit', [VehiculoController::class, 'edit'])->name('vehiculo.edit');
        Route::patch('/vehiculo/{vehiculo}', [VehiculoController::class, 'update'])->name('vehiculo.update');

        Route::post('/bloquear', [DisponibilidadController::class, 'bloquear'])->name('disp.bloquear');
        Route::delete('/bloquear/{id}', [DisponibilidadController::class, 'desbloquear'])->name('disp.desbloquear');

        Route::post('/mantenimiento', [DisponibilidadController::class, 'programarMantenimiento'])->name('disp.mantener');
        Route::delete('/mantenimiento/{id}', [DisponibilidadController::class, 'liberarMantenimiento'])->name('disp.liberarMant');
    });

    /** Reservas */
    Route::prefix('/reservas')->group(function () {
        Route::get('/', [ReservaController::class, 'index'])->name('reservas.index');
        Route::post('/', [ReservaController::class, 'store'])->name('reservas.store');
        Route::put('/{reserva}', [ReservaController::class, 'update'])->name('reservas.update');
        Route::delete('/{reserva}', [ReservaController::class, 'destroy'])->name('reservas.destroy');
    });

    /** Roles */
    Route::prefix('/rol')->group(function () {
        Route::get('/', [RolController::class, 'index'])->name('mostrar.rol');
        Route::get('/create', [RolController::class, 'create'])->name('crearRol.create');
        Route::post('/', [RolController::class, 'store'])->name('guardarRol.store');
        Route::get('/{rol}/edit', [RolController::class, 'edit'])->name('editarRol.edit');
        Route::patch('/{rol}', [RolController::class, 'update'])->name('actualizarRol.update');
        Route::patch('/{rol}/estado', [RolController::class, 'toggleEstado'])->name('cambiarEstadoRol.toggle');
        Route::patch('/{rol}/permisos', [RolController::class, 'syncPermisos'])->name('gestionarPermisosDeRol.sync');
    });

    /** Usuarios (módulo interno) */
    Route::prefix('/usuario')->group(function () {
        Route::get('/', [UsuarioController::class, 'index'])->name('mostrar.usuario');
        Route::get('/create', [UsuarioController::class, 'create'])->name('crearUsuario.create');
        Route::post('/', [UsuarioController::class, 'store'])->name('guardarNuevo.store');
        Route::get('/{usuario}/edit', [UsuarioController::class, 'edit'])->name('editarUsuario.edit');
        Route::patch('/{usuario}', [UsuarioController::class, 'update'])->name('guardarEdicion.update');
        Route::patch('/{usuario}/estado', [UsuarioController::class, 'toggleEstado'])->name('cambiarEstadoUsuario.toggle');
        Route::post('/{usuario}/roles/{rol}', [UsuarioController::class, 'asignarRol'])->name('asignarRolAUsuario.asignar');
        Route::delete('/{usuario}/roles/{rol}', [UsuarioController::class, 'quitarRol'])->name('quitarRolDeUsuario.quitar');
    });

    /** Clientes (gestión interna) */
    Route::prefix('/cliente')->group(function () {
        Route::get('/', [ClienteController::class, 'index'])->name('mostrar.cliente');
        Route::get('/create', [ClienteController::class, 'create'])->name('registrarCliente.create');
        Route::post('/', [ClienteController::class, 'store'])->name('guardarCliente.store');
        Route::get('/{cliente}/edit', [ClienteController::class, 'edit'])->name('editarCliente.edit');
        Route::patch('/{cliente}', [ClienteController::class, 'update'])->name('actualizarCliente.update');
    });

    /** Permisos */
    Route::prefix('/permiso')->group(function(){
        Route::get('/', [PermisoController::class, 'index'])->name('mostrar.permiso');
        Route::post('/', [PermisoController::class, 'store'])->name('crear.permiso');
        Route::patch('/{permiso}', [PermisoController::class, 'update'])->name('editar.permiso');
        Route::delete('/{permiso}', [PermisoController::class, 'destroy'])->name('eliminar.permiso');
        Route::post('/{permiso}/roles/{rol}', [PermisoController::class, 'asignar'])->name('asignar.permiso');
        Route::delete('/{permiso}/roles/{rol}', [PermisoController::class, 'quitar'])->name('quitar.permiso');
    });

    /** Promociones */
    Route::prefix('/promocion')->group(function () {
        Route::get('/', [PromocionController::class, 'index'])->name('mostrar.promocion');
        Route::post('/', [PromocionController::class, 'store'])->name('crear.promocion');
        Route::patch('/{promocion}', [PromocionController::class, 'update'])->name('editar.promocion');
        Route::delete('/{promocion}', [PromocionController::class, 'destroy'])->name('eliminar.promocion');
        Route::patch('/{promocion}/activar', [PromocionController::class, 'activar'])->name('activar.promocion');
        Route::patch('/{promocion}/desactivar', [PromocionController::class, 'desactivar'])->name('desactivar.promocion');
    });

    /** Contratos */
    Route::prefix('/contrato')->group(function () {
        Route::get('/', [ContratoController::class, 'index'])->name('mostrar.contrato');
        Route::post('/generar-desde-reserva/{reserva}', [ContratoController::class, 'generarDesdeReserva'])->name('generar.contrato');
        Route::get('/{contrato}', [ContratoController::class, 'show'])->name('ver.contrato');
        Route::patch('/{contrato}', [ContratoController::class, 'update'])->name('editar.contrato');
        Route::delete('/{contrato}', [ContratoController::class, 'anular'])->name('anular.contrato');
        Route::post('/{contrato}/pdf', [ContratoController::class, 'regenerarPdf'])->name('reemitir.contrato');
        Route::get('/{contrato}/pdf', [ContratoController::class, 'pdf'])->name('pdf.contrato');
        Route::post('/{contrato}/enviar', [ContratoController::class, 'enviar'])->name('enviar.contrato');
        Route::post('/{contrato}/anexos', [ContratoController::class, 'agregarAnexo'])->name('anexo.agregar.contrato');
        Route::delete('/{contrato}/anexos/{anexo}', [ContratoController::class, 'quitarAnexo'])->name('anexo.quitar.contrato');
    });

    /** Devoluciones */
    Route::prefix('/devolucion')->group(function () {
        Route::get('/', [DevolucionController::class, 'index'])->name('mostrar.devolucion');
        Route::post('/{contrato}', [DevolucionController::class, 'store'])->name('crear.devolucion');
        Route::post('/{devolucion}/mantenimiento', [DevolucionController::class, 'programarMantenimiento'])->name('mantenimiento.devolucion');
        Route::post('/{devolucion}/liquidar', [DevolucionController::class, 'liquidar'])->name('liquidar.devolucion');
        Route::get('/{devolucion}/comprobante', [DevolucionController::class, 'comprobante'])->name('comprobante.devolucion');
    });

    /** Mantenimiento */
    Route::prefix('/mantenimiento')->group(function () {
        Route::get('/', [MantenimientoController::class, 'index'])->name('mostrar.mantenimiento');
        Route::post('/', [MantenimientoController::class, 'store'])->name('crear.mantenimiento');
        Route::patch('/{mantenimiento}', [MantenimientoController::class, 'update'])->name('editar.mantenimiento');
        Route::delete('/{mantenimiento}', [MantenimientoController::class, 'destroy'])->name('eliminar.mantenimiento');
    });

    /** Cotización */
    Route::prefix('/cotizacion')->group(function () {
        Route::get('/', [CotizacionController::class, 'index'])->name('mostrar.cotizacion');
        Route::post('/', [CotizacionController::class, 'calcular'])->name('calcular.cotizacion');
    });

    /** Tarifa */
    Route::prefix('/tarifa')->group(function () {
        Route::get('/', [TarifaController::class, 'index'])->name('mostrar.tarifa');
        Route::post('/', [TarifaController::class, 'store'])->name('crear.tarifa');
        Route::patch('/{tarifa}', [TarifaController::class, 'update'])->name('editar.tarifa');
        Route::delete('/{tarifa}', [TarifaController::class, 'destroy'])->name('eliminar.tarifa');
    });

});

/**
 * Demos/roles (si te sirven) — Protégelos si quieres
 */
Route::middleware(['auth'])->group(function () {
    Route::get('/admin', fn() => 'Admin')->name('admin.dashboard');
    Route::get('/encargado', fn() => 'Encargado')->name('encargado.home');
    Route::get('/home', fn() => 'Cliente')->name('cliente.home');
});
