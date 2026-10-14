<?php
// Escapa todas las cadenas dinámicas antes de incluirlas en el documento HTML.
$escaparRegistro = static fn (string $valor): string => htmlspecialchars($valor, ENT_QUOTES, 'UTF-8'); // Reduce el riesgo de inyección de marcado en el formulario.
// Conserva los valores del formulario enviados por el controlador o usa un arreglo vacío.
$datosRegistroVista = isset($datosFormulario) && is_array($datosFormulario) ? $datosFormulario : []; // Mantiene la plantilla segura al renderizar una petición GET.
// Garantiza que la colección compacta de empleados siempre sea un arreglo.
$usuariosRegistroVista = isset($usuariosRegistrados) && is_array($usuariosRegistrados) ? $usuariosRegistrados : []; // Evita fallos al recorrer una lista vacía.
// Conserva solo una lista de permisos marcada y validada por el controlador.
$permisosRegistroVista = isset($permisosFormulario) && is_array($permisosFormulario) ? $permisosFormulario : []; // No confía en datos POST leídos directamente por la plantilla.
// Define los cinco valores exactos permitidos por el ENUM de la columna rol.
$rolesRegistro = [ // Mantiene las opciones del formulario sincronizadas con el diccionario de datos.
    'ADMINISTRADOR' => 'Administrador', // Etiqueta visible para el rol administrador.
    'VENTAS' => 'Ventas', // Etiqueta visible para el rol de ventas.
    'ALMACEN' => 'Almacén', // Etiqueta visible para el rol de almacén.
    'DISTRIBUCION' => 'Distribución', // Etiqueta visible para el rol de distribución.
    'REPARTIDOR' => 'Repartidor', // Etiqueta visible para el rol de repartidor.
]; // Finaliza la lista de roles permitidos.
// Define las capacidades que pueden representarse mediante la grilla de permisos.
$opcionesPermisos = [ // Usa las mismas claves estáticas que el controlador de autenticación.
    'gestionar_empleados' => ['etiqueta' => 'Gestionar empleados', 'roles' => ['ADMINISTRADOR']], // Limita la gestión de personal al rol administrador.
    'restablecer_password' => ['etiqueta' => 'Restablecer contraseñas', 'roles' => ['ADMINISTRADOR']], // Limita los restablecimientos al rol administrador.
    'ver_catalogo' => ['etiqueta' => 'Consultar catálogo', 'roles' => ['ADMINISTRADOR', 'VENTAS', 'ALMACEN', 'DISTRIBUCION', 'REPARTIDOR']], // Permite consulta básica a los roles funcionales.
    'editar_productos' => ['etiqueta' => 'Editar productos', 'roles' => ['ADMINISTRADOR', 'ALMACEN']], // Limita cambios del catálogo a administrador y almacén.
]; // Finaliza la definición de permisos para el formulario.
// Normaliza el tipo de alerta recibido y limita sus variantes a clases conocidas de Bootstrap.
$tipoAlertaRegistro = isset($tipoAlerta) && in_array($tipoAlerta, ['success', 'danger', 'warning', 'info'], true) ? $tipoAlerta : 'danger'; // Evita insertar clases arbitrarias en el atributo HTML.
// Determina si el controlador ha preparado un mensaje para mostrar sobre el formulario.
$mensajeRegistro = isset($mensaje) ? (string) $mensaje : ''; // Permite renderizar la vista sin alertas iniciales.
?>
<!-- Declara la estructura principal del formulario de registro en HTML5. -->
<!DOCTYPE html>
<!-- Configura el idioma español para tecnologías de asistencia y presentación del navegador. -->
<html lang="es">
<!-- Inicia los metadatos y recursos externos de Bootstrap y del formulario. -->
<head>
    <!-- Define la codificación necesaria para nombres y mensajes en español. -->
    <meta charset="UTF-8">
    <!-- Adapta las dos columnas a pantallas móviles y de escritorio. -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Describe la pantalla de alta de cuentas de personal. -->
    <meta name="description" content="Registro administrativo de usuarios del sistema El Güero.">
    <!-- Define el color de fondo cálido para la barra del navegador móvil. -->
    <meta name="theme-color" content="#fff5f8">
    <!-- Define el título que aparece en la pestaña del navegador. -->
    <title>Registrar usuarios | El Güero</title>
    <!-- Carga Bootstrap 5.3.3 con integridad del recurso remoto. -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Reutiliza la paleta y las dimensiones del menú administrativo de empleados. -->
    <link href="assets/css/empleados.css" rel="stylesheet">
    <!-- Carga los ajustes visuales específicos del formulario sin estilos en línea. -->
    <link href="assets/css/registro-empleados.css" rel="stylesheet">
    <link href="assets/css/paleta-rosa.css" rel="stylesheet">
    <!-- Carga la validación y selección de permisos desde un archivo JavaScript externo. -->
    <script src="assets/js/registro-empleados.js" defer></script>
    <script src="assets/js/navegacion.js" defer></script>
