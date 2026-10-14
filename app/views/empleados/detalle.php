<?php
// Escapa los datos de la cuenta para que se representen como texto y no como HTML.
$escaparHtml = static fn (string $valor): string => htmlspecialchars($valor, ENT_QUOTES, 'UTF-8'); // Aplica codificación segura UTF-8.
// Define nombres comprensibles para los roles admitidos por el sistema.
$etiquetasRoles = ['ADMINISTRADOR' => 'Administrador', 'VENTAS' => 'Ventas', 'ALMACEN' => 'Almacén', 'DISTRIBUCION' => 'Distribución', 'REPARTIDOR' => 'Repartidor']; // Traduce los valores del ENUM.
// Obtiene el rol registrado para el usuario consultado.
$rolTecnico = (string) ($empleadoDetalle['rol'] ?? ''); // Normaliza el valor para la etiqueta.
// Presenta el nombre traducido del rol o el valor escapado si no está catalogado.
$rolVisible = $etiquetasRoles[$rolTecnico] ?? $rolTecnico; // No modifica el dato almacenado.
// Compone el nombre completo usando solo campos que el esquema puede tener.
$nombreCompleto = trim(implode(' ', array_filter([(string) ($empleadoDetalle['nombre'] ?? ''), (string) ($empleadoDetalle['seg_nombre'] ?? ''), (string) ($empleadoDetalle['apellido_p'] ?? ''), (string) ($empleadoDetalle['apellido_m'] ?? '')]))); // Omite partes vacías.
// Agrupa la información personal permitida y excluye siempre la contraseña.
$camposDetalle = ['Identificador' => (string) ($empleadoDetalle['id_empleado'] ?? ''), 'Nombre completo' => $nombreCompleto, 'Usuario' => (string) ($empleadoDetalle['usuario'] ?? ''), 'CURP' => (string) ($empleadoDetalle['curp'] ?? ''), 'Teléfono' => (string) ($empleadoDetalle['telefono'] ?? ''), 'Género' => (string) ($empleadoDetalle['genero'] ?? ''), 'Rol' => $rolVisible, 'Fecha de registro' => (string) ($empleadoDetalle['fecha_creacion'] ?? '')]; // No incluye la columna de contraseña.
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#fff5f8">
    <title>Información del usuario | El Güero</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="assets/css/empleados.css" rel="stylesheet">
    <link href="assets/css/paleta-rosa.css" rel="stylesheet">
    <script src="assets/js/navegacion.js" defer></script>
</head>
<body class="pagina-empleados">
    <div class="contenedor-empleados d-flex min-vh-100">
        <aside class="barra-empleados d-flex flex-column p-3" aria-label="Navegación del sistema">
            <a class="marca-empleados d-flex align-items-center gap-3 text-decoration-none" href="index.php" aria-label="El Güero, inicio">
                <!-- Usa el logotipo oficial en el encabezado uniforme del menú. -->
                <img class="logo-sistema" src="assets/logoPIG.png" alt="Logotipo de El Güero">
                <span class="d-flex flex-column"><span class="fw-bold">El Güero</span><span class="small text-secondary">Tocinería · Sistema</span></span>
            </a>
            <a class="enlace-inicio-navegacion" href="index.php" aria-label="Ir a la pantalla principal">⌂ <span>Inicio</span></a>
            <nav class="mt-4" aria-label="Navegación del sistema">
                <button class="boton-categoria-navegacion titulo-navegacion small fw-semibold text-uppercase" type="button" data-menu-desplegable aria-expanded="true" aria-controls="menu-catalogo">Catálogo e inventario</button>
                <div class="grupo-navegacion-contenido is-open" id="menu-catalogo">
                <a class="enlace-navegacion d-flex align-items-center gap-2 rounded-3 px-3 py-2 text-decoration-none" href="index.php?c=producto&amp;a=nuevo"><span>Registrar Producto</span></a>
                <a class="enlace-navegacion d-flex align-items-center gap-2 rounded-3 px-3 py-2 text-decoration-none" href="index.php?c=producto&amp;a=editar"><span>Productos e Inventario</span></a>
                </div>
                <button class="boton-categoria-navegacion titulo-navegacion small fw-semibold text-uppercase" type="button" data-menu-desplegable aria-expanded="true" aria-controls="menu-seguridad">Seguridad y acceso</button>
                <div class="grupo-navegacion-contenido is-open" id="menu-seguridad">
                <a class="enlace-navegacion activo d-flex align-items-center gap-2 rounded-3 px-3 py-2 text-decoration-none" href="index.php?c=empleado&amp;a=lista" aria-current="page"><span>Lista de Usuarios</span></a>
                <a class="enlace-navegacion d-flex align-items-center gap-2 rounded-3 px-3 py-2 text-decoration-none" href="index.php?c=empleado&amp;a=registro"><span>Agregar Usuario</span></a>
                <a class="enlace-navegacion d-flex align-items-center gap-2 rounded-3 px-3 py-2 text-decoration-none" href="index.php?c=empleado&amp;a=perfil"><span>Mi Perfil</span></a>
                </div>
            </nav>
            <div class="sesion-empleado d-flex align-items-center gap-2 mt-auto pt-3 border-top">
                <span class="small fw-semibold text-truncate"><?= $escaparHtml((string) ($_SESSION['user']['usuario'] ?? 'Usuario')) ?></span>
                <a class="btn btn-sm btn-outline-secondary enlace-cerrar-sesion" href="index.php?c=auth&amp;a=logout">Cerrar sesión</a>
            </div>
        </aside>
        <main class="contenido-empleados flex-grow-1 p-3 p-md-4 p-xl-5">
        <header class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <p class="miga-pan small mb-2">Seguridad y acceso / Usuarios</p>
                <h1 class="h2 fw-bold mb-1">Información del usuario</h1>
                <p class="text-secondary mb-0">Datos de la cuenta seleccionada. La contraseña nunca se muestra.</p>
            </div>
            <a class="btn btn-outline-primary" href="index.php?c=empleado&amp;a=lista">Volver a usuarios</a>
        </header>
        <section class="tarjeta-lista card border-0" aria-labelledby="titulo-detalle">
            <div class="card-body p-3 p-md-4">
                <h2 class="h5 fw-bold mb-4" id="titulo-detalle"><?= $escaparHtml($nombreCompleto !== '' ? $nombreCompleto : 'Cuenta de empleado') ?></h2>
                <dl class="row mb-0">
                    <?php foreach ($camposDetalle as $etiqueta => $valor): ?>
                        <dt class="col-sm-4 col-lg-3 py-2 text-secondary"><?= $escaparHtml($etiqueta) ?></dt>
                        <dd class="col-sm-8 col-lg-9 py-2 border-bottom"><?= $escaparHtml($valor !== '' ? $valor : 'No registrado') ?></dd>
                    <?php endforeach; ?>
                </dl>
            </div>
        </section>
        </main>
    </div>
</body>
</html>
