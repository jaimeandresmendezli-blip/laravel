# Flujo 1: Autenticación y Gestión de Usuarios

Este documento describe la trazabilidad de los procesos de autenticación (Login), registro y administración de usuarios en el sistema Egg Express.

## 1. Archivos Involucrados

### Vistas (Entrada / Interfaz)
- **`resources/views/auth/login.blade.php`**: Formulario de inicio de sesión.
- **`resources/views/auth/register.blade.php`**: Formulario de registro público para clientes.
- **`resources/views/admin/usuarios/index.blade.php`**: Vista del administrador para gestionar todos los usuarios.

### Rutas (Puntos de Entrada)
- **`routes/auth.php`**: Contiene las rutas generadas por Breeze para el inicio de sesión, registro y restablecimiento de contraseñas.
- **`routes/web.php`**: Contiene las rutas CRUD de usuarios bajo el grupo del administrador (`Route::resource('usuarios', UsuarioController::class)`).


### Controladores (Procesamiento Lógico)
- **`app/Http/Controllers/Auth/*`** (Breeze): Controladores proporcionados por Laravel Breeze para manejar login, registro y recuperación de contraseñas.
- **`app/Http/Controllers/UsuarioController.php`**: Contiene métodos CRUD (Crear, Editar, Eliminar) accesibles solo para administradores.

### Modelos (Persistencia y Base de Datos)
- **`app/Models/Usuario.php` / `User.php`**: Representa la tabla de usuarios en la base de datos. Utiliza Eloquent ORM de Laravel.
- **`app/Models/Role.php`**: Define los roles de los usuarios (Administrador, Cliente).

---

## 2. Trazabilidad: Proceso de Inicio de Sesión (Login)

1. **Entrada (Vista):** El usuario ingresa su correo y contraseña en `login.blade.php` y presiona "Ingresar". Los datos viajan vía `POST`.
2. **Recepción (Controlador):** El controlador de autenticación de Breeze recibe la petición.
3. **Consulta (Modelo):** Laravel consulta la base de datos a través del modelo asociado.
4. **Validación (Controlador):** Se verifica la contraseña.
   - **Si es incorrecta:** Se redirige atrás con errores de validación (`$errors`).
   - **Si es correcta:** Se genera la sesión de Laravel y se autentica al usuario.
5. **Salida (Vista):** A través del middleware, si el rol es Administrador, redirige a `admin.dashboard`. Si es Cliente, redirige a `cliente.dashboard`.

---

## 3. Trazabilidad: Proceso de Registro (Register)

1. **Entrada (Vista):** El nuevo cliente llena el formulario en `register.blade.php`.
2. **Recepción (Controlador):** El controlador de registro recibe por `POST` los datos. Valida usando las reglas de validación de Laravel (ej. `unique:users`).
3. **Persistencia (Modelo):** Se crea el nuevo registro en la base de datos y se asigna el rol por defecto (Cliente).
4. **Salida (Vista):** Redirige a la pantalla de login o al dashboard correspondiente, ya autenticado.

---

## 4. Trazabilidad: Administración de Usuarios (CRUD Admin)

1. **Entrada (Vista):** En `usuarios/index.blade.php`, el administrador hace clic en "Crear Usuario" o "Editar".
2. **Procesamiento (Controlador):** `UsuarioController.php` -> `store()` / `update()`.
   - Protegido por los middlewares `auth` y `role:admin`.
3. **Persistencia (Modelo):** Se usa el modelo `Usuario` para actualizar o crear los datos en la base de datos.
4. **Actualización (Salida):** Se redirige a la tabla `usuarios.index` con un mensaje de éxito.
