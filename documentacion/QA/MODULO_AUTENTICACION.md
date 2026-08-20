# DOCUMENTACIÓN MÓDULO: AUTENTICACIÓN

**Sistema EGG EXPRESS - Sistema de Autenticación**

---

## 1. INFORMACIÓN GENERAL

- **Módulo**: Autenticación de Usuarios
- **Paquete**: Laravel Breeze
- **Roles**: Administrador, Cliente
- **Estado**: IMPLEMENTADO
- **Fecha de documentación**: Agosto 2026

---

## 2. TRAZABILIDAD COMPLETA

### 2.1 Rutas (routes/auth.php)

```php
Route::middleware(['guest'])->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.update');
});

Route::middleware(['auth'])->group(function () {
    Route::get('verify-email', [EmailVerificationNotificationController::class, 'store'])
        ->name('verification.send');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
```

**Rutas del módulo:**

| Método HTTP | Ruta | Nombre de Ruta | Controlador | Método | Función | Middleware |
|-------------|------|----------------|-------------|--------|---------|------------|
| GET | `/register` | register | RegisteredUserController | create() | Formulario registro | guest |
| POST | `/register` | - | RegisteredUserController | store() | Registrar usuario | guest |
| GET | `/login` | login | AuthenticatedSessionController | create() | Formulario login | guest |
| POST | `/login` | - | AuthenticatedSessionController | store() | Iniciar sesión | guest |
| GET | `/forgot-password` | password.request | PasswordResetLinkController | create() | Formulario recuperación | guest |
| POST | `/forgot-password` | password.email | PasswordResetLinkController | store() | Enviar enlace recuperación | guest |
| GET | `/reset-password/{token}` | password.reset | NewPasswordController | create() | Formulario nueva contraseña | guest |
| POST | `/reset-password` | password.update | NewPasswordController | store() | Guardar nueva contraseña | guest |
| GET | `/verify-email` | verification.send | EmailVerificationNotificationController | store() | Reenviar verificación | auth |
| POST | `/logout` | logout | AuthenticatedSessionController | destroy() | Cerrar sesión | auth |

**NOTA**: Las rutas de autenticación están en `routes/auth.php`, no en `routes/web.php`.

---

### 2.2 Controladores (app/Http/Controllers/Auth/)

#### RegisteredUserController
**Archivo**: `app/Http/Controllers/Auth/RegisteredUserController.php`

**Modelo utilizado**: `App\Models\Usuario`

**Métodos:**

##### create()
```php
public function create()
{
    return view('auth.register');
}
```
- **Función**: Muestra formulario de registro
- **Vista**: `auth.register`
- **Datos enviados**: Ninguno

##### store()
```php
public function store(Request $request)
{
    $request->validate([
        'name'     => 'required|string|max:255',
        'email'    => 'required|string|email|max:255|unique:usuario,correo',
        'password' => 'required|string|confirmed|min:8',
    ]);

    $usuario = Usuario::create([
        'nombre'        => $request->name,
        'correo'        => $request->email,
        'password_hash' => Hash::make($request->password),
        'id_rol'        => 2, // Cliente por defecto
        'estado'        => 'activo',
        'fecha_registro'=> now(),
    ]);

    event(new Registered($usuario));

    // Redirigir al login para que el usuario ingrese sus credenciales
    return redirect()->route('login');
}
```
- **Función**: Registra nuevo usuario y redirige a login
- **Validaciones**: 
  - name: requerido, string, max 255
  - email: requerido, email, max 255, unique en usuario.correo
  - password: requerido, string, confirmed, min 8
- **Operación**: `Usuario::create()` con Hash::make() para contraseña
- **Rol por defecto**: 2 (Cliente)
- **Estado por defecto**: 'activo'
- **Evento**: `event(new Registered($usuario))` - evento de Laravel
- **Redirección**: `login` (NO autentica automáticamente)
- **NOTA**: Modificado para redirigir a login en lugar de autenticar automáticamente

---

#### AuthenticatedSessionController
**Archivo**: `app/Http/Controllers/Auth/AuthenticatedSessionController.php`

**Métodos:**

