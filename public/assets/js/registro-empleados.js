// Espera a que el navegador cree los elementos del formulario antes de asociar comportamientos.
document.addEventListener('DOMContentLoaded', () => { // Evita enlazar controles que todavía no existen en el documento.
    // Localiza el selector cuyo valor define los permisos visibles del empleado.
    const selectorRol = document.querySelector('[data-selector-rol]'); // Mantiene la dependencia de rol explícita en el marcado.
    // Localiza las casillas que representan las capacidades disponibles por rol.
    const casillasPermisos = Array.from(document.querySelectorAll('[data-permiso-roles]')); // Facilita actualizar todas las opciones con un solo recorrido.
    // Localiza el texto auxiliar que explica la selección de permisos.
    const ayudaPermisos = document.querySelector('[data-ayuda-permisos]'); // Permite informar el estado actual del grupo de casillas.
    // Localiza el formulario que envía los datos de alta al Front Controller.
    const formularioRegistro = document.querySelector('[data-validacion-registro]'); // Evita buscar controles globales fuera de esta pantalla.

    // Detiene la inicialización cuando la vista actual no contiene el selector de rol.
    if (!selectorRol) { // Permite compartir el archivo sin provocar errores en páginas distintas.
        return; // No registra eventos si no hay formulario de registro.
    } // Finaliza la comprobación inicial del selector.

    // Habilita solamente las casillas asociadas al rol actualmente seleccionado.
    const actualizarPermisosPorRol = () => { // Reacciona al cambio de rol y a la selección inicial del formulario.
        // Lee el código de rol actualmente seleccionado.
        const rolSeleccionado = selectorRol.value; // Usa el valor ENUM enviado al controlador.
        // Recorre las casillas para comparar el rol con la lista estática de cada permiso.
        casillasPermisos.forEach((casilla) => { // Evalúa cada permiso de forma independiente.
            // Convierte la lista separada por comas en un arreglo de roles permitidos.
            const rolesPermitidos = (casilla.dataset.permisoRoles ?? '').split(',').filter(Boolean); // Ignora valores vacíos del atributo de datos.
            // Comprueba si el rol actual puede activar esta capacidad.
            const permisoDisponible = rolesPermitidos.includes(rolSeleccionado); // No concede opciones a roles desconocidos o aún no seleccionados.
            // Bloquea o habilita la casilla de acuerdo con el rol elegido.
            casilla.disabled = !permisoDisponible; // Evita enviar opciones ajenas al rol seleccionado.
            // Desmarca cualquier opción que deje de corresponder al rol actual.
            if (!permisoDisponible) { // Impide conservar un permiso incompatible tras cambiar el rol.
                casilla.checked = false; // Elimina la selección no autorizada en el navegador.
            } // Finaliza la limpieza de permisos no disponibles.
        }); // Finaliza la actualización de las casillas.
        // Actualiza la ayuda visible con el nombre legible del rol cuando hay selección.
        if (ayudaPermisos) { // Verifica que el texto auxiliar esté en la vista.
            ayudaPermisos.textContent = rolSeleccionado ? 'Marca las capacidades disponibles para el rol seleccionado.' : 'Selecciona un rol para activar sus permisos disponibles.'; // Informa cómo usar la grilla sin ocultar la nota de persistencia.
        } // Finaliza la actualización de ayuda.
    }; // Finaliza la función que sincroniza permisos y rol.

    // Asocia la actualización de permisos con el cambio del selector de rol.
    selectorRol.addEventListener('change', actualizarPermisosPorRol); // Mantiene la lógica de controles fuera del HTML.
    // Configura inicialmente la grilla, incluso cuando el formulario vuelve tras errores.
    actualizarPermisosPorRol(); // Habilita solo permisos permitidos por el rol seleccionado.

    // Aplica validación de navegador y estados visuales Bootstrap cuando existe el formulario.
    if (formularioRegistro) { // Verifica la presencia del formulario antes de enlazar el envío.
        // Revisa los campos obligatorios durante el envío del formulario.
        formularioRegistro.addEventListener('submit', (evento) => { // Mantiene toda interacción JavaScript en este archivo externo.
            // Comprueba las restricciones HTML de cada campo antes de enviar al servidor.
            const formularioValido = formularioRegistro.checkValidity(); // Replica visualmente la validación que también aplica el controlador.
            // Muestra las indicaciones visuales de Bootstrap en los controles requeridos.
            formularioRegistro.classList.add('was-validated'); // Activa los estados de validación después del primer intento.
            // Cancela el POST local cuando falta un campo requerido o su tipo no es válido.
            if (!formularioValido) { // Permite que el usuario corrija los campos antes de llegar al servidor.
                evento.preventDefault(); // Impide el envío mientras exista una validación fallida.
                evento.stopPropagation(); // Evita que otros manejadores procesen el envío inválido.
            } // Finaliza el bloqueo local de formularios inválidos.
        }); // Finaliza el manejador de envío de registro.
    } // Finaliza la validación opcional del formulario.
}); // Finaliza la inicialización de registro de empleados.
