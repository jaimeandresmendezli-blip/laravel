# DOCUMENTACIÓN MÓDULO: USUARIOS (US-0003)

**Sistema EGG EXPRESS - Gestión de Usuarios**

---

## 1. INFORMACIÓN GENERAL

- **Código de Historia de Usuario**: US-0003
- **Módulo**: Gestión de Usuarios
- **Rol con acceso**: Administrador
- **Estado**: IMPLEMENTADO
- **Fecha de documentación**: Agosto 2026

---

## 2. TRAZABILIDAD COMPLETA

### 2.1 Rutas (routes/web.php)

```php
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'role:admin'])
    ->group(function () {
        Route::resource('usuarios', UsuarioController::class);
        Route::patch('usuarios/{usuario}/toggle', [UsuarioController::class, 'toggleEstado'])
            ->name('usuarios.toggle');
    });
```

**Rutas generadas por Route::resource:**

| Método HTTP | Ruta | Nombre de Ruta | Controlador | Método | Función |
|-------------|------|----------------|-------------|--------|---------|
| GET | `/admin/usuarios` | admin.usuarios.index | UsuarioController | index() | Listar usuarios |
| GET | `/admin/usuarios/create` | admin.usuarios.create | UsuarioController | create() | Formulario crear |
| POST | `/admin/usuarios` | admin.usuarios.store | UsuarioController | store() | Guardar usuario |
| GET | `/admin/usuarios/{usuario}` | admin.usuarios.show | UsuarioController | show() | Ver usuario |
| GET | `/admin/usuarios/{usuario}/edit` | admin.usuarios.edit | UsuarioController | edit() | Formulario editar |
| PUT/PATCH | `/admin/usuarios/{usuario}` | admin.usuarios.update | UsuarioController | update() | Actualizar usuario |
| DELETE | `/admin/usuarios/{usuario}` | admin.usuarios.destroy | UsuarioController | destroy() | Eliminar usuario |
| PATCH | `/admin/usuarios/{usuario}/toggle` | admin.usuarios.toggle | UsuarioController | toggleEstado() | Cambiar estado |

---

### 2.2 Controlador (app/Http/Controllers/UsuarioController.php)

**Archivo**: `app/Http/Controllers/UsuarioController.php`

**Modelos utilizados:**
- `App\Models\Usuario`
- `App\Models\Role`

**Métodos del controlador:**

#### index()
```php
public function index()
{
    $usuarios = Usuario::with('rol')->orderBy('fecha_registro', 'desc')->get();
    return view('admin.usuarios.index', compact('usuarios'));
}
```
- **Función**: Lista todos los usuarios con su rol
- **Consulta**: `Usuario::with('rol')->orderBy('fecha_registro', 'desc')->get()`
- **Vista**: `admin.usuarios.index`
- **Datos enviados**: `$usuarios` (colección de usuarios con rol cargado)

#### create()
```php
public function create()
{
    $roles = Role::all();
    return view('admin.usuarios.create', compact('roles'));
}
```
- **Función**: Muestra formulario para crear usuario
- **Consulta**: `Role::all()` - Obtiene todos los roles disponibles
- **Vista**: `admin.usuarios.create`
- **Datos enviados**: `$roles` (lista de roles para select)

#### store()
```php
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
```
- **Función**: Guarda nuevo usuario en base de datos
- **Validaciones**: 
  - nombre: requerido, string, max 100
  - correo: requerido, email, max 100, único en tabla usuario
  - telefono: nullable, string, max 20
  - id_rol: requerido, debe existir en tabla rol
  - password: requerido, min 8 caracteres, confirmado, regex (1 letra + 1 número)
- **Operación**: `Usuario::create()` con Hash::make() para contraseña
- **Redirección**: `admin.usuarios.index` con mensaje de éxito

#### edit()
```php
public function edit(Usuario $usuario)
{
    $roles = Role::all();
    return view('admin.usuarios.edit', compact('usuario', 'roles'));
}
```
- **Función**: Muestra formulario para editar usuario existente
- **Parámetro**: `$usuario` (inyectado por route model binding)
- **Consulta**: `Role::all()` - Obtiene todos los roles
- **Vista**: `admin.usuarios.edit`
- **Datos enviados**: `$usuario` (datos actuales), `$roles` (lista de roles)

