# DOCUMENTACIÓN MÓDULO: INVENTARIO (US-0005, US-0006)

**Sistema EGG EXPRESS - Gestión de Inventario**

---

## 1. INFORMACIÓN GENERAL

- **Código de Historia de Usuario**: US-0005 (Actualizar Inventario), US-0006 (Consultar Historial)
- **Módulo**: Gestión de Inventario
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
        // US-0005 / US-0006 — Inventario
        Route::get('inventario', [InventarioController::class, 'index'])->name('inventario.index');
        Route::get('inventario/crear', [InventarioController::class, 'create'])->name('inventario.create');
        Route::post('inventario', [InventarioController::class, 'store'])->name('inventario.store');
    });
```

**Rutas del módulo:**

| Método HTTP | Ruta | Nombre de Ruta | Controlador | Método | Función |
|-------------|------|----------------|-------------|--------|---------|
| GET | `/admin/inventario` | admin.inventario.index | InventarioController | index() | Ver historial de movimientos |
| GET | `/admin/inventario/crear` | admin.inventario.create | InventarioController | create() | Formulario movimiento manual |
| POST | `/admin/inventario` | admin.inventario.store | InventarioController | store() | Registrar movimiento manual |

**NOTA**: No usa Route::resource, usa rutas individuales.

---

### 2.2 Controlador (app/Http/Controllers/InventarioController.php)

**Archivo**: `app/Http/Controllers/InventarioController.php`

**Modelos utilizados:**
- `App\Models\MovimientoInventario`
- `App\Models\Producto`

**Métodos del controlador:**

#### index()
```php
public function index(Request $request)
{
    $query = MovimientoInventario::with('producto')->orderBy('fecha', 'desc');

    // Filtro por producto
    if ($request->filled('id_producto')) {
        $query->where('id_producto', $request->id_producto);
    }
    // Filtro por tipo de movimiento
    if ($request->filled('tipo_movimiento')) {
        $query->where('tipo_movimiento', $request->tipo_movimiento);
    }
    // Filtro por fechas
    if ($request->filled('fecha_desde')) {
        $query->whereDate('fecha', '>=', $request->fecha_desde);
    }
    if ($request->filled('fecha_hasta')) {
        $query->whereDate('fecha', '<=', $request->fecha_hasta);
    }

    $movimientos = $query->get();
    $productos   = Producto::orderBy('nombre')->get();

    return view('admin.inventario.index', compact('movimientos', 'productos'));
}
```
- **Función**: Lista historial de movimientos con filtros opcionales
- **Consulta**: `MovimientoInventario::with('producto')->orderBy('fecha', 'desc')`
- **Filtros aplicados**:
  - `id_producto` - Filtra por producto específico
  - `tipo_movimiento` - Filtra por entrada/salida
  - `fecha_desde` - Filtra movimientos desde fecha
  - `fecha_hasta` - Filtra movimientos hasta fecha
- **Consulta adicional**: `Producto::orderBy('nombre')->get()` para select de filtros
- **Vista**: `admin.inventario.index`
- **Datos enviados**: `$movimientos` (historial filtrado), `$productos` (lista para filtros)

#### create()
```php
public function create()
{
    $productos = Producto::where('estado', 'activo')->orderBy('nombre')->get();
    return view('admin.inventario.create', compact('productos'));
}
```
- **Función**: Muestra formulario para registrar movimiento manual
- **Consulta**: `Producto::where('estado', 'activo')->orderBy('nombre')->get()`
- **Filtro**: Solo productos activos
- **Vista**: `admin.inventario.create`
- **Datos enviados**: `$productos` (lista de productos activos para select)

#### store()
```php
public function store(Request $request)
{
    $request->validate([
        'id_producto'     => 'required|exists:producto,id_producto',
        'tipo_movimiento' => 'required|in:entrada,salida',
        'cantidad'        => 'required|integer|min:1',
        'motivo'          => 'required|string|max:255',
    ], [
        'cantidad.min' => 'La cantidad debe ser al menos 1.',
        'motivo.required' => 'El motivo es obligatorio para registrar el movimiento.',
    ]);

    $producto = Producto::findOrFail($request->id_producto);

    // US-0005 escenario 4: verificar stock suficiente en salida
    if ($request->tipo_movimiento === 'salida') {
        if ($producto->cantidad < $request->cantidad) {
            return back()->withErrors([
                'cantidad' => "Stock insuficiente. Disponible: {$producto->cantidad} unidades.",
            ])->withInput();
        }
        // Reducir stock
        $producto->decrement('cantidad', $request->cantidad);
    } else {
        // Incrementar stock en entrada
        $producto->increment('cantidad', $request->cantidad);
    }

    // Registrar movimiento en historial
    MovimientoInventario::create([
        'id_producto'     => $request->id_producto,
        'tipo_movimiento' => $request->tipo_movimiento,
        'cantidad'        => $request->cantidad,
        'motivo'          => $request->motivo,
        'fecha'           => now(),
    ]);

    return redirect()->route('admin.inventario.index')
        ->with('success', 'Movimiento de inventario registrado correctamente.');
}
```
- **Función**: Registra movimiento manual de entrada/salida y actualiza stock
- **Validaciones**: 
  - id_producto: requerido, debe existir en tabla producto
  - tipo_movimiento: requerido, in: entrada,salida
  - cantidad: requerido, integer, min 1
  - motivo: requerido, string, max 255
- **Validación de stock**: Para salidas, verifica que haya stock suficiente
- **Actualización de stock**:
  - Salida: `$producto->decrement('cantidad', $request->cantidad)`
  - Entrada: `$producto->increment('cantidad', $request->cantidad)`
- **Registro de movimiento**: `MovimientoInventario::create()`
- **Redirección**: `admin.inventario.index` con mensaje de éxito

---

### 2.3 Modelo (app/Models/MovimientoInventario.php)

**Archivo**: `app/Models/MovimientoInventario.php`

**Configuración:**
```php
protected $table = 'movimiento_inventario';
protected $primaryKey = 'id_movimiento';
public $timestamps = false;

