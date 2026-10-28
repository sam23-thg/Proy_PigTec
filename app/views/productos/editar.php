<?php
// Escapa cualquier valor dinámico para prevenir ataques de inyección XSS en el HTML.
$escaparHtml = static fn (string $valor): string => htmlspecialchars($valor, ENT_QUOTES, 'UTF-8'); // Aplica codificación segura UTF-8.
// Garantiza que el catálogo de productos exista como arreglo estructurado.
$catalogoVista = isset($catalogoProductos) && is_array($catalogoProductos) ? $catalogoProductos : []; // Evita errores por variable indefinida.
// Garantiza que los datos del producto seleccionado existan o usa null.
$productoVista = isset($productoSeleccionado) && is_array($productoSeleccionado) ? $productoSeleccionado : null; // Mantiene null si no hay producto activo.
// Asegura que los datos de sesión existan para el menú de navegación.
$sesionVista = isset($usuarioSesion) && is_array($usuarioSesion) ? $usuarioSesion : []; // Mantiene la consistencia de sesión.
// Normaliza el mensaje del controlador o usa cadena vacía.
$mensajeVista = isset($mensaje) ? (string) $mensaje : ''; // Prepara el texto de la alerta.
// Normaliza el tipo de alerta de Bootstrap validando únicamente variantes seguras.
$tipoAlertaVista = isset($tipoAlerta) && $tipoAlerta === 'success' ? 'success' : 'danger'; // Reserva verde para cambios guardados y rojo para errores.
// Recupera el rol autenticado para controlar la navegación según el rol.
$rolSesion = (string) ($sesionVista['rol'] ?? ''); // Lee el rol de la sesión activa.
// Determina si el producto seleccionado tiene estado activo para preseleccionar el interruptor.
$estadoActivo = $productoVista !== null ? (bool) ($productoVista['estado'] ?? true) : true; // Convierte el valor a booleano para el toggle.
?>
<!-- Declara el documento HTML5 para la interfaz de edición de productos. -->
<!DOCTYPE html>
<!-- Configura el idioma español para tecnologías de asistencia y navegadores. -->
<html lang="es">
<!-- Inicia la cabecera del documento con metadatos y hojas de estilo. -->
<head>
    <!-- Define la codificación UTF-8 para caracteres especiales y acentos. -->
    <meta charset="UTF-8">
    <!-- Habilita el diseño responsivo en pantallas móviles y de escritorio. -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Describe el propósito de la página de edición de productos e inventario. -->
    <meta name="description" content="Busca y edita productos del catálogo con sus existencias de inventario en El Güero.">
    <!-- Establece el color de tema para la interfaz móvil. -->
    <meta name="theme-color" content="#fff5f8">
    <!-- Define el título de la pestaña del navegador. -->
    <title>Editar Producto | El Güero</title>
    <!-- Carga Bootstrap 5.3.3 desde CDN oficial con comprobación de integridad. -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Carga los estilos generales del módulo de empleados y menú lateral. -->
    <link href="assets/css/empleados.css" rel="stylesheet">
    <!-- Carga los estilos específicos del módulo de productos e inventarios. -->
    <link href="assets/css/productos.css" rel="stylesheet">
    <link href="assets/css/paleta-rosa.css" rel="stylesheet">
    <!-- Carga el script externo de validación y buscador con atributo defer. -->
    <script src="assets/js/productos.js" defer></script>
    <script src="assets/js/navegacion.js?v=menu-persistente-2" defer></script>
