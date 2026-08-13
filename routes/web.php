<?php

use App\Http\Controllers\Admin\CatalogoController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DespachoController;
use App\Http\Controllers\Admin\InventarioController;
use App\Http\Controllers\Admin\PedidoController;
use App\Http\Controllers\Admin\PerdidaController;
use App\Http\Controllers\Admin\RecepcionController;
use App\Http\Controllers\Admin\ReclamoController;
use App\Http\Controllers\Admin\ReporteController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\Bodeguero\DashboardController as BodegueroDashboardController;
use App\Http\Controllers\Bodeguero\DescargaController as BodegueroDescargaController;
use App\Http\Controllers\Bodeguero\InventarioController as BodegueroInventarioController;
use App\Http\Controllers\Bodeguero\PerdidaController as BodegueroPerdidaController;
use App\Http\Controllers\Celador\DashboardController as CeladorDashboardController;
use App\Http\Controllers\Celador\HistorialController as CeladorHistorialController;
use App\Http\Controllers\Celador\LlegadaController as CeladorLlegadaController;
use App\Http\Controllers\Celador\RecepcionController as CeladorRecepcionController;
use App\Http\Controllers\ProfileController;
use App\Http\Middleware\EnsureAdministrador;
use App\Http\Middleware\EnsureBodeguero;
use App\Http\Middleware\EnsureCelador;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    $user = auth()->user();
    if ($user?->rol?->nombre === 'administrador') {
        return redirect()->route('admin.dashboard');
    }
    if ($user?->rol?->nombre === 'celador') {
        return redirect()->route('celador.dashboard');
    }
    if ($user?->rol?->nombre === 'bodeguero') {
        return redirect()->route('bodeguero.dashboard');
    }

    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', EnsureAdministrador::class])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');

        Route::get('/usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
        Route::post('/usuarios', [UsuarioController::class, 'store'])->name('usuarios.store');
        Route::patch('/usuarios/{usuario}/estado', [UsuarioController::class, 'updateEstado'])->name('usuarios.estado');

        Route::get('/pedidos', [PedidoController::class, 'index'])->name('pedidos.index');
        Route::get('/pedidos/{pedido}', [PedidoController::class, 'show'])->name('pedidos.show');
        Route::post('/pedidos/{pedido}/verificar-pago', [PedidoController::class, 'verificarPago'])->name('pedidos.verificar-pago');
        Route::post('/pedidos/{pedido}/aprobar', [PedidoController::class, 'aprobar'])->name('pedidos.aprobar');
        Route::post('/pedidos/{pedido}/rechazar', [PedidoController::class, 'rechazar'])->name('pedidos.rechazar');

        Route::get('/despachos', [DespachoController::class, 'index'])->name('despachos.index');
        Route::get('/despachos/crear', [DespachoController::class, 'create'])->name('despachos.create');
        Route::post('/despachos', [DespachoController::class, 'store'])->name('despachos.store');
        Route::post('/despachos/{despacho}/cancelar', [DespachoController::class, 'cancelar'])->name('despachos.cancelar');

        Route::get('/inventario', [InventarioController::class, 'index'])->name('inventario.index');

        Route::get('/recepciones', [RecepcionController::class, 'index'])->name('recepciones.index');
        Route::get('/recepciones/{recepcion}', [RecepcionController::class, 'show'])->name('recepciones.show');

        Route::get('/perdidas', [PerdidaController::class, 'index'])->name('perdidas.index');

        Route::get('/reclamos', [ReclamoController::class, 'index'])->name('reclamos.index');
        Route::get('/reclamos/{reclamo}', [ReclamoController::class, 'show'])->name('reclamos.show');
        Route::post('/reclamos/{reclamo}/responder', [ReclamoController::class, 'responder'])->name('reclamos.responder');

        Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');
        Route::get('/reportes/mensual', [ReporteController::class, 'mensuales'])->name('reportes.mensual');

        Route::get('/productos', [CatalogoController::class, 'productos'])->name('productos.index');
        Route::get('/proveedores', [CatalogoController::class, 'proveedores'])->name('proveedores.index');
        Route::get('/vehiculos', [CatalogoController::class, 'vehiculos'])->name('vehiculos.index');
    });

Route::middleware(['auth', EnsureCelador::class])
    ->prefix('celador')
    ->name('celador.')
    ->group(function () {
        Route::get('/', CeladorDashboardController::class)->name('dashboard');

        Route::get('/llegadas/crear', [CeladorLlegadaController::class, 'create'])->name('llegadas.create');
        Route::post('/llegadas', [CeladorLlegadaController::class, 'store'])->name('llegadas.store');

        Route::get('/bodega', [CeladorRecepcionController::class, 'bodega'])->name('bodega.index');
        Route::get('/recepciones/{recepcion}', [CeladorRecepcionController::class, 'show'])->name('recepciones.show');
        Route::get('/recepciones/{recepcion}/salida', [CeladorRecepcionController::class, 'salidaForm'])->name('recepciones.salida');
        Route::post('/recepciones/{recepcion}/salida', [CeladorRecepcionController::class, 'salidaStore'])->name('recepciones.salida.store');

        Route::get('/historial', [CeladorHistorialController::class, 'index'])->name('historial.index');
    });

Route::middleware(['auth', EnsureBodeguero::class])
    ->prefix('bodeguero')
    ->name('bodeguero.')
    ->group(function () {
        Route::get('/', BodegueroDashboardController::class)->name('dashboard');

        Route::get('/pendientes', [BodegueroDescargaController::class, 'pendientes'])->name('pendientes.index');
        Route::get('/recepciones/{recepcion}/descarga', [BodegueroDescargaController::class, 'create'])->name('recepciones.descarga');
        Route::post('/recepciones/{recepcion}/descarga', [BodegueroDescargaController::class, 'store'])->name('recepciones.descarga.store');

        Route::get('/inventario', [BodegueroInventarioController::class, 'index'])->name('inventario.index');

        Route::get('/perdidas', [BodegueroPerdidaController::class, 'index'])->name('perdidas.index');
        Route::get('/perdidas/crear', [BodegueroPerdidaController::class, 'create'])->name('perdidas.create');
        Route::post('/perdidas', [BodegueroPerdidaController::class, 'store'])->name('perdidas.store');
    });

require __DIR__.'/auth.php';
