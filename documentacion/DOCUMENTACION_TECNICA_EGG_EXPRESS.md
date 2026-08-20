# DOCUMENTACIÓN TÉCNICA - EGG EXPRESS

**Sistema de Comercialización de Huevos**

Proyecto Laravel para gestión de ventas de productos de huevos, inventario, pedidos y usuarios.

---

## 1. DOCUMENTACIÓN CRUD

### MÓDULO: USUARIOS (US-0003)
- **Controlador**: `UsuarioController`
- **Modelo**: `Usuario`
- **Tabla**: `usuario`
- **Rutas**: `admin.usuarios.*` (Route::resource)
- **Vistas**: `admin/usuarios/index.blade.php`, `admin/usuarios/create.blade.php`, `admin/usuarios/edit.blade.php`

**Operaciones CRUD:**

- **CREATE**: `store()` - Registra un nuevo usuario con nombre, correo, teléfono, rol y contraseña. Valida que el correo sea único. La contraseña se encripta con Hash::make().
- **READ**: `index()` - Lista todos los usuarios con su rol, ordenados por fecha de registro descendente.
- **UPDATE**: `update()` - Modifica nombre, correo, teléfono y rol de un usuario existente. Valida que el correo no esté duplicado.
- **DELETE**: `destroy()` - Elimina un usuario de la base de datos. No permite eliminar el propio usuario autenticado.
- **ADICIONAL**: `toggleEstado()` - Cambia el estado entre 'activo' e 'inactivo' sin eliminar el registro.

---

### MÓDULO: PRODUCTOS (US-0004)
- **Controlador**: `ProductoController`
- **Modelo**: `Producto`
- **Tabla**: `producto`
- **Rutas**: `admin.productos.*` (Route::resource)
- **Vistas**: `admin/productos/index.blade.php`, `admin/productos/create.blade.php`, `admin/productos/edit.blade.php`

**Operaciones CRUD:**

- **CREATE**: `store()` - Registra un producto con nombre, tipo de huevo, presentación, descripción, precio, cantidad inicial, imagen y estado. Si la cantidad > 0, registra automáticamente una entrada en inventario. Valida duplicados por nombre + presentación.
- **READ**: `index()` - Lista todos los productos ordenados alfabéticamente por nombre.
- **UPDATE**: `update()` - Modifica datos del producto. Si se carga nueva imagen, elimina la anterior. No permite modificar la cantidad directamente (esto se hace por inventario).
- **DELETE**: NO IMPLEMENTADO - Se usa `toggleEstado()` para desactivar.
- **ADICIONAL**: `toggleEstado()` - Cambia el estado entre 'activo' e 'inactivo'.

---

### MÓDULO: INVENTARIO (US-0005, US-0006)
- **Controlador**: `InventarioController`
- **Modelo**: `MovimientoInventario`
- **Tabla**: `movimiento_inventario`
- **Rutas**: `admin.inventario.*` (rutas individuales)
- **Vistas**: `admin/inventario/index.blade.php`, `admin/inventario/create.blade.php`

**Operaciones CRUD:**

- **CREATE**: `store()` - Registra movimientos manuales de entrada o salida de inventario. Para salidas, verifica stock suficiente. Actualiza automáticamente la cantidad del producto.
- **READ**: `index()` - Lista historial de movimientos con filtros por producto, tipo de movimiento y rango de fechas.
- **UPDATE**: NO IMPLEMENTADO
- **DELETE**: NO IMPLEMENTADO

---

### MÓDULO: PEDIDOS (ADMINISTRADOR - US-0008)
- **Controlador**: `AdminPedidoController`
- **Modelo**: `Pedido`
- **Tabla**: `pedido`
- **Rutas**: `admin.pedidos.*` (rutas individuales)
- **Vistas**: `admin/pedidos/index.blade.php`, `admin/pedidos/show.blade.php`

**Operaciones CRUD:**

- **CREATE**: NO IMPLEMENTADO - Los pedidos los crean los clientes.
- **READ**: `index()` - Lista todos los pedidos con filtros por estado y estado de entrega. `show()` muestra detalle de un pedido específico.
- **UPDATE**: `actualizarEstadoEntrega()` - Actualiza el estado de entrega (pendiente, en_camino, entregado, cancelado).
- **DELETE**: NO IMPLEMENTADO

---

### MÓDULO: PEDIDOS (CLIENTE - US-0008)
- **Controlador**: `ClientePedidoController`
- **Modelo**: `Pedido`
- **Tabla**: `pedido`
- **Rutas**: `cliente.pedidos.*` (rutas individuales)
- **Vistas**: `cliente/pedidos/index.blade.php`, `cliente/pedidos/create.blade.php`, `cliente/pedidos/show.blade.php`

**Operaciones CRUD:**

- **CREATE**: `store()` - Convierte el carrito en pedido. Valida datos de entrega, método de pago. Verifica stock de todos los productos. Crea pedido, detalles, pago, descontar stock y registrar movimientos de salida. Marca carrito como 'comprado'.
- **READ**: `index()` - Lista pedidos del cliente autenticado con resumen de totales. `show()` muestra detalle de un pedido propio.
- **UPDATE**: NO IMPLEMENTADO
- **DELETE**: `cancelar()` - Cancela un pedido en estado 'pendiente'. Devuelve stock automáticamente, registra movimientos de entrada.

---

### MÓDULO: CARRITO (US-0008)
- **Controlador**: `CarritoController`
- **Modelo**: `Carrito`, `DetalleCarrito`
- **Tablas**: `carrito`, `detalle_carrito`
- **Rutas**: `cliente.carrito.*` (rutas individuales)
- **Vistas**: `cliente/carrito/index.blade.php`

**Operaciones CRUD:**

- **CREATE**: `agregar()` - Agrega producto al carrito. Verifica stock y estado activo del producto. Si ya existe, suma cantidad.
- **READ**: `index()` - Muestra el carrito activo del usuario con sus detalles y total.
- **UPDATE**: `actualizar()` - Modifica la cantidad de un ítem del carrito. Verifica stock disponible.
- **DELETE**: `eliminar()` - Elimina un ítem específico. `vaciar()` - Elimina todos los ítems del carrito.

---

