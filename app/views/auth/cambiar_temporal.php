<?php
// Normaliza la alerta recibida antes de representarla en el documento.
$mensajeVista = isset($mensaje) ? (string) $mensaje : ''; // Permite incluir la vista sin definir una alerta.
// Restringe el tipo de alerta a los estilos previstos por Bootstrap.
$tipoAlertaVista = isset($tipoAlerta) && $tipoAlerta === 'success' ? 'success' : 'danger'; // Evita clases CSS controladas por valores externos.
?>
<!-- Declara el documento HTML5 para el cambio obligatorio de contraseña. -->
<!DOCTYPE html>
<!-- Establece el idioma español para accesibilidad y presentación del navegador. -->
<html lang="es">
<!-- Inicia los metadatos y recursos externos de la pantalla temporal. -->
<head>
    <!-- Define la codificación usada por nombres y mensajes en español. -->
    <meta charset="UTF-8">
    <!-- Adapta el diseño responsivo a la pantalla disponible. -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Comunica a navegadores móviles el tono claro de la interfaz de acceso. -->
    <meta name="theme-color" content="#fff5f8">
    <!-- Define un título específico para el paso obligatorio de credenciales. -->
    <title>Cambiar contraseña | El Puerquito Digital</title>
    <!-- Carga el framework Bootstrap 5.3.3 con verificación de integridad del recurso. -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Comparte la paleta y distribución de la pantalla de autenticación. -->
    <link href="assets/css/auth.css" rel="stylesheet">
    <link href="assets/css/paleta-rosa.css" rel="stylesheet">
    <!-- Carga el comportamiento de validación y mostrar contraseña sin JavaScript en línea. -->
    <script src="assets/js/auth.js" defer></script>
<!-- Cierra los metadatos y recursos de la página. -->
</head>
<!-- Inicia el contenido visible de la pantalla temporal. -->
<body class="pagina-autenticacion">
    <!-- Ocupa la pantalla y centra el formulario obligatorio. -->
    <main class="contenedor-autenticacion container-fluid d-flex align-items-center justify-content-center min-vh-100 px-3 py-5">
        <!-- Presenta el formulario en una tarjeta centrada que no ofrece navegación alternativa. -->
        <section class="tarjeta-autenticacion card border-0 shadow-lg" aria-labelledby="titulo-cambio">
            <!-- Aplica el espacio interno estándar de Bootstrap a la tarjeta. -->
            <div class="card-body p-4 p-md-5">
                <!-- Identifica el propósito del paso antes de solicitar la nueva credencial. -->
                <header class="encabezado-autenticacion text-center mb-4">
                    <!-- Marca visual textual consistente con la vista Login. -->
                    <!-- Indica al usuario que el cambio es parte del sistema de acceso. -->
                    <p class="nombre-sistema text-uppercase fw-semibold mb-2">El Puerquito Digital</p>
                    <!-- Explica claramente la acción obligatoria de esta pantalla. -->
                    <h1 class="h3 fw-bold mb-2" id="titulo-cambio">Actualiza tu contraseña</h1>
                    <!-- Informa que la credencial temporal debe cambiarse para continuar. -->
                    <p class="texto-secundario mb-0">Por seguridad, establece una contraseña definitiva para continuar.</p>
                <!-- Cierra el encabezado informativo de la tarjeta. -->
                </header>

                <!-- Muestra la alerta del servidor encima de los campos del formulario. -->
                <?php if ($mensajeVista !== ''): ?>
                    <!-- Escapa el texto y usa solo un tipo de alerta aceptado. -->
                    <div class="alert alert-<?= htmlspecialchars($tipoAlertaVista, ENT_QUOTES, 'UTF-8') ?>" role="alert"><?= htmlspecialchars($mensajeVista, ENT_QUOTES, 'UTF-8') ?></div>
                <!-- Cierra la condición de alerta opcional. -->
                <?php endif; ?>

                <!-- Envía la nueva credencial al controlador sin opción de saltar el paso. -->
                <form id="formulario-cambio-temporal" action="index.php?accion=cambiar-temporal" method="post" data-validacion-cliente novalidate>
                    <!-- Agrupa el campo de contraseña nueva con su control para mostrar u ocultar. -->
                    <div class="mb-3">
                        <!-- Asocia el rótulo del campo con su identificador accesible. -->
                        <label class="form-label fw-semibold" for="contrasenia-nueva">Nueva contraseña <span class="text-danger" aria-hidden="true">(*)</span></label>
                        <!-- Agrupa la entrada y el control de visibilidad usando componentes Bootstrap. -->
                        <div class="input-group">
                            <!-- Oculta la clave, aplica longitud mínima y activa el control externo de coincidencia. -->
                            <input class="form-control" id="contrasenia-nueva" name="contrasenia_nueva" type="password" autocomplete="new-password" minlength="8" maxlength="72" required data-contrasenia-nueva aria-describedby="ayuda-contrasenia error-contrasenia-nueva">
                            <!-- Permite ver la nueva contraseña con un control accesible y sin evento en línea. -->
                            <button class="btn btn-outline-secondary" type="button" data-alternar-contrasenia="contrasenia-nueva" aria-label="Mostrar contraseña" aria-pressed="false">Mostrar</button>
                        <!-- Cierra el grupo de entrada y visibilidad. -->
                        </div>
                        <!-- Explica la longitud mínima para orientar al usuario antes del envío. -->
                        <div class="form-text" id="ayuda-contrasenia">Utiliza al menos 8 caracteres; el límite de almacenamiento es 72 bytes.</div>
                        <!-- Comunica cuando la nueva contraseña está vacía o no alcanza la longitud mínima. -->
                        <div class="invalid-feedback" id="error-contrasenia-nueva">La contraseña debe tener al menos 8 caracteres y no superar 72 bytes.</div>
                    <!-- Cierra el grupo de la nueva contraseña. -->
                    </div>
                    <!-- Agrupa la confirmación para que el usuario repita la clave definitiva. -->
                    <div class="mb-4">
                        <!-- Asocia la etiqueta del campo de confirmación con su identificador. -->
                        <label class="form-label fw-semibold" for="confirmacion-contrasenia">Confirmar contraseña <span class="text-danger" aria-hidden="true">(*)</span></label>
                        <!-- Oculta la confirmación y permite que el JavaScript compare ambos valores. -->
                        <input class="form-control form-control-lg" id="confirmacion-contrasenia" name="confirmacion_contrasenia" type="password" autocomplete="new-password" minlength="8" maxlength="72" required data-confirmar-contrasenia aria-describedby="error-confirmacion">
                        <!-- Comunica al usuario cuando la confirmación no coincide con el campo anterior. -->
                        <div class="invalid-feedback" id="error-confirmacion">Las contraseñas deben coincidir.</div>
                    <!-- Cierra el grupo de confirmación. -->
                    </div>
                    <!-- Envía la credencial definitiva para verificación y almacenamiento seguro. -->
                    <button class="btn btn-primary btn-lg w-100 boton-acceso" type="submit">Confirmar contraseña</button>
                <!-- Cierra el formulario obligatorio de actualización de contraseña. -->
                </form>
            <!-- Cierra el contenido interno de la tarjeta. -->
            </div>
        <!-- Cierra la tarjeta de cambio obligatorio. -->
        </section>
    <!-- Cierra el contenedor centrado de la pantalla. -->
    </main>
<!-- Cierra el cuerpo visible del documento. -->
</body>
<!-- Finaliza el documento HTML. -->
</html>
