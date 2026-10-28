# Tareas del Sprint 2: Plan de Ejecución Técnico

## ⚠️ Reglas Estrictas de Desarrollo para el Agente
1. **Secuencialidad:** Resolver una sola subtarea a la vez en orden numérico estricto.
2. **Pruebas:** Probar la ejecución visual en el navegador interactuando con las tablas antes de avanzar.
3. **Commits:** Realizar un commit descriptivo en Git inmediatamente al completar con éxito cada subtarea.
4. **Triggers Nativos:** Recordar que la base de datos ya tiene triggers para actualizar el stock. No descuentes stock por código PHP; deja que actúe el SGBD al insertar en `ticket`.

---

## 1. Módulo de Clientes (HU6, HU7)
- [ ] 1.1 Crear el modelo `app/models/Cliente.php` e implementar los métodos parametrizados con PDO: `registrar($datos)`, `buscarPorNombreOTelefono($termino)` y `comprobarDuplicado($nombre, $telefono)`.
- [ ] 1.2 Crear el controlador `app/controllers/ClienteController.php` restringiendo el acceso exclusivamente a los roles `VENTAS` y `ADMINISTRADOR`.
- [ ] 1.3 Diseñar la vista `app/views/clientes/registro.php` utilizando Bootstrap 5.3 y la paleta cálida según Figma, marcando con asterisco `*` los campos obligatorios.
- [ ] 1.4 Implementar la lógica por POST en el controlador para capturar los datos y manejar los mensajes de error para campos faltantes o perfiles duplicados.

## 2. Consultas de Catálogo e Inventario (HU8)
- [ ] 2.1 Añadir al modelo `app/models/Producto.php` el método `obtenerCatalogoConStock()` ejecutando un `JOIN` con la tabla `inventario` para extraer la columna `cantidad_kg`.
- [ ] 2.2 Diseñar la vista del catálogo de ventas en `app/views/inventario/catalogo.php` incorporando la tabla responsiva de disponibilidad y el campo buscador en tiempo real.
- [ ] 2.3 Programar un archivo JavaScript independiente en `public/assets/js/catalogo-ventas.js` para filtrar dinámicamente las filas de los productos según la búsqueda del vendedor.

## 3. Módulo de Transacciones de Ventas (HU5)
- [ ] 3.1 Crear el modelo `app/models/Venta.php`. Diseñar el método transaccional centralizado `procesarVenta($datosVenta, $productosDetalle)`. Debe iniciar `beginTransaction()`, insertar en `venta`, recuperar el ID mediante `lastInsertId()`, e iterar el arreglo para insertar cada artículo en la tabla `ticket`.
- [ ] 3.2 Implementar un bloque `catch(PDOException $e)` dentro del modelo: si el trigger de la base de datos lanza el error de stock insuficiente (SQLSTATE 45000), capturar el mensaje de excepción, ejecutar `rollBack()` y enviarlo limpiamente al controlador.
- [ ] 3.3 Crear `app/controllers/VentaController.php` coordinando la recepción del carrito de compras enviado desde el frontend.
- [ ] 3.4 Diseñar la interfaz de ventas en `app/views/ventas/nueva.php` basándose en el wireframe de Figma, mostrando la tabla dinámica de artículos agregados, el cálculo automatizado de subtotales/totales y el botón "Confirmar venta".

## 4. Consulta de Pedidos en Almacén (HU11)
- [ ] 4.1 Crear el modelo `app/models/Pedido.php` e implementar el método `listarPedidosPendientes()` utilizando un `JOIN` con `venta` y `ticket` para agrupar los kilogramos solicitados por fecha.
- [ ] 4.2 Configurar el controlador `app/controllers/PedidoController.php` restringiendo el acceso únicamente al rol `ALMACEN` y `ADMINISTRADOR`.
- [ ] 4.3 Diseñar la pantalla de almacén en `app/views/pedidos/pendientes.php` para renderizar las tarjetas colapsables de preparación de pedidos según el diseño de Figma.

## 5. Control y Bloqueo de Cuentas (HU19)
- [ ] 5.1 Añadir al modelo `app/models/Empleado.php` el método `actualizarEstadoCuenta($id, $estado)` para conmutar el campo booleano `estado`.
- [ ] 5.2 Modificar los métodos de gestión de usuarios en `app/controllers/EmpleadoController.php` para incorporar las acciones de inhabilitar/habilitar empleados por interfaz.
- [ ] 5.3 Actualizar la vista `app/views/empleados/lista.php` inyectando los botones dinámicos de alternancia de estado y el formulario precargado completo.
- [ ] 5.4 Asegurar en `app/controllers/AuthController.php` que si el empleado tiene las credenciales correctas pero su columna `estado` es igual a `0` (FALSE), el login sea rechazado de inmediato.