### MÓDULO: CATÁLOGO (US-0007)
- **Controlador**: `CatalogoController`
- **Modelo**: `Producto`
- **Tabla**: `producto`
- **Rutas**: `cliente.catalogo.*` (rutas individuales)
- **Vistas**: `cliente/catalogo/index.blade.php`, `cliente/catalogo/show.blade.php`

**Operaciones CRUD:**

- **CREATE**: NO IMPLEMENTADO
- **READ**: `index()` - Lista productos activos con búsqueda en tiempo real por nombre, tipo, presentación o descripción. `show()` muestra detalle de un producto activo.
- **UPDATE**: NO IMPLEMENTADO
- **DELETE**: NO IMPLEMENTADO

---

### MÓDULO: REPORTES (US-0009)
- **Controlador**: `ReporteController`
- **Modelo**: `Pedido`, `MovimientoInventario`, `Pago`
- **Tablas**: `pedido`, `movimiento_inventario`, `pago`
- **Rutas**: `admin.reportes.*` (rutas individuales)
- **Vistas**: `admin/reportes/index.blade.php`, `admin/reportes/pdf.blade.php`

**Operaciones CRUD:**

- **CREATE**: NO IMPLEMENTADO
- **READ**: `index()` - Muestra reporte con filtros de fechas. Calcula totales de ventas, pedidos por estado. Lista pedidos y movimientos del período.
- **UPDATE**: NO IMPLEMENTADO
- **DELETE**: NO IMPLEMENTADO
- **ADICIONAL**: `exportarPdf()` - Genera y descarga reporte en PDF con la misma información.

---

## 2. CONEXIÓN ENTRE RUTAS, CONTROLADORES, MODELOS Y VISTAS

### Flujo de Datos en el Sistema

```
Usuario
↓
Vista (Blade)
↓
Ruta (routes/web.php)
↓
Controlador (app/Http/Controllers)
↓
Modelo (app/Models)
↓
Base de Datos
↓
Modelo
↓
Controlador
↓
Vista
```

### Ubicación de Archivos

**Rutas:**
- `routes/web.php` - Rutas web principales (admin, cliente, auth)
- `routes/auth.php` - Rutas de autenticación (login, registro, recuperación)

**Controladores:**
- `app/Http/Controllers/` - Controladores principales
- `app/Http/Controllers/Auth/` - Controladores de autenticación (Breeze)

**Modelos:**
- `app/Models/` - Modelos de Eloquent

**Vistas:**
- `resources/views/admin/` - Vistas del administrador
- `resources/views/cliente/` - Vistas del cliente
- `resources/views/auth/` - Vistas de autenticación
- `resources/views/layouts/` - Layouts principales
- `resources/views/components/` - Componentes Blade

### Ejemplo de Flujo: Crear Producto

1. **Usuario** accede a `/admin/productos/create`
2. **Ruta** en `routes/web.php`: `Route::resource('productos', ProductoController::class);`
3. **Controlador** `ProductoController@create()` retorna vista `admin.productos.create`
4. **Usuario** llena formulario y envía POST a `/admin/productos`
5. **Ruta** llama a `ProductoController@store()`
6. **Controlador** valida datos, usa `Producto::create()` para guardar
7. **Modelo** `Producto` inserta en tabla `producto`
8. **Controlador** redirige a `admin.productos.index` con mensaje de éxito

### Ejemplo de Flujo: Consultar Catálogo

1. **Usuario** accede a `/cliente/catalogo`
2. **Ruta** en `routes/web.php`: `Route::get('catalogo', [CatalogoController::class, 'index'])`
3. **Controlador** `CatalogoController@index()` consulta `Producto::where('estado', 'activo')->get()`
4. **Modelo** `Producto` ejecuta SELECT en tabla `producto`
5. **Controlador** retorna vista `cliente.catalogo.index` con productos
6. **Vista** muestra lista de productos al usuario

### Cómo se Envían Formularios

- **Formularios HTML** usan método POST, PUT/PATCH, DELETE
- **Laravel** requiere `@csrf` para protección CSRF
- **PUT/PATCH/DELETE** usan campo oculto `_method`
- **Controladores** reciben datos en parámetro `Request $request`
- **Validaciones** se realizan con `$request->validate()`

---

## 3. CONTROLADORES

### UsuarioController
- **Ubicación**: `app/Http/Controllers/UsuarioController.php`
- **Módulo**: Gestión de usuarios (Administrador)
- **Modelo**: `Usuario`
- **Métodos**:
  - `index()` - Lista todos los usuarios con su rol
  - `create()` - Muestra formulario de creación
  - `store()` - Guarda nuevo usuario con validaciones
  - `edit()` - Muestra formulario de edición
  - `update()` - Actualiza datos de usuario
  - `toggleEstado()` - Cambia estado activo/inactivo
  - `destroy()` - Elimina usuario (no propio)

### ProductoController
- **Ubicación**: `app/Http/Controllers/ProductoController.php`
- **Módulo**: Gestión de productos (Administrador)
- **Modelo**: `Producto`, `MovimientoInventario`
- **Métodos**:
  - `index()` - Lista todos los productos
  - `create()` - Muestra formulario de creación
  - `store()` - Guarda producto y registra entrada inicial en inventario
  - `edit()` - Muestra formulario de edición
  - `update()` - Actualiza producto (incluye imagen)
  - `toggleEstado()` - Cambia estado activo/inactivo

### InventarioController
- **Ubicación**: `app/Http/Controllers/InventarioController.php`
- **Módulo**: Gestión de inventario (Administrador)
- **Modelo**: `MovimientoInventario`, `Producto`
- **Métodos**:
  - `index()` - Lista historial de movimientos con filtros
  - `create()` - Muestra formulario de movimiento manual
  - `store()` - Registra entrada/salida y actualiza stock

### AdminPedidoController
- **Ubicación**: `app/Http/Controllers/AdminPedidoController.php`
- **Módulo**: Gestión de pedidos (Administrador)
- **Modelo**: `Pedido`
- **Métodos**:
  - `index()` - Lista todos los pedidos con filtros
  - `show()` - Muestra detalle de un pedido
  - `actualizarEstadoEntrega()` - Actualiza estado de entrega

