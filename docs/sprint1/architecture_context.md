# Contexto de Arquitectura y Diccionario de Datos - El Puerquito Digital

## 1. Mapeo Relacional de la Base de Datos (Esquema Real Uno a Uno)

### Tabla: `empleado`
- `id_empleado` (INT, PK, Auto Increment)
- `curp` (VARCHAR 18, Único, Requerido)
- `nombre` (VARCHAR 100, Requerido)
- `seg_nombre` (VARCHAR 100, Opcional)
- `apellido_p` (VARCHAR 100, Requerido)
- `apellido_m` (VARCHAR 100, Opcional)
- `genero` (VARCHAR 20, Opcional)
- `telefono` (VARCHAR 15, Requerido)
- `usuario` (VARCHAR 50, Único, Requerido) -> *Usar para el Login*
- `contrasenia` (VARCHAR 255, Requerido)
- `rol` (ENUM: 'ADMINISTRADOR', 'VENTAS', 'ALMACEN', 'DISTRIBUCION', 'REPARTIDOR')
- `estado` BOOLEAN DEFAULT TRUE, -> *Para activar y/o desactivar una cuenta*
- `es_temporal` (TINYINT/BOOLEAN, Requerido) -> *Bandera de control para obligar el cambio de contraseña al iniciar sesión*

### Tabla: `producto`
- `id_producto` (INT, PK, Auto Increment)
- `nombre_producto` (VARCHAR 100, Requerido y Único)
- `tipo_corte` (VARCHAR 50, Opcional)
- `precio_kg` (DECIMAL 10,2, Requerido)
- `estado` (BOOLEAN, Por defecto TRUE)

### Tabla: `inventario` (Relación 1:1 con Producto)
- `id_inventario` (INT, PK, Auto Increment)
- `id_producto` (INT, FK referenciando a `producto(id_producto)`) -> *Unión Uno a Uno estricta*
- `cantidad_existencia` (DECIMAL/INT, Requerido) -> *Existencia inicial y acumulada*
- `fecha_actualizacion` (TIMESTAMP / DATETIME)

---

## 2. Estándar de Codificación (PHP / MVC)

### Variables de Entorno (`.env`)
```env
DB_HOST=localhost
DB_NAME=elpuerquitodigital
DB_USER=root
DB_PASS=
```

### Transacciones PDO para el Módulo de Productos (HU9 y HU10 - ¡Obligatorio!)
Al registrar o editar productos, el controlador interactúa con un único formulario visual, pero el modelo debe procesar de forma transaccional la escritura en las dos tablas (`producto` e `inventario`) para garantizar la integridad relacional de 1 a 1.

#### Ejemplo de Estructura Transaccional para la Creación (HU9):
```php
try {
    \$this->db->beginTransaction();
    
    stmtProd = this->db->prepare("INSERT INTO producto (nombre_producto, tipo_corte, precio_kg, estado) VALUES (:nombre, :corte, :precio, :estado)");
    \$stmtProd->execute([
        ':nombre' => \$datos['nombre_producto'],
        ':corte'  => \$datos['tipo_corte'],
        ':precio' => \$datos['precio_kg'],
        ':estado' => 1
    ]);
    
    idProducto = this->db->lastInsertId();
    
    stmtInv = this->db->prepare("INSERT INTO inventario (id_producto, cantidad_existencia, fecha_actualizacion) VALUES (:id_prod, :existencia, NOW())");
    \$stmtInv->execute([
        ':id_prod'    => \$idProducto,
        ':existencia' => \$datos['existencia_inicial']
    ]);
    
    \$this->db->commit();
    return true;
} catch (Exception \(e) {\)this->db->rollBack();
    return false;
}
```

#### Ejemplo de Estructura Transaccional para la Actualización (HU10):
```php
try {
    \$this->db->beginTransaction();
    
    stmtProd = this->db->prepare("UPDATE producto SET nombre_producto = :nombre, tipo_corte = :corte, precio_kg = :precio, estado = :estado WHERE id_producto = :id");
    \$stmtProd->execute([
        ':nombre' => \$datos['nombre_producto'],
        ':corte'  => \$datos['tipo_corte'],
        ':precio' => \$datos['precio_kg'],
        ':estado' => \$datos['estado'],
        ':id'     => \$id
    ]);
    
    stmtInv = this->db->prepare("UPDATE inventario SET cantidad_existencia = :existencia, fecha_actualizacion = NOW() WHERE id_producto = :id");
    \$stmtInv->execute([
        ':existencia' => \$datos['cantidad_existencia'],
        ':id'         => \$id
    ]);
    
    \$this->db->commit();
    return true;
} catch (Exception \(e) {\)this->db->rollBack();
    return false;
}
```
