<?php
// Activa la comprobación estricta de tipos para las operaciones de venta.
declare(strict_types=1); // Reduce conversiones implícitas en los datos monetarios y de cantidades.

// Declara el modelo que registra encabezados de venta y sus artículos.
class Venta // Mantiene el trabajo transaccional separado del controlador HTTP.
{ // Inicia la definición del modelo Venta.
    // Conserva la conexión PDO compartida con las demás operaciones del sistema.
    private PDO $db; // Permite ejecutar venta y ticket dentro de una misma transacción.

    // Recibe la conexión PDO configurada por el punto de entrada de la aplicación.
    public function __construct(PDO $db) // Evita abrir conexiones adicionales desde el modelo.
    { // Inicia la asignación de la dependencia.
        // Guarda la conexión para las consultas preparadas y la transacción.
        $this->db = $db; // Reutiliza el PDO que administra la petición.
    } // Finaliza el constructor del modelo.

    // Registra la venta y sus artículos de forma atómica y deja el stock al trigger de ticket.
    public function procesarVenta(array $datosVenta, array $productosDetalle): array // Recibe encabezado validado y artículos con producto y cantidad.
    { // Inicia la preparación transaccional de la venta.
        // Normaliza el identificador del cliente recibido por el controlador.
        $idCliente = filter_var($datosVenta['id_cliente'] ?? null, FILTER_VALIDATE_INT); // Rechaza valores que no sean enteros válidos.
        // Normaliza el identificador del empleado tomado de la sesión autenticada.
        $idEmpleado = filter_var($datosVenta['id_empleado_ven'] ?? null, FILTER_VALIDATE_INT); // Evita usar un identificador no numérico.
        // Lee el método de pago y limita los valores al ENUM documentado.
        $metodoPago = (string) ($datosVenta['metodo_pago'] ?? ''); // No acepta métodos definidos por el navegador.
        // Define los valores de pago que admite la tabla venta.
        $metodosPagoPermitidos = ['EFECTIVO']; // Permite efectivo mientras se habilitan futuros métodos de pago.

        // Rechaza encabezados incompletos antes de abrir una transacción.
        if ($idCliente === false || $idCliente < 1 || $idEmpleado === false || $idEmpleado < 1 || !in_array($metodoPago, $metodosPagoPermitidos, true) || $productosDetalle === []) { // Exige cliente, empleado, pago y al menos un artículo.
            // Devuelve un error entendible sin consultar ni modificar la base de datos.
            return ['exito' => false, 'error' => 'Revisa el cliente, el método de pago y los productos de la venta.']; // Mantiene la validación del modelo como defensa adicional.
        } // Finaliza la validación mínima del encabezado.

        // Inicializa la lista segura de artículos con su precio vigente obtenido de la base.
        $articulosPreparados = []; // Evita confiar en precios enviados por el navegador.
        // Inicializa el importe de los productos antes de calcular el impuesto.
        $subtotalVenta = 0.0; // Acumula los subtotales con precisión decimal.
        // Conserva el producto cuyo ticket está procesando el trigger en cada momento.
        $idProductoEnProceso = null; // Permite asociar un error de stock con su línea del carrito.

        // Inicia el bloque que revierte la venta si falla cualquier consulta o inserción.
        try { // Agrupa todos los pasos de la transacción central.
            // Abre una transacción para mantener juntos el encabezado y todos sus detalles.
            $this->db->beginTransaction(); // Garantiza que la nota no quede incompleta.

            // Prepara la lectura de precio solo para productos que sigan activos.
            $consultaPrecio = $this->db->prepare('SELECT precio_kg FROM producto WHERE id_producto = :id_producto AND estado = :estado LIMIT 1'); // No consulta ni modifica inventario.
            // Prepara la lectura del próximo folio de nota dentro del flujo transaccional.
            $consultaFolio = $this->db->prepare('SELECT COALESCE(MAX(folio_nota), 0) + 1 FROM venta'); // Genera el folio desde la secuencia actualmente registrada.

            // Recorre los artículos enviados y obtiene su precio autoritativo desde producto.
            foreach ($productosDetalle as $articuloDetalle) { // Rechaza cualquier línea con formato inesperado.
                // Comprueba que cada línea recibida contenga un arreglo de datos.
                if (!is_array($articuloDetalle)) { // Impide acceder a índices de un valor manipulado.
                    // Detiene el procesamiento ante una línea que no tenga estructura válida.
                    throw new InvalidArgumentException('La lista de productos no tiene un formato válido.'); // Se manejará con la reversión general.
                } // Finaliza la validación de estructura de artículo.

                // Valida el identificador del producto como entero positivo.
                $idProducto = filter_var($articuloDetalle['id_producto'] ?? null, FILTER_VALIDATE_INT); // Rechaza identificadores ausentes o no enteros.
                // Lee la cantidad como número decimal del artículo.
                $cantidadKg = $articuloDetalle['cantidad_kg'] ?? null; // Conserva la unidad definida por ticket.cantidad_kg.

                // Rechaza cantidades inválidas antes de consultar el precio.
                if ($idProducto === false || $idProducto < 1 || !is_numeric($cantidadKg) || (float) $cantidadKg <= 0) { // Solo acepta cantidades positivas.
                    // Detiene la venta para impedir insertar líneas inconsistentes.
                    throw new InvalidArgumentException('Uno de los productos o sus kilogramos no son válidos.'); // La transacción se revertirá en el bloque de excepciones.
                } // Finaliza la validación del producto y su cantidad.

                // Enlaza el identificador de producto para consultar el precio activo.
                $consultaPrecio->bindValue(':id_producto', $idProducto, PDO::PARAM_INT); // Mantiene el ID fuera del SQL.
                // Enlaza el estado activo con tipo entero.
                $consultaPrecio->bindValue(':estado', 1, PDO::PARAM_INT); // Evita procesar productos inactivos.
                // Ejecuta la consulta parametrizada del precio vigente.
                $consultaPrecio->execute(); // Lee solo la tabla producto.
                // Obtiene el precio o false cuando el producto ya no está activo.
                $precioKg = $consultaPrecio->fetchColumn(); // No realiza ninguna comprobación ni modificación de stock.

                // Revierte el encabezado si un artículo dejó de existir o fue desactivado.
                if ($precioKg === false) { // Comprueba que el producto continúe disponible en el catálogo.
                    // Revierte cualquier operación iniciada antes de informar el problema.
                    $this->db->rollBack(); // Deja las tablas venta y ticket sin cambios parciales.
                    // Devuelve un mensaje controlado sobre el artículo faltante.
                    return ['exito' => false, 'error' => 'Uno de los productos ya no está disponible. Actualiza el catálogo e inténtalo de nuevo.']; // Evita revelar detalles internos de SQL.
                } // Finaliza la comprobación del producto activo.

                // Calcula el importe de esta línea con el precio almacenado en la base.
                $totalLinea = round((float) $cantidadKg * (float) $precioKg, 2); // Redondea cada artículo a dos posiciones monetarias.
                // Guarda los valores ya normalizados para la inserción posterior en ticket.
                $articulosPreparados[] = ['id_producto' => $idProducto, 'cantidad_kg' => round((float) $cantidadKg, 3), 'precio_kg' => (float) $precioKg, 'total_linea' => $totalLinea]; // Ignora precios y totales enviados desde el navegador.
                // Acumula el total de los artículos antes de impuestos.
                $subtotalVenta += $totalLinea; // Usa el precio vigente consultado por PDO.
            } // Finaliza la lectura y preparación de las líneas.

            // Redondea el subtotal de los productos a dos decimales.
            $subtotalVenta = round($subtotalVenta, 2); // Conserva la precisión de la columna DECIMAL monetaria.
            // Calcula el impuesto del 16 por ciento mostrado por la interfaz de venta.
            $ivaVenta = round($subtotalVenta * 0.16, 2); // Calcula el IVA a partir del subtotal autoritativo.
            // Calcula el total de la nota sumando subtotal e impuesto.
            $totalVenta = round($subtotalVenta + $ivaVenta, 2); // Guarda en venta el importe final cobrado.

            // Ejecuta la consulta preparada para obtener el siguiente folio de nota.
            $consultaFolio->execute(); // Lee el mayor folio existente sin interpolar datos externos.
            // Convierte el folio recuperado en un entero para su inserción.
            $folioNota = (int) $consultaFolio->fetchColumn(); // Cumple el tipo de dato documentado para folio_nota.

            // Prepara la inserción del encabezado con todas las columnas requeridas por el esquema.
            $consultaVenta = $this->db->prepare('INSERT INTO venta (folio_nota, id_cliente, id_empleado_ven, fecha_venta, metodo_pago, total_venta, estado_venta) VALUES (:folio_nota, :id_cliente, :id_empleado_ven, NOW(), :metodo_pago, :total_venta, :estado_venta)'); // Deja la fecha al reloj del servidor de base de datos.
            // Ejecuta el alta de venta mediante parámetros PDO.
            $consultaVenta->execute([ // Inserta el encabezado antes de sus detalles.
                ':folio_nota' => $folioNota, // Asigna el número único de la nota.
                ':id_cliente' => $idCliente, // Vincula la venta con su cliente.
                ':id_empleado_ven' => $idEmpleado, // Registra al vendedor de la sesión autenticada.
                ':metodo_pago' => $metodoPago, // Guarda el método de pago admitido.
                ':total_venta' => $totalVenta, // Guarda el subtotal más el IVA calculados en servidor.
                ':estado_venta' => 'COMPLETADA', // Marca como completada la venta que se procesa en esta pantalla.
            ]); // Finaliza la inserción parametrizada del encabezado.

            // Recupera la clave primaria asignada al encabezado recién insertado.
            $idVenta = (int) $this->db->lastInsertId(); // Usa este identificador para enlazar cada fila ticket.

            // Prepara la inserción de los detalles y permite que el trigger gestione el stock.
            $consultaTicket = $this->db->prepare('INSERT INTO ticket (id_venta, id_producto, cantidad_kg, precio_kg, `Total`) VALUES (:id_venta, :id_producto, :cantidad_kg, :precio_kg, :total_linea)'); // No contiene UPDATE ni resta manual de inventario.

            // Inserta cada artículo relacionado con la venta recién creada.
            foreach ($articulosPreparados as $articuloPreparado) { // El trigger de ticket es responsable de la existencia.
                // Conserva el identificador antes de ejecutar la inserción que activa el trigger.
                $idProductoEnProceso = (int) $articuloPreparado['id_producto']; // Identifica la línea si la base rechaza su cantidad.
                // Ejecuta el detalle de venta con valores preparados de producto y cantidad.
                $consultaTicket->execute([ // Cada inserción puede activar la validación nativa del SGBD.
                    ':id_venta' => $idVenta, // Enlaza el ticket al encabezado que se acaba de guardar.
                    ':id_producto' => $articuloPreparado['id_producto'], // Asocia el producto elegido en la interfaz.
                    ':cantidad_kg' => $articuloPreparado['cantidad_kg'], // Envía los kilogramos al trigger nativo.
                    ':precio_kg' => $articuloPreparado['precio_kg'], // Conserva el precio vigente consultado por el modelo.
                    ':total_linea' => $articuloPreparado['total_linea'], // Guarda el subtotal calculado para esta línea.
                ]); // Finaliza la ejecución parametrizada del detalle.
            } // Finaliza la inserción de todos los artículos.

            // Consulta el domicilio registrado del cliente para asignarlo al pedido de almacén.
            $consultaDireccion = $this->db->prepare('SELECT calle, colonia, codigo_postal, ciudad FROM cliente WHERE id_cliente = :id_cliente LIMIT 1'); // Mantiene los datos del cliente fuera de la instrucción SQL.
            // Ejecuta la consulta con el identificador de cliente validado al inicio del método.
            $consultaDireccion->execute([':id_cliente' => $idCliente]); // Reutiliza el mismo cliente asociado con la venta.
            // Obtiene los componentes opcionales de la dirección del cliente.
            $datosDireccion = $consultaDireccion->fetch(PDO::FETCH_ASSOC) ?: []; // Permite manejar una dirección vacía sin interrumpir una venta de mostrador.
            // Prepara una lista únicamente con componentes de dirección no vacíos.
            $componentesDireccion = array_filter([ // Evita agregar separadores para datos no registrados.
                trim((string) ($datosDireccion['calle'] ?? '')), // Incluye la calle y número cuando el cliente lo capturó.
                trim((string) ($datosDireccion['colonia'] ?? '')), // Incluye la colonia si está disponible.
                trim((string) ($datosDireccion['codigo_postal'] ?? '')), // Conserva el código postal como texto.
                trim((string) ($datosDireccion['ciudad'] ?? '')), // Añade la ciudad al destino de entrega.
            ], static fn (string $componente): bool => $componente !== ''); // Descarta valores vacíos antes de unirlos.
            // Une los datos disponibles en una dirección legible para almacén.
            $direccionEntrega = implode(', ', $componentesDireccion); // Separa los componentes con comas.
            // Define un texto explícito cuando el perfil del cliente no tiene domicilio.
            if ($direccionEntrega === '') { // La columna destino no admite NULL en el esquema actual.
                // Conserva la venta y deja claro que no se registró una dirección.
                $direccionEntrega = 'Dirección no registrada'; // Evita inventar un destino para la venta.
            } // Finaliza la asignación de destino alternativo.

            // Prepara la creación del pedido pendiente ligado a la venta y su dirección.
            $consultaPedido = $this->db->prepare('INSERT INTO pedido (id_venta, id_empleado_rep, direccion_entrega, estado_preparacion) VALUES (:id_venta, NULL, :direccion_entrega, :estado_preparacion)'); // Deja que almacén asigne un repartidor más adelante.
            // Registra el pedido dentro de la misma transacción que venta y ticket.
            $consultaPedido->execute([ // Si falla la creación del pedido, la venta completa se revertirá.
                ':id_venta' => $idVenta, // Relaciona el pedido con la nota recién creada.
                ':direccion_entrega' => $direccionEntrega, // Guarda el domicilio del cliente o el aviso de dirección ausente.
                ':estado_preparacion' => 'PENDIENTE', // Hace que el pedido aparezca en la consulta de almacén.
            ]); // Finaliza la inserción parametrizada del pedido.

            // Confirma la venta, sus tickets y el pedido si todas las inserciones terminaron correctamente.
            $this->db->commit(); // Hace permanentes juntos todos los registros de la operación.
            // Devuelve los importes calculados para el mensaje de confirmación.
            return ['exito' => true, 'folio_nota' => $folioNota, 'subtotal' => $subtotalVenta, 'iva' => $ivaVenta, 'total_venta' => $totalVenta]; // Informa el resultado sin incluir datos no necesarios.
        } catch (PDOException $excepcion) { // Captura errores PDO, incluido SIGNAL SQLSTATE 45000 del trigger.
            // Revierte encabezado y detalles si la conexión mantiene una transacción abierta.
            if ($this->db->inTransaction()) { // Protege frente a errores ocurridos al iniciar o cerrar la transacción.
                // Deshace todas las escrituras de la nota incompleta.
                $this->db->rollBack(); // No altera el inventario; también revierte cualquier efecto transaccional del trigger.
            } // Finaliza la reversión condicional de PDO.
            // Obtiene el SQLSTATE original reportado por el servidor cuando está disponible.
            $estadoSql = (string) ($excepcion->errorInfo[0] ?? $excepcion->getCode()); // Prioriza el estado enviado por MariaDB.
            // Detecta el código de stock insuficiente del trigger o su mensaje configurado.
            if ($estadoSql === '45000' || str_contains($excepcion->getMessage(), 'Stock insuficiente')) { // Captura el SIGNAL definido para falta de existencia.
                // Devuelve el mensaje específico solicitado para el error de stock.
                return ['exito' => false, 'error' => 'Error: Stock insuficiente en inventario para realizar la venta.', 'id_producto_error' => $idProductoEnProceso]; // Identifica el producto sin exponer el mensaje crudo del SGBD.
            } // Finaliza la identificación del error de stock.
            // Registra la causa técnica en el log del servidor sin enviarla al navegador.
            error_log('Error PDO al procesar una venta: ' . $excepcion->getMessage()); // Facilita diagnóstico interno del fallo.
            // Devuelve un mensaje general ante otros errores de escritura.
            return ['exito' => false, 'error' => 'Error de registro en la base de datos.']; // Mantiene la respuesta segura para el usuario.
        } catch (Throwable $excepcion) { // Captura errores de validación interna y fallos no PDO.
            // Revierte cualquier transacción abierta antes de informar el error.
            if ($this->db->inTransaction()) { // Confirma que aún haya trabajo que deshacer.
                // Deshace el encabezado o los artículos insertados previamente.
                $this->db->rollBack(); // Conserva la atomicidad del proceso de venta.
            } // Finaliza la reversión de errores generales.
            // Registra el detalle técnico fuera de la respuesta visible.
            error_log('Error interno al procesar una venta: ' . $excepcion->getMessage()); // Evita filtrar datos internos al navegador.
            // Devuelve una respuesta general de validación o persistencia.
            return ['exito' => false, 'error' => 'No fue posible procesar la venta. Revisa los datos e inténtalo de nuevo.']; // Mantiene estable el contrato del controlador.
        } // Finaliza el bloque transaccional y de manejo de errores.
    } // Finaliza el procesamiento transaccional de la venta.
} // Finaliza la definición del modelo Venta.
