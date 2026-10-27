<?php
// Activa la comprobación estricta de tipos para la consulta de existencias.
declare(strict_types=1); // Evita conversiones implícitas en el controlador del catálogo.

// Declara el controlador de solo lectura para la disponibilidad de productos.
class InventarioController // Separa la autorización y la presentación de la consulta PDO.
{ // Inicia la definición del controlador.
    // Conserva el modelo de productos que entrega los datos del catálogo.
    private Producto $producto; // Reutiliza el modelo existente sin alterar sus consultas actuales.

    // Valida el acceso del rol comercial o administrativo antes de asignar el modelo.
    public function __construct(Producto $producto) // Recibe el modelo con la conexión PDO configurada.
    { // Inicia la comprobación de acceso a la consulta de ventas.
        // Lee el rol desde la sesión establecida tras la autenticación.
        $rolUsuario = $_SESSION['user']['rol'] ?? ''; // Ignora cualquier rol enviado como parámetro.
        // Define los roles autorizados para consultar el catálogo de productos.
        $rolesConCatalogo = ['ADMINISTRADOR', 'VENTAS', 'ALMACEN']; // Coincide con el acceso fijado para esta opción del menú.
        // Rechaza sesiones ausentes y roles que no pertenecen al catálogo autorizado.
        if (session_status() !== PHP_SESSION_ACTIVE || !isset($_SESSION['user']) || !is_array($_SESSION['user']) || !in_array($rolUsuario, $rolesConCatalogo, true)) { // Autoriza exclusivamente por rol.
            // Devuelve el código HTTP estándar para una solicitud sin autorización.
            http_response_code(403); // Comunica al navegador que el acceso no está permitido.
            // Detiene la solicitud antes de consultar o mostrar datos.
            exit('Acceso denegado: se requiere una sesión activa con rol VENTAS, ALMACEN o ADMINISTRADOR.'); // No expone datos del inventario a roles no autorizados.
        } // Finaliza la validación de sesión y rol permitido.
        // Guarda el modelo después de autorizar la sesión actual.
        $this->producto = $producto; // Habilita la consulta aislada de catálogo con stock.
    } // Finaliza la inicialización del controlador.

    // Consulta los productos existentes con sus cantidades y presenta la vista de catálogo.
    public function catalogo(): void // Atiende index.php?c=inventario&a=catalogo.
    { // Inicia la acción de consulta de catálogo.
        // Recupera el catálogo de productos activos con un registro de inventario relacionado.
        $catalogoProductos = $this->producto->obtenerCatalogoConStock(); // Ejecuta el INNER JOIN preparado del modelo.
        // Conserva la identidad autenticada para la navegación y el pie de sesión.
        $usuarioSesion = $_SESSION['user']; // El constructor verificó previamente la existencia de esta sesión.
        // Renderiza la vista responsiva del catálogo comercial.
        require dirname(__DIR__) . '/views/inventario/catalogo.php'; // Mantiene la presentación fuera de la lógica del controlador.
    } // Finaliza la acción de catálogo.
} // Finaliza la definición del controlador de inventario.
