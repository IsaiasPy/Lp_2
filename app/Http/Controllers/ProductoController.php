<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class ProductoController extends Controller
{
    private $path;

    public function __construct()
    {
        $this->middleware('auth');
        $this->path = public_path() . "/img/productos/";
    }

    public function index(Request $request)
    {
        $buscar = $request->get('buscar');
        $sql = '';

        if (!empty($buscar)) {
            $sql = " WHERE p.descripcion iLIKE '%" . $buscar . "%' 
            or m.descripcion iLIKE '%" . $buscar . "%' 
            or cast(p.id_producto as text) iLIKE '%" . $buscar . "%'";
        }

        $productos = DB::select(
            'SELECT p.*, m.descripcion as marcas 
             FROM productos p
                JOIN marcas m ON p.id_marca = m.id_marca
             ' . $sql . '
             ORDER BY p.id_producto desc'
        );

        $page = $request->input('page', 1);
        $perPage = 10;
        $total = count($productos);
        $items = array_slice($productos, ($page - 1) * $perPage, $perPage);

        $productos = new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        if ($request->ajax()) {
            return view('productos.table')->with('productos', $productos);
        }

        return view('productos.index')->with('productos', $productos);
    }

    public function create()
    {
        $tipo_iva = array(
            '0' => 'Exento',
            '5' => 'Gravada 5%',
            '10' => 'Gravada 10%',
        );

        $marcas = DB::table('marcas')->pluck('descripcion', 'id_marca');

        return view('productos.create')->with('tipo_iva', $tipo_iva)->with('marcas', $marcas);
    }

    public function store(Request $request)
    {
        $input = $request->all();

        // 1. SANITIZACIÓN: Convertir a Mayúsculas
        if (isset($input['descripcion'])) {
            $input['descripcion'] = Str::upper(Str::ascii(trim($input['descripcion'])));
        }

        // 2. VALIDACIÓN
        $validacion = Validator::make($input, [
            // Agregamos 'unique' apuntando a la tabla productos y columna descripcion
            'descripcion' => 'required|unique:productos,descripcion',
            'precio' => 'required',
            'id_marca' => 'required|exists:marcas,id_marca',
            'tipo_iva' => 'required|numeric',
            'imagen_producto' => 'nullable|image|max:2048'
        ], [
            'descripcion.required' => 'La descripción es obligatoria.',
            'descripcion.unique' => 'Ya existe un producto con esta descripción.',
            'precio.required' => 'El precio es obligatorio.',
            'id_marca.required' => 'La marca es obligatoria.',
            'id_marca.exists' => 'La marca seleccionada no existe.',
            'tipo_iva.required' => 'El tipo de IVA es obligatorio.',
            'imagen_producto.image' => 'El archivo debe ser una imagen.',
            'imagen_producto.max' => 'La imagen no debe superar los 2MB.',
        ]);

        if ($validacion->fails()) {
            Alert::toast('Error en la validación de datos.', 'error');
            return redirect()->back()
                ->withErrors($validacion)
                ->withInput();
        }

        // Procesar imagen
        if ($request->hasFile('imagen_producto')) {
            $imagen = $request->file('imagen_producto')->getClientOriginalName();
            $request->file('imagen_producto')->move($this->path, $imagen);
        }
        $input['imagen_producto'] = isset($imagen) ? $imagen : null;

        // Limpiar precio (quitar puntos)
        $precio = str_replace('.', '', $input['precio']);

        // Insertar
        DB::insert(
            'INSERT INTO productos (descripcion, precio, id_marca, tipo_iva, imagen_producto) VALUES (?, ?, ?, ?, ?)',
            [
                $input['descripcion'], // Ya va en Mayúsculas
                $precio,
                $input['id_marca'],
                $input['tipo_iva'],
                $input['imagen_producto']
            ]
        );

        Alert::toast('Producto creado correctamente.', 'success');
        return redirect(route('productos.index'));
    }

    public function edit($id)
    {
        $productos = DB::selectOne('SELECT * FROM productos WHERE id_producto = ?', [$id]);

        if (empty($productos)) {
            Alert::toast('Producto no encontrado.', 'error');
            return redirect(route('productos.index'));
        }

        $tipo_iva = array(
            '0' => 'Exento',
            '5' => 'Gravada 5%',
            '10' => 'Gravada 10%',
        );

        $marcas = DB::table('marcas')->pluck('descripcion', 'id_marca');

        return view('productos.edit')->with('productos', $productos)->with('tipo_iva', $tipo_iva)->with('marcas', $marcas);
    }

    public function update(Request $request, $id)
    {
        // Verificar existencia
        $productos = DB::selectOne('SELECT * FROM productos WHERE id_producto = ?', [$id]);

        if (empty($productos)) {
            Alert::toast('Producto no encontrado.', 'error');
            return redirect(route('productos.index'));
        }

        $input = $request->all();

        // 1. SANITIZACIÓN: Convertir a Mayúsculas
        if (isset($input['descripcion'])) {
            $input['descripcion'] = Str::upper(Str::ascii(trim($input['descripcion'])));
        }

        // 2. VALIDACIÓN (Con ignore para el ID actual)
        $validacion = Validator::make($input, [
            // unique:tabla, columna, id_a_ignorar, columna_pk
            'descripcion' => 'required|unique:productos,descripcion,' . $id . ',id_producto',
            'precio' => 'required',
            'id_marca' => 'required|exists:marcas,id_marca',
            'tipo_iva' => 'required|numeric',
            'imagen_producto' => 'nullable|image|max:2048',
        ], [
            'descripcion.required' => 'La descripción es obligatoria.',
            'descripcion.unique' => 'Ya existe otro producto con esta descripción.',
            'precio.required' => 'El precio es obligatorio.',
            // ... resto de mensajes
        ]);

        if ($validacion->fails()) {
            Alert::toast('Error en la validación de datos.', 'error');
            return redirect()->back()
                ->withErrors($validacion)
                ->withInput();
        }

        // Procesar imagen (lógica original mantenida)
        if ($request->hasFile('imagen_producto')) {
            $imagen = $request->file('imagen_producto')->getClientOriginalName();
            $request->file('imagen_producto')->move($this->path, $imagen);
        }
        // Si no subió imagen nueva, mantenemos la anterior ($productos->imagen_producto)
        $input['imagen_producto'] = isset($imagen) ? $imagen : $productos->imagen_producto;

        // Limpiar precio
        $precio = str_replace('.', '', $input['precio']);

        // Actualizar
        DB::update(
            'UPDATE productos SET descripcion = ?, precio = ?, id_marca = ?, tipo_iva = ?, imagen_producto = ? WHERE id_producto = ?',
            [
                $input['descripcion'], // Mayúsculas
                $precio,
                $input['id_marca'],
                $input['tipo_iva'],
                $input['imagen_producto'],
                $id
            ]
        );

        Alert::toast('Producto actualizado correctamente.', 'success');
        return redirect(route('productos.index'));
    }

    public function destroy($id)
    {
        $producto = DB::selectOne('SELECT * FROM productos WHERE id_producto = ?', [$id]);

        if (empty($producto)) {
            Alert::toast('Producto no encontrado.', 'error');
            return redirect(route('productos.index'));
        }

        try {
            DB::delete('DELETE FROM productos WHERE id_producto = ?', [$id]);
            Alert::toast('Producto eliminado correctamente.', 'success');
        } catch (\Exception $e) {
            // Capturamos si falla por clave foránea (ej: si ya se vendió el producto)
            Alert::toast('No se puede eliminar: el producto está en uso.', 'error');
        }

        return redirect(route('productos.index'));
    }
}
