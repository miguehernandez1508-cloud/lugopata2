<?php
require_once "../conex.php";
require_once "../user/gestorsesion.php";
GestorSesiones::get('firma');

class Solicitud {
    private $conexion;

    public $fecha;
    public $emisor;
    public $receptor;
    public $departamento_emisor;
    public $departamento_destino;
    public $descripcion;
    public $id_incidencia;
    public $razon_manual;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    // Crear solicitud
public function crearSolicitud() {
    try {
        $sql = "INSERT INTO solicitud_materiales(fecha, emisor, receptor, departamento_emisor, departamento_destino, descripcion, id_incidencia, razon_manual)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $sentencia = $this->conexion->prepare($sql);
        $sentencia->execute([
            $this->fecha,
            $this->emisor,
            $this->receptor,
            $this->departamento_emisor,
            $this->departamento_destino,
            $this->descripcion,
            $this->id_incidencia,
            $this->razon_manual
        ]);
        return $this->conexion->lastInsertId();
    } catch (PDOException $e) {
        echo "Error SQL solicitud: " . $e->getMessage();
        return false;
    }
}

    // Crear detalle de solicitud con firma del emisor
    public function agregarDetalle($id_solicitud, $materiales) {
        try {
            require_once "../user/gestorsesion.php";
            GestorSesiones::iniciar();
            $firma_emisor = GestorSesiones::get('firma');

            $sql = "INSERT INTO detalle_solicitud_material
                    (id_solicitud, id_insumo, cantidad_pedida, cantidad_recibida, firma_emisor)
                    VALUES (?, ?, ?, ?, ?)";
            $sentencia = $this->conexion->prepare($sql);

            foreach ($materiales as $m) {
                $sentencia->execute([
                    $id_solicitud, 
                    $m['id_insumo'], 
                    $m['cantidad_pedida'], 
                    $m['cantidad_recibida'], 
                    $firma_emisor
                ]);
            }
            return true;
        } catch (PDOException $e) {
            echo "Error SQL detalle: " . $e->getMessage();
            return false;
        }
    }

    // Registrar aprobación: guardar firma del aprobador en detalle_solicitud_material
    public function aprobarSolicitud($id_solicitud, $id_usuario) {
        try {
            require_once "../user/gestorsesion.php";
            GestorSesiones::iniciar();
            $firma_aprobador = GestorSesiones::get('firma');

            // Actualizar estado en solicitud_materiales
            $sql = "UPDATE solicitud_materiales
                    SET estado = 'Pendiente', id_aprobador = ?
                    WHERE id_solicitud = ?";
            $sentencia = $this->conexion->prepare($sql);
            $sentencia->execute([$id_usuario, $id_solicitud]);

            // Guardar firma del aprobador en cada detalle
            $sql2 = "UPDATE detalle_solicitud_material
                     SET firma_aprobador = ?
                     WHERE id_solicitud = ?";
            $sentencia2 = $this->conexion->prepare($sql2);
            $sentencia2->execute([$firma_aprobador, $id_solicitud]);

            return true;
        } catch (PDOException $e) {
            echo "Error al aprobar solicitud: " . $e->getMessage();
            return false;
        }
    }

    // Obtener solicitud con nombre de departamentos
    public function obtener($id_solicitud) {
        try {
            $sql = "SELECT s.*, 
                           d1.nombre AS nombre_departamento_emisor,
                           d2.nombre AS nombre_departamento_destino
                    FROM solicitud_materiales s
                    INNER JOIN departamentos d1 ON s.departamento_emisor = d1.id_departamento
                    INNER JOIN departamentos d2 ON s.departamento_destino = d2.id_departamento
                    WHERE s.id_solicitud = ?";
            $sentencia = $this->conexion->prepare($sql);
            $sentencia->execute([$id_solicitud]);
            return $sentencia->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            echo "Error SQL obtener solicitud: " . $e->getMessage();
            return false;
        }
    }

    // Obtener detalles de la solicitud con todas las firmas
    public function obtenerDetalle($id_solicitud) {
        try {
            $sentencia = $this->conexion->prepare(
                "SELECT d.id_detalle, d.id_insumo, 
                i.nombre, i.descripcion, i.unidad_medida AS unidad, i.imagen, 
                d.cantidad_pedida, d.cantidad_recibida,
                d.firma_receptor, d.firma_emisor, d.firma_aprobador
         FROM detalle_solicitud_material d
         INNER JOIN insumos i ON d.id_insumo = i.id_insumo
         WHERE d.id_solicitud = ?"
            );
            $sentencia->execute([$id_solicitud]);
            return $sentencia->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            echo 'Error SQL obtener detalle: ' . $e->getMessage();
            return [];
        }
    }

