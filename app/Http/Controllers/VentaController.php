<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Laracasts\Flash\Flash;
use Barryvdh\DomPDF\Facade\Pdf;

class VentaController extends Controller
{
    public function index()
    {
        $ventas = DB::select(
            "SELECT v.*, concat(c.clie_nombre,' ', c.clie_apellido) as cliente, c.clie_ci,
            users.name as usuario
            FROM ventas v
                JOIN clientes c ON v.id_cliente = c.id_cliente
                JOIN users ON v.user_id = users.id
            order by v.fecha_venta desc"
        );

        return view('ventas.index')->with('ventas', $ventas);
    }

    public function create()
    {
        // Crear select para clientes utilizamos selectRaw para consultas puras
        $clientes = DB::table('clientes')
            ->selectRaw("id_cliente, concat(clie_nombre,' ', clie_apellido) as cliente")
            ->pluck('cliente', 'id_cliente');

        // Compartir el usuario en session para el formulario ventas utilizando auth()
        $usuario = auth()->user()->name;

        // Condicion de venta opciones: CONTADO O CREDITO
        $condicion_venta = [
            'CONTADO' => 'CONTADO',
            'CREDITO' => 'CREDITO'
        ];

        // Intervalo de vencimiento
        $intervalo_vencimiento = [
            '7' => '7 Días',
            '15' => '15 Días',
            '30' => '30 Días'
        ];

        // Enviar datos de sucursales
        $sucursales = DB::table('sucursales')->pluck('descripcion', 'id_sucursal');

        return view('ventas.create')->with('clientes', $clientes)
            ->with('usuario', $usuario)
            ->with('condicion_venta', $condicion_venta)
            ->with('intervalo_vencimiento', $intervalo_vencimiento)
            ->with('sucursales', $sucursales);
    }

    public function store(Request $request)
    {
        $input = $request->all();

        // Para que el validator no falle al momento de validacion require_if
        $input['intervalo'] = $input['intervalo'] ?? 0;
        $input['cantidad_cuota'] = $input['cantidad_cuota'] ?? 0;
        // Validaciones personalizadas para el formulario ventas
        $validacion = Validator::make(
            $input,
            [
                'id_cliente' => 'required|exists:clientes,id_cliente',
                'condicion_venta' => 'required|in:CONTADO,CREDITO',
                'intervalo' => 'required_if:condicion_venta,CREDITO|in:0,7,15,30',
                'cantidad_cuota' => 'required_if:condicion_venta,CREDITO|integer',
                'fecha_venta' => 'required|date',
                'user_id' => 'required|exists:users,id'
            ],
            [
                'id_cliente.required' => 'El campo cliente es obligatorio.',
                'id_cliente.exists' => 'El cliente seleccionado no es válido.',
                'condicion_venta.required' => 'El campo condición de venta es obligatorio.',
                'condicion_venta.in' => 'La condición de venta seleccionada no es válida.',
                'intervalo.required_if' => 'El campo intervalo es obligatorio cuando la condición de venta es crédito.',
                'intervalo.in' => 'El intervalo seleccionado no es válido.',
                'cantidad_cuota.required_if' => 'El campo cantidad de cuota es obligatorio cuando la condición de venta es crédito.',
                'cantidad_cuota.integer' => 'El campo cantidad de cuota debe ser un número entero.',
                'fecha_venta.required' => 'El campo fecha de venta es obligatorio.',
                'fecha_venta.date' => 'El campo fecha de venta debe ser una fecha válida.',
                'user_id.required' => 'El campo usuario es obligatorio.',
                'user_id.exists' => 'El usuario seleccionado no es válido.'
            ]
        );

        if ($validacion->fails()) {
            return redirect()->back()->withErrors($validacion)->withInput();
        }

        // Si la validación pasa, continuar con el almacenamiento de la venta
        // Recuperamos el usuario en session
        $user_id = auth()->user()->id;
        // Utilizamos insertGetId para obtener el ID de la venta registrada
        // Agregar transacciones
        DB::beginTransaction();
        try {
            $ventas = DB::table('ventas')->insertGetId([
                'id_cliente' => $input['id_cliente'],
                'condicion_venta' => $input['condicion_venta'],
                'intervalo' => $input['intervalo'] ?? 0,
                'cantidad_cuota' => $input['cantidad_cuota'] ?? 0,
                'fecha_venta' => $input['fecha_venta'],
                'factura_nro' => $input['factura_nro'] ?? '0',
                'user_id' => $user_id,
                'total' => $input['total'] ?? 0,
                'id_sucursal' => $input['id_sucursal'],
                'estado' => 'COMPLETADO'
            ], 'id_venta');

            // insertar detalle ventas
            $subtotal = 0;
            // validar que existea $input['codigo'] de productos
            if ($request->has('codigo')) {
                foreach ($input['codigo'] as $key => $codigo) {
                    // quitar separador de miles
                    $monto = str_replace('.', '', $input['precio'][$key]);
                    // calculo de subtotal
                    $subtotal += $monto * $input['cantidad'][$key];
                    // insertar en detalle_ventas
                    DB::insert(
                        'INSERT INTO detalle_ventas (id_venta, id_producto, cantidad, precio) VALUES (?, ?, ?, ?)',
                        [
                            $ventas,
                            $codigo,
                            $input['cantidad'][$key],
                            $monto
                        ]
                    );

                    // disminuir stocks segun producto y sucursal
                    DB::update('UPDATE stocks SET cantidad = cantidad - ? WHERE id_producto = ? AND id_sucursal = ?', [
                        $input['cantidad'][$key],
                        $codigo,
                        $input['id_sucursal']
                    ]);
                }
                // actualizar el total en la tabla ventas valor total
                DB::update('UPDATE ventas SET total = ? WHERE id_venta = ?', [
                    $subtotal,
                    $ventas
                ]);
            }
            // Si todo esta bien realiza el envio e inserta la venta y detalle
            DB::commit();
        } catch (\Exception $e) {
            // si algo salio mal lo revertimos
            Log::info('Error al registrar la venta: ' . $e->getMessage());
            DB::rollback();
            return redirect()->back()->withErrors($e->getMessage());
        }


        Flash::success('Venta registrada exitosamente.');
        return redirect()->route('ventas.index');
    }

