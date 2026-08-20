# DOCUMENTACIÓN MÓDULO: PRODUCTOS (US-0004)

**Sistema EGG EXPRESS - Gestión de Productos**

---

## 1. INFORMACIÓN GENERAL

- **Código de Historia de Usuario**: US-0004
- **Módulo**: Gestión de Productos
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
        Route::resource('productos', ProductoController::class);
        Route::patch('productos/{producto}/toggle', [ProductoController::class, 'toggleEstado'])
            ->name('productos.toggle');
    });
```

**Rutas generadas por Route::resource:**

| Método HTTP | Ruta | Nombre de Ruta | Controlador | Método | Función |
|-------------|------|----------------|-------------|--------|---------|
| GET | `/admin/productos` | admin.productos.index | ProductoController | index() | Listar productos |
| GET | `/admin/productos/create` | admin.productos.create | ProductoController | create() | Formulario crear |
| POST | `/admin/productos` | admin.productos.store | ProductoController | store() | Guardar producto |
| GET | `/admin/productos/{producto}` | admin.productos.show | ProductoController | show() | Ver producto |
| GET | `/admin/productos/{producto}/edit` | admin.productos.edit | ProductoController | edit() | Formulario editar |
| PUT/PATCH | `/admin/productos/{producto}` | admin.productos.update | ProductoController | update() | Actualizar producto |
| DELETE | `/admin/productos/{producto}` | admin.productos.destroy | ProductoController | destroy() | Eliminar producto |
| PATCH | `/admin/productos/{producto}/toggle` | admin.productos.toggle | ProductoController | toggleEstado() | Cambiar estado |

---

### 2.2 Controlador (app/Http/Controllers/ProductoController.php)

**Archivo**: `app/Http/Controllers/ProductoController.php`

**Modelos utilizados:**
- `App\Models\Producto`
- `App\Models\MovimientoInventario`

**Dependencias adicionales:**
- `Illuminate\Support\Facades\Storage` - Para gestión de imágenes

**Métodos del controlador:**

#### index()
```php
public function index()
{
    $productos = Producto::orderBy('nombre')->get();
    return view('admin.productos.index', compact('productos'));
}
```
- **Función**: Lista todos los productos ordenados alfabéticamente
- **Consulta**: `Producto::orderBy('nombre')->get()`
- **Vista**: `admin.productos.index`
- **Datos enviados**: `$productos` (colección de productos)

#### create()
```php
public function create()
{
    return view('admin.productos.create');
}
```
- **Función**: Muestra formulario para crear producto
- **Vista**: `admin.productos.create`
- **Datos enviados**: Ninguno (formulario vacío)

#### store()
```php
public function store(Request $request)
{
    $request->validate([
        'nombre'       => 'required|string|max:100',
        'tipo_huevo'   => 'nullable|string|max:50',
        'presentacion' => 'nullable|string|max:50',
        'descripcion'  => 'nullable|string',
        'precio'       => 'required|numeric|min:0',
        'cantidad'     => 'required|integer|min:0',
        'imagen'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        'estado'       => 'required|in:activo,inactivo',
    ], [
        'nombre.required' => 'El nombre del producto es obligatorio.',
        'precio.required' => 'El precio es obligatorio.',
        'cantidad.required' => 'La cantidad es obligatoria.',
    ]);

    // Verificar duplicado por nombre + presentación
    $existe = Producto::where('nombre', $request->nombre)
        ->where('presentacion', $request->presentacion)
        ->exists();

    if ($existe) {
        return back()->withErrors(['nombre' => 'Ya existe un producto con este nombre y presentación.'])->withInput();
    }

    // Subir imagen si se proporcionó
    $imagenPath = null;
    if ($request->hasFile('imagen')) {
        $imagenPath = $request->file('imagen')->store('productos', 'public');
    }

    $producto = Producto::create([
        'nombre'       => $request->nombre,
        'tipo_huevo'   => $request->tipo_huevo,
        'presentacion' => $request->presentacion,
        'descripcion'  => $request->descripcion,
        'precio'       => $request->precio,
        'cantidad'     => $request->cantidad,
        'imagen'       => $imagenPath,
        'estado'       => $request->estado,
    ]);

    // US-0005 escenario 6: registrar entrada inicial en inventario si cantidad > 0
    if ($request->cantidad > 0) {
        MovimientoInventario::create([
            'id_producto'     => $producto->id_producto,
            'tipo_movimiento' => 'entrada',
            'cantidad'        => $request->cantidad,
            'motivo'          => 'Inventario inicial al registrar producto',
            'fecha'           => now(),
        ]);
    }

    return redirect()->route('admin.productos.index')->with('success', 'Producto registrado correctamente.');
}
```
- **Función**: Guarda nuevo producto y registra entrada inicial en inventario
- **Validaciones**: 
  - nombre: requerido, string, max 100
  - tipo_huevo: nullable, string, max 50
  - presentacion: nullable, string, max 50
  - descripcion: nullable, string
  - precio: requerido, numeric, min 0
  - cantidad: requerido, integer, min 0
  - imagen: nullable, image, mimes: jpg,jpeg,png,webp, max 2048KB
  - estado: requerido, in: activo,inactivo
- **Validación adicional**: Verifica duplicado por nombre + presentación
- **Imagen**: Si se proporciona, se guarda en `storage/app/public/productos/`
- **Inventario automático**: Si cantidad > 0, registra movimiento de entrada
- **Operación**: `Producto::create()` + `MovimientoInventario::create()`
- **Redirección**: `admin.productos.index` con mensaje de éxito

#### edit()
```php
public function edit(Producto $producto)
{
    return view('admin.productos.edit', compact('producto'));
}
```
- **Función**: Muestra formulario para editar producto existente
- **Parámetro**: `$producto` (inyectado por route model binding)
- **Vista**: `admin.productos.edit`
- **Datos enviados**: `$producto` (datos actuales)

#### update()
```php
public function update(Request $request, Producto $producto)
{
    $request->validate([
        'nombre'       => 'required|string|max:100',
        'tipo_huevo'   => 'nullable|string|max:50',
        'presentacion' => 'nullable|string|max:50',
        'descripcion'  => 'nullable|string',
        'precio'       => 'required|numeric|min:0',
        'imagen'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        'estado'       => 'required|in:activo,inactivo',
    ]);

    $datos = [
        'nombre'       => $request->nombre,
        'tipo_huevo'   => $request->tipo_huevo,
        'presentacion' => $request->presentacion,
        'descripcion'  => $request->descripcion,
        'precio'       => $request->precio,
        'estado'       => $request->estado,
    ];

    if ($request->hasFile('imagen')) {
        // Eliminar imagen anterior si existe
        if ($producto->imagen) {
            Storage::disk('public')->delete($producto->imagen);
        }
        $datos['imagen'] = $request->file('imagen')->store('productos', 'public');
    }

    $producto->update($datos);

    return redirect()->route('admin.productos.index')->with('success', 'Producto actualizado correctamente.');
}
```
- **Función**: Actualiza datos de producto existente
- **Validaciones**: Similar a store, pero NO incluye cantidad
- **Imagen**: Si se carga nueva, elimina la anterior y guarda la nueva
- **NOTA**: No permite modificar la cantidad directamente (esto se hace por inventario)
- **Operación**: `$producto->update($datos)`
- **Redirección**: `admin.productos.index` con mensaje de éxito

#### toggleEstado()
```php
public function toggleEstado(Producto $producto)
{
    $producto->update([
        'estado' => $producto->estado === 'activo' ? 'inactivo' : 'activo',
    ]);

    return redirect()->route('admin.productos.index')->with('success', 'Estado del producto actualizado.');
}
```
- **Función**: Cambia estado entre 'activo' e 'inactivo'
- **Operación**: Toggle del campo estado
- **Redirección**: `admin.productos.index` con mensaje de éxito

#### destroy()
```php
// Método generado por Route::resource pero NO implementado en el controlador
```
- **Estado**: NO IMPLEMENTADO
- **Alternativa**: Usar `toggleEstado()` para desactivar productos

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

### 2.4 Modelo Relacionado (app/Models/MovimientoInventario.php)

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

**Relación:**
```php
public function producto()
{
    return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
}
```

---

### 2.5 Base de Datos

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

**Tabla relacionada**: `movimiento_inventario`

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

---

### 2.6 Vistas (resources/views/admin/productos/)

#### index.blade.php
- **Ubicación**: `resources/views/admin/productos/index.blade.php`
- **Función**: Lista todos los productos en tabla
- **Datos recibidos**: `$productos` (colección)
- **Elementos**:
  - Tabla con: ID, Nombre, Tipo, Presentación, Precio, Cantidad, Estado, Acciones
  - Imagen miniatura si existe
  - Botones: Editar, Toggle Estado
  - NO tiene botón Eliminar (usa toggle)

#### create.blade.php
- **Ubicación**: `resources/views/admin/productos/create.blade.php`
- **Función**: Formulario para crear nuevo producto
- **Datos recibidos**: Ninguno
- **Campos del formulario**:
  - nombre (text, required)
  - tipo_huevo (text, optional)
  - presentacion (text, optional)
  - descripcion (textarea, optional)
  - precio (number, required, step="0.01")
  - cantidad (number, required, min="0")
  - imagen (file, optional, accept="image/*")
  - estado (select, required: activo/inactivo)
- **Método**: POST
- **Action**: `route('admin.productos.store')`

#### edit.blade.php
- **Ubicación**: `resources/views/admin/productos/edit.blade.php`
- **Función**: Formulario para editar producto existente
- **Datos recibidos**: `$producto` (datos actuales)
- **Campos del formulario**:
  - nombre (text, required, valor actual)
  - tipo_huevo (text, optional, valor actual)
  - presentacion (text, optional, valor actual)
  - descripcion (textarea, optional, valor actual)
  - precio (number, required, valor actual)
  - imagen (file, optional) - muestra imagen actual si existe
  - estado (select, required, valor actual)
  - NOTA: NO incluye campo cantidad
- **Método**: PUT
- **Action**: `route('admin.productos.update', $producto)`

#### show.blade.php
- **Estado**: NO IMPLEMENTADO (el método show() existe en Route::resource pero no se usa)

---

## 3. FLUJO COMPLETO DE OPERACIONES

### 3.1 FLUJO CREATE (Crear Producto)

```
1. Usuario (Admin)
   ↓
