<?php
// Activa la comprobación estricta de tipos para los argumentos y resultados de esta clase.
declare(strict_types=1); // Evita conversiones implícitas inesperadas al trabajar con datos de empleados.

// Declara el modelo responsable de consultar y modificar exclusivamente la tabla empleado.
class Empleado // Representa las operaciones de persistencia de empleados del sistema.
{ // Inicia la definición de la clase Empleado.
    // Conserva la conexión PDO que se inyecta desde la configuración de la aplicación.
    private PDO $db; // Centraliza el acceso preparado a la base de datos existente.

    // Recibe una conexión PDO ya configurada para ejecutar las operaciones del modelo.
    public function __construct(PDO $db) // Permite reutilizar la conexión común de la aplicación.
    { // Inicia la inicialización del modelo.
        // Guarda la conexión recibida sin abrir conexiones adicionales ni alterar la configuración.
        $this->db = $db; // Asigna la conexión PDO al atributo privado del modelo.
    } // Finaliza la inicialización del modelo.

    // Busca por usuario y devuelve las columnas necesarias para autenticación o null si no existe.
    public function buscarPorUsuario(string $usuario): ?array // Tipifica el usuario y el resultado opcional de la consulta.
    { // Inicia la búsqueda de un empleado por su nombre de usuario.
        // Consulta las columnas existentes para adaptarse al esquema real sin modificarlo.
        $consultaColumnas = $this->db->query('SHOW COLUMNS FROM empleado'); // Lee solo metadatos de la tabla para detectar columnas opcionales.
        // Obtiene los nombres de columna informados por el servidor MariaDB o MySQL.
        $columnasExistentes = array_column($consultaColumnas->fetchAll(), 'Field'); // No obtiene ni imprime datos de empleados.
        // Define las columnas mínimas conocidas para identificar y validar una cuenta.
        $columnasConsulta = ['id_empleado', 'nombre', 'apellido_p', 'usuario', 'contrasenia', 'rol', 'estado']; // Incluye el estado requerido para validar la activación de la cuenta.
        // Define los campos adicionales documentados que pueden faltar en el esquema actualmente desplegado.
        $columnasOpcionales = ['curp', 'seg_nombre', 'apellido_m', 'genero', 'telefono', 'es_temporal']; // Mantiene opcionales solo los campos que pueden faltar en el esquema.
        // Añade a la consulta solo campos opcionales que realmente existan en empleado.
        foreach ($columnasOpcionales as $columnaOpcional) { // Recorre una lista fija de identificadores confiables.
            // Comprueba el nombre de columna contra los metadatos obtenidos del servidor.
            if (in_array($columnaOpcional, $columnasExistentes, true)) { // Evita crear consultas contra columnas inexistentes.
                // Agrega a la selección la columna válida detectada en el esquema.
                $columnasConsulta[] = $columnaOpcional; // Usa únicamente identificadores predefinidos.
            } // Finaliza la comprobación de existencia de la columna opcional.
        } // Finaliza la detección de columnas opcionales.
        // Construye la selección usando exclusivamente nombres incluidos en las listas locales.
        $columnasSql = implode(', ', $columnasConsulta); // Los valores externos nunca participan en los identificadores SQL.
        // Prepara la búsqueda parametrizada con el conjunto de columnas que soporta el esquema activo.
        $consulta = $this->db->prepare("SELECT {$columnasSql} FROM empleado WHERE usuario = :usuario LIMIT 1"); // Evita errores por columnas faltantes sin cambiar tablas.
        // Asocia el usuario recibido al parámetro nombrado de la consulta preparada.
        $consulta->bindValue(':usuario', $usuario, PDO::PARAM_STR); // Envía el dato como texto al controlador PDO.
        // Ejecuta la búsqueda parametrizada en la tabla empleado existente.
        $consulta->execute(); // Realiza una operación de lectura sin modificar datos.
        // Devuelve la fila asociativa encontrada o null cuando el usuario no está registrado.
        return $consulta->fetch() ?: null; // Mantiene una respuesta clara para los casos con y sin coincidencia.
    } // Finaliza la búsqueda por usuario.

