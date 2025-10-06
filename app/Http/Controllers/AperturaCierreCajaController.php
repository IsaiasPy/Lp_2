<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use RealRashid\SweetAlert\Facades\Alert;

class AperturaCierreCajaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // colocar los permisos
    }
    
    public function store(Request $request)
    {
        $input = $request->all();
        
        // eliminar comas del monto apertura
        $input['monto_apertura'] = str_replace(',', '', $input['monto_apertura']);
        //validar si no posee monto apertura colocar 0
        $input['monto_apertura'] = !empty($input['monto_apertura']) ? $input['monto_apertura'] : 0;

        // validar datos recibidos
        $validacion = Validator::make($input, [
            'id_caja' => 'required|exists:cajas,id_caja',
            'monto_apertura' => 'numeric|min:0',
            'fecha_apertura' => 'required|date',
        ], [
            'id_caja.required' => 'El campo caja es obligatorio',
            'id_caja.exists' => 'El campo caja seleccionado no existe',
            'monto_apertura.numeric' => 'El campo monto apertura debe ser un número',
            'monto_apertura.min' => 'El campo monto apertura debe ser un número positivo',
            'fecha_apertura.required' => 'El campo fecha apertura es obligatorio',
            'fecha_apertura.date' => 'El campo fecha apertura debe ser una fecha válida',
        ]);

        if($validacion->fails()){
            return redirect()->route('ventas.index')
                ->withErrors($validacion)
                ->withInput();
        }

        // insertar datos en la tabla apertura_cierre_cajas
        DB::insert('INSERT INTO apertura_cierre_cajas (id_caja, monto_apertura, monto_cierre, fecha_apertura, user_id, estado)
            VALUES (?, ?, ?, ?, ?, ?)', [
                $input['id_caja'],
                $input['monto_apertura'] ?? 0,//validar si no posee monto apertura colocar 0
                0,// monto cierre 0 al abrir caja
                $input['fecha_apertura'],
                auth()->user()->id,
                'ABIERTA' 
            ]);
        
        // redireccionar a la vista ventas con mensaje de exito
        Alert::toast('Caja abierta con éxito', 'success');
        return redirect()->route('ventas.index');
    }

    public function cerrar_caja(Request $request)
    {
        $input = $request->all();
        
        // validar datos recibidos
        $validacion = Validator::make($input, [
            'id_caja' => 'required|exists:cajas,id_caja',
            'monto_cierre' => 'required|numeric|min:0',
            'fecha_cierre' => 'required|date',
        ], [
            'id_caja.required' => 'El campo caja es obligatorio',
            'id_caja.exists' => 'El campo caja seleccionado no existe',
            'monto_cierre.required' => 'El campo monto cierre es obligatorio',
            'monto_cierre.numeric' => 'El campo monto cierre debe ser un número',
            'monto_cierre.min' => 'El campo monto cierre debe ser un número positivo',
            'fecha_cierre.required' => 'El campo fecha cierre es obligatorio',
            'fecha_cierre.date' => 'El campo fecha cierre debe ser una fecha válida',
        ]);
    }
}