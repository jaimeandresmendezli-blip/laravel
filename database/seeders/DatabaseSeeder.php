<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/*
|--------------------------------------------------------------------------
| DATABASE SEEDER
|--------------------------------------------------------------------------
| Siembra los datos iniciales del sistema:
|   1. Roles: Administrador y Cliente
|   2. Usuario administrador por defecto
|
*/

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |----------------------------------------------------------------------
        | ROLES
        |----------------------------------------------------------------------
        | Insertar los dos roles del sistema si no existen.
        |
        */
        DB::table('rol')->insertOrIgnore([
            ['id_rol' => 1, 'nombre' => 'Administrador'],
            ['id_rol' => 2, 'nombre' => 'Cliente'],
        ]);

        /*
        |----------------------------------------------------------------------
        | USUARIO ADMINISTRADOR
        |----------------------------------------------------------------------
        | Crear el administrador por defecto si no existe.
        |
        */
        DB::table('usuario')->insertOrIgnore([
            [
                'id_rol'         => 1,
                'correo'         => 'admin@eggexpress.com',
                'password_hash'  => Hash::make('Admin1234'),
                'nombre'         => 'Administrador',
                'telefono'       => null,
                'estado'         => 'activo',
                'fecha_registro' => now(),
            ],
        ]);
    }
}
