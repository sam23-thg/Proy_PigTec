<?php
// Activa la comprobación estricta de tipos para los datos y resultados del controlador.
declare(strict_types=1); // Reduce conversiones implícitas durante la autenticación.

// Declara el controlador que coordina las vistas y la autenticación de empleados.
class AuthController // Mantiene el procesamiento de credenciales fuera de las vistas.
{ // Inicia la definición de AuthController.
    // Conserva el modelo de empleados para consultar cuentas y actualizar contraseñas.
    private Empleado $empleado; // Reutiliza la capa de datos implementada para la tabla empleado.

    // Recibe el modelo que ya contiene la conexión PDO configurada.
    public function __construct(Empleado $empleado) // Inyecta la dependencia sin crear otra conexión.
    { // Inicia la inicialización del controlador.
        // Guarda el modelo para las operaciones de autenticación y credenciales temporales.
        $this->empleado = $empleado; // Asigna la dependencia recibida al atributo privado.
    } // Finaliza la inicialización del controlador.

    // Muestra la pantalla de acceso con una alerta opcional.
    public function mostrarLogin(?string $mensaje = null, string $tipoAlerta = 'danger'): void // Permite presentar errores controlados desde el enrutador.
    { // Inicia la preparación de la vista Login.
        // Define las variables de la vista como formulario no autenticado.
        $modoSesionIniciada = false; // Garantiza que se renderice el formulario de acceso.
        // Expone a la vista solo el mensaje entregado por el flujo de autenticación.
        $mensaje = $mensaje ?? ''; // Usa una cadena vacía cuando no se necesita alerta.
        // Incluye la vista ubicada en la ruta MVC definida para autenticación.
        require dirname(__DIR__) . '/views/auth/login.php'; // Renderiza el formulario sin duplicar marcado.
    } // Finaliza la presentación de la vista Login.