2. Accede a: /admin/productos/create
   ↓
3. Ruta: admin.productos.create (GET)
   ↓
4. Controlador: ProductoController@create()
   ↓
5. Vista: admin.productos.create
   ↓
6. Usuario llena formulario
   ↓
7. Envía POST a: /admin/productos
   ↓
8. Ruta: admin.productos.store (POST)
   ↓
9. Controlador: ProductoController@store()
   ↓
10. Validación: $request->validate()
    ↓
11. Validación adicional: Verificar duplicado (nombre + presentación)
    ↓
12. Si hay imagen: Storage::store('productos', 'public')
    ↓
13. Modelo: Producto::create([...])
    ↓
14. Base de Datos: INSERT INTO producto
    ↓
15. Si cantidad > 0: MovimientoInventario::create([...])
    ↓
16. Base de Datos: INSERT INTO movimiento_inventario
    ↓
17. Redirección: admin.productos.index
    ↓
18. Vista: admin.productos.index con mensaje de éxito
```

**Validaciones en CREATE:**
- nombre: required, string, max:100
- tipo_huevo: nullable, string, max:50
- presentacion: nullable, string, max:50
- descripcion: nullable, string
- precio: required, numeric, min:0
- cantidad: required, integer, min:0
- imagen: nullable, image, mimes:jpg,jpeg,png,webp, max:2048
- estado: required, in:activo,inactivo

**Validación de duplicado:**
```php
$existe = Producto::where('nombre', $request->nombre)
    ->where('presentacion', $request->presentacion)
    ->exists();
