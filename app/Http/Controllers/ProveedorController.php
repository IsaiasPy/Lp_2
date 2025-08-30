<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Laracasts\Flash\Flash;

class ProveedorController extends Controller
{
    public function index()
{
    $proveedores = DB::select('SELECT * FROM proveedores');

    return view('proveedores.index')->with('proveedores', $proveedores);
}

    public function create()
    {
        return view('proveedores.create');
    }

    public function store(Request $request)
    {
        # campos recibidos del formulario proveedores
        $input = $request->all();

        # validacion de los campos proveedor
        $validacion = Validator::make(
            $input,
            [
                'descripcion' => 'required',
                'telefono' => 'required',
            ],
            [
                'descripcion.required' => 'El campo descripcion es obligatorio.',
                'telefono.required' => 'El campo telefono es obligatorio.',
            ]
        );

        if ($validacion->fails()) {
            return redirect()->back()->withErrors($validacion)->withInput();
        }

        # insertar proveedor en la base de datos
        DB::insert('INSERT INTO proveedores (descripcion, direccion, telefono) VALUES (?, ?, ?)', [
            $input['descripcion'],
            $input['direccion'],
            $input['telefono'],
        ]);

        # imprimir mensaje de exito
        Flash::success('El proveedor se ha creado con éxito.');

        # redireccionar a la lista de proveedores
        return redirect(route('proveedores.index'));
    }

    public function edit($id)
    {
        $proveedor = DB::selectOne('SELECT * FROM proveedores WHERE id_proveedor = ?', [$id]);

        if (empty($proveedor)) {
            Flash::error('Proveedor no encontrado');
            return redirect(route('proveedores.index'));
        }

        return view('proveedores.edit')->with('proveedores', $proveedor);
    }

    public function update(Request $request, $id)
    {
        $input = $request->all();

        $proveedor = DB::selectOne('SELECT * FROM proveedores WHERE id_proveedor = ?', [$id]);

        if (empty($proveedor)) {
            Flash::error('Proveedor no encontrado');
            return redirect(route('proveedores.index'));
        }

        $validacion = Validator::make(
            $input,
            [
                'descripcion' => 'required',
                'telefono' => 'required',
            ],
            [
                'descripcion.required' => 'El campo descripcion es obligatorio.',
                'telefono.required' => 'El campo telefono es obligatorio.',
            ]
        );

        if ($validacion->fails()) {
            return redirect()->back()->withErrors($validacion)->withInput();
        }

        DB::update(
            'UPDATE proveedores 
            SET descripcion = ?, 
                direccion = ?, 
                telefono = ? 
            WHERE id_proveedor = ?',
            [
                $input['descripcion'],
                $input['direccion'],
                $input['telefono'],
                $id
            ]
        );

        Flash::success('El proveedor se ha actualizado con éxito.');

        return redirect(route('proveedores.index'));
    }

    public function destroy($id) 
    {
        $proveedor = DB::selectOne('SELECT * FROM proveedores WHERE id_proveedor = ?', [$id]);

        if (empty($proveedor)) {
            Flash::error('Proveedor no encontrado');
            return redirect(route('proveedores.index'));
        }

        DB::delete('DELETE FROM proveedores WHERE id_proveedor = ?', [$id]);

        Flash::success('El proveedor se ha eliminado con éxito.');

        return redirect(route('proveedores.index'));
    }
}