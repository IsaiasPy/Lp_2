<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Laracasts\Flash\Flash;

class MarcaController extends Controller
{
    public function index()
    {
        $marcas = DB::SELECT('SELECT * FROM marcas');
        return view('marcas.index')->with('marcas', $marcas);
    }
    public function create()
    {
        return view('marcas.create');
    }
    public function store(Request $request)
    {
        //campos recibidos del formulario
        $input = $request->all();
        //validacion de los campos
        $validacion = Validator::make(
            $input,
            [
                'descripcion' => 'required'
            ],
            [
                'descripcion.required' => 'El campo descripcion es obligatorio'
            ]
        );
        if ($validacion->fails()) {
            return back()->withErrors($validacion)->withInput();
        }
        //insertar proveedores en la base de datos
        DB::insert(
            'INSERT INTO marcas (descripcion) values (?)',
            [
                $input['descripcion']
            ]
        );
        Flash::success('Marca creada con exito');
        return redirect()->route('marcas.index');
    }
    public function edit($id)
    {
        $marca = DB::selectOne('SELECT * FROM marcas WHERE id_marca = ?', [$id]);
        if (empty($marca)) {
            Flash::error('Marca no encontrado');
            return redirect()->route('marcas.index');
        }
        return view('marcas.edit')->with('marcas', $marca);
    }
    public function update(Request $request, $id)
    {
        $marca = DB::selectOne('SELECT * FROM marcas WHERE id_marca = ?', [$id]);
        if (empty($marca)) {
            Flash::error('Marca no encontrado');
            return redirect()->route('marcas.index');
        }
        $input = $request->all();
        $validacion = Validator::make(
            $input,
            [
                'descripcion' => 'required'
            ],
            [
                'descripcion.required' => 'El campo descripcion es obligatorio'
            ]
        );
        if ($validacion->fails()) {
            return back()->withErrors($validacion)->withInput();
        }
        DB::update(
            'UPDATE marcas 
       SET descripcion = ? 
       WHERE id_marca = ?',
            [
                $input['descripcion'],
                $id
            ]
        );
        Flash::success('Marca actualizada con exito');
        return redirect()->route('marcas.index');
    }
    public function destroy($id)
    {
        $marca = DB::selectOne('SELECT * FROM marcas WHERE id_marca = ?', [$id]);
        if (empty($marca)) {
            Flash::error('Marca no encontrado');
            return redirect()->route('marcas.index');
        }
        DB::delete('DELETE FROM marcas WHERE id_marca = ?', [$id]);
        Flash::success('Marca eliminada con exito');
        return redirect()->route('marcas.index');
    }
}