##### create()
```php
public function create()
{
    return view('auth.login');
}
```
- **Función**: Muestra formulario de login
- **Vista**: `auth.login`
- **Datos enviados**: Ninguno

##### store()
```php
public function store(LoginRequest $request)
{
    $request->authenticate();

    $request->session()->regenerate();

    return redirect()->intended(
        Auth::user()->esAdmin() ? route('admin.dashboard') : route('cliente.dashboard')
    );
}
```
- **Función**: Autentica usuario y redirige según rol
- **Autenticación**: `$request->authenticate()` - valida credenciales
- **Regeneración de sesión**: `$request->session()->regenerate()` - previene session fixation
- **Redirección**: 
  - Si es admin: `admin.dashboard`
  - Si es cliente: `cliente.dashboard`
- **NOTA**: Usa método helper `esAdmin()` del modelo Usuario

##### destroy()
```php
public function destroy(Request $request)
{
    Auth::guard('web')->logout();

    $request->session()->invalidate();

    $request->session()->regenerateToken();

    return redirect('/');
}
```
- **Función**: Cierra sesión del usuario
- **Logout**: `Auth::guard('web')->logout()`
- **Invalidación de sesión**: `$request->session()->invalidate()`
- **Regeneración de token**: `$request->session()->regenerateToken()` - previene CSRF
- **Redirección**: `/` (página principal)

---

#### PasswordResetLinkController
**Archivo**: `app/Http/Controllers/Auth/PasswordResetLinkController.php`

**Métodos:**

##### create()
```php
public function create()
{
    return view('auth.forgot-password');
}
```
- **Función**: Muestra formulario de recuperación de contraseña
- **Vista**: `auth.forgot-password`
- **Datos enviados**: Ninguno

##### store()
```php
public function store(Request $request)
{
    $request->validate([
        'email' => 'required|email',
    ]);

    Status::sendResetLink($request->only('email'));

    return back()->with('status', __('passwords.sent'));
}
```
- **Función**: Envía enlace de recuperación por correo
- **Validaciones**: 
  - email: requerido, email
- **Envío**: `Status::sendResetLink($request->only('email'))`
- **Redirección**: `back()` con mensaje de estado
- **NOTA**: Usa Laravel Password Broker

---

#### NewPasswordController
**Archivo**: `app/Http/Controllers/Auth/NewPasswordController.php`

**Métodos:**

##### create()
```php
public function create(Request $request)
{
    return view('auth.reset-password', ['request' => $request]);
}
```
- **Función**: Muestra formulario para nueva contraseña
- **Vista**: `auth.reset-password`
- **Datos enviados**: `$request` (contiene token)

##### store()
```php
public function store(Request $request)
{
    $request->validate([
        'token'    => 'required',
        'email'    => 'required|email',
        'password' => 'required|string|confirmed|min:8',
    ]);

    $status = Password::reset($request->only('email', 'password', 'password_confirmation', 'token'), function ($user, $password) {
        $user->forceFill([
            'password_hash' => Hash::make($password),
        ])->save();
    });

    return $status === Password::PASSWORD_RESET
        ? redirect()->route('login')->with('status', __('passwords.reset'))
        : back()->withInput($request->only('email'))->withErrors(['email' => __($status)]);
}
```
- **Función**: Restablece contraseña del usuario
- **Validaciones**: 
  - token: requerido
  - email: requerido, email
  - password: requerido, string, confirmed, min 8
- **Reset**: `Password::reset()` - Laravel Password Broker
- **Actualización**: `$user->forceFill(['password_hash' => Hash::make($password)])->save()`
- **Redirección**: 
  - Si éxito: `login` con mensaje
  - Si error: `back()` con errores

---

#### EmailVerificationNotificationController
**Archivo**: `app/Http/Controllers/Auth/EmailVerificationNotificationController.php`

**Métodos:**

##### store()
```php
public function store(Request $request)
{
    if ($request->user()->hasVerifiedEmail()) {
        return redirect()->intended();
    }

    $request->user()->sendEmailVerificationNotification();

    return back()->with('status', 'verification-link-sent');
}
```
- **Función**: Reenvía correo de verificación
- **Validación**: Verifica si el email ya está verificado
- **Envío**: `$request->user()->sendEmailVerificationNotification()`
- **Redirección**: `back()` con mensaje de estado
- **NOTA**: Esta funcionalidad puede no estar completamente implementada en el proyecto

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

