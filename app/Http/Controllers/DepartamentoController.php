<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use RealRashid\SweetAlert\Facades\Alert;

class DepartamentoController extends Controller
{
    public function __construct()
    {
        // validar que el usuario este autenticado
        $this->middleware('auth');
        // validar permisos para cada accion
        $this->middleware('permission:departamentos index')->only(['index']);
        $this->middleware('permission:departamentos create')->only(['create', 'store']);
        $this->middleware('permission:departamentos edit')->only(['edit', 'update']);
        $this->middleware('permission:departamentos destroy')->only(['destroy']);
    }
    public function index(Request $request)
    {
        $buscar = $request->get('buscar');
        if ($buscar) {
            $departamentos = DB::select(
                'SELECT * FROM departamentos WHERE descripcion ILIKE ?',
                ['%' . $buscar . '%']
            );
        } else {
            $departamentos = DB::select('SELECT * FROM departamentos');
        }
        //Definimos los valores de paginación
        $page = $request->input('page', 1);   // página actual (por defecto 1)
        $perPage = 10;                        // cantidad de registros por página
        $total = count($departamentos);       // total de registros

        // Cortamos el array para solo devolver los registros de la página actual
        $items = array_slice($departamentos, ($page - 1) * $perPage, $perPage);

        // Creamos el paginador manualmente
        $departamentos = new LengthAwarePaginator(
            $items,        // registros de esta página
            $total,        // total de registros
            $perPage,      // registros por página
            $page,         // página actual
            [
                'path'  => $request->url(),     // mantiene la ruta base
                'query' => $request->query(),   // mantiene parámetros como "buscar"
            ]
        );
        // Si la accion es buscador, significa que se debe recargar mediante ajax la tabla
        if ($request->ajax()) {
            return view('departamentos.table')->with('departamentos', $departamentos);
        }
        // Si no, se carga la vista normalmente
        return view('departamentos.index')->with('departamentos', $departamentos);
    }

    public function create()
    {
        return view('departamentos.create');
    }

    public function store(Request $request)
    {
        $input = $request->all();
        // validar los datos del formulario
        // 1. Limpieza
        $input['descripcion'] = Str::upper(Str::ascii(trim($input['descripcion'])));

        // 2. Validación
        $validacion = Validator::make(
            $input,
            [
                'descripcion' => 'required|unique:departamentos,descripcion',
            ],
            [
                'descripcion.required' => 'El campo descripción es obligatorio.',

                'descripcion.unique'   => 'Este registro ya existe en la base de datos.',
            ]
        );
        // Imprimir el error si la validacion falla
        if ($validacion->fails()) {
            return back()->withErrors($validacion)->withInput();
        }


        // Si la validación pasa, guardar el nuevo departamento utilizando la función insert de la base de datos
        DB::insert(
            'insert into departamentos (descripcion) values (?)',
            [
                $input['descripcion']
            ]
        );

        ## Imprimir mensaje de éxito y redirigir a la vista index
        Alert::toast('El departamento fue creado con éxito.', 'success');
        return redirect(route('departamentos.index'));
    }

    public function edit($id)
    {
        // Obtener el departamento por su ID utilizando la función select de la base de datos segun $id recibido
        $departamento = DB::selectOne('select * from departamentos where id_departamento = ?', [$id]);

        // Verificar si el departamento existe y no está vacío
        if (empty($departamento)) {
            Alert::error('Error', 'El departamento no fue encontrado.');
            // Redirigir a la vista index si el departamento no existe
            return redirect()->route('departamentos.index');
        }

        // Retornar la vista con el formulario de edición
        return view('departamentos.edit')->with('departamentos', $departamento);
    }

    public function update(Request $request, $id)
    {
        $input = $request->all();

        $input['descripcion'] = Str::upper(Str::ascii(trim($input['descripcion'])));

        $validacion = Validator::make(
            $input,
            [
                'descripcion' => 'required|unique:departamentos,descripcion,' . $id . ',id_departamento',
            ],
            [
                'descripcion.required' => 'El campo descripción es obligatorio.',

                'descripcion.unique'   => 'No puedes usar este nombre, ya existe otro igual.',
            ]
        );

        // Imprimir el error si la validacion falla
        if ($validacion->fails()) {
            return back()->withErrors($validacion)->withInput();
        }

        // Actualizar el departamento utilizando la función update de la base de datos
        DB::update(
            'update departamentos set descripcion = ? where id_departamento = ?',
            [
                $input['descripcion'],
                $id
            ]
        );

        // Imprimir mensaje de éxito y redirigir a la vista index
        Alert::toast('El departamento fue actualizado con éxito.', 'success');
        return redirect(route('departamentos.index'));
    }

    public function destroy($id)
    {
        // Obtener el cargo por su ID utilizando la función select de la base de datos segun $id recibido
        $departamentos = DB::selectOne('select * from departamentos where id_departamento = ?', [$id]);
        // Verificar si el cargo existe y no está vacío
        if (empty($departamentos)) {
            Alert::error('Error', 'El departamento no fue encontrado.');
            // Redirigir a la vista index si el cargo no existe
            return redirect()->route('departamentos.index');
        }

        // Eliminar el cargo utilizando la función delete de la base de datos
        DB::delete('delete from departamentos where id_departamento = ?', [$id]);

        // Imprimir mensaje de éxito y redirigir a la vista index
        Alert::toast('El departamento fue eliminado con éxito.', 'success');
        return redirect(route('departamentos.index'));
    }
}
