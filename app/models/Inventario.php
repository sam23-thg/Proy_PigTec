<?php
// Activa la comprobación estricta de tipos para las operaciones de inventario.
declare(strict_types=1); // Evita conversiones implícitas al recibir identificadores y cantidades.

// Declara el modelo que consulta y modifica exclusivamente la tabla inventario.
class Inventario // Representa las operaciones básicas sobre las existencias de productos.
{ // Inicia la definición de la clase Inventario.
    // Conserva la conexión PDO compartida por los modelos de la aplicación.
    private PDO $db; // Evita crear conexiones adicionales desde este modelo.

    // Recibe la conexión PDO configurada por la aplicación mediante inyección.
    public function __construct(PDO $db) // Permite probar y reutilizar la conexión existente.
    { // Inicia la inicialización del modelo.
        // Guarda la conexión para usarla en consultas preparadas sobre inventario.
        $this->db = $db; // Asigna PDO al atributo privado de la clase.
    } // Finaliza la inicialización del modelo.

    // Busca la existencia relacionada con el identificador de producto indicado.
    public function buscarPorProducto(int $idProducto): ?array // Devuelve una fila asociativa o null si no hay relación.
    { // Inicia la búsqueda de inventario por producto.
        // Prepara la consulta con los nombres exactos de las columnas de inventario.
        $consulta = $this->db->prepare('SELECT id_inventario, id_producto, cantidad_existencia, fecha_actualizacion FROM inventario WHERE id_producto = :id_producto LIMIT 1'); // Limita el resultado al registro uno a uno esperado.
        // Asocia el identificador como entero al parámetro de la consulta.
        $consulta->bindValue(':id_producto', $idProducto, PDO::PARAM_INT); // Mantiene el tipo numérico del identificador.
        // Ejecuta la lectura parametrizada sin cambiar los datos almacenados.
        $consulta->execute(); // Solicita a MariaDB o MySQL la fila relacionada.
        // Devuelve el registro encontrado o null cuando el producto aún no tiene existencia asociada.
        return $consulta->fetch() ?: null; // Utiliza el modo asociativo establecido en PDO.
    } // Finaliza la búsqueda por producto.

    // Registra la existencia inicial de un producto ya existente en la tabla producto.
    public function registrar(int $idProducto, int|float|string $cantidadExistencia): bool // Recibe la clave foránea y la cantidad inicial, incluso en formato decimal de formulario.
    { // Inicia la creación del registro de inventario.
        // Prepara la inserción usando exclusivamente las columnas documentadas para inventario.
        $consulta = $this->db->prepare('INSERT INTO inventario (id_producto, cantidad_existencia, fecha_actualizacion) VALUES (:id_producto, :cantidad_existencia, NOW())'); // Genera la fecha con el reloj del servidor de base de datos.
        // Ejecuta la inserción con valores parametrizados y devuelve el resultado de PDO.
        return $consulta->execute([ // Comunica si la sentencia de escritura se ejecutó correctamente.
            ':id_producto' => $idProducto, // Relaciona la existencia con la clave foránea id_producto.
            ':cantidad_existencia' => $cantidadExistencia, // Guarda la cantidad inicial en su columna correspondiente.
        ]); // Finaliza la ejecución parametrizada del INSERT.
    } // Finaliza la creación del registro de inventario.

    // Actualiza las existencias y su fecha para el producto identificado.
    public function actualizarCantidad(int $idProducto, int|float|string $cantidadExistencia): bool // Recibe el producto y la nueva cantidad acumulada, incluso en formato decimal de formulario.
    { // Inicia la modificación de la existencia.
        // Prepara la actualización de cantidad y fecha usando los nombres exactos del esquema.
        $consulta = $this->db->prepare('UPDATE inventario SET cantidad_existencia = :cantidad_existencia, fecha_actualizacion = NOW() WHERE id_producto = :id_producto'); // Actualiza solo el inventario asociado a ese producto.
        // Ejecuta la modificación con parámetros separados del texto SQL.
        return $consulta->execute([ // Devuelve el indicador de ejecución de PDO.
            ':cantidad_existencia' => $cantidadExistencia, // Sustituye el valor acumulado de la existencia.
            ':id_producto' => $idProducto, // Limita la actualización a la clave foránea recibida.
        ]); // Finaliza la ejecución parametrizada del UPDATE.
    } // Finaliza la modificación de existencias.
} // Finaliza la definición de la clase Inventario.
