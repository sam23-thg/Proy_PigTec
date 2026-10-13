// Espera a que el navegador complete la carga del documento HTML antes de asociar los comportamientos.
document.addEventListener('DOMContentLoaded', () => { // Evita consultar elementos antes de que existan en el DOM.
    // Localiza el formulario de registro de nuevo producto mediante su atributo de datos.
    const formularioNuevo = document.querySelector('[data-formulario-producto]'); // Obtiene el formulario de alta.
    // Localiza el formulario de edición de producto si está presente en la vista activa.
    const formularioEditar = document.querySelector('[data-formulario-editar-producto]'); // Obtiene el formulario de edición.
    // Localiza el campo interactivo del buscador de productos en la vista de edición.
    const campoBuscador = document.querySelector('[data-buscador-catalogo]'); // Obtiene el input de búsqueda.
    // Localiza todos los elementos o filas de productos filtrables en el catálogo.
    const itemsCatalogo = Array.from(document.querySelectorAll('[data-item-catalogo]')); // Agrupa los ítems en un arreglo.
    // Localiza el contenedor de aviso cuando no hay resultados en el buscador.
    const avisoSinResultados = document.querySelector('[data-sin-resultados]'); // Obtiene el mensaje de catálogo vacío.
    // Localiza el interruptor de estado activo/inactivo en el formulario de edición.
    const interruptorEstado = document.querySelector('[data-switch-estado]'); // Obtiene el switch de estado.
    // Localiza el texto descriptivo del estado del producto.
    const etiquetaEstado = document.querySelector('[data-etiqueta-estado]'); // Obtiene la etiqueta del estado.

    // Configura la validación interactiva del formulario de nuevo producto.
    if (formularioNuevo) { // Verifica la presencia del formulario de alta en la página.
        // Escucha el evento submit para validar los campos obligatorios antes de enviar.
        formularioNuevo.addEventListener('submit', (evento) => { // Intercepta el envío del formulario.
            // Evalúa si todos los controles cumplen las restricciones requeridas.
            const esValido = formularioNuevo.checkValidity(); // Comprueba atributos required, min y formatos.
            // Aplica la clase de Bootstrap para resaltar los estados válidos e inválidos.
            formularioNuevo.classList.add('was-validated'); // Activa los estilos visuales de validación.
            // Si algún campo obligatorio está vacío o es inválido, detiene el envío.
            if (!esValido) { // Detecta datos faltantes en el cliente.
                // Cancela la petición HTTP nativa para evitar recargar con errores.
                evento.preventDefault(); // Impide el envío incompleto.
                // Detiene la propagación del evento en el árbol del DOM.
                evento.stopPropagation(); // Evita eventos adicionales.
                // Localiza el primer campo con error para orientar el foco del usuario.
                const primerInvalido = formularioNuevo.querySelector(':invalid'); // Encuentra el control con fallo.
                // Si existe el control inválido, enfoca dicho elemento para corrección inmediata.
                if (primerInvalido) { // Confirma la existencia del campo.
                    primerInvalido.focus(); // Establece el foco del teclado.
                } // Finaliza la asignación de foco.
            } // Finaliza la cancelación por formulario inválido.
        }); // Finaliza el escuchador de submit de nuevo producto.
    } // Finaliza la configuración de validación de nuevo producto.

    // Configura la validación interactiva del formulario de edición de producto.
    if (formularioEditar) { // Verifica la presencia del formulario de edición.
        // Escucha el evento submit para validar los datos antes de persistir cambios.
        formularioEditar.addEventListener('submit', (evento) => { // Intercepta el envío de la edición.
            // Evalúa la validez de los campos comerciales y de inventario.
            const esValido = formularioEditar.checkValidity(); // Comprueba restricciones HTML5.
            // Activa las clases visuales de retroalimentación de Bootstrap.
            formularioEditar.classList.add('was-validated'); // Muestra los bordes de éxito o error.
            // Cancela el envío si faltan datos requeridos.
            if (!esValido) { // Detecta campos incompletos.
                // Cancela el envío nativo.
                evento.preventDefault(); // Impide enviar datos erróneos al servidor.
                // Detiene la propagación del evento.
                evento.stopPropagation(); // Detiene la burbuja de eventos.
                // Busca el primer control no válido.
                const primerInvalido = formularioEditar.querySelector(':invalid'); // Localiza el control inválido.
                // Si existe, le asigna el foco.
                if (primerInvalido) { // Confirma el campo con fallo.
                    primerInvalido.focus(); // Coloca el cursor en el campo.
                } // Finaliza la asignación del foco.
            } // Finaliza la comprobación de validez de edición.
        }); // Finaliza el escuchador de submit de edición.
    } // Finaliza la configuración de validación de edición.

    // Configura el filtrado en vivo de productos en el buscador de catálogo.
    if (campoBuscador && itemsCatalogo.length > 0) { // Verifica la existencia del buscador y sus elementos.
        // Define la función para filtrar los elementos del catálogo según el término escrito.
        const filtrarCatalogo = () => { // Ejecuta el filtrado en memoria sin recargar la página.
            // Obtiene el término escrito en minúsculas y sin espacios periféricos.
            const termino = campoBuscador.value.toLowerCase().trim(); // Normaliza la cadena de búsqueda.
            // Inicializa el contador de coincidencias visibles.
            let coincidencias = 0; // Lleva la cuenta de elementos que coinciden.
            // Recorre cada uno de los ítems del catálogo.
            itemsCatalogo.forEach((item) => { // Evalúa cada fila o tarjeta de producto.
                // Lee el texto de búsqueda asociado al ítem o su contenido de texto.
                const textoItem = (item.dataset.terminos || item.textContent || '').toLowerCase(); // Obtiene el texto en minúsculas.
                // Comprueba si el texto del producto contiene el término buscado.
                const coincide = textoItem.includes(termino); // Determina la coincidencia textual.
                // Muestra u oculta el elemento según el resultado de la búsqueda.
                item.style.display = coincide ? '' : 'none'; // Aplica o quita la visualización.
                // Si coincide, incrementa el contador de productos visibles.
                if (coincide) { // Comprueba si el ítem es visible.
                    coincidencias += 1; // Suma una coincidencia al total.
                } // Finaliza el incremento de coincidencias.
            }); // Finaliza el recorrido de ítems de catálogo.
            // Si no hay coincidencias y existe el aviso de sin resultados, lo hace visible.
            if (avisoSinResultados) { // Verifica la existencia del contenedor de aviso.
                avisoSinResultados.style.display = coincidencias === 0 ? 'block' : 'none'; // Muestra u oculta el aviso.
            } // Finaliza el control del aviso de sin resultados.
        }; // Finaliza la función de filtrado.

        // Asocia el evento input al campo buscador para filtrar en tiempo real mientras se escribe.
        campoBuscador.addEventListener('input', filtrarCatalogo); // Reacciona a cada carácter tecleado.
    } // Finaliza la configuración del buscador de catálogo.

    // Configura la actualización dinámica de la etiqueta de texto del interruptor de estado.
    if (interruptorEstado && etiquetaEstado) { // Verifica que existan el switch y su etiqueta.
        // Define la función que actualiza el texto visible según el estado de la casilla.
        const actualizarEtiquetaEstado = () => { // Sincroniza el texto descriptivo con el valor booleano.
            // Comprueba si la casilla del switch está marcada como activa.
            const estaActivo = interruptorEstado.checked; // Lee el estado booleano actual.
            // Actualiza el texto visible entre Activo e Inactivo.
            etiquetaEstado.textContent = estaActivo ? 'Activo' : 'Inactivo'; // Muestra la denominación en español.
            // Actualiza la clase visual para reflejar verde cuando esté activo y gris cuando esté inactivo.
            if (estaActivo) { // Si el producto está activo.
                etiquetaEstado.classList.remove('text-secondary'); // Quita el color gris atenuado.
                etiquetaEstado.classList.add('text-success'); // Aplica color verde de éxito.
            } else { // Si el producto está inactivo.
                etiquetaEstado.classList.remove('text-success'); // Quita el color verde.
                etiquetaEstado.classList.add('text-secondary'); // Aplica color gris atenuado.
            } // Finaliza la actualización de clases visuales.
        }; // Finaliza la función de sincronización de estado.

        // Escucha el evento change del interruptor para actualizar la etiqueta al interactuar.
        interruptorEstado.addEventListener('change', actualizarEtiquetaEstado); // Reacciona a cambios del usuario.
        // Ejecuta una sincronización inicial al cargar la página con el valor precargado.
        actualizarEtiquetaEstado(); // Establece el texto correcto según el valor de base de datos.
    } // Finaliza la configuración del interruptor de estado.
}); // Finaliza la inicialización de eventos de productos.
