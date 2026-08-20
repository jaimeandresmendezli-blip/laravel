# DOCUMENTACIÓN MÓDULO: CATÁLOGO (US-0007)

**Sistema EGG EXPRESS - Catálogo de Productos**

---

## 1. INFORMACIÓN GENERAL

- **Código de Historia de Usuario**: US-0007
- **Módulo**: Catálogo de Productos
- **Rol con acceso**: Cliente
- **Estado**: IMPLEMENTADO
- **Fecha de documentación**: Agosto 2026

---

## 2. TRAZABILIDAD COMPLETA

### 2.1 Rutas (routes/web.php)

```php
Route::prefix('cliente')
    ->name('cliente.')
    ->middleware(['auth', 'role:cliente'])
    ->group(function () {
        // US-0007 — Catálogo
        Route::get('catalogo', [CatalogoController::class, 'index'])->name('catalogo.index');
        Route::get('catalogo/{producto}', [CatalogoController::class, 'show'])->name('catalogo.show');
    });
```

**Rutas del módulo:**

| Método HTTP | Ruta | Nombre de Ruta | Controlador | Método | Función |
|-------------|------|----------------|-------------|--------|---------|
| GET | `/cliente/catalogo` | cliente.catalogo.index | CatalogoController | index() | Ver catálogo de productos |
| GET | `/cliente/catalogo/{producto}` | cliente.catalogo.show | CatalogoController | show() | Ver detalle de producto |

**NOTA**: No usa Route::resource, usa rutas individuales. Solo clientes pueden acceder al catálogo. Solo muestra productos activos.

---

### 2.2 Controlador (app/Http/Controllers/CatalogoController.php)

**Archivo**: `app/Http/Controllers/CatalogoController.php`

**Modelos utilizados:**
- `App\Models\Producto`

**Métodos del controlador:**

#### index()
```php
public function index(Request $request)
{
    $query = Producto::where('estado', 'activo');

    if ($request->filled('buscar')) {
        $buscar = $request->buscar;
        $query->where(function ($q) use ($buscar) {
            $q->where('nombre', 'like', "%{$buscar}%")
              ->orWhere('tipo_huevo', 'like', "%{$buscar}%")
              ->orWhere('presentacion', 'like', "%{$buscar}%")
              ->orWhere('descripcion', 'like', "%{$buscar}%");
        });
    }

    $productos = $query->orderBy('nombre')->get();

    return view('cliente.catalogo.index', compact('productos'));
}
```
- **Función**: Lista productos activos con búsqueda en tiempo real
- **Consulta base**: `Producto::where('estado', 'activo')`
- **Filtro de búsqueda**: Si existe parámetro `buscar`, busca en:
  - nombre
  - tipo_huevo
  - presentacion
  - descripcion
- **Búsqueda**: LIKE con comodines %...% (búsqueda parcial)
- **Ordenamiento**: `orderBy('nombre')` - alfabético
- **Vista**: `cliente.catalogo.index`
- **Datos enviados**: `$productos` (colección de productos activos)

#### show()
```php
public function show(Producto $producto)
{
    // Verificar que el producto esté activo
    if ($producto->estado !== 'activo') {
        abort(404);
    }

    return view('cliente.catalogo.show', compact('producto'));
}
```
- **Función**: Muestra detalle de un producto activo
- **Parámetro**: `$producto` (inyectado por route model binding)
- **Validación**: Verifica que el producto esté 'activo'
- **Si inactivo**: `abort(404)` - página no encontrada
- **Vista**: `cliente.catalogo.show`
- **Datos enviados**: `$producto`

---

### 2.3 Modelo (app/Models/Producto.php)

**Archivo**: `app/Models/Producto.php`

**Configuración:**
```php
protected $table = 'producto';
protected $primaryKey = 'id_producto';
public $timestamps = false;

protected $fillable = [
    'nombre', 'tipo_huevo', 'presentacion', 'descripcion',
    'precio', 'cantidad', 'imagen', 'estado',
];

protected $casts = [
    'precio'   => 'decimal:2',
    'cantidad' => 'integer',
];
```

