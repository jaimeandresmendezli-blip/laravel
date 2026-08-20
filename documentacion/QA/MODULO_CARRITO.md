# DOCUMENTACIÓN MÓDULO: CARRITO (US-0008)

**Sistema EGG EXPRESS - Gestión de Carrito de Compras**

---

## 1. INFORMACIÓN GENERAL

- **Código de Historia de Usuario**: US-0008 (escenarios 4, 5, 6, 7)
- **Módulo**: Carrito de Compras
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
        // US-0008 — Carrito
        Route::get('carrito', [CarritoController::class, 'index'])->name('carrito.index');
        Route::post('carrito/agregar', [CarritoController::class, 'agregar'])->name('carrito.agregar');
        Route::patch('carrito/actualizar/{detalle}', [CarritoController::class, 'actualizar'])->name('carrito.actualizar');
        Route::delete('carrito/eliminar/{detalle}', [CarritoController::class, 'eliminar'])->name('carrito.eliminar');
        Route::delete('carrito/vaciar', [CarritoController::class, 'vaciar'])->name('carrito.vaciar');
    });
```

**Rutas del módulo:**

| Método HTTP | Ruta | Nombre de Ruta | Controlador | Método | Función |
|-------------|------|----------------|-------------|--------|---------|
| GET | `/cliente/carrito` | cliente.carrito.index | CarritoController | index() | Ver mi carrito |
| POST | `/cliente/carrito/agregar` | cliente.carrito.agregar | CarritoController | agregar() | Agregar producto al carrito |
| PATCH | `/cliente/carrito/actualizar/{detalle}` | cliente.carrito.actualizar | CarritoController | actualizar() | Actualizar cantidad de ítem |
| DELETE | `/cliente/carrito/eliminar/{detalle}` | cliente.carrito.eliminar | CarritoController | eliminar() | Eliminar ítem del carrito |
| DELETE | `/cliente/carrito/vaciar` | cliente.carrito.vaciar | CarritoController | vaciar() | Vaciar todo el carrito |

**NOTA**: No usa Route::resource, usa rutas individuales. Solo clientes pueden acceder a su carrito.

---

### 2.2 Controlador (app/Http/Controllers/CarritoController.php)

**Archivo**: `app/Http/Controllers/CarritoController.php`

**Modelos utilizados:**
- `App\Models\Carrito`
- `App\Models\DetalleCarrito`
- `App\Models\Producto`

**Dependencias adicionales:**
- `Illuminate\Support\Facades\Auth`

**Método privado auxiliar:**
```php
private function obtenerCarrito()
{
    return Carrito::firstOrCreate(
        ['id_usuario' => Auth::id(), 'estado' => 'activo'],
        ['fecha_creacion' => now()]
    );
}
```
- **Función**: Obtiene o crea el carrito activo del usuario
- **Lógica**: `firstOrCreate()` busca carrito activo, si no existe lo crea
- **Parámetros**:
  - Busca: `id_usuario = Auth::id()` y `estado = 'activo'`
  - Crea: `fecha_creacion = now()`
- **Retorno**: Instancia de Carrito

**Métodos del controlador:**

#### index()
```php
public function index()
{
    $carrito  = $this->obtenerCarrito();
    $detalles = $carrito->detalles()->with('producto')->get();
    $total    = $detalles->sum('subtotal');

    return view('cliente.carrito.index', compact('carrito', 'detalles', 'total'));
}
```
- **Función**: Muestra el carrito activo del usuario con sus ítems
- **Llamada**: `$this->obtenerCarrito()` - obtiene o crea carrito
- **Consulta**: `$carrito->detalles()->with('producto')->get()`
- **Cálculo**: `$total = $detalles->sum('subtotal')`
- **Vista**: `cliente.carrito.index`
- **Datos enviados**: `$carrito`, `$detalles` (con productos), `$total`

#### agregar()
```php
public function agregar(Request $request)
{
    $request->validate([
        'id_producto' => 'required|exists:producto,id_producto',
        'cantidad'    => 'required|integer|min:1',
    ]);

    $producto = Producto::findOrFail($request->id_producto);

    // Verificar que el producto esté activo
    if ($producto->estado !== 'activo') {
        return back()->withErrors(['error' => 'Este producto no está disponible.']);
    }

    // US-0008 escenario 8: verificar stock suficiente
    if ($producto->cantidad < $request->cantidad) {
        return back()->withErrors([
            'error' => "Stock insuficiente. Solo hay {$producto->cantidad} unidades disponibles.",
        ]);
    }

    $carrito  = $this->obtenerCarrito();

    // Verificar si ya existe en el carrito (unique constraint id_carrito + id_producto)
    $detalle = DetalleCarrito::where('id_carrito', $carrito->id_carrito)
        ->where('id_producto', $producto->id_producto)
        ->first();

    if ($detalle) {
        // Ya existe: sumar cantidad
        $nuevaCantidad = $detalle->cantidad + $request->cantidad;

        if ($producto->cantidad < $nuevaCantidad) {
            return back()->withErrors([
                'error' => "Stock insuficiente. Solo hay {$producto->cantidad} unidades disponibles.",
            ]);
        }

        $detalle->update([
            'cantidad' => $nuevaCantidad,
            'subtotal' => $nuevaCantidad * $producto->precio,
        ]);
    } else {
        DetalleCarrito::create([
            'id_carrito'      => $carrito->id_carrito,
            'id_producto'     => $producto->id_producto,
            'cantidad'        => $request->cantidad,
            'precio_unitario' => $producto->precio,
            'subtotal'        => $request->cantidad * $producto->precio,
        ]);
    }

    return redirect()->route('cliente.carrito.index')
        ->with('success', 'Producto agregado al carrito.');
}
```
- **Función**: Agrega producto al carrito (US-0008 escenario 4)
- **Validaciones**: 
  - id_producto: requerido, debe existir en tabla producto
  - cantidad: requerido, integer, min 1
- **Validación de estado**: Verifica que el producto esté 'activo'
- **Validación de stock**: Verifica stock suficiente (US-0008 escenario 8)
- **Lógica de duplicado**:
  - Busca si ya existe el producto en el carrito
  - Si existe: suma cantidad y actualiza subtotal
  - Si no existe: crea nuevo detalle
- **Validación de stock al sumar**: Si ya existe, verifica stock para la nueva cantidad total
- **Operación**: `DetalleCarrito::create()` o `$detalle->update()`
- **Redirección**: `cliente.carrito.index` con mensaje de éxito

#### actualizar()
```php
public function actualizar(Request $request, DetalleCarrito $detalle)
{
    $request->validate([
        'cantidad' => 'required|integer|min:1',
    ]);

    $producto = $detalle->producto;

    // Verificar stock
    if ($producto->cantidad < $request->cantidad) {
        return back()->withErrors([
            'error' => "Stock insuficiente. Solo hay {$producto->cantidad} unidades disponibles.",
        ]);
    }

    $detalle->update([
        'cantidad' => $request->cantidad,
        'subtotal' => $request->cantidad * $detalle->precio_unitario,
    ]);

    return redirect()->route('cliente.carrito.index')
        ->with('success', 'Cantidad actualizada.');
}
```
- **Función**: Actualiza cantidad de un ítem del carrito (US-0008 escenario 5)
- **Validaciones**: 
  - cantidad: requerido, integer, min 1
- **Validación de stock**: Verifica stock disponible para la nueva cantidad
- **Operación**: `$detalle->update()` - actualiza cantidad y subtotal
- **Redirección**: `cliente.carrito.index` con mensaje de éxito

#### eliminar()
```php
public function eliminar(DetalleCarrito $detalle)
{
    $detalle->delete();

    return redirect()->route('cliente.carrito.index')
        ->with('success', 'Producto eliminado del carrito.');
}
```
- **Función**: Elimina un ítem del carrito (US-0008 escenario 6)
- **Parámetro**: `$detalle` (inyectado por route model binding)
- **Operación**: `$detalle->delete()` - eliminación física
- **Redirección**: `cliente.carrito.index` con mensaje de éxito

#### vaciar()
```php
public function vaciar()
{
    $carrito = $this->obtenerCarrito();
    $carrito->detalles()->delete();

    return redirect()->route('cliente.carrito.index')
        ->with('success', 'Carrito vaciado.');
}
```
- **Función**: Elimina todos los ítems del carrito (US-0008 escenario 7)
- **Llamada**: `$this->obtenerCarrito()` - obtiene carrito activo
- **Operación**: `$carrito->detalles()->delete()` - elimina todos los detalles
- **Redirección**: `cliente.carrito.index` con mensaje de éxito

---

### 2.3 Modelo (app/Models/Carrito.php)

**Archivo**: `app/Models/Carrito.php`

**Configuración:**
```php
protected $table = 'carrito';
protected $primaryKey = 'id_carrito';
public $timestamps = false;

