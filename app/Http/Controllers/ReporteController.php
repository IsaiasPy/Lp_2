<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    public function rpt_cargos(Request $request)
    {
        //recibir datos del formulario
        $input = $request->all();

        if (!empty($input['desde']) && !empty($input['hasta'])) {

            $cargos = DB::select('SELECT * FROM cargos WHERE id_cargo BETWEEN ' . $input['desde'] . ' AND ' . $input['hasta']);
        } else {
            $cargos = DB::select('SELECT * FROM cargos');
        }

        if (isset($input['exportar']) && $input['exportar'] == 'pdf') {
            $pdf = Pdf::loadView(
                'reportes.pdf_cargos',
                compact('cargos')
            )
                ->setPaper('a4', 'portrait'); //se especifica el tamaño de hoja 
            //y dispoisicion de la hoja (landscape= horizontal o portrait= vertical)

            return $pdf->download('reporte_cargos.pdf');
        }


        return view('reportes.rpt_cargos')->with('cargos', $cargos);
    }
    public function rpt_clientes(Request $request)
    {
        $input = $request->all();
    
        // definir variables para filtros
        $filtro_ciudad = "";
        $filtro_desde = "";
        $filtro_hasta = "";

        if (!empty($input['ciudad'])) {
            $filtro_ciudad = " AND c.id_ciudad = " . $input['ciudad'];
        }

        if (!empty($input['desde']) && !empty($input['hasta'])) {
            $filtro_desde = " AND c.cli_fecha_nac >= '" . $input['desde'];
        }
        if (!empty($input['hasta'])) {
            $filtro_hasta = " AND c.id_cliente <= ". $input['hasta'];
        }

        //concatenar todos los filtros en el where si llega a recibir algun valor por defecto esta where 1=1 que siempre sera verdadero
        $clientes = DB::select('SELECT c.*, ciu.descripcion as ciudad, extract(year from age(now(), c.cli_fecha_nac)) as edad
	FROM clientes c 
        LEFT JOIN ciudades ciu ON c.id_ciudad = ciu.id_ciudad
        WHERE 1 = 1 ' . $filtro_ciudad . $filtro_desde . $filtro_hasta . '
        ORDER BY c.id_cliente');

        if (isset($input['exportar']) && $input['exportar'] == 'pdf') {
            $pdf = Pdf::loadView(
                'reportes.pdf_clientes',
                compact('clientes')
            )
                ->setPaper('a4', 'landscape'); //se especifica el tamanho de hoja 
            //y dispoisicion de la hoja (landscape= horizontal o portrait= vertical)

            //retornar la descarga del archivo
            return $pdf->download('reporte_clientes.pdf');
        }
        //consulta para llenar el select de ciudad en reporte cliente
        $ciudad_select = DB::table('ciudades')->pluck('descripcion', 'id_ciudad');

        return view('reportes.rpt_clientes')->with('clientes', $clientes)->with('ciudades', $ciudad_select);
    }
}