protected $hidden = [
    'password_hash',
];
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

---

### 2.4 Middleware de Rol

**Archivo**: `app/Http/Middleware/RoleMiddleware.php`

```php
public function handle(Request $request, Closure $next, string $role)
{
    if (!Auth::check()) {
        return redirect()->route('login');
    }

    if ($role === 'admin' && !Auth::user()->esAdmin()) {
        abort(403);
    }

    if ($role === 'cliente' && !Auth::user()->esCliente()) {
        abort(403);
    }

    return $next($request);
}
```

**Función**: Verifica que el usuario tenga el rol especificado
- Si no está autenticado → redirige a login
- Si no tiene el rol → abort(403)
- Si tiene el rol → continúa con la solicitud

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
- `id_rol` - Foreign key rol, default 2 (Cliente)
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

**Tabla relacionada**: `recuperacion_cuenta`

**Estructura:**
```php
Schema::create('recuperacion_cuenta', function (Blueprint $table) {
    $table->integer('id_recuperacion')->autoIncrement();
    $table->integer('id_usuario');
    $table->string('codigo', 20);
    $table->dateTime('fecha_expiracion');
    $table->tinyInteger('usado')->default(0);

    $table->foreign('id_usuario')->references('id_usuario')->on('usuario')->onDelete('cascade');
});
```

---

### 2.6 Vistas (resources/views/auth/)

#### register.blade.php
- **Ubicación**: `resources/views/auth/register.blade.php`
- **Función**: Formulario de registro de nuevos usuarios
- **Campos del formulario**:
  - name (text, required)
  - email (email, required)
  - password (password, required, min 8)
  - password_confirmation (password, required)
- **Método**: POST
- **Action**: `route('register')`

#### login.blade.php
- **Ubicación**: `resources/views/auth/login.blade.php`
- **Función**: Formulario de inicio de sesión
- **Campos del formulario**:
  - email (email, required)
  - password (password, required)
  - remember (checkbox, optional)
- **Método**: POST
- **Action**: `route('login')`

#### forgot-password.blade.php
- **Ubicación**: `resources/views/auth/forgot-password.blade.php`
- **Función**: Formulario para solicitar recuperación de contraseña
- **Campos del formulario**:
  - email (email, required)
- **Método**: POST
- **Action**: `route('password.email')`

#### reset-password.blade.php
- **Ubicación**: `resources/views/auth/reset-password.blade.php`
- **Función**: Formulario para establecer nueva contraseña
- **Campos del formulario**:
  - email (email, required, hidden)
  - token (hidden)
  - password (password, required, min 8)
  - password_confirmation (password, required)
- **Método**: POST
- **Action**: `route('password.update')`

#### verify-email.blade.php
- **Ubicación**: `resources/views/auth/verify-email.blade.php`
- **Función**: Pantalla de verificación de email
- **Estado**: Puede no estar completamente implementado

#### confirm-password.blade.php
- **Ubicación**: `resources/views/auth/confirm-password.blade.php`
- **Función**: Confirmación de contraseña para acciones sensibles
- **Estado**: Puede no estar completamente implementado

---

## 3. FLUJO COMPLETO DE OPERACIONES

### 3.1 FLUJO REGISTRO

```
1. Usuario no autenticado
   ↓
2. Accede a: /register
   ↓
3. Ruta: register (GET)
   ↓
4. Controlador: RegisteredUserController@create()
   ↓
5. Vista: auth.register
   ↓
6. Usuario llena formulario (nombre, email, password)
   ↓
7. Envía POST a: /register
   ↓
8. Ruta: register (POST)
   ↓
9. Controlador: RegisteredUserController@store()
   ↓
10. Validación: $request->validate()
    ↓
11. Modelo: Usuario::create([
        'nombre' => $request->name,
        'correo' => $request->email,
        'password_hash' => Hash::make($request->password),
        'id_rol' => 2,
        'estado' => 'activo',
        'fecha_registro' => now(),
    ])
    ↓
12. Base de Datos: INSERT INTO usuario
    ↓
13. Evento: event(new Registered($usuario))
    ↓
14. Redirección: route('login')
    ↓
15. Vista: auth.login
    ↓
16. Usuario debe ingresar credenciales
```

