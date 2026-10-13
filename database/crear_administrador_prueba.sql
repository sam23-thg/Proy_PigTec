-- Inserta un administrador de prueba usando columnas confirmadas en la tabla empleado activa.
-- id_empleado se omite porque es autoincremental y fecha_creacion tiene un valor predeterminado en la base de datos.
-- La contraseña de prueba es 12345678 y se almacena únicamente su hash BCRYPT compatible con password_verify().
INSERT INTO empleado (curp, nombre, apellido_p, telefono, usuario, contrasenia, rol) -- Incluye solo columnas que existen en el esquema activo.
SELECT 'XEXX010101HNEXXXA4', 'Administrador', 'Prueba', '0000000000', 'admin_puerquito', '$2y$10$0ye2VeQZ/praMP1V.4/HAetd2zZ/X5SpOK9Rfy4sB27oK0U76FvD6', 'ADMINISTRADOR' -- Asigna los datos mínimos y la contraseña cifrada con BCRYPT.
WHERE NOT EXISTS (SELECT 1 FROM empleado WHERE usuario = 'admin_puerquito') -- Evita insertar otra cuenta con el mismo nombre de usuario.
  AND NOT EXISTS (SELECT 1 FROM empleado WHERE curp = 'XEXX010101HNEXXXA4'); -- Evita infringir la unicidad del CURP.