protected $fillable = [
    'id_producto', 'tipo_movimiento', 'cantidad', 'motivo', 'fecha',
];

protected $casts = [
    'fecha'    => 'datetime',
    'cantidad' => 'integer',
];
```

**Relaciones:**
```php
public function producto()
{
    return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
}
```

---

### 2.4 Modelo Relacionado (app/Models/Producto.php)

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
```

---

### 2.5 Base de Datos

**Tabla**: `movimiento_inventario`

**Migración**: `database/migrations/2026_08_13_160924_create_movimiento_inventario_table.php`

**Estructura:**
```php
Schema::create('movimiento_inventario', function (Blueprint $table) {
    $table->integer('id_movimiento')->autoIncrement();
    $table->integer('id_producto');
    $table->enum('tipo_movimiento', ['entrada', 'salida']);
    $table->integer('cantidad');
    $table->string('motivo', 255);
    $table->dateTime('fecha')->useCurrent();

    $table->foreign('id_producto')->references('id_producto')->on('producto')->onDelete('cascade');
});
```

**Campos:**
- `id_movimiento` - Primary key, auto increment
- `id_producto` - Foreign key a tabla producto
- `tipo_movimiento` - Enum: 'entrada', 'salida'
- `cantidad` - Cantidad movida (integer)
- `motivo` - Razón del movimiento (string 255)
- `fecha` - Fecha del movimiento, current timestamp

**Foreign key**: `id_producto` con ON DELETE CASCADE

**Tabla relacionada**: `producto`

**Estructura:**
```php
Schema::create('producto', function (Blueprint $table) {
    $table->integer('id_producto')->autoIncrement();
    $table->string('nombre', 100);
    $table->string('tipo_huevo', 50)->nullable();
    $table->string('presentacion', 50)->nullable();
    $table->text('descripcion')->nullable++;
    $table->decimal('precio', 10, 2);
    $table->integer('cantidad')->default(0);
    $table->string('imagen', 255)->nullable();
    $table->enum('estado', ['activo', 'inactivo'])->default('activo');
});
```

**Campo importante**: `cantidad` - Stock actual del producto

---

### 2.6 Vistas (resources/views/admin/inventario/)

