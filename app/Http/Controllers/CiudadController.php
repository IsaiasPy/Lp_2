<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Laracasts\Flash\Flash;
use RealRashid\SweetAlert\Facades\Alert;

class CiudadController extends Controller
{
    public function index(Request $request)
    {
        $ciudades = DB::select("SELECT c.*, d.descripcion as departamento 
        FROM ciudades c 
        JOIN departamentos d ON c.id_departamento = d.id_departamento
        ORDER BY c.id_ciudad desc");

        return view('ciudades.index', compact('ciudades'));
    }
    public function create()
    {
        //mostrar departamentos en la vista con table pluck
        $departamentos = DB::table('departamentos')->pluck('descripcion', 'id_departamento');

        return view('ciudades.create')->with('departamentos', $departamentos);
    }
    public function store(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'descripcion' => 'required',
            'id_departamento' => 'required | exists:departamentos,id_departamento'
        ]);
        [
            'required' => 'El campo Descripcion es Obligatorio',
            'id_departamento.required' => 'El campo Departamento es Obligatorio',
            'exists' => 'El campo id_departamento proporcionado no existe'
        ];

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->errors();
        }

        DB::insert('INSERT INTO ciudades (descripcion, id_departamento) VALUES (?, ?)', [
            $input['descripcion'],
            $input['id_departamento']
        ]);

        //Flash::success('La ciudad se ha creado con exito.');
        Alert::success('Exito', 'La ciudad se ha creado con exito.');

        return redirect()->route('ciudades.index');
    }
    public function edit($id)
    {
        //Obtener la ciudad por su ID utilizando la funcion select de la base de datos
        $ciudad = DB::selectOne('select * from ciudades where id_ciudad = ?', [$id]);

        if (empty($ciudad)) {
           // Flash::error("La ciudad no encontrada.");
           Alert::error('Error', 'La ciudad no encontrada.');
            //Redirigir a la vista index si la ciudad no existe
            return redirect()->route('ciudades.index');
        }

        $departamentos = DB::table('departamentos')->pluck('descripcion', 'id_departamento');

        return view('ciudades.edit')->with('ciudades', $ciudad)->with('departamentos', $departamentos);
    }
    public function update(Request $request, $id) {
        $input = $request->all();
        $ciudad = DB::selectOne('select * from ciudades where id_ciudad = ?', [$id]);

        if (empty($ciudad)) {
            Flash::error("La ciudad no encontrada.");
            return redirect()->route('ciudades.index');
        }

        $validator = Validator::make($input, [
            'descripcion' => 'required',
            'id_departamento' => 'required | exists:departamentos,id_departamento'
        ]);
        [
            'required' => 'El campo Descripcion es Obligatorio',
            'id_departamento.required' => 'El campo Departamento es Obligatorio',
            'exists' => 'El campo id_departamento proporcionado no existe'
        ];
    DB::update('update ciudades set descripcion = ?, id_departamento = ? where id_ciudad = ?', [
        $input['descripcion'],
        $input['id_departamento'],
        $id
    ]);
    flash()->success('La ciudad se ha actualizado con exito.');    
    return redirect()->route('ciudades.index');
}
    public function destroy($id) {
        $ciudad = DB::selectOne('select * from ciudades where id_ciudad = ?', [$id]);

        if (empty($ciudad)) {
            //Flash::error("La ciudad no encontrada.");
            Alert::error('Error', 'La ciudad no encontrada.');
            return redirect()->route('ciudades.index');
        }
        DB::delete('delete from ciudades where id_ciudad = ?', [$id]);
        //flash()->success('La ciudad se ha eliminado con exito.');
        Alert::success('Exito', 'La ciudad se ha eliminado con exito.');        

        return redirect()->route('ciudades.index');
    }
}
