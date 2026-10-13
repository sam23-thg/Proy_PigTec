<?php
// Conserva el texto de alerta recibido y usa una cadena vacía cuando no hay mensajes.
$mensajeVista = isset($mensaje) ? (string) $mensaje : ''; // Prepara un valor seguro para la salida HTML.
// Permite exclusivamente los estados visuales de éxito y error definidos por Bootstrap.
$tipoAlertaVista = isset($tipoAlerta) && $tipoAlerta === 'success' ? 'success' : 'danger'; // Representa fallos técnicos y de credenciales como alertas rojas.
// Identifica si se está mostrando una confirmación de sesión ya autenticada.
$sesionIniciadaVista = !empty($modoSesionIniciada); // Reutiliza la página como destino provisional por rol.
?>
<!-- Declara el documento de inicio de sesión en HTML5. -->
<!DOCTYPE html>
<!-- Configura el idioma español para el contenido y tecnologías de asistencia. -->
<html lang="es">
<!-- Inicia los metadatos y recursos externos de la página. -->
<head>
    <!-- Establece la codificación que permite representar correctamente caracteres en español. -->
    <meta charset="UTF-8">
    <!-- Hace que la distribución de columnas pueda adaptarse a pantallas móviles. -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Describe el propósito de esta página de acceso. -->
    <meta name="description" content="Acceso del personal al sistema de gestión de la tocinería El Güero.">
    <!-- Define el color de la interfaz usado por el navegador en dispositivos móviles. -->
    <meta name="theme-color" content="#4c142b">
    <!-- Define el título de la pestaña del navegador. -->
    <title>Iniciar sesión | Tocinería El Güero</title>
    <!-- Carga Bootstrap 5.3.3 desde jsDelivr con control de integridad del recurso. -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="assets/css/paleta-rosa.css" rel="stylesheet">
    <!-- Carga el comportamiento de validación desde un archivo externo y sin código JavaScript en línea. -->
    <script src="assets/js/auth.js" defer></script>
