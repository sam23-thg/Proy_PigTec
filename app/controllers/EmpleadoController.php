<?php
// Activa la comprobación estricta de tipos para los métodos del controlador.
declare(strict_types=1); // Evita conversiones implícitas en las operaciones futuras de empleados.

// Carga las reglas comunes que no deben depender de la validación del navegador.
require_once dirname(__DIR__) . '/helpers/ValidacionFormulario.php'; // Comparte límites y patrones en todas las acciones de empleado.

// Declara el controlador encargado de coordinar las operaciones de gestión de empleados.
class EmpleadoController // Mantiene juntas las acciones protegidas del rol administrador.
{ // Inicia la definición de la clase EmpleadoController.
    // Conserva el modelo que ejecuta las consultas preparadas de empleados.
    private Empleado $empleado; // Reutiliza la capa de datos inyectada desde el Front Controller.

    // Comprueba la sesión activa de cualquier empleado antes de permitir construir el controlador.
    public function __construct(Empleado $empleado) // Recibe el modelo después de que el enrutador prepara PDO.
    { // Inicia la verificación de acceso al controlador.
        // Rechaza la solicitud si no hay sesión activa o falta la identidad válida del empleado.
        if (session_status() !== PHP_SESSION_ACTIVE || !isset($_SESSION['user']) || !is_array($_SESSION['user'])) { // Evita peticiones de usuarios sin sesión autenticada.
            // Establece el código HTTP que identifica una solicitud sin autorización.
            http_response_code(403); // Comunica la denegación mediante el estado estándar de acceso prohibido.
            // Finaliza inmediatamente con un mensaje controlado sin ejecutar acciones del controlador.
            exit('Acceso denegado: se requiere una sesión activa.'); // Impide continuar sin autorización.
        } // Finaliza la comprobación estricta de sesión activa.
        // Guarda el modelo únicamente después de verificar la sesión del usuario.
        $this->empleado = $empleado; // Deja disponible la capa de datos para las acciones permitidas.
    } // Finaliza el constructor del controlador.

    // Comprueba que el usuario autenticado cuente exclusivamente con el rol de administrador.
    private function verificarRolAdministrador(): void // Protege las acciones del bloque administrativo de personal.
    { // Inicia la validación del rol administrativo.
        // Rechaza el acceso si el rol de la sesión activa no es ADMINISTRADOR exacto.
        if (($_SESSION['user']['rol'] ?? null) !== 'ADMINISTRADOR') { // Evita que empleados comunes ejecuten operaciones administrativas.
            // Establece el código HTTP 403 de acceso prohibido.
            http_response_code(403); // Comunica la denegación mediante código de respuesta estándar.
            // Finaliza inmediatamente con el mensaje controlado de restricción administrativa.
            exit('Acceso denegado: se requiere una sesión activa con rol ADMINISTRADOR.'); // Impide continuar sin privilegios de administrador.
        } // Finaliza la comprobación del rol administrador.
    } // Finaliza el método de protección administrativa.

    // Reserva la acción principal del controlador para su implementación posterior.
    public function index(): void // Define el punto de entrada para las funciones de administración de empleados.
    { // Inicia la acción principal todavía sin lógica, según el alcance solicitado.
        // Mantiene este método vacío hasta desarrollar su tarea correspondiente.
    } // Finaliza la acción principal del controlador.

