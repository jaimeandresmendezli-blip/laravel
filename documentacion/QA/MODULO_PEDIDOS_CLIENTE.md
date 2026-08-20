# DOCUMENTACIÓN MÓDULO: PEDIDOS (CLIENTE - US-0008)

**Sistema EGG EXPRESS - Gestión de Pedidos del Cliente**

---

## 1. INFORMACIÓN GENERAL

- **Código de Historia de Usuario**: US-0008 (escenarios 1, 3, 9, 10, 11, 12, 13)
- **Módulo**: Gestión de Pedidos (Vista Cliente)
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
        // US-0008 — Pedidos
        Route::get('pedidos', [ClientePedidoController::class, 'index'])->name('pedidos.index');
        Route::get('pedidos/crear', [ClientePedidoController::class, 'create'])->name('pedidos.create');
        Route::post('pedidos', [ClientePedidoController::class, 'store'])->name('pedidos.store');
        Route::get('pedidos/{pedido}', [ClientePedidoController::class, 'show'])->name('pedidos.show');
        Route::delete('pedidos/{pedido}/cancelar', [ClientePedidoController::class, 'cancelar'])->name('pedidos.cancelar');
    });
```

**Rutas del módulo:**

| Método HTTP | Ruta | Nombre de Ruta | Controlador | Método | Función |
|-------------|------|----------------|-------------|--------|---------|
| GET | `/cliente/pedidos` | cliente.pedidos.index | ClientePedidoController | index() | Mis pedidos con resumen |
| GET | `/cliente/pedidos/crear` | cliente.pedidos.create | ClientePedidoController | create() | Formulario confirmación pedido |
| POST | `/cliente/pedidos` | cliente.pedidos.store | ClientePedidoController | store() | Confirmar y guardar pedido |
| GET | `/cliente/pedidos/{pedido}` | cliente.pedidos.show | ClientePedidoController | show() | Ver detalle de mi pedido |
| DELETE | `/cliente/pedidos/{pedido}/cancelar` | cliente.pedidos.cancelar | ClientePedidoController | cancelar() | Cancelar mi pedido |

**NOTA**: No usa Route::resource, usa rutas individuales. Solo permite ver y cancelar pedidos propios.

---

### 2.2 Controlador (app/Http/Controllers/ClientePedidoController.php)

**Archivo**: `app/Http/Controllers/ClientePedidoController.php`

**Modelos utilizados:**
- `App\Models\Pedido`
- `App\Models\Carrito`
- `App\Models\DetallePedido`
- `App\Models\Pago`
- `App\Models\MovimientoInventario`
- `App\Models\Producto`

**Dependencias adicionales:**
- `Illuminate\Support\Facades\Auth`
- `Illuminate\Support\Facades\DB`
- `Illuminate\Support\Facades\Auth`

**Métodos del controlador:**

#### index()
```php
public function index()
{
    $pedidos           = Pedido::where('id_cliente', Auth::id())->orderBy('fecha', 'desc')->get();
    $totalPedidos      = $pedidos->count();
    $pedidosPendientes = $pedidos->where('estado', 'pendiente')->count();
    $pedidosPagados    = $pedidos->where('estado', 'pagado')->count();

    return view('cliente.pedidos.index', compact('pedidos', 'totalPedidos', 'pedidosPendientes', 'pedidosPagados'));
}
```
- **Función**: Lista pedidos del cliente autenticado con resumen de totales
- **Consulta**: `Pedido::where('id_cliente', Auth::id())->orderBy('fecha', 'desc')->get()`
- **Filtro**: Solo pedidos del cliente autenticado
- **Cálculos**:
  - `totalPedidos` - Total de pedidos
  - `pedidosPendientes` - Pedidos en estado pendiente
  - `pedidosPagados` - Pedidos en estado pagado
- **Vista**: `cliente.pedidos.index`
- **Datos enviados**: `$pedidos`, `$totalPedidos`, `$pedidosPendientes`, `$pedidosPagados`

#### create()
```php
public function create()
{
    $carrito = Carrito::where('id_usuario', Auth::id())
        ->where('estado', 'activo')
        ->with('detalles.producto')
        ->first();

    if (!$carrito || $carrito->detalles->isEmpty()) {
        return redirect()->route('cliente.carrito.index')
            ->withErrors(['error' => 'Tu carrito está vacío.']);
    }

    $total = $carrito->detalles->sum('subtotal');

    return view('cliente.pedidos.create', compact('carrito', 'total'));
}
```
- **Función**: Muestra formulario de confirmación de pedido desde carrito
- **Consulta**: `Carrito::where('id_usuario', Auth::id())->where('estado', 'activo')->with('detalles.producto')->first()`
- **Validación**: Verifica que el carrito exista y tenga ítems
- **Cálculo**: `$total = $carrito->detalles->sum('subtotal')`
- **Vista**: `cliente.pedidos.create`
- **Datos enviados**: `$carrito` (con detalles y productos), `$total`

#### store()
```php
public function store(Request $request)
{
    $request->validate([
        'direccion_entrega' => 'required|string|max:255',
        'barrio'            => 'required|string|max:100',
        'telefono_entrega'  => 'required|string|max:20',
        'referencia'        => 'nullable|string|max:255',
        'observaciones'     => 'nullable|string',
        'metodo_pago'       => 'required|in:efectivo,transferencia,nequi',
        'referencia_pago'   => 'nullable|string|max:100',
    ], [
        'direccion_entrega.required' => 'La dirección de entrega es obligatoria.',
        'barrio.required'            => 'El barrio es obligatorio.',
        'telefono_entrega.required'  => 'El teléfono de entrega es obligatorio.',
        'metodo_pago.required'       => 'Selecciona un método de pago.',
    ]);

    $carrito = Carrito::where('id_usuario', Auth::id())
        ->where('estado', 'activo')
        ->with('detalles.producto')
        ->first();

    if (!$carrito || $carrito->detalles->isEmpty()) {
        return redirect()->route('cliente.carrito.index')
            ->withErrors(['error' => 'Tu carrito está vacío.']);
    }

    // Verificar stock de todos los productos antes de confirmar (US-0005 escenario 4)
    foreach ($carrito->detalles as $detalle) {
        if ($detalle->producto->cantidad < $detalle->cantidad) {
            return back()->withErrors([
                'error' => "Stock insuficiente para '{$detalle->producto->nombre}'. Solo hay {$detalle->producto->cantidad} unidades.",
            ]);
        }
    }

    DB::transaction(function () use ($request, $carrito) {
        $total = $carrito->detalles->sum('subtotal');

        // Crear pedido
        $pedido = Pedido::create([
            'id_cliente'        => Auth::id(),
            'direccion_entrega' => $request->direccion_entrega,
            'barrio'            => $request->barrio,
            'telefono_entrega'  => $request->telefono_entrega,
            'referencia'        => $request->referencia,
            'observaciones'     => $request->observaciones,
            'estado_entrega'    => 'pendiente',
            'fecha'             => now(),
            'estado'            => 'pendiente',
            'total'             => $total,
        ]);

        // Crear detalles del pedido y descontar stock
        foreach ($carrito->detalles as $detalle) {
            DetallePedido::create([
                'id_pedido'   => $pedido->id_pedido,
                'id_producto' => $detalle->id_producto,
                'cantidad'    => $detalle->cantidad,
                'precio'      => $detalle->precio_unitario,
                'subtotal'    => $detalle->subtotal,
            ]);

            // US-0005 escenario 2: reducir stock automáticamente
            $detalle->producto->decrement('cantidad', $detalle->cantidad);

            // Registrar movimiento de salida en inventario
            MovimientoInventario::create([
                'id_producto'     => $detalle->id_producto,
                'tipo_movimiento' => 'salida',
                'cantidad'        => $detalle->cantidad,
                'motivo'          => "Venta - Pedido #{$pedido->id_pedido}",
                'fecha'           => now(),
            ]);
        }

        // Crear registro de pago
        Pago::create([
            'id_pedido'       => $pedido->id_pedido,
            'metodo_pago'     => $request->metodo_pago,
            'monto'           => $total,
            'estado_pago'     => 'pendiente',
            'referencia_pago' => $request->referencia_pago,
            'fecha'           => now(),
        ]);

        // Marcar carrito como comprado
        $carrito->update(['estado' => 'comprado']);
    });

    return redirect()->route('cliente.pedidos.index')
        ->with('success', 'Pedido registrado correctamente.');
}
```
- **Función**: Convierte carrito en pedido, descuenta stock, registra movimientos
- **Validaciones**: 
  - direccion_entrega: requerido, string, max 255
  - barrio: requerido, string, max 100
  - telefono_entrega: requerido, string, max 20
  - referencia: nullable, string, max 255
  - observaciones: nullable, string
  - metodo_pago: requerido, in: efectivo,transferencia,nequi
  - referencia_pago: nullable, string, max 100
- **Validación de stock**: Verifica stock de todos los productos antes de confirmar
- **Transacción DB**: Usa `DB::transaction()` para asegurar integridad
- **Operaciones dentro de transacción**:
  1. Crear pedido con datos de entrega
  2. Crear detalles del pedido
  3. Decrementar stock de cada producto (US-0005 escenario 2)
  4. Registrar movimiento de salida en inventario
  5. Crear registro de pago
  6. Marcar carrito como 'comprado'
- **Redirección**: `cliente.pedidos.index` con mensaje de éxito

#### show()
```php
public function show(Pedido $pedido)
{
    // Verificar que el pedido pertenece al cliente
    if ($pedido->id_cliente !== Auth::id()) {
        abort(403);
    }

    $pedido->load(['detalles.producto', 'pago']);

    return view('cliente.pedidos.show', compact('pedido'));
}
```
- **Función**: Muestra detalle de un pedido propio
- **Parámetro**: `$pedido` (inyectado por route model binding)
- **Validación de seguridad**: Verifica que el pedido pertenezca al cliente autenticado
- **Carga eager**: `load(['detalles.producto', 'pago'])`
- **Vista**: `cliente.pedidos.show`
- **Datos enviados**: `$pedido` (con detalles y pago)

#### cancelar()
```php
public function cancelar(Pedido $pedido)
{
    if ($pedido->id_cliente !== Auth::id()) {
        abort(403);
    }

    // Solo se puede cancelar si está pendiente (US-0008 escenario 9)
    if ($pedido->estado !== 'pendiente') {
        return back()->withErrors(['error' => 'Solo puedes cancelar pedidos en estado pendiente.']);
    }

    DB::transaction(function () use ($pedido) {
        $pedido->load('detalles.producto');

        // US-0005 escenario 3: devolver stock automáticamente
        foreach ($pedido->detalles as $detalle) {
            $detalle->producto->increment('cantidad', $detalle->cantidad);

            MovimientoInventario::create([
                'id_producto'     => $detalle->id_producto,
                'tipo_movimiento' => 'entrada',
                'cantidad'        => $detalle->cantidad,
                'motivo'          => "Reintegro por cancelación - Pedido #{$pedido->id_pedido}",
                'fecha'           => now(),
            ]);
        }

        $pedido->update(['estado' => 'cancelado', 'estado_entrega' => 'cancelado']);
    });

    return redirect()->route('cliente.pedidos.index')
        ->with('success', 'Pedido cancelado. El stock fue devuelto.');
}
```
- **Función**: Cancela pedido pendiente y devuelve stock automáticamente
- **Validación de seguridad**: Verifica que el pedido pertenezca al cliente
- **Validación de estado**: Solo permite cancelar pedidos en estado 'pendiente' (US-0008 escenario 9)
- **Transacción DB**: Usa `DB::transaction()` para asegurar integridad
- **Operaciones dentro de transacción**:
  1. Cargar detalles con productos
  2. Incrementar stock de cada producto (US-0005 escenario 3)
  3. Registrar movimiento de entrada en inventario
  4. Actualizar estado del pedido a 'cancelado'
  5. Actualizar estado_entrega a 'cancelado'
- **Redirección**: `cliente.pedidos.index` con mensaje de éxito

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

#### Carrito (app/Models/Carrito.php)
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

**Tabla relacionada**: `carrito`

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

---

### 2.6 Vistas (resources/views/cliente/pedidos/)

#### index.blade.php
- **Ubicación**: `resources/views/cliente/pedidos/index.blade.php`
- **Función**: Lista pedidos del cliente con resumen de totales
- **Datos recibidos**: `$pedidos`, `$totalPedidos`, `$pedidosPendientes`, `$pedidosPagados`
- **Elementos**:
  - Resumen de estadísticas:
    - Total de pedidos
    - Pedidos pendientes
    - Pedidos pagados
  - Tabla de pedidos:
    - ID, Fecha, Total, Estado, Estado Entrega, Acciones
    - Colores diferenciados por estado
  - Botones: Ver detalle, Cancelar (si está pendiente)

#### create.blade.php
- **Ubicación**: `resources/views/cliente/pedidos/create.blade.php`
- **Función**: Formulario de confirmación de pedido desde carrito
- **Datos recibidos**: `$carrito` (con detalles y productos), `$total`
- **Elementos**:
  - Resumen del carrito:
    - Lista de productos con cantidades y subtotales
    - Total a pagar
  - Formulario de entrega:
    - direccion_entrega (text, required)
    - barrio (text, required)
    - telefono_entrega (text, required)
    - referencia (text, optional)
    - observaciones (textarea, optional)
  - Formulario de pago:
    - metodo_pago (select, required: efectivo, transferencia, nequi)
    - referencia_pago (text, optional)
  - Botón: Confirmar pedido
- **Método**: POST
- **Action**: `route('cliente.pedidos.store')`

#### show.blade.php
- **Ubicación**: `resources/views/cliente/pedidos/show.blade.php`
- **Función**: Muestra detalle de un pedido propio
- **Datos recibidos**: `$pedido` (con detalles.producto y pago)
- **Elementos**:
  - Información del pedido:
    - ID, Fecha, Total
    - Estado de pago
    - Estado de entrega
  - Información de entrega:
    - Dirección, barrio, teléfono, referencia, observaciones
  - Tabla de detalles:
    - Producto, cantidad, precio unitario, subtotal
  - Información de pago:
    - Método, monto, estado, referencia
  - Botón: Cancelar pedido (si está pendiente)

---

## 3. FLUJO COMPLETO DE OPERACIONES

### 3.1 FLUJO READ (Mis Pedidos)

```
1. Usuario (Cliente)
   ↓
