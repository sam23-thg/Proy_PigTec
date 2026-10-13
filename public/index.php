<?php
// Activa la comprobación estricta de tipos para el punto de entrada público.
declare(strict_types=1); // Evita conversiones inesperadas en las rutas y datos de sesión.

// Define las opciones de la cookie antes de iniciar la sesión para limitar su exposición.
session_set_cookie_params([ // Agrupa las opciones de seguridad de la cookie de sesión.
    'lifetime' => 0, // Mantiene la cookie solo durante la sesión actual del navegador.
    'path' => '/', // Permite que el sistema reciba la cookie en todas sus rutas.
    'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off', // Envía la cookie únicamente por HTTPS cuando el sitio usa TLS.
    'httponly' => true, // Impide que JavaScript del navegador lea la cookie de sesión.
    'samesite' => 'Lax', // Reduce el envío de la cookie en solicitudes externas no confiables.
]); // Finaliza la configuración de la cookie de sesión.

// Inicia la sesión solo cuando PHP todavía no haya iniciado una sesión activa.
if (session_status() !== PHP_SESSION_ACTIVE) { // Evita avisos por inicializar la sesión más de una vez.
    session_start(); // Habilita el almacenamiento de la autenticación entre solicitudes.
} // Finaliza la inicialización condicional de la sesión.

// Obtiene el controlador solicitado para reconocer enlaces administrativos legibles.
$controladorSolicitado = isset($_GET['c']) ? (string) $_GET['c'] : ''; // Usa un valor vacío cuando la URL no incluye controlador.
// Obtiene la acción solicitada dentro del controlador indicado.
$accionSolicitada = isset($_GET['a']) ? (string) $_GET['a'] : ''; // Evita depender de parámetros ausentes.
// Define solo las combinaciones permitidas de controlador y acción en la URL.
$rutasPorControlador = [ // Evita despachar clases o métodos arbitrarios desde parámetros HTTP.
    'empleado' => ['lista' => 'empleados-lista', 'registro' => 'empleados-registro', 'perfil' => 'empleados-perfil'], // Mantiene disponibles las acciones de empleados y perfil.
    'auth' => ['logout' => 'cerrar-sesion'], // Convierte el enlace de salida en una acción interna permitida.
]; // Finaliza el mapa permitido de alias MVC.
// Resuelve los alias c=...&a=... o usa la ruta accion existente como alternativa.
$accion = isset($rutasPorControlador[$controladorSolicitado][$accionSolicitada]) ? $rutasPorControlador[$controladorSolicitado][$accionSolicitada] : (isset($_GET['accion']) ? (string) $_GET['accion'] : 'login'); // Conserva compatibilidad y rechaza combinaciones no incluidas.
// Obtiene la identidad autenticada de la sesión o null para visitantes sin autenticar.
$usuarioSesion = $_SESSION['user'] ?? null; // Centraliza la identidad de la sesión definida por AuthController.
// Obtiene la bandera de cambio obligatorio de contraseña desde el estado de sesión.
$requiereCambioTemporal = (bool) ($_SESSION['requiereCambioTemporal'] ?? false); // Evita depender de parámetros controlables por la URL.

