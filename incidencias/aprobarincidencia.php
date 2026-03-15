<?php
// /incidencias/aprobarincidencia.php - Interfaz para aprobación de incidencias por supervisores
session_start();
include_once __DIR__ . "/../encabezado.php";
require_once __DIR__ . "/incidencia.php";
require_once __DIR__ . "/../departamentos/departamento.php";
require_once __DIR__ . "/../user/gestorsesion.php";
require_once __DIR__ . "/../trabajador/trabajador.php";

// Valida que el usuario tenga sesión activa y sea supervisor
GestorSesiones::iniciar();

// Verificar que el usuario tenga permisos de supervisor
$nivel_usuario = GestorSesiones::get('nivel');
$niveles_permitidos = ['admin', 'supmantenimiento', 'superadministrador'];

if (!in_array($nivel_usuario, $niveles_permitidos)) {
    header("Location: /lugopata/dashboard.php");
    exit;
}

$mensaje = "";
$incObj = new Incidencia($conexion);
$trabObj = new Trabajador($conexion);

// Obtener lista de trabajadores para asignación manual
$trabajadores = $trabObj->listar();

// Obtener tipos de incidencia disponibles
$tipos_incidencia = $conexion->query(
    "SELECT DISTINCT tipo_incidencia FROM fases_incidencia WHERE tipo_incidencia != 'General' ORDER BY tipo_incidencia"
)->fetchAll(PDO::FETCH_COLUMN);

// Agregar tipo General por defecto
array_unshift($tipos_incidencia, 'General');

// Obtener incidencias pendientes de aprobación
$incidencias_pendientes = $incObj->obtenerPendientesAprobacion();

// Función para obtener trabajadores con sus aptitudes y conteo de incidencias asignadas
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
    
    // Filtrar por aptitud si se especifica un tipo de incidencia
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