<!-- Cierra la cabecera del documento HTML. -->
</head>
<!-- Inicia el cuerpo de la página con la clase base del sistema. -->
<body class="pagina-empleados">
    <!-- Organiza la barra de navegación lateral y el contenido central. -->
    <div class="contenedor-empleados d-flex min-vh-100">
        <!-- Renderiza la barra lateral fija en pantallas grandes. -->
        <aside class="barra-empleados d-flex flex-column p-3" aria-label="Navegación del sistema">
            <!-- Presenta la identidad de marca de la tocinería como enlace. -->
            <a class="marca-empleados d-flex align-items-center gap-3 text-decoration-none" href="index.php" aria-label="El Güero, inicio">
                <!-- Usa el logotipo oficial en el encabezado uniforme del menú. -->
                <img class="logo-sistema" src="assets/logoPIG.png" alt="Logotipo de El Güero">
                <!-- Distintivo circular de marca. -->
                <!-- Agrupa el nombre comercial y descriptor. -->
                <span class="d-flex flex-column">
                    <!-- Nombre comercial. -->
                    <span class="fw-bold">El Güero</span>
                    <!-- Descriptor de la aplicación. -->
                    <span class="small text-secondary">Tocinería · Sistema</span>
                <!-- Cierra los textos de marca. -->
                </span>
            <!-- Cierra el enlace de marca. -->
            </a>
            <a class="enlace-inicio-navegacion" href="index.php" aria-label="Ir a la pantalla principal">⌂ <span>Inicio</span></a>
            <!-- Agrupa las opciones de navegación según el rol autenticado. -->
            <?php require dirname(__DIR__) . '/partials/menu_lateral.php'; ?>
            <!-- Pie de barra lateral con información de la sesión activa. -->
            <div class="sesion-empleado d-flex align-items-center justify-content-between mt-auto pt-3 border-top">
                <!-- Agrupa el avatar y nombre de sesión. -->
                <div class="d-flex align-items-center gap-2 overflow-hidden">
                    <!-- Avatar con iniciales del usuario activo. -->
                    <!-- Muestra el usuario y su rol. -->
                    <span class="d-flex flex-column overflow-hidden">
                        <!-- Nombre de usuario. -->
                        <span class="small fw-semibold text-truncate"><?= $escaparHtml((string) ($sesionVista['usuario'] ?? 'Usuario')) ?></span>
                        <!-- Rol activo en la sesión. -->
                        <span class="small text-secondary"><?= $escaparHtml($rolSesion) ?></span>
                    <!-- Cierra los textos. -->
                    </span>
                <!-- Cierra la identidad de sesión. -->
                </div>
                <!-- Botón de salida para cerrar la sesión activa de forma segura. -->
                <a class="btn btn-sm btn-outline-secondary flex-shrink-0 ms-2" href="index.php?c=auth&amp;a=logout">Cerrar sesión</a>
            <!-- Cierra el pie del menú lateral. -->
            </div>
        <!-- Cierra la barra lateral. -->
        </aside>
        <!-- Inicia el contenido principal de la pantalla de edición de productos. -->
        <main class="contenido-empleados flex-grow-1 p-3 p-md-4 p-xl-5">
            <!-- Encabezado de página con miga de pan y título descriptivo. -->
            <header class="encabezado-productos mb-4">
                <!-- Miga de pan de navegación. -->
                <p class="miga-pan text-secondary small mb-2">Catálogo e inventario / Editar producto</p>
                <!-- Título principal de la pantalla. -->
                <h1 class="h2 fw-bold mb-2">Editar Producto</h1>
                <!-- Subtítulo explicativo de la función de búsqueda y edición. -->
                <p class="text-secondary mb-0">Busca un producto del catálogo para actualizar sus datos comerciales y sus existencias de inventario.</p>
            <!-- Cierra el encabezado de página. -->
            </header>
            <!-- Renderiza la alerta de notificación cuando existe un mensaje del controlador. -->
            <?php if ($mensajeVista !== ''): ?>
                <!-- Contenedor de alerta de Bootstrap 5.3. -->
                <div class="alert alert-<?= $escaparHtml($tipoAlertaVista) ?> d-flex align-items-center gap-2 mb-4" role="alert">
                    <!-- Identifica con palabras el resultado comunicado por la alerta. -->
                    <span class="fw-bold"><?= $tipoAlertaVista === 'success' ? 'Correcto:' : 'Aviso:' ?></span>
                    <!-- Mensaje emitido por el controlador de productos. -->
                    <span><?= $escaparHtml($mensajeVista) ?></span>
                <!-- Cierra la alerta. -->
                </div>
            <!-- Finaliza la condición de alerta visible. -->
            <?php endif; ?>
            <!-- Distribuye el buscador lateral y el formulario de edición en columnas responsivas. -->
            <div class="row g-4">
                <!-- Columna izquierda: Buscador de catálogo. -->
                <div class="col-12 col-xl-4">
                    <!-- Tarjeta que contiene el buscador y listado del catálogo. -->
                    <section class="tarjeta-producto card border-0 h-100" aria-labelledby="titulo-buscador-catalogo">
                        <!-- Cuerpo de la tarjeta del buscador. -->
                        <div class="card-body p-3 p-md-4">
                            <!-- Título del buscador de catálogo. -->
                            <h2 class="h5 fw-bold mb-1" id="titulo-buscador-catalogo">Catálogo de Productos</h2>
                            <!-- Instrucción para el uso del buscador. -->
                            <p class="small text-secondary mb-3">Escribe para filtrar o haz clic en un producto para editarlo.</p>
                            <!-- Campo de búsqueda del catálogo con atributo de datos para el script externo. -->
                            <div class="input-group mb-3">
                                <!-- Identifica con texto el campo de búsqueda. -->
                                <span class="input-group-text">Buscar</span>
                                <!-- Control de texto del buscador de productos. -->
                                <input class="form-control" id="buscador-catalogo" type="search" placeholder="Buscar producto..." autocomplete="off" data-buscador-catalogo aria-label="Buscar en el catálogo">
                            <!-- Cierra el grupo de entrada del buscador. -->
                            </div>
                            <!-- Contenedor desplazable del listado de productos del catálogo. -->
                            <div class="overflow-auto" style="max-height: 420px;" role="list" aria-label="Listado de productos">
                                <!-- Muestra el mensaje de catálogo vacío si no hay productos registrados. -->
                                <?php if (empty($catalogoVista)): ?>
                                    <!-- Aviso de catálogo vacío cuando no hay productos en la base de datos. -->
                                    <p class="text-secondary small text-center py-4">No hay productos registrados en el catálogo.</p>
                                <!-- Finaliza la comprobación de catálogo vacío. -->
                                <?php else: ?>
                                    <!-- Contenedor de aviso cuando el buscador no encuentra coincidencias. -->
                                    <div data-sin-resultados style="display:none;" class="text-secondary small text-center py-4">No se encontraron productos con ese término.</div>
                                    <!-- Recorre cada producto del catálogo para generar su fila interactiva. -->
                                    <?php foreach ($catalogoVista as $itemCatalogo): ?>
                                        <!-- Fila interactiva del producto con sus datos de búsqueda como atributo. -->
                                        <div class="item-catalogo-buscador px-2 py-2 rounded-2 <?= ($productoVista !== null && (int) ($productoVista['id_producto'] ?? 0) === (int) $itemCatalogo['id_producto']) ? 'seleccionado' : '' ?>" data-item-catalogo data-terminos="<?= $escaparHtml(strtolower((string) $itemCatalogo['nombre_producto'])) ?>" role="listitem">
                                            <!-- Enlace que carga el producto en el formulario de edición mediante GET. -->
                                            <a class="text-decoration-none d-flex flex-column gap-1" href="index.php?c=producto&amp;a=editar&amp;id=<?= (int) $itemCatalogo['id_producto'] ?>">
                                                <!-- Nombre del producto con tipografía compacta. -->
                                                <span class="fw-semibold small"><?= $escaparHtml((string) $itemCatalogo['nombre_producto']) ?></span>
                                                <!-- Información secundaria: precio y existencia en una sola línea. -->
                                                <span class="text-secondary" style="font-size: 0.78rem;">$<?= $escaparHtml(number_format((float) ($itemCatalogo['precio_kg'] ?? 0), 2)) ?>/Kg &nbsp;·&nbsp; <?= $escaparHtml(number_format((float) ($itemCatalogo['cantidad_existencia'] ?? 0), 2)) ?> Kg
                                                    <!-- Insignia de estado del producto en el catálogo. -->
                                                    <span class="badge ms-1 <?= ((bool) ($itemCatalogo['estado'] ?? true)) ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' ?>" style="font-size:0.7rem;"><?= ((bool) ($itemCatalogo['estado'] ?? true)) ? 'Activo' : 'Inactivo' ?></span>
                                                </span>
                                            <!-- Cierra el enlace del ítem. -->
                                            </a>
                                        <!-- Cierra la fila del producto. -->
                                        </div>
                                    <!-- Finaliza el recorrido del catálogo. -->
                                    <?php endforeach; ?>
                                <!-- Finaliza la condición de catálogo no vacío. -->
                                <?php endif; ?>
                            <!-- Cierra el contenedor desplazable del catálogo. -->
                            </div>
                        <!-- Cierra el cuerpo de la tarjeta del buscador. -->
                        </div>
                    <!-- Cierra la sección del buscador. -->
                    </section>
                <!-- Cierra la columna del buscador. -->
                </div>
                <!-- Columna derecha: Formulario integrado de edición. -->
                <div class="col-12 col-xl-8">
                    <!-- Tarjeta que contiene el formulario de edición de datos comerciales e inventario. -->
                    <section class="tarjeta-producto card border-0" aria-labelledby="titulo-formulario-edicion">
                        <!-- Cuerpo de la tarjeta del formulario de edición. -->
                        <div class="card-body p-3 p-md-4 p-xl-5">
                            <!-- Muestra un estado inicial cuando no hay producto seleccionado. -->
                            <?php if ($productoVista === null): ?>
                                <!-- Contenedor de estado vacío de selección. -->
                                <div class="text-center py-5 text-secondary">
                                    <!-- Instrucción de selección de producto. -->
                                    <h2 class="h5 fw-bold mb-2" id="titulo-formulario-edicion">Selecciona un producto</h2>
                                    <!-- Descripción auxiliar para guiar al usuario. -->
                                    <p class="small">Usa el buscador de la izquierda para localizar y seleccionar el producto que deseas editar.</p>
                                <!-- Cierra el estado vacío. -->
                                </div>
                            <!-- Presenta el formulario de edición cuando hay un producto seleccionado. -->
                            <?php else: ?>
                                <!-- Inicia el formulario de edición con envío hacia el controlador por método POST. -->
                                <form action="index.php?c=producto&amp;a=editar" method="post" data-formulario-editar-producto novalidate>
                                    <!-- Campo oculto con el identificador del producto para la actualización. -->
                                    <input type="hidden" name="id_producto" value="<?= (int) ($productoVista['id_producto'] ?? 0) ?>">
                                    <!-- Campo oculto que activa el procesamiento del formulario de guardar cambios. -->
                                    <input type="hidden" name="accion_guardar" value="1">
                                    <!-- Encabezado de la sección de datos comerciales del formulario. -->
                                    <div class="mb-4 pb-2 border-bottom">
                                        <!-- Título del formulario de edición. -->
                                        <h2 class="h5 fw-bold mb-1" id="titulo-formulario-edicion">Editar: <?= $escaparHtml((string) ($productoVista['nombre_producto'] ?? '')) ?></h2>
                                        <!-- Aviso sobre campos obligatorios con asterisco rojo. -->
                                        <p class="small text-secondary mb-0">Los campos marcados con <span class="text-danger fw-bold">(*)</span> son obligatorios.</p>
                                    <!-- Cierra la cabecera del formulario. -->
                                    </div>
                                    <!-- Cuadrícula responsiva de campos comerciales del producto. -->
                                    <div class="row g-3 mb-4">
                                        <!-- Campo obligatorio de Nombre del Producto. -->
                                        <div class="col-12 col-md-6">
                                            <!-- Etiqueta del nombre con asterisco de obligatoriedad. -->
                                            <label class="form-label fw-semibold" for="editar-nombre-producto">Nombre del Producto <span class="text-danger">(*)</span></label>
                                            <!-- Control de texto precargado con el nombre actual del producto. -->
                                            <input class="form-control" id="editar-nombre-producto" name="nombre_producto" type="text" maxlength="100" placeholder="Nombre comercial del producto" value="<?= $escaparHtml((string) ($productoVista['nombre_producto'] ?? '')) ?>" required aria-describedby="error-editar-nombre">
                                            <!-- Mensaje de validación si el nombre está vacío. -->
                                            <div class="invalid-feedback" id="error-editar-nombre">Ingresa un nombre de hasta 100 caracteres.</div>
                                        <!-- Cierra la columna del nombre. -->
                                        </div>
                                        <!-- Campo opcional de Tipo de Corte. -->
                                        <div class="col-12 col-md-6">
                                            <!-- Etiqueta del tipo de corte opcional. -->
                                            <label class="form-label" for="editar-tipo-corte">Tipo de Corte <span class="text-secondary small">(Opcional)</span></label>
                                            <!-- Control de texto precargado con el tipo de corte actual. -->
                                            <input class="form-control" id="editar-tipo-corte" name="tipo_corte" type="text" maxlength="50" placeholder="Ej. Rebanado fino, Grueso, Tira" value="<?= $escaparHtml((string) ($productoVista['tipo_corte'] ?? '')) ?>">
                                            <div class="invalid-feedback">El tipo de corte admite hasta 50 caracteres.</div>
                                        <!-- Cierra la columna del tipo de corte. -->
                                        </div>
                                        <!-- Campo obligatorio de Precio por Kilogramo. -->
                                        <div class="col-12 col-md-6">
                                            <!-- Etiqueta del precio con asterisco de obligatoriedad. -->
                                            <label class="form-label fw-semibold" for="editar-precio-kg">Precio por Kilogramo <span class="text-danger">(*)</span></label>
                                            <!-- Grupo de entrada estilizado con signo de pesos como prefijo. -->
                                            <div class="input-group">
                                                <!-- Símbolo de pesos como prefijo del precio. -->
                                                <span class="input-group-text fw-bold">$</span>
                                                <!-- Control numérico precargado con el precio actual. -->
                                                <input class="form-control" id="editar-precio-kg" name="precio_kg" type="number" step="0.01" min="0.01" placeholder="0.00" value="<?= $escaparHtml(number_format((float) ($productoVista['precio_kg'] ?? 0), 2, '.', '')) ?>" required aria-describedby="error-editar-precio">
                                                <!-- Mensaje de validación si falta el precio. -->
                                                <div class="invalid-feedback" id="error-editar-precio">Ingresa un precio positivo con hasta 2 decimales.</div>
                                            <!-- Cierra el grupo de entrada de precio. -->
                                            </div>
                                        <!-- Cierra la columna de precio. -->
                                        </div>
                                        <!-- Campo obligatorio de Existencia Actual en Inventario. -->
                                        <div class="col-12 col-md-6">
                                            <!-- Etiqueta de existencia con asterisco de obligatoriedad. -->
                                            <label class="form-label fw-semibold" for="editar-cantidad-existencia">Existencia Actual <span class="text-danger">(*)</span></label>
                                            <!-- Grupo de entrada con sufijo de kilogramos. -->
                                            <div class="input-group">
                                                <!-- Control numérico precargado con la existencia actual de inventario. -->
                                                <input class="form-control" id="editar-cantidad-existencia" name="cantidad_existencia" type="number" step="0.001" min="0" placeholder="0.000" value="<?= $escaparHtml(number_format((float) ($productoVista['cantidad_existencia'] ?? 0), 3, '.', '')) ?>" required aria-describedby="error-editar-existencia">
                                                <!-- Sufijo visual de unidad de kilogramos. -->
                                                <span class="input-group-text">Kg</span>
                                                <!-- Mensaje de validación si falta la existencia. -->
                                                <div class="invalid-feedback" id="error-editar-existencia">Ingresa una cantidad no negativa con hasta 3 decimales.</div>
                                            <!-- Cierra el grupo de existencia. -->
                                            </div>
                                        <!-- Cierra la columna de existencia. -->
                                        </div>
                                    <!-- Cierra la cuadrícula de campos comerciales. -->
                                    </div>
                                    <!-- Sección Especial de Estado del Producto con interruptor visual. -->
                                    <div class="seccion-almacen mb-4">
                                        <!-- Encabezado de la sección de estado con insignia. -->
                                        <div class="d-flex align-items-center justify-content-between mb-3">
                                            <!-- Agrupa el título y la descripción del estado del producto. -->
                                            <div>
                                                <!-- Título de la sección de estado. -->
                                                <h3 class="h6 fw-bold mb-1">Estado del Producto <span class="text-danger">(*)</span></h3>
                                                <!-- Nota explicativa sobre el impacto del estado. -->
                                                <p class="small text-secondary mb-0">Habilita o deshabilita la visibilidad del producto en el catálogo comercial.</p>
                                            <!-- Cierra los títulos de estado. -->
                                            </div>
                                            <!-- Insignia visual de interruptor de estado. -->
                                            <span class="badge insignia-almacen rounded-pill">Estado</span>
                                        <!-- Cierra la cabecera de estado. -->
                                        </div>
                                        <!-- Contenedor del interruptor de estado activo/inactivo. -->
                                        <div class="interruptor-estado form-check form-switch d-flex align-items-center gap-3">
                                            <!-- Control tipo switch de Bootstrap para el estado del producto. -->
                                            <input class="form-check-input" id="editar-estado" name="estado" type="checkbox" role="switch" value="1" <?= $estadoActivo ? 'checked' : '' ?> data-switch-estado aria-describedby="etiqueta-estado-producto">
                                            <!-- Etiqueta del interruptor que muestra el estado actual. -->
                                            <label class="form-check-label fw-semibold <?= $estadoActivo ? 'text-success' : 'text-secondary' ?>" for="editar-estado" data-etiqueta-estado id="etiqueta-estado-producto"><?= $estadoActivo ? 'Activo' : 'Inactivo' ?></label>
                                        <!-- Cierra el contenedor del interruptor. -->
                                        </div>
                                    <!-- Cierra la sección de estado del producto. -->
                                    </div>
                                    <!-- Fila inferior de botones de acción para guardar o cancelar. -->
                                    <div class="d-flex flex-column flex-sm-row justify-content-end gap-2 pt-2 border-top">
                                        <!-- Botón para cancelar la edición y regresar al catálogo. -->
                                        <a class="btn btn-outline-secondary px-4" href="index.php?c=producto&amp;a=editar">Volver al Catálogo</a>
                                        <!-- Botón principal para guardar los cambios mediante la transacción coordinada. -->
                                        <button class="btn btn-success px-4" type="submit">Guardar Cambios</button>
                                    <!-- Cierra los botones de acción. -->
                                    </div>
                                <!-- Cierra el formulario de edición de producto. -->
                                </form>
                            <!-- Finaliza la condición de producto seleccionado. -->
                            <?php endif; ?>
                        <!-- Cierra el cuerpo de la tarjeta de edición. -->
                        </div>
                    <!-- Cierra la sección de la tarjeta de edición. -->
                    </section>
                <!-- Cierra la columna del formulario de edición. -->
                </div>
            <!-- Cierra la distribución de columnas. -->
            </div>
        <!-- Cierra el contenido principal. -->
        </main>
    <!-- Cierra la distribución principal. -->
    </div>
<!-- Cierra el cuerpo de la página. -->
</body>
<!-- Finaliza el documento HTML5. -->
</html>
