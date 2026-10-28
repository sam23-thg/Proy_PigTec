<?php
// Activa la comprobación estricta de tipos para el procesamiento de ventas.
declare(strict_types=1); // Reduce conversiones inesperadas en identificadores y datos del formulario.

// Declara el controlador que autoriza y coordina la pantalla de venta.
class VentaController // Mantiene el flujo de venta separado de sus modelos y plantilla.
{ // Inicia la definición del controlador.
    // Conserva el modelo que registra las notas y sus artículos.
    private Venta $venta; // Centraliza la persistencia transaccional.
    // Conserva el modelo que consulta clientes para asociar la nota.
    private Cliente $cliente; // Reutiliza el listado existente de clientes.
    // Conserva el modelo que consulta productos activos con su precio actual.
    private Producto $producto; // Solo utiliza lecturas del catálogo y no modifica inventario.

    // Restringe el módulo de ventas a usuarios con rol VENTAS o ADMINISTRADOR.
    public function __construct(Venta $venta, Cliente $cliente, Producto $producto) // Recibe los modelos conectados al mismo PDO.
    { // Inicia la verificación del rol autenticado.
        // Lee el rol exclusivamente desde la sesión creada por autenticación.
        $rolUsuario = $_SESSION['user']['rol'] ?? ''; // No confía en parámetros de la solicitud.
        // Rechaza visitas sin sesión y roles ajenos a ventas.
        if (session_status() !== PHP_SESSION_ACTIVE || !isset($_SESSION['user']) || !is_array($_SESSION['user']) || !in_array($rolUsuario, ['VENTAS', 'ADMINISTRADOR'], true)) { // Limita el acceso de escritura.
            // Comunica formalmente que el rol no tiene permiso para vender.
            http_response_code(403); // Devuelve el estado HTTP de acceso prohibido.
            // Detiene la solicitud antes de consultar clientes o productos.
            exit('Acceso denegado: se requiere una sesión activa con rol VENTAS o ADMINISTRADOR.'); // Evita continuar sin autorización.
        } // Finaliza la comprobación de sesión y rol.
        // Guarda el modelo transaccional una vez autorizada la solicitud.
        $this->venta = $venta; // Permite registrar la venta y sus tickets.
        // Guarda el modelo de clientes para mostrar y validar al comprador.
        $this->cliente = $cliente; // Reutiliza el catálogo existente de clientes.
        // Guarda el modelo de productos para obtener precio, nombre y stock mostrado.
        $this->producto = $producto; // Ejecuta únicamente consultas de lectura al catálogo.
    } // Finaliza la inicialización autorizada del controlador.

    // Presenta el punto de venta y procesa la nota enviada mediante POST.
    public function nueva(): void // Atiende la ruta index.php?c=venta&a=nueva.
    { // Mantiene la venta local vinculada de forma fija a Público General.
        $this->mostrarFormulario(false); // Reutiliza el mismo flujo transaccional del formulario.
    } // Finaliza la acción de venta local.

    // Presenta el mismo formulario fijado a un cliente elegido desde su consulta.
    public function nuevaCliente(): void // Atiende la ruta index.php?c=venta&a=nueva-cliente.
    { // Inicia la venta asociada a un cliente específico.
        $this->mostrarFormulario(true); // Reutiliza el carrito y el guardado transaccional existente.
    } // Finaliza la acción de venta específica.

