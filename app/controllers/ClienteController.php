<?php
// Activa la comprobación estricta de tipos para las acciones del módulo de clientes.
declare(strict_types=1); // Reduce conversiones inesperadas al validar los datos recibidos.

// Carga las reglas comunes para validar nombres, teléfonos y texto libre.
require_once dirname(__DIR__) . '/helpers/ValidacionFormulario.php'; // Reutiliza validaciones coherentes en el servidor.

// Declara el controlador que protege y coordina las operaciones de clientes.
class ClienteController // Separa el flujo HTTP de las consultas PDO del modelo.
{ // Inicia la definición del controlador.
    // Conserva el modelo de clientes inyectado desde el enrutador.
    private Cliente $cliente; // Centraliza las operaciones de consulta y registro.

    // Comprueba que exista una sesión permitida antes de aceptar acciones.
    public function __construct(Cliente $cliente) // Recibe el modelo con PDO ya configurado.
    { // Inicia la verificación de autorización.
        // Lee el rol únicamente desde la identidad guardada en la sesión autenticada.
        $rolUsuario = $_SESSION['user']['rol'] ?? ''; // No confía en parámetros enviados por el navegador.
        // Rechaza sesiones ausentes o roles que no pueden gestionar clientes.
        if (session_status() !== PHP_SESSION_ACTIVE || !isset($_SESSION['user']) || !is_array($_SESSION['user']) || !in_array($rolUsuario, ['VENTAS', 'ADMINISTRADOR'], true)) { // Autoriza por rol fijo a ventas y administración.
            // Comunica que el usuario no tiene acceso a este módulo.
            http_response_code(403); // Usa el código HTTP estándar de acceso prohibido.
            // Termina la solicitud sin renderizar datos del módulo.
            exit('Acceso denegado: se requiere una sesión activa con rol VENTAS o ADMINISTRADOR.'); // Evita continuar sin autorización.
        } // Finaliza la comprobación de sesión y rol.
        // Guarda el modelo después de completar la comprobación de acceso.
        $this->cliente = $cliente; // Deja disponible la capa de persistencia.
    } // Finaliza el constructor del controlador.

