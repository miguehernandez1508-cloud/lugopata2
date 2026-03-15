<?php
// /incidencias/Incidencia.php
require_once __DIR__ . '/../conex.php';
require_once __DIR__ . '/../user/auditoria.php';

class Incidencia {
    private $conexion;
    private $auditoria;

    public $fecha;
    public $departamento_emisor;
    public $departamento_receptor;
    public $descripcion;
    public $ubicacion;
    public $prioridad;
    public $estado;
    public $id_firma_usuario;
    public $id_trabajador_asignado;
    public $fecha_estimada_finalizacion;
    public $fecha_finalizacion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
        $this->auditoria = new Auditoria($conexion);
    }

    // crear incidencia
public function crear() {
    try {
        $sql = "INSERT INTO incidencias
                (fecha, departamento_emisor, departamento_receptor, descripcion, ubicacion, prioridad, estado, id_firma_usuario, id_trabajador_asignado)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $sentencia = $this->conexion->prepare($sql);
        $resultado = $sentencia->execute([
            $this->fecha,
            $this->departamento_emisor,
            $this->departamento_receptor,
            $this->descripcion,
            $this->ubicacion,
            $this->prioridad,
            $this->estado ?? 'En espera',
            $this->id_firma_usuario,
            $this->id_trabajador_asignado
        ]);
        
        // Registrar auditoría
        if ($resultado) {
            $id_incidencia = $this->conexion->lastInsertId();
            $usuario = $_SESSION['username'] ?? 'sistema';
            $this->auditoria->registrar("crear incidencia", $usuario, "ID: $id_incidencia, Descripción: " . substr($this->descripcion, 0, 100));
        }
        
        return $resultado;
    } catch (PDOException $e) {
        echo "Error SQL Incidencia: " . $e->getMessage();
        return false;
    }
}

    // listar incidencias con joins (paginación opcional)
    public function listar($inicio = 0, $limite = 50, $filtro = null) {
        $where = "";
        $params = [];

        if ($filtro && $filtro !== 'todos') {
            if ($filtro === 'pendiente_espera') {
                $where = " WHERE i.estado IN ('En espera','Pendiente')";
            } else {
                $where = " WHERE i.estado = :estado";
                $params[':estado'] = $filtro;
            }
        }

        $sql = "SELECT i.*,
                       d1.nombre AS depto_emisor,
                       d2.nombre AS depto_receptor,
                       t.nombre AS trabajador_nombre, t.apellido AS trabajador_apellido,
                       u.username AS creado_por, u.id_usuario AS creador_id
                FROM incidencias i
                LEFT JOIN departamentos d1 ON i.departamento_emisor = d1.id_departamento
                LEFT JOIN departamentos d2 ON i.departamento_receptor = d2.id_departamento
                LEFT JOIN trabajadores t ON i.id_trabajador_asignado = t.id_trabajador
                LEFT JOIN usuarios u ON i.id_firma_usuario = u.id_usuario
                $where
                ORDER BY i.id_incidencia DESC
                LIMIT :inicio, :limite";

        $sentencia = $this->conexion->prepare($sql);
        foreach ($params as $k => $v) $sentencia->bindValue($k, $v);
        $sentencia->bindValue(':inicio', (int)$inicio, PDO::PARAM_INT);
        $sentencia->bindValue(':limite', (int)$limite, PDO::PARAM_INT);
        $sentencia->execute();
        return $sentencia->fetchAll(PDO::FETCH_OBJ);
    }

    // obtener una incidencia con nombres para el imprimir/monitorear
