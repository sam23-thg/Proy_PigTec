// Espera a que exista el contenido HTML antes de preparar el punto de venta.
document.addEventListener('DOMContentLoaded', () => { // Evita acceder a elementos antes de que el navegador los cree.
    // Localiza el formulario que envía la nota de venta.
    const formularioVenta = document.querySelector('[data-formulario-venta]'); // Limita los manejadores a esta vista.
    // Detiene la inicialización cuando el documento no es el punto de venta.
    if (!formularioVenta) { // Permite que el archivo se cargue en páginas que compartan recursos.
        return; // Evita registrar eventos innecesarios.
    } // Finaliza la comprobación del formulario.

    // Localiza el campo de búsqueda de producto.
    const campoBusqueda = formularioVenta.querySelector('[data-buscar-producto]'); // Permite filtrar opciones mientras se escribe.
    // Localiza el identificador oculto del producto elegido.
    const campoIdProducto = formularioVenta.querySelector('[data-id-producto-seleccionado]'); // No acepta que se escriba manualmente un ID.
    // Localiza el menú de sugerencias de producto.
    const listaSugerencias = formularioVenta.querySelector('[data-lista-sugerencias]'); // Contiene botones generados con datos del servidor.
    // Obtiene los botones de opción que representan productos activos.
    const opcionesProducto = Array.from(formularioVenta.querySelectorAll('[data-opcion-producto]')); // Sirve como fuente del buscador.
    // Localiza el campo de kilogramos por agregar.
    const campoCantidad = formularioVenta.querySelector('[data-cantidad-producto]'); // La cantidad se valida antes de agregarla.
    // Localiza el texto breve de kilogramos disponibles para agregar.
    const mensajeDisponibilidad = formularioVenta.querySelector('[data-disponibilidad-producto]'); // Informa el remanente sin mostrar el stock total.
    // Localiza el aviso para errores del panel de captura.
    const avisoProducto = formularioVenta.querySelector('[data-aviso-producto]'); // Informa datos faltantes sin alertas en línea.
    // Localiza la acción que agrega el artículo al carrito.
    const botonAgregar = formularioVenta.querySelector('[data-agregar-producto]'); // Evita enviar la nota antes de completar el carrito.
    // Localiza el botón para eliminar todos los artículos del carrito.
    const botonVaciar = formularioVenta.querySelector('[data-vaciar-carrito]'); // Permite reiniciar el detalle de la nota.
    // Localiza el cuerpo de tabla para representar cada línea.
    const cuerpoCarrito = formularioVenta.querySelector('[data-cuerpo-carrito]'); // Recibe filas creadas mediante DOM seguro.
    // Localiza el elemento que muestra el total de productos.
    const conteoCarrito = formularioVenta.querySelector('[data-conteo-carrito]'); // Actualiza el rótulo del panel.
    // Localiza la celda visual de subtotal.
    const textoSubtotal = formularioVenta.querySelector('[data-subtotal-venta]'); // Presenta el subtotal calculado.
    // Localiza la celda visual del impuesto.
    const textoIva = formularioVenta.querySelector('[data-iva-venta]'); // Presenta el IVA del 16 por ciento.
    // Localiza la celda visual del total final.
    const textoTotal = formularioVenta.querySelector('[data-total-venta]'); // Presenta el importe final de la nota.
    // Localiza el contenedor de campos ocultos que el controlador recibe por POST.
    const detalleOculto = formularioVenta.querySelector('[data-detalle-oculto]'); // Serializa cada línea en un campo independiente.
    // Localiza el selector obligatorio del cliente.
    const selectorCliente = formularioVenta.querySelector('[data-cliente-venta]'); // Comprueba el encabezado antes del envío.
    // Localiza el selector obligatorio del método de pago.
    const selectorPago = formularioVenta.querySelector('[name="metodo_pago"]'); // Comprueba que el método corresponda al ENUM.
    // Localiza el botón que confirma la transacción.
    const botonConfirmar = formularioVenta.querySelector('[data-confirmar-venta]'); // Se desactiva si no hay productos agregados.
    // Localiza la tarjeta del carrito para leer su estado inicial serializado.
    const contenedorCarrito = formularioVenta.querySelector('[data-carrito-inicial]'); // Recupera artículos tras errores del servidor.
    // Localiza el mensaje que corresponde al selector de cliente.
    const errorCliente = formularioVenta.querySelector('[data-error-cliente]'); // Mantiene el error junto al control correcto.
    // Localiza el mensaje que corresponde al método de pago.
    const errorPago = formularioVenta.querySelector('[data-error-pago]'); // Mantiene el error junto al control correcto.
    // Localiza el mensaje que corresponde al buscador de productos.
    const errorBusqueda = formularioVenta.querySelector('[data-error-busqueda-producto]'); // Explica cuándo no se eligió un artículo válido.
    // Localiza el mensaje que corresponde a la cantidad en kilogramos.
    const errorCantidad = formularioVenta.querySelector('[data-error-cantidad]'); // Explica cuándo la cantidad no cumple sus límites.
    // Localiza el mensaje asociado al detalle completo del carrito.
    const errorCarrito = formularioVenta.querySelector('[data-error-carrito]'); // Informa si falta una línea o el trigger rechazó la venta.

    // Inicializa el carrito con el estado que el servidor pudo haber reconstruido.
    let carrito = []; // Mantiene las líneas activas en memoria mientras se edita la nota.
    // Conserva el producto que se eligió en el menú de sugerencias.
    let productoSeleccionado = null; // Requiere elegir un resultado antes de agregar.
    // Inicializa los errores de cantidad devueltos por el controlador.
    let erroresProductos = {}; // Permite destacar la línea que no pasó una comprobación del servidor.
    // Crea un formateador de moneda para presentar precios en pesos mexicanos.
    const formatoMoneda = new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }); // Mantiene el mismo formato en cada subtotal.
    // Crea un formateador que muestra un decimal como mínimo y conserva hasta tres si hacen falta.
    const formatoKilogramos = new Intl.NumberFormat('es-MX', { minimumFractionDigits: 1, maximumFractionDigits: 3 }); // Evita ceros decimales innecesarios.

    // Normaliza texto para que la búsqueda ignore acentos y diferencias de mayúsculas.
    const normalizarTexto = (texto) => texto.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLocaleLowerCase('es-MX').trim(); // Facilita localizar nombres en español.

    // Intenta recuperar el carrito anterior si el controlador lo devolvió después de un error.
    if (contenedorCarrito) { // Omite la lectura cuando el elemento no existe.
        try { // Protege el renderizado ante datos JSON no válidos.
            carrito = JSON.parse(contenedorCarrito.dataset.carritoInicial || '[]'); // Convierte el atributo escapado en artículos.
        } catch (error) { // Maneja un atributo malformado sin detener la pantalla.
            carrito = []; // Inicializa vacío si no se puede leer el estado anterior.
        } // Finaliza la lectura segura del carrito inicial.
    } // Finaliza la recuperación del carrito del servidor.

    // Recupera los errores de stock que el controlador asoció con cada producto.
    if (contenedorCarrito) { // Lee el mapa solo cuando la tarjeta del carrito está disponible.
        try { // Protege la vista ante un atributo de error con formato inesperado.
            erroresProductos = JSON.parse(contenedorCarrito.dataset.erroresProductosIniciales || '{}'); // Convierte los mensajes serializados a un mapa.
        } catch (error) { // Continúa aunque el JSON no se pueda interpretar.
            erroresProductos = {}; // Evita que un dato de presentación detenga el punto de venta.
        } // Finaliza la lectura de errores iniciales.
    } // Finaliza la recuperación de errores de cantidad.

    // Actualiza el estado visual y accesible de un campo de formulario.
    const actualizarEstadoCampo = (campo, elementoError, mensaje, valido) => { // Centraliza las clases de validación Bootstrap.
        // Activa el borde de error solo cuando hay un mensaje pendiente.
        campo.classList.toggle('is-invalid', mensaje !== ''); // Muestra el control con estado inválido.
        // Marca como válido un campo comprobado que no tiene errores.
        campo.classList.toggle('is-valid', mensaje === '' && valido); // Da confirmación visual después de una selección correcta.
        // Sincroniza el atributo de accesibilidad con el estado del control.
        campo.setAttribute('aria-invalid', String(mensaje !== '')); // Informa el estado a lectores de pantalla.
        // Escribe el mensaje como texto para evitar insertar marcado.
        elementoError.textContent = mensaje; // Presenta el motivo de error bajo el campo.
        // Fuerza la visibilidad del mensaje aunque el control esté dentro de un contenedor posicionado.
        elementoError.classList.toggle('d-block', mensaje !== ''); // Garantiza que cada error aparezca junto a su campo.
    }; // Finaliza la actualización visual del control.

    // Comprueba que el cliente seleccionado pertenezca a las opciones visibles.
    const validarCliente = () => { // Mantiene la verificación de la interfaz alineada con el servidor.
        // Comprueba el identificador fijo y revisa la posición solo si el control sigue siendo una lista.
        const valido = selectorCliente.value !== '' && (selectorCliente.tagName !== 'SELECT' || selectorCliente.selectedIndex > 0); // Acepta el cliente de mostrador o el perfil específico fijado por el servidor.
        // Prepara un mensaje específico para el cliente faltante.
        const mensaje = valido ? '' : 'Selecciona un cliente de la lista.'; // Explica cómo corregir el control.
        // Actualiza clases, estado accesible y texto de retroalimentación.
        actualizarEstadoCampo(selectorCliente, errorCliente, mensaje, valido); // Mantiene el error bajo el selector.
        // Devuelve el resultado para detener el envío si hace falta.
        return valido; // Permite validar todos los campos antes de enviar.
    }; // Finaliza la comprobación del cliente.

    // Comprueba que el método de pago sea uno de los valores permitidos.
    const validarPago = () => { // Refuerza en el navegador las opciones permitidas por el servidor.
        // Limita la selección a los métodos que acepta la base de datos.
        const valido = selectorPago.value === 'EFECTIVO'; // Acepta únicamente efectivo mientras los otros métodos estén deshabilitados.
        // Prepara un mensaje específico para el método de pago faltante.
        const mensaje = valido ? '' : 'Selecciona un método de pago válido.'; // Explica cómo corregir el control.
        // Actualiza clases, estado accesible y texto de retroalimentación.
        actualizarEstadoCampo(selectorPago, errorPago, mensaje, valido); // Mantiene el error bajo el selector.
        // Devuelve el resultado para detener el envío si hace falta.
        return valido; // Permite validar todos los campos antes de enviar.
    }; // Finaliza la comprobación del método de pago.

    // Conserva una sola consulta activa para evitar respuestas de stock fuera de orden.
    let solicitudDisponibilidad = null; // Comparte la petición actual entre el temporizador y las acciones del usuario.

    // Consulta al servidor las existencias actuales y actualiza las sugerencias del formulario.
    const actualizarDisponibilidad = () => { // Usa el catálogo de solo lectura protegido por el controlador de ventas.
        // Reutiliza la consulta pendiente si otro evento ya está actualizando el catálogo.
        if (solicitudDisponibilidad) { // Evita disparar solicitudes simultáneas.
            return solicitudDisponibilidad; // Mantiene una única respuesta como fuente de stock.
        } // Finaliza la comprobación de una consulta existente.

        // Inicia la lectura del endpoint JSON sin permitir respuestas almacenadas en caché.
        solicitudDisponibilidad = (async () => { // Mantiene la promesa accesible a los siguientes eventos.
            // Solicita el catálogo vigente con las credenciales de la sesión actual.
            const respuesta = await fetch('index.php?c=venta&a=disponibilidad', { headers: { Accept: 'application/json' }, cache: 'no-store' }); // Reutiliza la sesión autenticada.
            // Rechaza respuestas HTTP fallidas antes de interpretar el cuerpo.
            if (!respuesta.ok) { // Detecta pérdida de sesión o un error del servidor.
                throw new Error('No se pudo consultar la disponibilidad.'); // Conserva el último stock conocido.
            } // Finaliza la comprobación HTTP.
            // Convierte la respuesta JSON del servidor en datos utilizables.
            const datos = await respuesta.json(); // Recibe existencias calculadas por la base de datos.
            // Comprueba que el endpoint haya devuelto una lista válida.
            if (!datos.exito || !Array.isArray(datos.productos)) { // Evita procesar una respuesta incompleta.
                throw new Error('La disponibilidad recibida no es válida.'); // Hace que la operación falle de forma segura.
            } // Finaliza la comprobación de formato JSON.

            // Crea un índice de productos actuales por su identificador.
            const productosActuales = new Map(datos.productos.map((producto) => [String(producto.id_producto), producto])); // Facilita sincronizar opciones y selección.
            // Actualiza la existencia que muestra cada sugerencia del buscador.
            opcionesProducto.forEach((opcion) => { // Conserva la lista de productos generada desde PHP.
                // Busca el producto activo correspondiente a la sugerencia actual.
                const productoActual = productosActuales.get(opcion.dataset.id); // Omite confianza en valores escritos por el usuario.
                // Convierte la existencia actual o cero si el artículo dejó de estar disponible.
                const stockActual = productoActual ? Number(productoActual.cantidad_kg) : 0; // Mantiene la precisión decimal del inventario.
                // Guarda el stock actualizado para cuando el vendedor elija esta opción.
                opcion.dataset.stock = String(Number.isFinite(stockActual) ? stockActual : 0); // Mantiene sincronizada la selección futura.
                // Deshabilita productos que desaparecieron del catálogo o quedaron sin stock.
                opcion.disabled = !productoActual || stockActual <= 0; // Evita agregar artículos sin existencia disponible.
            }); // Finaliza la sincronización de sugerencias.

            // Actualiza las existencias de artículos que ya estén en el carrito.
            carrito.forEach((articulo) => { // Revalida cantidades agregadas mientras la página seguía abierta.
                // Busca el registro más reciente del artículo incluido en el carrito.
                const productoActual = productosActuales.get(String(articulo.id_producto)); // Comprueba que siga activo.
                // Actualiza el dato informativo del artículo desde la base.
                articulo.stock_disponible = productoActual ? Number(productoActual.cantidad_kg) : 0; // Conserva el último stock autorizado.
                // Marca la línea si la existencia actual ya no alcanza para surtirla.
                if (articulo.cantidad_kg > articulo.stock_disponible) { // Detecta ventas concurrentes desde otra sesión.
                    erroresProductos[String(articulo.id_producto)] = 'La existencia cambió y ya no alcanza para esta cantidad; quita el artículo o reduce los kilogramos.'; // Impide confirmar un dato que ya se sabe obsoleto.
                } // Finaliza la comprobación de cantidad contra stock.
            }); // Finaliza la revisión de productos del carrito.

            // Actualiza el producto seleccionado y los kilogramos que todavía se pueden agregar.
            if (productoSeleccionado) { // Solo ajusta controles si el vendedor ya eligió una sugerencia.
                // Obtiene la disponibilidad nueva del producto seleccionado.
                const productoActual = productosActuales.get(String(productoSeleccionado.id_producto)); // Comprueba que siga activo.
                // Sustituye la cantidad informativa por la consultada en esta respuesta.
                productoSeleccionado.stock_disponible = productoActual ? Number(productoActual.cantidad_kg) : 0; // No utiliza un valor anterior del navegador.
                // Busca la cantidad de ese producto que ya se reservó en el carrito local.
                const articuloExistente = carrito.find((articulo) => articulo.id_producto === productoSeleccionado.id_producto); // Evita contar dos veces la existencia.
                // Calcula el remanente disponible para agregar una cantidad adicional.
                const cantidadRestante = Math.max(0, productoSeleccionado.stock_disponible - (articuloExistente ? articuloExistente.cantidad_kg : 0)); // Evita exceder el stock al sumar líneas.
                // Actualiza el límite del campo de kilogramos.
                campoCantidad.max = cantidadRestante.toFixed(3); // Refleja la disponibilidad más reciente.
                // Muestra el remanente con un decimal mínimo y los adicionales necesarios.
                mensajeDisponibilidad.textContent = `Disponible para agregar: ${formatoKilogramos.format(cantidadRestante)} kg`; // Omite el total de existencias del producto.
                // Hace visible el mensaje solo después de seleccionar un producto.
                mensajeDisponibilidad.hidden = false; // Evita mostrar instrucciones sin una selección.
                // Vuelve a validar la cantidad escrita si el vendedor estaba capturándola.
                if (campoCantidad.value !== '') { // No marca error antes de que haya una cantidad.
                    validarCantidad(); // Comprueba el valor contra el máximo vigente.
                } // Finaliza la revalidación de la cantidad.
            } // Finaliza la actualización de la selección actual.

            // Redibuja el carrito cuando alguna cantidad ya no alcanza el stock consultado.
            if (carrito.some((articulo) => Number(articulo.cantidad_kg) > Number(articulo.stock_disponible))) { // Mantiene visibles las alertas por línea.
                renderizarCarrito(); // Actualiza errores, filas y estado del botón de confirmar.
            } // Finaliza la actualización condicional del carrito.
            // Indica que la lectura de existencias finalizó correctamente.
            return true; // Permite continuar con las validaciones de la interfaz.
        })().catch(() => { // Mantiene la pantalla operable sin ocultar que la consulta falló.
            // Devuelve un resultado controlado para que el agregado pueda detenerse.
            return false; // Evita usar stock antiguo al intentar agregar un producto.
        }).finally(() => { // Libera la referencia al terminar la consulta.
            // Permite iniciar una nueva actualización en el próximo intervalo o acción.
            solicitudDisponibilidad = null; // Evita conservar una promesa ya resuelta.
        }); // Finaliza la creación de la consulta compartida.
        // Devuelve la consulta para que los manejadores puedan esperarla.
        return solicitudDisponibilidad; // Sincroniza el temporizador y el agregado.
    }; // Finaliza la consulta de disponibilidad vigente.

    // Comprueba que se haya elegido una sugerencia válida del catálogo.
    const validarProducto = () => { // Un nombre escrito sin selección no identifica una fila del catálogo.
        // Requiere un producto seleccionado y el mismo identificador oculto.
        const valido = productoSeleccionado !== null && campoIdProducto.value === String(productoSeleccionado.id_producto); // Evita enviar texto libre como producto.
        // Define el mensaje específico del buscador.
        const mensaje = valido ? '' : (campoBusqueda.value.trim() === '' ? 'Busca y selecciona un producto.' : 'Elige un producto de las sugerencias.'); // Aclara el siguiente paso.
        // Actualiza clases, estado accesible y texto de retroalimentación.
        actualizarEstadoCampo(campoBusqueda, errorBusqueda, mensaje, valido); // Mantiene el error bajo el buscador.
        // Devuelve el resultado para la acción de agregar o enviar.
        return valido; // No permite usar un producto que no esté en el catálogo.
    }; // Finaliza la comprobación de selección de producto.

    // Comprueba el formato, el rango y el stock informativo de los kilogramos.
    const validarCantidad = () => { // Mantiene el detalle con cantidades positivas y hasta tres decimales.
        // Lee el texto para validar expresamente la cantidad de posiciones decimales.
        const valorTexto = campoCantidad.value.trim(); // Evita convertir una cadena vacía a cero.
        // Comprueba que el valor use hasta siete enteros y tres decimales.
        const formatoValido = /^\d{1,7}(?:\.\d{1,3})?$/.test(valorTexto); // Coincide con la precisión de ticket.cantidad_kg.
        // Convierte el texto a número solo para revisar sus límites.
        const cantidad = Number(valorTexto); // Permite comparar contra los límites seleccionados.
        // Inicializa el mensaje que se mostrará debajo del control.
        let mensaje = ''; // Solo se llena cuando una regla no se cumple.
        // Rechaza el control vacío y las cantidades con formato incorrecto.
        if (valorTexto === '') { // Comprueba el requisito del campo.
            mensaje = 'Indica la cantidad en kilogramos.'; // Señala el dato obligatorio.
        } else if (!formatoValido || !Number.isFinite(cantidad) || cantidad < 0.001) { // Comprueba formato decimal y mínimo positivo.
            mensaje = 'Usa una cantidad mayor que cero y hasta tres decimales.'; // Da el formato esperado.
        } else if (cantidad > Number(campoCantidad.max || 9999999.999)) { // Comprueba el máximo del decimal y stock informativo.
            mensaje = productoSeleccionado ? `La cantidad supera los ${formatoKilogramos.format(Number(campoCantidad.max))} kg disponibles para agregar.` : 'La cantidad excede el máximo permitido.'; // Da el límite que se debe respetar.
        } // Finaliza la evaluación de formato y límites.
        // Actualiza clases, estado accesible y texto de retroalimentación.
        actualizarEstadoCampo(campoCantidad, errorCantidad, mensaje, mensaje === ''); // Mantiene el error bajo la cantidad.
        // Devuelve el resultado para detener la operación si hace falta.
        return mensaje === ''; // Impide agregar kilogramos fuera de rango.
    }; // Finaliza la comprobación de cantidad.

    // Actualiza el mensaje de error que corresponde al carrito completo.
    const actualizarErrorCarrito = (mensaje) => { // Muestra el error debajo de la tabla de artículos.
        // Escribe el mensaje como texto y nunca como HTML.
        errorCarrito.textContent = mensaje; // Conserva los mensajes de servidor y de captura en la vista.
        // Hace visible el bloque Bootstrap solo cuando existe un error.
        errorCarrito.classList.toggle('d-block', mensaje !== ''); // Permite leer el error bajo el detalle.
        // Informa a tecnologías de asistencia si el carrito tiene un error.
        errorCarrito.setAttribute('aria-invalid', String(mensaje !== '')); // Mantiene el estado accesible actualizado.
    }; // Finaliza la actualización del error del carrito.

    // Define una función para reconstruir la tabla, campos POST e importes del carrito.
    const renderizarCarrito = () => { // Mantiene todas las vistas del carrito sincronizadas.
        // Inicializa el subtotal de la nota antes de sumar los artículos.
        let subtotal = 0; // Usa las mismas reglas de redondeo del modelo.
        // Elimina las filas anteriores antes de renderizar el estado actualizado.
        cuerpoCarrito.replaceChildren(); // Evita duplicados al agregar o quitar artículos.
        // Elimina los campos POST viejos para volver a crearlos desde el carrito actual.
        detalleOculto.replaceChildren(); // Envía solo las líneas que permanecen en memoria.
        // Presenta una fila de estado cuando el carrito no contiene artículos.
        if (carrito.length === 0) { // Conserva el mensaje vacío dentro del cuerpo que se acaba de limpiar.
            // Crea el renglón que explica el estado inicial de la nota.
            const filaVacia = document.createElement('tr'); // Mantiene la tabla semántica aun sin productos.
            // Crea la celda única que ocupa todo el ancho de la tabla.
            const celdaVacia = document.createElement('td'); // Aloja la descripción del carrito vacío.
            // Centra y separa el texto del área de captura.
            celdaVacia.className = 'text-center text-secondary py-5'; // Reutiliza las utilidades Bootstrap.
            // Hace que el estado vacío abarque todas las columnas de la tabla.
            celdaVacia.colSpan = 6; // Mantiene la alineación con las seis cabeceras.
            // Informa cómo agregar artículos a la nota.
            celdaVacia.textContent = 'No hay productos agregados. Busca y selecciona un producto para incluirlo en la nota.'; // Usa texto seguro y entendible.
            // Anexa la celda al renglón vacío.
            filaVacia.append(celdaVacia); // Completa la estructura de la tabla.
            // Presenta el estado vacío en la tabla.
            cuerpoCarrito.append(filaVacia); // Muestra el mensaje después de limpiar o al iniciar.
        } // Finaliza la presentación del carrito vacío.

        // Recorre cada producto agregado para crear su fila y su campo de envío.
        carrito.forEach((articulo) => { // Genera elementos de texto en vez de concatenar HTML.
            // Calcula el importe de línea con dos decimales.
            const totalLinea = Math.round((articulo.precio_kg * articulo.cantidad_kg + Number.EPSILON) * 100) / 100; // Coincide con el cálculo transaccional del servidor.
            // Acumula el subtotal de los artículos antes del impuesto.
            subtotal += totalLinea; // Suma cada línea ya redondeada.
            // Crea la fila para el artículo actual.
            const fila = document.createElement('tr'); // Mantiene el marcado construido por DOM.
            // Crea la celda descriptiva del producto.
            const celdaProducto = document.createElement('th'); // Define semántica de encabezado por fila.
            // Marca la celda como encabezado del renglón.
            celdaProducto.scope = 'row'; // Mejora la navegación de lectores de pantalla.
            // Inserta el nombre y el tipo de corte como texto seguro.
            celdaProducto.textContent = articulo.tipo_corte ? `${articulo.nombre_producto} · ${articulo.tipo_corte}` : articulo.nombre_producto; // Evita interpretar nombres como HTML.
            // Crea la celda del precio por kilogramo.
            const celdaPrecio = document.createElement('td'); // Aloja el precio unitario.
            // Formatea el precio con el idioma y moneda de la aplicación.
            celdaPrecio.textContent = formatoMoneda.format(articulo.precio_kg); // Presenta la tarifa vigente.
            // Crea la celda de cantidad del producto.
            const celdaCantidad = document.createElement('td'); // Aloja los kilogramos seleccionados.
            // Presenta la cantidad con hasta tres decimales en un nodo de texto.
            const textoCantidad = document.createElement('span'); // Permite agregar el error debajo de la cantidad.
            // Conserva la precisión de kilogramos que requiere ticket.cantidad_kg.
            textoCantidad.textContent = `${formatoKilogramos.format(Number(articulo.cantidad_kg))} kg`; // Muestra un decimal mínimo y conserva los necesarios.
            // Inserta la cantidad antes de cualquier mensaje asociado a la línea.
            celdaCantidad.append(textoCantidad); // Mantiene la lectura de kilogramos en su columna.
            // Recupera el error que el servidor asoció con este producto.
            const mensajeErrorLinea = String(erroresProductos[String(articulo.id_producto)] || ''); // Vincula el rechazo con su cantidad.
            // Presenta un mensaje específico cuando la cantidad de esta línea no es válida.
            if (mensajeErrorLinea !== '') { // Omite el mensaje cuando la línea pasó la validación.
                // Marca como inválida la celda que contiene la cantidad afectada.
                celdaCantidad.setAttribute('aria-invalid', 'true'); // Informa el estado a lectores de pantalla.
                // Crea el elemento de error asociado a la cantidad de esta fila.
                const errorLinea = document.createElement('div'); // Usa un nodo Bootstrap separado del valor.
                // Muestra el mensaje de error debajo de los kilogramos.
                errorLinea.className = 'invalid-feedback d-block'; // Conserva el estilo de validación del formulario.
                // Inserta el mensaje como texto para evitar interpretar datos externos.
                errorLinea.textContent = mensajeErrorLinea; // Explica por qué debe corregirse la línea.
                // Anexa el error a la celda correspondiente.
                celdaCantidad.append(errorLinea); // Hace visible el error del producto exacto.
            } // Finaliza la presentación del error de cantidad en la fila.
            // Crea la celda del importe de línea.
            const celdaTotalLinea = document.createElement('td'); // Aloja el subtotal del artículo.
            // Formatea el importe calculado para la fila.
            celdaTotalLinea.textContent = formatoMoneda.format(totalLinea); // Mantiene una lectura monetaria uniforme.
            // Crea la celda para la acción de quitar el producto.
            const celdaAccion = document.createElement('td'); // Aloja el botón de eliminación.
            // Crea un botón accesible para quitar esta línea.
            const botonQuitar = document.createElement('button'); // Evita usar atributos de eventos en el HTML.
            // Define el botón como acción local y no como envío.
            botonQuitar.type = 'button'; // Impide que el clic confirme la nota.
            // Aplica las clases discretas de acción secundaria.
            botonQuitar.className = 'btn btn-sm btn-outline-secondary'; // Reutiliza estilos Bootstrap del sistema.
            // Especifica el producto que se eliminará desde el manejador delegado.
            botonQuitar.dataset.quitarProducto = String(articulo.id_producto); // Facilita localizar la línea exacta.
            // Asigna el texto visible y accesible del botón.
            botonQuitar.textContent = 'Quitar'; // Mantiene una etiqueta clara sin iconos decorativos.
            // Describe la acción para tecnologías de asistencia.
            botonQuitar.setAttribute('aria-label', `Quitar ${articulo.nombre_producto} de la venta`); // Agrega contexto al botón repetido.
            // Anida el botón en su celda de acción.
            celdaAccion.append(botonQuitar); // Completa la última columna de la fila.
            // Anexa todas las celdas al renglón actual.
            fila.append(celdaProducto, celdaPrecio, celdaCantidad, celdaTotalLinea, celdaAccion); // Mantiene el orden de las cabeceras.
            // Agrega la fila terminada al cuerpo de tabla.
            cuerpoCarrito.append(fila); // Refresca la representación visual del carrito.
            // Crea el campo oculto para enviar la cantidad de esta línea.
            const campoDetalle = document.createElement('input'); // Serializa el carrito en la estructura POST esperada.
            // Marca el campo como oculto para que solo viaje al servidor.
            campoDetalle.type = 'hidden'; // Evita mostrar controles técnicos en la interfaz.
            // Asigna el nombre anidado que espera VentaController.
            campoDetalle.name = `productos[${articulo.id_producto}][cantidad_kg]`; // Conserva el ID como clave del artículo.
            // Envía la cantidad con tres posiciones decimales.
            campoDetalle.value = Number(articulo.cantidad_kg).toFixed(3); // Mantiene el formato permitido por ticket.
            // Agrega el campo oculto al formulario de venta.
            detalleOculto.append(campoDetalle); // Asegura que todos los artículos lleguen por POST.
        }); // Finaliza el renderizado de líneas.

        // Redondea el subtotal final acumulado.
        subtotal = Math.round((subtotal + Number.EPSILON) * 100) / 100; // Evita residuos de coma flotante.
        // Calcula el IVA del dieciséis por ciento.
        const iva = Math.round((subtotal * 0.16 + Number.EPSILON) * 100) / 100; // Reproduce el resumen mostrado al usuario.
        // Calcula el total que se enviará a la transacción.
        const total = Math.round((subtotal + iva + Number.EPSILON) * 100) / 100; // Suma subtotal e impuesto.
        // Actualiza el rótulo del número de productos agregados.
        conteoCarrito.textContent = `${carrito.length} ${carrito.length === 1 ? 'producto' : 'productos'}`; // Presenta la cantidad de líneas del carrito.
        // Actualiza el resumen monetario del subtotal.
        textoSubtotal.textContent = formatoMoneda.format(subtotal); // Mantiene el subtotal visible.
        // Actualiza el resumen monetario del impuesto.
        textoIva.textContent = formatoMoneda.format(iva); // Mantiene el impuesto visible.
        // Actualiza el resumen del total final.
        textoTotal.textContent = formatoMoneda.format(total); // Mantiene el total visible.
        // Mantiene el botón disponible para que los campos vacíos puedan mostrar sus errores.
        botonConfirmar.disabled = false; // La validación por campo detendrá un envío sin artículos.
    }; // Finaliza la función de renderizado del carrito.

    // Filtra las opciones de producto al escribir en el buscador.
    campoBusqueda.addEventListener('input', () => { // Reacciona a cada tecla sin recargar la página.
        // Invalida el producto previamente seleccionado si el texto cambia.
        productoSeleccionado = null; // Requiere elegir una opción nueva del catálogo.
        // Limpia el identificador oculto hasta que se elija una sugerencia.
        campoIdProducto.value = ''; // Evita enviar un artículo distinto al texto visible.
        // Restablece el máximo al límite decimal mientras todavía no hay producto elegido.
        campoCantidad.max = '9999999.999'; // Evita conservar el stock de una selección anterior.
        // Limpia y oculta la disponibilidad del producto cuya búsqueda cambió.
        mensajeDisponibilidad.textContent = ''; // Evita mostrar una cantidad de una selección anterior.
        // Oculta el mensaje hasta que se seleccione otra sugerencia.
        mensajeDisponibilidad.hidden = true; // Mantiene limpia la captura sin producto.
        // Quita el error anterior mientras se actualiza la búsqueda.
        actualizarEstadoCampo(campoBusqueda, errorBusqueda, '', false); // Permite corregir el texto sin conservar un estado obsoleto.
        // Normaliza el término para buscar por nombre y corte.
        const termino = normalizarTexto(campoBusqueda.value); // Ignora acentos y mayúsculas.
        // Inicializa el total de sugerencias que coinciden.
        let coincidencias = 0; // Determina si existe algún producto que mostrar.

        // Revisa y muestra las opciones que contienen el término actual.
        opcionesProducto.forEach((opcion) => { // Conserva las opciones seguras renderizadas desde PHP.
            // Combina el nombre y el corte para incluir ambos criterios.
            const textoOpcion = normalizarTexto(`${opcion.dataset.nombre || ''} ${opcion.dataset.corte || ''}`); // Permite buscar cualquiera de los dos textos.
            // Determina si la opción corresponde a la búsqueda actual.
            const coincide = termino !== '' && textoOpcion.includes(termino); // No abre toda la lista para un campo vacío.
            // Oculta las opciones que no cumplen con el término.
            opcion.hidden = !coincide; // Mantiene la lista limitada a coincidencias.
            // Suma las sugerencias que sí coinciden.
            if (coincide) { // Solo cuenta opciones visibles.
                coincidencias += 1; // Informa el total disponible.
            } // Finaliza el conteo de coincidencias.
        }); // Finaliza el filtrado del catálogo local.

        // Muestra el menú solo mientras exista un término de búsqueda.
        listaSugerencias.hidden = termino === '' || coincidencias === 0; // Evita desplegar una lista vacía.
        // Sincroniza el estado accesible del menú con su visibilidad.
        campoBusqueda.setAttribute('aria-expanded', String(!listaSugerencias.hidden)); // Informa a tecnologías de asistencia.
        // Avisa si el catálogo no contiene coincidencias para lo escrito.
        avisoProducto.textContent = termino !== '' && coincidencias === 0 ? 'No se encontró un producto con ese nombre.' : ''; // Da retroalimentación inmediata.
        // Muestra u oculta el mensaje de búsqueda sin coincidencias.
        avisoProducto.hidden = avisoProducto.textContent === ''; // Evita reservar espacio cuando no hay aviso.
    }); // Finaliza el filtro del campo de producto.

    // Valida el buscador cuando se abandona con un término escrito sin seleccionar.
    campoBusqueda.addEventListener('blur', () => { // Evita marcar error mientras el usuario todavía escribe.
        if (campoBusqueda.value.trim() !== '') { // Revisa el término solo si el control contiene texto.
            validarProducto(); // Explica que se debe elegir una sugerencia válida.
        } // Finaliza la comprobación al salir del buscador.
    }); // Finaliza la validación por salida del campo.

    // Valida la cantidad a medida que se captura después de seleccionar un producto.
    campoCantidad.addEventListener('input', () => { // Da retroalimentación sin esperar el envío de venta.
        if (campoCantidad.value !== '') { // Evita mostrar error antes de que el usuario empiece.
            validarCantidad(); // Revisa formato, mínimo, máximo y disponibilidad informativa.
        } else { // Limpia el estado cuando el campo vuelve a quedar vacío.
            actualizarEstadoCampo(campoCantidad, errorCantidad, '', false); // Espera al siguiente intento de agregar.
        } // Finaliza la validación de captura inmediata.
    }); // Finaliza la validación mientras se escribe.

    // Revisa la cantidad al salir del campo, aunque no se intente agregar todavía.
    campoCantidad.addEventListener('blur', () => { // Da una segunda oportunidad de ver el error.
        if (campoCantidad.value !== '') { // No señala un campo vacío hasta una acción de envío.
            validarCantidad(); // Comprueba el valor capturado.
        } // Finaliza la validación al abandonar la cantidad.
    }); // Finaliza la validación por salida del campo.

    // Comprueba el cliente cada vez que se cambia la opción seleccionada.
    selectorCliente.addEventListener('change', validarCliente); // Marca el resultado del selector inmediatamente.

    // Comprueba el pago cada vez que se cambia la forma seleccionada.
    selectorPago.addEventListener('change', validarPago); // Marca el resultado del selector inmediatamente.

    // Registra la selección cuando el usuario elige una sugerencia.
    listaSugerencias.addEventListener('click', (evento) => { // Usa delegación para los botones del catálogo.
        // Busca la opción seleccionada dentro del menú.
        const opcion = evento.target.closest('[data-opcion-producto]'); // Ignora clics en el espacio vacío del contenedor.
        // Detiene el manejador si no se seleccionó un producto.
        if (!opcion) { // Confirma que el clic venga de una sugerencia.
            return; // No modifica el estado del carrito.
        } // Finaliza la comprobación de opción.

        // Conserva los datos públicos del artículo elegido.
        productoSeleccionado = { id_producto: Number(opcion.dataset.id), nombre_producto: opcion.dataset.nombre || '', tipo_corte: opcion.dataset.corte || '', precio_kg: Number(opcion.dataset.precio), stock_disponible: Number(opcion.dataset.stock) }; // Usa valores que el servidor incluyó en el catálogo.
        // Muestra el nombre en el campo de búsqueda.
        campoBusqueda.value = productoSeleccionado.nombre_producto; // Sustituye el término temporal por el producto elegido.
        // Guarda el ID en el campo oculto de estado.
        campoIdProducto.value = String(productoSeleccionado.id_producto); // Evita depender del nombre del producto.
        // Calcula cuánto stock informativo queda para agregar después de considerar el carrito actual.
        const articuloExistente = carrito.find((articulo) => articulo.id_producto === productoSeleccionado.id_producto); // Evita rebasar existencias al acumular una línea.
        // Resta la cantidad que ya está en el carrito a la existencia consultada.
        const cantidadRestante = Math.max(0, productoSeleccionado.stock_disponible - (articuloExistente ? articuloExistente.cantidad_kg : 0)); // Limita la nueva captura al stock visible.
        // Aplica al control el máximo de kilogramos que todavía se pueden agregar.
        campoCantidad.max = cantidadRestante.toFixed(3); // Da validación inmediata antes de llegar al trigger.
        // Presenta los kilogramos que pueden agregarse con el formato solicitado.
        mensajeDisponibilidad.textContent = `Disponible para agregar: ${formatoKilogramos.format(cantidadRestante)} kg`; // No muestra el stock total del producto.
        // Hace visible la disponibilidad del producto seleccionado.
        mensajeDisponibilidad.hidden = false; // Mantiene el mensaje oculto antes de elegir un producto.
        // Revalida una cantidad que el usuario hubiera escrito antes de elegir el producto.
        if (campoCantidad.value !== '') { // Omite la comprobación hasta que el control tenga un dato.
            validarCantidad(); // Actualiza el error con el stock del producto recién elegido.
        } // Finaliza la validación de cantidad tras seleccionar el producto.
        // Oculta el menú después de elegir una opción.
        listaSugerencias.hidden = true; // Cierra el listado visual.
        // Actualiza el estado accesible del campo de búsqueda.
        campoBusqueda.setAttribute('aria-expanded', 'false'); // Informa que ya no hay sugerencias abiertas.
        // Marca el producto elegido como válido y quita el error del buscador.
        actualizarEstadoCampo(campoBusqueda, errorBusqueda, '', true); // Confirma visualmente la selección del catálogo.
        // Limpia el aviso de búsqueda previo.
        avisoProducto.hidden = true; // Quita mensajes asociados al término anterior.
        // Sitúa el foco en el campo para capturar kilogramos.
        campoCantidad.focus(); // Agiliza la secuencia de captura del vendedor.
    }); // Finaliza la selección de producto.

    // Agrega la cantidad seleccionada al carrito local.
    botonAgregar.addEventListener('click', async () => { // Mantiene el control separado del envío de venta.
        // Desactiva temporalmente el botón para evitar agregar varias veces durante la consulta.
        botonAgregar.disabled = true; // Impide duplicar una línea mientras se valida el stock vigente.
        // Consulta una existencia recién leída antes de aceptar la cantidad.
        const disponibilidadActualizada = await actualizarDisponibilidad(); // Comprueba nuevamente la base de datos al agregar.
        // Reactiva el control después de completar la consulta.
        botonAgregar.disabled = false; // Permite continuar o volver a intentar.
        // Detiene el agregado si no se pudo comprobar el stock actual.
        if (!disponibilidadActualizada) { // Evita utilizar una cantidad que puede haber quedado obsoleta.
            // Muestra una advertencia visible junto al panel del producto.
            avisoProducto.textContent = 'No se pudo actualizar la existencia. Intenta agregar el producto nuevamente.'; // Explica el motivo del bloqueo.
            // Hace visible el aviso de disponibilidad fallida.
            avisoProducto.hidden = false; // Mantiene la advertencia accesible.
            // Termina la acción hasta que se pueda consultar el catálogo.
            return; // Conserva el carrito sin cambios.
        } // Finaliza la protección ante una consulta fallida.
        // Comprueba que se haya seleccionado un producto de la lista.
        if (!validarProducto()) { // No toma un nombre escrito como selección confiable.
            // Devuelve el foco al buscador para corregir la selección.
            campoBusqueda.focus(); // Permite continuar la captura sin buscar el control.
            // Detiene el agregado de una línea incompleta.
            return; // Mantiene intacto el carrito actual.
        } // Finaliza la validación de selección.

        // Valida el formato positivo del campo de kilogramos.
        if (!validarCantidad()) { // Aplica el mínimo, el formato decimal y el máximo disponible.
            // Devuelve el foco a la cantidad para corregirla.
            campoCantidad.focus(); // Sitúa el cursor en el control marcado.
            // Detiene el agregado hasta recibir una cantidad válida.
            return; // No agrega valores vacíos o fuera de rango.
        } // Finaliza la validación de cantidad.

        // Lee la cantidad positiva que eligió el usuario.
        const cantidadNueva = Number(campoCantidad.value); // Convierte el valor validado a número decimal.
        // Busca si el mismo producto ya está presente en el carrito.
        const articuloExistente = carrito.find((articulo) => articulo.id_producto === productoSeleccionado.id_producto); // Evita repetir filas idénticas.

        // Acumula la cantidad si el producto ya estaba en la nota.
        if (articuloExistente) { // Reutiliza la línea existente del mismo producto.
            articuloExistente.cantidad_kg = Math.round((articuloExistente.cantidad_kg + cantidadNueva) * 1000) / 1000; // Conserva tres posiciones decimales.
        } else { // Agrega un producto nuevo al detalle de la venta.
            carrito.push({ ...productoSeleccionado, cantidad_kg: Math.round(cantidadNueva * 1000) / 1000 }); // Conserva precio y descripción del catálogo.
        } // Finaliza la acumulación o inserción local.
        // Elimina un error previo del producto después de corregir y volver a agregarlo.
        delete erroresProductos[String(productoSeleccionado.id_producto)]; // Quita el error de cantidad que ya se atendió.

        // Reconstruye las filas, totales y campos ocultos del formulario.
        renderizarCarrito(); // Mantiene visible y enviable el mismo estado.
        // Limpia la elección de producto para preparar otra captura.
        productoSeleccionado = null; // Exige una nueva selección para agregar otro artículo.
        // Limpia el nombre del producto anterior.
        campoBusqueda.value = ''; // Deja lista la búsqueda siguiente.
        // Limpia el identificador oculto del producto anterior.
        campoIdProducto.value = ''; // Evita reutilizar el ID anterior accidentalmente.
        // Restablece el máximo decimal para una búsqueda todavía sin producto.
        campoCantidad.max = '9999999.999'; // Evita mantener el límite del artículo anterior.
        // Limpia y oculta la disponibilidad al terminar de agregar el producto.
        mensajeDisponibilidad.textContent = ''; // Evita conservar el remanente de la captura anterior.
        // Oculta el texto mientras se prepara la búsqueda siguiente.
        mensajeDisponibilidad.hidden = true; // Muestra la disponibilidad cuando se elija otro producto.
        // Limpia la cantidad anterior.
        campoCantidad.value = ''; // Previene repetir una cantidad sin confirmar.
        // Limpia los estados visuales de los controles para la siguiente captura.
        actualizarEstadoCampo(campoBusqueda, errorBusqueda, '', false); // Deja el buscador listo para otro artículo.
        // Limpia el estado visual de cantidad después de agregar la línea.
        actualizarEstadoCampo(campoCantidad, errorCantidad, '', false); // Deja la cantidad lista para otra captura.
        // Quita un error previo del carrito cuando se agrega una línea corregida.
        actualizarErrorCarrito(''); // Conserva solo los avisos que aún requieren atención.
        // Quita cualquier aviso de validación anterior.
        avisoProducto.hidden = true; // Limpia el estado del panel al completar.
        // Sitúa el foco en el próximo término de búsqueda.
        campoBusqueda.focus(); // Facilita agregar artículos consecutivos.
    }); // Finaliza el agregado al carrito.

    // Quita una línea del carrito usando el botón de acción de cada fila.
    cuerpoCarrito.addEventListener('click', (evento) => { // Usa un solo manejador para todas las filas dinámicas.
        // Localiza el botón quitar en el elemento que originó el clic.
        const botonQuitar = evento.target.closest('[data-quitar-producto]'); // Ignora interacciones en celdas sin acción.
        // Detiene el manejo si el clic no solicitó quitar una línea.
        if (!botonQuitar) { // Comprueba que se presionó el botón esperado.
            return; // Mantiene las filas al interactuar con su contenido.
        } // Finaliza la comprobación del botón.

        // Recupera el ID del producto que se debe quitar.
        const idProducto = Number(botonQuitar.dataset.quitarProducto); // Convierte el atributo a número.
        // Conserva en el carrito todos los demás productos.
        carrito = carrito.filter((articulo) => articulo.id_producto !== idProducto); // Elimina solamente la línea seleccionada.
        // Quita el error que pertenecía a la línea que el usuario eliminó.
        delete erroresProductos[String(idProducto)]; // Evita conservar errores de un producto que ya no está en el carrito.
        // Limpia el mensaje general para volver a validar el detalle actualizado.
        actualizarErrorCarrito(''); // Permite confirmar después de corregir o eliminar una línea.
        // Actualiza tabla, campos ocultos y totales.
        renderizarCarrito(); // Sincroniza el cambio en la interfaz.
    }); // Finaliza la acción de quitar productos.

    // Vacía el carrito completo cuando se usa el botón superior.
    botonVaciar.addEventListener('click', () => { // Evita enviar una nota sin artículos.
        // Elimina todas las líneas mantenidas en memoria.
        carrito = []; // Restablece el estado local.
        // Quita todos los mensajes de línea que ya no correspondan al carrito.
        erroresProductos = {}; // Evita errores visibles de artículos eliminados.
        // Limpia el aviso del detalle después de vaciar la tabla.
        actualizarErrorCarrito(''); // La validación volverá a indicar que falta un producto al confirmar.
        // Actualiza la tabla y el resumen monetario.
        renderizarCarrito(); // Muestra el estado vacío y limpia los campos POST.
    }); // Finaliza la acción para vaciar el carrito.

    // Valida por separado cada campo y cada cantidad antes de confirmar la nota.
    formularioVenta.addEventListener('submit', (evento) => { // Comprueba errores antes de enviar el POST al servidor.
        // Valida cliente y pago sin ocultar errores simultáneos.
        const clienteValido = validarCliente(); // Marca directamente el selector si falta una opción válida.
        // Valida el método de pago sin ocultar el estado del cliente.
        const pagoValido = validarPago(); // Marca directamente el selector si falta una opción permitida.
        // Comprueba si ya existe un producto que pueda formar parte de la nota.
        const carritoValido = carrito.length > 0; // Exige por lo menos una línea.
        // Inicializa el estado de los campos de captura de producto.
        let productoValido = true; // Se revisa si la nota todavía no tiene artículos.
        // Inicializa el estado del campo de cantidad.
        let cantidadValida = true; // Se revisa si la nota todavía no tiene artículos.
        // Señala el requisito de carrito cuando no hay ninguna línea agregada.
        if (!carritoValido) { // Comprueba el detalle antes de enviar.
            productoValido = validarProducto(); // Marca el campo de búsqueda si no hay producto seleccionado.
            cantidadValida = validarCantidad(); // Marca el campo de kilogramos si está vacío o no cumple sus reglas.
            actualizarErrorCarrito('Agrega al menos un producto antes de confirmar la venta.'); // Presenta el requisito junto a la tabla.
        } // Finaliza la validación de los campos de producto en una nota vacía.
        // Busca errores de stock que todavía pertenezcan a líneas presentes en el carrito.
        const existeErrorEnDetalle = carrito.some((articulo) => Boolean(erroresProductos[String(articulo.id_producto)])); // Evita reenviar una línea que el trigger ya rechazó.
        // Explica junto a la tabla si permanece una cantidad que requiere corrección.
        if (existeErrorEnDetalle) { // Revisa la asociación entre producto y mensaje del servidor.
            actualizarErrorCarrito('Corrige o quita las líneas marcadas antes de confirmar la venta.'); // Mantiene el error al lado del detalle.
        } // Finaliza la validación del error de cantidad del servidor.
        // Combina los resultados independientes del formulario y su carrito.
        const formularioValido = clienteValido && pagoValido && carritoValido && productoValido && cantidadValida && !existeErrorEnDetalle; // Rechaza solo cuando al menos un campo requiere atención.

        // Detiene el envío y lleva el foco al primer control que necesite corrección.
        if (!formularioValido) { // Evita que el navegador mande campos incompletos.
            evento.preventDefault(); // Conserva la captura para corregirla.
            // Da prioridad al cliente si el servidor o navegador marcó ese selector.
            if (!clienteValido) { // Comprueba el primer campo del encabezado.
                selectorCliente.focus(); // Lleva el cursor al selector de cliente.
            } else if (!pagoValido) { // Comprueba el segundo campo del encabezado.
                selectorPago.focus(); // Lleva el cursor al selector de pago.
            } else if (!productoValido) { // Comprueba la selección del producto.
                campoBusqueda.focus(); // Lleva el cursor al buscador del catálogo.
            } else if (!cantidadValida) { // Comprueba la cantidad en kilogramos.
                campoCantidad.focus(); // Lleva el cursor a la cantidad que requiere ajuste.
            } else if (existeErrorEnDetalle) { // Comprueba si el servidor marcó una cantidad en el carrito.
                const botonLineaConError = cuerpoCarrito.querySelector('[aria-invalid="true"]')?.closest('tr')?.querySelector('[data-quitar-producto]'); // Localiza una acción para corregir la línea marcada.
                if (botonLineaConError) { // Comprueba que exista una fila identificada.
                    botonLineaConError.focus(); // Permite quitar la cantidad rechazada y agregarla de nuevo.
                } // Finaliza el enfoque de la fila con error.
            } // Finaliza la selección del primer control que requiere atención.
        } // Finaliza la prevención del envío inválido.
    }); // Finaliza la validación previa al envío.

    // Renderiza el carrito inicial antes de cualquier interacción del usuario.
    renderizarCarrito(); // Restaura filas y totales después de una respuesta de error.
    // Consulta el stock al abrir la pantalla para sincronizar la primera vista.
    actualizarDisponibilidad(); // Evita esperar al primer intervalo para mostrar existencias actuales.
    // Revisa las existencias con frecuencia mientras la pantalla de venta permanezca abierta.
    window.setInterval(actualizarDisponibilidad, 5000); // Mantiene actualizado el catálogo cada cinco segundos.
}); // Finaliza la inicialización del punto de venta.
