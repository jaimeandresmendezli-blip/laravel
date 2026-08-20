<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/*
|--------------------------------------------------------------------------
| US-0003 — GESTIÓN DE USUARIOS (ADMINISTRADOR)
|--------------------------------------------------------------------------
*/

class UsuarioController extends Controller
{
    /** Lista todos los usuarios */
    public function index()
    {
        $usuarios = Usuario::with('rol')->orderBy('fecha_registro', 'desc')->get();
        return view('admin.usuarios.index', compact('usuarios'));
    }

    /** Formulario crear usuario */
    public function create()
    {
        $roles = Role::all();
        return view('admin.usuarios.create', compact('roles'));
    }

    /** Guardar nuevo usuario */
    public function store(Request $request)
    {
        $request->validate([
            'nombre'    => 'required|string|max:100',
            'correo'    => 'required|email|max:100|unique:usuario,correo',
            'telefono'  => 'nullable|string|max:20',
            'id_rol'    => 'required|exists:rol,id_rol',
            'password'  => 'required|string|min:8|confirmed|regex:/^(?=.*[a-zA-Z])(?=.*\d).+$/',
        ], [
            'correo.unique'    => 'Este correo ya está registrado.',
            'password.regex'   => 'La contraseña debe tener al menos una letra y un número.',
            'password.min'     => 'La contraseña debe tener mínimo 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        Usuario::create([
            'nombre'        => $request->nombre,
            'correo'        => $request->correo,
            'telefono'      => $request->telefono,
            'id_rol'        => $request->id_rol,
            'password_hash' => Hash::make($request->password),
            'estado'        => 'activo',
        ]);

        return redirect()->route('admin.usuarios.index')->with('success', 'Usuario creado correctamente.');
    }

    /** Formulario editar usuario */
    public function edit(Usuario $usuario)
    {
        $roles = Role::all();
        return view('admin.usuarios.edit', compact('usuario', 'roles'));
    }

    /** Actualizar datos de usuario */
    public function update(Request $request, Usuario $usuario)
    {
        $request->validate([
            'nombre'   => 'required|string|max:100',
            'correo'   => 'required|email|max:100|unique:usuario,correo,' . $usuario->id_usuario . ',id_usuario',
            'telefono' => 'nullable|string|max:20',
            'id_rol'   => 'required|exists:rol,id_rol',
        ], [
            'correo.unique' => 'Este correo ya está registrado por otro usuario.',
        ]);

        $usuario->update([
            'nombre'   => $request->nombre,
            'correo'   => $request->correo,
            'telefono' => $request->telefono,
            'id_rol'   => $request->id_rol,
        ]);

        return redirect()->route('admin.usuarios.index')->with('success', 'Usuario actualizado correctamente.');
    }

    /** Cambiar estado activo/inactivo */
    public function toggleEstado(Usuario $usuario)
    {
        $usuario->update([
            'estado' => $usuario->estado === 'activo' ? 'inactivo' : 'activo',
        ]);

        $msg = $usuario->estado === 'activo' ? 'activado' : 'desactivado';
        return redirect()->route('admin.usuarios.index')->with('success', "Usuario {$msg} correctamente.");
    }

    /** Eliminar usuario */
    public function destroy(Usuario $usuario)
    {
        if ($usuario->id_usuario === auth()->id()) {
            return redirect()->route('admin.usuarios.index')
                ->with('error', 'No puedes eliminar tu propia cuenta.');
        }

        $nombre = $usuario->nombre;
        $usuario->delete();

        return redirect()->route('admin.usuarios.index')
            ->with('success', "Usuario \"{$nombre}\" eliminado correctamente.");
    }
}
