<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laracasts\Flash\Flash;

class MarcaController extends Controller
{
    public function index(Request $request)
    {
        $buscar = $request->get('buscar');

        if ($buscar) {

            $marcas = DB::select(
                'select m.*, m.id_marca as id, m.descripcion as descripcion
                from marcas m
                where (cast(m.descripcion as text) ilike ?)
                order by id_marca desc',
                [
                    '%' . $buscar . '%',
                ]
            );
        } else {
            $marcas = DB::select(
                'SELECT * from marcas'
            );
        }
        // Definimos los valores de paginación
        $page = $request->input('page', 1);   // página actual (por defecto 1)
        $perPage = 10;                        // cantidad de registros por página
        $total = count($marcas);           // total de registros

        // Cortamos el array para solo devolver los registros de la página actual
        $items = array_slice($marcas, ($page - 1) * $perPage, $perPage);

        // Creamos el paginador manualmente
        $marcas = new LengthAwarePaginator(
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

            return view('marcas.table')->with('marcas', $marcas);
        }

        return view('marcas.index')->with('marcas', $marcas);
    }
    public function create()
    {
        return view('marcas.create');
    }
    public function store(Request $request)
    {
        // 1. Capturamos los datos
        $input = $request->all();

        // Por Normalizacion
        // Pasamos a mayúsculas y quitamos espacios en blanco al inicio/final
        if (isset($input['descripcion'])) {
            $input['descripcion'] = Str::upper(Str::ascii(trim($input['descripcion'])));
        }

        //Validación (Ahora comparará peras con peras)
        $validacion = Validator::make(
            $input,
            [
                'descripcion' => 'required|unique:marcas,descripcion'
            ],
            [
                'descripcion.required' => 'El campo descripcion es obligatorio',
                'descripcion.unique' => 'La marca ' . $input['descripcion'] . ' ya existe en el sistema.'
            ]
        );

        if ($validacion->fails()) {
            return back()->withErrors($validacion)->withInput();
        }

        // 4. Insertar usando el dato ya limpio ($input['descripcion'])
        DB::insert(
            'INSERT INTO marcas (descripcion) values (?)',
            [
                $input['descripcion'] // Aquí viaja en MAYÚSCULAS
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
        $input['descripcion'] = Str::upper(Str::ascii(trim($input['descripcion'])));
        $validacion = Validator::make(
            $input,
            [
                //Ignoramos el ID actual especificando 'id_marca' al final
                'descripcion' => 'required|unique:marcas,descripcion,' . $id . ',id_marca'
            ],
            [
                'descripcion.required' => 'El campo descripcion es obligatorio',
                'descripcion.unique' => 'Ya existe otra marca con este nombre.'
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