// Atiende el cierre de sesión antes de cualquier redirección obligatoria de contraseña temporal.
if ($accion === 'cerrar-sesion') { // Permite que cualquier cuenta finalice una sesión activa.
    // Vacía todas las variables de sesión antes de destruir su identificador.
    $_SESSION = []; // Elimina la identidad y las banderas de flujo almacenadas.
    // Comprueba si PHP mantiene una cookie de sesión para invalidarla también en el navegador.
    if (ini_get('session.use_cookies')) { // Evita emitir una cookie si la sesión no usa cookies.
        // Recupera los atributos de seguridad actuales de la cookie de sesión.
        $parametrosCookie = session_get_cookie_params(); // Reutiliza ruta, dominio, Secure, HttpOnly y SameSite vigentes.
        // Expira la cookie con los mismos atributos con que fue creada.
        setcookie(session_name(), '', [ // Invalida en el navegador el identificador de sesión previo.
            'expires' => time() - 42000, // Sitúa la caducidad en el pasado para eliminar la cookie.
            'path' => $parametrosCookie['path'], // Conserva la ruta original de la cookie.
            'domain' => $parametrosCookie['domain'], // Conserva el dominio original de la cookie.
            'secure' => $parametrosCookie['secure'], // Conserva el requisito HTTPS original.
            'httponly' => $parametrosCookie['httponly'], // Conserva el acceso restringido a HTTP.
            'samesite' => $parametrosCookie['samesite'] ?? 'Lax', // Conserva la política entre sitios o aplica el valor predeterminado seguro.
        ]); // Finaliza la expiración de la cookie de sesión.
    } // Finaliza la comprobación y eliminación de la cookie.
    // Destruye en el servidor los datos asociados a la sesión actual.
    session_destroy(); // Impide reutilizar una identidad que ya cerró su sesión.
    // Devuelve al visitante a la pantalla de acceso con una redirección posterior a GET.
    header('Location: ?accion=login', true, 303); // Evita volver al panel con el historial de navegación.
    // Detiene el procesamiento después de cerrar la sesión.
    exit; // Impide ejecutar otras rutas con una sesión destruida.
} // Finaliza el cierre seguro de sesión.

// Define las rutas internas disponibles para cada rol del ENUM documentado.
$rutasPorRol = [ // Usa destinos fijos para evitar redirecciones externas controladas por el usuario.
    'ADMINISTRADOR' => 'panel-administrador', // Asocia el rol administrador con su ruta interna.
    'VENTAS' => 'panel-ventas', // Asocia el rol de ventas con su ruta interna.
    'ALMACEN' => 'panel-almacen', // Asocia el rol de almacén con su ruta interna.
    'DISTRIBUCION' => 'panel-distribucion', // Asocia el rol de distribución con su ruta interna.
    'REPARTIDOR' => 'panel-repartidor', // Asocia el rol de repartidor con su ruta interna.
]; // Finaliza el mapeo de rutas permitidas por rol.

// Obliga a cualquier cuenta temporal a permanecer en el flujo de cambio de contraseña.
if (is_array($usuarioSesion) && $requiereCambioTemporal && $accion !== 'cambiar-temporal') { // Intercepta rutas normales durante el cambio obligatorio.
    header('Location: ?accion=cambiar-temporal', true, 303); // Redirige internamente a la pantalla obligatoria.
    exit; // Detiene el enrutamiento para impedir el acceso a otras vistas.
} // Finaliza el control global de contraseñas temporales.

// Bloquea el acceso a la pantalla temporal cuando no hay una sesión autenticada.
if ($accion === 'cambiar-temporal' && !is_array($usuarioSesion)) { // Impide cambiar credenciales sin una identidad validada.
    header('Location: ?accion=login', true, 303); // Devuelve al usuario sin sesión al formulario de acceso.
    exit; // Detiene el enrutamiento tras la redirección.
} // Finaliza la protección de la ruta temporal para visitantes.

// Redirige las sesiones ya autenticadas desde el formulario a su destino interno por rol.
if ($accion === 'login' && is_array($usuarioSesion)) { // Evita mostrar el formulario de acceso a una sesión vigente.
    $rolSesion = (string) ($usuarioSesion['rol'] ?? ''); // Obtiene únicamente el rol guardado tras verificar credenciales.
    $destinoSesion = $rutasPorRol[$rolSesion] ?? 'login'; // Selecciona una ruta interna o el acceso si el rol no es válido.
    header('Location: ?accion=' . rawurlencode($destinoSesion), true, 303); // Evita incluir contenido del usuario en la ruta.
    exit; // Detiene la ejecución tras redirigir al usuario autenticado.
} // Finaliza la redirección de sesiones autenticadas.