    // Verifica las credenciales y crea la sesión autenticada del empleado.
    public function procesarInicioSesion(array $datos): void // Recibe los campos enviados mediante POST.
    { // Inicia el procesamiento del formulario de acceso.
        // Elimina espacios externos del nombre de usuario sin modificar la contraseña.
        $usuario = trim((string) ($datos['usuario'] ?? '')); // Normaliza el identificador para buscarlo.
        // Conserva exactamente la contraseña recibida para que password_verify pueda validarla.
        $contrasenia = (string) ($datos['contrasenia'] ?? ''); // Evita alterar espacios que formen parte de la credencial.
        // Deniega las solicitudes incompletas también cuando el navegador no ejecutó JavaScript.
        if ($usuario === '' || $contrasenia === '') { // Repite en servidor las reglas de obligatoriedad del formulario.
            $this->mostrarLogin('Usuario o contraseña incorrectos', 'danger'); // Usa un mensaje uniforme para datos inválidos.
            return; // Termina el procesamiento sin consultar la base de datos.
        } // Finaliza la validación de campos obligatorios.

        // Busca la cuenta por usuario y presenta un mensaje técnico controlado ante fallos de PDO.
        try { // Aísla errores de consulta para no confundirlos con una contraseña incorrecta.
            $empleadoEncontrado = $this->empleado->buscarPorUsuario($usuario); // Ejecuta la búsqueda parametrizada sobre el esquema activo.
        } catch (Throwable $excepcion) { // Captura errores de conexión y consulta sin revelar detalles al navegador.
            error_log('Falló la consulta de una cuenta durante el inicio de sesión.'); // Registra información genérica sin credenciales.
            http_response_code(503); // Informa que el servicio de autenticación no está disponible por un fallo técnico.
            $this->mostrarLogin('No fue posible conectar con el sistema. Inténtalo nuevamente más tarde.', 'warning'); // Expone un error de servicio, no detalles del servidor.
            return; // Finaliza el proceso sin crear sesión ni aceptar la contraseña.
        } // Finaliza el manejo controlado de errores PDO.
        // Comprueba la existencia y el hash antes de crear cualquier dato de sesión.
        if ($empleadoEncontrado === null || !password_verify($contrasenia, (string) $empleadoEncontrado['contrasenia'])) { // Rechaza usuario inexistente o credencial incorrecta.
            $this->mostrarLogin('Usuario o contraseña incorrectos', 'danger'); // No revela cuál de los dos datos falló.
            return; // Evita continuar con una identidad no autenticada.
        } // Finaliza la comprobación de credenciales.

        // Obtiene el rol validado de la fila encontrada para seleccionar permisos estáticos.
        $rolEmpleado = (string) $empleadoEncontrado['rol']; // Usa únicamente el rol almacenado en la base de datos.
        // Define las capacidades autorizadas para cada rol sin depender de tablas de permisos inexistentes.
        $permisosPorRol = [ // Mantiene el mapeo local y explícito para esta fase del sistema.
            'ADMINISTRADOR' => ['gestionar_empleados', 'restablecer_password', 'ver_catalogo', 'editar_productos'], // Otorga al administrador las cuatro capacidades solicitadas.
            'ALMACEN' => ['ver_catalogo', 'editar_productos'], // Limita almacén a consultar y editar productos.
            'VENTAS' => ['ver_catalogo'], // Permite a ventas consultar el catálogo sin modificar productos.
            'DISTRIBUCION' => ['ver_catalogo'], // Permite a distribución consultar el catálogo sin facultades administrativas.
            'REPARTIDOR' => ['ver_catalogo'], // Asigna al repartidor el permiso básico de consulta.
        ]; // Finaliza el mapeo estático de roles y permisos.
        // Obtiene los permisos correspondientes o no concede capacidades a roles no reconocidos.
        $permisosEmpleado = $permisosPorRol[$rolEmpleado] ?? []; // Aplica el principio de mínimo privilegio a roles desconocidos.

        // Deniega la cuenta si su estado no confirma explícitamente que está activa.
        if ((int) ($empleadoEncontrado['estado'] ?? 0) !== 1) { // Trata cero y la ausencia inesperada del estado como desactivados por seguridad.
            $this->mostrarLogin('Cuenta desactivada. Contacte al administrador.', 'danger'); // Presenta el mensaje controlado requerido para cuentas inactivas.
            return; // No crea sesión para una cuenta inactiva.
        } // Finaliza la validación de estado de la cuenta.

        // Renueva el identificador para impedir la fijación de sesión al iniciar sesión.
        session_regenerate_id(true); // Descarta el identificador previo y crea uno nuevo.
        // Guarda únicamente la identidad y los datos necesarios para la sesión autenticada.
        $_SESSION['user'] = [ // Establece la estructura de sesión solicitada para el usuario.
            'id_empleado' => (int) $empleadoEncontrado['id_empleado'], // Almacena el identificador primario del empleado.
            'usuario' => (string) $empleadoEncontrado['usuario'], // Almacena el nombre de usuario verificado.
            'rol' => $rolEmpleado, // Almacena el rol validado contra el registro de empleado.
            'permisos' => $permisosEmpleado, // Almacena los permisos estáticos definidos para el rol autenticado.
        ]; // Finaliza la asignación de la identidad de sesión.
        // Conserva si la cuenta requiere renovar su contraseña antes de navegar.
        $_SESSION['requiereCambioTemporal'] = (array_key_exists('es_temporal', $empleadoEncontrado) && (int) $empleadoEncontrado['es_temporal'] === 1) || hash_equals('12345678', $contrasenia); // Usa la bandera del esquema cuando existe o simula el estado temporal con la clave genérica tras verificarla.
        // Prepara una notificación que se mostrará después de redirigir al destino interno.
        $_SESSION['mensajeAutenticacion'] = 'Inicio de sesión correcto.'; // Evita enviar mensajes mediante parámetros manipulables de la URL.

        // Obliga a las cuentas con credenciales temporales a completar el cambio antes de entrar a su área.
        if ($_SESSION['requiereCambioTemporal']) { // Verifica la bandera persistida es_temporal.
            header('Location: ?accion=cambiar-temporal', true, 303); // Envía al empleado a la pantalla de cambio obligatorio.
            exit; // Detiene la ejecución para impedir navegación antes de la actualización.
        } // Finaliza la interceptación de cuentas con contraseña temporal.

        // Relaciona cada valor permitido del ENUM de rol con un destino interno fijo.
        $rutasPorRol = [ // Evita utilizar valores enviados por el navegador como destinos de redirección.
            'ADMINISTRADOR' => 'panel-administrador', // Define el destino interno del administrador.
            'VENTAS' => 'panel-ventas', // Define el destino interno del personal de ventas.
            'ALMACEN' => 'panel-almacen', // Define el destino interno del personal de almacén.
            'DISTRIBUCION' => 'panel-distribucion', // Define el destino interno del personal de distribución.
            'REPARTIDOR' => 'panel-repartidor', // Define el destino interno del personal repartidor.
        ]; // Finaliza el mapeo cerrado de roles a destinos.
        // Resuelve la ruta correspondiente al rol almacenado y usa Login como destino seguro alternativo.
        $destino = $rutasPorRol[$_SESSION['user']['rol']] ?? 'login'; // No permite redirecciones externas ni rutas arbitrarias.
        // Redirige al destino interno con el código 303 para cambiar de POST a GET.
        header('Location: ?accion=' . rawurlencode($destino), true, 303); // Impide que el refresco repita el envío de credenciales.
        exit; // Finaliza la solicitud después de autenticar y redirigir.
    } // Finaliza el procesamiento del formulario de acceso.

