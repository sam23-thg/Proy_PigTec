<?php
// Activa el modo estricto de tipos para detectar valores incompatibles durante la conexión.
declare(strict_types=1); // Mantiene comprobaciones estrictas de tipos en este archivo.

// Construye la ruta absoluta del archivo de entorno ubicado en la raíz del proyecto.
$archivoEntorno = dirname(__DIR__) . '/.env'; // Localiza las credenciales sin depender del directorio de ejecución.

// Verifica que el archivo de configuración local exista antes de intentar leerlo.
if (!is_file($archivoEntorno)) { // Evita continuar sin los parámetros requeridos.
    throw new RuntimeException('No se encontró el archivo de configuración del entorno.'); // Informa el problema sin exponer credenciales.
} // Finaliza la validación de existencia del archivo de entorno.

// Lee las variables del archivo .env como valores de texto sin procesar.
$configuracion = parse_ini_file($archivoEntorno, false, INI_SCANNER_RAW); // Conserva los valores tal como fueron definidos.

// Comprueba que la lectura del archivo de entorno haya terminado correctamente.
if ($configuracion === false) { // Detecta errores de lectura o formato del archivo.
    throw new RuntimeException('No fue posible leer la configuración del entorno.'); // Informa el fallo de forma controlada.
} // Finaliza la validación de lectura del archivo .env.

// Obtiene el nombre del servidor o utiliza una cadena vacía si falta la clave.
$servidor = (string) ($configuracion['DB_HOST'] ?? ''); // Lee el host definido en la arquitectura.
// Obtiene el nombre de la base de datos o utiliza una cadena vacía si falta la clave.
$nombreBaseDatos = (string) ($configuracion['DB_NAME'] ?? ''); // Lee la base de datos definida para el sistema.
// Obtiene el usuario de conexión o utiliza una cadena vacía si falta la clave.
$usuarioBaseDatos = (string) ($configuracion['DB_USER'] ?? ''); // Lee el usuario definido para MariaDB o MySQL.
// Obtiene la contraseña, permitiendo que permanezca vacía según la configuración local.
$contraseniaBaseDatos = (string) ($configuracion['DB_PASS'] ?? ''); // Lee la contraseña sin mostrarla en la salida.

// Confirma que los parámetros obligatorios estén presentes antes de construir el DSN.
if ($servidor === '' || $nombreBaseDatos === '' || $usuarioBaseDatos === '') { // La contraseña puede estar vacía, pero los demás datos son necesarios.
    throw new RuntimeException('Faltan parámetros obligatorios para conectar con la base de datos.'); // Expone solo un mensaje seguro.
} // Finaliza la comprobación de parámetros obligatorios.

// Prepara opciones PDO con errores como excepciones, resultados asociativos y sentencias nativas.
$opcionesConexion = [ // Agrupa las opciones de conexión requeridas por PDO.
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Permite capturar los errores de PDO mediante excepciones.
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Devuelve filas de consulta con claves asociativas por defecto.
    PDO::ATTR_EMULATE_PREPARES => false, // Solicita al controlador preparar sentencias directamente en el servidor.
]; // Termina la configuración de opciones PDO.

// Intenta establecer una conexión segura con la base de datos definida en el archivo .env.
try { // Inicia el bloque que controla los fallos producidos al conectar.
    $cadenaConexion = "mysql:host={$servidor};dbname={$nombreBaseDatos};charset=utf8mb4"; // Define el controlador, host, esquema y codificación.
    $conexion = new PDO($cadenaConexion, $usuarioBaseDatos, $contraseniaBaseDatos, $opcionesConexion); // Crea la conexión con manejo de errores por excepción.
} catch (PDOException $excepcion) { // Captura únicamente los errores reportados por el controlador PDO.
    error_log('Falló la conexión PDO con la base de datos configurada.'); // Registra un detalle genérico sin divulgar credenciales.
    throw new RuntimeException('No fue posible establecer la conexión con la base de datos.', 0, $excepcion); // Propaga un error controlado conservando la causa original.
} // Finaliza el manejo controlado de errores de conexión.

// Devuelve la conexión para que los componentes del sistema puedan utilizarla.
return $conexion; // Proporciona la instancia PDO configurada.
