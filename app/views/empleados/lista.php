<?php
// Garantiza que la vista reciba una colección válida de empleados.
$usuariosVista = isset($usuarios) && is_array($usuarios) ? $usuarios : []; // Evita errores si la vista se incluye sin resultados.
// Limita los datos de la sesión al formato de arreglo antes de mostrar la identificación administrativa.
$sesionVista = isset($usuarioSesion) && is_array($usuarioSesion) ? $usuarioSesion : []; // Mantiene el renderizado seguro ante sesiones incompletas.
// Convierte cualquier texto dinámico en salida HTML segura con codificación UTF-8.
$escaparHtml = static fn (string $valor): string => htmlspecialchars($valor, ENT_QUOTES, 'UTF-8'); // Previene que datos de empleados se interpreten como marcado.
// Define etiquetas comprensibles para los roles existentes en el ENUM de empleados.
$etiquetasRoles = [ // Mantiene los nombres técnicos separados de los nombres visibles.
    'ADMINISTRADOR' => 'Administrador', // Presenta el rol administrador con mayúscula inicial.
    'VENTAS' => 'Ventas', // Presenta el rol de ventas en formato legible.
    'ALMACEN' => 'Almacén', // Presenta el rol de almacén con acento.
    'DISTRIBUCION' => 'Distribución', // Presenta el rol de distribución con acento.
    'REPARTIDOR' => 'Repartidor', // Presenta el rol de repartidor en formato legible.
]; // Finaliza el catálogo de nombres visibles por rol.
?>
<!-- Declara el documento HTML5 para la pantalla administrativa de usuarios. -->
<!DOCTYPE html>
<!-- Establece el idioma español para lectores de pantalla y navegador. -->
<html lang="es">
<!-- Inicia los metadatos y recursos externos de la página. -->
<head>
    <!-- Define la codificación de caracteres para presentar correctamente el contenido en español. -->
    <meta charset="UTF-8">
    <!-- Adapta la distribución de la página a pantallas móviles y de escritorio. -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Describe el contenido principal de esta pantalla administrativa. -->
    <meta name="description" content="Consulta y administra las cuentas de empleados de la tocinería.">
    <!-- Define el color de la interfaz mostrado por el marco del navegador móvil. -->
    <meta name="theme-color" content="#f6f0e6">
    <!-- Define el título de la pestaña del navegador. -->
    <title>Usuarios registrados | El Güero</title>
    <!-- Carga Bootstrap 5.3.3 con integridad del recurso desde jsDelivr. -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Carga los ajustes de color y geometría que complementan las utilidades de Bootstrap. -->
    <link href="assets/css/empleados.css" rel="stylesheet">
    <!-- Carga desde un archivo externo la búsqueda interactiva de filas de la tabla. -->
    <script src="assets/js/empleados.js" defer></script>