    // Presenta el formulario de alta y procesa los nuevos perfiles enviados por POST.
    public function registro(): void // Atiende la ruta index.php?c=cliente&a=registro.
    { // Inicia la acción exclusiva de registro.
        // Inicializa los campos de texto para mostrar el formulario vacío.
        $datosFormulario = ['nombre_razonsocial' => '', 'telefono' => '', 'calle' => 'N/A', 'colonia' => 'N/A', 'codigo_postal' => '', 'ciudad' => 'N/A']; // Ofrece N/A como valor predeterminado para datos de domicilio desconocidos.
        // Inicializa el mensaje visible para validaciones y confirmaciones.
        $mensaje = ''; // Evita mostrar alertas al cargar la pantalla por primera vez.
        // Define la variante inicial de alerta como error.
        $tipoAlerta = 'danger'; // Limita las clases dinámicas de Bootstrap.
        // Inicializa los mensajes específicos que se mostrarán junto a cada campo.
        $erroresCampos = []; // Permite corregir cada entrada sin perder los demás valores.
        // Procesa los datos únicamente cuando el método de solicitud es POST.
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') { // Evita escribir en la base al consultar el formulario.
            // Recupera el nombre obligatorio solo si se recibió como texto.
            $datosFormulario['nombre_razonsocial'] = isset($_POST['nombre_razonsocial']) && is_string($_POST['nombre_razonsocial']) ? trim($_POST['nombre_razonsocial']) : ''; // Normaliza espacios externos.
            // Recupera el teléfono obligatorio como cadena para conservar ceros iniciales.
            $datosFormulario['telefono'] = isset($_POST['telefono']) && is_string($_POST['telefono']) ? trim($_POST['telefono']) : ''; // Mantiene el teléfono como texto.
            // Recupera la calle obligatoria desde el formulario.
            $datosFormulario['calle'] = isset($_POST['calle']) && is_string($_POST['calle']) ? trim($_POST['calle']) : ''; // Rechaza el dato ausente o de tipo inesperado.
            // Recupera la colonia obligatoria desde el formulario.
            $datosFormulario['colonia'] = isset($_POST['colonia']) && is_string($_POST['colonia']) ? trim($_POST['colonia']) : ''; // Normaliza el dato para validarlo.
            // Recupera el código postal como texto para conservar ceros iniciales.
            $datosFormulario['codigo_postal'] = isset($_POST['codigo_postal']) && is_string($_POST['codigo_postal']) ? trim($_POST['codigo_postal']) : ''; // No convierte el código postal a número.
            // Recupera la ciudad obligatoria desde el formulario.
            $datosFormulario['ciudad'] = isset($_POST['ciudad']) && is_string($_POST['ciudad']) ? trim($_POST['ciudad']) : ''; // Rechaza el dato ausente o de tipo inesperado.
            // Valida el nombre obligatorio, su longitud máxima y los caracteres de control.
            if ($datosFormulario['nombre_razonsocial'] === '') { // Distingue el campo vacío de un formato incorrecto.
                $erroresCampos['nombre_razonsocial'] = 'Ingresa el nombre o razón social.'; // Explica el dato que falta.
            } elseif (!ValidacionFormulario::textoLibre($datosFormulario['nombre_razonsocial'], 100, true)) { // Usa el límite documentado para nombre comercial.
                $erroresCampos['nombre_razonsocial'] = 'Usa hasta 100 caracteres y evita caracteres de control.'; // Describe las reglas del campo.
            } // Finaliza la validación del nombre.
            // Exige teléfono y valida entre diez y quince dígitos sin símbolos.
            if ($datosFormulario['telefono'] === '') { // Detecta la omisión del teléfono requerido.
                $erroresCampos['telefono'] = 'Ingresa el teléfono del cliente.'; // Señala el campo que falta.
            } elseif (!ValidacionFormulario::telefono($datosFormulario['telefono'])) { // Reutiliza la regla estricta de teléfono.
                $erroresCampos['telefono'] = 'Usa únicamente de 10 a 15 dígitos.'; // Informa el patrón esperado.
            } // Finaliza la validación del teléfono.
            // Exige un código postal compuesto exactamente por cinco dígitos.
            if ($datosFormulario['codigo_postal'] === '') { // Hace obligatorio el código postal.
                $erroresCampos['codigo_postal'] = 'Ingresa el código postal.'; // Señala el campo requerido.
            } elseif (preg_match('/^[0-9]{5}$/D', $datosFormulario['codigo_postal']) !== 1) { // Rechaza letras, separadores y longitudes diferentes.
                $erroresCampos['codigo_postal'] = 'Debe contener exactamente 5 dígitos, sin letras ni símbolos.'; // Describe la longitud y los caracteres permitidos.
            } // Finaliza la validación del código postal.
            // Comprueba que la calle obligatoria respete su longitud y no incluya caracteres de control.
            if (!ValidacionFormulario::textoLibre($datosFormulario['calle'], 255, true)) { // Exige una calle o el valor N/A cuando no exista.
                $erroresCampos['calle'] = $datosFormulario['calle'] === '' ? 'Ingresa la calle o escribe N/A si no existe.' : 'Usa hasta 255 caracteres y evita caracteres de control.'; // Explica el dato requerido o el límite.
            } // Finaliza la validación de la calle.
            // Comprueba que la colonia obligatoria respete su longitud y contenido.
            if (!ValidacionFormulario::textoLibre($datosFormulario['colonia'], 255, true)) { // Exige una colonia o N/A cuando no se conozca.
                $erroresCampos['colonia'] = $datosFormulario['colonia'] === '' ? 'Ingresa la colonia o escribe N/A si no existe.' : 'Usa hasta 255 caracteres y evita caracteres de control.'; // Explica el dato requerido o el límite.
            } // Finaliza la validación de la colonia.
            // Comprueba que la ciudad obligatoria respete el límite de su columna.
            if (!ValidacionFormulario::textoLibre($datosFormulario['ciudad'], 100, true)) { // Exige una ciudad o N/A cuando no se conozca.
                $erroresCampos['ciudad'] = $datosFormulario['ciudad'] === '' ? 'Ingresa la ciudad o escribe N/A si no existe.' : 'Usa hasta 100 caracteres y evita caracteres de control.'; // Explica el dato requerido o el límite.
            } // Finaliza la validación de la ciudad.

            // Comprueba por separado si falta cualquiera de los campos obligatorios.
            $camposObligatoriosCompletos = $datosFormulario['nombre_razonsocial'] !== '' && $datosFormulario['telefono'] !== '' && $datosFormulario['calle'] !== '' && $datosFormulario['colonia'] !== '' && $datosFormulario['codigo_postal'] !== '' && $datosFormulario['ciudad'] !== ''; // Comprueba que todos los campos requeridos tengan contenido.
            // Deniega el alta con el mensaje existente cuando falta un campo obligatorio.
            if (!$camposObligatoriosCompletos) { // Mantiene el mensaje general requerido por el módulo.
                $mensaje = 'Error: datos faltantes'; // Devuelve exactamente el aviso de datos obligatorios.
                $tipoAlerta = 'danger'; // Presenta el error en rojo.
            // Detiene el registro si uno o más campos no cumplen sus reglas.
            } elseif ($erroresCampos !== []) { // Evita consultar duplicados con entradas mal formadas.
                $mensaje = 'Corrige los campos indicados antes de guardar.'; // Resume los errores ya asociados a cada campo.
                $tipoAlerta = 'danger'; // Comunica la validación fallida.
            } else { // Comprueba duplicados y registra solo cuando todos los campos son válidos.
                // Protege la consulta y escritura frente a excepciones de PDO.
                try { // Maneja de forma controlada los errores de persistencia.
                    // Busca coincidencia exacta del nombre o teléfono antes de insertar.
                    if ($this->cliente->comprobarDuplicado($datosFormulario['nombre_razonsocial'], $datosFormulario['telefono'])) { // Aplica la regla de duplicidad solicitada.
                        $mensaje = 'El cliente ya existe.'; // Informa que alguno de los campos ya está registrado.
                        $tipoAlerta = 'danger'; // Presenta el registro duplicado como un resultado negativo.
                    } else { // Inserta los datos únicamente cuando no hay coincidencias.
                        // Envía el registro validado al modelo PDO.
                        if ($this->cliente->registrar($datosFormulario)) { // Persiste los campos permitidos de cliente.
                            $mensaje = 'Cliente registrado correctamente.'; // Confirma la operación al usuario.
                            $tipoAlerta = 'success'; // Muestra la confirmación con estilo positivo.
                            $datosFormulario = ['nombre_razonsocial' => '', 'telefono' => '', 'calle' => 'N/A', 'colonia' => 'N/A', 'codigo_postal' => '', 'ciudad' => 'N/A']; // Restablece N/A en domicilio para el siguiente registro.
                            $erroresCampos = []; // Limpia los avisos tras registrar correctamente.
                        } else { // Maneja una ejecución de inserción sin éxito.
                            $mensaje = 'Error de registro'; // Evita exponer detalles internos de PDO.
                            $tipoAlerta = 'danger'; // Presenta el fallo con alerta roja.
                        } // Finaliza la interpretación de la inserción.
                    } // Finaliza la bifurcación de duplicidad.
                } catch (PDOException $excepcion) { // Captura errores de base de datos sin revelar SQL.
                    error_log('Falló una operación de registro del módulo de clientes.'); // Registra un mensaje genérico para diagnóstico.
                    $mensaje = 'Error de registro'; // Presenta una respuesta segura y entendible.
                    $tipoAlerta = 'danger'; // Identifica la operación como fallida.
                } // Finaliza el manejo de excepciones PDO.
            } // Finaliza la validación de datos enviados.
        } // Finaliza el procesamiento POST.

