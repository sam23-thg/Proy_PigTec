<?php
// Activa la comprobación estricta de tipos para la consulta de pedidos.
declare(strict_types=1); // Evita conversiones implícitas durante la agrupación de resultados.

// Declara el modelo que consulta las solicitudes pendientes de preparación.
class Pedido // Mantiene el acceso a los datos fuera del controlador y la vista.
{ // Inicia la definición del modelo Pedido.
    // Conserva la conexión PDO preparada por el punto de entrada de la aplicación.
    private PDO $db; // Reutiliza la conexión compartida para todas las consultas.

    // Recibe la conexión PDO sin crear conexiones adicionales.
    public function __construct(PDO $db) // Inyecta la dependencia configurada por la aplicación.
    { // Inicia la asignación de la conexión.
        // Guarda la conexión disponible para las consultas del modelo.
        $this->db = $db; // Mantiene una sola conexión durante la solicitud.
    } // Finaliza el constructor del modelo.

    // Obtiene los pedidos abiertos y agrupa sus productos y kilogramos por pedido.
    public function listarPedidosPendientes(): array // Devuelve tarjetas listas para presentar en la vista.
    { // Inicia la consulta de pedidos pendientes de preparación.
        // Selecciona los datos del pedido, la nota y el desglose agregado por producto.
        $consultaSql = 'SELECT p.id_pedido, p.id_venta, p.direccion_entrega, p.estado_preparacion, c.nombre_razonsocial, ' // Recupera el cliente, el destino y el estado del pedido.
            . 'v.folio_nota, v.fecha_venta, ' // Obtiene el folio y la fecha cronológica desde la venta asociada.
            . 't.id_producto, pr.nombre_producto, pr.tipo_corte, ' // Lee el nombre y el corte de cada producto solicitado.
            . 'COALESCE(SUM(t.cantidad_kg), 0) AS cantidad_kg ' // Suma los kilogramos repetidos del mismo producto.
            . 'FROM pedido AS p ' // Usa pedido como registro principal que se debe mostrar.
            . 'INNER JOIN venta AS v ON v.id_venta = p.id_venta ' // Relaciona cada pedido con su folio y fecha.
            . 'INNER JOIN cliente AS c ON c.id_cliente = v.id_cliente ' // Recupera el nombre del cliente que originó la venta.
            . 'LEFT JOIN ticket AS t ON t.id_venta = v.id_venta ' // Conserva pedidos aunque todavía no tengan artículos.
            . 'LEFT JOIN producto AS pr ON pr.id_producto = t.id_producto ' // Agrega los nombres de producto al desglose.
            . 'WHERE p.estado_preparacion IN (:estado_pendiente, :estado_preparacion) ' // Excluye pedidos ya listos o cancelados.
            . 'GROUP BY p.id_pedido, p.id_venta, p.direccion_entrega, p.estado_preparacion, c.nombre_razonsocial, ' // Agrupa todos los valores de cabecera.
            . 'v.folio_nota, v.fecha_venta, t.id_producto, pr.nombre_producto, pr.tipo_corte ' // Mantiene una fila agregada por artículo.
            . 'ORDER BY v.fecha_venta ASC, p.id_pedido ASC, pr.nombre_producto ASC'; // Ordena los pedidos desde la fecha más antigua.

        // Prepara la consulta para separar la estructura SQL de los estados permitidos.
        $consulta = $this->db->prepare($consultaSql); // Evita interpolar datos recibidos desde una solicitud.
        // Ejecuta la búsqueda usando únicamente estados definidos por el requisito.
        $consulta->execute([ // Conserva los estados fuera de la cadena SQL.
            ':estado_pendiente' => 'PENDIENTE', // Incluye pedidos que aún no comienzan a prepararse.
            ':estado_preparacion' => 'EN_PREPARACION', // Incluye pedidos que el almacén ya está trabajando.
        ]); // Finaliza la ejecución de la consulta parametrizada.

        // Inicializa la colección indexada que agrupará las filas por pedido.
        $pedidosAgrupados = []; // Evita repetir tarjetas cuando hay varios productos.
        // Recorre las filas ordenadas que devuelve la base de datos.
        while ($registro = $consulta->fetch(PDO::FETCH_ASSOC)) { // Cada fila representa una cabecera o un artículo agrupado.
            // Normaliza el identificador del pedido para usarlo como clave interna.
            $idPedido = (int) $registro['id_pedido']; // Garantiza un índice entero consistente.

            // Crea la cabecera solo cuando aparece por primera vez el pedido.
            if (!isset($pedidosAgrupados[$idPedido])) { // Evita duplicar los datos generales en cada producto.
                // Conserva los datos requeridos para el encabezado de la tarjeta.
                $pedidosAgrupados[$idPedido] = [ // Agrupa por la clave primaria del pedido.
                    'id_pedido' => $idPedido, // Proporciona un identificador seguro para el componente colapsable.
                    'id_venta' => (int) $registro['id_venta'], // Enlaza la tarjeta con la venta de origen.
                    'folio_nota' => (int) $registro['folio_nota'], // Muestra el folio de la nota al personal de almacén.
                    'fecha_venta' => (string) $registro['fecha_venta'], // Conserva la fecha para orden y presentación.
                    'nombre_razonsocial' => (string) $registro['nombre_razonsocial'], // Muestra quién hizo el pedido.
                    'direccion_entrega' => (string) ($registro['direccion_entrega'] ?? ''), // Mantiene el destino informado para la preparación.
                    'estado_preparacion' => (string) $registro['estado_preparacion'], // Distingue entre pendiente y en preparación.
                    'kilogramos_totales' => 0.0, // Acumula el peso total de todos los productos del pedido.
                    'productos' => [], // Inicializa el desglose que se expande dentro de la tarjeta.
                ]; // Termina la creación de la cabecera agrupada.
            } // Finaliza la inicialización condicional del pedido.

            // Omite el detalle cuando el pedido todavía no tiene tickets asociados.
            if ($registro['id_producto'] === null) { // El LEFT JOIN permite conservar la cabecera vacía.
                // Continúa con la siguiente fila sin inventar productos solicitados.
                continue; // Deja que la vista muestre un mensaje de detalle vacío.
            } // Finaliza el tratamiento del pedido sin artículos.

            // Convierte la cantidad agregada a un valor numérico para la vista.
            $cantidadKg = (float) $registro['cantidad_kg']; // Mantiene la precisión decimal del esquema.
            // Añade el producto agrupado a la tarjeta de su pedido.
            $pedidosAgrupados[$idPedido]['productos'][] = [ // Presenta un renglón por producto y corte.
                'id_producto' => (int) $registro['id_producto'], // Conserva la identidad del producto consultado.
                'nombre_producto' => (string) ($registro['nombre_producto'] ?? 'Producto no disponible'), // Evita una etiqueta vacía si falta el catálogo relacionado.
                'tipo_corte' => (string) ($registro['tipo_corte'] ?? ''), // Conserva el corte cuando fue registrado.
                'cantidad_kg' => $cantidadKg, // Entrega los kilogramos agregados para su empaque.
            ]; // Finaliza la preparación del artículo.
            // Acumula el peso del artículo en el total visible del pedido.
            $pedidosAgrupados[$idPedido]['kilogramos_totales'] += $cantidadKg; // Facilita priorizar y revisar la carga total.
        } // Finaliza la agrupación de cabeceras y productos.

        // Devuelve una lista numerada para que la vista recorra las tarjetas.
        return array_values($pedidosAgrupados); // Oculta las claves temporales utilizadas durante la agrupación.
    } // Finaliza la consulta de pedidos pendientes.

