# Requisitos del Sprint: Sistema El Puerquito Digital

## Roles del Sistema (Basados en ENUM de Base de Datos)
- **ADMINISTRADOR:** Gestión total de empleados, roles, casillas de permisos y restablecimiento de contraseñas.
- **ALMACEN:** Gestión exclusiva del catálogo de productos e inventarios asociados (registro y actualización).
- **Usuario General (Cualquier empleado autenticado):** Acceso a sus funciones base y modificación de sus datos de contacto autorizados.

---

## MÓDULO 1: AUTENTICACIÓN Y EMPLEADOS (HU1, HU2, HU3, HU4)

### Requisito 1: Inicio de Sesión y Control de Seguridad (HU1 y HU3)
**Historia:** Como usuario del sistema, necesito iniciar sesión mediante mis credenciales.

#### Especificación de Requisitos (EARS)
1. **[Event-driven]** Cuando el empleado ingresa su `usuario` y `contrasenia` correctos, el sistema debe iniciar la sesión, cargar los permisos asignados a su rol y redirigirlo a su panel correspondiente según su `rol`.
2. **[Event-driven]** Cuando el empleado introduce datos inválidos, el sistema debe mostrar el mensaje "Usuario o contraseña incorrectos" y denegar el acceso, permitiendo reintentar el proceso.
3. **[Event-driven]** Cuando un campo del formulario queda vacío, el sistema debe marcarlo en rojo en la interfaz y bloquear el envío sin recargar la página.
4. **[Event-driven]** Cuando falle la conexión técnica a la base de datos (fallo del SGBD), el sistema debe capturar la excepción y mostrar un error controlado en la interfaz.
5. **[State-driven]** Mientras la cuenta del empleado esté inactiva o bloqueada en la base de datos, el sistema debe negar rotundamente el acceso.
6. **[Event-driven]** Cuando un empleado inicie sesión utilizando una contraseña marcada como "temporal" (por restablecimiento del administrador), el sistema debe interrumpir el flujo y desplegar una ventana obligatoria intermedia que exija ingresar y confirmar una contraseña nueva y definitiva antes de dejarlo navegar.

### Requisito 2: Registro de Empleados con Roles y Permisos (HU4)
**Historia:** Como administrador, necesito registrar usuarios con roles y permisos para permitir el acceso al sistema.

#### Criterios de Aceptación
1. Al registrar un empleado, el sistema debe exigir obligatoriamente: `nombre`, `apellido_p`, `usuario`, `contrasenia`, `curp`, `telefono` y el `rol` (conforme al ENUM de la base de datos) junto a sus permisos. Campos opcionales: `seg_nombre`, `apellido_m`, `genero`.
2. Si el valor de `usuario` o `curp` ya se encuentra registrado por otro empleado, el sistema debe rechazar la operación de inmediato y alertar al administrador.
3. El sistema debe impedir guardar si faltan campos obligatorios.
4. Las contraseñas deben ser encriptadas de forma segura mediante `password_hash()` antes de ser insertadas en la columna `contrasenia`.

### Requisito 3: Restablecer Contraseña (HU3)
**Historia:** Como administrador, necesito restablecer la contraseña de un usuario.

#### Criterios de Aceptación
1. El administrador debe poder buscar y seleccionar a un empleado de la lista. Si el usuario no existe, el sistema debe mostrar el mensaje "Usuario no encontrado".
2. Al seleccionar al usuario, el sistema permitirá escribir y guardar una contraseña temporal para la cuenta, marcándola en la base de datos como "temporal" para forzar el flujo del cambio obligatorio en su siguiente login.
3. La nueva contraseña definitiva ingresada por el empleado debe cumplir con un mínimo de 8 caracteres, almacenarse encriptada y desplegar el mensaje "Contraseña guardada".

### Requisito 4: Modificación de Información Personal (HU2)
**Historia:** Como usuario, necesito poder modificar mi información para mantenerla actualizada.

#### Criterios de Aceptación
1. El sistema debe mostrar un formulario con los datos actuales del usuario precargados. Cualquier empleado con sesión activa puede editar sus propios datos (`nombre`, `seg_nombre`, `apellido_p`, `apellido_m`, `telefono`, `genero`).
2. Si falta algún campo obligatorio marcado con asterisco (*), se debe mostrar en pantalla "Error: datos faltantes", no se guardarán los cambios y el usuario regresará al formulario.
3. Al guardar con éxito, el sistema actualizará los datos en la base de datos y mostrará el mensaje "Datos actualizados". Si el SGBD falla, mostrará "Error de registro".
4. **[Restricción]** El empleado común no puede modificarse a sí mismo el campo `rol`, su nombre de `usuario` ni su `curp`; esas opciones deben aparecer estrictamente bloqueadas o deshabilitadas en su vista de perfil para evitar inconsistencias de identidad.

---

## MÓDULO 2: CATÁLOGO E INVENTARIO DE PRODUCTOS (HU9, HU10)

### Requisito 5: Registro de Productos e Inventario Inicial (HU9)
**Historia:** Como almacenista, necesito registrar productos para mantener actualizado el catálogo de la tocinería.

#### Criterios de Aceptación
1. El sistema debe procesar el registro de forma coordinada e insertar simultáneamente en dos tablas: datos comerciales en `producto` y existencias físicas en `inventario` usando una relación uno a uno mediante `id_producto`.
2. El sistema debe solicitar obligatoriamente: `nombre_producto`, `precio_kg`, categoría, unidad (kg o pieza) y existencia inicial. De manera opcional se puede ingresar el `tipo_corte`.
3. Se debe validar que no exista otro producto con el mismo nombre en la base de datos. Si existe, el sistema debe rechazar la operación por completo y alertar al almacenista.
4. El sistema impedirá guardar si faltan datos obligatorios. Al completarse con éxito, el campo `estado` se inicializará en `TRUE` (Activo) automáticamente y el producto se mostrará en el catálogo.

### Requisito 6: Actualización de Productos e Inventarios (HU10)
**Historia:** Como almacenista, necesito actualizar los datos de los productos para mostrar información vigente.

#### Criterios de Aceptación
1. El sistema debe permitir buscar y seleccionar un producto en el catálogo. Si no existe, mostrará el mensaje "Producto no encontrado".
2. El sistema presentará un formulario con los datos actuales cargados y solicitará obligatoriamente: `nombre_producto`, `precio_kg` y el `estado` (true/false), además de las existencias actuales. De manera opcional se puede ingresar el `tipo_corte`.
3. Si faltan campos obligatorios, el sistema bloquea el envío. Si ocurre un error al intentar guardar los cambios en la base de datos (`producto` o `inventario`), se mostrará «Error de registro».
4. Al guardar con éxito, la información en la tabla `producto` e `inventario` debe actualizarse de forma inmediata en todo el sistema.
