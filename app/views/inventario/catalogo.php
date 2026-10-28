<?php
// Prepara una función de escape para valores dinámicos que se insertan en el HTML.
$escaparCatalogo = static fn (string $valor): string => htmlspecialchars($valor, ENT_QUOTES, 'UTF-8'); // Evita interpretar nombres como marcado ejecutable.
// Garantiza que el controlador entregue una colección de productos en forma de arreglo.
$catalogoVista = isset($catalogoProductos) && is_array($catalogoProductos) ? $catalogoProductos : []; // Evita referencias indefinidas al cargar la plantilla.
// Conserva la identidad de sesión validada por el controlador.
$sesionCatalogoVista = isset($usuarioSesion) && is_array($usuarioSesion) ? $usuarioSesion : []; // Reutiliza los datos autenticados para el menú.
// Lee el rol autenticado para controlar las categorías que se muestran.
$rolCatalogoVista = (string) ($sesionCatalogoVista['rol'] ?? ''); // No obtiene permisos desde la petición del navegador.
// Inicializa los datos de resumen y los tipos de corte disponibles para los filtros.
$productosEnStock = 0; // Cuenta productos con cualquier existencia positiva.
$productosConAlerta = 0; // Cuenta productos con cinco kilogramos o menos.
$tiposCorteCatalogo = []; // Agrupa los tipos de corte presentes en la consulta.
// Recorre el catálogo una vez para preparar los indicadores y filtros de pantalla.
foreach ($catalogoVista as $productoResumen) { // Usa los datos ya recuperados por el modelo.
    // Convierte la existencia a número antes de clasificar el producto.
    $existenciaResumen = (float) ($productoResumen['cantidad_kg'] ?? 0); // Evita depender de la representación decimal de PDO.
    // Cuenta los productos con disponibilidad para venta.
    if ($existenciaResumen > 0) { // Separa agotados de productos que conservan stock.
        $productosEnStock++; // Actualiza la tarjeta de productos disponibles.
    } // Finaliza el conteo de existencias positivas.
    // Considera baja o agotada la existencia de cinco kilogramos o menos.
    if ($existenciaResumen <= 5) { // Usa un umbral visual explícito para las alertas del catálogo.
        $productosConAlerta++; // Actualiza la tarjeta de productos por revisar.
    } // Finaliza el conteo de alertas de stock.
    // Normaliza el tipo de corte para presentarlo como filtro de categoría.
    $tipoCorteResumen = trim((string) ($productoResumen['tipo_corte'] ?? '')); // Conserva el valor del esquema actual sin inventar columnas.
    // Usa una etiqueta común cuando el producto no tiene tipo de corte.
    $tipoCorteResumen = $tipoCorteResumen !== '' ? $tipoCorteResumen : 'Sin especificar'; // Permite filtrar también productos sin detalle.
    // Agrega el filtro solo cuando aún no existe una categoría equivalente.
$tiposCorteCatalogo[strtolower($tipoCorteResumen)] = $tipoCorteResumen; // Evita chips duplicados por mayúsculas sin requerir extensiones.
} // Finaliza el cálculo de indicadores y categorías visibles.
// Convierte los tipos únicos a una lista sencilla para recorrerla en la plantilla.
$tiposCorteCatalogo = array_values($tiposCorteCatalogo); // Mantiene el orden en que aparecen en el catálogo.
?>
<!-- Declara el documento HTML5 de la consulta de catálogo. -->
<!DOCTYPE html>
<!-- Establece el idioma español para la página y tecnologías de asistencia. -->
<html lang="es">
<!-- Inicia la cabecera con metadatos y recursos visuales. -->
<head>
    <!-- Define la codificación para acentos y caracteres del idioma. -->
    <meta charset="UTF-8">
    <!-- Ajusta el ancho de la interfaz al tamaño de cada dispositivo. -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Describe la página de disponibilidad de inventario. -->
    <meta name="description" content="Consulta los productos y existencias disponibles en El Puerquito Digital.">
    <!-- Aplica el color cálido del sistema en las barras del navegador móvil. -->
    <meta name="theme-color" content="#fff5f8">
    <!-- Define el título de la pestaña del navegador. -->
    <title>Catálogo de productos | El Puerquito Digital</title>
    <!-- Carga Bootstrap 5.3.3 para la cuadrícula, tabla y controles responsivos. -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Reutiliza la estructura de navegación del módulo de empleados. -->
    <link href="assets/css/empleados.css" rel="stylesheet">
    <!-- Reutiliza la paleta cálida común de la aplicación. -->
    <link href="assets/css/paleta-rosa.css" rel="stylesheet">
    <!-- Carga el filtro en tiempo real desde un archivo JavaScript externo. -->
    <script src="assets/js/catalogo-ventas.js?v=3" defer></script>
    <!-- Carga el comportamiento compartido de las categorías desplegables. -->
    <script src="assets/js/navegacion.js?v=menu-persistente-2" defer></script>
