<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// IMPORTACIONES DE CONTROLADORES (Siempre arriba)
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CobroController;
use App\Http\Controllers\CuentasAPagarController;
use App\Http\Controllers\PagoProveedorController;
use App\Http\Controllers\CuentasACobrarController;
use App\Http\Controllers\CargoController;
use App\Http\Controllers\DepartamentoController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\MarcaController;
use App\Http\Controllers\CiudadController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\SucursalController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CajaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\PedidosController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\ComprasController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\AperturaCierreCajaController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\AuditoriaController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('auth.login');
});

Auth::routes();

// GRUPO PROTEGIDO POR AUTENTICACIÓN
Route::group(['middleware' => ['auth']], function () {

    Route::get('/home', [HomeController::class, 'index'])->name('home');

    // RUTAS PERSONALIZADAS: COBROS (CXC)
    //## 1. Formulario de Cobro (GET)
    Route::get('cobros/crear-cxc', [CobroController::class, 'createCuentaCobrar'])->name('cobros.cxc.create');
    // 2. Procesar Cobro (POST)
    Route::post('cobros/cuentas-a-cobrar', [CobroController::class, 'storeCuentaCobrar'])->name('cobros.cxc.store');
    // 3. Rutas originales de cobro venta contado
    Route::get('cobros', [CobroController::class, 'index'])->name('cobros.index'); 
    Route::post('cobros', [CobroController::class, 'store']);


    ## RUTAS PERSONALIZADAS: PAGOS PROVEEDORES (CXP)
    ## 1. Listado de Deudas (Cuentas a Pagar)
    Route::get('cuentasapagar', [CuentasAPagarController::class, 'index'])->name('cuentasapagar.index');

    ## 2. Formulario de Pago (GET)
    Route::get('pagos-proveedor/crear-cxp', [PagoProveedorController::class, 'createCuentaPagar'])
        ->name('pagosproveedor.cxp.create');

    ## 3. Procesar Pago (POST)
    Route::post('pagos-proveedor/procesar', [PagoProveedorController::class, 'storeCuentaPagar'])
        ->name('pagosproveedor.cxp.store');


    ## RUTAS TIPO RESOURCE (CRUDs Generales)
    Route::resource('cargos', CargoController::class);
    Route::resource('departamentos', DepartamentoController::class);
    Route::resource('proveedores', ProveedorController::class);
    Route::resource('marcas', MarcaController::class);
    Route::resource('ciudades', CiudadController::class);
    Route::resource('productos', ProductoController::class);
    Route::resource('sucursales', SucursalController::class);
    Route::resource('users', UserController::class);
    Route::resource('cajas', CajaController::class);
    Route::resource('clientes', ClienteController::class);
    
    ## Ventas y sus rutas adicionales
    Route::resource('ventas', VentaController::class);
    Route::get('buscar-productos', [VentaController::class, 'buscarProducto']);
    Route::get('pdf', [VentaController::class, 'pdf']);
    Route::get('imprimir-factura/{id}', [VentaController::class, 'factura']);

    ## Pedidos
    Route::resource('pedidos', PedidosController::class);

    ## Compras
    Route::resource('compras', ComprasController::class);

    ## Cuentas a Cobrar (Resource)
    Route::resource('cuentasacobrar', CuentasACobrarController::class);
    
    ## Cobros Resource (Dejar al final para que no choque con las custom de arriba)
    Route::resource('cobros', CobroController::class); 

    ## Seguridad y Auditoría
    Route::resource('permissions', PermissionController::class);
    Route::resource('roles', RoleController::class);
    Route::resource('auditoria', AuditoriaController::class);

    ## Caja y Stocks
    Route::resource('apertura-cierre-caja', AperturaCierreCajaController::class);
    Route::get('apertura_cierre/editCierre/{id}', [AperturaCierreCajaController::class, 'editCierre']);
    Route::get('apertura_cierre/cerrar_caja/{id}', [AperturaCierreCajaController::class, 'cerrar_caja']);
    Route::resource('stocks', StockController::class);

    ##Reportes
    Route::get('reporte-cargos', [ReporteController::class, 'rpt_cargos']);
    Route::get('reporte-clientes', [ReporteController::class, 'rpt_clientes']);
    Route::get('reporte-ventas', [ReporteController::class, 'rpt_ventas']);
    Route::get('reporte-proveedores', [ReporteController::class, 'rpt_proveedores']);
    Route::get('reporte-productos', [ReporteController::class, 'rpt_productos']);
    Route::get('reporte-sucursales', [ReporteController::class, 'rpt_sucursales']);
    Route::get('reporte-cajas', [ReporteController::class, 'rpt_cajas']);
    Route::get('reporte-pedidos', [ReporteController::class, 'rpt_pedidos']);
    Route::get('reporte-ciudades', [ReporteController::class, 'rpt_ciudades']);
    Route::get('reporte-marcas', [ReporteController::class, 'rpt_marcas']);
    Route::get('reporte-departamentos', [ReporteController::class, 'rpt_departamentos']);
    Route::get('reporte-usuarios', [ReporteController::class, 'rpt_usuarios']);
    
    Route::post('exportar-cargos', [ReporteController::class, 'exportar']);
});