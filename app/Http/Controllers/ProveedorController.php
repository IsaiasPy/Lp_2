<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laracasts\Flash\Flash;

class ProveedorController extends Controller
{
    public function index(Request $request)
    {
        $buscar = $request->get('buscar');

        if ($buscar) {

            $proveedores = DB::select(
                'select * from proveedores 
                where (cast(descripcion as text) ilike ?
                OR cast(direccion as text) ilike ?
                OR cast(telefono as text) ilike ?)',
                [
                    '%' . $buscar . '%',
                    '%' . $buscar . '%',
                    '%' . $buscar . '%'
                ]
            );
        } else {
            $proveedores = DB::select(
                'SELECT * from proveedores'
            );
        }
        // Definimos los valores de paginación
        $page = $request->input('page', 1);   // página actual (por defecto 1)
        $perPage = 10;                        // cantidad de registros por página
        $total = count($proveedores);           // total de registros

        // Cortamos el array para solo devolver los registros de la página actual
        $items = array_slice($proveedores, ($page - 1) * $perPage, $perPage);

        // Creamos el paginador manualmente
        $proveedores = new LengthAwarePaginator(
            $items,        // registros de esta página
            $total,        // total de registros
            $perPage,      // registros por página
            $page,         // página actual
            [
                'path'  => $request->url(),     // mantiene la ruta base
                'query' => $request->query(),   // mantiene parámetros como "buscar"
            ]
        );

        if ($request->ajax()) {

            return view('proveedores.table')->with('proveedores', $proveedores);
        }

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

        $input['descripcion'] = Str::upper(Str::ascii(trim($input['descripcion'])));

        # validacion de los campos proveedor
        $validacion = Validator::make(
            $input,
            [
                'descripcion' => 'required|unique:proveedores,descripcion',
                'telefono' => 'required',
            ],
            [
                'descripcion.required' => 'El campo descripcion es obligatorio.',
                'descripcion.unique' => 'Ya existe un proveedor con esta descripcion.',
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

        $input['descripcion'] = Str::upper(Str::ascii(trim($input['descripcion'])));

        $proveedor = DB::selectOne('SELECT * FROM proveedores WHERE id_proveedor = ?', [$id]);

        if (empty($proveedor)) {
            Flash::error('Proveedor no encontrado');
            return redirect(route('proveedores.index'));
        }

        $validacion = Validator::make(
            $input,
            [
                'descripcion' => 'required|unique:proveedores,descripcion,' . $id . ',id_proveedor',
                'telefono' => 'required',
            ],
            [
                'descripcion.required' => 'El campo descripcion es obligatorio.',
                'descripcion.unique' => 'Ya existe un proveedor con esta descripcion.',
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
