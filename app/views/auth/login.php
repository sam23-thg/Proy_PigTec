<?php
// Normaliza el mensaje recibido antes de imprimirlo en la página.
$mensajeVista = isset($mensaje) ? (string) $mensaje : ''; // Evita avisos cuando la vista se incluye sin notificación.
// Limita el tipo de alerta a las variantes seguras de Bootstrap.
$tipoAlertaVista = isset($tipoAlerta) && $tipoAlerta === 'success' ? 'success' : 'danger'; // Evita insertar clases arbitrarias en el documento.
// Determina si el visitante ya completó la autenticación y está viendo su estado de sesión.
$sesionIniciadaVista = !empty($modoSesionIniciada); // Permite reutilizar el diseño para confirmar el acceso por rol.
?>
<!-- Declara el documento HTML5 de la pantalla de autenticación. -->
<!DOCTYPE html>
<!-- Establece el idioma español para lectores de pantalla y reglas tipográficas. -->
<html lang="es">
<!-- Inicia los metadatos y recursos visuales del documento. -->
<head>
    <!-- Define la codificación para caracteres y textos en español. -->
    <meta charset="UTF-8">
    <!-- Adapta el ancho de la interfaz a dispositivos móviles y de escritorio. -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Describe el propósito de la pantalla en navegadores y buscadores. -->
    <meta name="description" content="Acceso al sistema de gestión El Puerquito Digital.">
    <!-- Define el color de la interfaz para el marco del navegador móvil. -->
    <meta name="theme-color" content="#f7f8fc">
    <!-- Asigna el título que aparece en la pestaña del navegador. -->
    <title>Iniciar sesión | El Puerquito Digital</title>
    <!-- Carga Bootstrap 5.3.3 desde un CDN con integridad del recurso verificada. -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Carga los estilos propios de autenticación desde un archivo externo. -->
    <link href="assets/css/auth.css" rel="stylesheet">
    <!-- Carga el comportamiento de validación y visibilidad de contraseña después del HTML. -->
    <script src="assets/js/auth.js" defer></script>