<!-- Cierra la cabecera de recursos. -->
</head>
<!-- Inicia el documento con el estilo general de las vistas de empleados. -->
<body class="pagina-empleados">
    <!-- Organiza la navegación lateral y el área principal. -->
    <div class="contenedor-empleados d-flex min-vh-100">
        <!-- Reutiliza la barra lateral común del sistema. -->
        <aside class="barra-empleados d-flex flex-column p-3" aria-label="Navegación del sistema">
            <!-- Presenta la identidad de marca y enlaza al inicio. -->
            <a class="marca-empleados d-flex align-items-center gap-3 text-decoration-none" href="index.php" aria-label="El Güero, inicio">
                <!-- Muestra el logotipo oficial. -->
                <img class="logo-sistema" src="assets/logoPIG.png" alt="Logotipo de El Güero">
                <!-- Agrupa el nombre comercial y descriptor. -->
                <span class="d-flex flex-column">
                    <!-- Muestra el nombre comercial de la tocinería. -->
                    <span class="fw-bold">El Güero</span>
                    <!-- Muestra el descriptor del sistema. -->
                    <span class="small text-secondary">Tocinería · Sistema</span>
                <!-- Cierra el bloque textual de marca. -->
                </span>
            <!-- Cierra el enlace de marca. -->
            </a>
            <!-- Proporciona el enlace al inicio de la aplicación. -->
            <a class="enlace-inicio-navegacion" href="index.php" aria-label="Ir a la pantalla principal">⌂ <span>Inicio</span></a>
            <!-- Agrupa los accesos disponibles para el rol autenticado. -->
            <?php require dirname(__DIR__) . '/partials/menu_lateral.php'; ?>
            <!-- Presenta el usuario activo y su rol en el pie del menú. -->
            <div class="sesion-empleado d-flex align-items-center gap-2 mt-auto pt-3 border-top">
                <!-- Agrupa el nombre de usuario y el rol. -->
                <span class="d-flex flex-column overflow-hidden">
                    <!-- Escapa el nombre de usuario antes de mostrarlo. -->
                    <span class="small fw-semibold text-truncate"><?= $escaparCatalogo((string) ($sesionCatalogoVista['usuario'] ?? 'Usuario')) ?></span>
                    <!-- Escapa el rol antes de mostrarlo. -->
                    <span class="small text-secondary"><?= $escaparCatalogo($rolCatalogoVista) ?></span>
                <!-- Cierra la identidad del usuario. -->
                </span>
                <!-- Conserva el enlace común para cerrar la sesión. -->
                <a class="btn btn-sm btn-outline-secondary enlace-cerrar-sesion" href="index.php?c=auth&amp;a=logout">Cerrar sesión</a>
            <!-- Cierra el pie de sesión. -->
            </div>
        <!-- Cierra la barra lateral. -->
        </aside>
        <!-- Reserva el área principal con el espaciado del layout existente. -->
        <main class="contenido-empleados flex-grow-1 p-3 p-md-4 p-xl-5">
            <!-- Presenta la ruta, el título y la explicación de la consulta. -->
            <header class="encabezado-pagina mb-4">
                <!-- Muestra la ubicación dentro del módulo de inventario. -->
                <p class="miga-pan text-secondary small mb-2">Módulo de inventario / Catálogo de productos</p>
                <!-- Nombra la vista de disponibilidad. -->
                <h1 class="h2 fw-bold mb-2">Catálogo de productos</h1>
                <!-- Explica la información disponible para la venta. -->
                <p class="text-secondary mb-0">Consulta los cortes, precios y existencias actuales antes de registrar una venta.</p>
            <!-- Cierra el encabezado. -->
            </header>
            <!-- Presenta el resumen general del stock antes del catálogo. -->
            <section class="row g-3 mb-4" aria-label="Resumen de existencias">
                <!-- Resume el número total de productos consultables. -->
                <div class="col-12 col-md-4">
                    <!-- Reutiliza la tarjeta clara del sistema para el total general. -->
                    <article class="tarjeta-lista card border-0 h-100">
                        <!-- Alinea el indicador y su texto explicativo. -->
                        <div class="card-body d-flex align-items-center gap-3 p-3">
                            <!-- Marca visualmente el total del catálogo. -->
                            <span class="badge rounded-3 text-bg-light p-3" aria-hidden="true">▦</span>
                            <!-- Presenta la etiqueta y la cantidad total. -->
                            <div><p class="small text-secondary mb-1">Total de productos</p><p class="h5 fw-bold mb-0"><?= count($catalogoVista) ?> artículos</p></div>
                        <!-- Cierra el contenido de la tarjeta total. -->
                        </div>
                    <!-- Cierra la tarjeta total. -->
                    </article>
                <!-- Cierra la columna del total. -->
                </div>
                <!-- Resume los productos que tienen stock positivo. -->
                <div class="col-12 col-md-4">
                    <!-- Reutiliza la tarjeta clara del sistema para disponibilidad. -->
                    <article class="tarjeta-lista card border-0 h-100">
                        <!-- Alinea el indicador y su texto explicativo. -->
                        <div class="card-body d-flex align-items-center gap-3 p-3">
                            <!-- Marca visualmente los productos disponibles. -->
                            <span class="badge rounded-3 text-bg-success p-3" aria-hidden="true">●</span>
                            <!-- Presenta la etiqueta y la cantidad disponible. -->
                            <div><p class="small text-secondary mb-1">En stock</p><p class="h5 fw-bold text-success mb-0"><?= $productosEnStock ?> disponibles</p></div>
                        <!-- Cierra el contenido de la tarjeta de stock. -->
                        </div>
                    <!-- Cierra la tarjeta de stock. -->
                    </article>
                <!-- Cierra la columna de stock. -->
                </div>
                <!-- Resume productos en bajo stock o agotados con un umbral anunciado. -->
                <div class="col-12 col-md-4">
                    <!-- Reutiliza la tarjeta clara del sistema para alertas. -->
                    <article class="tarjeta-lista card border-0 h-100">
                        <!-- Alinea el indicador y su texto explicativo. -->
                        <div class="card-body d-flex align-items-center gap-3 p-3">
                            <!-- Marca visualmente las existencias que requieren revisión. -->
                            <span class="badge rounded-3 text-bg-warning p-3" aria-hidden="true">!</span>
                            <!-- Presenta la etiqueta y el número de alertas de stock. -->
                            <div><p class="small text-secondary mb-1">Bajo stock / agotado</p><p class="h5 fw-bold text-warning-emphasis mb-0"><?= $productosConAlerta ?> alertas</p><span class="small text-secondary">5 kg o menos</span></div>
                        <!-- Cierra el contenido de la tarjeta de alertas. -->
                        </div>
                    <!-- Cierra la tarjeta de alertas. -->
                    </article>
                <!-- Cierra la columna de alertas. -->
                </div>
            <!-- Cierra el resumen de existencias. -->
            </section>
            <!-- Presenta el buscador y el catálogo en una tarjeta de lista existente. -->
            <section class="tarjeta-lista card border-0" aria-labelledby="titulo-tabla-catalogo">
                <!-- Aplica el espaciado interior consistente con las tarjetas del sistema. -->
                <div class="card-body p-3 p-md-4">
                    <!-- Distribuye el título, el conteo y el buscador en una fila adaptable. -->
                    <div class="d-flex flex-column flex-xl-row align-items-xl-center justify-content-between gap-3 mb-4">
                        <!-- Agrupa el título y el total inicial de productos. -->
                        <div>
                            <!-- Identifica la tabla de disponibilidad. -->
                            <h2 class="h5 fw-bold mb-1" id="titulo-tabla-catalogo">Disponibilidad actual</h2>
                            <!-- Actualiza el conteo visible al filtrar. -->
                            <span class="badge etiqueta-total rounded-pill" data-conteo-catalogo aria-live="polite"><?= count($catalogoVista) ?> productos</span>
                        <!-- Cierra el resumen del listado. -->
                        </div>
                            <!-- Agrupa el buscador automático con la acción para limpiar filtros. -->
                        <div class="busqueda-empleados input-group" role="search">
                            <!-- Proporciona una etiqueta accesible al buscador. -->
                            <label class="visually-hidden" for="buscar-catalogo-ventas">Buscar producto</label>
                            <!-- Captura el término que filtra las filas en el navegador. -->
                            <input class="form-control" id="buscar-catalogo-ventas" type="search" placeholder="Buscar producto por nombre o tipo de corte" autocomplete="off" data-buscador-catalogo-ventas aria-controls="tabla-catalogo-ventas">
                            <!-- Limpia el término y restablece todos los productos y categorías. -->
                            <button class="btn btn-outline-secondary" type="button" data-limpiar-filtro-catalogo>Limpiar</button>
                        <!-- Cierra el grupo de búsqueda. -->
                        </div>
                        <!-- Ofrece filtros rápidos derivados de los tipos de corte existentes. -->
                        <div class="d-flex flex-wrap align-items-center gap-2 mt-3" aria-label="Filtrar por tipo de corte">
                            <!-- Identifica la función de los botones de filtro. -->
                            <span class="small text-secondary me-1">Tipo de corte:</span>
                            <!-- Muestra el catálogo completo al iniciar o restablecer los filtros. -->
                            <button class="btn btn-sm btn-dark rounded-pill" type="button" data-categoria-catalogo="todos" aria-pressed="true">Todos</button>
                            <!-- Genera un filtro por cada tipo de corte almacenado en los resultados. -->
                            <?php foreach ($tiposCorteCatalogo as $tipoCorteFiltro): ?>
                                <!-- Conserva el valor real del tipo para filtrar sin consultar otras columnas. -->
                                <button class="btn btn-sm btn-light rounded-pill" type="button" data-categoria-catalogo="<?= $escaparCatalogo($tipoCorteFiltro) ?>" aria-pressed="false"><?= $escaparCatalogo($tipoCorteFiltro) ?></button>
                            <!-- Finaliza los botones de tipos de corte. -->
                            <?php endforeach; ?>
                        <!-- Cierra los filtros por tipo de corte. -->
                        </div>
                    <!-- Cierra la fila de título y búsqueda. -->
                    </div>
                    <!-- Informa cuando no existen productos activos con inventario relacionado. -->
                    <?php if ($catalogoVista === []): ?>
                        <!-- Explica que el catálogo no tiene filas disponibles para consultar. -->
                        <div class="alert alert-light border mb-0" role="status">No hay productos activos con existencias registradas.</div>
                    <!-- Presenta la tabla cuando existen productos en el catálogo. -->
                    <?php else: ?>
                        <!-- Permite desplazar la tabla horizontalmente en pantallas pequeñas. -->
                        <div class="table-responsive">
                            <!-- Presenta los campos requeridos de disponibilidad con filas filtrables. -->
                            <table class="table tabla-empleados align-middle mb-0" id="tabla-catalogo-ventas">
                                <!-- Nombra cada columna de información. -->
                                <thead>
                                    <!-- Agrupa los encabezados semánticos de la tabla. -->
                                    <tr>
                                        <!-- Encabeza el nombre de producto. -->
                                        <th scope="col">Producto</th>
                                        <!-- Encabeza el tipo de corte. -->
                                        <th scope="col">Tipo de corte</th>
                                        <!-- Encabeza el precio por kilogramo. -->
                                        <th scope="col">Precio por kg</th>
                                        <!-- Encabeza la cantidad física disponible. -->
                                        <th scope="col">Existencia</th>
                                        <!-- Encabeza el estado de disponibilidad. -->
                                        <th scope="col">Disponibilidad</th>
                                    <!-- Cierra la fila de encabezados. -->
                                    </tr>
                                <!-- Cierra la cabecera de la tabla. -->
                                </thead>
                                <!-- Agrupa las filas que el buscador filtrará. -->
                                <tbody>
                                    <!-- Recorre cada producto unido a su existencia actual. -->
                                    <?php foreach ($catalogoVista as $productoCatalogo): ?>
                                        <!-- Renderiza una fila que JavaScript puede filtrar por su texto. -->
                                        <tr data-fila-catalogo-ventas data-tipo-corte="<?= $escaparCatalogo(trim((string) (($productoCatalogo['tipo_corte'] ?? '') !== '' ? $productoCatalogo['tipo_corte'] : 'Sin especificar'))) ?>">
                                            <!-- Muestra el nombre escapado del producto. -->
                                            <th scope="row"><?= $escaparCatalogo((string) ($productoCatalogo['nombre_producto'] ?? '')) ?></th>
                                            <!-- Muestra el corte o una marca cuando no se especificó. -->
                                            <td><?= $escaparCatalogo((string) (($productoCatalogo['tipo_corte'] ?? '') !== '' ? $productoCatalogo['tipo_corte'] : 'Sin especificar')) ?></td>
                                            <!-- Formatea el precio en moneda mexicana por kilogramo. -->
                                            <td>$<?= number_format((float) ($productoCatalogo['precio_kg'] ?? 0), 2, '.', ',') ?></td>
                                            <!-- Presenta la cantidad de stock registrada en kilogramos. -->
                                            <td><?= number_format((float) ($productoCatalogo['cantidad_kg'] ?? 0), 3, '.', ',') ?> kg</td>
                                            <!-- Distingue visualmente las existencias positivas de las agotadas. -->
                                            <td>
                                                <!-- Muestra una etiqueta acorde a la cantidad disponible. -->
                                                <?php if ((float) ($productoCatalogo['cantidad_kg'] ?? 0) > 5): ?>
                                                    <!-- Señala que el producto tiene stock para venta. -->
                                                    <span class="badge rounded-pill text-bg-success">Disponible</span>
                                                <?php elseif ((float) ($productoCatalogo['cantidad_kg'] ?? 0) > 0): ?>
                                                    <!-- Advierte que el producto conserva cinco kilogramos o menos. -->
                                                    <span class="badge rounded-pill text-bg-warning">Bajo stock</span>
                                                <!-- Señala que el producto no tiene stock actual. -->
                                                <?php else: ?>
                                                    <!-- Indica que no quedan kilogramos disponibles. -->
                                                    <span class="badge rounded-pill text-bg-secondary">Sin existencias</span>
                                                <!-- Finaliza la condición de stock positivo. -->
                                                <?php endif; ?>
                                            <!-- Cierra la celda de estado. -->
                                            </td>
                                        <!-- Cierra la fila filtrable. -->
                                        </tr>
                                    <!-- Finaliza el recorrido de productos. -->
                                    <?php endforeach; ?>
                                <!-- Cierra el cuerpo de tabla filtrable. -->
                                </tbody>
                            <!-- Cierra la tabla del catálogo. -->
                            </table>
                        <!-- Cierra el contenedor responsivo. -->
                        </div>
                        <!-- Informa cuando el término escrito no coincide con ninguna fila. -->
                        <p class="alert alert-danger mt-3 mb-0" data-mensaje-sin-resultados role="status" aria-live="polite" hidden>No se encontró ningún producto con ese nombre o tipo de corte.</p>
                    <!-- Finaliza la selección entre catálogo vacío y tabla de productos. -->
                    <?php endif; ?>
                <!-- Cierra el cuerpo de la tarjeta. -->
                </div>
            <!-- Cierra la tarjeta de disponibilidad. -->
            </section>
        <!-- Cierra el área principal. -->
        </main>
    <!-- Cierra la distribución de navegación y contenido. -->
    </div>
<!-- Cierra el cuerpo del documento. -->
</body>
<!-- Cierra el documento HTML. -->
</html>
