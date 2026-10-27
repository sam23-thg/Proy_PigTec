// Espera a que el navegador construya el documento antes de enlazar los controles.
document.addEventListener('DOMContentLoaded', () => { // Evita consultar elementos antes de que exista la tabla.
    // Localiza el campo de búsqueda propio del catálogo de ventas.
    const campoBusqueda = document.querySelector('[data-buscador-catalogo-ventas]'); // Limita el filtro a esta pantalla.
    // Localiza únicamente las filas válidas del catálogo.
    const filasCatalogo = Array.from(document.querySelectorAll('[data-fila-catalogo-ventas]')); // Excluye los encabezados de la búsqueda.
    // Localiza el aviso para términos sin coincidencias.
    const mensajeSinResultados = document.querySelector('[data-mensaje-sin-resultados]'); // Permite mostrar u ocultar el estado vacío.
    // Localiza el contador de filas visibles.
    const conteoCatalogo = document.querySelector('[data-conteo-catalogo]'); // Refleja el resultado actual del filtro.
    // Localiza los botones de filtro derivados de los tipos de corte disponibles.
    const filtrosCategoria = Array.from(document.querySelectorAll('[data-categoria-catalogo]')); // Permite combinar búsqueda con una categoría.
    // Localiza el botón que limpia el término y la categoría seleccionada.
    const botonLimpiarFiltro = document.querySelector('[data-limpiar-filtro-catalogo]'); // Restablece texto y categoría seleccionada.
    // Conserva el tipo seleccionado para combinarlo con la búsqueda textual.
    let categoriaSeleccionada = 'todos'; // Inicia con el catálogo completo visible.

    // Detiene la inicialización si la vista no contiene el buscador esperado.
    if (!campoBusqueda) { // Permite que el archivo se cargue sin modificar otras páginas.
        return; // Evita registrar manejadores cuando no existe el catálogo.
    } // Finaliza la comprobación del campo de búsqueda.

    // Define la normalización común para ignorar mayúsculas y acentos al comparar.
    const normalizarTexto = (texto) => texto.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLocaleLowerCase('es-MX').trim(); // Facilita búsquedas naturales en español.

    // Filtra filas usando a la vez el texto y el tipo de corte seleccionado.
    const filtrarCatalogo = () => { // Reutiliza la misma regla en escritura, categorías y botones.
        // Normaliza el término una vez para usarlo en todas las filas.
        const terminoBusqueda = normalizarTexto(campoBusqueda.value); // Ignora diferencias de acentos, espacios y mayúsculas.
        // Normaliza la categoría actual para compararla con los atributos de las filas.
        const categoriaNormalizada = normalizarTexto(categoriaSeleccionada); // Evita diferencias por acentos o mayúsculas.
        // Inicializa el total que permanecerá visible tras aplicar el filtro.
        let filasVisibles = 0; // Actualiza el contador del catálogo.

        // Revisa cada fila para mostrar solo las que contengan el término.
        filasCatalogo.forEach((filaCatalogo) => { // Evalúa el nombre, corte, precio y stock presentados.
            // Obtiene y normaliza el texto completo de la fila actual.
            const textoFila = normalizarTexto(filaCatalogo.textContent ?? ''); // Evita comparar fragmentos HTML.
            // Compara la fila con el tipo de corte elegido.
            const tipoCorteFila = normalizarTexto(filaCatalogo.dataset.tipoCorte || 'Sin especificar'); // Lee la categoría respaldada por el dato existente.
            const coincideCategoria = categoriaNormalizada === 'todos' || tipoCorteFila === categoriaNormalizada; // Incluye todos los cortes cuando se elige Todos.
            // Determina si fila coincide tanto con texto como con categoría.
            const coincideBusqueda = textoFila.includes(terminoBusqueda) && coincideCategoria; // Combina las dos opciones de filtro.
            // Oculta filas que no contengan el texto buscado.
            filaCatalogo.hidden = !coincideBusqueda; // Actualiza el resultado sin cambiar la URL.
            filaCatalogo.classList.toggle('d-none', !coincideBusqueda); // Refuerza el ocultamiento en tablas Bootstrap.
            // Cuenta las filas que permanecen visibles.
            if (coincideBusqueda) { // Aumenta el total solo para resultados presentes.
                filasVisibles += 1; // Mantiene sincronizado el indicador de resultados.
            } // Finaliza el conteo condicional.
        }); // Finaliza el recorrido de filas filtrables.

        // Actualiza el número de productos mostrados cuando el indicador existe.
        if (conteoCatalogo) { // Mantiene el archivo seguro si el contador no se renderiza.
            conteoCatalogo.textContent = `${filasVisibles} ${filasVisibles === 1 ? 'producto' : 'productos'}`; // Usa singular o plural según el resultado.
        } // Finaliza la actualización del contador.

        // Presenta el aviso únicamente cuando una búsqueda no encuentra coincidencias.
        if (mensajeSinResultados) { // El mensaje no existe cuando el catálogo está vacío desde el servidor.
            const mostrarMensaje = (terminoBusqueda !== '' || categoriaNormalizada !== 'todos') && filasVisibles === 0; // Distingue filtros vacíos del catálogo sin productos.
            mensajeSinResultados.hidden = !mostrarMensaje; // Conserva el estado nativo accesible del aviso.
            mensajeSinResultados.classList.toggle('d-none', !mostrarMensaje); // Asegura que se muestre dentro de la tarjeta.
        } // Finaliza la actualización del estado vacío.
    }; // Finaliza la función conjunta de filtrado.

    // Filtra en vivo cada vez que se cambia el texto del buscador.
    campoBusqueda.addEventListener('input', filtrarCatalogo); // Evita recargar la página para actualizar las filas.

    // Asocia cada botón de tipo de corte al filtro conjunto.
    filtrosCategoria.forEach((botonCategoria) => { // Registra los chips construidos por la vista.
        botonCategoria.addEventListener('click', () => { // Actualiza la lista al elegir una categoría.
            categoriaSeleccionada = botonCategoria.dataset.categoriaCatalogo || 'todos'; // Guarda el valor definido en el botón.
            filtrosCategoria.forEach((boton) => { // Actualiza el estado activo y accesible de cada chip.
                const activo = boton === botonCategoria; // Determina cuál opción se eligió.
                boton.classList.toggle('btn-dark', activo); // Destaca la categoría activa.
                boton.classList.toggle('btn-light', !activo); // Mantiene neutras las demás categorías.
                boton.setAttribute('aria-pressed', String(activo)); // Informa el estado a lectores de pantalla.
            }); // Finaliza la actualización de estados de categoría.
            filtrarCatalogo(); // Combina la categoría elegida con el término ya escrito.
        }); // Finaliza el evento del botón de categoría.
    }); // Finaliza el registro de filtros de categoría.

    // Restablece el buscador y la selección de categoría desde el botón Limpiar.
    if (botonLimpiarFiltro) { // Permite cargar el script aunque no exista ese botón.
        botonLimpiarFiltro.addEventListener('click', () => { // Deja el catálogo completo disponible con una sola acción.
            campoBusqueda.value = ''; // Limpia el término de texto.
            categoriaSeleccionada = 'todos'; // Regresa la categoría a su opción general.
            filtrosCategoria.forEach((botonCategoria) => { // Restablece los estilos y atributos accesibles.
                const activo = botonCategoria.dataset.categoriaCatalogo === 'todos'; // Selecciona de nuevo el filtro general.
                botonCategoria.classList.toggle('btn-dark', activo); // Destaca Todos como opción activa.
                botonCategoria.classList.toggle('btn-light', !activo); // Neutraliza los otros chips.
                botonCategoria.setAttribute('aria-pressed', String(activo)); // Sincroniza el estado para tecnologías de asistencia.
            }); // Finaliza el restablecimiento visual de categorías.
            filtrarCatalogo(); // Muestra todos los productos disponibles.
            campoBusqueda.focus(); // Devuelve el cursor al buscador.
        }); // Finaliza la acción para limpiar filtros.
    } // Finaliza el enlace del botón Limpiar.

    // Inicializa contador, filas y aviso según los controles predeterminados.
    filtrarCatalogo(); // Evita estados desincronizados al cargar la página.
}); // Finaliza la inicialización del catálogo de ventas.