**Validaciones en REGISTRO:**
- name: required, string, max:255
- email: required, email, max:255, unique:usuario,correo
- password: required, string, confirmed, min:8

**NOTA**: El usuario NO se autentica automáticamente. Se redirige a login para ingresar credenciales.

---

### 3.2 FLUJO LOGIN

```
1. Usuario no autenticado
   ↓
2. Accede a: /login
   ↓
3. Ruta: login (GET)
   ↓
4. Controlador: AuthenticatedSessionController@create()
   ↓
5. Vista: auth.login
   ↓
6. Usuario ingresa email y password
   ↓
7. Envía POST a: /login
   ↓
8. Ruta: login (POST)
   ↓
9. Controlador: AuthenticatedSessionController@store()
    ↓
10. Autenticación: $request->authenticate()
    ↓
11. Laravel valida credenciales usando Usuario::getAuthPassword()
    ↓
12. Si credenciales inválidas → Error y redirección
    ↓
13. Si credenciales válidas → $request->session()->regenerate()
    ↓
14. Verificar rol: Auth::user()->esAdmin()
    ↓
15. Si admin → redirect()->route('admin.dashboard')
    ↓
16. Si cliente → redirect()->route('cliente.dashboard')
```

**Autenticación personalizada:**
- Laravel usa `getAuthPassword()` para obtener el campo de contraseña
- El modelo Usuario retorna `$this->password_hash` en lugar de `password`
- Esto permite usar el campo personalizado `password_hash`

---

### 3.3 FLUJO LOGOUT

```
1. Usuario autenticado
   ↓
2. Hace clic en "Cerrar sesión"
   ↓
3. Envía POST a: /logout
   ↓
4. Ruta: logout (POST)
   ↓
5. Controlador: AuthenticatedSessionController@destroy()
    ↓
6. Logout: Auth::guard('web')->logout()
    ↓
7. Invalidación: $request->session()->invalidate()
    ↓
8. Regeneración token: $request->session()->regenerateToken()
    ↓
9. Redirección: /
    ↓
10. Usuario en página principal sin autenticación
```

---

### 3.4 FLUJO RECUPERACIÓN DE CONTRASEÑA

```
1. Usuario no autenticado
   ↓
2. Accede a: /forgot-password
   ↓
3. Ruta: password.request (GET)
   ↓
4. Controlador: PasswordResetLinkController@create()
   ↓
5. Vista: auth.forgot-password
   ↓
6. Usuario ingresa email
   ↓
7. Envía POST a: /forgot-password
   ↓
8. Ruta: password.email (POST)
   ↓
9. Controlador: PasswordResetLinkController@store()
    ↓
10. Validación: $request->validate()
    ↓
11. Envío: Status::sendResetLink($request->only('email'))
    ↓
12. Laravel genera token y envía correo
    ↓
13. Redirección: back() con mensaje de estado
    ↓
14. Usuario recibe correo con enlace
    ↓
15. Usuario hace clic en enlace: /reset-password/{token}
    ↓
16. Ruta: password.reset (GET)
    ↓
17. Controlador: NewPasswordController@create()
    ↓
18. Vista: auth.reset-password con token
    ↓
19. Usuario ingresa nueva contraseña
    ↓
20. Envía POST a: /reset-password
    ↓
21. Ruta: password.update (POST)
    ↓
22. Controlador: NewPasswordController@store()
    ↓
23. Validación: $request->validate()
    ↓
24. Reset: Password::reset(...)
    ↓
25. Actualización: $user->forceFill(['password_hash' => Hash::make($password)])->save()
    ↓
26. Redirección: route('login') con mensaje de éxito
```

---

## 4. PUNTOS DE ATENCIÓN PARA MANTENIMIENTO

