# Requisitos del Sprint 2: Ventas, Clientes y Administración de Usuarios

## Contexto del Sistema
Este sprint automatiza el flujo comercial de la tocinería "El Puerquito Digital", permitiendo registrar ventas, dar de alta clientes, consultar la disponibilidad de stock, listar pedidos en almacén y dar superpoderes al administrador para habilitar/inhabilitar cuentas de personal.

## Roles del Sistema e Intervención
- **ADMINISTRADOR:** Acceso total, control y modificación de usuarios e inhabilitación de cuentas.
- **VENTAS / VENDEDOR:** Registro de ventas, alta y consulta de clientes, y visualización del catálogo vigente.
- **ALMACEN:** Consulta y preparación de los productos solicitados en los pedidos de clientes.

---

## MÓDULO 1: GESTIÓN DE CLIENTES (HU6, HU7)

### Requisito 1: Registro de Clientes (HU6)
**Historia:** Como vendedor, necesito registrar los datos de los clientes para dejar sus perfiles disponibles en el sistema.

#### Especificación de Requisitos (EARS)
1. **[Event-driven]** Cuando el vendedor envía el formulario con `nombre_razonsocial` y `telefono`, el sistema debe persistir el registro en la tabla `cliente`.
2. **[Event-driven]** Cuando se intente registrar un cliente omitiendo campos obligatorios (`nombre_razonsocial` o `telefono`), el sistema debe denegar el guardado y mostrar "Error: datos faltantes".
3. **[Event-driven]** Cuando el `nombre_razonsocial` o el `telefono` ya existan registrados de forma idéntica en la tabla, el sistema debe alertar al usuario que el cliente ya existe.

### Requisito 2: Consulta y Asociación de Clientes (HU7)
**Historia:** Como vendedor, necesito consultar los datos de los clientes para asociarlos con sus ventas.

#### Criterios de Aceptación
1. El sistema debe permitir buscar clientes escribiendo su nombre o número de teléfono.
2. Si la búsqueda no arroja coincidencias, debe desplegar un aviso indicando que el cliente no existe.
3. Al seleccionar un cliente de la lista de resultados, sus datos se vinculan de manera temporal a la venta en curso en la interfaz.

---

## MÓDULO 2: CONTROL DE INVENTARIO Y PEDIDOS (HU8, HU11)

### Requisito 3: Consulta de Productos Existentes (HU8)
**Historia:** Como vendedor, necesito consultar los productos existentes para conocer su disponibilidad antes de realizar una venta.

#### Criterios de Aceptación
1. El vendedor debe visualizar el catálogo completo leyendo de manera combinada las tablas `producto` e `inventario`.
2. La vista debe mostrar: nombre, precio, tipo de corte y el stock disponible (`cantidad_kg`).
3. Si el buscador por nombre de producto no encuentra coincidencias, el sistema mostrará "Mostrar productos no encontrados".

### Requisito 4: Consulta de Pedidos en Almacén (HU11)
**Historia:** Como almacenista, necesito consultar los pedidos para preparar los productos solicitados por los clientes.

#### Criterios de Aceptación
1. El sistema presentará un listado de los registros en la tabla `pedido` cuyo estado de preparación sea 'PENDIENTE' o 'EN_PREPARACION', ordenados cronológicamente por fecha.
2. Al expandir o seleccionar un pedido, se debe desplegar el detalle de los productos y sus respectivas cantidades en kilogramos calculadas desde la tabla `ticket`.
3. Si no existen pedidos pendientes por surtir en la base de datos, el sistema debe desplegar un mensaje explícito.

---

## MÓDULO 3: REGISTRO DE VENTAS (HU5)

### Requisito 5: Procesamiento de Notas de Venta (HU5)
**Historia:** Como vendedor, necesito registrar las ventas para llevar un control de las operaciones.

#### Especificación de Requisitos (EARS)
1. **[Event-driven]** Cuando el vendedor confirma la operación, el sistema debe abrir una transacción PDO e insertar una fila en la tabla `venta` y múltiples filas asociadas en la tabla `ticket`.
2. **[Event-driven]** Cuando se intente vender un producto cuya cantidad solicitada supere el stock actual en `inventario.cantidad_kg`, el sistema debe rechazar la operación y mostrar el error controlado de stock insuficiente.
3. **[Event-driven]** Si se produce cualquier fallo de escritura en las tablas o falta un dato obligatorio de la nota, el sistema ejecutará un rollback absoluto.

---

## MÓDULO 4: EXTENSIÓN DE ADMINISTRACIÓN (HU19)

### Requisito 6: Inhabilitación y Modificación de Personal (HU19)
**Historia:** Como administrador, necesito modificar los datos de los usuarios registrados e inhabilitar sus cuentas para controlar el acceso.

#### Criterios de Aceptación
1. El administrador podrá buscar empleados mediante un buscador centralizado por nombre, usuario o rol.
2. El sistema debe permitir cambiar el campo `estado` de la tabla `empleado` a `FALSE` (Inhabilitado) o `TRUE` (Habilitado). 
3. Si una cuenta está inhabilitada (`estado == 0`), el módulo de autenticación del Sprint 1 debe rechazar inmediatamente su inicio de sesión.
