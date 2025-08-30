<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Laracasts\Flash\Flash;

class ProductoController extends Controller
{
    public function index()
    {
        $productos = DB::select(
            'SELECT p.*, m.descripcion as marcas 
             FROM productos p
                JOIN marcas m ON p.id_marca = m.id_marca
            ORDER BY p.id_producto desc'
        );

        return view('productos.index')->with('productos', $productos);
    }

    public function create()
    {
        // Cargar los tipos de IVA para el select
        $tipo_iva = array(
            '0' => 'Exento',
            '5' => 'Gravada 5%',
            '10' => 'Gravada 10%',
        );

        // Obtener las marcas para cargar el select
        $marcas = DB::table('marcas')->pluck('descripcion', 'id_marca');

        return view('productos.create')->with('tipo_iva', $tipo_iva)->with('marcas', $marcas);
    }

    public function store(Request $request)
    {
        $input = $request->all();

        // Validar los datos del formulario
        $validacion = Validator::make($input, [
            'descripcion' => 'required',
            'precio' => 'required',
            'id_marca' => 'required|exists:marcas,id_marca',
            'tipo_iva' => 'required|numeric',
        ], [
            'descripcion.required' => 'La descripción es obligatoria.',
            'precio.required' => 'El precio es obligatorio.',
            'id_marca.required' => 'La marca es obligatoria.',
            'id_marca.exists' => 'La marca seleccionada no existe.',
            'tipo_iva.required' => 'El tipo de IVA es obligatorio.',
            'tipo_iva.numeric' => 'El tipo de IVA debe ser un número válido.',
        ]);

        // Si la validación falla, redirigir con errores
        if ($validacion->fails()) {
            return redirect()->back()
                ->withErrors($validacion)
                ->withInput();
        }

        // Sacar separador de miles y cambiar  por vacio en el precio
        $precio = str_replace('.', '', $input['precio']);

        // Insertar el nuevo producto en la base de datos
        DB::insert(
            'INSERT INTO productos (descripcion, precio, id_marca, tipo_iva) VALUES (?, ?, ?, ?)',
            [
                $input['descripcion'],
                $precio,
                $input['id_marca'],
                $input['tipo_iva']
            ]
        );

        // Redirigir a la lista de productos con un mensaje de éxito
        Flash::success('Producto creado correctamente.');

        return redirect(route('productos.index'));
    }

    public function edit($id)
    {
        $productos = DB::selectOne('SELECT * FROM productos WHERE id_producto = ?', [$id]);

        // Si el producto no existe, redirigir con un mensaje de error
        if (empty($productos)) {
            Flash::error('Producto no encontrado.');
            return redirect(route('productos.index'));
        }

        // Cargar los tipos de IVA para el select
        $tipo_iva = array(
            '0' => 'Exento',
            '5' => 'Gravada 5%',
            '10' => 'Gravada 10%',
        );

        // Obtener las marcas para cargar el select
        $marcas = DB::table('marcas')->pluck('descripcion', 'id_marca');

        return view('productos.edit')->with('productos', $productos)->with('tipo_iva', $tipo_iva)->with('marcas', $marcas);
    }

    public function update(Request $request, $id) 
    {
        $input = $request->all();

        // Obtener el producto de la base de datos 1 solo valor utilizando selectOne
        $productos = DB::selectOne('SELECT * FROM productos WHERE id_producto = ?', [$id]);

        // Si el producto no existe, redirigir con un mensaje de error
        if (empty($productos)) {
            Flash::error('Producto no encontrado.');
            return redirect(route('productos.index'));
        }

        // Validar los datos del formulario
        $validacion = Validator::make($input, [
            'descripcion' => 'required',
            'precio' => 'required',
            'id_marca' => 'required|exists:marcas,id_marca',
            'tipo_iva' => 'required|numeric',
        ], [
            'descripcion.required' => 'La descripción es obligatoria.',
            'precio.required' => 'El precio es obligatorio.',
            'id_marca.required' => 'La marca es obligatoria.',
            'id_marca.exists' => 'La marca seleccionada no existe.',
            'tipo_iva.required' => 'El tipo de IVA es obligatorio.',
            'tipo_iva.numeric' => 'El tipo de IVA debe ser un número válido.',
        ]);

        // Si la validación falla, redirigir con errores
        if ($validacion->fails()) {
            return redirect()->back()
                ->withErrors($validacion)
                ->withInput();
        }

        // Sacar separador de miles y cambiar  por vacio en el precio
        $precio = str_replace('.', '', $input['precio']);

        // Actualizar el producto en la base de datos
        DB::update(
            'UPDATE productos SET descripcion = ?, precio = ?, id_marca = ?, tipo_iva = ? WHERE id_producto = ?',
            [
                $input['descripcion'],
                $precio,
                $input['id_marca'],
                $input['tipo_iva'],
                $id
            ]
        );

        // Redirigir a la lista de productos con un mensaje de éxito
        Flash::success('Producto actualizado correctamente.');

        return redirect(route('productos.index'));
    }

    public function destroy($id) 
    {
        // Verificar si el producto existe
        $producto = DB::selectOne('SELECT * FROM productos WHERE id_producto = ?', [$id]);

        if (empty($producto)) {
            Flash::error('Producto no encontrado.');
            return redirect(route('productos.index'));
        }

        // Eliminar el producto de la base de datos
        DB::delete('DELETE FROM productos WHERE id_producto = ?', [$id]);

        // Redirigir a la lista de productos con un mensaje de éxito
        Flash::success('Producto eliminado correctamente.');

        return redirect(route('productos.index'));
    }
}
