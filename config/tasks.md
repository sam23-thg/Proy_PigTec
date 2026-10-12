# Tareas del Sprint: Implementación "El Puerquito Digital"

## ⚠️ Reglas Estrictas de Desarrollo para el Agente
1. **Secuencialidad:** Realizar estrictamente una sola tarea a la vez. No avances a la siguiente hasta completar la actual.
2. **Pruebas:** Probar la funcionalidad de la tarea en el navegador utilizando los datos reales de la base de datos antes de marcarla como lista.
3. **Control de Versiones:** Realizar un commit de Git descriptivo inmediatamente al finalizar cada tarea con éxito.
4. **Respeto a la Base de Datos:** Se trabaja sobre las tablas existentes (`empleado` y `producto`). Está prohibido modificar columnas, ejecutar `DROP TABLE` o alterar los triggers/constraints existentes.

---

## 1. Preparación del Entorno y Conexión
- 1.1 Crear la estructura base de carpetas MVC: `app/models/`, `app/views/`, `app/controllers/`, y `public/`.
- 1.2 Configurar el archivo `.env` en la raíz mapeando la base de datos `elpuerquitodigital` y crear la conexión PDO en `config/database.php`.
- 1.3 Crear el archivo `.gitignore` para excluir estrictamente el archivo de configuración de base de datos y el `.env`.

## 2. Capa de Modelos (Mapeo Exacto de Tablas)
- 2.1 Crear `app/models/Empleado.php` interactuando con la tabla `empleado`. Implementar los métodos: `buscarPorUsuario($usuario)`, `registrar($datos)`, `actualizarDatos($id, $datos)`, `cambiarPassword($id, $hash)` y `listarTodos()`.
- 2.2 Crear `app/models/Producto.php` interactuando con la tabla `producto`. Implementar los métodos: `crear($datos)`, `actualizar($id, $datos)`, `listarTodo()` y `buscarPorId($id)`.

## 3. Módulo de Autenticación
- 3.1 Crear `public/index.php` (Front Controller) para gestionar el enrutamiento inicial del sistema.
- 3.2 Crear la vista `app/views/auth/login.php` con Bootstrap 5. El formulario debe pedir "Usuario" (`usuario`) y "Contraseña" (`contrasenia`) y aplicar estilos en rojo si se envían vacíos.
- 3.3 Crear `app/controllers/AuthController.php` procesando el login con `password_verify()`. Almacenar el `id_empleado`, `usuario` y el `rol` (`ADMINISTRADOR`, `VENTAS`, etc.) en la sesión PHP.

## 4. Gestión de Empleados (Rol ADMINISTRADOR)
- 4.1 Crear un sistema de restricción en el controlador para asegurar que solo usuarios con sesión activa y rol `ADMINISTRADOR` accedan a este bloque.
- 4.2 Crear `app/controllers/EmpleadoController.php` para coordinar el CRUD de personal.
- 4.3 Crear la vista `app/views/empleados/registro.php`. Debe permitir registrar un empleado completo mapeando los campos requeridos y encriptando el campo `contrasenia` con `password_hash()`.
- 4.4 Crear la vista `app/views/empleados/lista.php` para visualizar a todo el personal y añadir un botón de "Restablecer Contraseña" que apunte a una vista dedicada `app/views/empleados/restablecer.php`.

## 5. Perfil de Usuario (Cualquier Empleado Autenticado)
- 5.1 Crear la vista `app/views/empleados/perfil.php` donde el usuario logueado pueda modificar exclusivamente su campo `telefono`. Asegurar que los campos `usuario` y `rol` se muestren como solo lectura (`disabled` o `readonly`).

## 6. Módulo de Productos (Rol ALMACEN)
- 6.1 Implementar la validación de acceso en el controlador de productos asegurando que solo los roles `ALMACEN` o `ADMINISTRADOR` puedan interactuar con él.
- 6.2 Crear `app/controllers/ProductoController.php` con métodos para registrar y actualizar productos en la base de datos.
- 6.3 Crear la vista `app/views/productos/nuevo.php` con el formulario de registro mapeando exactamente `nombre_producto`, `tipo_corte` y `precio_kg`.
- 6.4 Crear la vista `app/views/productos/editar.php` para cargar los datos actuales de la tabla `producto` y permitir la actualización de sus columnas, incluyendo cambiar el `estado` (1 o 0).

## 7. Cierre del Sprint
- 7.1 Realizar una prueba de inicio de sesión utilizando uno de los usuarios del dump de tu base de datos (Nota: Asegúrate de actualizar manualmente un registro en tu BD con un hash válido de `password_hash()` para la prueba, por ejemplo para `alexis_admin`).
- 7.2 Verificar en el navegador que el rol `VENTAS` o `ALMACEN` tenga denegado el acceso a la lista de empleados.
- 7.3 Actualizar el archivo `README.md` detallando las credenciales de prueba y los requerimientos de entorno.