    public function show($id)
    {
        // Obtener la cabecera de ventas
        $venta = DB::selectOne(
            "SELECT v.*, concat(c.clie_nombre,' ', c.clie_apellido) as cliente, c.clie_ci,
            users.name as usuario
            FROM ventas v
                JOIN clientes c ON v.id_cliente = c.id_cliente
                JOIN users ON v.user_id = users.id
            WHERE v.id_venta = ?",
            [$id]
        );

        if (empty($venta)) {
            Flash::error('Venta no encontrada');
            return redirect()->route('ventas.index');
        }

        // obtener los detalles de ventas
        $detalle_venta = DB::select(
            "SELECT dv.*, p.descripcion
            FROM detalle_ventas dv
                JOIN productos p ON dv.id_producto = p.id_producto
            WHERE dv.id_venta = ?",
            [$id]
        );

        return view('ventas.show')->with('ventas', $venta)->with('detalle_venta', $detalle_venta);
    }
    public function edit($id)
    {
        $venta = DB::selectOne('SELECT * FROM ventas WHERE id_venta = ?', [$id]);

        if (empty($venta)) {
            Flash::error('Venta no encontrada');
            return redirect()->route('ventas.index');
        }

        // Crear select para clientes utilizamos selectRaw para consultas puras
        $clientes = DB::table('clientes')
            ->selectRaw("id_cliente, concat(clie_nombre,' ', clie_apellido) as cliente")
            ->pluck('cliente', 'id_cliente');

        // Compartir el usuario en session para el formulario ventas utilizando auth()
        $usuario = auth()->user()->name;

        // Condicion de venta opciones: CONTADO O CREDITO
        $condicion_venta = [
            'CONTADO' => 'CONTADO',
            'CREDITO' => 'CREDITO'
        ];

        // Intervalo de vencimiento
        $intervalo_vencimiento = [
            '7' => '7 Días',
            '15' => '15 Días',
            '30' => '30 Días'
        ];

        // Enviar datos de sucursales
        $sucursales = DB::table('sucursales')->pluck('descripcion', 'id_sucursal');

        // obtener los detalles de ventas
        $detalle_venta = DB::select(
            "SELECT dv.*, p.descripcion
            FROM detalle_ventas dv
                JOIN productos p ON dv.id_producto = p.id_producto
            WHERE dv.id_venta = ?",
            [$id]
        );

        return view('ventas.edit')->with('ventas', $venta)
            ->with('detalle_venta', $detalle_venta)
            ->with('clientes', $clientes)
            ->with('condicion_venta', $condicion_venta)
            ->with('intervalo_vencimiento', $intervalo_vencimiento)
            ->with('usuario', $usuario)
            ->with('sucursales', $sucursales);
    }

