-- Este INSERT corresponde al diccionario de arquitectura, que define la columna es_temporal en empleado.
-- La tabla inspeccionada previamente en este entorno no contenía es_temporal; si sigue igual, el INSERT fallará hasta que el esquema autorizado incluya esa columna.
-- La contraseña de prueba es 12345678 y se almacena únicamente su hash BCRYPT compatible con password_verify().
INSERT INTO empleado (curp, nombre, apellido_p, telefono, usuario, contrasenia, rol, es_temporal) -- Inserta los campos requeridos y marca la contraseña como no temporal.
SELECT 'XEXX010101HNEXXXA4', 'Administrador', 'Prueba', '0000000000', 'admin_puerquito', '$2y$10$0ye2VeQZ/praMP1V.4/HAetd2zZ/X5SpOK9Rfy4sB27oK0U76FvD6', 'ADMINISTRADOR', 0 -- Incluye un hash BCRYPT y el valor entero cero para es_temporal.
WHERE NOT EXISTS (SELECT 1 FROM empleado WHERE usuario = 'admin_puerquito') -- Evita insertar otra cuenta con el mismo nombre de usuario.
  AND NOT EXISTS (SELECT 1 FROM empleado WHERE curp = 'XEXX010101HNEXXXA4'); -- Evita infringir la unicidad del CURP.
