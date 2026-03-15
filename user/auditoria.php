
<?php
// auditoria.php - SIN el require de conex.php
class Auditoria {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    public function registrar($accion, $usuario = null, $detalle = null) {
        $sql = "INSERT INTO auditoria (accion, usuario, detalle) VALUES (?, ?, ?)";
        $sentencia = $this->conexion->prepare($sql);
        return $sentencia->execute([$accion, $usuario, $detalle]);
    }
}
?>