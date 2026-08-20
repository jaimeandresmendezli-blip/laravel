# DOCUMENTACIÓN MÓDULO: PEDIDOS (ADMINISTRADOR - US-0008)

**Sistema EGG EXPRESS - Gestión de Pedidos del Administrador**

---

## 1. INFORMACIÓN GENERAL

- **Código de Historia de Usuario**: US-0008 (escenario 14)
- **Módulo**: Gestión de Pedidos (Vista Administrador)
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
        // US-0008 (admin) — Pedidos
        Route::get('pedidos', [AdminPedidoController::class, 'index'])->name('pedidos.index');
        Route::get('pedidos/{pedido}', [AdminPedidoController::class, 'show'])->name('pedidos.show');
        Route::patch('pedidos/{pedido}/estado-entrega', [AdminPedidoController::class, 'actualizarEstadoEntrega'])
            ->name('pedidos.estado-entrega');
    });
```

**Rutas del módulo:**

| Método HTTP | Ruta | Nombre de Ruta | Controlador | Método | Función |
|-------------|------|----------------|-------------|--------|---------|
| GET | `/admin/pedidos` | admin.pedidos.index | AdminPedidoController | index() | Listar todos los pedidos |
| GET | `/admin/pedidos/{pedido}` | admin.pedidos.show | AdminPedidoController | show() | Ver detalle de pedido |
| PATCH | `/admin/pedidos/{pedido}/estado-entrega` | admin.pedidos.estado-entrega | AdminPedidoController | actualizarEstadoEntrega() | Actualizar estado de entrega |

**NOTA**: No usa Route::resource, usa rutas individuales. No permite crear pedidos (eso lo hacen clientes).

---

### 2.2 Controlador (app/Http/Controllers/AdminPedidoController.php)

**Archivo**: `app/Http/Controllers/AdminPedidoController.php`

**Modelos utilizados:**
- `App\Models\Pedido`

**Métodos del controlador:**

#### index()
```php
public function index(Request $request)
{
    $query = Pedido::with(['cliente', 'pago'])->orderBy('fecha', 'desc');

    if ($request->filled('estados')) {
        $query->where('estado', $request->estado);
    }
    if ($request->filled('estado_entrega')) {
        $query->where('estado_entrega', $request->estado_entrega);
    }

    $pedidos = $query->get();
    return view('admin.pedidos.index', compact('pedidos'));
}
```
- **Función**: Lista todos los pedidos del sistema con filtros opcionales
- **Consulta**: `Pedido::with(['cliente', 'pago'])->orderBy('fecha', 'desc')`
- **Filtros aplicados**:
  - `estado` - Filtra por estado de pago (pendiente, pagado, cancelado)
  - `estado_entrega` - Filtra por estado de entrega (pendiente, en_camino, entregado, cancelado)
- **Vista**: `admin.pedidos.index`
- **Datos enviados**: `$pedidos` (colección con cliente y pago cargados)

#### show()
```php
public function show(Pedido $pedido)
{
    $pedido->load(['cliente', 'detalles.producto', 'pago']);
    return view('admin.pedidos.show', compact('pedido'));
}
```
- **Función**: Muestra detalle completo de un pedido específico
- **Parámetro**: `$pedido` (inyectado por route model binding)
- **Carga eager**: `load(['cliente', 'detalles.producto', 'pago'])`
- **Relaciones cargadas**:
  - `cliente` - Datos del cliente
  - `detalles.producto` - Detalles con información de productos
  - `pago` - Información de pago
- **Vista**: `admin.pedidos.show`
- **Datos enviados**: `$pedido` (con todas las relaciones cargadas)

#### actualizarEstadoEntrega()
```php
public function actualizarEstadoEntrega(Request $request, Pedido $pedido)
{
    $request->validate([
        'estado_entrega' => 'required|in:pendiente,en_camino,entregado,cancelado',
    ]);

    $pedido->update(['estado_entrega' => $request->estado_entrega]);

    return redirect()->route('admin.pedidos.show', $pedido->id_pedido)
        ->with('success', 'Estado de entrega actualizado.');
}
```
- **Función**: Actualiza el estado de entrega de un pedido
- **Validaciones**: 
  - estado_entrega: requerido, in: pendiente,en_camino,entregado,cancelado
- **Operación**: `$pedido->update(['estado_entrega' => $request->estado_entrega])`
- **Redirección**: `admin.pedidos.show` con mensaje de éxito
- **NOTA**: No modifica el estado de pago (pendiente/pagado/cancelado)

---

### 2.3 Modelo (app/Models/Pedido.php)

**Archivo**: `app/Models/Pedido.php`

**Configuración:**
```php
protected $table = 'pedido';
protected $primaryKey = 'id_pedido';
public $timestamps = false;

