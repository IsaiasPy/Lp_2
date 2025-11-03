<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use RealRashid\SweetAlert\Facades\Alert;

class CuentasACobrarController extends Controller
{
    // Definir una variable para el path
    private $path;
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:cuentasacobrar index')->only(['index']);
    }
    public function index(Request $request)
    {
        // Obtener el término de búsqueda del request
        $buscar = $request->get('buscar');
        // validar que contenga un valor el buscar
        $sql = '';// definir una variable sql vacia

        if (!empty($buscar)) {
            $sql = "WHERE (CONCAT(clie_nombre,' ',clie_apellido) iLIKE '%" . $buscar . "%'
            or factura_nro iLIKE '%" . $buscar . "%' 
            or to_char(importe, 'FM999999990.00') iLIKE '%" . $buscar . "%' 
            or to_char(vencimiento, 'YYYY-MM-DD') iLIKE '%" . $buscar . "%' 
            or ca.estado iLIKE '%" . $buscar . "%')"; // si tiene valor agregar la condicion a la variable sql
        }
        
        // Consulta para obtener los productos con la marca asociada y si posee filtros
        $cuentasacobrar = DB::select(
            'SELECT id_cuenta, CONCAT(clie_nombre,\' \',clie_apellido) AS cliente, factura_nro, fecha_venta, 
            importe, ca.estado, vencimiento, nro_cuota AS nro_cuotas
            FROM cuentas_a_cobrar ca
            JOIN clientes USING(id_cliente)
            JOIN ventas USING(id_venta)
            '.$sql.'
             ORDER BY vencimiento desc'
        );

        // Definimos los valores de paginación
        $page = $request->input('page', 1);   // página actual (por defecto 1)
        $perPage = 10;                   // cantidad de registros por página
        $total = count($cuentasacobrar);           // total de registros

        // Cortamos el array para solo devolver los registros de la página actual
        $items = array_slice($cuentasacobrar, ($page - 1) * $perPage, $perPage);

        // Creamos el paginador manualmente
        $cuentasacobrar = new LengthAwarePaginator(
            $items,        // registros de esta página
            $total,        // total de registros
            $perPage,      // registros por página
            $page,         // página actual
            [
                'path'  => $request->url(),     // mantiene la ruta base
                'query' => $request->query(),   // mantiene parámetros como "buscar"
            ]
        );

         // Verificamos si la petición es AJAX
        if ($request->ajax()) { //devuelve true o false si es ajax o no
            //solo llmamamos a table.blade.php y mediante compact pasamos la variable users
            return view('cuentasacobrar.table')->with('cuentasacobrar', $cuentasacobrar);
        }


        return view('cuentasacobrar.index')->with('cuentasacobrar', $cuentasacobrar);
    }

}