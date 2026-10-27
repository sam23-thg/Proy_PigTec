// Espera a que el documento esté disponible antes de enlazar los formularios.
document.addEventListener('DOMContentLoaded', () => { // Evita consultar elementos antes de que se construya el HTML.
    // Localiza el formulario de alta de clientes.
    const formularioCliente = document.querySelector('[data-formulario-cliente]'); // Mantiene el comportamiento limitado a la vista de clientes.
    // Localiza el formulario que consulta clientes por nombre o teléfono.
    const formularioBusqueda = document.querySelector('[data-busqueda-cliente]'); // Permite filtrar la tabla de consulta de clientes.

    // Valida los campos requeridos del formulario de alta cuando este existe.
    if (formularioCliente) { // Permite que el archivo sea seguro en otras vistas.
        // Obtiene cada campo que requiere una regla individual.
        const camposCliente = Array.from(formularioCliente.querySelectorAll('[data-validacion-cliente]')); // Incluye obligatorios y opcionales.
        // Define los límites de texto que deben coincidir con las reglas del servidor.
        const limitesTexto = { nombre_razonsocial: 100, calle: 255, colonia: 255, ciudad: 100 }; // Mantiene cada columna dentro de su tamaño documentado.
        // Define el patrón de caracteres de control que no se permite en texto libre.
        const caracteresControl = /[\u0000-\u001F\u007F-\u009F]/; // Rechaza saltos y controles ocultos.

        // Valida un campo y actualiza su mensaje accesible junto al control.
        const validarCampoCliente = (campo) => { // Comparte una regla por campo para blur, input y submit.
            // Conserva el contenido recortado para comprobar campos de texto.
            const valor = campo.value.trim(); // Evita considerar espacios como un dato obligatorio.
            // Cuenta caracteres Unicode igual que la validación PHP del servidor.
            const longitud = Array.from(valor).length; // No confunde bytes UTF-8 con caracteres visibles.
            // Inicializa el mensaje sin error para los campos que ya son válidos.
            let error = ''; // Se reemplaza únicamente cuando una regla falla.

            // Aplica la regla específica según el nombre del campo HTML.
            switch (campo.name) { // Mantiene las reglas explícitas y revisables.
                // Revisa que la razón social exista y no supere cien caracteres.
                case 'nombre_razonsocial': // El nombre es obligatorio.
                    if (valor === '') { // Detecta el valor ausente.
                        error = 'Ingresa el nombre o razón social.'; // Indica el dato faltante.
                    } else if (longitud > limitesTexto.nombre_razonsocial || caracteresControl.test(valor)) { // Comprueba longitud y caracteres no visibles.
                        error = 'Usa hasta 100 caracteres y evita caracteres de control.'; // Explica la corrección requerida.
                    } // Finaliza las reglas del nombre.
                    break; // Continúa al siguiente campo.
                // Revisa el teléfono como una cadena de diez a quince dígitos.
                case 'telefono': // El teléfono es obligatorio y no admite separadores.
                    if (!/^[0-9]{10,15}$/.test(valor)) { // Rechaza símbolos, letras y longitudes incorrectas.
                        error = valor === '' ? 'Ingresa el teléfono del cliente.' : 'Usa únicamente de 10 a 15 dígitos.'; // Distingue vacío de formato inválido.
                    } // Finaliza las reglas del teléfono.
                    break; // Continúa al siguiente campo.
                // Revisa el código postal obligatorio con exactamente cinco dígitos.
                case 'codigo_postal': // El código postal conserva ceros iniciales como texto.
                    if (!/^[0-9]{5}$/.test(valor)) { // Rechaza espacios, letras, símbolos y longitudes distintas.
                        error = valor === '' ? 'Ingresa el código postal.' : 'Debe contener exactamente 5 dígitos, sin letras ni símbolos.'; // Explica el formato requerido.
                    } // Finaliza las reglas del código postal.
                    break; // Continúa al siguiente campo.
                // Revisa los campos de texto opcionales con su propio máximo de caracteres.
                case 'calle': // La calle es obligatoria, con N/A como alternativa si no existe.
                case 'colonia': // La colonia es obligatoria, con N/A como alternativa si no existe.
                case 'ciudad': // La ciudad es obligatoria, con N/A como alternativa si no existe.
                    if (valor === '') { // Rechaza el campo vacío aunque se manipule el HTML.
                        error = `Ingresa ${campo.name === 'calle' ? 'la calle' : campo.name === 'colonia' ? 'la colonia' : 'la ciudad'} o escribe N/A si no existe.`; // Explica cómo completar un dato desconocido.
                    } else if (longitud > limitesTexto[campo.name] || caracteresControl.test(valor)) { // Limita tamaño y controles incluso si el navegador se manipula.
                        error = `Usa hasta ${limitesTexto[campo.name]} caracteres y evita caracteres de control.`; // Muestra el límite propio de cada campo.
                    } // Finaliza las reglas del campo opcional.
                    break; // Continúa al siguiente campo.
            } // Finaliza la selección de reglas.

            // Entrega el mensaje a la validación nativa del navegador.
            campo.setCustomValidity(error); // Bloquea el submit si el mensaje no está vacío.
            // Marca individualmente el campo según el resultado actual.
            campo.classList.toggle('is-invalid', error !== ''); // Mantiene el borde y el mensaje de error visibles.
            // Indica validez solo después de que la persona interactúe o envíe el formulario.
            campo.classList.toggle('is-valid', error === '' && (campo.dataset.tocado === 'true' || formularioCliente.classList.contains('was-validated'))); // Evita marcar verde campos aún no revisados.
            // Localiza el mensaje asociado a este campo para actualizarlo.
            const mensajeCampo = formularioCliente.querySelector(`[data-error-cliente="${campo.name}"]`); // Mantiene la explicación junto al control correcto.
            // Actualiza el texto sin insertar contenido HTML.
            if (mensajeCampo) { // Comprueba que la vista tenga un contenedor de error.
                mensajeCampo.textContent = error || mensajeCampo.dataset.mensajeOriginal || ''; // Restaura el mensaje general cuando se corrige.
            } // Finaliza la actualización del mensaje.
            // Devuelve si el campo ya cumple sus reglas.
            return error === ''; // Permite que el envío conozca el resultado individual.
        }; // Finaliza la función de validación individual.

        // Guarda el texto inicial que se mostrará al corregir un error.
        camposCliente.forEach((campo) => { // Prepara feedback individual antes de enlazar eventos.
            // Encuentra el feedback correspondiente al campo actual.
            const mensajeCampo = formularioCliente.querySelector(`[data-error-cliente="${campo.name}"]`); // Busca por el nombre validado.
            // Conserva el mensaje de ayuda inicial en un atributo de datos.
            if (mensajeCampo) { // Evita acceso a nodos ausentes.
                mensajeCampo.dataset.mensajeOriginal = mensajeCampo.textContent; // Permite recuperar el texto después de corregir.
            } // Finaliza la preparación del mensaje.
            // Valida el campo al salir para señalar los errores cuanto antes.
            campo.addEventListener('blur', () => { // Mantiene la validación en el JavaScript externo.
                campo.dataset.tocado = 'true'; // Registra que el campo ya se revisó.
                validarCampoCliente(campo); // Muestra su mensaje específico.
            }); // Finaliza el evento blur.
            // Revalida en tiempo real los campos ya revisados o el formulario enviado.
            campo.addEventListener('input', () => { // Permite que el error desaparezca al corregir.
                if (campo.dataset.tocado === 'true' || formularioCliente.classList.contains('was-validated')) { // Evita estados verdes antes de interactuar.
                    validarCampoCliente(campo); // Actualiza el borde y el mensaje de este campo.
                } // Finaliza la validación reactiva.
            }); // Finaliza el evento input.
        }); // Finaliza la preparación individual de los controles.

        // Revisa todos los campos antes de enviar el registro al servidor.
        formularioCliente.addEventListener('submit', (evento) => { // Conserva el procesamiento fuera de atributos HTML.
            // Activa los estados visuales de Bootstrap para toda la tarjeta.
            formularioCliente.classList.add('was-validated'); // Presenta errores de cada campo al primer intento.
            // Ejecuta todas las reglas aunque falle el primer campo.
            const camposValidos = camposCliente.map((campo) => validarCampoCliente(campo)); // Recoge un resultado booleano por control.
            // Comprueba también los atributos HTML nativos del formulario.
            const formularioValido = formularioCliente.checkValidity() && camposValidos.every(Boolean); // Combina required, pattern y reglas propias.
            // Detiene el envío si cualquier campo no es válido.
            if (!formularioValido) { // Mantiene los datos para corregir en la pantalla.
                evento.preventDefault(); // Evita el POST inválido.
                evento.stopPropagation(); // Impide que otro manejador procese el envío.
            } // Finaliza la prevención del formulario inválido.
        }); // Finaliza la validación de registro.
    } // Finaliza la comprobación del formulario de alta.

    // Filtra la tabla al escribir y restaura todas las filas cuando el campo queda vacío.
    if (formularioBusqueda) { // Omite la validación cuando no está presente el formulario de búsqueda.
        // Localiza el campo que contiene el criterio de consulta.
        const campoBusqueda = formularioBusqueda.querySelector('[name="buscar"]'); // No inspecciona campos fuera del formulario correspondiente.
        // Localiza las filas completas que el servidor ya cargó en la tabla.
        const filasClientes = Array.from(document.querySelectorAll('[data-fila-cliente]')); // Permite mostrar de nuevo todos los clientes sin otra solicitud.
        // Localiza el aviso para búsquedas sin coincidencias.
        const filaSinResultados = document.querySelector('[data-sin-resultados]'); // Muestra un estado vacío propio del filtro.
        // Localiza el indicador de cantidad visible.
        const totalClientes = document.querySelector('[data-total-clientes]'); // Sincroniza el total con las filas filtradas.

        // Normaliza acentos y mayúsculas para facilitar búsquedas en español.
        const normalizarBusquedaCliente = (texto) => texto.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLocaleLowerCase('es-MX').trim(); // Compara nombres y teléfonos de manera consistente.

        // Aplica el término actual a las filas existentes en el documento.
        const filtrarClientes = () => { // Reutiliza la misma operación al escribir, borrar o enviar.
            // Convierte el texto escrito a una forma comparable.
            const termino = normalizarBusquedaCliente(campoBusqueda.value); // Ignora acentos, mayúsculas y espacios externos.
            // Conserva solo las filas cuyo nombre o teléfono contiene el término.
            const filasVisibles = filasClientes.filter((fila) => { // Evalúa cada cliente sin recargar la página.
                // Normaliza los datos seguros impresos en la fila actual.
                const textoCliente = normalizarBusquedaCliente(fila.dataset.textoCliente || ''); // Usa únicamente nombre y teléfono.
                // Muestra todas las filas para un término vacío.
                const coincide = termino === '' || textoCliente.includes(termino); // Restaurar vacío equivale al catálogo completo.
                // Oculta únicamente los clientes que no coinciden.
                fila.hidden = !coincide; // Mantiene las filas no coincidentes en el DOM.
                fila.classList.toggle('d-none', !coincide); // Refuerza la ocultación con la utilidad de Bootstrap para filas de tabla.
                // Devuelve el resultado para calcular el total visible.
                return coincide; // Cuenta solo los clientes mostrados.
            }); // Finaliza el filtrado de todas las filas.
            // Actualiza el contador visible en la tarjeta.
            if (totalClientes) { // Protege vistas sin el distintivo de cantidad.
                totalClientes.textContent = `${filasVisibles.length} clientes`; // Refleja los resultados actuales.
            } // Finaliza la actualización del contador.
            // Muestra el aviso cuando existe un término y ninguna fila coincide.
            if (filaSinResultados) { // Protege vistas sin estado de búsqueda vacía.
                const mostrarAviso = termino !== '' && filasVisibles.length === 0; // Solo muestra el aviso al buscar sin coincidencias.
                filaSinResultados.hidden = !mostrarAviso; // Conserva el estado nativo accesible de la fila.
                filaSinResultados.classList.toggle('d-none', !mostrarAviso); // Asegura la visibilidad correcta dentro de la tabla.
            } // Finaliza la actualización del estado vacío.
        }; // Finaliza la función de filtrado.

        // Actualiza la lista inmediatamente en cada cambio del campo.
        if (campoBusqueda) { // Omite los eventos si la vista no contiene el campo esperado.
            campoBusqueda.addEventListener('input', filtrarClientes); // Incluye el botón nativo para borrar el término.
            filtrarClientes(); // Aplica también un término recibido por GET al cargar la vista.

            // Evita recargar la página al presionar Buscar o Enter.
            formularioBusqueda.addEventListener('submit', (evento) => { // Usa el filtrado inmediato para la búsqueda manual también.
                evento.preventDefault(); // Conserva el estado completo de filas en el documento.
                filtrarClientes(); // Aplica el término actual sin esperar otra acción.
            }); // Finaliza el manejo del formulario.
        } // Finaliza la comprobación del campo de búsqueda.
    } // Finaliza la comprobación del formulario de consulta.
}); // Finaliza la inicialización de los comportamientos de clientes.