    // Presenta la pantalla de cambio obligatorio solo para una sesión marcada como temporal.
    public function mostrarCambioTemporal(?string $mensaje = null, string $tipoAlerta = 'danger'): void // Permite re-renderizar el formulario con mensajes controlados.
    { // Inicia la preparación de la vista temporal.
        // Verifica que exista una identidad autenticada con cambio pendiente.
        if (!isset($_SESSION['user']) || empty($_SESSION['requiereCambioTemporal'])) { // Evita mostrar el formulario fuera del flujo autorizado.
            header('Location: ?accion=login', true, 303); // Devuelve a Login las solicitudes sin cambio pendiente.
            exit; // Impide que continúe el procesamiento de una vista protegida.
        } // Finaliza la protección del formulario temporal.
        // Prepara la notificación para la vista y conserva el modo de acceso obligatorio.
        $mensaje = $mensaje ?? ''; // Usa una cadena vacía cuando no hay error que presentar.
        // Carga la vista intermedia de autenticación definida en el MVC.
        require dirname(__DIR__) . '/views/auth/cambiar_temporal.php'; // Renderiza el formulario sin insertar marcado aquí.
    } // Finaliza la presentación del formulario de cambio temporal.

    // Valida la contraseña nueva y guarda un hash definitivo en el registro autenticado.
    public function procesarCambioTemporal(array $datos): void // Recibe la contraseña nueva y su confirmación desde POST.
    { // Inicia el procesamiento del cambio obligatorio.
        // Confirma nuevamente que el usuario siga autenticado y marcado para el cambio.
        if (!isset($_SESSION['user']) || empty($_SESSION['requiereCambioTemporal'])) { // Protege también la llamada directa al método.
            header('Location: ?accion=login', true, 303); // Envía a Login cualquier sesión que no cumpla la condición.
            exit; // Detiene el procesamiento de una solicitud no autorizada.
        } // Finaliza la protección del cambio de contraseña.

        // Obtiene la contraseña nueva conservando los caracteres que eligió el empleado.
        $contraseniaNueva = (string) ($datos['contrasenia_nueva'] ?? ''); // No modifica espacios que sean parte de la clave.
        // Obtiene el valor de confirmación para compararlo en el servidor.
        $confirmacionContrasenia = (string) ($datos['confirmacion_contrasenia'] ?? ''); // Evita depender solo de la validación del navegador.

        // Exige al menos ocho caracteres y valores idénticos en ambos campos.
        if (strlen($contraseniaNueva) < 8 || $contraseniaNueva !== $confirmacionContrasenia) { // Aplica las reglas de contraseña definitiva del requisito.
            $this->mostrarCambioTemporal('Las contraseñas deben coincidir y tener al menos 8 caracteres.', 'danger'); // Explica las condiciones sin alterar los campos enviados.
            return; // Mantiene la sesión en la pantalla obligatoria para corregir los datos.
        } // Finaliza la validación de longitud y coincidencia.

        // Genera una nueva credencial BCRYPT antes de actualizar la columna contrasenia.
        $hashContrasenia = password_hash($contraseniaNueva, PASSWORD_BCRYPT); // Nunca guarda la contraseña definitiva en texto plano.
        // Detiene el proceso si la función de hash no pudo generar una credencial segura.
        if ($hashContrasenia === false) { // Comprueba explícitamente el resultado antes de persistir.
            $this->mostrarCambioTemporal('No fue posible guardar la contraseña. Inténtalo nuevamente.', 'danger'); // Presenta un error controlado.
            return; // Conserva el flujo obligatorio para permitir un nuevo intento.
        } // Finaliza la validación de generación del hash.

        // Actualiza únicamente el hash de contraseña mediante el modelo de empleados.
        $actualizacionCorrecta = $this->empleado->cambiarPassword((int) $_SESSION['user']['id_empleado'], $hashContrasenia); // Envía el identificador y el nuevo hash definitivo.
        // Mantiene al empleado en el paso obligatorio si el modelo no pudo completar la escritura.
        if (!$actualizacionCorrecta) { // Detecta fallos de ejecución reportados por PDO.
            $this->mostrarCambioTemporal('No fue posible guardar la contraseña. Inténtalo nuevamente.', 'danger'); // No expone detalles internos del SGBD.
            return; // Evita habilitar la navegación tras una actualización fallida.
        } // Finaliza la comprobación de persistencia.

        // Elimina el bloqueo de navegación temporal en la sesión que ya fue actualizada.
        $_SESSION['requiereCambioTemporal'] = false; // Impide que el Front Controller vuelva a interceptar al empleado.
        // Prepara el mensaje de éxito para mostrarlo después de la redirección.
        $_SESSION['mensajeAutenticacion'] = 'Contraseña guardada'; // Confirma el resultado usando el texto especificado en el requisito.
        // Selecciona una ruta interna según el rol autenticado después de terminar el cambio.
        $rutasPorRol = [ // Limita los destinos disponibles a los roles reconocidos por la base de datos.
            'ADMINISTRADOR' => 'panel-administrador', // Define la ruta interna para administrador.
            'VENTAS' => 'panel-ventas', // Define la ruta interna para ventas.
            'ALMACEN' => 'panel-almacen', // Define la ruta interna para almacén.
            'DISTRIBUCION' => 'panel-distribucion', // Define la ruta interna para distribución.
            'REPARTIDOR' => 'panel-repartidor', // Define la ruta interna para repartidor.
        ]; // Finaliza el mapeo de destinos posteriores al cambio.
        // Resuelve el destino a partir del rol guardado en la sesión y conserva un destino seguro alternativo.
        $destino = $rutasPorRol[(string) ($_SESSION['user']['rol'] ?? '')] ?? 'login'; // No utiliza datos controlados por el formulario.
        // Redirige al destino de sesión con una solicitud GET nueva.
        header('Location: ?accion=' . rawurlencode($destino), true, 303); // Muestra el mensaje flash en la pantalla siguiente.
        exit; // Termina el flujo obligatorio tras guardar la contraseña.
    } // Finaliza el procesamiento de la contraseña temporal.
} // Finaliza la definición de AuthController.