### 4.1 Si no se puede registrar
**Revisar en orden:**
1. Formulario en `resources/views/auth/register.blade.php` - Verificar campos
2. Validaciones en `RegisteredUserController@store()` - Verificar reglas
3. `$fillable` en modelo Usuario - Verificar campos permitidos
4. Campo `correo` unique en base de datos - Verificar restricción
5. Hash::make() - Verificar que encripte correctamente

### 4.2 Si no se puede iniciar sesión
**Revisar en orden:**
1. Formulario en `resources/views/auth/login.blade.php` - Verificar campos
2. `getAuthPassword()` en modelo Usuario - Verificar que retorne password_hash
3. Campo `correo` en validación - Verificar que use el campo correcto
4. Estado del usuario - Verificar que esté 'activo'
5. Configuración de auth en config/auth.php - Verificar guards y providers

### 4.3 Si no se cierra sesión
**Revisar en orden:**
1. Ruta logout - Verificar que exista y apunte al método correcto
2. Método `destroy()` - Verificar lógica de logout
3. Middleware auth - Verificar que la ruta esté protegida
4. CSRF token - Verificar que el formulario tenga @csrf

### 4.4 Si la recuperación de contraseña no funciona
**Revisar en orden:**
1. Configuración de correo en .env - MAIL_MAILER, MAIL_HOST, etc.
2. Modelo Usuario - Verificar que implemente la interfaz de Laravel
3. Tabla password_resets - Verificar que exista (Laravel la crea automáticamente)
4. Enlace en correo - Verificar que tenga el token correcto
5. Tiempo de expiración del token - Verificar configuración

### 4.5 Si el middleware de rol no funciona
**Revisar en orden:**
1. `RoleMiddleware.php` - Verificar lógica de verificación
2. Registro de middleware en bootstrap/app.php - Verificar que esté registrado
3. Uso en rutas - Verificar que se aplique correctamente
4. Métodos `esAdmin()` y `esCliente()` - Verificar que funcionen

### 4.6 Si la redirección por rol no funciona
**Revisar en orden:**
1. Método `store()` en AuthenticatedSessionController - Verificar lógica
2. Métodos helpers en modelo Usuario - Verificar `esAdmin()` y `esCliente()`
3. Valores de id_rol - Verificar que 1 = admin, 2 = cliente
4. Rutas de dashboard - Verificar que existan

---

## 5. RELACIONES CON OTROS MÓDULOS

### 5.1 Relación con Módulo Usuarios
- La autenticación usa el modelo Usuario
- El registro crea usuarios con rol 'cliente' por defecto
- El login verifica credenciales contra tabla usuario
- El middleware de rol usa métodos del modelo Usuario

### 5.2 Relación con Módulo Administrador
- Los administradores acceden a rutas protegidas con middleware role:admin
- El middleware verifica `esAdmin()` del modelo Usuario
- Los administradores tienen id_rol = 1

### 5.3 Relación con Módulo Cliente
- Los clientes acceden a rutas protegidas con middleware role:cliente
- El middleware verifica `esCliente()` del modelo Usuario
- Los clientes tienen id_rol = 2

### 5.4 Relación con Todos los Módulos
- Todos los módulos requieren autenticación (middleware auth)
- La autenticación es el punto de entrada al sistema
- El middleware de rol controla acceso a cada módulo

---

## 6. ESTADO DE IMPLEMENTACIÓN

| Funcionalidad | Estado | Observaciones |
|--------------|--------|---------------|
| Registro de usuarios | ✅ IMPLEMENTADO | Con redirección a login |
| Login | ✅ IMPLEMENTADO | Con autenticación personalizada |
| Logout | ✅ IMPLEMENTADO | Con invalidación de sesión |
| Recuperación de contraseña | ✅ IMPLEMENTADO | Con envío de correo |
| Restablecimiento de contraseña | ✅ IMPLEMENTADO | Con token |
| Verificación de email | ⚠️ PARCIAL | Vista existe, puede no estar activa |
| Confirmación de contraseña | ⚠️ PARCIAL | Vista existe, puede no estar activa |
| Recordar sesión | ✅ IMPLEMENTADO | Checkbox remember |
| Middleware de rol | ✅ IMPLEMENTADO | RoleMiddleware personalizado |
| Redirección por rol | ✅ IMPLEMENTADO | Admin → admin.dashboard, Cliente → cliente.dashboard |
| Registro de admin | ❌ NO IMPLEMENTADO | Solo se registran clientes |
| 2FA | ❌ NO IMPLEMENTADO | No hay autenticación de dos factores |
| OAuth | ❌ NO IMPLEMENTADO | No hay login social |