protected $fillable = [
    'id_usuario', 'estado', 'fecha_creacion',
];

protected $casts = [
    'fecha_creacion' => 'datetime',
];
```

**Relaciones:**
```php
public function usuario()
{
    return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
}

public function detalles()
{
    return $this->hasMany(DetalleCarrito::class, 'id_carrito', 'id_carrito');
}
```

---

### 2.4 Modelo Relacionado (app/Models/DetalleCarrito.php)

**Archivo**: `app/Models/DetalleCarrito.php`

**Configuración:**
```php
protected $table = 'detalle_carrito';
protected $primaryKey = 'id_detalle_carrito';
public $timestamps = false;

protected $fillable = [
    'id_carrito', 'id_producto', 'cantidad', 'precio_unitario', 'subtotal',
];

protected $casts = [
    'precio_unitario' => 'decimal:2',
    'subtotal'        => 'decimal:2',
    'cantidad'        => 'integer',
];
```

**Relaciones:**
```php
public function carrito()
{
    return $this->belongsTo(Carrito::class, 'id_carrito', 'id_carrito');
}

public function producto()
{
    return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
}
```

---

### 2.5 Modelo Relacionado (app/Models/Producto.php)

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
public function detallesCarrito()
{
    return $this->hasMany(DetalleCarrito::class, 'id_producto', 'id_producto');
}
```

