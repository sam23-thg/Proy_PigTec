<?php
// Activa la comprobación estricta de tipos para los argumentos del modelo de productos.
declare(strict_types=1); // Reduce conversiones implícitas durante el manejo de productos e inventario.

// Declara el modelo responsable de mantener producto e inventario coordinados.
class Producto // Agrupa las operaciones de persistencia comercial y existencias de productos.
{ // Inicia la definición de la clase Producto.
    // Conserva la conexión PDO compartida y permite ejecutar las dos tablas bajo una transacción.
    private PDO $db; // Reutiliza la misma conexión para garantizar atomicidad entre tablas.

    // Recibe una conexión PDO previamente configurada por la aplicación.
    public function __construct(PDO $db) // Evita que el modelo cree credenciales o conexiones propias.
    { // Inicia la inicialización del modelo.
        // Guarda la conexión recibida para utilizarla en las operaciones transaccionales.
        $this->db = $db; // Asigna PDO al atributo privado del modelo.
    } // Finaliza la inicialización del modelo.

    // Detecta el nombre real de la columna de existencia en la tabla inventario.
    private function obtenerColumnaExistencia(): string // Adapta las consultas al nombre activo en el esquema MariaDB o MySQL.
    { // Inicia la detección dinámica del nombre de la columna.
        // Consulta los metadatos de las columnas de la tabla inventario.
        $consultaColumnas = $this->db->query('SHOW COLUMNS FROM inventario'); // Lee los campos disponibles en la tabla activa.
        // Obtiene el arreglo con los nombres de todas las columnas existentes.
        $columnasExistentes = array_column($consultaColumnas->fetchAll(PDO::FETCH_ASSOC), 'Field'); // Extrae los nombres de campo.
        // Retorna cantidad_kg si existe en la base de datos o cantidad_existencia como alternativa.
        return in_array('cantidad_kg', $columnasExistentes, true) ? 'cantidad_kg' : 'cantidad_existencia'; // Garantiza compatibilidad total.
    } // Finaliza la detección del nombre de columna.

    // Crea un producto activo y su existencia inicial dentro de una única transacción.
    public function crearConInventario(array $datosProducto, int|float|string $existenciaInicial): bool // Recibe los datos comerciales y la cantidad inicial.
    { // Inicia el proceso transaccional de creación.
        // Inicia el bloque de control para revertir cualquier escritura parcial.
        try { // Captura errores de base de datos y otros fallos durante la operación.
            // Abre una transacción sobre la conexión PDO que se usará en ambas tablas.
            $this->db->beginTransaction(); // Evita dejar producto sin su inventario correspondiente.

            // Prepara la inserción comercial con los nombres exactos de las columnas producto.
            $consultaProducto = $this->db->prepare('INSERT INTO producto (nombre_producto, tipo_corte, precio_kg, estado) VALUES (:nombre_producto, :tipo_corte, :precio_kg, :estado)'); // Mantiene la consulta libre de valores interpolados.
            // Inserta los datos de producto de manera parametrizada y lo marca activo por defecto.
            $consultaProducto->execute([ // Ejecuta primero el alta en la tabla producto.
                ':nombre_producto' => (string) $datosProducto['nombre_producto'], // Mapea el nombre obligatorio a nombre_producto.
                ':tipo_corte' => $datosProducto['tipo_corte'] ?? null, // Mapea el tipo de corte opcional o guarda null.
                ':precio_kg' => $datosProducto['precio_kg'], // Mapea el precio decimal a precio_kg.
                ':estado' => 1, // Inicializa el producto como activo según los requisitos.
            ]); // Finaliza la inserción parametrizada del producto.

            // Recupera el identificador generado por la inserción anterior en la misma conexión.
            $idProducto = (int) $this->db->lastInsertId(); // Obtiene la clave primaria para crear su inventario relacionado.

            // Obtiene la columna real de existencia en la tabla inventario.
            $columnaExistencia = $this->obtenerColumnaExistencia(); // Determina si la columna es cantidad_kg o cantidad_existencia.

            // Prepara la inserción de inventario con las columnas reales del esquema.
            $consultaInventario = $this->db->prepare("INSERT INTO inventario (id_producto, {$columnaExistencia}, fecha_actualizacion) VALUES (:id_producto, :cantidad, NOW())"); // Usa el reloj del servidor para registrar la fecha.
            // Inserta inmediatamente la existencia vinculada al producto recién creado.
            $consultaInventario->execute([ // Mantiene la relación uno a uno con la clave recién obtenida.
                ':id_producto' => $idProducto, // Relaciona inventario con producto mediante id_producto.
                ':cantidad' => $existenciaInicial, // Guarda la existencia inicial proporcionada.
            ]); // Finaliza la inserción parametrizada de inventario.

            // Confirma conjuntamente el producto y su registro de inventario.
            $this->db->commit(); // Hace permanentes ambas inserciones solo cuando las dos finalizaron.
            // Indica que la creación de producto e inventario se completó correctamente.
            return true; // Comunica el éxito de la operación transaccional.
        } catch (Throwable $excepcion) { // Captura fallos de PDO y errores ocurridos durante la transacción.
            // Registra el error interno en la bitácora técnica del servidor.
            error_log('Fallo transaccional al crear producto con inventario: ' . $excepcion->getMessage()); // Registro seguro sin datos externos.
            // Comprueba que la transacción siga activa antes de intentar revertirla.
            if ($this->db->inTransaction()) { // Evita llamar rollBack cuando no hay una transacción abierta.
                // Revierte las inserciones para impedir registros comerciales sin inventario.
                $this->db->rollBack(); // Restaura el estado anterior de ambas tablas.
            } // Finaliza la comprobación y reversión de la transacción activa.
            // Informa al controlador que la operación falló sin exponer detalles internos de la base de datos.
            return false; // Comunica el fallo manteniendo la respuesta definida en la arquitectura.
        } // Finaliza el control de errores de la creación transaccional.
    } // Finaliza la creación coordinada de producto e inventario.