#### update()
```php
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
```
- **Función**: Actualiza datos de usuario existente
- **Validaciones**: Similar a store, pero correo único excluye el usuario actual
- **Operación**: `$usuario->update()` - NO actualiza contraseña
- **Redirección**: `admin.usuarios.index` con mensaje de éxito

#### toggleEstado()
```php
public function toggleEstado(Usuario $usuario)
{
    $usuario->update([
        'estado' => $usuario->estado === 'activo' ? 'inactivo' : 'activo',
    ]);

    $msg = $usuario->estado === 'activo' ? 'activado' : 'desactivado';
    return redirect()->route('admin.usuarios.index')->with('success', "Usuario {$msg} correctamente.");
}
```
- **Función**: Cambia estado entre 'activo' e 'inactivo'
- **Operación**: Toggle del campo estado
- **Redirección**: `admin.usuarios.index` con mensaje dinámico

#### destroy()
```php
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
```
- **Función**: Elimina usuario de base de datos
- **Validación**: No permite eliminar el propio usuario autenticado
- **Operación**: `$usuario->delete()` - eliminación física
- **Redirección**: `admin.usuarios.index` con mensaje de éxito

---

### 2.3 Modelo (app/Models/Usuario.php)

**Archivo**: `app/Models/Usuario.php`

**Configuración:**
```php
protected $table = 'usuario';
protected $primaryKey = 'id_usuario';
public $timestamps = false;

protected $fillable = [
    'id_rol',
    'correo',
    'password_hash',
    'nombre',
    'telefono',
    'estado',
    'fecha_registro',
];
```

**Relaciones:**
```php
public function rol()
{
    return $this->belongsTo(Role::class, 'id_rol', 'id_rol');
}

public function recuperaciones()
{
    return $this->hasMany(RecuperacionCuenta::class, 'id_usuario', 'id_usuario');
}

public function carritos()
{
    return $this->hasMany(Carrito::class, 'id_usuario', 'id_usuario');
}

public function pedidos()
{
    return $this->hasMany(Pedido::class, 'id_cliente', 'id_usuario');
}
```

**Métodos helpers:**
```php
public function esAdmin(): bool
{
    return $this->id_rol === 1;
}

public function esCliente(): bool
{
    return $this->id_rol === 2;
}
```

**Autenticación personalizada:**
```php
public function getAuthPassword(): string
{
    return $this->password_hash;
}

public function getAuthIdentifierName(): string
{
    return 'id_usuario';
}
```

---

### 2.4 Modelo Relacionado (app/Models/Role.php)

**Archivo**: `app/Models/Role.php`

**Configuración:**
```php
protected $table = 'rol';
protected $primaryKey = 'id_rol';
public $timestamps = false;

protected $fillable = [
    'nombre'
];
```

**Relación:**
```php
public function usuarios()
{
    return $this->hasMany(Usuario::class, 'id_rol', 'id_rol');
}
```

---

### 2.5 Base de Datos

**Tabla**: `usuario`

**Migración**: `database/migrations/2026_08_13_160827_create_usuario_table.php`

**Estructura:**
```php
Schema::create('usuario', function (Blueprint $table) {
    $table->integer('id_usuario')->autoIncrement();
    $table->integer('id_rol')->default(2); // 2 = Cliente por defecto
    $table->string('correo', 100)->unique();
    $table->string('password_hash', 255);
    $table->string('nombre', 100)->nullable();
    $table->string('telefono', 20)->nullable();
    $table->enum('estado', ['activo', 'inactivo'])->default('activo');
    $table->dateTime('fecha_registro')->useCurrent();

    $table->foreign('id_rol')->references('id_rol')->on('rol');
});
```

**Campos:**
- `id_usuario` - Primary key, auto increment
- `id_rol` - Foreign key a tabla rol, default 2 (Cliente)
- `correo` - Unique, email del usuario
- `password_hash` - Contraseña encriptada
- `nombre` - Nombre completo
- `telefono` - Teléfono (opcional)
- `estado` - Enum: 'activo', 'inactivo'
- `fecha_registro` - Fecha de registro, current timestamp

**Tabla relacionada**: `rol`

