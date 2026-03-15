<?php
// Incluye el archivo de conexión a la base de datos
require_once __DIR__ . "/../conex.php";

// Clase para gestionar categorías de insumos en el sistema
class Categoria {
    private $conexion;      // Objeto de conexión a la base de datos
    
    // Propiedades públicas que representan los campos de una categoría
    public $id_categoria;
    public $nombre;
    public $descripcion;
    public $abreviatura;

    // Constructor - inicializa las propiedades de la categoría
    public function __construct($conexion, $id_categoria=null, $nombre=null, $descripcion=null, $abreviatura=null) {
        $this->conexion = $conexion;
        $this->id_categoria = $id_categoria;
        $this->nombre = $nombre;
        $this->descripcion = $descripcion;
        $this->abreviatura = $abreviatura;
    }

    // Método para crear una nueva categoría en la base de datos
    public function crear() {
        $sql = "INSERT INTO categorias_insumo (nombre, descripcion, abreviatura) VALUES (?, ?, ?)";
        $sentencia = $this->conexion->prepare($sql);
        // Ejecuta la inserción con los valores de las propiedades
        return $sentencia->execute([$this->nombre, $this->descripcion, $this->abreviatura]);
    }

    // Método para obtener todas las categorías ordenadas por nombre
    public function obtenerTodas() {
        $sql = "SELECT * FROM categorias_insumo ORDER BY nombre ASC";
        $sentencia = $this->conexion->query($sql);
        // Retorna todas las categorías como array asociativo
        return $sentencia->fetchAll(PDO::FETCH_ASSOC);
    }

    // Método para obtener categorías paginadas ordenadas por ID descendente
    public function obtenerTodasPaginadas($inicio, $limite) {
        $sql = "SELECT * FROM categorias_insumo ORDER BY id_categoria DESC LIMIT :inicio, :limite";
        $sentencia = $this->conexion->prepare($sql);
        $sentencia->bindValue(':inicio', (int)$inicio, PDO::PARAM_INT);
        $sentencia->bindValue(':limite', (int)$limite, PDO::PARAM_INT);
        $sentencia->execute();
        return $sentencia->fetchAll(PDO::FETCH_ASSOC);
    }

    // Método para contar el total de categorías
    public function contarTotal() {
        $sql = "SELECT COUNT(*) as total FROM categorias_insumo";
        $sentencia = $this->conexion->query($sql);
        $resultado = $sentencia->fetch(PDO::FETCH_ASSOC);
        return $resultado['total'];
    }

    // Método para obtener una categoría específica por su ID
    public function obtenerPorId($id_categoria) {
        $sql = "SELECT * FROM categorias_insumo WHERE id_categoria = ?";
        $sentencia = $this->conexion->prepare($sql);
        $sentencia->execute([$id_categoria]);
        // Retorna la categoría encontrada como array asociativo
        return $sentencia->fetch(PDO::FETCH_ASSOC);
    }

    // Método para actualizar una categoría existente
    public function actualizar($id_categoria, $nombre, $descripcion, $abreviatura) {
        $sql = "UPDATE categorias_insumo SET nombre = ?, descripcion = ?, abreviatura = ? WHERE id_categoria = ?";
        $sentencia = $this->conexion->prepare($sql);
        return $sentencia->execute([$nombre, $descripcion, $abreviatura, $id_categoria]);
    }

    // Método para eliminar una categoría
    public function eliminar($id_categoria) {
        $sql = "DELETE FROM categorias_insumo WHERE id_categoria = ?";
        $sentencia = $this->conexion->prepare($sql);
        return $sentencia->execute([$id_categoria]);
    }

    // Método para verificar si una categoría tiene insumos asociados
    public function tieneInsumos($id_categoria) {
        $sql = "SELECT COUNT(*) as total FROM insumos WHERE id_categoria = ?";
        $sentencia = $this->conexion->prepare($sql);
        $sentencia->execute([$id_categoria]);
        $resultado = $sentencia->fetch(PDO::FETCH_ASSOC);
        return $resultado['total'] > 0;
    }
}
?>