    public function update(Request $request, $id)
    {
        $input = $request->all();

        $ventas = DB::selectOne('SELECT * FROM ventas WHERE id_venta = ?', [$id]);

        if (empty($ventas)) {
            Flash::error('Venta no encontrada');
            return redirect()->route('ventas.index');
        }

        // Validaciones personalizadas para el formulario ventas
        $validacion = Validator::make(
            $input,
            [
                'condicion_venta' => 'required|in:CONTADO,CREDITO',
                'intervalo' => 'required_if:condicion_venta,CREDITO|in:0,7,15,30',
                'cantidad_cuota' => 'required_if:condicion_venta,CREDITO|integer',
                'id_sucursal' => 'required|exists:sucursales,id_sucursal',
            ],
            [
                'condicion_venta.required' => 'El campo condición de venta es obligatorio.',
                'condicion_venta.in' => 'La condición de venta seleccionada no es válida.',
                'intervalo.required_if' => 'El campo intervalo es obligatorio cuando la condición de venta es crédito.',
                'intervalo.in' => 'El intervalo seleccionado no es válido.',
                'cantidad_cuota.required_if' => 'El campo cantidad de cuota es obligatorio cuando la condición de venta es crédito.',
                'cantidad_cuota.integer' => 'El campo cantidad de cuota debe ser un número entero.',
                'id_sucursal.required' => 'El campo sucursal es obligatorio.',
                'id_sucursal.exists' => 'La sucursal seleccionada no es válida.',
            ]
        );

        if ($validacion->fails()) {
            return redirect()->back()->withErrors($validacion)->withInput();
        }

        DB::beginTransaction();
        try {
            // Actualizar la venta
            DB::update('UPDATE ventas SET condicion_venta = ?, intervalo = ?, cantidad_cuota = ?, id_sucursal = ? WHERE id_venta = ?', [
                $input['condicion_venta'],
                $input['intervalo'],
                $input['cantidad_cuota'],
                $input['id_sucursal'],
                $id
            ]);

            // Actualizar los detalles de venta
            foreach ($input['codigo'] as $key => $codigo) {
                // quitar separador de miles
                $monto = str_replace('.', '', $input['precio'][$key]);
                // obtener detalle de venta si existe segun producto recibido
                $detalle = DB::selectOne('SELECT * FROM detalle_ventas WHERE id_venta = ? AND id_producto = ?', [$id, $codigo]);

                if (!empty($detalle)) {
                    // actualizar en detalle_ventas
                    DB::update(
                        'UPDATE detalle_ventas SET cantidad = ?, precio = ? WHERE id_venta = ? AND id_producto = ?',
                        [
                            $input['cantidad'][$key],
                            $monto,
                            $id,
                            $codigo
                        ]
                    );
                    // calcular diferencia de cantidad para disminucion de stock
                    $diferencia = $input['cantidad'][$key] - $detalle->cantidad;
                    // actualizar stocks segun producto y sucursal
                    if ($diferencia != 0) {
                        DB::update('UPDATE stocks SET cantidad = cantidad - ? WHERE id_producto = ? AND id_sucursal = ?', [
                            $diferencia,
                            $codigo,
                            $ventas->id_sucursal
                        ]);
                    }
                }else{
                    // Si no existe insertar nuevo detalle
                    DB::insert('INSERT INTO detalle_ventas (id_venta, id_producto, cantidad, precio) VALUES (?, ?, ?, ?)', 
                    [
                        $id,
                        $codigo,
                        $input['cantidad'][$key],
                        $monto
                    ]);

                    // Disminuir stock
                    DB::update('UPDATE stocks SET cantidad = cantidad - ? WHERE id_producto = ? AND id_sucursal = ?', [
                        $input['cantidad'][$key],
                        $codigo,
                        $ventas->id_sucursal
                    ]);
                }
            }
            // Si todo esta bien realiza el envio e inserta la venta y detalle
            DB::commit();

        } catch (\Exception $e) {
            Log::info('Error al actualizar la venta: ' . $e->getMessage());
            DB::rollback();
            return redirect()->back()->withErrors($e->getMessage());
        }


        Flash::success('Venta actualizada exitosamente.');
        return redirect()->route('ventas.index');
    }

