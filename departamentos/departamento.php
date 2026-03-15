<?php
// Inclusión de archivos necesarios para la conexión a BD y gestión de sesiones
require_once "../conex.php";
require_once "../user/gestorsesion.php";
require_once "../user/auditoria.php";

// Definición de la clase Departamento para gestionar departamentos en el sistema
class Departamento {
    private $conexion;  // Objeto de conexión a la base de datos
    private $auditoria; // Objeto para registrar auditoría

    // Propiedades públicas que representan los campos de un departamento
    public $nombre;
    public $descripcion;
    public $ubicacion;
    public $telefono;
    public $email;
    public $responsable; 

    // Constructor de la clase - inicializa las propiedades y inicia sesión
    public function __construct($conexion, $nombre = "", $descripcion = "", $ubicacion = "", $telefono = "", $email = "", $responsable = "") {
        $this->conexion = $conexion;
        $this->nombre = $nombre;
        $this->descripcion = $descripcion;
        $this->ubicacion = $ubicacion;
        $this->telefono = $telefono;
        $this->email = $email;
        $this->responsable = $responsable;
        $this->auditoria = new Auditoria($conexion);

        // Inicia la sesión del usuario
        GestorSesiones::iniciar();
    }

    // Método para crear un nuevo departamento en la base de datos
    public function crear() {
        try {
            // Query SQL para insertar un nuevo departamento
            $sql = "INSERT INTO departamentos(nombre, descripcion, ubicacion, telefono, email, responsable) VALUES (?, ?, ?, ?, ?, ?)";
            $sentencia = $this->conexion->prepare($sql);
            // Ejecuta la consulta con los valores de las propiedades
            $resultado = $sentencia->execute([$this->nombre, $this->descripcion, $this->ubicacion, $this->telefono, $this->email, $this->responsable]);
            
            // Registrar en auditoría si fue exitoso
            if ($resultado) {
                $usuario = $_SESSION['username'] ?? 'sistema';
                $this->auditoria->registrar("crear departamento", $usuario, "Departamento: {$this->nombre}");
            }
            
            return $resultado;
        } catch (PDOException $e) {
            // Retorna false en caso de error
            return false;
        }
    }

    // Método para listar departamentos con paginación
    public function listar($inicio, $registrosPorPagina) {
        // Prepara consulta con límites para paginación
        $sentencia = $this->conexion->prepare("SELECT * FROM departamentos ORDER BY id_departamento DESC LIMIT :inicio, :registros");
        // Vincula parámetros de paginación
        $sentencia->bindValue(':inicio', $inicio, PDO::PARAM_INT);
        $sentencia->bindValue(':registros', $registrosPorPagina, PDO::PARAM_INT);
        $sentencia->execute();
        // Retorna todos los resultados como objetos
        return $sentencia->fetchAll(PDO::FETCH_OBJ);
    }

    // Método para contar el total de departamentos
    public function contar() {
        $sentencia = $this->conexion->query("SELECT COUNT(*) FROM departamentos");
        // Retorna solo el valor del count
        return $sentencia->fetchColumn();
    }

    // Método para obtener un departamento específico por ID
    public function obtener($id) {
        $sentencia = $this->conexion->prepare("SELECT * FROM departamentos WHERE id_departamento = ?");
        $sentencia->execute([$id]);
        // Retorna un solo registro como objeto
        return $sentencia->fetch(PDO::FETCH_OBJ);
    }

    // Método para actualizar un departamento existente
    public function actualizar($id) {
        $sql = "UPDATE departamentos SET nombre=?, descripcion=?, ubicacion=?, telefono=?, email=?, responsable=? WHERE id_departamento=?";
        $sentencia = $this->conexion->prepare($sql);
        // Ejecuta la actualización con todos los campos más el ID
        $resultado = $sentencia->execute([$this->nombre, $this->descripcion, $this->ubicacion, $this->telefono, $this->email, $this->responsable, $id]);
        
        // Registrar en auditoría si fue exitoso
        if ($resultado) {
            $usuario = $_SESSION['username'] ?? 'sistema';
            $this->auditoria->registrar("editar departamento", $usuario, "ID: {$id}, Nuevo nombre: {$this->nombre}");
        }
        
        return $resultado;
    }

    // Método para eliminar un departamento por ID
    public function eliminar($id) {
        // Obtener información del departamento antes de eliminar para auditoría
        $departamento = $this->obtener($id);
        $nombreDepartamento = $departamento ? $departamento->nombre : 'Desconocido';
        
        $sentencia = $this->conexion->prepare("DELETE FROM departamentos WHERE id_departamento=?");
        // Ejecuta el DELETE y retorna el resultado
        $resultado = $sentencia->execute([$id]);
        
        // Registrar en auditoría si fue exitoso
        if ($resultado) {
            $usuario = $_SESSION['username'] ?? 'sistema';
            $this->auditoria->registrar("eliminar departamento", $usuario, "Departamento eliminado: {$nombreDepartamento} (ID: {$id})");
        }
        
        return $resultado;
    }
}
?>