---

### 2.6 Base de Datos

**Tabla**: `carrito`

**Migración**: `database/migrations/2026_08_13_160935_create_carrito_table.php`

**Estructura:**
```php
Schema::create('carrito', function (Blueprint $table) {
    $table->integer('id_carrito')->autoIncrement();
    $table->integer('id_usuario');
    $table->enum('estado', ['activo', 'comprado'])->default('activo');
    $table->dateTime('fecha_creacion')->useCurrent();

    $table->foreign('id_usuario')->references('id_usuario')->on('usuario')->onDelete('cascade');
});
```

**Campos:**
- `id_carrito` - Primary key, auto increment
- `id_usuario` - Foreign key Usuario (propietario del carrito)
- `estado` - Enum: 'activo', 'comprado'
- `fecha_creacion` - Fecha de creación, current timestamp

**Foreign key**: `id_usuario` con ON DELETE CASCADE

**Tabla relacionada**: `detalle_carrito`

**Estructura:**
```php
Schema::create('detalle_carrito', function (Blueprint $table) {
    $table->integer('id_detalle_carrito')->autoIncrement();
    $table->integer('id_carrito');
    $table->integer('id_producto');
    $table->integer('cantidad');
    $table->decimal('precio_unitario', 10, 2);
    $table->decimal('subtotal', 10, 2);

    $table->unique(['id_carrito', 'id_producto'], 'unique_producto_carrito');

    $table->foreign('id_carrito')->references('id_carrito')->on('carrito')->onDelete('cascade');
    $table->foreign('id_producto')->references('id_producto')->on('producto')->onDelete('cascade');
});
```