protected $fillable = [
    'id_cliente', 'direccion_entrega', 'barrio', 'telefono_entrega',
    'referencia', 'observaciones', 'estado_entrega', 'fecha', 'estado', 'total',
];

protected $casts = [
    'total' => 'decimal:2',
    'fecha' => 'datetime',
];
```

**Relaciones:**
```php
public function cliente()
{
    return $this->belongsTo(Usuario::class, 'id_cliente', 'id_usuario');
}

public function detalles()
{
    return $this->hasMany(DetallePedido::class, 'id_pedido', 'id_pedido');
}

public function pago()
{
    return $this->hasOne(Pago::class, 'id_pedido', 'id_pedido');
}
```

---

### 2.4 Modelos Relacionados

#### DetallePedido (app/Models/DetallePedido.php)
**Configuración:**
```php
protected $table = 'detalle_pedido';
protected $primaryKey = 'id_detalle';
public $timestamps = false;

protected $fillable = [
    'id_pedido', 'id_producto', 'cantidad', 'precio', 'subtotal',
];

protected $casts = [
    'precio'   => 'decimal:2',
    'subtotal' => 'decimal:2',
    'cantidad' => 'integer',
];
```

**Relaciones:**
```php
public function pedido()
{
    return $this->belongsTo(Pedido::class, 'id_pedido', 'id_pedido');
}

public function producto()
{
    return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
}
```

#### Pago (app/Models/Pago.php)
**Configuración:**
```php
protected $table = 'pago';
protected $primaryKey = 'id_pago';
public $timestamps = false;

protected $fillable = [
    'id_pedido', 'metodo_pago', 'monto', 'estado_pago', 'referencia_pago', 'fecha',
];

