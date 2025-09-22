<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use RealRashid\SweetAlert\Facades\Alert;

class RoleController extends Controller
{
    public function index()
    {
        //Listar los roles
        $roles = DB::select('select * from roles order by id desc');
        return view('roles.index')->with('roles', $roles);
    }
    public function create()
    {
        //retornar la vista con el formulario de roles create
        return view('roles.create');
    }
    public function store(Request $request)
    {
        //recibir los datos del formulario
        $input = $request->all();

        //Validar los datos del formulario
        $validador = Validator::make($input, [
            'name' => 'required|unique:roles,name',
        ], [
            'name.required' => 'El campo nombre es obligatorio.',
            'name.unique' => 'El campo nombre ya está en uso.',
        ]);
        //Si la validacion falla redirigir de vuelta con errores
        if ($validador->fails()) {
            return redirect()->back()->withErrors($validador)->withInput();
        }
        //Insertar el nuevo rol en la base de datos
        DB::insert('INSERT INTO roles (name, guard_name) VALUES (?, ?)', [
            $input['name'],
            $input['guard_name']
        ]);
        //Mostrar un mensaje de configuracion
        Alert::toast('Rol creado con exito', 'success');
        //Redirigir al listado de roles
        return redirect()->route('roles.index');
    }
    public function edit($id)
    {
        $roles = DB::selectone('select * from roles where id = ?', [$id]);
        //verificar si se encontro el rol
        if (empty($roles)) {
            Alert::toast('El rol no existe', 'error');
            return redirect()->route('roles.index');
        }
        return view('roles.edit')->with('roles', $roles);
    }
    public function update(Request $request, $id)
    {
        $input = $request->all();
        $roles = DB::selectone('select * from roles where id = ?', [$id]);
        //verificar si se encontro el rol
        if (empty($roles)) {
            Alert::toast('El rol no existe', 'error');
            return redirect()->route('roles.index');
        }
        //Validar que el campo 'name' no este vacio
        $validacion = Validator::make(
            $input,
            [
                'name' => 'required|unique:roles,name,' . $id,
            ],
            [
                'name.required' => 'El campo nombre es obligatorio.',
                'name.unique' => 'El nombre del rol ya existe.',
            ]
        );
        //Si la validacion falla redirigir de vuelta con errores
        if ($validacion->fails()) {
            return redirect()->back()->withErrors($validacion)->withInput();
        }
        //Actualizar el rol en la base de datos
        DB::update('UPDATE roles SET name = ?, guard_name = ? WHERE id = ?', [
            $input['name'],
            $input['guard_name'],
            $id
        ]);
        //Mostrar un mensaje de configuracion
        Alert::toast('Rol actualizado con exito', 'success');
        //Redirigir al listado de roles
        return redirect()->route('roles.index');
    }
    public function destroy($id)
    {
        $roles = DB::selectone('SELECT * FROM roles WHERE id = ?', [$id]);
        //verificar si se encontro el rol
        if (empty($roles)) {
            Alert::toast('El rol no existe', 'error');
            return redirect()->route('roles.index');
        }
        //Utilizar un try catch para capturar el error de llave foranea
        try {
            //verificar si el rol esta asignado a algun usuario
            DB::delete('DELETE FROM model_has_roles WHERE role_id = ?', [$id]);
        } catch (\Exception $e) {
            //Manejar el error si el rol esta asignado a algun usuario
            Alert::toast('Error al eliminar el rol: ' . $e->getMessage(), 'error');
            return redirect()->route('roles.index');
        }
        //Mostrar un mensaje de Configuracion
        Alert::toast('Rol eliminado con exito', 'success');
        //Redirigir al listado de roles
        return redirect()->route('roles.index');
    }
}