**Campos:**
- `id_detalle_carrito` - Primary key, auto increment
- `id_carrito` - Foreign key Carrito
- `id_producto` - Foreign key Producto
- `cantidad` - Cantidad del producto
- `precio_unitario` - Precio unitario al momento de agregar
- `subtotal` - Cantidad × precio_unitario

**Unique constraint**: `unique_producto_carrito` (id_carrito, id_producto)

**Foreign keys**: 
- `id_carrito` con ON DELETE CASCADE
- `id_producto` con ON DELETE CASCADE

**Tabla relacionada**: `producto`

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

---

### 2.7 Vistas (resources/views/cliente/carrito/)

#### index.blade.php
- **Ubicación**: `resources/views/cliente/carrito/index.blade.php`
- **Función**: Muestra el carrito con sus ítems y total
- **Datos recibidos**: `$carrito`, `$detalles` (con productos), `$total`
- **Elementos**:
  - Tabla de ítems del carrito:
    - Producto, Precio unitario, Cantidad, Subtotal, Acciones
    - Imagen miniatura del producto
    - Botón: Actualizar cantidad
    - Botón: Eliminar ítem
  - Resumen del carrito:
    - Total a pagar
    - Botón: Vaciar carrito
    - Botón: Confirmar pedido (redirige a /cliente/pedidos/crear)
  - Mensaje si carrito está vacío

---

## 3. FLUJO COMPLETO DE OPERACIONES

### 3.1 FLUJO READ (Ver Mi Carrito)

```
1. Usuario (Cliente)
   ↓
2. Accede a: /cliente/carrito
   ↓
3. Ruta: cliente.carrito.index (GET)
   ↓
4. Controlador: CarritoController@index()
    ↓
5. Llamada: $this->obtenerCarrito()
    ↓
6. Consulta: Carrito::firstOrCreate([...])
    ↓
7. Si no existe carrito activo → Crea nuevo carrito
    ↓
8. Si existe carrito activo → Usa el existente
    ↓
9. Consulta: $carrito->detalles()->with('producto')->get()
    ↓
10. Cálculo: $total = $detalles->sum('subtotal')
    ↓
11. Vista: cliente.carrito.index
    ↓
12. Usuario ve su carrito con ítems y total
```

**Consulta SQL generada (para obtener carrito):**
```sql
SELECT * FROM carrito 
WHERE id_usuario = ? AND estado = 'activo'
LIMIT 1
```

**Si no existe, se ejecuta:**
```sql
INSERT INTO carrito (id_usuario, estado, fecha_creacion) 
VALUES (?, 'activo', NOW())
```

**Consulta SQL para detalles:**
```sql
SELECT dc.*, p.nombre, p.precio, p.imagen 
FROM detalle_carrito dc 
LEFT JOIN producto p ON dc.id_producto = p.id_producto 
WHERE dc.id_carrito = ?
```

---

### 3.2 FLUJO CREATE (Agregar Producto al Carrito)

```
1. Usuario (Cliente)
   ↓
2. En catálogo, hace clic en "Agregar al carrito"
   ↓
3. Envía POST a: /cliente/carrito/agregar
   ↓
4. Ruta: cliente.carrito.agregar (POST)
   ↓
5. Controlador: CarritoController@agregar()
    ↓
6. Validación: $request->validate()
    ↓
7. Consulta: Producto::findOrFail($request->id_producto)
    ↓
8. Validación de estado: if ($producto->estado !== 'activo')
    ↓
9. Si inactivo → Error y redirección
    ↓
10. Validación de stock (US-0008 escenario 8):
    ↓
11. if ($producto->cantidad < $request->cantidad)
    ↓
12. Si insuficiente → Error y redirección
    ↓
13. Llamada: $this->obtenerCarrito()
    ↓
14. Consulta: DetalleCarrito::where('id_carrito', $carrito->id_carrito)->where('id_producto', $producto->id_producto)->first()
    ↓
15. Si ya existe en carrito:
    ↓
16. - Calcular nueva cantidad: $detalle->cantidad + $request->cantidad
    ↓
17. - Validar stock para nueva cantidad
    ↓
18. - $detalle->update(['cantidad' => $nuevaCantidad, 'subtotal' => $nuevaCantidad * $producto->precio])
    ↓
19. Si no existe en carrito:
    ↓
20. - DetalleCarrito::create([...])
    ↓
21. Redirección: cliente.carrito.index
    ↓
22. Vista: cliente.carrito.index con mensaje de éxito
```