2. Accede a: /cliente/pedidos
   ↓
3. Ruta: cliente.pedidos.index (GET)
   ↓
4. Controlador: ClientePedidoController@index()
    ↓
5. Consulta: Pedido::where('id_cliente', Auth::id())->orderBy('fecha', 'desc')->get()
    ↓
6. Cálculos:
    ↓
7. - totalPedidos = $pedidos->count()
    ↓
8. - pedidosPendientes = $pedidos->where('estado', 'pendiente')->count()
    ↓
9. - pedidosPagados = $pedidos->where('estado', 'pagado')->count()
    ↓
10. Vista: cliente.pedidos.index
    ↓
11. Usuario ve sus pedidos con resumen
```

**Consulta SQL generada:**
```sql
SELECT * FROM pedido 
WHERE id_cliente = ? 
ORDER BY fecha DESC
```

---

### 3.2 FLUJO CREATE (Confirmar Pedido desde Carrito)

```
1. Usuario (Cliente)
   ↓
2. Accede a: /cliente/pedidos/crear
   ↓
3. Ruta: cliente.pedidos.create (GET)
   ↓
4. Controlador: ClientePedidoController@create()
    ↓
5. Consulta: Carrito::where('id_usuario', Auth::id())->where('estado', 'activo')->with('detalles.producto')->first()
    ↓
