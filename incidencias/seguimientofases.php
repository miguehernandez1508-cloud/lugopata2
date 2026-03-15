<?php
// /incidencias/seguimientofases.php - Sistema de seguimiento por fases con confirmación del solicitante
session_start();
include_once __DIR__ . "/../encabezado.php";
require_once "incidencia.php";
require_once __DIR__ . "/../user/gestorsesion.php";
require_once __DIR__ . "/../trabajador/trabajador.php";

GestorSesiones::iniciar();

if (!isset($_GET['id'])) {
    header("Location: listarincidencias.php");
    exit;
}

$id_incidencia = (int)$_GET['id'];
$id_usuario = GestorSesiones::get('id_usuario');
$nivel_usuario = GestorSesiones::get('nivel');

$incObj = new Incidencia($conexion);
$incidencia = $incObj->obtener($id_incidencia);

if (!$incidencia) {
    echo "<div class='alert-error'>Incidencia no encontrada.</div>";
    exit;
}

// Verificar permisos: solo el trabajador asignado o supervisores pueden acceder
$puede_acceder = false;
$modo_solo_lectura = false;

if ($nivel_usuario === 'admin' || $nivel_usuario === 'supmantenimiento' || $nivel_usuario === 'superadministrador') {
    $puede_acceder = true;
    $modo_solo_lectura = false;
} elseif ($nivel_usuario === 'obmantenimiento') {
    $trabajador_sesion = GestorSesiones::get('id_trabajador');
    if ($trabajador_sesion && $incidencia->id_trabajador_asignado == $trabajador_sesion) {
        $puede_acceder = true;
        $modo_solo_lectura = false;
    }
}

// Permitir acceso en modo solo lectura al usuario que creó la incidencia
if (!$puede_acceder) {
    if ($incidencia->id_firma_usuario == $id_usuario) {
        $puede_acceder = true;
        $modo_solo_lectura = true;
    }
}

if (!$puede_acceder) {
    http_response_code(403);
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Acceso Denegado</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }
            .error-container {
                background: white;
                border-radius: 20px;
                padding: 50px 40px;
                text-align: center;
                max-width: 450px;
                width: 100%;
                box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                animation: slideUp 0.5s ease;
            }
            @keyframes slideUp {
                from { opacity: 0; transform: translateY(30px); }
                to { opacity: 1; transform: translateY(0); }
            }
            .error-icon {
                width: 90px;
                height: 90px;
                background: #fee2e2;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 25px;
                font-size: 45px;
            }
            .error-code {
                font-size: 72px;
                font-weight: bold;
                color: #dc2626;
                line-height: 1;
                margin-bottom: 10px;
            }
            .error-title {
                font-size: 24px;
                color: #1f2937;
                margin-bottom: 15px;
                font-weight: 600;
            }
            .error-message {
                color: #6b7280;
                font-size: 16px;
                line-height: 1.6;
                margin-bottom: 30px;
            }
            .btn-back {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                padding: 14px 28px;
                background: #dc2626;
                color: white;
                text-decoration: none;
                border-radius: 10px;
                font-weight: 500;
                transition: all 0.3s ease;
            }
            .btn-back:hover {
                background: #b91c1c;
                transform: translateY(-2px);
                box-shadow: 0 10px 20px rgba(220, 38, 38, 0.3);
            }
        </style>
    </head>
    <body>
        <div class="error-container">
            <div class="error-icon"><img src="/lugopata/assets/resources/cancelar.png" alt="Error Icon" width="90" height="90"></div>
            <div class="error-code">403</div>
            <h1 class="error-title">Acceso Denegado</h1>
            <p class="error-message">
                No tiene permisos para acceder a esta incidencia.<br>
                Si cree que esto es un error, contacte al administrador.
            </p>
            <a href="javascript:history.back()" class="btn-back">← Volver atrás</a>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// ========== OBTENER FASES DE LA BASE DE DATOS ========== //
function obtenerFasesIncidencia($conexion, $id_incidencia) {
    $sql = "SELECT sf.*, f.nombre_fase, f.descripcion, f.orden, f.requiere_evidencia, f.seguimiento_secuencial
            FROM incidencia_fases sf 
            INNER JOIN fases_incidencia f ON sf.id_fase = f.id_fase 
            WHERE sf.id_incidencia = ? 
            ORDER BY f.orden ASC";
    
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id_incidencia]);
    $fases = $stmt->fetchAll(PDO::FETCH_OBJ);
    $stmt->closeCursor();
    
    return $fases;
}

// ========== NUEVAS FUNCIONES PARA CONTROL SECUENCIAL ========== //

/**
 * Verificar si la fase anterior está APROBADA (para fases secuenciales)
 * MODIFICACIÓN: Ahora requiere que esté APROBADA, no solo completada
 */
function puedeCompletarFase($conexion, $id_incidencia, $orden_fase_actual) {
    // Si es la primera fase, siempre se puede completar
    if ($orden_fase_actual == 1) {
        return true;
    }
    
    // Buscar fase anterior
    $sql = "SELECT sf.*, f.orden, f.seguimiento_secuencial
            FROM incidencia_fases sf 
            INNER JOIN fases_incidencia f ON sf.id_fase = f.id_fase 
            WHERE sf.id_incidencia = ? 
            AND f.orden = ?";
    
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id_incidencia, $orden_fase_actual - 1]);
    $fase_anterior = $stmt->fetch(PDO::FETCH_OBJ);
    $stmt->closeCursor();
    
    // Si no hay fase anterior o no es secuencial, permitir
    if (!$fase_anterior || !$fase_anterior->seguimiento_secuencial) {
        return true;
    }
    
    // MODIFICACIÓN: Para fases secuenciales, la fase anterior debe estar APROBADA
    // No basta con que esté "completada", debe ser aprobada por el supervisor
    return $fase_anterior->estado === 'aprobada';
}

/**
 * Verificar si la fase anterior está completada (para mostrar mensajes)
 * Función auxiliar para diferenciar entre "completada" y "aprobada"
 */
function faseAnteriorCompletada($conexion, $id_incidencia, $orden_fase_actual) {
    if ($orden_fase_actual == 1) {
        return true;
    }
    
    $sql = "SELECT sf.estado
            FROM incidencia_fases sf 
            INNER JOIN fases_incidencia f ON sf.id_fase = f.id_fase 
            WHERE sf.id_incidencia = ? 
            AND f.orden = ?";
    
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id_incidencia, $orden_fase_actual - 1]);
    $fase_anterior = $stmt->fetch(PDO::FETCH_OBJ);
    $stmt->closeCursor();
    
    return $fase_anterior && $fase_anterior->estado === 'completada';
}

/**
 * Obtener información de la fase anterior bloqueante
 * MODIFICACIÓN: Ahora indica claramente que necesita estar APROBADA
 */
function obtenerFaseAnteriorBloqueante($conexion, $id_incidencia, $orden_actual) {
    $sql = "SELECT f.nombre_fase, f.orden, sf.estado, f.seguimiento_secuencial
            FROM incidencia_fases sf 
            INNER JOIN fases_incidencia f ON sf.id_fase = f.id_fase 
            WHERE sf.id_incidencia = ? 
            AND f.orden < ? 
            AND f.seguimiento_secuencial = 1
            ORDER BY f.orden DESC
            LIMIT 1";
    
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id_incidencia, $orden_actual]);
    $fase = $stmt->fetch(PDO::FETCH_OBJ);
    $stmt->closeCursor();
    
    return $fase;
}

/**
 * Verificar si hay fases pendientes de aprobación antes de la fase actual
 * MODIFICACIÓN: Para detectar si la fase anterior está completada pero no aprobada
 */
function fasesPendientesAprobacion($conexion, $id_incidencia, $orden_actual) {
    $sql = "SELECT f.nombre_fase, f.orden, sf.estado
            FROM incidencia_fases sf 
            INNER JOIN fases_incidencia f ON sf.id_fase = f.id_fase 
            WHERE sf.id_incidencia = ? 
            AND f.orden < ? 
            AND sf.estado = 'completada'
            AND f.seguimiento_secuencial = 1
            ORDER BY f.orden DESC";
    
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id_incidencia, $orden_actual]);
    $fases = $stmt->fetchAll(PDO::FETCH_OBJ);
    $stmt->closeCursor();
    
    return $fases;
}

// ========== FUNCIONES NUEVAS PARA CONFIRMACIÓN ========== //
function verificarTodasFasesTecnicasAprobadas($conexion, $id_incidencia) {
    $sql = "SELECT COUNT(*) as total, 
                   SUM(CASE WHEN inc_f.estado = 'aprobada' THEN 1 ELSE 0 END) as aprobadas
            FROM incidencia_fases inc_f
            JOIN fases_incidencia f ON inc_f.id_fase = f.id_fase
            WHERE inc_f.id_incidencia = ?
            AND f.nombre_fase != 'Confirmación del Solicitante'";
    
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id_incidencia]);
    $resultado = $stmt->fetch(PDO::FETCH_OBJ);
    $stmt->closeCursor();
    
    return ($resultado->total > 0 && $resultado->total == $resultado->aprobadas);
}

function existeFaseConfirmacion($conexion, $id_incidencia) {
    $sql = "SELECT COUNT(*) as existe
            FROM incidencia_fases inc_f
            JOIN fases_incidencia f ON inc_f.id_fase = f.id_fase
            WHERE inc_f.id_incidencia = ?
            AND f.nombre_fase = 'Confirmación del Solicitante'";
    
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id_incidencia]);
    $resultado = $stmt->fetch(PDO::FETCH_OBJ);
    $stmt->closeCursor();
    
    return ($resultado->existe > 0);
}