    // Prepara el formulario compartido y procesa la venta para el tipo de cliente indicado.
    private function mostrarFormulario(bool $ventaClienteEspecifico): void // Separa el modo de cliente sin duplicar la lógica del carrito.
    { // Inicia la preparación del formulario y de sus mensajes.
        // Inicializa la notificación que la vista puede mostrar después del envío.
        $mensaje = ''; // Mantiene la pantalla limpia en la primera carga.
        // Define una variante de alerta segura para Bootstrap.
        $tipoAlerta = 'danger'; // Se cambia a success para una confirmación posterior.
        // Conserva los valores de cliente y pago si el servidor rechaza el envío.
        $datosFormulario = ['id_cliente' => '', 'metodo_pago' => 'EFECTIVO']; // Prepara efectivo como pago inicial y los selectores de la interfaz.
        // Prepara mensajes independientes para cada control visible del formulario.
        $erroresCampos = ['id_cliente' => '', 'metodo_pago' => '', 'buscar_producto' => '', 'cantidad_producto' => '', 'productos' => '']; // Mantiene cada error junto a su campo.
        // Prepara mensajes por artículo para los errores que reporte el trigger de inventario.
        $erroresProductos = []; // Asocia el fallo de stock con la cantidad del producto correspondiente.
        // Inicializa el carrito reconstruido para conservarlo si una validación falla.
        $carritoVista = []; // No pierde artículos cuando el trigger rechaza una cantidad.
        // Carga una sola vez los clientes que servirán para resolver y validar el comprador.
        $clientesVista = $this->cliente->listarTodos(); // Reutiliza estas filas para el modo local y el específico.
        // Inicializa el cliente fijo que recibirá la venta en el modo actual.
        $clienteVentaSeleccionado = null; // Solo toma un cliente encontrado en la lista consultada.
        // Inicializa el identificador que el servidor aceptará en este formulario.
        $idClienteFijado = ''; // Impide aceptar cambios de cliente enviados desde el navegador.
        // Resuelve qué cliente se usará según el modo de venta solicitado.
        if ($ventaClienteEspecifico) { // Atiende el acceso que proviene de la ficha de un cliente.
            // Valida el identificador recibido en la URL de la acción específica.
            $idClienteRuta = filter_var($_GET['id_cliente'] ?? null, FILTER_VALIDATE_INT); // Rechaza IDs con formato incorrecto.
            // Busca el ID en la lista de clientes reales.
            foreach ($clientesVista as $clienteDisponible) { // Evita confiar en el identificador de la URL sin comprobarlo.
                // Compara el ID solicitado con el cliente registrado.
                if ($idClienteRuta !== false && $idClienteRuta !== null && $idClienteRuta > 0 && (int) ($clienteDisponible['id_cliente'] ?? 0) === $idClienteRuta) { // Acepta solo clientes existentes.
                    // Conserva el perfil completo para la ficha informativa dentro de la venta.
                    $clienteVentaSeleccionado = $clienteDisponible; // La vista escapará todos sus valores.
                    // Fija el ID para el formulario y la validación del POST.
                    $idClienteFijado = (string) $idClienteRuta; // El navegador no podrá sustituirlo por otro cliente.
                    // Detiene la búsqueda tras confirmar la cuenta del cliente.
                    break; // Evita recorrer filas adicionales.
                } // Finaliza la comparación de cliente específico.
            } // Finaliza la búsqueda de cliente específico.
            // Evita presentar o procesar una venta para una cuenta que no existe.
            if ($clienteVentaSeleccionado === null) { // Comprueba que la URL señale un cliente registrado.
                http_response_code(404); // Indica que el cliente solicitado no existe.
                exit('No se encontró el cliente seleccionado para la venta.'); // Detiene el flujo antes de crear una nota.
            } // Finaliza la validación del cliente específico.
        } else { // Mantiene la venta local fija al perfil de mostrador.
            // Conserva una coincidencia simple por si el nombre exacto no está configurado.
            $idClientePublicoAlternativo = null; // Guarda únicamente un ID que provenga de la base.
            // Recorre los clientes reales para encontrar Público General.
            foreach ($clientesVista as $clienteDisponible) { // No crea ni modifica perfiles al preparar el formulario.
                // Normaliza el nombre para comparar la etiqueta del perfil local.
                $nombreClienteDisponible = trim((string) ($clienteDisponible['nombre_razonsocial'] ?? '')); // Usa el texto obtenido desde PDO.
                // Prioriza el nombre de mostrador indicado para ventas locales.
                if (preg_match('/^p[uú]blico\s+general\s*\/\s*venta\s+mostrador$/iu', $nombreClienteDisponible) === 1) { // Reconoce variaciones con acento.
                    // Fija el ID existente de Público General / Venta Mostrador.
                    $idClienteFijado = (string) $clienteDisponible['id_cliente']; // Mantiene el cliente inalterable durante la venta local.
                    // Conserva la información que verá el vendedor en el formulario.
                    $clienteVentaSeleccionado = $clienteDisponible; // Presenta la identidad del cliente de mostrador.
                    // Termina tras encontrar el perfil preferido.
                    break; // Evita sustituir la coincidencia exacta.
                } // Finaliza la búsqueda del perfil exacto de mostrador.
                // Recuerda una coincidencia amplia para bases con un nombre simplificado.
                if ($idClientePublicoAlternativo === null && preg_match('/p[uú]blico\s+general/iu', $nombreClienteDisponible) === 1) { // Acepta el perfil general como alternativa local.
                    // Conserva el ID y la fila real para usarla si no aparece el nombre preferido.
                    $idClientePublicoAlternativo = $clienteDisponible; // No depende de un ID fijo en el código.
                } // Finaliza la búsqueda de coincidencia alternativa.
            } // Finaliza la búsqueda de Público General.
            // Usa el perfil alternativo únicamente cuando falta Venta Mostrador.
            if ($clienteVentaSeleccionado === null && $idClientePublicoAlternativo !== null) { // Evita reemplazar una coincidencia exacta.
                // Fija el identificador alternativo existente.
                $idClienteFijado = (string) $idClientePublicoAlternativo['id_cliente']; // Mantiene el registro sin cambios.
                // Conserva la fila para presentarla en la venta.
                $clienteVentaSeleccionado = $idClientePublicoAlternativo; // La selección local queda visible.
            } // Finaliza la alternativa de Público General.
            // Explica la configuración faltante si no existe un perfil para ventas locales.
            if ($clienteVentaSeleccionado === null) { // No inventa ni modifica clientes para iniciar una venta.
                $mensaje = 'No se encontró el cliente Público General / Venta Mostrador. Regístralo para habilitar ventas locales.'; // Indica por qué no hay cliente fijo disponible.
                $tipoAlerta = 'danger'; // Presenta la falta de configuración como un resultado negativo.
                $erroresCampos['id_cliente'] = 'No está disponible el perfil de Público General.'; // Asocia el aviso al bloque de cliente.
            } // Finaliza la notificación de perfil local faltante.
        } // Finaliza la resolución del cliente fijo.
        // Mantiene el identificador aprobado como cliente inicial del formulario.
        $datosFormulario['id_cliente'] = $idClienteFijado; // Nunca permite cambiarlo en la pantalla de venta.

        // Recupera y consume una confirmación almacenada antes de una redirección GET.
        if (isset($_SESSION['resultadoVenta']) && is_array($_SESSION['resultadoVenta'])) { // Aplica el patrón POST-Redirect-GET tras guardar.
            // Recupera el texto de confirmación o error guardado en sesión.
            $mensaje = (string) ($_SESSION['resultadoVenta']['mensaje'] ?? ''); // Conserva el resultado de la operación previa.
            // Limita la clase de alerta a opciones Bootstrap conocidas.
            $tipoAlerta = ($_SESSION['resultadoVenta']['tipo'] ?? '') === 'success' ? 'success' : 'danger'; // Muestra en verde solo confirmaciones y en rojo cualquier fallo.
            // Elimina el mensaje para que no vuelva a mostrarse en otra visita.
            unset($_SESSION['resultadoVenta']); // Consume la notificación temporal.
        } // Finaliza la lectura de mensajes de sesión.

        // Procesa los datos del formulario solo cuando la solicitud usa POST.
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') { // Evita crear ventas al consultar la pantalla.
            // Restablece el cliente fijado por el servidor y descarta cualquier sustitución del navegador.
            $datosFormulario['id_cliente'] = $idClienteFijado; // Conserva Público General o el cliente específico según la ruta autorizada.
            // Recupera el método de pago como texto plano.
            $datosFormulario['metodo_pago'] = isset($_POST['metodo_pago']) && is_string($_POST['metodo_pago']) ? trim($_POST['metodo_pago']) : ''; // Prepara la validación del ENUM.
            // Carga clientes y productos para validar las selecciones contra datos de base.
            $clientesDisponibles = $this->cliente->listarTodos(); // Obtiene solo clientes registrados.
            // Recupera productos activos con existencias usando la consulta de catálogo existente.
            $productosDisponibles = $this->producto->obtenerCatalogoConStock(); // No actualiza ni descuenta inventario.
            // Construye un índice de productos permitidos para validar los IDs recibidos.
            $productosPorId = []; // Facilita la búsqueda de cada producto sin confiar en el navegador.
            // Recorre el catálogo activo para indexarlo por identificador numérico.
            foreach ($productosDisponibles as $productoDisponible) { // Usa solo los artículos devueltos por el modelo.
                // Guarda el producto bajo su clave primaria entera.
                $productosPorId[(int) $productoDisponible['id_producto']] = $productoDisponible; // Se reutiliza para mostrar errores y preservar el carrito.
            } // Finaliza el índice de productos activos.

            // Normaliza la selección del cliente como entero positivo.
            $idCliente = filter_var($datosFormulario['id_cliente'], FILTER_VALIDATE_INT); // Rechaza IDs vacíos o con caracteres ajenos.
            // Crea una lista numérica de clientes que el servidor sí conoce.
            $idsClientesDisponibles = array_map(static fn (array $clienteDisponible): int => (int) $clienteDisponible['id_cliente'], $clientesDisponibles); // Evita confiar en la lista HTML.
            // Confirma que el cliente elegido pertenezca a los resultados consultados.
            $clienteValido = $idCliente !== false && in_array($idCliente, $idsClientesDisponibles, true); // Impide enlazar una nota a un cliente inexistente.
            // Asigna el mensaje concreto al selector de cliente cuando no pasa la validación del servidor.
            if (!$clienteValido) { // Comprueba tanto el formato como la existencia del cliente.
                $erroresCampos['id_cliente'] = 'Selecciona un cliente válido de la lista.'; // Muestra el error junto al selector.
            } // Finaliza la validación individual del cliente.
            // Comprueba que el método de pago pertenezca al ENUM definido en la base.
            $metodoPagoValido = $datosFormulario['metodo_pago'] === 'EFECTIVO'; // Acepta únicamente el método habilitado actualmente.
            // Asigna el mensaje concreto al selector de pago cuando su valor no está permitido.
            if (!$metodoPagoValido) { // Comprueba que se haya elegido uno de los métodos admitidos.
                $erroresCampos['metodo_pago'] = 'Selecciona un método de pago válido.'; // Muestra el error junto al selector.
            } // Finaliza la validación individual del método de pago.
            // Recupera el detalle enviado como arreglo para rechazar datos con estructura incorrecta.
            $productosRecibidos = isset($_POST['productos']) && is_array($_POST['productos']) ? $_POST['productos'] : []; // Solo acepta el formato de carrito esperado.
            // Inicializa los artículos normalizados que recibirá el modelo.
            $productosDetalle = []; // Contendrá únicamente IDs y cantidades validadas.
            // Indica si todas las líneas recibidas cumplen las reglas de captura.
            $detalleValido = $productosRecibidos !== []; // Exige al menos un producto para crear la nota.
            // Informa bajo la tabla cuando la solicitud no contiene productos.
            if ($productosRecibidos === []) { // Revisa el requisito mínimo del detalle de venta.
                $erroresCampos['productos'] = 'Agrega al menos un producto antes de confirmar la venta.'; // Vincula el error con el carrito.
            } // Finaliza la validación inicial del carrito.

            // Revisa cada artículo recibido y reconstruye datos confiables para la vista.
            foreach ($productosRecibidos as $idProductoRecibido => $datosArticulo) { // Lee el identificador desde la clave del arreglo POST.
                // Convierte la clave a un identificador seguro antes de asociar errores.
                $idProductoLinea = filter_var((string) $idProductoRecibido, FILTER_VALIDATE_INT); // Permite mostrar errores solo para productos identificables.
                // Valida que la línea sea un arreglo con la cantidad esperada.
                if (!is_array($datosArticulo) || !isset($datosArticulo['cantidad_kg']) || !is_scalar($datosArticulo['cantidad_kg'])) { // Rechaza estructuras incompletas.
                    // Marca el detalle como inválido y continúa revisando otras líneas.
                    $detalleValido = false; // Evita insertar el carrito incompleto.
                    // Asocia el error a la línea cuando su identificador corresponde a un producto del catálogo.
                    if ($idProductoLinea !== false && isset($productosPorId[$idProductoLinea])) { // Evita guardar claves manipuladas o inexistentes.
                        $erroresProductos[$idProductoLinea] = 'Revisa la cantidad de este producto.'; // Señala el dato incorrecto en la tabla.
                    } else { // Atiende una estructura imposible de asociar con un producto conocido.
                        $erroresCampos['productos'] = 'Revisa los productos y sus cantidades antes de confirmar.'; // Muestra un error en el detalle.
                    } // Finaliza la asociación del error de estructura.
                    // Omite una línea sin cantidad válida.
                    continue; // No intenta convertir datos manipulados.
                } // Finaliza la comprobación estructural del artículo.

                // Convierte la clave recibida a un identificador entero.
                $idProducto = $idProductoLinea; // Reutiliza el identificador ya validado.
                // Convierte la cantidad escalar a una cadena para validar sus decimales.
                $cantidadTexto = trim((string) $datosArticulo['cantidad_kg']); // Mantiene control sobre el formato decimal.
                // Comprueba que la cantidad tenga máximo tres posiciones decimales.
                $cantidadValida = preg_match('/^\d{1,7}(?:\.\d{1,3})?$/D', $cantidadTexto) === 1 && (float) $cantidadTexto > 0; // Respeta DECIMAL(10,3) y requiere un valor positivo.

                // Rechaza productos inactivos, sin inventario registrado o cantidades no válidas.
                if ($idProducto === false || !isset($productosPorId[$idProducto]) || !$cantidadValida) { // Valida producto y cantidad con el catálogo del servidor.
                    // Marca el carrito para evitar que una parte válida se registre por separado.
                    $detalleValido = false; // La nota debe procesarse completa o no registrarse.
                    // Asocia los errores detectables a la cantidad de su producto.
                    if ($idProducto !== false && isset($productosPorId[$idProducto])) { // Confirma que el producto esté en el catálogo vigente.
                        $erroresProductos[$idProducto] = !$cantidadValida ? 'Indica una cantidad mayor que cero con máximo tres decimales.' : 'El producto ya no está disponible.'; // Explica qué debe corregirse.
                        $erroresCampos['productos'] = 'Revisa la cantidad del producto antes de confirmar la venta.'; // Muestra el error incluso si la línea inválida no se reconstruye.
                    } else { // Atiende un identificador inválido o un producto no disponible.
                        $erroresCampos['productos'] = 'Uno de los productos ya no está disponible; actualiza el carrito.'; // Muestra la incidencia en el detalle.
                    } // Finaliza la asociación del error de producto o cantidad.
                    // Omite la línea que no pasó las validaciones.
                    continue; // Mantiene la iteración segura.
                } // Finaliza la validación de producto y cantidad.

                // Recupera el nombre, el precio y la existencia actuales desde el catálogo del servidor.
                $productoValidado = $productosPorId[$idProducto]; // No usa etiquetas, precios ni stock enviados en POST.
                // Convierte la cantidad validada a tres posiciones decimales.
                $cantidadNormalizada = number_format((float) $cantidadTexto, 3, '.', ''); // Conserva la precisión de ticket.cantidad_kg.
                // Añade la línea mínima que necesita el modelo transaccional.
                $productosDetalle[] = ['id_producto' => $idProducto, 'cantidad_kg' => $cantidadNormalizada]; // La base obtendrá el precio vigente.
                // Añade datos públicos para reconstruir el carrito en la vista.
                $carritoVista[] = ['id_producto' => $idProducto, 'nombre_producto' => (string) $productoValidado['nombre_producto'], 'tipo_corte' => (string) ($productoValidado['tipo_corte'] ?? ''), 'precio_kg' => (float) $productoValidado['precio_kg'], 'cantidad_kg' => (float) $cantidadNormalizada, 'stock_disponible' => (float) $productoValidado['cantidad_kg']]; // Evita perder las cantidades si el servidor devuelve un error.
            } // Finaliza la validación y reconstrucción del carrito.

            // Comprueba que el rol de sesión contenga un identificador de empleado válido.
            $idEmpleado = filter_var($_SESSION['user']['id_empleado'] ?? null, FILTER_VALIDATE_INT); // Toma el vendedor autenticado y no un campo POST.
            // Combina las reglas del encabezado, de la sesión y del carrito.
            if (!$clienteValido || !$metodoPagoValido || !$detalleValido || $idEmpleado === false || $idEmpleado < 1) { // Detiene ventas sin datos obligatorios o con líneas inválidas.
                // Presenta un aviso que permite corregir los datos de la pantalla.
                $mensaje = 'Revisa el cliente, el método de pago y los artículos de la venta.'; // No muestra información interna de validación.
                // Mantiene la alerta en estado de error.
                $tipoAlerta = 'danger'; // Resalta los datos que requieren corrección.
            } else { // Envía al modelo solo los datos validados por el controlador.
                // Prepara el encabezado con cliente, vendedor y método de pago.
                $datosVenta = ['id_cliente' => $idCliente, 'id_empleado_ven' => $idEmpleado, 'metodo_pago' => $datosFormulario['metodo_pago']]; // Mantiene el empleado de la sesión.
                // Ejecuta la transacción que crea venta y sus detalles ticket.
                $resultadoVenta = $this->venta->procesarVenta($datosVenta, $productosDetalle); // El trigger nativo controla la suficiencia de stock.
                // Redirige al patrón GET cuando el SGBD confirma la nota.
                if (($resultadoVenta['exito'] ?? false) === true) { // Evita duplicar ventas al refrescar el navegador.
                    // Prepara la confirmación con el folio creado por el modelo.
                    $_SESSION['resultadoVenta'] = ['tipo' => 'success', 'mensaje' => 'Venta registrada correctamente. Folio: ' . (int) $resultadoVenta['folio_nota'] . '.']; // Conserva solo datos de presentación.
                    // Prepara la ruta que conserva el modo actual después de registrar la venta.
                    $rutaRedireccionVenta = $ventaClienteEspecifico ? 'index.php?c=venta&a=nueva-cliente&id_cliente=' . rawurlencode($idClienteFijado) : 'index.php?c=venta&a=nueva'; // Conserva el cliente en el modo específico y el mostrador en el local.
                    // Dirige de nuevo al formulario correcto sin repetir el POST.
                    header('Location: ' . $rutaRedireccionVenta, true, 303); // Aplica el patrón POST-Redirect-GET.
                    // Detiene el procesamiento después de enviar la redirección.
                    exit; // Impide renderizar una respuesta bajo el POST exitoso.
                } // Finaliza el manejo de venta confirmada.
                // Conserva el mensaje seguro que el modelo devuelve ante un fallo.
                $mensaje = (string) ($resultadoVenta['error'] ?? 'Error de registro en la base de datos.'); // Incluye el mensaje específico de stock insuficiente.
                // Muestra el resultado fallido en una alerta Bootstrap.
                $tipoAlerta = 'danger'; // Permite corregir el carrito y reenviarlo.
                // Recupera el artículo que provocó la señal de stock si el modelo pudo identificarlo.
                $idProductoError = filter_var($resultadoVenta['id_producto_error'] ?? null, FILTER_VALIDATE_INT); // Mantiene el mensaje junto a la cantidad afectada.
                // Muestra la falta de existencias en la fila del producto que falló.
                if ($idProductoError !== false && isset($productosPorId[$idProductoError]) && str_contains($mensaje, 'Stock insuficiente')) { // Asocia el error al artículo indicado por el trigger.
                    $erroresProductos[$idProductoError] = 'La existencia disponible cambió o no alcanza; quita el artículo y agrégalo de nuevo con menos kilogramos.'; // Explica cómo corregir la cantidad rechazada.
                } elseif (str_contains($mensaje, 'Stock insuficiente')) { // Atiende fallos de stock que no incluyen un producto identificable.
                    $erroresCampos['productos'] = 'No hay existencia suficiente para uno de los artículos; revisa las cantidades del carrito.'; // Evita dejar el fallo solo en la alerta general.
                } else { // Asocia otros rechazos del modelo con el detalle que se intentó guardar.
                    $erroresCampos['productos'] = $mensaje; // Muestra el resultado cerca de los artículos.
                } // Finaliza la asociación del resultado de persistencia.
            } // Finaliza la validación previa o el resultado de persistencia.
        } // Finaliza el procesamiento POST.

