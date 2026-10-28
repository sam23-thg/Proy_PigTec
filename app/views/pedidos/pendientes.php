<?php
// Prepara una función para escapar todo texto dinámico presentado en la página.
$escaparPedido = static fn (string $valor): string => htmlspecialchars($valor, ENT_QUOTES, 'UTF-8'); // Protege el marcado frente a datos de la base o la sesión.
// Garantiza que la vista recorra una colección de pedidos válida.
$pedidosVista = isset($pedidosPendientes) && is_array($pedidosPendientes) ? $pedidosPendientes : []; // Evita advertencias cuando el modelo no entrega filas.
// Conserva los datos de identidad que el controlador validó.
$sesionPedidoVista = isset($usuarioSesion) && is_array($usuarioSesion) ? $usuarioSesion : []; // Usa la misma fuente de sesión que las demás vistas.
// Determina qué opciones se muestran según el rol autenticado.
$rolPedidoVista = (string) ($sesionPedidoVista['rol'] ?? ''); // No permite controlar el menú mediante parámetros HTTP.
// Normaliza el aviso de actualización para presentarlo después de una redirección.
$mensajePedidoVista = isset($mensajePedido) ? (string) $mensajePedido : ''; // Mantiene limpia la pantalla cuando no hay notificaciones.
// Limita la clase de alerta a las variantes Bootstrap admitidas.
$tipoAlertaPedidoVista = isset($tipoAlertaPedido) && $tipoAlertaPedido === 'success' ? 'success' : 'danger'; // Reserva verde para pedidos realizados y rojo para cambios fallidos.
?>
<!-- Declara el documento HTML5 de consulta de pedidos. -->
<!DOCTYPE html>
<!-- Establece el español como idioma de la interfaz. -->
<html lang="es">
<!-- Inicia la cabecera del documento y carga los recursos existentes. -->
<head>
    <!-- Define la codificación necesaria para mostrar correctamente el español. -->
    <meta charset="UTF-8">
    <!-- Adapta el diseño al ancho de teléfonos y tabletas. -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Describe el propósito de la pantalla de almacén. -->
    <meta name="description" content="Consulta los pedidos pendientes y prepara los productos solicitados.">
    <!-- Mantiene el color cálido del navegador usado por el sistema. -->
    <meta name="theme-color" content="#fff5f8">
    <!-- Define el nombre que aparece en la pestaña del navegador. -->
    <title>Pedidos pendientes | El Puerquito Digital</title>
    <!-- Carga Bootstrap 5.3.3 para la cuadrícula, tarjetas y componentes colapsables. -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Reutiliza el layout y los estilos laterales existentes. -->
    <link href="assets/css/empleados.css" rel="stylesheet">
    <!-- Reutiliza la paleta cálida definida por el sistema. -->
    <link href="assets/css/paleta-rosa.css" rel="stylesheet">
    <!-- Reutiliza las superficies y tarjetas ya existentes en el catálogo. -->
    <link href="assets/css/productos.css" rel="stylesheet">
    <!-- Carga el componente Bootstrap Collapse desde un archivo externo. -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous" defer></script>
    <!-- Carga el comportamiento externo que controla las categorías de navegación. -->
    <script src="assets/js/navegacion.js?v=menu-persistente-2" defer></script>