### ClientePedidoController
- **Ubicación**: `app/Http/Controllers/ClientePedidoController.php`
- **Módulo**: Pedidos del cliente
- **Modelo**: `Pedido`, `Carrito`, `DetallePedido`, `Pago`, `MovimientoInventario`
- **Métodos**:
  - `index()` - Lista pedidos del cliente con resumen
  - `create()` - Muestra confirmación de pedido desde carrito
  - `store()` - Convierte carrito en pedido, descuenta stock
  - `show()` - Muestra detalle de pedido propio
  - `cancelar()` - Cancela pedido pendiente, devuelve stock

### CarritoController
- **Ubicación**: `app/Http/Controllers/CarritoController.php`
- **Módulo**: Carrito de compras (Cliente)
- **Modelo**: `Carrito`, `DetalleCarrito`, `Producto`
- **Métodos**:
  - `index()` - Muestra carrito activo con detalles
  - `agregar()` - Agrega producto al carrito
  - `actualizar()` - Modifica cantidad de un ítem
  - `eliminar()` - Elimina un ítem del carrito
  - `vaciar()` - Elimina todos los ítems

### CatalogoController
- **Ubicación**: `app/Http/Controllers/CatalogoController.php`
- **Módulo**: Catálogo de productos (Cliente)
- **Modelo**: `Producto`
- **Métodos**:
  - `index()` - Lista productos activos con búsqueda
  - `show()` - Muestra detalle de producto activo

### ReporteController
- **Ubicación**: `app/Http/Controllers/ReporteController.php`
- **Módulo**: Reportes del sistema (Administrador)
- **Modelo**: `Pedido`, `MovimientoInventario`, `Pago`
- **Métodos**:
  - `index()` - Muestra reporte con filtros de fechas
  - `exportarPdf()` - Genera y descarga reporte PDF

### Controladores de Autenticación (Breeze)
- **Ubicación**: `app/Http/Controllers/Auth/`
- **Módulo**: Autenticación de usuarios
- **Controladores**:
  - `RegisteredUserController` - Registro de usuarios
  - `AuthenticatedSessionController` - Login/logout
  - `PasswordResetLinkController` - Recuperación de contraseña
  - `NewPasswordController` - Restablecer contraseña
  - `EmailVerificationNotificationController` - Verificación de email

---

## 4. MODELOS

### Usuario
- **Tabla**: `usuario`
- **Campos principales**: `id_usuario`, `id_rol`, `correo`, `password_hash`, `nombre`, `telefono`, `estado`, `fecha_registro`
- **Relaciones**:
  - `belongsTo(Role)` - Un usuario tiene un rol
  - `hasMany(RecuperacionCuenta)` - Un usuario tiene recuperaciones de cuenta
  - `hasMany(Carrito)` - Un usuario tiene carritos
  - `hasMany(Pedido)` - Un usuario tiene pedidos (como cliente)
- **Función**: Modelo de autenticación personalizado. Extiende Authenticatable. Usa campos personalizados (correo, password_hash).

### Role
- **Tabla**: `rol`
- **Campos principales**: `id_rol`, `nombre`
- **Relaciones**:
  - `hasMany(Usuario)` - Un rol tiene muchos usuarios
- **Función**: Define roles del sistema (Administrador, Cliente).

### Producto
- **Tabla**: `producto`
- **Campos principales**: `id_producto`, `nombre`, `tipo_huevo`, `presentacion`, `descripcion`, `precio`, `cantidad`, `imagen`, `estado`
- **Relaciones**:
  - `hasMany(MovimientoInventario)` - Un producto tiene movimientos de inventario
  - `hasMany(DetalleCarrito)` - Un producto tiene detalles en carritos
  - `hasMany(DetallePedido)` - Un producto tiene detalles en pedidos
- **Función**: Representa los productos de huevos que se venden.

### MovimientoInventario
- **Tabla**: `movimiento_inventario`
- **Campos principales**: `id_movimiento`, `id_producto`, `tipo_movimiento`, `cantidad`, `motivo`, `fecha`
- **Relaciones**:
  - `belongsTo(Producto)` - Un movimiento pertenece a un producto
- **Función**: Registra historial de entradas y salidas de inventario.

### Carrito
- **Tabla**: `carrito`
- **Campos principales**: `id_carrito`, `id_usuario`, `estado`, `fecha_creacion`
- **Relaciones**:
  - `belongsTo(Usuario)` - Un carrito pertenece a un usuario
  - `hasMany(DetalleCarrito)` - Un carrito tiene muchos detalles
- **Función**: Almacena productos temporales antes de generar pedido.

### DetalleCarrito
- **Tabla**: `detalle_carrito`
- **Campos principales**: `id_detalle_carrito`, `id_carrito`, `id_producto`, `cantidad`, `precio_unitario`, `subtotal`
- **Relaciones**:
  - `belongsTo(Carrito)` - Un detalle pertenece a un carrito
  - `belongsTo(Producto)` - Un detalle pertenece a un producto
- **Función**: Ítems individuales en un carrito. Unique constraint: id_carrito + id_producto.

### Pedido
- **Tabla**: `pedido`
- **Campos principales**: `id_pedido`, `id_cliente`, `direccion_entrega`, `barrio`, `telefono_entrega`, `referencia`, `observaciones`, `estado_entrega`, `fecha`, `estado`, `total`
- **Relaciones**:
  - `belongsTo(Usuario)` - Un pedido pertenece a un cliente
  - `hasMany(DetallePedido)` - Un pedido tiene muchos detalles
  - `hasOne(Pago)` - Un pedido tiene un pago
- **Función**: Representa una orden de compra de un cliente.

### DetallePedido
- **Tabla**: `detalle_pedido`
- **Campos principales**: `id_detalle`, `id_pedido`, `id_producto`, `cantidad`, `precio`, `subtotal`
- **Relaciones**:
  - `belongsTo(Pedido)` - Un detalle pertenece a un pedido
  - `belongsTo(Producto)` - Un detalle pertenece a un producto
- **Función**: Ítems individuales en un pedido. Unique constraint: id_pedido + id_producto.

### Pago
- **Tabla**: `pago`
- **Campos principales**: `id_pago`, `id_pedido`, `metodo_pago`, `monto`, `estado_pago`, `referencia_pago`, `fecha`
- **Relaciones**:
  - `belongsTo(Pedido)` - Un pago pertenece a un pedido
- **Función**: Registra información de pago de un pedido.