#### index.blade.php
- **Ubicación**: `resources/views/admin/inventario/index.blade.php`
- **Función**: Lista historial de movimientos con filtros
- **Datos recibidos**: `$movimientos` (historial), `$productos` (lista para filtros)
- **Elementos**:
  - Formulario de filtros:
    - Select de producto
    - Select de tipo de movimiento (entrada/salida)
    - Fecha desde
    - Fecha hasta
  - Tabla de movimientos:
    - ID, Producto, Tipo, Cantidad, Motivo, Fecha
    - Colores diferenciados para entrada (verde) y salida (rojo)
  - Botón: Registrar nuevo movimiento

#### create.blade.php
- **Ubicación**: `resources/views/admin/inventario/create.blade.php`
- **Función**: Formulario para registrar movimiento manual
- **Datos recibidos**: `$productos` (lista de productos activos)
- **Campos del formulario**:
  - id_producto (select, required, solo productos activos)
  - tipo_movimiento (select, required: entrada/salida)
  - cantidad (number, required, min="1")
  - motivo (text, required, max 255)
- **Método**: POST
- **Action**: `route('admin.inventario.store')`

---

## 3. FLUJO COMPLETO DE OPERACIONES

### 3.1 FLUJO CREATE (Registrar Movimiento Manual)

```
1. Usuario (Admin)
   ↓
2. Accede a: /admin/inventario/crear
   ↓
3. Ruta: admin.inventario.create (GET)
   ↓
4. Controlador: InventarioController@create()
   ↓
5. Consulta: Producto::where('estado', 'activo')->orderBy('nombre')->get()
   ↓
6. Vista: admin.inventario.create
   ↓
7. Usuario selecciona producto, tipo, cantidad, motivo
   ↓
8. Envía POST a: /admin/inventario
   ↓
9. Ruta: admin.inventario.store (POST)
   ↓
10. Controlador: InventarioController@store()
    ↓
11. Validación: $request->validate()
    ↓
12. Consulta: Producto::findOrFail($request->id_producto)
    ↓
13. Si tipo_movimiento == 'salida':
    ↓
14. Verificar stock: if ($producto->cantidad < $request->cantidad)
    ↓
15. Si insuficiente → Error y redirección
    ↓
16. Si suficiente → $producto->decrement('cantidad', $request->cantidad)
    ↓
17. Si tipo_movimiento == 'entrada':
    ↓
18. $producto->increment('cantidad', $request->cantidad)
    ↓
19. Modelo: MovimientoInventario::create([...])
    ↓
20. Base de Datos: INSERT INTO movimiento_inventario
    ↓
21. Base de Datos: UPDATE producto SET cantidad = ...
    ↓
22. Redirección: admin.inventario.index
    ↓
23. Vista: admin.inventario.index con mensaje de éxito
```

**Validaciones en CREATE:**
- id_producto: required, exists:producto,id_producto
- tipo_movimiento: required, in:entrada,salida
- cantidad: required, integer, min:1
- motivo: required, string, max:255

**Validación de stock (US-0005 escenario 4):**
```php
if ($request->tipo_movimiento === 'salida') {
    if ($producto->cantidad < $request->cantidad) {
        return back()->withErrors([
            'cantidad' => "Stock insuficiente. Disponible: {$producto->cantidad} unidades.",
        ])->withInput();
    }
    $producto->decrement('cantidad', $request->cantidad);
}
```

**Actualización de stock:**
- Entrada: `$producto->increment('cantidad', $request->cantidad)`
- Salida: `$producto->decrement('cantidad', $request->cantidad)`

---

### 3.2 FLUJO READ (Consultar Historial)

```
1. Usuario (Admin)
   ↓
2. Accede a: /admin/inventario
   ↓
3. Ruta: admin.inventario.index (GET)
   ↓
4. Controlador: InventarioController@index($request)
    ↓
5. Consulta base: MovimientoInventario::with('producto')->orderBy('fecha', 'desc')
    ↓
6. Aplicar filtros (si existen):
    ↓
7. - id_producto: ->where('id_producto', $request->id_producto)
    ↓
8. - tipo_movimiento: ->where('tipo_movimiento', $request->tipo_movimiento)
    ↓
9. - fecha_desde: ->whereDate('fecha', '>=', $request->fecha_desde)
    ↓
10. - fecha_hasta: ->whereDate('fecha', '<=', $request->fecha_hasta)
    ↓
11. Consulta adicional: Producto::orderBy('nombre')->get()
    ↓
12. Vista: admin.inventario.index
    ↓
13. Usuario ve historial filtrado
```