```

**Registro automático en inventario:**
```php
if ($request->cantidad > 0) {
    MovimientoInventario::create([
        'id_producto'     => $producto->id_producto,
        'tipo_movimiento' => 'entrada',
        'cantidad'        => $request->cantidad,
        'motivo'          => 'Inventario inicial al registrar producto',
        'fecha'           => now(),
    ]);
}
```

---

### 3.2 FLUJO READ (Listar Productos)

```
1. Usuario (Admin)
   ↓
2. Accede a: /admin/productos
   ↓
3. Ruta: admin.productos.index (GET)
   ↓
4. Controlador: ProductoController@index()
   ↓
5. Consulta: Producto::orderBy('nombre')->get()
   ↓
6. Base de Datos: SELECT * FROM producto ORDER BY nombre
   ↓
7. Vista: admin.productos.index
   ↓
8. Usuario ve tabla de productos
```

**Consulta SQL generada:**
```sql
SELECT * FROM producto ORDER BY nombre ASC
```

---

### 3.3 FLUJO UPDATE (Actualizar Producto)

```
1. Usuario (Admin)
   ↓
2. Accede a: /admin/productos/{id}/edit
   ↓
3. Ruta: admin.productos.edit (GET)
   ↓
4. Controlador: ProductoController@edit($producto)
   ↓