---

## 7. ARCHIVOS RELACIONADOS

### Controladores
- `app/Http/Controllers/Auth/RegisteredUserController.php` - Registro
- `app/Http/Controllers/Auth/AuthenticatedSessionController.php` - Login/Logout
- `app/Http/Controllers/Auth/PasswordResetLinkController.php` - Recuperación
- `app/Http/Controllers/Auth/NewPasswordController.php` - Nueva contraseña
- `app/Http/Controllers/Auth/EmailVerificationNotificationController.php` - Verificación email

### Middleware
- `app/Http/Middleware/RoleMiddleware.php` - Middleware de rol personalizado

### Modelos
- `app/Models/Usuario.php` - Modelo de usuario con autenticación personalizada
- `app/Models/Role.php` - Modelo de roles
- `app/Models/RecuperacionCuenta.php` - Modelo de recuperación (si existe)

### Vistas
- `resources/views/auth/register.blade.php` - Formulario registro
- `resources/views/auth/login.blade.php` - Formulario login
- `resources/views/auth/forgot-password.blade.php` - Formulario recuperación
- `resources/views/auth/reset-password.blade.php` - Formulario nueva contraseña
- `resources/views/auth/verify-email.blade.php` - Verificación email
- `resources/views/auth/confirm-password.blade.php` - Confirmación contraseña

### Migraciones
- `database/migrations/2026_08_13_160826_create_rol_table.php` - Tabla rol
- `database/migrations/2026_08_13_160827_create_usuario_table.php` - Tabla usuario
- `database/migrations/2026_08_13_161100_create_recuperacion_cuenta_table.php` - Tabla recuperación

### Rutas
- `routes/auth.php` - Rutas de autenticación
- `routes/web.php` - Rutas protegidas con middleware

### Configuración
- `config/auth.php` - Configuración de autenticación de Laravel
- `.env` - Variables de entorno para correo, etc.

---

## 8. CARACTERÍSTICAS ESPECIALES

### 8.1 Autenticación Personalizada
- El modelo Usuario usa campo `password_hash` en lugar de `password`
- Método `getAuthPassword()` retorna el campo personalizado
- Esto permite usar nombres de campos no estándar de Laravel
- El campo de identificación es `id_usuario` en lugar de `id`

### 8.2 Redirección a Login Después de Registro
- Modificado para NO autenticar automáticamente
- Después de registrar, redirige a `/login`
- El usuario debe ingresar credenciales manualmente
- Esto mejora seguridad y claridad del flujo

### 8.3 Middleware de Rol Personalizado
- `RoleMiddleware` verifica roles de usuario
- Usa métodos helpers `esAdmin()` y `esCliente()`
- Aborta con 403 si no tiene el rol requerido
- Redirige a login si no está autenticado

### 8.4 Redirección por Rol
- Después de login, redirige según rol:
  - Admin → `admin.dashboard`
  - Cliente → `cliente.dashboard`
- Esto lleva al usuario al módulo correcto
- Usa métodos helpers del modelo Usuario

### 8.5 Registro Solo de Clientes
- El registro crea usuarios con id_rol = 2 (Cliente)
- No hay formulario para registrar administradores
- Los administradores se crean manualmente desde el módulo de usuarios
- Esto mantiene control sobre quién es administrador

### 8.6 Estado de Usuario
- Los usuarios tienen campo `estado` (activo/inactivo)
- El middleware de rol podría verificar el estado
- Los usuarios inactivos podrían no poder iniciar sesión
- Esto permite desactivar cuentas sin eliminarlas

### 8.7 Protección CSRF
- Todos los formularios incluyen `@csrf`
- Al hacer logout, se regenera el token CSRF
- Esto previene ataques de falsificación de petición
- Laravel maneja esto automáticamente