// Procesar aprobación/rechazo
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_incidencia = (int)$_POST['id_incidencia'];
    $accion = $_POST['accion'];
    $id_supervisor = GestorSesiones::get('id_usuario');

    if ($accion === 'aprobar') {
        // Asignar trabajador (manual o automático)
        $id_trabajador = !empty($_POST['trabajador_asignado']) ? (int)$_POST['trabajador_asignado'] : null;
        $tipo_incidencia = $_POST['tipo_incidencia'] ?? 'General';
        $fecha_estimada_finalizacion = $_POST['fecha_estimada_finalizacion'] ?? null;

        $resultado = $incObj->aprobarIncidencia($id_incidencia, $id_supervisor, $id_trabajador, $tipo_incidencia, $fecha_estimada_finalizacion);
        
        if ($resultado['success']) {
            if ($resultado['estado'] === 'Sin Trabajador Disponible') {
                $mensaje = "<div class='alert alert-warning'>Incidencia aprobada pero no se encontró trabajador disponible para el tipo: $tipo_incidencia. La incidencia queda en estado 'Sin Trabajador Disponible'.</div>";
            } else {
                $trabajador = $resultado['trabajador_asignado'];
                $tipo_asignacion = $resultado['asignacion_automatica'] ? 'automática' : 'manual';
                
                if ($trabajador) {
                    $mensaje = "<div class='alert alert-success'>Incidencia aprobada y asignada correctamente.<br><strong>Trabajador asignado:</strong> {$trabajador->nombre} {$trabajador->apellido} <br><strong>Tipo de asignación:</strong> {$tipo_asignacion} <br><strong>Tipo de incidencia:</strong> {$tipo_incidencia}</div>";
                } else {
                    $mensaje = "<div class='alert alert-success'>Incidencia aprobada correctamente.</div>";
                }
            }
            
            // DIAGNÓSTICO: Verificar si se crearon las fases
            $fases_creadas = $incObj->obtenerProgresoFases($id_incidencia);
            error_log("DEBUG aprobarincidencia: Fases creadas: " . count($fases_creadas) . " para incidencia $id_incidencia, tipo: $tipo_incidencia");
            
        } else {
            $mensaje = "<div class='alert alert-error'>Error al aprobar la incidencia: {$resultado['error']}</div>";
        }
    } 
    elseif ($accion === 'rechazar') {
        $justificacion = trim($_POST['justificacion']);
        
        if (empty($justificacion)) {
            $mensaje = "<div class='alert alert-error'>Debe proporcionar una justificación para el rechazo.</div>";
        } else {
            if ($incObj->rechazarIncidencia($id_incidencia, $id_supervisor, $justificacion)) {
                $mensaje = "<div class='alert alert-success'>Incidencia rechazada correctamente.</div>";
            } else {
                $mensaje = "<div class='alert alert-error'>Error al rechazar la incidencia.</div>";
            }
        }
    }
    
    // Recargar la lista después de la acción
    $incidencias_pendientes = $incObj->obtenerPendientesAprobacion();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Aprobar Incidencias</title>
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
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 500;
            font-size: 14px;
        }

        .alert-error {
            background-color: #fee2e2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        .alert-success {
            background-color: #d1fae5;
            color: #059669;
            border: 1px solid #a7f3d0;
        }

        .alert-warning {
            background-color: #fef3c7;
            color: #d97706;
            border: 1px solid #fde68a;
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
            background-color: #2563eb;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
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

        /* ===== HEADER ACTIONS ===== */
        .header-actions {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e9ecef;
        }

        /* ===== TARJETA DE INCIDENCIA ===== */
        .incidencia-card {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 25px;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }

        .incidencia-card:hover {
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            border-color: #0d6efd;
        }

        .incidencia-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e9ecef;
            flex-wrap: wrap;
            gap: 10px;
        }

        .incidencia-id {
            font-size: 20px;
            font-weight: bold;
            color: #0d6efd;
        }

        .incidencia-prioridad {
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .prioridad-urgente {
            background: #dc2626;
            color: white;
        }

        .prioridad-moderada {
            background: #f59e0b;
            color: white;
        }

        .prioridad-leve {
            background: #059669;
            color: white;
        }

        /* ===== INFO GRID ===== */
        .incidencia-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
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

        /* ===== SECCIONES DE ACCIÓN ===== */
        .action-section {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 2px solid #e9ecef;
        }

        .action-section h3 {
            margin-top: 0;
            margin-bottom: 20px;
            color: #333;
            font-size: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .action-section h3 img {
            width: 28px;
            height: 28px;
        }

        .acciones-container {
            background: #f8f9fa;
            padding: 20px;
            border: 1px solid #e9ecef;
            border-radius: 12px;
        }

        /* ===== FORMULARIOS ===== */
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

        .required-field::after {
            content: " *";
            color: #dc2626;
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
            height: 100px;
            resize: vertical;
            min-height: 80px;
        }

        .info-text {
            font-size: 12px;
            color: #6b7280;
            margin-top: 6px;
            font-style: italic;
        }

        /* ===== SELECT PERSONALIZADO ===== */
        select {
            appearance: none;
            background-image: url('data:image/svg+xml;utf8,<svg fill="%23666" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><path d="M7 10l5 5 5-5z"/><path d="M0 0h24v24H0z" fill="none"/></svg>');
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 20px;
            padding-right: 40px;
        }

        /* ===== FORM GRID ===== */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        /* ===== MENSAJE SIN DATOS ===== */
        .no-pendientes {
            text-align: center;
            padding: 50px 20px;
            color: #6c757d;
            background: #f8f9fa;
            border-radius: 12px;
            border: 2px dashed #dee2e6;
            margin: 20px 0;
        }

        .no-pendientes h3 {
            color: #059669;
            margin-bottom: 10px;
            font-size: 24px;
        }

        .no-pendientes p {
            font-size: 16px;
            margin: 5px 0;
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

            .incidencia-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .incidencia-info {
                grid-template-columns: 1fr;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .btn-container {
                flex-direction: column;
                gap: 10px;
            }

            .btn {
                width: 100%;
                min-width: auto;
            }

            .action-section h3 {
                font-size: 16px;
            }
        }

        @media (max-width: 480px) {
            .title-card {
                padding: 15px;
            }

            .content-card {
                padding: 15px;
            }

            .incidencia-card {
                padding: 15px;
            }

            .info-item {
                padding: 12px;
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
        }
    </style>
</head>
<body>
<div class="form-wrapper">
    <!-- TARJETA DE TÍTULO -->
    <div class="title-card">
        <h1>
            <img src="../assets/resources/caducado3.png" alt="Aprobar">
            APROBAR INCIDENCIAS
        </h1>
        <p>Gestión de incidencias pendientes de aprobación</p>
    </div>

    <!-- CONTENIDO -->
    <div class="content-card">
        
        <div class="header-actions">
            <a href="/lugopata/incidencias/gestionarfases.php" class="btn btn-info">
                <img src="../assets/resources/engranaje1.png" alt="Gestionar">
                GESTIONAR TIPOS Y FASES
            </a>
        </div>

        <?php echo $mensaje; ?>

        <?php if (empty($incidencias_pendientes)): ?>
            <div class="no-pendientes">
                <h3>¡Todo en Orden!</h3>
                <p>No hay incidencias pendientes de aprobación</p>
                <p style="font-size: 14px; color: #999;">Todas las incidencias han sido procesadas</p>
            </div>
        <?php else: ?>
            <?php foreach ($incidencias_pendientes as $incidencia): ?>
            <div class="incidencia-card">
                <div class="incidencia-header">
                    <div class="incidencia-id">INCIDENCIA #<?= $incidencia->id_incidencia ?></div>
                    <div class="incidencia-prioridad prioridad-<?= strtolower($incidencia->prioridad) ?>">
                        <?= $incidencia->prioridad ?>
                    </div>
                </div>

                <div class="incidencia-info">
                    <div class="info-item">
                        <strong><img src="../assets/resources/calendario-reloj.png" alt="Fecha"> Fecha de Reporte:</strong> 
                        <?= date('d/m/Y', strtotime($incidencia->fecha)) ?>
                    </div>
                    <div class="info-item">
                        <strong><img src="../assets/resources/marcador.png" alt="Ubicación"> Ubicación:</strong> 
                        <?= $incidencia->ubicacion ?? 'No especificada' ?>
                    </div>
                    <div class="info-item">
                        <strong><img src="../assets/resources/demanda.png" alt="Emisor"> Departamento Emisor:</strong> 
                        <?= $incidencia->depto_emisor ?>
                    </div>
                    <div class="info-item">
                        <strong><img src="../assets/resources/engranaje1.png" alt="Receptor"> Departamento Receptor:</strong> 
                        <?= $incidencia->depto_receptor ?>
                    </div>
                    <div class="info-item" style="grid-column: 1 / -1;">
                        <strong><img src="../assets/resources/informes.png" alt="Descripción"> Descripción:</strong>
                        <div style="margin-top: 8px; font-size: 14px; color: #666; line-height: 1.5;">
                            <?= nl2br(htmlspecialchars($incidencia->descripcion)) ?>
                        </div>
                    </div>
                </div>

                <!-- Formulario de Aprobación -->
                <div class="action-section">
                    <h3>
                        <img src="../assets/resources/caja.png" alt="Aprobar">
                        Aprobar Incidencia
                    </h3>
                    
                    <form method="post" id="formAprobar_<?= $incidencia->id_incidencia ?>" class="acciones-container">
                        <input type="hidden" name="id_incidencia" value="<?= $incidencia->id_incidencia ?>">
                        <input type="hidden" name="accion" value="aprobar">
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="fecha_estimada_<?= $incidencia->id_incidencia ?>" class="required-field">Fecha Estimada de Finalización:</label>
                                <input type="date" name="fecha_estimada_finalizacion" id="fecha_estimada_<?= $incidencia->id_incidencia ?>" required
                                       min="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d', strtotime('+7 days')) ?>">
                                <div class="info-text">Fecha en la que se espera completar la incidencia</div>
                            </div>
                            
                            <div class="form-group">
                                <label for="tipo_<?= $incidencia->id_incidencia ?>" class="required-field">Tipo de Incidencia:</label>
                                <select name="tipo_incidencia" id="tipo_<?= $incidencia->id_incidencia ?>" required onchange="cargarTrabajadores(<?= $incidencia->id_incidencia ?>, this.value)">
                                    <option value="">Seleccione el tipo</option>
                                    <?php foreach($tipos_incidencia as $tipo): ?>
                                        <option value="<?= $tipo ?>"><?= $tipo ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="info-text">Determina las fases de trabajo y la asignación automática</div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="trabajador_<?= $incidencia->id_incidencia ?>">Asignar a trabajador:</label>
                            <select name="trabajador_asignado" id="trabajador_<?= $incidencia->id_incidencia ?>">
                                <option value="">Asignación automática recomendada</option>
                            </select>
                            <div class="info-text" id="info_trabajador_<?= $incidencia->id_incidencia ?>">Seleccione un tipo de incidencia para ver los trabajadores recomendados</div>
                        </div>

                        <div class="btn-container">
                            <button type="submit" class="btn btn-success" onclick="return confirm('¿Está seguro de APROBAR la incidencia #<?= $incidencia->id_incidencia ?>?')">
                                <img src="../assets/resources/controlar.png" alt="Aprobar">
                                APROBAR INCIDENCIA
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Formulario de Rechazo -->
                <div class="action-section">
                    <h3>
                        <img src="../assets/resources/cuadrado-x.png" alt="Rechazar">
                        Rechazar Incidencia
                    </h3>
                    
                    <form method="post" id="formRechazar_<?= $incidencia->id_incidencia ?>" class="acciones-container">
                        <input type="hidden" name="id_incidencia" value="<?= $incidencia->id_incidencia ?>">
                        <input type="hidden" name="accion" value="rechazar">
                        
                        <div class="form-group">
                            <label for="justificacion_<?= $incidencia->id_incidencia ?>" class="required-field">Justificación:</label>
                            <textarea name="justificacion" id="justificacion_<?= $incidencia->id_incidencia ?>" 
                                      placeholder="Explique detalladamente por qué rechaza esta incidencia..." 
                                      required></textarea>
                            <div class="info-text">Esta justificación será registrada en el historial de la incidencia</div>
                        </div>

                        <div class="btn-container">
                            <button type="submit" class="btn btn-danger" onclick="return confirmRechazo(<?= $incidencia->id_incidencia ?>)">
                                <img src="../assets/resources/x.png" alt="Rechazar">
                                RECHAZAR INCIDENCIA
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- Botones de navegación -->
        <div class="btn-container">
            <a href="/lugopata/incidencias/listarincidencias.php" class="btn btn-primary">
                <img src="../assets/resources/volver2.png" alt="Volver">
                Regresar
            </a>
        </div>
    </div>
</div>

<script>
// Función para cargar trabajadores según el tipo de incidencia
function cargarTrabajadores(idIncidencia, tipoIncidencia) {
    const selectTrabajador = document.getElementById(`trabajador_${idIncidencia}`);
    const infoText = document.getElementById(`info_trabajador_${idIncidencia}`);
    
    // Mostrar mensaje de carga
    selectTrabajador.innerHTML = '<option value="">Cargando trabajadores...</option>';
    infoText.innerHTML = 'Buscando trabajadores con la aptitud requerida...';
    
    // Hacer petición AJAX
    fetch(`/lugopata/incidencias/obtener_trabajadores_ajax.php?tipo=${encodeURIComponent(tipoIncidencia)}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                selectTrabajador.innerHTML = '<option value="">Error al cargar trabajadores</option>';
                infoText.innerHTML = data.error;
                return;
            }
            
            // Construir opciones del select
            let options = '<option value="">Asignación automática recomendada</option>';
            
            if (data.trabajadores && data.trabajadores.length > 0) {
                data.trabajadores.forEach(t => {
                    options += `<option value="${t.id_trabajador}">${t.nombre} ${t.apellido} | Aptitudes: ${t.aptitudes || 'Ninguna'} | Incidencias: ${t.incidencias_asignadas}</option>`;
                });
                
                if (tipoIncidencia && tipoIncidencia !== 'General') {
                    infoText.innerHTML = `Se encontraron ${data.trabajadores.length} trabajadores con aptitud en "${tipoIncidencia}"`;
                } else {
                    infoText.innerHTML = 'Mostrando todos los trabajadores disponibles';
                }
            } else {
                options = '<option value="">No hay trabajadores disponibles</option>';
                infoText.innerHTML = tipoIncidencia ? 
                    `No se encontraron trabajadores con aptitud en "${tipoIncidencia}"` : 
                    'No hay trabajadores disponibles';
            }
            
            selectTrabajador.innerHTML = options;
        })
        .catch(error => {
            console.error('Error:', error);
            selectTrabajador.innerHTML = '<option value="">Error de conexión</option>';
            infoText.innerHTML = 'Error al cargar los trabajadores';
        });
}

document.addEventListener('DOMContentLoaded', function() {
    // Configurar fecha mínima para fecha estimada
    const hoy = new Date().toISOString().split('T')[0];
    const fechaInputs = document.querySelectorAll('input[type="date"]');
    fechaInputs.forEach(input => {
        if (input.name === 'fecha_estimada_finalizacion') {
            input.min = hoy;
        }
    });

    // Auto-expand textareas
    const textareas = document.querySelectorAll('textarea');
    textareas.forEach(textarea => {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
    });

    // Validar formulario de aprobación
    document.querySelectorAll('form[id^="formAprobar_"]').forEach(form => {
        form.addEventListener('submit', function(e) {
            const fechaEstimada = this.querySelector('input[name="fecha_estimada_finalizacion"]');
            const tipoIncidencia = this.querySelector('select[name="tipo_incidencia"]');
            
            if (!fechaEstimada.value) {
                e.preventDefault();
                alert('Por favor, seleccione una fecha estimada de finalización.');
                fechaEstimada.focus();
                return false;
            }
            
            if (!tipoIncidencia.value) {
                e.preventDefault();
                alert('Por favor, seleccione el tipo de incidencia.');
                tipoIncidencia.focus();
                return false;
            }
            
            // Validar que la fecha estimada no sea en el pasado
            const fechaSeleccionada = new Date(fechaEstimada.value);
            const fechaActual = new Date();
            fechaActual.setHours(0, 0, 0, 0);
            
            if (fechaSeleccionada < fechaActual) {
                e.preventDefault();
                alert('La fecha estimada de finalización no puede ser anterior a hoy.');
                fechaEstimada.focus();
                return false;
            }
            
            return true;
        });
    });

    // Auto-ocultar mensajes después de 5 segundos
    setTimeout(function() {
        const alert = document.querySelector('.alert');
        if (alert) {
            alert.style.display = 'none';
        }
    }, 5000);
});

function confirmRechazo(idIncidencia) {
    const textarea = document.querySelector(`#justificacion_${idIncidencia}`);
    if (!textarea || !textarea.value.trim()) {
        alert('Debe proporcionar una justificación para el rechazo.');
        if (textarea) textarea.focus();
        return false;
    }
    
    return confirm(`¿Está seguro de que desea RECHAZAR la incidencia #${idIncidencia}?\n\nEsta acción no se puede deshacer y la justificación quedará registrada.`);
}
</script>

<?php include_once __DIR__ . "/../pie.php"; ?>
</body>
</html>