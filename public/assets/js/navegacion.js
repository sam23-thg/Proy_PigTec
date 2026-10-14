// Espera a que el documento esté disponible antes de enlazar los botones de categoría.
document.addEventListener('DOMContentLoaded', () => {
    // Recorre los controles desplegables de cualquier menú lateral de la aplicación.
    document.querySelectorAll('[data-menu-desplegable]').forEach((botonCategoria) => {
        // Recupera el identificador del grupo asociado con este botón.
        const identificadorGrupo = botonCategoria.getAttribute('aria-controls');
        // Busca el grupo de enlaces que controla el botón actual.
        const grupoEnlaces = document.getElementById(identificadorGrupo);

        // Omite controles incompletos para evitar errores y no afectar otros scripts.
        if (!grupoEnlaces) {
            return;
        }

        // Sincroniza el estado visual y accesible del botón y su grupo de enlaces.
        const actualizarEstado = (estaAbierto) => {
            // Expone el estado actual del grupo a tecnologías de asistencia.
            botonCategoria.setAttribute('aria-expanded', String(estaAbierto));
            // Anima el grupo mediante la clase que controla su altura y opacidad.
            grupoEnlaces.classList.toggle('is-open', estaAbierto);
            // Evita que enlaces ocultos reciban foco o se anuncien como contenido activo.
            grupoEnlaces.inert = !estaAbierto;
            // Expone el estado del contenido a lectores de pantalla.
            grupoEnlaces.setAttribute('aria-hidden', String(!estaAbierto));
        };

        // Inicializa cada grupo a partir del estado declarado en el HTML.
        actualizarEstado(botonCategoria.getAttribute('aria-expanded') === 'true');

        // Cambia el estado del grupo al activar su encabezado con puntero o teclado.
        botonCategoria.addEventListener('click', () => {
            // Invierte el estado desplegado actual de la categoría.
            const estaAbierto = botonCategoria.getAttribute('aria-expanded') !== 'true';
            // Aplica el nuevo estado al botón y su contenido asociado.
            actualizarEstado(estaAbierto);
        });
    });
});
