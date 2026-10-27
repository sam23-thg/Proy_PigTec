<?php
// Escapa los datos de la cuenta para que se representen como texto y no como HTML.
$escaparHtml = static fn (string $valor): string => htmlspecialchars($valor, ENT_QUOTES, 'UTF-8'); // Aplica codificación segura UTF-8.
// Define nombres comprensibles para los roles admitidos por el sistema.
$etiquetasRoles = ['ADMINISTRADOR' => 'Administrador', 'VENTAS' => 'Ventas', 'ALMACEN' => 'Almacén', 'DISTRIBUCION' => 'Distribución', 'REPARTIDOR' => 'Repartidor']; // Traduce los valores del ENUM.
// Convierte el campo booleano persistido en un valor legible para el interruptor.
$cuentaActiva = (int) ($empleadoDetalle['estado'] ?? 1) === 1; // Usa activo como valor predeterminado si el dato no está disponible.
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
    <script src="assets/js/navegacion.js?v=menu-persistente-2" defer></script>
    <script src="assets/js/registro-empleados.js" defer></script>
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
            <?php require dirname(__DIR__) . '/partials/menu_lateral.php'; ?>
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
        <section class="tarjeta-lista card border-0" aria-labelledby="titulo-formulario-edicion">
            <div class="card-body p-3 p-md-4">
                <!-- Presenta mensajes de validación o guardado sin insertar HTML dinámico. -->
                <?php if (isset($avisoDetalle) && is_array($avisoDetalle)): ?>
                    <!-- Usa una alerta Bootstrap acorde al resultado del formulario. -->
                    <div class="alert alert-<?= $escaparHtml((string) ($avisoDetalle['tipo'] ?? 'danger')) ?>" role="status"><?= $escaparHtml((string) ($avisoDetalle['mensaje'] ?? '')) ?></div>
                <!-- Finaliza la condición de aviso. -->
                <?php endif; ?>
                <!-- Identifica el formulario que permite modificar la cuenta seleccionada. -->
                <h2 class="h5 fw-bold mb-4" id="titulo-formulario-edicion">Editar datos del usuario</h2>
                <!-- Permite editar la cuenta completa y validar los mismos campos del formulario de alta. -->
                <form action="index.php?c=empleado&amp;a=ver" method="post" class="row g-3" data-validacion-registro novalidate>
                    <!-- Envía el identificador primario sin permitir cambiarlo desde el formulario. -->
                    <input type="hidden" name="id_empleado" value="<?= (int) $empleadoDetalle['id_empleado'] ?>">
                    <!-- Incluye el token de sesión para proteger la actualización. -->
                    <input type="hidden" name="token_administracion" value="<?= $escaparHtml((string) ($_SESSION['tokenAdministracionEmpleado'] ?? '')) ?>">
                    <!-- Presenta el nombre actual del empleado y permite editarlo. -->
                    <div class="col-md-6">
                        <!-- Asocia la etiqueta con el campo obligatorio de nombre. -->
                        <label class="form-label" for="nombre-empleado">Nombre *</label>
                        <!-- Precarga el valor validado desde controlador o base de datos. -->
                        <input class="form-control" id="nombre-empleado" name="nombre" type="text" maxlength="100" pattern="[\p{L}\p{M} .’'\-]+" required aria-describedby="error-nombre-empleado" value="<?= $escaparHtml((string) ($datosEdicion['nombre'] ?? '')) ?>">
                        <!-- Describe el formato requerido cuando el nombre no cumple la regla. -->
                        <div class="invalid-feedback" id="error-nombre-empleado">Ingresa un nombre con letras y separadores habituales, sin números.</div>
                    <!-- Cierra el campo de nombre. -->
                    </div>
                    <!-- Presenta el segundo nombre opcional. -->
                    <div class="col-md-6">
                        <!-- Asocia la etiqueta al segundo nombre. -->
                        <label class="form-label" for="segundo-nombre-empleado">Segundo nombre</label>
                        <!-- Precarga y permite cambiar el segundo nombre. -->
                        <input class="form-control" id="segundo-nombre-empleado" name="seg_nombre" type="text" maxlength="100" pattern="[\p{L}\p{M} .’'\-]+" aria-describedby="error-segundo-nombre-empleado" value="<?= $escaparHtml((string) ($datosEdicion['seg_nombre'] ?? '')) ?>">
                        <!-- Explica el formato permitido si se captura un segundo nombre. -->
                        <div class="invalid-feedback" id="error-segundo-nombre-empleado">Usa letras y separadores habituales, sin números.</div>
                    <!-- Cierra el campo de segundo nombre. -->
                    </div>
                    <!-- Presenta el apellido paterno obligatorio. -->
                    <div class="col-md-6">
                        <!-- Asocia la etiqueta al apellido paterno. -->
                        <label class="form-label" for="apellido-paterno-empleado">Apellido paterno *</label>
                        <!-- Precarga el apellido paterno actual. -->
                        <input class="form-control" id="apellido-paterno-empleado" name="apellido_p" type="text" maxlength="100" pattern="[\p{L}\p{M} .’'\-]+" required aria-describedby="error-apellido-paterno-empleado" value="<?= $escaparHtml((string) ($datosEdicion['apellido_p'] ?? '')) ?>">
                        <!-- Describe el formato requerido para el apellido paterno. -->
                        <div class="invalid-feedback" id="error-apellido-paterno-empleado">Ingresa un apellido con letras y separadores habituales, sin números.</div>
                    <!-- Cierra el campo de apellido paterno. -->
                    </div>
                    <!-- Presenta el apellido materno opcional. -->
                    <div class="col-md-6">
                        <!-- Asocia la etiqueta al apellido materno. -->
                        <label class="form-label" for="apellido-materno-empleado">Apellido materno</label>
                        <!-- Precarga y permite cambiar el apellido materno. -->
                        <input class="form-control" id="apellido-materno-empleado" name="apellido_m" type="text" maxlength="100" pattern="[\p{L}\p{M} .’'\-]+" aria-describedby="error-apellido-materno-empleado" value="<?= $escaparHtml((string) ($datosEdicion['apellido_m'] ?? '')) ?>">
                        <!-- Explica el formato permitido si se captura un apellido materno. -->
                        <div class="invalid-feedback" id="error-apellido-materno-empleado">Usa letras y separadores habituales, sin números.</div>
                    <!-- Cierra el campo de apellido materno. -->
                    </div>
                    <!-- Presenta el teléfono editable y obligatorio. -->
                    <div class="col-md-6">
                        <!-- Asocia la etiqueta al teléfono de contacto. -->
                        <label class="form-label" for="telefono-empleado">Teléfono *</label>
                        <!-- Precarga el teléfono conservándolo como texto. -->
                        <input class="form-control" id="telefono-empleado" name="telefono" type="tel" inputmode="numeric" minlength="10" maxlength="15" pattern="[0-9]{10,15}" required aria-describedby="error-telefono-empleado" value="<?= $escaparHtml((string) ($datosEdicion['telefono'] ?? '')) ?>">
                        <!-- Informa que el teléfono debe contener únicamente de diez a quince dígitos. -->
                        <div class="invalid-feedback" id="error-telefono-empleado">Ingresa entre 10 y 15 dígitos, sin espacios ni símbolos.</div>
                    <!-- Cierra el campo de teléfono. -->
                    </div>
                    <!-- Presenta el nombre de usuario requerido para iniciar sesión. -->
                    <div class="col-md-6">
                        <!-- Asocia la etiqueta con el identificador de acceso editable. -->
                        <label class="form-label" for="usuario-empleado">Usuario *</label>
                        <!-- Aplica los mismos límites y caracteres permitidos en el registro. -->
                        <input class="form-control" id="usuario-empleado" name="usuario" type="text" minlength="3" maxlength="50" pattern="[A-Za-z0-9._-]{3,50}" autocomplete="username" required aria-describedby="error-usuario-empleado" value="<?= $escaparHtml((string) ($datosEdicion['usuario'] ?? '')) ?>">
                        <!-- Explica los caracteres permitidos para el nombre de usuario. -->
                        <div class="invalid-feedback" id="error-usuario-empleado">Usa de 3 a 50 letras, números, puntos, guiones o guiones bajos.</div>
                    <!-- Cierra el campo de usuario. -->
                    </div>
                    <!-- Presenta la CURP obligatoria y editable. -->
                    <div class="col-md-6">
                        <!-- Asocia la etiqueta con la CURP normalizada por el JavaScript externo. -->
                        <label class="form-label" for="curp-empleado">CURP *</label>
                        <!-- Limita la captura a dieciocho caracteres con la estructura usada en el alta. -->
                        <input class="form-control text-uppercase" id="curp-empleado" name="curp" type="text" minlength="18" maxlength="18" pattern="[A-Za-z][AaEeIiOoUuXx][A-Za-z]{2}[0-9]{6}[HhMm][A-Za-z]{2}[B-DF-HJ-NP-TV-Zb-df-hj-np-tv-z]{3}[A-Za-z0-9][0-9]" autocomplete="off" required aria-describedby="error-curp-empleado" value="<?= $escaparHtml((string) ($datosEdicion['curp'] ?? '')) ?>">
                        <!-- Indica la longitud y estructura requerida para la CURP. -->
                        <div class="invalid-feedback" id="error-curp-empleado">La CURP debe tener exactamente 18 caracteres y un formato válido.</div>
                    <!-- Cierra el campo de CURP. -->
                    </div>
                    <!-- Presenta la lista de roles admitidos para cambiar la asignación del usuario. -->
                    <div class="col-md-6">
                        <!-- Asocia la etiqueta con el selector de rol. -->
                        <label class="form-label" for="rol-empleado">Rol *</label>
                        <!-- Marca el selector para reutilizar la validación externa del alta. -->
                        <select class="form-select" id="rol-empleado" name="rol" data-selector-rol required>
                            <!-- Solicita una selección explícita cuando no hay rol válido. -->
                            <option value="">Selecciona un rol</option>
                            <!-- Recorre los roles del sistema con sus etiquetas legibles. -->
                            <?php foreach ($etiquetasRoles as $valorRol => $etiquetaRol): ?>
                                <!-- Selecciona el valor pendiente enviado o el rol guardado. -->
                                <option value="<?= $escaparHtml($valorRol) ?>" <?= ($datosEdicion['rol'] ?? '') === $valorRol ? 'selected' : '' ?>><?= $escaparHtml($etiquetaRol) ?></option>
                            <!-- Finaliza la generación de roles permitidos. -->
                            <?php endforeach; ?>
                        <!-- Cierra el selector de rol. -->
                        </select>
                        <!-- Señala que el rol debe seleccionarse antes de guardar. -->
                        <div class="invalid-feedback">Selecciona uno de los roles disponibles.</div>
                    <!-- Cierra el campo de rol. -->
                    </div>
                    <!-- Presenta las opciones permitidas de género. -->
                    <div class="col-md-6">
                        <!-- Asocia la etiqueta con el selector de género. -->
                        <label class="form-label" for="genero-empleado">Género</label>
                        <!-- Conserva la selección cargada y permite actualizarla. -->
                        <select class="form-select" id="genero-empleado" name="genero">
                            <!-- Mantiene la opción vacía válida para un campo opcional. -->
                            <option value="" <?= ($datosEdicion['genero'] ?? '') === '' ? 'selected' : '' ?>>Sin especificar</option>
                            <!-- Ofrece el primer valor admitido por el perfil. -->
                            <option value="Masculino" <?= ($datosEdicion['genero'] ?? '') === 'Masculino' ? 'selected' : '' ?>>Masculino</option>
                            <!-- Ofrece el segundo valor admitido por el perfil. -->
                            <option value="Femenino" <?= ($datosEdicion['genero'] ?? '') === 'Femenino' ? 'selected' : '' ?>>Femenino</option>
                            <!-- Ofrece el tercer valor admitido por el perfil. -->
                            <option value="Otro" <?= ($datosEdicion['genero'] ?? '') === 'Otro' ? 'selected' : '' ?>>Otro</option>
                        <!-- Cierra las opciones de género. -->
                        </select>
                    <!-- Cierra el selector de género. -->
                    </div>
                    <!-- Agrupa la acción que guarda todos los campos administrativos en conjunto. -->
                    <div class="col-12 d-flex justify-content-end">
                        <!-- Envía el formulario completo a la acción administrativa protegida. -->
                        <button class="btn btn-primary" type="submit" name="accion_empleado" value="actualizar_datos">Guardar cambios</button>
                    <!-- Cierra el grupo de guardado. -->
                    </div>
                <!-- Cierra el formulario que guarda los datos generales de la cuenta. -->
                </form>
                <!-- Separa la acción inmediata del estado del guardado de datos generales. -->
                <hr class="my-4">
                <!-- Presenta el estado de acceso y permite cambiarlo de forma independiente. -->
                <section class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3" aria-labelledby="titulo-estado-cuenta">
                    <!-- Agrupa la etiqueta y el valor de estado actualmente guardado. -->
                    <div>
                        <!-- Identifica la sección de estado al pie del formulario. -->
                        <h3 class="h6 fw-bold mb-2" id="titulo-estado-cuenta">Estado de la cuenta</h3>
                        <!-- Muestra el estado persistido con texto y color. -->
                        <span id="estado-cuenta-empleado" class="badge rounded-pill <?= $cuentaActiva ? 'text-bg-success' : 'text-bg-danger' ?>"><?= $cuentaActiva ? '🟢 Activo' : '🔴 Inactivo' ?></span>
                    <!-- Cierra la información del estado actual. -->
                    </div>
                    <!-- Envía únicamente la acción de alternar estado, separada del formulario anterior. -->
                    <form action="index.php?c=empleado&amp;a=ver" method="post" class="m-0">
                        <!-- Identifica la cuenta cuyo estado se cambiará. -->
                        <input type="hidden" name="id_empleado" value="<?= (int) $empleadoDetalle['id_empleado'] ?>">
                        <!-- Protege la acción independiente con el token de administración de la sesión. -->
                        <input type="hidden" name="token_administracion" value="<?= $escaparHtml((string) ($_SESSION['tokenAdministracionEmpleado'] ?? '')) ?>">
                        <!-- Alterna entre habilitar e inhabilitar con una acción explícita. -->
                        <button class="btn <?= $cuentaActiva ? 'btn-outline-danger' : 'btn-outline-success' ?>" type="submit" name="accion_empleado" value="cambiar_estado"><?= $cuentaActiva ? 'Inhabilitar cuenta' : 'Habilitar cuenta' ?></button>
                    <!-- Cierra el formulario dedicado al cambio de estado. -->
                    </form>
                <!-- Cierra la sección de estado ubicada al final de la ficha. -->
                </section>
            </div>
        </section>
        </main>
    </div>
</body>
</html>
