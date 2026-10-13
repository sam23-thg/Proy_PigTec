// Espera a que el navegador procese todo el marcado HTML antes de inicializar los manejadores de eventos.
document.addEventListener('DOMContentLoaded', () => { // Garantiza que los elementos del DOM existan antes de consultarlos.
    // Localiza el formulario de edición de perfil mediante el selector de atributo de datos específico.
    const formularioPerfil = document.querySelector('[data-formulario-perfil]'); // Obtiene el elemento formulario de la vista.
    // Detiene la ejecución si la vista activa no contiene el formulario de perfil.
    if (!formularioPerfil) { // Previene errores en páginas que no incorporan este formulario.
        return; // Finaliza la función de inicialización de forma segura.
    } // Finaliza la comprobación del formulario.

    // Escucha el evento submit del formulario para coordinar la validación interactiva del lado del cliente.
    formularioPerfil.addEventListener('submit', (evento) => { // Intercepta el intento de envío del usuario.
        // Evalúa si todos los campos requeridos satisfacen las reglas HTML5 de validación.
        const esValido = formularioPerfil.checkValidity(); // Comprueba atributos required, maxlength y patrones.
        // Añade la clase de Bootstrap para detonar los estilos visuales de validación en los controles.
        formularioPerfil.classList.add('was-validated'); // Activa los bordes verdes y rojos de retroalimentación.
        // Si el formulario contiene campos obligatorios vacíos o no válidos, cancela la petición al servidor.
        if (!esValido) { // Detecta la ausencia de datos obligatorios en el cliente.
            // Cancela el evento de envío nativo para evitar la recarga de página con datos faltantes.
            evento.preventDefault(); // Impide el viaje de datos incompletos hacia el servidor.
            // Detiene la propagación del evento para otros posibles escuchadores.
            evento.stopPropagation(); // Evita ejecuciones adicionales en la jerarquía del DOM.
            // Localiza el primer elemento inválido del formulario para llevar el foco visual del usuario hacia él.
            const primerCampoInvalido = formularioPerfil.querySelector(':invalid'); // Encuentra el control con error.
            // Si existe un campo con fallo, traslada el foco del teclado para mejorar la accesibilidad.
            if (primerCampoInvalido) { // Confirma la existencia del primer campo con error.
                primerCampoInvalido.focus(); // Enfoca el control para corregir los datos inmediatamente.
            } // Finaliza la asignación del foco al campo inválido.
        } // Finaliza el bloqueo condicional de formularios inválidos.
    }); // Finaliza el escuchador de submit del formulario.
}); // Finaliza la inicialización de eventos del perfil.