        // Recupera los clientes para las opciones del selector en la vista.
        $clientesVista = $this->cliente->listarTodos(); // Lee solo los perfiles existentes.
        // Prioriza el cliente enviado desde la consulta cuando llega una carga GET válida.
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET' && isset($_GET['id_cliente'])) { // No reemplaza selecciones conservadas por errores POST.
            // Convierte el parámetro de ruta en un identificador entero estricto.
            $idClienteSolicitado = filter_var($_GET['id_cliente'], FILTER_VALIDATE_INT); // Rechaza arreglos y formatos que no sean números enteros.
            // Confirma que el ID pertenece a un cliente cargado desde la base.
            if ($idClienteSolicitado !== false && $idClienteSolicitado !== null && $idClienteSolicitado > 0) { // Solo acepta un ID positivo.
                // Busca la selección solicitada dentro de las opciones disponibles.
                foreach ($clientesVista as $clienteDisponible) { // Evita confiar en el parámetro de URL por sí solo.
                    // Compara el identificador solicitado con el de cada cliente registrado.
                    if ((int) ($clienteDisponible['id_cliente'] ?? 0) === $idClienteSolicitado) { // Garantiza que el cliente exista.
                        // Guarda el ID real para que la vista marque el selector correspondiente.
                        $datosFormulario['id_cliente'] = (string) $idClienteSolicitado; // Enlaza la selección con la futura venta.
                        // Detiene la búsqueda al encontrar el cliente válido.
                        break; // No recorre filas adicionales tras confirmar la selección.
                    } // Finaliza la comparación del cliente.
                } // Finaliza la búsqueda del cliente solicitado.
            } // Finaliza la validación del ID recibido.
        } // Finaliza la selección de cliente enviada desde otra vista.
        // Elige Público general por defecto únicamente en la carga inicial del formulario.
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET' && $datosFormulario['id_cliente'] === '') { // Conserva la selección enviada cuando hay errores.
            // Prepara una coincidencia general como alternativa si falta el perfil exacto.
            $idClientePublicoAlternativo = null; // Mantiene la fila real del cliente sin depender de un ID fijo.
            // Recorre los clientes reales sin crear registros ni asumir un identificador fijo.
            foreach ($clientesVista as $clienteVista) { // Busca el perfil público que ya exista en la base.
                // Normaliza el nombre existente para comparar la opción predeterminada.
                $nombreClienteVista = trim((string) ($clienteVista['nombre_razonsocial'] ?? '')); // Conserva los datos obtenidos del modelo.
                // Da prioridad al cliente que aparece en la captura del usuario.
                if (preg_match('/^p[uú]blico\s+general\s*\/\s*venta\s+mostrador$/iu', $nombreClienteVista) === 1) { // Reconoce Público General / Venta Mostrador.
                    // Guarda el ID de la fila encontrada para marcarla como seleccionada.
                    $datosFormulario['id_cliente'] = (string) $clienteVista['id_cliente']; // Usa el identificador real del cliente.
                    // Detiene la búsqueda al encontrar la coincidencia prioritaria.
                    break; // Evita seleccionar otra variante del público general.
                } // Finaliza la comprobación del cliente exacto.
                // Conserva una coincidencia más general si el perfil exacto no existe.
                if ($idClientePublicoAlternativo === null && preg_match('/p[uú]blico\s+general/i', $nombreClienteVista) === 1) { // Acepta el perfil simple como alternativa.
                    // Guarda el ID alternativo sin asignarlo todavía como preferido.
                    $idClientePublicoAlternativo = (string) $clienteVista['id_cliente']; // Mantiene una opción válida de público general.
                } // Termina la comparación del nombre del cliente.
            } // Termina la búsqueda de Público general.
            // Selecciona la alternativa solo cuando no se encontró la opción exacta.
            if ($datosFormulario['id_cliente'] === '' && $idClientePublicoAlternativo !== null) { // Respalda bases con nombres simplificados.
                // Asigna el ID de cliente público alternativo al formulario.
                $datosFormulario['id_cliente'] = $idClientePublicoAlternativo; // Conserva la selección automática.
            } // Termina la selección alternativa del cliente.
        } // Termina la selección inicial del cliente.
        // Restablece la identidad de cliente fijada después de las reglas antiguas de valor inicial.
        $datosFormulario['id_cliente'] = $idClienteFijado; // Mantiene fijo el cliente también en la vista de este modo.
        // Recupera los productos activos y existencias para el buscador de la venta.
        $productosVista = $this->producto->obtenerCatalogoConStock(); // La operación es de lectura y deja el trigger a cargo del stock.
        // Informa a la plantilla cuál es el modo de venta actual.
        $modoVentaClienteEspecifico = $ventaClienteEspecifico; // Permite mostrar el cliente como dato fijo, nunca como selector.
        // Conserva la identidad autenticada que muestra el menú lateral.
        $usuarioSesion = $_SESSION['user']; // El constructor confirmó la existencia de la sesión.
        // Incluye la plantilla del punto de venta después de preparar sus datos.
        require dirname(__DIR__) . '/views/ventas/nueva.php'; // Mantiene el marcado fuera de la lógica del controlador.
    } // Finaliza la pantalla y el procesamiento de venta.

    // Devuelve el catálogo vigente para refrescar existencias en el punto de venta.
    public function disponibilidad(): void // Atiende una solicitud de lectura periódica desde ventas.js.
    { // Inicia la respuesta de disponibilidad en formato JSON.
        // Indica que la respuesta contiene datos JSON y no debe almacenarse en caché.
        header('Content-Type: application/json; charset=utf-8'); // Permite que el navegador interprete el resultado como JSON.
        // Evita reutilizar cantidades guardadas por el navegador o intermediarios.
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0'); // Fuerza una lectura reciente en cada petición.
        // Consulta productos activos junto con su cantidad actual mediante el modelo existente.
        try { // Mantiene controlados los errores de conexión o consulta.
            // Obtiene el catálogo con stock desde la base sin ejecutar escrituras.
            $productosDisponibles = $this->producto->obtenerCatalogoConStock(); // Deja al trigger el control de cualquier descuento.
            // Devuelve los registros consultados para sincronizar las sugerencias del formulario.
            echo json_encode(['exito' => true, 'productos' => $productosDisponibles], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR); // Conserva los nombres en español.
        } catch (Throwable $excepcion) { // Oculta detalles internos y permite que el cliente falle de forma segura.
            // Registra en el servidor que falló la lectura periódica sin exponer datos sensibles.
            error_log('No fue posible actualizar la disponibilidad de productos en ventas.'); // Facilita el diagnóstico operativo.
            // Informa al navegador que no pudo obtener una existencia actual.
            http_response_code(500); // Permite conservar el último stock conocido en pantalla.
            // Devuelve un mensaje genérico en el mismo formato JSON.
            echo json_encode(['exito' => false, 'mensaje' => 'No fue posible actualizar las existencias.'], JSON_UNESCAPED_UNICODE); // No filtra consultas ni credenciales.
        } // Finaliza el manejo seguro de la consulta de disponibilidad.
        // Finaliza la petición JSON para que el enrutador no agregue contenido HTML.
        exit; // Mantiene limpia la respuesta que consume JavaScript.
    } // Finaliza la acción de disponibilidad.
} // Finaliza la definición del controlador VentaController.