**Relaciones:**
```php
public function movimientos()
{
    return $this->hasMany(MovimientoInventario::class, 'id_producto', 'id_producto');
}

public function detallesCarrito()
{
    return $this->hasMany(DetalleCarrito::class, 'id_producto', 'id_producto');
}

public function detallesPedido()
{
    return $this->hasMany(DetallePedido::class, 'id_producto', 'id_producto');
}
```

---

### 2.4 Base de Datos

**Tabla**: `producto`

**Migración**: `database/migrations/2026_08_13_160910_create_producto_table.php`

**Estructura:**
```php
Schema::create('producto', function (Blueprint $table) {
    $table->integer('id_producto')->autoIncrement();
    $table->string('nombre', 100);
    $table->string('tipo_huevo', 50)->nullable();
    $table->string('presentacion', 50)->nullable();
    $table->text('descripcion')->nullable();
    $table->decimal('precio', 10, 2);
    $table->integer('cantidad')->default(0);
    $table->string('imagen', 255)->nullable();
    $table->enum('estado', ['activo', 'inactivo'])->default('activo');
});
```

**Campos:**
- `id_producto` - Primary key, auto increment
- `nombre` - Nombre del producto (ej: "Huevo Blanco")
- `tipo_huevo` - Tipo de huevo (ej: "Gallina", "Codorniz")
- `presentacion` - Presentación (ej: "Docena", "Cartón 30")
- `descripcion` - Descripción detallada
- `precio` - Precio unitario (decimal 10,2)
- `cantidad` - Stock disponible (integer, default 0)
- `imagen` - Ruta de la imagen en storage
- `estado` - Enum: 'activo', 'inactivo'

---

### 2.5 Vistas (resources/views/cliente/catalogo/)

#### index.blade.php
- **Ubicación**: `resources/views/cliente/catalogo/index.blade.php`
- **Función**: Lista productos activos con búsqueda
- **Datos recibidos**: `$productos` (colección de productos activos)
- **Elementos**:
  - Formulario de búsqueda:
    - Campo de texto "buscar"
    - Búsqueda en tiempo real (AJAX o submit)
  - Grid de productos:
    - Imagen del producto
    - Nombre
    - Tipo de huevo
    - Presentación
    - Precio
    - Stock disponible
    - Botón: Ver detalle
    - Botón: Agregar al carrito
  - Mensaje si no hay resultados de búsqueda

#### show.blade.php
- **Ubicación**: `resources/views/cliente/catalogo/show.blade.php`
- **Función**: Muestra detalle completo de un producto activo
- **Datos recibidos**: `$producto`
- **Elementos**:
  - Imagen del producto (grande)
  - Nombre
  - Tipo de huevo
  - Presentación
  - Descripción
  - Precio
  - Stock disponible
  - Información adicional (si aplica)
  - Botón: Agregar al carrito
  - Botón: Volver al catálogo
  - Cantidad a agregar (input number)

---

## 3. FLUJO COMPLETO DE OPERACIONES

### 3.1 FLUJO READ (Ver Catálogo)

```
1. Usuario (Cliente)
   ↓
2. Accede a: /cliente/catalogo
   ↓
3. Ruta: cliente.catalogo.index (GET)
   ↓
4. Controlador: CatalogoController@index($request)
    ↓
5. Consulta base: Producto::where('estado', 'activo')
    ↓
6. Si existe parámetro buscar:
    ↓
7. - Aplicar búsqueda en nombre, tipo_huevo, presentacion, descripcion
    ↓
8. - WHERE nombre LIKE %buscar% OR tipo_huevo LIKE %buscar% OR ...
    ↓
9. Ordenamiento: orderBy('nombre')
    ↓
10. Consulta: $query->get()
    ↓
11. Vista: cliente.catalogo.index
    ↓
12. Usuario ve lista de productos activos
```

**Consulta SQL generada (sin búsqueda):**
```sql
SELECT * FROM producto 
WHERE estado = 'activo' 
ORDER BY nombre ASC
```

**Consulta SQL generada (con búsqueda):**
```sql
SELECT * FROM producto 
WHERE estado = 'activo' 
AND (
    nombre LIKE '%buscar%' 
    OR tipo_huevo LIKE '%buscar%' 
    OR presentacion LIKE '%buscar%' 
    OR descripcion LIKE '%buscar%'
) 
ORDER BY nombre ASC
```