    // Actualiza producto e inventario bajo una única transacción para conservar su relación.
    public function actualizarConInventario(int $id, array $datosProducto, int|float|string $cantidadActual): bool // Recibe el identificador, los datos comerciales y la cantidad acumulada.
    { // Inicia el proceso transaccional de actualización.
        // Inicia el bloque de control para revertir ambos cambios ante cualquier fallo.
        try { // Agrupa las dos actualizaciones bajo manejo de excepciones.
            // Abre la transacción antes de ejecutar cualquiera de las dos sentencias UPDATE.
            $this->db->beginTransaction(); // Garantiza que producto e inventario cambien conjuntamente.

            // Prepara la actualización de los datos comerciales con columnas exactas de producto.
            $consultaProducto = $this->db->prepare('UPDATE producto SET nombre_producto = :nombre_producto, tipo_corte = :tipo_corte, precio_kg = :precio_kg, estado = :estado WHERE id_producto = :id_producto'); // Limita el cambio al producto identificado.
            // Actualiza primero el producto con valores enviados como parámetros independientes.
            $consultaProducto->execute([ // Ejecuta la sentencia comercial dentro de la transacción.
                ':nombre_producto' => (string) $datosProducto['nombre_producto'], // Sustituye el nombre guardado en nombre_producto.
                ':tipo_corte' => $datosProducto['tipo_corte'] ?? null, // Sustituye el tipo de corte o conserva null si es opcional.
                ':precio_kg' => $datosProducto['precio_kg'], // Sustituye el precio decimal de venta por kilogramo.
                ':estado' => (int) $datosProducto['estado'], // Aplica el estado activo o inactivo recibido.
                ':id_producto' => $id, // Selecciona la fila cuyo identificador coincide con el solicitado.
            ]); // Finaliza la actualización parametrizada de producto.

            // Obtiene la columna real de existencia en la tabla inventario.
            $columnaExistencia = $this->obtenerColumnaExistencia(); // Determina la columna de existencias del esquema.

            // Prepara la actualización de existencias y fecha usando las columnas reales de inventario.
            $consultaInventario = $this->db->prepare("UPDATE inventario SET {$columnaExistencia} = :cantidad, fecha_actualizacion = NOW() WHERE id_producto = :id_producto"); // Relaciona la actualización mediante id_producto.
            // Actualiza inmediatamente la cantidad vinculada dentro de la misma transacción.
            $consultaInventario->execute([ // Ejecuta la segunda sentencia UPDATE antes de confirmar.
                ':cantidad' => $cantidadActual, // Sustituye la cantidad acumulada del inventario.
                ':id_producto' => $id, // Limita el cambio al inventario del producto correspondiente.
            ]); // Finaliza la actualización parametrizada de inventario.

            // Confirma simultáneamente los cambios comerciales y de existencias.
            $this->db->commit(); // Hace permanentes ambas actualizaciones solo si concluyeron correctamente.
            // Indica que las dos tablas quedaron actualizadas correctamente.
            return true; // Comunica el éxito de la operación transaccional.
        } catch (Throwable $excepcion) { // Captura fallos de PDO y cualquier error que ocurra durante la transacción.
            // Registra el error interno en la bitácora del servidor.
            error_log('Fallo transaccional al actualizar producto con inventario: ' . $excepcion->getMessage()); // Registra el error de forma segura.
            // Comprueba que haya una transacción activa antes de solicitar su reversión.
            if ($this->db->inTransaction()) { // Protege contra una reversión innecesaria tras el cierre transaccional.
                // Deshace la actualización previa si falla cualquiera de las sentencias o el commit.
                $this->db->rollBack(); // Mantiene producto e inventario en su estado previo conjunto.
            } // Finaliza la comprobación y reversión de la transacción activa.
            // Devuelve un resultado controlado sin filtrar mensajes técnicos de la base de datos.
            return false; // Permite que el controlador trate el fallo de manera segura.
        } // Finaliza el control de errores de la actualización transaccional.
    } // Finaliza la actualización coordinada de producto e inventario.

