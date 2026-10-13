// Espera a que el documento esté listo antes de enlazar los controles de la tabla.
document.addEventListener('DOMContentLoaded', () => { // Evita consultar elementos antes de que la vista exista.
    // Localiza el campo de búsqueda definido por la plantilla de empleados.
    const campoBusqueda = document.querySelector('[data-busqueda-empleados]'); // Mantiene desacoplados el comportamiento y el HTML.
    // Obtiene las filas reales que contienen registros de empleados.
    const filasEmpleados = Array.from(document.querySelectorAll('[data-fila-empleado]')); // Excluye la fila informativa de resultados vacíos.
    // Obtiene la fila que comunica que no hay resultados para la búsqueda actual.
    const filaSinResultados = document.querySelector('[data-sin-resultados]'); // Reutiliza una única fila para lista vacía o filtro sin coincidencias.

    // Detiene la inicialización si la página no contiene el buscador o la tabla esperada.
    if (!campoBusqueda || !filaSinResultados) { // Permite reutilizar el archivo en otras páginas sin errores.
        return; // No añade manejadores cuando los elementos del módulo no están presentes.
    } // Finaliza la comprobación de los elementos necesarios.

    // Define una función que normaliza texto para comparar mayúsculas y acentos de forma consistente.
    const normalizarTexto = (texto) => texto.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLocaleLowerCase('es'); // Facilita buscar tanto "Almacen" como "Almacén".

    // Filtra filas por los términos disponibles en nombre, usuario, rol y teléfono.
    const filtrarEmpleados = () => { // Se invoca cada vez que cambia el campo de búsqueda.
        // Normaliza y recorta la consulta escrita por la persona administradora.
        const termino = normalizarTexto(campoBusqueda.value.trim()); // Evita que espacios y acentos impidan encontrar coincidencias.
        // Inicializa el contador de filas que permanecen visibles.
        let cantidadCoincidencias = 0; // Permite mostrar un estado vacío si el filtro no encuentra empleados.

        // Revisa cada fila para determinar si coincide con la consulta actual.
        filasEmpleados.forEach((fila) => { // Recorre solo los registros reales recibidos desde PDO.
            // Lee los términos preparados en el marcado por el controlador de vista.
            const terminosFila = normalizarTexto(fila.dataset.terminos ?? fila.textContent); // Usa el texto de fila como alternativa accesible.
            // Decide si la fila debe permanecer visible según el término normalizado.
            const coincide = terminosFila.includes(termino); // Usa una comparación parcial para buscar nombres y usuarios.
            // Muestra u oculta la fila sin recargar la página.
            fila.hidden = !coincide; // Conserva los datos actuales del servidor y filtra solo la presentación.
            // Incrementa el total visible cuando existe una coincidencia.
            if (coincide) { // Actualiza el contador únicamente para las filas encontradas.
                cantidadCoincidencias += 1; // Cuenta un empleado que coincide con el filtro.
            } // Finaliza el conteo de coincidencias.
        }); // Finaliza la revisión de todos los empleados.

        // Muestra el estado vacío cuando la tabla no tiene filas o el filtro no obtuvo coincidencias.
        filaSinResultados.hidden = cantidadCoincidencias > 0; // No deja una fila vacía visible junto con resultados.
    }; // Finaliza la función de filtrado local.

    // Actualiza los resultados mientras se escribe en el campo de búsqueda.
    campoBusqueda.addEventListener('input', filtrarEmpleados); // Evita recargas y no inserta eventos en línea.

    // Ejecuta una primera vez el filtro para mantener correcto el estado inicial de lista vacía.
    filtrarEmpleados(); // Asegura que la fila vacía se comporte bien cuando el conjunto está inicialmente vacío.
}); // Finaliza la inicialización de la búsqueda de empleados.
