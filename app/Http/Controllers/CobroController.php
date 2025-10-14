<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RealRashid\SweetAlert\Facades\Alert;

class CobroController extends Controller
{
    public function index(Request $request){
        // Recibir parametros id venta y buscar las ventas asociadas
        $id_venta = $request->get('id_venta');

        if(empty($id_venta)){
            Alert::toast('Debe seleccionar una venta', 'error');
            return redirect()->route('ventas.index');
        }
        // Consultar la venta
        $venta = DB::selectOne("SELECT v.*, concat(c.clie_nombre, ' ', c.clie_apellido) as cliente
        FROM ventas v
        INNER JOIN clientes c ON c.id_cliente = v.id_cliente
        WHERE id_venta = ?", [$id_venta]);

        if(empty($venta)){
            Alert::toast('La venta no existe', 'error');
            return redirect()->route('ventas.index');
        }

        //enviar los metodos de pago, listar solo los activos
        $metodos_pago = DB::table('metodo_pagos')->where('estado', true)->pluck('descripcion', 'id_metodo_pago');

        //retornar la vista de cobros que se encuentra en la carpeta ventas
        return view('ventas.cobros')->with('ventas', $venta)->with('metodos_pago', $metodos_pago);

    }

    public function store(Request $request){
       $input = $request->all();

       $venta = DB::selectOne("SELECT * FROM ventas WHERE id_venta = ?", [$input['id_venta']]);

       if(empty($venta)){
           Alert::toast('La venta no existe', 'error');
           return redirect()->route('ventas.index');
       }

       // validar que exista la formas de pago recibidas del formulario
       if($request->has('forma_pago')){
           foreach($input['forma_pago'] as $key => $metodo){
            $metodo_pago = DB::selectOne("SELECT * FROM metodo_pagos WHERE id_metodo_pago = ?", [$metodo]);
            if(empty($metodo_pago)){
                Alert::toast('La forma de pago no existe', 'error');
                return redirect()->route('ventas.index');
            }
            $importe = str_replace('.', '', $input['importe'][$key]);

            //validar que el importe sea un numero
            if(is_numeric($importe) || $importe <= 0){
                Alert::toast('El importe debe ser mayor a 0', 'error');
                return redirect()->route('cobros.index', ['id_venta' => $input['id_venta']]);
            }
            //Registrar el cobro
            
        }
           Alert::toast('Cobro realizado correctamente', 'success');
           return redirect()->route('ventas.index');
       }
    }
}