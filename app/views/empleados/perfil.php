<?php
// Escapa cualquier valor dinámico para prevenir ataques XSS al renderizar en el HTML.
$escaparHtml = static fn (string $valor): string => htmlspecialchars($valor, ENT_QUOTES, 'UTF-8'); // Aplica codificación segura UTF-8.
// Garantiza que los datos del perfil existan como un arreglo estructurado.
$datosPerfilVista = isset($datosEmpleado) && is_array($datosEmpleado) ? $datosEmpleado : []; // Evita errores por variables indefinidas.
// Asegura que los datos de la sesión existan para mostrar los metadatos de usuario.
$sesionVista = isset($usuarioSesion) && is_array($usuarioSesion) ? $usuarioSesion : []; // Mantiene la consistencia de sesión.
// Normaliza el mensaje enviado por el controlador o usa una cadena vacía.
$mensajeVista = isset($mensaje) ? (string) $mensaje : ''; // Prepara el texto de la alerta.
// Normaliza la clase de alerta de Bootstrap permitiendo únicamente variantes seguras.
$tipoAlertaVista = isset($tipoAlerta) && in_array($tipoAlerta, ['success', 'danger', 'warning', 'info'], true) ? $tipoAlerta : 'danger'; // Aplica estilo estándar.
// Define el diccionario de etiquetas legibles para los roles del ENUM del sistema.
$etiquetasRoles = [ // Mantiene sincronizadas las etiquetas de visualización por rol.
    'ADMINISTRADOR' => 'Administrador', // Rol con privilegios totales.
    'VENTAS' => 'Ventas', // Rol para operaciones de venta.
    'ALMACEN' => 'Almacén', // Rol para inventarios y productos.
    'DISTRIBUCION' => 'Distribución', // Rol para logística.
    'REPARTIDOR' => 'Repartidor', // Rol para entregas directas.
]; // Finaliza el mapeo de nombres de roles.
// Obtiene el rol técnico de la base de datos para la cuenta activa.
$rolTecnico = (string) ($datosPerfilVista['rol'] ?? ($sesionVista['rol'] ?? '')); // Lee el rol almacenado.
// Traduce el rol a su etiqueta amigable o conserva el valor seguro si es desconocido.
$rolVisible = $etiquetasRoles[$rolTecnico] ?? ($rolTecnico !== '' ? $rolTecnico : 'Empleado'); // Muestra la denominación en español.
// Recupera el nombre de pila para componer el nombre y las iniciales.
$nombreEmpleado = (string) ($datosPerfilVista['nombre'] ?? ''); // Lee el nombre capturado.
// Recupera el apellido paterno para componer las iniciales del avatar.
$apellidoPEmpleado = (string) ($datosPerfilVista['apellido_p'] ?? ''); // Lee el apellido paterno.
// Genera las dos iniciales del empleado en mayúsculas para el avatar visual.
$inicialesAvatar = mb_strtoupper(mb_substr($nombreEmpleado !== '' ? $nombreEmpleado : 'U', 0, 1) . mb_substr($apellidoPEmpleado !== '' ? $apellidoPEmpleado : 'S', 0, 1)); // Produce iniciales tipo AR o DP.
// Compone el nombre completo para la tarjeta de resumen superior.
$nombreCompleto = trim(implode(' ', array_filter([$nombreEmpleado, (string) ($datosPerfilVista['seg_nombre'] ?? ''), $apellidoPEmpleado, (string) ($datosPerfilVista['apellido_m'] ?? '')]))); // Une los nombres no vacíos.
// Determina la ruta del botón cancelar de acuerdo con el rol de la sesión.
$urlCancelar = ($sesionVista['rol'] ?? '') === 'ADMINISTRADOR' ? 'index.php?c=empleado&amp;a=lista' : 'index.php'; // Redirige a lista para admin o al inicio para otros.
?>
<!-- Declara el documento HTML5 para la interfaz del perfil de usuario. -->
<!DOCTYPE html>
<!-- Configura el idioma español para tecnologías de asistencia y navegadores. -->
<html lang="es">
<!-- Inicia la cabecera del documento con metadatos y hojas de estilo. -->
<head>
    <!-- Define la codificación UTF-8 para nombres y caracteres con acento. -->
    <meta charset="UTF-8">
    <!-- Habilita el diseño responsivo en pantallas móviles y de escritorio. -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Describe el propósito de la página de gestión de información personal. -->
    <meta name="description" content="Consulta y actualiza tu información personal en el sistema de El Güero.">
    <!-- Establece el color de tema para la barra de estado en navegadores móviles. -->
    <meta name="theme-color" content="#fff5f8">
    <!-- Define el título de la pestaña del navegador. -->
    <title>Mi Perfil | El Güero</title>
    <!-- Carga Bootstrap 5.3.3 desde CDN oficial con comprobación de integridad. -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Carga los estilos generales del módulo de empleados y menú lateral. -->
    <link href="assets/css/empleados.css" rel="stylesheet">
    <!-- Carga los estilos particulares de la vista de perfil sin estilos en línea. -->
    <link href="assets/css/perfil.css" rel="stylesheet">
    <link href="assets/css/paleta-rosa.css" rel="stylesheet">
    <!-- Carga el script de validación externa con el atributo defer para ejecución diferida. -->
    <script src="assets/js/perfil.js" defer></script>
    <script src="assets/js/navegacion.js" defer></script>