    // Procesa altas administrativas y prepara los datos que necesita el formulario.
    public function registro(): void // Define el punto de entrada para registrar personal.
    { // Inicia el procesamiento del formulario de alta.
        // Comprueba que la sesión activa posea facultades de rol ADMINISTRADOR.
        $this->verificarRolAdministrador(); // Restringe el formulario de alta exclusivamente al administrador.
        // Define los campos del formulario que podrán conservarse tras una validación fallida.
        $datosFormulario = ['nombre' => '', 'seg_nombre' => '', 'apellido_p' => '', 'apellido_m' => '', 'usuario' => '', 'curp' => '', 'telefono' => '', 'genero' => '', 'rol' => '']; // No incluye ni repuebla la contraseña enviada.
        // Inicializa el mensaje de la vista sin presentar una alerta al cargar el formulario.
        $mensaje = ''; // Permite mostrar errores o confirmaciones con el componente Bootstrap.
        // Inicializa la variante visual de alerta para los errores del formulario.
        $tipoAlerta = 'danger'; // Usa rojo para fallos de validación y duplicados.
        // Inicializa la selección de permisos que se devolverá a la vista después de un error.
        $permisosFormulario = []; // Conserva únicamente claves permitidas por la lista estática.
        // Define los permisos que puede mostrar el formulario según el ENUM real de rol.
        $permisosPorRol = [ // Reutiliza las capacidades estáticas existentes en el inicio de sesión.
            'ADMINISTRADOR' => ['gestionar_empleados', 'restablecer_password', 'ver_catalogo', 'editar_productos'], // Enumera las capacidades administrativas.
            'VENTAS' => ['ver_catalogo'], // Limita ventas a la consulta del catálogo.
            'ALMACEN' => ['ver_catalogo', 'editar_productos'], // Permite a almacén consultar y editar productos.
            'DISTRIBUCION' => ['ver_catalogo'], // Asigna la consulta básica a distribución.
            'REPARTIDOR' => ['ver_catalogo'], // Asigna la consulta básica a reparto.
        ]; // Finaliza la configuración estática de permisos por rol.
        // Comprueba el método HTTP para procesar solo envíos POST del formulario.
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') { // Evita insertar empleados al consultar la página.
            // Recupera los datos obligatorios como texto y quita espacios externos de campos no secretos.
            $datosFormulario['nombre'] = trim(ValidacionFormulario::texto($_POST['nombre'] ?? null)); // Valida el nombre sin conservar espacios periféricos.
            // Recupera el apellido paterno obligatorio desde el formulario.
            $datosFormulario['apellido_p'] = trim(ValidacionFormulario::texto($_POST['apellido_p'] ?? null)); // Normaliza el dato antes de comprobarlo.
            // Recupera el usuario obligatorio usado para iniciar sesión.
            $datosFormulario['usuario'] = trim(ValidacionFormulario::texto($_POST['usuario'] ?? null)); // Evita espacios externos en el identificador.
            // Recupera el CURP obligatorio para validar su unicidad.
            $datosFormulario['curp'] = strtoupper(trim(ValidacionFormulario::texto($_POST['curp'] ?? null))); // Normaliza el CURP para compararlo con el registro existente.
            // Recupera el teléfono obligatorio como texto para conservar ceros iniciales.
            $datosFormulario['telefono'] = trim(ValidacionFormulario::texto($_POST['telefono'] ?? null)); // Evita convertir el teléfono a un tipo numérico.
            // Conserva exactamente la contraseña escrita para aplicar password_hash sin alterarla.
            $contrasenia = ValidacionFormulario::texto($_POST['contrasenia'] ?? null); // No recorta espacios que puedan formar parte de la clave.
            // Recupera el rol seleccionado para comprobarlo contra el ENUM admitido.
            $datosFormulario['rol'] = trim(ValidacionFormulario::texto($_POST['rol'] ?? null)); // No confía en las opciones enviadas por el navegador.
            // Recupera los campos opcionales y los normaliza como cadenas.
            $datosFormulario['seg_nombre'] = trim(ValidacionFormulario::texto($_POST['seg_nombre'] ?? null)); // Permite dejar vacío el segundo nombre.
            // Recupera el apellido materno opcional.
            $datosFormulario['apellido_m'] = trim(ValidacionFormulario::texto($_POST['apellido_m'] ?? null)); // Permite dejar vacío el apellido materno.
            // Recupera el género opcional.
            $datosFormulario['genero'] = trim(ValidacionFormulario::texto($_POST['genero'] ?? null)); // Permite dejar el género sin seleccionar.
            // Acepta permisos solo si el navegador envió una lista y descarta valores de otro formato.
            $permisosEnviados = isset($_POST['permisos']) && is_array($_POST['permisos']) ? $_POST['permisos'] : []; // Evita operar sobre datos POST con estructura inesperada.
            // Descarta elementos que no sean cadenas antes de comparar las claves de permisos.
            $permisosEnviados = array_filter($permisosEnviados, 'is_string'); // Evita errores por valores anidados manipulados en la solicitud.
            // Comprueba en el servidor que todos los campos requeridos estén presentes.
            $camposObligatoriosCompletos = $datosFormulario['nombre'] !== '' && $datosFormulario['apellido_p'] !== '' && $datosFormulario['usuario'] !== '' && $datosFormulario['curp'] !== '' && $datosFormulario['telefono'] !== '' && $datosFormulario['rol'] !== '' && $contrasenia !== ''; // No depende de la validación JavaScript del navegador.
            // Devuelve un error explícito si falta cualquiera de los datos obligatorios.
            if (!$camposObligatoriosCompletos) { // Detiene el flujo antes de consultar o insertar registros.
                $mensaje = 'Error: datos faltantes'; // Usa el mensaje requerido para campos obligatorios vacíos.
                $tipoAlerta = 'danger'; // Indica el problema con una alerta roja Bootstrap.
            } elseif (!ValidacionFormulario::nombrePersona($datosFormulario['nombre']) || !ValidacionFormulario::nombrePersona($datosFormulario['apellido_p']) || !ValidacionFormulario::nombrePersona($datosFormulario['seg_nombre'], false) || !ValidacionFormulario::nombrePersona($datosFormulario['apellido_m'], false)) { // Rechaza dígitos, símbolos no permitidos y textos demasiado largos en nombres.
                $mensaje = 'Los nombres solo pueden contener letras, espacios, apóstrofos, puntos o guiones y tener hasta 100 caracteres.'; // Explica las reglas de los campos personales.
                $tipoAlerta = 'danger'; // Presenta el error antes de consultar duplicados.
            } elseif (!preg_match('/^[A-Za-z0-9._-]{3,50}$/D', $datosFormulario['usuario'])) { // Acepta solo caracteres previstos para el identificador de acceso.
                $mensaje = 'El usuario debe tener entre 3 y 50 caracteres: letras, números, punto, guion o guion bajo.'; // Informa la longitud y caracteres permitidos.
                $tipoAlerta = 'danger'; // Evita procesar un identificador inválido.
            } elseif (!ValidacionFormulario::curp($datosFormulario['curp'])) { // Requiere una CURP de 18 caracteres con formato correcto.
                $mensaje = 'La CURP debe tener exactamente 18 caracteres y un formato válido.'; // Explica la regla de longitud y estructura.
                $tipoAlerta = 'danger'; // Detiene el alta antes de consultar duplicados.
            } elseif (!ValidacionFormulario::telefono($datosFormulario['telefono'])) { // Restringe el teléfono a dígitos y longitud admitida.
                $mensaje = 'El teléfono debe contener entre 10 y 15 dígitos, sin espacios ni signos.'; // Informa la entrada válida.
                $tipoAlerta = 'danger'; // Evita guardar valores no telefónicos.
            } elseif (strlen($contrasenia) < 8 || strlen($contrasenia) > 72) { // Impide claves débiles o que BCRYPT truncaría.
                $mensaje = 'La contraseña debe tener entre 8 y 72 bytes.'; // Informa el límite compatible con BCRYPT.
                $tipoAlerta = 'danger'; // Evita guardar una clave insegura o truncada.
            } elseif ($datosFormulario['genero'] !== '' && !in_array($datosFormulario['genero'], ['Masculino', 'Femenino', 'Otro'], true)) { // Acepta solo las opciones previstas en el perfil.
                $mensaje = 'La opción de género seleccionada no es válida.'; // Rechaza valores manipulados.
                $tipoAlerta = 'danger'; // Protege el catálogo de género.
            // Comprueba que el rol enviado corresponda exactamente a una opción del ENUM de empleado.
            } elseif (!array_key_exists($datosFormulario['rol'], $permisosPorRol)) { // Rechaza valores inventados o alterados en la petición.
                $mensaje = 'El rol seleccionado no es válido.'; // Informa el error sin exponer detalles de la base de datos.
                $tipoAlerta = 'danger'; // Usa el estilo rojo de error.
            } else { // Continúa a la comprobación de duplicados cuando los campos y el rol son válidos.
                // Conserva solo permisos reconocidos que estén autorizados para el rol seleccionado.
                $permisosFormulario = array_values(array_intersect($permisosPorRol[$datosFormulario['rol']], $permisosEnviados)); // Descarta permisos desconocidos o ajenos al rol.
                // Busca duplicados y registra la cuenta dentro del manejo de errores de PDO.
                try { // Captura colisiones de unicidad o fallos de escritura sin mostrar excepciones técnicas.
                    // Busca el usuario mediante la consulta parametrizada ya disponible en el modelo.
                    $usuarioDuplicado = $this->empleado->buscarPorUsuario($datosFormulario['usuario']) !== null; // Detecta un identificador de acceso ya registrado.
                    // Solo consulta el CURP si el nombre de usuario todavía no está duplicado.
                    $curpDuplicado = !$usuarioDuplicado && $this->empleado->buscarPorCurp($datosFormulario['curp']); // Evita una consulta innecesaria tras detectar duplicación de usuario.
                    // Rechaza el alta si el nombre de usuario ya pertenece a otra cuenta.
                    if ($usuarioDuplicado) { // Conserva la restricción de unicidad del campo usuario.
                        $mensaje = 'El usuario ya está registrado.'; // Informa claramente la colisión del usuario.
                        $tipoAlerta = 'danger'; // Muestra el duplicado en rojo según la guía visual.
                    // Rechaza el alta si el CURP ya pertenece a otra cuenta.
                    } elseif ($curpDuplicado) { // Conserva la restricción de unicidad del CURP.
                        $mensaje = 'La CURP ya está registrada.'; // Informa claramente la colisión del CURP.
                        $tipoAlerta = 'danger'; // Muestra el duplicado en rojo según la guía visual.
                    } else { // Inserta solo cuando las dos verificaciones de unicidad no encuentran coincidencias.
                        // Genera el hash BCRYPT en el controlador antes de persistir la contraseña.
                        $hashContrasenia = password_hash($contrasenia, PASSWORD_BCRYPT); // Evita guardar la clave en texto plano.
                        // Evita el alta si el algoritmo no pudo producir un hash válido.
                        if ($hashContrasenia === false) { // Comprueba el resultado de password_hash antes de escribir.
                            $mensaje = 'Error de registro'; // Devuelve el mensaje seguro para fallos de alta.
                            $tipoAlerta = 'danger'; // Presenta el problema en una alerta roja.
                        } else { // Continúa con la inserción usando el hash calculado.
                            // Añade el hash en el campo que el modelo insertará en la columna contrasenia.
                            $datosRegistro = $datosFormulario; // Conserva los demás valores ya validados.
                            // Reemplaza la clave recibida por su hash seguro antes de llamar al modelo.
                            $datosRegistro['contrasenia'] = $hashContrasenia; // Garantiza que la capa PDO nunca reciba texto plano.
                            // Inserta el empleado con una sentencia preparada dentro del modelo.
                            $registroCorrecto = $this->empleado->registrar($datosRegistro); // Mantiene la persistencia fuera del controlador.
                            // Prepara el estado visible según el resultado devuelto por PDO.
                            if ($registroCorrecto) { // Confirma la creación cuando el INSERT terminó correctamente.
                                $mensaje = 'Empleado registrado correctamente.'; // Muestra una confirmación clara al administrador.
                                $tipoAlerta = 'success'; // Usa una alerta verde de Bootstrap para el alta exitosa.
                                $datosFormulario = ['nombre' => '', 'seg_nombre' => '', 'apellido_p' => '', 'apellido_m' => '', 'usuario' => '', 'curp' => '', 'telefono' => '', 'genero' => '', 'rol' => '']; // Limpia los valores del formulario después del alta.
                                $permisosFormulario = []; // Limpia las casillas marcadas después de guardar.
                            } else { // Maneja un INSERT que no haya podido completarse.
                                $mensaje = 'Error de registro'; // Evita mostrar información interna del motor.
                                $tipoAlerta = 'danger'; // Informa el fallo mediante la alerta roja solicitada.
                            } // Finaliza la interpretación del resultado de inserción.
                        } // Finaliza la comprobación del hash BCRYPT.
                    } // Finaliza la selección entre duplicado e inserción.
                } catch (PDOException $excepcion) { // Captura errores PDO y posibles colisiones concurrentes de índices únicos.
                    error_log('Falló el registro de un empleado desde el formulario administrativo.'); // Registra un detalle genérico sin credenciales ni datos personales.
                    $mensaje = $excepcion->getCode() === '23000' ? 'El usuario o la CURP ya están registrados.' : 'Error de registro'; // Traduce una restricción única a una alerta entendible.
                    $tipoAlerta = 'danger'; // Presenta cualquier error de persistencia como alerta roja.
                } // Finaliza el manejo controlado de errores de almacenamiento.
            } // Finaliza la validación de datos y el procesamiento POST.
        } // Finaliza el procesamiento condicional del formulario.
        // Impide que el navegador repueble el campo secreto después de un error de validación.
        $datosFormulario['contrasenia'] = ''; // Nunca devuelve la contraseña original ni su hash a la vista.
        // Recupera el listado compacto para mostrarlo junto al formulario de alta.
        $usuariosRegistrados = $this->empleado->listarTodos(); // Usa el método PDO preparado definido para la tabla de empleados.
        // Conserva la identidad validada para presentar el encabezado de sesión del menú.
        $usuarioSesion = $_SESSION['user']; // La sesión y el rol administrador fueron validados al construir el controlador.
        // Renderiza la vista de registro con los datos validados y las alertas actuales.
        require dirname(__DIR__) . '/views/empleados/registro.php'; // Mantiene el marcado HTML separado de la lógica de negocio.
    } // Finaliza la acción de registro de empleados.

    // Recupera los empleados y notificaciones para la pantalla administrativa.
    public function lista(): void // Define el punto de entrada para listar y buscar personal.
    { // Inicia la acción de listado de empleados autorizada.
        // Comprueba que la sesión activa cuente con el rol ADMINISTRADOR.
        $this->verificarRolAdministrador(); // Protege la lista para uso exclusivo de administradores.
        // Inicializa un token de sesión para proteger los formularios de restablecimiento.
        if (!isset($_SESSION['tokenRestablecimiento']) || !is_string($_SESSION['tokenRestablecimiento'])) { // Reutiliza el token durante la navegación actual.
            $_SESSION['tokenRestablecimiento'] = bin2hex(random_bytes(32)); // Genera un valor impredecible y lo conserva en la sesión.
        } // Finaliza la inicialización del token administrativo.
        // Inicializa un token independiente para editar datos y cambiar estados de cuenta.
        if (!isset($_SESSION['tokenAdministracionEmpleado']) || !is_string($_SESSION['tokenAdministracionEmpleado'])) { // Reutiliza el token mientras dure la sesión.
            $_SESSION['tokenAdministracionEmpleado'] = bin2hex(random_bytes(32)); // Genera un valor impredecible para proteger los formularios administrativos.
        } // Finaliza la inicialización del token de administración de empleados.
        // Obtiene los datos de la tabla empleado a través del método PDO preparado del modelo.
        $usuarios = $this->empleado->listarTodos(); // No accede directamente a la base de datos desde el controlador.
        // Recupera un único aviso después de que la consulta de la lista se completó correctamente.
        $avisoAdministrativo = $_SESSION['avisoAdministrativo'] ?? null; // Conserva el aviso si la consulta lanza una excepción.
        // Consume el aviso para que la contraseña temporal no vuelva a presentarse al recargar.
        unset($_SESSION['avisoAdministrativo']); // Elimina el contenido temporal después de recuperarlo.
        // Carga el usuario administrador para mostrar el encabezado de sesión de la vista.
        $usuarioSesion = $_SESSION['user']; // La identidad ya fue validada por el constructor.
        // Renderiza la vista de lista usando la ruta MVC correspondiente.
        require dirname(__DIR__) . '/views/empleados/lista.php'; // Entrega la colección consultada al archivo de presentación.
    } // Finaliza la acción de listado de empleados.

    // Restablece la contraseña de una cuenta desde una solicitud administrativa protegida.
    public function restablecer(): void // Define el punto de entrada para el restablecimiento administrativo.
    { // Inicia la acción de restablecimiento administrativo.
        // Comprueba que la sesión activa cuente con el rol ADMINISTRADOR.
        $this->verificarRolAdministrador(); // Protege el restablecimiento para uso exclusivo de administradores.
        // Rechaza solicitudes que no provengan del envío del formulario.
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') { // Evita modificar contraseñas mediante enlaces GET.
            http_response_code(405); // Informa que la ruta requiere una petición POST.
            exit('Método no permitido.'); // Detiene el proceso sin alterar ninguna cuenta.
        } // Finaliza la validación del método HTTP.
        // Recupera el token enviado para verificar el origen de la solicitud.
        $tokenEnviado = ValidacionFormulario::texto($_POST['token_restauracion'] ?? null); // Rechaza arreglos o valores de tipo inesperado.
        // Exige coincidencia entre el token del formulario y el de la sesión administrativa.
        if (!isset($_SESSION['tokenRestablecimiento']) || !hash_equals((string) $_SESSION['tokenRestablecimiento'], $tokenEnviado)) { // Mitiga solicitudes de otros sitios.
            http_response_code(403); // Informa que la solicitud no está autorizada.
            exit('La solicitud no es válida. Actualiza la lista e inténtalo nuevamente.'); // No procesa un token inválido.
        } // Finaliza la validación del token de formulario.
        // Comprueba que el identificador recibido sea un entero positivo.
        $idEmpleado = filter_var($_POST['id_empleado'] ?? null, FILTER_VALIDATE_INT); // Evita identificadores ambiguos.
        // Busca el empleado solo cuando el identificador cumple el formato esperado.
        $empleadoObjetivo = is_int($idEmpleado) && $idEmpleado > 0 ? $this->empleado->buscarPorId($idEmpleado) : null; // Usa el método parametrizado del modelo.
        // Prepara un mensaje genérico para los casos en los que el proceso no se complete.
        $avisoAdministrativo = ['tipo' => 'danger', 'mensaje' => 'No fue posible restablecer la contraseña.', 'usuario' => '', 'contraseniaTemporal' => '']; // No incluye datos sensibles en errores.
        // Evita cambios para identificadores inválidos o cuentas inexistentes.
        if ($empleadoObjetivo === null) { // No se debe modificar un registro que no fue encontrado.
            $avisoAdministrativo['mensaje'] = 'No se encontró el usuario seleccionado.'; // Indica el motivo de forma segura.
        // Impide invalidar la sesión del administrador que ejecuta esta operación.
        } elseif ($idEmpleado === (int) ($_SESSION['user']['id_empleado'] ?? 0)) { // El administrador no se restablece desde la lista.
            $avisoAdministrativo['mensaje'] = 'No puedes restablecer tu propia contraseña desde esta pantalla.'; // Conserva disponible la sesión activa.
        } else { // Genera una contraseña temporal únicamente para una cuenta existente y distinta.
            try { // Controla fallos de generación aleatoria, hash o base de datos.
                // Genera una clave temporal aleatoria y fácil de copiar, distinta para cada operación.
                $contraseniaTemporal = rtrim(strtr(base64_encode(random_bytes(18)), '+/', '-_'), '='); // No reutiliza una contraseña compartida.
                // Calcula el hash BCRYPT que se guardará en la base de datos.
                $hashContrasenia = password_hash($contraseniaTemporal, PASSWORD_BCRYPT); // Nunca almacena la clave temporal en texto plano.
                // Comprueba que el hash se haya generado antes de persistirlo.
                if ($hashContrasenia === false) { // Evita almacenar un resultado de hash inválido.
                    throw new RuntimeException('No fue posible generar el hash temporal.'); // Inicia el manejo de error controlado.
                } // Finaliza la comprobación del hash.
                // Guarda el hash con un marcador para exigir un cambio en el siguiente inicio de sesión.
                $actualizacionCorrecta = $this->empleado->cambiarPassword($idEmpleado, $hashContrasenia, true); // No necesita agregar columnas a la tabla.
                // Evita mostrar éxito si el modelo no completó la actualización.
                if (!$actualizacionCorrecta) { // Confirma que PDO aceptó la operación.
                    throw new RuntimeException('No se pudo actualizar la contraseña.'); // Activa el aviso seguro de error.
                } // Finaliza la comprobación de la actualización.
                // Prepara un mensaje de una sola lectura con la credencial destinada al empleado.
                $avisoAdministrativo = ['tipo' => 'success', 'mensaje' => 'Contraseña restablecida. Entrega esta clave temporal al usuario; deberá cambiarla al iniciar sesión.', 'usuario' => (string) $empleadoObjetivo['usuario'], 'contraseniaTemporal' => $contraseniaTemporal]; // La clave solo vive en el aviso flash de sesión.
            } catch (Throwable $excepcion) { // No revela excepciones técnicas ni contraseñas en la respuesta.
                error_log('Falló el restablecimiento administrativo de una contraseña.'); // Registra un mensaje que no contiene credenciales.
            } // Finaliza el manejo de errores del restablecimiento.
        } // Finaliza la selección del resultado administrativo.
        // Guarda el aviso para mostrarlo en la siguiente respuesta de la lista.
        $_SESSION['avisoAdministrativo'] = $avisoAdministrativo; // El aviso se consume una sola vez en lista().
        // Redirige mediante GET para que actualizar la página no repita el restablecimiento.
        header('Location: ?accion=empleados-lista', true, 303); // Aplica el patrón POST/Redirect/GET.
        exit; // Detiene la solicitud después de iniciar la redirección.
    } // Finaliza la acción de restablecimiento.

    // Muestra y actualiza la ficha administrativa sin modificar la lógica del perfil personal.
    public function ver(): void // Conserva la ruta anterior y añade edición de datos y estado.
    { // Inicia la consulta o actualización de la cuenta seleccionada.
        // Restringe la ficha y sus formularios al rol administrador.
        $this->verificarRolAdministrador(); // Reutiliza el control de acceso existente.
        // Inicializa un token independiente para proteger los formularios de esta ficha.
        if (!isset($_SESSION['tokenAdministracionEmpleado']) || !is_string($_SESSION['tokenAdministracionEmpleado'])) { // Permite abrir directamente la ficha.
            $_SESSION['tokenAdministracionEmpleado'] = bin2hex(random_bytes(32)); // Genera el secreto de validación en la sesión.
        } // Finaliza la preparación del token.
        // Determina si la solicitud representa una edición enviada desde la ficha o el estado de la lista.
        $esEnvio = ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'; // No permite mutaciones mediante enlaces GET.
        // Lee el identificador desde POST al modificar o desde GET al consultar la ficha.
        $idEmpleado = filter_var($esEnvio ? ($_POST['id_empleado'] ?? null) : ($_GET['id'] ?? null), FILTER_VALIDATE_INT); // Exige un entero recibido por el canal esperado.
        // Inicializa el mensaje de la ficha para errores de edición.
        $avisoDetalle = $_SESSION['avisoDetalle'] ?? null; // Recupera el resultado de un guardado anterior.
        // Consume el mensaje para evitar que reaparezca al recargar.
        unset($_SESSION['avisoDetalle']); // Mantiene la notificación de una sola lectura.
        // Inicializa los valores editables para conservar entradas ante un error.
        $datosEdicion = []; // Se completa con la fila actual o con el formulario enviado.
        // Consulta el registro únicamente si el identificador cumple la validación.
        $empleadoDetalle = is_int($idEmpleado) && $idEmpleado > 0 ? $this->empleado->buscarPorId($idEmpleado) : null; // La consulta está parametrizada en el modelo.
        // Devuelve un estado 404 cuando no existe el empleado solicitado.
        if ($empleadoDetalle === null) { // Evita representar datos de una cuenta inexistente.
            http_response_code(404); // Informa que no se encontró el recurso.
            exit('No se encontró el usuario solicitado.'); // Detiene el renderizado sin filtrar información técnica.
        } // Finaliza la validación de existencia.
        // Procesa únicamente las modificaciones enviadas mediante POST.
        if ($esEnvio) { // Separa las lecturas de las escrituras administrativas.
            // Recupera el token enviado para comprobar el origen del formulario.
            $tokenEnviado = ValidacionFormulario::texto($_POST['token_administracion'] ?? null); // Descarta valores que no sean texto.
            // Exige coincidencia exacta con el token de la sesión administrativa.
            if (!isset($_SESSION['tokenAdministracionEmpleado']) || !hash_equals((string) $_SESSION['tokenAdministracionEmpleado'], $tokenEnviado)) { // Mitiga envíos desde otros sitios.
                http_response_code(403); // Informa que la solicitud no está autorizada.
                exit('La solicitud no es válida. Actualiza la ficha e inténtalo nuevamente.'); // Detiene la modificación sin tocar la cuenta.
            } // Finaliza la comprobación del token.
            // Lee la operación permitida indicada por el formulario administrativo.
            $accionEmpleado = ValidacionFormulario::texto($_POST['accion_empleado'] ?? null); // Evita aceptar acciones arbitrarias.
            // Alterna el estado con una acción independiente de los demás datos del formulario.
            if ($accionEmpleado === 'cambiar_estado') { // Permite activar o desactivar sin requerir guardar los campos de perfil.
                // Calcula el estado opuesto usando el valor vigente leído de la base de datos.
                $estadoNuevo = (int) ($empleadoDetalle['estado'] ?? 1) === 1 ? 0 : 1; // No confía en un estado arbitrario enviado por el navegador.
                // Intenta actualizar exclusivamente la columna de estado mediante el modelo.
                try { // Mantiene los errores de persistencia fuera de la pantalla.
                    $estadoActualizado = $this->empleado->actualizarEstadoCuenta($idEmpleado, $estadoNuevo); // Ejecuta la acción aislada de activación o bloqueo.
                } catch (Throwable $excepcion) { // Captura fallos sin revelar datos técnicos.
                    error_log('Falló el cambio independiente del estado de una cuenta de empleado.'); // Registra un mensaje seguro para diagnóstico.
                    $estadoActualizado = false; // Prepara una respuesta controlada para la vista.
                } // Finaliza el manejo de errores del cambio de estado.
                // Guarda un aviso para mostrar el resultado después de redirigir.
                $_SESSION['avisoDetalle'] = ['tipo' => $estadoActualizado ? 'success' : 'danger', 'mensaje' => $estadoActualizado ? ($estadoNuevo === 1 ? 'Cuenta habilitada.' : 'Cuenta inhabilitada.') : 'No fue posible cambiar el estado de la cuenta.']; // Separa este resultado del guardado general.
                // Regresa a la ficha actualizada sin reenviar el formulario al recargar.
                header('Location: index.php?c=empleado&a=ver&id=' . $idEmpleado, true, 303); // Aplica el patrón POST/Redirect/GET.
                exit; // Detiene la solicitud tras completar la alternancia.
            } // Finaliza la acción independiente de estado.
            // Acepta únicamente el guardado de los demás campos administrativos.
            if ($accionEmpleado !== 'actualizar_datos') { // Rechaza operaciones que no pertenecen a esta ficha.
                // Informa que la solicitud no representa una acción válida de esta ficha.
                http_response_code(400); // Rechaza cualquier operación fuera de la lista permitida.
                // Detiene la solicitud antes de modificar cualquier columna.
                exit('La acción solicitada no es válida.'); // Detiene el flujo antes de modificar datos.
            } // Finaliza la validación de la acción de guardado.
            // Recupera todos los datos administrativos que se guardan juntos desde el formulario.
            $datosEdicion = [ // Conserva lo enviado para repoblar el formulario cuando haya errores.
                'nombre' => trim(ValidacionFormulario::texto($_POST['nombre'] ?? null)), // Normaliza el nombre obligatorio.
                'seg_nombre' => trim(ValidacionFormulario::texto($_POST['seg_nombre'] ?? null)), // Conserva el segundo nombre opcional.
                'apellido_p' => trim(ValidacionFormulario::texto($_POST['apellido_p'] ?? null)), // Normaliza el apellido paterno obligatorio.
                'apellido_m' => trim(ValidacionFormulario::texto($_POST['apellido_m'] ?? null)), // Conserva el apellido materno opcional.
                'telefono' => trim(ValidacionFormulario::texto($_POST['telefono'] ?? null)), // Conserva el teléfono como texto.
                'genero' => trim(ValidacionFormulario::texto($_POST['genero'] ?? null)), // Recupera el género seleccionado.
                'usuario' => trim(ValidacionFormulario::texto($_POST['usuario'] ?? null)), // Recupera el usuario que identifica el acceso.
                'curp' => strtoupper(trim(ValidacionFormulario::texto($_POST['curp'] ?? null))), // Normaliza la CURP para aplicar las mismas reglas del alta.
                'rol' => trim(ValidacionFormulario::texto($_POST['rol'] ?? null)), // Recupera el rol que se asignará al empleado.
            ]; // Finaliza la lectura de los datos editables.
            // Define los roles que la aplicación permite asignar según el formulario de registro.
            $rolesPermitidos = ['ADMINISTRADOR', 'VENTAS', 'ALMACEN', 'DISTRIBUCION', 'REPARTIDOR']; // Evita asignar valores ajenos al ENUM funcional.
            // Busca otra cuenta con el mismo usuario sin marcar la cuenta editada como duplicada.
            $usuarioExistente = $this->empleado->buscarPorUsuario($datosEdicion['usuario']); // Usa la consulta preparada del modelo.
            // Comprueba si el usuario pertenece a un empleado distinto del objetivo.
            $usuarioDuplicado = $usuarioExistente !== null && (int) ($usuarioExistente['id_empleado'] ?? 0) !== $idEmpleado; // Permite conservar el identificador actual.
            // Busca una CURP repetida excluyendo el registro que se está editando.
            $curpDuplicado = $this->empleado->buscarPorCurpExcluyendoId($datosEdicion['curp'], $idEmpleado); // Mantiene la unicidad al cambiar o conservar la CURP.
            // Comprueba campos requeridos, formatos, listas permitidas y valores estrictos del estado.
            $datosValidos = $datosEdicion['nombre'] !== '' && $datosEdicion['apellido_p'] !== '' && $datosEdicion['usuario'] !== '' && $datosEdicion['curp'] !== '' && $datosEdicion['telefono'] !== '' && $datosEdicion['rol'] !== '' && ValidacionFormulario::nombrePersona($datosEdicion['nombre']) && ValidacionFormulario::nombrePersona($datosEdicion['seg_nombre'], false) && ValidacionFormulario::nombrePersona($datosEdicion['apellido_p']) && ValidacionFormulario::nombrePersona($datosEdicion['apellido_m'], false) && ValidacionFormulario::telefono($datosEdicion['telefono']) && preg_match('/^[A-Za-z0-9._-]{3,50}$/D', $datosEdicion['usuario']) === 1 && ValidacionFormulario::curp($datosEdicion['curp']) && in_array($datosEdicion['rol'], $rolesPermitidos, true) && ($datosEdicion['genero'] === '' || in_array($datosEdicion['genero'], ['Masculino', 'Femenino', 'Otro'], true)) && !$usuarioDuplicado && !$curpDuplicado; // Replica en servidor las reglas del registro y evita duplicados.
            // Rechaza cualquier dato incompleto, duplicado o con formato no permitido.
            if (!$datosValidos) { // Impide persistir campos mal formados.
                $mensajeValidacion = $usuarioDuplicado ? 'Ese nombre de usuario ya está registrado.' : ($curpDuplicado ? 'Esa CURP ya está registrada.' : 'Revisa nombre, apellidos, usuario, CURP, teléfono, rol y género.'); // Identifica duplicados y resume los campos que deben corregirse.
                $avisoDetalle = ['tipo' => 'danger', 'mensaje' => $mensajeValidacion]; // Mantiene las entradas enviadas para que el administrador las corrija.
            } else { // Continúa con la actualización administrativa integral.
                // Intenta guardar todos los cambios validados en una sola sentencia del modelo.
                try { // Controla errores de PDO sin afectar el flujo de autenticación.
                    $datosActualizados = $this->empleado->actualizarDatosAdministrativos($idEmpleado, $datosEdicion); // Incluye el estado en el mismo guardado de datos y rol.
                } catch (Throwable $excepcion) { // Mantiene los detalles técnicos fuera de la respuesta.
                    error_log('Falló la edición administrativa de datos de empleado.'); // Registra un mensaje genérico.
                    $datosActualizados = false; // Permite mostrar una alerta controlada.
                } // Finaliza el manejo de errores de edición.
                // Redirige tras guardar correctamente para evitar repetir el POST.
                if ($datosActualizados) { // Confirma el cambio antes de redirigir.
                    $_SESSION['avisoDetalle'] = ['tipo' => 'success', 'mensaje' => 'Datos de la cuenta actualizados.']; // Conserva una confirmación de una sola lectura.
                    header('Location: index.php?c=empleado&a=ver&id=' . $idEmpleado, true, 303); // Vuelve a cargar la ficha desde la base.
                    exit; // Detiene el procesamiento tras el redireccionamiento.
                } // Finaliza la confirmación de edición.
                // Presenta un mensaje seguro si el UPDATE no pudo completarse.
                $avisoDetalle = ['tipo' => 'danger', 'mensaje' => 'No fue posible guardar los cambios; revisa que el usuario y la CURP no estén registrados en otra cuenta.']; // No revela el error interno del SGBD.
            } // Finaliza la validación y persistencia de datos editables.
        } // Finaliza el procesamiento POST de la ficha.
        // Inicializa el formulario con la información vigente cuando no hubo datos enviados.
        if ($datosEdicion === []) { // Conserva lo enviado si la validación mostró errores.
            $datosEdicion = [ // Prepara los valores guardados para el formulario precargado.
                'nombre' => (string) ($empleadoDetalle['nombre'] ?? ''), // Carga el nombre desde la fila encontrada.
                'seg_nombre' => (string) ($empleadoDetalle['seg_nombre'] ?? ''), // Carga el segundo nombre si está disponible.
                'apellido_p' => (string) ($empleadoDetalle['apellido_p'] ?? ''), // Carga el apellido paterno.
                'apellido_m' => (string) ($empleadoDetalle['apellido_m'] ?? ''), // Carga el apellido materno.
                'telefono' => (string) ($empleadoDetalle['telefono'] ?? ''), // Carga el teléfono guardado.
                'genero' => (string) ($empleadoDetalle['genero'] ?? ''), // Carga el género guardado.
                'usuario' => (string) ($empleadoDetalle['usuario'] ?? ''), // Carga el nombre de usuario registrado.
                'curp' => (string) ($empleadoDetalle['curp'] ?? ''), // Carga la CURP vigente.
                'rol' => (string) ($empleadoDetalle['rol'] ?? ''), // Carga el rol vigente.
            ]; // Finaliza la preparación del formulario.
        } // Finaliza la selección de valores de formulario.
        // Compone el nombre para el encabezado de la ficha.
        $nombreCompleto = trim(implode(' ', array_filter([(string) ($empleadoDetalle['nombre'] ?? ''), (string) ($empleadoDetalle['seg_nombre'] ?? ''), (string) ($empleadoDetalle['apellido_p'] ?? ''), (string) ($empleadoDetalle['apellido_m'] ?? '')]))); // Omite componentes vacíos.
        // Pasa la identidad administrativa al menú lateral de la vista.
        $usuarioSesion = $_SESSION['user']; // Usa exclusivamente la identidad autenticada.
        // Renderiza la ficha editable sin incluir la contraseña.
        require dirname(__DIR__) . '/views/empleados/detalle.php'; // Mantiene la presentación separada del controlador.
    } // Finaliza la acción de ficha, edición y estado.

    // Carga los datos del perfil activo y procesa la actualización por el método POST.
    public function perfil(): void // Define el punto de entrada para que cualquier empleado autenticado gestione su perfil.
    { // Inicia la acción de perfil de usuario.
        // Lee el rol desde la identidad autenticada y no desde los parámetros de la solicitud.
        $rolPerfil = (string) ($_SESSION['user']['rol'] ?? ''); // Conserva el valor validado durante el inicio de sesión.
        // Declara los roles autorizados para consultar y actualizar su propio perfil.
        $rolesPerfilPermitidos = ['ALMACEN', 'VENTAS', 'ADMINISTRADOR', 'DISTRIBUCION', 'REPARTIDOR']; // Coincide con la visibilidad de Mi Perfil en la navegación.
        // Rechaza perfiles cuyo rol no pertenece al conjunto operativo autorizado.
        if (!in_array($rolPerfil, $rolesPerfilPermitidos, true)) { // Evita que un rol ajeno use la ruta aunque conozca su URL.
            // Informa al navegador que la sesión no tiene permiso para acceder al perfil.
            http_response_code(403); // Usa el estado HTTP estándar de acceso prohibido.
            // Detiene la acción antes de consultar los datos personales de la cuenta.
            exit('Acceso denegado: el rol no tiene acceso al perfil personal.'); // Mantiene la autorización del servidor alineada con el menú.
        } // Finaliza la comprobación explícita de rol.
        // Obtiene el identificador numérico del empleado desde los datos de sesión activa.
        $idEmpleado = (int) ($_SESSION['user']['id_empleado'] ?? 0); // Localiza al usuario sin confiar en parámetros externos.
        // Inicializa el mensaje de alerta para la vista sin notificaciones previas.
        $mensaje = ''; // Permite mostrar confirmaciones o errores en la vista.
        // Inicializa el estilo de alerta con la clase danger de Bootstrap para errores.
        $tipoAlerta = 'danger'; // Usa rojo para fallos de validación y de persistencia.
        // Consulta los datos vigentes del empleado mediante el método seguro del modelo.
        try { // Captura posibles anomalías de conexión o consulta de base de datos.
            // Busca las columnas del empleado usando su identificador primario.
            $empleadoActual = $this->empleado->buscarPorId($idEmpleado); // Ejecuta una consulta parametrizada con PDO.
        } catch (Throwable $excepcion) { // Captura excepciones lanzadas durante la consulta.
            // Registra el error en la bitácora interna sin exponer datos confidenciales.
            error_log('Error al consultar los datos del perfil del empleado activo.'); // Registra el suceso de forma segura.
            // Asigna null al empleado para gestionar el fallo de manera controlada.
            $empleadoActual = null; // Establece el estado de lectura fallida.
        } // Finaliza el manejo de excepción de lectura.
        // Detiene el flujo con error controlado si no existe el registro del empleado.
        if ($empleadoActual === null) { // Evita operaciones sobre identidades inexistentes en la base.
            // Establece el código de estado HTTP para error interno.
            http_response_code(500); // Emite código estándar de fallo de servicio.
            // Finaliza la ejecución con un mensaje claro y seguro.
            exit('Error: no fue posible cargar los datos del perfil.'); // Detiene el procesamiento.
        } // Finaliza la validación de existencia del empleado.
        // Prepara los campos del formulario inicializados con la información actual de la base de datos.
        $datosEmpleado = [ // Agrupa los datos del empleado para precargarlos en la vista.
            'id_empleado' => (int) $empleadoActual['id_empleado'], // Conserva el identificador primario.
            'nombre' => (string) ($empleadoActual['nombre'] ?? ''), // Conserva el nombre de pila.
            'seg_nombre' => (string) ($empleadoActual['seg_nombre'] ?? ''), // Conserva el segundo nombre opcional.
            'apellido_p' => (string) ($empleadoActual['apellido_p'] ?? ''), // Conserva el apellido paterno.
            'apellido_m' => (string) ($empleadoActual['apellido_m'] ?? ''), // Conserva el apellido materno opcional.
            'telefono' => (string) ($empleadoActual['telefono'] ?? ''), // Conserva el teléfono de contacto.
            'genero' => (string) ($empleadoActual['genero'] ?? ''), // Conserva el género registrado.
            'usuario' => (string) ($empleadoActual['usuario'] ?? ''), // Conserva el nombre de usuario de solo lectura.
            'curp' => (string) ($empleadoActual['curp'] ?? ''), // Conserva la CURP de solo lectura.
            'rol' => (string) ($empleadoActual['rol'] ?? ''), // Conserva el rol asignado de solo lectura.
        ]; // Finaliza la inicialización de datos del empleado.
        // Comprueba si la solicitud corresponde a un envío de formulario mediante POST.
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') { // Evita procesar cambios durante peticiones de lectura GET.
            // Extrae y normaliza el campo obligatorio de nombre eliminando espacios en blanco.
            $nombre = trim(ValidacionFormulario::texto($_POST['nombre'] ?? null)); // Normaliza la entrada del nombre.
            // Extrae y normaliza el campo obligatorio de apellido paterno.
            $apellidoP = trim(ValidacionFormulario::texto($_POST['apellido_p'] ?? null)); // Normaliza la entrada del apellido paterno.
            // Extrae y normaliza el campo obligatorio de teléfono de contacto.
            $telefono = trim(ValidacionFormulario::texto($_POST['telefono'] ?? null)); // Normaliza la entrada del teléfono.
            // Extrae y normaliza el campo opcional de segundo nombre.
            $segNombre = trim(ValidacionFormulario::texto($_POST['seg_nombre'] ?? null)); // Normaliza la entrada del segundo nombre.
            // Extrae y normaliza el campo opcional de apellido materno.
            $apellidoM = trim(ValidacionFormulario::texto($_POST['apellido_m'] ?? null)); // Normaliza la entrada del apellido materno.
            // Extrae y normaliza el campo opcional de género.
            $genero = trim(ValidacionFormulario::texto($_POST['genero'] ?? null)); // Normaliza la selección de género.
            // Repuebla los campos modificables en el arreglo para no perder lo ingresado ante errores.
            $datosEmpleado['nombre'] = $nombre; // Conserva el nombre capturado.
            // Repuebla el apellido paterno capturado.
            $datosEmpleado['apellido_p'] = $apellidoP; // Conserva el apellido paterno.
            // Repuebla el teléfono capturado.
            $datosEmpleado['telefono'] = $telefono; // Conserva el teléfono.
            // Repuebla el segundo nombre capturado.
            $datosEmpleado['seg_nombre'] = $segNombre; // Conserva el segundo nombre.
            // Repuebla el apellido materno capturado.
            $datosEmpleado['apellido_m'] = $apellidoM; // Conserva el apellido materno.
            // Repuebla el género capturado.
            $datosEmpleado['genero'] = $genero; // Conserva el género.
            // Comprueba los nombres personales, el teléfono y el género antes de persistir el perfil.
            $nombresValidos = ValidacionFormulario::nombrePersona($nombre) && ValidacionFormulario::nombrePersona($segNombre, false) && ValidacionFormulario::nombrePersona($apellidoP) && ValidacionFormulario::nombrePersona($apellidoM, false); // Mantiene la misma regla que el alta administrativa.
            $telefonoValido = ValidacionFormulario::telefono($telefono); // Exige únicamente dígitos y una longitud de 10 a 15.
            $generoValido = $genero === '' || in_array($genero, ['Masculino', 'Femenino', 'Otro'], true); // Acepta solo valores mostrados por el selector.
            // Verifica que los tres campos obligatorios no se encuentren vacíos.
            $camposObligatoriosValidos = $nombre !== '' && $apellidoP !== '' && $telefono !== ''; // Exige obligatoriedad en nombre, apellido paterno y teléfono.
            // Rechaza la actualización si falta alguno de los campos obligatorios.
            if (!$camposObligatoriosValidos) { // Comprueba si faltan datos obligatorios.
                // Establece el mensaje de error textual exacto requerido por la especificación.
                $mensaje = 'Error: datos faltantes'; // Comunica la falta de información requerida.
                // Asigna el estilo de alerta de peligro en rojo de Bootstrap.
                $tipoAlerta = 'danger'; // Muestra la alerta visual correspondiente.
            } elseif (!$nombresValidos) { // Rechaza nombres con números, símbolos inválidos o más de 100 caracteres.
                $mensaje = 'Los nombres solo pueden contener letras, espacios, apóstrofos, puntos o guiones y tener hasta 100 caracteres.'; // Detalla la regla para corregir los campos.
                $tipoAlerta = 'danger'; // Comunica el error sin intentar guardar cambios.
            } elseif (!$telefonoValido) { // Rechaza teléfonos con caracteres distintos de dígitos.
                $mensaje = 'El teléfono debe contener entre 10 y 15 dígitos, sin espacios ni signos.'; // Informa el formato aceptado.
                $tipoAlerta = 'danger'; // Señala la entrada que debe corregirse.
            } elseif (!$generoValido) { // Rechaza un género enviado fuera del conjunto permitido.
                $mensaje = 'La opción de género seleccionada no es válida.'; // Explica por qué no se acepta el valor.
                $tipoAlerta = 'danger'; // Impide persistir valores manipulados.
            } else { // Procede con la persistencia cuando la validación es satisfactoria.
                // Prepara el conjunto de datos autorizados para la actualización en el modelo.
                $datosActualizar = [ // Excluye estrictamente usuario, curp y rol.
                    'nombre' => $nombre, // Asigna el nuevo nombre validado.
                    'seg_nombre' => $segNombre !== '' ? $segNombre : null, // Guarda segundo nombre o null si está vacío.
                    'apellido_p' => $apellidoP, // Asigna el apellido paterno validado.
                    'apellido_m' => $apellidoM !== '' ? $apellidoM : null, // Guarda apellido materno o null si está vacío.
                    'telefono' => $telefono, // Asigna el teléfono validado.
                    'genero' => $genero !== '' ? $genero : null, // Guarda género o null si no fue seleccionado.
                ]; // Finaliza el arreglo de datos modificables.
                // Intenta ejecutar la sentencia UPDATE controlando posibles fallos del SGBD.
                try { // Aísla los errores de base de datos para no interrumpir el flujo.
                    // Invoca la actualización parametrizada en el modelo pasando el ID de sesión.
                    $resultadoActualizacion = $this->empleado->actualizarDatos($idEmpleado, $datosActualizar); // Ejecuta la consulta preparada.
                    // Evalúa si la ejecución de la sentencia se completó con éxito.
                    if ($resultadoActualizacion) { // Comprueba el resultado booleano devuelto por PDO.
                        // Establece el mensaje de confirmación exacto requerido por la especificación.
                        $mensaje = 'Datos actualizados'; // Comunica que la información fue guardada.
                        // Asigna el estilo de alerta de éxito en verde de Bootstrap.
                        $tipoAlerta = 'success'; // Muestra la alerta de éxito.
                        // Recarga los datos actualizados desde la base de datos para reflejar los cambios guardados.
                        $empleadoRecargado = $this->empleado->buscarPorId($idEmpleado); // Obtiene el registro fresco.
                        // Actualiza el arreglo del formulario con los valores frescos persistidos.
                        if ($empleadoRecargado !== null) { // Confirma la existencia del registro recargado.
                            // Refresca el nombre guardado.
                            $datosEmpleado['nombre'] = (string) ($empleadoRecargado['nombre'] ?? ''); // Actualiza nombre en pantalla.
                            // Refresca el segundo nombre guardado.
                            $datosEmpleado['seg_nombre'] = (string) ($empleadoRecargado['seg_nombre'] ?? ''); // Actualiza segundo nombre en pantalla.
                            // Refresca el apellido paterno guardado.
                            $datosEmpleado['apellido_p'] = (string) ($empleadoRecargado['apellido_p'] ?? ''); // Actualiza apellido paterno en pantalla.
                            // Refresca el apellido materno guardado.
                            $datosEmpleado['apellido_m'] = (string) ($empleadoRecargado['apellido_m'] ?? ''); // Actualiza apellido materno en pantalla.
                            // Refresca el teléfono guardado.
                            $datosEmpleado['telefono'] = (string) ($empleadoRecargado['telefono'] ?? ''); // Actualiza teléfono en pantalla.
                            // Refresca el género guardado.
                            $datosEmpleado['genero'] = (string) ($empleadoRecargado['genero'] ?? ''); // Actualiza género en pantalla.
                        } // Finaliza la actualización de valores recargados.
                    } else { // Atiende el caso en que la ejecución del UPDATE no fue exitosa.
                        // Establece el mensaje de fallo del SGBD requerido por la especificación.
                        $mensaje = 'Error de registro'; // Comunica el fallo de persistencia.
                        // Asigna el estilo de alerta de peligro en rojo de Bootstrap.
                        $tipoAlerta = 'danger'; // Muestra la alerta roja.
                    } // Finaliza la comprobación del resultado de actualización.
                } catch (Throwable $excepcion) { // Captura fallos arrojados por el SGBD o PDO.
                    // Registra el mensaje técnico en la bitácora interna del servidor.
                    error_log('Fallo técnico del SGBD al actualizar perfil: ' . $excepcion->getMessage()); // Registro técnico seguro.
                    // Establece el mensaje de error de base de datos requerido por la especificación.
                    $mensaje = 'Error de registro'; // Comunica el fallo del SGBD según requerimiento.
                    // Asigna el estilo de alerta de peligro en rojo de Bootstrap.
                    $tipoAlerta = 'danger'; // Muestra la alerta roja.
                } // Finaliza el bloque try-catch de persistencia.
            } // Finaliza la evaluación de campos obligatorios.
        } // Finaliza el procesamiento del formulario por método POST.
        // Asigna los datos de sesión para el encabezado y navegación del layout.
        $usuarioSesion = $_SESSION['user']; // Proporciona la identidad activa.
        // Carga la plantilla visual del perfil entregando las variables requeridas.
        require dirname(__DIR__) . '/views/empleados/perfil.php'; // Renderiza la vista de perfil de usuario.
    } // Finaliza el método perfil.
} // Finaliza la definición de la clase EmpleadoController.
