<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use RealRashid\SweetAlert\Facades\Alert;

class PagoProveedorController extends Controller
{
      /**
     * Muestra el formulario para pagar una CUOTA específica (CXP).
     */
    public function createCuentaPagar(Request $request)
    {
        $id_cta = $request->get('id_cta');

        if (empty($id_cta)) {
            Alert::toast('Debe seleccionar una cuenta a pagar.', 'error');
            return redirect()->route('cuentasapagar.index');
        }

        // 1. VALIDAR QUE EL USUARIO TENGA CAJA ABIERTA (NUEVO)
        $cajaAbierta = DB::table('apertura_cierre_cajas')
                        ->where('user_id', auth()->id())
                        ->where('estado', 'ABIERTA')
                        ->first();

        if (!$cajaAbierta) {
            Alert::error('Atención', 'No tienes ninguna caja abierta. Debes abrir caja para registrar pagos.');
            return redirect()->route('home'); // O redirige a abrir caja si prefieres
        }

        // 2. Consultamos la DEUDA
        $deuda = DB::selectOne(
            "SELECT ca.*, p.descripcion as proveedor, c.factura, c.total as total_compra
            FROM cuentas_a_pagar ca
            INNER JOIN proveedores p ON p.id_proveedor = ca.id_proveedor
            INNER JOIN compras c ON c.id_compra = ca.id_compra
            WHERE ca.id_cta = ?", 
            [$id_cta]
        );

        if (empty($deuda)) {
            Alert::toast('La cuenta a pagar no existe.', 'error');
            return redirect()->route('cuentasapagar.index');
        }
        
        $metodos_pago = DB::table('metodo_pagos')->where('estado', true)->pluck('descripcion', 'id_metodo_pago');

        // 3. Pasamos la variable $cajaAbierta a la vista
        return view('compras.pagos')
            ->with('deuda', $deuda) 
            ->with('metodos_pago', $metodos_pago)
            ->with('caja_abierta', $cajaAbierta); 
    }
    
    public function storeCuentaPagar(Request $request)
    {
        // 1. OBTENER INPUTS
        $input = $request->all();

        // 2. LIMPIEZA MANUAL DE DATOS (CRÍTICO)
        // Limpiamos el monto total
        $input['monto_pagado'] = str_replace(['.', ','], '', $input['monto_pagado']);

        // Limpiamos el array de importes (Uno por uno)
        if (isset($input['importe_pago']) && is_array($input['importe_pago'])) {
            $importes_limpios = [];
            foreach ($input['importe_pago'] as $importe) {
                $importes_limpios[] = str_replace(['.', ','], '', $importe);
            }
            $input['importe_pago'] = $importes_limpios;
        }

        // Reemplazamos los datos sucios con los limpios en el Request
        $request->replace($input);

        // 3. VALIDACIÓN MANUAL CON DEPURACIÓN (AQUÍ ESTÁ LA MAGIA)
        $validator = Validator::make($request->all(), [
            'id_cta' => 'required|numeric|exists:cuentas_a_pagar,id_cta',
            'monto_pagado' => 'required|numeric|min:1', 
            // Quitamos validacion de caja temporalmente para descartar errores
            // 'id_caja_abierta' => 'required|numeric', 
            'id_metodo_pago' => 'required|array', 
            'importe_pago' => 'required|array',   
            'importe_pago.*' => 'numeric|min:0', // Ahora sí podemos validar porque ya limpiamos arriba
        ]);

        // --- ZONA DE DETECCIÓN DE ERRORES ---
        if ($validator->fails()) {
            // SI ENTRA AQUÍ, VERÁS UNA PANTALLA NEGRA CON EL ERROR EXACTO
            dd('FALLÓ LA VALIDACIÓN:', $validator->errors()->all(), $request->all());
        }
        // -------------------------------------

        
        $monto_total_pagado = $input['monto_pagado']; 
        $id_cxp = $input['id_cta'];
        $fecha_pago = Carbon::now()->format('Y-m-d H:i:s');
        
        // RECUPERAR CAJA MANUALMENTE SI NO VIENE EN EL FORMULARIO
        // (Esto asegura que no falle si el hidden input estaba vacío)
        $id_caja = $input['id_caja_abierta'] ?? DB::table('apertura_cierre_cajas')
                                                ->where('user_id', auth()->id())
                                                ->where('estado', 'ABIERTA')
                                                ->value('id_apertura');

        if (!$id_caja) {
             dd("ERROR CRÍTICO: El usuario no tiene una caja abierta o el ID no llegó.");
        }

        DB::beginTransaction();

        try {
            // BLOQUEO Y CONSULTA
            $deuda = DB::selectOne('SELECT * FROM cuentas_a_pagar WHERE id_cta = ? FOR UPDATE', [$id_cxp]);

            if (empty($deuda) || $deuda->saldo <= 0) {
                throw new \Exception("La cuenta ya está saldada o no existe.");
            }
            
            if ($monto_total_pagado > $deuda->saldo) {
                throw new \Exception("El monto a pagar excede el saldo pendiente.");
            }

            // ACTUALIZACIONES
            $nuevo_saldo = $deuda->saldo - $monto_total_pagado;
            $estado_final = ($nuevo_saldo <= 0) ? 'CANCELADO' : 'PENDIENTE'; 

            DB::update('UPDATE cuentas_a_pagar SET saldo = ?, estado = ? WHERE id_cta = ?', 
                [$nuevo_saldo, $estado_final, $id_cxp]
            );

            // REGISTRO DE PAGOS
            foreach ($input['id_metodo_pago'] as $key => $metodo) {
                $monto_parcial = $input['importe_pago'][$key]; // Ya está limpio
                
                DB::insert('INSERT INTO pagos_proveedores (id_cuenta_pagar, id_compra, user_id, id_metodo_pago, id_apertura, pago_fecha, pago_importe, pago_estado, nro_recibo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)', 
                    [
                        $id_cxp, $deuda->id_compra, auth()->user()->id, $metodo, $id_caja,
                        $fecha_pago, $monto_parcial, 'PAGADO', $input['nro_recibo'] ?? null
                    ]
                );
            }
            
            // CAJA
            DB::update('UPDATE apertura_cierre_cajas SET monto_saliente = monto_saliente + ? WHERE id_apertura = ?', 
                [$monto_total_pagado, $id_caja] 
            );
            
            DB::commit();
            Alert::toast("Pago registrado correctamente.", 'success');
            return redirect()->route('cuentasapagar.index');

        } catch (\Exception $e) {
            DB::rollback();
            // SI ENTRA AQUÍ, VERÁS EL ERROR SQL O LÓGICO
            dd("ERROR EN TRANSACCIÓN (SQL/LÓGICA):", $e->getMessage(), $e->getTraceAsString());
        }
    }
}