protected $casts = [
    'monto' => 'decimal:2',
    'fecha' => 'datetime',
];
```

**Relación:**
```php
public function pedido()
{
    return $this->belongsTo(Pedido::class, 'id_pedido', 'id_pedido');
}
```

---

### 2.5 Base de Datos

**Tabla**: `pedido`

**Migración**: `database/migrations/2026_08_13_160957_create_pedido_table.php`

**Estructura:**
```php
Schema::create('pedido', function (Blueprint $table) {
    $table->integer('id_pedido')->autoIncrement();
    $table->integer('id_cliente');
    $table->string('direccion_entrega', 255);
    $table->string('barrio', 100);
    $table->string('telefono_entrega', 20);
    $table->string('referencia', 255)->nullable();
    $table->text('observaciones')->nullable();
    $table->enum('estado_entrega', ['pendiente', 'en_camino', 'entregado', 'cancelado'])->default('pendiente');
    $table->dateTime('fecha')->useCurrent();
    $table->enum('estado', ['pendiente', 'pagado', 'cancelado'])->default('pendiente');
    $table->decimal('total', 10, 2);

    $table->foreign('id_cliente')->references('id_usuario')->on('usuario')->onDelete('cascade');
});
```

**Campos:**
- `id_pedido` - Primary key, auto increment
- `id_cliente` - Foreign key Usuario (cliente que hizo el pedido)
- `direccion_entrega` - Dirección de entrega
- `barrio` - Barrio de entrega
- `telefono_entrega` - Teléfono para contacto
- `referencia` - Referencia de ubicación (opcional)
- `observaciones` - Observaciones del cliente (opcional)
- `estado_entrega` - Enum: pendiente, en_camino, entregado, cancelado
- `fecha` - Fecha del pedido, current timestamp
- `estado` - Enum: pendiente, pagado, cancelado (estado de pago)
- `total` - Total del pedido (decimal 10,2)

**Foreign key**: `id_cliente` con ON DELETE CASCADE

**Tabla relacionada**: `detalle_pedido`

**Estructura:**
```php
Schema::create('detalle_pedido', function (Blueprint $table) {
    $table->integer('id_detalle')->autoIncrement();
    $table->integer('id_pedido');
    $table->integer('id_producto');
    $table->integer('cantidad');
    $table->decimal('precio', 10, 2);
    $table->decimal('subtotal', 10, 2);

    $table->unique(['id_pedido', 'id_producto'], 'unique_producto_pedido');

    $table->foreign('id_pedido')->references('id_pedido')->on('pedido')->onDelete('cascade');
    $table->foreign('id_producto')->references('id_producto')->on('producto')->onDelete('cascade');
});
```

**Tabla relacionada**: `pago`

**Estructura:**
```php
Schema::create('pago', function (Blueprint $table) {
    $table->integer('id_pago')->autoIncrement();
    $table->integer('id_pedido');
    $table->enum('metodo_pago', ['efectivo', 'transferencia', 'nequi']);
    $table->decimal('monto', 10, 2);
    $table->enum('estado_pago', ['pendiente', 'pagado', 'rechazado'])->default('pendiente');
    $table->string('referencia_pago', 100)->nullable();
    $table->dateTime('fecha')->useCurrent();

    $table->foreign('id_pedido')->references('id_pedido')->on('pedido')->onDelete('cascade');
});
```

---

### 2.6 Vistas (resources/views/admin/pedidos/)

#### index.blade.php
- **Ubicación**: `resources/views/admin/pedidos/index.blade.php`
- **Función**: Lista todos los pedidos del sistema
- **Datos recibidos**: `$pedidos` (colección con cliente y pago)
- **Elementos**:
  - Formulario de filtros:
    - Select de estado (pendiente, pagado, cancelado)
    - Select de estado de entrega (pendiente, en_camino, entregado, cancelado)
  - Tabla de pedidos:
    - ID, Cliente, Fecha, Total, Estado Pago, Estado Entrega, Acciones
    - Colores diferenciados por estado
  - Botón: Ver detalle

#### show.blade.php
- **Ubicación**: `resources/views/admin/pedidos/show.blade.php`
- **Función**: Muestra detalle completo de un pedido
- **Datos recibidos**: `$pedido` (con cliente, detalles.producto, pago)
- **Elementos**:
  - Información del cliente: nombre, correo, teléfono
  - Información de entrega: dirección, barrio, referencia, observaciones
  - Información del pedido: fecha, total, estado pago, estado entrega
  - Tabla de detalles:
    - Producto, Cantidad, Precio unitario, Subtotal
  - Información de pago:
    - Método, monto, estado, referencia
  - Formulario para actualizar estado de entrega:
    - Select de estado entrega
    - Botón actualizar

---

## 3. FLUJO COMPLETO DE OPERACIONES

### 3.1 FLUJO READ (Listar Pedidos)

```
1. Usuario (Admin)
   ↓
2. Accede a: /admin/pedidos
   ↓
3. Ruta: admin.pedidos.index (GET)
   ↓
4. Controlador: AdminPedidoController@index($request)
    ↓
5. Consulta base: Pedido::with(['cliente', 'pago'])->orderBy('fecha', 'desc')
    ↓
6. Aplicar filtros (si existen):
    ↓
7. - estado: ->where('estado', $request->estado)
    ↓
8. - estado_entrega: ->where('estado_entrega', $request->estado_entrega)
    ↓
9. Vista: admin.pedidos.index
    ↓
10. Usuario ve lista de pedidos filtrados
```

**Consulta SQL generada (sin filtros):**
```sql
SELECT p.*, u.nombre as cliente_nombre, u.correo as cliente_correo,
       pg.metodo_pago, pg.monto, pg.estado_pago
FROM pedido p
LEFT JOIN usuario u ON p.id_cliente = u.id_usuario
LEFT JOIN pago pg ON p.id_pedido = pg.id_pedido
ORDER BY p.fecha DESC
```

---

### 3.2 FLUJO READ (Ver Detalle de Pedido)

```
1. Usuario (Admin)
   ↓
2. Hace clic en "Ver detalle" de un pedido
   ↓
3. Accede a: /admin/pedidos/{id}
   ↓
4. Ruta: admin.pedidos.show (GET)
   ↓
5. Controlador: AdminPedidoController@show($pedido)
    ↓
6. Carga eager: $pedido->load(['cliente', 'detalles.producto', 'pago'])
    ↓
7. Consultas adicionales:
    ↓