public function obtener($id_incidencia) {
    $sentencia = $this->conexion->prepare("
        SELECT i.*, 
               de.nombre AS depto_emisor,
               dr.nombre AS depto_receptor,
               t.nombre AS trabajador_nombre,
               t.apellido AS trabajador_apellido,
               t.firma AS trabajador_firma,
               CONCAT(u_trab.nombre, ' ', u_trab.apellido) AS creado_por
        FROM incidencias i
        JOIN departamentos de ON i.departamento_emisor = de.id_departamento
        JOIN departamentos dr ON i.departamento_receptor = dr.id_departamento
        LEFT JOIN trabajadores t ON i.id_trabajador_asignado = t.id_trabajador
        JOIN usuarios u ON i.id_firma_usuario = u.id_usuario
        JOIN trabajadores u_trab ON u.id_trabajador = u_trab.id_trabajador
        WHERE i.id_incidencia = ?
    ");
    $sentencia->execute([$id_incidencia]);
    return $sentencia->fetch(PDO::FETCH_OBJ);
}

    // asignación automática por descripción (busca aptitudes en detalle_trabajador)
    public function asignarTrabajadorAuto($descripcion) {
        $mapeo = [
            'aire' => 'Aires acondicionados',
            'aire acondicionado' => 'Aires acondicionados',
            'computadora' => 'Computadoras',
            'pc' => 'Computadoras',
            'carpinter' => 'Carpintería',
            'carpintería' => 'Carpintería',
            'plomer' => 'Plomería',
            'plomería' => 'Plomería',
            'electric' => 'Electricidad',
            'aire acondicionado' => 'Aires acondicionados',
            'ventilador' => 'Electricidad'
        ];

        $aptitud = null;
        foreach ($mapeo as $pal => $val) {
            if (stripos($descripcion, $pal) !== false) {
                $aptitud = $val;
                break;
            }
        }

        if ($aptitud) {
            $sentencia = $this->conexion->prepare("
                SELECT t.id_trabajador
                FROM trabajadores t
                INNER JOIN detalle_trabajador dt ON t.id_trabajador = dt.id_trabajador
                WHERE dt.aptitud = ?
                ORDER BY RAND() LIMIT 1
            ");
            $sentencia->execute([$aptitud]);
            $row = $sentencia->fetch(PDO::FETCH_OBJ);
            return $row ? $row->id_trabajador : null;
        }
        return null;
    }

/**
 * Asignacion automatica basada en el tipo de incidencia seleccionado
 */
public function asignarTrabajadorPorTipo($tipo_incidencia) {
    try {
        // Buscar trabajadores que tengan el tipo de incidencia como aptitud
        $sql = "SELECT 
                    t.id_trabajador,
                    t.nombre,
                    t.apellido,
                    dt.aptitud,
                    dt.nivel_experiencia,
                    (SELECT COUNT(*) FROM incidencias i 
                     WHERE i.id_trabajador_asignado = t.id_trabajador 
                     AND i.estado IN ('Pendiente')) as incidencias_pendientes
                FROM trabajadores t
                INNER JOIN detalle_trabajador dt ON t.id_trabajador = dt.id_trabajador
                WHERE dt.aptitud = ?
                AND t.id_departamento IN (
                    SELECT id_departamento FROM departamentos 
                    WHERE nombre LIKE '%mantenimiento%'
                )
                ORDER BY 
                    CASE dt.nivel_experiencia 
                        WHEN 'avanzado' THEN 1
                        WHEN 'intermedio' THEN 2
                        WHEN 'basico' THEN 3
                        ELSE 4
                    END,
                    incidencias_pendientes ASC";

        $sentencia = $this->conexion->prepare($sql);
        $sentencia->execute([$tipo_incidencia]);
        $trabajadores = $sentencia->fetchAll(PDO::FETCH_OBJ);

        if (empty($trabajadores)) {
            error_log("DEBUG: No se encontraron trabajadores con aptitud: $tipo_incidencia");
            return null;
        }

        // Seleccionar el trabajador mas adecuado
        $trabajadorSeleccionado = $trabajadores[0];
        
        error_log("DEBUG: Trabajador seleccionado para tipo '$tipo_incidencia': {$trabajadorSeleccionado->id_trabajador} - {$trabajadorSeleccionado->nombre} - Nivel: {$trabajadorSeleccionado->nivel_experiencia} - Carga: {$trabajadorSeleccionado->incidencias_pendientes}");

        return $trabajadorSeleccionado->id_trabajador;

    } catch (PDOException $e) {
        error_log("ERROR en asignacion por tipo: " . $e->getMessage());
        return null;
    }
}

    // actualizar estado y opcionalmente asignar trabajador
    public function actualizarEstado($id_incidencia, $estado, $id_trabajador = null) {
        try {
            if ($estado === 'Finalizada') {
                $sql = "UPDATE incidencias 
                        SET estado = ?, id_trabajador_asignado = ?, fecha_finalizacion = NOW()
                        WHERE id_incidencia = ?";
            } else {
                $sql = "UPDATE incidencias 
                        SET estado = ?, id_trabajador_asignado = ? 
                        WHERE id_incidencia = ?";
            }
            $sentencia = $this->conexion->prepare($sql);
            $resultado = $sentencia->execute([$estado, $id_trabajador, $id_incidencia]);
            
            // Registrar auditoría
            if ($resultado) {
                $usuario = $_SESSION['username'] ?? 'sistema';
                $this->auditoria->registrar("actualizar estado incidencia", $usuario, "ID: $id_incidencia, Nuevo estado: $estado");
            }
            
            return $resultado;
        } catch (PDOException $e) {
            echo "Error actualizar estado: " . $e->getMessage();
            return false;
        }
    }

    // imágenes
    public function agregarImagen($id_incidencia, $ruta) {
        try {
            $sentencia = $this->conexion->prepare("INSERT INTO incidencia_imagenes (id_incidencia, ruta) VALUES (?, ?)");
            $resultado = $sentencia->execute([$id_incidencia, $ruta]);
            
            // Registrar auditoría
            if ($resultado) {
                $usuario = $_SESSION['username'] ?? 'sistema';
                $this->auditoria->registrar("agregar imagen incidencia", $usuario, "ID incidencia: $id_incidencia, Ruta: $ruta");
            }
            
            return $resultado;
        } catch (PDOException $e) {
            echo "Error guardar imagen: " . $e->getMessage();
            return false;
        }
    }

    public function obtenerImagenes($id_incidencia) {
        $sentencia = $this->conexion->prepare("SELECT * FROM incidencia_imagenes WHERE id_incidencia = ? ORDER BY fecha_subida DESC");
        $sentencia->execute([$id_incidencia]);
        return $sentencia->fetchAll(PDO::FETCH_OBJ);
    }

/**
 * Contar incidencias en estado "En espera" (para notificaciones)
 */
public function contarEnEspera() {
    $sentencia = $this->conexion->query("SELECT COUNT(*) FROM incidencias WHERE estado = 'En espera'");
    return $sentencia->fetchColumn();
}

/**
 * Contar todas las pendientes (En espera + Pendiente) - para compatibilidad
 */
public function contarPendientes() {
    $sentencia = $this->conexion->query("SELECT COUNT(*) FROM incidencias WHERE estado IN ('En espera','Pendiente')");
    return $sentencia->fetchColumn();
}

    // ========== NUEVOS MÉTODOS PARA SISTEMA DE FASES ==========

    /**
     * Aprobar una incidencia (cambia estado a Pendiente y puede asignar trabajador)
     */
public function aprobarIncidencia($id_incidencia, $id_supervisor, $id_trabajador = null, $tipo_incidencia = 'General') {
    try {
        $this->conexion->beginTransaction();

        // Obtener informacion de la incidencia
        $incidencia = $this->obtener($id_incidencia);
        if (!$incidencia) {
            throw new Exception("Incidencia no encontrada");
        }

        $trabajador_asignado = null;
        $asignacion_automatica = false;

        // ASIGNACION AUTOMATICA BASADA EN EL TIPO SELECCIONADO
        if (!$id_trabajador) {
            $asignacion_automatica = true;
            $id_trabajador = $this->asignarTrabajadorPorTipo($tipo_incidencia);
            
            if (!$id_trabajador) {
                // Si no se encontro trabajador para este tipo, marcar estado especial
                $sql = "UPDATE incidencias SET estado = 'Sin Trabajador Disponible' WHERE id_incidencia = ?";
                $sentencia = $this->conexion->prepare($sql);
                $sentencia->execute([$id_incidencia]);
                
                $this->conexion->commit();
                
                return [
                    'success' => true,
                    'trabajador_asignado' => null,
                    'asignacion_automatica' => true,
                    'estado' => 'Sin Trabajador Disponible'
                ];
            }
        }

        // Obtener información del trabajador asignado
        $sql_trabajador = "SELECT nombre, apellido FROM trabajadores WHERE id_trabajador = ?";
        $stmt_trabajador = $this->conexion->prepare($sql_trabajador);
        $stmt_trabajador->execute([$id_trabajador]);
        $trabajador_asignado = $stmt_trabajador->fetch(PDO::FETCH_OBJ);

        // Actualizar estado y trabajador asignado
        $sql = "UPDATE incidencias SET estado = 'Pendiente', id_trabajador_asignado = ? WHERE id_incidencia = ?";
        $sentencia = $this->conexion->prepare($sql);
        $resultado = $sentencia->execute([$id_trabajador, $id_incidencia]);

        if (!$resultado) {
            throw new Exception("Error al actualizar incidencia");
        }

        // Crear registro de fases para esta incidencia segun el tipo seleccionado
        $this->crearFasesParaIncidencia($id_incidencia, $tipo_incidencia);

        $this->conexion->commit();

        // Registrar auditoría
        $usuario = $_SESSION['username'] ?? 'sistema';
        $this->auditoria->registrar("aprobar incidencia", $usuario, "ID: $id_incidencia, Tipo: $tipo_incidencia, Trabajador asignado: {$trabajador_asignado->nombre} {$trabajador_asignado->apellido}");

        // Retornar información de la asignación
        return [
            'success' => true,
            'trabajador_asignado' => $trabajador_asignado,
            'asignacion_automatica' => $asignacion_automatica,
            'tipo_incidencia' => $tipo_incidencia,
            'estado' => 'Pendiente'
        ];

    } catch (Exception $e) {
        $this->conexion->rollBack();
        error_log("ERROR aprobar incidencia: " . $e->getMessage());
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

    /**
     * Rechazar una incidencia con justificación
     */
/**
 * Rechazar una incidencia con justificación
 */
public function rechazarIncidencia($id_incidencia, $id_supervisor, $justificacion) {
    try {
        $this->conexion->beginTransaction();

        // DIAGNÓSTICO: Verificar datos de entrada
        error_log("DEBUG rechazarIncidencia: ID: $id_incidencia, Supervisor: $id_supervisor, Justificación: " . substr($justificacion, 0, 50));

        // 1. Actualizar estado de la incidencia
        $sql = "UPDATE incidencias SET estado = 'Rechazada' WHERE id_incidencia = ?";
        $sentencia = $this->conexion->prepare($sql);
        $resultado_update = $sentencia->execute([$id_incidencia]);
        
        // DIAGNÓSTICO: Verificar si se actualizó
        error_log("DEBUG rechazarIncidencia: Resultado UPDATE: " . ($resultado_update ? 'TRUE' : 'FALSE') . ", Filas afectadas: " . $sentencia->rowCount());

        // 2. Guardar justificación del rechazo
        $sql_rechazo = "INSERT INTO incidencia_rechazos (id_incidencia, id_supervisor, justificacion) VALUES (?, ?, ?)";
        $sentencia_rechazo = $this->conexion->prepare($sql_rechazo);
        $resultado_insert = $sentencia_rechazo->execute([$id_incidencia, $id_supervisor, $justificacion]);
        
        // DIAGNÓSTICO: Verificar si se insertó
        error_log("DEBUG rechazarIncidencia: Resultado INSERT: " . ($resultado_insert ? 'TRUE' : 'FALSE'));

        $this->conexion->commit();
        
        // Registrar auditoría
        if ($resultado_update && $resultado_insert) {
            $usuario = $_SESSION['username'] ?? 'sistema';
            $this->auditoria->registrar("rechazar incidencia", $usuario, "ID: $id_incidencia, Justificación: " . substr($justificacion, 0, 100));
        }
        
        // Verificar que realmente se cambió el estado
        $sql_verify = "SELECT estado FROM incidencias WHERE id_incidencia = ?";
        $stmt_verify = $this->conexion->prepare($sql_verify);
        $stmt_verify->execute([$id_incidencia]);
        $estado_actual = $stmt_verify->fetchColumn();
        error_log("DEBUG rechazarIncidencia: Estado después del rechazo: " . $estado_actual);
        
        return $resultado_update && $resultado_insert;
    } catch (PDOException $e) {
        $this->conexion->rollBack();
        error_log("ERROR rechazar incidencia: " . $e->getMessage());
        return false;
    }
}

    /**
     * Crear registros de fases para una incidencia nueva
     */
public function crearFasesParaIncidencia($id_incidencia, $tipo_incidencia = 'General') {
    try {
        // DIAGNÓSTICO
        error_log("DEBUG crearFases: ID: $id_incidencia, Tipo: $tipo_incidencia");
        
        // Obtener SOLO las fases específicas para el tipo de incidencia
        $sql = "SELECT * FROM fases_incidencia 
                WHERE tipo_incidencia = ? 
                ORDER BY orden";
        $sentencia = $this->conexion->prepare($sql);
        $sentencia->execute([$tipo_incidencia]);
        $fases = $sentencia->fetchAll(PDO::FETCH_OBJ);

        // DIAGNÓSTICO: Ver cuántas fases se encontraron
        error_log("DEBUG crearFases: Fases encontradas: " . count($fases) . " para tipo: $tipo_incidencia");

        // Si no hay fases para el tipo específico, usar las generales como fallback
        if (empty($fases) && $tipo_incidencia !== 'General') {
            error_log("DEBUG crearFases: No hay fases para tipo '$tipo_incidencia', usando General como fallback");
            $sql = "SELECT * FROM fases_incidencia WHERE tipo_incidencia = 'General' ORDER BY orden";
            $sentencia = $this->conexion->prepare($sql);
            $sentencia->execute();
            $fases = $sentencia->fetchAll(PDO::FETCH_OBJ);
        }

        // Insertar cada fase para esta incidencia
$sql_insert = "INSERT INTO incidencia_fases (id_incidencia, id_fase, estado) VALUES (?, ?, 'pendiente')";
$sentencia_insert = $this->conexion->prepare($sql_insert);

        $fases_creadas = 0;
        foreach ($fases as $fase) {
            $resultado_insert = $sentencia_insert->execute([$id_incidencia, $fase->id_fase]);
            if ($resultado_insert) {
                $fases_creadas++;
            }
            error_log("DEBUG crearFases: Insertando fase {$fase->id_fase} - {$fase->nombre_fase} (Tipo: {$fase->tipo_incidencia}), resultado: $resultado_insert");
        }

        error_log("DEBUG crearFases: Se crearon $fases_creadas fases para incidencia $id_incidencia (Tipo: $tipo_incidencia)");
        
        // Registrar auditoría
        if ($fases_creadas > 0) {
            $usuario = $_SESSION['username'] ?? 'sistema';
            $this->auditoria->registrar("crear fases incidencia", $usuario, "ID incidencia: $id_incidencia, Tipo: $tipo_incidencia, Fases creadas: $fases_creadas");
        }
        
        return $fases_creadas > 0;
    } catch (PDOException $e) {
        error_log("ERROR crearFases: " . $e->getMessage());
        return false;
    }
}

    /**
     * Obtener el progreso de fases de una incidencia
     */
/**
 * Obtener el progreso de fases de una incidencia - CORREGIDO
 */
public function obtenerProgresoFases($id_incidencia) {
    try {
        $sql = "SELECT 
                f.id_fase,  // <- AGREGAR ESTO
                f.nombre_fase,
                f.descripcion,
                f.orden,
                f.requiere_evidencia,
                if.estado,
                if.fecha_completado,
                if.fecha_aprobacion,
                if.observaciones,
                if.evidencias
            FROM incidencia_fases if
            JOIN fases_incidencia f ON if.id_fase = f.id_fase
            WHERE if.id_incidencia = ?
            ORDER BY f.orden";
        
        $sentencia = $this->conexion->prepare($sql);
        $sentencia->execute([$id_incidencia]);
        return $sentencia->fetchAll(PDO::FETCH_OBJ);
    } catch (PDOException $e) {
        error_log("Error obtener progreso fases: " . $e->getMessage());
        return [];
    }
}

    /**
     * Completar una fase específica
     */
    public function completarFase($id_incidencia, $id_fase, $evidencias = []) {
        try {
            $sql = "UPDATE incidencia_fases 
                    SET estado = 'completada', 
                        fecha_completado = NOW(),
                        evidencias = ?
                    WHERE id_incidencia = ? AND id_fase = ?";
            
            $sentencia = $this->conexion->prepare($sql);
            $evidencias_json = !empty($evidencias) ? json_encode($evidencias) : null;
            $resultado = $sentencia->execute([$evidencias_json, $id_incidencia, $id_fase]);
            
            // Registrar auditoría
            if ($resultado) {
                $usuario = $_SESSION['username'] ?? 'sistema';
                $this->auditoria->registrar("completar fase incidencia", $usuario, "ID incidencia: $id_incidencia, ID fase: $id_fase");
            }
            
            return $resultado;
        } catch (PDOException $e) {
            error_log("Error completar fase: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Aprobar/rechazar una fase por el supervisor
     */
    public function validarFase($id_incidencia, $id_fase, $aprobada, $id_supervisor, $observaciones = '') {
        try {
            $estado = $aprobada ? 'aprobada' : 'rechazada';
            
            $sql = "UPDATE incidencia_fases 
                    SET estado = ?, 
                        fecha_aprobacion = NOW(),
                        id_supervisor_aprobador = ?,
                        observaciones = ?
                    WHERE id_incidencia = ? AND id_fase = ?";
            
            $sentencia = $this->conexion->prepare($sql);
            $resultado = $sentencia->execute([$estado, $id_supervisor, $observaciones, $id_incidencia, $id_fase]);
            
            // Registrar auditoría
            if ($resultado) {
                $usuario = $_SESSION['username'] ?? 'sistema';
                $accion = $aprobada ? "aprobar fase incidencia" : "rechazar fase incidencia";
                $this->auditoria->registrar($accion, $usuario, "ID incidencia: $id_incidencia, ID fase: $id_fase, Observaciones: " . substr($observaciones, 0, 100));
            }
            
            return $resultado;
        } catch (PDOException $e) {
            error_log("Error validar fase: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener incidencias pendientes de aprobación (para supervisores)
     */
public function obtenerPendientesAprobacion() {
    try {
        $sql = "SELECT i.*, de.nombre as depto_emisor, dr.nombre as depto_receptor
                FROM incidencias i
                JOIN departamentos de ON i.departamento_emisor = de.id_departamento
                JOIN departamentos dr ON i.departamento_receptor = dr.id_departamento
                WHERE i.estado = 'En espera'
                ORDER BY i.id_incidencia DESC"; // Cambiado a id_incidencia DESC
        
        $sentencia = $this->conexion->prepare($sql);
        $sentencia->execute();
        return $sentencia->fetchAll(PDO::FETCH_OBJ);
    } catch (PDOException $e) {
        error_log("Error obtener pendientes aprobacion: " . $e->getMessage());
        return [];
    }
}

/**
 * Contar incidencias pendientes asignadas a un trabajador específico
 */
public function contarPendientesAsignadas($id_trabajador) {
    try {
        $sql = "SELECT COUNT(*) FROM incidencias 
                WHERE estado = 'Pendiente' 
                AND id_trabajador_asignado = ?";
        $sentencia = $this->conexion->prepare($sql);
        $sentencia->execute([$id_trabajador]);
        return $sentencia->fetchColumn();
    } catch (PDOException $e) {
        error_log("Error contarPendientesAsignadas: " . $e->getMessage());
        return 0;
    }
}

    /**
     * Obtener justificación de rechazo de una incidencia
     */
    public function obtenerJustificacionRechazo($id_incidencia) {
        try {
            $sql = "SELECT ir.*, u.username as supervisor_nombre
                    FROM incidencia_rechazos ir
                    JOIN usuarios u ON ir.id_supervisor = u.id_usuario
                    WHERE ir.id_incidencia = ?";
            
            $sentencia = $this->conexion->prepare($sql);
            $sentencia->execute([$id_incidencia]);
            return $sentencia->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log("Error obtener justificacion rechazo: " . $e->getMessage());
            return null;
        }
    }

    /**
 * Notificar al supervisor cuando una incidencia es rechazada por el solicitante
 */
public function notificarRechazoSolicitante($id_incidencia, $razon) {
    try {
        // Obtener supervisor del departamento receptor
        $sql = "SELECT u.id_usuario, u.email, u.username
                FROM usuarios u
                JOIN trabajadores t ON u.id_trabajador = t.id_trabajador
                JOIN incidencias i ON i.departamento_receptor = t.id_departamento
                WHERE i.id_incidencia = ?
                AND u.nivel IN ('supmantenimiento', 'admin', 'superadministrador')
                LIMIT 1";
        
        $sentencia = $this->conexion->prepare($sql);
        $sentencia->execute([$id_incidencia]);
        $supervisor = $sentencia->fetch(PDO::FETCH_OBJ);
        
        if ($supervisor) {
            // Aquí puedes implementar:
            // 1. Email de notificación
            // 2. Mensaje en sistema
            // 3. Notificación push
            
            error_log("NOTIFICACIÓN: Incidencia #$id_incidencia rechazada por solicitante. Supervisor: {$supervisor->username}");
            
            // Registrar auditoría
            $this->auditoria->registrar(
                "incidencia rechazada por solicitante", 
                $_SESSION['username'] ?? 'sistema',
                "ID: $id_incidencia, Razon: " . substr($razon, 0, 100)
            );
        }
        
        return true;
    } catch (PDOException $e) {
        error_log("Error notificar rechazo solicitante: " . $e->getMessage());
        return false;
    }
}

    /**
     * Obtener estadísticas de fases completadas
     */
    public function obtenerEstadisticasFases($id_trabajador = null, $fecha_inicio = null, $fecha_fin = null) {
        try {
            $where = "WHERE i.estado = 'Finalizada'";
            $params = [];

            if ($id_trabajador) {
                $where .= " AND i.id_trabajador_asignado = ?";
                $params[] = $id_trabajador;
            }

            if ($fecha_inicio) {
                $where .= " AND i.fecha_finalizacion >= ?";
                $params[] = $fecha_inicio;
            }

            if ($fecha_fin) {
                $where .= " AND i.fecha_finalizacion <= ?";
                $params[] = $fecha_fin;
            }

            $sql = "SELECT 
                    COUNT(*) as total_incidencias,
                    AVG(TIMESTAMPDIFF(HOUR, i.fecha, i.fecha_finalizacion)) as tiempo_promedio_horas,
                    COUNT(if.id_seguimiento) as total_fases,
                    SUM(CASE WHEN if.estado = 'aprobada' THEN 1 ELSE 0 END) as fases_aprobadas,
                    SUM(CASE WHEN if.estado = 'rechazada' THEN 1 ELSE 0 END) as fases_rechazadas
                FROM incidencias i
                LEFT JOIN incidencia_fases if ON i.id_incidencia = if.id_incidencia
                $where";

            $sentencia = $this->conexion->prepare($sql);
            $sentencia->execute($params);
            return $sentencia->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log("Error obtener estadisticas fases: " . $e->getMessage());
            return null;
        }
    }

/**
 * Obtener historial completo de una incidencia - VERSION DEPURADA
 */
/**
 * Obtener historial simplificado de una incidencia
 */
public function obtenerHistorialCompleto($id_incidencia) {
    try {
        $historial = [];
        
        // 1. Obtener información básica de la incidencia
        $sql_info = "SELECT i.*, 
                    de.nombre as depto_emisor,
                    dr.nombre as depto_receptor,
                    u.username as creador
                FROM incidencias i
                JOIN departamentos de ON i.departamento_emisor = de.id_departamento
                JOIN departamentos dr ON i.departamento_receptor = dr.id_departamento
                JOIN usuarios u ON i.id_firma_usuario = u.id_usuario
                WHERE i.id_incidencia = ?";
        
        $stmt_info = $this->conexion->prepare($sql_info);
        $stmt_info->execute([$id_incidencia]);
        $info = $stmt_info->fetch(PDO::FETCH_OBJ);
        
        if ($info) {
            $historial[] = [
                'tipo' => 'creacion',
                'titulo' => 'Incidencia creada',
                'descripcion' => "Incidencia #$id_incidencia creada por {$info->creador}",
                'fecha' => $info->fecha . ' 00:00:00',
                'detalles' => [
                    'Descripcion' => $info->descripcion,
                    'Ubicacion' => $info->ubicacion,
                    'Prioridad' => $info->prioridad,
                    'Departamento emisor' => $info->depto_emisor,
                    'Departamento receptor' => $info->depto_receptor
                ]
            ];
        }
        
        // 2. Obtener fases completadas
        $sql_fases = "SELECT 
                    f.nombre_fase,
                    if.estado,
                    if.fecha_completado,
                    if.fecha_aprobacion,
                    if.comentarios_obrero,
                    if.observaciones,
                    u.username as supervisor
                FROM incidencia_fases if
                JOIN fases_incidencia f ON if.id_fase = f.id_fase
                LEFT JOIN usuarios u ON if.id_supervisor_aprobador = u.id_usuario
                WHERE if.id_incidencia = ?
                ORDER BY f.orden";
        
        $stmt_fases = $this->conexion->prepare($sql_fases);
        $stmt_fases->execute([$id_incidencia]);
        $fases = $stmt_fases->fetchAll(PDO::FETCH_OBJ);
        
        foreach ($fases as $fase) {
            if ($fase->fecha_completado) {
                $historial[] = [
                    'tipo' => 'fase_completada',
                    'titulo' => "Fase completada: {$fase->nombre_fase}",
                    'descripcion' => "Fase {$fase->nombre_fase} marcada como completada",
                    'fecha' => $fase->fecha_completado,
                    'detalles' => [
                        'Comentarios' => $fase->comentarios_obrero,
                        'Estado' => ucfirst($fase->estado)
                    ]
                ];
            }
            
            if ($fase->fecha_aprobacion) {
                $accion = $fase->estado === 'aprobada' ? 'aprobada' : 'rechazada';
                $historial[] = [
                    'tipo' => 'fase_' . $accion,
                    'titulo' => "Fase $accion: {$fase->nombre_fase}",
                    'descripcion' => "Fase {$fase->nombre_fase} $accion por supervisor",
                    'fecha' => $fase->fecha_aprobacion,
                    'usuario' => $fase->supervisor,
                    'detalles' => [
                        'Observaciones' => $fase->observaciones
                    ]
                ];
            }
        }
        
        // 3. Obtener confirmación del solicitante (si existe)
        try {
            $sql_confirmacion = "SELECT ic.*, u.username 
                               FROM incidencia_conformidad ic
                               JOIN usuarios u ON ic.id_usuario_solicitante = u.id_usuario
                               WHERE ic.id_incidencia = ?";
            
            $stmt_confirmacion = $this->conexion->prepare($sql_confirmacion);
            $stmt_confirmacion->execute([$id_incidencia]);
            $confirmacion = $stmt_confirmacion->fetch(PDO::FETCH_OBJ);
            
            if ($confirmacion) {
                $estado = $confirmacion->confirmada ? 'ACEPTADA' : 'RECHAZADA';
                $historial[] = [
                    'tipo' => 'confirmacion_solicitante',
                    'titulo' => "Confirmación: $estado",
                    'descripcion' => "El solicitante ha " . ($confirmacion->confirmada ? 'aceptado' : 'rechazado') . " el trabajo",
                    'fecha' => $confirmacion->fecha_confirmacion,
                    'usuario' => $confirmacion->username,
                    'detalles' => [
                        'Calificacion' => $confirmacion->calificacion ? "{$confirmacion->calificacion}/5" : 'Sin calificar',
                        'Comentarios' => $confirmacion->comentarios
                    ]
                ];
            }
        } catch (Exception $e) {
            // Tabla no existe, omitir
        }
        
        // 4. Ordenar por fecha
        usort($historial, function($a, $b) {
            return strtotime($a['fecha']) - strtotime($b['fecha']);
        });
        
        return $historial;
        
    } catch (PDOException $e) {
        error_log("Error obtener historial: " . $e->getMessage());
        return [];
    }
}
}