<!-- Cierra la cabecera del documento HTML. -->
</head>
<!-- Inicia el cuerpo de la página con la clase base del módulo. -->
<body class="pagina-empleados">
    <!-- Organiza la barra de navegación lateral y el contenido central. -->
    <div class="contenedor-empleados d-flex min-vh-100">
        <!-- Renderiza la barra lateral fija en pantallas grandes. -->
        <aside class="barra-empleados d-flex flex-column p-3" aria-label="Navegación del sistema">
            <!-- Presenta la identidad de marca de la tocinería como enlace. -->
            <a class="marca-empleados d-flex align-items-center gap-3 text-decoration-none" href="index.php" aria-label="El Güero, inicio">
                <!-- Usa el logotipo oficial en el encabezado uniforme del menú. -->
                <img class="logo-sistema" src="assets/logoPIG.png" alt="Logotipo de El Güero">
                <!-- Muestra el distintivo circular con la letra G de la marca. -->
                <!-- Agrupa el nombre comercial y descriptor en bloque vertical. -->
                <span class="d-flex flex-column">
                    <!-- Nombre visible de la tocinería. -->
                    <span class="fw-bold">El Güero</span>
                    <!-- Descriptor secundario del sistema. -->
                    <span class="small text-secondary">Tocinería · Sistema</span>
                <!-- Cierra el bloque textual de la marca. -->
                </span>
            <!-- Cierra el enlace de identidad de marca. -->
            </a>
            <a class="enlace-inicio-navegacion" href="index.php" aria-label="Ir a la pantalla principal">⌂ <span>Inicio</span></a>
            <!-- Agrupa las opciones de navegación accesibles según el rol. -->
            <nav class="mt-4" aria-label="Opciones de cuenta y navegación">
                <?php if (isset($sesionVista['rol']) && in_array($sesionVista['rol'], ['ADMINISTRADOR', 'ALMACEN'], true)): ?>
                    <!-- Identifica las opciones de productos e inventario accesibles para estos roles. -->
                    <button class="boton-categoria-navegacion titulo-navegacion small fw-semibold text-uppercase" type="button" data-menu-desplegable aria-expanded="true" aria-controls="menu-catalogo">Catálogo e inventario</button>
                    <div class="grupo-navegacion-contenido is-open" id="menu-catalogo">
                    <!-- Enlaza al registro unificado de producto y existencia inicial. -->
                    <a class="enlace-navegacion d-flex align-items-center gap-2 rounded-3 px-3 py-2 text-decoration-none" href="index.php?c=producto&amp;a=nuevo">
                        <!-- Icono representativo del alta de producto. -->
                        <!-- Nombre del acceso al alta de producto. -->
                        <span>Registrar Producto</span>
                    <!-- Cierra el enlace al alta de producto. -->
                    </a>
                    <!-- Enlaza al catálogo de productos y la gestión de existencias. -->
                    <a class="enlace-navegacion d-flex align-items-center gap-2 rounded-3 px-3 py-2 text-decoration-none" href="index.php?c=producto&amp;a=editar">
                        <!-- Icono representativo de catálogo e inventario. -->
                        <!-- Nombre del acceso al catálogo. -->
                        <span>Productos e Inventario</span>
                    <!-- Cierra el enlace al catálogo e inventario. -->
                    </a>
                    <!-- Separa las opciones del módulo de las opciones de cuenta. -->
                    </div>
                    <button class="boton-categoria-navegacion titulo-navegacion small fw-semibold text-uppercase" type="button" data-menu-desplegable aria-expanded="true" aria-controls="menu-seguridad">Seguridad y acceso</button>
                <?php else: ?>
                <!-- Muestra el título de categoría para la navegación. -->
                <button class="boton-categoria-navegacion titulo-navegacion small fw-semibold text-uppercase" type="button" data-menu-desplegable aria-expanded="true" aria-controls="menu-seguridad">Seguridad y acceso</button>
                <?php endif; ?>
                <div class="grupo-navegacion-contenido is-open" id="menu-seguridad">
                <!-- Renderiza accesos administrativos únicamente para usuarios con rol ADMINISTRADOR. -->
                <?php if (isset($sesionVista['rol']) && $sesionVista['rol'] === 'ADMINISTRADOR'): ?>
                    <!-- Enlace directo al directorio de usuarios. -->
                    <a class="enlace-navegacion d-flex align-items-center gap-2 rounded-3 px-3 py-2 text-decoration-none" href="index.php?c=empleado&amp;a=lista">
                        <!-- Icono o viñeta de la lista. -->
                        <!-- Nombre del enlace a la lista. -->
                        <span>Lista de Usuarios</span>
                    <!-- Cierra el enlace a lista. -->
                    </a>
                    <!-- Enlace directo al formulario de alta de usuarios. -->
                    <a class="enlace-navegacion d-flex align-items-center gap-2 rounded-3 px-3 py-2 text-decoration-none" href="index.php?c=empleado&amp;a=registro">
                        <!-- Signo de adición para el formulario de alta. -->
                        <!-- Nombre del enlace de alta. -->
                        <span>Agregar Usuario</span>
                    <!-- Cierra el enlace de registro. -->
                    </a>
                <!-- Finaliza la restricción de opciones de administración. -->
                <?php endif; ?>
                <!-- Enlace al perfil de usuario activo marcado con la clase activo. -->
                <a class="enlace-navegacion activo d-flex align-items-center gap-2 rounded-3 px-3 py-2 text-decoration-none" href="index.php?c=empleado&amp;a=perfil" aria-current="page">
                    <!-- Icono representativo del perfil de usuario. -->
                    <!-- Título de la opción de perfil. -->
                    <span>Mi Perfil</span>
                <!-- Cierra el enlace de perfil. -->
                </a>
                </div>
            <!-- Cierra el contenedor de navegación. -->
            </nav>
            <!-- Muestra la información de la sesión actual al final del menú lateral. -->
            <div class="sesion-empleado d-flex align-items-center justify-content-between mt-auto pt-3 border-top">
                <!-- Agrupa el avatar y nombres de la sesión. -->
                <div class="d-flex align-items-center gap-2 overflow-hidden">
                    <!-- Avatar con las iniciales calculadas del usuario. -->
                    <!-- Nombre de usuario y rol en dos líneas compactas. -->
                    <span class="d-flex flex-column overflow-hidden">
                        <!-- Nombre de usuario de acceso. -->
                        <span class="small fw-semibold text-truncate"><?= $escaparHtml((string) ($sesionVista['usuario'] ?? 'Usuario')) ?></span>
                        <!-- Rol legible asignado. -->
                        <span class="small text-secondary"><?= $escaparHtml($rolVisible) ?></span>
                    <!-- Cierra los textos del usuario. -->
                    </span>
                <!-- Cierra la agrupación de identidad. -->
                </div>
                <!-- Botón de salida para cerrar la sesión activa de forma segura. -->
                <a class="btn btn-sm btn-outline-secondary flex-shrink-0 ms-2" href="index.php?c=auth&amp;a=logout">Cerrar sesión</a>
            <!-- Cierra el pie de la barra lateral. -->
            </div>
        <!-- Cierra la barra lateral. -->
        </aside>
        <!-- Inicia el contenido principal de la pantalla de perfil de usuario. -->
        <main class="contenido-empleados flex-grow-1 p-3 p-md-4 p-xl-5">
            <!-- Encabezado con miga de pan, título y descripción de la vista. -->
            <header class="encabezado-perfil mb-4">
                <!-- Ruta de navegación accesible con miga de pan. -->
                <p class="miga-pan text-secondary small mb-2">Seguridad y acceso / Mi perfil</p>
                <!-- Título principal de la pantalla de perfil. -->
                <h1 class="h2 fw-bold mb-2">Mi Perfil</h1>
                <!-- Subtítulo explicativo del propósito de la interfaz. -->
                <p class="text-secondary mb-0">Consulta y actualiza tu información personal en el sistema.</p>
            <!-- Cierra el encabezado de página. -->
            </header>
            <!-- Renderiza la alerta de notificación cuando existe un mensaje del controlador. -->
            <?php if ($mensajeVista !== ''): ?>
                <!-- Contenedor de alerta con componente estándar de Bootstrap 5.3. -->
                <div class="alert alert-<?= $escaparHtml($tipoAlertaVista) ?> d-flex align-items-center gap-2 mb-4" role="alert">
                    <!-- Icono o glifo indicativo según el tipo de resultado. -->
                    <span class="fw-bold"><?= $tipoAlertaVista === 'success' ? 'Correcto:' : 'Aviso:' ?></span>
                    <!-- Mensaje emitido por el controlador (Datos actualizados, Error: datos faltantes o Error de registro). -->
                    <span><?= $escaparHtml($mensajeVista) ?></span>
                <!-- Cierra el contenedor de alerta. -->
                </div>
            <!-- Finaliza la comprobación de alerta visible. -->
            <?php endif; ?>
            <!-- Tarjeta superior de presentación visual del empleado emulando el marco Figma. -->
            <section class="tarjeta-perfil card border-0 mb-4" aria-labelledby="titulo-resumen-perfil">
                <!-- Relleno interior de la tarjeta de presentación con clases Bootstrap. -->
                <div class="card-body p-3 p-md-4">
                    <!-- Distribuye el avatar grande y los metadatos principales del empleado. -->
                    <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-3">
                        <!-- Avatar circular de gran formato con las iniciales del empleado. -->
                        <!-- Información textual y etiquetas de rol y estado. -->
                        <div class="d-flex flex-column">
                            <!-- Nombre completo compuesto del empleado con énfasis tipográfico. -->
                            <h2 class="h4 fw-bold mb-1" id="titulo-resumen-perfil"><?= $escaparHtml($nombreCompleto !== '' ? $nombreCompleto : 'Perfil de Empleado') ?></h2>
                            <!-- Nombre de usuario con prefijo arroba para identificación visual. -->
                            <p class="text-secondary small mb-2"><span class="texto-usuario fw-semibold">@<?= $escaparHtml((string) ($datosPerfilVista['usuario'] ?? '')) ?></span></p>
                            <!-- Agrupa las insignias de rol y cuenta activa en fila adaptable. -->
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <!-- Insignia del rol asignado al empleado. -->
                                <span class="badge etiqueta-rol rounded-pill"><?= $escaparHtml($rolVisible) ?></span>
                                <!-- Insignia de estado de cuenta activa. -->
                                <span class="badge insignia-estado-activo rounded-pill">Cuenta Activa</span>
                            <!-- Cierra el grupo de insignias. -->
                            </div>
                        <!-- Cierra el bloque de información textual. -->
                        </div>
                    <!-- Cierra la fila del resumen de perfil. -->
                    </div>
                <!-- Cierra el cuerpo de la tarjeta de presentación. -->
                </div>
            <!-- Cierra la sección superior de presentación. -->
            </section>
            <!-- Inicia el formulario de edición de perfil con destino al controlador por POST. -->
            <form action="index.php?c=empleado&amp;a=perfil" method="post" data-formulario-perfil novalidate>
                <!-- Cuadrícula responsiva que divide la información editable y los datos protegidos. -->
                <div class="row g-4">
                    <!-- Columna izquierda: Información personal modificable por el empleado. -->
                    <div class="col-12 col-xl-7">
                        <!-- Tarjeta que contiene los campos editables del perfil. -->
                        <div class="tarjeta-perfil card border-0 h-100">
                            <!-- Contenedor interior con espaciado estándar. -->
                            <div class="card-body p-3 p-md-4">
                                <!-- Título de la sección de información personal. -->
                                <h3 class="h5 fw-bold mb-1">Información Personal</h3>
                                <!-- Instrucción que destaca la obligatoriedad de los campos marcados con asterisco. -->
                                <p class="small text-secondary mb-3">Los campos marcados con <span class="text-danger fw-bold">(*)</span> son obligatorios.</p>
                                <!-- Cuadrícula de dos columnas para los controles de datos personales. -->
                                <div class="row g-3">
                                    <!-- Campo de nombre requerido. -->
                                    <div class="col-12 col-md-6">
                                        <!-- Etiqueta del nombre con asterisco rojo de obligatoriedad. -->
                                        <label class="form-label fw-semibold" for="nombre">Nombre <span class="text-danger">(*)</span></label>
                                        <!-- Control de entrada para el nombre de pila. -->
                                        <input class="form-control" id="nombre" name="nombre" type="text" maxlength="100" pattern="[\p{L}\p{M} .’'\-]+" autocomplete="given-name" value="<?= $escaparHtml((string) ($datosPerfilVista['nombre'] ?? '')) ?>" required aria-describedby="error-nombre">
                                        <!-- Mensaje de retroalimentación nativo cuando el nombre está vacío. -->
                                        <div class="invalid-feedback" id="error-nombre">Ingresa un nombre con letras y separadores habituales, sin números.</div>
                                    <!-- Cierra la columna de nombre. -->
                                    </div>
                                    <!-- Campo opcional de segundo nombre. -->
                                    <div class="col-12 col-md-6">
                                        <!-- Etiqueta del segundo nombre sin asterisco. -->
                                        <label class="form-label" for="seg-nombre">Segundo nombre</label>
                                        <!-- Control de entrada para el segundo nombre. -->
                                        <input class="form-control" id="seg-nombre" name="seg_nombre" type="text" maxlength="100" pattern="[\p{L}\p{M} .’'\-]*" autocomplete="additional-name" value="<?= $escaparHtml((string) ($datosPerfilVista['seg_nombre'] ?? '')) ?>">
                                        <div class="invalid-feedback">Usa solo letras, espacios, apóstrofos, puntos o guiones.</div>
                                    <!-- Cierra la columna de segundo nombre. -->
                                    </div>
                                    <!-- Campo de apellido paterno requerido. -->
                                    <div class="col-12 col-md-6">
                                        <!-- Etiqueta del apellido paterno con asterisco rojo de obligatoriedad. -->
                                        <label class="form-label fw-semibold" for="apellido-p">Apellido paterno <span class="text-danger">(*)</span></label>
                                        <!-- Control de entrada para el apellido paterno. -->
                                        <input class="form-control" id="apellido-p" name="apellido_p" type="text" maxlength="100" pattern="[\p{L}\p{M} .’'\-]+" autocomplete="family-name" value="<?= $escaparHtml((string) ($datosPerfilVista['apellido_p'] ?? '')) ?>" required aria-describedby="error-apellido-p">
                                        <!-- Mensaje de retroalimentación si falta el apellido paterno. -->
                                        <div class="invalid-feedback" id="error-apellido-p">Ingresa un apellido con letras y separadores habituales, sin números.</div>
                                    <!-- Cierra la columna de apellido paterno. -->
                                    </div>
                                    <!-- Campo opcional de apellido materno. -->
                                    <div class="col-12 col-md-6">
                                        <!-- Etiqueta del apellido materno sin asterisco. -->
                                        <label class="form-label" for="apellido-m">Apellido materno</label>
                                        <!-- Control de entrada para el apellido materno. -->
                                        <input class="form-control" id="apellido-m" name="apellido_m" type="text" maxlength="100" pattern="[\p{L}\p{M} .’'\-]*" autocomplete="family-name" value="<?= $escaparHtml((string) ($datosPerfilVista['apellido_m'] ?? '')) ?>">
                                        <div class="invalid-feedback">Usa solo letras, espacios, apóstrofos, puntos o guiones.</div>
                                    <!-- Cierra la columna de apellido materno. -->
                                    </div>
                                    <!-- Campo de teléfono requerido para contacto. -->
                                    <div class="col-12 col-md-6">
                                        <!-- Etiqueta del teléfono con asterisco rojo de obligatoriedad. -->
                                        <label class="form-label fw-semibold" for="telefono">Teléfono <span class="text-danger">(*)</span></label>
                                        <!-- Control de entrada para el número telefónico de contacto. -->
                                        <input class="form-control" id="telefono" name="telefono" type="tel" minlength="10" maxlength="15" pattern="[0-9]{10,15}" inputmode="numeric" autocomplete="tel" value="<?= $escaparHtml((string) ($datosPerfilVista['telefono'] ?? '')) ?>" required aria-describedby="error-telefono">
                                        <!-- Mensaje de retroalimentación si falta el teléfono. -->
                                        <div class="invalid-feedback" id="error-telefono">Ingresa de 10 a 15 dígitos, sin espacios ni signos.</div>
                                    <!-- Cierra la columna de teléfono. -->
                                    </div>
                                    <!-- Campo de género opcional. -->
                                    <div class="col-12 col-md-6">
                                        <!-- Etiqueta para la selección de género. -->
                                        <label class="form-label" for="genero">Género</label>
                                        <!-- Selector desplegable de opciones de género. -->
                                        <select class="form-select" id="genero" name="genero">
                                            <!-- Opción vacía predeterminada. -->
                                            <option value="">Selecciona una opción</option>
                                            <!-- Opción de género masculino. -->
                                            <option value="Masculino" <?= (($datosPerfilVista['genero'] ?? '') === 'Masculino') ? 'selected' : '' ?>>Masculino</option>
                                            <!-- Opción de género femenino. -->
                                            <option value="Femenino" <?= (($datosPerfilVista['genero'] ?? '') === 'Femenino') ? 'selected' : '' ?>>Femenino</option>
                                            <!-- Opción de género no especificado u otro. -->
                                            <option value="Otro" <?= (($datosPerfilVista['genero'] ?? '') === 'Otro') ? 'selected' : '' ?>>Otro</option>
                                        <!-- Cierra el selector de género. -->
                                        </select>
                                    <!-- Cierra la columna de género. -->
                                    </div>
                                <!-- Cierra la fila de campos editables. -->
                                </div>
                            <!-- Cierra el cuerpo de la tarjeta de datos personales. -->
                            </div>
                        <!-- Cierra la tarjeta de información personal. -->
                        </div>
                    <!-- Cierra la columna izquierda. -->
                    </div>
                    <!-- Columna derecha: Datos protegidos estrictamente en solo lectura (readonly/disabled). -->
                    <div class="col-12 col-xl-5">
                        <!-- Tarjeta que contiene los campos bloqueados de la cuenta. -->
                        <div class="tarjeta-perfil card border-0 h-100">
                            <!-- Contenedor interior con espaciado estándar. -->
                            <div class="card-body p-3 p-md-4 d-flex flex-column">
                                <!-- Título de la sección de datos protegidos de la cuenta. -->
                                <h3 class="h5 fw-bold mb-1">Datos de la Cuenta</h3>
                                <!-- Explicación sobre la protección de los campos de solo lectura. -->
                                <p class="small text-secondary mb-3">Información de identidad administrativa protegida contra cambios.</p>
                                <!-- Apila verticalmente los controles de solo lectura. -->
                                <div class="row g-3">
                                    <!-- Campo de nombre de usuario en solo lectura estricta. -->
                                    <div class="col-12">
                                        <!-- Etiqueta con indicador de candado para identificar campo bloqueado. -->
                                        <label class="form-label text-secondary" for="usuario">Usuario (solo lectura)</label>
                                        <!-- Control de entrada renderizado como readonly y disabled para impedir alteración. -->
                                        <input class="form-control campo-solo-lectura" id="usuario" name="usuario" type="text" value="<?= $escaparHtml((string) ($datosPerfilVista['usuario'] ?? '')) ?>" readonly disabled aria-label="Usuario no modificable">
                                    <!-- Cierra el campo de usuario. -->
                                    </div>
                                    <!-- Campo de CURP en solo lectura estricta. -->
                                    <div class="col-12">
                                        <!-- Etiqueta de CURP con indicador de candado. -->
                                        <label class="form-label text-secondary" for="curp">CURP (solo lectura)</label>
                                        <!-- Control de entrada renderizado como readonly y disabled con texto en mayúsculas. -->
                                        <input class="form-control campo-solo-lectura text-uppercase" id="curp" name="curp" type="text" value="<?= $escaparHtml((string) ($datosPerfilVista['curp'] ?? '')) ?>" readonly disabled aria-label="CURP no modificable">
                                    <!-- Cierra el campo de CURP. -->
                                    </div>
                                    <!-- Campo de Rol asignado en solo lectura estricta. -->
                                    <div class="col-12">
                                        <!-- Etiqueta de Rol con indicador de candado. -->
                                        <label class="form-label text-secondary" for="rol">Rol asignado (solo lectura)</label>
                                        <!-- Control de entrada renderizado como readonly y disabled con el rol visible. -->
                                        <input class="form-control campo-solo-lectura" id="rol" name="rol" type="text" value="<?= $escaparHtml($rolVisible) ?>" readonly disabled aria-label="Rol no modificable">
                                    <!-- Cierra el campo de rol. -->
                                    </div>
                                <!-- Cierra la fila de campos de cuenta. -->
                                </div>
                                <!-- Bloque informativo de seguridad para explicar la restricción de los tres campos. -->
                                <div class="aviso-seguridad-perfil small text-secondary mt-auto pt-3">
                                    <!-- Icono de protección y texto explicativo de la política de identidad. -->
                                    <span class="fw-semibold text-dark">Restricción de identidad:</span> Los campos de usuario, CURP y rol solo pueden ser gestionados por un administrador para preservar la integridad del sistema.
                                <!-- Cierra el bloque informativo de seguridad. -->
                                </div>
                            <!-- Cierra el cuerpo de la tarjeta de cuenta. -->
                            </div>
                        <!-- Cierra la tarjeta de datos de cuenta. -->
                        </div>
                    <!-- Cierra la columna derecha. -->
                    </div>
                    <!-- Fila inferior con botones de acción para Guardar y Cancelar. -->
                    <div class="col-12">
                        <!-- Alinea los botones al final del contenedor en pantallas amplias. -->
                        <div class="d-flex flex-column flex-sm-row justify-content-end gap-2 pt-2">
                            <!-- Enlace que permite cancelar la edición y retornar a la vista anterior. -->
                            <a class="btn btn-outline-secondary px-4" href="<?= $escaparHtml($urlCancelar) ?>">Cancelar</a>
                            <!-- Botón de envío del formulario para persistir los cambios. -->
                            <button class="btn btn-primary px-4" type="submit">Guardar</button>
                        <!-- Cierra la alineación de botones. -->
                        </div>
                    <!-- Cierra la columna de botones. -->
                    </div>
                <!-- Cierra la cuadrícula de campos y acciones. -->
                </div>
            <!-- Cierra el formulario de perfil. -->
            </form>
        <!-- Cierra el contenedor de contenido principal. -->
        </main>
    <!-- Cierra la distribución principal. -->
    </div>
<!-- Cierra el cuerpo de la página. -->
</body>
<!-- Finaliza el documento HTML5. -->
</html>
