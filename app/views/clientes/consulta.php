<?php
// Escapa datos dinámicos antes de insertarlos en el documento HTML.
$escaparCliente = static fn (string $valor): string => htmlspecialchars($valor, ENT_QUOTES, 'UTF-8'); // Evita interpretar nombres o direcciones como marcado.
// Conserva la lista recibida del controlador solo cuando su estructura es válida.
$clientesVista = isset($clientes) && is_array($clientes) ? $clientes : []; // Permite mostrar una tabla vacía con seguridad.
// Normaliza el término recibido para devolverlo al campo de búsqueda.
$terminoVista = isset($terminoBusqueda) ? (string) $terminoBusqueda : ''; // Mantiene visible el filtro actual.
// Normaliza el aviso emitido por la acción de consulta.
$mensajeVista = isset($mensaje) ? (string) $mensaje : ''; // Permite indicar cuando un filtro no encuentra resultados.
// Restringe la variante de alerta a clases conocidas de Bootstrap.
$tipoAlertaVista = isset($tipoAlerta) && in_array($tipoAlerta, ['success', 'danger', 'warning', 'info'], true) ? $tipoAlerta : 'warning'; // Evita clases controladas por datos externos.
// Conserva los datos de la sesión ya autorizada para el menú lateral.
$sesionClienteVista = isset($usuarioSesion) && is_array($usuarioSesion) ? $usuarioSesion : []; // Usa identidad validada por el controlador.
// Conserva el cliente seleccionado únicamente cuando el controlador confirmó que existe.
$clienteSeleccionadoVista = isset($clienteSeleccionado) && is_array($clienteSeleccionado) ? $clienteSeleccionado : null; // Evita usar identificadores de cliente sin validar.
?>
<!-- Declara el documento HTML5 de consulta de clientes. -->
<!DOCTYPE html>
<!-- Establece español como idioma del documento. -->
<html lang="es">
<!-- Inicia metadatos y recursos visuales. -->
<head>
    <!-- Define UTF-8 para presentar texto en español. -->
    <meta charset="UTF-8">
    <!-- Ajusta el ancho del contenido al dispositivo. -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Describe la función de esta pantalla. -->
    <meta name="description" content="Consulta el listado de clientes de El Puerquito Digital.">
    <!-- Conserva el tono del layout del Sprint 1 en navegadores móviles. -->
    <meta name="theme-color" content="#f5f2fa">
    <!-- Identifica la pantalla en la pestaña del navegador. -->
    <title>Consultar clientes | El Güero</title>
    <!-- Carga Bootstrap 5.3.3, igual que las vistas del Sprint 1. -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Reutiliza el layout lateral y las tarjetas del módulo de empleados. -->
    <link href="assets/css/empleados.css" rel="stylesheet">
    <!-- Reutiliza los colores compartidos del proyecto. -->
    <link href="assets/css/paleta-rosa.css" rel="stylesheet">
    <!-- Carga la lógica de búsqueda para recargar el listado al borrar el filtro. -->
    <script src="assets/js/clientes.js?v=2" defer></script>
    <!-- Carga el comportamiento compartido de navegación con defer. -->
    <script src="assets/js/navegacion.js?v=menu-persistente-2" defer></script>
