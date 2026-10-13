<?php
// Escapa cualquier valor dinámico para prevenir ataques de inyección XSS en el HTML.
$escaparHtml = static fn (string $valor): string => htmlspecialchars($valor, ENT_QUOTES, 'UTF-8'); // Aplica codificación segura en UTF-8.
// Garantiza que los datos del formulario existan como un arreglo estructurado.
$datosVista = isset($datosFormulario) && is_array($datosFormulario) ? $datosFormulario : []; // Evita errores por variables indefinidas.
// Asegura que los datos de la sesión existan para el menú de navegación.
$sesionVista = isset($usuarioSesion) && is_array($usuarioSesion) ? $usuarioSesion : []; // Mantiene la consistencia de sesión.
// Normaliza el mensaje enviado por el controlador o usa una cadena vacía.
$mensajeVista = isset($mensaje) ? (string) $mensaje : ''; // Prepara el texto de la alerta.
// Normaliza el tipo de alerta de Bootstrap permitiendo únicamente variantes seguras.
$tipoAlertaVista = isset($tipoAlerta) && in_array($tipoAlerta, ['success', 'danger', 'warning', 'info'], true) ? $tipoAlerta : 'danger'; // Aplica estilo estándar.
// Define el listado de categorías disponibles para el catálogo comercial.
$categoriasDisponibles = ['Cortes de Cerdo', 'Tocinería', 'Embutidos', 'Ahumados', 'Especialidades']; // Opciones de categoría en la vista.
// Define las unidades de medida disponibles para la venta.
$unidadesDisponibles = ['Kg' => 'Kilogramos (Kg)', 'Pza' => 'Pieza (Pza)']; // Opciones de unidad comercial.
// Recupera el rol autenticado para validar permisos en la barra de navegación.
$rolSesion = (string) ($sesionVista['rol'] ?? ''); // Lee el rol actual.
// Determina la ruta de cancelación según el rol del usuario.
$urlCancelar = $rolSesion === 'ADMINISTRADOR' ? 'index.php?c=empleado&amp;a=lista' : 'index.php'; // Redirige a lista para admin o al inicio.
?>
<!-- Declara el documento HTML5 para la interfaz de nuevo producto. -->
<!DOCTYPE html>
<!-- Configura el idioma español para tecnologías de asistencia y navegadores. -->
<html lang="es">
<!-- Inicia la cabecera del documento con metadatos y hojas de estilo. -->
<head>
    <!-- Define la codificación UTF-8 para caracteres especiales y acentos. -->
    <meta charset="UTF-8">
    <!-- Habilita el diseño responsivo en pantallas móviles y de escritorio. -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Describe el propósito de la página de registro de productos e inventario. -->
    <meta name="description" content="Registro unificado de productos comerciales y existencias de inventario en El Güero.">
    <!-- Establece el color de tema para la interfaz móvil. -->
    <meta name="theme-color" content="#fff5f8">
    <!-- Define el título de la pestaña del navegador. -->
    <title>Registrar Producto | El Güero</title>
    <!-- Carga Bootstrap 5.3.3 desde CDN oficial con comprobación de integridad. -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Carga los estilos generales del módulo de empleados y menú lateral. -->
    <link href="assets/css/empleados.css" rel="stylesheet">
    <!-- Carga los estilos específicos para formularios de productos. -->
    <link href="assets/css/productos.css" rel="stylesheet">
    <link href="assets/css/paleta-rosa.css" rel="stylesheet">
    <!-- Carga el script de validación externa con el atributo defer. -->
    <script src="assets/js/productos.js" defer></script>
