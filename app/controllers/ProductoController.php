<?php
// Activa el modo estricto de tipos para asegurar la integridad de datos del controlador de productos.
declare(strict_types=1); // Evita conversiones implícitas en precios, existencias e identificadores.

// Carga reglas comunes para límites de texto y precisión numérica del servidor.
require_once dirname(__DIR__) . '/helpers/ValidacionFormulario.php'; // Impide depender únicamente de los atributos HTML.

// Declara el controlador encargado de coordinar el catálogo comercial y los inventarios unificados.
class ProductoController // Coordina las operaciones protegidas para los roles ALMACEN y ADMINISTRADOR.
{ // Inicia la definición de la clase ProductoController.
    // Conserva la instancia del modelo de productos para operaciones transaccionales.
    private Producto $producto; // Centraliza la lógica de negocio de productos y existencias.
    // Conserva la instancia opcional del modelo de inventarios para consultas auxiliares.
    private ?Inventario $inventario; // Permite consultar existencias independientes si es necesario.

    // Restringe el acceso al controlador únicamente a sesiones activas con rol ALMACEN o ADMINISTRADOR.
    public function __construct(Producto $producto, ?Inventario $inventario = null) // Recibe dependencias inyectadas desde el enrutador.
    { // Inicia la verificación de acceso y seguridad del controlador.
        // Valida que exista sesión iniciada, datos de usuario válidos y un rol autorizado.
        $rolUsuario = $_SESSION['user']['rol'] ?? ''; // Obtiene el rol almacenado en la sesión.
        // Comprueba si la sesión no está activa o si el rol no pertenece a la lista permitida.
        if (session_status() !== PHP_SESSION_ACTIVE || !isset($_SESSION['user']) || !is_array($_SESSION['user']) || !in_array($rolUsuario, ['ALMACEN', 'ADMINISTRADOR'], true)) { // Exige estrictamente ALMACEN o ADMINISTRADOR.
            // Establece el código de respuesta HTTP 403 de acceso prohibido.
            http_response_code(403); // Notifica el rechazo formal por falta de autorización.
            // Detiene inmediatamente la ejecución con el mensaje de denegación controlado.
            exit('Acceso denegado: se requiere una sesión activa con rol ALMACEN o ADMINISTRADOR.'); // Protege el módulo contra accesos no autorizados.
        } // Finaliza la comprobación de sesión y rol permitido.
        // Asigna el modelo de productos al atributo de la clase.
        $this->producto = $producto; // Almacena la dependencia de productos.
        // Asigna el modelo de inventario al atributo de la clase.
        $this->inventario = $inventario; // Almacena la dependencia de inventario.
    } // Finaliza el constructor del controlador de productos.

