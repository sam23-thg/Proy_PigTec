# Tareas del Sprint: Implementation "El Puerquito Digital"

## ⚠️ Reglas Estrictas de Desarrollo para el Agente
1. **Secuencialidad:** Realizar estrictamente una sola tarea a la vez. No avances a la siguiente hasta completar la actual.
2. **Pruebas:** Probar la funcionalidad de la tarea en el navegador utilizando los datos reales de la base de datos antes de marcarla como lista.
3. **Control de Versiones:** Realizar un commit de Git descriptivo inmediatamente al finalizar cada tarea con éxito.
4. **Respeto a la Base de Datos:** Se trabaja sobre las tablas existentes (`empleado`, `producto`, `inventario`). Está prohibido modificar columnas, ejecutar `DROP TABLE` o alterar triggers existentes de forma arbitraria.

---

## 1. Preparación del Entorno y Conexión
- 1.1 Crear la estructura base de carpetas MVC: `app/models/`, `app/views/`, `app/controllers/`, y `public/`.
- 1.2 Configurar el archivo `.env` en la raíz mapeando la base de datos `elpuerquitodigital` y crear la conexión PDO en `config/database.php`. Incorporar el manejo de excepciones `try-catch` para controlar errores de conexión.
- 1.3 Crear el archivo `.gitignore` para excluir estrictamente el archivo de configuración de base de datos y el `.env`.

## 2. Capa de Modelos (Mapeo Exacto de Tablas Relacionales)
- 2.1 Crear `app/models/Empleado.php` interactuando con la tabla `empleado`. Implementar los métodos: `buscarPorUsuario($usuario)`, `registrar($datos)`, `actualizarDatos($id, $datos)`, `cambiarPassword($id, $hash, $esTemporal)` y `listarUsuarios()`.
- 2.2 Crear `app/models/Inventario.php` con métodos básicos de escritura/lectura vinculados a la tabla `inventario`.
- 2.3 Crear `app/models/Producto.php`. Incorporar un método transaccional PDO (`beginTransaction`, `commit`, `rollBack`). El método `crearConInventario($datosProducto, $existenciaInicial)` debe insertar primero en `producto`, obtener `id_producto` mediante `lastInsertId()` e inmediatamente insertar en `inventario`.
- 2.4 Implementar en el modelo `Producto.php` el método transaccional `actualizarConInventario($id, $datosProducto, $cantidadActual)`. Debe ejecutar dos sentencias `UPDATE` consecutivas (una para la tabla `producto` y otra para `inventario`) bajo la misma transacción.

## 3. Módulo de Autenticación y Control de Contraseñas (HU1 y HU3)
- 3.1 Crear `public/index.php` (Front Controller) para gestionar el enrutamiento inicial y la verificación de sesiones globales.
- 3.2 Crear la vista `app/views/auth/login.php` con un formulario con campos de usuario y contraseña (ocultos por tipo password) y botón «Ingresar».
- 3.3 Crear `app/controllers/AuthController.php` procesando el login con `password_verify()`. Almacenar id, usuario, rol y permisos en `$_SESSION['user']`. Negar acceso explícito si la cuenta está inactiva o bloqueada.
- 3.4 Implementar en `AuthController.php` una validación de estado: Si la contraseña activa es identificada como temporal (`es_temporal == 1`), redirigir forzadamente a una vista obligatoria intermedia `app/views/auth/cambiar_temporal.php`.
- 3.5 Diseñar la interfaz de cambio de contraseña obligatoria en `app/views/auth/cambiar_temporal.php` que oculte la nueva contraseña, provea la opción visual de "poder verla", compare contraseñas iguales y procese la actualización definitiva a través del controlador mostrando el mensaje "Contraseña guardada".

## 4. Gestión de Empleados (Rol ADMINISTRADOR) (HU4, HU3)
- 4.1 Crear un sistema de restricción en el controlador para asegurar que solo usuarios con sesión activa y rol `ADMINISTRADOR` accedan a este bloque.
- 4.2 Crear `app/controllers/EmpleadoController.php` para coordinar el CRUD de personal y restablecimientos.
- 4.3 Crear la vista `app/views/empleados/registro.php` que muestre un formulario de alta (Nombre, Usuario, Contraseña, CURP, Teléfono) con lista desplegable de roles, casillas de verificación para permisos específicos y un listado inferior de usuarios registrados. Validar que el usuario no esté duplicado e impedir guardar si faltan campos obligatorios.
- 4.4 Implementar el buscador de usuarios y campo para capturar la contraseña temporal en la vista `app/views/empleados/lista.php` o una subvista de restablecimiento. Mostrar "Usuario no encontrado" si falla la consulta.

## 5. Perfil de Usuario (Cualquier Empleado Autenticado) (HU2)
- 5.1 Crear la vista `app/views/empleados/perfil.php` donde se carguen y precarguen los datos actuales del usuario. Los campos obligatorios deben estar marcados con asterisco (*), y contar con botones de "Guardar" y "Cancelar".
- 5.2 Añadir validaciones en el controlador para detonar los mensajes visibles de confirmación y error correspondientes: "Datos actualizados", "Error: datos faltantes" o "Error de registro". Mantener los campos `usuario`, `curp` y `rol` estrictamente como solo lectura (`disabled`/`readonly`).

## 6. Módulo de Productos e Inventarios Unificados (Rol ALMACEN) (HU9, HU10)
- 6.1 Implementar la validación de acceso en el controlador de productos asegurando que solo los roles `ALMACEN` o `ADMINISTRADOR` puedan interactuar.
- 6.2 Crear `app/controllers/ProductoController.php` unificando transaccionalmente las escrituras sobre las tablas `producto` e `inventario` a través de los formularios únicos.
- 6.3 Crear la vista `app/views/productos/nuevo.php`. Diseñar un **formulario único** que solicite juntos: Nombre, Categoría, Unidad, Tipo de Corte, Precio por Kg **y la Existencia Inicial**. Todos los obligatorios marcados con `*`. Validar que el nombre del producto sea único antes de almacenar, de lo contrario lanzar aviso.
- 6.4 Crear la vista `app/views/productos/editar.php`. Mostrar el formulario integrado con los datos comerciales y la cantidad de inventario actual cargados simultáneamente en los inputs. Incluir el cambio de `estado` (true/false) e implementar el manejo de alertas para "Producto no encontrado" y "Error de registro".

## 7. Cierre del Sprint
- 7.1 Realizar una prueba integral simulando los flujos de login común, redirecciones de roles, interceptor de contraseña temporal, validaciones de asteriscos vacíos, inserciones uno a uno en inventarios, etc.
- 7.2 Actualizar el archivo `README.md` detallando las credenciales de prueba y los requerimientos de entorno.