        // Conserva la identidad autenticada que el menú muestra en su pie.
        $usuarioSesion = $_SESSION['user']; // La sesión fue validada por el constructor.
        // Incluye la plantilla del formulario independiente de alta.
        require dirname(__DIR__) . '/views/clientes/registro.php'; // Mantiene el marcado fuera de la lógica del controlador.
    } // Finaliza la acción de registro.

    // Presenta la tabla de clientes y permite filtrar por nombre o teléfono.
    public function consulta(): void // Atiende la ruta index.php?c=cliente&a=consulta.
    { // Inicia la acción separada de consulta.
        // Conserva el término GET solo cuando el navegador envía texto plano.
        $terminoBusqueda = isset($_GET['buscar']) && is_string($_GET['buscar']) ? trim($_GET['buscar']) : ''; // Evita procesar arreglos manipulados.
        // Inicializa el aviso que la vista puede mostrar por ausencia de coincidencias.
        $mensaje = ''; // No muestra alerta hasta que una búsqueda no tenga resultados.
        // Define la variante Bootstrap por omisión para posibles avisos.
        $tipoAlerta = 'danger'; // Reserva el rojo para cualquier resultado de consulta sin coincidencias.
        // Recupera todos los registros para que el buscador pueda filtrarlos al escribir.
        $clientes = $this->cliente->listarTodos(); // Mantiene disponibles todas las filas cuando se limpia el filtro.
        // Lee el identificador opcional del cliente que la persona desea usar en una venta.
        $idSeleccionado = filter_var($_GET['seleccionar'] ?? null, FILTER_VALIDATE_INT); // Rechaza valores que no sean identificadores enteros.
        // Inicializa el detalle hasta confirmar que la selección pertenece al listado real.
        $clienteSeleccionado = null; // Impide confiar en datos completos enviados por el navegador.
        // Busca el registro seleccionado dentro de los clientes leídos desde la base de datos.
        if ($idSeleccionado !== false && $idSeleccionado !== null && $idSeleccionado > 0) { // Solo procesa identificadores positivos.
            // Recorre las filas existentes para encontrar el cliente solicitado.
            foreach ($clientes as $clienteDisponible) { // Reutiliza los datos PDO ya consultados para esta vista.
                // Compara el identificador como entero para evitar coincidencias textuales ambiguas.
                if ((int) ($clienteDisponible['id_cliente'] ?? 0) === $idSeleccionado) { // Acepta únicamente clientes registrados.
                    // Conserva todos los datos que se mostrarán al pie de la consulta.
                    $clienteSeleccionado = $clienteDisponible; // La vista escapará cada texto antes de imprimirlo.
                    // Detiene la búsqueda tras encontrar una única fila.
                    break; // Evita recorrer el resto del listado sin necesidad.
                } // Finaliza la comparación del identificador.
            } // Finaliza la búsqueda dentro del listado real.
        } // Finaliza la lectura opcional de selección.
        // Conserva la identidad autenticada para el menú lateral de la vista.
        $usuarioSesion = $_SESSION['user']; // El constructor ya comprobó la sesión y el rol.
        // Renderiza la vista independiente de consulta de clientes.
        require dirname(__DIR__) . '/views/clientes/consulta.php'; // Separa la tabla de consulta del formulario de registro.
    } // Finaliza la acción de consulta.
} // Finaliza la definición de ClienteController.
