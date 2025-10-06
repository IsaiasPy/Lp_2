<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    public function index()
    {
        $stocks = DB::select('SELECT s.*, p.descripcion as producto, suc.descripcion as sucursal 
             FROM stocks s
                JOIN productos p ON s.id_producto = p.id_producto
                Join sucursales suc on s.id_sucursal = suc.id_sucursal
             ORDER BY s.id_producto desc');
        
        return view('stocks.index')->with('stocks', $stocks);
    }
    public function create()
    {
        return view('stocks.create');
    }
}
