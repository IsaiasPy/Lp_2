<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CuentasAPagarController extends Controller
{
    public function index(Request $request)
    {
        $buscar = $request->get('buscar');
        
        $sql_filter = '';
        
        // 1. LÓGICA DE FILTRADO
        if (!empty($buscar)) {
            // Usamos AND porque la consulta base ya tiene WHERE ca.estado = 'PENDIENTE'
            $sql_filter = "AND (CONCAT(p.descripcion) iLIKE '%" . $buscar . "%' 
            OR ca.id_cta::text iLIKE '%" . $buscar . "%'
            OR c.factura iLIKE '%" . $buscar . "%' 
            OR to_char(ca.importe, 'FM999999990.00') iLIKE '%" . $buscar . "%' 
            OR to_char(ca.vencimiento, 'DD/MM/YYYY') iLIKE '%" . $buscar . "%' 
            OR ca.estado iLIKE '%" . $buscar . "%')";
        }
        
        // 2. CONSULTA SQL (Incluyendo ca.saldo y usando el filtro)
        $cuentasapagar = DB::select(
            "SELECT ca.id_cta, CONCAT(p.descripcion) AS proveedor, c.factura, c.fecha_compra, 
            ca.importe, ca.saldo, ca.vencimiento, ca.nro_cuenta, ca.estado
            FROM cuentas_a_pagar ca
            JOIN proveedores p ON p.id_proveedor = ca.id_proveedor
            JOIN compras c ON c.id_compra = ca.id_compra
            WHERE ca.estado = 'PENDIENTE' 
            {$sql_filter}
            ORDER BY ca.vencimiento ASC"
        );

        // 3. PAGINACIÓN
        $page = $request->input('page', 1);   
        $perPage = 10;                   
        $total = count($cuentasapagar);           
        $items = array_slice($cuentasapagar, ($page - 1) * $perPage, $perPage);

        $cuentasapagar = new LengthAwarePaginator(
            $items,        
            $total,        
            $perPage,      
            $page,         
            [
                'path'  => $request->url(),     
                'query' => $request->query(),   
            ]
        );

         // Verificamos si la petición es AJAX
        if ($request->ajax()) { 
            return view('cuentasapagar.table')->with('cuentasapagar', $cuentasapagar);
        }

        // 4. RETORNO DE VISTA
        return view('cuentasapagar.index')->with('cuentasapagar', $cuentasapagar);
    }
}