    // Comprueba con una consulta preparada si el CURP ya pertenece a un empleado.
    public function buscarPorCurp(string $curp): bool // Devuelve si existe una fila con el CURP indicado.
    { // Inicia la comprobación de unicidad del CURP.
        // Prepara una búsqueda limitada que no modifica ningún registro.
        $consulta = $this->db->prepare('SELECT id_empleado FROM empleado WHERE curp = :curp LIMIT 1'); // Mantiene el CURP fuera del texto SQL.
        // Ejecuta la consulta asociando el valor recibido al parámetro nombrado.
        $consulta->execute([':curp' => $curp]); // Evita inyección SQL y solo lee la tabla existente.
        // Informa si la búsqueda encontró un identificador de empleado.
        return $consulta->fetchColumn() !== false; // Convierte la existencia de una fila en un resultado booleano.
    } // Finaliza la comprobación de CURP duplicado.

    // Registra un empleado usando las columnas reales y el hash generado por el controlador.
    public function registrar(array $datos): bool // Recibe datos validados y la contraseña ya cifrada con BCRYPT.
    { // Inicia el registro de un empleado.
        // Prepara la inserción usando solo columnas reales y no incluye credenciales en el texto SQL.
        $consulta = $this->db->prepare('INSERT INTO empleado (curp, nombre, seg_nombre, apellido_p, apellido_m, genero, telefono, usuario, contrasenia, rol) VALUES (:curp, :nombre, :seg_nombre, :apellido_p, :apellido_m, :genero, :telefono, :usuario, :contrasenia, :rol)'); // Omite identificador y estado para respetar sus valores por defecto.
        // Ejecuta el alta con valores parametrizados y los campos opcionales como null cuando no se proporcionan.
        return $consulta->execute([ // Devuelve el resultado de ejecución de PDO como indicador de éxito.
            ':curp' => (string) $datos['curp'], // Mapea el CURP obligatorio a la columna curp.
            ':nombre' => (string) $datos['nombre'], // Mapea el nombre obligatorio a la columna nombre.
            ':seg_nombre' => $datos['seg_nombre'] ?? null, // Mapea el segundo nombre opcional o guarda null.
            ':apellido_p' => (string) $datos['apellido_p'], // Mapea el apellido paterno obligatorio a apellido_p.
            ':apellido_m' => $datos['apellido_m'] ?? null, // Mapea el apellido materno opcional o guarda null.
            ':genero' => $datos['genero'] ?? null, // Mapea el género opcional o guarda null.
            ':telefono' => (string) $datos['telefono'], // Mapea el teléfono obligatorio a la columna telefono.
            ':usuario' => (string) $datos['usuario'], // Mapea el identificador de acceso a la columna usuario.
            ':contrasenia' => (string) $datos['contrasenia'], // Persiste únicamente el hash BCRYPT generado previamente por el controlador.
            ':rol' => (string) $datos['rol'], // Mapea el rol permitido por el ENUM existente.
        ]); // Finaliza la ejecución parametrizada del INSERT.
    } // Finaliza el registro de empleado.

    // Actualiza exclusivamente los datos personales que puede modificar el propio empleado.
    public function actualizarDatos(int $id, array $datos): bool // Recibe el identificador y campos personales modificables.
    { // Inicia la actualización de datos personales.
        // Prepara una actualización limitada a los campos autorizados del perfil personal.
        $consulta = $this->db->prepare('UPDATE empleado SET nombre = :nombre, seg_nombre = :seg_nombre, apellido_p = :apellido_p, apellido_m = :apellido_m, telefono = :telefono, genero = :genero WHERE id_empleado = :id_empleado'); // No permite modificar usuario, CURP ni rol desde este método.
        // Ejecuta la actualización mediante parámetros para impedir que los datos alteren la consulta SQL.
        return $consulta->execute([ // Devuelve el estado reportado por PDO para la sentencia UPDATE.
            ':nombre' => (string) $datos['nombre'], // Sustituye el nombre personal requerido.
            ':seg_nombre' => $datos['seg_nombre'] ?? null, // Sustituye el segundo nombre opcional o lo deja vacío.
            ':apellido_p' => (string) $datos['apellido_p'], // Sustituye el apellido paterno requerido.
            ':apellido_m' => $datos['apellido_m'] ?? null, // Sustituye el apellido materno opcional o lo deja vacío.
            ':telefono' => (string) $datos['telefono'], // Sustituye el teléfono personal requerido.
            ':genero' => $datos['genero'] ?? null, // Sustituye el género opcional o lo deja vacío.
            ':id_empleado' => $id, // Limita el cambio al empleado cuyo identificador fue recibido.
        ]); // Finaliza la ejecución parametrizada del UPDATE.
    } // Finaliza la actualización de datos personales.

