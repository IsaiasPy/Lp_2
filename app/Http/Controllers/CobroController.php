<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\Validator;

class CobroController extends Controller
{
    public function index(Request $request)
    {
        // ... (Tu código index original se mantiene)
        $id_venta = $request->get('id_venta');
        if (empty($id_venta)) {
            Alert::toast('Debe seleccionar una venta', 'error');
            return redirect()->route('ventas.index');
        }
        $venta = DB::selectOne("SELECT v.*, concat(c.clie_nombre, ' ', c.clie_apellido) as cliente 
                    FROM ventas v INNER JOIN clientes c ON c.id_cliente = v.id_cliente WHERE id_venta = ?", [$id_venta]);
        if (empty($venta)) {
            Alert::toast('La venta no existe', 'error');
            return redirect()->route('ventas.index');
        }
        $metodos_pago = DB::table('metodo_pagos')->where('estado', true)->pluck('descripcion', 'id_metodo_pago');
        return view('ventas.cobros')->with('ventas', $venta)->with('metodos_pago', $metodos_pago);
    }

    public function store(Request $request)
    {
        // ... (Tu código store original se mantiene)
        $input = $request->all();
        $ventas = DB::selectOne("SELECT * FROM ventas WHERE id_venta = ?", [$input['id_venta']]);
        if (empty($ventas)) {
            Alert::toast('La venta no existe', 'error');
            return redirect()->route('ventas.index');
        }
        DB::beginTransaction();
        try {
            $total_cobros = 0;
            if ($request->has('metodos_pago')) {
                foreach ($input['metodos_pago'] as $key => $metodo) {
                    $metodo_pago = DB::selectOne("SELECT * FROM metodo_pagos WHERE id_metodo_pago = ?", [$metodo]);
                    if (empty($metodo_pago)) {
                        Alert::toast('La forma de pago no existe', 'error');
                        return redirect()->route('cobros.index', ['id_venta' => $input['id_venta']]);
                    }
                    $importe = str_replace('.', '', $input['importe'][$key]);
                    $total_cobros += $importe;
                    if (!is_numeric($importe) || $importe <= 0) {
                        Alert::toast('El importe debe ser un numero mayor a 0', 'error');
                        return redirect()->route('cobros.index', ['id_venta' => $input['id_venta']]);
                    }
                    DB::insert('INSERT INTO cobros(id_venta, user_id, id_metodo_pago, cobro_fecha, cobro_importe, cobro_estado, nro_voucher) VALUES(?, ?, ?, ?, ?, ?, ?)',
                        [ $ventas->id_venta, auth()->user()->id, $metodo, Carbon::now()->format('Y-m-d'), $importe, 'COBRADO', $input['nro_voucher'][$key] ?? null ]
                    );
                }
                if ($total_cobros != $ventas->total) {
                    Alert::toast('El total de cobros debe ser igual al total de la venta', 'error');
                    return redirect()->route('cobros.index', ['id_venta' => $input['id_venta']]);
                }
                DB::update("UPDATE ventas SET estado = 'PAGADO' WHERE id_venta = ?", [$ventas->id_venta]);
                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::info("Error en transaccion de cobros::::::" . $e->getMessage());
            Alert::toast("Error en el proceso:" . $e->getMessage(), "error");
            return redirect()->route('cobros.index', ['id_venta' => $input['id_venta']]);
        }
        Alert::toast('Cobro guardado correctamente', 'success');
        return redirect()->route('ventas.index');
    }
    
    // =========================================================================
    // NUEVA LÓGICA: PAGO PARCIAL/TOTAL DE CUENTA A COBRAR (CXC)
    // =========================================================================

    public function createCuentaCobrar(Request $request)
    {
        // ... (Tu código createCuentaCobrar se mantiene)
        $id_cuenta = $request->get('id_cuenta'); 
        if (empty($id_cuenta)) {
            Alert::toast('Debe seleccionar una cuenta a cobrar.', 'error');
            return redirect()->route('cuentasacobrar.index');
        }
        $deuda = DB::selectOne(
            "SELECT ca.*, concat(c.clie_nombre, ' ', c.clie_apellido) as cliente, 
            v.factura_nro, v.total as total_venta
            FROM cuentas_a_cobrar ca INNER JOIN clientes c ON c.id_cliente = ca.id_cliente
            INNER JOIN ventas v ON v.id_venta = ca.id_venta
            WHERE ca.id_cuenta = ?", 
            [$id_cuenta]
        );
        if (empty($deuda)) {
            Alert::toast('La cuenta a cobrar no existe.', 'error');
            return redirect()->route('cuentasacobrar.index');
        }
        $metodos_pago = DB::table('metodo_pagos')->where('estado', true)->pluck('descripcion', 'id_metodo_pago');
        return view('ventas.cobros')
            ->with('deuda', $deuda) 
            ->with('metodos_pago', $metodos_pago);
    }
    
    /**
     * Procesa el pago parcial o total de una CUENTA A COBRAR con transacción atómica.
     */
    public function storeCuentaCobrar(Request $request)
    {
        $input = $request->all();

        // 1. VALIDACIÓN DE ENTRADA: AHORA ESPERA ARRAYS DE FORMAS DE PAGO
        $request->validate([
            'id_cuenta' => 'required|numeric|exists:cuentas_a_cobrar,id_cuenta',
            'monto_cobrado' => 'required|numeric|min:0.01', 
            'id_caja_abierta' => 'required|numeric', // Se valida existencia con exists
            'id_metodo_pago' => 'required|array', // **CRÍTICO: Debe ser ARRAY**
            'importe_pago' => 'required|array',   // **CRÍTICO: Debe ser ARRAY**
            'importe_pago.*' => 'numeric|min:0', // Validamos cada monto
            'nro_voucher' => 'nullable|array',
        ], [
            'id_cuenta.required' => 'Debe especificar la cuenta a cobrar.',
            'monto_cobrado.min' => 'El monto a pagar debe ser positivo.',
            'id_caja_abierta.required' => 'Debe seleccionar una caja abierta válida.',
        ]);
        
        // Limpieza de datos (Quitamos los puntos del monto TOTAL pagado)
        $monto_total_pagado = str_replace(['.', ','], '', $input['monto_cobrado']); 
        $id_cxc = $input['id_cuenta'];
        $fecha_cobro = Carbon::now()->format('Y-m-d H:i:s');
        
        DB::beginTransaction();

        try {
            // 2. OBTENER Y BLOQUEAR LA DEUDA (CRÍTICO: FOR UPDATE)
            $deuda = DB::selectOne(
                'SELECT * FROM cuentas_a_cobrar WHERE id_cuenta = ? FOR UPDATE', 
                [$id_cxc]
            );

            // Validación 1: Existencia y Saldo Pendiente
            if (empty($deuda) || $deuda->saldo <= 0) {
                DB::rollback();
                Alert::toast('La cuenta ya está saldada o no existe.', 'error');
                return redirect()->route('cuentasacobrar.index');
            }
            
            // Validación 2: El pago NO puede exceder el saldo pendiente.
            if ($monto_total_pagado > $deuda->saldo) {
                DB::rollback();
                Alert::toast('El monto a pagar ($' . number_format($monto_total_pagado, 0, ',', '.') . ') excede el saldo pendiente ($' . number_format($deuda->saldo, 0, ',', '.') . ').', 'error');
                return redirect()->route('cuentasacobrar.index');
            }

            // 3. CÁLCULOS Y ESTADO
            $nuevo_saldo = $deuda->saldo - $monto_total_pagado;
            $estado_final_cxc = ($nuevo_saldo <= 0) ? 'CANCELADO' : 'PENDIENTE'; 

            // 4. ACTUALIZACIÓN 1: Cuentas a Cobrar (Reduce el saldo y actualiza el estado)
            DB::update('UPDATE cuentas_a_cobrar SET saldo = ?, estado = ? WHERE id_cuenta = ?', 
                [$nuevo_saldo, $estado_final_cxc, $id_cxc]
            );

            // 5. ACTUALIZACIÓN 2: Registro en la tabla Cobros (ITERAMOS por cada forma de pago)
            foreach ($input['id_metodo_pago'] as $key => $metodo) {
                // Limpiamos cada monto individualmente
                $monto_parcial = str_replace(['.', ','], '', $input['importe_pago'][$key]);
                Log::info('Datos Finales a Insertar en DB: ' . print_r($input, true));
                DB::insert('INSERT INTO cobros (id_cuenta, id_venta, user_id, id_metodo_pago, cobro_fecha, cobro_importe, cobro_estado, nro_voucher) VALUES (?, ?, ?, ?, ?, ?, ?, ?)', 
                    [
                        $id_cxc, 
                        $deuda->id_venta,
                        auth()->user()->id,
                        $metodo, 
                        $fecha_cobro,
                        $monto_parcial, // Usamos el monto PARCIAL
                        'COBRADO',
                        $input['nro_voucher'][$key] ?? null
                    ]
                );
            }
            
            // 6. ACTUALIZACIÓN 3: Caja Abierta (Suma el monto TOTAL pagado)
            DB::update('UPDATE apertura_cierre_cajas SET monto_entrante = monto_entrante + ? WHERE id_apertura = ?', 
                [$monto_total_pagado, $input['id_caja_abierta']] 
            );
            
            // 7. ACTUALIZACIÓN 4: Venta (Actualiza el estado de pago de la Venta si la CUENTA pasó a CANCELADO)
            if ($estado_final_cxc == 'CANCELADO') {
                 DB::update("UPDATE ventas SET estado_pago = 'CANCELADO' WHERE id_venta = ?", 
                    [$deuda->id_venta]
                );
            }

            // COMMIT: Fin exitoso de la Transacción
            DB::commit();
            Alert::toast("Cobro de $ {$monto_total_pagado} registrado. Saldo pendiente: $ {$nuevo_saldo}", 'success');
            return redirect()->route('cuentasacobrar.index');

        } catch (\Exception $e) {
            // ROLLBACK: Si algo falló (Aquí debe caer si hay un error de SQL, y se revierte todo)
            DB::rollback();
            Log::error("Error en transaccion de cobro CXC: " . $e->getMessage()); 
            Alert::toast('Error crítico al procesar el cobro. Contacte a soporte.', 'error');
            return redirect()->route('cuentasacobrar.index');
        }
    }
}