    // Registrar recepción de insumos
// Registrar recepción de insumos
public function registrarRecepcion($id_solicitud, $materiales) {
    try {
        require_once "../user/gestorsesion.php";
        GestorSesiones::iniciar();

        $firma_receptor = GestorSesiones::get('firma'); //  Obtener firma del usuario logueado

        //  Verificar stock
        foreach ($materiales as $m) {
            $sentenciaStock = $this->conexion->prepare(
                "SELECT cantidad FROM stock_almacen WHERE id_insumo = ?"
            );
            $sentenciaStock->execute([$m['id_insumo']]);
            $stock = $sentenciaStock->fetchColumn();

            if ($m['cantidad_recibida'] > $stock) {
                return "No hay suficiente stock para el insumo ID {$m['id_insumo']}. Stock disponible: $stock";
            }
        }

        // Actualizar detalle, firma y stock
        foreach ($materiales as $m) {
            //  Guardar cantidad y firma del receptor
            $sentenciaDetalle = $this->conexion->prepare(
                "UPDATE detalle_solicitud_material 
                 SET cantidad_recibida = cantidad_recibida + ?,
                     firma_receptor = ?
                 WHERE id_solicitud = ? AND id_insumo = ?"
            );
            $sentenciaDetalle->execute([
                $m['cantidad_recibida'],
                $firma_receptor,
                $id_solicitud,
                $m['id_insumo']
            ]);

            //  Actualizar cantidad de insumos
            $sentenciaInsumo = $this->conexion->prepare(
                "UPDATE insumos SET cantidad = cantidad + ? WHERE id_insumo = ?"
            );
            $sentenciaInsumo->execute([$m['cantidad_recibida'], $m['id_insumo']]);

            //  Descontar del stock
            $sentenciaStock = $this->conexion->prepare(
                "UPDATE stock_almacen SET cantidad = cantidad - ? WHERE id_insumo = ?"
            );
            $sentenciaStock->execute([$m['cantidad_recibida'], $m['id_insumo']]);
        }

        // Verificar si ya está finalizada
        $sentenciaCheck = $this->conexion->prepare(
            "SELECT SUM(cantidad_pedida - cantidad_recibida) AS faltantes
             FROM detalle_solicitud_material
             WHERE id_solicitud = ?"
        );
        $sentenciaCheck->execute([$id_solicitud]);
        $resultado = $sentenciaCheck->fetch(PDO::FETCH_ASSOC);

        if ($resultado && $resultado['faltantes'] <= 0) {
            $sentenciaUpdate = $this->conexion->prepare(
                "UPDATE solicitud_materiales SET estado = 'Finalizada' WHERE id_solicitud = ?"
            );
            $sentenciaUpdate->execute([$id_solicitud]);
        }

        return true;

    } catch (PDOException $e) {
        return "Error SQL: " . $e->getMessage();
    }
}






    // Contar total de solicitudes
    public function contar() {
        $sentencia = $this->conexion->query("SELECT COUNT(*) FROM solicitud_materiales");
        return $sentencia->fetchColumn();
    }

    // Listar solicitudes con departamentos y estado
    public function listar($inicio = 0, $limite = 10) {
        $sql = "SELECT s.id_solicitud, s.fecha, s.emisor, s.receptor, 
                       d1.nombre AS departamento_emisor,
                       d2.nombre AS departamento_destino,
                       s.descripcion,
                       s.estado
                FROM solicitud_materiales s
                INNER JOIN departamentos d1 ON s.departamento_emisor = d1.id_departamento
                INNER JOIN departamentos d2 ON s.departamento_destino = d2.id_departamento
                ORDER BY s.id_solicitud DESC
                LIMIT :inicio, :limite";

        $sentencia = $this->conexion->prepare($sql);
        $sentencia->bindValue(':inicio', (int)$inicio, PDO::PARAM_INT);
        $sentencia->bindValue(':limite', (int)$limite, PDO::PARAM_INT);
        $sentencia->execute();

        return $sentencia->fetchAll(PDO::FETCH_OBJ);
    }

