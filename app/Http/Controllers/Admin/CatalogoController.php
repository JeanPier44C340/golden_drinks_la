<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CatalogoController extends Controller
{
    public function productos(): View
    {
        $productos = DB::table('productos as p')
            ->join('categorias_producto as c', 'c.id', '=', 'p.categoria_id')
            ->orderBy('p.nombre')
            ->get([
                'p.id',
                'p.codigo',
                'p.nombre',
                'p.unidad_medida',
                'p.precio_compra',
                'p.precio_distribucion',
                'p.stock_minimo',
                'p.activo',
                'c.nombre as categoria',
            ]);

        return view('admin.catalogos.productos', compact('productos'));
    }

    public function proveedores(): View
    {
        $proveedores = DB::table('proveedores')->orderBy('nombre')->get();

        return view('admin.catalogos.proveedores', compact('proveedores'));
    }

    public function vehiculos(): View
    {
        $vehiculos = DB::table('vehiculos')->orderBy('placa')->get();

        return view('admin.catalogos.vehiculos', compact('vehiculos'));
    }
}