5. Vista: admin.productos.edit con datos del producto
   ↓
6. Usuario modifica formulario
   ↓
7. Envía PUT a: /admin/productos/{id}
   ↓
8. Ruta: admin.productos.update (PUT)
   ↓
9. Controlador: ProductoController@update($request, $producto)
   ↓
10. Validación: $request->validate()
    ↓
11. Si nueva imagen: Eliminar anterior + guardar nueva
    ↓
12. Modelo: $producto->update($datos)
    ↓
13. Base de Datos: UPDATE producto SET ...
    ↓
14. Redirección: admin.productos.index
    ↓
15. Vista: admin.productos.index con mensaje de éxito
```

**Validaciones en UPDATE:**
- nombre: required, string, max:100
- tipo_huevo: nullable, string, max:50
- presentacion: nullable, string, max:50
- descripcion: nullable, string
- precio: required, numeric, min:0
- imagen: nullable, image, mimes:jpg,jpeg,png,webp, max:2048
- estado: required, in:activo,inactivo

**NOTA**: No se permite modificar la cantidad desde este formulario.

**Gestión de imágenes:**
```php
if ($request->hasFile('imagen')) {
    if ($producto->imagen) {
        Storage::disk('public')->delete($producto->imagen);
    }
    $datos['imagen'] = $request->file('imagen')->store('productos', 'public');
}
```

---

### 3.4 FLUJO DELETE (Eliminar Producto)

```
ESTADO: NO IMPLEMENTADO

El método destroy() NO está implementado en el controlador.
Para "eliminar" un producto, se usa toggleEstado() para desactivarlo.
```

**Alternativa - Toggle Estado:**
```
1. Usuario (Admin)
   ↓
2. Hace clic en botón Toggle Estado
   ↓
3. Envía PATCH a: /admin/productos/{id}/toggle
   ↓
4. Ruta: admin.productos.toggle (PATCH)
   ↓
5. Controlador: ProductoController@toggleEstado($producto)
   ↓
6. Modelo: $producto->update([
        'estado' => $producto->estado === 'activo' ? 'inactivo' : 'activo',
    ])
   ↓
