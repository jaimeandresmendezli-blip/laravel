# `AdminPedidoController.php`

## Propósito
Gestiona los pedidos desde la perspectiva del administrador. Permite al administrador visualizar todos los pedidos del sistema, filtrar por estados y actualizar el estado de entrega.

## Métodos

### `index(Request $request)`
Lista todos los pedidos registrados en el sistema.
- Carga las relaciones `cliente` y `pago`.
- Permite aplicar filtros por `estado` y `estado_entrega`.
- Retorna la vista `admin.pedidos.index` con los resultados.

### `show(Pedido $pedido)`
Muestra los detalles completos de un pedido específico.
- Carga las relaciones: `cliente`, `detalles.producto` y `pago`.
- Retorna la vista `admin.pedidos.show`.

### `actualizarEstadoEntrega(Request $request, Pedido $pedido)`
Actualiza el estado de entrega de un pedido.
- **Validaciones**: Requiere que el estado sea uno de: `pendiente, en_camino, entregado, cancelado`.
- Actualiza el registro y redirige a la vista del detalle con un mensaje de éxito.

## Relaciones
- **Modelos usados**: `App\Models\Pedido`
- **Vistas**: `admin.pedidos.index`, `admin.pedidos.show`