**Validaciones en CREATE:**
- id_producto: required, exists:producto,id_producto
- cantidad: required, integer, min:1

**Validación de stock (US-0008 escenario 8):**
```php
if ($producto->cantidad < $request->cantidad) {
    return back()->withErrors([
        'error' => "Stock insuficiente. Solo hay {$producto->cantidad} unidades disponibles.",
    ]);
}
```

**Lógica de duplicado:**
```php
$detalle = DetalleCarrito::where('id_carrito', $carrito->id_carrito)
    ->where('id_producto', $producto->id_producto)
    ->first();
```

---

### 3.3 FLUJO UPDATE (Actualizar Cantidad de Ítem)

```
1. Usuario (Cliente)
   ↓
2. En carrito, modifica cantidad de un ítem
   ↓
3. Envía PATCH a: /cliente/carrito/actualizar/{id}
   ↓
4. Ruta: cliente.carrito.actualizar (PATCH)
   ↓
5. Controlador: CarritoController@actualizar($request, $detalle)
    ↓
6. Validación: $request->validate()
    ↓
7. Consulta: $detalle->producto (relación)
    ↓
8. Validación de stock:
    ↓
9. if ($producto->cantidad < $request->cantidad)
    ↓
10. Si insuficiente → Error y redirección
    ↓
11. Operación: $detalle->update([
        'cantidad' => $request->cantidad,
        'subtotal' => $request->cantidad * $detalle->precio_unitario,
    ])
    ↓
12. Redirección: cliente.carrito.index
    ↓
13. Vista: cliente.carrito.index con mensaje de éxito
```

**Validaciones en UPDATE:**
- cantidad: required, integer, min:1

**Validación de stock:**
```php
if ($producto->cantidad < $request->cantidad) {
    return back()->withErrors([
        'error' => "Stock insuficiente. Solo hay {$producto->cantidad} unidades disponibles.",
    ]);
}
```

---

### 3.4 FLUJO DELETE (Eliminar Ítem del Carrito)

```
1. Usuario (Cliente)
   ↓
2. En carrito, hace clic en "Eliminar" de un ítem
   ↓
3. Envía DELETE a: /cliente/carrito/eliminar/{id}
   ↓
4. Ruta: cliente.carrito.eliminar (DELETE)
   ↓
5. Controlador: CarritoController@eliminar($detalle)
    ↓
6. Operación: $detalle->delete()
    ↓
7. Base de Datos: DELETE FROM detalle_carrito WHERE id_detalle_carrito = ?
    ↓
8. Redirección: cliente.carrito.index
    ↓
9. Vista: cliente.carrito.index con mensaje de éxito
```

---

### 3.5 FLUJO DELETE (Vaciar Carrito)

```
1. Usuario (Cliente)
   ↓
2. En carrito, hace clic en "Vaciar carrito"
   ↓
3. Envía DELETE a: /cliente/carrito/vaciar
   ↓
4. Ruta: cliente.carrito.vaciar (DELETE)
   ↓
5. Controlador: CarritoController@vaciar()
    ↓
6. Llamada: $this->obtenerCarrito()
    ↓
7. Operación: $carrito->detalles()->delete()
    ↓
8. Base de Datos: DELETE FROM detalle_carrito WHERE id_carrito = ?
    ↓
9. Redirección: cliente.carrito.index
    ↓
10. Vista: cliente.carrito.index con mensaje de éxito
```

---

## 4. PUNTOS DE ATENCIÓN PARA MANTENIMIENTO

