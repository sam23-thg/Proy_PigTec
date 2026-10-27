<?php
// Prepara una función para escapar todas las cadenas dinámicas de la vista.
$escaparVenta = static fn (string $valor): string => htmlspecialchars($valor, ENT_QUOTES, 'UTF-8'); // Evita insertar marcado ejecutable desde la base o la sesión.
// Garantiza que la vista reciba el listado de clientes como arreglo.
$clientesVentaVista = isset($clientesVista) && is_array($clientesVista) ? $clientesVista : []; // Evita advertencias con una colección no preparada.
// Garantiza que la vista reciba el catálogo de productos activos como arreglo.
$productosVentaVista = isset($productosVista) && is_array($productosVista) ? $productosVista : []; // Facilita presentar opciones de búsqueda seguras.
// Garantiza que el carrito conservado después de un error sea un arreglo.
$carritoVentaVista = isset($carritoVista) && is_array($carritoVista) ? $carritoVista : []; // Mantiene los artículos si el servidor rechaza la nota.
// Garantiza que los datos de la sesión estén disponibles para el menú.
$sesionVentaVista = isset($usuarioSesion) && is_array($usuarioSesion) ? $usuarioSesion : []; // Reutiliza únicamente la identidad autenticada.
// Lee el rol que controla los enlaces visibles en la navegación lateral.
$rolVentaVista = (string) ($sesionVentaVista['rol'] ?? ''); // No obtiene permisos desde la solicitud HTTP.
// Limita el tipo de alerta a variantes conocidas de Bootstrap.
$tipoAlertaVenta = isset($tipoAlerta) && in_array($tipoAlerta, ['success', 'danger', 'warning', 'info'], true) ? $tipoAlerta : 'danger'; // Evita clases CSS arbitrarias.
// Normaliza el mensaje del controlador antes de presentarlo.
$mensajeVentaVista = isset($mensaje) ? (string) $mensaje : ''; // Mantiene la plantilla segura ante una carga inicial.
// Serializa el carrito inicial para que JavaScript pueda reconstruirlo después de un error.
$carritoInicialSerializado = json_encode($carritoVentaVista, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG); // Codifica caracteres especiales para un atributo HTML.
// Escapa la representación JSON que se coloca como atributo del contenedor.
$carritoInicialSeguro = htmlspecialchars(is_string($carritoInicialSerializado) ? $carritoInicialSerializado : '[]', ENT_QUOTES, 'UTF-8'); // Evita romper el documento con datos del carrito.
// Conserva el método de pago seleccionado tras una respuesta de error.
$metodoPagoSeleccionado = (string) ($datosFormulario['metodo_pago'] ?? 'EFECTIVO'); // Conserva efectivo como método inicial y tras errores.
// Conserva el cliente seleccionado tras una respuesta de error.
$clienteSeleccionado = (string) ($datosFormulario['id_cliente'] ?? ''); // Permite corregir el formulario sin perder su contexto.
// Conserva la información del cliente fijado por el controlador para el modo actual.
$clienteVentaSeleccionadoVista = isset($clienteVentaSeleccionado) && is_array($clienteVentaSeleccionado) ? $clienteVentaSeleccionado : null; // Evita usar perfiles no confirmados desde la solicitud.
// Identifica si esta pantalla corresponde a una venta local o a una cuenta concreta.
$ventaClienteEspecificoVista = isset($modoVentaClienteEspecifico) && $modoVentaClienteEspecifico === true; // Mantiene el tipo de venta decidido en el servidor.
// Construye la ruta de POST de acuerdo con el modo y conserva el ID específico en la URL.
$accionVentaVista = $ventaClienteEspecificoVista && $clienteVentaSeleccionadoVista !== null ? 'index.php?c=venta&amp;a=nueva-cliente&amp;id_cliente=' . (int) $clienteVentaSeleccionadoVista['id_cliente'] : 'index.php?c=venta&amp;a=nueva'; // Evita perder el contexto de cliente durante el POST.
// Conserva los mensajes de validación preparados por el controlador.
$erroresCamposVenta = isset($erroresCampos) && is_array($erroresCampos) ? $erroresCampos : []; // Permite marcar errores sin depender del envío POST.
// Conserva los errores de cantidad asociados a cada producto del carrito.
$erroresProductosVenta = isset($erroresProductos) && is_array($erroresProductos) ? $erroresProductos : []; // Vincula el rechazo del trigger con una línea concreta.
// Serializa los mensajes por producto para que la tabla dinámica los presente con texto seguro.
$erroresProductosSerializados = json_encode($erroresProductosVenta, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG); // Codifica caracteres especiales antes de colocarlos en HTML.
// Escapa el JSON para guardarlo como atributo HTML de la tarjeta.
$erroresProductosSeguros = htmlspecialchars(is_string($erroresProductosSerializados) ? $erroresProductosSerializados : '{}', ENT_QUOTES, 'UTF-8'); // Evita alterar el documento con datos del servidor.
// Obtiene el mensaje de validación del selector de cliente.
$errorClienteVenta = is_string($erroresCamposVenta['id_cliente'] ?? null) ? $erroresCamposVenta['id_cliente'] : ''; // Normaliza el texto mostrado junto al campo.
// Obtiene el mensaje de validación del método de pago.
$errorPagoVenta = is_string($erroresCamposVenta['metodo_pago'] ?? null) ? $erroresCamposVenta['metodo_pago'] : ''; // Normaliza el texto mostrado junto al campo.
// Obtiene el mensaje de validación general del detalle de productos.
$errorDetalleVenta = is_string($erroresCamposVenta['productos'] ?? null) ? $erroresCamposVenta['productos'] : ''; // Normaliza el texto mostrado junto a la tabla.
?>
<!-- Declara el documento HTML5 del punto de venta. -->
<!DOCTYPE html>
<!-- Establece español como idioma de la interfaz. -->
<html lang="es">
<!-- Inicia la cabecera del documento con sus recursos. -->
<head>
    <!-- Define la codificación necesaria para el texto en español. -->
    <meta charset="UTF-8">
    <!-- Adapta la anchura del diseño al dispositivo. -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Describe el propósito del punto de venta. -->
    <meta name="description" content="Registra ventas y administra los artículos de la nota en El Puerquito Digital.">
    <!-- Aplica el tono cálido del sistema en la barra del navegador móvil. -->
    <meta name="theme-color" content="#fff5f8">
    <!-- Define el título de la pestaña. -->
    <title>Registrar venta | El Puerquito Digital</title>
    <!-- Carga Bootstrap 5.3.3 para la cuadrícula y componentes de la pantalla. -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Reutiliza el layout y la barra lateral de los módulos anteriores. -->
    <link href="assets/css/empleados.css" rel="stylesheet">
    <!-- Reutiliza los tonos cálidos definidos para la interfaz. -->
    <link href="assets/css/paleta-rosa.css" rel="stylesheet">
    <!-- Reutiliza las superficies y controles de las tarjetas de producto. -->
    <link href="assets/css/productos.css" rel="stylesheet">
    <!-- Carga el comportamiento del carrito desde un archivo JavaScript independiente. -->
    <script src="assets/js/ventas.js?v=2" defer></script>
    <!-- Carga el comportamiento existente para abrir y cerrar categorías laterales. -->
    <script src="assets/js/navegacion.js?v=menu-persistente-2" defer></script>