    // Presenta el formulario único de alta de producto e inventario y procesa su inserción transaccional.
    public function nuevo(): void // Define la acción para registrar un nuevo producto con existencia inicial.
    { // Inicia el procesamiento del formulario de nuevo producto.
        // Inicializa el mensaje informativo de la vista en vacío.
        $mensaje = ''; // Almacena avisos de validación o confirmaciones de éxito.
        // Inicializa el tipo de alerta de Bootstrap en danger por defecto.
        $tipoAlerta = 'danger'; // Aplica estilo rojo para errores y verde para éxito.
        // Define la estructura base de los datos del formulario para conservarlos ante fallos.
        $datosFormulario = [ // Agrupa los campos de la interfaz visual de registro.
            'nombre_producto' => '', // Nombre comercial del producto cárnico.
            'categoria' => '', // Categoría comercial requerida en la interfaz.
            'unidad' => 'Kg', // Unidad de medida por defecto requerida en la interfaz.
            'tipo_corte' => '', // Tipo de corte opcional de la pieza.
            'precio_kg' => '', // Precio de venta por kilogramo.
            'existencia_inicial' => '', // Existencia física inicial en inventario.
        ]; // Finaliza la inicialización de campos del formulario.

        // Comprueba si la solicitud corresponde al envío del formulario mediante POST.
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') { // Evita procesar altas durante consultas GET.
            // Recupera y limpia el nombre del producto de espacios externos.
            $datosFormulario['nombre_producto'] = trim(ValidacionFormulario::texto($_POST['nombre_producto'] ?? null)); // Normaliza el nombre.
            // Recupera y limpia la categoría seleccionada en el formulario.
            $datosFormulario['categoria'] = trim(ValidacionFormulario::texto($_POST['categoria'] ?? null)); // Normaliza la categoría.
            // Recupera y limpia la unidad seleccionada.
            $datosFormulario['unidad'] = trim(ValidacionFormulario::texto($_POST['unidad'] ?? null)); // Normaliza la unidad de medida.
            // Recupera y limpia el tipo de corte opcional.
            $datosFormulario['tipo_corte'] = trim(ValidacionFormulario::texto($_POST['tipo_corte'] ?? null)); // Normaliza el tipo de corte.
            // Recupera el precio por kilogramo como cadena para su validación numérica.
            $datosFormulario['precio_kg'] = trim(ValidacionFormulario::texto($_POST['precio_kg'] ?? null)); // Conserva la cadena de precio.
            // Recupera la existencia inicial como cadena para validación numérica.
            $datosFormulario['existencia_inicial'] = trim(ValidacionFormulario::texto($_POST['existencia_inicial'] ?? null)); // Conserva la existencia ingresada.

            // Define los valores comerciales que el formulario ofrece como opciones cerradas.
            $categoriasPermitidas = ['Cortes de Cerdo', 'Tocinería', 'Embutidos', 'Ahumados', 'Especialidades']; // Rechaza categorías manipuladas en la solicitud.
            $unidadesPermitidas = ['Kg', 'Pza']; // Conserva las unidades que presenta la vista.
            // Valida que los campos obligatorios marcados con asterisco no estén vacíos.
            $camposObligatoriosLlenos = $datosFormulario['nombre_producto'] !== '' && $datosFormulario['categoria'] !== '' && $datosFormulario['unidad'] !== '' && $datosFormulario['precio_kg'] !== '' && $datosFormulario['existencia_inicial'] !== ''; // Exige presencia de todos los campos obligatorios.
            // Valida el nombre comercial y el corte opcional con los límites de sus columnas.
            $textoProductoValido = ValidacionFormulario::textoLibre($datosFormulario['nombre_producto'], 100, true) && ValidacionFormulario::textoLibre($datosFormulario['tipo_corte'], 50); // Rechaza controles y cadenas excesivas.
            // Valida que las selecciones recibidas formen parte de los catálogos visibles.
            $catalogosValidos = in_array($datosFormulario['categoria'], $categoriasPermitidas, true) && in_array($datosFormulario['unidad'], $unidadesPermitidas, true); // No acepta opciones inventadas por el cliente.
            // Exige precio positivo y hasta dos posiciones decimales.
            $precioValido = ValidacionFormulario::decimal($datosFormulario['precio_kg'], 2, 0.01); // Rechaza cantidades negativas, formato exponencial y decimales de más.
            // Permite inventario igual a cero y hasta tres posiciones decimales.
            $existenciaValida = ValidacionFormulario::decimal($datosFormulario['existencia_inicial'], 3, 0.0); // Rechaza cantidades negativas o con precisión no soportada.

            // Comprueba si falta algún campo obligatorio o si los valores numéricos son incorrectos.
            if (!$camposObligatoriosLlenos || !$textoProductoValido || !$catalogosValidos || !$precioValido || !$existenciaValida) { // Detiene el alta ante cualquier campo inválido.
                // Asigna el mensaje de error estándar para datos faltantes o inválidos.
                $mensaje = 'Revisa los campos: precio mayor que cero con hasta 2 decimales e inventario no negativo con hasta 3 decimales.'; // Indica las reglas numéricas principales.
                // Asigna la variante roja de peligro de Bootstrap.
                $tipoAlerta = 'danger'; // Muestra la notificación en color rojo.
            } else { // Procede con la comprobación de unicidad cuando los campos son válidos.
                // Consulta en la base de datos si ya existe un producto con el mismo nombre.
                $productoExistente = $this->producto->buscarPorNombre($datosFormulario['nombre_producto']); // Verifica unicidad en tabla producto.
                // Rechaza el registro si se detecta un producto duplicado.
                if ($productoExistente !== null) { // Alerta al almacenista sobre la duplicidad comercial.
                    // Asigna el mensaje de aviso sobre nombre duplicado según los requisitos.
                    $mensaje = 'El nombre del producto ya está registrado.'; // Notifica la colisión de nombre.
                    // Asigna la variante de peligro para resaltar el duplicado.
                    $tipoAlerta = 'danger'; // Muestra la alerta en rojo.
                } else { // Ejecuta la inserción transaccional coordinada en producto e inventario.
                    // Prepara los datos comerciales del producto para enviar al modelo.
                    $datosProducto = [ // Mapea los valores validados.
                        'nombre_producto' => $datosFormulario['nombre_producto'], // Nombre único validado.
                        'tipo_corte' => $datosFormulario['tipo_corte'] !== '' ? $datosFormulario['tipo_corte'] : null, // Corte opcional o null.
                        'precio_kg' => (float) $datosFormulario['precio_kg'], // Precio decimal positivo.
                    ]; // Finaliza el arreglo de datos del producto.
                    // Convierte la existencia inicial a valor flotante o numérico adecuado.
                    $existenciaInicial = (float) $datosFormulario['existencia_inicial']; // Normaliza la cantidad física.
                    // Ejecuta la transacción de creación coordinada en ambas tablas.
                    $registroExitoso = $this->producto->crearConInventario($datosProducto, $existenciaInicial); // Inserta en producto e inventario con commit/rollback.

                    // Evalúa si la transacción se confirmó satisfactoriamente.
                    if ($registroExitoso) { // Confirma la creación del producto y su inventario.
                        // Establece el mensaje de confirmación exitosa del registro.
                        $mensaje = 'Producto guardado con éxito'; // Informa la persistencia correcta.
                        // Asigna el estilo de éxito en verde de Bootstrap.
                        $tipoAlerta = 'success'; // Despliega la alerta verde de éxito.
                        // Reinicia los campos del formulario para permitir capturar otro producto.
                        $datosFormulario = ['nombre_producto' => '', 'categoria' => '', 'unidad' => 'Kg', 'tipo_corte' => '', 'precio_kg' => '', 'existencia_inicial' => '']; // Limpia los controles visuales.
                    } else { // Atiende el fallo reportado por el modelo o la base de datos.
                        // Establece el mensaje de error de registro según las especificaciones.
                        $mensaje = 'Error de registro'; // Comunica el fallo técnico sin exponer la base de datos.
                        // Asigna el estilo visual de peligro.
                        $tipoAlerta = 'danger'; // Muestra la alerta roja de fallo.
                    } // Finaliza la evaluación del resultado transaccional.
                } // Finaliza la comprobación de producto duplicado.
            } // Finaliza la validación de campos obligatorios.
        } // Finaliza el procesamiento del formulario POST.