---

### 3.2 FLUJO READ (Ver Detalle de Producto)

```
1. Usuario (Cliente)
   ↓
2. Hace clic en "Ver detalle" de un producto
   ↓
3. Accede a: /cliente/catalogo/{id}
   ↓
4. Ruta: cliente.catalogo.show (GET)
   ↓
5. Controlador: CatalogoController@show($producto)
    ↓
6. Route model binding: Producto::findOrFail($id)
    ↓
7. Validación: if ($producto->estado !== 'activo')
    ↓
8. Si inactivo → abort(404)
    ↓
9. Si activo → Vista: cliente.catalogo.show
    ↓
10. Usuario ve detalle del producto
```

**Validación de estado:**
```php
if ($producto->estado !== 'activo') {
    abort(404);
}
```

Esto previene que los clientes accedan a productos inactivos directamente por URL.

---

### 3.3 FLUJO CREATE (Agregar al Carrito)

```
NOTA: Esta operación NO está en CatalogoController.

El catálogo solo muestra productos.
La acción de agregar al carrito se maneja en CarritoController@agregar().

Desde el catálogo, el formulario envía POST a /cliente/carrito/agregar.
```

**Flujo desde catálogo:**
```
1. Usuario (Cliente)
   ↓
2. En catálogo, hace clic en "Agregar al carrito"
   ↓
3. Formulario envía POST a: /cliente/carrito/agregar
   ↓
4. Ruta: cliente.carrito.agregar (POST)
   ↓
5. Controlador: CarritoController@agregar()
   ↓
6. ... (ver documentación del módulo Carrito)
```

---

## 4. PUNTOS DE ATENCIÓN PARA MANTENIMIENTO

### 4.1 Si no se ven los productos
**Revisar en orden:**
1. Vista en `resources/views/cliente/catalogo/index.blade.php` - Verificar iteración
2. Consulta en `CatalogoController@index()` - Verificar where('estado', 'activo')
3. Estado de productos - Verificar que haya productos con estado 'activo'
4. Autenticación - Verificar que el usuario esté autenticado
5. Rol - Verificar que el usuario tenga rol 'cliente'

### 4.2 Si la búsqueda no funciona
**Revisar en orden:**
1. Formulario de búsqueda en index.blade.php - Verificar nombre del campo
2. Método `filled()` en controlador - Verificar que detecte parámetro
3. Consulta where() - Verificar lógica de búsqueda
4. Comodines LIKE - Verificar que use "%{$buscar}%"
5. Campos de búsqueda - Verificar que incluya todos los campos necesarios

### 4.3 Si se ven productos inactivos
**Revisar en orden:**
1. Consulta en index() - Verificar where('estado', 'activo')
2. Validación en show() - Verificar if ($producto->estado !== 'activo')
3. Estado de productos en base de datos - Verificar valores reales
4. Enum de estado - Verificar que sea 'activo'/'inactivo'

### 4.4 Si no se puede ver el detalle
**Revisar en orden:**
1. Validación en show() - Verificar abort(404) para inactivos
2. Route model binding - Verificar que el ID se pase correctamente
3. Vista show.Blade.php - Verificar acceso a $producto
4. Ruta show - Verificar que exista y apunte al método correcto

### 4.5 Si las imágenes no se muestran
**Revisar en orden:**
1. Campo imagen en tabla producto - Verificar que tenga datos
2. Enlace simbólico public/storage - Ejecutar `php artisan storage:link`
3. Permisos de storage - Verificar que sea accesible
4. Ruta de la imagen en vista - Verificar que use asset() o Storage::url()

### 4.6 Si el stock no se muestra
**Revisar en orden:**
1. Campo cantidad en tabla producto - Verificar que tenga datos
2. Vista index.blade.php - Verificar que muestre $producto->cantidad
3. Vista show.blade.php - Verificar que muestre $producto->cantidad
4. Cast de cantidad en modelo - Verificar que sea integer

---

## 5. RELACIONES CON OTROS MÓDULOS

### 5.1 Relación con Módulo Carrito
- Los productos del catálogo se agregan al carrito
- `CarritoController@agregar()` recibe id_producto desde catálogo
- Solo productos activos pueden agregarse
- El catálogo muestra botón "Agregar al carrito"

