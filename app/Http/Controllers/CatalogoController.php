<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| US-0007 — CATÁLOGO DE PRODUCTOS (CLIENTE)
|--------------------------------------------------------------------------
*/

class CatalogoController extends Controller
{
    /** Lista productos activos con búsqueda en tiempo real */
    public function index(Request $request)
    {
        $query = Producto::where('estado', 'activo');

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('tipo_huevo', 'like', "%{$buscar}%")
                  ->orWhere('presentacion', 'like', "%{$buscar}%")
                  ->orWhere('descripcion', 'like', "%{$buscar}%");
            });
        }

        $productos = $query->orderBy('nombre')->get();

        return view('cliente.catalogo.index', compact('productos'));
    }

    /** Detalle de un producto */
    public function show(Producto $producto)
    {
        // Solo productos activos son accesibles
        if ($producto->estado !== 'activo') {
            abort(404);
        }

        return view('cliente.catalogo.show', compact('producto'));
    }
}
