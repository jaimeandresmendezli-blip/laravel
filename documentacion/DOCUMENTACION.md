# Documentación del Proyecto: Egg Express (Laravel)

## NUEVA DOCUMENTACIÓN: Basada en Flujos de Negocio

El sistema está construido bajo la arquitectura **MVC** utilizando **Laravel (PHP)** y **Eloquent ORM** para la gestión de la base de datos MySQL.

**COMIENZA AQUÍ:** [01_Flujo_Autenticacion.md](01_Flujo_Autenticacion.md)

### Documentación por Flujos

Hemos estructurado la documentación en base a las historias de usuario y flujos principales de la aplicación:

- [01_Flujo_Autenticacion.md](01_Flujo_Autenticacion.md) - Login, registro, gestión de roles y administración de usuarios.
- [02_Flujo_Pedidos_y_Carrito.md](02_Flujo_Pedidos_y_Carrito.md) - Proceso de compra, carrito y gestión de pedidos (Admin/Cliente).
- [03_Flujo_Inventario_y_Productos.md](03_Flujo_Inventario_y_Productos.md) - Gestión del catálogo y movimientos de inventario.

---

## 📁 Estructura de Directorios (Laravel)

- **`app/Http/Controllers`**: Lógica de negocio y controladores divididos por roles (Admin/Cliente).
- **`app/Models`**: Modelos Eloquent y relaciones de base de datos.
- **`resources/views`**: Interfaces gráficas desarrolladas con Blade (`.blade.php`).
- **`routes/web.php`**: Enrutador central con protección mediante middlewares (`auth`, `role`).
- **`documentacion/`**: Archivos con la documentación técnica del proyecto.

---

## 🛠️ 1. Configuración Principal

### Base de Datos (`.env`)
Laravel usa el archivo `.env` en la raíz del proyecto para definir las credenciales de conexión con MySQL.

---

## 🧠 2. Principales Modelos (`app/Models`)

- **`Usuario.php` / `Role.php`**: Gestión de roles y usuarios.
- **`Producto.php`**: Catálogo de productos.
- **`Pedido.php` / `DetallePedido.php`**: Compras realizadas.
- **`Carrito.php` / `DetalleCarrito.php`**: Carrito temporal de compras.
- **`MovimientoInventario.php`**: Historial de kardex.

---

## ⚙️ 3. Controladores Clave (`app/Http/Controllers`)

- **Breeze Controllers**: Autenticación.
- **`AdminPedidoController` / `ClientePedidoController`**: Gestión separada por rol para la seguridad.
- **`CarritoController`**: Gestión de compras pendientes del usuario.
- **`InventarioController`**: Administración de stock.

---

## 🔒 Seguridad Implementada
1. **Validación de Middlewares**: Rutas protegidas para requerir autenticación (`auth`) y validar el rol (`role:admin` / `role:cliente`).
2. **Eloquent ORM**: Uso nativo de Laravel para prevenir inyección SQL.
3. **Blade y XSS**: Laravel escapa automáticamente todas las variables impresas en Blade (`{{ $var }}`) protegiendo contra Cross-Site Scripting.
4. **Validaciones Form Request**: Todas las entradas son sanitizadas y validadas por las reglas de validación de Laravel antes de tocar la BD.
