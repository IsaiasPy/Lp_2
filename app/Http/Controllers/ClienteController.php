<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laracasts\Flash\Flash;
use RealRashid\SweetAlert\Facades\Alert;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        $buscar = $request->get('buscar');

        if ($buscar) {
            $clientes = DB::select('SELECT c.*, ciu.descripcion as ciudad,
            d.descripcion as departamento
            FROM clientes c
            JOIN ciudades ciu ON ciu.id_ciudad = c.id_ciudad
            JOIN departamentos d ON d.id_departamento = c.id_departamento
            WHERE (CAST(c.clie_ci AS TEXT) iLIKE ?
            OR c.clie_nombre iLIKE ?
            OR c.clie_apellido iLIKE ?)',
            [
            "%$buscar%", 
            "%$buscar%", 
            "%$buscar%"
            ]);
        }
        else {
            $clientes = DB::select('SELECT c.*, ciu.descripcion as ciudad,
            d.descripcion as departamento
            FROM clientes c
            JOIN ciudades ciu ON ciu.id_ciudad = c.id_ciudad
            JOIN departamentos d ON d.id_departamento = c.id_departamento');
        }
        
        // Definimos los valores de paginación
        $page = $request->input('page', 1);   // página actual (por defecto 1)
        $perPage = 10;                        // cantidad de registros por página
        $total = count($clientes);           // total de registros

        // Cortamos el array para solo devolver los registros de la página actual
        $items = array_slice($clientes, ($page - 1) * $perPage, $perPage);

        // Creamos el paginador manualmente
        $clientes = new LengthAwarePaginator(
            $items,        // registros de esta página
            $total,        // total de registros
            $perPage,      // registros por página
            $page,         // página actual
            [
                'path'  => $request->url(),     // mantiene la ruta base
                'query' => $request->query(),   // mantiene parámetros como "buscar"
            ]
        );

        // si la accion es buscardor entonces significa que se debe recargar mediante ajax la tabla
        if ($request->ajax()) {
            //solo llmamamos a table.blade.php y mediante compact pasamos la variable users
            return view('clientes.table')->with('clientes', $clientes);
        }
        // si no es busqueda entonces simplemente se muestra la vista
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

        // --- PASO 1: SANITIZACIÓN DE DATOS ---
    // Quitamos los puntos de la cédula para dejar solo números
    if(isset($input['clie_ci'])) {
        $input['clie_ci'] = str_replace('.', '', $input['clie_ci']);
    }

    // Convertimos a mayúsculas para mantener el estándar
    $input['clie_nombre'] = Str::upper(Str::ascii(trim($input['clie_nombre'])));
    $input['clie_apellido'] = Str::upper(Str::ascii(trim($input['clie_apellido'])));
    $input['clie_direccion'] = Str::upper(Str::ascii(trim($input['clie_direccion'])));

    // --- PASO 2: VALIDACIÓN ---
    $validacion = Validator::make($input, [
        'clie_nombre' => 'required',
        'clie_apellido' => 'required',
        // Activamos unique y max:8 (esto reemplaza el if manual de strlen)
        'clie_ci' => 'required|unique:clientes,clie_ci|max:15', // Puse 15 por seguridad, a veces los RUC son largos
        'clie_fecha_nac' => 'required|date',
        'id_departamento' => 'required|exists:departamentos,id_departamento',
        'id_ciudad' => 'required|exists:ciudades,id_ciudad',
    ], [
        'clie_ci.unique' => 'Ya existe un cliente registrado con esta Cédula/RUC.',
        'clie_ci.max' => 'La cédula no debe superar los caracteres permitidos.',
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
        return back()->withErrors($validacion)->withInput();
    }

    // Validaciones lógicas extras (Edad, Fechas)
    $fec_actual = Carbon::now();
    $fecha_nac = Carbon::parse($input['clie_fecha_nac']);

    if ($fec_actual->diffInYears($fecha_nac) < 18) {
        Alert::toast('El cliente debe ser mayor de 18 años.' , 'error');
        return back()->withInput();
    }

    if ($fecha_nac > $fec_actual) {
        Alert::toast('La fecha de nacimiento no puede ser futura.', 'error');
        return back()->withInput();
    }

    // --- PASO 3: INSERTAR (Usando $input que ya está limpio) ---
    DB::insert('INSERT INTO clientes (clie_nombre, clie_apellido, clie_ci, clie_telefono, clie_direccion, clie_fecha_nac, id_departamento, id_ciudad) VALUES (?, ?, ?, ?, ?, ?, ?, ?)', [
        $input['clie_nombre'],
        $input['clie_apellido'],
        $input['clie_ci'],       // Va sin puntos
        $input['clie_telefono'],
        $input['clie_direccion'], // Va en mayúsculas
        $input['clie_fecha_nac'], // Nota: corregí 'cli_fecha_nac' a 'clie_fecha_nac' según tu array
        $input['id_departamento'],
        $input['id_ciudad']
    ]);

    Alert::toast('Cliente creado correctamente.' , 'success');
    return redirect(route('clientes.index'));
}

    public function edit($id)
    {
        // Validar que exista el id cliente antes de procesar
        $clientes = DB::selectOne('SELECT * FROM clientes WHERE id_cliente = ?', [$id]);

        if (empty($clientes)) {
            Alert::toast('Cliente no encontrado.' , 'error');
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
    // 1. Verificar existencia
    $cliente = DB::selectOne('SELECT * FROM clientes WHERE id_cliente = ?', [$id]);

    if (empty($cliente)) {
        Alert::toast('Cliente no encontrado.', 'error');
        return redirect(route('clientes.index'));
    }

    $input = $request->all();

    // 2. SANITIZACIÓN (Limpiar datos ANTES de validar)
    
    // Quitar puntos de la cédula (ej: 4.500.000 -> 4500000)
    if(isset($input['clie_ci'])) {
        $input['clie_ci'] = str_replace('.', '', $input['clie_ci']);
    }

    // Convertir textos a Mayúsculas
    $input['clie_nombre'] = Str::upper(Str::ascii(trim($input['clie_nombre'])));
    $input['clie_apellido'] = Str::upper(Str::ascii(trim($input['clie_apellido'])));
    $input['clie_direccion'] = Str::upper(Str::ascii(trim($input['clie_direccion'])));

    // 3. VALIDACIÓN
    $validacion = Validator::make($input, [
        // Corrección de sintaxis: 'campo' => 'reglas'
        'clie_nombre' => 'required',
        'clie_apellido' => 'required',
        
        // Validación UNIQUE ignorando el ID actual para que no de error al guardar cambios propios
        // unique:tabla, columna, id_a_ignorar, nombre_columna_pk
        'clie_ci' => 'required|max:15|unique:clientes,clie_ci,'.$id.',id_cliente',
        
        'clie_fecha_nac' => 'required|date',
        'id_departamento' => 'required|exists:departamentos,id_departamento',
        'id_ciudad' => 'required|exists:ciudades,id_ciudad',
    ], [
        'clie_nombre.required' => 'Campo nombre obligatorio',
        'clie_apellido.required' => 'Campo apellido obligatorio',
        'clie_ci.required' => 'Campo CI obligatorio',
        'clie_ci.unique' => 'Ya existe otro cliente con este número de CI.',
        'clie_ci.max' => 'El CI no puede tener más de 15 dígitos.',
        'clie_fecha_nac.required' => 'Campo fecha de nacimiento obligatorio',
        'id_departamento.required' => 'Debe seleccionar un departamento',
        'id_ciudad.required' => 'Debe seleccionar una ciudad',
    ]);

    if ($validacion->fails()) {
        Alert::toast('Error en la validación de datos.', 'error');
        return back()->withErrors($validacion)->withInput();
    }

    // 4. LÓGICA DE NEGOCIO (Edad)
    $fec_actual = Carbon::now();
    $fecha_nac = Carbon::parse($input['clie_fecha_nac']);
    $edad = $fec_actual->diffInYears($fecha_nac);

    if ($edad < 18 || $fecha_nac > $fec_actual) {
        Alert::toast('El cliente debe ser mayor de 18 años y la fecha válida.', 'error');
        return back()->withInput();
    }

    // Nota: Ya no necesitamos validar strlen($ci) > 8 manualmente 
    // porque pusimos 'max:15' en el Validator arriba.

    // 5. ACTUALIZAR (Usando los datos limpios de $input)
    DB::update('UPDATE clientes SET 
        clie_nombre = ?, 
        clie_apellido = ?, 
        clie_ci = ?, 
        clie_telefono = ?, 
        clie_direccion = ?, 
        clie_fecha_nac = ?, 
        id_departamento = ?, 
        id_ciudad = ? 
    WHERE id_cliente = ?', 
    [
        $input['clie_nombre'],
        $input['clie_apellido'],
        $input['clie_ci'],        // Ya va sin puntos
        $input['clie_telefono'],
        $input['clie_direccion'], // Ya va en mayúsculas
        $input['clie_fecha_nac'],
        $input['id_departamento'],
        $input['id_ciudad'],
        $id
    ]);

    Alert::toast('Cliente actualizado correctamente.', 'success');
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