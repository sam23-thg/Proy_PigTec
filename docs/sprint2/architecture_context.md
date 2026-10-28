# Contexto de Arquitectura y Diccionario de Datos - Sprint 2

## 1. Esquema Relacional de las Nuevas Tablas (Uso Estricto)

### Tabla: `cliente`
- `id_cliente` (INT, PK, Auto Increment)
- `nombre_razonsocial` (VARCHAR 100, Requerido)
- `telefono` (VARCHAR 15, Requerido)
- `calle`, `colonia`, `codigo_postal`, `ciudad` (VARCHAR, Opcionales)

### Tabla: `venta`
- `id_venta` (INT, PK, Auto Increment)
- `folio_nota` (INT, Único)
- `id_cliente` (INT, FK -> cliente)
- `id_empleado_ven` (INT, FK -> empleado)
- `fecha_venta` (DATETIME)
- `metodo_pago` (ENUM: 'EFECTIVO', 'TARJETA', 'TRANSFERENCIA')
- `total_venta` (DECIMAL 10,2)
- `estado_venta` (ENUM: 'COMPLETADA', 'CANCELADA')

### Tabla: `ticket`
- `id_detalle_ticket` (INT, PK, Auto Increment)
- `id_venta` (INT, FK -> venta)
- `id_producto` (INT, FK -> producto)
- `cantidad_kg` (DECIMAL 10,3) -> *Detona los triggers de stock*
- `precio_kg` (DECIMAL 10,2)
- `Total` (DECIMAL 10,2)

### Tabla: `pedido`
- `id_pedido` (INT, PK, Auto Increment)
- `id_venta` (INT, FK -> venta)
- `id_empleado_rep` (INT, FK -> empleado)
- `direccion_entrega` (TEXT)
- `estado_preparacion` (ENUM: 'PENDIENTE', 'EN_PREPARACION', 'PREPARADO', 'CANCELADO')

---

## 2. Manejo de Errores Críticos de Stock (Captura del Trigger)
Cuando se inserte una fila en `ticket`, el trigger `trg_descontar_inventario` puede arrojar una excepción si no hay carne suficiente. Copilot debe capturar el error de la siguiente manera:

```php
try {
    \$this->db->beginTransaction();
    // Inserción en venta...
    // Inserción en ticket...
    \$this->db->commit();
    return ["exito" => true];
} catch (PDOException \(e) {\)this->db->rollBack();
    // Captura el SIGNAL SQLSTATE '45000' enviado por MariaDB
    if (\(e->getCode() === '45000' \vert{}\vert{} str_contains(\)e->getMessage(), 'Stock insuficiente')) {
        return ["exito" => false, "error" => "Error: Stock insuficiente en inventario para realizar la venta."];
    }
    return ["exito" => false, "error" => "Error de registro en la base de datos."];
}
```
