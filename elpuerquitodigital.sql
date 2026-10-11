CREATE DATABASE IF NOT EXISTS elpuerquitodigital
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE elpuerquitodigital;

DROP TABLE IF EXISTS distribution;
DROP TABLE IF EXISTS pedido;
DROP TABLE IF EXISTS ticket;
DROP TABLE IF EXISTS venta;
DROP TABLE IF EXISTS inventario;
DROP TABLE IF EXISTS producto;
DROP TABLE IF EXISTS cliente;
DROP TABLE IF EXISTS empleado;

CREATE TABLE empleado (
    id_empleado INT AUTO_INCREMENT PRIMARY KEY,
    curp VARCHAR(18) UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    seg_nombre VARCHAR(100),
    apellido_p VARCHAR(100) NOT NULL,
    apellido_m VARCHAR(100),
    genero VARCHAR(20),
    telefono VARCHAR(15),
    usuario VARCHAR(50) NOT NULL UNIQUE,
    contrasenia VARCHAR(255) NOT NULL, 
    rol ENUM('ADMINISTRADOR', 'VENTAS', 'ALMACEN', 'DISTRIBUCION', 'REPARTIDOR') NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE cliente (
    id_cliente INT AUTO_INCREMENT PRIMARY KEY,
    nombre_razonsocial VARCHAR(100) NOT NULL,
    telefono VARCHAR(15),
    calle VARCHAR(100),
    colonia VARCHAR(100),
    codigo_postal VARCHAR(10),
    ciudad VARCHAR(50),
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE producto (
    id_producto INT AUTO_INCREMENT PRIMARY KEY,
    nombre_producto VARCHAR(100) NOT NULL,
    tipo_corte VARCHAR(50),
    precio_kg DECIMAL(10, 2) NOT NULL,
    estado BOOLEAN DEFAULT TRUE
) ENGINE=InnoDB;

CREATE TABLE inventario (
    id_inventario INT AUTO_INCREMENT PRIMARY KEY,
    id_producto INT NOT NULL UNIQUE,
    cantidad_kg DECIMAL(10, 3) NOT NULL DEFAULT 0.000,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_producto) REFERENCES producto(id_producto) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE venta (
    id_venta INT AUTO_INCREMENT PRIMARY KEY,
    folio_nota INT UNIQUE,
    id_cliente INT NOT NULL,
    id_empleado_ven INT NOT NULL,
    fecha_venta DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    metodo_pago ENUM('EFECTIVO', 'TARJETA', 'TRANSFERENCIA') NOT NULL DEFAULT 'EFECTIVO',
    total_venta DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    estado_venta ENUM('COMPLETADA', 'CANCELADA') DEFAULT 'COMPLETADA',
    FOREIGN KEY (id_cliente) REFERENCES cliente(id_cliente) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (id_empleado_ven) REFERENCES empleado(id_empleado) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE ticket (
    id_detalle_ticket INT AUTO_INCREMENT PRIMARY KEY,
    id_venta INT NOT NULL,
    id_producto INT NOT NULL,
    cantidad_kg DECIMAL(10, 3) NOT NULL,
    precio_kg DECIMAL(10, 2) NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (id_venta) REFERENCES venta(id_venta) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_producto) REFERENCES producto(id_producto) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE pedido (
    id_pedido INT AUTO_INCREMENT PRIMARY KEY,
    id_venta INT NOT NULL UNIQUE,
    id_empleado_rep INT,
    direccion_entrega TEXT NOT NULL,
    estado_preparacion ENUM('PENDIENTE', 'EN_PREPARACION', 'PREPARADO', 'CANCELADO') DEFAULT 'PENDIENTE',
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_venta) REFERENCES venta(id_venta) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (id_empleado_rep) REFERENCES empleado(id_empleado) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE distribution (
    id_distribucion INT AUTO_INCREMENT PRIMARY KEY,
    id_pedido INT NOT NULL UNIQUE,
    transporte VARCHAR(50),
    fecha_asignacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_entrega DATETIME,
    estado_entrega ENUM('POR_ASIGNAR', 'EN_CAMINO', 'ENTREGADO', 'NO_ENTREGADO') DEFAULT 'POR_ASIGNAR',
    evidencia_entrega_url VARCHAR(255),
    FOREIGN KEY (id_pedido) REFERENCES pedido(id_pedido) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

DELIMITER //

CREATE TRIGGER trg_descontar_inventario
BEFORE INSERT ON ticket
FOR EACH ROW
BEGIN
    DECLARE stock_actual DECIMAL(10, 3);
    
    SELECT cantidad_kg INTO stock_actual 
    FROM inventario 
    WHERE id_producto = NEW.id_producto;
    
    IF stock_actual IS NULL OR stock_actual < NEW.cantidad_kg THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Error: Stock insuficiente en inventario para realizar la venta.';
    ELSE
        UPDATE inventario 
        SET cantidad_kg = cantidad_kg - NEW.cantidad_kg
        WHERE id_producto = NEW.id_producto;
    END IF;
END //

CREATE TRIGGER trg_actualizar_inventario_ticket
AFTER UPDATE ON ticket
FOR EACH ROW
BEGIN
    DECLARE diferencia DECIMAL(10, 3);
    SET diferencia = NEW.cantidad_kg - OLD.cantidad_kg;
    
    UPDATE inventario 
    SET cantidad_kg = cantidad_kg - diferencia
    WHERE id_producto = NEW.id_producto;
END //

CREATE TRIGGER trg_restaurar_inventario_eliminar_ticket
AFTER DELETE ON ticket
FOR EACH ROW
BEGIN
    UPDATE inventario 
    SET cantidad_kg = cantidad_kg + OLD.cantidad_kg
    WHERE id_producto = OLD.id_producto;
END //

CREATE TRIGGER trg_reintegrar_inventario
AFTER UPDATE ON venta
FOR EACH ROW
BEGIN
    IF NEW.estado_venta = 'CANCELADA' AND OLD.estado_venta != 'CANCELADA' THEN
        UPDATE inventario i
        INNER JOIN ticket dv ON i.id_producto = dv.id_producto
        SET i.cantidad_kg = i.cantidad_kg + dv.cantidad_kg
        WHERE dv.id_venta = NEW.id_venta;
    ELSEIF NEW.estado_venta = 'COMPLETADA' AND OLD.estado_venta = 'CANCELADA' THEN
        UPDATE inventario i
        INNER JOIN ticket dv ON i.id_producto = dv.id_producto
        SET i.cantidad_kg = i.cantidad_kg - dv.cantidad_kg
        WHERE dv.id_venta = NEW.id_venta;
    END IF;
END //

DELIMITER ;

CREATE INDEX idx_venta_folio ON venta(folio_nota);
CREATE INDEX idx_producto_corte ON producto(tipo_corte);
CREATE INDEX idx_distribucion_estado ON distribution(estado_entrega);