8. - Cliente: SELECT * FROM usuario WHERE id_usuario = ?
    ↓
9. - Detalles: SELECT * FROM detalle_pedido WHERE id_pedido = ?
    ↓
10. - Productos: SELECT * FROM producto WHERE id_producto IN (...)
    ↓
11. - Pago: SELECT * FROM pago WHERE id_pedido = ?
    ↓
12. Vista: admin.pedidos.show
    ↓
13. Usuario ve detalle completo del pedido
```

---

### 3.3 FLUJO UPDATE (Actualizar Estado de Entrega)

```
1. Usuario (Admin)
   ↓
2. En vista de detalle, selecciona nuevo estado de entrega
   ↓
3. Envía PATCH a: /admin/pedidos/{id}/estado-entrega
   ↓
4. Ruta: admin.pedidos.estado-entrega (PATCH)
   ↓
5. Controlador: AdminPedidoController@actualizarEstadoEntrega($request, $pedido)
    ↓
6. Validación: $request->validate()
    ↓
7. Modelo: $pedido->update(['estado_entrega' => $request->estado_entrega])
    ↓
8. Base de Datos: UPDATE pedido SET estado_entrega = ?
    ↓
9. Redirección: admin.pedidos.show
    ↓
10. Vista: admin.pedidos.show con mensaje de éxito
```

**Validaciones en UPDATE:**
- estado_entrega: required, in:pendiente,en_camino,entregado,cancelado

**Estados de entrega:**
- `pendiente` - Pedido recibido, no enviado
- `en_camino` - Pedido en ruta de entrega
- `entregado` - Pedido entregado al cliente
- `cancelado` - Pedido cancelado por administrador

**NOTA**: Este método NO modifica el estado de pago (pendiente/pagado/cancelado).

---

### 3.4 FLUJO CREATE (Crear Pedido)

```
ESTADO: NO IMPLEMENTADO

Los administradores NO pueden crear pedidos.
Solo los clientes pueden crear pedidos a través de su módulo.
```

**Razón**: Los pedidos son iniciados por clientes desde el carrito de compras.

---

### 3.5 FLUJO DELETE (Eliminar Pedido)

```
ESTADO: NO IMPLEMENTADO

