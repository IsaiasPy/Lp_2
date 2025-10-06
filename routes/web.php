<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('auth.login');
});

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Auth::routes();

## Crear rutas para cargos
Route::resource('cargos', App\Http\Controllers\CargoController::class);
## Crear rutas para departamentos
Route::resource('departamentos', App\Http\Controllers\DepartamentoController::class);
## Crear rutas para proveedores
Route::resource('proveedores', App\Http\Controllers\ProveedorController::class);

## Crear rutas para marcas
Route::resource('marcas', App\Http\Controllers\MarcaController::class);

## Crear rutas para ciudades
Route::resource('ciudades', App\Http\Controllers\CiudadController::class);

## Crear rutas para productos
Route::resource('productos', App\Http\Controllers\ProductoController::class);

## Crear rutas para sucursales
Route::resource('sucursales', App\Http\Controllers\SucursalController::class);

## Crear rutas para usuarios
Route::resource('users', App\Http\Controllers\UserController::class);

## Crear rutas para cajas
Route::resource('cajas', App\Http\Controllers\CajaController::class);

## Crear rutas para clientes
Route::resource('clientes', App\Http\Controllers\ClienteController::class);

## Crear rutas para ventas
Route::resource('ventas', App\Http\Controllers\VentaController::class);

## Crear ruta para el buscador
Route::get('buscar-productos', [App\Http\Controllers\VentaController::class, 'buscarProducto']);

## Crear ruta para Pedidos Examen Final
Route::resource('pedidos', App\Http\Controllers\PedidosController::class);

## Crear ruta Pdf
Route::get('pdf', [App\Http\Controllers\VentaController::class, 'pdf']);

## Crear ruta reporte cargo
Route::get('reporte-cargos', [App\Http\Controllers\ReporteController::class, 'rpt_cargos']);

## Crear ruta para reporte clientes
Route::get('reporte-clientes', [App\Http\Controllers\ReporteController::class, 'rpt_clientes']);

## Crear ruta para Pedidos
Route::resource('pedidos', App\Http\Controllers\PedidosController::class);

## Crear ruta para reporte ventas
Route::get('reporte-ventas', [App\Http\Controllers\ReporteController::class, 'rpt_ventas']);

## Crear ruta para reporte proveedores
Route::get('reporte-proveedores', [App\Http\Controllers\ReporteController::class, 'rpt_proveedores']);

## Crear ruta para reporte productos
Route::get('reporte-productos', [App\Http\Controllers\ReporteController::class, 'rpt_productos']);

## Crear ruta para reporte sucursales
Route::get('reporte-sucursales', [App\Http\Controllers\ReporteController::class, 'rpt_sucursales']);

## Crear ruta para reporte cajas
Route::get('reporte-cajas', [App\Http\Controllers\ReporteController::class, 'rpt_cajas']);

## Crear ruta para reporte pedidos
Route::get('reporte-pedidos', [App\Http\Controllers\ReporteController::class, 'rpt_pedidos']);

## Crear ruta para reporte ciudades
Route::get('reporte-ciudades', [App\Http\Controllers\ReporteController::class, 'rpt_ciudades']);

## Crear ruta para reporte marcas
Route::get('reporte-marcas', [App\Http\Controllers\ReporteController::class, 'rpt_marcas']);

## Crear ruta para reporte departamentos
Route::get('reporte-departamentos', [App\Http\Controllers\ReporteController::class, 'rpt_departamentos']);

## Crear ruta para reporte usuarios
Route::get('reporte-usuarios', [App\Http\Controllers\ReporteController::class, 'rpt_usuarios']);

## Crear ruta para reporte proveedores
Route::get('reporte-proveedores', [App\Http\Controllers\ReporteController::class, 'rpt_proveedores']);

## Crear ruta para compras
Route::resource('compras', App\Http\Controllers\ComprasController::class);

## Ruta role permission
Route::resource('permissions', App\Http\Controllers\PermissionController::class);

## Ruta para Role
Route::resource('roles', App\Http\Controllers\RoleController::class);

## Ruta para apertura cierre de caja
Route::resource('apertura-cierre-caja', App\Http\Controllers\AperturaCierreCajaController::class);

## Ruta para Stock
Route::resource('stocks', App\Http\Controllers\StockController::class);

## Ruta para imprimir factura
Route::get('imprimir-factura/{id}', [App\Http\Controllers\VentaController::class, 'factura']);