function crearFaseConfirmacionAutomatica($conexion, $id_incidencia) {
    // Verificar si ya existe fase de confirmación
    if (existeFaseConfirmacion($conexion, $id_incidencia)) {
        return false;
    }
    
    // Verificar si todas las fases técnicas están aprobadas
    if (!verificarTodasFasesTecnicasAprobadas($conexion, $id_incidencia)) {
        return false;
    }
    
    // Obtener ID de la fase de confirmación
    $sql_fase = "SELECT id_fase FROM fases_incidencia WHERE nombre_fase = 'Confirmación del Solicitante' LIMIT 1";
    $stmt_fase = $conexion->prepare($sql_fase);
    $stmt_fase->execute();
    $fase_confirmacion = $stmt_fase->fetch(PDO::FETCH_OBJ);
    $stmt_fase->closeCursor();
    
    if (!$fase_confirmacion) {
        return false;
    }
    
    // Crear fase de confirmación
    $sql_insert = "INSERT INTO incidencia_fases (id_incidencia, id_fase, estado) VALUES (?, ?, 'pendiente')";
    $stmt_insert = $conexion->prepare($sql_insert);
    $resultado = $stmt_insert->execute([$id_incidencia, $fase_confirmacion->id_fase]);
    $stmt_insert->closeCursor();
    
    return $resultado;
}

// Obtener fases directamente
$progreso_fases = obtenerFasesIncidencia($conexion, $id_incidencia);
$mensaje = "";

// ========== PROCESAR CREACIÓN MANUAL DE FASES PRIMERO ========== //
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_fases_manual'])) {
    $tipo_manual = $_POST['tipo_manual'];
    
    if ($incObj->crearFasesParaIncidencia($id_incidencia, $tipo_manual)) {
        $mensaje = "<div class='alert-success'>Fases creadas manualmente para tipo '$tipo_manual'.</div>";
        $progreso_fases = obtenerFasesIncidencia($conexion, $id_incidencia);
    } else {
        $mensaje = "<div class='alert-error'>Error al crear fases manualmente. Revisa los logs.</div>";
    }
}

// Configuración de directorio para evidencias
$dirBase = __DIR__ . "/../assets/incidencias/";
if (!is_dir($dirBase)) mkdir($dirBase, 0777, true);
$dirInc = $dirBase . $id_incidencia . "/";
if (!is_dir($dirInc)) mkdir($dirInc, 0777, true);

// Procesar completado de fase (para obreros)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['completar_fase'])) {
    $id_fase = (int)$_POST['id_fase'];
    $comentarios_obrero = trim($_POST['comentarios_obrero'] ?? '');
    $evidencias = [];
    
    // VERIFICAR SI LA FASE PUEDE SER COMPLETADA (CONTROL SECUENCIAL)
    $sql_fase_info = "SELECT f.orden, f.seguimiento_secuencial 
                     FROM fases_incidencia f 
                     WHERE f.id_fase = ?";
    $stmt_fase_info = $conexion->prepare($sql_fase_info);
    $stmt_fase_info->execute([$id_fase]);
    $fase_info = $stmt_fase_info->fetch(PDO::FETCH_OBJ);
    $stmt_fase_info->closeCursor();
    
    $bloqueo_secuencial = false;
    
    if ($fase_info && $fase_info->seguimiento_secuencial) {
        // Si es secuencial, verificar fase anterior
        if (!puedeCompletarFase($conexion, $id_incidencia, $fase_info->orden)) {
            $fase_anterior = obtenerFaseAnteriorBloqueante($conexion, $id_incidencia, $fase_info->orden);
            if ($fase_anterior) {
                // MODIFICACIÓN: Mensaje más específico indicando que necesita APROBACIÓN
                $estado_requerido = "APROBADA";
                $estado_actual = ucfirst($fase_anterior->estado);
                
                if ($fase_anterior->estado === 'completada') {
                    $mensaje_bloqueo = "La fase anterior '{$fase_anterior->nombre_fase}' (Fase {$fase_anterior->orden}) está completada pero necesita ser aprobada por el supervisor antes de continuar.";
                } else {
                    $mensaje_bloqueo = "La fase anterior '{$fase_anterior->nombre_fase}' (Fase {$fase_anterior->orden}) debe estar aprobada. Estado actual: $estado_actual";
                }
                
                $mensaje = "<div class='alert-error'>$mensaje_bloqueo</div>";
                $bloqueo_secuencial = true;
            }
        }
    }
    
    // Si hay bloqueo secuencial, no continuar
    if ($bloqueo_secuencial) {
        // Recargar fases para mostrar estado actual
        $progreso_fases = obtenerFasesIncidencia($conexion, $id_incidencia);
        // Salir del procesamiento
        goto mostrar_pagina;
    }
    
    // Procesar imágenes subidas
    if (isset($_FILES['evidencias_fase']) && !empty($_FILES['evidencias_fase']['name'][0])) {
        $files = $_FILES['evidencias_fase'];
        
        for ($i = 0; $i < count($files['name']); $i++) {
            $tmp = $files['tmp_name'][$i];
            $name = preg_replace('/[^A-Za-z0-9\-_\.]/', '_', $files['name'][$i]);
            
            if (!is_uploaded_file($tmp)) continue;
            
            $info = getimagesize($tmp);
            if ($info === false) continue;
            
            $mime = $info['mime'];
            switch ($mime) {
                case 'image/jpeg': $src = imagecreatefromjpeg($tmp); break;
                case 'image/png': $src = imagecreatefrompng($tmp); break;
                case 'image/gif': $src = imagecreatefromgif($tmp); break;
                default: $src = null; break;
            }
            if (!$src) continue;
            
            $width = imagesx($src);
            $height = imagesy($src);
            $maxW = 1000;
            
            if ($width > $maxW) {
                $ratio = $height / $width;
                $newW = $maxW;
                $newH = intval($newW * $ratio);
            } else {
                $newW = $width;
                $newH = $height;
            }
            
            $dst = imagecreatetruecolor($newW, $newH);
            
            if ($mime === 'image/png' || $mime === 'image/gif') {
                imagecolortransparent($dst, imagecolorallocatealpha($dst, 0, 0, 0, 127));
                imagealphablending($dst, false);
                imagesavealpha($dst, true);
            }
            
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $width, $height);
            
            $filename = "fase_" . $id_fase . "_" . time() . "_" . $i . ".jpg";
            $rutaFinal = $dirInc . $filename;
            imagejpeg($dst, $rutaFinal, 75);
            
            imagedestroy($src);
            imagedestroy($dst);
            
            $rutaWeb = "/lugopata/assets/incidencias/{$id_incidencia}/" . $filename;
            $evidencias[] = $rutaWeb;
        }
    }
    
    // Actualizar fase en la base de datos
    $sql = "UPDATE incidencia_fases 
            SET estado = 'completada', 
                fecha_completado = NOW(), 
                evidencias = ?,
                comentarios_obrero = ?
            WHERE id_incidencia = ? AND id_fase = ?";
    
    $stmt = $conexion->prepare($sql);
    $evidencias_json = !empty($evidencias) ? json_encode($evidencias) : NULL;
    $stmt->execute([$evidencias_json, $comentarios_obrero, $id_incidencia, $id_fase]);
    
    if ($stmt->rowCount() > 0) {
        $mensaje = "<div class='alert-success'>Fase completada correctamente. Esperando validación del supervisor.</div>";
    } else {
        $mensaje = "<div class='alert-error'>Error al completar la fase.</div>";
    }
    $stmt->closeCursor();
    
    $progreso_fases = obtenerFasesIncidencia($conexion, $id_incidencia);
}

// Procesar validación de fase (para supervisores)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['validar_fase'])) {
    $id_fase = (int)$_POST['id_fase'];
    $aprobada = $_POST['accion'] === 'aprobar';
    $observaciones = trim($_POST['observaciones']);
    
    $nuevo_estado = $aprobada ? 'aprobada' : 'rechazada';
    
    $sql = "UPDATE incidencia_fases 
            SET estado = ?, 
                fecha_aprobacion = NOW(), 
                id_supervisor_aprobador = ?, 
                observaciones = ? 
            WHERE id_incidencia = ? AND id_fase = ?";
    
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$nuevo_estado, $id_usuario, $observaciones, $id_incidencia, $id_fase]);
    
    if ($stmt->rowCount() > 0) {
        $accion_texto = $aprobada ? 'aprobada' : 'rechazada';
        $mensaje = "<div class='alert-success'>Fase $accion_texto correctamente.</div>";
        
        // SI LA FASE FUE APROBADA, VERIFICAR SI TODAS LAS FASES TÉCNICAS ESTÁN APROBADAS
        if ($aprobada) {
            $progreso_fases_actualizado = obtenerFasesIncidencia($conexion, $id_incidencia);
            
            // Verificar si todas las fases TÉCNICAS están aprobadas (excluyendo confirmación)
            $todas_tecnicas_aprobadas = verificarTodasFasesTecnicasAprobadas($conexion, $id_incidencia);
            $existe_confirmacion = existeFaseConfirmacion($conexion, $id_incidencia);
            
            // Si todas las fases técnicas están aprobadas y NO existe fase de confirmación, crearla
            if ($todas_tecnicas_aprobadas && !$existe_confirmacion) {
                if (crearFaseConfirmacionAutomatica($conexion, $id_incidencia)) {
                    $mensaje .= "<div class='alert-success'>Todas las fases técnicas aprobadas. Se ha creado la fase de confirmación del solicitante.</div>";
                    // Recargar fases
                    $progreso_fases = obtenerFasesIncidencia($conexion, $id_incidencia);
                }
            } else {
                // Contar progreso
                $fases_tecnicas = array_filter($progreso_fases_actualizado, function($fase) {
                    return $fase->nombre_fase !== 'Confirmación del Solicitante';
                });
                $fases_aprobadas = 0;
                foreach ($fases_tecnicas as $fase) {
                    if ($fase->estado === 'aprobada') $fases_aprobadas++;
                }
                $mensaje .= "<div class='alert-success'>Progreso actual: $fases_aprobadas/" . count($fases_tecnicas) . " fases técnicas aprobadas.</div>";
            }
        }
    } else {
        $mensaje = "<div class='alert-error'>Error al validar la fase.</div>";
    }
    $stmt->closeCursor();
    
    $progreso_fases = obtenerFasesIncidencia($conexion, $id_incidencia);
}

