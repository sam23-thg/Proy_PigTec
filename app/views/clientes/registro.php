<?php
// Prepara una función de escape para toda salida dinámica del formulario.
$escaparCliente = static fn (string $valor): string => htmlspecialchars($valor, ENT_QUOTES, 'UTF-8'); // Evita interpretar datos como marcado HTML.
// Garantiza que los datos devueltos por el controlador tengan estructura de arreglo.
$datosClienteVista = isset($datosFormulario) && is_array($datosFormulario) ? $datosFormulario : []; // Evita referencias a variables no inicializadas.
// Garantiza una lista de errores por campo preparada por el controlador.
$erroresClienteVista = isset($erroresCampos) && is_array($erroresCampos) ? $erroresCampos : []; // Permite marcar cada entrada sin avisos de PHP.
// Limita la variante de alerta a clases Bootstrap permitidas.
$tipoAlertaCliente = isset($tipoAlerta) && in_array($tipoAlerta, ['success', 'danger', 'warning', 'info'], true) ? $tipoAlerta : 'danger'; // Evita clases arbitrarias.
// Normaliza el mensaje mostrado junto al formulario.
$mensajeCliente = isset($mensaje) ? (string) $mensaje : ''; // Permite cargar la vista sin notificación inicial.
// Obtiene la identidad validada de la sesión para el menú común.
$sesionClienteVista = isset($usuarioSesion) && is_array($usuarioSesion) ? $usuarioSesion : []; // Usa datos ya comprobados por el controlador.
?>
<!-- Declara la estructura HTML5 de la pantalla de clientes. -->
<!DOCTYPE html>
<!-- Establece el idioma para lectores de pantalla y navegador. -->
<html lang="es">
<!-- Inicia los metadatos y recursos visuales de la página. -->
<head>
    <!-- Define la codificación para texto en español. -->
    <meta charset="UTF-8">
    <!-- Ajusta la página al ancho del dispositivo. -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Identifica el propósito de la página en resultados y navegadores. -->
    <meta name="description" content="Registro y consulta de clientes de El Puerquito Digital.">
    <!-- Define el tono cálido de la barra del navegador móvil. -->
    <meta name="theme-color" content="#f5f2fa">
    <!-- Nombra la página en la pestaña del navegador. -->
    <title>Clientes | El Puerquito Digital</title>
    <!-- Carga Bootstrap 5.3.3 para la cuadrícula y los componentes. -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Reutiliza el layout, la paleta cálida y la navegación de empleados del Sprint 1. -->
    <link href="assets/css/empleados.css" rel="stylesheet">
    <!-- Reutiliza los ajustes cromáticos compartidos del proyecto. -->
    <link href="assets/css/paleta-rosa.css" rel="stylesheet">
    <!-- Reutiliza la tarjeta y el estilo de campos de Registrar Producto. -->
    <link href="assets/css/productos.css" rel="stylesheet">
    <!-- Carga validación y búsqueda desde JavaScript independiente. -->
    <script src="assets/js/clientes.js?v=2" defer></script>
    <!-- Carga el comportamiento existente de navegación lateral. -->
    <script src="assets/js/navegacion.js?v=menu-persistente-2" defer></script>
