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
        //eliminar comas del monto de apertura
        $input['monto_apertura'] = str_replace(',', '', $input['monto_apertura']);

        $input['monto_apertura'] = !empty($input['monto_apertura']) ? $input['monto_apertura'] : 0;

        //validar datos recibidos
        $validacion = Validator::make($input, [
            'id_caja' => 'required|exists:cajas,id_caja',
            'monto_apertura' => 'numeric|min:0',
            'fecha_apertura' => 'required|date',
        ],
        [
            'id_caja.required' => 'La caja es obligatoria',
            'id_caja.exists' => 'La caja no existe',
            'monto_apertura.required' => 'El monto de apertura es obligatorio',
            'monto_apertura.numeric' => 'El monto de apertura debe ser un número',
            'monto_apertura.min' => 'El monto de apertura debe ser mayor o igual a 0',
            'fecha_apertura.required' => 'La fecha de apertura es obligatoria',
            'fecha_apertura.date' => 'La fecha de apertura debe ser una fecha válida',
        ]);
        if ($validacion->fails()) {
            return redirect()->route('ventas.index')
            ->withErrors($validacion)
            ->withInput();
        }
        //insertar apertura en la base de datos
        DB::insert(
            'INSERT INTO apertura_cierre_cajas (id_caja, monto_apertura, fecha_apertura, user_id, estado) 
            VALUES (?, ?, ?, ?, ?)',
            $input['id_caja'],
            $input['monto_apertura'],
            $input['fecha_apertura'],
            auth()->user()->id,
            'ABIERTA'
        );
        Alert::toast('Apertura de caja abierta con exito', 'success');
        return redirect()->route('ventas.index');
        }

    public function cerrar_caja(Request $request)
    {
        //
    }
}