<!-- Finaliza la cabecera del documento. -->
</head>
<!-- Inicia la interfaz visible de la lista de empleados. -->
<body class="pagina-empleados">
    <!-- Organiza la navegación lateral y el contenido principal en una composición Bootstrap. -->
    <div class="contenedor-empleados d-flex min-vh-100">
        <!-- Presenta la navegación administrativa con el ancho visual del panel Figma. -->
        <aside class="barra-empleados d-none d-lg-flex flex-column p-3" aria-label="Navegación administrativa">
            <!-- Agrupa el distintivo y el nombre de la tocinería en la cabecera lateral. -->
            <a class="marca-empleados d-flex align-items-center gap-3 text-decoration-none" href="index.php?c=empleado&amp;a=lista" aria-label="El Güero, lista de usuarios">
                <!-- Representa el distintivo circular de la marca con la inicial de la tocinería. -->
                <span class="distintivo-marca d-flex align-items-center justify-content-center rounded-circle fw-bold" aria-hidden="true">G</span>
                <!-- Presenta el nombre comercial y su descriptor como dos líneas. -->
                <span class="d-flex flex-column">
                    <!-- Muestra el nombre de la tocinería. -->
                    <span class="fw-bold">El Güero</span>
                    <!-- Muestra el descriptor secundario del sistema. -->
                    <span class="small text-secondary">Tocinería · Sistema</span>
                <!-- Cierra la identidad textual de la marca. -->
                </span>
            <!-- Cierra el enlace de identidad de marca. -->
            </a>
            <!-- Agrupa las opciones de control de personal bajo su categoría de navegación. -->
            <nav class="mt-4" aria-label="Seguridad y acceso">
                <!-- Identifica el grupo de navegación de seguridad. -->
                <p class="titulo-navegacion small fw-semibold text-uppercase mb-2">Seguridad y acceso</p>
                <!-- Muestra los enlaces del personal solo a una sesión con rol administrador exacto. -->
                <?php if (isset($_SESSION['user']['rol']) && $_SESSION['user']['rol'] === 'ADMINISTRADOR'): ?>
                    <!-- Enlaza a la lista de usuarios usando el controlador y la acción solicitados. -->
                    <a class="enlace-navegacion activo d-flex align-items-center gap-2 rounded-3 px-3 py-2 text-decoration-none" href="index.php?c=empleado&amp;a=lista" aria-current="page">
                        <!-- Muestra un indicador visual para la lista de usuarios. -->
                        <span aria-hidden="true">●</span>
                        <!-- Nombra de forma explícita la lista de usuarios. -->
                        <span>Lista de Usuarios</span>
                    <!-- Cierra el enlace a la lista administrativa. -->
                    </a>
                    <!-- Enlaza al formulario de alta mediante el controlador y acción especificados. -->
                    <a class="enlace-navegacion d-flex align-items-center gap-2 rounded-3 px-3 py-2 text-decoration-none" href="index.php?c=empleado&amp;a=registro">
                        <!-- Representa la acción de alta con un signo más. -->
                        <span aria-hidden="true">＋</span>
                        <!-- Nombra claramente la acción de agregar un usuario. -->
                        <span>Agregar Usuario</span>
                    <!-- Cierra el enlace al formulario de registro. -->
                    </a>
                <!-- Finaliza la condición de visibilidad de las opciones administrativas. -->
                <?php endif; ?>
                <!-- Enlaza al perfil del usuario autenticado disponible para cualquier rol. -->
                <a class="enlace-navegacion d-flex align-items-center gap-2 rounded-3 px-3 py-2 text-decoration-none" href="index.php?c=empleado&amp;a=perfil">
                    <!-- Representa la acción de perfil con un símbolo descriptivo. -->
                    <span aria-hidden="true">👤</span>
                    <!-- Identifica el acceso al perfil personal. -->
                    <span>Mi Perfil</span>
                <!-- Cierra el enlace al perfil. -->
                </a>
            <!-- Cierra la navegación lateral. -->
            </nav>
            <!-- Mantiene la identificación de sesión anclada al borde inferior del menú. -->
            <div class="sesion-empleado d-flex align-items-center gap-2 mt-auto pt-3">
                <!-- Presenta las iniciales de la cuenta administrativa autenticada. -->
                <span class="avatar-empleado d-flex align-items-center justify-content-center rounded-circle fw-semibold" aria-hidden="true">AD</span>
                <!-- Muestra el nombre de usuario y su rol activo escapados para seguridad. -->
                <span class="d-flex flex-column overflow-hidden">
                    <!-- Identifica el usuario que inició sesión. -->
                    <span class="small fw-semibold text-truncate"><?= $escaparHtml((string) ($sesionVista['usuario'] ?? 'Administrador')) ?></span>
                    <!-- Indica el rol verificado por el constructor del controlador. -->
                    <span class="small text-secondary">Administrador</span>
                <!-- Cierra las etiquetas de identidad de la sesión. -->
                </span>
            <!-- Cierra el bloque de sesión del menú lateral. -->
            </div>
        <!-- Cierra el menú lateral. -->
        </aside>
        <!-- Agrupa el encabezado de página, búsqueda y tabla de empleados. -->
        <main class="contenido-empleados flex-grow-1 p-3 p-md-4 p-xl-5">
            <!-- Presenta la ruta de navegación y el título de la pantalla de usuarios. -->
            <header class="encabezado-pagina mb-4">
                <!-- Indica la ubicación de esta pantalla dentro del módulo administrativo. -->
                <p class="miga-pan text-secondary small mb-2">Seguridad y acceso <span aria-hidden="true">›</span> Usuarios</p>
                <!-- Alinea el título descriptivo y el acceso al formulario de alta. -->
                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                    <!-- Agrupa el título y la descripción de la lista. -->
                    <div>
                        <!-- Describe de forma directa la información que puede consultar el administrador. -->
                        <h1 class="h2 fw-bold mb-2">Usuarios registrados</h1>
                        <!-- Resume el contenido de esta pantalla. -->
                        <p class="text-secondary mb-0">Consulta las cuentas, roles y datos de contacto del personal.</p>
                    <!-- Cierra el bloque de texto del encabezado. -->
                    </div>
                    <!-- Enlaza a la pantalla del formulario de alta de usuarios. -->
                    <a class="btn btn-primary flex-shrink-0" href="index.php?c=empleado&amp;a=registro">Registrar usuario</a>
                <!-- Cierra la fila de encabezado y acción. -->
                </div>
            <!-- Cierra el encabezado de la pantalla. -->
            </header>
            <!-- Agrupa la búsqueda, el total de empleados y la tabla en una tarjeta blanca. -->
            <section class="tarjeta-lista card border-0" aria-labelledby="titulo-lista-empleados">
                <!-- Aplica espaciado responsivo al encabezado de la tarjeta. -->
                <div class="card-body p-3 p-md-4">
                    <!-- Distribuye el título, el total y el buscador sobre una sola fila cuando hay espacio. -->
                    <div class="d-flex flex-column flex-xl-row align-items-xl-center justify-content-between gap-3 mb-4">
                        <!-- Agrupa el nombre de la tabla y el número de registros recibidos del controlador. -->
                        <div>
                            <!-- Identifica la colección de empleados mostrada en la tabla. -->
                            <h2 class="h5 fw-bold mb-1" id="titulo-lista-empleados">Personal</h2>
                            <!-- Presenta el total real devuelto por el método listarTodos(). -->
                            <span class="badge etiqueta-total rounded-pill"><?= count($usuariosVista) ?> usuarios</span>
                        <!-- Cierra el resumen de la colección. -->
                        </div>
                        <!-- Proporciona un campo para filtrar las filas ya cargadas sin recargar la página. -->
                        <div class="busqueda-empleados input-group" role="search">
                            <!-- Añade una etiqueta accesible aunque el diseño use un campo compacto. -->
                            <label class="visually-hidden" for="buscar-empleado">Buscar empleado</label>
                            <!-- Captura el término usado para filtrar nombre, usuario, rol y teléfono. -->
                            <input class="form-control" id="buscar-empleado" type="search" placeholder="Buscar por nombre o usuario" autocomplete="off" data-busqueda-empleados>
                            <!-- Identifica visualmente la acción de búsqueda. -->
                            <span class="input-group-text" aria-hidden="true">⌕</span>
                        <!-- Cierra el grupo de búsqueda. -->
                        </div>
                    <!-- Cierra el encabezado interno de la tarjeta. -->
                    </div>
                    <!-- Mantiene desplazamiento horizontal de tabla en dispositivos estrechos. -->
                    <div class="table-responsive">
                        <!-- Presenta los campos solicitados con encabezados accesibles de tabla. -->
                        <table class="table tabla-empleados align-middle mb-0">
                            <!-- Define las columnas de identidad, contacto, rol y acciones. -->
                            <thead>
                                <!-- Nombra cada dato que el administrador puede revisar. -->
                                <tr>
                                    <!-- Encabeza la columna del identificador primario. -->
                                    <th scope="col">ID</th>
                                    <!-- Encabeza la columna del nombre compuesto. -->
                                    <th scope="col">Nombre completo</th>
                                    <!-- Encabeza el identificador de acceso del empleado. -->
                                    <th scope="col">Usuario</th>
                                    <!-- Encabeza el rol almacenado para el empleado. -->
                                    <th scope="col">Rol</th>
                                    <!-- Encabeza el número de contacto del empleado. -->
                                    <th scope="col">Teléfono</th>
                                    <!-- Encabeza los controles visibles de cada fila. -->
                                    <th scope="col" class="text-end">Acciones</th>
                                <!-- Cierra la fila de encabezados. -->
                                </tr>
                            <!-- Cierra la cabecera de la tabla. -->
                            </thead>
                            <!-- Presenta cada fila devuelta por el controlador y el estado sin resultados. -->
                            <tbody data-tabla-empleados>
                                <!-- Recorre cada empleado recibido desde EmpleadoController::lista(). -->
                                <?php foreach ($usuariosVista as $empleadoVista): ?>
                                    <!-- Normaliza el identificador a entero antes de usarlo en la celda. -->
                                    <?php $idEmpleadoVista = (int) ($empleadoVista['id_empleado'] ?? 0); ?>
                                    <!-- Obtiene el rol como texto para mostrar su etiqueta sin interpolar HTML. -->
                                    <?php $rolTecnicoVista = (string) ($empleadoVista['rol'] ?? ''); ?>
                                    <!-- Traduce los valores conocidos del ENUM y conserva otros valores escapados. -->
                                    <?php $rolVisibleVista = $etiquetasRoles[$rolTecnicoVista] ?? $rolTecnicoVista; ?>
                                    <!-- Asocia todos los campos visibles en un atributo de búsqueda para el filtro externo. -->
                                    <tr data-fila-empleado data-terminos="<?= $escaparHtml(implode(' ', [(string) ($empleadoVista['nombre_completo'] ?? ''), (string) ($empleadoVista['usuario'] ?? ''), $rolVisibleVista, (string) ($empleadoVista['telefono'] ?? '')])) ?>">
                                        <!-- Muestra el identificador de la cuenta. -->
                                        <td class="text-secondary">#<?= $idEmpleadoVista ?></td>
                                        <!-- Muestra el nombre compuesto escapado y con énfasis moderado. -->
                                        <td class="fw-semibold"><?= $escaparHtml((string) ($empleadoVista['nombre_completo'] ?? '')) ?></td>
                                        <!-- Muestra el nombre de acceso escapado. -->
                                        <td><span class="texto-usuario">@<?= $escaparHtml((string) ($empleadoVista['usuario'] ?? '')) ?></span></td>
                                        <!-- Muestra una etiqueta neutra para el rol del empleado. -->
                                        <td><span class="badge etiqueta-rol rounded-pill"><?= $escaparHtml($rolVisibleVista) ?></span></td>
                                        <!-- Muestra el teléfono o una indicación si el dato está vacío. -->
                                        <td><?= $escaparHtml((string) ($empleadoVista['telefono'] ?? '')) ?: '—' ?></td>
                                        <!-- Presenta acciones con texto visible y etiquetas accesibles. -->
                                        <td class="text-end">
                                            <!-- Agrupa los botones de fila sin mezclar JavaScript en el marcado. -->
                                            <div class="d-inline-flex flex-wrap justify-content-end gap-2">
                                                <!-- Ofrece un control de lectura visual con el identificador del empleado. -->
                                                <button class="btn btn-sm btn-outline-secondary" type="button" aria-label="Ver empleado <?= $idEmpleadoVista ?>" data-accion-empleado="ver" data-id-empleado="<?= $idEmpleadoVista ?>">Ver</button>
                                                <!-- Presenta la acción administrativa de restablecimiento para la implementación de su flujo. -->
                                                <button class="btn btn-sm btn-outline-primary" type="button" aria-label="Restablecer contraseña de <?= $escaparHtml((string) ($empleadoVista['usuario'] ?? '')) ?>" data-accion-empleado="restablecer" data-id-empleado="<?= $idEmpleadoVista ?>">Restablecer</button>
                                            <!-- Cierra el grupo de acciones de la fila. -->
                                            </div>
                                        <!-- Cierra la celda de acciones. -->
                                        </td>
                                    <!-- Cierra la fila del empleado. -->
                                    </tr>
                                <!-- Finaliza el recorrido de la colección de empleados. -->
                                <?php endforeach; ?>
                                <!-- Muestra el estado vacío cuando la consulta no devuelve empleados o el filtro no encuentra coincidencias. -->
                                <tr data-sin-resultados <?= $usuariosVista === [] ? '' : 'hidden' ?>>
                                    <!-- Explica al administrador que no hay filas coincidentes. -->
                                    <td class="estado-vacio text-center text-secondary py-5" colspan="7">No se encontraron usuarios.</td>
                                <!-- Cierra la fila del estado vacío. -->
                                </tr>
                            <!-- Cierra el cuerpo de la tabla. -->
                            </tbody>
                        <!-- Cierra la tabla responsiva. -->
                        </table>
                    <!-- Cierra el contenedor de desplazamiento de tabla. -->
                    </div>
                <!-- Cierra el contenido de la tarjeta. -->
                </div>
            <!-- Cierra la tarjeta de usuarios. -->
            </section>
        <!-- Cierra el contenido principal. -->
        </main>
    <!-- Cierra la estructura general de la pantalla. -->
    </div>
<!-- Cierra el cuerpo del documento. -->
</body>
<!-- Finaliza el documento HTML. -->
</html>
