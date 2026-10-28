# Guía de Diseño de Interfaz (UI) - El Puerquito Digital

## 1. Lineamientos Visuales Generales (Bootstrap 5.3)
- **Campos Obligatorios:** Deben llevar un asterisco rojo claramente visible: `(*)` o `class="text-danger"`.
- **Mensajes y Alertas:** Mostrar notificaciones en la parte superior de los contenedores utilizando componentes de alerta de Bootstrap:
  - Éxito: `.alert.alert-success` ("Datos actualizados", "Contraseña guardada").
  - Error/Fallo: `.alert.alert-danger` ("Error: datos faltantes", "Error de registro", "Usuario no encontrado", "Usuario o contraseña incorrectos").
- **Campos Ocultos de Contraseña:** Utilizar `<input type="password">`. En la pantalla de contraseña temporal, incluir un botón con icono de ojo para conmutar el atributo `type` entre `text` y `password` (Permitir ver la contraseña).

---

## 2. Distribución de Pantallas (Mapeo de Wireframes de Figma)
Estos son elementos a incluir, pero siempre basados en los diseños de figma por lo que puede haber modificaciones.

### Vista: Login (`app/views/auth/login.php`)
- Tarjeta centrada horizontal y verticalmente. Formulario con inputs para usuario y contraseña (oculto de forma nativa), botón «Ingresar» (`.btn-primary.w-100`). Alerta roja si falla la conexión al SGBD.

### Vista obligatoria intermedia: Cambio de Contraseña Temporal (`app/views/auth/cambiar_temporal.php`)
- Bloqueo total de navegación. Tarjeta modal u obligatoria. Campo "Nueva contraseña" (tipo password con opción de toggle para poder verla) y un campo de confirmación con botón "Confirmar contraseña".

### Vista: Lista de Empleados y Restablecimiento (`app/views/empleados/lista.php`)
- Buscador superior de usuarios por campo de texto. Tabla de personal. En la fila de acciones, botón de llave que abre un modal con el campo para capturar la contraseña temporal y el botón para aplicar el restablecimiento por parte del administrador.

### Vista: Formulario de Registro de Empleados (`app/views/empleados/registro.php`)
- Formulario de alta completo con inputs de texto básicos, una lista desplegable (`<select>`) de roles y una sección con una grilla de casillas de verificación para los permisos individuales del rol. En la sección inferior o lateral derecha, incluir el listado de usuarios ya registrados de forma compacta.

### Vista: Perfil del Empleado (`app/views/empleados/perfil.php`)
- Formulario con campos precargados con los datos actuales. Marcar obligatorios con `*`. Incluir de forma clara los botones "Guardar" y "Cancelar". Los inputs de `usuario`, `curp` y `rol` se renderizan como `readonly` o `disabled`.

### Vista: Registro de Productos (`app/views/productos/nuevo.php`)
- **Estructura:** Tarjeta central con formulario limpio y ordenado. Todos los campos obligatorios marcados con `(*)`.
- **Distribución de Elementos en Pantalla:**
  - Caja de Texto: Nombre del Producto `(*)`
  - Selección (Dropdown): Categoría y Unidad (Kg) `(*)`
  - Caja de Texto: Tipo de Corte (Opcional)
  - Caja Numérica: Precio por Kilogramo `(*)` (Estilizado con un signo `$` usando la clase `.input-group` de Bootstrap).
  - **Sección Especial de Almacén:** Caja Numérica: Existencia Inicial `(*)` (Aparece al final del mismo formulario).
  - Botones inferiores: "Guardar Producto" (`.btn-primary`) y "Cancelar".

### Vista: Edición de Productos e Inventario (`app/views/productos/editar.php`)
- **Estructura:** Buscador de productos superior. Al seleccionar uno, se despliega el formulario unificado con la información comercial e inventario vigente precargada.
- **Distribución de Elementos en Pantalla:**
  - Muestra los mismos campos de la vista de registro pero con los valores actuales leídos desde ambas tablas (`producto` e `inventario`).
  - Interruptor visual (Toggle/Checkbox): "Estado del Producto" (Activo / Inactivo) `(*)` para habilitar o deshabilitar el registro comercial.
  - El campo de existencia debe permitir modificar la cantidad actual directamente.
  - Botones inferiores: "Guardar Cambios" (`.btn-success`) y "Volver al Catálogo".

