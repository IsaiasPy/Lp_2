<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Laracasts\Flash\Flash;

class SucursalController extends Controller
{
    public function index(){
       $sucursales = DB::select(
       'SELECT s.*, c.descripcion as ciudades
	    FROM sucursales s
	    JOIN ciudades c ON s.id_ciudad = c.id_ciudad
	    ORDER BY s.id_sucursal DESC'
        );
        return view('sucursales.index')->with('sucursales', $sucursales);
    }
    public function create(){
        //Obtener ciudades
        $ciudades = DB::table('ciudades')->pluck('descripcion', 'id_ciudad');

        return view('sucursales.create')->with('ciudades', $ciudades);
        
    }
    public function store(Request $request){
        $input = $request->all();

        //Validar los datos
        $validacion = Validator::make($input, [
            'descripcion' => 'required',
            'direccion' => 'required',
            'telefono' => 'required',
            'id_ciudad' => 'required|exists:ciudades,id_ciudad',
        ], [
            'descripcion.required' => 'La descripcion es obligatoria',
            'direccion.required' => 'La direccion es obligatoria',
            'telefono.required' => 'El telefono es obligatorio',
            'id_ciudad.required' => 'La ciudad es obligatoria',
            'id_ciudad.exists' => 'La ciudad no existe',
        ]);
        //si la validacion falla, redirigir con errores
        if ($validacion->fails()) {
            return redirect()->back()
                ->withErrors($validacion)
                ->withInput();
        }
        //Insertar el nuevo producto en la Base de datos
        DB::insert(
            'INSERT INTO sucursales (descripcion, direccion, telefono, id_ciudad) VALUES (?, ?, ?, ?)',
            [
                $input['descripcion'],
                $input['direccion'],
                $input['telefono'],
                $input['id_ciudad']
            ]
        );
        //Redirigir a la lista de sucursales con un mesaje de exito
        Flash::success('Sucursal creada con exito');
        return redirect()->route('sucursales.index');   
    }
    public function edit($id){
        $sucursales = DB::selectOne('SELECT * FROM sucursales WHERE id_sucursal = ?', [$id]); //SELECTONE devuelve un objeto()
        if (empty($sucursales)) {
            flash('Sucursal no encontrado');
            return redirect()->route('sucursales.index');
        }
        //Obtener ciudades
        $ciudades = DB::table('ciudades')->pluck('descripcion', 'id_ciudad');
        return view('sucursales.edit')->with('sucursales', $sucursales)->with('ciudades', $ciudades);
    }
    public function update(Request $request, $id){
        $input = $request->all();
        //Obtener el producto de la base de datos  1 solo valor utilizando selectOne
        $sucursal = DB::selectOne('SELECT * FROM sucursales WHERE id_sucursal = ?', [$id]);
        //Validar si el producto no existe, redirigir con un mesaje de error
        if (empty($sucursal)) {
            flash('Sucursal no encontrado');
            return redirect()->route('sucursales.index');
        }
        //Validar los datos
        $validacion = Validator::make($input, [
            'descripcion' => 'required',
            'direccion' => 'required',
            'telefono' => 'required',
            'id_ciudad' => 'required|exists:ciudades,id_ciudad',
        ], [
            'descripcion.required' => 'La descripcion es obligatoria',
            'direccion.required' => 'La direccion es obligatoria',
            'telefono.required' => 'El telefono es obligatorio',
            'id_ciudad.required' => 'La ciudad es obligatoria',
            'id_ciudad.exists' => 'La ciudad no existe',
        ]);
        //si la validacion falla, redirigir con errores
        if ($validacion->fails()) {
            return redirect()->back()
                ->withErrors($validacion)
                ->withInput();
        }
        //Actualizar el producto en la Base de datos
        DB::update(
            'UPDATE sucursales SET descripcion = ?, direccion = ?, telefono = ?, id_ciudad = ? WHERE id_sucursal = ?',
            [
                $input['descripcion'],
                $input['direccion'],
                $input['telefono'],
                $input['id_ciudad'],
                $id
            ]
        );
        //Redirigir a la lista de sucursales con un mesaje de exito
        Flash::success('Sucursal actualizada con exito');
        return redirect()->route('sucursales.index');
    }
    public function destroy($id){
        //Validar si el producto no existe
        $sucursal = DB::delete('DELETE FROM sucursales WHERE id_sucursal = ?', [$id]);
        if (empty($sucursal)) {
            flash('Sucursal no encontrado');
            return redirect()->route('sucursales.index');
        }
        //Eliminar el producto de la base de datos
        DB::delete('DELETE FROM sucursales WHERE id_sucursal = ?', [$id]);
        
        //Redirigir a la lista de productos con un mesaje de exito
        flash('Sucursal eliminada con exito');

        return redirect(route('sucursales.index'));
    }
}
