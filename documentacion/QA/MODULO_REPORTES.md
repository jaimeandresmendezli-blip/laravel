# DOCUMENTACIÓN MÓDULO: REPORTES (US-0009)

**Sistema EGG EXPRESS - Reportes del Sistema**

---

## 1. INFORMACIÓN GENERAL

- **Código de Historia de Usuario**: US-0009
- **Módulo**: Reportes del Sistema
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
        // US-0009 — Reportes
        Route::get('reportes', [ReporteController::class, 'index'])->name('reportes.index');
        Route::get('reportes/pdf', [ReporteController::class, 'exportarPdf'])->name('reportes.pdf');
    });
```

**Rutas del módulo:**

| Método HTTP | Ruta | Nombre de Ruta | Controlador | Método | Función |
|-------------|------|----------------|-------------|--------|---------|
| GET | `/admin/reportes` | admin.reportes.index | ReporteController | index() | Ver reporte con filtros |
| GET | `/admin/reportes/pdf` | admin.reportes.pdf | ReporteController | exportarPdf() | Exportar reporte PDF |

**NOTA**: No usa Route::resource, usa rutas individuales. Solo administradores pueden acceder a los reportes.

---

### 2.2 Controlador (app/Http/Controllers/ReporteController.php)

**Archivo**: `app/Http/Controllers/ReporteController.php`

**Modelos utilizados:**
- `App\Models\Pedido`
- `App\Models\MovimientoInventario`
- `App\Models\Pago`

**Dependencias adicionales:**
- `Illuminate\Support\Facades\DB`

**Métodos del controlador:**

#### index()
```php
public function index(Request $request)
{
    $fechaDesde = $request->get('fecha_desde', now()->startOfMonth()->toDateString());
    $fechaHasta = $request->get('fecha_hasta', now()->endOfDay()->toDateString());

    // US-0009 escenario 1: total de ventas en el período
    $totalVentas = Pedido::whereBetween('fecha', [$fechaDesde, $fechaHasta])
        ->where('estado', '!=', 'cancelado')
        ->sum('total');

    // US-0009 escenario 2: cantidad de pedidos por estado
    $pedidosPorEstado = Pedido::select('estado', DB::raw('COUNT(*) as total'))
        ->whereBetween('fecha', [$fechaDesde, $fechaHasta])
        ->groupBy('estado')
        ->pluck('total', 'estado');

    // US-0009 escenario 3: lista de pedidos del período
    $pedidos = Pedido::with(['cliente', 'pago'])
        ->whereBetween('fecha', [$fechaDesde, $fechaHasta])
        ->orderBy('fecha', 'desc')
        ->get();

    // US-0009 escenario 4: movimientos de inventario del período
    $movimientos = MovimientoInventario::with('producto')
        ->whereBetween('fecha', [$fechaDesde, $fechaHasta])
        ->orderBy('fecha', 'desc')
        ->get();

    return view('admin.reportes.index', compact(
        'fechaDesde',
        'fechaHasta',
        'totalVentas',
        'pedidosPorEstado',
        'pedidos',
        'movimientos'
    ));
}
```
- **Función**: Muestra reporte con filtros de fechas y métricas
- **Parámetros por defecto**:
  - `fecha_desde`: Primer día del mes actual
  - `fecha_hasta`: Fin del día actual
- **Métricas calculadas**:
  - `totalVentas`: Suma de totales de pedidos no cancelados en el período
  - `pedidosPorEstado`: Conteo de pedidos agrupados por estado
- **Datos consultados**:
  - `pedidos`: Lista de pedidos del período con cliente y pago
  - `movimientos`: Lista de movimientos de inventario del período
- **Vista**: `admin.reportes.index`
- **Datos enviados**: `$fechaDesde`, `$fechaHasta`, `$totalVentas`, `$pedidosPorEstado`, `$pedidos`, `$movimientos`

#### exportarPdf()
```php
public function exportarPdf(Request $request)
{
    $fechaDesde = $request->get('fecha_desde', now()->startOfMonth()->toDateString());
    $fechaHasta = $request->get('fecha_hasta', now()->endOfDay()->toDateString());

    // Mismas consultas que index()
    $totalVentas = Pedido::whereBetween('fecha', [$fechaDesde, $fechaHasta])
        ->where('estado', '!=', 'cancelado')
        ->sum('total');

    $pedidosPorEstado = Pedido::select('estado', DB::raw('COUNT(*) as total'))
        ->whereBetween('fecha', [$fechaDesde, $fechaHasta])
        ->groupBy('estado')
        ->pluck('total', 'estado');

    $pedidos = Pedido::with(['cliente', 'pago'])
        ->whereBetween('fecha', [$fechaDesde, $fechaHasta])
        ->orderBy('fecha', 'desc')
        ->get();

    $movimientos = MovimientoInventario::with('producto')
        ->whereBetween('fecha', [$fechaDesde, $fechaHasta])
        ->orderBy('fecha', 'desc')
        ->get();

    // Generar PDF usando DomPDF
    $pdf = app('dompdf')->loadView('admin.reportes.pdf', compact(
        'fechaDesde',
        'fechaHasta',
        'totalVentas',
        'pedidosPorEstado',
        'pedidos',
        'movimientos'
    ));

    return $pdf->download('reporte_' . now()->format('Y-m-d_His') . '.pdf');
}
```
- **Función**: Genera y descarga reporte en PDF
- **Parámetros**: Mismos que index() (fecha_desde, fecha_hasta)
- **Consultas**: Mismas que index() para consistencia
- **Generación PDF**: Usa `app('dompdf')->loadView()`
- **Vista PDF**: `admin.reportes.pdf` (vista específica para PDF)
- **Descarga**: `$pdf->download()` con nombre de archivo con timestamp
- **NOTA**: Usa `app('dompdf')` en lugar del facade para evitar errores en Laravel 11

---

### 2.3 Modelos Utilizados

#### Pedido (app/Models/Pedido.php)
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

#### MovimientoInventario (app/Models/MovimientoInventario.php)
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

### 2.4 Base de Datos

**Tablas utilizadas:**
- `pedido` - Pedidos del sistema
- `movimiento_inventario` - Movimientos de stock
- `pago` - Información de pagos
- `usuario` - Clientes (relacionado con pedidos)
- `producto` - Productos (relacionado con movimientos)

**Consultas principales:**

**Total de ventas:**
```sql
SELECT SUM(total) 
FROM pedido 
WHERE fecha BETWEEN ? AND ? 
AND estado != 'cancelado'
```

**Pedidos por estado:**
```sql
SELECT estado, COUNT(*) as total 
FROM pedido 
WHERE fecha BETWEEN ? AND ? 
GROUP BY estado
```

**Lista de pedidos:**
```sql
SELECT p.*, u.nombre as cliente_nombre, pg.metodo_pago, pg.monto 
FROM pedido p
LEFT JOIN usuario u ON p.id_cliente = u.id_usuario
LEFT JOIN pago pg ON p.id_pedido = pg.id_pedido
WHERE p.fecha BETWEEN ? AND ?
ORDER BY p.fecha DESC
```

**Movimientos de inventario:**
```sql
SELECT m.*, pr.nombre as producto_nombre 
FROM movimiento_inventario m
LEFT JOIN producto pr ON m.id_producto = pr.id_producto
WHERE m.fecha BETWEEN ? AND ?
ORDER BY m.fecha DESC
```

---

### 2.5 Vistas (resources/views/admin/reportes/)

#### index.blade.php
- **Ubicación**: `resources/views/admin/reportes/index.blade.php`
- **Función**: Muestra reporte interactivo con filtros
- **Datos recibidos**: `$fechaDesde`, `$fechaHasta`, `$totalVentas`, `$pedidosPorEstado`, `$pedidos`, `$movimientos`
- **Elementos**:
  - Formulario de filtros:
    - Fecha desde (date, default: inicio del mes)
    - Fecha hasta (date, default: hoy)
    - Botón: Filtrar
    - Botón: Exportar PDF redirige a /admin/reportes/pdf
  - Resumen de métricas:
    - Total de ventas en el período
    - Cantidad de pedidos por estado (pendiente, pagado, cancelado)
  - Tabla de pedidos:
    - ID, Cliente, Fecha, Total, Estado, Estado Entrega, Método de Pago
    - Colores diferenciados por estado
  - Tabla de movimientos de inventario:
    - ID, Producto, Tipo, Cantidad, Motivo, Fecha
    - Colores diferenciados (verde para entrada, rojo para salida)

#### pdf.blade.php
- **Ubicación**: `resources/views/admin/reportes/pdf.blade.php`
- **Función**: Vista específica para generación de PDF
- **Datos recibidos**: Mismos que index()
- **Elementos**:
  - Encabezado del reporte:
    - Título: "Reporte de Ventas e Inventario"
    - Período de fechas
    - Fecha de generación
  - Resumen de métricas:
    - Total de ventas
    - Pedidos por estado
  - Tabla de pedidos:
    - Formato optimizado para PDF
    - Sin botones ni acciones
  - Tabla de movimientos:
    - Formato optimizado para PDF
    - Sin botones ni acciones
  - Estilos CSS específicos para impresión

---

## 3. FLUJO COMPLETO DE OPERACIONES

### 3.1 FLUJO READ (Ver Reporte)

```
1. Usuario (Admin)
   ↓
