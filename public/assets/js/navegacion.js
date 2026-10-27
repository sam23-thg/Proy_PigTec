// Espera a que el documento esté disponible antes de enlazar los botones de categoría.
document.addEventListener('DOMContentLoaded', () => {
    // Busca solamente la navegación clara cuya preferencia debe conservarse entre módulos.
    const menuPersistente = document.querySelector('[data-menu-persistente="modulos"]');
    // Localiza la barra lateral desplazable que contiene el menú persistente.
    const barraPersistente = menuPersistente ? menuPersistente.closest('.barra-empleados') : null;
    // Crea una clave independiente para la preferencia de cada usuario autenticado.
    const claveEstadoMenu = menuPersistente ? `menu-lateral-modulos-v1-${menuPersistente.dataset.usuarioMenu || 'sesion'}` : null;
    // Prepara el estado previo de categorías y desplazamiento cuando el almacenamiento está disponible.
    let estadoMenuGuardado = {};

    // Recupera la preferencia de este usuario sin interrumpir el menú si el navegador bloquea el almacenamiento.
    if (claveEstadoMenu !== null) {
        // Intenta leer y convertir el estado de menú guardado durante una visita anterior.
        try {
            // Obtiene la preferencia de la pestaña actual para conservarla entre cambios de módulo.
            const textoEstadoMenu = sessionStorage.getItem(claveEstadoMenu);
            // Convierte el texto almacenado a un objeto cuando ya existe una preferencia.
            estadoMenuGuardado = textoEstadoMenu ? JSON.parse(textoEstadoMenu) : {};
            // Reemplaza valores inesperados para que el resto de la navegación use un objeto válido.
            if (!estadoMenuGuardado || typeof estadoMenuGuardado !== 'object' || Array.isArray(estadoMenuGuardado)) {
                // Descarta datos mal formados sin bloquear la navegación.
                estadoMenuGuardado = {};
            }
        } catch (errorAlLeerEstado) {
            // Mantiene el estado inicial del HTML si sessionStorage no se puede consultar.
            estadoMenuGuardado = {};
        }

        // Evita animar la apertura inicial para restaurar el desplazamiento con precisión.
        menuPersistente.classList.add('menu-lateral-restaurando');
    }

    // Sincroniza la presentación y accesibilidad de una categoría desplegable.
    const actualizarEstado = (botonCategoria, grupoEnlaces, estaAbierto) => {
        // Expone el estado desplegado actual a tecnologías de asistencia.
        botonCategoria.setAttribute('aria-expanded', String(estaAbierto));
        // Aplica la clase que anima y muestra el grupo cuando la categoría está abierta.
        grupoEnlaces.classList.toggle('is-open', estaAbierto);
        // Retira del orden de tabulación los enlaces que están ocultos.
        grupoEnlaces.inert = !estaAbierto;
        // Informa a lectores de pantalla si el grupo se encuentra oculto.
        grupoEnlaces.setAttribute('aria-hidden', String(!estaAbierto));
    };

    // Persiste el estado abierto y la posición del único menú lateral de módulos.
    const guardarEstadoMenu = () => {
        // Omite el guardado para el menú oscuro de inicio y para estructuras incompletas.
        if (!menuPersistente || !barraPersistente || claveEstadoMenu === null) {
            // No afecta el comportamiento normal del menú sin persistencia.
            return;
        }

        // Prepara el estado individual de cada categoría visible para el rol actual.
        const categoriasAbiertas = {};
        // Recorre solo los botones que pertenecen al menú lateral persistente.
        menuPersistente.querySelectorAll('[data-menu-desplegable]').forEach((botonCategoria) => {
            // Obtiene el identificador estable del grupo controlado por el botón.
            const identificadorGrupo = botonCategoria.getAttribute('aria-controls');
            // Guarda el estado desplegado de la categoría cuando tiene identificador.
            if (identificadorGrupo) {
                // Registra si el usuario dejó abierta o cerrada esta categoría.
                categoriasAbiertas[identificadorGrupo] = botonCategoria.getAttribute('aria-expanded') === 'true';
            }
        });

        // Empaqueta las categorías y la posición vertical para recuperarlas en otro módulo.
        const estadoParaGuardar = {
            categorias: categoriasAbiertas,
            desplazamiento: barraPersistente.scrollTop,
        };

        // Intenta conservar la preferencia en la pestaña actual.
        try {
            // Serializa el estado para almacenarlo como texto en sessionStorage.
            sessionStorage.setItem(claveEstadoMenu, JSON.stringify(estadoParaGuardar));
        } catch (errorAlGuardarEstado) {
            // Mantiene operativa la navegación aunque el almacenamiento esté lleno o bloqueado.
        }
    };

    // Conecta cada categoría desplegable y restaura su estado solo en el menú marcado.
    document.querySelectorAll('[data-menu-desplegable]').forEach((botonCategoria) => {
        // Recupera el identificador del grupo asociado con el botón actual.
        const identificadorGrupo = botonCategoria.getAttribute('aria-controls');
        // Busca el grupo de enlaces que controla este botón.
        const grupoEnlaces = document.getElementById(identificadorGrupo);

        // Omite controles incompletos para evitar errores en otros elementos de la página.
        if (!grupoEnlaces) {
            // Continúa con el siguiente botón disponible.
            return;
        }

        // Comprueba si el botón pertenece a la barra lateral que conserva sus preferencias.
        const perteneceAlMenuPersistente = menuPersistente !== null && menuPersistente.contains(botonCategoria);
        // Consulta el estado guardado de esta categoría cuando existe una preferencia previa.
        const tieneEstadoGuardado = perteneceAlMenuPersistente && Object.prototype.hasOwnProperty.call(estadoMenuGuardado.categorias || {}, identificadorGrupo);
        // Restaura la preferencia previa o conserva el estado cerrado definido por el HTML.
        const estadoInicialAbierto = tieneEstadoGuardado
            ? Boolean(estadoMenuGuardado.categorias[identificadorGrupo])
            : botonCategoria.getAttribute('aria-expanded') === 'true';

        // Aplica el estado inicial a los atributos accesibles y al grupo visual.
        actualizarEstado(botonCategoria, grupoEnlaces, estadoInicialAbierto);

        // Cambia la categoría cuando el usuario activa su encabezado con teclado o puntero.
        botonCategoria.addEventListener('click', () => {
            // Invierte el estado desplegado actual de esta categoría.
            const estaAbierto = botonCategoria.getAttribute('aria-expanded') !== 'true';
            // Aplica el nuevo estado visual y accesible al grupo asociado.
            actualizarEstado(botonCategoria, grupoEnlaces, estaAbierto);
            // Conserva el cambio de preferencia al navegar a otro módulo.
            guardarEstadoMenu();
        });
    });

    // Guarda la última posición del menú mientras el usuario desplaza la barra lateral clara.
    if (menuPersistente && barraPersistente) {
        // Restaura las categorías y la posición después de que el navegador calcule el nuevo diseño.
        window.requestAnimationFrame(() => {
            // Recupera el desplazamiento guardado cuando el usuario ya movió la barra antes.
            const desplazamientoGuardado = Number(estadoMenuGuardado.desplazamiento);
            // Asigna la posición previa cuando el valor leído es numérico y no negativo.
            if (Number.isFinite(desplazamientoGuardado) && desplazamientoGuardado >= 0) {
                // Sitúa el menú en el mismo punto que tenía antes de cambiar de módulo.
                barraPersistente.scrollTop = desplazamientoGuardado;
            }

            // Retira la clase temporal para devolver las animaciones al uso normal.
            window.requestAnimationFrame(() => {
                // Reactiva la transición después de restaurar categorías y desplazamiento.
                menuPersistente.classList.remove('menu-lateral-restaurando');
            });
        });

        // Actualiza el desplazamiento guardado cuando la persona mueve la barra lateral.
        barraPersistente.addEventListener('scroll', guardarEstadoMenu, { passive: true });
        // Guarda el estado justo antes de abrir otro módulo desde un enlace del menú.
        menuPersistente.addEventListener('click', (eventoMenu) => {
            // Identifica un enlace de navegación incluso si se activa desde su elemento hijo.
            const enlaceActivado = eventoMenu.target instanceof Element ? eventoMenu.target.closest('a[href]') : null;
            // Persiste la posición y las categorías cuando el clic navegará a otra pantalla.
            if (enlaceActivado) {
                // Conserva el estado antes de que se descargue el documento actual.
                guardarEstadoMenu();
            }
        });
        // Guarda el último estado como respaldo durante cualquier navegación o cierre de página.
        window.addEventListener('pagehide', guardarEstadoMenu);
        // Conserva el estado en navegadores que no emiten pagehide antes de cambiar de documento.
        window.addEventListener('beforeunload', guardarEstadoMenu);
        // Guarda el estado cuando el navegador oculta la pestaña para navegar o cambiar de contexto.
        document.addEventListener('visibilitychange', () => {
            // Persiste solo al salir de la pestaña para no sobrescribir la posición durante su uso.
            if (document.visibilityState === 'hidden') {
                // Mantiene la última categoría abierta y el scroll para la siguiente vista.
                guardarEstadoMenu();
            }
        });
    }
});