<!-- Finaliza la cabecera del documento. -->
</head>
<!-- Inicia el contenido visible de la pantalla. -->
<body class="bg-light">
    <!-- Divide la pantalla en presentación de marca y zona de acceso con las columnas responsivas de Bootstrap. -->
    <main class="container-fluid p-0">
        <!-- Distribuye los dos paneles en una fila sin separación entre columnas. -->
        <div class="row g-0 min-vh-100">
            <!-- Presenta la bienvenida del sistema como panel lateral en escritorio y la oculta en móviles. -->
            <aside class="col-lg-5 d-none d-lg-flex flex-column bg-dark text-white p-5" aria-label="Presentación del sistema">
                <!-- Agrupa el logotipo textual y la identificación de la tocinería. -->
                <a class="d-flex align-items-center gap-3 text-white text-decoration-none" href="index.php?accion=login" aria-label="Tocinería El Güero, inicio">
                    <!-- Representa el monograma de marca con utilidades de Bootstrap. -->
                    <!-- Agrupa nombre comercial y descriptor en una columna compacta. -->
                    <span class="d-flex flex-column">
                        <!-- Muestra el nombre de la tocinería. -->
                        <span class="fs-5 fw-semibold">El Güero</span>
                        <!-- Muestra el descriptor del sistema de gestión. -->
                        <span class="small text-white-50">Tocinería · Sistema de gestión</span>
                    <!-- Cierra el bloque de identidad de marca. -->
                    </span>
                <!-- Cierra el enlace de identidad. -->
                </a>
                <!-- Muestra las opciones de navegación únicamente en el panel del usuario autenticado. -->
                <?php if ($sesionIniciadaVista): ?>
                    <!-- Agrupa los accesos como lista vertical Bootstrap dentro de la barra lateral. -->
                    <nav class="list-group list-group-flush mt-4" aria-label="Control y navegación">
                        <?php if (isset($_SESSION['user']['rol']) && in_array($_SESSION['user']['rol'], ['ADMINISTRADOR', 'ALMACEN'], true)): ?>
                            <!-- Agrupa las opciones de catálogo e inventario disponibles para administración y almacén. -->
                            <p class="small fw-semibold text-uppercase text-white-50 mt-2 mb-2">Catálogo e inventario</p>
                            <!-- Enlaza al registro unificado de producto y existencia inicial. -->
                            <a class="list-group-item list-group-item-action bg-transparent border-secondary text-white rounded-3 mb-1" href="index.php?c=producto&amp;a=nuevo">Registrar Producto</a>
                            <!-- Enlaza a la búsqueda, edición y consulta de existencias del catálogo. -->
                            <a class="list-group-item list-group-item-action bg-transparent border-secondary text-white rounded-3 mb-1" href="index.php?c=producto&amp;a=editar">Productos e Inventario</a>
                        <?php endif; ?>
                        <?php if (isset($_SESSION['user']['rol']) && $_SESSION['user']['rol'] === 'ADMINISTRADOR'): ?>
                            <!-- Enlaza al directorio de usuarios con el controlador y la acción solicitados. -->
                            <a class="list-group-item list-group-item-action bg-transparent border-secondary text-white rounded-3 mb-1" href="index.php?c=empleado&amp;a=lista">Lista de Usuarios</a>
                            <!-- Enlaza al formulario de alta de empleados con la ruta MVC solicitada. -->
                            <a class="list-group-item list-group-item-action bg-transparent border-secondary text-white rounded-3 mb-1" href="index.php?c=empleado&amp;a=registro">Registrar Empleado</a>
                        <?php endif; ?>
                        <!-- Enlaza al perfil del usuario autenticado para cualquier rol. -->
                        <a class="list-group-item list-group-item-action bg-transparent border-secondary text-white rounded-3" href="index.php?c=empleado&amp;a=perfil">Mi Perfil</a>
                    <!-- Cierra el menú visible. -->
                    </nav>
                <!-- Finaliza la condición que restringe el menú a la sesión iniciada. -->
                <?php endif; ?>
                <!-- Centra el mensaje de bienvenida entre la marca y el pie del panel. -->
                <section class="my-auto py-5" aria-labelledby="titulo-bienvenida">
                    <!-- Presenta el mensaje de bienvenida con la escala de títulos de Bootstrap. -->
                    <h1 class="display-5 fw-bold mb-3" id="titulo-bienvenida">Bienvenido al sistema de la tocinería</h1>
                    <!-- Resume el propósito general del sistema. -->
                    <p class="lead text-white-50 mb-4">Administra ventas, inventario, pedidos y entregas desde un solo lugar.</p>
                    <!-- Agrupa las áreas disponibles con etiquetas redondeadas de Bootstrap. -->
                    <ul class="d-flex flex-wrap gap-2 list-unstyled mb-0" aria-label="Módulos del sistema">
                        <!-- Identifica el módulo de ventas. -->
                        <li><span class="badge rounded-pill text-bg-secondary">Ventas</span></li>
                        <!-- Identifica el módulo de inventario. -->
                        <li><span class="badge rounded-pill text-bg-secondary">Inventario</span></li>
                        <!-- Identifica el módulo de pedidos. -->
                        <li><span class="badge rounded-pill text-bg-secondary">Pedidos</span></li>
                        <!-- Identifica el módulo de distribución. -->
                        <li><span class="badge rounded-pill text-bg-secondary">Distribución</span></li>
                    <!-- Cierra la lista de módulos. -->
                    </ul>
                <!-- Cierra el mensaje de bienvenida. -->
                </section>
                <!-- Muestra la acción de salida solo cuando la sesión ya fue autenticada. -->
                <?php if ($sesionIniciadaVista): ?>
                    <!-- Proporciona a cualquier usuario autenticado una acción visible para cerrar sesión. -->
                    <a class="btn btn-outline-light w-100 mb-3" href="index.php?c=auth&amp;a=logout">Cerrar Sesión</a>
                <!-- Oculta el cierre de sesión a visitantes que aún no tienen cuenta autenticada. -->
                <?php endif; ?>
                <!-- Muestra el crédito de la marca al final del panel lateral. -->
                <p class="small text-white-50 mb-0">Todos los derechos reservados. Tocinería El Güero</p>
            <!-- Cierra el panel de presentación de marca. -->
            </aside>
            <!-- Centra la tarjeta de Login en el espacio restante de la pantalla. -->
            <section class="col-12 col-lg-7 d-flex align-items-center justify-content-center p-0" aria-label="Área de acceso">
                <!-- Limita la tarjeta mediante las columnas internas de Bootstrap y la centra horizontalmente. -->
                <div class="row w-100 justify-content-center g-0">
                    <!-- Ajusta el ancho de la tarjeta a escritorio y dispositivos pequeños. -->
                    <div class="col-12 col-md-9 col-xl-6 px-0 ms-xl-2">
                        <!-- Presenta el formulario en una tarjeta centrada con borde y sombra de Bootstrap. -->
                        <div class="card border-0 shadow" aria-labelledby="titulo-login">
                            <!-- Añade el espaciado interior estándar de Bootstrap al contenido. -->
                            <div class="card-body p-4">
                                <!-- Centra los elementos internos con la cuadrícula de doce columnas de Bootstrap. -->
                                <div class="row g-0 justify-content-center">
                                    <!-- Ajusta el contenido a una anchura interior cercana a los 346px de Figma. -->
                                    <div class="col-11 ms-2">
                                <!-- Presenta el título y la instrucción alineados a la izquierda. -->
                                <header class="mt-3 mb-2">
                                    <!-- Identifica el propósito de la pantalla o el área de sesión autenticada. -->
                                    <h2 class="h3 fw-bold mb-2" id="titulo-login"><?= $sesionIniciadaVista ? htmlspecialchars((string) ($tituloPanel ?? 'Acceso autorizado'), ENT_QUOTES, 'UTF-8') : 'Iniciar sesión' ?></h2>
                                    <!-- Indica al usuario qué debe hacer para continuar. -->
                                    <p class="text-secondary mb-0"><?= $sesionIniciadaVista ? 'Tu sesión se encuentra activa.' : 'Ingresa tus credenciales para continuar.' ?></p>
                                <!-- Cierra el encabezado de la tarjeta. -->
                                </header>
                                <!-- Muestra los errores técnicos o de credenciales encima de los campos. -->
                                <?php if ($mensajeVista !== ''): ?>
                                    <!-- Representa el mensaje en una alerta de éxito o error de Bootstrap y escapa el texto. -->
                                    <div class="alert alert-<?= htmlspecialchars($tipoAlertaVista, ENT_QUOTES, 'UTF-8') ?> d-flex align-items-center gap-2" role="alert">
                                        <!-- Muestra un símbolo de atención sin cargar una dependencia adicional. -->
                                        <span class="fw-bold">Aviso:</span>
                                        <!-- Presenta el mensaje escapado para evitar interpretar HTML externo. -->
                                        <span><?= htmlspecialchars($mensajeVista, ENT_QUOTES, 'UTF-8') ?></span>
                                    <!-- Cierra la alerta del formulario. -->
                                    </div>
                                <!-- Finaliza la condición que muestra alertas. -->
                                <?php endif; ?>
                                <!-- Muestra un resumen cuando el Front Controller confirma una sesión autenticada. -->
                                <?php if ($sesionIniciadaVista): ?>
                                    <!-- Agrupa los datos de la sesión dentro de un contenedor de estado. -->
                                    <div class="alert alert-success mb-0" role="status">
                                        <!-- Presenta el usuario validado y escapa su contenido. -->
                                        <p class="mb-2"><strong>Usuario:</strong> <?= htmlspecialchars((string) ($usuarioAutenticado ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                                        <!-- Presenta el rol autorizado y escapa su contenido. -->
                                        <p class="mb-3"><strong>Área:</strong> <?= htmlspecialchars((string) ($tituloPanel ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                                        <?php if (isset($_SESSION['user']['rol']) && in_array($_SESSION['user']['rol'], ['ADMINISTRADOR', 'ALMACEN'], true)): ?>
                                            <!-- Ofrece acceso directo al módulo de catálogo desde el panel de bienvenida. -->
                                            <a class="btn btn-success w-100 mb-2" href="index.php?c=producto&amp;a=editar">Productos e Inventario</a>
                                            <!-- Ofrece acceso directo al formulario de alta de producto. -->
                                            <a class="btn btn-outline-success w-100 mb-2" href="index.php?c=producto&amp;a=nuevo">Registrar Producto</a>
                                        <?php endif; ?>
                                        <!-- Enlace directo al perfil del empleado. -->
                                        <a class="btn btn-outline-success w-100" href="index.php?c=empleado&amp;a=perfil">Mi Perfil</a>
                                    <!-- Cierra el resumen de sesión. -->
                                    </div>
                                <!-- Muestra el formulario si todavía no hay una sesión autenticada. -->
                                <?php else: ?>
                                    <!-- Envía las credenciales mediante POST conservando la ruta aprobada del Front Controller. -->
                                    <form id="formulario-login" action="index.php?accion=login" method="post" data-validacion-cliente novalidate>
                                        <!-- Agrupa el rótulo, la caja de usuario y su validación. -->
                                        <div class="mb-3">
                                            <!-- Asocia el rótulo con el campo de usuario. -->
                                            <label class="form-label fw-semibold" for="usuario">Usuario <span class="text-danger" aria-hidden="true">(*)</span></label>
                                            <!-- Captura el nombre de usuario como texto requerido. -->
                                            <input class="form-control form-control-lg" id="usuario" name="usuario" type="text" autocomplete="username" maxlength="50" placeholder="Ingresa tu usuario" required aria-describedby="error-usuario">
                                            <!-- Informa el error accesible si el usuario deja el campo vacío. -->
                                            <div class="invalid-feedback" id="error-usuario">Ingresa tu usuario.</div>
                                        <!-- Cierra el grupo del nombre de usuario. -->
                                        </div>
                                        <!-- Agrupa el rótulo, la caja protegida y su validación. -->
                                        <div class="mb-4">
                                            <!-- Asocia el rótulo con el campo de contraseña. -->
                                            <label class="form-label fw-semibold" for="contrasenia">Contraseña <span class="text-danger" aria-hidden="true">(*)</span></label>
                                            <!-- Captura la clave con el tipo password para ocultar sus caracteres. -->
                                            <input class="form-control form-control-lg" id="contrasenia" name="contrasenia" type="password" autocomplete="current-password" placeholder="Ingresa tu contraseña" required aria-describedby="error-contrasenia">
                                            <!-- Informa el error accesible si el campo de contraseña está vacío. -->
                                            <div class="invalid-feedback" id="error-contrasenia">Ingresa tu contraseña.</div>
                                        <!-- Cierra el grupo de contraseña. -->
                                        </div>
                                        <!-- Reserva el bloque de ayuda de 32px presente en la tarjeta de Figma. -->
                                        <p class="small text-secondary py-2 mb-0">¿Olvidaste tu contraseña? Contacta al administrador.</p>
                                        <!-- Envía el formulario mediante un botón primario a todo lo ancho de la tarjeta. -->
                                        <button class="btn btn-primary btn-lg w-100" type="submit">Ingresar</button>
                                    <!-- Cierra el formulario de inicio de sesión. -->
                                    </form>
                                <!-- Finaliza la selección entre el formulario y el resumen de sesión. -->
                                <?php endif; ?>
                                <!-- Indica que el sistema está reservado para personal autorizado. -->
                                <p class="small text-secondary d-flex align-items-center gap-2 mt-4 mb-0">
                                    <!-- Añade un indicador sencillo de información en línea. -->
                                    <span class="fw-semibold">Información:</span>
                                    <!-- Presenta el aviso de acceso restringido. -->
                                    <span>Acceso exclusivo para personal autorizado.</span>
                                <!-- Cierra el aviso informativo. -->
                                </p>
                                    <!-- Cierra la columna interior que centra los componentes del formulario. -->
                                    </div>
                                <!-- Cierra la fila Bootstrap del contenido de la tarjeta. -->
                                </div>
                            <!-- Cierra el contenido de la tarjeta. -->
                            </div>
                        <!-- Cierra la tarjeta de inicio de sesión. -->
                        </div>
                    <!-- Cierra la columna responsiva de la tarjeta. -->
                    </div>
                <!-- Cierra la fila de centrado de tarjeta. -->
                </div>
            <!-- Cierra el panel del área de acceso. -->
            </section>
        <!-- Cierra la fila de paneles. -->
        </div>
    <!-- Cierra el contenedor principal. -->
    </main>
<!-- Cierra el cuerpo del documento. -->
</body>
<!-- Finaliza el documento HTML. -->
</html>