2. Accede a: /admin/reportes
   ↓
3. Ruta: admin.reportes.index (GET)
   ↓
4. Controlador: ReporteController@index($request)
    ↓
5. Obtener parámetros de fecha:
    ↓
6. - fecha_desde: default = inicio del mes actual
    ↓
7. - fecha_hasta: default = fin del día actual
    ↓
8. Consulta 1: Total de ventas
    ↓
9. Pedido::whereBetween('fecha', [$fechaDesde, $fechaHasta])->where('estado', '!=', 'cancelado')->sum('total')
    ↓
10. Consulta 2: Pedidos por estado
    ↓
11. Pedido::select('estado', DB::raw('COUNT(*) as total'))->whereBetween(...)->groupBy('estado')->pluck('total', 'estado')
    ↓
12. Consulta 3: Lista de pedidos
    ↓
13. Pedido::with(['cliente', 'pago'])->whereBetween(...)->orderBy('fecha', 'desc')->get()
    ↓
14. Consulta 4: Movimientos de inventario
    ↓
15. MovimientoInventario::with('producto')->whereBetween(...)->orderBy('fecha', 'desc')->get()
    ↓
16. Vista: admin.reportes.index
    ↓
17. Usuario ve reporte con métricas y tablas
```

**Consultas SQL generadas:**

**Total de ventas:**
```sql
SELECT SUM(total) FROM pedido 
WHERE fecha BETWEEN '2026-08-01' AND '2026-08-20 23:59:59' 
AND estado != 'cancelado'
```

**Pedidos por estado:**
```sql
SELECT estado, COUNT(*) as total 
FROM pedido 
WHERE fecha BETWEEN '2026-08-01' AND '2026-08-20 23:59:59' 
GROUP BY estado
```

**Lista de pedidos:**
```sql
SELECT p.*, u.nombre as cliente_nombre, pg.metodo_pago, pg.monto 
FROM pedido p
LEFT JOIN usuario u ON p.id_cliente = u.id_usuario
LEFT JOIN pago pg ON p.id_pedido = pg.id_pedido
WHERE p.fecha BETWEEN '2026-08-01' AND '2026-08-20 23:59:59'
ORDER BY p.fecha DESC
```

**Movimientos de inventario:**
```sql
SELECT m.*, pr.nombre as producto_nombre 
FROM movimiento_inventario m
LEFT JOIN producto pr ON m.id_producto = pr.id_producto
WHERE m.fecha BETWEEN '2026-08-01' AND '2026-08-20 23:59:59'
ORDER BY m.fecha DESC
```

---

### 3.2 FLUJO READ (Exportar PDF)

```
1. Usuario (Admin)
   ↓