// ========== PROCESAR CONFIRMACIÓN DEL SOLICITANTE ========== //
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar_incidencia'])) {
    $confirmada = ($_POST['accion'] === 'aceptar');
    $calificacion = isset($_POST['calificacion']) ? (int)$_POST['calificacion'] : null;
    $comentarios = trim($_POST['comentarios_solicitante'] ?? '');

        if ($confirmada && empty($calificacion)) {
        $mensaje = "<div class='alert-error'>Debe seleccionar una calificación antes de aceptar la incidencia.</div>";
        goto mostrar_pagina;
    }
    
    // Verificar que el usuario actual sea el solicitante original
    if ($incidencia->id_firma_usuario == $id_usuario) {
        try {
            $conexion->beginTransaction();
            
            // 1. Registrar conformidad
            $sql_conformidad = "INSERT INTO incidencia_conformidad 
                              (id_incidencia, id_usuario_solicitante, confirmada, calificacion, comentarios, fecha_confirmacion)
                              VALUES (?, ?, ?, ?, ?, NOW())
                              ON DUPLICATE KEY UPDATE 
                              confirmada = VALUES(confirmada),
                              calificacion = VALUES(calificacion),
                              comentarios = VALUES(comentarios),
                              fecha_confirmacion = NOW()";
            
            $stmt_conformidad = $conexion->prepare($sql_conformidad);
            $stmt_conformidad->execute([$id_incidencia, $id_usuario, $confirmada ? 1 : 0, $calificacion, $comentarios]);
            
            // 2. Actualizar fase de confirmación
            $sql_fase = "UPDATE incidencia_fases 
                        SET estado = ?, 
                            fecha_completado = NOW(),
                            observaciones = ?
                        WHERE id_incidencia = ? 
                        AND id_fase = (SELECT id_fase FROM fases_incidencia WHERE nombre_fase = 'Confirmación del Solicitante' LIMIT 1)";
            
            $estado_fase = $confirmada ? 'aprobada' : 'rechazada';
            $stmt_fase = $conexion->prepare($sql_fase);
            $stmt_fase->execute([$estado_fase, $comentarios, $id_incidencia]);
            
            // 3. Actualizar estado de la incidencia
            if ($confirmada) {
                $sql_incidencia = "UPDATE incidencias 
                                 SET estado = 'Finalizada', 
                                     fecha_finalizacion = NOW()
                                 WHERE id_incidencia = ?";
                $mensaje_texto = "Incidencia confirmada y finalizada exitosamente. Gracias por su retroalimentacion!";
            } else {
                $sql_incidencia = "UPDATE incidencias 
                                 SET estado = 'Rechazada por Solicitante'
                                 WHERE id_incidencia = ?";
                $mensaje_texto = "Incidencia rechazada. El supervisor sera notificado para revisar.";
                
                // Notificar al supervisor
                if (method_exists($incObj, 'notificarRechazoSolicitante')) {
                    $incObj->notificarRechazoSolicitante($id_incidencia, $comentarios);
                }
            }
            
            $stmt_incidencia = $conexion->prepare($sql_incidencia);
            $stmt_incidencia->execute([$id_incidencia]);
            
            $conexion->commit();
            
            $mensaje = "<div class='alert-success'>$mensaje_texto</div>";
            
            // Recargar datos
            $incidencia = $incObj->obtener($id_incidencia);
            $progreso_fases = obtenerFasesIncidencia($conexion, $id_incidencia);
            
        } catch (Exception $e) {
            $conexion->rollBack();
            $mensaje = "<div class='alert-error'>Error al procesar confirmacion: " . $e->getMessage() . "</div>";
        }
    } else {
        $mensaje = "<div class='alert-error'>Solo el usuario que creo la incidencia puede confirmarla.</div>";
    }
}

// ========== OBTENER DATOS DE CONFIRMACIÓN SI EXISTEN ========== //
$sql_conformidad = "SELECT * FROM incidencia_conformidad WHERE id_incidencia = ?";
$stmt_conformidad = $conexion->prepare($sql_conformidad);
$stmt_conformidad->execute([$id_incidencia]);
$conformidad = $stmt_conformidad->fetch(PDO::FETCH_OBJ);
$stmt_conformidad->closeCursor();

// Verificar si es el solicitante original
$es_solicitante_original = ($incidencia->id_firma_usuario == $id_usuario);

// Verificar y crear fase de confirmación automáticamente si es necesario
if (!$conformidad && verificarTodasFasesTecnicasAprobadas($conexion, $id_incidencia) && !existeFaseConfirmacion($conexion, $id_incidencia)) {
    crearFaseConfirmacionAutomatica($conexion, $id_incidencia);
    $progreso_fases = obtenerFasesIncidencia($conexion, $id_incidencia);
}

// Obtener historial completo de la incidencia
$historial_completo = $incObj->obtenerHistorialCompleto($id_incidencia);