### 4.1 Si no se puede ver el carrito
**Revisar en orden:**
1. Vista en `resources/views/cliente/carrito/index.blade.php` - Verificar iteración
2. Método `obtenerCarrito()` - Verificar lógica de firstOrCreate
3. Estado del carrito - Verificar que esté 'activo'
4. Autenticación - Verificar que el usuario esté autenticado
5. Rol - Verificar que el usuario tenga rol 'cliente'

### 4.2 Si no se pueden agregar productos
**Revisar en orden:**
1. Validaciones en agregar() - Verificar reglas de validación
2. Estado del producto - Verificar que esté 'activo'
3. Stock del producto - Verificar validación de stock
4. Unique constraint - Verificar (id_carrito, id_producto)
5. `$fillable` en DetalleCarrito - Verificar campos permitidos

### 4.3 Si se duplican productos en el carrito
**Revisar en orden:**
1. Lógica de duplicado en agregar() - Líneas 62-70
2. Unique constraint en base de datos - Verificar que exista
3. Consulta where() - Verificar que busque correctamente
4. Condición if ($detalle) - Verificar que funcione

### 4.4 Si no se actualiza el subtotal
**Revisar en orden:**
1. Línea 79 en agregar() - `$nuevaCantidad * $producto->precio`
2. Línea 113 en actualizar() - `$request->cantidad * $detalle->precio_unitario`
3. Campo subtotal en DetalleCarrito - Verificar que sea decimal
4. Cálculo del total en index() - Verificar sum('subtotal')

### 4.5 Si no se puede actualizar la cantidad
**Revisar en orden:**
1. Formulario en index.blade.php - Verificar método PATCH
2. Campo oculto `_method` con valor "PATCH"
3. Validaciones en actualizar() - Verificar reglas
4. Route model binding - Verificar que el ID se pase correctamente

### 4.6 Si el carrito no se marca como comprado
**Revisar en orden:**
1. Esto NO se hace en CarritoController
2. Revisar ClientePedidoController@store() - Línea 140
3. `$carrito->update(['estado' => 'comprado'])`
4. El carrito se marca como comprado al generar pedido

---

## 5. RELACIONES CON OTROS MÓDULOS

### 5.1 Relación con Módulo Catálogo
- Los productos del catálogo se agregan al carrito
- Solo productos activos pueden agregarse
- El catálogo muestra stock disponible
- Los precios se toman del catálogo al momento de agregar

### 5.2 Relación con Módulo Pedidos (Cliente)
- El carrito se convierte en pedido
- `ClientePedidoController@store()` usa el carrito
- El carrito se marca como 'comprado' después de generar pedido
- Los detalles del carrito se convierten en detalles del pedido

### 5.3 Relación con Módulo Productos
- Los ítems del carrito contienen productos
- `DetalleCarrito::belongsTo(Producto::class, 'id_producto', 'id_producto')`
- Los precios se guardan al momento de agregar al carrito
- El stock se verifica antes de agregar al carrito

### 5.4 Relación con Módulo Inventario
- El carrito NO afecta el inventario directamente
- El inventario se afecta al generar el pedido
- Las salidas de stock se registran al crear pedido
- El carrito es temporal, no afecta stock

### 5.5 Relación con Módulo Usuarios
- Un usuario puede tener múltiples carritos
- Solo un carrito activo por usuario a la vez
- Los carritos 'comprados' son históricos
- `Carrito::belongsTo(Usuario::class, 'id_usuario', 'id_usuario')`

---

## 6. ESTADO DE IMPLEMENTACIÓN

