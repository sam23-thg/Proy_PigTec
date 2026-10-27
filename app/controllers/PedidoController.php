<?php
// Activa la comprobación estricta de tipos en el controlador de almacén.
declare(strict_types=1); // Evita conversiones implícitas en el flujo de consulta.

// Declara el controlador que protege y presenta los pedidos de almacén.
class PedidoController // Coordina el rol autenticado, el modelo y la vista.
{ // Inicia la definición del controlador.
    // Conserva el modelo que consulta los pedidos pendientes.
    private Pedido $pedido; // Mantiene el acceso a los datos separado de la plantilla.

    // Restringe el módulo exclusivamente a los roles ALMACEN y ADMINISTRADOR.
    public function __construct(Pedido $pedido) // Recibe el modelo con la conexión PDO existente.
    { // Inicia la comprobación del acceso al módulo.
        // Lee el rol desde la sesión establecida por el proceso de autenticación.
        $rolUsuario = $_SESSION['user']['rol'] ?? ''; // Ignora cualquier rol enviado desde la URL o un formulario.
        // Rechaza usuarios sin sesión activa o con roles distintos a los autorizados.
        if (session_status() !== PHP_SESSION_ACTIVE || !isset($_SESSION['user']) || !is_array($_SESSION['user']) || !in_array($rolUsuario, ['ALMACEN', 'ADMINISTRADOR'], true)) { // Limita la consulta al personal autorizado.
            // Informa al cliente HTTP que no tiene permiso para abrir el módulo.
            http_response_code(403); // Usa el código estándar de acceso prohibido.
            // Termina la solicitud antes de consultar información de los pedidos.
            exit('Acceso denegado: se requiere una sesión activa con rol ALMACEN o ADMINISTRADOR.'); // No expone datos a roles ajenos.
        } // Finaliza la validación de sesión y rol.
        // Guarda el modelo una vez autorizada la sesión.
        $this->pedido = $pedido; // Habilita la consulta de pedidos para la acción.
    } // Finaliza la inicialización del controlador.

    // Consulta y muestra las tarjetas con pedidos que requieren preparación.
    public function pendientes(): void // Atiende index.php?c=pedido&a=pendientes.
    { // Inicia la acción de pedidos pendientes.
        // Crea un token de sesión para proteger los formularios que cambian el estado.
        if (!isset($_SESSION['tokenPedido']) || !is_string($_SESSION['tokenPedido'])) { // Reutiliza el token mientras dure la sesión.
            $_SESSION['tokenPedido'] = bin2hex(random_bytes(32)); // Genera un valor impredecible para los formularios de almacén.
        } // Finaliza la inicialización del token de pedido.
        // Inicializa el aviso que la vista muestra después de una actualización.
        $mensajePedido = ''; // Mantiene limpia la primera carga de la pantalla.
        // Usa solo variantes conocidas para la alerta de Bootstrap.
        $tipoAlertaPedido = 'success'; // Confirma el cambio de estado cuando se complete.
        // Recupera y consume el resultado de una actualización previa.
        if (isset($_SESSION['resultadoPedido']) && is_array($_SESSION['resultadoPedido'])) { // Aplica el patrón POST-Redirect-GET.
            // Recupera el mensaje temporal guardado por la acción de cambio de estado.
            $mensajePedido = (string) ($_SESSION['resultadoPedido']['mensaje'] ?? ''); // Muestra el resultado cerca del encabezado.
            // Limita la clase de alerta a valores permitidos.
            $tipoAlertaPedido = in_array($_SESSION['resultadoPedido']['tipo'] ?? '', ['success', 'warning', 'danger'], true) ? (string) $_SESSION['resultadoPedido']['tipo'] : 'success'; // Evita clases controladas por una solicitud.
            // Elimina la notificación después de leerla una vez.
            unset($_SESSION['resultadoPedido']); // Evita repetir el aviso en cargas posteriores.
        } // Finaliza la recuperación del resultado temporal.
        // Recupera las tarjetas y sus productos desde el modelo de lectura.
        $pedidosPendientes = $this->pedido->listarPedidosPendientes(); // Agrupa los kilogramos por pedido.
        // Conserva los datos autenticados para el menú y el pie de la barra lateral.
        $usuarioSesion = $_SESSION['user']; // El constructor validó la sesión activa.
        // Renderiza la vista de almacén fuera de la lógica del controlador.
        require dirname(__DIR__) . '/views/pedidos/pendientes.php'; // Presenta la lista de pedidos y sus detalles colapsables.
    } // Finaliza la acción de consulta.