### 5.2 Relación con Módulo Productos (Admin)
- Los administradores gestionan los productos
- Los administradores activan/desactivan productos
- Solo productos activos aparecen en catálogo
- Los cambios de precio se reflejan inmediatamente

### 5.3 Relación con Módulo Inventario
- El catálogo muestra stock disponible
- El stock viene del campo cantidad en producto
- El stock se actualiza por movimientos de inventario
- Stock insuficiente impide agregar al carrito

### 5.4 Relación con Módulo Pedidos
- Los productos del catálogo se compran en pedidos
- Los precios del catálogo se usan al crear pedido
- El stock se descuenta al crear pedido
- Solo productos activos pueden comprarse

### 5.5 Relación con Módulo Reportes
- Los reportes incluyen información de productos vendidos
- Se cuentan productos por estado de pedido
- Se relacionan con movimientos de inventario

---

## 6. ESTADO DE IMPLEMENTACIÓN

| Funcionalidad | Estado | Observaciones |
|--------------|--------|---------------|
| Ver catálogo de productos | ✅ IMPLEMENTADO | Solo productos activos |
| Búsqueda de productos | ✅ IMPLEMENTADO | En nombre, tipo, presentación, descripción |
| Ver detalle de producto | ✅ IMPLEMENTADO | Solo productos activos |
| Mostrar stock disponible | ✅ IMPLEMENTADO | Campo cantidad |
| Mostrar precio | ✅ IMPLEMENTADO | Campo precio |
| Mostrar imagen | ✅ IMPLEMENTADO | Campo imagen |
| Agregar al carrito | ✅ IMPLEMENTADO | Redirige a CarritoController |
| Filtrar por categoría | ❌ NO IMPLEMENTADO | No hay categorías |
| Ordenar por precio | ❌ NO IMPLEMENTADO | Solo ordena por nombre |
| Ordenar por stock | ❌ NO IMPLEMENTADO | Solo ordena por nombre |
| Paginación | ❌ NO IMPLEMENTADO | Muestra todos los productos |
| Comparar productos | ❌ NO IMPLEMENTADO | No hay función de comparación |
| Favoritos | ❌ NO IMPLEMENTADO | No hay lista de favoritos |
| Reseñas | ❌ NO IMPLEMENTADO | No hay sistema de reseñas |

---

## 7. ARCHIVOS RELACIONADOS

### Controladores
- `app/Http/Controllers/CatalogoController.php` - Controlador principal
- `app/Http/Controllers/CarritoController.php` - Controlador de carrito (agregar)
- `app/Http/Controllers/ProductoController.php` - Controlador de productos (admin)

### Modelos
- `app/Models/Producto.php` - Modelo principal
- `app/Models/DetalleCarrito.php` - Modelo de detalles en carrito
- `app/Models/DetallePedido.php` - Modelo de detalles en pedidos

### Vistas
- `resources/views/cliente/catalogo/index.blade.php` - Lista de productos
- `resources/views/cliente/catalogo/show.blade.php` - Detalle de producto
- `resources/views/admin/productos/index.blade.php` - Gestión de productos (admin)

### Migraciones
- `database/migrations/2026_08_13_160910_create_producto_table.php` - Tabla producto

### Rutas
- `routes/web.php` - Rutas del módulo (líneas 75-77)

---

## 8. CARACTERÍSTICAS ESPECIALES

### 8.1 Solo Productos Activos
- El catálogo filtra automáticamente por estado 'activo'
- `Producto::where('estado', 'activo')`
- Los productos inactivos no son visibles para clientes
- Esto permite a administradores desactivar productos sin eliminarlos

### 8.2 Búsqueda en Múltiples Campos
- La búsqueda busca en 4 campos simultáneamente:
  - nombre
  - tipo_huevo
  - presentacion
  - descripcion
- Usa OR lógico: si encuentra en cualquiera, muestra el producto
- Búsqueda parcial con LIKE %...%
- Esto facilita encontrar productos sin saber exactamente dónde buscar

### 8.3 Validación de Estado en Detalle
- Al ver detalle, se valida que el producto esté activo
- `if ($producto->estado !== 'activo') abort(404)`
- Esto previene acceso directo por URL a productos inactivos
- Mantiene consistencia entre lista y detalle