    // Cambia exclusivamente el hash de contraseña de la cuenta indicada.
    public function cambiarPassword(int $id, string $hash): bool // Recibe el identificador del empleado y un hash ya calculado.
    { // Inicia la actualización de credenciales.
        // Prepara una actualización que modifica solo la columna contrasenia.
        $consulta = $this->db->prepare('UPDATE empleado SET contrasenia = :contrasenia WHERE id_empleado = :id_empleado'); // Evita referirse a es_temporal porque no existe en la base real.
        // Ejecuta la actualización mediante parámetros para evitar inyección SQL.
        return $consulta->execute([ // Devuelve si PDO pudo ejecutar la sentencia.
            ':contrasenia' => $hash, // Persiste el hash recibido sin asumir ni almacenar texto plano.
            ':id_empleado' => $id, // Identifica la cuenta cuya contraseña se debe cambiar.
        ]); // Finaliza la ejecución parametrizada del cambio de contraseña.
    } // Finaliza la actualización de credenciales.

    // Devuelve el listado de empleados sin exponer hashes de contraseñas.
    public function listarUsuarios(): array // Proporciona una colección asociativa con los datos de listado.
    { // Inicia la consulta de listado de empleados.
        // Prepara la selección de columnas de identidad, contacto, rol y estado útiles para el listado.
        $consulta = $this->db->prepare('SELECT id_empleado, curp, nombre, seg_nombre, apellido_p, apellido_m, genero, telefono, usuario, rol, estado, es_temporal FROM empleado ORDER BY id_empleado'); // Excluye deliberadamente la columna sensible contrasenia.
        // Ejecuta la consulta de lectura de todos los empleados ordenados por identificador.
        $consulta->execute(); // No requiere valores externos porque la consulta no contiene parámetros.
        // Devuelve las filas asociativas obtenidas o una colección vacía si no hay empleados.
        return $consulta->fetchAll(); // Conserva el modo FETCH_ASSOC configurado en la conexión PDO.
    } // Finaliza la consulta del listado de empleados.

    // Recupera las columnas visibles para la tabla administrativa sin exponer contrasenias.
    public function listarTodos(): array // Devuelve los datos básicos de cada empleado en formato asociativo.
    { // Inicia la consulta que alimenta la vista de lista de empleados.
        // Prepara una consulta que excluye contrasenia y compone el nombre usando los campos reales del esquema.
        $consulta = $this->db->prepare("SELECT id_empleado, CONCAT_WS(' ', nombre, NULLIF(seg_nombre, ''), apellido_p, NULLIF(apellido_m, '')) AS nombre_completo, usuario, rol, telefono FROM empleado ORDER BY id_empleado DESC"); // Omite la contraseña y presenta primero los registros recientes.
        // Ejecuta la consulta preparada, aunque no reciba filtros externos.
        $consulta->execute(); // Conserva el uso de PDO preparado solicitado para esta operación.
        // Devuelve las filas de forma asociativa para que la vista escape cada valor al renderizarlo.
        return $consulta->fetchAll(); // Usa el modo asociativo configurado en la conexión PDO.
    } // Finaliza la consulta de todos los empleados.
} // Finaliza la definición de la clase Empleado.
