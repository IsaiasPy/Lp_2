<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use RealRashid\SweetAlert\Facades\Alert;

class PermissionController extends Controller
{
    public function index()
    {
        // Listar permisos existentes
        $permisos = DB::select('SELECT * FROM permissions order by id desc');

        return view('permissions.index')->with('permisos', $permisos);
    }

    public function create()
    {
        return view('permissions.create');
    }

    public function store(Request $request)
    {
        $input = $request->all();
        // Validar que el campo 'name' no esté vacío
        $validacion = Validator::make($input, [
            'name' => 'required|unique:permissions,name',
        ], [
            'name.required' => 'El campo Permiso es obligatorio.',
            'name.unique' => 'El permiso ya existe en la base de datos.',
        ]);

        // Si la validación falla, redirigir de vuelta con errores
        if ($validacion->fails()) {
            return redirect()->back()->withErrors($validacion)->withInput();
        }

        // Insertar el nuevo permiso en la base de datos
        DB::insert('INSERT INTO permissions (name, guard_name) VALUES (?, ?)', [
            $input['name'],
            $input['guard_name'],
        ]);

        // Mostrar un mensaje de confirmación
        Alert::toast('Permiso creado correctamente', 'success');

        // Redirigir al listado de permisos
        return redirect()->route('permissions.index');
    }

    public function edit($id) 
    {
        $permisos = DB::selectOne('SELECT * FROM permissions WHERE id = ?', [$id]);
        // Verificar si se encontró el permiso
        if (empty($permisos)) {
            Alert::toast('Permiso no encontrado', 'error');
            return redirect()->route('permissions.index');
        }

        return view('permissions.edit')->with('permisos', $permisos);
    }

    public function update(Request $request, $id) 
    {
        $input = $request->all();

        $permisos = DB::selectOne('SELECT * FROM permissions WHERE id = ?', [$id]);
        // Verificar si se encontró el permiso
        if (empty($permisos)) {
            Alert::toast('Permiso no encontrado', 'error');
            return redirect()->route('permissions.index');
        }

        // Validar que el campo 'name' no esté vacío y sea único, excluyendo el permiso actual
        $validacion = Validator::make($input, [
            'name' => 'required|unique:permissions,name,' . $id,
        ], [
            'name.required' => 'El campo Permiso es obligatorio.',
            'name.unique' => 'El permiso ya existe en la base de datos.',
        ]);

        // Si la validación falla, redirigir de vuelta con errores
        if ($validacion->fails()) {
            return redirect()->back()->withErrors($validacion)->withInput();
        }

        // Actualizar el permiso en la base de datos
        DB::update('UPDATE permissions SET name = ?, guard_name = ? WHERE id = ?', [
            $input['name'],
            $input['guard_name'],
            $id,
        ]);

        // Mostrar un mensaje de confirmación
        Alert::toast('Permiso actualizado correctamente', 'success');

        // Redirigir al listado de permisos
        return redirect()->route('permissions.index');
    }

    public function destroy($id) 
    {
        $permisos = DB::selectOne('SELECT * FROM permissions WHERE id = ?', [$id]);
        // Verificar si se encontró el permiso
        if (empty($permisos)) {
            Alert::toast('Permiso no encontrado', 'error');
            return redirect()->route('permissions.index');
        }

        // Utilizar un bloque try-catch para manejar posibles errores al eliminar
        try{
            // Eliminar el permiso de la base de datos
            DB::delete('DELETE FROM permissions WHERE id = ?', [$id]);
        }catch(\Exception $e){
            // Manejar el error si el permiso está asignado a algún rol
            Alert::toast('Error al eliminar el permiso: ' . $e->getMessage(), 'error');
            return redirect()->route('permissions.index');
        }
        

        // Mostrar un mensaje de confirmación
        Alert::toast('Permiso eliminado correctamente', 'success');

        // Redirigir al listado de permisos
        return redirect()->route('permissions.index');
    }
}
