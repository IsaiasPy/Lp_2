<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Laracasts\Flash\Flash;

class ProductoController extends Controller
{
    private $path;
    public function __construct()
    {
        $this->middleware('auth');
        $this->path = public_path() . '/img/productos/';
    }
    public function index(Request $request)
    {
        $buscar = $request->get('buscar');
        if(empty($buscar)){
            $sql = 'WHERE p. descripcion LIKE :buscar or m.descripcion LIKE :buscar or p.id_producto LIKE :buscar';
        }
        $productos = DB::select(
            'SELECT p.*, m.descripcion as marcas 
             FROM productos p
                JOIN marcas m ON p.id_marca = m.id_marca
                ' . $sql . '
            ORDER BY p.id_producto desc',
            [
                'buscar' => '%' . $buscar . '%'
            ]
        );
        if ($request -> ajax()) {
            
            return view('productos.index')->with('productos', $productos);
        }

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
            'imagen_producto' => 'nullable|image|max:2048' // Validación para la imagen (opcional)
        ], [
            'descripcion.required' => 'La descripción es obligatoria.',
            'precio.required' => 'El precio es obligatorio.',
            'id_marca.required' => 'La marca es obligatoria.',
            'id_marca.exists' => 'La marca seleccionada no existe.',
            'tipo_iva.required' => 'El tipo de IVA es obligatorio.',
            'tipo_iva.numeric' => 'El tipo de IVA debe ser un número válido.',
            'imagen_producto.image' => 'El archivo debe ser una imagen.',
            'imagen_producto.max' => 'La imagen no debe superar los 2MB.',
        ]);

        // Si la validación falla, redirigir con errores
        if ($validacion->fails()) {
            return redirect()->back()
                ->withErrors($validacion)
                ->withInput();
        }

        // Subir la imagen del producto
        if ($request->hasFile('imagen_producto')) {
            $imagen = $request->file('imagen_producto')->getClientOriginalName();

            $request->file('imagen_producto')->move($this->path, $imagen);
        }

        $input['imagen_producto'] = isset($imagen) ? $imagen : null;

        // Sacar separador de miles y cambiar  por vacio en el precio
        $precio = str_replace('.', '', $input['precio']);

        // Insertar el nuevo producto en la base de datos
        DB::insert(
            'INSERT INTO productos (descripcion, precio, id_marca, tipo_iva, imagen_producto) VALUES (?, ?, ?, ?, ?)',
            [
                $input['descripcion'],
                $precio,
                $input['id_marca'],
                $input['tipo_iva'],
                $input['imagen_producto']
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
            'imagen_producto' => 'nullable|image|max:2048', // Validación para la imagen (opcional)
        ], [
            'descripcion.required' => 'La descripción es obligatoria.',
            'precio.required' => 'El precio es obligatorio.',
            'id_marca.required' => 'La marca es obligatoria.',
            'id_marca.exists' => 'La marca seleccionada no existe.',
            'tipo_iva.required' => 'El tipo de IVA es obligatorio.',
            'tipo_iva.numeric' => 'El tipo de IVA debe ser un número válido.',
            'imagen_producto.image' => 'El archivo debe ser una imagen.',
            'imagen_producto.max' => 'La imagen no debe superar los 2MB.',
        ]);

        // Si la validación falla, redirigir con errores
        if ($validacion->fails()) {
            return redirect()->back()
                ->withErrors($validacion)
                ->withInput();
        }

        // Subir la imagen del producto
        if ($request->hasFile('imagen_producto')) {
            $imagen = $request->file('imagen_producto')->getClientOriginalName();

            $request->file('imagen_producto')->move($this->path, $imagen);
        }

        $input['imagen_producto'] = isset($imagen) ? $imagen : $productos->imagen_producto;

        // Sacar separador de miles y cambiar  por vacio en el precio
        $precio = str_replace('.', '', $input['precio']);

        // Actualizar el producto en la base de datos
        DB::update(
            'UPDATE productos SET descripcion = ?, precio = ?, id_marca = ?, tipo_iva = ?, imagen_producto = ? WHERE id_producto = ?',
            [
                $input['descripcion'],
                $precio,
                $input['id_marca'],
                $input['tipo_iva'],
                $input['imagen_producto'],
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