**Consulta SQL generada (sin filtros):**
```sql
SELECT m.*, p.nombre as producto_nombre 
FROM movimiento_inventario m 
LEFT JOIN producto p ON m.id_producto = p.id_producto 
ORDER BY m.fecha DESC
```

**Con filtro de producto:**
```sql
SELECT m.*, p.nombre as producto_nombre 
FROM movimiento_inventario m 
LEFT JOIN producto p ON m.id_producto = p.id_producto 
WHERE m.id_producto = ?
ORDER BY m.fecha DESC
```

---

### 3.3 FLUJO UPDATE (Actualizar Movimiento)

```
ESTADO: NO IMPLEMENTADO

Los movimientos de inventario NO se pueden modificar.
Esto asegura la integridad del historial de stock.
```

**Razón de no implementar update():**
- Los movimientos son registros históricos inmutables
- Modificar un movimiento alteraría la trazabilidad
- Si hay error, se debe crear un movimiento de corrección

---

### 3.4 FLUJO DELETE (Eliminar Movimiento)

```
ESTADO: NO IMPLEMENTADO

Los movimientos de inventario NO se pueden eliminar.
Esto asegura la integridad del historial de stock.
```

**Razón de no implementar destroy():**
- Los movimientos son registros históricos inmutables
- Eliminar un movimiento rompería la trazabilidad
- La foreign key tiene cascade delete por integridad referencial

---

## 4. PUNTOS DE ATENCIÓN PARA MANTENIMIENTO

### 4.1 Si no se pueden registrar movimientos
**Revisar en orden:**
1. Formulario en `resources/views/admin/inventario/create.blade.php` - Verificar campos y nombres
2. Validaciones en `InventarioController@store()` - Verificar reglas de validación
3. `$fillable` en `app/Models/MovimientoInventario.php` - Verificar campos permitidos
4. Migración `2026_08_13_160924_create_movimiento_inventario_table.php` - Verificar estructura
5. Foreign key `id_producto` - Verificar relación con tabla producto

### 4.2 Si el stock no se actualiza
**Revisar en orden:**
1. Lógica de actualización en store() - Líneas 67-79
2. Métodos `increment()` y `decrement()` de Eloquent
3. Campo `cantidad` en tabla producto - Verificar que sea integer
4. Validación de stock en salidas - Verificar que no bloquee incorrectamente

### 4.3 Si la validación de stock falla
**Revisar en orden:**
1. Condición `if ($request->tipo_movimiento === 'salida')` - Línea 68
2. Comparación `if ($producto->cantidad < $request->cantidad)` - Línea 69
3. Mensaje de error específico - Verificar que sea claro
4. Estado actual del producto - Verificar stock real en base de datos

### 4.4 Si los filtros no funcionan
**Revisar en orden:**
1. Formulario de filtros en index.blade.php - Verificar nombres de campos
2. Método `filled()` en controlador - Verificar que detecte parámetros
3. Consultas condicionales - Verificar lógica de where
4. Tipos de datos - Verificar que fechas sean válidas

### 4.5 Si no aparecen productos en el select
**Revisar en orden:**
1. Consulta en create() - `Producto::where('estado', 'activo')`
2. Estado de productos - Verificar que haya productos activos
3. Consulta en index() - `Producto::orderBy('nombre')->get()`
4. Vista - Verificar que se itere correctamente sobre `$productos`

### 4.6 Si los movimientos automáticos no se registran
**Revisar en orden:**
1. ProductoController@store() - Líneas 74-82 (registro inicial)
2. ClientePedidoController@store() - Líneas 119-126 (ventas)
3. ClientePedidoController@cancelar() - Líneas 179-185 (cancelaciones)
4. Verificar que todas las rutas de registro usen el mismo formato

---