<!-- Cierra los recursos de la cabecera. -->
</head>
<!-- Inicia el cuerpo con los estilos generales del sistema. -->
<body class="pagina-empleados">
    <!-- Distribuye la navegación común y el área principal de contenido. -->
    <div class="contenedor-empleados d-flex min-vh-100">
        <!-- Copia el contenedor lateral y la navegación de empleados/lista.php. -->
        <aside class="barra-empleados d-flex flex-column p-3" aria-label="Navegación administrativa">
            <!-- Reproduce el enlace de marca del menú Sprint 1. -->
            <a class="marca-empleados d-flex align-items-center gap-3 text-decoration-none" href="index.php?c=empleado&amp;a=lista" aria-label="El Güero, lista de usuarios">
                <!-- Reutiliza el logotipo original del sistema. -->
                <img class="logo-sistema" src="assets/logoPIG.png" alt="Logotipo de El Güero">
                <!-- Agrupa el nombre y descriptor con las clases originales. -->
                <span class="d-flex flex-column">
                    <!-- Mantiene el nombre mostrado por la vista terminada. -->
                    <span class="fw-bold">El Güero</span>
                    <!-- Mantiene el descriptor de la marca original. -->
                    <span class="small text-secondary">Tocinería · Sistema</span>
                <!-- Cierra el bloque de identidad. -->
                </span>
            <!-- Cierra el enlace de marca. -->
            </a>
            <!-- Reutiliza el acceso directo al inicio. -->
            <a class="enlace-inicio-navegacion" href="index.php" aria-label="Ir a la pantalla principal">⌂ <span>Inicio</span></a>
            <!-- Reproduce la navegación del layout Sprint 1. -->
            <?php require dirname(__DIR__) . '/partials/menu_lateral.php'; ?>
            <!-- Reproduce el pie de sesión del menú de empleados. -->
            <div class="sesion-empleado d-flex align-items-center gap-2 mt-auto pt-3">
                <!-- Muestra la identidad del usuario autenticado. -->
                <span class="d-flex flex-column overflow-hidden">
                    <!-- Escapa el nombre de usuario antes de presentarlo. -->
                    <span class="small fw-semibold text-truncate"><?= $escaparCliente((string) ($sesionClienteVista['usuario'] ?? 'Usuario')) ?></span>
                    <!-- Muestra el rol de la sesión. -->
                    <span class="small text-secondary"><?= $escaparCliente((string) ($sesionClienteVista['rol'] ?? '')) ?></span>
                <!-- Cierra los datos de sesión. -->
                </span>
                <!-- Conserva el botón de cierre de sesión y sus clases Bootstrap originales. -->
                <a class="btn btn-sm btn-outline-secondary enlace-cerrar-sesion" href="index.php?c=auth&amp;a=logout">Cerrar sesión</a>
            <!-- Cierra el pie de sesión. -->
            </div>
        <!-- Cierra el menú lateral. -->
        </aside>
        <!-- Reserva el área de contenido principal con el espacio definido por el layout. -->
        <main class="contenido-empleados flex-grow-1 p-3 p-md-4 p-xl-5">
            <!-- Reproduce el encabezado de página de empleados/lista.php. -->
            <header class="encabezado-productos mb-4">
                <!-- Muestra la ruta jerárquica de navegación con la clase del Sprint 1. -->
                <p class="miga-pan text-secondary small mb-2">Clientes / Registro y consulta</p>
                <!-- Muestra el título principal con la jerarquía de Registrar Producto. -->
                <h1 class="h2 fw-bold mb-2">Registrar clientes</h1>
                <!-- Explica brevemente la acción del formulario. -->
                <p class="text-secondary mb-0">Captura la información de contacto para crear un perfil de cliente.</p>
            <!-- Cierra el encabezado de página. -->
            </header>
                <!-- Muestra los mensajes de resultado preparados por el controlador. -->
                <?php if ($mensajeCliente !== ''): ?>
                    <!-- Escapa el mensaje y conserva una variante Bootstrap permitida. -->
                    <div class="alert alert-<?= $escaparCliente($tipoAlertaCliente) ?>" role="status"><?= $escaparCliente($mensajeCliente) ?></div>
                <!-- Finaliza la alerta condicional. -->
                <?php endif; ?>
                <!-- Reutiliza la tarjeta exterior y el espaciado de empleados/lista.php. -->
                <section class="tarjeta-producto card border-0 mb-4" aria-labelledby="titulo-formulario-cliente">
                    <!-- Reproduce el espaciado adaptable de la tarjeta Registrar Producto. -->
                    <div class="card-body p-3 p-md-4 p-xl-5">
                <!-- Presenta el formulario con la estructura visual de Registrar Producto. -->
                <form action="index.php?c=cliente&amp;a=registro" method="post" data-formulario-cliente novalidate>
                    <!-- Presenta el título de sección y la indicación de campos obligatorios. -->
                    <div class="mb-4 pb-2 border-bottom">
                                    <!-- Nombra la sección de información del formulario. -->
                                    <h2 class="h5 fw-bold mb-1" id="titulo-formulario-cliente">Información del Cliente</h2>
                                    <!-- Indica el significado del asterisco rojo igual que en productos. -->
                                    <p class="small text-secondary mb-0">Los campos marcados con <span class="text-danger fw-bold">(*)</span> son obligatorios.</p>
                                <!-- Cierra el encabezado interno del formulario. -->
                    </div>
                    <!-- Organiza los campos con la misma cuadrícula Bootstrap de productos. -->
                    <div class="row g-3 mb-4">
                                        <!-- Coloca el nombre o razón social en la primera columna. -->
                                        <div class="col-12 col-md-6">
                                            <!-- Asocia la etiqueta al campo obligatorio de nombre. -->
                                            <label class="form-label fw-semibold" for="nombre_razonsocial">Nombre / Razón Social <span class="text-danger fw-bold">(*)</span></label>
                                            <!-- Captura el nombre con el máximo documentado. -->
                                            <input class="form-control <?= isset($erroresClienteVista['nombre_razonsocial']) ? 'is-invalid' : '' ?>" id="nombre_razonsocial" name="nombre_razonsocial" type="text" maxlength="100" placeholder="Ej. Carnes del Centro S.A. de C.V." required data-validacion-cliente value="<?= $escaparCliente((string) ($datosClienteVista['nombre_razonsocial'] ?? '')) ?>" aria-describedby="ayuda-nombre-cliente error-nombre-cliente">
                                            <!-- Explica que el asterisco denota obligatoriedad. -->
                                            <div class="form-text" id="ayuda-nombre-cliente">Campo obligatorio.</div>
                                            <!-- Reserva el mensaje visual de validación. -->
                                            <div class="invalid-feedback" id="error-nombre-cliente" data-error-cliente="nombre_razonsocial"><?= $escaparCliente((string) ($erroresClienteVista['nombre_razonsocial'] ?? 'Ingresa el nombre o razón social.')) ?></div>
                                        <!-- Cierra el campo de nombre. -->
                                        </div>
                                        <!-- Coloca el teléfono en la primera columna. -->
                                        <div class="col-12 col-md-6">
                                            <!-- Asocia la etiqueta al teléfono obligatorio. -->
                                            <label class="form-label fw-semibold" for="telefono">Teléfono <span class="text-danger fw-bold">(*)</span></label>
                                            <!-- Captura el teléfono como texto para conservar ceros iniciales. -->
                                            <input class="form-control <?= isset($erroresClienteVista['telefono']) ? 'is-invalid' : '' ?>" id="telefono" name="telefono" type="tel" inputmode="numeric" pattern="[0-9]{10,15}" maxlength="15" placeholder="Ej. 5551234567" required data-validacion-cliente value="<?= $escaparCliente((string) ($datosClienteVista['telefono'] ?? '')) ?>" aria-describedby="ayuda-telefono-cliente error-telefono-cliente">
                                            <!-- Informa que se aceptan entre diez y quince dígitos sin símbolos. -->
                                            <div class="form-text" id="ayuda-telefono-cliente">De 10 a 15 dígitos.</div>
                                            <!-- Reserva el mensaje visual de validación. -->
                                            <div class="invalid-feedback" id="error-telefono-cliente" data-error-cliente="telefono"><?= $escaparCliente((string) ($erroresClienteVista['telefono'] ?? 'Usa únicamente de 10 a 15 dígitos.')) ?></div>
                                        <!-- Cierra el campo de teléfono. -->
                                        </div>
                                        <!-- Coloca la calle en la segunda columna. -->
                                        <div class="col-12 col-md-6">
                                            <!-- Asocia la etiqueta al campo obligatorio de calle. -->
                                            <label class="form-label fw-semibold" for="calle">Calle <span class="text-danger fw-bold">(*)</span></label>
                                            <!-- Captura la calle o conserva N/A cuando no exista el dato. -->
                                            <input class="form-control <?= isset($erroresClienteVista['calle']) ? 'is-invalid' : '' ?>" id="calle" name="calle" type="text" maxlength="255" placeholder="N/A" required data-validacion-cliente value="<?= $escaparCliente((string) ($datosClienteVista['calle'] ?? 'N/A')) ?>" aria-describedby="ayuda-calle-cliente error-calle-cliente">
                                            <!-- Indica que N/A satisface el campo cuando no existe la calle. -->
                                            <div class="form-text" id="ayuda-calle-cliente"> Si no existe, deja N/A.</div>
                                            <!-- Muestra el error de obligatoriedad, longitud o contenido de la calle. -->
                                            <div class="invalid-feedback" id="error-calle-cliente" data-error-cliente="calle"><?= $escaparCliente((string) ($erroresClienteVista['calle'] ?? 'Ingresa la calle o escribe N/A si no existe.')) ?></div>
                                        <!-- Cierra el campo de calle. -->
                                        </div>
                                        <!-- Coloca la colonia en la primera columna. -->
                                        <div class="col-12 col-md-6">
                                            <!-- Asocia la etiqueta a la colonia obligatoria. -->
                                            <label class="form-label fw-semibold" for="colonia">Colonia <span class="text-danger fw-bold">(*)</span></label>
                                            <!-- Captura la colonia o conserva N/A si no se conoce. -->
                                            <input class="form-control <?= isset($erroresClienteVista['colonia']) ? 'is-invalid' : '' ?>" id="colonia" name="colonia" type="text" maxlength="255" placeholder="N/A" required data-validacion-cliente value="<?= $escaparCliente((string) ($datosClienteVista['colonia'] ?? 'N/A')) ?>" aria-describedby="ayuda-colonia-cliente error-colonia-cliente">
                                            <!-- Indica que N/A satisface el campo cuando no existe la colonia. -->
                                            <div class="form-text" id="ayuda-colonia-cliente">Si no existe, deja N/A.</div>
                                            <!-- Muestra el error de obligatoriedad, longitud o contenido de la colonia. -->
                                            <div class="invalid-feedback" id="error-colonia-cliente" data-error-cliente="colonia"><?= $escaparCliente((string) ($erroresClienteVista['colonia'] ?? 'Ingresa la colonia o escribe N/A si no existe.')) ?></div>
                                        <!-- Cierra el campo de colonia. -->
                                        </div>
                                        <!-- Coloca el código postal en la segunda columna. -->
                                        <div class="col-12 col-md-6">
                                            <!-- Marca como obligatorio el código postal de cinco dígitos. -->
                                            <label class="form-label fw-semibold" for="codigo_postal">Código postal <span class="text-danger fw-bold">(*)</span></label>
                                            <!-- Conserva ceros iniciales y limita el valor a cinco dígitos. -->
                                            <input class="form-control <?= isset($erroresClienteVista['codigo_postal']) ? 'is-invalid' : '' ?>" id="codigo_postal" name="codigo_postal" type="text" inputmode="numeric" pattern="[0-9]{5}" minlength="5" maxlength="5" placeholder="Ej. 06000" required data-validacion-cliente value="<?= $escaparCliente((string) ($datosClienteVista['codigo_postal'] ?? '')) ?>" aria-describedby="ayuda-codigo-postal error-codigo-postal">
                                            <!-- Explica la longitud y los caracteres permitidos. -->
                                            <div class="form-text" id="ayuda-codigo-postal"> Exactamente 5 dígitos.</div>
                                            <!-- Muestra el error específico de código postal. -->
                                            <div class="invalid-feedback" id="error-codigo-postal" data-error-cliente="codigo_postal"><?= $escaparCliente((string) ($erroresClienteVista['codigo_postal'] ?? 'Debe contener exactamente 5 dígitos, sin letras ni símbolos.')) ?></div>
                                        <!-- Cierra el campo de código postal. -->
                                        </div>
                                        <!-- Coloca la ciudad en la primera columna. -->
                                        <div class="col-12 col-md-6">
                                            <!-- Asocia la etiqueta a la ciudad obligatoria. -->
                                            <label class="form-label fw-semibold" for="ciudad">Ciudad <span class="text-danger fw-bold">(*)</span></label>
                                            <!-- Captura la ciudad o conserva N/A si no se conoce. -->
                                            <input class="form-control <?= isset($erroresClienteVista['ciudad']) ? 'is-invalid' : '' ?>" id="ciudad" name="ciudad" type="text" maxlength="100" placeholder="N/A" required data-validacion-cliente value="<?= $escaparCliente((string) ($datosClienteVista['ciudad'] ?? 'N/A')) ?>" aria-describedby="ayuda-ciudad-cliente error-ciudad-cliente">
                                            <!-- Indica que N/A satisface el campo cuando no existe la ciudad. -->
                                            <div class="form-text" id="ayuda-ciudad-cliente">Si no existe, deja N/A.</div>
                                            <!-- Muestra el error de obligatoriedad, longitud o contenido de la ciudad. -->
                                            <div class="invalid-feedback" id="error-ciudad-cliente" data-error-cliente="ciudad"><?= $escaparCliente((string) ($erroresClienteVista['ciudad'] ?? 'Ingresa la ciudad o escribe N/A si no existe.')) ?></div>
                                        <!-- Cierra el campo de ciudad. -->
                                        </div>
                                        <!-- Cierra la cuadrícula responsiva de campos del formulario. -->
                                    </div>
                                    <!-- Presenta cancelar y guardar con las clases de botones del módulo de productos. -->
                                    <div class="d-flex flex-column flex-sm-row justify-content-end gap-2 pt-2 border-top">
                                        <!-- Regresa al listado de clientes sin enviar el formulario. -->
                                        <a class="btn btn-outline-secondary px-4" href="index.php?c=cliente&amp;a=consulta">Cancelar</a>
                                        <!-- Envía los datos al controlador para su validación definitiva. -->
                                        <button class="btn btn-primary px-4" type="submit">Guardar Cliente</button>
                                    <!-- Cierra la fila de acciones. -->
                    </div>
                <!-- Cierra el formulario de alta. -->
                </form>
                    <!-- Cierra el cuerpo de tarjeta común. -->
                    </div>
                <!-- Cierra la tarjeta de registro y consulta. -->
                </section>
        <!-- Cierra el contenido principal. -->
        </main>
    <!-- Cierra el layout general. -->
    </div>
<!-- Cierra el cuerpo del documento. -->
</body>
<!-- Cierra el documento HTML. -->
</html>
