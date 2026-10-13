// Espera a que el navegador haya creado el documento antes de enlazar los controles.
document.addEventListener('DOMContentLoaded', () => { // Evita buscar elementos antes de que el HTML esté disponible.
    // Recorre los formularios que declaran explícitamente validación del lado del cliente.
    document.querySelectorAll('[data-validacion-cliente]').forEach((formulario) => { // Limita la validación a los formularios de autenticación.
        // Revisa cada envío para marcar errores y evitar la solicitud si faltan datos.
        formulario.addEventListener('submit', (evento) => { // Mantiene la interacción sin atributos de evento en el HTML.
            // Inicializa el estado de validez usando las reglas nativas de los campos HTML.
            let formularioValido = formulario.checkValidity(); // Incluye obligatoriedad y límites declarados en los campos.
            // Obtiene todos los campos obligatorios para activar sus mensajes Bootstrap.
            const camposObligatorios = formulario.querySelectorAll('input[required]'); // Solo inspecciona entradas que el formulario declara requeridas.
            // Marca visualmente los campos vacíos o con valores que no cumplen sus restricciones.
            camposObligatorios.forEach((campo) => { // Recorre las entradas obligatorias individualmente.
                // Establece el estado inválido sin esperar una respuesta del servidor.
                campo.classList.toggle('is-invalid', !campo.checkValidity()); // Usa la clase de error de Bootstrap 5.3.
            }); // Finaliza la revisión de campos obligatorios.
            // Obtiene el campo de contraseña nueva, si el formulario incluye el cambio temporal.
            const contraseniaNueva = formulario.querySelector('[data-contrasenia-nueva]'); // Devuelve null en el formulario de acceso habitual.
            // Obtiene la confirmación, si la pantalla actual solicita repetir la nueva contraseña.
            const confirmacionContrasenia = formulario.querySelector('[data-confirmar-contrasenia]'); // Permite compartir este archivo entre las dos vistas.
            // Comprueba que las dos entradas del cambio temporal coincidan exactamente.
            if (contraseniaNueva && confirmacionContrasenia) { // Ejecuta la regla solo en el formulario temporal.
                // Determina si el texto de confirmación es distinto de la contraseña nueva.
                const contraseniasDistintas = contraseniaNueva.value !== confirmacionContrasenia.value; // Compara los valores sin alterarlos.
                // Marca la confirmación como inválida si está vacía o no coincide.
                confirmacionContrasenia.classList.toggle('is-invalid', !confirmacionContrasenia.checkValidity() || contraseniasDistintas); // Comunica el error con estilos Bootstrap.
                // Añade al estado general la regla de coincidencia de contraseñas.
                formularioValido = formularioValido && !contraseniasDistintas; // Impide enviar claves que no coinciden.
            } // Finaliza la validación de coincidencia cuando se utiliza el formulario temporal.
            // Cancela el envío y evita recargar si algún campo no es válido.
            if (!formularioValido) { // Mantiene los datos en el formulario para que el usuario los corrija.
                evento.preventDefault(); // Bloquea la solicitud HTTP cuando faltan datos o no coinciden.
                evento.stopPropagation(); // Impide que otro manejador procese el formulario inválido.
            } // Finaliza la protección del envío inválido.
            // Activa los mensajes de validación de Bootstrap para los campos del formulario.
            formulario.classList.add('was-validated'); // Muestra las ayudas visuales después del primer intento de envío.
        }); // Finaliza el manejador de envío del formulario.
    }); // Finaliza el recorrido de formularios con validación declarada.

    // Conecta cada botón de visibilidad con el campo de contraseña indicado en su atributo de datos.
    document.querySelectorAll('[data-alternar-contrasenia]').forEach((boton) => { // Mantiene el comportamiento fuera de las vistas PHP.
        // Alterna el tipo del campo entre texto visible y contraseña oculta.
        boton.addEventListener('click', () => { // Responde únicamente a la interacción del botón asociado.
            // Busca el campo objetivo por el identificador incluido en el botón.
            const campo = document.getElementById(boton.dataset.alternarContrasenia); // Evita selectores construidos con contenido arbitrario.
            // Cambia el estado de visibilidad cuando el campo objetivo existe.
            if (campo) { // Evita errores si el botón no apunta a un campo válido.
                // Determina si el campo está actualmente oculto.
                const estaOculto = campo.type === 'password'; // Usa el tipo HTML actual como fuente del estado.
                // Muestra u oculta el texto sin modificar el valor de la contraseña.
                campo.type = estaOculto ? 'text' : 'password'; // Alterna entre visibilidad y protección visual.
                // Actualiza el texto visible para que siempre describa la acción disponible.
                boton.textContent = estaOculto ? 'Ocultar' : 'Mostrar'; // Mantiene el control comprensible sin iconos.
                // Actualiza el nombre accesible del botón según la acción disponible.
                boton.setAttribute('aria-label', estaOculto ? 'Ocultar contraseña' : 'Mostrar contraseña'); // Ayuda a tecnologías de asistencia.
                // Comunica el estado actual del control a los lectores de pantalla.
                boton.setAttribute('aria-pressed', estaOculto ? 'true' : 'false'); // Expone el estado de visibilidad como un interruptor.
            } // Finaliza la comprobación del campo objetivo.
        }); // Finaliza el manejador de alternancia de contraseña.
    }); // Finaliza el recorrido de botones de visibilidad.
}); // Finaliza la inicialización del comportamiento de autenticación.
