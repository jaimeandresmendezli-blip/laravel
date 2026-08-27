# Flujo 3: Inventario y Catálogo de Productos

Este documento describe la trazabilidad de los productos (huevos) desde su ingreso al inventario hasta su visualización en el catálogo público.

## 1. Archivos Involucrados

### Vistas
- **`resources/views/admin/productos/index.blade.php`**: CRUD de productos.
- **`resources/views/admin/inventario/index.blade.php`**: Gestión de entradas y salidas manuales.
- **`resources/views/cliente/catalogo/index.blade.php`**: Visualización de los productos activos.

### Rutas (Puntos de Entrada en `routes/web.php`)
- **Grupo `admin.`**: Rutas de recursos para productos (`Route::resource('productos')`) e inventario (`/admin/inventario`).
- **Grupo `cliente.`**: Ruta para visualizar los productos en catálogo (`/cliente/catalogo`).

### Controladores
- **`app/Http/Controllers/ProductoController.php`**: CRUD de productos.
- **`app/Http/Controllers/InventarioController.php`**: Kardex y movimientos.
- **`app/Http/Controllers/CatalogoController.php`**: Listado de productos para clientes.

### Modelos
- **`app/Models/Producto.php`**: Representa los productos.
- **`app/Models/MovimientoInventario.php`**: Historial de movimientos.

---

## 2. Trazabilidad: Creación de Producto

1. **Entrada (Vista):** El admin usa `productos/index.blade.php`.
2. **Procesamiento (Controlador):** `ProductoController@store` valida los datos e imagen.
3. **Persistencia (Modelo):** `Producto` crea el registro en la BD.
4. **Salida:** Se retorna al listado de productos con éxito.

---

## 3. Trazabilidad: Movimiento de Inventario

1. **Entrada (Vista):** En `inventario/index.blade.php`, el admin registra un ingreso de cubetas.
2. **Procesamiento (Controlador):** `InventarioController@store` procesa el movimiento.
3. **Persistencia (Modelo):** Se registra en `MovimientoInventario` y se actualiza el stock actual del `Producto`.
4. **Salida:** Reflejo inmediato del nuevo stock en las vistas de administrador y catálogo.