2. En vista de reportes, hace clic en "Exportar PDF"
   ↓
3. Accede a: /admin/reportes/pdf?fecha_desde=...&fecha_hasta=...
   ↓
4. Ruta: admin.reportes.pdf (GET)
   ↓
5. Controlador: ReporteController@exportarPdf($request)
    ↓
6. Obtener parámetros de fecha (mismos que index)
    ↓
7. Consultas 1-4: Mismas que index() para consistencia
    ↓
8. Generación PDF: app('dompdf')->loadView('admin.reportes.pdf', compact(...))
    ↓
9. DomPDF renderiza la vista pdf.blade.php
    ↓
10. Descarga: $pdf->download('reporte_2026-08-20_143022.pdf')
    ↓
11. Usuario recibe archivo PDF
```

**NOTA**: Usa `app('dompdf')` en lugar del facade para evitar errores de clase no encontrada en Laravel 11.

---

## 4. PUNTOS DE ATENCIÓN PARA MANTENIMIENTO

### 4.1 Si no se muestra el reporte
**Revisar en orden:**
1. Vista en `resources/views/admin/reportes/index.blade.php` - Verificar iteración
2. Consultas en `ReporteController@index()` - Verificar whereBetween
3. Parámetros de fecha - Verificar que se estén pasando correctamente
4. Autenticación - Verificar que el usuario esté autenticado
5. Rol - Verificar que el usuario tenga rol 'admin'

### 4.2 Si las fechas no funcionan
**Revisar en orden:**
1. Formulario de filtros en index.blade.php - Verificar nombres de campos
2. Método `get()` en controlador - Verificar que capture parámetros
3. Valores por defecto - Verificar now()->startOfMonth() y now()->endOfDay()
4. Formato de fecha - Verificar que sea compatible con base de datos
5. whereBetween - Verificar que use el formato correcto

### 4.3 Si el total de ventas es incorrecto
**Revisar en orden:**
1. Consulta de totalVentas - Línea 21 en index()
2. Condición `where('estado', '!=', 'cancelado')` - Verificar que excluya cancelados
3. Función sum() - Verificar que sume correctamente
4. Campo total en pedido - Verificar que sea decimal
5. Datos de prueba - Verificar valores en base de datos

### 4.4 Si no se genera el PDF
**Revisar en orden:**
1. Servicio DomPDF - Verificar que esté registrado en bootstrap/providers.php
2. Método `app('dompdf')` - Verificar que resuelva el servicio
3. Vista pdf.blade.php - Verificar que exista y sea válida
4. Estilos CSS - Verificar que sean compatibles con DomPDF
5. Permisos de escritura - Verificar que se pueda descargar el archivo

### 4.5 Si el PDF está vacío
**Revisar en orden:**
1. Consultas en exportarPdf() - Verificar que sean las mismas que index()
2. Parámetros de fecha - Verificar que se pasen en la URL
3. Vista pdf.blade.php - Verificar que itere sobre los datos
4. Datos en el período - Verificar que existan datos en el rango de fechas

### 4.6 Si los movimientos no aparecen
**Revisar en orden:**
1. Consulta de movimientos - Línea 35 en index()
2. Relación with('producto') - Verificar que cargue el producto
3. Tabla movimiento_inventario - Verificar que tenga datos
4. Rango de fechas - Verificar que coincida con movimientos
5. Vista - Verificar que itere sobre $movimientos

---

## 5. RELACIONES CON OTROS MÓDULOS

### 5.1 Relación con Módulo Pedidos
- Los reportes incluyen información de pedidos
- Se calcula total de ventas desde pedidos
- Se cuentan pedidos por estado
- Se muestran detalles de pedidos con cliente y pago

### 5.2 Relación con Módulo Inventario
- Los reportes incluyen movimientos de inventario
- Se muestran entradas y salidas del período
- Se relacionan con productos
- Esto permite análisis de stock

### 5.3 Relación con Módulo Productos
- Los movimientos de inventario muestran productos
- Se muestra nombre del producto en reporte
- Esto permite identificar qué productos tuvieron movimiento

### 5.4 Relación con Módulo Usuarios
- Los pedidos muestran información del cliente
- Se muestra nombre del cliente en reporte
- Esto permite análisis por cliente

### 5.5 Relación con Módulo Pagos
- Los pedidos incluyen información de pago
- Se muestra método de pago y monto
- Esto permite análisis de métodos de pago

---

## 6. ESTADO DE IMPLEMENTACIÓN

| Funcionalidad | Estado | Observaciones |
|--------------|--------|---------------|
| Ver reporte en pantalla | ✅ IMPLEMENTADO | Con métricas y tablas |
| Filtrar por rango de fechas | ✅ IMPLEMENTADO | Fecha desde/hasta |
| Total de ventas | ✅ IMPLEMENTADO | US-0009 escenario 1 |
| Pedidos por estado | ✅ IMPLEMENTADO | US-0009 escenario 2 |
| Lista de pedidos | ✅ IMPLEMENTADO | US-0009 escenario 3 |
| Movimientos de inventario | ✅ IMPLEMENTADO | US-0009 escenario 4 |
| Exportar a PDF | ✅ IMPLEMENTADO | Con DomPDF |
| Valores por defecto de fechas | ✅ IMPLEMENTADO | Inicio mes / fin día actual |
| Reporte por cliente | ❌ NO IMPLEMENTADO | Sin filtro por cliente |
| Reporte por producto | ❌ NO IMPLEMENTADO | Sin filtro por producto |
| Gráficos | ❌ NO IMPLEMENTADO | Solo tablas |
| Comparación de períodos | ❌ NO IMPLEMENTADO | Solo un período a la vez |
| Exportar a Excel | ❌ NO IMPLEMENTADO | Solo PDF |

---

## 7. ARCHIVOS RELACIONADOS

### Controladores
- `app/Http/Controllers/ReporteController.php` - Controlador principal

### Modelos
- `app/Models/Pedido.php` - Modelo de pedidos
- `app/Models/MovimientoInventario.php` - Modelo de movimientos
- `app/Models/Pago.php` - Modelo de pagos
- `app/Models/Usuario.php` - Modelo de clientes
- `app/Models/Producto.php` - Modelo de productos

### Vistas
- `resources/views/admin/reportes/index.blade.php` - Reporte en pantalla
- `resources/views/admin/reportes/pdf.blade.php` - Vista para PDF

### Migraciones
- `database/migrations/2026_08_13_160957_create_pedido_table.php` - Tabla pedido
- `database/migrations/2026_08_13_160924_create_movimiento_inventario_table.php` - Tabla movimiento_inventario
- `database/migrations/2026_08_13_161019_create_pago_table.php` - Tabla pago

### Rutas
- `routes/web.php` - Rutas del módulo (líneas 58-60)

### Configuración
- `bootstrap/providers.php` - Registro de DomPDF ServiceProvider

---

## 8. CARACTERÍSTICAS ESPECIALES

### 8.1 Filtros de Fecha por Defecto
- `fecha_desde`: Primer día del mes actual (`now()->startOfMonth()`)
- `fecha_hasta`: Fin del día actual (`now()->endOfDay()`)
- Esto permite ver reporte del mes actual sin configuración
- El usuario puede ajustar las fechas según necesite

### 8.2 Exclusión de Pedidos Cancelados
- El total de ventas excluye pedidos cancelados
- `where('estado', '!=', 'cancelado')`
- Esto refleja solo ventas efectivas
- Los pedidos cancelados se cuentan en pedidosPorEstado

### 8.3 Agrupación por Estado
- Los pedidos se agrupan por estado para análisis
- `groupBy('estado')` con `COUNT(*)`
- Estados: pendiente, pagado, cancelado
- Esto permite ver distribución de pedidos

### 8.4 Carga Eager de Relaciones
- Pedidos cargan cliente y pago: `with(['cliente', 'pago'])`
- Movimientos cargan producto: `with('producto')`
- Esto evita problema N+1 de consultas
- Optimiza rendimiento del reporte

### 8.5 Consistencia entre HTML y PDF
- Las consultas son idénticas en index() y exportarPdf()
- Esto garantiza que el PDF muestre los mismos datos
- Los mismos parámetros de fecha se aplican
- La vista PDF usa los mismos datos

### 8.6 Uso de app('dompdf')
- En lugar del facade `Pdf::loadView()`
- Usa `app('dompdf')->loadView()`
- Esto evita errores de clase no encontrada en Laravel 11
- El servicio se resuelve directamente del contenedor

### 8.7 Nombre de Archivo con Timestamp
- El archivo PDF se nombra con timestamp
- `reporte_2026-08-20_143022.pdf`
- Esto evita sobrescribir archivos anteriores
- Permite mantener historial de reportes

---

## 9. ESCENARIOS DE USO

### 9.1 Escenario 1: Ver Reporte del Mes Actual
```
1. Admin accede a /admin/reportes
2. Sistema muestra reporte del mes actual por defecto
3. Admin ve total de ventas del mes
4. Admin ve distribución de pedidos por estado
5. Admin ve lista de pedidos del mes
6. Admin ve movimientos de inventario del mes
```

### 9.2 Escenario 2: Filtrar por Rango de Fechas
```
1. Admin en reportes selecciona fechas específicas
2. Admin selecciona fecha desde: 2026-08-01
3. Admin selecciona fecha hasta: 2026-08-15
4. Admin hace clic en "Filtrar"
5. Sistema recalcula métricas para el nuevo rango
6. Admin ve reporte actualizado
```

### 9.3 Escenario 3: Exportar a PDF
```
1. Admin en reportes hace clic en "Exportar PDF"
2. Sistema genera PDF con los datos actuales
3. Sistema descarga archivo PDF
4. Admin puede guardar o imprimir el PDF
5. El PDF tiene el mismo formato que la vista HTML
```

### 9.4 Escenario 4: Análisis de Ventas
```
1. Admin ve total de ventas: $1,500,000
2. Admin ve pedidos por estado: 10 pendientes, 25 pagados, 2 cancelados
3. Admin identifica que hay muchos pedidos pendientes
4. Admin puede tomar acciones para gestionar pedidos pendientes
```

### 9.5 Escenario 5: Análisis de Inventario
```
1. Admin ve movimientos de inventario
2. Admin identifica muchas salidas recientes
3. Admin verifica stock de productos
4. Admin puede registrar entradas para reponer stock
```

---

## 10. REGLAS DE NEGOCIO

### 10.1 Reglas de Acceso
- Solo administradores pueden ver reportes
- Los clientes no tienen acceso a reportes
- El middleware role:admin restringe acceso
- Los usuarios no autenticados no pueden acceder

### 10.2 Reglas de Fechas
- El rango de fechas es inclusivo
- La fecha desde puede ser cualquier fecha pasada
- La fecha hasta puede ser cualquier fecha presente o futura
- Los valores por defecto facilitan uso común

### 10.3 Reglas de Cálculo
- El total de ventas excluye pedidos cancelados
- Los pedidos por estado incluyen todos los estados
- Los movimientos incluyen entradas y salidas
- No se aplican descuentos ni ajustes

### 10.4 Reglas de Exportación
- El PDF contiene exactamente los mismos datos que la vista
- El PDF no incluye elementos interactivos
- El PDF se genera on-demand
- No se almacenan copias de los reportes

### 10.5 Reglas de Integridad
- Los reportes reflejan el estado actual de la base de datos
- Los datos no se cachean
- Los reportes son consistentes en tiempo real
- No hay manipulación de datos en reportes

---

## 11. CONFIGURACIÓN DE DOMPDF

### 11.1 Registro del Service Provider

**Archivo**: `bootstrap/providers.php`

```php
use App\Providers\AppServiceProvider;
use Barryvdh\DomPDF\ServiceProvider;

return [
    AppServiceProvider::class,
    ServiceProvider::class,
];
```

**NOTA**: Se registra `Barryvdh\DomPDF\ServiceProvider` para que DomPDF esté disponible en Laravel 11.

### 11.2 Uso en el Controlador

**Archivo**: `app/Http/Controllers/ReporteController.php`

```php
// Línea 75
$pdf = app('dompdf')->loadView('admin.reportes.pdf', compact(...));
```

**NOTA**: Se usa `app('dompdf')` en lugar del facade para evitar errores de clase no encontrada.

---

**Fin de documentación del módulo Reportes**