### RecuperacionCuenta
- **Tabla**: `recuperacion_cuenta`
- **Campos principales**: `id_recuperacion`, `id_usuario`, `codigo`, `fecha_expiracion`, `usado`
- **Relaciones**:
  - `belongsTo(Usuario)` - Una recuperación pertenece a un usuario
- **Función**: Almacena códigos para recuperación de contraseña.

---

## 5. RUTAS

### Rutas del Administrador (`/admin/*`)
Middleware: `auth` + `role:admin`

| Ruta | Método | Controlador | Función |
|------|--------|-------------|---------|
| `/admin/dashboard GET` | GET | Closure | Dashboard administrador |
| `/admin/usuarios GET` | GET | UsuarioController@index | Listar usuarios |
| `/admin/usuarios POST` | POST | UsuarioController@store | Crear usuario |
| `/admin/usuarios/{usuario} GET` | GET | UsuarioController@show | Ver usuario |
| `/admin/usuarios/{usuario} PUT` | PUT | UsuarioController@update | Actualizar usuario |
| `/admin/usuarios/{usuario} DELETE` | DELETE | UsuarioController@destroy | Eliminar usuario |
| `/admin/usuarios/{usuario}/toggle PATCH` | PATCH | UsuarioController@toggleEstado | Cambiar estado usuario |
| `/admin/productos GET` | GET | ProductoController@index | Listar productos |
| `/admin/productos POST` | POST | ProductoController@store | Crear producto |
| `/admin/productos/{producto} GET` | GET | ProductoController@show | Ver producto |
| `/admin/productos/{producto} PUT` | PUT | ProductoController@update | Actualizar producto |
| `/admin/productos/{producto} DELETE` | DELETE | ProductoController@destroy | Eliminar producto |
| `/admin/productos/{producto}/toggle PATCH` | PATCH | ProductoController@toggleEstado | Cambiar estado producto |
| `/admin/inventario GET` | GET | InventarioController@index | Ver inventario |
| `/admin/inventario/crear GET` | GET | InventarioController@create | Formulario movimiento |
| `/admin/inventario POST` | POST | InventarioController@store | Registrar movimiento |
| `/admin/pedidos GET` | GET | AdminPedidoController@index | Listar pedidos |
| `/admin/pedidos/{pedido} GET` | GET | AdminPedidoController@show | Ver pedido |
| `/admin/pedidos/{pedido}/estado-entrega PATCH` | PATCH | AdminPedidoController@actualizarEstadoEntrega | Actualizar entrega |
| `/admin/reportes GET` | GET | ReporteController@index | Ver reportes |
| `/admin/reportes/pdf GET` | GET | ReporteController@exportarPdf | Exportar PDF |

### Rutas del Cliente (`/cliente/*`)
Middleware: `auth` + `role:cliente`

| Ruta | Método | Controlador | Función |
|------|--------|-------------|---------|
| `/cliente/dashboard GET` | GET | Closure | Dashboard cliente |
| `/cliente/catalogo GET` | GET | CatalogoController@index | Ver catálogo |
| `/cliente/catalogo/{producto} GET` | GET | CatalogoController@show | Ver producto |
| `/cliente/carrito GET` | GET | CarritoController@index | Ver carrito |
| `/cliente/carrito/agregar POST` | POST | CarritoController@agregar | Agregar al carrito |
| `/cliente/carrito/actualizar/{detalle} PATCH` | PATCH | CarritoController@actualizar | Actualizar ítem |
| `/cliente/carrito/eliminar/{detalle} DELETE` | DELETE | CarritoController@eliminar | Eliminar ítem |
| `/cliente/carrito/vaciar DELETE` | DELETE | CarritoController@vaciar | Vaciar carrito |
| `/cliente/pedidos GET` | GET | ClientePedidoController@index | Mis pedidos |
| `/cliente/pedidos/crear GET` | GET | ClientePedidoController@create | Crear pedido |
| `/cliente/pedidos POST` | POST | ClientePedidoController@store | Guardar pedido |
| `/cliente/pedidos/{pedido} GET` | GET | ClientePedidoController@show | Ver pedido |
| `/cliente/pedidos/{pedido}/cancelar DELETE` | DELETE | ClientePedidoController@cancelar | Cancelar pedido |

### Rutas de Autenticación
Middleware: `guest` (no autenticado) o `auth` (autenticado)

| Ruta | Método | Controlador | Función |
|------|--------|-------------|---------|
| `/register GET` | GET | RegisteredUserController@create | Formulario registro |
| `/register POST` | POST | RegisteredUserController@store | Registrar usuario |
| `/login GET` | GET | AuthenticatedSessionController@create | Formulario login |
| `/login POST` | POST | AuthenticatedSessionController@store | Iniciar sesión |
| `/logout POST` | POST | AuthenticatedSessionController@destroy | Cerrar sesión |
| `/forgot-password GET` | GET | PasswordResetLinkController@create | Formulario recuperación |
| `/forgot-password POST` | POST | PasswordResetLinkController@store | Enviar correo |
| `/reset-password/{token} GET` | GET | NewPasswordController@create | Formulario nueva contraseña |
| `/reset-password POST` | POST | NewPasswordController@store | Guardar nueva contraseña |

---

## 6. BASE DE DATOS

### Configuración
- **Motor**: MySQL/MariaDB (configurado en Laravel)
- **Archivo de configuración**: `config/database.php`
- **Variables de entorno**: `.env` (DB_DATABASE, DB_USERNAME, DB_PASSWORD, etc.)

### Tablas Principales

1. **rol** - Roles del sistema (Administrador, Cliente)
2. **usuario** - Usuarios del sistema
3. **producto** - Catálogo de productos
4. **movimiento_inventario** - Historial de movimientos de stock
5. **carrito** - Carritos de compras
6. **detalle_carrito** - Ítems en carrito
7. **pedido** - Pedidos de clientes
8. **detalle_pedido** - Ítems en pedidos
9. **pago** - Información de pagos
10. **recuperacion_cuenta** - Códigos de recuperación de contraseña

### Relaciones Importantes