<!-- Finaliza la cabecera de la página. -->
</head>
<!-- Inicia la página con el fondo cálido común de los módulos. -->
<body class="pagina-empleados">
    <!-- Distribuye la barra lateral y el contenido principal. -->
    <div class="contenedor-empleados d-flex min-vh-100">
        <!-- Reutiliza la barra lateral existente sin cambiar sus estilos. -->
        <aside class="barra-empleados d-flex flex-column p-3" aria-label="Navegación del sistema">
            <!-- Lleva al panel de inicio desde la marca del sistema. -->
            <a class="marca-empleados d-flex align-items-center gap-3 text-decoration-none" href="index.php" aria-label="El Güero, inicio">
                <!-- Presenta el logotipo existente de la aplicación. -->
                <img class="logo-sistema" src="assets/logoPIG.png" alt="Logotipo de El Güero">
                <!-- Agrupa el nombre comercial y su descriptor. -->
                <span class="d-flex flex-column">
                    <!-- Muestra el nombre de la marca. -->
                    <span class="fw-bold">El Güero</span>
                    <!-- Muestra el descriptor que usan las demás vistas. -->
                    <span class="small text-secondary">Tocinería · Sistema</span>
                <!-- Finaliza el bloque textual de la marca. -->
                </span>
            <!-- Finaliza el enlace de marca. -->
            </a>
            <!-- Conserva el acceso común a Inicio. -->
            <a class="enlace-inicio-navegacion" href="index.php" aria-label="Ir a la pantalla principal">⌂ <span>Inicio</span></a>
            <!-- Agrupa las categorías laterales con el comportamiento existente. -->
            <?php require dirname(__DIR__) . '/partials/menu_lateral.php'; ?>
            <!-- Presenta la identidad y el rol al pie de la barra lateral. -->
            <div class="sesion-empleado d-flex align-items-center gap-2 mt-auto pt-3 border-top">
                <!-- Agrupa el nombre de usuario y el rol en líneas separadas. -->
                <span class="d-flex flex-column overflow-hidden">
                    <!-- Escapa el nombre de usuario antes de mostrarlo. -->
                    <span class="small fw-semibold text-truncate"><?= $escaparPedido((string) ($sesionPedidoVista['usuario'] ?? 'Usuario')) ?></span>
                    <!-- Escapa el nombre del rol antes de mostrarlo. -->
                    <span class="small text-secondary"><?= $escaparPedido($rolPedidoVista) ?></span>
                <!-- Finaliza la identidad de sesión. -->
                </span>
                <!-- Conserva el enlace de cierre de sesión del sistema. -->
                <a class="btn btn-sm btn-outline-secondary enlace-cerrar-sesion" href="index.php?c=auth&amp;a=logout">Cerrar sesión</a>
            <!-- Finaliza el pie de la barra lateral. -->
            </div>
        <!-- Finaliza la navegación lateral común. -->
        </aside>
        <!-- Inicia el área de contenido con el espaciado estándar. -->
        <main class="contenido-empleados flex-grow-1 p-3 p-md-4 p-xl-5">
            <!-- Presenta la ubicación y el propósito de esta vista. -->
            <header class="encabezado-productos mb-4">
                <!-- Muestra la ruta de navegación actual. -->
                <p class="miga-pan text-secondary small mb-2">Almacén / Pedidos pendientes</p>
                <!-- Nombra la consulta para el personal de almacén. -->
                <h1 class="h2 fw-bold mb-2">Pedidos pendientes</h1>
                <!-- Explica qué se muestra en cada tarjeta. -->
                <p class="text-secondary mb-0">Consulta las solicitudes por fecha y revisa los kilogramos que debes preparar.</p>
            <!-- Finaliza el encabezado de la página. -->
            </header>
            <!-- Presenta el resultado de marcar un pedido como realizado. -->
            <?php if ($mensajePedidoVista !== ''): ?>
                <!-- Escapa el mensaje y usa únicamente una variante Bootstrap conocida. -->
                <div class="alert alert-<?= $escaparPedido($tipoAlertaPedidoVista) ?> rounded-4" role="status"><?= $escaparPedido($mensajePedidoVista) ?></div>
            <!-- Finaliza el aviso temporal del pedido. -->
            <?php endif; ?>
            <!-- Informa claramente cuando no hay pedidos que preparar. -->
            <?php if ($pedidosVista === []): ?>
                <!-- Usa una alerta informativa con la paleta Bootstrap existente. -->
                <div class="alert alert-info rounded-4" role="status">No hay pedidos pendientes por preparar.</div>
            <!-- Presenta las tarjetas cuando el modelo encontró pedidos abiertos. -->
            <?php else: ?>
                <!-- Recorre la lista agrupada que entrega el modelo. -->
                <?php foreach ($pedidosVista as $pedidoVista): ?>
                    <!-- Normaliza el estado para elegir una etiqueta visible segura. -->
                    <?php $estadoPedidoVista = (string) ($pedidoVista['estado_preparacion'] ?? 'PENDIENTE'); ?>
                    <!-- Traduce los estados de base de datos a etiquetas de lectura. -->
                    <?php $etiquetaEstadoVista = $estadoPedidoVista === 'EN_PREPARACION' ? 'En preparación' : 'Pendiente'; ?>
                    <!-- Elige una clase Bootstrap fija según el estado permitido. -->
                    <?php $claseEstadoVista = $estadoPedidoVista === 'EN_PREPARACION' ? 'text-bg-warning' : 'text-bg-info'; ?>
                    <!-- Convierte la fecha a formato local y prepara un reemplazo seguro. -->
                    <?php $marcaTiempoPedido = strtotime((string) ($pedidoVista['fecha_venta'] ?? '')); ?>
                    <!-- Formatea la fecha solo cuando la base entrega un valor reconocible. -->
                    <?php $fechaPedidoVista = $marcaTiempoPedido === false ? 'Fecha no disponible' : date('d/m/Y H:i', $marcaTiempoPedido); ?>
                    <!-- Asegura que la colección de artículos se pueda recorrer. -->
                    <?php $productosPedidoVista = isset($pedidoVista['productos']) && is_array($pedidoVista['productos']) ? $pedidoVista['productos'] : []; ?>
                    <!-- Crea una tarjeta colapsable independiente para cada pedido. -->
                    <article class="card border-0 shadow-sm rounded-4 mb-3">
                        <!-- Mantiene el botón de expansión como encabezado de la tarjeta. -->
                        <div class="card-header bg-white border-0 rounded-top-4 p-0">
                            <!-- Permite expandir el desglose completo del pedido. -->
                            <button class="btn w-100 text-start p-3 p-md-4 text-body" type="button" data-bs-toggle="collapse" data-bs-target="#detalle-pedido-<?= (int) $pedidoVista['id_pedido'] ?>" aria-expanded="false" aria-controls="detalle-pedido-<?= (int) $pedidoVista['id_pedido'] ?>">
                                <!-- Distribuye el folio, la fecha y el resumen de peso. -->
                                <span class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                                    <!-- Presenta el folio y la fecha de venta. -->
                                    <span class="d-flex flex-column gap-1">
                                        <!-- Escapa el folio de nota antes de mostrarlo. -->
                                        <span class="fw-bold">Folio #<?= (int) ($pedidoVista['folio_nota'] ?? 0) ?></span>
                                        <!-- Escapa la fecha formateada antes de presentarla. -->
                                        <span class="small text-secondary">Fecha: <?= $escaparPedido($fechaPedidoVista) ?></span>
                                    <!-- Finaliza los datos principales del pedido. -->
                                    </span>
                                    <!-- Agrupa el estado y el peso total solicitado. -->
                                    <span class="d-flex align-items-center gap-2 flex-wrap">
                                        <!-- Presenta un estado con una clase Bootstrap predeterminada. -->
                                        <span class="badge rounded-pill <?= $claseEstadoVista ?>"><?= $escaparPedido($etiquetaEstadoVista) ?></span>
                                        <!-- Muestra el total de kilogramos calculado por el modelo. -->
                                        <span class="badge rounded-pill text-bg-light border"><?= number_format((float) ($pedidoVista['kilogramos_totales'] ?? 0), 3, '.', ',') ?> kg</span>
                                    <!-- Finaliza el resumen del pedido. -->
                                    </span>
                                <!-- Finaliza la distribución del encabezado. -->
                                </span>
                            <!-- Finaliza el control colapsable. -->
                            </button>
                        <!-- Finaliza el encabezado de la tarjeta. -->
                        </div>
                        <!-- Mantiene oculto el detalle hasta que el usuario expanda la tarjeta. -->
                        <div class="collapse" id="detalle-pedido-<?= (int) $pedidoVista['id_pedido'] ?>">
                            <!-- Presenta el destino y el detalle del pedido expandido. -->
                            <div class="card-body border-top p-3 p-md-4">
                                <!-- Muestra quién realizó la compra antes de indicar el destino. -->
                                <p class="mb-2"><span class="fw-semibold">Cliente:</span> <?= $escaparPedido((string) ($pedidoVista['nombre_razonsocial'] ?? 'Cliente no disponible')) ?></p>
                                <!-- Presenta la dirección solo cuando existe información de entrega. -->
                                <?php if (trim((string) ($pedidoVista['direccion_entrega'] ?? '')) !== ''): ?>
                                    <!-- Escapa la dirección guardada en la tabla pedido. -->
                                    <p class="mb-3"><span class="fw-semibold">Dirección de entrega:</span> <?= $escaparPedido((string) $pedidoVista['direccion_entrega']) ?></p>
                                <!-- Finaliza la presentación condicional del destino. -->
                                <?php endif; ?>
                                <!-- Informa cuando un pedido abierto todavía no contiene artículos. -->
                                <?php if ($productosPedidoVista === []): ?>
                                    <!-- Mantiene el detalle vacío explícito para almacén. -->
                                    <p class="text-secondary mb-0">Este pedido todavía no tiene productos registrados.</p>
                                <!-- Presenta el desglose agrupado por producto cuando está disponible. -->
                                <?php else: ?>
                                    <!-- Crea una tabla responsiva con producto, corte y kilogramos. -->
                                    <div class="table-responsive">
                                        <!-- Usa las clases de tabla Bootstrap ya empleadas por el sistema. -->
                                        <table class="table align-middle mb-0">
                                            <!-- Nombra las columnas de preparación. -->
                                            <thead>
                                                <!-- Define la fila de encabezados accesible. -->
                                                <tr>
                                                    <!-- Identifica el producto solicitado. -->
                                                    <th scope="col">Producto</th>
                                                    <!-- Identifica el tipo de corte. -->
                                                    <th scope="col">Tipo de corte</th>
                                                    <!-- Identifica el peso solicitado. -->
                                                    <th scope="col" class="text-end">Cantidad a preparar</th>
                                                <!-- Finaliza los encabezados de la tabla. -->
                                                </tr>
                                            <!-- Finaliza la cabecera de la tabla. -->
                                            </thead>
                                            <!-- Recorre los productos sumados por el modelo. -->
                                            <tbody>
                                                <!-- Presenta una fila por producto de este pedido. -->
                                                <?php foreach ($productosPedidoVista as $productoPedidoVista): ?>
                                                    <!-- Muestra el nombre, corte y peso del artículo. -->
                                                    <tr>
                                                        <!-- Escapa el nombre del producto. -->
                                                        <td class="fw-semibold"><?= $escaparPedido((string) ($productoPedidoVista['nombre_producto'] ?? 'Producto')) ?></td>
                                                        <!-- Escapa el corte o indica que no fue especificado. -->
                                                        <td><?= $escaparPedido((string) ($productoPedidoVista['tipo_corte'] ?? '')) ?: 'Sin especificar' ?></td>
                                                        <!-- Muestra el peso agregado con tres decimales. -->
                                                        <td class="text-end fw-semibold"><?= number_format((float) ($productoPedidoVista['cantidad_kg'] ?? 0), 3, '.', ',') ?> kg</td>
                                                    <!-- Finaliza la fila del artículo. -->
                                                    </tr>
                                                <!-- Continúa con el siguiente producto del pedido. -->
                                                <?php endforeach; ?>
                                            <!-- Finaliza el cuerpo de la tabla. -->
                                            </tbody>
                                        <!-- Finaliza la tabla del desglose. -->
                                        </table>
                                    <!-- Finaliza el contenedor responsivo. -->
                                    </div>
                                <!-- Finaliza la selección entre desglose y detalle vacío. -->
                                <?php endif; ?>
                                <!-- Envía la acción para marcar este pedido como realizado. -->
                                <form action="index.php?c=pedido&amp;a=realizar" method="post" class="mt-4 pt-3 border-top">
                                    <!-- Identifica el pedido específico que se va a cerrar. -->
                                    <input type="hidden" name="id_pedido" value="<?= (int) $pedidoVista['id_pedido'] ?>">
                                    <!-- Protege la actualización con el token guardado en la sesión. -->
                                    <input type="hidden" name="token_pedido" value="<?= $escaparPedido((string) ($_SESSION['tokenPedido'] ?? '')) ?>">
                                    <!-- Cambia el estado a realizado y retira el pedido del listado pendiente. -->
                                    <button class="btn btn-success" type="submit">Marcar como realizado</button>
                                <!-- Finaliza el formulario de cambio de estado. -->
                                </form>
                            <!-- Finaliza el cuerpo expandible de la tarjeta. -->
                            </div>
                        <!-- Finaliza el componente colapsable. -->
                        </div>
                    <!-- Finaliza la tarjeta de este pedido. -->
                    </article>
                <!-- Continúa con el siguiente pedido pendiente. -->
                <?php endforeach; ?>
            <!-- Finaliza la selección entre listado vacío y tarjetas. -->
            <?php endif; ?>
        <!-- Finaliza el contenido principal. -->
        </main>
    <!-- Finaliza el layout compartido del sistema. -->
    </div>
<!-- Finaliza el contenido de la página. -->
</body>
<!-- Finaliza el documento HTML. -->
</html>
