<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use RealRashid\SweetAlert\Facades\Alert;
use Carbon\Carbon; // Aseguramos importar Carbon para fechas

class ComprasController extends Controller
{
    /**
     * Constructor para aplicar middlewares de autenticación y permisos.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:compras index')->only(['index']);
        $this->middleware('permission:compras create')->only(['create', 'store']);
        $this->middleware('permission:compras edit')->only(['edit', 'update']);
        $this->middleware('permission:compras destroy')->only(['destroy']);
    }

    /**
     * Muestra una lista paginada de las compras.
     */
    public function index(Request $request){
        // 1. LIMPIAMOS el término de búsqueda para la comparación exacta.
        $buscar = trim($request->get('buscar'));
        
        $baseQuery = DB::table('compras as c')
            ->leftJoin('proveedores as p', 'c.id_proveedor', '=', 'p.id_proveedor')
            ->leftJoin('users as u', 'c.user_id', '=', 'u.id')
            ->leftJoin('sucursales as s', 'c.id_sucursal', '=', 's.id_sucursal')
            ->select('c.*', 'p.descripcion as proveedor', 'u.name as usuario', 's.descripcion as sucursal');
        
        if (!empty($buscar)) {
            $baseQuery->where(function($query) use ($buscar) {
                // Filtros de búsqueda (LIKE)
                $query->where('p.descripcion', 'ILIKE', "%{$buscar}%")          // Proveedor
                      ->orWhere('c.factura', 'ILIKE', "%{$buscar}%")            // Nro. Factura
                      ->orWhere(DB::raw("CAST(c.id_compra AS TEXT)"), 'ILIKE', "%{$buscar}%") // ID Compra
                      // ** ADICIÓN: BÚSQUEDA POR FECHA DE COMPRA **
                      ->orWhere(DB::raw("TO_CHAR(c.fecha_compra, 'DD/MM/YYYY')"), 'ILIKE', "%{$buscar}%");
            });

            // ** ORDENAMIENTO DE PRIORIDAD POR ID **
            // Prioriza la coincidencia exacta de ID (por ejemplo, buscar "5" pone el ID 5 primero).
            $baseQuery->orderByRaw("
                CASE 
                    WHEN CAST(c.id_compra AS TEXT) = '{$buscar}' THEN 0 
                    ELSE 1 
                END ASC
            ");
            
            // Ordenamiento secundario: por fecha (ASC) y luego por ID (ASC) para los demás resultados
            $baseQuery->orderBy('c.fecha_compra', 'asc')
                      ->orderBy('c.id_compra', 'asc');
        } else {
            // Ordenamiento por defecto sin búsqueda
            $baseQuery->orderBy('c.fecha_compra', 'asc')
                      ->orderBy('c.id_compra', 'asc');
        }

        $compras = $baseQuery->paginate(10); // Se aplica la paginación

        // Manejo de AJAX para la paginación y búsqueda
        if ($request->ajax()) {
            return view('compras.table', compact('compras'))->render();
        }

        return view('compras.index', compact('compras'));
    }

    /**
     * Muestra el formulario para crear una nueva compra.
     */
    public function create()
    {
        $proveedores = DB::table('proveedores')->orderBy('descripcion')->pluck('descripcion', 'id_proveedor');
        $sucursales  = DB::table('sucursales')->pluck('descripcion', 'id_sucursal');
        $condicion_compra = ['CONTADO' => 'CONTADO', 'CREDITO' => 'CREDITO'];
        $intervalo = ['7' => '7 Días', '15' => '15 Días', '30' => '30 Días'];

        return view('compras.create', compact('proveedores', 'sucursales', 'condicion_compra', 'intervalo'));
    }

    public function store(Request $request)
    {
        $input = $request->all();

        if (!$request->has('codigo') || empty($input['codigo'])) {
            Alert::error('Error de Validación', 'Debe agregar al menos un producto a la compra.');
            return redirect()->back()->withInput();
        }

        if (($input['condicion_compra'] ?? 'CONTADO') === 'CONTADO') {
            $input['intervalo'] = null;
            $input['cantidad_cuotas'] = null;
        }

        $validator = Validator::make($input, [
            'id_proveedor'     => 'required|exists:proveedores,id_proveedor',
            'fecha_compra'     => 'required|date|before_or_equal:today',
            'id_sucursal'      => 'required|exists:sucursales,id_sucursal',
            'factura'          => 'nullable|string|max:30',
            'condicion_compra' => 'required|in:CONTADO,CREDITO',
            'intervalo'        => 'nullable|required_if:condicion_compra,CREDITO|in:7,15,30',
            'cantidad_cuotas'  => 'nullable|required_if:condicion_compra,CREDITO|integer|min:1|max:36',
            'codigo.*'         => 'required|integer|exists:productos,id_producto',
            'cantidad.*'       => 'required|numeric|min:1',
            'precio.*'         => 'required',
        ], [
            'fecha_compra.before_or_equal' => 'La fecha de compra no puede ser futura.',
            'codigo.*.required' => 'Debe agregar al menos un producto a la compra.',
            'intervalo.required_if' => 'El intervalo es obligatorio para compras a crédito.',
            'cantidad_cuotas.required_if' => 'La cantidad de cuotas es obligatoria para compras a crédito.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            $total = 0;
            foreach ($input['precio'] as $i => $precio_str) {
                // Limpieza de precios
                $precio_limpio = preg_replace('/[^0-9]/', '', $precio_str);
                $precio = (float)$precio_limpio;
                $cantidad = (int)$input['cantidad'][$i];
                $total += $precio * $cantidad;
            }

            // 1. INSERCIÓN DE LA COMPRA (Cabecera)
            $idCompra = DB::table('compras')->insertGetId([
                'id_proveedor'     => $input['id_proveedor'],
                'fecha_compra'     => $input['fecha_compra'],
                'total'            => $total,
                'user_id'          => auth()->id(),
                'id_sucursal'      => $input['id_sucursal'],
                'factura'          => $input['factura'],
                'condicion_compra' => $input['condicion_compra'],
                'intervalo'        => $input['intervalo'] ?? 0,
                'cantidad_cuotas'  => $input['cantidad_cuotas'] ?? 0,
                'estado'           => 'COMPLETADO',
                'estado_pago'      => ($input['condicion_compra'] === 'CREDITO') ? 'PENDIENTE' : 'PAGADO', // Nuevo campo
            ], 'id_compra'); 

            // 2. INSERCIÓN DE DETALLES Y ACTUALIZACIÓN DE STOCK
            foreach ($input['codigo'] as $i => $idProducto) {
                $precio_limpio = preg_replace('/[^0-9]/', '', $input['precio'][$i]);
                $precio = (float)$precio_limpio;
                $cantidad = (int)$input['cantidad'][$i];

                DB::table('detalle_compras')->insert([
                    'id_compra'        => $idCompra,
                    'id_producto'      => $idProducto,
                    'cantidad'         => $cantidad,
                    'precio_unitario'  => $precio,
                ]);

                // Aumentamos el stock
                $this->upsertStock($idProducto, $cantidad, $input['id_sucursal']);
            }

            // 3. LÓGICA DE DEUDA: GENERACIÓN DE CUENTAS POR PAGAR (CXP)
            if ($input['condicion_compra'] === 'CREDITO') {
                $cantCuotas = (int)$input['cantidad_cuotas'];
                $importeCuota = round($total / $cantCuotas, 0, PHP_ROUND_HALF_UP);
                $fechaVencimiento = Carbon::parse($input['fecha_compra']);
                $intervaloDias = (int)$input['intervalo'];

                for ($i = 1; $i <= $cantCuotas; $i++) {
                    $fechaVencimiento->addDays($intervaloDias);
                    
                    // Ajuste de la última cuota para cuadrar el total por redondeo
                    $monto_final = ($i === $cantCuotas) 
                                   ? ($total - ($importeCuota * ($i - 1))) 
                                   : $importeCuota;

                    DB::table('cuentas_a_pagar')->insert([
                        'id_proveedor' => $input['id_proveedor'],
                        'id_compra'    => $idCompra,
                        'vencimiento'  => $fechaVencimiento->copy(), // Usar copy() es crucial para Carbon
                        'importe'      => $monto_final,
                        'saldo'        => $monto_final, // El saldo es igual al importe inicial
                        'nro_cuenta'   => $i,
                        'estado'       => 'PENDIENTE'
                    ]);
                }
            }
            DB::commit();
            Alert::success('Éxito', 'Compra registrada, stock y deuda generada.');
            return redirect()->route('compras.index');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en ComprasController@store: ' . $e->getMessage() . ' en la línea ' . $e->getLine());

            if (config('app.debug')) {
                Alert::error('Error al registrar la compra', $e->getMessage() . ' en la línea ' . $e->getLine());
            } else {
                Alert::error('Error Inesperado', 'No se pudo registrar la compra. Contacte al administrador.');
            }

            return back()->withInput();
        }
    }
    public function destroy($id)
    {
        $compra = DB::table('compras')->where('id_compra', $id)->first();

        if (!$compra) {
            Alert::error('Error', 'Compra no encontrada.');
            return redirect()->route('compras.index');
        }

        if ($compra->estado === 'ANULADO') {
            Alert::warning('Atención', 'Esta compra ya fue anulada anteriormente.');
            return redirect()->route('compras.index');
        }

        $detalles = DB::table('detalle_compras')->where('id_compra', $id)->get();

        DB::beginTransaction();
        try {
            /** @var \stdClass $detalle */
            foreach ($detalles as $detalle) {
                $stock_actual = DB::table('stocks')
                    ->where('id_producto', $detalle->id_producto)
                    ->where('id_sucursal', $compra->id_sucursal)
                    ->value('cantidad');

                if ($stock_actual === null || $stock_actual < $detalle->cantidad) {
                    $producto = DB::table('productos')->where('id_producto', $detalle->id_producto)->value('descripcion');
                    throw new \Exception("No se puede anular. El stock para '{$producto}' (actual: {$stock_actual}) es insuficiente para restar la cantidad de la compra ({$detalle->cantidad}).");
                }
            }
            /** @var \stdClass $detalle */
            foreach ($detalles as $detalle) {
                $this->upsertStock($detalle->id_producto, -$detalle->cantidad, $compra->id_sucursal);
            }

            DB::table('compras')->where('id_compra', $id)->update(['estado' => 'ANULADO']);

            // ** CORRECCIÓN: Si anulamos la compra, también deberíamos anular o borrar las cuotas pendientes **
            DB::table('cuentas_a_pagar')
                ->where('id_compra', $id)
                ->where('estado', 'PENDIENTE')
                ->update(['estado' => 'ANULADO']); // O ->delete() si prefieres borrarlas

            DB::commit();
            Alert::success('Éxito', 'Compra anulada y stock revertido.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en ComprasController@destroy: ' . $e->getMessage());
            Alert::error('Error al Anular', $e->getMessage());
        }

        return redirect()->route('compras.index');
    }

    /**
     * Inserta o actualiza el stock de un producto en una sucursal.
     * Acepta cantidades positivas (sumar) y negativas (restar).
     */
    private function upsertStock(int $idProducto, int $cantidad, int $idSucursal): void
    {
        if ($cantidad == 0) return;

        $stock = DB::table('stocks')
            ->where('id_producto', $idProducto)
            ->where('id_sucursal', $idSucursal)
            ->first();

        if ($stock) {
            DB::table('stocks')->where('id_stock', $stock->id_stock)->increment('cantidad', $cantidad);
        } elseif ($cantidad > 0) {
            DB::table('stocks')->insert([
                'id_producto' => $idProducto,
                'id_sucursal' => $idSucursal,
                'cantidad'    => $cantidad,
            ]);
        }
    }

    public function show($id)
    {
        $compra = DB::table('compras as c')
            ->join('proveedores as p', 'c.id_proveedor', '=', 'p.id_proveedor')
            ->join('users as u', 'c.user_id', '=', 'u.id')
            ->join('sucursales as s', 'c.id_sucursal', '=', 's.id_sucursal')
            ->select('c.*', 'p.descripcion as proveedor', 'u.name as usuario', 's.descripcion as sucursal')
            ->where('c.id_compra', $id)->first();

        if (!$compra) {
            Alert::error('Error', 'Compra no encontrada.');
            return redirect()->route('compras.index');
        }
        $detalles = DB::table('detalle_compras as d')
            ->leftJoin('productos as pr', 'pr.id_producto', '=', 'd.id_producto')
            ->select('d.*', 'pr.descripcion')
            ->where('d.id_compra', $id)->get();

        return view('compras.show', compact('compra', 'detalles'));
    }

    public function edit($id)
    {
        $compra = DB::table('compras')->where('id_compra', $id)->first();
        if (!$compra) {
            Alert::error('Error', 'Compra no encontrada.');
            return redirect()->route('compras.index');
        }

        $proveedores = DB::table('proveedores')->orderBy('descripcion')->pluck('descripcion', 'id_proveedor');
        $sucursales  = DB::table('sucursales')->pluck('descripcion', 'id_sucursal');
        $condicion_compra = ['CONTADO' => 'CONTADO', 'CREDITO' => 'CREDITO'];
        $intervalo = ['7' => '7 Días', '15' => '15 Días', '30' => '30 Días'];

        $detalles = DB::table('detalle_compras as d')
            ->join('productos as p', 'd.id_producto', '=', 'p.id_producto')
            ->select('d.*', 'p.descripcion', 'd.precio_unitario')
            ->where('d.id_compra', $id)->get();

        return view('compras.edit', compact('compra', 'detalles', 'proveedores', 'sucursales', 'condicion_compra', 'intervalo'));
    }

    public function update(Request $request, $id)
    {
        $input = $request->all();

        // Verificar que la compra exista
        $compra = DB::table('compras')->where('id_compra', $id)->first();
        if (!$compra) {
            Alert::error('Error', 'Compra no encontrada.');
            return redirect()->route('compras.index');
        }

        // Verificar que la compra no esté anulada
        if ($compra->estado === 'ANULADO') {
            Alert::warning('Atención', 'No se puede modificar una compra anulada.');
            return redirect()->route('compras.index');
        }

        if (!$request->has('codigo') || empty($input['codigo'])) {
            Alert::error('Error de Validación', 'Debe agregar al menos un producto a la compra.');
            return redirect()->back()->withInput();
        }

        // Si es CONTADO, establecer valores nulos para los campos de crédito
        if (($input['condicion_compra'] ?? 'CONTADO') === 'CONTADO') {
            $input['intervalo'] = null;
            $input['cantidad_cuotas'] = null;
        }

        $validator = Validator::make($input, [
            'id_proveedor'     => 'required|exists:proveedores,id_proveedor',
            'fecha_compra'     => 'required|date|before_or_equal:today',
            'id_sucursal'      => 'required|exists:sucursales,id_sucursal',
            'factura'          => 'nullable|string|max:30',
            'condicion_compra' => 'required|in:CONTADO,CREDITO',
            'intervalo'        => 'nullable|required_if:condicion_compra,CREDITO|in:7,15,30',
            'cantidad_cuotas'  => 'nullable|required_if:condicion_compra,CREDITO|integer|min:1|max:36',
            'codigo.*'         => 'required|integer|exists:productos,id_producto',
            'cantidad.*'       => 'required|numeric|min:1',
            'precio.*'         => 'required',
        ], [
            'fecha_compra.before_or_equal' => 'La fecha de compra no puede ser futura.',
            'codigo.*.required' => 'Debe agregar al menos un producto a la compra.',
            'intervalo.required_if' => 'El intervalo es obligatorio para compras a crédito.',
            'cantidad_cuotas.required_if' => 'La cantidad de cuotas es obligatoria para compras a crédito.',
        ]);

        if ($validator->fails()) {
            $html = '<ul style="text-align: left;">';
            foreach ($validator->errors()->all() as $error) {
                $html .= "<li>$error</li>";
            }
            $html .= '</ul>';
            Alert::html('Error de Validación', $html, 'error');
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            // Calcular el nuevo total
            $total = 0;
            foreach ($input['precio'] as $i => $precio_str) {
                $precio_limpio = preg_replace('/[^0-9]/', '', $precio_str);
                $precio = (float)$precio_limpio;
                $cantidad = (int)$input['cantidad'][$i];
                $total += $precio * $cantidad;
            }

            // Actualizar la compra
            DB::table('compras')->where('id_compra', $id)->update([
                'id_proveedor'     => $input['id_proveedor'],
                'fecha_compra'     => $input['fecha_compra'],
                'total'            => $total,
                'factura'          => $input['factura'],
                'condicion_compra' => $input['condicion_compra'],
                'intervalo'        => $input['intervalo'] ?? 0,
                'cantidad_cuotas'  => $input['cantidad_cuotas'] ?? 0,
            ]);

            // Obtener los detalles originales para comparar
            $detallesOriginales = DB::table('detalle_compras')->where('id_compra', $id)->get()->keyBy('id_producto');

            // Procesar cada producto del formulario
            foreach ($input['codigo'] as $i => $idProducto) {
                $precio_limpio = preg_replace('/[^0-9]/', '', $input['precio'][$i]);
                $precio = (float)$precio_limpio;
                $cantidad = (int)$input['cantidad'][$i];

                // Verificar si el producto ya estaba en la compra original
                if (isset($detallesOriginales[$idProducto])) {
                    $detalleOriginal = $detallesOriginales[$idProducto];

                    // Calcular la diferencia de cantidad para ajustar el stock
                    $diferenciaCantidad = $cantidad - $detalleOriginal->cantidad;

                    // Actualizar el detalle
                    DB::table('detalle_compras')
                        ->where('id_detalle_compra', $detalleOriginal->id_detalle_compra)
                        ->update([
                            'cantidad' => $cantidad,
                            'precio_unitario' => $precio,
                        ]);

                    // Ajustar el stock según la diferencia
                    if ($diferenciaCantidad != 0) {
                        $this->upsertStock($idProducto, $diferenciaCantidad, $input['id_sucursal']);
                    }

                    // Eliminar de la lista de originales para procesar los que se eliminaron
                    unset($detallesOriginales[$idProducto]);
                } else {
                    // Es un producto nuevo, agregarlo
                    DB::table('detalle_compras')->insert([
                        'id_compra' => $id,
                        'id_producto' => $idProducto,
                        'cantidad' => $cantidad,
                        'precio_unitario' => $precio,
                    ]);

                    // Aumentar el stock
                    $this->upsertStock($idProducto, $cantidad, $input['id_sucursal']);
                }
            }

            // Eliminar los productos que ya no están en el formulario
            foreach ($detallesOriginales as $detalleOriginal) {
                // Eliminar el detalle
                DB::table('detalle_compras')
                    ->where('id_detalle_compra', $detalleOriginal->id_detalle_compra)
                    ->delete();

                // Reducir el stock
                $this->upsertStock($detalleOriginal->id_producto, -$detalleOriginal->cantidad, $compra->id_sucursal);
            }

            // =========================================================================
            // REGENERACIÓN DE CUOTAS (Lógica Crítica para cambio de 6 a 3 cuotas)
            // =========================================================================
            if ($input['condicion_compra'] === 'CREDITO') {
                // Verificar si existen cuotas ya pagadas para no romper integridad
                $cuotasPagadas = DB::table('cuentas_a_pagar')
                    ->where('id_compra', $id)
                    ->where('estado', '!=', 'PENDIENTE')
                    ->exists();
                
                if ($cuotasPagadas) {
                    throw new \Exception("No se puede editar las cuotas porque ya existen pagos registrados. Anule los pagos primero.");
                }
                
                // Borrar cuotas viejas (ej: las 6 cuotas anteriores)
                DB::table('cuentas_a_pagar')->where('id_compra', $id)->delete();
                
                // Generar nuevas cuotas (ej: las 3 nuevas)
                $cantCuotas = (int)$input['cantidad_cuotas'];
                $montoCuota = round($total / $cantCuotas);
                $fechaVencimiento = Carbon::parse($input['fecha_compra']);
                $intervaloDias = (int)$input['intervalo'];
                
                for ($i = 1; $i <= $cantCuotas; $i++) {
                    $fechaVencimiento->addDays($intervaloDias); // Sumar días al vencimiento
                    
                    DB::table('cuentas_a_pagar')->insert([
                        'id_proveedor' => $input['id_proveedor'],
                        'id_compra'    => $id,
                        'vencimiento'  => $fechaVencimiento->copy(),
                        'importe'      => $montoCuota,
                        'nro_cuenta'   => $i,
                        'estado'       => 'PENDIENTE'
                    ]);
                }
            } else {
                // Si cambió a CONTADO, borrar cuotas pendientes si existían
                DB::table('cuentas_a_pagar')
                    ->where('id_compra', $id)
                    ->where('estado', 'PENDIENTE')
                    ->delete();
            }
            // =========================================================================


            DB::commit();
            Alert::success('Éxito', 'Compra actualizada y cuotas recalculadas correctamente.');
            return redirect()->route('compras.index');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en ComprasController@update: ' . $e->getMessage() . ' en la línea ' . $e->getLine());

            if (config('app.debug')) {
                Alert::error('Error al actualizar la compra', $e->getMessage() . ' en la línea ' . $e->getLine());
            } else {
                Alert::error('Error Inesperado', 'No se pudo actualizar la compra: ' . $e->getMessage());
            }

            return back()->withInput();
        }
    }

    public function buscarProducto(Request $request)
    {
        $buscar = trim($request->get('query', ''));
        $productos = DB::table('productos')
            ->where(function ($q) use ($buscar) {
                $q->where('descripcion', 'ILIKE', "%{$buscar}%")
                    ->orWhere(DB::raw("CAST(id_producto AS TEXT)"), 'ILIKE', "%{$buscar}%");
            })
            ->limit(50)->get();
        return view('compras.body_producto', compact('productos'));
    }
}