- `usuario.id_rol` → `rol.id_rol` (Un usuario tiene un rol)
- `pedido.id_cliente` → `usuario.id_usuario` (Un pedido pertenece a un cliente)
- `movimiento_inventario.id_producto` → `producto.id_producto` (Movimiento de un producto)
- `carrito.id_usuario` → `usuario.id_usuario` (Carrito de un usuario)
- `detalle_carrito.id_carrito` → `carrito.id_carrito` (Detalle pertenece a carrito)
- `detalle_carrito.id_producto` → `producto.id_producto` (Detalle de un producto)
- `detalle_pedido.id_pedido` → `pedido.id_pedido` (Detalle pertenece a pedido)
- `detalle_pedido.id_producto` → `producto.id_producto` (Detalle de un producto)
- `pago.id_pedido` → `pedido.id_pedido` (Pago de un pedido)

### Migraciones

Las migraciones se encuentran en `database/migrations/`:

- `2026_08_13_160826_create_rol_table.php` - Crea tabla rol
- `2026_08_13_160827_create_usuario_table.php` - Crea tabla usuario
- `2026_08_13_160910_create_producto_table.php` - Crea tabla producto
- `2026_08_13_160924_create_movimiento_inventario_table.php` - Crea tabla movimiento_inventario
- `2026_08_13_160935_create_carrito_table.php` - Crea tabla carrito
- `2026_08_13_160946_create_detalle_carrito_table.php` - Crea tabla detalle_carrito
- `2026_08_13_160957_create_pedido_table.php` - Crea tabla pedido
- `2026_08_13_161009_create_detalle_pedido_table.php` - Crea tabla detalle_pedido
- `2026_08_13_161019_create_pago_table.php` - Crea tabla pago
- `2026_08_13_161100_create_recuperacion_cuenta_table.php` - Crea tabla recuperacion_cuenta

### Restricciones

- **Unique constraints**:
  - `usuario.correo` - Correo único
  - `rol.nombre` - Nombre de rol único
  - `detalle_carrito` - Unique (id_carrito, id_producto)
  - `detalle_pedido` - Unique (id_pedido, id_producto)

- **Foreign keys con cascade**:
  - `movimiento_inventario.id_producto` - ON DELETE CASCADE
  - `carrito.id_usuario` - ON DELETE CASCADE
  - `detalle_carrito.id_carrito` - ON DELETE CASCADE
  - `detalle_carrito.id_producto` - ON DELETE CASCADE
  - `pedido.id_cliente` - ON DELETE CASCADE
  - `detalle_pedido.id_pedido` - ON DELETE CASCADE
  - `detalle_pedido.id_producto` - ON DELETE CASCADE
  - `pago.id_pedido` - ON DELETE CASCADE
  - `recuperacion_cuenta.id_usuario` - ON DELETE CASCADE

---

## 7. ¿CÓMO SE MANEJA ACTUALMENTE EL SISTEMA?

### 1. Cómo entra un usuario al sistema
- El usuario accede a la URL principal del sistema
- Si no está autenticado, es redirigido a `/login`
- Puede registrarse en `/register` si es un nuevo cliente

### 2. Cómo se autentica
- **Registro**: Usuario completa formulario en `/register`. Se crea cuenta con rol 2 (Cliente) por defecto. Se redirige a `/login` para ingresar credenciales.
- **Login**: Usuario ingresa correo y contraseña en `/login`. Laravel valida usando el modelo `Usuario` con campo personalizado `password_hash`.
- **Middleware**: Rutas protegidas usan middleware `auth` para verificar autenticación y `role:admin` o `role:cliente` para verificar permisos.

### 3. Cómo accede a los módulos
- **Administrador**: Accede a rutas bajo `/admin/*` (dashboard, usuarios, productos, inventario, pedidos, reportes)
- **Cliente**: Accede a rutas bajo `/cliente/*` (dashboard, catálogo, carrito, pedidos)
- El middleware de rol bloquea acceso a módulos no autorizados

### 4. Cómo se registran datos
- **Formularios HTML**: En vistas Blade con método POST/PUT/PATCH/DELETE
- **Validación**: En controladores con `$request->validate()`
- **Creación**: Modelos usan `Model::create($datos)` o `new Model() + save()`
- **Transacciones**: Operaciones complejas usan `DB::transaction()` para asegurar integridad

### 5. Cómo se consultan datos
- **Eloquent**: Modelos usan métodos como `Model::get()`, `Model::find()`, `Model::where()`
- **Relaciones**: Se cargan con `with()` para eager loading (ej: `Pedido::with(['cliente', 'detalles.producto'])`)
- **Filtros**: Se aplican condicionalmente según parámetros de request

### 6. Cómo se actualizan
- **Formularios de edición**: Vistas con datos pre-cargados del registro
- **Actualización**: Modelos usan `$modelo->update($datos)` o asignación individual + `save()`
- **Validaciones**: Se repiten validaciones relevantes del create

### 7. Cómo se eliminan o desactivan
- **Eliminación física**: `destroy()` elimina el registro de la base de datos
- **Eliminación lógica**: `toggleEstado()` cambia campo `estado` a 'inactivo'
- **Cascade**: Foreign keys con ON DELETE CASCADE eliminan registros relacionados automáticamente

### 8. Cómo se relacionan los diferentes módulos
- **Usuarios → Pedidos**: Un cliente tiene muchos pedidos
- **Pedidos → Detalles**: Un pedido tiene muchos detalles de productos
- **Detalles → Productos**: Cada detalle referencia un producto
- **Pedidos → Pagos**: Un pedido tiene un pago
- **Productos → Inventario**: Un producto tiene muchos movimientos de inventario
- **Carritos → Usuarios**: Un usuario tiene carritos de compras
- **Carritos → Detalles**: Un carrito tiene muchos detalles de productos

### 9. Cómo se almacenan los datos
- **Base de datos relacional**: MySQL/MariaDB
- **ORM**: Eloquent ORM de Laravel mapea tablas a modelos
- **Migraciones**: Control de versiones de estructura de base de datos
- **Timestamps**: La mayoría de modelos no usan timestamps automáticos (configurado `$timestamps = false`)

### 10. Cómo se muestran nuevamente al usuario
- **Vistas Blade**: Archivos `.blade.php` en `resources/views/`
- **Compact**: Controladores pasan datos a vistas con `compact('variable')`
- **Layouts**: Vistas usan layouts base (`layouts/admin.blade.php`, `layouts/app.blade.php`)
- **Componentes**: Componentes reutilizables en `resources/views/components/`
- **Mensajes**: Sesiones flash para mensajes de éxito/error (`with('success', 'mensaje')`)

