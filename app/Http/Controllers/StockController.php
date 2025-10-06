<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    public function index()
    {
        $productos = DB::select('SELECT * FROM productos');
        
        return view('stocks.index')->with('productos', $productos);
    }
    public function create()
    {
        return view('stocks.create');
    }
}