## 5. RELACIONES CON OTROS MÓDULOS

### 5.1 Relación con Módulo Productos
- Un movimiento pertenece a un producto
- `MovimientoInventario::belongsTo(Producto::class, 'id_producto', 'id_producto')`
- Un producto tiene muchos movimientos
- `Producto::hasMany(MovimientoInventario::class, 'id_producto', 'id_producto')`
- Al eliminar producto, sus movimientos se eliminan en cascade

### 5.2 Relación con Módulo Pedidos (Cliente)
- Las ventas generan movimientos automáticos de salida
- `ClientePedidoController@store()` registra salidas por venta
- El motivo es: "Venta - Pedido #{id_pedido}"
- Esto mantiene trazabilidad entre ventas y stock

### 5.3 Relación con Módulo Pedidos (Cancelaciones)
- Las cancelaciones generan movimientos automáticos de entrada
- `ClientePedidoController@cancelar()` registra entradas por cancelación
- El motivo es: "Reintegro por cancelación - Pedido #{id_pedido}"
- Esto devuelve el stock automáticamente

### 5.4 Relación con Módulo Productos (Creación)
- Al crear producto con cantidad > 0, se registra movimiento inicial
- `ProductoController@store()` registra entrada inicial
- El motivo es: "Inventario inicial al registrar producto"
- Esto asegura trazabilidad desde el inicio

### 5.5 Relación con Módulo Reportes
- Los reportes incluyen movimientos de inventario
- Se filtran por rango de fechas
- Se muestran junto con pedidos para análisis completo

---

## 6. ESTADO DE IMPLEMENTACIÓN

| Funcionalidad | Estado | Observaciones |
|--------------|--------|---------------|
| Ver historial de movimientos | ✅ IMPLEMENTADO | Con filtros por producto, tipo, fechas |
| Registrar movimiento manual | ✅ IMPLEMENTADO | Entradas y salidas con validación de stock |
| Actualizar movimiento | ❌ NO IMPLEMENTADO | Movimientos son inmutables |
| Eliminar movimiento | ❌ NO IMPLEMENTADO | Movimientos son inmutables |
| Actualización automática de stock | ✅ IMPLEMENTADO | increment/decrement de Eloquent |
| Validación de stock en salidas | ✅ IMPLEMENTADO | US-0005 escenario 4 |
| Registro automático por ventas | ✅ IMPLEMENTADO | En ClientePedidoController@store() |
| Registro automático por cancelaciones | ✅ IMPLEMENTADO | En ClientePedidoController@cancelar() |
| Registro inicial al crear producto | ✅ IMPLEMENTADO | En ProductoController@store() |
| Filtros de búsqueda | ✅ IMPLEMENTADO | Por producto, tipo, fechas |

---

## 7. ARCHIVOS RELACIONADOS

### Controladores
- `app/Http/Controllers/InventarioController.php` - Controlador principal
- `app/Http/Controllers/ProductoController.php` - Controlador de productos (registro inicial)
- `app/Http/Controllers/ClientePedidoController.php` - Controlador de pedidos (ventas/cancelaciones)

### Modelos
- `app/Models/MovimientoInventario.php` - Modelo principal
- `app/Models/Producto.php` - Modelo de productos

### Vistas
- `resources/views/admin/inventario/index.blade.php` - Historial con filtros
- `resources/views/admin/inventario/create.blade.php` - Formulario de movimiento

### Migraciones
- `database/migrations/2026_08_13_160924_create_movimiento_inventario_table.php` - Tabla movimientos
- `database/migrations/2026_08_13_160910_create_producto_table.php` - Tabla producto

### Rutas
- `routes/web.php` - Rutas del módulo (líneas 47-50)

---

## 8. CARACTERÍSTICAS ESPECIALES

### 8.1 Inmutabilidad de Movimientos
- Los movimientos NO se pueden modificar ni eliminar
- Esto garantiza trazabilidad completa del stock
- Si hay error, se crea un movimiento de corrección
- El historial permanece intacto para auditoría

### 8.2 Actualización Automática de Stock
- Los movimientos actualizan automáticamente el stock del producto
- Métodos Eloquent: `increment()` y `decrement()`
- Esto evita inconsistencias entre stock y movimientos

