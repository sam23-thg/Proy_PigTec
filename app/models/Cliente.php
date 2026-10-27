<?php
// Activa la comprobación estricta de tipos para las operaciones de persistencia de clientes.
declare(strict_types=1); // Evita conversiones implícitas en datos y resultados de PDO.

// Declara el modelo que concentra las consultas de la tabla cliente.
class Cliente // Mantiene la persistencia separada de la lógica del controlador.
{ // Inicia la definición del modelo Cliente.
    // Conserva la conexión PDO que recibe el enrutador.
    private PDO $db; // Permite reutilizar la conexión configurada por la aplicación.

    // Recibe la conexión PDO existente sin crear conexiones adicionales.
    public function __construct(PDO $db) // Inyecta la dependencia de base de datos.
    { // Inicia la asignación de la conexión.
        // Guarda la conexión para las consultas preparadas del modelo.
        $this->db = $db; // Mantiene una sola conexión para el ciclo de petición.
    } // Finaliza el constructor del modelo.

    // Registra un cliente con los campos admitidos por el esquema documentado.
    public function registrar(array $datos): bool // Recibe datos ya validados por el controlador.
    { // Inicia el alta del cliente.
        // Prepara el INSERT con parámetros para todos los valores variables.
        $consulta = $this->db->prepare('INSERT INTO cliente (nombre_razonsocial, telefono, calle, colonia, codigo_postal, ciudad) VALUES (:nombre_razonsocial, :telefono, :calle, :colonia, :codigo_postal, :ciudad)'); // Protege la consulta contra inyección SQL.
        // Ejecuta el INSERT usando valores independientes de la instrucción SQL.
        return $consulta->execute([ // Devuelve si PDO completó correctamente la escritura.
            ':nombre_razonsocial' => $datos['nombre_razonsocial'], // Asigna el nombre o razón social obligatorio.
            ':telefono' => $datos['telefono'], // Asigna el teléfono obligatorio como texto.
            ':calle' => $datos['calle'] !== '' ? $datos['calle'] : null, // Convierte el campo opcional vacío en NULL.
            ':colonia' => $datos['colonia'] !== '' ? $datos['colonia'] : null, // Convierte la colonia opcional vacía en NULL.
            ':codigo_postal' => $datos['codigo_postal'] !== '' ? $datos['codigo_postal'] : null, // Conserva el código postal como texto o NULL.
            ':ciudad' => $datos['ciudad'] !== '' ? $datos['ciudad'] : null, // Convierte la ciudad opcional vacía en NULL.
        ]); // Finaliza la ejecución parametrizada del alta.
    } // Finaliza el método de registro.

    // Busca coincidencias de nombre o teléfono mediante una consulta preparada.
    public function buscarPorNombreOTelefono(string $termino): array // Recibe el término de búsqueda normalizado.
    { // Inicia la búsqueda de clientes.
        // Prepara una coincidencia parcial en nombre o una coincidencia parcial en teléfono.
        $consulta = $this->db->prepare('SELECT id_cliente, nombre_razonsocial, telefono, calle, colonia, codigo_postal, ciudad FROM cliente WHERE nombre_razonsocial LIKE :termino_nombre OR telefono LIKE :termino_telefono ORDER BY id_cliente ASC'); // Mantiene búsqueda parametrizada y orden por identificador.
        // Agrega comodines al término sin interpolarlo dentro del SQL.
        $terminoBusqueda = '%' . $termino . '%'; // Facilita encontrar fragmentos escritos por la persona usuaria.
        // Ejecuta la búsqueda con parámetros separados para cada condición.
        $consulta->execute([':termino_nombre' => $terminoBusqueda, ':termino_telefono' => $terminoBusqueda]); // Evita concatenar datos en SQL.
        // Devuelve todas las coincidencias en formato asociativo.
        return $consulta->fetchAll(PDO::FETCH_ASSOC); // Entrega una lista vacía cuando no hay resultados.
    } // Finaliza la búsqueda por nombre o teléfono.

    // Recupera todos los clientes para llenar la tabla de consulta.
    public function listarTodos(): array // Devuelve los perfiles ordenados por identificador.
    { // Inicia la consulta general de clientes.
        // Prepara una selección limitada a las columnas públicas del perfil de cliente.
        $consulta = $this->db->prepare('SELECT id_cliente, nombre_razonsocial, telefono, calle, colonia, codigo_postal, ciudad FROM cliente ORDER BY id_cliente ASC'); // Presenta primero los identificadores más antiguos.
        // Ejecuta la consulta sin datos externos porque no tiene filtros.
        $consulta->execute(); // Usa PDO preparado también para la lectura general.
        // Devuelve los registros en formato asociativo para la plantilla.
        return $consulta->fetchAll(PDO::FETCH_ASSOC); // Entrega una lista vacía cuando no existen clientes.
    } // Finaliza el listado completo de clientes.

    // Comprueba duplicados exactos en cualquiera de los dos campos obligatorios.
    public function comprobarDuplicado(string $nombre, string $telefono): bool // Recibe los valores normalizados antes del alta.
    { // Inicia la comprobación de duplicidad.
        // Busca filas cuyo nombre o teléfono coincidan exactamente con los enviados.
        $consulta = $this->db->prepare('SELECT 1 FROM cliente WHERE nombre_razonsocial = :nombre OR telefono = :telefono LIMIT 1'); // Compara ambos campos mediante parámetros.
        // Ejecuta la consulta con los dos valores obligatorios.
        $consulta->execute([':nombre' => $nombre, ':telefono' => $telefono]); // No transforma la comparación exacta definida por el requisito.
        // Informa si la base de datos devolvió por lo menos una coincidencia.
        return $consulta->fetchColumn() !== false; // Distingue una fila encontrada de la ausencia de duplicados.
    } // Finaliza la comprobación de duplicados.
} // Finaliza la definición de Cliente.