| Funcionalidad | Estado | Observaciones |
|--------------|--------|---------------|
| Ver mi carrito | ✅ IMPLEMENTADO | Con detalles y productos |
| Agregar producto al carrito | ✅ IMPLEMENTADO | US-0008 escenario 4 |
| Actualizar cantidad de ítem | ✅ IMPLEMENTADO | US-0008 escenario 5 |
| Eliminar ítem del carrito | ✅ IMPLEMENTADO | US-0008 escenario 6 |
| Vaciar carrito | ✅ IMPLEMENTADO | US-0008 escenario 7 |
| Verificar stock al agregar | ✅ IMPLEMENTADO | US-0008 escenario 8 |
| Manejar duplicados | ✅ IMPLEMENTADO | Suma cantidad si ya existe |
| Guardar precio al agregar | ✅ IMPLEMENTADO | Precio unitario fijo |
| Calcular subtotal automáticamente | ✅ IMPLEMENTADO | cantidad × precio_unitario |
| Persistencia de carrito | ✅ IMPLEMENTADO | Carrito activo persiste |
| Múltiples carritos por usuario | ✅ IMPLEMENTADO | Solo uno activo |
| Editar precio en carrito | ❌ NO IMPLEMENTADO | Precio es fijo al agregar |

---

## 7. ARCHIVOS RELACIONADOS

### Controladores
- `app/Http/Controllers/CarritoController.php` - Controlador principal
- `app/Http/Controllers/ClientePedidoController.php` - Controlador de pedidos (usa carrito)
- `app/Http/Controllers/CatalogoController.php` - Controlador de catálogo (agrega al carrito)

### Modelos
- `app/Models/Carrito.php` - Modelo principal
- `app/Models/DetalleCarrito.php` - Modelo de detalles
- `app/Models/Producto.php` - Modelo de productos
- `app/Models/Usuario.php` - Modelo de usuarios

### Vistas
- `resources/views/cliente/carrito/index.blade.php` - Vista del carrito
- `resources/views/cliente/catalogo/index.blade.php` - Catálogo (agrega al carrito)
- `resources/views/cliente/catalogo/show.blade.php` - Detalle producto (agrega al carrito)
- `resources/views/cliente/pedidos/create.blade.php` - Confirmación desde carrito

### Migraciones
- `database/migrations/2026_08_13_160935_create_carrito_table.php` - Tabla carrito
- `database/migrations/2026_08_13_160946_create_detalle_carrito_table.php` - Tabla detalle_carrito
- `database/migrations/2026_08_13_160910_create_producto_table.php` - Tabla producto

### Rutas
- `routes/web.php` - Rutas del módulo (líneas 82-87)

---

## 8. CARACTERÍSTICAS ESPECIALES

### 8.1 Carrito Persistente
- El carrito persiste entre sesiones del usuario
- `firstOrCreate()` crea carrito si no existe
- El carrito activo se mantiene hasta que se convierte en pedido
- Los ítems se guardan en base de datos, no en sesión

### 8.2 Manejo de Duplicados
- Si un producto ya está en el carrito, se suma la cantidad
- Unique constraint (id_carrito, id_producto) previene duplicados en BD
- El subtotal se recalcula automáticamente
- Se verifica stock para la cantidad total

### 8.3 Validación de Stock
- Se verifica stock antes de agregar producto (US-0008 escenario 8)
- Se verifica stock antes de actualizar cantidad
- Se verifica stock al sumar cantidades de duplicados
- Mensaje específico indica cantidad disponible

### 8.4 Precio Fijo al Agregar
- El precio se guarda al momento de agregar al carrito
- `precio_unitario` no cambia si el precio del producto cambia
- Esto protege al cliente de cambios de precio
- El subtotal se calcula: cantidad × precio_unitario

### 8.5 Estados del Carrito
- **activo**: Carrito disponible para agregar productos
- **comprado**: Carrito convertido en pedido (histórico)
- Al generar pedido, el carrito activo se marca como 'comprado'
- Se crea un nuevo carrito activo automáticamente

### 8.6 Unique Constraint
- `unique_producto_carrito` (id_carrito, id_producto)
- Previene que el mismo producto aparezca dos veces
- Permite manejar duplicados en código (sumar cantidades)
- Garantiza integridad de datos

### 8.7 Cálculo Automático de Subtotal
- El subtotal se calcula automáticamente al agregar
- El subtotal se recalcula al actualizar cantidad
- El total del carrito es la suma de todos los subtotales
- Esto evita errores manuales de cálculo

