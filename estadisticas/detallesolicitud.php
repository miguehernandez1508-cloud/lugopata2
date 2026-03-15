<?php
// /detalleSolicitud.php - Archivo para mostrar detalles de solicitudes e incidencias
session_start();
include_once "../encabezado.php";
require_once "../conex.php";
require_once "../user/gestorsesion.php";

// Valida que el usuario tenga sesión activa
GestorSesiones::iniciar();

// Obtiene el tipo de solicitud (Insumo/Incidencia) y el ID desde la URL
$tipo = $_GET['tipo'] ?? '';
$id = $_GET['id'] ?? '';

// Valida que se hayan proporcionado tanto tipo como ID
if (!$tipo || !$id) {
    die("Solicitud inválida.");
}

$solicitud = null;
$insumos = [];

// Procesa solicitudes de tipo "Insumo"
if ($tipo === 'Insumo') {
    
    // Consulta para obtener información principal de la solicitud de materiales
    $sentencia = $conexion->prepare("
        SELECT sm.id_solicitud, sm.fecha, sm.emisor, sm.receptor, sm.descripcion, 
               sm.estado, sm.razon_solicitud,
               d1.nombre AS dept_emisor, d2.nombre AS dept_receptor
        FROM solicitud_materiales sm
        LEFT JOIN departamentos d1 ON sm.departamento_emisor = d1.id_departamento
        LEFT JOIN departamentos d2 ON sm.departamento_destino = d2.id_departamento
        WHERE sm.id_solicitud = ?
    ");
    $sentencia->execute([$id]);
    $solicitud = $sentencia->fetch(PDO::FETCH_OBJ);

    // Verifica que la solicitud exista
    if (!$solicitud) {
        die("Solicitud de insumo no encontrada.");
    }

    // Consulta para obtener los insumos detallados de la solicitud
    $sentencia_detalle = $conexion->prepare("
        SELECT i.nombre, i.unidad_medida, i.descripcion AS descripcion_insumo,
               d.cantidad_pedida, d.cantidad_recibida
        FROM detalle_solicitud_material d
        LEFT JOIN insumos i ON d.id_insumo = i.id_insumo
        WHERE d.id_solicitud = ?
    ");
    $sentencia_detalle->execute([$id]);
    $insumos = $sentencia_detalle->fetchAll(PDO::FETCH_OBJ);

// Procesa solicitudes de tipo "Incidencia"
} elseif ($tipo === 'Incidencia') {
    
    // Consulta para obtener información completa de la incidencia
    $sentencia = $conexion->prepare("
        SELECT i.id_incidencia, i.fecha, i.fecha_estimada_finalizacion, 
               i.fecha_finalizacion, i.descripcion, i.prioridad, i.estado,
               d1.nombre AS dept_emisor, d2.nombre AS dept_receptor, 
               CONCAT(t.nombre, ' ', t.apellido) AS trabajador_asignado,
               i.observaciones
        FROM incidencias i
        LEFT JOIN departamentos d1 ON i.departamento_emisor = d1.id_departamento
        LEFT JOIN departamentos d2 ON i.departamento_receptor = d2.id_departamento
        LEFT JOIN trabajadores t ON i.id_trabajador_asignado = t.id_trabajador
        WHERE i.id_incidencia = ?
    ");
    $sentencia->execute([$id]);
    $solicitud = $sentencia->fetch(PDO::FETCH_OBJ);

    // Verifica que la incidencia exista
    if (!$solicitud) {
        die("Incidencia no encontrada.");
    }
} else {
    die("Tipo de solicitud inválido.");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
<title>Detalle de <?= htmlspecialchars($tipo) ?> #<?= $id ?></title>
<style>
    * {
        box-sizing: border-box !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    body {
        background: url("/lugopata/assets/resources/fondoR.png") no-repeat center center fixed !important;
        background-size: cover !important;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
        padding: 15px !important;
        min-height: 100vh !important;
    }

    .detalle-wrapper {
        max-width: 1200px !important;
        margin: 0 auto !important;
        width: 100% !important;
    }

    .title-card {
        background: white !important;
        padding: 20px !important;
        border-radius: 15px !important;
        border: 2px solid #ccc !important;
        margin-bottom: 20px !important;
        width: 100% !important;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1) !important;
    }

    .title-card h1 {
        color: #333 !important;
        font-weight: bold !important;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.2) !important;
        margin: 0 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 15px !important;
        flex-wrap: wrap !important;
        font-size: clamp(1.3rem, 4vw, 2rem) !important;
        text-align: center !important;
    }

    .title-card h1 img {
        width: clamp(35px, 8vw, 45px) !important;
        height: auto !important;
    }

    .title-card p {
        color: #6c757d !important;
        margin-top: 10px !important;
        font-size: clamp(14px, 2vw, 16px) !important;
        text-align: center !important;
    }

    .content-card {
        background: white !important;
        border-radius: 12px !important;
        padding: 20px !important;
        border: 1px solid #ddd !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
    }

    .mensaje-exito {
        background: #d4edda !important;
        color: #155724 !important;
        padding: 12px !important;
        border-radius: 8px !important;
        margin-bottom: 15px !important;
        text-align: center !important;
        font-size: 14px !important;
    }

    .mensaje-sin-datos {
        text-align: center !important;
        padding: 40px 20px !important;
        color: #666 !important;
    }

    .mensaje-sin-datos img {
        width: 60px !important;
        opacity: 0.4 !important;
        margin-bottom: 15px !important;
    }

    /* Información de la solicitud */
    .info-solicitud {
        background: #f8f9fa !important;
        border: 1px solid #e0e0e0 !important;
        border-radius: 10px !important;
        padding: 20px !important;
        margin-bottom: 25px !important;
    }

    .info-item {
        margin-bottom: 12px !important;
        display: flex !important;
        flex-direction: column !important;
        padding: 8px 0 !important;
        border-bottom: 1px dashed #dee2e6 !important;
    }

    .info-item:last-child {
        border-bottom: none !important;
    }

    .info-label {
        font-weight: 600 !important;
        color: #495057 !important;
        font-size: 13px !important;
        margin-bottom: 4px !important;
    }

    .info-value {
        color: #212529 !important;
        font-size: 15px !important;
        line-height: 1.5 !important;
        word-break: break-word !important;
    }

    /* Badges y etiquetas */
    .badge {
        display: inline-block !important;
        padding: 4px 10px !important;
        border-radius: 12px !important;
        font-size: 11px !important;
        font-weight: 600 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.3px !important;
    }

    .estado-badge {
        background: #f8f9fa !important;
        color: #495057 !important;
        border: 1px solid #dee2e6 !important;
    }

    .estado-finalizada {
        background: #d4edda !important;
        color: #155724 !important;
        border-color: #c3e6cb !important;
    }

    .estado-pendiente {
        background: #fff3cd !important;
        color: #856404 !important;
        border-color: #ffeaa7 !important;
    }

    .estado-espera {
        background: #fff3cd !important;
        color: #856404 !important;
        border-color: #ffeaa7 !important;
    }

    .estado-rechazada {
        background: #f8d7da !important;
        color: #721c24 !important;
        border-color: #f5c6cb !important;
    }

    .prioridad-badge {
        background: #e9ecef !important;
        color: #495057 !important;
        border: 1px solid #dee2e6 !important;
    }

    .prioridad-alta {
        background: #f8d7da !important;
        color: #721c24 !important;
        border-color: #f5c6cb !important;
    }

    .prioridad-media {
        background: #fff3cd !important;
        color: #856404 !important;
        border-color: #ffeaa7 !important;
    }

    .prioridad-baja {
        background: #d4edda !important;
        color: #155724 !important;
        border-color: #c3e6cb !important;
    }

    .badge-cantidad {
        display: inline-block !important;
        padding: 4px 8px !important;
        border-radius: 12px !important;
        font-size: 11px !important;
        font-weight: 600 !important;
    }

    .badge-pedida {
        background: #e9ecef !important;
        color: #495057 !important;
        border: 1px solid #dee2e6 !important;
    }

    .badge-recibida {
        background: #d4edda !important;
        color: #155724 !important;
        border: 1px solid #29a946 !important;
    }

    .badge-faltante {
        background: #ffccd1 !important;
        color: #c70101 !important;
        border: 1px solid #ff707f !important;
    }

    /* Sección de observaciones */
    .observaciones {
        background: #fff3cd !important;
        border: 1px solid #ffeaa7 !important;
        border-radius: 8px !important;
        padding: 15px !important;
        margin-top: 15px !important;
    }

    .observaciones h4 {
        color: #856404 !important;
        font-size: 14px !important;
        font-weight: 600 !important;
        margin-bottom: 10px !important;
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
    }

    .observaciones h4 img {
        width: 20px !important;
        height: 20px !important;
    }

    .observaciones p {
        color: #856404 !important;
        font-size: 14px !important;
        line-height: 1.5 !important;
        margin: 0 !important;
    }

    /* Sección de materiales */
    .section-header {
        display: flex !important;
        align-items: center !important;
        gap: 10px !important;
        margin: 25px 0 15px 0 !important;
        padding-bottom: 10px !important;
        border-bottom: 2px solid #e0e0e0 !important;
    }

    .section-header img {
        width: 25px !important;
        height: 25px !important;
    }

    .section-header h3 {
        color: #333 !important;
        font-size: 18px !important;
        font-weight: 600 !important;
        margin: 0 !important;
    }

    /* Tabla responsive */
    .table-container {
        overflow-x: auto !important;
        border: 1px solid #ddd !important;
        border-radius: 10px !important;
        margin: 15px 0 !important;
        background: white !important;
    }

    table {
        width: 100% !important;
        border-collapse: collapse !important;
        min-width: 700px !important;
        font-size: 14px !important;
    }

    th, td {
        border: 1px solid #dee2e6 !important;
        padding: 12px !important;
        text-align: center !important;
        background-color: #fff !important;
        vertical-align: middle !important;
        font-size: 14px !important;
    }

    th {
        background: #cfe2ff !important;
        font-weight: bold !important;
        color: #333 !important;
        position: sticky !important;
        top: 0 !important;
        white-space: nowrap !important;
    }

    tr:hover {
        background: #f8f9fa !important;
    }

    /* Resumen de cantidades */
    .resumen-cantidades {
        background: #f8f9fa !important;
        border: 1px solid #e0e0e0 !important;
        border-radius: 10px !important;
        padding: 15px !important;
        margin-top: 20px !important;
    }

    .resumen-cantidades h4 {
        color: #495057 !important;
        font-size: 14px !important;
        font-weight: 600 !important;
        margin-bottom: 12px !important;
    }

    .resumen-flex {
        display: flex !important;
        gap: 8px !important;
        flex-wrap: wrap !important;
    }

    .resumen-item {
        font-size: 13px !important;
        color: #666 !important;
        padding: 6px 12px !important;
        background: white !important;
        border-radius: 15px !important;
        border: 1px solid #ddd !important;
    }

    .resumen-item strong {
        color: #333 !important;
    }

    /* Botones */
    .btn {
        padding: 10px 16px !important;
        color: white !important;
        border: none !important;
        border-radius: 6px !important;
        cursor: pointer !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        text-decoration: none !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 6px !important;
        transition: background 0.2s !important;
    }

    .btn-secondary {
        background: #6c757d !important;
    }

    .btn-secondary:hover {
        background: #5a6268 !important;
    }

    .btn-icon {
        width: 16px !important;
        height: 16px !important;
    }

    .boton-regresar-container {
        text-align: center !important;
        margin-top: 25px !important;
        padding-top: 20px !important;
        border-top: 1px solid #e0e0e0 !important;
    }

    .btn-regresar {
        max-width: 200px !important;
        background: #6c757d !important;
    }

    /* Responsive */
    @media (min-width: 576px) {
        .info-item {
            flex-direction: row !important;
            align-items: flex-start !important;
        }

        .info-label {
            min-width: 200px !important;
            margin-bottom: 0 !important;
            font-size: 14px !important;
        }

        .info-value {
            flex: 1 !important;
        }
    }

    @media (max-width: 480px) {
        .title-card {
            padding: 15px !important;
        }

        .content-card {
            padding: 15px !important;
        }

        .section-header h3 {
            font-size: 16px !important;
        }
    }
</style>
</head>
<body>
    <div class="detalle-wrapper">
        <!-- Encabezado -->
        <div class="title-card">
            <h1>
                <?php if($tipo === 'Insumo'): ?>
                    <img src="../assets/resources/insumos.png" alt="Insumo">
                <?php else: ?>
                    <img src="../assets/resources/incidencia.png" alt="Incidencia">
                <?php endif; ?>
                Detalle de <?= strtoupper(htmlspecialchars($tipo)) ?> #<?= $id ?>
            </h1>
            <p>
                <?php if($tipo === 'Insumo'): ?>
                    Información completa de la solicitud de materiales
                <?php else: ?>
                    Información completa de la incidencia registrada
                <?php endif; ?>
            </p>
        </div>

        <!-- Contenido principal -->
        <div class="content-card">
            <?php if($tipo === 'Insumo'): ?>
                <!-- Detalles de solicitud de insumos -->
                <div class="info-solicitud">
                    <div class="info-item">
                        <span class="info-label">ID Solicitud:</span>
                        <span class="info-value"><strong>#<?= $solicitud->id_solicitud ?></strong></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Fecha de solicitud:</span>
                        <span class="info-value"><?= $solicitud->fecha ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Solicitante:</span>
                        <span class="info-value"><?= htmlspecialchars($solicitud->emisor) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Departamento Emisor:</span>
                        <span class="info-value"><?= htmlspecialchars($solicitud->dept_emisor) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Departamento Destino:</span>
                        <span class="info-value"><?= htmlspecialchars($solicitud->dept_receptor) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Estado:</span>
                        <span class="info-value">
                            <?php 
                                $estadoClass = '';
                                $estado = $solicitud->estado ?? 'En espera';
                                switch($estado) {
                                    case 'Finalizada': $estadoClass = 'estado-finalizada'; break;
                                    case 'Pendiente': $estadoClass = 'estado-pendiente'; break;
                                    case 'En espera': $estadoClass = 'estado-espera'; break;
                                    case 'Rechazada': $estadoClass = 'estado-rechazada'; break;
                                    default: $estadoClass = 'estado-espera';
                                }
                            ?>
                            <span class="badge estado-badge <?= $estadoClass ?>"><?= $estado ?></span>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Descripción:</span>
                        <span class="info-value"><?= nl2br(htmlspecialchars($solicitud->descripcion)) ?></span>
                    </div>
                    
                    <?php if(!empty($solicitud->razon_solicitud)): ?>
                    <div class="observaciones">
                        <h4>
                            <img src="../assets/resources/ojo-de-lupa.png" alt="Observaciones">
                            Observaciones / Razón:
                        </h4>
                        <p><?= nl2br(htmlspecialchars($solicitud->razon_solicitud)) ?></p>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Sección de insumos solicitados -->
                <div class="section-header">
                    <img src="../assets/resources/la-gestion-del-inventario.png" alt="Materiales">
                    <h3>Materiales Solicitados</h3>
                </div>
                
                <?php if(count($insumos) === 0): ?>
                    <div class="mensaje-sin-datos">
                        <img src="../assets/resources/vacio.png" alt="Sin datos">
                        <p>No hay insumos registrados para esta solicitud.</p>
                    </div>
                <?php else: ?>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Nombre del Insumo</th>
                                    <th>Descripción</th>
                                    <th>Unidad de Medida</th>
                                    <th>Cantidad Pedida</th>
                                    <th>Cantidad Recibida</th>
                                    <th>Cantidad Faltante</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $totalPedido = 0;
                                $totalRecibido = 0;
                                $totalFaltante = 0;
                                ?>
                                <?php foreach($insumos as $ins): 
                                    $faltante = $ins->cantidad_pedida - $ins->cantidad_recibida;
                                    $totalPedido += $ins->cantidad_pedida;
                                    $totalRecibido += $ins->cantidad_recibida;
                                    $totalFaltante += $faltante;
                                ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($ins->nombre) ?></strong></td>
                                    <td><?= htmlspecialchars($ins->descripcion_insumo) ?></td>
                                    <td><span class="badge-cantidad badge-pedida"><?= htmlspecialchars($ins->unidad_medida) ?></span></td>
                                    <td><span class="badge-cantidad badge-pedida"><?= $ins->cantidad_pedida ?></span></td>
                                    <td>
                                        <?php if($ins->cantidad_recibida > 0): ?>
                                            <span class="badge-cantidad badge-recibida"><?= $ins->cantidad_recibida ?></span>
                                        <?php else: ?>
                                            <span class="badge-cantidad badge-faltante">0</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($faltante > 0): ?>
                                            <span class="badge-cantidad badge-faltante"><?= $faltante ?></span>
                                        <?php else: ?>
                                            <span class="badge-cantidad badge-recibida">0</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <!-- Fila de totales -->
                                <tr style="background-color: #f8f9fa; font-weight: bold;">
                                    <td colspan="3" style="text-align: right;">TOTALES:</td>
                                    <td><span class="badge-cantidad badge-pedida"><?= $totalPedido ?></span></td>
                                    <td><span class="badge-cantidad badge-recibida"><?= $totalRecibido ?></span></td>
                                    <td>
                                        <?php if($totalFaltante > 0): ?>
                                            <span class="badge-cantidad badge-faltante"><?= $totalFaltante ?></span>
                                        <?php else: ?>
                                            <span class="badge-cantidad badge-recibida">0</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Resumen de cantidades -->
                    <div class="resumen-cantidades">
                        <h4>Resumen de cantidades:</h4>
                        <div class="resumen-flex">
                            <span class="resumen-item">Total pedido: <strong><?= $totalPedido ?></strong></span>
                            <span class="resumen-item">Total recibido: <strong><?= $totalRecibido ?></strong></span>
                            <span class="resumen-item">Total faltante: <strong><?= $totalFaltante ?></strong></span>
                        </div>
                    </div>
                <?php endif; ?>

            <?php elseif($tipo === 'Incidencia'): ?>
                <!-- Detalles de incidencia -->
                <div class="info-solicitud">
                    <div class="info-item">
                        <span class="info-label">ID Incidencia:</span>
                        <span class="info-value"><strong>#<?= $solicitud->id_incidencia ?></strong></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Fecha de registro:</span>
                        <span class="info-value"><?= $solicitud->fecha ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Fecha estimada finalización:</span>
                        <span class="info-value">
                            <?php if($solicitud->fecha_estimada_finalizacion): ?>
                                <?= $solicitud->fecha_estimada_finalizacion ?>
                            <?php else: ?>
                                <span style="color: #6c757d; font-style: italic;">No especificada</span>
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Fecha de finalización:</span>
                        <span class="info-value">
                            <?php if($solicitud->fecha_finalizacion): ?>
                                <?= $solicitud->fecha_finalizacion ?>
                            <?php else: ?>
                                <span style="color: #6c757d; font-style: italic;">En proceso</span>
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Departamento Emisor:</span>
                        <span class="info-value"><?= htmlspecialchars($solicitud->dept_emisor) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Departamento Receptor:</span>
                        <span class="info-value"><?= htmlspecialchars($solicitud->dept_receptor) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Prioridad:</span>
                        <span class="info-value">
                            <?php 
                                $prioridadClass = '';
                                $prioridad = $solicitud->prioridad ?? 'Media';
                                switch(strtolower($prioridad)) {
                                    case 'alta': $prioridadClass = 'prioridad-alta'; break;
                                    case 'media': $prioridadClass = 'prioridad-media'; break;
                                    case 'baja': $prioridadClass = 'prioridad-baja'; break;
                                    default: $prioridadClass = 'prioridad-media';
                                }
                            ?>
                            <span class="badge prioridad-badge <?= $prioridadClass ?>"><?= ucfirst($prioridad) ?></span>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Estado:</span>
                        <span class="info-value">
                            <?php 
                                $estadoClass = '';
                                $estado = $solicitud->estado ?? 'Pendiente';
                                switch($estado) {
                                    case 'Finalizada': $estadoClass = 'estado-finalizada'; break;
                                    case 'Pendiente': $estadoClass = 'estado-pendiente'; break;
                                    case 'En proceso': $estadoClass = 'estado-espera'; break;
                                    default: $estadoClass = 'estado-pendiente';
                                }
                            ?>
                            <span class="badge estado-badge <?= $estadoClass ?>"><?= $estado ?></span>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Trabajador Asignado:</span>
                        <span class="info-value">
                            <?php if($solicitud->trabajador_asignado): ?>
                                <?= htmlspecialchars($solicitud->trabajador_asignado) ?>
                            <?php else: ?>
                                <span style="color: #6c757d; font-style: italic;">Sin asignar</span>
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Descripción:</span>
                        <span class="info-value"><?= nl2br(htmlspecialchars($solicitud->descripcion)) ?></span>
                    </div>
                    
                    <?php if(!empty($solicitud->observaciones)): ?>
                    <div class="observaciones">
                        <h4>
                            <img src="../assets/resources/ojo-de-lupa.png" alt="Observaciones">
                            Observaciones:
                        </h4>
                        <p><?= nl2br(htmlspecialchars($solicitud->observaciones)) ?></p>
                    </div>
                    <?php endif; ?>
                </div>

            <?php endif; ?>

            <!-- Botón para regresar -->
            <div class="boton-regresar-container">
                <button type="button" onclick="history.back()" class="btn btn-secondary btn-regresar">
                    <img src="../assets/resources/volver2.png" alt="Regresar" class="btn-icon">
                    Regresar
                </button>
            </div>
        </div>
    </div>

    <script>
    // Efecto hover para botones
    document.querySelectorAll('.btn').forEach(btn => {
        btn.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
        });
        
        btn.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
    
    // Mostrar información adicional si se hace clic en la descripción (para móviles)
    document.querySelectorAll('.info-item .info-value').forEach(item => {
        if(item.textContent.length > 150) {
            item.style.cursor = 'pointer';
            item.title = 'Clic para ver más';
            
            item.addEventListener('click', function() {
                if(this.style.whiteSpace === 'normal') {
                    this.style.whiteSpace = 'nowrap';
                    this.style.overflow = 'hidden';
                    this.style.textOverflow = 'ellipsis';
                } else {
                    this.style.whiteSpace = 'normal';
                    this.style.overflow = 'visible';
                    this.style.textOverflow = 'clip';
                }
            });
        }
    });
    </script>
</body>
</html>