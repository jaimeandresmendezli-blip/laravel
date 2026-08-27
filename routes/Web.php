
<?php

use App\Http\Controllers\AdminPedidoController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\ClientePedidoController;
use App\Http\Controllers\CatalogoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| RUTA PRINCIPAL
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('auth.login');
});


/*
|--------------------------------------------------------------------------
| RUTAS DEL ADMINISTRADOR
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'role:admin'])
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        })->name('dashboard');

        // US-0003 — Gestión de usuarios
        Route::resource('usuarios', UsuarioController::class);
        Route::patch('usuarios/{usuario}/toggle', [UsuarioController::class, 'toggleEstado'])
            ->name('usuarios.toggle');

        // US-0004 — Gestión de productos
        Route::resource('productos', ProductoController::class);
        Route::patch('productos/{producto}/toggle', [ProductoController::class, 'toggleEstado'])
            ->name('productos.toggle');

        // US-0005 / US-0006 — Inventario
        Route::get('inventario/crear', function () {
            return redirect()->route('admin.inventario.index');
        });
        Route::get('inventario', [inventarioController::class, 'index'])->name('inventario.index');
        Route::post('inventario', [InventarioController::class, 'store'])->name('inventario.store');

        // US-0008 (admin) — Pedidos
        Route::get('pedidos', [AdminPedidoController::class, 'index'])->name('pedidos.index');
        Route::get('pedidos/{pedido}', [AdminPedidoController::class, 'show'])->name('pedidos.show');
        Route::patch('pedidos/{pedido}/estado-entrega', [AdminPedidoController::class, 'actualizarEstadoEntrega'])
            ->name('pedidos.estado-entrega');

        // US-0009 — Reportes
        Route::get('reportes', [ReporteController::class, 'index'])->name('reportes.index');
        Route::get('reportes/pdf', [ReporteController::class, 'exportarPdf'])->name('reportes.pdf');
    });

/*
|--------------------------------------------------------------------------
| RUTAS DEL CLIENTE
|--------------------------------------------------------------------------
*/
Route::prefix('cliente')
    ->name('cliente.')
    ->middleware(['auth', 'role:cliente'])
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', function () {
            return view('cliente.dashboard');
        })->name('dashboard');

        // US-0007 — Catálogo
        Route::get('catalogo', [CatalogoController::class, 'index'])->name('catalogo.index');
        Route::get('catalogo/{producto}', [CatalogoController::class, 'show'])->name('catalogo.show');

        // US-0008 — Carrito
        Route::get('carrito', [CarritoController::class, 'index'])->name('carrito.index');
        Route::post('carrito/agregar', [CarritoController::class, 'agregar'])->name('carrito.agregar');
        Route::patch('carrito/actualizar/{detalle}', [CarritoController::class, 'actualizar'])->name('carrito.actualizar');
        Route::delete('carrito/eliminar/{detalle}', [CarritoController::class, 'eliminar'])->name('carrito.eliminar');
        Route::delete('carrito/vaciar', [CarritoController::class, 'vaciar'])->name('carrito.vaciar');

        // US-0008 — Pedidos
        Route::get('pedidos', [ClientePedidoController::class, 'index'])->name('pedidos.index');
        Route::get('pedidos/crear', [ClientePedidoController::class, 'create'])->name('pedidos.create');
        Route::post('pedidos', [ClientePedidoController::class, 'store'])->name('pedidos.store');
        Route::get('pedidos/{pedido}', [ClientePedidoController::class, 'show'])->name('pedidos.show');
        Route::delete('pedidos/{pedido}/cancelar', [ClientePedidoController::class, 'cancelar'])->name('pedidos.cancelar');
    });

/*
|--------------------------------------------------------------------------
| RUTAS DE AUTENTICACIÓN (BREEZE)
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';
