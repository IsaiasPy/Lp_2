<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    public function rpt_cargos(Request $request)
    {
        // recibir datos del formulario a partir de la url get
        $input = $request->all();

        // verificar si los filtros no estan vacios para realizar el where en la consulta cargos
        if (!empty($input['desde']) and !empty($input['hasta'])) {
            $cargos = DB::select('SELECT * FROM cargos WHERE id_cargo 
            BETWEEN ' . $input['desde'] . ' AND ' . $input['hasta']);
        } else {
            // si no se reciben datos, se traen todos los registros (SELECT * FROM cargos)
            $cargos = DB::select('SELECT * FROM cargos');
        }

        if (isset($input['exportar']) && $input['exportar'] == 'pdf') {
            ##crear vista pdf con loadView y utilizar la misma vista reportes rpt_cargos para convertir en pdf
            $pdf = Pdf::loadView(
                'reportes.pdf_cargos',
                compact('cargos')
            )
                ->setPaper('a4', 'portrait'); ## especificar tamaño de hoja y disposición
            # de hoja landscape=horizontal, portrait=vertical

            ##retornar pdf con una configuracion de pagina tipo de impresion y que se hara una descarga
            return $pdf->download("ReporteCargos.pdf");
        }


        return view('reportes.rpt_cargos')->with('cargos', $cargos);
    }

    public function rpt_clientes(Request $request)
    {
        // recibir datos del formulario a partir de la url get
        $input = $request->all();

        // definir variables para filtros
        $filtro_ciudad = "";
        $filtro_desde = "";
        $filtro_hasta = "";

        if (!empty($input['ciudad'])) {
            // concatenar los filtros y siempre dejar un espacio en blanco al comienzo del string " "
            $filtro_ciudad = " AND c.id_ciudad = " . $input['ciudad'];
        }

        // filtros desde y concatena el sql and
        if (!empty($input['desde'])) {
            $filtro_desde = " AND c.id_cliente >= " . $input['desde'];
        }

        // filtros hasta y concatena el sql correspondientes
        if (!empty($input['hasta'])) {
            $filtro_hasta = " AND c.id_cliente <= " . $input['hasta'];
        }

        // concatenar todos los filtros en el where si llega a recibir algun valor, por defecto esta where 1=1 que siempre sera true es para evitar fallo en la consulta normal
        $clientes = DB::select('SELECT c.*, ciu.descripcion as ciudad, extract(year from age(now(), c.cli_fecha_nac))as edad
            FROM clientes c 
            LEFT JOIN ciudades ciu ON c.id_ciudad = ciu.id_ciudad
            WHERE 1=1 ' . $filtro_ciudad . ' ' . $filtro_desde . ' ' . $filtro_hasta . '
            ORDER BY c.id_cliente');

        // validar que exista la variable input['exportar']
        if (isset($input['exportar']) && $input['exportar'] == 'pdf') {
            // crear pdf con loadView y utilizar la misma vista reportes rpt_clientes para convertir en pdf
            $pdf = Pdf::loadView(
                'reportes.pdf_clientes',
                compact('clientes')
            )
                ->setPaper('a4', 'landscape'); ## especificar tamaño de hoja y disposición
            # de hoja landscape=horizontal, portrait=vertical

            // retornar la descarga del archivo
            return $pdf->download("ReporteClientes.pdf");
        }

        // consulta para llenar el select de ciudad en reporte cliente
        $ciudad_select = DB::table('ciudades')->pluck('descripcion', 'id_ciudad');

        return view('reportes.rpt_clientes')->with('clientes', $clientes)->with('ciudades', $ciudad_select);
    }

    public function rpt_proveedores(Request $request)
    {
        // recibir datos del formulario a partir de la url get
        $input = $request->all();

        // verificar si los filtros no estan vacios para realizar el where en la consulta proveedores
        if (!empty($input['desde']) and !empty($input['hasta'])) {
            $proveedores = DB::select('SELECT * FROM proveedores WHERE id_proveedor 
        BETWEEN ' . $input['desde'] . ' AND ' . $input['hasta']);
        } else {
            // si no se reciben datos, se traen todos los registros (SELECT * FROM proveedores)
            $proveedores = DB::select('SELECT * FROM proveedores');
        }

        if (isset($input['exportar']) && $input['exportar'] == 'pdf') {
            ##crear vista pdf con loadView y utilizar la misma vista reportes rpt_proveedores para convertir en pdf
            $pdf = Pdf::loadView(
                'reportes.pdf_proveedores',
                compact('proveedores')
            )
                ->setPaper('a4', 'portrait'); ## especificar tamaño de hoja y disposición
            # de hoja landscape=horizontal, portrait=vertical

            ##retornar pdf con una configuracion de pagina tipo de impresion y que se hara una descarga
            return $pdf->download("ReporteProveedores.pdf");
        }

        return view('reportes.rpt_proveedores')->with('proveedores', $proveedores);
    }

    public function rpt_productos(Request $request)
    {
        // recibir datos del formulario a partir de la url get
        $input = $request->all();

        // construir la consulta con join para mostrar la descripción de la marca
        $baseQuery = 'SELECT p.id_producto, p.descripcion, p.precio, p.tipo_iva, m.descripcion AS marca 
                 FROM productos p 
                 LEFT JOIN marcas m ON p.id_marca = m.id_marca';

        // verificar si los filtros no estan vacios para realizar el where
        if (!empty($input['desde']) and !empty($input['hasta'])) {
            $productos = DB::select($baseQuery . ' WHERE p.id_producto BETWEEN ' . $input['desde'] . ' AND ' . $input['hasta']);
        } else {
            // si no se reciben datos, se traen todos los registros
            $productos = DB::select($baseQuery);
        }

        if (isset($input['exportar']) && $input['exportar'] == 'pdf') {
            $pdf = Pdf::loadView(
                'reportes.pdf_productos',
                compact('productos')
            )
                ->setPaper('a4', 'portrait');

            return $pdf->download("ReporteProductos.pdf");
        }

        return view('reportes.rpt_productos')->with('productos', $productos);
    }
    public function rpt_sucursales(Request $request)
    {
        // recibir datos del formulario a partir de la url get
        $input = $request->all();

        // construir la consulta con join para mostrar la descripción de la ciudad
        $baseQuery = 'SELECT s.id_sucursal, s.descripcion, s.direccion, s.telefono, c.descripcion AS ciudad 
                 FROM sucursales s 
                 INNER JOIN ciudades c ON s.id_ciudad = c.id_ciudad';

        // verificar si los filtros no estan vacios para realizar el where
        if (!empty($input['desde']) and !empty($input['hasta'])) {
            $sucursales = DB::select($baseQuery . ' WHERE s.id_sucursal BETWEEN ' . $input['desde'] . ' AND ' . $input['hasta']);
        } else {
            // si no se reciben datos, se traen todos los registros
            $sucursales = DB::select($baseQuery);
        }

        if (isset($input['exportar']) && $input['exportar'] == 'pdf') {
            $pdf = Pdf::loadView(
                'reportes.pdf_sucursales',
                compact('sucursales')
            )
                ->setPaper('a4', 'portrait');

            return $pdf->download("ReporteSucursales.pdf");
        }

        return view('reportes.rpt_sucursales')->with('sucursales', $sucursales);
    }
}