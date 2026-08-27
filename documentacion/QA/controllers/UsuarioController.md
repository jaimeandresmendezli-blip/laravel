# `UsuarioController.php`

## Propósito
Gestiona el CRUD (Crear, Leer, Actualizar, Eliminar) de los usuarios del sistema desde el panel de administración. También permite cambiar el estado de los usuarios y gestionar sus roles.

## Métodos

### `index()`
Lista todos los usuarios registrados.
- Carga la relación `rol` y ordena por fecha de registro descendente.
- Retorna la vista `admin.usuarios.index`.

### `create()` y `store(Request $request)`
Manejan la creación de nuevos usuarios.
- **`create()`**: Obtiene todos los roles y retorna la vista de creación.
- **`store()`**: Valida los datos (nombre, correo único, contraseña segura). Encripta la contraseña usando `Hash::make()` y establece el estado inicial en 'activo'.

### `edit(Usuario $usuario)` y `update(Request $request, Usuario $usuario)`
Manejan la actualización de usuarios existentes.
- **`edit()`**: Carga los datos del usuario y los roles disponibles.
- **`update()`**: Valida los datos, permitiendo que el correo pertenezca al usuario actual para evitar errores de validación 'unique'. Actualiza los campos en la BD.

### `toggleEstado(Usuario $usuario)`
Alterna el estado de la cuenta del usuario entre `activo` e `inactivo`.

### `destroy(Usuario $usuario)`
Elimina a un usuario de la base de datos.
- Impide que el administrador actual elimine su propia cuenta.

## Relaciones
- **Modelos usados**: `App\Models\Usuario`, `App\Models\Role`
- **Vistas**: `admin.usuarios.index`, `admin.usuarios.create`, `admin.usuarios.edit`
