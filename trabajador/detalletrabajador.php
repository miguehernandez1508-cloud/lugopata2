<?php
require_once "../conex.php";

class DetalleTrabajador {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    // Lista todas las aptitudes de un trabajador
    public function listarPorTrabajador($id_trabajador) {
        $sql = "SELECT * FROM detalle_trabajador WHERE id_trabajador = ?";
        $sentencia = $this->conexion->prepare($sql);
        $sentencia->execute([$id_trabajador]);
        return $sentencia->fetchAll(PDO::FETCH_ASSOC);
    }

    // Agrega una aptitud al trabajador
    public function agregar($id_trabajador, $aptitud) {
        $sql = "INSERT INTO detalle_trabajador (id_trabajador, aptitud) VALUES (?, ?)";
        $sentencia = $this->conexion->prepare($sql);
        return $sentencia->execute([$id_trabajador, $aptitud]);
    }

    // Elimina todas las aptitudes de un trabajador
    public function eliminarPorTrabajador($id_trabajador) {
        $sql = "DELETE FROM detalle_trabajador WHERE id_trabajador = ?";
        $sentencia = $this->conexion->prepare($sql);
        return $sentencia->execute([$id_trabajador]);
    }
}
?>