    // Marca como preparado un pedido que todavía está pendiente de atención.
    // Obtiene el folio de venta asociado a un pedido para presentar confirmaciones precisas.
    public function obtenerFolioPedido(int $idPedido): ?int // Recibe el identificador interno del pedido.
    { // Inicia la consulta del folio asociado.
        // Prepara una consulta de lectura con relación entre pedido y venta.
        $consulta = $this->db->prepare('SELECT v.folio_nota FROM pedido AS p INNER JOIN venta AS v ON v.id_venta = p.id_venta WHERE p.id_pedido = :id_pedido LIMIT 1'); // Recupera el folio visible para el personal.
        // Ejecuta la búsqueda con el identificador validado por el controlador.
        $consulta->execute([':id_pedido' => $idPedido]); // Mantiene el dato fuera de la instrucción SQL.
        // Recupera el folio encontrado o false si el pedido no existe.
        $folioPedido = $consulta->fetchColumn(); // No confunde el ID del pedido con el folio de la nota.
        // Devuelve un entero cuando la relación existe.
        return $folioPedido === false ? null : (int) $folioPedido; // Indica ausencia sin devolver datos inventados.
    } // Finaliza la consulta del folio del pedido.

    public function marcarComoRealizado(int $idPedido): bool // Recibe el ID positivo validado por el controlador.
    { // Inicia la actualización del estado del pedido.
        // Prepara una actualización que no acepta estados enviados por el navegador.
        $consulta = $this->db->prepare('UPDATE pedido SET estado_preparacion = :estado_realizado WHERE id_pedido = :id_pedido AND estado_preparacion IN (:estado_pendiente, :estado_preparacion)'); // Usa el estado PREPARADO definido por el esquema existente.
        // Ejecuta la transición únicamente para pedidos que aún estén abiertos.
        $consulta->execute([ // Mantiene todos los valores separados del SQL.
            ':estado_realizado' => 'PREPARADO', // Representa Realizado con el valor permitido por el ENUM.
            ':id_pedido' => $idPedido, // Limita la escritura a un pedido concreto.
            ':estado_pendiente' => 'PENDIENTE', // Acepta pedidos sin iniciar.
            ':estado_preparacion' => 'EN_PREPARACION', // Acepta pedidos que ya se están preparando.
        ]); // Finaliza la actualización parametrizada.
        // Informa si se cambió una fila que todavía estaba abierta.
        return $consulta->rowCount() === 1; // Evita confirmar pedidos inexistentes o ya cerrados.
    } // Finaliza el cambio a estado realizado.
} // Finaliza la definición del modelo Pedido.