    // Busca un producto por su clave primaria unificando su información con el inventario actual.
    public function buscarPorId(int $id): ?array // Devuelve los datos comerciales y la existencia física relacionada.
    { // Inicia la consulta unificada de producto e inventario.
        // Obtiene la columna exacta de existencia para la consulta.
        $columnaExistencia = $this->obtenerColumnaExistencia(); // Detecta cantidad_kg o cantidad_existencia.
        // Prepara la consulta con unión externa hacia inventario para garantizar que se recupere la fila.
        $consulta = $this->db->prepare("SELECT p.id_producto, p.nombre_producto, p.tipo_corte, p.precio_kg, p.estado, i.id_inventario, COALESCE(i.{$columnaExistencia}, 0) AS cantidad_existencia, COALESCE(i.{$columnaExistencia}, 0) AS cantidad_kg, i.fecha_actualizacion FROM producto p LEFT JOIN inventario i ON p.id_producto = i.id_producto WHERE p.id_producto = :id_producto LIMIT 1"); // Unifica la lectura 1:1.
        // Enlaza el identificador como entero al parámetro nombrado.
        $consulta->bindValue(':id_producto', $id, PDO::PARAM_INT); // Protege la consulta contra inyecciones SQL.
        // Ejecuta la lectura preparada en la base de datos.
        $consulta->execute(); // Lee los datos de las tablas producto e inventario.
        // Retorna la fila encontrada como arreglo asociativo o null si no existe.
        return $consulta->fetch(PDO::FETCH_ASSOC) ?: null; // Devuelve el registro completo.
    } // Finaliza la búsqueda de producto por identificador.

    // Busca si ya existe un producto con el nombre indicado para validar unicidad.
    public function buscarPorNombre(string $nombre): ?array // Devuelve el producto con ese nombre o null si está libre.
    { // Inicia la verificación de unicidad de nombre de producto.
        // Prepara la consulta que localiza coincidencias exactas por nombre de producto.
        $consulta = $this->db->prepare('SELECT id_producto, nombre_producto, tipo_corte, precio_kg, estado FROM producto WHERE LOWER(TRIM(nombre_producto)) = LOWER(TRIM(:nombre_producto)) LIMIT 1'); // Compara sin distinción de mayúsculas ni espacios externos.
        // Asocia el nombre al parámetro nombrado de la consulta preparada.
        $consulta->bindValue(':nombre_producto', $nombre, PDO::PARAM_STR); // Mapea el texto del nombre.
        // Ejecuta la lectura de comprobación en la tabla producto.
        $consulta->execute(); // Ejecuta la búsqueda parametrizada.
        // Devuelve el registro coincidente o null si el nombre no está registrado.
        return $consulta->fetch(PDO::FETCH_ASSOC) ?: null; // Indica si hay colisión de nombres.
    } // Finaliza la búsqueda por nombre de producto.

