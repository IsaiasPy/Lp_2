<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use RealRashid\SweetAlert\Facades\Alert; // Importamos Alert

class StockController extends Controller
{
    // Constructor con middlewares
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:stocks index')->only(['index']);
        $this->middleware('permission:stocks create')->only(['create', 'store']);
        $this->middleware('permission:stocks edit')->only(['edit', 'update']);
        $this->middleware('permission:stocks destroy')->only(['destroy']);
    }
    
    public function index(Request $request)
    {
        // 1. OBTENER TODOS LOS FILTROS
        $buscar = $request->get('buscar');
        $filtro_sucursal = $request->get('id_sucursal'); // Filtro nuevo
        $filtro_producto = $request->get('id_producto'); // Filtro nuevo

        // 2. CONSTRUIR CONSULTA SQL BASE
        $baseQuery = 'SELECT s.*, p.descripcion as producto, suc.descripcion as sucursal 
                      FROM stocks s 
                      JOIN productos p ON s.id_producto = p.id_producto 
                      JOIN sucursales suc on s.id_sucursal = suc.id_sucursal';
        
        $whereClauses = [];
        $bindings = [];

        // 3. AÑADIR FILTROS DINÁMICAMENTE
        if ($buscar) {
            $whereClauses[] = "(
            p.descripcion ILIKE ? 
            OR suc.descripcion ILIKE ? 
            OR s.cantidad::text ILIKE ?
            )";
            $bindings[] = '%' . $buscar . '%';
            $bindings[] = '%' . $buscar . '%';
            $bindings[] = '%' . $buscar . '%';
        }

        if ($filtro_sucursal) { // Filtro nuevo
            $whereClauses[] = "s.id_sucursal = ?";
            $bindings[] = $filtro_sucursal;
        }

        if ($filtro_producto) { // Filtro nuevo
            $whereClauses[] = "s.id_producto = ?";
            $bindings[] = $filtro_producto;
        }

        // 4. COMBINAR LA CONSULTA
        $sql = $baseQuery;
        if (!empty($whereClauses)) {
            $sql .= " WHERE " . implode(" AND ", $whereClauses);
        }
        $sql .= " ORDER BY s.id_stock DESC";

        // 5. EJECUTAR CONSULTA
        $stocks = DB::select($sql, $bindings);

        // 6. PAGINACIÓN MANUAL (Se respeta tu lógica)
        $page = $request->input('page', 1);
        $perPage = 10;
        $total = count($stocks);
        $items = array_slice($stocks, ($page - 1) * $perPage, $perPage);

        $stocks = new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            [
                'path'  => $request->url(),
                'query' => $request->query(), // Mantiene todos los filtros (buscar, id_sucursal, etc.)
            ]
        );

        // 7. DATOS PARA LOS <select> DE FILTROS (¡ESTO CORRIGE EL ERROR 500!)
        $sucursales = DB::table('sucursales')->orderBy('descripcion')->pluck('descripcion', 'id_sucursal');
        $productos = DB::table('productos')->orderBy('descripcion')->pluck('descripcion', 'id_producto');

        // 8. MANEJO DE AJAX (Para el buscador en tiempo real)
        if ($request->ajax()) {
            // Si es AJAX, solo devolvemos la tabla
            return view('stocks.table', compact('stocks'))->render();
        }

        // 9. RETORNAR VISTA (Pasando las nuevas variables)
        return view('stocks.index', compact('stocks', 'sucursales', 'productos'));
    }

    /**
     * Muestra el formulario para crear un nuevo registro de stock (Ajuste).
     */
    public function create()
    {
        $sucursales = DB::table('sucursales')->orderBy('descripcion')->pluck('descripcion', 'id_sucursal');
        $productos = DB::table('productos')->orderBy('descripcion')->pluck('descripcion', 'id_producto');
        
        return view('stocks.create', compact('sucursales', 'productos'));
    }

    /**
     * Guarda un nuevo registro de stock (Ajuste manual).
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_producto' => 'required|exists:productos,id_producto',
            'id_sucursal' => 'required|exists:sucursales,id_sucursal',
            'cantidad' => 'required|integer|min:0',
        ]);

        DB::table('stocks')->updateOrInsert(
            [ // Condiciones para buscar
                'id_producto' => $request->id_producto,
                'id_sucursal' => $request->id_sucursal,
            ],
            [ // Datos para actualizar o insertar
                'cantidad' => $request->cantidad,
            ]
        );

        Alert::success('Éxito', 'Stock actualizado correctamente.');
        return redirect()->route('stocks.index');
    }

    /**
     * Muestra el formulario para editar un registro de stock.
     */
    public function edit($id)
    {
        $stock = DB::table('stocks as s')
            ->join('productos as p', 's.id_producto', '=', 'p.id_producto')
            ->join('sucursales as suc', 's.id_sucursal', '=', 'suc.id_sucursal')
            ->select('s.*', 'p.descripcion as producto', 'suc.descripcion as sucursal')
            ->where('s.id_stock', $id)
            ->first();

        if (!$stock) {
            Alert::error('Error', 'Registro de stock no encontrado.');
            return redirect()->route('stocks.index');
        }
        
        return view('stocks.edit', compact('stock'));
    }

    /**
     * Actualiza un registro de stock.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'cantidad' => 'required|integer|min:0',
        ]);
        
        DB::table('stocks')->where('id_stock', $id)->update([
            'cantidad' => $request->cantidad
        ]);

        Alert::success('Éxito', 'Stock actualizado correctamente.');
        return redirect()->route('stocks.index');
    }

    /**
     * Elimina un registro de stock.
     */
    public function destroy($id)
    {
        try {
            DB::table('stocks')->where('id_stock', $id)->delete();
            Alert::success('Éxito', 'Registro de stock eliminado.');
        } catch (\Exception $e) {
            Alert::error('Error', 'No se pudo eliminar el registro, puede estar en uso.');
        }
        
        return redirect()->route('stocks.index');
    }
}