Los administradores NO pueden eliminar pedidos.
Los clientes pueden cancelar sus propios pedidos en estado pendiente.
```

**Razón**: Los pedidos se eliminan lógicamente mediante cancelación, no eliminación física.

---

## 4. PUNTOS DE ATENCIÓN PARA MANTENIMIENTO

### 4.1 Si no se pueden ver los pedidos
**Revisar en orden:**
1. Vista en `resources/views/admin/pedidos/index.blade.php` - Verificar iteración
2. Consulta en `AdminPedidoController@index()` - Verificar with()
3. Relaciones en modelo Pedido - Verificar cliente() y pago()
4. Foreign keys en base de datos - Verificar integridad referencial
5. Datos de prueba - Verificar que existan pedidos en base de datos

### 4.2 Si no se carga el detalle del pedido
**Revisar en orden:**
1. Método `load()` en show() - Verificar relaciones cargadas
2. Vista show.blade.php - Verificar acceso a relaciones
3. Route model binding - Verificar que el ID se pase correctamente
4. Relaciones anidadas - Verificar detalles.producto

### 4.3 Si no se puede actualizar el estado de entrega
**Revisar en orden:**
1. Formulario en show.blade.php - Verificar método PATCH
2. Campo oculto `_method` con valor "PATCH"
3. Validaciones en actualizarEstadoEntrega() - Verificar reglas
4. `$fillable` en modelo Pedido - Verificar campo estado_entrega
5. Ruta PATCH - Verificar que exista y apunte al método correcto

### 4.4 Si los filtros no funcionan
**Revisar en orden:**
1. Formulario de filtros en index.blade.php - Verificar nombres de campos
2. Método `filled()` en controlador - Verificar que detecte parámetros
3. Consultas condicionales - Verificar lógica de where
4. Nombres de parámetros - Verificar que coincidan con el request

### 4.5 Si no aparecen los datos del cliente
**Revisar en orden:**
1. Relación cliente() en modelo Pedido - Verificar belongsTo
2. Foreign key id_cliente - Verificar que apunte a id_usuario
3. Consulta with() - Verificar que incluya 'cliente'
4. Vista - Verificar acceso a $pedido->cliente

### 4.6 Si no aparecen los detalles del pedido
**Revisar en orden:**
1. Relación detalles() en modelo Pedido - Verificar hasMany
2. Relación producto() en modelo DetallePedido - Verificar belongsTo
3. Consulta load() - Verificar que incluya 'detalles.producto'
4. Vista - Verificar iteración sobre $pedido->detalles

---

## 5. RELACIONES CON OTROS MÓDULOS

### 5.1 Relación con Módulo Usuarios
- Un pedido pertenece a un cliente (usuario)
- `Pedido::belongsTo(Usuario::class, 'id_cliente', 'id_usuario')`
- Al eliminar usuario, sus pedidos se eliminan en cascade
- El cliente puede ver solo sus propios pedidos

### 5.2 Relación con Módulo Pedidos (Cliente)
- Los pedidos son creados por clientes
- `ClientePedidoController@store()` crea los pedidos
- Los clientes pueden cancelar sus pedidos pendientes
- Los administradores solo pueden ver y actualizar estado de entrega

### 5.3 Relación con Módulo Carrito
- Los pedidos se generan desde el carrito
- `ClientePedidoController@store()` convierte carrito en pedido
- El carrito se marca como 'comprado' después de generar pedido
- Los detalles del carrito se convierten en detalles del pedido

### 5.4 Relación con Módulo Inventario
- Los pedidos generan movimientos automáticos de salida
- `ClientePedidoController@store()` registra salidas por venta
- Las cancelaciones generan movimientos automáticos de entrada
- El stock se actualiza automáticamente

### 5.5 Relación con Módulo Productos
- Los detalles del pedido contienen productos
- `DetallePedido::belongsTo(Producto::class, 'id_producto', 'id_producto')`
- Al eliminar producto, detalles en pedidos se eliminan en cascade
- Los precios se guardan en el momento del pedido

### 5.6 Relación con Módulo Reportes
- Los reportes incluyen información de pedidos
- Se filtran por rango de fechas
- Se cuentan por estado (pendiente, pagado, cancelado)
- Se calculan totales de ventas

---

## 6. ESTADO DE IMPLEMENTACIÓN

| Funcionalidad | Estado | Observaciones |
|--------------|--------|---------------|
| Listar todos los pedidos | ✅ IMPLEMENTADO | Con filtros por estado y estado entrega |
| Ver detalle de pedido | ✅ IMPLEMENTADO | Con cliente, detalles y pago |
| Actualizar estado de entrega | ✅ IMPLEMENTADO | US-0008 escenario 14 |
| Crear pedido | ❌ NO IMPLEMENTADO | Solo clientes pueden crear |
| Eliminar pedido | ❌ NO IMPLEMENTADO | Solo clientes pueden cancelar |
| Actualizar estado de pago | ❌ NO IMPLEMENTADO | No implementado en admin |
| Buscar pedidos | ❌ NO IMPLEMENTADO | Solo filtros básicos |
| Asignar repartidor | ❌ NO IMPLEMENTADO | No hay módulo de repartidores |

---

## 7. ARCHIVOS RELACIONADOS

### Controladores
- `app/Http/Controllers/AdminPedidoController.php` - Controlador principal (admin)
- `app/Http/Controllers/ClientePedidoController.php` - Controlador de pedidos (cliente)

### Modelos
- `app/Models/Pedido.php` - Modelo principal
- `app/Models/DetallePedido.php` - Modelo de detalles
- `app/Models/Pago.php` - Modelo de pagos
- `app/Models/Usuario.php` - Modelo de clientes

### Vistas
- `resources/views/admin/pedidos/index.blade.php` - Lista de pedidos
- `resources/views/admin/pedidos/show.blade.php` - Detalle de pedido
- `resources/views/cliente/pedidos/index.blade.php` - Pedidos del cliente
- `resources/views/cliente/pedidos/show.blade.php` - Detalle (vista cliente)

### Migraciones
- `database/migrations/2026_08_13_160957_create_pedido_table.php` - Tabla pedido
- `database/migrations/2026_08_13_161009_create_detalle_pedido_table.php` - Tabla detalle_pedido
- `database/migrations/2026_08_13_161019_create_pago_table.php` - Tabla pago

### Rutas
- `routes/web.php` - Rutas del módulo (líneas 52-56)

---

## 8. CARACTERÍSTICAS ESPECIALES

### 8.1 Separación de Estados
El sistema maneja dos estados independientes:
- **estado**: Estado de pago (pendiente, pagado, cancelado)
- **estado_entrega**: Estado logístico (pendiente, en_camino, entregado, cancelado)

Esto permite que un pedido esté pagado pero aún no entregado, o entregado pero pendiente de pago.

### 8.2 Solo Lectura para Administrador
Los administradores tienen acceso limitado:
- ✅ Pueden ver todos los pedidos
- ✅ Pueden ver detalles completos
- ✅ Pueden actualizar estado de entrega
- ❌ No pueden crear pedidos
- ❌ No pueden eliminar pedidos
- ❌ No pueden modificar estado de pago

### 8.3 Filtros Independientes
Los filtros funcionan de manera independiente:
- Se puede filtrar por estado de pago
- Se puede filtrar por estado de entrega
- Se pueden combinar ambos filtros
- Los filtros son opcionales

### 8.4 Carga Eager de Relaciones
Para optimizar rendimiento:
- `index()` con `with(['cliente', 'pago'])`
- `show()` con `load(['cliente', 'detalles.producto', 'pago'])`
- Evita problema N+1 de consultas

### 8.5 Información Completa en Detalle
La vista show muestra toda la información:
- Datos del cliente
- Datos de entrega
- Datos del pedido
- Detalles con productos
- Información de pago
- Formulario para actualizar estado

---

## 9. ESCENARIOS DE USO

### 9.1 Escenario 1: Consultar Todos los Pedidos
```
1. Admin accede a /admin/pedidos
2. Sistema muestra todos los pedidos ordenados por fecha
3. Admin ve información básica de cada pedido
4. Admin puede filtrar por estado
5. Admin puede hacer clic en "Ver detalle"
```

### 9.2 Escenario 2: Ver Detalle de Pedido
```
1. Admin hace clic en "Ver detalle" de un pedido
2. Sistema carga información completa del pedido
3. Admin ve datos del cliente
4. Admin ve dirección de entrega
5. Admin ve detalles de productos
6. Admin ve información de pago
7. Admin puede actualizar estado de entrega
```

### 9.3 Escenario 3: Actualizar Estado de Entrega
```
1. Admin en vista de detalle de pedido
2. Selecciona nuevo estado: "en_camino"
3. Envía formulario
4. Sistema actualiza estado_entrega a "en_camino"
5. Sistema redirige a vista de detalle
6. Admin ve estado actualizado
```

### 9.4 Escenario 4: Filtrar Pedidos
```
1. Admin en lista de pedidos
2. Selecciona filtro: estado = "pagado"
3. Sistema muestra solo pedidos pagados
4. Admin puede agregar filtro: estado_entrega = "entregado"
5. Sistema muestra pedidos pagados y entregados
```

### 9.5 Escenario 5: Seguimiento de Pedido
```
1. Cliente realiza pedido
2. Sistema crea pedido con estado_entrega = "pendiente"
3. Admin ve pedido en lista
4. Admin actualiza a "en_camino"
5. Admin actualiza a "entregado"
6. Cliente puede ver estado en su módulo
```

---

## 10. REGLAS DE NEGOCIO

### 10.1 Reglas de Acceso
- Solo administradores pueden ver todos los pedidos
- Los clientes solo ven sus propios pedidos
- Los administradores no pueden crear pedidos
- Los administradores no pueden eliminar pedidos

### 10.2 Reglas de Estados
- **estado_entrega**: pendiente → en_camino → entregado (flujo normal)
- **estado_entrega**: puede ir a cancelado en cualquier momento
- **estado**: pendiente → pagado (cuando se confirma pago)
- **estado**: puede ir a cancelado (solo si está pendiente)

### 10.3 Reglas de Actualización
- Solo se puede actualizar estado_entrega desde admin
- El estado de pago NO se modifica desde admin
- Los cambios de estado deben ser registrados
- No se puede modificar el historial de estados

### 10.4 Reglas de Integridad
- Los pedidos no se eliminan físicamente
- Los pedidos cancelados se mantienen para historial
- Los detalles de pedido son inmutables
- Los precios se guardan al momento del pedido

---

**Fin de documentación del módulo Pedidos (Administrador)**