**Estructura:**
```php
Schema::create('rol', function (Blueprint $table) {
    $table->integer('id_rol')->autoIncrement();
    $table->string('nombre', 50)->unique();
});
```

**Datos típicos:**
- id_rol = 1, nombre = 'Administrador'
- id_rol = 2, nombre = 'Cliente'

---

### 2.6 Vistas (resources/views/admin/usuarios/)

#### index.blade.php
- **Ubicación**: `resources/views/admin/usuarios/index.blade.php`
- **Función**: Lista todos los usuarios en tabla
- **Datos recibidos**: `$usuarios` (colección con rol cargado)
- **Elementos**:
  - Tabla con: ID, Nombre, Correo, Teléfono, Rol, Estado, Fecha Registro, Acciones
  - Botones: Editar, Toggle Estado, Eliminar
  - Filtros/Búsqueda: NO IMPLEMENTADO

#### create.blade.php
- **Ubicación**: `resources/views/admin/usuarios/create.blade.php`
- **Función**: Formulario para crear nuevo usuario
- **Datos recibidos**: `$roles` (lista de roles)
- **Campos del formulario**:
  - nombre (text, required)
  - correo (email, required)
  - telefono (text, optional)
  - id_rol (select, required)
  - password (password, required)
  - password_confirmation (password, required)
- **Método**: POST
- **Action**: `route('admin.usuarios.store')`

#### edit.blade.php
- **Ubicación**: `resources/views/admin/usuarios/edit.blade.php`
- **Función**: Formulario para editar usuario existente
- **Datos recibidos**: `$usuario` (datos actuales), `$roles` (lista de roles)
- **Campos del formulario**:
  - nombre (text, required, valor actual)
  - correo (email, required, valor actual)
  - telefono (text, optional, valor actual)
  - id_rol (select, required, valor actual)
  - NOTA: No incluye campos de contraseña
- **Método**: PUT
- **Action**: `route('admin.usuarios.update', $usuario)`

#### show.blade.php
- **Estado**: NO IMPLEMENTADO (el método show() existe en Route::resource pero no se usa)

---

## 3. FLUJO COMPLETO DE OPERACIONES

### 3.1 FLUJO CREATE (Crear Usuario)

```
1. Usuario (Admin)
   ↓
2. Accede a: /admin/usuarios/create
   ↓
3. Ruta: admin.usuarios.create (GET)
   ↓
4. Controlador: UsuarioController@create()
   ↓
5. Consulta: Role::all()
   ↓
6. Vista: admin.usuarios.create
   ↓
7. Usuario llena formulario
   ↓
8. Envía POST a: /admin/usuarios
   ↓
9. Ruta: admin.usuarios.store (POST)
   ↓
10. Controlador: UsuarioController@store()
    ↓
11. Validación: $request->validate()
    ↓
12. Modelo: Usuario::create([
        'nombre' => $request->nombre,
        'correo' => $request->correo,
        'telefono' => $request->telefono,
        'id_rol' => $request->id_rol,
        'password_hash' => Hash::make($request->password),
        'estado' => 'activo',
    ])
    ↓
13. Base de Datos: INSERT INTO usuario
    ↓
14. Redirección: admin.usuarios.index
    ↓
15. Vista: admin.usuarios.index con mensaje de éxito
```

**Validaciones en CREATE:**
- nombre: required, string, max:100
- correo: required, email, max:100, unique:usuario,correo
- telefono: nullable, string, max:20
- id_rol: required, exists:rol,id_rol
- password: required, string, min:8, confirmed, regex:/^(?=.*[a-zA-Z])(?=.*\d).+$/
- password_confirmation: required

---

### 3.2 FLUJO READ (Listar Usuarios)

```
1. Usuario (Admin)
   ↓
2. Accede a: /admin/usuarios
   ↓
3. Ruta: admin.usuarios.index (GET)
   ↓
4. Controlador: UsuarioController@index()
   ↓
5. Consulta: Usuario::with('rol')->orderBy('fecha_registro', 'desc')->get()
   ↓
6. Base de Datos: SELECT * FROM usuario JOIN rol
   ↓
7. Vista: admin.usuarios.index
   ↓
8. Usuario ve tabla de usuarios
```

