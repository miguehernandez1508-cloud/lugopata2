<?php
require_once "../conex.php";
require_once "../user/gestorsesion.php";

// Clase para gestionar las solicitudes de salida de almacén
class SalidaAlmacen {
    private $conexion;  // Objeto de conexión a la base de datos
    
    // Propiedades públicas que representan los campos de una solicitud de salida
    public $fecha;
    public $emisor;
    public $departamento_emisor;
    public $receptor;
    public $departamento_destino;
    public $descripcion;

    // Constructor - recibe la conexión a la base de datos
    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    // Método para crear una nueva solicitud de salida en la base de datos
    public function crearSalida() {
        try {
            // Query SQL para insertar una nueva solicitud de salida
            $sql = "INSERT INTO solicitud_salida_almacen
                    (fecha, emisor, departamento_emisor, receptor, departamento_destino, descripcion)
                    VALUES (?, ?, ?, ?, ?, ?)";
            $sentencia = $this->conexion->prepare($sql);
            // Ejecuta la inserción con todos los valores de las propiedades
            $sentencia->execute([
                $this->fecha,
                $this->emisor,
                $this->departamento_emisor,
                $this->receptor,
                $this->departamento_destino,
                $this->descripcion
            ]);
            // Retorna el ID de la solicitud recién creada
            return $this->conexion->lastInsertId();
        } catch(PDOException $e) {
            echo "Error SQL crear salida: ".$e->getMessage();
            return false;
        }
    }

    // Método para agregar los detalles (materiales) de una solicitud de salida
    public function agregarDetalle($id_solicitud, $materiales) {
        try {
            // Inicia sesión para obtener la firma del usuario actual
            GestorSesiones::iniciar();
            $firma_emisor = GestorSesiones::get('firma');

            // Query SQL para insertar detalles de la solicitud
            $sql = "INSERT INTO detalle_salida_almacen
                    (id_solicitud, id_insumo, cantidad_solicitada, cantidad_entregada, firma_emisor)
                    VALUES (?, ?, ?, 0, ?)";
            $sentencia = $this->conexion->prepare($sql);

            // Itera sobre cada material y lo inserta en la base de datos
            foreach ($materiales as $m) {
                $sentencia->execute([
                    $id_solicitud,
                    $m['id_insumo'],
                    $m['cantidad_solicitada'],
                    $firma_emisor
                ]);
            }
            return true;
        } catch(PDOException $e) {
            echo "Error SQL detalle salida: ".$e->getMessage();
            return false;
        }
    }

    // Método para obtener una solicitud de salida específica por su ID
    public function obtener($id_solicitud) {
        try {
            // Consulta que incluye información de departamentos mediante JOIN
            $sql = "SELECT s.*, d1.nombre AS nombre_departamento_emisor, d2.nombre AS nombre_departamento_destino
                    FROM solicitud_salida_almacen s
                    INNER JOIN departamentos d1 ON s.departamento_emisor = d1.id_departamento
                    INNER JOIN departamentos d2 ON s.departamento_destino = d2.id_departamento
                    WHERE s.id_solicitud = ?";
            $sentencia = $this->conexion->prepare($sql);
            $sentencia->execute([$id_solicitud]);
            // Retorna un solo registro como objeto
            return $sentencia->fetch(PDO::FETCH_OBJ);
        } catch(PDOException $e) {
            echo "Error SQL obtener salida: ".$e->getMessage();
            return false;
        }
    }

    // Método para obtener los detalles (materiales) de una solicitud de salida
    public function obtenerDetalle($id_solicitud) {
        try {
            // Consulta que incluye información de insumos mediante JOIN
            $sql = "SELECT d.id_detalle, d.id_insumo, i.nombre, i.unidad_medida AS unidad, i.imagen,
                           d.cantidad_solicitada, d.cantidad_entregada,
                           d.firma_emisor, d.firma_receptor
                    FROM detalle_salida_almacen d
                    INNER JOIN insumos i ON d.id_insumo = i.id_insumo
                    WHERE d.id_solicitud = ?";
            $sentencia = $this->conexion->prepare($sql);
            $sentencia->execute([$id_solicitud]);
            // Retorna todos los registros como array de objetos
            return $sentencia->fetchAll(PDO::FETCH_OBJ);
        } catch(PDOException $e) {
            echo "Error SQL detalle salida: ".$e->getMessage();
            return [];
        }
    }
}
?>