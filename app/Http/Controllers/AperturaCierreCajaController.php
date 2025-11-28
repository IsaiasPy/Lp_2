<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use RealRashid\SweetAlert\Facades\Alert;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class AperturaCierreCajaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(Request $request)
    {
        $input = $request->all();

        // Eliminar comas del monto apertura y validar
        $input['monto_apertura'] = str_replace(['.', ','], '', $input['monto_apertura']);
        $input['monto_apertura'] = !empty($input['monto_apertura']) ? $input['monto_apertura'] : 0;

        $validacion = Validator::make($input, [
            'id_caja' => 'required|exists:cajas,id_caja',
            'monto_apertura' => 'numeric|min:0',
            'fecha_apertura' => 'required|date',
        ]);

        if ($validacion->fails()) {
            return redirect()->route('ventas.index')->withErrors($validacion)->withInput();
        }

        // Insertar datos inicializando los acumuladores en 0
        DB::insert('INSERT INTO apertura_cierre_cajas (id_caja, monto_apertura, monto_cierre, monto_entrante, monto_saliente, fecha_apertura, user_id, estado)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)', [
            $input['id_caja'],
            $input['monto_apertura'],
            0, // monto cierre
            0, // monto_entrante inicial
            0, // monto_saliente inicial
            $input['fecha_apertura'],
            auth()->user()->id,
            'ABIERTA'
        ]);

        Alert::toast('Caja abierta con éxito', 'success');
        return redirect()->route('ventas.index');
    }

    // Método llamado por AJAX para mostrar el modal de cierre
    public function editCierre($id_apertura)
    {
        // 1. Buscamos el registro de la caja
        $apertura = DB::selectOne('SELECT * FROM apertura_cierre_cajas WHERE id_apertura = ?', [$id_apertura]);

        if (empty($apertura)) {
            return response()->json(['message' => 'La apertura de caja no existe', 'success' => false]);
        }

        // 2. RECUPERAMOS LOS VALORES REALES (Ya calculados por los controladores de Cobros y Pagos)
        // Nota: Usamos floatval para asegurar que sean números manejables en JS
        $saldo_inicial = floatval($apertura->monto_apertura);
        $total_ingresos = floatval($apertura->monto_entrante); // Cobros de ventas
        $total_egresos  = floatval($apertura->monto_saliente); // Pagos a proveedores
        
        // 3. CÁLCULO DEL SALDO ESPERADO (Lo que debería haber en el cajón)
        $saldo_esperado = $saldo_inicial + $total_ingresos - $total_egresos;

        return response()->json([
            'apertura'       => $apertura,
            'saldo_inicial'  => $saldo_inicial,
            'total_ingresos' => $total_ingresos,
            'total_egresos'  => $total_egresos,
            'saldo_esperado' => $saldo_esperado,
            'success'        => true
        ]);
    }

    public function cerrar_caja(Request $request, $id_apertura)
    {
        $input = $request->all();

        // Limpieza del monto ingresado por el usuario (Arqueo físico)
        $input['monto_cierre'] = str_replace(['.', ','], '', $input['monto_cierre']);
        $input['monto_cierre'] = !empty($input['monto_cierre']) ? $input['monto_cierre'] : 0;

        // Validar ventas pendientes de cobro (Regla de negocio)
        // Nota: Solo bloqueamos si hay ventas CONTADO sin cobrar en esta sesión. 
        // Las de CRÉDITO van por otro carril.
        $ventas_pendientes = DB::selectOne(
            "SELECT COUNT(*) AS cantidad 
            FROM ventas 
            WHERE id_apertura = ? AND condicion_venta = 'CONTADO' AND estado_pago = 'PENDIENTE'",
            [$id_apertura]
        );

        if ($ventas_pendientes->cantidad > 0) {
            Alert::error('Error', 'No se puede cerrar caja: Existen ventas al CONTADO sin cobrar.');
            return redirect()->route('ventas.index');
        }

        // Actualizar cierre
        DB::update('UPDATE apertura_cierre_cajas 
            SET monto_cierre = ?, estado = ?, fecha_cierre = ?
            WHERE id_apertura = ?', [
            $input['monto_cierre'],
            'CERRADA',
            Carbon::now(), // Guardamos fecha y hora real de cierre
            $id_apertura
        ]);

        /* AQUÍ PUEDES GENERAR EL PDF SI LO DESEAS */
        
        Alert::toast('Caja cerrada con éxito. Turno finalizado.', 'success');
        return redirect()->route('ventas.index'); // O redirigir al Login si el turno terminó
    }
}