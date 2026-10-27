<?php
// Lee el rol desde la sesión autenticada para controlar qué módulos aparecen.
$rolMenuLateral = (string) ($_SESSION['user']['rol'] ?? ''); // No usa permisos enviados por la URL ni por formularios.
// Obtiene la ruta MVC actual para resaltar la opción abierta.
$controladorMenuLateral = (string) ($_GET['c'] ?? ''); // Usa únicamente rutas conocidas por el enrutador.
// Obtiene la acción actual para identificar el enlace activo.
$accionMenuLateral = (string) ($_GET['a'] ?? ''); // No cambia las reglas de autorización del servidor.
// Activa la persistencia únicamente en las barras claras de los módulos.
$menuLateralPersisteEstado = empty($sesionIniciadaVista); // Deja el menú oscuro del inicio con su comportamiento actual.
// Obtiene el identificador de sesión del usuario para separar las preferencias de cada cuenta.
$idUsuarioMenuLateral = (int) ($_SESSION['user']['id_empleado'] ?? 0); // Nunca usa un identificador controlado por la URL.
// Define los roles que pueden operar productos e inventario.
$rolesInventarioMenu = ['ALMACEN', 'ADMINISTRADOR']; // Coincide con la protección de ProductoController.
// Define los roles que pueden consultar el catálogo de productos.
$rolesCatalogoMenu = ['ALMACEN', 'VENTAS', 'ADMINISTRADOR']; // Coincide con el acceso fijo de InventarioController.
// Define los roles que pueden registrar clientes y ventas.
$rolesVentasMenu = ['VENTAS', 'ADMINISTRADOR']; // Mantiene las funciones comerciales dentro de sus roles.
// Define los roles que pueden consultar pedidos pendientes.
$rolesPedidosMenu = ['ALMACEN', 'ADMINISTRADOR']; // Coincide con la autorización de PedidoController.
// Define los roles que tienen un perfil personal disponible.
$rolesPerfilMenu = ['ALMACEN', 'VENTAS', 'ADMINISTRADOR', 'DISTRIBUCION', 'REPARTIDOR']; // Incluye todos los perfiles operativos existentes.
?>
<!-- Reúne en todas las pantallas los módulos permitidos para el rol autenticado. -->
<nav class="mt-4" aria-label="Navegación principal" <?= $menuLateralPersisteEstado ? 'data-menu-persistente="modulos" data-usuario-menu="' . $idUsuarioMenuLateral . '"' : '' ?>>
    <!-- Muestra la categoría de inventario solo a almacén y administración. -->
    <?php if (in_array($rolMenuLateral, $rolesInventarioMenu, true)): ?>
        <!-- Abre o cierra el grupo de acciones de inventario. -->
        <button class="boton-categoria-navegacion titulo-navegacion small fw-semibold text-uppercase" type="button" data-menu-desplegable aria-expanded="false" aria-controls="menu-inventario">Inventario</button>
        <!-- Agrupa el registro y la edición de productos. -->
        <div class="grupo-navegacion-contenido" id="menu-inventario">
            <!-- Enlaza al alta de producto e inventario inicial. -->
            <a class="enlace-navegacion d-flex align-items-center gap-2 rounded-3 px-3 py-2 text-decoration-none <?= $controladorMenuLateral === 'producto' && $accionMenuLateral === 'nuevo' ? 'activo' : '' ?>" href="index.php?c=producto&amp;a=nuevo" <?= $controladorMenuLateral === 'producto' && $accionMenuLateral === 'nuevo' ? 'aria-current="page"' : '' ?>><span>Registrar Producto</span></a>
            <!-- Enlaza a la búsqueda y edición del catálogo interno. -->
            <a class="enlace-navegacion d-flex align-items-center gap-2 rounded-3 px-3 py-2 text-decoration-none <?= $controladorMenuLateral === 'producto' && $accionMenuLateral === 'editar' ? 'activo' : '' ?>" href="index.php?c=producto&amp;a=editar" <?= $controladorMenuLateral === 'producto' && $accionMenuLateral === 'editar' ? 'aria-current="page"' : '' ?>><span>Editar Producto</span></a>
        <!-- Cierra el grupo de inventario. -->
        </div>
    <!-- Oculta inventario a los roles sin acceso. -->
    <?php endif; ?>
    <!-- Muestra el catálogo a almacén, ventas y administración. -->
    <?php if (in_array($rolMenuLateral, $rolesCatalogoMenu, true)): ?>
        <!-- Abre o cierra el grupo de consulta de productos. -->
        <button class="boton-categoria-navegacion titulo-navegacion small fw-semibold text-uppercase" type="button" data-menu-desplegable aria-expanded="false" aria-controls="menu-catalogo">Catálogo</button>
        <!-- Agrupa el acceso al catálogo de productos con existencias. -->
        <div class="grupo-navegacion-contenido" id="menu-catalogo">
            <!-- Enlaza a la consulta disponible para todos los roles autorizados. -->
            <a class="enlace-navegacion d-flex align-items-center gap-2 rounded-3 px-3 py-2 text-decoration-none <?= $controladorMenuLateral === 'inventario' && $accionMenuLateral === 'catalogo' ? 'activo' : '' ?>" href="index.php?c=inventario&amp;a=catalogo" <?= $controladorMenuLateral === 'inventario' && $accionMenuLateral === 'catalogo' ? 'aria-current="page"' : '' ?>><span>Catálogo de productos</span></a>
        <!-- Cierra el grupo Catálogo. -->
        </div>
    <!-- Oculta el catálogo a roles sin acceso. -->
    <?php endif; ?>
    <!-- Muestra las acciones de ventas solo a ventas y administración. -->
    <?php if (in_array($rolMenuLateral, $rolesVentasMenu, true)): ?>
        <!-- Abre o cierra el grupo comercial. -->
        <button class="boton-categoria-navegacion titulo-navegacion small fw-semibold text-uppercase" type="button" data-menu-desplegable aria-expanded="false" aria-controls="menu-ventas">Ventas</button>
        <!-- Agrupa clientes, venta por cliente y venta de mostrador. -->
        <div class="grupo-navegacion-contenido" id="menu-ventas">
            <!-- Enlaza al formulario de registro de clientes. -->
            <a class="enlace-navegacion d-flex align-items-center gap-2 rounded-3 px-3 py-2 text-decoration-none <?= $controladorMenuLateral === 'cliente' && $accionMenuLateral === 'registro' ? 'activo' : '' ?>" href="index.php?c=cliente&amp;a=registro" <?= $controladorMenuLateral === 'cliente' && $accionMenuLateral === 'registro' ? 'aria-current="page"' : '' ?>><span>Registrar Nuevo Cliente</span></a>
            <!-- Enlaza a la selección de clientes previa a registrar una venta. -->
            <a class="enlace-navegacion d-flex align-items-center gap-2 rounded-3 px-3 py-2 text-decoration-none <?= ($controladorMenuLateral === 'cliente' && $accionMenuLateral === 'consulta') || ($controladorMenuLateral === 'venta' && $accionMenuLateral === 'nueva-cliente') ? 'activo' : '' ?>" href="index.php?c=cliente&amp;a=consulta" <?= ($controladorMenuLateral === 'cliente' && $accionMenuLateral === 'consulta') || ($controladorMenuLateral === 'venta' && $accionMenuLateral === 'nueva-cliente') ? 'aria-current="page"' : '' ?>><span>Registrar Venta de Clientes</span></a>
            <!-- Enlaza al formulario de venta local con Público General fijo. -->
            <a class="enlace-navegacion d-flex align-items-center gap-2 rounded-3 px-3 py-2 text-decoration-none <?= $controladorMenuLateral === 'venta' && $accionMenuLateral === 'nueva' ? 'activo' : '' ?>" href="index.php?c=venta&amp;a=nueva" <?= $controladorMenuLateral === 'venta' && $accionMenuLateral === 'nueva' ? 'aria-current="page"' : '' ?>><span>Registrar Venta Mostrador</span></a>
        <!-- Cierra el grupo Ventas. -->
        </div>
    <!-- Oculta Ventas a roles sin acceso. -->
    <?php endif; ?>
    <!-- Muestra pedidos pendientes solo a almacén y administración. -->
    <?php if (in_array($rolMenuLateral, $rolesPedidosMenu, true)): ?>
        <!-- Abre o cierra el grupo de pedidos. -->
        <button class="boton-categoria-navegacion titulo-navegacion small fw-semibold text-uppercase" type="button" data-menu-desplegable aria-expanded="false" aria-controls="menu-pedidos">Pedidos</button>
        <!-- Agrupa la preparación de pedidos. -->
        <div class="grupo-navegacion-contenido" id="menu-pedidos">
            <!-- Enlaza a la consulta operativa de almacén. -->
            <a class="enlace-navegacion d-flex align-items-center gap-2 rounded-3 px-3 py-2 text-decoration-none <?= $controladorMenuLateral === 'pedido' && $accionMenuLateral === 'pendientes' ? 'activo' : '' ?>" href="index.php?c=pedido&amp;a=pendientes" <?= $controladorMenuLateral === 'pedido' && $accionMenuLateral === 'pendientes' ? 'aria-current="page"' : '' ?>><span>Pedidos Pendientes</span></a>
        <!-- Cierra el grupo Pedidos. -->
        </div>
    <!-- Oculta Pedidos a roles sin acceso. -->
    <?php endif; ?>
    <!-- Muestra seguridad y acceso a los roles definidos por el sistema. -->
    <?php if (in_array($rolMenuLateral, $rolesPerfilMenu, true)): ?>
        <!-- Abre o cierra la administración de cuentas y el perfil personal. -->
        <button class="boton-categoria-navegacion titulo-navegacion small fw-semibold text-uppercase" type="button" data-menu-desplegable aria-expanded="false" aria-controls="menu-seguridad">Seguridad y acceso</button>
        <!-- Agrupa las opciones administrativas y personales. -->
        <div class="grupo-navegacion-contenido" id="menu-seguridad">
            <!-- Limita la lista y el alta de empleados al administrador. -->
            <?php if ($rolMenuLateral === 'ADMINISTRADOR'): ?>
                <!-- Enlaza al listado de cuentas registradas. -->
                <a class="enlace-navegacion d-flex align-items-center gap-2 rounded-3 px-3 py-2 text-decoration-none <?= $controladorMenuLateral === 'empleado' && $accionMenuLateral === 'lista' ? 'activo' : '' ?>" href="index.php?c=empleado&amp;a=lista" <?= $controladorMenuLateral === 'empleado' && $accionMenuLateral === 'lista' ? 'aria-current="page"' : '' ?>><span>Lista de Usuarios</span></a>
                <!-- Enlaza al alta de personal. -->
                <a class="enlace-navegacion d-flex align-items-center gap-2 rounded-3 px-3 py-2 text-decoration-none <?= $controladorMenuLateral === 'empleado' && $accionMenuLateral === 'registro' ? 'activo' : '' ?>" href="index.php?c=empleado&amp;a=registro" <?= $controladorMenuLateral === 'empleado' && $accionMenuLateral === 'registro' ? 'aria-current="page"' : '' ?>><span>Registrar Empleado</span></a>
            <!-- Oculta la administración de empleados a otros roles. -->
            <?php endif; ?>
            <!-- Ofrece el perfil personal a todos los roles operativos. -->
            <a class="enlace-navegacion d-flex align-items-center gap-2 rounded-3 px-3 py-2 text-decoration-none <?= $controladorMenuLateral === 'empleado' && $accionMenuLateral === 'perfil' ? 'activo' : '' ?>" href="index.php?c=empleado&amp;a=perfil" <?= $controladorMenuLateral === 'empleado' && $accionMenuLateral === 'perfil' ? 'aria-current="page"' : '' ?>><span>Mi Perfil</span></a>
        <!-- Cierra el grupo de Seguridad y acceso. -->
        </div>
    <!-- Oculta seguridad a roles fuera del sistema operativo. -->
    <?php endif; ?>
<!-- Cierra la navegación lateral compartida. -->
</nav>
