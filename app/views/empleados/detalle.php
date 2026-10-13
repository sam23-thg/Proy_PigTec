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
</head>
<body class="pagina-empleados">
    <main class="container py-4 py-md-5">
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
</body>
</html>