---

## 9. ESCENARIOS DE USO

### 9.1 Escenario 1: Ver Carrito Vacío
```
1. Cliente accede a /cliente/carrito por primera vez
2. Sistema crea carrito activo automáticamente
3. Sistema muestra carrito vacío
4. Cliente puede ir al catálogo para agregar productos
```

### 9.2 Escenario 2: Agregar Producto Nuevo
```
1. Cliente en catálogo selecciona producto
2. Cliente hace clic en "Agregar al carrito"
3. Sistema verifica que el producto esté activo
4. Sistema verifica stock suficiente
5. Sistema crea nuevo detalle en carrito
6. Sistema calcula subtotal automáticamente
7. Cliente ve mensaje de éxito
```

### 9.3 Escenario 3: Agregar Producto Existente
```
1. Cliente agrega producto que ya está en carrito
2. Sistema detecta duplicado por unique constraint
3. Sistema suma cantidad al detalle existente
4. Sistema verifica stock para nueva cantidad total
5. Sistema actualiza subtotal
6. Cliente ve mensaje de éxito
```

### 9.4 Escenario 4: Actualizar Cantidad
```
1. Cliente en carrito modifica cantidad de un ítem
2. Sistema verifica stock disponible
3. Sistema actualiza cantidad y subtotal
4. Cliente ve carrito actualizado
```

### 9.5 Escenario 5: Eliminar Ítem
```
1. Cliente hace clic en "Eliminar" de un ítem
2. Sistema elimina el detalle del carrito
3. Sistema recalcula total
4. Cliente ve carrito actualizado
```

### 9.6 Escenario 6: Vaciar Carrito
```
1. Cliente hace clic en "Vaciar carrito"
2. Sistema elimina todos los detalles
3. Sistema mantiene el carrito activo
4. Cliente ve carrito vacío
```

### 9.7 Escenario 7: Stock Insuficiente
```
1. Cliente intenta agregar producto
2. Sistema verifica stock disponible
3. Sistema detecta stock insuficiente
4. Sistema muestra error con cantidad disponible
5. Cliente no puede agregar el producto
```

### 9.8 Escenario 8: Convertir a Pedido
```
1. Cliente hace clic en "Confirmar pedido"
2. Sistema redirige a /cliente/pedidos/crear
3. Sistema muestra resumen del carrito
4. Cliente confirma datos de entrega y pago
5. Sistema convierte carrito en pedido
6. Sistema marca carrito como 'comprado'
7. Sistema crea nuevo carrito activo
```

---

## 10. REGLAS DE NEGOCIO

### 10.1 Reglas de Acceso
- Solo los clientes pueden acceder a su carrito
- Cada cliente tiene su propio carrito
- Los clientes no pueden ver carritos de otros clientes
- Los administradores no acceden a carritos de clientes

### 10.2 Reglas de Agregado
- Solo productos activos pueden agregarse al carrito
- Se debe verificar stock antes de agregar
- Si el producto ya existe, se suma la cantidad
- El precio se fija al momento de agregar

### 10.3 Reglas de Stock
- El stock se verifica antes de agregar
- El stock se verifica antes de actualizar cantidad
- El stock se verifica al sumar cantidades
- No se permiten cantidades mayores al stock disponible

### 10.4 Reglas de Actualización
- Solo se puede actualizar la cantidad
- No se puede modificar el precio
- No se puede modificar el producto
- El subtotal se recalcula automáticamente

### 10.5 Reglas de Eliminación
- Se pueden eliminar ítems individuales
- Se puede vaciar todo el carrito
- La eliminación es física (DELETE)
- El carrito activo persiste después de vaciar

### 10.6 Reglas de Conversión
- El carrito se convierte en pedido al confirmar
- El carrito se marca como 'comprado' después de convertir
- Se crea un nuevo carrito activo automáticamente
- Los detalles del carrito se convierten en detalles del pedido

---

**Fin de documentación del módulo Carrito**
