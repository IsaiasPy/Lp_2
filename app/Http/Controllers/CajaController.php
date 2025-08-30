<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Laracasts\Flash\Flash;

class CajaController extends Controller
{
    public function index()
    {
        $cajas = DB::select(
        'SELECT c.*, s.descripcion as sucursal
        FROM cajas c
        JOIN sucursales s ON c.id_sucursal = s.id_sucursal
        ORDER BY c.id_caja DESC
        ');
        return view('cajas.index')->with('cajas', $cajas);
    }
    public function create()
    {
        //Obtener sucursales
        $sucursales = DB::table('sucursales')->pluck('descripcion', 'id_sucursal');
        return view('cajas.create')->with('sucursales', $sucursales);
    }
    public function store(Request $request)
    {
        $input = $request->all();

        //Validar los datos
        $validacion = Validator::make($input, [
            'descripcion' => 'required',
            'id_sucursal' => 'required|exists:sucursales,id_sucursal',
            'punto_expedicion' => 'required',
            'ultima_factura_impresa' => 'required',
        ], [
            'descripcion.required' => 'La descripcion es obligatoria',
            'id_sucursal.required' => 'La sucursal es obligatoria',
            'id_sucursal.exists' => 'La sucursal no existe',
            'punto_expedicion.required' => 'El punto de expedicion es obligatorio',
            'ultima_factura_impresa.required' => 'La ultima factura impresa es obligatoria',
        ]);
        //si la validacion falla, redirigir con errores
        if ($validacion->fails()) {
            return redirect()->back()
                ->withErrors($validacion)
                ->withInput();
        }
        //Insertar el nuevo producto en la Base de datos
        DB::insert(
            'INSERT INTO cajas (descripcion, id_sucursal, punto_expedicion, ultima_factura_impresa) VALUES (?, ?, ?, ?)',
            [
                $input['descripcion'],
                $input['id_sucursal'],
                $input['punto_expedicion'],
                $input['ultima_factura_impresa']
            ]
        );
        //Redirigir a la lista de cajas con un mesaje de exito
        Flash::success('Caja creada con exito');
        return redirect()->route('cajas.index');
    }
    public function edit($id)
    {
        $cajas = DB::selectOne('SELECT * FROM cajas WHERE id_caja = ?', [$id]); //SELECTONE devuelve un objeto()
        if (empty($cajas)) {
            flash('Caja no encontrado');
            return redirect()->route('cajas.index');
        }
        //Obtener sucursales
        $sucursales = DB::table('sucursales')->pluck('descripcion', 'id_sucursal');
        return view('cajas.edit')->with('cajas', $cajas)->with('sucursales', $sucursales)->with(
            'cajas', $cajas);
    }
    public function update(Request $request, $id)
    {
        $input = $request->all();
        //Obtener el producto de la base de datos  1 solo valor utilizando selectOne
        $cajas = DB::selectOne('SELECT * FROM cajas WHERE id_caja = ?', [$id]);
        //Validar si el producto no existe, redirigir con un mesaje de error
        if (empty($cajas)) {
            Flash('Caja no encontrado');
            return redirect()->route('cajas.index');
        }
        //Validar los datos
        $validacion = Validator::make($input, [
            'descripcion' => 'required',
            'id_sucursal' => 'required|exists:sucursales,id_sucursal',
            'punto_expedicion' => 'required',
            'ultima_factura_impresa' => 'required',
        ], [
            'descripcion.required' => 'La descripcion es obligatoria',
            'id_sucursal.required' => 'La sucursal es obligatoria',
            'id_sucursal.exists' => 'La sucursal no existe',
            'punto_expedicion.required' => 'El punto de expedicion es obligatorio',
            'ultima_factura_impresa.required' => 'La ultima factura impresa es obligatoria',
        ]);
        //si la validacion falla, redirigir con errores
        if ($validacion->fails()) {
            return redirect()->back()
                ->withErrors($validacion)
                ->withInput();
        }
        //Actualizar el producto en la Base de datos
        DB::update(
            'UPDATE cajas SET descripcion = ?, id_sucursal = ?, punto_expedicion = ?, ultima_factura_impresa = ? WHERE id_caja = ?',
            [
                $input['descripcion'],
                $input['id_sucursal'],
                $input['punto_expedicion'],
                $input['ultima_factura_impresa'],
                $id
            ]
        );
        //Redirigir a la lista de cajas con un mesaje de exito
        Flash::success('Caja actualizada con exito');
        return redirect()->route('cajas.index');
    }
    public function destroy($id)
    {
        //Validar si el producto no existe
        $cajas = DB::delete('DELETE FROM cajas WHERE id_caja = ?', [$id]);
        if (empty($cajas)) {
            flash('Caja no encontrado');
            return redirect()->route('cajas.index');
        }
        //Eliminar la caja de la base de datos
        DB::delete('DELETE FROM cajas WHERE id_caja = ?', [$id]);
        //Redirigir a la lista de productos con un mesaje de exito
        flash('Caja eliminada con exito');
        return redirect(route('cajas.index'));
    }
}