---

## 8. GUÍA BÁSICA DE MANTENIMIENTO Y SOLUCIÓN DE ERRORES

### Si una página no carga

**Revisar en este orden:**
1. `routes/web.php` - Verificar que la ruta existe
2. Controlador correspondiente en `app/Http/Controllers/` - Verificar que el método existe
3. Vista en `resources/views/` - Verificar que el archivo existe
4. Consola de Laravel - Ejecutar `php artisan route:list` para ver rutas registradas
5. `storage/logs/laravel.log` - Revisar errores recientes

### Si no se puede guardar un registro

**Revisar en este orden:**
1. **Formulario**: Verificar que los campos tienen los nombres correctos (`name="campo"`)
2. **Ruta**: Verificar que la ruta POST existe y apunta al controlador correcto
3. **Método store()**: Revisar validaciones en `$request->validate()`
4. **Validaciones**: Verificar reglas de validación y mensajes de error
5. **Modelo**: Revisar `$fillable` en el modelo - campos deben estar listados
6. **Migración**: Verificar que la tabla existe con `php artisan migrate:status`
7. **Estructura de tabla**: Verificar que las columnas coinciden con los campos del formulario

### Si no se puede actualizar

**Revisar en este orden:**
1. **Método edit()**: Verificar que carga el registro correctamente
2. **Formulario de edición**: Verificar que envía método PUT/PATCH
3. **Campo oculto**: Verificar que incluye `<input type="hidden" name="_method" value="PUT">`
4. **Método update()**: Revisar validaciones y lógica de actualización
5. **Ruta**: Verificar que la ruta PUT/PATCH existe
6. **ID del registro**: Verificar que el ID se pasa correctamente en la URL
7. **Modelo**: Verificar `$fillable` incluye los campos a actualizar

### Si no se puede eliminar

**Revisar en este orden:**
1. **Método destroy()**: Verificar lógica de eliminación
2. **Ruta DELETE**: Verificar que la ruta DELETE existe
3. **Campo oculto**: Verificar que incluye `<input type="hidden" name="_method" value="DELETE">`
4. **Modelo**: Verificar que no hay restricciones que impidan eliminación
5. **Relaciones**: Revisar foreign keys - puede haber restricciones en base de datos
6. **Cascade**: Si hay relaciones, verificar ON DELETE CASCADE o eliminar manualmente

### Si aparece un error de base de datos

**Revisar en este orden:**
1. **Archivo .env**: Verificar configuración de conexión (DB_DATABASE, DB_USERNAME, etc.)
2. **Conexión configurada**: Ejecutar `php artisan config:cache` para recargar configuración
3. **Nombre de base de datos**: Verificar que la base de datos existe
4. **Usuario y contraseña**: Verificar credenciales (NO mostrar en documentación)
5. **Migraciones**: Ejecutar `php artisan migrate:status` para verificar migraciones aplicadas
6. **Existencia de tabla**: Verificar que la tabla existe en la base de datos
7. **Columnas**: Verificar que las columnas usadas por el modelo existen en la tabla

### Si aparece un error 404

**Revisar en este orden:**
1. **Ruta**: Verificar que la ruta existe en `routes/web.php`
2. **Método HTTP**: Verificar que coincide (GET, POST, PUT, DELETE)
3. **Nombre de ruta**: Verificar que no hay typos en el nombre
4. **Controlador**: Verificar que el controlador y método existen
5. **Enlace/Formulario**: Verificar que el enlace o formulario apunta a la URL correcta
6. **Middleware**: Verificar que el usuario tiene los permisos requeridos (auth, role)

### Si aparece un error 500

**Revisar en este orden:**
1. **storage/logs/laravel.log** - Revisar el error específico
2. **Controlador involucrado** - Verificar sintaxis y lógica
3. **Modelo involucrado** - Verificar relaciones y consultas
4. **Vista involucrada** - Verificar sintaxis Blade
5. **Consulta realizada** - Verificar que la consulta SQL es válida
6. **Permisos** - Verificar permisos de carpetas (storage, bootstrap/cache)

### Si el carrito no funciona

**Revisar en este orden:**
1. **Autenticación**: Verificar que el usuario está autenticado
2. **Carrito activo**: Verificar que existe carrito con estado 'activo' para el usuario
3. **Stock**: Verificar que el producto tiene cantidad suficiente
4. **Estado producto**: Verificar que el producto está 'activo'
5. **Unique constraint**: Verificar que no hay duplicados en detalle_carrito

### Si los pedidos no se generan

**Revisar en este orden:**
1. **Carrito**: Verificar que el carrito tiene ítems
2. **Stock**: Verificar stock de todos los productos antes de confirmar
3. **Transacción**: Verificar que la transacción DB se completa correctamente
4. **Validaciones**: Revisar todas las validaciones del formulario de pedido
5. **Relaciones**: Verificar que se crean correctamente pedido, detalles y pago

---

## 9. FLUJO GENERAL DEL CRUD

### CREATE (Crear)

```
Usuario
↓
Formulario (Vista Blade)
↓
Ruta POST (routes/web.php)
↓
Controlador store()
↓
Validación ($request->validate())
↓
Modelo::create() o new Model() + save()
↓
Base de Datos (INSERT)
↓
Redirección con mensaje de éxito
↓
Vista (index o show)
```

**Ejemplo real - Crear Producto:**
1. Usuario accede a `/admin/productos/create`
2. Vista `admin/productos/create.blade.php` muestra formulario
3. Usuario envía POST a `/admin/productos`
4. Ruta llama a `ProductoController@store()`
5. Controlador valida datos
6. `Producto::create()` inserta en tabla `producto`
7. Si cantidad > 0, `MovimientoInventario::create()` registra entrada
8. Redirección a `/admin/productos` con mensaje de éxito

### READ (Leer/Consultar)

```
Usuario
↓
Ruta GET (routes/web.php)
↓
Controlador index() o show()
↓
Modelo::get(), find(), where()
↓
Base de Datos (SELECT)
↓
Vista con datos (compact)
↓
Usuario ve información
```

**Ejemplo real - Ver Catálogo:**
1. Usuario accede a `/cliente/catalogo`
2. Ruta llama a `CatalogoController@index()`
3. Controlador ejecuta `Producto::where('estado', 'activo')->get()`
4. Modelo consulta tabla `producto`
5. Controlador retorna vista `cliente/catalogo/index` con productos
6. Vista muestra lista de productos al usuario

