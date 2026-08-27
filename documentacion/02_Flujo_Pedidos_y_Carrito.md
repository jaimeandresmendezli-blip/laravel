# Flujo 2: Gestión de Pedidos y Carrito de Compras

Este documento detalla la trazabilidad del proceso desde que el cliente selecciona productos hasta que el administrador gestiona el pedido.

## 1. Archivos Involucrados

### Vistas
- **`resources/views/cliente/catalogo/index.blade.php`**: Catálogo de productos disponibles para el cliente.
- **`resources/views/cliente/carrito/index.blade.php`**: Vista del carrito con el subtotal y productos.
- **`resources/views/admin/pedidos/index.blade.php`**: Gestión de pedidos por parte del administrador.

### Rutas (Puntos de Entrada en `routes/web.php`)
- **Grupo `cliente.`**: Rutas para visualizar el catálogo (`/cliente/catalogo`), gestionar el carrito (`/cliente/carrito/*`) y confirmar el pedido (`/cliente/pedidos`).
- **Grupo `admin.`**: Rutas para que el administrador vea y cambie los estados de los pedidos (`/admin/pedidos`).


### Controladores
- **`app/Http/Controllers/CarritoController.php`**: Lógica para agregar, actualizar y eliminar ítems del carrito.
- **`app/Http/Controllers/ClientePedidoController.php`**: Convierte el carrito en un pedido formal.
- **`app/Http/Controllers/AdminPedidoController.php`**: Permite al administrador cambiar el estado del pedido (Pendiente, En camino, Entregado).

### Modelos
- **`app/Models/Carrito.php`** y **`DetalleCarrito.php`**
- **`app/Models/Pedido.php`** y **`DetallePedido.php`**

---

## 2. Trazabilidad: Agregar al Carrito

1. **Entrada (Vista):** El cliente navega por `catalogo/index.blade.php` y hace clic en "Agregar al carrito" en un producto.
2. **Procesamiento (Controlador):** `CarritoController@agregar` recibe la petición.
   - Verifica si el producto ya existe en el carrito activo del usuario.
   - Crea un nuevo `DetalleCarrito` o actualiza la cantidad del existente.
3. **Persistencia (Modelo):** `DetalleCarrito` guarda en la base de datos.
4. **Salida (Vista):** Redirige al cliente de vuelta al catálogo o al carrito con un mensaje de confirmación.

---

## 3. Trazabilidad: Finalización de la Compra (Checkout)

1. **Entrada (Vista):** En `carrito/index.blade.php`, el cliente confirma la compra.
2. **Procesamiento (Controlador):** `ClientePedidoController@store` recibe la petición.
   - Se abre una **Transacción de Base de Datos** (`DB::beginTransaction()`).
   - Crea un nuevo registro en `Pedido`.
   - Transfiere los ítems de `DetalleCarrito` a `DetallePedido`.
   - Vacía el carrito actual del usuario.
3. **Persistencia (Modelo):** Se guardan los datos relacionados al pedido y se ejecutan movimientos de inventario si corresponde.
4. **Salida (Vista):** Se notifica al cliente que el pedido fue creado y se muestra en `pedidos.index` (Cliente).

---

## 4. Trazabilidad: Gestión Administrativa

1. **Entrada (Vista):** El administrador revisa `pedidos/index.blade.php`.
2. **Procesamiento (Controlador):** `AdminPedidoController@actualizarEstadoEntrega`.
3. **Persistencia (Modelo):** Se actualiza el estado del `Pedido` en la BD.
4. **Salida:** La vista se actualiza mostrando el nuevo estado.
