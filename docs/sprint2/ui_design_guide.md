# Guía de Diseño de Interfaz (UI) - Sprint 2

## 1. Lineamientos de Navegación por Parámetros Simples
Las nuevas vistas deben mapearse en `public/index.php` bajo las siguientes rutas de control:
- Registro de Clientes: `index.php?c=cliente&a=registro`
- Catálogo de Stock: `index.php?c=inventario&a=catalogo`
- Punto de Venta: `index.php?c=venta&a=nueva`
- Pedidos Pendientes: `index.php?c=pedido&a=pendientes`

---

## 2. Comportamiento Visual de las Pantallas (Mapeo de Figma)

### Vista: Punto de Venta (`app/views/ventas/nueva.php`)
- **Estructura:** Pantalla dividida. Panel superior con buscador de productos por nombre e input numérico de cantidad. Al presionar "Agregar", el elemento se renderiza en una tabla dinámica inferior.
- **Campos de la Tabla:** Producto | Precio Unitario | Cantidad (Kg) | Subtotal | Acción (Botón para eliminar de la lista).
- **Cierre de Nota:** Caja inferior derecha que calcula de forma automática el Total General usando clases de Bootstrap `.fw-bold` y el botón destacado "Confirmar venta".

### Vista: Registro de Clientes (`app/views/clientes/registro.php`)
- Formulario limpio usando la paleta cálida del proyecto. Campos `Nombre / Razón Social (*)` y `Teléfono (*)` obligatorios con validación visual en rojo `.is-invalid` si se procesan en blanco.

### Vista: Pedidos en Almacén (`app/views/pedidos/pendientes.php`)
- Tarjetas colapsables de Bootstrap (`.card` con contenedores `.collapse`). El encabezado de la tarjeta muestra el folio de la nota y la fecha. Al hacer clic, se expande hacia abajo mostrando de forma detallada una lista limpia de los cortes de carne y los kilogramos exactos que el almacenista debe empaquetar.
