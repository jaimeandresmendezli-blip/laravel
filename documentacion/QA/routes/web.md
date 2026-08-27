# Documentación de Rutas

Este archivo describe la estructura y configuración de las rutas de la aplicación ubicadas en `routes/web.php` y `routes/auth.php`. En Laravel, las rutas actúan como el punto de entrada que dirige las peticiones HTTP hacia el controlador correspondiente.

## 1. Archivos Involucrados
- **`routes/web.php`**: Contiene todas las rutas principales de la aplicación (clientes y administradores).
- **`routes/auth.php`**: Generado por Breeze, contiene las rutas exclusivas de autenticación.

## 2. Estructura de `routes/web.php`

Las rutas están agrupadas y protegidas mediante **Middlewares**. Esto asegura que solo los usuarios autorizados accedan a secciones específicas.

### 2.1 Ruta Principal Pública
- `GET /` -> Renderiza la vista `welcome` de bienvenida.

### 2.2 Grupo del Administrador (`prefix: admin`)
Este grupo está protegido por los middlewares `auth` y `role:admin`. Todas las rutas aquí dentro comienzan con `/admin/` y tienen el nombre de prefijo `admin.`.

- **Dashboard**: `GET /dashboard` -> Renderiza `admin.dashboard`.
- **Usuarios (CRUD)**: `Route::resource('usuarios', UsuarioController::class)` y ruta PATCH para activar/desactivar.
- **Productos (CRUD)**: `Route::resource('productos', ProductoController::class)` y ruta PATCH para cambiar estado.
- **Inventario**: Rutas GET y POST hacia `InventarioController` para visualizar y actualizar el stock.
- **Pedidos**: Rutas GET y PATCH en `AdminPedidoController` para consultar pedidos y actualizar su estado logístico.
- **Reportes**: Rutas GET en `ReporteController` para ver y exportar a PDF.

### 2.3 Grupo del Cliente (`prefix: cliente`)
Protegido por `auth` y `role:cliente`. Rutas con prefijo `/cliente/` y nombre `cliente.`.

- **Dashboard**: `GET /dashboard` -> Renderiza `cliente.dashboard`.
- **Catálogo**: `GET /catalogo` en `CatalogoController` para listar productos a la venta.
- **Carrito**: Rutas GET, POST, PATCH, DELETE en `CarritoController` para gestionar los productos pre-seleccionados.
- **Pedidos**: Rutas GET, POST, DELETE en `ClientePedidoController` para formalizar la compra y ver historial.

## 3. Seguridad
- Si un usuario no está autenticado e intenta acceder a rutas de `admin` o `cliente`, el middleware `auth` lo redirige automáticamente a la pantalla de login.
- Si un cliente autenticado intenta entrar a `/admin/dashboard`, el middleware `role:admin` lo bloqueará (generalmente un error 403 Forbidden).