### UPDATE (Actualizar)

```
Usuario
↓
Ruta GET (edit) - Formulario con datos
↓
Controlador edit() - Carga registro
↓
Vista con datos pre-cargados
↓
Usuario modifica y envía PUT/PATCH
↓
Ruta PUT/PATCH
↓
Controlador update()
↓
Validación
↓
Modelo->update() o asignación + save()
↓
Base de Datos (UPDATE)
↓
Redirección con mensaje de éxito
```

**Ejemplo real - Actualizar Usuario:**
1. Usuario accede a `/admin/usuarios/{usuario}/edit`
2. Controlador `UsuarioController@edit()` carga usuario
3. Vista `admin/usuarios/edit.blade.php` muestra formulario con datos
4. Usuario modifica y envía PUT a `/admin/usuarios/{usuario}`
5. Controlador `UsuarioController@update()` valida
6. `$usuario->update()` actualiza en tabla `usuario`
7. Redirección a `/admin/usuarios` con mensaje de éxito

### DELETE (Eliminar)

```
Usuario
↓
Solicitud DELETE (formulario o enlace)
↓
Ruta DELETE
↓
Controlador destroy()
↓
Verificaciones (no eliminar propio, etc.)
↓
Modelo->delete()
↓
Base de Datos (DELETE)
↓
Redirección con mensaje de éxito
```

**Ejemplo real - Eliminar Usuario:**
1. Usuario hace clic en eliminar (envía DELETE)
2. Ruta DELETE a `/admin/usuarios/{usuario}`
3. Controlador `UsuarioController@destroy()` verifica que no sea propio
4. `$usuario->delete()` elimina de tabla `usuario`
5. Redirección a `/admin/usuarios` con mensaje de éxito

---

## 10. ESTRUCTURA DE CARPETAS

```
egg-express1/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AdminPedidoController.php
│   │   │   ├── Auth/ (controladores de autenticación Breeze)
│   │   │   ├── CarritoController.php
│   │   │   ├── CatalogoController.php
│   │   │   ├── ClientePedidoController.php
│   │   │   ├── Controller.php
│   │   │   ├── InventarioController.php
│   │   │   ├── ProductoController.php
│   │   │   ├── ProfileController.php
│   │   │   ├── ReporteController.php
│   │   │   └── UsuarioController.php
│   │   └── Middleware/
│   │       └── RoleMiddleware.php (middleware de rol personalizado)
│   ├── Models/
│   │   ├── Carrito.php
│   │   ├── DetalleCarrito.php
│   │   ├── DetallePedido.php
│   │   ├── MovimientoInventario.php
│   │   ├── Pago.php
│   │   ├── Pedido.php
│   │   ├── Producto.php
│   │   ├── RecuperacionCuenta.php
│   │   ├── Role.php
│   │   └── Usuario.php
│   ├── Providers/
│   │   └── AppServiceProvider.php
│   └── View/
│       └── Components/
├── bootstrap/
│   ├── app.php (configuración de la aplicación)
│   └── providers.php (registro de service providers)
├── config/
│   ├── app.php
│   ├── auth.php
│   ├── database.php (configuración de base de datos)
│   └── ... (otros archivos de configuración)
├── database/
│   ├── migrations/ (migraciones de base de datos)
│   │   ├── 2026_08_13_160826_create_rol_table.php
│   │   ├── 2026_08_13_160827_create_usuario_table.php
│   │   ├── 2026_08_13_160910_create_producto_table.php
│   │   ├── 2026_08_13_160924_create_movimiento_inventario_table.php
│   │   ├── 2026_08_13_160935_create_carrito_table.php
│   │   ├── 2026_08_13_160946_create_detalle_carrito_table.php
│   │   ├── 2026_08_13_160957_create_pedido_table.php
│   │   ├── 2026_08_13_161009_create_detalle_pedido_table.php
│   │   ├── 2026_08_13_161019_create_pago_table.php
│   │   └── 2026_08_13_161100_create_recuperacion_cuenta_table.php
│   └── seeders/ (seeders de base de datos)
├── documentacion/ (documentación del proyecto)
│   └── DOCUMENTACION_TECNICA_EGG_EXPRESS.md
├── public/ (archivos públicos)
│   └── index.php (punto de entrada)
├── resources/
│   ├── views/
│   │   ├── admin/ (vistas del administrador)
│   │   │   ├── dashboard.blade.php
│   │   │   ├── inventario/
│   │   │   ├── pedidos/
│   │   │   ├── productos/
│   │   │   ├── reportes/
│   │   │   └── usuarios/
│   │   ├── auth/ (vistas de autenticación)
│   │   │   ├── confirm-password.blade.php
│   │   │   ├── forgot-password.blade.php
│   │   │   ├── login.blade.php
│   │   │   ├── register.blade.php
│   │   │   ├── reset-password.blade.php
│   │   │   └── verify-email.blade.php
│   │   ├── cliente/ (vistas del cliente)
│   │   │   ├── dashboard.blade.php
│   │   │   ├── carrito/
│   │   │   ├── catalogo/
│   │   │   └── pedidos/
│   │   ├── components/ (componentes Blade)
│   │   ├── layouts/ (layouts base)
│   │   ├── profile/ (vistas de perfil)
│   │   ├── dashboard.blade.php
│   │   └── welcome.blade.php
│   └── ... (otros recursos)
├── routes/
│   ├── api.php (rutas API - NO UTILIZADO)
│   ├── auth.php (rutas de autenticación)
│   ├── console.php (rutas de consola)
│   └── web.php (rutas web principales)
├── storage/ (archivos de almacenamiento)
│   ├── logs/ (logs de la aplicación)
│   │   └── laravel.log
│   └── ... (otros archivos de almacenamiento)
├── tests/ (tests de la aplicación)
├── .env (variables de entorno)
├── .env.example (ejemplo de variables de entorno)
├── artisan (CLI de Laravel)
├── composer.json (dependencias PHP)
├── composer.lock (versiones de dependencias)
├── package.json (dependencias Node.js)
├── phpunit.xml (configuración de tests)
├── README.md (documentación general)
└── vite.config.js (configuración de Vite)
```