    public function destroy($id)
    {
        $ventas = DB::selectOne('SELECT * FROM ventas WHERE id_venta = ?', [$id]);

        if (empty($ventas)) {
            Flash::error('Venta no encontrada');
            return redirect()->route('ventas.index');
        }

        // Usar transacciones 
        DB::beginTransaction();
        try {
            // Anular la venta
            DB::update(
                'UPDATE ventas SET estado = ? WHERE id_venta = ?',
                [
                    'ANULADO',
                    $id
                ]
            );

            // restaurar la cantidad de productos en stock para ello debemos consultar el detalle de venta para obtener datos del producto y cantidad
            $detalle_venta = DB::select('SELECT * FROM detalle_ventas WHERE id_venta = ?', [$id]);

            foreach ($detalle_venta as $item) {
                // actualizar las cantidades en stock segun la sucursal que obtendremos de la variable $ventas
                DB::update('UPDATE stocks SET cantidad = cantidad + ? WHERE id_producto = ? AND id_sucursal = ?', [
                    $item->cantidad,
                    $item->id_producto,
                    $ventas->id_sucursal
                ]);
            }
            // Si todo esta bien realiza el envio e inserta la venta y detalle
            DB::commit();
        } catch (\Exception $e) {
            Log::info('Error al anular la venta: ' . $e->getMessage());
            DB::rollback();
            return redirect()->back()->withErrors($e->getMessage());
        }

        Flash::success('Venta anulada exitosamente.');

        return redirect()->route('ventas.index');
    }

    public function buscarProducto(Request $request)
    {
        // Consulta personalizada para el buscador de productos con stock en la sucursal seleccionada segun parametro recibido
        $query   = $request->get('query'); //contenido a buscar
        $cod_suc = $request->get('cod_suc');

        ## Si query es vacio mostrar todo los productos utilizando un limitador
        if ($query) {
            $productos = DB::select(
                "SELECT productos.*, stocks.cantidad, stocks.id_sucursal
                    FROM productos
                        JOIN stocks ON productos.id_producto = stocks.id_producto 
                    WHERE (CAST(productos.id_producto AS TEXT) iLIKE ? OR CAST(productos.descripcion AS TEXT) iLIKE ?)
                        AND stocks.id_sucursal = ?
                    LIMIT 20",
                [
                    '%' . $query . '%',
                    '%' . $query . '%',
                    $cod_suc
                ]
            );
        } else {
            ## Cargar los primeros 20 productos si no hay búsqueda
            $productos = DB::select(
                "SELECT productos.*, stocks.cantidad, stocks.id_sucursal
                    FROM productos
                        JOIN stocks ON productos.id_producto = stocks.id_producto
                 WHERE stocks.id_sucursal = ?
                 LIMIT 20",
                [$cod_suc]
            );
        }

        ## Retornar la variable productos segun el filtro a nuestro html de buscar_productos
        return view('ventas.body_producto')->with('productos', $productos);
    }

    public function pdf()
    {
        $data = ['<h1>Prueba de PDF</h1>']; // Aquí debes preparar los datos que necesitas para la vista PDF
        $pdf = Pdf::loadView('ventas.invoice', compact('data'));
        return $pdf->download('invoice.pdf');
    }
}
