<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Laracasts\Flash\Flash;

class CargoController extends Controller
{
    public function index()
    {
        # Ejemplo query builder
        #$cargos = DB::table('cargos')->get();
        # Ejemplo con sql puro a utilizar
        $cargos = DB::select('select * from cargos');

        return view('cargos.index')->with('cargos', $cargos);
    }

    public function create()
    {
        // retornar la vista con el formulario de cargos create
        return view('cargos.create');
    }
    public function store(Request $request)
    {
        // recibir los datos del formulario
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


        // Si la validación pasa, guardar el nuevo cargo utilizando la función insert de la base de datos
        DB::insert('insert into cargos (descripcion) values (?)', 
            [ 
                $input['descripcion']
            ]
        );

        ## Imprimir mensaje de éxito y redirigir a la vista index
        Flash::success("El cargo fue creado con éxito.");
        return redirect(route('cargos.index'));
    }

    public function edit($id)
    {
        // Obtener el cargo por su ID utilizando la función select de la base de datos segun $id recibido
        $cargo = DB::selectOne('select * from cargos where id_cargo = ?', [$id]);

        // Verificar si el cargo existe y no está vacío
        if (empty($cargo)) {
            Flash::error("El cargo no fue encontrado.");
            // Redirigir a la vista index si el cargo no existe
            return redirect()->route('cargos.index');
        }

        // Retornar la vista con el formulario de edición
        return view('cargos.edit')->with('cargo', $cargo);
    }

    public function update(Request $request, $id)
    {
        // Actualizar el cargo utilizando la función update de la base de datos
        $input = $request->all();
        // Obtener el cargo por su ID utilizando la función select de la base de datos segun $id recibido
        $cargo = DB::selectOne('select * from cargos where id_cargo = ?', [$id]);
        // Verificar si el cargo existe y no está vacío
        if (empty($cargo)) {
            Flash::error("El cargo no fue encontrado.");
            // Redirigir a la vista index si el cargo no existe
            return redirect()->route('cargos.index');
        }

        // Validar los datos de entrada utilizando la clase Validator de Laravel
        $validator = Validator::make(
            $input,
            [
                'descripcion' => 'required',
            ],
            [
                'descripcion.required' => 'El campo descripción es obligatorio.',
            ]
        );

        // Imprimir los errores de validación si existen
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::update('update cargos set descripcion = ? where id_cargo = ?', 
            [
                $input['descripcion'],
                $id
            ]
        );

        // Imprimir mensaje de éxito y redirigir a la vista index
        Flash::success("El cargo fue actualizado con éxito.");
        return redirect(route('cargos.index'));
    }

    public function destroy($id)
    {
        $cargo = DB::selectOne('select * from cargos where id_cargo = ?', [$id]);
        // Verificar si el cargo existe y no está vacío
        if (empty($cargo)) {
            Flash::error("El cargo no fue encontrado.");
            // Redirigir a la vista index si el cargo no existe
            return redirect()->route('cargos.index');
        }
        // Eliminar el cargo utilizando la función delete de la base de datos
        DB::delete('delete from cargos where id_cargo = ?', [$id]);

        // Imprimir mensaje de éxito y redirigir a la vista index
        Flash::success("El cargo fue eliminado con éxito.");
        return redirect(route('cargos.index'));
    }
}