---

## 9. ESCENARIOS DE USO

### 9.1 Escenario 1: Registro de Nuevo Cliente
```
1. Usuario accede a /register
2. Sistema muestra formulario de registro
3. Usuario ingresa nombre, email, contraseña
4. Usuario confirma contraseña
5. Sistema valida datos
6. Sistema crea usuario con rol cliente
7. Sistema encripta contraseña
8. Sistema redirige a /login
9. Usuario ingresa credenciales
10. Sistema autentica y redirige a cliente.dashboard
```

### 9.2 Escenario 2: Login de Administrador
```
1. Admin accede a /login
2. Sistema muestra formulario de login
3. Admin ingresa email y contraseña
4. Sistema valida credenciales
5. Sistema verifica que es admin (id_rol = 1)
6. Sistema redirige a admin.dashboard
7. Admin accede a módulos administrativos
```

### 9.3 Escenario 3: Login de Cliente
```
1. Cliente accede a /login
2. Sistema muestra formulario de login
3. Cliente ingresa email y contraseña
4. Sistema valida credenciales
5. Sistema verifica que es cliente (id_rol = 2)
6. Sistema redirige a cliente.dashboard
7. Cliente accede a módulos de cliente
```

### 9.4 Escenario 4: Logout
```
1. Usuario autenticado hace clic en "Cerrar sesión"
2. Sistema cierra sesión
3. Sistema invalida sesión
4. Sistema regenera token CSRF
5. Sistema redirige a /
6. Usuario en página principal sin autenticación
```

### 9.5 Escenario 5: Recuperación de Contraseña
```
1. Usuario olvida contraseña
2. Usuario accede a /forgot-password
3. Sistema muestra formulario de recuperación
4. Usuario ingresa email
5. Sistema envía correo con token
6. Usuario recibe correo
7. Usuario hace clic en enlace
8. Sistema muestra formulario de nueva contraseña
9. Usuario ingresa nueva contraseña
10. Sistema actualiza contraseña
11. Sistema redirige a /login
```

### 9.6 Escenario 6: Acceso Denegado por Rol
```
1. Cliente intenta acceder a /admin/productos
2. Middleware role:admin verifica rol
3. Sistema detecta que no es admin
4. Sistema aborta con 403
5. Usuario ve error de acceso denegado
```

---

## 10. REGLAS DE NEGOCIO

### 10.1 Reglas de Registro
- Solo se pueden registrar clientes (rol = 2)
- El correo debe ser único
- La contraseña debe tener mínimo 8 caracteres
- La contraseña debe confirmarse
- El usuario se crea en estado 'activo'

### 10.2 Reglas de Login
- El usuario debe estar autenticado para acceder a módulos
- El correo y contraseña deben coincidir
- El usuario debe tener estado 'activo'
- La sesión se regenera después de login

### 10.3 Reglas de Roles
- id_rol = 1 es Administrador
- id_rol = 2 es Cliente
- Los roles se asignan al crear usuario
- Los roles no pueden cambiarse por login

### 10.4 Reglas de Seguridad
- Las contraseñas se encriptan con Hash::make()
- Las contraseñas nunca se muestran en texto plano
- El campo password_hash está oculto en serialización
- La sesión se invalida al hacer logout

### 10.5 Reglas de Recuperación
- La recuperación usa token único
- El token tiene tiempo de expiración
- El correo debe estar registrado
- La nueva contraseña debe cumplir las mismas reglas

---

## 11. CONFIGURACIÓN DE LARARL BREEZE

### 11.1 Instalación
El proyecto usa Laravel Breeze para autenticación:
- Proporciona scaffolding de autenticación
- Incluye rutas, controladores y vistas predefinidas
- Se ha personalizado para usar el modelo Usuario con campos personalizados

### 11.2 Personalizaciones Realizadas
- Modelo Usuario con campos personalizados (correo, password_hash)
- Método `getAuthPassword()` para autenticación personalizada
- Redirección a login después de registro (no autenticación automática)
- Middleware de rol personalizado (RoleMiddleware)
- Redirección por rol después de login

---

**Fin de documentación del módulo Autenticación**
