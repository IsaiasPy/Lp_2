<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Laracasts\Flash\Flash;

class DepartamentoController extends Controller
{
    public function index()
    {
        $departamentos = DB::select('SELECT * FROM departamentos');

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
        $validator = Validator::make(
            $input,
            [
                'descripcion' => 'required',
            ],
            [
                'descripcion.required' => 'El campo descripción es obligatorio.',
            ]
        );

        // Imprimir el error si la validacion falla
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }


        // Si la validación pasa, guardar el nuevo departamento utilizando la función insert de la base de datos
        DB::insert('insert into departamentos (descripcion) values (?)', 
            [ 
                $input['descripcion']
            ]
        );

        ## Imprimir mensaje de éxito y redirigir a la vista index
        Flash::success("El cargo fue creado con éxito.");
        return redirect(route('departamentos.index'));
    }

    public function edit($id)
    {
        // Obtener el departamento por su ID utilizando la función select de la base de datos segun $id recibido
        $departamento = DB::selectOne('select * from departamentos where id_departamento = ?', [$id]);

        // Verificar si el departamento existe y no está vacío
        if (empty($departamento)) {
            Flash::error("El departamento no fue encontrado.");
            // Redirigir a la vista index si el departamento no existe
            return redirect()->route('departamentos.index');
        }

        // Retornar la vista con el formulario de edición
        return view('departamentos.edit')->with('departamentos', $departamento);
    }

    public function update(Request $request, $id)
    {
        $input = $request->all();

        // Validar los datos del formulario
        $validator = Validator::make(
            $input,
            [
                'descripcion' => 'required',
            ],
            [
                'descripcion.required' => 'El campo descripción es obligatorio.',
            ]
        );

        // Imprimir el error si la validacion falla
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Actualizar el departamento utilizando la función update de la base de datos
        DB::update('update departamentos set descripcion = ? where id_departamento = ?', 
            [ 
                $input['descripcion'], 
                $id
            ]
        );

        // Imprimir mensaje de éxito y redirigir a la vista index
        Flash::success("El departamento fue actualizado con éxito.");
        return redirect(route('departamentos.index'));
    }

    public function destroy($id)
    {
        // Obtener el cargo por su ID utilizando la función select de la base de datos segun $id recibido
        $departamentos = DB::selectOne('select * from departamentos where id_departamento = ?', [$id]);
        // Verificar si el cargo existe y no está vacío
        if (empty($departamentos)) {
            Flash::error("El departamento no fue encontrado.");
            // Redirigir a la vista index si el cargo no existe
            return redirect()->route('departamentos.index');
        }

        // Eliminar el cargo utilizando la función delete de la base de datos
        DB::delete('delete from departamentos where id_departamento = ?', [$id]);

        // Imprimir mensaje de éxito y redirigir a la vista index
        Flash::success("El departamento fue eliminado con éxito.");
        return redirect(route('departamentos.index'));
    }
}

