<?php
// Activa la comprobación estricta de tipos para los métodos del controlador.
declare(strict_types=1); // Evita conversiones implícitas en las operaciones futuras de empleados.

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
            $datosFormulario['nombre'] = trim((string) ($_POST['nombre'] ?? '')); // Valida el nombre sin conservar espacios periféricos.
            // Recupera el apellido paterno obligatorio desde el formulario.
            $datosFormulario['apellido_p'] = trim((string) ($_POST['apellido_p'] ?? '')); // Normaliza el dato antes de comprobarlo.
            // Recupera el usuario obligatorio usado para iniciar sesión.
            $datosFormulario['usuario'] = trim((string) ($_POST['usuario'] ?? '')); // Evita espacios externos en el identificador.
            // Recupera el CURP obligatorio para validar su unicidad.
            $datosFormulario['curp'] = strtoupper(trim((string) ($_POST['curp'] ?? ''))); // Normaliza el CURP para compararlo con el registro existente.
            // Recupera el teléfono obligatorio como texto para conservar ceros iniciales.
            $datosFormulario['telefono'] = trim((string) ($_POST['telefono'] ?? '')); // Evita convertir el teléfono a un tipo numérico.
            // Conserva exactamente la contraseña escrita para aplicar password_hash sin alterarla.
            $contrasenia = (string) ($_POST['contrasenia'] ?? ''); // No recorta espacios que puedan formar parte de la clave.
            // Recupera el rol seleccionado para comprobarlo contra el ENUM admitido.
            $datosFormulario['rol'] = trim((string) ($_POST['rol'] ?? '')); // No confía en las opciones enviadas por el navegador.
            // Recupera los campos opcionales y los normaliza como cadenas.
            $datosFormulario['seg_nombre'] = trim((string) ($_POST['seg_nombre'] ?? '')); // Permite dejar vacío el segundo nombre.
            // Recupera el apellido materno opcional.
            $datosFormulario['apellido_m'] = trim((string) ($_POST['apellido_m'] ?? '')); // Permite dejar vacío el apellido materno.
            // Recupera el género opcional.
            $datosFormulario['genero'] = trim((string) ($_POST['genero'] ?? '')); // Permite dejar el género sin seleccionar.
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

    // Reserva la acción de listado de empleados para su implementación posterior.
    public function lista(): void // Define el punto de entrada para listar y buscar personal.
    { // Inicia la acción de listado de empleados autorizada.
        // Comprueba que la sesión activa cuente con el rol ADMINISTRADOR.
        $this->verificarRolAdministrador(); // Protege la lista para uso exclusivo de administradores.
        // Obtiene los datos de la tabla empleado a través del método PDO preparado del modelo.
        $usuarios = $this->empleado->listarTodos(); // No accede directamente a la base de datos desde el controlador.
        // Carga el usuario administrador para mostrar el encabezado de sesión de la vista.
        $usuarioSesion = $_SESSION['user']; // La identidad ya fue validada por el constructor.
        // Renderiza la vista de lista usando la ruta MVC correspondiente.
        require dirname(__DIR__) . '/views/empleados/lista.php'; // Entrega la colección consultada al archivo de presentación.
    } // Finaliza la acción de listado de empleados.

    // Reserva la acción de restablecimiento de contraseña para su implementación posterior.
    public function restablecer(): void // Define el punto de entrada para el restablecimiento administrativo.
    { // Inicia la acción de restablecimiento todavía sin lógica, según el alcance solicitado.
        // Comprueba que la sesión activa cuente con el rol ADMINISTRADOR.
        $this->verificarRolAdministrador(); // Protege el restablecimiento para uso exclusivo de administradores.
        // Mantiene este método vacío hasta desarrollar su tarea correspondiente.
    } // Finaliza la acción de restablecimiento.

    // Carga los datos del perfil activo y procesa la actualización por el método POST.
    public function perfil(): void // Define el punto de entrada para que cualquier empleado autenticado gestione su perfil.
    { // Inicia la acción de perfil de usuario.
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
            $nombre = trim((string) ($_POST['nombre'] ?? '')); // Sanitiza la entrada de nombre.
            // Extrae y normaliza el campo obligatorio de apellido paterno.
            $apellidoP = trim((string) ($_POST['apellido_p'] ?? '')); // Sanitiza la entrada de apellido paterno.
            // Extrae y normaliza el campo obligatorio de teléfono de contacto.
            $telefono = trim((string) ($_POST['telefono'] ?? '')); // Sanitiza la entrada de teléfono.
            // Extrae y normaliza el campo opcional de segundo nombre.
            $segNombre = trim((string) ($_POST['seg_nombre'] ?? '')); // Sanitiza la entrada de segundo nombre.
            // Extrae y normaliza el campo opcional de apellido materno.
            $apellidoM = trim((string) ($_POST['apellido_m'] ?? '')); // Sanitiza la entrada de apellido materno.
            // Extrae y normaliza el campo opcional de género.
            $genero = trim((string) ($_POST['genero'] ?? '')); // Sanitiza la selección de género.
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
            // Verifica que los tres campos obligatorios no se encuentren vacíos.
            $camposObligatoriosValidos = $nombre !== '' && $apellidoP !== '' && $telefono !== ''; // Exige obligatoriedad en nombre, apellido paterno y teléfono.
            // Rechaza la actualización si falta alguno de los campos obligatorios.
            if (!$camposObligatoriosValidos) { // Comprueba si faltan datos obligatorios.
                // Establece el mensaje de error textual exacto requerido por la especificación.
                $mensaje = 'Error: datos faltantes'; // Comunica la falta de información requerida.
                // Asigna el estilo de alerta de peligro en rojo de Bootstrap.
                $tipoAlerta = 'danger'; // Muestra la alerta visual correspondiente.
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