<!-- Cierra la cabecera del documento. -->
</head>
<!-- Inicia la página con el fondo y layout común del sistema. -->
<body class="pagina-empleados">
    <!-- Distribuye la navegación lateral y el contenido del punto de venta. -->
    <div class="contenedor-empleados d-flex min-vh-100">
        <!-- Reutiliza la barra lateral cálida de los demás módulos. -->
        <aside class="barra-empleados d-flex flex-column p-3" aria-label="Navegación del sistema">
            <!-- Muestra la marca y lleva al inicio del sistema. -->
            <a class="marca-empleados d-flex align-items-center gap-3 text-decoration-none" href="index.php" aria-label="El Güero, inicio">
                <!-- Presenta el logotipo oficial del sistema. -->
                <img class="logo-sistema" src="assets/logoPIG.png" alt="Logotipo de El Güero">
                <!-- Agrupa el nombre comercial y su descriptor. -->
                <span class="d-flex flex-column">
                    <!-- Muestra el nombre comercial. -->
                    <span class="fw-bold">El Güero</span>
                    <!-- Muestra el descriptor de la aplicación. -->
                    <span class="small text-secondary">Tocinería · Sistema</span>
                <!-- Cierra el bloque textual de marca. -->
                </span>
            <!-- Cierra el enlace de marca. -->
            </a>
            <!-- Proporciona el enlace común a Inicio. -->
            <a class="enlace-inicio-navegacion" href="index.php" aria-label="Ir a la pantalla principal">⌂ <span>Inicio</span></a>
            <!-- Agrupa los accesos según el rol autenticado. -->
            <?php require dirname(__DIR__) . '/partials/menu_lateral.php'; ?>
            <!-- Muestra el usuario activo y su rol en el pie del menú. -->
            <div class="sesion-empleado d-flex align-items-center gap-2 mt-auto pt-3 border-top">
                <!-- Agrupa la identidad de sesión en dos líneas. -->
                <span class="d-flex flex-column overflow-hidden">
                    <!-- Escapa el usuario antes de presentarlo. -->
                    <span class="small fw-semibold text-truncate"><?= $escaparVenta((string) ($sesionVentaVista['usuario'] ?? 'Usuario')) ?></span>
                    <!-- Escapa el rol antes de presentarlo. -->
                    <span class="small text-secondary"><?= $escaparVenta($rolVentaVista) ?></span>
                <!-- Cierra los datos de sesión. -->
                </span>
                <!-- Mantiene disponible el enlace común para cerrar sesión. -->
                <a class="btn btn-sm btn-outline-secondary enlace-cerrar-sesion" href="index.php?c=auth&amp;a=logout">Cerrar sesión</a>
            <!-- Cierra el pie de usuario. -->
            </div>
        <!-- Cierra el menú lateral. -->
        </aside>
        <!-- Inicia el área central del punto de venta con el espaciado común. -->
        <main class="contenido-empleados flex-grow-1 p-3 p-md-4 p-xl-5">
            <!-- Presenta la ruta, título y descripción de la pantalla. -->
            <header class="encabezado-productos mb-4">
                <!-- Muestra la ubicación dentro de ventas y caja. -->
                <p class="miga-pan text-secondary small mb-2">Ventas y caja / <?= $ventaClienteEspecificoVista ? 'Venta por cliente' : 'Registrar ventas' ?></p>
                <!-- Nombra la acción principal de la vista. -->
                <h1 class="h2 fw-bold mb-2"><?= $ventaClienteEspecificoVista ? 'Registrar venta para cliente' : 'Registrar ventas' ?></h1>
                <!-- Explica el propósito del flujo de captura. -->
                <p class="text-secondary mb-0">Registra la venta y los productos incluidos en la nota.</p>
            <!-- Cierra el encabezado de página. -->
            </header>
            <!-- Presenta el mensaje de error o confirmación preparado por el controlador. -->
            <?php if ($mensajeVentaVista !== ''): ?>
                <!-- Escapa el mensaje y usa una variante segura de alerta Bootstrap. -->
                <div class="alert alert-<?= $escaparVenta($tipoAlertaVenta) ?>" role="alert"><?= $escaparVenta($mensajeVentaVista) ?></div>
            <!-- Finaliza la alerta opcional. -->
            <?php endif; ?>
            <!-- Envía cliente, pago y artículos al controlador mediante POST. -->
            <form action="<?= $accionVentaVista ?>" method="post" data-formulario-venta novalidate>
                <!-- Distribuye los datos de la nota en dos campos responsivos. -->
                <div class="row g-3 mb-4">
                    <!-- Aloja la selección de cliente. -->
                    <div class="col-12 col-md-6">
                        <!-- Asocia una etiqueta accesible al cliente fijo de esta venta. -->
                        <span class="form-label fw-semibold d-block" id="etiqueta-cliente-venta">Cliente <span class="text-danger">*</span></span>
                        <!-- Envía el identificador del cliente fijado y validado por el servidor. -->
                        <input type="hidden" name="id_cliente" value="<?= $escaparVenta($clienteSeleccionado) ?>" data-cliente-venta aria-labelledby="etiqueta-cliente-venta" aria-describedby="error-cliente-venta">
                        <!-- Presenta la información del cliente dentro del formulario de venta. -->
                        <div class="border rounded-3 bg-white p-3 <?= $errorClienteVenta !== '' ? 'border-danger' : 'border-light' ?>">
                            <!-- Muestra el nombre del perfil que recibirá esta nota. -->
                            <p class="fw-semibold mb-1"><?= $escaparVenta((string) ($clienteVentaSeleccionadoVista['nombre_razonsocial'] ?? 'Cliente no disponible')) ?></p>
                            <!-- Muestra teléfono y dirección únicamente en ventas asignadas a un cliente específico. -->
                            <?php if ($ventaClienteEspecificoVista): ?>
                                <!-- Muestra el teléfono de contacto del cliente fijo. -->
                                <p class="small text-secondary mb-1">Teléfono: <?= $escaparVenta((string) ($clienteVentaSeleccionadoVista['telefono'] ?? 'No registrado')) ?></p>
                                <!-- Compone y presenta la dirección guardada para el cliente. -->
                                <p class="small text-secondary mb-0">Dirección: <?= $escaparVenta(implode(', ', array_filter([(string) ($clienteVentaSeleccionadoVista['calle'] ?? ''), (string) ($clienteVentaSeleccionadoVista['colonia'] ?? ''), (string) ($clienteVentaSeleccionadoVista['codigo_postal'] ?? ''), (string) ($clienteVentaSeleccionadoVista['ciudad'] ?? '')])) ?: 'N/A') ?></p>
                            <!-- Oculta los datos personales de contacto para ventas de mostrador. -->
                            <?php endif; ?>
                        <!-- Cierra la ficha de información del cliente. -->
                        </div>
                        <!-- Muestra cualquier error de validación junto al cliente fijo. -->
                        <div class="invalid-feedback <?= $errorClienteVenta !== '' ? 'd-block' : '' ?>" id="error-cliente-venta" data-error-cliente><?= $escaparVenta($errorClienteVenta) ?></div>
                    <!-- Cierra el campo de cliente. -->
                    </div>
                    <!-- Aloja la selección del método de pago. -->
                    <div class="col-12 col-md-6">
                        <!-- Asocia una etiqueta accesible al método de pago. -->
                        <label class="form-label fw-semibold" for="metodo-pago">Método de pago <span class="text-danger">*</span></label>
                        <!-- Restringe la selección a los valores ENUM documentados. -->
                        <select class="form-select <?= $errorPagoVenta !== '' ? 'is-invalid' : '' ?>" id="metodo-pago" name="metodo_pago" required aria-describedby="error-metodo-pago" aria-invalid="<?= $errorPagoVenta !== '' ? 'true' : 'false' ?>">
                            <!-- Mantiene efectivo como única forma habilitada y predeterminada actualmente. -->
                            <option value="EFECTIVO" <?= $metodoPagoSeleccionado === 'EFECTIVO' ? 'selected' : '' ?>>Efectivo</option>
                            <!-- Conserva la opción futura de tarjeta visible, pero no seleccionable por ahora. -->
                            <option value="TARJETA" disabled>Tarjeta (próximamente)</option>
                            <!-- Conserva la opción futura de transferencia visible, pero no seleccionable por ahora. -->
                            <option value="TRANSFERENCIA" disabled>Transferencia (próximamente)</option>
                        <!-- Cierra la lista de métodos de pago. -->
                        </select>
                        <!-- Muestra el error específico del método de pago y permite actualizarlo desde JavaScript. -->
                        <div class="invalid-feedback" id="error-metodo-pago" data-error-pago><?= $escaparVenta($errorPagoVenta) ?></div>
                    <!-- Cierra el campo de pago. -->
                    </div>
                <!-- Cierra la fila de datos principales de la nota. -->
                </div>
                <!-- Divide la captura de artículos y el resumen de venta en dos paneles. -->
                <div class="row g-4 align-items-start">
                    <!-- Aloja el buscador y la captura de cantidades. -->
                    <div class="col-12 col-xl-5">
                        <!-- Reutiliza la tarjeta visual de los formularios de producto. -->
                        <section class="tarjeta-producto card border-0 h-100" aria-labelledby="titulo-agregar-producto">
                            <!-- Aplica el espaciado de tarjeta ya definido en productos. -->
                            <div class="card-body p-3 p-md-4">
                                <!-- Presenta el título y la regla de cantidad. -->
                                <div class="mb-4 pb-2 border-bottom">
                                    <!-- Nombra el panel de captura de artículos. -->
                                    <h2 class="h5 fw-bold mb-1" id="titulo-agregar-producto">Agregar producto</h2>
                                    <!-- Explica el campo obligatorio de kilogramos. -->
                                    <p class="small text-secondary mb-0">Los campos marcados con <span class="text-danger fw-bold">*</span> son obligatorios.</p>
                                <!-- Cierra la cabecera del panel. -->
                                </div>
                                <!-- Agrupa los controles para seleccionar un producto. -->
                                <div class="mb-3">
                                    <!-- Asocia la etiqueta al campo de búsqueda. -->
                                    <label class="form-label fw-semibold" for="buscar-producto-venta">Buscar producto <span class="text-danger">*</span></label>
                                    <!-- Mantiene el campo y las sugerencias en el mismo contexto posicionado. -->
                                    <div class="position-relative">
                                        <!-- Busca por nombre y ofrece las opciones cargadas desde el catálogo. -->
                                        <input class="form-control" id="buscar-producto-venta" type="search" placeholder="Ej. Tocino ahumado, Chicharrón..." autocomplete="off" required data-buscar-producto aria-controls="sugerencias-producto-venta" aria-expanded="false" aria-describedby="error-buscar-producto">
                                        <!-- Guarda el ID elegido sin aceptar identificadores escritos a mano. -->
                                        <input type="hidden" data-id-producto-seleccionado>
                                        <!-- Presenta sugerencias filtrables debajo del campo de búsqueda. -->
                                        <div class="list-group position-absolute top-100 start-0 w-100 shadow z-3" id="sugerencias-producto-venta" data-lista-sugerencias hidden role="listbox">
                                            <!-- Recorre el catálogo activo de productos y existencias. -->
                                            <?php foreach ($productosVentaVista as $productoVenta): ?>
                                                <!-- Construye una opción accesible con datos escapados del producto. -->
                                                <button class="list-group-item list-group-item-action" type="button" role="option" data-opcion-producto data-id="<?= (int) $productoVenta['id_producto'] ?>" data-nombre="<?= $escaparVenta((string) $productoVenta['nombre_producto']) ?>" data-corte="<?= $escaparVenta((string) ($productoVenta['tipo_corte'] ?? '')) ?>" data-precio="<?= $escaparVenta((string) $productoVenta['precio_kg']) ?>" data-stock="<?= $escaparVenta((string) $productoVenta['cantidad_kg']) ?>">
                                                    <!-- Muestra solamente el nombre y el corte del producto. -->
                                                    <span class="fw-semibold"><?= $escaparVenta((string) $productoVenta['nombre_producto']) ?> <span class="small text-secondary"><?= $escaparVenta((string) ($productoVenta['tipo_corte'] ?? '')) ?></span></span>
                                                <!-- Cierra la opción de producto. -->
                                                </button>
                                            <!-- Finaliza el recorrido de productos. -->
                                            <?php endforeach; ?>
                                        <!-- Cierra el listado flotante de sugerencias. -->
                                        </div>
                                    <!-- Cierra el contenedor del buscador. -->
                                    </div>
                                    <!-- Muestra el error específico del buscador de producto. -->
                                    <div class="invalid-feedback" id="error-buscar-producto" data-error-busqueda-producto><?= $escaparVenta((string) ($erroresCamposVenta['buscar_producto'] ?? '')) ?></div>
                                    <!-- Presenta solo los kilogramos que quedan disponibles para agregar. -->
                                    <div class="form-text" data-disponibilidad-producto aria-live="polite" hidden></div>
                                <!-- Cierra el campo de búsqueda. -->
                                </div>
                                <!-- Agrupa la captura de kilogramos para el artículo seleccionado. -->
                                <div class="mb-4">
                                    <!-- Asocia la etiqueta al campo decimal de cantidad. -->
                                    <label class="form-label fw-semibold" for="cantidad-producto-venta">Cantidad (kg) <span class="text-danger">*</span></label>
                                    <!-- Limita el formato del campo a cantidades positivas de tres decimales. -->
                                    <input class="form-control" id="cantidad-producto-venta" type="number" min="0.001" max="9999999.999" step="0.001" placeholder="Ej. 1.500" required data-cantidad-producto aria-describedby="error-cantidad-producto">
                                    <!-- Explica errores de formato, rango o existencia de la cantidad. -->
                                    <div class="invalid-feedback" id="error-cantidad-producto" data-error-cantidad><?= $escaparVenta((string) ($erroresCamposVenta['cantidad_producto'] ?? '')) ?></div>
                                <!-- Cierra el campo de cantidad. -->
                                </div>
                                <!-- Agrega la selección al carrito sin enviar la venta. -->
                                <button class="btn btn-primary w-100" type="button" data-agregar-producto>Agregar a la venta</button>
                                <!-- Reserva espacio para avisos de captura del producto. -->
                                <p class="small text-danger mt-3 mb-0" data-aviso-producto role="status" hidden></p>
                            <!-- Cierra el cuerpo de la tarjeta de captura. -->
                            </div>
                        <!-- Cierra la tarjeta de captura. -->
                        </section>
                    <!-- Cierra la columna del buscador. -->
                    </div>
                    <!-- Aloja la tabla del carrito y los totales de la nota. -->
                    <div class="col-12 col-xl-7">
                        <!-- Reutiliza la misma tarjeta visual para los artículos de venta. -->
                        <section class="tarjeta-producto card border-0" aria-labelledby="titulo-productos-venta" data-carrito-inicial="<?= $carritoInicialSeguro ?>" data-errores-productos-iniciales="<?= $erroresProductosSeguros ?>">
                            <!-- Aplica el espaciado interior definido en los formularios del sistema. -->
                            <div class="card-body p-3 p-md-4">
                                <!-- Presenta el título, conteo y acción para vaciar el carrito. -->
                                <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
                                    <!-- Agrupa el título del carrito y su número de artículos. -->
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <!-- Nombra el listado dinámico de artículos. -->
                                        <h2 class="h5 fw-bold mb-0" id="titulo-productos-venta">Productos en la venta</h2>
                                        <!-- Actualiza el conteo visible de productos agregados. -->
                                        <span class="badge etiqueta-total rounded-pill" data-conteo-carrito>0 productos</span>
                                    <!-- Cierra el encabezado del carrito. -->
                                    </div>
                                    <!-- Vacía el detalle sin enviar la nota. -->
                                    <button class="btn btn-sm btn-outline-secondary" type="button" data-vaciar-carrito>Vaciar</button>
                                <!-- Cierra la fila del encabezado de carrito. -->
                                </div>
                                <!-- Permite desplazar la tabla en pantallas pequeñas. -->
                                <div class="table-responsive">
                                    <!-- Presenta el detalle dinámico de artículos y subtotales. -->
                                    <table class="table tabla-empleados align-middle mb-0">
                                        <!-- Identifica el propósito de cada columna. -->
                                        <thead>
                                            <!-- Agrupa las etiquetas de cabecera. -->
                                            <tr>
                                                <!-- Encabeza la descripción del artículo. -->
                                                <th scope="col">Producto</th>
                                                <!-- Encabeza el precio unitario por kilogramo. -->
                                                <th scope="col">Precio u.</th>
                                                <!-- Encabeza la cantidad agregada. -->
                                                <th scope="col">Cant. (kg)</th>
                                                <!-- Encabeza el importe de la línea. -->
                                                <th scope="col">Subtotal</th>
                                                <!-- Encabeza las acciones disponibles para la línea. -->
                                                <th scope="col"><span class="visually-hidden">Acción</span></th>
                                            <!-- Cierra la fila de encabezados. -->
                                            </tr>
                                        <!-- Cierra la cabecera de la tabla. -->
                                        </thead>
                                        <!-- Reserva el cuerpo que JavaScript construirá de forma segura. -->
                                        <tbody data-cuerpo-carrito>
                                            <!-- Informa el estado vacío inicial del carrito. -->
                                            <tr data-carrito-vacio>
                                                <!-- Explica que aún no se han añadido productos. -->
                                                <td class="text-center text-secondary py-5" colspan="6">No hay productos agregados. Busca y selecciona un producto para incluirlo en la nota.</td>
                                            <!-- Cierra la fila vacía del carrito. -->
                                            </tr>
                                        <!-- Cierra el cuerpo del detalle dinámico. -->
                                        </tbody>
                                    <!-- Cierra la tabla de venta. -->
                                    </table>
                                <!-- Cierra el contenedor responsivo de tabla. -->
                                </div>
                                <!-- Reserva el contenedor de campos repetidos para POST. -->
                                <div data-detalle-oculto></div>
                                <!-- Muestra errores detectados por el servidor en el detalle de venta. -->
                                <div class="invalid-feedback <?= $errorDetalleVenta !== '' ? 'd-block' : '' ?>" data-error-carrito role="alert"><?= $escaparVenta($errorDetalleVenta) ?></div>
                                <!-- Resume subtotal, IVA y total de la nota. -->
                                <div class="mt-4 p-3 rounded-3 border bg-light">
                                    <!-- Presenta el subtotal antes del impuesto. -->
                                    <div class="d-flex justify-content-between small mb-2"><span>Subtotal:</span><strong data-subtotal-venta>$0.00</strong></div>
                                    <!-- Presenta el IVA que se suma al subtotal. -->
                                    <div class="d-flex justify-content-between small mb-2"><span>IVA (16%):</span><strong data-iva-venta>$0.00</strong></div>
                                    <!-- Presenta el total final de la nota. -->
                                    <div class="d-flex justify-content-between border-top pt-2 fw-bold"><span>Total a pagar:</span><strong data-total-venta>$0.00</strong></div>
                                <!-- Cierra el resumen de importes. -->
                                </div>
                                <!-- Presenta acciones para cancelar o confirmar la nota. -->
                                <div class="d-flex flex-column flex-sm-row justify-content-end gap-2 mt-3 pt-3 border-top">
                                    <!-- Regresa al listado de clientes sin guardar una venta. -->
                                    <a class="btn btn-outline-secondary px-4" href="index.php?c=cliente&amp;a=consulta">Cancelar</a>
                                    <!-- Envía el cliente, pago y carrito al controlador transaccional. -->
                                    <button class="btn btn-primary px-4" type="submit" data-confirmar-venta>Confirmar venta</button>
                                <!-- Cierra la fila de acciones. -->
                                </div>
                            <!-- Cierra el cuerpo de la tarjeta de carrito. -->
                            </div>
                        <!-- Cierra la tarjeta de carrito. -->
                        </section>
                    <!-- Cierra la columna del detalle. -->
                    </div>
                <!-- Cierra la cuadrícula de captura y detalle. -->
                </div>
            <!-- Cierra el formulario de venta. -->
            </form>
        <!-- Cierra el área principal del punto de venta. -->
        </main>
    <!-- Cierra la distribución de página. -->
    </div>
<!-- Cierra el cuerpo del documento. -->
</body>
<!-- Cierra el documento HTML. -->
</html>