7. Base de Datos: UPDATE producto SET estado = ?
   ↓
8. Redirección: admin.productos.index
   ↓
9. Vista: admin.productos.index con mensaje de éxito
```

**Razón de no implementar destroy():**
- Los productos pueden estar relacionados con pedidos históricos
- Eliminar un producto podría afectar la integridad de datos
- Desactivar (toggle) es más seguro para mantener historial

---

## 4. PUNTOS DE ATENCIÓN PARA MANTENIMIENTO

### 4.1 Si no se pueden crear productos
**Revisar en orden:**
1. Formulario en `resources/views/admin/productos/create.blade.php` - Verificar campos y nombres
2. Validaciones en `ProductoController@store()` - Verificar reglas de validación
3. Validación de duplicado (nombre + presentación)
4. `$fillable` en `app/Models/Producto.php` - Verificar campos permitidos
5. Migración `2026_08_13_160910_create_producto_table.php` - Verificar estructura de tabla
6. Permisos de storage - Verificar que `storage/app/public/productos/` sea escribible

### 4.2 Si no se pueden actualizar productos
**Revisar en orden:**
1. Formulario en `resources/views/admin/productos/edit.blade.php` - Verificar método PUT
2. Campo oculto `_method` con valor "PUT"
3. Validaciones en `ProductoController@update()` - Verificar reglas
4. `$fillable` en modelo - Verificar campos permitidos
5. Route model binding - Verificar que el ID se pasa correctamente
6. Gestión de imágenes - Verificar permisos de Storage

### 4.3 Si las imágenes no se guardan
**Revisar en orden:**
1. Configuración de filesystem en `config/filesystems.php`
2. Permisos de carpeta `storage/app/public/`
3. Enlace simbólico `public/storage` - Ejecutar `php artisan storage:link`
4. Validación de imagen en formulario - Verificar accept="image/*"
5. Límite de tamaño - Max 2048KB (2MB)

### 4.4 Si el inventario no se registra automáticamente
**Revisar en orden:**
1. Lógica en `ProductoController@store()` - Líneas 74-82
2. Condición `if ($request->cantidad > 0)`
3. Modelo `MovimientoInventario` - Verificar `$fillable`
4. Foreign key `id_producto` - Verificar relación con tabla producto

### 4.5 Si hay productos duplicados
**Revisar en orden:**
1. Validación de duplicado en store() - Líneas 47-54
2. Índice unique en tabla producto - NO EXISTE (validación es en código)
3. Considerar agregar índice unique (nombre, presentacion) en migración

### 4.6 Si la cantidad no se puede modificar
**Revisar en orden:**
1. Formulario edit.blade.php - Verificar que NO tenga campo cantidad
2. Esto es INTENCIONAL - La cantidad se modifica por inventario
3. Usar módulo Inventario para ajustar stock

---

## 5. RELACIONES CON OTROS MÓDULOS

### 5.1 Relación con Módulo Inventario
- Un producto tiene muchos movimientos de inventario
- `Producto::hasMany(MovimientoInventario::class, 'id_producto', 'id_producto')`
- Al crear producto con cantidad > 0, se registra movimiento automático
- Al eliminar producto, sus movimientos se eliminan en cascade

### 5.2 Relación con Módulo Carrito
- Un producto tiene muchos detalles en carritos
- `Producto::hasMany(DetalleCarrito::class, 'id_producto', 'id_producto')`
- Los productos activos pueden agregarse al carrito
- Al eliminar producto, detalles en carrito se eliminan en cascade

### 5.3 Relación con Módulo Pedidos
- Un producto tiene muchos detalles en pedidos
- `Producto::hasMany(DetallePedido::class, 'id_producto', 'id_producto')`
- Los productos activos pueden comprarse
- Al eliminar producto, detalles en pedidos se eliminan en cascade

### 5.4 Relación con Módulo Catálogo
- El catálogo muestra solo productos con estado 'activo'
- `CatalogoController@index()` filtra por estado
- Los productos inactivos no son visibles para clientes

### 5.5 Relación con Módulo Reportes
- Los reportes incluyen información de productos vendidos
- Se cuentan productos por estado de pedido
- Se relacionan con movimientos de inventario

---

## 6. ESTADO DE IMPLEMENTACIÓN

| Funcionalidad | Estado | Observaciones |
|--------------|--------|---------------|
| Listar productos | ✅ IMPLEMENTADO | Ordenado alfabéticamente |
| Crear producto | ✅ IMPLEMENTADO | Con registro automático en inventario |
| Editar producto | ✅ IMPLEMENTADO | Sin cambio de cantidad |
| Eliminar producto | ❌ NO IMPLEMENTADO | Usa toggleEstado() para desactivar |
| Toggle estado | ✅ IMPLEMENTADO | Activo/Inactivo |
| Ver detalle producto | ❌ NO UTILIZADO | Método show() existe pero no se implementa |
| Gestión de imágenes | ✅ IMPLEMENTADO | Upload, reemplazo, storage |
| Validación duplicados | ✅ IMPLEMENTADO | Por nombre + presentación |
| Modificar cantidad | ❌ NO IMPLEMENTADO | Se hace por módulo Inventario |
| Buscar productos | ❌ NO IMPLEMENTADO | Sin filtros de búsqueda |

---

## 7. ARCHIVOS RELACIONADOS

### Controladores
- `app/Http/Controllers/ProductoController.php` - Controlador principal
- `app/Http/Controllers/InventarioController.php` - Controlador de inventario
- `app/Http/Controllers/CatalogoController.php` - Controlador de catálogo

### Modelos
- `app/Models/Producto.php` - Modelo principal
- `app/Models/MovimientoInventario.php` - Modelo de movimientos
- `app/Models/DetalleCarrito.php` - Modelo de detalles en carrito
- `app/Models/DetallePedido.php` - Modelo de detalles en pedidos

### Vistas
- `resources/views/admin/productos/index.blade.php` - Lista
- `resources/views/admin/productos/create.blade.php` - Crear
- `resources/views/admin/productos/edit.blade.php` - Editar
- `resources/views/cliente/catalogo/index.blade.php` - Catálogo (lectura)
- `resources/views/cliente/catalogo/show.blade.php` - Detalle (lectura)

### Migraciones
- `database/migrations/2026_08_13_160910_create_producto_table.php` - Tabla producto
- `database/migrations/2026_08_13_160924_create_movimiento_inventario_table.php` - Tabla movimientos

### Rutas
- `routes/web.php` - Rutas del módulo (líneas 42-45)

### Configuración
- `config/filesystems.php` - Configuración de storage para imágenes

---

## 8. CARACTERÍSTICAS ESPECIALES

### 8.1 Registro Automático en Inventario
Cuando se crea un producto con cantidad > 0, el sistema automáticamente:
1. Crea el producto en tabla `producto`
2. Registra un movimiento de entrada en `movimiento_inventario`
3. El motivo es: "Inventario inicial al registrar producto"
4. Esto asegura trazabilidad completa del stock

### 8.2 Validación de Duplicados
El sistema evita productos duplicados por:
- Nombre + Presentación
- Validación en código (no en base de datos)
- Mensaje de error específico

### 8.3 Gestión de Imágenes
- Las imágenes se guardan en `storage/app/public/productos/`
- Se acceden vía enlace simbólico `public/storage`
- Formatos permitidos: jpg, jpeg, png, webp
- Tamaño máximo: 2MB
- Al actualizar, se elimina la imagen anterior

### 8.4 No Eliminación Física
- No se implementa destroy() para mantener historial
- Se usa toggleEstado() para desactivar productos
- Esto preserva relaciones con pedidos históricos
- Los productos inactivos no aparecen en catálogo

---

**Fin de documentación del módulo Productos**