<!-- Cierra la cabecera. -->
</head>
<!-- Aplica el fondo y tipografía del sistema. -->
<body class="pagina-empleados">
    <!-- Reutiliza el contenedor de navegación y contenido del Sprint 1. -->
    <div class="contenedor-empleados d-flex min-vh-100">
        <!-- Reutiliza el menú lateral del módulo de empleados. -->
        <aside class="barra-empleados d-flex flex-column p-3" aria-label="Navegación del sistema">
            <!-- Presenta el logotipo y nombre del sistema. -->
            <a class="marca-empleados d-flex align-items-center gap-3 text-decoration-none" href="index.php" aria-label="El Güero, inicio">
                <!-- Usa el logotipo existente. -->
                <img class="logo-sistema" src="assets/logoPIG.png" alt="Logotipo de El Güero">
                <!-- Agrupa las dos líneas de identidad textual. -->
                <span class="d-flex flex-column">
                    <!-- Muestra el nombre comercial del sistema. -->
                    <span class="fw-bold">El Güero</span>
                    <!-- Muestra el descriptor del sistema. -->
                    <span class="small text-secondary">Tocinería · Sistema</span>
                <!-- Cierra el bloque de marca. -->
                </span>
            <!-- Cierra el enlace de marca. -->
            </a>
            <!-- Mantiene el acceso directo al inicio. -->
            <a class="enlace-inicio-navegacion" href="index.php" aria-label="Ir a la pantalla principal">⌂ <span>Inicio</span></a>
            <!-- Organiza las opciones existentes mediante categorías plegables. -->
            <?php require dirname(__DIR__) . '/partials/menu_lateral.php'; ?>
            <!-- Presenta los datos de sesión y el control de salida en el pie. -->
            <div class="sesion-empleado d-flex align-items-center gap-2 mt-auto pt-3">
                <!-- Agrupa el usuario y el rol en dos líneas. -->
                <span class="d-flex flex-column overflow-hidden">
                    <!-- Muestra el usuario escapado. -->
                    <span class="small fw-semibold text-truncate"><?= $escaparCliente((string) ($sesionClienteVista['usuario'] ?? 'Usuario')) ?></span>
                    <!-- Muestra el rol autorizado. -->
                    <span class="small text-secondary"><?= $escaparCliente((string) ($sesionClienteVista['rol'] ?? '')) ?></span>
                <!-- Cierra la identidad de sesión. -->
                </span>
                <!-- Conserva el enlace y estilo de salida del Sprint 1. -->
                <a class="btn btn-sm btn-outline-secondary enlace-cerrar-sesion" href="index.php?c=auth&amp;a=logout">Cerrar sesión</a>
            <!-- Cierra el pie lateral. -->
            </div>
        <!-- Cierra el menú lateral. -->
        </aside>
        <!-- Reutiliza el contenedor principal del Sprint 1. -->
        <main class="contenido-empleados flex-grow-1 p-3 p-md-4 p-xl-5">
            <!-- Reproduce el encabezado de página usado por empleados/lista.php. -->
            <header class="encabezado-pagina mb-4">
                <!-- Identifica la ruta dentro del módulo comercial. -->
                <p class="miga-pan text-secondary small mb-2">Ventas / Clientes</p>
                <!-- Mantiene la misma distribución del encabezado de Sprint 1. -->
                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                    <!-- Agrupa el título y la descripción de la vista. -->
                    <div>
                        <!-- Nombra la vista de consulta. -->
                        <h1 class="h2 fw-bold mb-2">Consultar clientes</h1>
                        <!-- Describe el listado y el filtro disponibles. -->
                        <p class="text-secondary mb-0">Revisa los perfiles registrados o busca por nombre y teléfono.</p>
                    <!-- Cierra el bloque descriptivo. -->
                    </div>
                <!-- Cierra la fila del encabezado. -->
                </div>
            <!-- Cierra el encabezado de página. -->
            </header>
            <!-- Presenta avisos de búsqueda vacía cuando los prepara el controlador. -->
            <?php if ($mensajeVista !== ''): ?>
                <!-- Escapa el aviso y aplica una variante permitida de Bootstrap. -->
                <div class="alert alert-<?= $escaparCliente($tipoAlertaVista) ?>" role="status"><?= $escaparCliente($mensajeVista) ?></div>
            <!-- Finaliza la condición del aviso. -->
            <?php endif; ?>
            <!-- Reutiliza la tarjeta exterior de listado de empleados. -->
            <section class="tarjeta-lista card border-0" aria-labelledby="titulo-tabla-clientes">
                <!-- Reproduce el espaciado interior de la tarjeta original. -->
                <div class="card-body p-3 p-md-4">
                    <!-- Distribuye el título, total y buscador como en la lista Sprint 1. -->
                    <div class="d-flex flex-column flex-xl-row align-items-xl-center justify-content-between gap-3 mb-4">
                        <!-- Resume la cantidad de clientes que aparecen en la tabla. -->
                        <div>
                            <!-- Identifica el listado. -->
                            <h2 class="h5 fw-bold mb-1" id="titulo-tabla-clientes">Clientes registrados</h2>
                            <!-- Muestra el total de resultados devueltos. -->
                            <span class="badge etiqueta-total rounded-pill" data-total-clientes><?= count($clientesVista) ?> clientes</span>
                        <!-- Cierra el resumen del listado. -->
                        </div>
                        <!-- Conserva el estilo de búsqueda y el envío GET como alternativa sin JavaScript. -->
                        <form class="busqueda-empleados input-group" action="index.php" method="get" role="search" data-busqueda-cliente>
                            <!-- Conserva el controlador al filtrar el listado. -->
                            <input type="hidden" name="c" value="cliente">
                            <!-- Conserva la acción separada de consulta. -->
                            <input type="hidden" name="a" value="consulta">
                            <!-- Proporciona una etiqueta accesible al filtro. -->
                            <label class="visually-hidden" for="buscar-cliente">Buscar cliente</label>
                            <!-- Captura el nombre o teléfono para el modelo. -->
                            <input class="form-control" id="buscar-cliente" name="buscar" type="search" value="<?= $escaparCliente($terminoVista) ?>" placeholder="Buscar por nombre o teléfono" autocomplete="off">
                            <!-- Conserva el bloque visual de búsqueda usado en empleados/lista.php. -->
                            <button class="btn btn-outline-secondary" type="submit">Buscar</button>
                        <!-- Cierra el formulario de búsqueda. -->
                        </form>
                    <!-- Cierra la franja de título y búsqueda. -->
                    </div>
                    <!-- Mantiene la tabla desplazable en pantallas estrechas. -->
                    <div class="table-responsive">
                        <!-- Reutiliza las clases de tabla del listado de empleados. -->
                        <table class="table tabla-empleados align-middle mb-0">
                            <!-- Define las columnas de información de clientes. -->
                            <thead>
                                <!-- Presenta los nombres de las columnas de la tabla. -->
                                <tr>
                                    <!-- Encabeza el identificador del cliente. -->
                                    <th scope="col">ID</th>
                                    <!-- Encabeza el nombre comercial o razón social. -->
                                    <th scope="col">Nombre / Razón Social</th>
                                    <!-- Encabeza el teléfono de contacto. -->
                                    <th scope="col">Teléfono</th>
                                    <!-- Encabeza la dirección disponible. -->
                                    <th scope="col">Dirección</th>
                                    <!-- Encabeza la acción para preparar una venta con el cliente. -->
                                    <th scope="col" class="text-end">Acción</th>
                                <!-- Cierra la fila de encabezados. -->
                                </tr>
                            <!-- Cierra la cabecera de tabla. -->
                            </thead>
                            <!-- Presenta todas las filas devueltas por el controlador. -->
                            <tbody>
                                <!-- Recorre los clientes ordenados por su identificador. -->
                                <?php foreach ($clientesVista as $clienteVista): ?>
                                    <!-- Renderiza un cliente en una fila segura. -->
                                    <tr data-fila-cliente data-texto-cliente="<?= $escaparCliente((string) ($clienteVista['nombre_razonsocial'] ?? '') . ' ' . (string) ($clienteVista['telefono'] ?? '')) ?>">
                                        <!-- Muestra el identificador como valor entero. -->
                                        <td class="text-secondary">#<?= (int) ($clienteVista['id_cliente'] ?? 0) ?></td>
                                        <!-- Escapa y destaca el nombre del cliente. -->
                                        <td class="fw-semibold"><?= $escaparCliente((string) ($clienteVista['nombre_razonsocial'] ?? '')) ?></td>
                                        <!-- Escapa el teléfono conservado como texto. -->
                                        <td><?= $escaparCliente((string) ($clienteVista['telefono'] ?? '')) ?></td>
                                        <!-- Compone la dirección solo con sus partes disponibles. -->
                                        <td><?= $escaparCliente(implode(', ', array_filter([(string) ($clienteVista['calle'] ?? ''), (string) ($clienteVista['colonia'] ?? ''), (string) ($clienteVista['codigo_postal'] ?? ''), (string) ($clienteVista['ciudad'] ?? '')]))) ?: '—' ?></td>
                                        <!-- Permite seleccionar este cliente y mostrar su ficha al pie de la página. -->
                                        <td class="text-end">
                                            <!-- Abre nuevamente la consulta con el identificador seleccionado. -->
                                            <a class="btn btn-sm <?= $clienteSeleccionadoVista !== null && (int) ($clienteSeleccionadoVista['id_cliente'] ?? 0) === (int) ($clienteVista['id_cliente'] ?? 0) ? 'btn-primary' : 'btn-outline-primary' ?>" href="index.php?c=cliente&amp;a=consulta&amp;seleccionar=<?= (int) ($clienteVista['id_cliente'] ?? 0) ?>" <?= $clienteSeleccionadoVista !== null && (int) ($clienteSeleccionadoVista['id_cliente'] ?? 0) === (int) ($clienteVista['id_cliente'] ?? 0) ? 'aria-current="true"' : '' ?>><?= $clienteSeleccionadoVista !== null && (int) ($clienteSeleccionadoVista['id_cliente'] ?? 0) === (int) ($clienteVista['id_cliente'] ?? 0) ? 'Seleccionado' : 'Seleccionar' ?></a>
                                        <!-- Cierra la acción de selección. -->
                                        </td>
                                    <!-- Cierra la fila del cliente. -->
                                    </tr>
                                <!-- Finaliza el recorrido de resultados. -->
                                <?php endforeach; ?>
                                <!-- Reserva una fila para avisar que el filtro no encontró coincidencias. -->
                                <tr data-sin-resultados hidden><td class="text-center text-secondary py-4" colspan="5">No hay clientes que coincidan con la búsqueda.</td></tr>
                                <!-- Comunica que aún no hay clientes si la tabla está vacía sin filtro. -->
                                <?php if ($clientesVista === [] && $terminoVista === ''): ?>
                                    <!-- Ocupa todas las columnas con un mensaje explícito. -->
                                    <tr><td class="text-center text-secondary py-4" colspan="5">No hay clientes registrados.</td></tr>
                                <!-- Finaliza el estado de listado vacío. -->
                                <?php endif; ?>
                            <!-- Cierra el cuerpo de la tabla. -->
                            </tbody>
                        <!-- Cierra la tabla de clientes. -->
                        </table>
                    <!-- Cierra el contenedor adaptable de tabla. -->
                    </div>
                <!-- Cierra el cuerpo de la tarjeta. -->
                </div>
            <!-- Cierra la tarjeta de listado. -->
            </section>
            <!-- Confirma al final de la página el cliente elegido mediante una notificación compacta. -->
            <?php if ($clienteSeleccionadoVista !== null): ?>
                <!-- Presenta la confirmación de selección con el componente de alerta de Bootstrap. -->
                <div class="alert alert-success mt-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3" role="status" aria-labelledby="titulo-cliente-seleccionado">
                    <!-- Resume el cliente que se usará al continuar con la venta. -->
                    <div>
                        <!-- Identifica la notificación de selección completada. -->
                        <h2 class="h6 fw-bold mb-1" id="titulo-cliente-seleccionado">Cliente seleccionado para la venta</h2>
                        <!-- Muestra el nombre y el teléfono como referencia breve. -->
                        <p class="mb-0"><?= $escaparCliente((string) ($clienteSeleccionadoVista['nombre_razonsocial'] ?? '')) ?> · <?= $escaparCliente((string) ($clienteSeleccionadoVista['telefono'] ?? 'No registrado')) ?></p>
                        <!-- Muestra la dirección resumida sin una ficha de datos extensa. -->
                        <p class="small mb-0"><?= $escaparCliente(implode(', ', array_filter([(string) ($clienteSeleccionadoVista['calle'] ?? ''), (string) ($clienteSeleccionadoVista['colonia'] ?? ''), (string) ($clienteSeleccionadoVista['codigo_postal'] ?? ''), (string) ($clienteSeleccionadoVista['ciudad'] ?? '')])) ?: 'N/A') ?></p>
                    <!-- Cierra el resumen notificado del cliente. -->
                    </div>
                    <!-- Conserva el acceso para abrir la venta con el cliente confirmado. -->
                    <a class="btn btn-primary flex-shrink-0" href="index.php?c=venta&amp;a=nueva-cliente&amp;id_cliente=<?= (int) ($clienteSeleccionadoVista['id_cliente'] ?? 0) ?>">Realizar venta con este cliente</a>
                <!-- Cierra la notificación de cliente seleccionado. -->
                </div>
            <!-- Oculta la notificación hasta que se seleccione un cliente. -->
            <?php endif; ?>
        <!-- Cierra el contenido principal. -->
        </main>
    <!-- Cierra el contenedor general. -->
    </div>
<!-- Cierra el cuerpo del documento. -->
</body>
<!-- Cierra el documento HTML. -->
</html>