    // Actualizar datos de la solicitud
public function actualizarSolicitud($id_solicitud, $fecha, $receptor, $departamento_emisor, $departamento_destino, $descripcion) {
        try {
            $sql = "UPDATE solicitud_materiales 
                    SET fecha = ?, receptor = ?, departamento_emisor = ?, departamento_destino = ?, descripcion = ?
                    WHERE id_solicitud = ?";
            $sentencia = $this->conexion->prepare($sql);
            return $sentencia->execute([$fecha, $receptor, $departamento_emisor, $departamento_destino, $descripcion, $id_solicitud]);
        } catch (PDOException $e) {
            echo "Error al actualizar solicitud: " . $e->getMessage();
            return false;
        }
    }

    // Actualizar detalles de la solicitud (editar, agregar)
// Método para actualizar detalles de solicitud (nueva versión mejorada)
// Agrega este método a la clase Solicitud en solicitud.php
public function actualizarDetalles($id_solicitud, $materiales) {
    try {
        // Array para almacenar IDs de detalles que se mantienen
        $ids_mantenidos = [];
        
        foreach ($materiales as $m) {
            // Validar datos mínimos
            if (empty($m['id_insumo']) || empty($m['cantidad_pedida']) || $m['cantidad_pedida'] <= 0) {
                continue;
            }
            
            // Convertir a entero (la BD usa INT)
            $cantidad = (int)$m['cantidad_pedida'];
            
            if (!empty($m['id_detalle'])) {
                // Actualizar detalle existente
                $sql = "UPDATE detalle_solicitud_material 
                        SET cantidad_pedida = ? 
                        WHERE id_detalle = ? AND id_solicitud = ?";
                $sentencia = $this->conexion->prepare($sql);
                $sentencia->execute([$cantidad, $m['id_detalle'], $id_solicitud]);
                $ids_mantenidos[] = $m['id_detalle'];
            } else {
                // Insertar nuevo detalle
                $sql = "INSERT INTO detalle_solicitud_material 
                        (id_solicitud, id_insumo, cantidad_pedida, cantidad_recibida) 
                        VALUES (?, ?, ?, 0)";
                $sentencia = $this->conexion->prepare($sql);
                $sentencia->execute([$id_solicitud, $m['id_insumo'], $cantidad]);
                $ids_mantenidos[] = $this->conexion->lastInsertId();
            }
        }
        
        // Eliminar detalles que ya no están en el formulario
        if (!empty($ids_mantenidos)) {
            $placeholders = implode(',', array_fill(0, count($ids_mantenidos), '?'));
            $sql_delete = "DELETE FROM detalle_solicitud_material 
                          WHERE id_solicitud = ? AND id_detalle NOT IN ($placeholders)";
            $params = array_merge([$id_solicitud], $ids_mantenidos);
        } else {
            // Si no hay materiales, eliminar todos los detalles
            $sql_delete = "DELETE FROM detalle_solicitud_material WHERE id_solicitud = ?";
            $params = [$id_solicitud];
        }
        
        $sentencia_delete = $this->conexion->prepare($sql_delete);
        $sentencia_delete->execute($params);
        
        return true;
        
    } catch (PDOException $e) {
        error_log("Error en actualizarDetalles: " . $e->getMessage());
        return false;
    }
}


    // Eliminar detalles que no estén en el array (para eliminar filas)
    public function eliminarDetallesNoExistentes($id_solicitud, $ids_existentes = []) {
        try {
            if (!empty($ids_existentes)) {
                $in  = str_repeat('?,', count($ids_existentes) - 1) . '?';
                $sentencia = $this->conexion->prepare("DELETE FROM detalle_solicitud_material WHERE id_solicitud = ? AND id_detalle NOT IN ($in)");
                $sentencia->execute(array_merge([$id_solicitud], $ids_existentes));
            } else {
                $sentencia = $this->conexion->prepare("DELETE FROM detalle_solicitud_material WHERE id_solicitud = ?");
                $sentencia->execute([$id_solicitud]);
            }
            return true;
        } catch (PDOException $e) {
            echo "Error al eliminar detalles: " . $e->getMessage();
            return false;
        }
    }

// Eliminar una solicitud junto con sus detalles
public function eliminarSolicitud($id_solicitud) {
    try {
        // Elimina detalles primero
        $sentencia = $this->conexion->prepare("DELETE FROM detalle_solicitud_material WHERE id_solicitud = ?");
        $sentencia->execute([$id_solicitud]);

        // Luego elimina la solicitud principal
        $sentencia = $this->conexion->prepare("DELETE FROM solicitud_materiales WHERE id_solicitud = ?");
        $sentencia->execute([$id_solicitud]);

        return true;
    } catch (PDOException $e) {
        echo "Error al eliminar solicitud: " . $e->getMessage();
        return false;
    }
}


}
?>
