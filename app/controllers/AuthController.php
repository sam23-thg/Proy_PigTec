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
            $this->mostrarLogin('Usuario o contraseña incorrectos.', 'danger'); // Usa un mensaje uniforme para datos inválidos.
            return; // Termina el procesamiento sin consultar la base de datos.
        } // Finaliza la validación de campos obligatorios.

        // Busca la cuenta por el nombre de usuario mediante la consulta parametrizada del modelo.
        $empleadoEncontrado = $this->empleado->buscarPorUsuario($usuario); // Obtiene null cuando no existe una cuenta coincidente.
        // Comprueba la existencia y el hash antes de crear cualquier dato de sesión.
        if ($empleadoEncontrado === null || !password_verify($contrasenia, (string) $empleadoEncontrado['contrasenia'])) { // Rechaza usuario inexistente o credencial incorrecta.
            $this->mostrarLogin('Usuario o contraseña incorrectos.', 'danger'); // No revela cuál de los dos datos falló.
            return; // Evita continuar con una identidad no autenticada.
        } // Finaliza la comprobación de credenciales.

        // Deniega una cuenta inactiva después de comprobar la contraseña válida.
        if ((int) ($empleadoEncontrado['estado'] ?? 0) !== 1) { // Interpreta el valor booleano almacenado por MariaDB o MySQL.
            $this->mostrarLogin('La cuenta está inactiva o bloqueada.', 'danger'); // Expone claramente que el acceso fue denegado por estado.
            return; // No crea sesión para una cuenta inactiva.
        } // Finaliza la validación de estado de la cuenta.

        // Renueva el identificador para impedir la fijación de sesión al iniciar sesión.
        session_regenerate_id(true); // Descarta el identificador previo y crea uno nuevo.
        // Guarda únicamente la identidad y los datos necesarios para la sesión autenticada.
        $_SESSION['user'] = [ // Establece la estructura de sesión solicitada para el usuario.
            'id_empleado' => (int) $empleadoEncontrado['id_empleado'], // Almacena el identificador primario del empleado.
            'usuario' => (string) $empleadoEncontrado['usuario'], // Almacena el nombre de usuario verificado.
            'rol' => (string) $empleadoEncontrado['rol'], // Almacena el rol validado contra el registro de empleado.
            'permisos' => [], // Mantiene una lista segura porque el esquema documentado no define tabla ni columnas de permisos.
        ]; // Finaliza la asignación de la identidad de sesión.
        // Prepara una notificación que se mostrará después de redirigir al destino interno.
        $_SESSION['mensajeAutenticacion'] = 'Inicio de sesión correcto.'; // Evita enviar mensajes mediante parámetros manipulables de la URL.

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
} // Finaliza la definición de AuthController.
