<?php
// /incidencias/obtener_trabajadores_ajax.php
session_start();
require_once __DIR__ . "/../conex.php";
require_once __DIR__ . "/../user/gestorsesion.php";

GestorSesiones::iniciar();

// Verificar que el usuario tenga permisos
$nivel_usuario = GestorSesiones::get('nivel');
$niveles_permitidos = ['admin', 'supmantenimiento', 'superadministrador'];

if (!in_array($nivel_usuario, $niveles_permitidos)) {
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$tipo = $_GET['tipo'] ?? '';

// Función para obtener trabajadores con sus aptitudes y conteo de incidencias
function obtenerTrabajadoresConAptitudes($conexion, $tipo_incidencia = null) {
    $sql = "
        SELECT 
            t.id_trabajador,
            t.nombre,
            t.apellido,
            d.nombre as departamento,
            (
                SELECT GROUP_CONCAT(CONCAT(dt.aptitud, ' (', dt.nivel_experiencia, ')') SEPARATOR ', ')
                FROM detalle_trabajador dt
                WHERE dt.id_trabajador = t.id_trabajador
            ) as aptitudes,
            (
                SELECT COUNT(*) 
                FROM incidencias i 
                WHERE i.id_trabajador_asignado = t.id_trabajador 
                AND i.estado NOT IN ('Finalizada', 'Rechazada')
            ) as incidencias_asignadas
        FROM trabajadores t
        LEFT JOIN departamentos d ON t.id_departamento = d.id_departamento
        WHERE 1=1
    ";
    
    $params = [];
    
    // Filtrar por aptitud si se especifica un tipo de incidencia y no es 'General'
    if ($tipo_incidencia && $tipo_incidencia !== 'General') {
        $sql .= " AND EXISTS (
            SELECT 1 FROM detalle_trabajador dt 
            WHERE dt.id_trabajador = t.id_trabajador 
            AND dt.aptitud = ?
        )";
        $params[] = $tipo_incidencia;
    }
    
    $sql .= " ORDER BY incidencias_asignadas ASC, t.nombre ASC";
    
    $stmt = $conexion->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$trabajadores = obtenerTrabajadoresConAptitudes($conexion, $tipo);

echo json_encode([
    'trabajadores' => $trabajadores,
    'total' => count($trabajadores),
    'tipo' => $tipo
]);
?>