**Consulta SQL generada:**
```sql
SELECT u.*, r.nombre as rol_nombre 
FROM usuario u 
LEFT JOIN rol r ON u.id_rol = r.id_rol 
ORDER BY u.fecha_registro DESC
```

---

### 3.3 FLUJO UPDATE (Actualizar Usuario)

```
1. Usuario (Admin)
   ↓
2. Accede a: /admin/usuarios/{id}/edit
   ↓
3. Ruta: admin.usuarios.edit (GET)
   ↓
4. Controlador: UsuarioController@edit($usuario)
   ↓
5. Consulta: Role::all()
   ↓
6. Vista: admin.usuarios.edit con datos del usuario
   ↓
7. Usuario modifica formulario
   ↓
8. Envía PUT a: /admin/usuarios/{id}
   ↓
9. Ruta: admin.usuarios.update (PUT)
   ↓
10. Controlador: UsuarioController@update($request, $usuario)
    ↓
11. Validación: $request->validate()
    ↓
12. Modelo: $usuario->update([
        'nombre' => $request->nombre,
        'correo' => $request->correo,
        'telefono' => $request->telefono,
        'id_rol' => $request->id_rol,
    ])
    ↓
13. Base de Datos: UPDATE usuario SET ...
    ↓
14. Redirección: admin.usuarios.index
    ↓
15. Vista: admin.usuarios.index con mensaje de éxito
```

**Validaciones en UPDATE:**
- nombre: required, string, max:100
- correo: required, email, max:100, unique:usuario,correo,{id},id_usuario
- telefono: nullable, string, max:20
- id_rol: required, exists:rol,id_rol

**NOTA**: No se permite cambiar la contraseña desde este formulario.

---

### 3.4 FLUJO DELETE (Eliminar Usuario)

```
1. Usuario (Admin)
   ↓
2. Hace clic en botón Eliminar
   ↓
3. Envía DELETE a: /admin/usuarios/{id}
   ↓
4. Ruta: admin.usuarios.destroy (DELETE)
   ↓
5. Controlador: UsuarioController@destroy($usuario)
   ↓
6. Validación: if ($usuario->id_usuario === auth()->id())
   ↓
7. Si es propio usuario → Error y redirección
   ↓
8. Si no es propio → $usuario->delete()
   ↓
9. Base de Datos: DELETE FROM usuario WHERE id_usuario = ?
   ↓
10. Redirección: admin.usuarios.index
    ↓
11. Vista: admin.usuarios.index con mensaje de éxito
```

**Restricción**: No se puede eliminar el propio usuario autenticado.

**Cascade**: Si el usuario tiene pedidos, carritos o recuperaciones, estos se eliminan en cascada por las foreign keys.

---

### 3.5 FLUJO TOGGLE ESTADO (Activar/Desactivar)

```
1. Usuario (Admin)
   ↓
2. Hace clic en botón Toggle Estado
   ↓
3. Envía PATCH a: /admin/usuarios/{id}/toggle
   ↓
4. Ruta: admin.usuarios.toggle (PATCH)
   ↓
5. Controlador: UsuarioController@toggleEstado($usuario)
   ↓
6. Modelo: $usuario->update([
        'estado' => $usuario->estado === 'activo' ? 'inactivo' : 'activo',
    ])
   ↓
7. Base de Datos: UPDATE usuario SET estado = ?
   ↓
8. Redirección: admin.usuarios.index
   ↓
9. Vista: admin.usuarios.index con mensaje dinámico
```

**Lógica**: Si estado es 'activo' → cambia a 'inactivo', si es 'inactivo' → cambia a 'activo'.

---

## 4. PUNTOS DE ATENCIÓN PARA MANTENIMIENTO

### 4.1 Si no se pueden crear usuarios
**Revisar en orden:**
1. Formulario en `resources/views/admin/usuarios/create.blade.php` - Verificar campos y nombres
2. Validaciones en `UsuarioController@store()` - Verificar reglas de validación
3. `$fillable` en `app/Models/Usuario.php` - Verificar campos permitidos
4. Migración `2026_08_13_160827_create_usuario_table.php` - Verificar estructura de tabla
5. Foreign key `id_rol` - Verificar que existan roles en tabla `rol`