<!-- Cierra la sección de metadatos y recursos del documento. -->
</head>
<!-- Inicia el cuerpo visible de la pantalla de acceso. -->
<body class="pagina-autenticacion">
    <!-- Mantiene la tarjeta de autenticación centrada en la pantalla en ambos ejes. -->
    <main class="contenedor-autenticacion container-fluid d-flex align-items-center justify-content-center min-vh-100 px-3 py-5">
        <!-- Agrupa la identidad del sistema, alertas y formulario en una tarjeta responsiva. -->
        <section class="tarjeta-autenticacion card border-0 shadow-lg" aria-labelledby="titulo-autenticacion">
            <!-- Conserva el espaciado interior de la tarjeta sin estilos en línea. -->
            <div class="card-body p-4 p-md-5">
                <!-- Centra la marca para mantener la composición de una pantalla de acceso enfocada. -->
                <header class="encabezado-autenticacion text-center mb-4">
                    <!-- Representa la marca con un distintivo textual independiente de imágenes externas. -->
                    <div class="marca-autenticacion d-inline-flex align-items-center justify-content-center rounded-circle mb-3" aria-hidden="true">EP</div>
                    <!-- Muestra el nombre del sistema sobre el formulario. -->
                    <p class="nombre-sistema text-uppercase fw-semibold mb-2">El Puerquito Digital</p>
                    <!-- Presenta el título según se muestre acceso o confirmación de sesión. -->
                    <h1 class="h3 fw-bold mb-2" id="titulo-autenticacion"><?= $sesionIniciadaVista ? 'Acceso confirmado' : 'Bienvenido' ?></h1>
                    <!-- Resume la acción necesaria sin distraer del formulario principal. -->
                    <p class="texto-secundario mb-0"><?= $sesionIniciadaVista ? 'Tu sesión está activa.' : 'Ingresa tus credenciales para continuar.' ?></p>
                <!-- Cierra el encabezado de identidad de la tarjeta. -->
                </header>

                <!-- Presenta mensajes de estado controlados por el controlador encima del contenido principal. -->
                <?php if ($mensajeVista !== ''): ?>
                    <!-- Escapa el texto y limita la clase de alerta para impedir inyección de marcado. -->
                    <div class="alert alert-<?= htmlspecialchars($tipoAlertaVista, ENT_QUOTES, 'UTF-8') ?> mb-4" role="alert"><?= htmlspecialchars($mensajeVista, ENT_QUOTES, 'UTF-8') ?></div>
                <!-- Finaliza la condición de presentación de mensajes. -->
                <?php endif; ?>

                <!-- Muestra un resumen de la identidad autenticada cuando el Front Controller confirmó la sesión. -->
                <?php if ($sesionIniciadaVista): ?>
                    <!-- Agrupa la confirmación para conservar la jerarquía visual de Bootstrap. -->
                    <div class="resumen-sesion rounded-3 p-3" role="status">
                        <!-- Muestra el nombre de usuario previamente escapado para evitar interpretar HTML. -->
                        <p class="mb-2"><span class="fw-semibold">Usuario:</span> <?= htmlspecialchars((string) ($usuarioAutenticado ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                        <!-- Muestra el rol autorizado y escapado para confirmar el destino de acceso. -->
                        <p class="mb-0"><span class="fw-semibold">Área asignada:</span> <?= htmlspecialchars((string) ($tituloPanel ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                    <!-- Cierra el resumen de la sesión validada. -->
                    </div>
                <!-- Muestra el formulario cuando todavía no hay una sesión iniciada. -->
                <?php else: ?>
                    <!-- Envía las credenciales al Front Controller mediante POST. -->
                    <form id="formulario-login" action="index.php?accion=login" method="post" data-validacion-cliente novalidate>
                        <!-- Agrupa el campo de nombre de usuario y su mensaje de validación accesible. -->
                        <div class="mb-3">
                            <!-- Asocia la etiqueta visible con el campo de usuario. -->
                            <label class="form-label fw-semibold" for="usuario">Usuario <span class="text-danger" aria-hidden="true">(*)</span></label>
                            <!-- Captura el nombre de usuario como texto y evita autocompletar datos ajenos. -->
                            <input class="form-control form-control-lg" id="usuario" name="usuario" type="text" autocomplete="username" maxlength="50" required aria-describedby="error-usuario">
                            <!-- Comunica el error de obligatoriedad cuando la validación detecta el campo vacío. -->
                            <div class="invalid-feedback" id="error-usuario">Ingresa tu usuario.</div>
                        <!-- Cierra el grupo del campo de usuario. -->
                        </div>
                        <!-- Agrupa el campo de contraseña y su mensaje de validación accesible. -->
                        <div class="mb-4">
                            <!-- Asocia la etiqueta visible con el campo de contraseña. -->
                            <label class="form-label fw-semibold" for="contrasenia">Contraseña <span class="text-danger" aria-hidden="true">(*)</span></label>
                            <!-- Oculta la contraseña mientras se escribe y permite gestores de credenciales confiables. -->
                            <input class="form-control form-control-lg" id="contrasenia" name="contrasenia" type="password" autocomplete="current-password" required aria-describedby="error-contrasenia">
                            <!-- Comunica el error de obligatoriedad cuando la validación detecta el campo vacío. -->
                            <div class="invalid-feedback" id="error-contrasenia">Ingresa tu contraseña.</div>
                        <!-- Cierra el grupo del campo de contraseña. -->
                        </div>
                        <!-- Envía el formulario completo y ocupa el ancho de la tarjeta según la guía visual. -->
                        <button class="btn btn-primary btn-lg w-100 boton-acceso" type="submit">Ingresar</button>
                    <!-- Cierra el formulario de inicio de sesión. -->
                    </form>
                <!-- Finaliza la selección entre formulario y estado autenticado. -->
                <?php endif; ?>
            <!-- Cierra el contenido interno de la tarjeta. -->
            </div>
        <!-- Cierra la tarjeta principal de autenticación. -->
        </section>
    <!-- Cierra el contenedor centrado de la pantalla. -->
    </main>
<!-- Cierra el cuerpo visible del documento. -->
</body>
<!-- Finaliza el documento HTML. -->
</html>
