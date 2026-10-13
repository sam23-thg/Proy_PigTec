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
        // Prepara una consulta limitada a las columnas reales requeridas para identificar la cuenta.
        $consulta = $this->db->prepare('SELECT id_empleado, curp, nombre, seg_nombre, apellido_p, apellido_m, genero, telefono, usuario, contrasenia, rol, estado, es_temporal FROM empleado WHERE usuario = :usuario LIMIT 1'); // Evita interpolar el valor recibido en SQL.
        // Asocia el usuario recibido al parámetro nombrado de la consulta preparada.
        $consulta->bindValue(':usuario', $usuario, PDO::PARAM_STR); // Envía el dato como texto al controlador PDO.
        // Ejecuta la búsqueda parametrizada en la tabla empleado existente.
        $consulta->execute(); // Realiza una operación de lectura sin modificar datos.
        // Devuelve la fila asociativa encontrada o null cuando el usuario no está registrado.
        return $consulta->fetch() ?: null; // Mantiene una respuesta clara para los casos con y sin coincidencia.
    } // Finaliza la búsqueda por usuario.

    // Registra un empleado usando las columnas reales y almacena la contraseña con BCRYPT.
    public function registrar(array $datos): bool // Recibe los datos del formulario y comunica si el INSERT tuvo éxito.
    { // Inicia el registro de un empleado.
        // Genera un hash BCRYPT antes de persistir la contraseña en la columna contrasenia.
        $hashContrasenia = password_hash((string) $datos['contrasenia'], PASSWORD_BCRYPT); // Nunca guarda la contraseña proporcionada en texto plano.
        // Detiene el registro si el proceso de hash no pudo generar una cadena válida.
        if ($hashContrasenia === false) { // Comprueba explícitamente el resultado de la función de seguridad.
            return false; // Evita guardar credenciales que no se hayan protegido correctamente.
        } // Finaliza la validación del hash de contraseña.

        // Prepara la inserción con nombres exactos de columnas definidos para empleado.
        $consulta = $this->db->prepare('INSERT INTO empleado (curp, nombre, seg_nombre, apellido_p, apellido_m, genero, telefono, usuario, contrasenia, rol, es_temporal) VALUES (:curp, :nombre, :seg_nombre, :apellido_p, :apellido_m, :genero, :telefono, :usuario, :contrasenia, :rol, :es_temporal)'); // Omite estado para respetar su valor predeterminado TRUE en el esquema.
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
            ':contrasenia' => $hashContrasenia, // Persiste únicamente el hash BCRYPT generado previamente.
            ':rol' => (string) $datos['rol'], // Mapea el rol permitido por el ENUM existente.
            ':es_temporal' => 0, // Marca la contraseña inicial como definitiva, no temporal.
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

    // Cambia el hash de contraseña y registra si debe considerarse temporal en el próximo acceso.
    public function cambiarPassword(int $id, string $hash, bool $esTemporal): bool // Recibe un hash ya calculado y la bandera de temporalidad.
    { // Inicia la actualización de credenciales.
        // Prepara la actualización de las dos columnas de credenciales existentes.
        $consulta = $this->db->prepare('UPDATE empleado SET contrasenia = :contrasenia, es_temporal = :es_temporal WHERE id_empleado = :id_empleado'); // Mantiene sin cambios los demás datos de la cuenta.
        // Ejecuta la actualización enviando la bandera booleana como entero compatible con MariaDB y MySQL.
        return $consulta->execute([ // Devuelve si PDO pudo ejecutar la sentencia.
            ':contrasenia' => $hash, // Persiste el hash recibido sin asumir ni almacenar texto plano.
            ':es_temporal' => $esTemporal ? 1 : 0, // Guarda uno para temporal y cero para definitiva.
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
} // Finaliza la definición de la clase Empleado.