    // Comprueba si otro producto diferente al indicado ya utiliza el mismo nombre comercial.
    public function buscarPorNombreExcluyendoId(string $nombre, int $idExcluir): ?array // Evita falsos positivos al editar el mismo producto.
    { // Inicia la comprobación de duplicados excluyendo el propio producto.
        // Prepara la consulta que busca el nombre omitiendo el identificador actual.
        $consulta = $this->db->prepare('SELECT id_producto, nombre_producto, tipo_corte, precio_kg, estado FROM producto WHERE LOWER(TRIM(nombre_producto)) = LOWER(TRIM(:nombre_producto)) AND id_producto != :id_producto LIMIT 1'); // Excluye la fila que se está editando.
        // Asocia el nombre a verificar a la consulta preparada.
        $consulta->bindValue(':nombre_producto', $nombre, PDO::PARAM_STR); // Enlaza el nombre del producto.
        // Asocia el identificador que debe excluirse de la búsqueda.
        $consulta->bindValue(':id_producto', $idExcluir, PDO::PARAM_INT); // Enlaza el ID propio.
        // Ejecuta la consulta de validación en la base de datos.
        $consulta->execute(); // Ejecuta la lectura parametrizada.
        // Devuelve el producto duplicado si existe o null si el nombre está disponible.
        return $consulta->fetch(PDO::FETCH_ASSOC) ?: null; // Informa si hay colisión con otro registro.
    } // Finaliza la búsqueda de nombre excluyendo identificador.

    // Devuelve el catálogo completo de productos unificado con sus existencias de inventario.
    public function listarTodos(): array // Recupera la colección completa de productos activos e inactivos.
    { // Inicia la consulta del catálogo completo de productos.
        // Obtiene el nombre real de la columna de existencia.
        $columnaExistencia = $this->obtenerColumnaExistencia(); // Detecta la columna de existencias del esquema activo.
        // Prepara la consulta que une producto e inventario para presentar el catálogo.
        $consulta = $this->db->prepare("SELECT p.id_producto, p.nombre_producto, p.tipo_corte, p.precio_kg, p.estado, COALESCE(i.{$columnaExistencia}, 0) AS cantidad_existencia, COALESCE(i.{$columnaExistencia}, 0) AS cantidad_kg, i.fecha_actualizacion FROM producto p LEFT JOIN inventario i ON p.id_producto = i.id_producto ORDER BY p.id_producto DESC"); // Presenta los registros más recientes primero.
        // Ejecuta la consulta preparada sin parámetros externos.
        $consulta->execute(); // Ejecuta la lectura del catálogo completo.
        // Devuelve el conjunto de productos como arreglo asociativo.
        return $consulta->fetchAll(PDO::FETCH_ASSOC); // Entrega la colección para la vista y el buscador.
    } // Finaliza la consulta del catálogo completo.

    // Consulta los productos activos junto con su existencia para la pantalla de disponibilidad de ventas.
    public function obtenerCatalogoConStock(): array // Devuelve los datos comerciales y la cantidad física asociada.
    { // Inicia la consulta aislada del catálogo de ventas.
        // Prepara una unión interna para incluir únicamente productos con registro relacionado de inventario.
        $consulta = $this->db->prepare('SELECT p.id_producto, p.nombre_producto, p.tipo_corte, p.precio_kg, i.cantidad_kg FROM producto AS p INNER JOIN inventario AS i ON i.id_producto = p.id_producto WHERE p.estado = :estado ORDER BY p.nombre_producto ASC'); // Mantiene los nombres del esquema documentado y ordena alfabéticamente.
        // Enlaza el estado activo como entero para mostrar únicamente productos disponibles para venta.
        $consulta->bindValue(':estado', 1, PDO::PARAM_INT); // Evita interpolar el estado dentro del SQL.
        // Ejecuta la consulta preparada de catálogo sin alterar los métodos existentes del modelo.
        $consulta->execute(); // Recupera las filas relacionadas de producto e inventario.
        // Devuelve las filas como arreglos asociativos para la vista.
        return $consulta->fetchAll(PDO::FETCH_ASSOC); // Entrega el catálogo con su stock actual.
    } // Finaliza la consulta aislada de catálogo con existencias.
} // Finaliza la definición de la clase Producto.
