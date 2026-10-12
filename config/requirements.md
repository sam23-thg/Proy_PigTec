# Requisitos del Sprint: Sistema El Puerquito Digital

## Contexto del Sistema
Este sistema controla el acceso de empleados mediante roles y gestiona el catálogo de productos de la tocinería "El Puerquito Digital" bajo una arquitectura MVC basada en una base de datos preexistente.

## Roles del Sistema (Basados en ENUM de Base de Datos)
- **ADMINISTRADOR:** Gestión total de empleados, roles y restablecimiento de contraseñas.
- **ALMACEN:** Gestión exclusiva del catálogo de productos (registro y actualización).
- **Usuario General (Cualquier empleado autenticado):** Acceso a sus funciones base y modificación de sus datos de contacto.

---

## MÓDULO 1: AUTENTICACIÓN Y EMPLEADOS (HU1, HU2, HU3, HU4)

### Requisito 1: Inicio de Sesión (HU1)
**Historia:** Como usuario del sistema, necesito iniciar sesión mediante mis credenciales.

#### Especificación de Requisitos (EARS)
1. **[Event-driven]** Cuando el empleado ingresa su `usuario` y `contrasenia` correctos, el sistema debe iniciar la sesión y redirigirlo a su panel correspondiente según su `rol`.
2. **[Event-driven]** Cuando el empleado introduce datos inválidos, el sistema debe mostrar el mensaje "Usuario o contraseña incorrectos" y denegar el acceso.
3. **[Event-driven]** Cuando un campo del formulario queda vacío, el sistema debe marcarlo en rojo en la interfaz y bloquear el envío.

### Requisito 2: Registro de Empleados con Roles (HU4)
**Historia:** Como administrador, necesito registrar usuarios con roles y permisos para permitir el acceso al sistema.

#### Criterios de Aceptación
1. Al registrar un empleado, el sistema debe exigir obligatoriamente: `nombre`, `apellido_p`, `usuario`, `contrasenia` y `rol` (conforme al ENUM de la base de datos). Campos opcionales: `curp`, `seg_nombre`, `apellido_m`, `genero`, `telefono`.
2. Si el valor de `usuario` o `curp` ya se encuentra registrado, el sistema debe rechazar la operación y alertar al administrador.
3. Las contraseñas deben ser encriptadas mediante `password_hash()` antes de ser insertadas en el campo `contrasenia`.

### Requisito 3: Restablecer Contraseña (HU3)
**Historia:** Como administrador, necesito restablecer la contraseña de un usuario.

#### Criterios de Aceptación
1. Cuando el administrador selecciona un empleado de la lista, el sistema debe permitirle escribir una nueva contraseña.
2. La nueva contraseña debe cumplir con un mínimo de 8 caracteres y almacenarse encriptada en la columna `contrasenia`.

### Requisito 4: Modificación de Información Personal (HU2)
**Historia:** Como usuario, necesito poder modificar mi información para mantenerla actualizada.

#### Criterios de Aceptación
1. Cualquier empleado con sesión activa puede editar sus propios datos de contacto (`telefono`).
2. **[Restricción]** El empleado común no puede modificarse a sí mismo el campo `rol` ni su nombre de `usuario`; esas opciones deben aparecer bloqueadas o deshabilitadas en su vista de perfil.

---

## MÓDULO 2: CATÁLOGO DE PRODUCTOS (HU9, HU10)

### Requisito 5: Registro de Productos (HU9)
**Historia:** Como almacenista, necesito registrar productos para mantener actualizado el catálogo de la tocinería.

#### Criterios de Aceptación
1. El sistema debe solicitar obligatoriamente: `nombre_producto` y `precio_kg`. De manera opcional se puede ingresar el `tipo_corte`.
2. El campo `estado` debe inicializarse en 1 (Activo) de forma automática.

### Requisito 6: Actualización de Productos (HU10)
**Historia:** Como almacenista, necesito actualizar los datos de los productos para mostrar información vigente.

#### Criterios de Aceptación
1. El sistema debe permitir modificar el `nombre_producto`, `tipo_corte`, `precio_kg` y el `estado` (activo/inactivo) de un registro existente.
2. Al guardar los cambios, la información en la tabla `producto` debe actualizarse de forma inmediata.