// Reconoce destinos por rol y comprueba que la sesión corresponda a ese rol.
if (in_array($accion, $rutasPorRol, true)) { // Restringe esta sección a los destinos listados explícitamente.
    $rolDestino = array_search($accion, $rutasPorRol, true); // Recupera el rol requerido para la ruta seleccionada.
    if (!is_array($usuarioSesion) || (string) ($usuarioSesion['rol'] ?? '') !== $rolDestino) { // Rechaza visitantes y sesiones con otro rol.
        header('Location: ?accion=login', true, 303); // Envía la solicitud no autorizada al formulario.
        exit; // Impide renderizar contenido de una ruta ajena.
    } // Finaliza la autorización de la ruta de panel.

    // Prepara los datos de una pantalla de sesión mientras se implementan los módulos de cada rol.
    $modoSesionIniciada = true; // Indica a la vista de acceso que muestre el estado autenticado.
    $tituloPanel = 'Acceso autorizado: ' . (string) $rolDestino; // Presenta el rol validado sin permitir que controle HTML.
    $usuarioAutenticado = (string) ($usuarioSesion['usuario'] ?? ''); // Lee el nombre guardado para mostrarlo de forma escapada.
    $mensaje = (string) ($_SESSION['mensajeAutenticacion'] ?? 'Inicio de sesión correcto.'); // Consume o usa el mensaje de estado de la autenticación.
    $tipoAlerta = 'success'; // Usa el estilo de éxito de Bootstrap para confirmar el acceso.
    unset($_SESSION['mensajeAutenticacion']); // Evita que el mismo mensaje reaparezca en solicitudes posteriores.
    require dirname(__DIR__) . '/app/views/auth/login.php'; // Renderiza la vista común como confirmación de la sesión.
    exit; // Termina el enrutamiento de la ruta de sesión.
} // Finaliza la comprobación de rutas internas por rol.