### 8.4 Ordenamiento Alfabético
- Los productos se ordenan alfabéticamente por nombre
- `orderBy('nombre')`
- Esto facilita encontrar productos específicos
- No hay opción de cambiar el ordenamiento

### 8.5 Información Completa
- El catálogo muestra toda la información relevante:
  - Imagen
  - Nombre
  - Tipo de huevo
  - Presentación
  - Descripción
  - Precio
  - Stock disponible
- Esto permite al cliente tomar decisiones informadas

### 8.6 Integración con Carrito
- Desde el catálogo se puede agregar directamente al carrito
- El formulario envía a CarritoController@agregar()
- Se verifica stock antes de agregar
- Se muestra mensaje de éxito/error

---

## 9. ESCENARIOS DE USO

### 9.1 Escenario 1: Ver Catálogo Completo
```
1. Cliente accede a /cliente/catalogo
2. Sistema muestra todos los productos activos
3. Productos ordenados alfabéticamente
4. Cliente ve imagen, nombre, precio, stock
5. Cliente puede hacer clic en "Ver detalle"
6. Cliente puede hacer clic en "Agregar al carrito"
```

### 9.2 Escenario 2: Buscar Producto
```
1. Cliente en catálogo ingresa texto en búsqueda
2. Sistema busca en nombre, tipo, presentación, descripción
3. Sistema muestra productos que coinciden
4. Cliente ve resultados filtrados
5. Cliente puede limpiar búsqueda para ver todos
```

### 9.3 Escenario 3: Ver Detalle de Producto
```
1. Cliente hace clic en "Ver detalle" de un producto
2. Sistema verifica que el producto esté activo
3. Sistema muestra información completa del producto
4. Cliente ve imagen grande, descripción, precio, stock
5. Cliente puede seleccionar cantidad
6. Cliente puede agregar al carrito
```

### 9.4 Escenario 4: Agregar al Carrito desde Catálogo
```
1. Cliente en catálogo hace clic en "Agregar al carrito"
2. Formulario envía POST a /cliente/carrito/agregar
3. Sistema verifica stock disponible
4. Sistema agrega producto al carrito
5. Cliente ve mensaje de éxito
6. Cliente puede continuar comprando
```

### 9.5 Escenario 5: Producto Inactivo
```
1. Cliente intenta acceder a /cliente/catalogo/{id} de producto inactivo
2. Sistema detecta que estado !== 'activo'
3. Sistema muestra error 404
4. Cliente no puede ver el producto
5. El producto no aparece en el catálogo
```

### 9.6 Escenario 6: Sin Resultados de Búsqueda
```
1. Cliente busca un término que no existe
2. Sistema busca en todos los campos
3. Sistema no encuentra coincidencias
4. Sistema muestra mensaje "No se encontraron productos"
5. Cliente puede intentar con otro término
```

---

## 10. REGLAS DE NEGOCIO

### 10.1 Reglas de Visibilidad
- Solo productos activos son visibles en catálogo
- Los productos inactivos no aparecen en lista
- Los productos inactivos no pueden verse por detalle
- Los administradores controlan el estado de los productos

### 10.2 Reglas de Búsqueda
- La búsqueda es parcial (LIKE %...%)
- La búsqueda busca en múltiples campos
- La búsqueda es case-insensitive (dependiendo de BD)
- La búsqueda no requiere coincidencia exacta

### 10.3 Reglas de Acceso
- Solo los clientes pueden acceder al catálogo
- Los administradores no acceden al catálogo (tienen su propia gestión)
- Los usuarios no autenticados no pueden acceder (middleware auth)
- El middleware role:cliente restringe acceso

### 10.4 Reglas de Información
- El precio mostrado es el precio actual del producto
- El stock mostrado es el stock actual en inventario
- La imagen mostrada es la imagen actual del producto
- La información se actualiza en tiempo real

### 10.5 Reglas de Integridad
- Los productos inactivos no pueden comprarse
- Los productos sin stock pueden verse pero no agregarse
- Los productos eliminados no aparecen (cascade delete)
- Los cambios en productos se reflejan inmediatamente

---

**Fin de documentación del módulo Catálogo**
