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
    id_empleado INT AUTO_INCREMENT PRIMAR Y KEY,
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
    estado BOOLEAN DEFAULT TRUE,
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

-- 1. EMPLEADOS
INSERT INTO empleado (curp, nombre, seg_nombre, apellido_p, apellido_m, genero, telefono, usuario, contrasenia, rol) VALUES
('RAMA980101HDFR01', 'Alexis', 'Jair', 'Ramírez', 'González', 'Masculino', '5551234567', 'alexis_admin', '$2b$10$HASHED_PASSWORD_HERE', 'ADMINISTRADOR'),
('LOPD990202HDFR02', 'David', 'Samuel', 'López', 'Valencia', 'Masculino', '5559876543', 'david_dist', '$2b$10$HASHED_PASSWORD_HERE', 'DISTRIBUCION'),
('HERP000303MDFR03', 'Paula', NULL, 'Hernández', 'Hernández', 'Femenino', '5554567890', 'paula_almacen', '$2b$10$HASHED_PASSWORD_HERE', 'ALMACEN'),
('LOPM010404MDFR04', 'Salma', 'Michelle', 'López', 'Martínez', 'Femenino', '5553332211', 'salma_ventas', '$2b$10$HASHED_PASSWORD_HERE', 'VENTAS');

-- 2. CLIENTES
INSERT INTO cliente (nombre_razonsocial, telefono, calle, colonia, codigo_postal, ciudad) VALUES
('Carnicería El Chicharito / Juan N.', '5551112233', 'Av. Insurgentes 123', 'Centro', '06000', 'Ciudad de México'),
('Restaurante La Cerdita / Ana Ruiz', '5554445566', 'Calle Reforma 45', 'Juárez', '06600', 'Ciudad de México'),
('Público General / Venta Mostrador', '5550000000', 'N/A', 'N/A', '00000', 'N/A');

-- 3. PRODUCTOS
INSERT INTO producto (nombre_producto, tipo_corte, precio_kg, estado) VALUES
('Tocino Ahumado Rebanado', 'Tocino', 180.00, TRUE),
('Tocino Tira Especial', 'Tocino', 165.50, TRUE),
('Lomo de Cerdo Fresh', 'Lomo', 140.00, TRUE),
('Costilla para BBQ', 'Costilla', 155.00, TRUE),
('Chicharrón Prensadito', 'Chicharrón', 120.00, TRUE);

-- 4. INVENTARIO
INSERT INTO inventario (id_producto, cantidad_kg) VALUES
(1, 50.000),  
(2, 30.000),  
(3, 100.000), 
(4, 40.000),  
(5, 25.000);  

-- 5. VENTA EN MOSTRADOR
INSERT INTO venta (folio_nota, id_cliente, id_empleado_ven, metodo_pago, total_venta, estado_venta) VALUES
(1001, 3, 4, 'EFECTIVO', 605.00, 'COMPLETADA');

-- Items de la nota 
INSERT INTO ticket (id_venta, id_producto, cantidad_kg, precio_kg, subtotal) VALUES
(1, 1, 2.500, 180.00, 450.00), 
(1, 4, 1.000, 155.00, 155.00); 

-- 6. VENTA CON PEDIDO Y DISTRIBUCIÓN
INSERT INTO venta (folio_nota, id_cliente, id_empleado_ven, metodo_pago, total_venta, estado_venta) VALUES
(1002, 1, 4, 'TRANSFERENCIA', 900.00, 'COMPLETADA');

INSERT INTO ticket (id_venta, id_producto, cantidad_kg, precio_kg, subtotal) VALUES
(2, 1, 5.000, 180.00, 900.00);

-- Registra el Pedido
INSERT INTO pedido (id_venta, id_empleado_rep, direccion_entrega, estado_preparacion) VALUES
(2, 2, 'Av. Insurgentes 123, Col. Centro', 'PREPARADO');

-- Registrando la Distribución del Pedido
INSERT INTO distribution (id_pedido, transporte, estado_entrega) VALUES
(1, 'Motocicleta Izuka 150', 'EN_CAMINO');


-- CONSULTAS

SELECT id_empleado, nombre, seg_nombre, apellido_p, apellido_m, rol, usuario 
FROM empleado;

SELECT 
    v.folio_nota,
    v.fecha_venta,
    c.nombre_razonsocial AS cliente,
    CONCAT(e.nombre, ' ', e.apellido_p) AS atendio_empleado,
    v.metodo_pago,
    v.total_venta,
    v.estado_venta
FROM venta v
JOIN cliente c ON v.id_cliente = c.id_cliente
JOIN empleado e ON v.id_empleado_ven = e.id_empleado;

SELECT 
    v.folio_nota,
    p.nombre_producto,
    t.cantidad_kg,
    t.precio_kg,
    t.subtotal
FROM ticket t
JOIN venta v ON t.id_venta = v.id_venta
JOIN producto p ON t.id_producto = p.id_producto;

SELECT 
    p.id_producto,
    p.nombre_producto,
    p.tipo_corte,
    p.precio_kg,
    i.cantidad_kg AS stock_restante_kg,
    i.fecha_actualizacion
FROM inventario i
JOIN producto p ON i.id_producto = p.id_producto;

SELECT 
    p.id_pedido,
    v.folio_nota,
    CONCAT(e.nombre, ' ', e.apellido_p) AS repartidor_asignado,
    p.direccion_entrega,
    p.estado_preparacion,
    d.transporte,
    d.estado_entrega
FROM pedido p
JOIN venta v ON p.id_venta = v.id_venta
LEFT JOIN empleado e ON p.id_empleado_rep = e.id_empleado
LEFT JOIN distribution d ON p.id_pedido = d.id_pedido;