<!-- Cierra la cabecera y sus recursos externos. -->
</head>
<!-- Inicia la pantalla administrativa de registro. -->
<body class="pagina-empleados">
    <!-- Distribuye el menú administrativo lateral y el contenido de registro. -->
    <div class="contenedor-empleados d-flex min-vh-100">
        <!-- Reutiliza el menú lateral del módulo de usuarios mostrado en la referencia visual. -->
        <aside class="barra-empleados d-flex flex-column p-3" aria-label="Navegación administrativa">
            <!-- Agrupa el distintivo y nombre de marca en la zona superior del menú. -->
            <a class="marca-empleados d-flex align-items-center gap-3 text-decoration-none" href="index.php?c=empleado&amp;a=lista" aria-label="El Güero, lista de usuarios">
                <!-- Usa el logotipo oficial en el encabezado uniforme del menú. -->
                <img class="logo-sistema" src="assets/logoPIG.png" alt="Logotipo de El Güero">
                <!-- Representa el distintivo de marca con una inicial accesible como decoración. -->
                <!-- Apila el nombre de la tocinería y el descriptor de su sistema. -->
                <span class="d-flex flex-column">
                    <!-- Muestra el nombre comercial. -->
                    <span class="fw-bold">El Güero</span>
                    <!-- Muestra el descriptor secundario. -->
                    <span class="small text-secondary">Tocinería · Sistema</span>
                <!-- Cierra la identidad textual de la marca. -->
                </span>
            <!-- Cierra el enlace de marca. -->
            </a>
            <!-- Ofrece un acceso directo a la bienvenida del panel autenticado. -->
            <a class="enlace-inicio-navegacion" href="index.php" aria-label="Ir a la pantalla principal">⌂ <span>Inicio</span></a>
            <!-- Agrupa las opciones de control de personal disponibles para el administrador. -->
            <nav class="mt-4" aria-label="Navegación del sistema">
                <!-- Mantiene visibles los accesos de catálogo junto con los de seguridad. -->
                <button class="boton-categoria-navegacion titulo-navegacion small fw-semibold text-uppercase" type="button" data-menu-desplegable aria-expanded="true" aria-controls="menu-catalogo">Catálogo e inventario</button>
                <div class="grupo-navegacion-contenido is-open" id="menu-catalogo">
                <a class="enlace-navegacion d-flex align-items-center gap-2 rounded-3 px-3 py-2 text-decoration-none" href="index.php?c=producto&amp;a=nuevo"><span>Registrar Producto</span></a>
                <a class="enlace-navegacion d-flex align-items-center gap-2 rounded-3 px-3 py-2 text-decoration-none" href="index.php?c=producto&amp;a=editar"><span>Productos e Inventario</span></a>
                <!-- Nombra la categoría de navegación del módulo. -->
                </div>
                <button class="boton-categoria-navegacion titulo-navegacion small fw-semibold text-uppercase" type="button" data-menu-desplegable aria-expanded="true" aria-controls="menu-seguridad">Seguridad y acceso</button>
                <div class="grupo-navegacion-contenido is-open" id="menu-seguridad">
                <!-- Limita la visibilidad de ambos enlaces a una sesión de rol ADMINISTRADOR exacto. -->
                <?php if (isset($_SESSION['user']['rol']) && $_SESSION['user']['rol'] === 'ADMINISTRADOR'): ?>
                    <!-- Enlaza a la lista de usuarios con el controlador y acción solicitados. -->
                    <a class="enlace-navegacion d-flex align-items-center gap-2 rounded-3 px-3 py-2 text-decoration-none" href="index.php?c=empleado&amp;a=lista">
                        <!-- Representa el acceso a la lista con un indicador visual sencillo. -->
                        <!-- Identifica claramente la lista de cuentas. -->
                        <span>Lista de Usuarios</span>
                    <!-- Cierra el enlace a la lista. -->
                    </a>
                    <!-- Indica la sección actual de registro con el estado activo. -->
                    <a class="enlace-navegacion activo d-flex align-items-center gap-2 rounded-3 px-3 py-2 text-decoration-none" href="index.php?c=empleado&amp;a=registro" aria-current="page">
                        <!-- Representa el acceso de alta mediante un signo más. -->
                        <!-- Nombra el formulario actual de incorporación. -->
                        <span>Agregar Usuario</span>
                    <!-- Cierra el enlace al formulario de registro. -->
                    </a>
                <!-- Finaliza la condición de visibilidad para roles que no sean administrador. -->
                <?php endif; ?>
                <!-- Enlaza al perfil del usuario autenticado disponible para cualquier rol. -->
                <a class="enlace-navegacion d-flex align-items-center gap-2 rounded-3 px-3 py-2 text-decoration-none" href="index.php?c=empleado&amp;a=perfil">
                    <!-- Representa la acción de perfil con un símbolo descriptivo. -->
                    <!-- Identifica el acceso al perfil personal. -->
                    <span>Mi Perfil</span>
                <!-- Cierra el enlace al perfil. -->
                </a>
                </div>
            <!-- Cierra la navegación lateral. -->
            </nav>
            <!-- Presenta la identidad del administrador en el borde inferior del menú. -->
            <div class="sesion-empleado d-flex align-items-center gap-2 mt-auto pt-3">
                <!-- Muestra las iniciales de la cuenta autenticada. -->
                <!-- Muestra el usuario autenticado y el rol que pasó la verificación del controlador. -->
                <span class="d-flex flex-column overflow-hidden">
                    <!-- Imprime el usuario escapado recibido desde la sesión. -->
                    <span class="small fw-semibold text-truncate"><?= $escaparRegistro((string) ($usuarioSesion['usuario'] ?? 'Administrador')) ?></span>
                    <!-- Identifica el rol administrativo requerido para acceder al formulario. -->
                    <span class="small text-secondary">Administrador</span>
                <!-- Cierra los datos de sesión. -->
                </span>
                <!-- Permite terminar la sesión desde el pie persistente del menú. -->
                <a class="btn btn-sm btn-outline-secondary enlace-cerrar-sesion" href="index.php?c=auth&amp;a=logout">Cerrar sesión</a>
            <!-- Cierra el bloque de sesión lateral. -->
            </div>
        <!-- Cierra el menú lateral. -->
        </aside>
        <!-- Agrupa el encabezado, alerta, formulario de dos columnas y usuarios registrados. -->
        <main class="contenido-empleados flex-grow-1 p-3 p-md-4 p-xl-5">
            <!-- Presenta la ruta administrativa y el título del formulario. -->
            <header class="encabezado-pagina mb-4">
                <!-- Indica la ubicación de la pantalla en la navegación. -->
                <p class="miga-pan text-secondary small mb-2">Seguridad y acceso / Usuarios</p>
                <!-- Muestra el título inspirado en el nodo de registro de Figma. -->
                <h1 class="h2 fw-bold mb-2">Registrar usuarios</h1>
                <!-- Resume los pasos y datos necesarios para el alta. -->
                <p class="text-secondary mb-0">Da de alta a una persona en el sistema: cuenta, datos personales, rol y permisos.</p>
            <!-- Cierra el encabezado de registro. -->
            </header>
            <!-- Presenta errores o confirmaciones antes del formulario como alertas Bootstrap. -->
            <?php if ($mensajeRegistro !== ''): ?>
                <!-- Escapa el mensaje y limita la clase a variantes permitidas. -->
                <div class="alert alert-<?= $escaparRegistro($tipoAlertaRegistro) ?>" role="alert"><?= $escaparRegistro($mensajeRegistro) ?></div>
            <!-- Finaliza la presentación condicional del mensaje. -->
            <?php endif; ?>
            <!-- Envía el alta por POST mediante la ruta del Front Controller. -->
            <form id="formulario-registro-empleado" action="index.php?accion=empleados-registro" method="post" data-validacion-registro novalidate>
                <!-- Organiza los campos obligatorios y opcionales en dos columnas responsivas. -->
                <div class="row g-4">
                    <!-- Reúne en la primera columna los datos que el requisito marca obligatorios. -->
                    <section class="col-12 col-xl-7" aria-labelledby="titulo-datos-obligatorios">
                        <!-- Presenta los campos obligatorios en una tarjeta de superficie blanca. -->
                        <div class="tarjeta-registro card border-0 h-100">
                            <!-- Espacia el contenido interior con utilidades Bootstrap. -->
                            <div class="card-body p-3 p-md-4">
                                <!-- Explica qué campos de esta columna son obligatorios. -->
                                <div class="mb-4">
                                    <!-- Identifica la sección requerida del formulario. -->
                                    <h2 class="h5 fw-bold mb-1" id="titulo-datos-obligatorios">Datos obligatorios</h2>
                                    <!-- Explica el uso del asterisco rojo indicado por la guía visual. -->
                                    <p class="small text-secondary mb-0">Los campos marcados con <span class="text-danger fw-bold">(*)</span> son obligatorios.</p>
                                <!-- Cierra la cabecera de datos obligatorios. -->
                                </div>
                                <!-- Coloca nombre y apellido paterno en una fila de dos controles. -->
                                <div class="row g-3">
                                    <!-- Aloja el nombre obligatorio dentro de media columna. -->
                                    <div class="col-12 col-md-6">
                                        <!-- Asocia el rótulo con el campo requerido de nombre. -->
                                        <label class="form-label fw-semibold" for="nombre">Nombre <span class="text-danger">(*)</span></label>
                                        <!-- Captura el nombre con longitud igual al límite del esquema. -->
                                        <input class="form-control" id="nombre" name="nombre" type="text" maxlength="100" pattern="[\p{L}\p{M} .’'\-]+" autocomplete="given-name" value="<?= $escaparRegistro((string) ($datosRegistroVista['nombre'] ?? '')) ?>" required aria-describedby="error-nombre">
                                        <!-- Muestra la ayuda de validación si falta el nombre. -->
                                        <div class="invalid-feedback" id="error-nombre">Ingresa un nombre con letras y separadores habituales, sin números.</div>
                                    <!-- Cierra la columna del nombre. -->
                                    </div>
                                    <!-- Aloja el apellido paterno obligatorio dentro de media columna. -->
                                    <div class="col-12 col-md-6">
                                        <!-- Asocia el rótulo con el campo requerido de apellido paterno. -->
                                        <label class="form-label fw-semibold" for="apellido-p">Apellido paterno <span class="text-danger">(*)</span></label>
                                        <!-- Captura el apellido paterno con longitud compatible con la columna. -->
                                        <input class="form-control" id="apellido-p" name="apellido_p" type="text" maxlength="100" pattern="[\p{L}\p{M} .’'\-]+" autocomplete="family-name" value="<?= $escaparRegistro((string) ($datosRegistroVista['apellido_p'] ?? '')) ?>" required aria-describedby="error-apellido-p">
                                        <!-- Muestra la ayuda de validación si falta el apellido paterno. -->
                                        <div class="invalid-feedback" id="error-apellido-p">Ingresa un apellido con letras y separadores habituales, sin números.</div>
                                    <!-- Cierra la columna del apellido paterno. -->
                                    </div>
                                    <!-- Aloja el usuario de inicio de sesión a todo el ancho de la columna principal. -->
                                    <div class="col-12 col-md-6">
                                        <!-- Asocia el rótulo con el campo requerido de usuario. -->
                                        <label class="form-label fw-semibold" for="usuario">Usuario <span class="text-danger">(*)</span></label>
                                        <!-- Captura un nombre de usuario dentro del tamaño definido por la base. -->
                                        <input class="form-control" id="usuario" name="usuario" type="text" minlength="3" maxlength="50" pattern="[A-Za-z0-9._-]{3,50}" autocomplete="username" value="<?= $escaparRegistro((string) ($datosRegistroVista['usuario'] ?? '')) ?>" required aria-describedby="error-usuario">
                                        <!-- Muestra la ayuda de validación si falta el usuario. -->
                                        <div class="invalid-feedback" id="error-usuario">Usa de 3 a 50 letras, números, puntos, guiones o guiones bajos.</div>
                                    <!-- Cierra la columna del usuario. -->
                                    </div>
                                    <!-- Aloja el campo de contraseña obligatorio y siempre oculto. -->
                                    <div class="col-12 col-md-6">
                                        <!-- Asocia el rótulo con el campo requerido de contraseña. -->
                                        <label class="form-label fw-semibold" for="contrasenia">Contraseña <span class="text-danger">(*)</span></label>
                                        <!-- Captura la contraseña como campo protegido con autocompletado de alta. -->
                                        <input class="form-control" id="contrasenia" name="contrasenia" type="password" minlength="8" maxlength="72" autocomplete="new-password" required aria-describedby="error-contrasenia">
                                        <!-- Informa que el navegador requiere este campo. -->
                                        <div class="invalid-feedback" id="error-contrasenia">La contraseña debe tener al menos 8 caracteres y no superar 72 bytes.</div>
                                    <!-- Cierra la columna de contraseña. -->
                                    </div>
                                    <!-- Aloja el CURP obligatorio y limitado a 18 caracteres. -->
                                    <div class="col-12 col-md-6">
                                        <!-- Asocia el rótulo con el campo requerido de CURP. -->
                                        <label class="form-label fw-semibold" for="curp">CURP <span class="text-danger">(*)</span></label>
                                        <!-- Captura la clave de identidad con longitud establecida en el diccionario. -->
                                        <input class="form-control text-uppercase" id="curp" name="curp" type="text" minlength="18" maxlength="18" pattern="[A-Za-z][AaEeIiOoUuXx][A-Za-z]{2}[0-9]{6}[HhMm][A-Za-z]{2}[B-DF-HJ-NP-TV-Zb-df-hj-np-tv-z]{3}[A-Za-z0-9][0-9]" autocomplete="off" value="<?= $escaparRegistro((string) ($datosRegistroVista['curp'] ?? '')) ?>" required aria-describedby="error-curp">
                                        <!-- Muestra la ayuda de validación si falta el CURP. -->
                                        <div class="invalid-feedback" id="error-curp">La CURP debe tener exactamente 18 caracteres y un formato válido.</div>
                                    <!-- Cierra la columna del CURP. -->
                                    </div>
                                    <!-- Aloja el teléfono obligatorio y preserva ceros iniciales. -->
                                    <div class="col-12 col-md-6">
                                        <!-- Asocia el rótulo con el campo requerido de teléfono. -->
                                        <label class="form-label fw-semibold" for="telefono">Teléfono <span class="text-danger">(*)</span></label>
                                        <!-- Captura el teléfono como texto con la longitud disponible en la tabla. -->
                                        <input class="form-control" id="telefono" name="telefono" type="tel" minlength="10" maxlength="15" pattern="[0-9]{10,15}" inputmode="numeric" autocomplete="tel" value="<?= $escaparRegistro((string) ($datosRegistroVista['telefono'] ?? '')) ?>" required aria-describedby="error-telefono">
                                        <!-- Muestra la ayuda de validación si falta el teléfono. -->
                                        <div class="invalid-feedback" id="error-telefono">Ingresa de 10 a 15 dígitos, sin espacios ni signos.</div>
                                    <!-- Cierra la columna del teléfono. -->
                                    </div>
                                    <!-- Aloja el rol obligatorio con las opciones exactas del ENUM. -->
                                    <div class="col-12">
                                        <!-- Asocia el rótulo con la selección requerida de rol. -->
                                        <label class="form-label fw-semibold" for="rol">Rol <span class="text-danger">(*)</span></label>
                                        <!-- Permite elegir solo una opción definida por la arquitectura de datos. -->
                                        <select class="form-select" id="rol" name="rol" required data-selector-rol aria-describedby="error-rol">
                                            <!-- Solicita seleccionar un rol explícitamente. -->
                                            <option value="">Selecciona un rol</option>
                                            <!-- Recorre las alternativas válidas del ENUM. -->
                                            <?php foreach ($rolesRegistro as $valorRol => $etiquetaRol): ?>
                                                <!-- Muestra cada valor ENUM como opción escapada y restaura la selección previa. -->
                                                <option value="<?= $escaparRegistro($valorRol) ?>" <?= (($datosRegistroVista['rol'] ?? '') === $valorRol) ? 'selected' : '' ?>><?= $escaparRegistro($etiquetaRol) ?></option>
                                            <!-- Finaliza el recorrido de roles permitidos. -->
                                            <?php endforeach; ?>
                                        <!-- Cierra la lista de roles. -->
                                        </select>
                                        <!-- Muestra el mensaje nativo de validación si no se selecciona un rol. -->
                                        <div class="invalid-feedback" id="error-rol">Selecciona el rol del empleado.</div>
                                    <!-- Cierra la columna de rol. -->
                                    </div>
                                <!-- Cierra la cuadrícula de campos obligatorios. -->
                                </div>
                            <!-- Cierra la tarjeta de datos obligatorios. -->
                            </div>
                        <!-- Cierra la superficie de datos obligatorios. -->
                        </div>
                    <!-- Cierra la primera columna responsiva. -->
                    </section>
                    <!-- Reúne campos opcionales y permisos en la segunda columna responsiva. -->
                    <section class="col-12 col-xl-5" aria-labelledby="titulo-datos-opcionales">
                        <!-- Agrupa información complementaria en una tarjeta blanca. -->
                        <div class="tarjeta-registro card border-0 mb-4">
                            <!-- Añade el relleno interior de la tarjeta con clases Bootstrap. -->
                            <div class="card-body p-3 p-md-4">
                                <!-- Identifica la sección no obligatoria del formulario. -->
                                <h2 class="h5 fw-bold mb-1" id="titulo-datos-opcionales">Datos opcionales</h2>
                                <!-- Explica que los siguientes campos pueden omitirse. -->
                                <p class="small text-secondary mb-3">Completa estos datos solo cuando estén disponibles.</p>
                                <!-- Apila los campos opcionales en una cuadrícula adaptable. -->
                                <div class="row g-3">
                                    <!-- Captura el segundo nombre opcional. -->
                                    <div class="col-12">
                                        <!-- Asocia el rótulo del segundo nombre con su control. -->
                                        <label class="form-label" for="seg-nombre">Segundo nombre</label>
                                        <!-- Permite omitir el segundo nombre. -->
                                        <input class="form-control" id="seg-nombre" name="seg_nombre" type="text" maxlength="100" pattern="[\p{L}\p{M} .’'\-]*" autocomplete="additional-name" value="<?= $escaparRegistro((string) ($datosRegistroVista['seg_nombre'] ?? '')) ?>">
                                        <div class="invalid-feedback">Usa solo letras, espacios, apóstrofos, puntos o guiones.</div>
                                    <!-- Cierra el campo de segundo nombre. -->
                                    </div>
                                    <!-- Captura el apellido materno opcional. -->
                                    <div class="col-12">
                                        <!-- Asocia el rótulo de apellido materno con su control. -->
                                        <label class="form-label" for="apellido-m">Apellido materno</label>
                                        <!-- Permite omitir el apellido materno. -->
                                        <input class="form-control" id="apellido-m" name="apellido_m" type="text" maxlength="100" pattern="[\p{L}\p{M} .’'\-]*" autocomplete="additional-name" value="<?= $escaparRegistro((string) ($datosRegistroVista['apellido_m'] ?? '')) ?>">
                                        <div class="invalid-feedback">Usa solo letras, espacios, apóstrofos, puntos o guiones.</div>
                                    <!-- Cierra el campo de apellido materno. -->
                                    </div>
                                    <!-- Permite elegir un valor de género conocido sin aceptar texto arbitrario. -->
                                    <div class="col-12">
                                        <!-- Asocia el rótulo de género con el selector opcional. -->
                                        <label class="form-label" for="genero">Género</label>
                                        <!-- Restringe la selección a opciones compartidas con el perfil. -->
                                        <select class="form-select" id="genero" name="genero">
                                            <option value="">Selecciona una opción</option>
                                            <option value="Masculino" <?= (($datosRegistroVista['genero'] ?? '') === 'Masculino') ? 'selected' : '' ?>>Masculino</option>
                                            <option value="Femenino" <?= (($datosRegistroVista['genero'] ?? '') === 'Femenino') ? 'selected' : '' ?>>Femenino</option>
                                            <option value="Otro" <?= (($datosRegistroVista['genero'] ?? '') === 'Otro') ? 'selected' : '' ?>>Otro</option>
                                        </select>
                                        <!-- Cierra el campo opcional de género. -->
                                    </div>
                                <!-- Cierra la cuadrícula de datos opcionales. -->
                                </div>
                            <!-- Cierra la tarjeta de datos opcionales. -->
                            </div>
                        <!-- Cierra la superficie de datos opcionales. -->
                        </div>
                        <!-- Presenta los permisos estáticos disponibles para el rol seleccionado. -->
                        <div class="tarjeta-registro card border-0">
                            <!-- Añade el espaciado interior a la tarjeta de permisos. -->
                            <div class="card-body p-3 p-md-4">
                                <!-- Identifica la grilla de capacidades del empleado. -->
                                <h2 class="h5 fw-bold mb-1">Permisos del rol</h2>
                                <!-- Explica que las opciones visibles dependen del rol elegido. -->
                                <p class="small text-secondary mb-3" data-ayuda-permisos>Selecciona un rol para activar sus permisos disponibles.</p>
                                <!-- Organiza las casillas de permisos en una grilla de dos columnas. -->
                                <div class="row g-2 grilla-permisos">
                                    <!-- Recorre los permisos que puede manejar el controlador de autenticación. -->
                                    <?php foreach ($opcionesPermisos as $clavePermiso => $definicionPermiso): ?>
                                        <!-- Agrupa cada casilla en una tarjeta compacta y adaptable. -->
                                        <div class="col-12 col-md-6">
                                            <!-- Dibuja el contenedor de checkbox con los roles autorizados como metadato para JS externo. -->
                                            <label class="permiso-opcion form-check d-flex align-items-start gap-2 rounded-3 p-2">
                                                <!-- Captura la selección individual sin eventos JavaScript en línea. -->
                                                <input class="form-check-input mt-1" type="checkbox" name="permisos[]" value="<?= $escaparRegistro($clavePermiso) ?>" data-permiso-roles="<?= $escaparRegistro(implode(',', $definicionPermiso['roles'])) ?>" <?= in_array($clavePermiso, $permisosRegistroVista, true) ? 'checked' : '' ?> disabled>
                                                <!-- Presenta la etiqueta segura del permiso. -->
                                                <span class="form-check-label small"><?= $escaparRegistro($definicionPermiso['etiqueta']) ?></span>
                                            <!-- Cierra la tarjeta individual del permiso. -->
                                            </label>
                                        <!-- Cierra la columna del permiso. -->
                                        </div>
                                    <!-- Finaliza la grilla de permisos disponibles. -->
                                    <?php endforeach; ?>
                                <!-- Cierra la grilla de permisos. -->
                                </div>
                                <!-- Aclara el límite del esquema actual para evitar afirmar que las casillas quedan persistidas. -->
                                <p class="aviso-permisos small text-secondary mt-3 mb-0">La tabla empleado no almacena permisos individuales; el acceso efectivo se asigna según el rol.</p>
                            <!-- Cierra la tarjeta de permisos. -->
                            </div>
                        <!-- Cierra la superficie de permisos. -->
                        </div>
                    <!-- Cierra la segunda columna responsiva. -->
                    </section>
                    <!-- Agrupa las acciones de envío y retorno bajo las dos columnas. -->
                    <div class="col-12">
                        <!-- Alinea los botones con el borde final del formulario en pantallas grandes. -->
                        <div class="d-flex flex-column flex-sm-row justify-content-end gap-2">
                            <!-- Permite regresar a la lista sin enviar los datos del formulario. -->
                            <a class="btn btn-outline-secondary" href="index.php?c=empleado&amp;a=lista">Cancelar</a>
                            <!-- Envía los campos para validar duplicados y guardar el empleado. -->
                            <button class="btn btn-primary px-4" type="submit">Registrar empleado</button>
                        <!-- Cierra la fila de acciones del formulario. -->
                        </div>
                    <!-- Cierra la columna de acciones. -->
                    </div>
                <!-- Cierra la cuadrícula de las dos columnas. -->
                </div>
            <!-- Cierra el formulario de registro. -->
            </form>
            <!-- Presenta los registros existentes de forma compacta en la parte inferior de la pantalla. -->
            <section class="tarjeta-registro tarjeta-usuarios-compacta card border-0 mt-4" aria-labelledby="titulo-usuarios-compactos">
                <!-- Añade separación entre el título y la tabla compacta. -->
                <div class="card-body p-3 p-md-4">
                    <!-- Alinea el título del listado y el contador de empleados. -->
                    <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                        <!-- Identifica el listado de personas registradas. -->
                        <h2 class="h5 fw-bold mb-0" id="titulo-usuarios-compactos">Usuarios registrados</h2>
                        <!-- Muestra el total suministrado por el modelo PDO. -->
                        <span class="badge etiqueta-total rounded-pill"><?= count($usuariosRegistroVista) ?> usuarios</span>
                    <!-- Cierra el encabezado del listado compacto. -->
                    </div>
                    <!-- Permite recorrer horizontalmente la tabla compacta en pantallas estrechas. -->
                    <div class="table-responsive">
                        <!-- Muestra una versión resumida del directorio de empleados. -->
                        <table class="table tabla-empleados tabla-compacta align-middle mb-0">
                            <!-- Define las columnas resumidas del directorio. -->
                            <thead>
                                <!-- Encabeza la lista compacta de usuarios. -->
                                <tr>
                                    <!-- Encabeza nombre completo. -->
                                    <th scope="col">Nombre completo</th>
                                    <!-- Encabeza usuario de acceso. -->
                                    <th scope="col">Usuario</th>
                                    <!-- Encabeza rol asignado. -->
                                    <th scope="col">Rol</th>
                                <!-- Cierra la fila de títulos compactos. -->
                                </tr>
                            <!-- Cierra la cabecera de la tabla compacta. -->
                            </thead>
                            <!-- Muestra los empleados ya almacenados o un estado vacío. -->
                            <tbody>
                                <!-- Recorre la colección resumida de empleados entregada por el controlador. -->
                                <?php foreach ($usuariosRegistroVista as $usuarioCompacto): ?>
                                    <!-- Recupera el rol de la fila para traducirlo a su etiqueta visible. -->
                                    <?php $rolCompacto = (string) ($usuarioCompacto['rol'] ?? ''); ?>
                                    <!-- Presenta cada usuario sin incluir datos sensibles. -->
                                    <tr>
                                        <!-- Muestra el nombre completo escapado del empleado. -->
                                        <td class="fw-semibold"><?= $escaparRegistro((string) ($usuarioCompacto['nombre_completo'] ?? '')) ?></td>
                                        <!-- Muestra el identificador de acceso escapado. -->
                                        <td><span class="texto-usuario">@<?= $escaparRegistro((string) ($usuarioCompacto['usuario'] ?? '')) ?></span></td>
                                        <!-- Muestra el rol legible dentro de una insignia. -->
                                        <td><span class="badge etiqueta-rol rounded-pill"><?= $escaparRegistro($etiquetasRoles[$rolCompacto] ?? $rolCompacto) ?></span></td>
                                    <!-- Cierra la fila compacta de empleado. -->
                                    </tr>
                                <!-- Finaliza el recorrido de usuarios compactos. -->
                                <?php endforeach; ?>
                                <!-- Presenta una fila de información cuando aún no hay cuentas registradas. -->
                                <?php if ($usuariosRegistroVista === []): ?>
                                    <!-- Explica que no hay usuarios anteriores para listar. -->
                                    <tr><td class="text-center text-secondary py-4" colspan="3">Aún no hay usuarios registrados.</td></tr>
                                <!-- Finaliza el estado vacío del listado compacto. -->
                                <?php endif; ?>
                            <!-- Cierra el cuerpo de la tabla compacta. -->
                            </tbody>
                        <!-- Cierra la tabla compacta. -->
                        </table>
                    <!-- Cierra el contenedor responsivo. -->
                    </div>
                <!-- Cierra el contenido de la tarjeta compacta. -->
                </div>
            <!-- Cierra la tarjeta de usuarios registrados. -->
            </section>
        <!-- Cierra el contenido principal. -->
        </main>
    <!-- Cierra la composición administrativa. -->
    </div>
<!-- Cierra el cuerpo del documento. -->
</body>
<!-- Finaliza el documento HTML. -->
</html>