---

## 11. TABLA DE REFERENCIA RÁPIDA

| Módulo | Ruta | Controlador | Modelo | Tabla | CRUD |
|--------|------|-------------|--------|-------|------|
| Usuarios | admin.usuarios.* | UsuarioController | Usuario | usuario | C, R, U, D |
| Productos | admin.productos.* | ProductoController | Producto | producto | C, R, U, D (toggle) |
| Inventario | admin.inventario.* | InventarioController | MovimientoInventario | movimiento_inventario | C, R |
| Pedidos (Admin) | admin.pedidos.* | AdminPedidoController | Pedido | pedido | R, U (estado) |
| Pedidos (Cliente) | cliente.pedidos.* | ClientePedidoController | Pedido | pedido | C, R, D (cancelar) |
| Carrito | cliente.carrito.* | CarritoController | Carrito, DetalleCarrito | carrito, detalle_carrito | C, R, U, D |
| Catálogo | cliente.catalogo.* | CatalogoController | Producto | producto | R |
| Reportes | admin.reportes.* | ReporteController | Pedido, MovimientoInventario, Pago | pedido, movimiento_inventario, pago | R |
| Autenticación | login, register | Auth Controllers | Usuario | usuario | C (register), R (login) |

**Leyenda:**
- C = CREATE (Crear)
- R = READ (Leer/Consultar)
- U = UPDATE (Actualizar)
- D = DELETE (Eliminar)

---

## 12. RECOMENDACIONES DE MANTENIMIENTO

### Copias de Seguridad
- **Base de datos**: Realizar copias de seguridad periódicas de la base de datos
- **Archivos**: Copiar la carpeta `storage/` que contiene logs y archivos subidos
- **Código**: Usar Git para versionar el código del proyecto

### Revisión de Logs
- **Errores**: Revisar `storage/logs/laravel.log` cuando aparezca un error
- **Frecuencia**: Revisar logs regularmente para detectar problemas temprano
- **Limpieza**: Rotar o limpiar logs antiguos periódicamente

### Modificaciones de Base de Datos
- **No modificar directamente**: Evitar modificar la base de datos directamente sin conocer las relaciones
- **Usar migraciones**: Siempre usar migraciones para modificar la estructura de tablas
- **Revisar relaciones**: Antes de eliminar tablas o columnas, revisar las relaciones y foreign keys
- **Backup**: Hacer backup antes de realizar cambios estructurales

### Modificaciones de Código
- **Verificar rutas**: Antes de modificar controladores, verificar las rutas que los utilizan
- **Verificar relaciones**: Antes de eliminar información, revisar las relaciones entre modelos
- **Probar cambios**: Probar el CRUD después de realizar cambios en controladores o modelos
- **Validaciones**: Mantener las validaciones para asegurar integridad de datos

### Archivo .env
- **No modificar sin conocimiento**: No modificar el archivo .env sin conocer la configuración
- **Credenciales**: No compartir credenciales sensibles (DB_PASSWORD, APP_KEY)
- **Entorno**: Mantener configuraciones diferentes para desarrollo y producción
- **Backup**: Mantener copia del archivo .env.example como referencia

### Buenas Prácticas
- **Nombres descriptivos**: Usar nombres descriptivos para variables, métodos y clases
- **Comentarios**: Mantener comentarios en código complejo para facilitar mantenimiento
- **Consistencia**: Mantener consistencia en el estilo de código
- **Testing**: Probar funcionalidades después de cada cambio importante

### Actualizaciones
- **Dependencias**: Mantener dependencias de PHP (Composer) actualizadas
- **Laravel**: Revisar actualizaciones de Laravel y sus implicaciones
- **Seguridad**: Aplicar parches de seguridad oportunamente
- **Backup**: Hacer backup antes de actualizar dependencias mayores

### Monitoreo
- **Performance**: Monitorear el rendimiento de la aplicación
- **Errores**: Establecer alertas para errores críticos
- **Uso**: Monitorear el uso de recursos (CPU, memoria, almacenamiento)
- **Logs**: Implementar sistema de monitoreo de logs

---

## ESTADO DE IMPLEMENTACIÓN

### Módulos Implementados
- ✅ **Autenticación**: Login, registro, recuperación de contraseña (IMPLEMENTADO)
- ✅ **Gestión de usuarios**: CRUD completo para administrador (IMPLEMENTADO)
- ✅ **Gestión de productos**: CRUD completo para administrador (IMPLEMENTADO)
- ✅ **Inventario**: Registro de movimientos y consulta de historial (IMPLEMENTADO)
- ✅ **Catálogo**: Consulta de productos por clientes (IMPLEMENTADO)
- ✅ **Carrito**: Agregar, actualizar, eliminar ítems (IMPLEMENTADO)
- ✅ **Pedidos (Cliente)**: Crear, consultar, cancelar pedidos (IMPLEMENTADO)
- ✅ **Pedidos (Admin)**: Consultar todos los pedidos, actualizar estado de entrega (IMPLEMENTADO)
- ✅ **Reportes**: Reportes con filtros de fecha y exportación PDF (IMPLEMENTADO)

### Funcionalidades Especiales Implementadas
- ✅ **Middleware de rol**: Control de acceso por rol (admin/cliente)
- ✅ **Validaciones**: Validaciones de formularios en backend
- ✅ **Transacciones DB**: Uso de transacciones para operaciones críticas
- ✅ **Stock automático**: Descuento de stock al crear pedido, reintegro al cancelar
- ✅ **Movimientos automáticos**: Registro automático de movimientos de inventario
- ✅ **Búsqueda en tiempo real**: Búsqueda de productos en catálogo
- ✅ **Exportación PDF**: Generación de reportes en PDF

### No Implementado
- ❌ **API REST**: No hay rutas API implementadas (routes/api.php vacío)
- ❌ **Paginación**: No se implementa paginación en listados
- ❌ **Notificaciones**: No hay sistema de notificaciones
- ❌ **Calificaciones**: No hay sistema de calificaciones de productos
- ❌ **Favoritos**: No hay lista de favoritos para clientes

---

**Documento generado automáticamente basado en el análisis del código fuente del proyecto EGG EXPRESS.**

**Fecha de generación**: Agosto 2026

**Versión de Laravel**: 11.x

**Motor de base de datos**: MySQL/MariaDB
