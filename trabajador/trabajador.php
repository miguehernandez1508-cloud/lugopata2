<?php
require_once "../conex.php";
require_once "../user/gestorsesion.php";
require_once "../user/auditoria.php";

class Trabajador {
    private $conexion;
    private $auditoria;

    public $cedula;
    public $nombre;
    public $apellido;
    public $telefono;
    public $direccion;
    public $firma;
    public $id_departamento; 

    public function __construct($conexion, $cedula = "", $nombre = "", $apellido = "", $telefono = "", $direccion = "", $firma = "", $id_departamento = null) {
        $this->conexion = $conexion;
        $this->auditoria = new Auditoria($conexion);
        $this->cedula = $cedula;
        $this->nombre = $nombre;
        $this->apellido = $apellido;
        $this->telefono = $telefono;
        $this->direccion = $direccion;
        $this->firma = $firma;
        $this->id_departamento = $id_departamento;

        GestorSesiones::iniciar();
    }

    public function crear() {
        try {
            $sql = "INSERT INTO trabajadores(cedula, nombre, apellido, telefono, direccion, firma, id_departamento) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $sentencia = $this->conexion->prepare($sql);
            $resultado = $sentencia->execute([$this->cedula, $this->nombre, $this->apellido, $this->telefono, $this->direccion, $this->firma, $this->id_departamento]);
            
            if ($resultado) {
                $id_trabajador = $this->conexion->lastInsertId();
                $usuario = $_SESSION['username'] ?? 'sistema';
                $this->auditoria->registrar("crear trabajador", $usuario, "ID: $id_trabajador, Nombre: {$this->nombre} {$this->apellido}, Cédula: {$this->cedula}");
                
                // RETORNAR EL ID para usarlo en las aptitudes
                return $id_trabajador;
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Error creando trabajador: " . $e->getMessage());
            return false;
        }
    }

    public function listar() {
        $sentencia = $this->conexion->query("
            SELECT t.*, d.nombre AS departamento, 
                   GROUP_CONCAT(dt.aptitud SEPARATOR ', ') AS aptitudes
            FROM trabajadores t
            LEFT JOIN departamentos d ON t.id_departamento = d.id_departamento
            LEFT JOIN detalle_trabajador dt ON t.id_trabajador = dt.id_trabajador
            GROUP BY t.id_trabajador
            ORDER BY t.id_trabajador DESC
        ");
        return $sentencia->fetchAll(PDO::FETCH_OBJ);
    }

    public function obtener($id) {
        $sentencia = $this->conexion->prepare("SELECT * FROM trabajadores WHERE id_trabajador = ? LIMIT 1");
        $sentencia->execute([$id]);
        return $sentencia->fetch(PDO::FETCH_OBJ);
    }

    public function actualizar($id) {
        $sql = "UPDATE trabajadores SET cedula=?, nombre=?, apellido=?, telefono=?, direccion=?, id_departamento=? WHERE id_trabajador=?";
        $sentencia = $this->conexion->prepare($sql);
        $resultado = $sentencia->execute([$this->cedula, $this->nombre, $this->apellido, $this->telefono, $this->direccion, $this->id_departamento, $id]);
        
        if ($resultado) {
            $usuario = $_SESSION['username'] ?? 'sistema';
            $this->auditoria->registrar("editar trabajador", $usuario, "ID: $id, Nombre: {$this->nombre} {$this->apellido}, Cédula: {$this->cedula}");
        }
        
        return $resultado;
    }

    public function eliminar($id) {
        $trabajador = $this->obtener($id);
        $nombreTrabajador = $trabajador ? $trabajador->nombre . ' ' . $trabajador->apellido : 'Desconocido';
        $cedulaTrabajador = $trabajador ? $trabajador->cedula : 'Desconocida';
        
        $sentencia = $this->conexion->prepare("DELETE FROM trabajadores WHERE id_trabajador=?");
        $resultado = $sentencia->execute([$id]);
        
        if ($resultado) {
            $usuario = $_SESSION['username'] ?? 'sistema';
            $this->auditoria->registrar("eliminar trabajador", $usuario, "ID: $id, Nombre: $nombreTrabajador, Cédula: $cedulaTrabajador");
        }
        
        return $resultado;
    }
}
?>