<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Laracasts\Flash\Flash;

class ClienteController extends Controller
{
    public function index()
    {
        $clientes = DB::select('
            SELECT c.*, ciu.descripcion as ciudad,
            d.descripcion as departamento
            FROM clientes c
            JOIN ciudades ciu ON ciu.id_ciudad = c.id_ciudad
            JOIN departamentos d ON d.id_departamento = c.id_departamento
        ');

        return view('clientes.index')->with('clientes', $clientes);
    }

    public function create()
    {
        // Armar consultar para cargar ciudad y departamento para el select
        $ciudades = DB::table('ciudades')->pluck('descripcion', 'id_ciudad');
        $departamentos = DB::table('departamentos')->pluck('descripcion', 'id_departamento');

        return view('clientes.create')->with('ciudades', $ciudades)
                                      ->with('departamentos', $departamentos);
    }

    public function store(Request $request)
    {
        $input = $request->all();
        # Obtener fecha actual
        $fec_actual = Carbon::now();
        # Parsear la fecha de nacimiento input clie_fecha_nac
        $fecha_nac = Carbon::parse($input['clie_fecha_nac']);

        $validacion = Validator::make($input, [
            'clie_nombre.required',
            'clie_apellido.required',
            #'clie_ci.required|unique:clientes,clie_ci|max:8',
            'clie_fecha_nac.required|date',
            'id_departamento.required|exists:departamentos,id_departamento',
            'id_ciudad.required|exists:ciudades,id_ciudad',
        ],
        [
            'clie_nombre.required' => 'Campo nombre obligatorio',
            'clie_apellido.required' => 'Campo apellido obligatorio',
            'clie_ci.required' => 'Campo CI obligatorio',
            'clie_ci.unique' => 'El dato de CI ya existe',
            #'clie_ci.max' => 'El campo CI debe tener como máximo 8 caracteres',
            'clie_fecha_nac.required' => 'Campo fecha de nacimiento obligatorio',
            'clie_fecha_nac.date' => 'Campo fecha de nacimiento debe ser una fecha válida',
            'id_departamento.required' => 'Campo departamento obligatorio',
            'id_departamento.exists' => 'Campo departamento debe ser un departamento válido',
            'id_ciudad.required' => 'Campo ciudad obligatorio',
            'id_ciudad.exists' => 'Campo ciudad debe ser una ciudad válida',
        ]);

        ##validar edad del cliente
        $edad = $fec_actual->diffInYears($fecha_nac);
        if ($edad < 18) {
            Flash::error('El cliente debe ser mayor de 18 años.');
            return redirect(route('clientes.create'))->withInput();
        }

        ## validar fecha mayor al actual
        if ($fecha_nac > $fec_actual) {
            Flash::error('La fecha de nacimiento no puede ser mayor a la fecha actual.');
            return redirect(route('clientes.create'))->withInput();
        }

        ##validar cantidad de digitos del campo ci
        $ci = strlen($input['clie_ci']); // utilizar strlen para contar caracteres
        if ($ci > 8) {# mayor a 8 caracteres
            Flash::error('El nro de cedula solo podra contener 8 digítos.');
            return redirect(route('clientes.create'))->withInput();
        }

        # insertar datos en clientes
        DB::insert('INSERT INTO clientes (clie_nombre, clie_apellido, clie_ci, clie_telefono, clie_direccion, cli_fecha_nac, id_departamento, id_ciudad) VALUES (?, ?, ?, ?, ?, ?, ?, ?)', [
            $input['clie_nombre'],
            $input['clie_apellido'],
            $input['clie_ci'],
            $input['clie_telefono'],
            $input['clie_direccion'],
            $input['clie_fecha_nac'],
            $input['id_departamento'],
            $input['id_ciudad']
        ]);

        Flash::success('Cliente creado correctamente.');

        return redirect(route('clientes.index'));

    }

    public function edit($id)
    {
        // Validar que exista el id cliente antes de procesar
        $clientes = DB::selectOne('SELECT * FROM clientes WHERE id_cliente = ?', [$id]);

        if (empty($clientes)) {
            Flash::error('Cliente no encontrado.');
            return redirect(route('clientes.index'));
        }

        // Armar consultar para cargar ciudad y departamento para el select
        $ciudades = DB::table('ciudades')->pluck('descripcion', 'id_ciudad');
        $departamentos = DB::table('departamentos')->pluck('descripcion', 'id_departamento');

        return view('clientes.edit')->with('clientes', $clientes)
                                     ->with('ciudades', $ciudades)
                                     ->with('departamentos', $departamentos);
    }

    public function update(Request $request, $id)
    {
        $input = $request->all();

        $clientes = DB::selectOne('SELECT * FROM clientes WHERE id_cliente = ?', [$id]);

        if (empty($clientes)) {
            Flash::error('Cliente no encontrado.');
            return redirect(route('clientes.index'));
        }

        # Obtener fecha actual
        $fec_actual = Carbon::now();
        # Parsear la fecha de nacimiento input clie_fecha_nac
        $fecha_nac = Carbon::parse($input['clie_fecha_nac']);

        // Validaciones
        $validacion = Validator::make($input, [
            'clie_nombre.required',
            'clie_apellido.required',
            #'clie_ci.required|unique:clientes,clie_ci|max:8',
            'clie_fecha_nac.required|date',
            'id_departamento.required|exists:departamentos,id_departamento',
            'id_ciudad.required|exists:ciudades,id_ciudad',
        ],
        [
            'clie_nombre.required' => 'Campo nombre obligatorio',
            'clie_apellido.required' => 'Campo apellido obligatorio',
            'clie_ci.required' => 'Campo CI obligatorio',
            'clie_ci.unique' => 'El dato de CI ya existe',
            #'clie_ci.max' => 'El campo CI debe tener como máximo 8 caracteres',
            'clie_fecha_nac.required' => 'Campo fecha de nacimiento obligatorio',
            'clie_fecha_nac.date' => 'Campo fecha de nacimiento debe ser una fecha válida',
            'id_departamento.required' => 'Campo departamento obligatorio',
            'id_departamento.exists' => 'Campo departamento debe ser un departamento válido',
            'id_ciudad.required' => 'Campo ciudad obligatorio',
            'id_ciudad.exists' => 'Campo ciudad debe ser una ciudad válida',
        ]);

        if ($validacion->fails()) {
            Flash::error('Error en la validación de datos.');
            return redirect()->back()->withErrors($validacion)->withInput();
        }

        ##validar edad del cliente
        $edad = $fec_actual->diffInYears($fecha_nac);
        if ($edad < 18 || $fecha_nac > $fec_actual) {
            Flash::error('El cliente debe ser mayor de 18 años y la fecha de nacimiento no puede ser mayor a la fecha actual.');
            return redirect()->back()->withInput();
        }

        ## validar fecha mayor al actual
        // if ($fecha_nac > $fec_actual) {
        //     Flash::error('La fecha de nacimiento no puede ser mayor a la fecha actual.');
        //     return redirect()->back()->withInput();
        // }

        ##validar cantidad de digitos del campo ci
        $ci = strlen($input['clie_ci']); // utilizar strlen para contar caracteres
        if ($ci > 8) {# mayor a 8 caracteres
            Flash::error('El nro de cedula solo podra contener 8 digítos.');
            return redirect(route('clientes.edit', $id))->withInput();
        }

        DB::update('UPDATE clientes SET 
            clie_nombre = ?, 
            cli_apellido = ?, 
            clie_ci = ?, 
            clie_telefono = ?, 
            clie_direccion = ?, 
            cli_fecha_nac = ?, 
            id_departamento = ?, 
            id_ciudad = ? 
        WHERE id_cliente = ?', 
        [
            $input['clie_nombre'],
            $input['clie_apellido'],
            $input['clie_ci'],
            $input['clie_telefono'],
            $input['clie_direccion'],
            $input['clie_fecha_nac'],
            $input['id_departamento'],
            $input['id_ciudad'],
            $id
        ]);

        Flash::success('Cliente actualizado correctamente.');

        return redirect(route('clientes.index'));
    }

    public function destroy($id)
    {
        $clientes = DB::selectOne('SELECT * FROM clientes WHERE id_cliente = ?', [$id]);

        if(empty($clientes)) {
            Flash::error('Cliente no encontrado.');
            return redirect(route('clientes.index'));
        }
        # Utilizaremos try catch en clientes 
        try {
            DB::delete('DELETE FROM clientes WHERE id_cliente = ?', [$id]);
            Flash::success('Cliente eliminado correctamente.');
        } catch (\Exception $e) {// excepcion capturada desde la base de datos
            Flash::error('Error al eliminar el cliente. Por motivo: ' . $e->getMessage());
        }

        return redirect(route('clientes.index'));
    }
}