// Etiqueta para salto condicional
mostrar_pagina:
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Seguimiento por Fases - Incidencia #<?= $id_incidencia ?></title>
       <style>
        /* ===== RESET Y VARIABLES ===== */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background: url("/lugopata/assets/resources/fondoR.png") no-repeat center center fixed;
            background-size: cover;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            min-height: 100vh;
            padding: 15px;
        }

        /* ===== CONTENEDOR PRINCIPAL ===== */
        .form-wrapper {
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
        }

        /* ===== TARJETA DE TÍTULO ===== */
        .title-card {
            background: white;
            padding: 20px;
            border-radius: 15px;
            border: 2px solid #ccc;
            margin-bottom: 20px;
            width: 100%;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            text-align: center;
        }

        .title-card h1 {
            color: #333;
            font-weight: bold;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
            margin: 0;
            font-size: clamp(1.1rem, 4vw, 1.6rem);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .title-card h1 img {
            width: 40px;
            height: 40px;
        }

        .title-card p {
            color: #666;
            margin-top: 10px;
            font-size: clamp(13px, 2vw, 15px);
        }

        /* ===== TARJETA DE CONTENIDO ===== */
        .content-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            border: 2px solid #ccc;
            width: 100%;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        /* ===== ALERTAS ===== */
        .alert-error {
            background-color: #fee2e2;
            color: #dc2626;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            border: 1px solid #fecaca;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background-color: #d1fae5;
            color: #059669;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            border: 1px solid #a7f3d0;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-warning {
            background-color: #fef3c7;
            color: #d97706;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            border: 1px solid #fde68a;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-info {
            background-color: #e0f2fe;
            color: #0284c7;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            border: 1px solid #bae6fd;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* ===== INFO GRID ===== */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }

        .info-item {
            padding: 15px;
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .info-item:hover {
            background: #e9ecef;
            transform: translateY(-2px);
        }

        .info-item strong {
            color: #495057;
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-item strong img {
            width: 24px;
            height: 24px;
        }

        /* ===== PROGRESS BAR MEJORADO ===== */
        .progress-container {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            border: 1px solid #e9ecef;
            margin-bottom: 30px;
        }

        .progress-container h3 {
            margin: 0 0 15px 0;
            color: #333;
            font-size: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .progress-container h3 img {
            width: 28px;
            height: 28px;
        }

        .progress-bar {
            display: flex;
            height: 60px;
            background: #e9ecef;
            border-radius: 30px;
            overflow: hidden;
            margin-bottom: 15px;
            border: 2px solid #dee2e6;
        }

        .progress-step {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 12px;
            text-align: center;
            transition: all 0.3s ease;
            position: relative;
            cursor: pointer;
            flex-direction: column;
            min-width: 60px;
        }

        .progress-step:hover {
            transform: scale(1.05);
            z-index: 10;
            box-shadow: 0 0 10px rgba(0,0,0,0.2);
        }

        .progress-step.pendiente { background: #6c757d; }
        .progress-step.completada { background: #fd7e14; }
        .progress-step.aprobada { background: #059669; }
        .progress-step.rechazada { background: #dc2626; }
        .progress-step.secuencial { border-left: 3px solid #0d6efd; }
        .progress-step.paralelo { border-left: 2px dashed #6c757d; }

        .badge-secuencial-mini, .badge-paralelo-mini {
            font-size: 9px;
            padding: 2px 5px;
            border-radius: 4px;
            margin-top: 2px;
        }
        .badge-secuencial-mini { background: #0d6efd; color: white; }
        .badge-paralelo-mini { background: #6c757d; color: white; }

        /* ===== LEYENDA ===== */
        .legend-text {
            text-align: center;
            font-size: 14px;
            color: #666;
            padding: 10px;
            background: white;
            border-radius: 8px;
        }

        /* ===== LISTA DE FASES ===== */
        .fases-list h3 {
            margin: 25px 0 15px 0;
            color: #333;
            font-size: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .fases-list h3 img {
            width: 28px;
            height: 28px;
        }

        .fase-item {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            margin-bottom: 25px;
            overflow: hidden;
            background: white;
            transition: all 0.3s ease;
        }

        .fase-item:hover {
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            border-color: #0d6efd;
        }

        .fase-header {
            padding: 20px;
            background: #f8f9fa;
            border-bottom: 2px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .fase-title {
            font-weight: bold;
            font-size: 16px;
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .fase-title img {
            width: 35px;
            height: 35px;
        }

        .fase-estado {
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            min-width: 100px;
            text-align: center;
        }

        .estado-pendiente { background: #6c757d; color: white; }
        .estado-completada { background: #fd7e14; color: white; }
        .estado-aprobada { background: #059669; color: white; }
        .estado-rechazada { background: #dc2626; color: white; }

        .fase-content {
            padding: 25px;
        }

        .fase-descripcion {
            color: #666;
            margin-bottom: 15px;
            font-style: italic;
            line-height: 1.5;
            font-size: 14px;
        }

        .fase-info {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .fase-info-item {
            background: #f8f9fa;
            padding: 5px 12px;
            border-radius: 6px;
            border: 1px solid #e9ecef;
            font-size: 13px;
        }

        /* ===== EVIDENCIAS ===== */
        .evidencias-container {
            margin: 20px 0;
        }

        .evidencias-container strong {
            display: block;
            margin-bottom: 10px;
            color: #333;
        }

        .evidencias-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 10px;
        }

        .evidencia-img {
            width: 100%;
            height: 120px;
            object-fit: cover;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .evidencia-img:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            border-color: #0d6efd;
        }

        .no-evidencias {
            color: #6c757d;
            font-style: italic;
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px dashed #dee2e6;
        }

        /* ===== COMENTARIOS Y OBSERVACIONES ===== */
        .comentario-obrero,
        .observaciones-supervisor,
        .conformidad-info {
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            border-left: 4px solid;
        }

        .comentario-obrero {
            background: #e7f8ff;
            border-left-color: #0d6efd;
        }

        .observaciones-supervisor {
            background: #fef3c7;
            border-left-color: #d97706;
        }

        .conformidad-info {
            background: #e7f8ff;
            border-left-color: #0d6efd;
        }

        /* ===== FORMULARIOS ===== */
        .acciones-container {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            border: 1px solid #e9ecef;
            margin-top: 20px;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            background-color: #fff;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: #0d6efd;
            outline: none;
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
        }

        .form-group textarea {
            min-height: 80px;
            resize: vertical;
        }

        .form-group input[type="file"] {
            padding: 8px;
            background: white;
        }

        /* ===== BOTONES MINIMALISTAS FLAT ===== */
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s ease;
            min-width: 180px;
        }

        .btn-success {
            background-color: #198754 !important;
            color: white;
        }

        .btn-success:hover {
            background-color: #059669;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .btn-danger {
            background-color: #dc2626 !important;
            color: white;
        }

        .btn-danger:hover {
            background-color: #b91c1c;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
        }

        .btn-primary {
            background-color: #6c757d !important;
            color: white;
        }

        .btn-primary:hover {
            background-color: #5a6268;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
        }

        .btn-info {
            background-color: #0d6efd !important;
            color: white;
        }

        .btn-info:hover {
            background-color: #0b5ed7;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
        }

        .btn-warning {
            background-color: #fd7e14 !important;
            color: white;
        }

        .btn-warning:hover {
            background-color: #e96b02;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(253, 126, 20, 0.3);
        }

        .btn:active {
            transform: translateY(0);
        }

        .btn img {
            width: 18px;
            height: 18px;
            filter: brightness(0) invert(1);
        }

        .btn-container {
            display: flex;
            gap: 12px;
            justify-content: center;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .btn-completar-bloqueado {
            background-color: #6c757d;
            color: white;
            opacity: 0.6;
            cursor: not-allowed;
            pointer-events: none;
        }

        /* ===== ESTRELLAS DE CALIFICACIÓN ===== */
        .estrellas-calificacion {
            font-size: 32px;
            color: #ccc;
            cursor: pointer;
            margin: 0 8px;
            transition: all 0.2s;
            display: inline-block;
        }

        .estrellas-calificacion:hover,
        .estrellas-calificacion.seleccionada {
            color: #ffc107;
            transform: scale(1.2);
        }

        /* ===== MENSAJE SIN FASES ===== */
        .no-fases-message {
            background: #fef3c7;
            padding: 25px;
            border-radius: 12px;
            margin: 20px 0;
            text-align: center;
            border: 2px solid #d97706;
        }

        .no-fases-message h4 {
            color: #d97706;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        /* ===== NUEVO MODAL PARA INFORMACIÓN DE FASE ===== */
        .fase-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            z-index: 10000;
            backdrop-filter: blur(3px);
            display: none;
        }

        .fase-modal {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 90%;
            max-width: 550px;
            max-height: 80vh;
            background: white;
            border-radius: 12px;
            box-shadow: 0 15px 50px rgba(0,0,0,0.3);
            z-index: 10001;
            display: none;
            flex-direction: column;
            overflow: hidden;
        }

        .fase-modal-header {
            padding: 20px 25px;
            border-bottom: 1px solid #e9ecef;
            background: #0d6efd;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-shrink: 0;
        }

        .fase-modal-header h2 {
            margin: 0;
            font-size: 20px;
            color: white;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .fase-modal-header h2 img {
            width: 30px;
            height: 30px;
            filter: brightness(0) invert(1);
        }

        .fase-modal-close {
            background: none;
            border: none;
            color: white;
            font-size: 28px;
            cursor: pointer;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }

        .fase-modal-close:hover {
            background: rgba(255,255,255,0.2);
        }

        .fase-modal-body {
            padding: 25px;
            overflow-y: auto;
            flex-grow: 1;
            max-height: 60vh;
        }

        .fase-modal-footer {
            padding: 20px 25px;
            border-top: 1px solid #e9ecef;
            background: #f8f9fa;
            text-align: right;
            flex-shrink: 0;
        }

        .fase-info-detalle {
            margin-bottom: 20px;
        }

        .fase-info-detalle h3 {
            color: #333;
            font-size: 18px;
            margin-bottom: 15px;
            border-bottom: 2px solid #0d6efd;
            padding-bottom: 8px;
        }

        .fase-info-detalle p {
            color: #555;
            line-height: 1.6;
            font-size: 15px;
            margin-bottom: 20px;
        }

        .fase-info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }

        .fase-info-grid-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }

        .fase-info-grid-item strong {
            display: block;
            color: #0d6efd;
            margin-bottom: 5px;
            font-size: 14px;
        }

        .fase-info-grid-item span {
            color: #333;
            font-size: 16px;
            font-weight: 600;
        }

        /* ===== MODAL DE HISTORIAL - CORREGIDO ===== */
        #modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            z-index: 9998;
            backdrop-filter: blur(3px);
        }

        .modal-historial {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 85%;
            max-width: 950px;
            max-height: 85vh;
            background: white;
            border-radius: 12px;
            box-shadow: 0 15px 50px rgba(0,0,0,0.3);
            z-index: 9999;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .modal-historial-header {
            padding: 20px 25px;
            border-bottom: 1px solid #e9ecef;
            background: #059669;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-shrink: 0;
        }

        .modal-historial-header h2 {
            margin: 0;
            font-size: 20px;
            color: white;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .cerrar-modal {
            background: none;
            border: none;
            color: white;
            font-size: 28px;
            cursor: pointer;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }

        .cerrar-modal:hover {
            background: rgba(255,255,255,0.2);
        }

        .modal-historial-body {
            padding: 25px;
            overflow-y: scroll !important;
            flex-grow: 1;
            max-height: 60vh;
        }

        .modal-historial-footer {
            padding: 20px 25px;
            border-top: 1px solid #e9ecef;
            background: #f8f9fa;
            text-align: right;
            flex-shrink: 0;
        }

        /* ===== TIMELINE HISTORIAL ===== */
        .timeline-historial {
            position: relative;
            padding-left: 30px;
        }

        .timeline-historial::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            bottom: 0;
            width: 3px;
            background: #0d6efd;
        }

        .evento-historial {
            position: relative;
            margin-bottom: 25px;
            display: flex;
            align-items: flex-start;
        }

        .evento-marcador {
            position: absolute;
            left: -35px;
            width: 30px;
            height: 30px;
            background: white;
            border: 3px solid #0d6efd;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: bold;
            z-index: 2;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        .evento-contenido {
            flex: 1;
            background: #f8f9fa;
            padding: 18px;
            border-radius: 8px;
            border-left: 4px solid #0d6efd;
            transition: all 0.2s;
        }

        .evento-contenido:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .evento-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .evento-header h4 {
            margin: 0;
            color: #333;
            font-size: 16px;
            font-weight: 600;
        }

        .evento-fecha {
            background: #0d6efd;
            color: white;
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 13px;
            font-weight: bold;
            min-width: 120px;
            text-align: center;
        }

        .evento-descripcion {
            color: #555;
            margin-bottom: 10px;
            line-height: 1.5;
        }

        .evento-detalles {
            background: white;
            padding: 12px;
            border-radius: 6px;
            border: 1px solid #e9ecef;
            margin-top: 10px;
        }

        .detalle-item {
            margin-bottom: 8px;
            padding-bottom: 8px;
            border-bottom: 1px dashed #eee;
            display: flex;
            align-items: flex-start;
        }

        .detalle-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .detalle-item strong {
            color: #495057;
            min-width: 150px;
            display: inline-block;
            font-weight: 600;
        }

        /* ===== MEDIA QUERIES ===== */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }

            .content-card {
                padding: 20px;
            }

            .title-card h1 {
                flex-direction: column;
                gap: 5px;
            }

            .title-card h1 img {
                width: 35px;
                height: 35px;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .fase-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .fase-title {
                flex-wrap: wrap;
            }

            .evidencias-grid {
                grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            }

            .btn {
                /* width: 100%; */
                min-width: auto;
            }

            .btn-container {
                flex-direction: column;
            }

            .modal-historial {
                width: 95%;
            }

            .fase-modal {
                width: 95%;
            }

            .evento-header {
                flex-direction: column;
            }

            .evento-fecha {
                align-self: flex-start;
            }

            .detalle-item {
                flex-direction: column;
            }

            .detalle-item strong {
                min-width: auto;
                margin-bottom: 5px;
            }

            .progress-step {
                min-width: 40px;
                font-size: 10px;
            }
        }

        @media (max-width: 480px) {
            .content-card {
                padding: 15px;
            }

            .fase-content {
                padding: 15px;
            }

            .form-group input,
            .form-group select,
            .form-group textarea {
                padding: 10px 12px;
                font-size: 16px;
            }

            .btn {
                padding: 14px 20px;
                font-size: 16px;
            }

            .modal-historial-header h2 {
                font-size: 18px;
            }
        }
    </style>
</head>
<body>
<div class="form-wrapper">
    <!-- TARJETA DE TÍTULO -->
    <div class="title-card">
        <h1>
            <img src="../assets/resources/lincidencia.png" alt="Seguimiento">
            Seguimiento de Incidencia #<?= $id_incidencia ?>
        </h1>
        <p>Control y seguimiento del progreso de la incidencia</p>
    </div>

    <!-- CONTENIDO -->
    <div class="content-card">
        <?php echo $mensaje; ?>

        <!-- Información general de la incidencia -->
        <div class="info-grid">
            <div class="info-item">
                <strong>
                    <img src="../assets/resources/informacion-del-circulo-de-archivos.png" width="20" style="vertical-align: middle; margin-right: 10px;"> 
                    Descripción:
                </strong><br>
                <?= nl2br(htmlspecialchars($incidencia->descripcion)) ?>
            </div>
            <div class="info-item">
                <strong>
                    <img src="../assets/resources/estructura-del-departamento.png" width="20" style="vertical-align: middle; margin-right: 10px;"> 
                    Información General:
                </strong><br>
                <strong>Asignado a:</strong> 
                <?= $incidencia->trabajador_nombre ? "{$incidencia->trabajador_nombre} {$incidencia->trabajador_apellido}" : "No asignado" ?><br>
                <strong>Estado General:</strong> 
                <span style="font-weight: bold; color: 
                    <?= $incidencia->estado === 'Finalizada' ? '#059669' : 
                       ($incidencia->estado === 'Rechazada' ? '#dc2626' : 
                       ($incidencia->estado === 'Rechazada por Solicitante' ? '#dc2626' :
                       ($incidencia->estado === 'Pendiente' ? '#fd7e14' : '#6c757d'))) ?>">
                    <?= $incidencia->estado ?>
                </span><br>
                <strong>Prioridad:</strong> 
                <span style="color: 
                    <?= $incidencia->prioridad === 'Alta' ? '#dc2626' : 
                       ($incidencia->prioridad === 'Media' ? '#fd7e14' : '#059669') ?>">
                    <?= $incidencia->prioridad ?>
                </span>
                <?php if (!empty($progreso_fases)): ?>
                    <?php
                    $fases_tecnicas = array_filter($progreso_fases, function($fase) {
                        return $fase->nombre_fase !== 'Confirmación del Solicitante';
                    });
                    $fases_aprobadas = 0;
                    $fases_secuenciales = 0;
                    $fases_paralelas = 0;
                    
                    foreach ($fases_tecnicas as $fase) {
                        if ($fase->estado === 'aprobada') $fases_aprobadas++;
                        if ($fase->seguimiento_secuencial) {
                            $fases_secuenciales++;
                        } else {
                            $fases_paralelas++;
                        }
                    }
                    ?>
                    <br><strong><img src="../assets/resources/progreso.png" width="18" style="vertical-align: middle; margin-right: 8px;"> Progreso técnico:</strong> <?= $fases_aprobadas ?>/<?= count($fases_tecnicas) ?> fases aprobadas
                    <br><strong><img src="../assets/resources/categoria.png" width="18" style="vertical-align: middle; margin-right: 8px;"> Tipo de fases:</strong> <?= $fases_secuenciales ?> secuenciales, <?= $fases_paralelas ?> paralelas
                    <?php if ($conformidad): ?>
                        <br><strong><img src="../assets/resources/verificacion-de-escudo.png" width="18" style="vertical-align: middle; margin-right: 8px;"> Confirmación:</strong> 
                        <span style="color: <?= $conformidad->confirmada ? '#059669' : '#dc2626' ?>; font-weight: bold;">
                            <?= $conformidad->confirmada ? 'Aceptada' : 'Rechazada' ?>
                        </span>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Barra de progreso visual -->
        <?php if (!empty($progreso_fases)): ?>
        <div class="progress-container">
            <h3>
                <img src="../assets/resources/grafico-circular.png" alt="Progreso">
                Progreso General
            </h3>
            <div class="progress-bar">
                <?php foreach ($progreso_fases as $fase): ?>
                <div class="progress-step <?= $fase->estado ?> <?= $fase->seguimiento_secuencial ? 'secuencial' : 'paralelo' ?>" 
                     data-fase="<?= htmlspecialchars(json_encode($fase)) ?>"
                     title="<?= $fase->nombre_fase ?> - <?= ucfirst($fase->estado ) ?> - <?= $fase->seguimiento_secuencial ? 'Secuencial' : 'Paralelo' ?>">
                    <?= $fase->orden ?>
                    <?php if ($fase->seguimiento_secuencial): ?>
                        <span class="badge-secuencial-mini">S</span>
                    <?php else: ?>
                        <span class="badge-paralelo-mini">P</span>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            

        </div>
        <?php endif; ?>

        <!-- Mensaje cuando no hay fases -->
        <?php if (empty($progreso_fases)): ?>
        <div class="no-fases-message">
            <h4>
                <img src="../assets/resources/advertencia-de-triangulo.png" width="25" style="vertical-align: middle; margin-right: 10px;"> 
                No se encontraron fases para esta incidencia
            </h4>
            <p><strong>Incidencia #<?= $id_incidencia ?></strong> - Estado: <?= $incidencia->estado ?></p>
            
            <?php if (in_array($nivel_usuario, ['admin', 'supmantenimiento', 'superadministrador'])): ?>
            <form method="post" style="margin-top: 15px;">
                <input type="hidden" name="crear_fases_manual" value="1">
                <div style="display: inline-block; margin: 0 10px;">
                    <label for="tipo_manual"><strong>Tipo de incidencia:</strong></label>
                    <select name="tipo_manual" id="tipo_manual" required style="margin-left: 8px; padding: 8px 12px; border: 2px solid #e0e0e0; border-radius: 6px;">
                        <option value="General">General</option>
                        <option value="Eléctrica">Electrica</option>
                        <option value="Plomería">Plomeria</option>
                        <option value="Aire Acondicionado">Aire Acondicionado</option>
                        <option value="Computadoras">Computadoras</option>
                        <option value="Carpintería">Carpinteria</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-success" style="padding: 8px 16px;">
                    <img src="../assets/resources/anadir-caja.png" width="18" style="vertical-align: middle; margin-right: 8px;">
                    Crear Fases Manualmente
                </button>
            </form>
            <p style="margin-top: 10px; font-size: 12px; color: #666;">
                <strong>Nota:</strong> Esto creara las fases correspondientes al tipo seleccionado.
            </p>
            <?php else: ?>
            <p><strong>Contacte al supervisor</strong> para que cree las fases para esta incidencia.</p>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Lista detallada de fases -->
        <?php if (!empty($progreso_fases)): ?>
        <div class="fases-list">
            <h3>
                <img src="../assets/resources/layers.png" alt="Fases">
                Detalle de Fases
            </h3>
            
            <?php foreach ($progreso_fases as $fase): ?>
            <?php $es_fase_confirmacion = ($fase->nombre_fase === 'Confirmación del Solicitante'); ?>
            <div class="fase-item">
                <div class="fase-header">
<div class="fase-title">
    <?php if ($es_fase_confirmacion): ?>
        <img src="../assets/resources/fase<?= $fase->orden ?>.png" alt="Fase">
        Fase Final: <?= $fase->nombre_fase ?>
    <?php else: ?>
        <img src="../assets/resources/fase<?= $fase->orden ?>.png" alt="Fase">
        Fase <?= $fase->orden ?>: <?= $fase->nombre_fase ?>
    <?php endif; ?>
    <?php if ($fase->requiere_evidencia && !$es_fase_confirmacion): ?>
        <span style="color: #dc2626; font-size: 15px;" title="Requiere evidencia fotográfica">*</span>
    <?php endif; ?>
</div>
                    <div class="fase-estado estado-<?= $fase->estado ?>">
                        <?= ucfirst($fase->estado) ?>
                    </div>
                </div>
                
                <div class="fase-content">

                    <!-- Botón de información de la fase con MODAL -->
                    <div style="text-align: right; margin-bottom: 15px;">
                        <button type="button" class="btn btn-info btn-fase-info" style="min-width: auto; padding: 8px 16px;" data-fase='<?= json_encode($fase) ?>'>
                            <img src="../assets/resources/info.png" width="18" style="vertical-align: middle; margin-right: 5px;">
                            Info
                        </button>
                    </div>

                    <div class="fase-info">

                        <?php if (!$es_fase_confirmacion): ?>
                        <div class="fase-info-item">
                            <strong>Evidencia:</strong> 
                            <?= $fase->requiere_evidencia ? 'Requerida' : 'Opcional' ?>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Mostrar comentarios del obrero si existen -->
                    <?php if (!empty($fase->comentarios_obrero) && !$es_fase_confirmacion): ?>
                        <div class="comentario-obrero">
                            <strong><img src="../assets/resources/comentario.png" width="18" style="vertical-align: middle; margin-right: 8px;"> Comentarios del obrero:</strong><br>
                            <?= nl2br(htmlspecialchars($fase->comentarios_obrero)) ?>
                        </div>
                    <?php endif; ?>

                    <!-- Mostrar observaciones del supervisor si existen -->
                    <?php if (!empty($fase->observaciones) && !$es_fase_confirmacion): ?>
                        <div class="observaciones-supervisor">
                            <strong><img src="../assets/resources/ojo-de-lupa.png" width="18" style="vertical-align: middle; margin-right: 8px;"> Observaciones del supervisor:</strong><br>
                            <?= nl2br(htmlspecialchars($fase->observaciones)) ?>
                        </div>
                    <?php endif; ?>

                    <!-- Evidencias existentes (solo para fases técnicas) -->
                    <?php if ($fase->evidencias && !$es_fase_confirmacion): ?>
                        <?php $evidencias_array = json_decode($fase->evidencias); ?>
                        <div class="evidencias-container">
                            <strong><img src="../assets/resources/imagen.png" width="20" style="vertical-align: middle; margin-right: 8px;"> Evidencias subidas:</strong>
                            <div class="evidencias-grid">
                                <?php foreach ($evidencias_array as $evidencia): ?>
                                    <a href="<?= $evidencia ?>" target="_blank">
                                        <img src="<?= $evidencia ?>" class="evidencia-img" alt="Evidencia">
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php elseif ($fase->estado === 'completada' && !$es_fase_confirmacion): ?>
                        <div class="no-evidencias">
                            <img src="../assets/resources/no-encontrado.png" width="25" style="vertical-align: middle; margin-right: 10px;">
                            No se subieron evidencias para esta fase
                        </div>
                    <?php endif; ?>

                    <!-- Acciones según el estado y permisos -->
                    <div class="acciones-container">
                        <?php if ($fase->estado === 'pendiente' && $nivel_usuario === 'obmantenimiento' && !$es_fase_confirmacion): ?>
                            <!-- Obrero: Completar fase técnica -->
                            <?php 
                            $puede_completar = true;
                            $mensaje_bloqueo = '';
                            $fase_anterior_info = null;
                            
                            // Verificar control secuencial
                            if ($fase->seguimiento_secuencial) {
                                $puede_completar = puedeCompletarFase($conexion, $id_incidencia, $fase->orden);
                                if (!$puede_completar) {
                                    $fase_anterior = obtenerFaseAnteriorBloqueante($conexion, $id_incidencia, $fase->orden);
                                    $fase_anterior_info = $fase_anterior;
                                    
                                    if ($fase_anterior) {
                                        if ($fase_anterior->estado === 'completada') {
                                            $mensaje_bloqueo = "La fase anterior '{$fase_anterior->nombre_fase}' (Fase {$fase_anterior->orden}) está completada pero necesita ser aprobada por el supervisor antes de continuar.";
                                        } else {
                                            $mensaje_bloqueo = "La fase anterior '{$fase_anterior->nombre_fase}' (Fase {$fase_anterior->orden}) debe estar aprobada. Estado actual: " . ucfirst($fase_anterior->estado);
                                        }
                                    } else {
                                        $mensaje_bloqueo = "La fase anterior debe estar APROBADA antes de iniciar esta fase.";
                                    }
                                }
                            }
                            ?>
                            
                            <?php if (!$puede_completar && $fase->seguimiento_secuencial): ?>
                                <div class="alert-warning" style="margin-bottom: 20px;">
                                    <span class="icono-bloqueo">⚠</span>
                                    <strong>Fase Bloqueada (Secuencial)</strong><br>
                                    <?= $mensaje_bloqueo ?>
                                    <br><small>Espere a que el supervisor apruebe la fase anterior.</small>
                                </div>
                                
                                <!-- Mostrar formulario deshabilitado -->
                                <form method="post" enctype="multipart/form-data" style="opacity: 0.5; pointer-events: none;">
                                    <input type="hidden" name="completar_fase" value="1">
                                    <input type="hidden" name="id_fase" value="<?= $fase->id_fase ?>">
                                    
                                    <div class="form-group">
                                        <label><img src="../assets/resources/subir-imagen.png" width="20" style="vertical-align: middle; margin-right: 8px;"> Subir evidencias <?= $fase->requiere_evidencia ? '(Obligatorio)' : '(Opcional)' ?>:</label>
                                        <input type="file" name="evidencias_fase[]" accept="image/*" multiple 
                                               <?= $fase->requiere_evidencia ? 'required' : '' ?> disabled>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label><img src="../assets/resources/nota.png" width="20" style="vertical-align: middle; margin-right: 8px;"> Comentarios sobre el trabajo realizado:</label>
                                        <textarea name="comentarios_obrero" placeholder="Describa el trabajo realizado, materiales utilizados, observaciones..." 
                                                  rows="4" disabled></textarea>
                                    </div>
                                    
                                    <button type="button" class="btn btn-completar-bloqueado" disabled>
                                        <img src="../assets/resources/bloqueado.png" width="20" style="vertical-align: middle; margin-right: 8px;">
                                        Esperando Aprobación Anterior
                                    </button>
                                </form>
                            <?php else: ?>
                                <!-- Mostrar formulario habilitado -->
                                <form method="post" enctype="multipart/form-data">
                                    <input type="hidden" name="completar_fase" value="1">
                                    <input type="hidden" name="id_fase" value="<?= $fase->id_fase ?>">
                                    
                                    <div class="form-group">
                                        <label><img src="../assets/resources/subir-imagen.png" width="20" style="vertical-align: middle; margin-right: 8px;"> Subir evidencias <?= $fase->requiere_evidencia ? '(Obligatorio)' : '(Opcional)' ?>:</label>
                                        <input type="file" name="evidencias_fase[]" accept="image/*" multiple 
                                               <?= $fase->requiere_evidencia ? 'required' : '' ?>>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label><img src="../assets/resources/nota.png" width="20" style="vertical-align: middle; margin-right: 8px;"> Comentarios sobre el trabajo realizado:</label>
                                        <textarea name="comentarios_obrero" placeholder="Describa el trabajo realizado, materiales utilizados, observaciones..." 
                                                  rows="4"></textarea>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-warning">
                                        <img src="../assets/resources/verificar.png" width="20" style="vertical-align: middle; margin-right: 8px;">
                                        Marcar como Completada
                                    </button>
                                </form>
                            <?php endif; ?>

                        <?php elseif ($fase->estado === 'completada' && in_array($nivel_usuario, ['admin', 'supmantenimiento', 'superadministrador']) && !$es_fase_confirmacion): ?>
                            <!-- Supervisor: Validar fase técnica -->
                            <form method="post">
                                <input type="hidden" name="validar_fase" value="1">
                                <input type="hidden" name="id_fase" value="<?= $fase->id_fase ?>">
                                
                                <div class="form-group">
                                    <label><img src="../assets/resources/observacion.png" width="20" style="vertical-align: middle; margin-right: 8px;"> Observaciones (opcional):</label>
                                    <textarea name="observaciones" placeholder="Ingrese observaciones para el trabajador..."></textarea>
                                </div>
                                
                                <?php 
                                // Verificar si esta aprobación desbloqueará fases posteriores
                                $fases_posteriores = fasesPendientesAprobacion($conexion, $id_incidencia, $fase->orden);
                                if ($fase->seguimiento_secuencial && !empty($fases_posteriores)): 
                                ?>
                                    <div class="alert-info" style="margin-bottom: 15px;">
                                        <strong>Importante:</strong> Al aprobar esta fase, se desbloquearán 
                                        <?= count($fases_posteriores) ?> fase(s) posterior(es) que están esperando aprobación.
                                    </div>
                                <?php endif; ?>
                                
                                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                                    <button type="submit" name="accion" value="aprobar" class="btn btn-success">
                                        <img src="../assets/resources/verificacion-de-escudo.png" width="20" style="vertical-align: middle; margin-right: 8px;">
                                        Aprobar Fase
                                    </button>
                                    <button type="submit" name="accion" value="rechazar" class="btn btn-danger" 
                                            onclick="return confirm('¿Esta seguro de rechazar esta fase?')">
                                        <img src="../assets/resources/x.png" width="20" style="vertical-align: middle; margin-right: 8px;">
                                        Rechazar Fase
                                    </button>
                                </div>
                            </form>

                        <?php elseif ($fase->estado === 'rechazada' && $nivel_usuario === 'obmantenimiento' && !$es_fase_confirmacion): ?>
                            <!-- Obrero: Reintentar fase rechazada -->
                            <div class="alert-danger" style="background: #fee2e2; color: #dc2626; padding: 12px; border-radius: 6px; margin-bottom: 15px;">
                                <img src="../assets/resources/advertencia.png" width="25" style="vertical-align: middle; margin-right: 10px;">
                                Fase rechazada. Por favor, corrija los problemas indicados y vuelva a completar la fase.
                            </div>
                            <form method="post" enctype="multipart/form-data" style="margin-top: 15px;">
                                <input type="hidden" name="completar_fase" value="1">
                                <input type="hidden" name="id_fase" value="<?= $fase->id_fase ?>">
                                
                                <div class="form-group">
                                    <label><img src="../assets/resources/subir-imagen.png" width="20" style="vertical-align: middle; margin-right: 8px;"> Subir nuevas evidencias:</label>
                                    <input type="file" name="evidencias_fase[]" accept="image/*" multiple required>
                                </div>
                                
                                <div class="form-group">
                                    <label><img src="../assets/resources/nota.png" width="20" style="vertical-align: middle; margin-right: 8px;"> Comentarios sobre las correcciones realizadas:</label>
                                    <textarea name="comentarios_obrero" placeholder="Describa las correcciones realizadas..." 
                                              rows="4"></textarea>
                                </div>
                                
                                <button type="submit" class="btn btn-warning">
                                    <img src="../assets/resources/girar-cuadrado.png" width="20" style="vertical-align: middle; margin-right: 8px;">
                                    Reintentar Fase
                                </button>
                            </form>

                        <?php elseif ($es_fase_confirmacion && $fase->estado === 'pendiente' && $es_solicitante_original && !$conformidad): ?>
                            <!-- Solicitante: Confirmar incidencia -->
                            <div style="text-align: center; margin-bottom: 20px;">
                                <h4 style="color: #0d6efd;">
                                    <img src="../assets/resources/verificacion.png" width="30" style="vertical-align: middle; margin-right: 10px;">
                                    Verifique el Trabajo Realizado
                                </h4>
                                <p>Como solicitante original de esta incidencia, por favor revise que el trabajo cumple con lo solicitado.</p>
                            </div>
                            
                            <form method="post">
                                <input type="hidden" name="confirmar_incidencia" value="1">
                                
                                <!-- Sistema de calificación -->
                                <div class="form-group">
                                    <label><img src="../assets/resources/corazon.png" width="25" style="vertical-align: middle; margin-right: 10px;"> Calificación del servicio:</label>
                                    <div style="text-align: center; margin: 10px 0;">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <span class="estrellas-calificacion" data-value="<?= $i ?>">★</span>
                                        <?php endfor; ?>
                                        <input type="hidden" name="calificacion" id="calificacion_valor" value="" required>
                                    </div>
                                    <small style="color: #666; text-align: center; display: block;">1 = Muy Insatisfecho, 5 = Excelente</small>
                                </div>
                                
                                <!-- Comentarios -->
                                <div class="form-group">
                                    <label><img src="../assets/resources/comentario.png" width="25" style="vertical-align: middle; margin-right: 10px;"> Comentarios o observaciones:</label>
                                    <textarea name="comentarios_solicitante" rows="4" 
                                              placeholder="¿El trabajo cumple con sus expectativas? ¿Alguna observacion adicional?"></textarea>
                                </div>
                                
                                <!-- Botones de acción -->
                                <div style="text-align: center; margin-top: 25px; padding-top: 15px; border-top: 1px solid #e9ecef;">
                                    <button type="submit" name="accion" value="aceptar" 
                                            class="btn btn-success">
                                        <img src="../assets/resources/caja.png" width="25" style="vertical-align: middle; margin-right: 10px;">
                                        Aceptar y Finalizar Incidencia
                                    </button>
                                    
                                    <button type="submit" name="accion" value="rechazar" 
                                            class="btn btn-danger"
                                            onclick="return confirm('¿Esta seguro de rechazar el trabajo? Esto notificara al supervisor para revision.')">
                                        <img src="../assets/resources/error.png" width="25" style="vertical-align: middle; margin-right: 10px;">
                                        Rechazar Trabajo
                                    </button>
                                </div>
                            </form>

                        <?php elseif ($es_fase_confirmacion && $fase->estado === 'aprobada' && $conformidad && $conformidad->confirmada): ?>
                            <!-- Confirmación aceptada -->
                            <div class="conformidad-info" style="background: #d1fae5; border-left-color: #059669;">
                                <h4 style="color: #059669; margin-top: 0;">
                                    <img src="../assets/resources/caja.png" width="30" style="vertical-align: middle; margin-right: 10px;">
                                    Incidencia Confirmada
                                </h4>
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                                    <div>
                                        <strong>Estado:</strong> Aceptada por el solicitante<br>
                                        <strong>Fecha:</strong> <?= date('d/m/Y H:i', strtotime($conformidad->fecha_confirmacion)) ?><br>
                                        
                                        <?php if ($conformidad->calificacion): ?>
                                            <strong>Calificación:</strong> 
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <span style="color: <?= $i <= $conformidad->calificacion ? '#ffc107' : '#ccc' ?>;">★</span>
                                            <?php endfor; ?>
                                            (<?= $conformidad->calificacion ?>/5)<br>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if ($conformidad->comentarios): ?>
                                    <div>
                                        <strong>Comentarios del solicitante:</strong><br>
                                        <div style="background: white; padding: 10px; border-radius: 5px; margin-top: 5px;">
                                            <?= nl2br(htmlspecialchars($conformidad->comentarios)) ?>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                        <?php elseif ($es_fase_confirmacion && $fase->estado === 'rechazada' && $conformidad && !$conformidad->confirmada): ?>
                            <!-- Confirmación rechazada -->
                            <div class="conformidad-info" style="background: #fee2e2; border-left-color: #dc2626;">
                                <h4 style="color: #dc2626; margin-top: 0;">
                                    <img src="../assets/resources/error.png" width="30" style="vertical-align: middle; margin-right: 10px;">
                                    Incidencia Rechazada
                                </h4>
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                                    <div>
                                        <strong>Estado:</strong> Rechazada por el solicitante<br>
                                        <strong>Fecha:</strong> <?= date('d/m/Y H:i', strtotime($conformidad->fecha_confirmacion)) ?><br>
                                        <strong>Nota:</strong> El supervisor ha sido notificado para revision.
                                    </div>
                                    
                                    <?php if ($conformidad->comentarios): ?>
                                    <div>
                                        <strong>Razon del rechazo:</strong><br>
                                        <div style="background: white; padding: 10px; border-radius: 5px; margin-top: 5px;">
                                            <?= nl2br(htmlspecialchars($conformidad->comentarios)) ?>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if (in_array($nivel_usuario, ['admin', 'supmantenimiento', 'superadministrador'])): ?>
                                <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #fecaca;">
                                    <a href="/lugopata/incidencias/monitorearincidencia.php?id=<?= $id_incidencia ?>" 
                                       class="btn btn-info">
                                        <img src="../assets/resources/busqueda.png" width="20" style="vertical-align: middle; margin-right: 8px;">
                                        Revisar Incidencia Rechazada
                                    </a>
                                </div>
                                <?php endif; ?>
                            </div>

                        <?php elseif ($fase->estado === 'aprobada' && !$es_fase_confirmacion): ?>
                            <!-- Fase técnica aprobada -->
                            <div style="text-align: center; color: #059669; font-weight: bold;">
                                <img src="../assets/resources/caja.png" width="25" style="vertical-align: middle; margin-right: 8px;">
                                Fase aprobada correctamente
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Botones de navegación con historial -->
        <div class="btn-container">
            <button id="btnHistorial" class="btn btn-info">
                <img src="../assets/resources/historial.png" width="20" style="vertical-align: middle; margin-right: 8px;">
                Ver Historial Completo
            </button>
            <a href="/lugopata/incidencias/monitorearincidencia.php?id=<?= $id_incidencia ?>" class="btn btn-primary">
                <img src="../assets/resources/info.png" width="20" style="vertical-align: middle; margin-right: 8px;">
                Info
            </a>
            <a href="javascript:history.back()" class="btn btn-primary">
                <img src="../assets/resources/volver2.png" width="20" style="vertical-align: middle; margin-right: 8px;">
                Regresar
            </a>
        </div>
    </div>
</div>

<!-- Modal para información de fase -->
<div class="fase-modal-overlay" id="faseModalOverlay"></div>
<div class="fase-modal" id="faseInfoModal">
    <div class="fase-modal-header">
        <h2>
            <img src="../assets/resources/info.png" alt="Info" width="30">
            Información de la Fase
        </h2>
        <button class="fase-modal-close">&times;</button>
    </div>
    <div class="fase-modal-body" id="faseModalBody">
        <!-- Contenido dinámico insertado por JavaScript -->
    </div>
    <div class="fase-modal-footer">
        <button class="btn btn-primary fase-modal-close-btn">
            Cerrar
        </button>
    </div>
</div>

<!-- Modal de Historial (con scroll corregido) -->
<div id="modal-overlay" style="display: none;"></div>
<div id="modalHistorial" class="modal-historial" style="display: none;">
    <div class="modal-historial-contenido" style="display: flex; flex-direction: column; height: 100%;">
        <div class="modal-historial-header">
            <h2>
                <img src="../assets/resources/historial.png" width="35" style="vertical-align: middle; margin-right: 10px;">
                Historial - Incidencia #<?= $id_incidencia ?>
            </h2>
            <button class="cerrar-modal">&times;</button>
        </div>
        <div class="modal-historial-body" style="flex: 1; overflow-y: auto; padding: 25px; min-height: 0;">
            <?php 
            $historial_simple = [];
            
            if ($incidencia) {
                $historial_simple[] = [
                    'tipo' => 'creacion',
                    'titulo' => 'Incidencia creada',
                    'fecha' => $incidencia->fecha,
                    'descripcion' => "Creada por el departamento: " . ($incidencia->depto_emisor ?? 'Desconocido'),
                    'detalles' => [
                        'Prioridad' => $incidencia->prioridad,
                        'Ubicacion' => $incidencia->ubicacion,
                        'Descripcion' => substr($incidencia->descripcion, 0, 200)
                    ]
                ];
            }
            
            foreach ($progreso_fases as $fase) {
                if ($fase->fecha_completado) {
                    $historial_simple[] = [
                        'tipo' => 'fase_completada',
                        'titulo' => "Fase completada: {$fase->nombre_fase}",
                        'fecha' => $fase->fecha_completado,
                        'descripcion' => "Fase {$fase->orden}: {$fase->nombre_fase}",
                        'detalles' => [
                            'Estado' => ucfirst($fase->estado),
                            'Comentarios' => $fase->comentarios_obrero ? substr($fase->comentarios_obrero, 0, 150) : 'Sin comentarios'
                        ]
                    ];
                }
                
                if ($fase->fecha_aprobacion) {
                    $historial_simple[] = [
                        'tipo' => 'fase_' . $fase->estado,
                        'titulo' => "Fase {$fase->estado}: {$fase->nombre_fase}",
                        'fecha' => $fase->fecha_aprobacion,
                        'descripcion' => "Fase {$fase->estado} por supervisor",
                        'detalles' => [
                            'Observaciones' => $fase->observaciones ? substr($fase->observaciones, 0, 150) : 'Sin observaciones'
                        ]
                    ];
                }
            }
            
            if ($conformidad) {
                $historial_simple[] = [
                    'tipo' => 'confirmacion_solicitante',
                    'titulo' => 'Confirmación del solicitante',
                    'fecha' => $conformidad->fecha_confirmacion,
                    'descripcion' => $conformidad->confirmada ? 'TRABAJO ACEPTADO - Incidencia FINALIZADA' : 'TRABAJO RECHAZADO',
                    'detalles' => [
                        'Calificacion' => $conformidad->calificacion ? "{$conformidad->calificacion}/5 estrellas" : 'No calificado',
                        'Comentarios' => $conformidad->comentarios ? substr($conformidad->comentarios, 0, 200) : 'Sin comentarios'
                    ]
                ];
            }
            
            usort($historial_simple, function($a, $b) {
                return strtotime($a['fecha']) <=> strtotime($b['fecha']);
            });
            
            if (empty($historial_simple)): ?>
                <div class="sin-historial">
                    <p>No hay historial registrado para esta incidencia.</p>
                    <div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 6px;">
                        <p><strong>Incidencia #<?= $id_incidencia ?></strong></p>
                        <p><strong>Estado actual:</strong> <?= $incidencia->estado ?></p>
                        <p><strong>Fecha creación:</strong> <?= $incidencia->fecha ?></p>
                        <?php if (!empty($progreso_fases)): ?>
                            <p><strong>Fases registradas:</strong> <?= count($progreso_fases) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="timeline-historial">
                    <?php foreach ($historial_simple as $index => $evento): ?>
                        <div class="evento-historial tipo-<?= $evento['tipo'] ?>">
                            <div class="evento-marcador">
                                <?= $index + 1 ?>
                            </div>
                            <div class="evento-contenido">
                                <div class="evento-header">
                                    <h4><?= $evento['titulo'] ?></h4>
                                    <span class="evento-fecha">
                                        <?= date('d/m/Y H:i', strtotime($evento['fecha'])) ?>
                                    </span>
                                </div>
                                <div class="evento-descripcion">
                                    <?= $evento['descripcion'] ?>
                                </div>
                                
                                <?php if (!empty($evento['detalles'])): ?>
                                    <div class="evento-detalles">
                                        <?php foreach ($evento['detalles'] as $clave => $valor): 
                                            if (!empty($valor)): ?>
                                                <div class="detalle-item">
                                                    <strong><?= $clave ?>:</strong> 
                                                    <?= nl2br(htmlspecialchars($valor)) ?>
                                                </div>
                                            <?php endif;
                                        endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="modal-historial-footer">
           <!-- <button class="btn btn-primary cerrar-modal">
                <img src="../assets/resources/volver2.png" width="20" style="vertical-align: middle; margin-right: 8px;">
                Cerrar
            </button> -->
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const progressSteps = document.querySelectorAll('.progress-step');
    progressSteps.forEach(step => {
        step.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.1)';
            this.style.zIndex = '10';
        });
        step.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
            this.style.zIndex = '1';
        });
        
        // Hacer que los pasos de progreso también abran el modal al hacer clic
        step.addEventListener('click', function() {
            const faseData = this.getAttribute('data-fase');
            if (faseData) {
                try {
                    const fase = JSON.parse(faseData);
                    mostrarModalFase(fase);
                } catch (e) {
                    console.error('Error parsing fase data', e);
                }
            }
        });
    });
    
    const estrellas = document.querySelectorAll('.estrellas-calificacion');
    const inputCalificacion = document.getElementById('calificacion_valor');
    
    if (estrellas.length > 0 && inputCalificacion) {
        estrellas.forEach(estrella => {
            estrella.addEventListener('click', function() {
                const valor = this.getAttribute('data-value');
                inputCalificacion.value = valor;
                
                estrellas.forEach(e => {
                    if (e.getAttribute('data-value') <= valor) {
                        e.style.color = '#ffc107';
                        e.classList.add('seleccionada');
                    } else {
                        e.style.color = '#ccc';
                        e.classList.remove('seleccionada');
                    }
                });
            });
            
            estrella.addEventListener('mouseover', function() {
                const hoverValor = this.getAttribute('data-value');
                estrellas.forEach(e => {
                    if (e.getAttribute('data-value') <= hoverValor) {
                        e.style.color = '#ffdb70';
                    }
                });
            });
            
            estrella.addEventListener('mouseout', function() {
                const valorActual = inputCalificacion.value || 0;
                estrellas.forEach(e => {
                    if (e.getAttribute('data-value') <= valorActual) {
                        e.style.color = '#ffc107';
                    } else {
                        e.style.color = '#ccc';
                    }
                });
            });
        });
    }
    
    const modal = document.getElementById('modalHistorial');
    const overlay = document.getElementById('modal-overlay');
    const btnAbrir = document.getElementById('btnHistorial');
    const btnsCerrar = document.querySelectorAll('.cerrar-modal');
    
    if (btnAbrir) {
        btnAbrir.addEventListener('click', function() {
            modal.style.display = 'flex';
            overlay.style.display = 'block';
            document.body.style.overflow = 'hidden';
        });
    }
    
    btnsCerrar.forEach(btn => {
        btn.addEventListener('click', function() {
            modal.style.display = 'none';
            overlay.style.display = 'none';
            document.body.style.overflow = 'auto';
        });
    });
    
    if (overlay) {
        overlay.addEventListener('click', function() {
            modal.style.display = 'none';
            overlay.style.display = 'none';
            document.body.style.overflow = 'auto';
        });
    }
    
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.style.display === 'flex') {
            modal.style.display = 'none';
            overlay.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    });
    
    // Modal de información de fase
    const faseModal = document.getElementById('faseInfoModal');
    const faseOverlay = document.getElementById('faseModalOverlay');
    const faseModalBody = document.getElementById('faseModalBody');
    const faseCloseBtn = document.querySelector('.fase-modal-close');
    const faseCloseFooterBtn = document.querySelector('.fase-modal-close-btn');
    
    // Función para mostrar el modal de fase
    function mostrarModalFase(fase) {
        if (!faseModal || !faseOverlay || !faseModalBody) return;
        
        // Construir contenido del modal
        const requiereEvidencia = fase.requiere_evidencia ? 'Sí' : 'No';
        const tipoSeguimiento = fase.seguimiento_secuencial ? 'Secuencial' : 'Paralelo';
        const estado = fase.estado.charAt(0).toUpperCase() + fase.estado.slice(1);
        
        let html = `
            <div class="fase-info-detalle">
                <h3>${fase.nombre_fase}</h3>
                <p>${fase.descripcion || 'Sin descripción disponible'}</p>
                
                <div class="fase-info-grid">
                    <div class="fase-info-grid-item">
                        <strong>Número de Fase</strong>
                        <span>${fase.orden}</span>
                    </div>
                    <div class="fase-info-grid-item">
                        <strong>Estado Actual</strong>
                        <span>${estado}</span>
                    </div>
                    <div class="fase-info-grid-item">
                        <strong>Tipo de Seguimiento</strong>
                        <span>${tipoSeguimiento}</span>
                    </div>
                    <div class="fase-info-grid-item">
                        <strong>Requiere Evidencia</strong>
                        <span>${requiereEvidencia}</span>
                    </div>
                </div>
                
                <div style="margin-top: 15px;">
                    <p><strong>Fecha de inicio:</strong> ${fase.fecha_asignacion ? new Date(fase.fecha_asignacion).toLocaleDateString('es-ES') : 'No disponible'}</p>
                    <p><strong>Fecha de completado:</strong> ${fase.fecha_completado ? new Date(fase.fecha_completado).toLocaleDateString('es-ES') + ' ' + new Date(fase.fecha_completado).toLocaleTimeString('es-ES') : 'Pendiente'}</p>
                    ${fase.fecha_aprobacion ? `<p><strong>Fecha de aprobación:</strong> ${new Date(fase.fecha_aprobacion).toLocaleDateString('es-ES') + ' ' + new Date(fase.fecha_aprobacion).toLocaleTimeString('es-ES')}</p>` : ''}
                </div>
            </div>
        `;
        
        faseModalBody.innerHTML = html;
        faseModal.style.display = 'flex';
        faseOverlay.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }
    
    // Asignar eventos a los botones de información de fase
    document.querySelectorAll('.btn-fase-info').forEach(btn => {
        btn.addEventListener('click', function() {
            const faseData = this.getAttribute('data-fase');
            if (faseData) {
                try {
                    const fase = JSON.parse(faseData);
                    mostrarModalFase(fase);
                } catch (e) {
                    console.error('Error parsing fase data', e);
                }
            }
        });
    });
    
    // Función para cerrar el modal de fase
    function cerrarModalFase() {
        faseModal.style.display = 'none';
        faseOverlay.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
    
    if (faseCloseBtn) {
        faseCloseBtn.addEventListener('click', cerrarModalFase);
    }
    
    if (faseCloseFooterBtn) {
        faseCloseFooterBtn.addEventListener('click', cerrarModalFase);
    }
    
    if (faseOverlay) {
        faseOverlay.addEventListener('click', cerrarModalFase);
    }
    
    // Tecla Escape para cerrar modal de fase
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && faseModal.style.display === 'flex') {
            cerrarModalFase();
        }
    });
});
</script>
</body>
</html>