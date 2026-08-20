<?php

namespace App\Http\Controllers;

use App\Models\MovimientoInventario;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| US-0004 — GESTIÓN DE PRODUCTOS (ADMINISTRADOR)
| US-0005 escenario 6 — Registro automático inventario inicial
|--------------------------------------------------------------------------
*/

class ProductoController extends Controller
{
    public function index()
    {
        $productos = Producto::orderBy('nombre')->get();
        return view('admin.productos.index', compact('productos'));
    }

    public function create()
    {
        return view('admin.productos.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'       => 'required|string|max:100',
            'tipo_huevo'   => 'nullable|string|max:50',
            'presentacion' => 'nullable|string|max:50',
            'descripcion'  => 'nullable|string',
            'precio'       => 'required|numeric|min:0',
            'cantidad'     => 'required|integer|min:0',
            'imagen'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'estado'       => 'required|in:activo,inactivo',
        ], [
            'nombre.required' => 'El nombre del producto es obligatorio.',
            'precio.required' => 'El precio es obligatorio.',
            'cantidad.required' => 'La cantidad es obligatoria.',
        ]);

        // Verificar duplicado por nombre + presentación
        $existe = Producto::where('nombre', $request->nombre)
            ->where('presentacion', $request->presentacion)
            ->exists();

        if ($existe) {
            return back()->withErrors(['nombre' => 'Ya existe un producto con este nombre y presentación.'])->withInput();
        }

        // Subir imagen si se proporcionó
        $imagenPath = null;
        if ($request->hasFile('imagen')) {
            $imagenPath = $request->file('imagen')->store('productos', 'public');
        }

        $producto = Producto::create([
            'nombre'       => $request->nombre,
            'tipo_huevo'   => $request->tipo_huevo,
            'presentacion' => $request->presentacion,
            'descripcion'  => $request->descripcion,
            'precio'       => $request->precio,
            'cantidad'     => $request->cantidad,
            'imagen'       => $imagenPath,
            'estado'       => $request->estado,
        ]);

        // US-0005 escenario 6: registrar entrada inicial en inventario si cantidad > 0
        if ($request->cantidad > 0) {
            MovimientoInventario::create([
                'id_producto'     => $producto->id_producto,
                'tipo_movimiento' => 'entrada',
                'cantidad'        => $request->cantidad,
                'motivo'          => 'Inventario inicial al registrar producto',
                'fecha'           => now(),
            ]);
        }

        return redirect()->route('admin.productos.index')->with('success', 'Producto registrado correctamente.');
    }

    public function edit(Producto $producto)
    {
        return view('admin.productos.edit', compact('producto'));
    }

    public function update(Request $request, Producto $producto)
    {
        $request->validate([
            'nombre'       => 'required|string|max:100',
            'tipo_huevo'   => 'nullable|string|max:50',
            'presentacion' => 'nullable|string|max:50',
            'descripcion'  => 'nullable|string',
            'precio'       => 'required|numeric|min:0',
            'imagen'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'estado'       => 'required|in:activo,inactivo',
        ]);

        $datos = [
            'nombre'       => $request->nombre,
            'tipo_huevo'   => $request->tipo_huevo,
            'presentacion' => $request->presentacion,
            'descripcion'  => $request->descripcion,
            'precio'       => $request->precio,
            'estado'       => $request->estado,
        ];

        if ($request->hasFile('imagen')) {
            // Eliminar imagen anterior si existe
            if ($producto->imagen) {
                Storage::disk('public')->delete($producto->imagen);
            }
            $datos['imagen'] = $request->file('imagen')->store('productos', 'public');
        }

        $producto->update($datos);

        return redirect()->route('admin.productos.index')->with('success', 'Producto actualizado correctamente.');
    }

    /** Cambiar estado activo/inactivo (US-0004 escenario 3) */
    public function toggleEstado(Producto $producto)
    {
        $producto->update([
            'estado' => $producto->estado === 'activo' ? 'inactivo' : 'activo',
        ]);

        return redirect()->route('admin.productos.index')->with('success', 'Estado del producto actualizado.');
    }
}
