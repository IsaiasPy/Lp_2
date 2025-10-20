<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    // Es recomendable añadir el constructor con los middlewares aquí también
    public function __construct()
    {
        $this->middleware('auth');
        // Aquí irían tus middlewares de permisos para 'stocks'
    }
    
    public function index(Request $request)
    {
        $buscar = $request->get('buscar');

        if ($buscar) {
            // CORRECCIÓN: Se especifica en qué tabla buscar (p.descripcion O suc.descripcion)
            $stocks = DB::select(
                'SELECT s.*, p.descripcion as producto, suc.descripcion as sucursal FROM stocks s 
                 JOIN productos p ON s.id_producto = p.id_producto
                 JOIN sucursales suc on s.id_sucursal = suc.id_sucursal
                 WHERE p.descripcion ILIKE ?
                 ORDER BY s.id_stock DESC',
                // Se pasa el valor para cada '?' en la consulta
                ['%' . $buscar . '%']
            );
        } else {
            $stocks = DB::select(
                'SELECT s.*, p.descripcion as producto, suc.descripcion as sucursal FROM stocks s
                 JOIN productos p ON s.id_producto = p.id_producto
                 JOIN sucursales suc on s.id_sucursal = suc.id_sucursal
                 ORDER BY s.id_stock DESC'
            );
        }

        // El resto del código es idéntico al tuyo, ya que la paginación manual estaba bien implementada.
        
        //Definimos los valores de paginación
        $page = $request->input('page', 1);    // página actual (por defecto 1)
        $perPage = 10;                         // cantidad de registros por página
        $total = count($stocks);               // total de registros

        // Cortamos el array para solo devolver los registros de la página actual
        $items = array_slice($stocks, ($page - 1) * $perPage, $perPage);

        // Creamos el paginador manualmente
        $stocks = new LengthAwarePaginator(
            $items,         // registros de esta página
            $total,         // total de registros
            $perPage,       // registros por página
            $page,          // página actual
            [
                'path'  => $request->url(),     // mantiene la ruta base
                'query' => $request->query(),   // mantiene parámetros como "buscar"
            ]
        );

        // si la accion es buscador entonces significa que se debe recargar mediante ajax la tabla
        if ($request->ajax()) {
            //solo llamamos a table.blade.php y mediante compact pasamos la variable
            return view('stocks.table')->with('stocks', $stocks);
        }
        return view('stocks.index')->with('stocks', $stocks);
    }

    public function create()
    {
        return view('stocks.create');
    }
}