    // Marca un pedido abierto como realizado mediante una solicitud POST protegida.
    public function realizar(): void // Atiende index.php?c=pedido&a=realizar.
    { // Inicia la acción para cerrar un pedido preparado.
        // Impide cambiar estados mediante enlaces GET.
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') { // Solo acepta envíos desde el botón de la tarjeta.
            // Informa que la ruta requiere una solicitud de escritura.
            http_response_code(405); // Usa el estado HTTP de método no permitido.
            // Termina la solicitud sin modificar el pedido.
            exit('Método no permitido.'); // Evita cambios de estado por navegación.
        } // Finaliza la comprobación del método HTTP.
        // Recupera el token del formulario solo si el valor es texto.
        $tokenEnviado = isset($_POST['token_pedido']) && is_string($_POST['token_pedido']) ? $_POST['token_pedido'] : ''; // Rechaza arreglos u otros datos inesperados.
        // Exige coincidencia con el secreto de la sesión actual.
        if (!isset($_SESSION['tokenPedido']) || !hash_equals((string) $_SESSION['tokenPedido'], $tokenEnviado)) { // Mitiga envíos externos no autorizados.
            // Devuelve un código de acceso prohibido para tokens inválidos.
            http_response_code(403); // Indica que el formulario no es válido.
            // Detiene el flujo antes de realizar una escritura.
            exit('La solicitud no es válida. Actualiza los pedidos e inténtalo nuevamente.'); // No modifica el estado.
        } // Finaliza la validación del token CSRF.
        // Valida el ID recibido como entero positivo.
        $idPedido = filter_var($_POST['id_pedido'] ?? null, FILTER_VALIDATE_INT); // Descarta valores vacíos o manipulados.
        // Prepara el texto temporal del resultado de la operación.
        if ($idPedido === false || $idPedido < 1) { // Rechaza identificadores no válidos.
            // Registra un aviso que no revela detalles internos.
            $_SESSION['resultadoPedido'] = ['tipo' => 'danger', 'mensaje' => 'No se pudo identificar el pedido seleccionado.']; // Devuelve una notificación segura.
        } else { // Intenta la transición del pedido validado.
            // Cambia solo pedidos en estado pendiente o en preparación.
            $folioPedido = $this->pedido->obtenerFolioPedido($idPedido); // Recupera el folio de la venta y no muestra la clave interna del pedido.
            $pedidoActualizado = $this->pedido->marcarComoRealizado($idPedido); // El modelo guarda PREPARADO como estado realizado permitido por el esquema.
            // Prepara una confirmación cuando se actualizó exactamente un pedido abierto.
            if ($pedidoActualizado) { // Evita mostrar éxito para pedidos inexistentes o ya cerrados.
                // Informa que el pedido se marcó realizado y saldrá del listado pendiente.
                $_SESSION['resultadoPedido'] = ['tipo' => 'success', 'mensaje' => 'El pedido con folio #' . (int) ($folioPedido ?? $idPedido) . ' se marcó como Realizado.']; // Confirma la acción con el folio de venta.
            } else { // Atiende pedidos que ya no están abiertos o que no existen.
                // Informa que otro proceso ya cambió el estado o que el pedido no está disponible.
                $_SESSION['resultadoPedido'] = ['tipo' => 'warning', 'mensaje' => 'El pedido ya no está pendiente o no existe.']; // Evita fingir una actualización.
            } // Finaliza la preparación del resultado.
        } // Finaliza la validación y actualización del pedido.
        // Regresa al listado usando GET para evitar repetir la actualización al recargar.
        header('Location: index.php?c=pedido&a=pendientes', true, 303); // Aplica POST-Redirect-GET.
        // Detiene la solicitud después de enviar la redirección.
        exit; // Evita renderizar una segunda respuesta bajo POST.
    } // Finaliza la acción de marcar como realizado.
} // Finaliza la definición del controlador PedidoController.