<!-- Cierra la cabecera del documento HTML. -->
</head>
<!-- Inicia el cuerpo de la página con la clase base del sistema. -->
<body class="pagina-empleados">
    <!-- Organiza la barra de navegación lateral y el contenido central. -->
    <div class="contenedor-empleados d-flex min-vh-100">
        <!-- Renderiza la barra lateral fija en pantallas grandes. -->
        <aside class="barra-empleados d-none d-lg-flex flex-column p-3" aria-label="Navegación del sistema">
            <!-- Presenta la identidad de marca de la tocinería como enlace. -->
            <a class="marca-empleados d-flex align-items-center gap-3 text-decoration-none" href="index.php" aria-label="El Güero, inicio">
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
            <!-- Agrupa las opciones de navegación según el rol autenticado. -->
            <nav class="mt-4" aria-label="Catálogo e inventarios">
                <!-- Título de sección de catálogo. -->
                <p class="titulo-navegacion small fw-semibold text-uppercase mb-2">Catálogo e inventario</p>
                <!-- Enlace activo a registro de producto. -->
                <a class="enlace-navegacion activo d-flex align-items-center gap-2 rounded-3 px-3 py-2 text-decoration-none" href="index.php?c=producto&amp;a=nuevo" aria-current="page">
                    <!-- Icono representativo de nuevo producto. -->
                    <!-- Texto de la opción de nuevo producto. -->
                    <span>Registrar Producto</span>
                <!-- Cierra el enlace de nuevo producto. -->
                </a>
                <!-- Enlace a la vista de edición y catálogo de productos. -->
                <a class="enlace-navegacion d-flex align-items-center gap-2 rounded-3 px-3 py-2 text-decoration-none" href="index.php?c=producto&amp;a=editar">
                    <!-- Icono representativo de edición. -->
                    <!-- Texto de la opción de edición. -->
                    <span>Editar Producto</span>
                <!-- Cierra el enlace de edición. -->
                </a>
                <!-- Título de sección de cuenta. -->
                <p class="titulo-navegacion small fw-semibold text-uppercase mt-3 mb-2">Seguridad y acceso</p>
                <!-- Accesos administrativos exclusivos para el rol ADMINISTRADOR. -->
                <?php if ($rolSesion === 'ADMINISTRADOR'): ?>
                    <!-- Enlace a la lista de usuarios. -->
                    <a class="enlace-navegacion d-flex align-items-center gap-2 rounded-3 px-3 py-2 text-decoration-none" href="index.php?c=empleado&amp;a=lista">
                        <!-- Icono de lista. -->
                        <!-- Nombre del enlace a lista. -->
                        <span>Lista de Usuarios</span>
                    <!-- Cierra el enlace a lista. -->
                    </a>
                    <!-- Enlace a registro de nuevo usuario. -->
                    <a class="enlace-navegacion d-flex align-items-center gap-2 rounded-3 px-3 py-2 text-decoration-none" href="index.php?c=empleado&amp;a=registro">
                        <!-- Icono de agregar usuario. -->
                        <!-- Nombre del enlace de alta. -->
                        <span>Agregar Usuario</span>
                    <!-- Cierra el enlace de registro de usuario. -->
                    </a>
                <!-- Finaliza la condición de administrador. -->
                <?php endif; ?>
                <!-- Enlace al perfil de usuario disponible para cualquier rol. -->
                <a class="enlace-navegacion d-flex align-items-center gap-2 rounded-3 px-3 py-2 text-decoration-none" href="index.php?c=empleado&amp;a=perfil">
                    <!-- Icono de usuario. -->
                    <!-- Nombre del enlace a perfil. -->
                    <span>Mi Perfil</span>
                <!-- Cierra el enlace al perfil. -->
                </a>
            <!-- Cierra la navegación lateral. -->
            </nav>
            <!-- Pie de barra lateral con información de la sesión activa. -->
            <div class="sesion-empleado d-flex align-items-center justify-content-between mt-auto pt-3 border-top">
                <!-- Agrupa el avatar y nombre de sesión. -->
                <div class="d-flex align-items-center gap-2 overflow-hidden">
                    <!-- Avatar con iniciales o rol. -->
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
                <!-- Botón de salida para cerrar la sesión. -->
                <a class="btn btn-sm btn-outline-secondary flex-shrink-0 ms-2" href="index.php?c=auth&amp;a=logout">Cerrar sesión</a>
            <!-- Cierra el pie del menú lateral. -->
            </div>
        <!-- Cierra la barra lateral. -->
        </aside>
        <!-- Inicia el contenido principal de la pantalla de nuevo producto. -->
        <main class="contenido-empleados flex-grow-1 p-3 p-md-4 p-xl-5">
            <!-- Encabezado de página con miga de pan y título descriptivo. -->
            <header class="encabezado-productos mb-4">
                <!-- Miga de pan de navegación. -->
                <p class="miga-pan text-secondary small mb-2">Catálogo e inventario / Registrar producto</p>
                <!-- Título principal de la pantalla. -->
                <h1 class="h2 fw-bold mb-2">Registrar Producto</h1>
                <!-- Subtítulo explicativo del proceso unificado. -->
                <p class="text-secondary mb-0">Captura la información comercial del producto y su existencia física inicial.</p>
            <!-- Cierra el encabezado de página. -->
            </header>
            <!-- Renderiza la alerta de notificación cuando existe un mensaje del controlador. -->
            <?php if ($mensajeVista !== ''): ?>
                <!-- Contenedor de alerta de Bootstrap 5.3. -->
                <div class="alert alert-<?= $escaparHtml($tipoAlertaVista) ?> d-flex align-items-center gap-2 mb-4" role="alert">
                    <!-- Icono indicativo según el tipo de alerta. -->
                    <span class="fw-bold"><?= $tipoAlertaVista === 'success' ? 'Correcto:' : 'Aviso:' ?></span>
                    <!-- Mensaje emitido por el controlador de productos. -->
                    <span><?= $escaparHtml($mensajeVista) ?></span>
                <!-- Cierra la alerta. -->
                </div>
            <!-- Finaliza la condición de alerta visible. -->
            <?php endif; ?>
            <!-- Tarjeta central que contiene el formulario único unificado. -->
            <section class="tarjeta-producto card border-0 mb-4" aria-labelledby="titulo-formulario-producto">
                <!-- Relleno interior de la tarjeta de formulario. -->
                <div class="card-body p-3 p-md-4 p-xl-5">
                    <!-- Inicia el formulario único con envío hacia el controlador por método POST. -->
                    <form action="index.php?c=producto&amp;a=nuevo" method="post" data-formulario-producto novalidate>
                        <!-- Encabezado de la sección comercial del formulario. -->
                        <div class="mb-4 pb-2 border-bottom">
                            <!-- Título de sección comercial. -->
                            <h2 class="h5 fw-bold mb-1" id="titulo-formulario-producto">Información Comercial</h2>
                            <!-- Aviso sobre campos obligatorios con asterisco rojo. -->
                            <p class="small text-secondary mb-0">Los campos marcados con <span class="text-danger fw-bold">(*)</span> son obligatorios.</p>
                        <!-- Cierra la cabecera de la sección. -->
                        </div>
                        <!-- Cuadrícula responsiva de campos de producto. -->
                        <div class="row g-3 mb-4">
                            <!-- Campo obligatorio de Nombre del Producto. -->
                            <div class="col-12 col-md-6">
                                <!-- Etiqueta del nombre del producto con asterisco. -->
                                <label class="form-label fw-semibold" for="nombre-producto">Nombre del Producto <span class="text-danger">(*)</span></label>
                                <!-- Control de texto para el nombre comercial. -->
                                <input class="form-control" id="nombre-producto" name="nombre_producto" type="text" maxlength="100" placeholder="Ej. Tocino Ahumado Rebanado" value="<?= $escaparHtml((string) ($datosVista['nombre_producto'] ?? '')) ?>" required aria-describedby="error-nombre-producto">
                                <!-- Mensaje de validación nativo si falta el nombre. -->
                                <div class="invalid-feedback" id="error-nombre-producto">Ingresa un nombre de hasta 100 caracteres.</div>
                            <!-- Cierra la columna del nombre. -->
                            </div>
                            <!-- Campo obligatorio de Categoría con menú desplegable. -->
                            <div class="col-12 col-md-6">
                                <!-- Etiqueta de la categoría con asterisco. -->
                                <label class="form-label fw-semibold" for="categoria">Categoría <span class="text-danger">(*)</span></label>
                                <!-- Selector desplegable de categorías comerciales. -->
                                <select class="form-select" id="categoria" name="categoria" required aria-describedby="error-categoria">
                                    <!-- Opción inicial vacía. -->
                                    <option value="">Selecciona una categoría</option>
                                    <!-- Recorre las categorías comerciales disponibles. -->
                                    <?php foreach ($categoriasDisponibles as $categoria): ?>
                                        <!-- Genera la opción y conserva la selección previa ante errores. -->
                                        <option value="<?= $escaparHtml($categoria) ?>" <?= (($datosVista['categoria'] ?? '') === $categoria) ? 'selected' : '' ?>><?= $escaparHtml($categoria) ?></option>
                                    <!-- Finaliza el recorrido de categorías. -->
                                    <?php endforeach; ?>
                                <!-- Cierra el selector de categoría. -->
                                </select>
                                <!-- Mensaje de validación si falta la categoría. -->
                                <div class="invalid-feedback" id="error-categoria">Selecciona una categoría.</div>
                            <!-- Cierra la columna de categoría. -->
                            </div>
                            <!-- Campo obligatorio de Unidad de Medida. -->
                            <div class="col-12 col-md-4">
                                <!-- Etiqueta de la unidad de medida con asterisco. -->
                                <label class="form-label fw-semibold" for="unidad">Unidad <span class="text-danger">(*)</span></label>
                                <!-- Selector desplegable de unidades de medida. -->
                                <select class="form-select" id="unidad" name="unidad" required aria-describedby="error-unidad">
                                    <!-- Recorre las unidades admitidas. -->
                                    <?php foreach ($unidadesDisponibles as $claveUnidad => $etiquetaUnidad): ?>
                                        <!-- Genera la opción de unidad y preselecciona Kilogramos. -->
                                        <option value="<?= $escaparHtml($claveUnidad) ?>" <?= (($datosVista['unidad'] ?? 'Kg') === $claveUnidad) ? 'selected' : '' ?>><?= $escaparHtml($etiquetaUnidad) ?></option>
                                    <!-- Finaliza el recorrido de unidades. -->
                                    <?php endforeach; ?>
                                <!-- Cierra el selector de unidad. -->
                                </select>
                                <!-- Mensaje de validación si falta la unidad. -->
                                <div class="invalid-feedback" id="error-unidad">Selecciona la unidad de medida.</div>
                            <!-- Cierra la columna de unidad. -->
                            </div>
                            <!-- Campo opcional de Tipo de Corte. -->
                            <div class="col-12 col-md-4">
                                <!-- Etiqueta del tipo de corte opcional. -->
                                <label class="form-label" for="tipo-corte">Tipo de Corte <span class="text-secondary small">(Opcional)</span></label>
                                <!-- Control de texto para el corte o especificación cárnica. -->
                                <input class="form-control" id="tipo-corte" name="tipo_corte" type="text" maxlength="50" placeholder="Ej. Rebanado fino, Grueso, Tira" value="<?= $escaparHtml((string) ($datosVista['tipo_corte'] ?? '')) ?>">
                                <div class="invalid-feedback">El tipo de corte admite hasta 50 caracteres.</div>
                            <!-- Cierra la columna de tipo de corte. -->
                            </div>
                            <!-- Campo obligatorio de Precio por Kilogramo con prefijo monetario. -->
                            <div class="col-12 col-md-4">
                                <!-- Etiqueta del precio con asterisco. -->
                                <label class="form-label fw-semibold" for="precio-kg">Precio por Kilogramo <span class="text-danger">(*)</span></label>
                                <!-- Grupo de entrada estilizado con signo de pesos usando Bootstrap. -->
                                <div class="input-group">
                                    <!-- Símbolo de pesos como prefijo visible. -->
                                    <span class="input-group-text fw-bold">$</span>
                                    <!-- Control numérico de precio con dos decimales permitidos. -->
                                    <input class="form-control" id="precio-kg" name="precio_kg" type="number" step="0.01" min="0.01" placeholder="0.00" value="<?= $escaparHtml((string) ($datosVista['precio_kg'] ?? '')) ?>" required aria-describedby="error-precio-kg">
                                    <!-- Mensaje de validación si falta el precio. -->
                                    <div class="invalid-feedback" id="error-precio-kg">Ingresa un precio positivo con hasta 2 decimales.</div>
                                <!-- Cierra el grupo de entrada de precio. -->
                                </div>
                            <!-- Cierra la columna de precio. -->
                            </div>
                        <!-- Cierra la cuadrícula de información comercial. -->
                        </div>
                        <!-- Sección Especial de Almacén e Inventario Inicial. -->
                        <div class="seccion-almacen mb-4">
                            <!-- Encabezado de la sección especial con insignia de almacén. -->
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <!-- Agrupa el título y la descripción de inventario inicial. -->
                                <div>
                                    <!-- Título de la sección de existencias. -->
                                    <h3 class="h6 fw-bold mb-1">Sección Especial de Almacén</h3>
                                    <!-- Nota sobre la persistencia transaccional inmediata en inventario. -->
                                    <p class="small text-secondary mb-0">Esta cantidad se registrará de forma inmediata en la tabla de inventario mediante relación 1:1.</p>
                                <!-- Cierra los títulos de almacén. -->
                                </div>
                                <!-- Insignia visual destacada de almacén. -->
                                <span class="badge insignia-almacen rounded-pill">Inventario 1:1</span>
                            <!-- Cierra la cabecera de almacén. -->
                            </div>
                            <!-- Fila para el control numérico de existencia inicial. -->
                            <div class="row g-3">
                                <!-- Aloja el campo de existencia inicial en media columna o columna completa. -->
                                <div class="col-12 col-md-6">
                                    <!-- Etiqueta del campo de existencia inicial con asterisco. -->
                                    <label class="form-label fw-semibold" for="existencia-inicial">Existencia Inicial <span class="text-danger">(*)</span></label>
                                    <!-- Grupo de entrada estilizado con sufijo de unidad. -->
                                    <div class="input-group">
                                        <!-- Control numérico para la cantidad física inicial. -->
                                        <input class="form-control" id="existencia-inicial" name="existencia_inicial" type="number" step="0.001" min="0" placeholder="0.000" value="<?= $escaparHtml((string) ($datosVista['existencia_inicial'] ?? '')) ?>" required aria-describedby="error-existencia-inicial">
                                        <!-- Sufijo visual de kilogramos. -->
                                        <span class="input-group-text">Kg</span>
                                        <!-- Mensaje de validación si falta la existencia. -->
                                        <div class="invalid-feedback" id="error-existencia-inicial">Ingresa una cantidad no negativa con hasta 3 decimales.</div>
                                    <!-- Cierra el grupo de existencia. -->
                                    </div>
                                <!-- Cierra la columna de existencia inicial. -->
                                </div>
                            <!-- Cierra la fila de almacén. -->
                            </div>
                        <!-- Cierra la sección especial de almacén. -->
                        </div>
                        <!-- Fila inferior de botones de acción para Guardar y Cancelar. -->
                        <div class="d-flex flex-column flex-sm-row justify-content-end gap-2 pt-2 border-top">
                            <!-- Botón para cancelar y regresar a la vista anterior. -->
                            <a class="btn btn-outline-secondary px-4" href="<?= $escaparHtml($urlCancelar) ?>">Cancelar</a>
                            <!-- Botón principal para enviar el formulario y guardar el producto. -->
                            <button class="btn btn-primary px-4" type="submit">Guardar Producto</button>
                        <!-- Cierra los botones de acción. -->
                        </div>
                    <!-- Cierra el formulario de registro de producto. -->
                    </form>
                <!-- Cierra el cuerpo de la tarjeta. -->
                </div>
            <!-- Cierra la sección de tarjeta. -->
            </section>
        <!-- Cierra el contenido principal. -->
        </main>
    <!-- Cierra la distribución principal. -->
    </div>
<!-- Cierra el cuerpo de la página. -->
</body>
<!-- Finaliza el documento HTML5. -->
</html>