6. Validación: if (!$carrito || $carrito->detalles->isEmpty())
    ↓
7. Si carrito vacío → Redirección a carrito con error
    ↓
8. Si carrito tiene ítems → Vista: cliente.pedidos.create
    ↓
9. Usuario llena datos de entrega y pago
    ↓
10. Envía POST a: /cliente/pedidos
    ↓
11. Ruta: cliente.pedidos.store (POST)
    ↓
12. Controlador: ClientePedidoController@store()
    ↓
13. Validación: $request->validate()
    ↓
14. Consulta carrito: Carrito::where('id_usuario', Auth::id())->where('estado', 'activo')->with('detalles.producto')->first()
    ↓
15. Validación: if (!$carrito || $carrito->detalles->isEmpty())
    ↓
16. Verificación de stock (US-0005 escenario 4):
    ↓
17. foreach ($carrito->detalles as $detalle)
    ↓
18. if ($detalle->producto->cantidad < $detalle->cantidad)
    ↓
19. Si stock insuficiente → Error y redirección
    ↓
20. Si stock suficiente → DB::transaction(function () use ($request, $carrito)
    ↓
21. Dentro de transacción:
    ↓
22. 1. Pedido::create([...]) - Crear pedido
    ↓
23. 2. foreach ($carrito->detalles as $detalle)
    ↓
24.    a. DetallePedido::create([...]) - Crear detalle
    ↓
25.    b. $detalle->producto->decrement('cantidad', $detalle->cantidad) - Reducir stock (US-0005 escenario 2)
    ↓
26.    c. MovimientoInventario::create([...]) - Registrar salida
    ↓
27. 3. Pago::create([...]) - Crear registro de pago
    ↓
28. 4. $carrito->update(['estado' => 'comprado']) - Marcar carrito como comprado
    ↓
29. Fin de transacción
    ↓
30. Redirección: cliente.pedidos.index
    ↓
31. Vista: cliente.pedidos.index con mensaje de éxito
```

**Validaciones en CREATE:**
- direccion_entrega: required, string, max:255
- barrio: required, string, max:100
- telefono_entrega: required, string, max:20
- referencia: nullable, string, max:255
- observaciones: nullable, string
- metodo_pago: required, in:efectivo,transferencia,nequi
- referencia_pago: nullable, string, max:100

**Verificación de stock (US-0005 escenario 4):**
```php
foreach ($carrito->detalles as $detalle) {
    if ($detalle->producto->cantidad < $detalle->cantidad) {
        return back()->withErrors([
            'error' => "Stock insuficiente para '{$detalle->producto->nombre}'. Solo hay {$detalle->producto->cantidad} unidades.",
        ]);
    }
}
```

**Operaciones en transacción:**
1. Crear pedido con datos de entrega
2. Por cada detalle del carrito:
   - Crear detalle del pedido
   - Decrementar stock del producto (US-0005 escenario 2)
   - Registrar movimiento de salida en inventario
3. Crear registro de pago
4. Marcar carrito como 'comprado'

---

### 3.3 FLUJO READ (Ver Detalle de Mi Pedido)

```
1. Usuario (Cliente)
   ↓
2. Hace clic en "Ver detalle" de un pedido
   ↓
3. Accede a: /cliente/pedidos/{id}
   ↓
4. Ruta: cliente.pedidos.show (GET)
   ↓
5. Controlador: ClientePedidoController@show($pedido)
    ↓
6. Validación de seguridad: if ($pedido->id_cliente !== Auth::id())
    ↓
7. Si no es propio → abort(403)
    ↓
8. Si es propio → $pedido->load(['detalles.producto', 'pago'])
    ↓
9. Vista: cliente.pedidos.show
    ↓
10. Usuario ve detalle de su pedido
```

**Validación de seguridad:**
```php
if ($pedido->id_cliente !== Auth::id()) {
    abort(403);
}
```

---

### 3.4 FLUJO DELETE (Cancelar Mi Pedido)

```
1. Usuario (Cliente)
   ↓
2. Hace clic en "Cancelar" de un pedido pendiente
   ↓
3. Envía DELETE a: /cliente/pedidos/{id}/cancelar
   ↓
4. Ruta: cliente.pedidos.cancelar (DELETE)
   ↓
5. Controlador: ClientePedidoController@cancelar($pedido)
    ↓
6. Validación de seguridad: if ($pedido->id_cliente !== Auth::id())
    ↓
7. Si no es propio → abort(403)
    ↓
8. Validación de estado (US-0008 escenario 9):
    ↓
9. if ($pedido->estado !== 'pendiente')
    ↓
10. Si no está pendiente → Error y redirección
    ↓
11. Si está pendiente → DB::transaction(function () use ($pedido)
    ↓
12. Dentro de transacción:
    ↓
13. 1. $pedido->load('detalles.producto')
    ↓
14. 2. foreach ($pedido->detalles as $detalle)
    ↓
15.    a. $detalle->producto->increment('cantidad', $detalle->cantidad) - Devolver stock (US-0005 escenario 3)
    ↓
16.    b. MovimientoInventario::create([...]) - Registrar entrada
    ↓
17. 3. $pedido->update(['estado' => 'cancelado', 'estado_entrega' => 'cancelado'])
    ↓
18. Fin de transacción
    ↓
19. Redirección: cliente.pedidos.index
    ↓
20. Vista: cliente.pedidos.index con mensaje de éxito
```

**Validación de estado (US-0008 escenario 9):**
```php
if ($pedido->estado !== 'pendiente') {
    return back()->withErrors(['error' => 'Solo puedes cancelar pedidos en estado pendiente.']);
}
```

**Operaciones en transacción:**
1. Cargar detalles con productos
2. Por cada detalle:
   - Incrementar stock del producto (US-0005 escenario 3)
   - Registrar movimiento de entrada en inventario
3. Actualizar estado del pedido a 'cancelado'
4. Actualizar estado_entrega a 'cancelado'

---

## 4. PUNTOS DE ATENCIÓN PARA MANTENIMIENTO

### 4.1 Si no se pueden ver los pedidos
**Revisar en orden:**
1. Vista en `resources/views/cliente/pedidos/index.blade.php` - Verificar iteración
2. Consulta en `ClientePedidoController@index()` - Verificar where('id_cliente', Auth::id())
3. Autenticación - Verificar que el usuario esté autenticado
4. Rol - Verificar que el usuario tenga rol 'cliente'
5. Datos de prueba - Verificar que el cliente tenga pedidos

### 4.2 Si no se puede crear el pedido
**Revisar en orden:**
1. Vista create.blade.php - Verificar campos del formulario
2. Validaciones en store() - Verificar reglas de validación
3. Estado del carrito - Verificar que esté 'activo'
4. Ítems del carrito - Verificar que no esté vacío
5. Stock de productos - Verificar verificación antes de confirmar

### 4.3 Si el stock no se descuenta
**Revisar en orden:**
1. Línea 117 en store() - `$detalle->producto->decrement('cantidad', $detalle->cantidad)`
2. Transacción DB - Verificar que se esté ejecutando dentro de transaction()
3. Campo cantidad en producto - Verificar que sea integer
4. Verificación de stock - Verificar que no bloquee incorrectamente

### 4.4 Si no se registran los movimientos
**Revisar en orden:**
1. Líneas 119-126 en store() - MovimientoInventario::create()
2. `$fillable` en MovimientoInventario - Verificar campos permitidos
3. Foreign key id_producto - Verificar relación con producto
4. Transacción DB - Verificar integridad de la transacción

### 4.5 Si no se puede cancelar el pedido
**Revisar en orden:**
1. Validación de seguridad - Verificar que el pedido sea del cliente
2. Validación de estado - Verificar que esté 'pendiente'
3. Línea 168 en cancelar() - if ($pedido->estado !== 'pendiente')
4. Ruta DELETE - Verificar que exista y apunte al método correcto

### 4.6 Si el stock no se devuelve al cancelar
**Revisar en orden:**
1. Línea 177 en cancelar() - `$detalle->producto->increment('cantidad', $detalle->cantidad)`
2. Transacción DB - Verificar que se esté ejecutando dentro de transaction()
3. Líneas 179-185 - MovimientoInventario::create() para entrada
4. Verificar que el pedido tenga detalles cargados

---

## 5. RELACIONES CON OTROS MÓDULOS

### 5.1 Relación con Módulo Carrito
- Los pedidos se generan desde el carrito
- `ClientePedidoController@store()` convierte carrito en pedido
- El carrito se marca como 'comprado' después de generar pedido
- Los detalles del carrito se convierten en detalles del pedido

### 5.2 Relación con Módulo Inventario
- Los pedidos generan movimientos automáticos de salida
- `ClientePedidoController@store()` registra salidas por venta (US-0005 escenario 2)
- Las cancelaciones generan movimientos automáticos de entrada (US-0005 escenario 3)
- El stock se actualiza automáticamente con increment/decrement

### 5.3 Relación con Módulo Productos
- Los detalles del pedido contienen productos
- `DetallePedido::belongsTo(Producto::class, 'id_producto', 'id_producto')`
- Los precios se guardan en el momento del pedido
- El stock se verifica antes de confirmar el pedido

### 5.4 Relación con Módulo Pedidos (Admin)
- Los administradores pueden ver todos los pedidos
- Los administradores pueden actualizar estado de entrega
- Los clientes solo ven sus propios pedidos
- Los clientes no pueden modificar estado de entrega

### 5.5 Relación con Módulo Catálogo
- Los productos del catálogo se agregan al carrito
- Solo productos activos pueden comprarse
- El catálogo muestra stock disponible

### 5.6 Relación con Módulo Reportes
- Los reportes incluyen información de pedidos
- Se cuentan por estado (pendiente, pagado, cancelado)
- Se calculan totales de ventas
- Se filtran por rango de fechas

---

## 6. ESTADO DE IMPLEMENTACIÓN

| Funcionalidad | Estado | Observaciones |
|--------------|--------|---------------|
| Ver mis pedidos | ✅ IMPLEMENTADO | Con resumen de totales (US-0008 escenario 10) |
| Crear pedido desde carrito | ✅ IMPLEMENTADO | US-0008 escenario 1 |
| Ver detalle de mi pedido | ✅ IMPLEMENTADO | Con validación de propiedad |
| Cancelar mi pedido | ✅ IMPLEMENTADO | Solo si está pendiente (US-0008 escenario 3, 9) |
| Actualizar pedido | ❌ NO IMPLEMENTADO | No se permite modificar pedidos |
| Eliminar pedido | ❌ NO IMPLEMENTADO | Solo cancelación lógica |
| Modificar estado de entrega | ❌ NO IMPLEMENTADO | Solo administradores |
| Modificar estado de pago | ❌ NO IMPLEMENTADO | No implementado |
| Reordenar pedido | ❌ NO IMPLEMENTADO | No hay función de reordenar |
| Buscar pedidos | ❌ NO IMPLEMENTADO | Sin filtros de búsqueda |

---

## 7. ARCHIVOS RELACIONADOS

### Controladores
- `app/Http/Controllers/ClientePedidoController.php` - Controlador principal (cliente)
- `app/Http/Controllers/AdminPedidoController.php` - Controlador de pedidos (admin)
- `app/Http/Controllers/CarritoController.php` - Controlador de carrito

### Modelos
- `app/Models/Pedido.php` - Modelo principal
- `app/Models/DetallePedido.php` - Modelo de detalles
- `app/Models/Pago.php` - Modelo de pagos
- `app/Models/Carrito.php` - Modelo de carrito
- `app/Models/DetalleCarrito.php` - Modelo de detalles de carrito
- `app/Models/MovimientoInventario.php` - Modelo de movimientos

### Vistas
- `resources/views/cliente/pedidos/index.blade.php` - Mis pedidos
- `resources/views/cliente/pedidos/create.blade.php` - Confirmar pedido
- `resources/views/cliente/pedidos/show.blade.php` - Detalle de pedido
- `resources/views/admin/pedidos/index.blade.php` - Lista admin
- `resources/views/admin/pedidos/show.blade.php` - Detalle admin

### Migraciones
- `database/migrations/2026_08_13_160957_create_pedido_table.php` - Tabla pedido
- `database/migrations/2026_08_13_161009_create_detalle_pedido_table.php` - Tabla detalle_pedido
- `database/migrations/2026_08_13_161019_create_pago_table.php` - Tabla pago
- `database/migrations/2026_08_13_160935_create_carrito_table.php` - Tabla carrito
- `database/migrations/2026_08_13_160946_create_detalle_carrito_table.php` - Tabla detalle_carrito

### Rutas
- `routes/web.php` - Rutas del módulo (líneas 89-94)

---

## 8. CARACTERÍSTICAS ESPECIALES

### 8.1 Transacciones de Base de Datos
Las operaciones críticas usan transacciones:
- `store()` - Creación de pedido con descuento de stock
- `cancelar()` - Cancelación con reintegro de stock
- Esto asegura integridad: o todo se ejecuta o nada se ejecuta

### 8.2 Actualización Automática de Stock
- Al crear pedido: decrement automático de stock (US-0005 escenario 2)
- Al cancelar pedido: increment automático de stock (US-0005 escenario 3)
- Movimientos de inventario se registran automáticamente
- Esto evita inconsistencias entre stock y pedidos

### 8.3 Validación de Stock Antes de Confirmar
- Antes de crear pedido, se verifica stock de todos los productos
- Si algún producto tiene stock insuficiente, se rechaza el pedido
- Mensaje específico indica qué producto tiene problema
- Esto evita pedidos que no se pueden cumplir

### 8.4 Seguridad por Propiedad
- Los clientes solo pueden ver sus propios pedidos
- Validación: `if ($pedido->id_cliente !== Auth::id()) abort(403)`
- Esto evita que un cliente vea pedidos de otros clientes

### 8.5 Cancelación Restringida
- Solo se pueden cancelar pedidos en estado 'pendiente' (US-0008 escenario 9)
- Pedidos pagados no se pueden cancelar
- Pedidos entregados no se pueden cancelar
- Esto protege la integridad de las ventas

### 8.6 Conversión de Carrito a Pedido
- El carrito se marca como 'comprado' después de generar pedido
- Los detalles del carrito se convierten en detalles del pedido
- Se crea un nuevo carrito 'activo' para futuras compras
- Esto mantiene trazabilidad completa

### 8.7 Registro Automático de Pagos
- Al crear pedido, se crea automáticamente un registro de pago
- El estado inicial del pago es 'pendiente'
- El método de pago se selecciona al confirmar el pedido
- La referencia de pago es opcional

---

## 9. ESCENARIOS DE USO

### 9.1 Escenario 1: Consultar Mis Pedidos
```
1. Cliente accede a /cliente/pedidos
2. Sistema muestra sus pedidos ordenados por fecha
3. Cliente ve resumen: total, pendientes, pagados
4. Cliente puede hacer clic en "Ver detalle"
5. Cliente puede cancelar pedidos pendientes
```

### 9.2 Escenario 2: Crear Pedido desde Carrito
```
1. Cliente tiene carrito con productos
2. Cliente accede a /cliente/pedidos/crear
3. Sistema muestra resumen del carrito
4. Cliente ingresa datos de entrega
5. Cliente selecciona método de pago
6. Cliente confirma pedido
7. Sistema verifica stock de todos los productos
8. Sistema crea pedido, detalles, pago
9. Sistema descuenta stock de productos
10. Sistema registra movimientos de salida
11. Sistema marca carrito como comprado
12. Cliente ve mensaje de éxito
```

### 9.3 Escenario 3: Ver Detalle de Mi Pedido
```
1. Cliente hace clic en "Ver detalle" de un pedido
2. Sistema verifica que el pedido sea del cliente
3. Sistema carga información completa del pedido
4. Cliente ve datos de entrega
5. Cliente ve detalles de productos
6. Cliente ve información de pago
7. Cliente puede cancelar si está pendiente
```

### 9.4 Escenario 4: Cancelar Mi Pedido
```
1. Cliente hace clic en "Cancelar" de un pedido pendiente
2. Sistema verifica que el pedido sea del cliente
3. Sistema verifica que esté en estado pendiente
4. Sistema carga detalles del pedido
5. Sistema devuelve stock de cada producto
6. Sistema registra movimientos de entrada
7. Sistema actualiza estado a cancelado
8. Cliente ve mensaje de éxito
```

### 9.5 Escenario 5: Stock Insuficiente
```
1. Cliente intenta crear pedido
2. Sistema verifica stock de todos los productos
3. Sistema detecta que un producto tiene stock insuficiente
4. Sistema rechaza el pedido
5. Sistema muestra error específico
6. Cliente debe ajustar cantidades en carrito
```

---

## 10. REGLAS DE NEGOCIO

### 10.1 Reglas de Acceso
- Los clientes solo pueden ver sus propios pedidos
- Los clientes no pueden ver pedidos de otros clientes
- Los clientes no pueden modificar pedidos de otros clientes
- Los administradores pueden ver todos los pedidos

### 10.2 Reglas de Creación
- Los pedidos se crean desde el carrito
- El carrito debe tener ítems para crear pedido
- Se debe verificar stock antes de confirmar
- Los datos de entrega son obligatorios

### 10.3 Reglas de Cancelación
- Solo se pueden cancelar pedidos en estado pendiente
- Los pedidos pagados no se pueden cancelar
- Al cancelar, el stock se devuelve automáticamente
- Se registran movimientos de entrada por cancelación

### 10.4 Reglas de Stock
- El stock se descuenta automáticamente al crear pedido
- El stock se devuelve automáticamente al cancelar
- Los movimientos de inventario se registran automáticamente
- No se permiten pedidos con stock insuficiente

### 10.5 Reglas de Integridad
- Las operaciones críticas usan transacciones
- Los precios se guardan al momento del pedido
- Los detalles del pedido son inmutables
- Los pedidos no se eliminan físicamente

---

**Fin de documentación del módulo Pedidos (Cliente)**