### 8.3 Validación de Stock en Salidas
- Antes de registrar una salida, se verifica stock disponible
- US-0005 escenario 4: previene stock negativo
- Mensaje de error específico con cantidad disponible

### 8.4 Registro Automático en Otros Módulos
- **Creación de producto**: Registro inicial si cantidad > 0
- **Ventas**: Registro automático de salida por cada detalle
- **Cancelaciones**: Registro automático de entrada por cada detalle
- Esto mantiene trazabilidad sin intervención manual

### 8.5 Filtros Avanzados
- Filtrado por producto específico
- Filtrado por tipo de movimiento (entrada/salida)
- Filtrado por rango de fechas
- Combinación de múltiples filtros

### 8.6 Motivos Descriptivos
- Cada movimiento requiere un motivo obligatorio
- Los motivos automáticos son descriptivos:
  - "Inventario inicial al registrar producto"
  - "Venta - Pedido #{id_pedido}"
  - "Reintegro por cancelación - Pedido #{id_pedido}"
- Los movimientos manuales requieren motivo personalizado

---

## 9. ESCENARIOS DE USO

### 9.1 Escenario 1: Registrar Entrada Manual
```
1. Admin accede a /admin/inventario/crear
2. Selecciona producto "Huevo Blanco Docena"
3. Selecciona tipo "entrada"
4. Ingresa cantidad 50
5. Ingresa motivo "Compra al proveedor XYZ"
6. Sistema incrementa stock del producto a +50
7. Sistema registra movimiento en historial
```

### 9.2 Escenario 2: Registrar Salida Manual
```
1. Admin accede a /admin/inventario/crear
2. Selecciona producto "Huevo Blanco Docena"
3. Selecciona tipo "salida"
4. Ingresa cantidad 10
5. Ingresa motivo "Merma por rotura"
6. Sistema verifica stock disponible
7. Si suficiente, decrementa stock a -10
8. Sistema registra movimiento en historial
```

### 9.3 Escenario 3: Consultar Historial
```
1. Admin accede a /admin/inventario
2. Sistema muestra todos los movimientos ordenados por fecha
3. Admin puede filtrar por producto específico
4. Admin puede filtrar solo entradas o salidas
5. Admin puede filtrar por rango de fechas
6. Sistema aplica filtros y muestra resultados
```

### 9.4 Escenario 4: Venta Automática
```
1. Cliente realiza pedido con 2 productos
2. Sistema crea pedido y detalles
3. Por cada detalle, sistema:
   - Decrementa stock del producto
   - Registra movimiento de salida
   - Motivo: "Venta - Pedido #123"
4. Stock actualizado automáticamente
5. Historial actualizado automáticamente
```

### 9.5 Escenario 5: Cancelación Automática
```
1. Cliente cancela pedido pendiente
2. Sistema verifica estado del pedido
3. Por cada detalle, sistema:
   - Incrementa stock del producto
   - Registra movimiento de entrada
   - Motivo: "Reintegro por cancelación - Pedido #123"
4. Stock devuelto automáticamente
5. Historial actualizado automáticamente
```

---

## 10. REGLAS DE NEGOCIO

### 10.1 Reglas de Stock
- El stock nunca puede ser negativo
- Las salidas requieren verificación de stock disponible
- Las entradas incrementan stock sin límite
- El stock se actualiza automáticamente en cada movimiento

### 10.2 Reglas de Movimientos
- Todo cambio de stock debe tener un movimiento asociado
- Los movimientos son inmutables (no se editan ni eliminan)
- Los movimientos requieren motivo obligatorio
- Los movimientos automáticos tienen motivos estandarizados

### 10.3 Reglas de Trazabilidad
- El historial de movimientos debe ser completo
- Cada movimiento debe tener fecha y hora
- Cada movimiento debe estar asociado a un producto
- El historial debe permitir auditoría

### 10.4 Reglas de Acceso
- Solo administradores pueden registrar movimientos manuales
- Los movimientos automáticos no requieren intervención
- El historial es visible solo para administradores
- Los clientes no ven movimientos de inventario

---

**Fin de documentación del módulo Inventario**