### 4.2 Si no se pueden actualizar usuarios
**Revisar en orden:**
1. Formulario en `resources/views/admin/usuarios/edit.blade.php` - Verificar método PUT
2. Campo oculto `_method` con valor "PUT"
3. Validaciones en `UsuarioController@update()` - Verificar reglas
4. `$fillable` en modelo - Verificar campos permitidos
5. Route model binding - Verificar que el ID se pasa correctamente

### 4.3 Si no se pueden eliminar usuarios
**Revisar enorden:**
1. Validación en `UsuarioController@destroy()` - Verificar lógica de auto-eliminación
2. Foreign keys en base de datos - Verificar restricciones
3. Datos relacionados - Verificar si tiene pedidos, carritos, etc.
4. Cascade delete - Verificar configuración de foreign keys

### 4.4 Si el correo se duplica
**Revisar en orden:**
1. Validación `unique:usuario,correo` en store()
2. Validación `unique:usuario,correo,{id},id_usuario` en update()
3. Índice unique en tabla `usuario` para campo `correo`

### 4.5 Si la contraseña no funciona
**Revisar en orden:**
1. `Hash::make($request->password)` en store()
2. Campo `password_hash` en lugar de `password`
3. Método `getAuthPassword()` en modelo Usuario
4. Configuración de autenticación en Laravel

---

## 5. RELACIONES CON OTROS MÓDULOS

### 5.1 Relación con Módulo Pedidos
- Un usuario (cliente) tiene muchos pedidos
- `Usuario::hasMany(Pedido::class, 'id_cliente', 'id_usuario')`
- Al eliminar usuario, sus pedidos se eliminan en cascade

### 5.2 Relación con Módulo Carrito
- Un usuario tiene muchos carritos
- `Usuario::hasMany(Carrito::class, 'id_usuario', 'id_usuario')`
- Al eliminar usuario, sus carritos se eliminan en cascade

### 5.3 Relación con Módulo Inventario
- No hay relación directa
- El inventario está relacionado con productos, no con usuarios

### 5.4 Relación con Módulo Autenticación
- El modelo Usuario extiende Authenticatable
- Usa autenticación personalizada con campos `correo` y `password_hash`
- Middleware `auth` protege rutas de usuarios
- Middleware `role:admin` restringe acceso a administradores

---

## 6. ESTADO DE IMPLEMENTACIÓN

| Funcionalidad | Estado | Observaciones |
|--------------|--------|---------------|
| Listar usuarios | ✅ IMPLEMENTADO | Con rol cargado, ordenado por fecha |
| Crear usuario | ✅ IMPLEMENTADO | Con validaciones completas |
| Editar usuario | ✅ IMPLEMENTADO | Sin cambio de contraseña |
| Eliminar usuario | ✅ IMPLEMENTADO | No permite auto-eliminación |
| Toggle estado | ✅ IMPLEMENTADO | Activo/Inactivo |
| Ver detalle usuario | ❌ NO UTILIZADO | Método show() existe pero no se implementa |
| Cambiar contraseña | ❌ NO IMPLEMENTADO | Requiere módulo separado |
| Buscar usuarios | ❌ NO IMPLEMENTADO | Sin filtros de búsqueda |

---

## 7. ARCHIVOS RELACIONADOS

### Controladores
- `app/Http/Controllers/UsuarioController.php` - Controlador principal
- `app/Http/Controllers/Auth/RegisteredUserController.php` - Registro público

### Modelos
- `app/Models/Usuario.php` - Modelo principal
- `app/Models/Role.php` - Modelo de roles

### Vistas
- `resources/views/admin/usuarios/index.blade.php` - Lista
- `resources/views/admin/usuarios/create.blade.php` - Crear
- `resources/views/admin/usuarios/edit.blade.php` - Editar

### Migraciones
- `database/migrations/2026_08_13_160826_create_rol_table.php` - Tabla rol
- `database/migrations/2026_08_13_160827_create_usuario_table.php` - Tabla usuario

### Rutas
- `routes/web.php` - Rutas del módulo (líneas 37-40)

### Middleware
- `app/Http/Middleware/RoleMiddleware.php` - Middleware de rol

---

**Fin de documentación del módulo Usuarios**