        // Obtiene los datos del usuario autenticado para la cabecera y el menú de navegación.
        $usuarioSesion = $_SESSION['user']; // Permite mostrar nombre, rol y avatar del empleado.
        // Carga y renderiza la vista del formulario de nuevo producto.
        require dirname(__DIR__) . '/views/productos/nuevo.php'; // Incluye la plantilla visual en Bootstrap 5.3.
    } // Finaliza la acción de nuevo producto.

    // Presenta el buscador y formulario integrado de edición de datos comerciales e inventario.
    public function editar(): void // Define la acción para buscar y actualizar productos y existencias.
    { // Inicia la acción de edición de productos.
        // Inicializa el mensaje informativo de la vista.
        $mensaje = ''; // Almacena notificaciones de resultado.
        // Inicializa el tipo de alerta de Bootstrap.
        $tipoAlerta = 'danger'; // Define el estilo visual de las alertas.
        // Obtiene la lista completa de productos para alimentar el buscador del catálogo.
        $catalogoProductos = $this->producto->listarTodos(); // Consulta la colección con sus cantidades.
        // Inicializa la variable que contendrá los datos del producto seleccionado para edición.
        $productoSeleccionado = null; // Se poblará cuando se indique un ID válido o tras una búsqueda.

        // Detecta el identificador del producto solicitado por parámetro GET o POST.
        $idRecibido = $_GET['id'] ?? $_POST['id_producto'] ?? null; // Prioriza el ID de URL y permite el valor POST de la edición.
        $idValidado = filter_var($idRecibido, FILTER_VALIDATE_INT); // Rechaza arreglos, decimales y texto añadido al identificador.
        $idSolicitado = is_int($idValidado) && $idValidado > 0 ? $idValidado : 0; // Permite consultar únicamente IDs positivos.

        // Si se recibió un identificador, intenta cargar el producto correspondiente desde la base de datos.
        if ($idSolicitado > 0) { // Evalúa si hay una selección activa de producto.
            // Consulta el producto e inventario unificados por su identificador primario.
            $productoSeleccionado = $this->producto->buscarPorId($idSolicitado); // Realiza lectura preparada 1:1.
            // Si el identificador no existe en la base de datos, activa la alerta correspondiente.
            if ($productoSeleccionado === null) { // Detecta producto inexistente.
                // Establece el mensaje exacto documentado para producto no encontrado.
                $mensaje = 'Producto no encontrado'; // Notifica que no existe el registro.
                // Asigna el estilo de alerta de peligro.
                $tipoAlerta = 'danger'; // Muestra la alerta roja.
            } // Finaliza la comprobación de existencia.
        } // Finaliza la carga inicial por identificador.

        // Procesa la actualización de datos comerciales e inventario cuando la petición sea POST.
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && isset($_POST['accion_guardar'])) { // Atiende solo envíos del formulario de edición.
            // Recupera el identificador del producto que se intenta modificar.
            $idProductoValidado = filter_var($_POST['id_producto'] ?? null, FILTER_VALIDATE_INT); // Rechaza valores manipulados o no enteros.
            $idProducto = is_int($idProductoValidado) && $idProductoValidado > 0 ? $idProductoValidado : 0; // Solo admite identificadores positivos.
            // Recupera y limpia el nombre del producto.
            $nombreProducto = trim(ValidacionFormulario::texto($_POST['nombre_producto'] ?? null)); // Normaliza el nombre.
            // Recupera y limpia el tipo de corte opcional.
            $tipoCorte = trim(ValidacionFormulario::texto($_POST['tipo_corte'] ?? null)); // Normaliza el corte.
            // Recupera el precio por kilogramo.
            $precioKgTexto = trim(ValidacionFormulario::texto($_POST['precio_kg'] ?? null)); // Normaliza el texto de precio.
            // Recupera la cantidad de existencia física actual en inventario.
            $cantidadExistenciaTexto = trim(ValidacionFormulario::texto($_POST['cantidad_existencia'] ?? null)); // Normaliza la existencia.
            // Determina el estado del producto (1 para activo, 0 para inactivo) a partir del interruptor visual.
            $estadoValido = !isset($_POST['estado']) || (is_string($_POST['estado']) && in_array($_POST['estado'], ['1', 'on'], true)); // Acepta únicamente el valor estándar de la casilla.
            $estado = isset($_POST['estado']) && $estadoValido ? 1 : 0; // Interpreta ausencia como inactivo y valor reconocido como activo.

            // Comprueba que el producto objetivo realmente exista antes de intentar actualizarlo.
            $productoActual = $this->producto->buscarPorId($idProducto); // Verifica la existencia previa.
            // Rechaza la actualización si el producto no existe en el sistema.
            if ($idProducto <= 0 || $productoActual === null) { // Detecta identificadores inválidos o productos inexistentes.
                // Establece el mensaje de producto no encontrado.
                $mensaje = 'Producto no encontrado'; // Informa la ausencia del producto.
                // Asigna la alerta roja.
                $tipoAlerta = 'danger'; // Muestra el mensaje en estilo de peligro.
            } else { // Continúa con la validación de campos cuando el producto existe.
                // Valida que el nombre, el precio y la existencia no estén vacíos.
                $camposCompletos = $nombreProducto !== '' && $precioKgTexto !== '' && $cantidadExistenciaTexto !== ''; // Revisa campos requeridos.
                // Valida el nombre del producto y el tipo de corte opcional.
                $textosValidos = ValidacionFormulario::textoLibre($nombreProducto, 100, true) && ValidacionFormulario::textoLibre($tipoCorte, 50); // Rechaza controles y límites excedidos.
                // Valida que el precio sea positivo y tenga hasta dos decimales.
                $precioValido = ValidacionFormulario::decimal($precioKgTexto, 2, 0.01); // Rechaza negativos, notación exponencial y precisión excesiva.
                // Valida que el inventario sea no negativo y tenga hasta tres decimales.
                $existenciaValida = ValidacionFormulario::decimal($cantidadExistenciaTexto, 3, 0.0); // Permite cero y rechaza valores mal formados.

                // Si falta algún dato o los números no son válidos, cancela la actualización.
                if (!$camposCompletos || !$textosValidos || !$precioValido || !$existenciaValida || !$estadoValido) { // Revisa todas las reglas antes de guardar.
                    // Establece el mensaje de error de datos faltantes.
                    $mensaje = 'Error: datos faltantes'; // Comunica los campos incompletos.
                    // Asigna el estilo de peligro.
                    $tipoAlerta = 'danger'; // Muestra la alerta roja.
                    // Conserva los datos enviados en la estructura de edición para no perder capturas.
                    $productoSeleccionado = [ // Mantiene los datos capturados en pantalla.
                        'id_producto' => $idProducto, // Conserva ID.
                        'nombre_producto' => $nombreProducto, // Conserva nombre ingresado.
                        'tipo_corte' => $tipoCorte, // Conserva corte ingresado.
                        'precio_kg' => $precioKgTexto, // Conserva precio ingresado.
                        'cantidad_existencia' => $cantidadExistenciaTexto, // Conserva existencia ingresada.
                        'estado' => $estado, // Conserva estado seleccionado.
                    ]; // Finaliza la conservación de datos enviados.
                } else { // Valida que el nombre no pertenezca a otro producto diferente.
                    // Consulta si otro producto ya utiliza el mismo nombre comercial.
                    $nombreDuplicado = $this->producto->buscarPorNombreExcluyendoId($nombreProducto, $idProducto); // Excluye el propio ID.
                    // Si el nombre ya está ocupado por otro registro, emite la advertencia.
                    if ($nombreDuplicado !== null) { // Detecta conflicto de nombre comercial.
                        // Establece el aviso de producto duplicado.
                        $mensaje = 'El nombre del producto ya está registrado.'; // Notifica la colisión.
                        // Asigna el estilo de alerta de peligro.
                        $tipoAlerta = 'danger'; // Muestra la alerta roja.
                    } else { // Ejecuta la actualización transaccional en ambas tablas.
                        // Prepara los datos comerciales del producto.
                        $datosActualizarProducto = [ // Mapea los campos de producto.
                            'nombre_producto' => $nombreProducto, // Nombre validado.
                            'tipo_corte' => $tipoCorte !== '' ? $tipoCorte : null, // Corte opcional o null.
                            'precio_kg' => (float) $precioKgTexto, // Precio decimal positivo.
                            'estado' => $estado, // Estado activo (1) o inactivo (0).
                        ]; // Finaliza los datos de producto.
                        // Normaliza la cantidad acumulada de inventario.
                        $cantidadActual = (float) $cantidadExistenciaTexto; // Convierte a flotante la existencia.
                        // Llama al método transaccional que actualiza producto e inventario conjuntamente.
                        $actualizacionExitosa = $this->producto->actualizarConInventario($idProducto, $datosActualizarProducto, $cantidadActual); // Ejecuta ambas sentencias bajo transacción.

                        // Evalúa el resultado de la transacción coordinada.
                        if ($actualizacionExitosa) { // Confirma la persistencia exitosa.
                            // Establece el mensaje de éxito de actualización documentado.
                            $mensaje = 'Datos actualizados'; // Notifica que se guardaron los cambios.
                            // Asigna el estilo de éxito en verde de Bootstrap.
                            $tipoAlerta = 'success'; // Muestra la alerta verde.
                            // Recarga el producto actualizado desde la base de datos.
                            $productoSeleccionado = $this->producto->buscarPorId($idProducto); // Obtiene el estado fresco.
                            // Refresca la lista completa de productos para reflejar los cambios en el buscador.
                            $catalogoProductos = $this->producto->listarTodos(); // Actualiza el catálogo en memoria.
                        } else { // Atiende el fallo de persistencia reportado por PDO.
                            // Establece el mensaje de error de registro según los requisitos.
                            $mensaje = 'Error de registro'; // Notifica el fallo del SGBD.
                            // Asigna el estilo de peligro.
                            $tipoAlerta = 'danger'; // Muestra la alerta roja.
                        } // Finaliza la evaluación de persistencia.
                    } // Finaliza la comprobación de nombre duplicado.
                } // Finaliza la validación de campos obligatorios.
            } // Finaliza la comprobación de existencia previa.
        } // Finaliza el procesamiento del formulario de edición POST.

        // Obtiene los datos del usuario autenticado para la presentación de la plantilla.
        $usuarioSesion = $_SESSION['user']; // Proporciona datos de sesión.
        // Renderiza la vista de edición e inventario de productos.
        require dirname(__DIR__) . '/views/productos/editar.php'; // Incluye la vista de edición con buscador.
    } // Finaliza la acción de edición de productos.
} // Finaliza la definición de ProductoController.
