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

            // Prepara la inserción de inventario con las columnas reales del esquema.
            $consultaInventario = $this->db->prepare('INSERT INTO inventario (id_producto, cantidad_existencia, fecha_actualizacion) VALUES (:id_producto, :cantidad_existencia, NOW())'); // Usa el reloj del servidor para registrar la fecha.
            // Inserta inmediatamente la existencia vinculada al producto recién creado.
            $consultaInventario->execute([ // Mantiene la relación uno a uno con la clave recién obtenida.
                ':id_producto' => $idProducto, // Relaciona inventario con producto mediante id_producto.
                ':cantidad_existencia' => $existenciaInicial, // Guarda la existencia inicial proporcionada.
            ]); // Finaliza la inserción parametrizada de inventario.

            // Confirma conjuntamente el producto y su registro de inventario.
            $this->db->commit(); // Hace permanentes ambas inserciones solo cuando las dos finalizaron.
            // Indica que la creación de producto e inventario se completó correctamente.
            return true; // Comunica el éxito de la operación transaccional.
        } catch (Throwable $excepcion) { // Captura fallos de PDO y errores ocurridos durante la transacción.
            // Comprueba que la transacción siga activa antes de intentar revertirla.
            if ($this->db->inTransaction()) { // Evita llamar rollBack cuando no hay una transacción abierta.
                // Revierte las inserciones para impedir registros comerciales sin inventario.
                $this->db->rollBack(); // Restaura el estado anterior de ambas tablas.
            } // Finaliza la comprobación y reversión de la transacción activa.
            // Informa al controlador que la operación falló sin exponer detalles internos de la base de datos.
            return false; // Comunica el fallo manteniendo la respuesta definida en la arquitectura.
        } // Finaliza el control de errores de la creación transaccional.
    } // Finaliza la creación coordinada de producto e inventario.

} // Finaliza la definición de la clase Producto.