// Carga el modelo, el controlador y la conexión solo para las acciones de autenticación.
try { // Permite presentar errores controlados si falla el SGBD o la configuración.
    require_once dirname(__DIR__) . '/app/models/Empleado.php'; // Carga el modelo de empleado usado por el flujo de acceso.
    $conexion = require dirname(__DIR__) . '/config/database.php'; // Obtiene la conexión PDO configurada por el proyecto.
    require_once dirname(__DIR__) . '/app/controllers/AuthController.php'; // Carga el controlador después de disponer de sus dependencias.
    $controladorAuth = new AuthController(new Empleado($conexion)); // Inyecta el modelo con la conexión PDO única.

    // Distribuye las solicitudes recibidas entre autenticación y lista administrativa.
    switch ($accion) { // Selecciona el flujo según la acción permitida.
        case 'login': // Atiende el formulario de inicio de sesión.
            if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') { // Procesa credenciales únicamente cuando se envía el formulario.
                $controladorAuth->procesarInicioSesion($_POST); // Delega la validación al controlador de autenticación.
            } else { // Presenta el formulario ante solicitudes de lectura.
                $controladorAuth->mostrarLogin(); // Renderiza la vista del formulario de acceso.
            } // Finaliza la selección del método HTTP para el acceso.
            break; // Impide que la ejecución continúe en otra acción.

        case 'empleados-lista': // Atiende la pantalla protegida de lista de empleados.
            require_once dirname(__DIR__) . '/app/controllers/EmpleadoController.php'; // Carga el controlador que verifica la sesión y el rol administrador.
            $controladorEmpleado = new EmpleadoController(new Empleado($conexion)); // Inyecta el modelo y la conexión PDO ya preparada.
            $controladorEmpleado->lista(); // Obtiene los datos con listarTodos y renderiza la vista de empleados.
            break; // Finaliza el enrutamiento de la pantalla de usuarios.

        case 'empleados-registro': // Atiende el formulario protegido de alta de empleados.
            require_once dirname(__DIR__) . '/app/controllers/EmpleadoController.php'; // Carga el controlador con restricción para administradores.
            $controladorEmpleado = new EmpleadoController(new Empleado($conexion)); // Comparte el modelo construido con la conexión PDO actual.
            $controladorEmpleado->registro(); // Procesa el POST o presenta el formulario de registro.
            break; // Finaliza el enrutamiento del alta de empleados.

        case 'perfil': // Permite invocar el perfil mediante el alias directo de acción.
        case 'empleados-perfil': // Atiende la visualización y edición del perfil de cualquier empleado autenticado.
            require_once dirname(__DIR__) . '/app/controllers/EmpleadoController.php'; // Carga el controlador de empleados.
            $controladorEmpleado = new EmpleadoController(new Empleado($conexion)); // Inyecta el modelo y la conexión PDO preparada.
            $controladorEmpleado->perfil(); // Carga los datos actuales del usuario activo o procesa la actualización.
            break; // Finaliza el enrutamiento del perfil de empleado.

        case 'cambiar-temporal': // Atiende la pantalla obligatoria para renovar una contraseña temporal.
            if ($requiereCambioTemporal !== true) { // Impide cambiar una contraseña que no esté marcada temporal.
                $rolSesion = (string) ($usuarioSesion['rol'] ?? ''); // Recupera el rol de la cuenta autenticada.
                $destinoSesion = $rutasPorRol[$rolSesion] ?? 'login'; // Elige la ruta interna permitida para su rol.
                header('Location: ?accion=' . rawurlencode($destinoSesion), true, 303); // Devuelve la cuenta no temporal a su destino.
                exit; // Detiene el procesamiento después de la redirección.
            } // Finaliza la protección contra cambios no requeridos.
            if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') { // Procesa la contraseña solo tras el envío del formulario.
                $controladorAuth->procesarCambioTemporal($_POST); // Delega la comparación y persistencia al controlador.
            } else { // Presenta la pantalla obligatoria ante solicitudes de lectura.
                $controladorAuth->mostrarCambioTemporal(); // Renderiza la vista de actualización de contraseña.
            } // Finaliza la selección del método HTTP para el cambio temporal.
            break; // Impide que la ejecución continúe en otra acción.

        default: // Rechaza cualquier acción no incluida en esta sección del proyecto.
            http_response_code(404); // Informa que la ruta solicitada no está implementada.
            $controladorAuth->mostrarLogin('La ruta solicitada no está disponible.', 'danger'); // Presenta un mensaje controlado en la vista de acceso.
            break; // Finaliza el enrutamiento de acciones no reconocidas.
    } // Finaliza la distribución de rutas permitidas.
} catch (Throwable $excepcion) { // Captura fallos técnicos sin exponer consultas ni credenciales.
    error_log('Falló el procesamiento de una solicitud del módulo de autenticación.'); // Registra un mensaje genérico para diagnóstico del servidor.
    http_response_code(503); // Comunica que el servicio no puede atender la solicitud actualmente.
    $mensaje = 'No fue posible conectar con el sistema. Inténtalo nuevamente más tarde.'; // Prepara una alerta sin detalles internos.
    $tipoAlerta = 'danger'; // Solicita a la vista representar el error con el estilo de Bootstrap.
    if ($requiereCambioTemporal && is_array($usuarioSesion)) { // Conserva la pantalla obligatoria si la sesión ya necesita renovarse.
        require dirname(__DIR__) . '/app/views/auth/cambiar_temporal.php'; // Presenta el error en el flujo temporal autenticado.
    } else { // Usa el acceso como pantalla segura para los demás casos de error.
        require dirname(__DIR__) . '/app/views/auth/login.php'; // Muestra el error del servicio sin revelar información de conexión.
    } // Finaliza la selección segura de la vista de error.
} // Finaliza el manejo de fallos de configuración o autenticación.
