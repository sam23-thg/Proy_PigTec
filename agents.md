# Proyecto: Sistema El Puerquito Digital

## Descripción
Sistema web MVC para la gestión de usuarios, roles y catálogo de productos de la tocinería "El Puerquito Digital".

## Stack (Tecnologías)
- **Backend:** PHP nativo
- **Base de Datos:** MariaDB / MySQL (Conexión mediante PDO)
- **Frontend:** HTML5, CSS3, Bootstrap v5.3 y JavaScript nativo (Vanilla JS)

## Convenciones de Código
- **Arquitectura:** Modelo-Vista-Controlador (MVC) estricto.
- **Seguridad SQL:** Consultas 100% parametrizadas utilizando PDO con sentencias preparadas para mitigar inyecciones SQL.
- **Seguridad de Acceso:** Manejo de contraseñas mediante `password_hash()` con algoritmo BCRYPT y verificación con `password_verify()`. Nunca almacenar texto plano.

### Frontend y Scripts
- Todo el código JavaScript debe residir exclusivamente en archivos independientes dentro de la ruta `public/assets/js/`.
- Queda estrictamente prohibido insertar JavaScript en línea (`<script>...</script>`) o atributos de eventos inline (`onclick`, `onsubmit`, etc.) dentro de las vistas PHP o HTML.
- Los scripts externos en las vistas deben cargarse utilizando el atributo `defer` para asegurar el procesamiento previo del HTML.

## Reglas de Comportamiento del Agente
- **Alcance Acotado:** Implementar única y exclusivamente lo solicitado en la tarea activa. No asumas ni desarrolles funciones extra.
- **Archivos de Control:** Prohibido modificar o alterar los archivos de la carpeta de documentación, incluyendo este archivo `agents.md`.
- **Dependencias:** No utilices Composer ni instales paquetes de terceros. Todo el desarrollo debe ser nativo.
- **Base de Datos:** No intentes crear, modificar la estructura, eliminar ni alterar la base de datos ni sus tablas. El esquema ya está diseñado e instanciado en el SGBD; solo debes realizar operaciones DML (Select, Insert, Update, Delete) respetando los nombres exactos de las columnas.

## Nomenclatura (Naming Conventions)
- **Clases:** PascalCase (Ejemplo: `EmpleadoController`, `Producto`).
- **Archivos de Clases:** Mismo nombre exacto que la clase con extensión `.php` (Ejemplo: `EmpleadoController.php`).
- **Métodos y Variables:** camelCase (Ejemplo: `buscarPorUsuario`, `idEmpleado`).
- **Constantes:** UPPER_SNAKE_CASE (Ejemplo: `ESTADO_ACTIVO`).
- **Idioma:** Todo el código, variables, métodos, tablas y comentarios deben escribirse en **español**, de forma clara y sin abreviaturas confusas.

## Documentación y Comentarios
- **Exhaustividad:** Es obligatorio documentar con comentarios en español **cada línea de código** lógico o de control que se escriba, explaining detalladamente su función.
- **README:** Actualizar el archivo `README.md` de la raíz cada vez que se finalice la implementación de un módulo completo.
