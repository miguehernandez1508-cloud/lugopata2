<?php
session_start();
if (!isset($_GET['id'])) exit("No hay id de incidencia");
$id_incidencia = (int)$_GET['id'];

include_once __DIR__ . "/../conex.php";
require_once __DIR__ . "/incidencia.php";
require_once __DIR__ . "/../user/gestorsesion.php";

GestorSesiones::iniciar();

// Obtener usuario actual
$nombreUsuario = GestorSesiones::get('nombre_completo');
$firmaRutaSesion = GestorSesiones::get('firma');

$incObj = new Incidencia($conexion);
$inc = $incObj->obtener($id_incidencia);
if (!$inc) exit("No existe la incidencia");

// Obtener imágenes
$imgs = $incObj->obtenerImagenes($id_incidencia);

// Obtener progreso de fases
$progreso_fases = $incObj->obtenerProgresoFases($id_incidencia);

// Obtener conformidad del solicitante si existe
$sql_conformidad = "SELECT ic.*, u.username as solicitante_nombre 
                    FROM incidencia_conformidad ic
                    JOIN usuarios u ON ic.id_usuario_solicitante = u.id_usuario
                    WHERE ic.id_incidencia = ?";
$stmt_conformidad = $conexion->prepare($sql_conformidad);
$stmt_conformidad->execute([$id_incidencia]);
$conformidad = $stmt_conformidad->fetch(PDO::FETCH_OBJ);
$stmt_conformidad->closeCursor();

// Obtener justificación de rechazo si existe
$sql_rechazo = "SELECT ir.*, u.username as supervisor_nombre 
                FROM incidencia_rechazos ir
                JOIN usuarios u ON ir.id_supervisor = u.id_usuario
                WHERE ir.id_incidencia = ?";
$stmt_rechazo = $conexion->prepare($sql_rechazo);
$stmt_rechazo->execute([$id_incidencia]);
$rechazo = $stmt_rechazo->fetch(PDO::FETCH_OBJ);
$stmt_rechazo->closeCursor();

// Calcular estadísticas de fases
$total_fases = 0;
$fases_completadas = 0;
$fases_aprobadas = 0;
$fases_rechazadas = 0;

if (!empty($progreso_fases)) {
    $total_fases = count($progreso_fases);
    foreach ($progreso_fases as $fase) {
        if ($fase->estado === 'completada') $fases_completadas++;
        if ($fase->estado === 'aprobada') $fases_aprobadas++;
        if ($fase->estado === 'rechazada') $fases_rechazadas++;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Informe de Incidencia #<?= $id_incidencia ?></title>
<style>
    @page {
        size: A4;
        margin: 15mm;
    }
    body {
        font-family: Arial, sans-serif;
        font-size: 11px;
        line-height: 1.3;
        color: #333;
        background: white;
        margin: 0;
        padding: 0;
    }
    .contenedor {
        max-width: 210mm;
        margin: 0 auto;
        padding: 0;
        box-sizing: border-box;
    }
    .encabezado {
        text-align: center;
        margin-bottom: 15px;
        border-bottom: 1px solid #ccc;
        padding-bottom: 10px;
    }
    .encabezado h1 {
        margin: 0 0 5px 0;
        font-size: 18px;
        color: #2c3e50;
    }
    .encabezado .subtitulo {
        font-size: 12px;
        color: #666;
    }
    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 8px;
        margin-bottom: 15px;
        font-size: 10px;
    }
    .info-item {
        display: flex;
        margin-bottom: 5px;
    }
    .info-item strong {
        min-width: 130px;
        color: #2c3e50;
    }
    .seccion {
        margin-bottom: 15px;
    }
    .seccion h3 {
        background: #f0f0f0;
        color: #333;
        padding: 5px 8px;
        margin: 0 0 10px 0;
        font-size: 13px;
        border-left: 3px solid #2c3e50;
    }
    .descripcion {
        border: 1px solid #ddd;
        padding: 10px;
        border-radius: 3px;
        background: #f9f9f9;
        margin-bottom: 15px;
        font-size: 11px;
        min-height: 60px;
    }
    .tabla-fases {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 15px;
        font-size: 10px;
    }
    .tabla-fases th {
        background: #f0f0f0;
        color: #333;
        padding: 6px;
        text-align: left;
        font-weight: bold;
        border: 1px solid #ccc;
    }
    .tabla-fases td {
        padding: 6px;
        border: 1px solid #ddd;
        vertical-align: top;
    }
    .tabla-fases tr:nth-child(even) {
        background: #f9f9f9;
    }
    .estado-badge {
        display: inline-block;
        padding: 2px 6px;
        border-radius: 10px;
        font-size: 9px;
        font-weight: bold;
    }
    .estado-pendiente { background: #bdc3c7; color: #333; }
    .estado-completada { background: #e67e22; color: white; }
    .estado-aprobada { background: #27ae60; color: white; }
    .estado-rechazada { background: #e74c3c; color: white; }
    .estado-finalizada { background: #2ecc71; color: white; }
    .estado-rechazada-solicitante { background: #c0392b; color: white; }
    .estado-en-espera { background: #95a5a6; color: white; }
    
    .firma-section {
        margin-top: 20px;
        margin-bottom: 20px;
    }
    .firma-container {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin-top: 15px;
    }
    .firma-box {
        text-align: center;
        padding: 10px 5px;
        border: 1px solid #ddd;
        border-radius: 3px;
        min-height: 120px;
        font-size: 10px;
        display: flex;
        flex-direction: column;
    }
    .firma-box strong {
        display: block;
        margin-bottom: 5px;
        color: #2c3e50;
        font-size: 11px;
    }
    .firma-img-container {
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        min-height: 60px;
    }
    .firma-line {
        border-top: 1px solid #333;
        width: 100%;
        margin-top: auto;
    }
    .firma-img {
        max-width: 120px;
        max-height: 40px;
        margin: 0 auto 10px auto;
        display: block;
    }
    .nombre-firma {
        margin-top: 5px;
        font-size: 10px;
        text-align: center;
    }
    
    .estadisticas {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 8px;
        margin-bottom: 15px;
        font-size: 10px;
    }
    .estadistica-item {
        text-align: center;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 3px;
        background: #f9f9f9;
    }
    .estadistica-numero {
        font-size: 16px;
        font-weight: bold;
        color: #2c3e50;
    }
    .estadistica-texto {
        font-size: 10px;
        color: #666;
    }
    
    .conformidad-box {
        border: 1px solid #bdc3c7;
        background: #f8f9fa;
        padding: 10px;
        border-radius: 3px;
        margin-bottom: 15px;
        font-size: 10px;
    }
    
    .evidencias-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 8px;
        margin-top: 8px;
    }
    .evidencia-img {
        width: 100%;
        height: 80px;
        object-fit: cover;
        border: 1px solid #ddd;
        border-radius: 2px;
    }
    
    .rechazo-box {
        border: 1px solid #e74c3c;
        background: #fdf2f0;
        padding: 10px;
        border-radius: 3px;
        margin-bottom: 15px;
        font-size: 10px;
    }
    
    .compact-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
        margin-bottom: 15px;
    }
    
    .pie-pagina {
        text-align: center;
        margin-top: 30px;
        padding-top: 10px;
        border-top: 1px solid #ccc;
        font-size: 9px;
        color: #666;
        line-height: 1.4;
    }
    
    .conformidad-content {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
        margin-bottom: 10px;
        font-size: 10px;
    }
    
    @media print {
        body {
            font-size: 10px;
        }
        .contenedor {
            padding: 0;
            max-width: 100%;
        }
    }
</style>
</head>
<body>
<div class="contenedor">
    <div class="encabezado">
        <h1>INFORME DE INCIDENCIA #<?= str_pad($id_incidencia, 6, '0', STR_PAD_LEFT) ?></h1>
        <div class="subtitulo">Fecha de Emisión: <?= date('d/m/Y H:i') ?></div>
    </div>

    <!-- Información Básica Compacta -->
    <div class="seccion">
        <h3>INFORMACIÓN GENERAL</h3>
        <div class="compact-grid">
            <div>
                <div class="info-item">
                    <strong>Fecha Creación:</strong> <?= date('d/m/Y', strtotime($inc->fecha)) ?>
                </div>
                <div class="info-item">
                    <strong>Estado:</strong> 
                    <span class="estado-badge estado-<?= strtolower(str_replace(' ', '-', $inc->estado)) ?>">
                        <?= $inc->estado ?>
                    </span>
                </div>
                <div class="info-item">
                    <strong>Reportado por:</strong> <?= $inc->creado_por ?? $nombreUsuario ?>
                </div>
                <div class="info-item">
                    <strong>Depto. Emisor:</strong> <?= $inc->depto_emisor ?>
                </div>
                <div class="info-item">
                    <strong>Prioridad:</strong> <?= $inc->prioridad ?>
                </div>
            </div>
            <div>
                <div class="info-item">
                    <strong>Depto. Receptor:</strong> <?= $inc->depto_receptor ?>
                </div>
                <div class="info-item">
                    <strong>Ubicación:</strong> <?= $inc->ubicacion ?? 'No especificada' ?>
                </div>
                <div class="info-item">
                    <strong>Técnico:</strong> 
                    <?= !empty($inc->trabajador_nombre) ? $inc->trabajador_nombre . ' ' . $inc->trabajador_apellido : 'No asignado' ?>
                </div>
                <?php if ($inc->fecha_finalizacion): ?>
                <div class="info-item">
                    <strong>Fecha Cierre:</strong> <?= date('d/m/Y H:i', strtotime($inc->fecha_finalizacion)) ?>
                </div>
                <?php endif; ?>
                <?php if ($inc->fecha_estimada_finalizacion): ?>
                <div class="info-item">
                    <strong>Fecha Estimada:</strong> <?= date('d/m/Y', strtotime($inc->fecha_estimada_finalizacion)) ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Descripción Compacta -->
    <div class="seccion">
        <h3>DESCRIPCIÓN</h3>
        <div class="descripcion">
            <?= nl2br(htmlspecialchars($inc->descripcion)) ?>
        </div>
    </div>

    <!-- Estadísticas Compactas -->
    <?php if ($total_fases > 0): ?>
    <div class="seccion">
        <h3>PROGRESO</h3>
        <div class="estadisticas">
            <div class="estadistica-item">
                <div class="estadistica-numero"><?= $total_fases ?></div>
                <div class="estadistica-texto">Fases</div>
            </div>
            <div class="estadistica-item">
                <div class="estadistica-numero"><?= $fases_completadas ?></div>
                <div class="estadistica-texto">Completadas</div>
            </div>
            <div class="estadistica-item">
                <div class="estadistica-numero"><?= $fases_aprobadas ?></div>
                <div class="estadistica-texto">Aprobadas</div>
            </div>
            <div class="estadistica-item">
                <div class="estadistica-numero"><?= $fases_rechazadas ?></div>
                <div class="estadistica-texto">Rechazadas</div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Tabla de Fases Compacta -->
    <?php if (!empty($progreso_fases)): ?>
    <div class="seccion">
        <h3>DETALLE DE FASES</h3>
        <table class="tabla-fases">
            <thead>
                <tr>
                    <th style="width: 5%">#</th>
                    <th style="width: 25%">Fase</th>
                    <th style="width: 40%">Descripción</th>
                    <th style="width: 15%">Estado</th>
                    <th style="width: 15%">Fecha</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($progreso_fases as $fase): 
                    $fecha = $fase->fecha_completado ?: $fase->fecha_aprobacion;
                ?>
                <tr>
                    <td><?= $fase->orden ?></td>
                    <td><strong><?= htmlspecialchars($fase->nombre_fase) ?></strong></td>
                    <td><?= htmlspecialchars(substr($fase->descripcion, 0, 60)) ?><?= strlen($fase->descripcion) > 60 ? '...' : '' ?></td>
                    <td>
                        <span class="estado-badge estado-<?= $fase->estado ?>">
                            <?= ucfirst($fase->estado) ?>
                        </span>
                    </td>
                    <td>
                        <?= $fecha ? date('d/m/Y', strtotime($fecha)) : '--' ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <!-- Conformidad del Solicitante -->
    <?php if ($conformidad): ?>
    <div class="seccion">
        <h3>CONFIRMACIÓN SOLICITANTE</h3>
        <div class="conformidad-box">
            <div class="conformidad-content">
                <div>
                    <strong>Estado:</strong> 
                    <span style="font-weight: bold; color: <?= $conformidad->confirmada ? '#27ae60' : '#e74c3c' ?>">
                        <?= $conformidad->confirmada ? 'Aceptada' : 'Rechazada' ?>
                    </span><br>
                    <strong>Fecha:</strong> <?= date('d/m/Y', strtotime($conformidad->fecha_confirmacion)) ?><br>
                    <strong>Solicitante:</strong> <?= $conformidad->solicitante_nombre ?>
                </div>
                
                <?php if ($conformidad->comentarios): ?>
                <div>
                    <strong>Comentario:</strong><br>
                    <div style="background: white; padding: 5px; border-radius: 2px; margin-top: 3px; font-size: 9px;">
                        <?= nl2br(htmlspecialchars(substr($conformidad->comentarios, 0, 100))) ?>
                        <?= strlen($conformidad->comentarios) > 100 ? '...' : '' ?>
                    </div>
                    
                    <?php if ($conformidad->calificacion): ?>
                    <div style="margin-top: 8px;">
                        <strong>Calificación:</strong> 
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span style="color: <?= $i <= $conformidad->calificacion ? '#f39c12' : '#ddd' ?>; font-size: 12px;">★</span>
                        <?php endfor; ?>
                        (<?= $conformidad->calificacion ?>/5)
                    </div>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <div>
                    <?php if ($conformidad->calificacion): ?>
                    <div style="margin-top: 8px;">
                        <strong>Calificación:</strong> 
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span style="color: <?= $i <= $conformidad->calificacion ? '#f39c12' : '#ddd' ?>; font-size: 12px;">★</span>
                        <?php endfor; ?>
                        (<?= $conformidad->calificacion ?>/5)
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Justificación de Rechazo -->
    <?php if ($rechazo): ?>
    <div class="seccion">
        <h3>JUSTIFICACIÓN RECHAZO</h3>
        <div class="rechazo-box">
            <div class="conformidad-content">
                <div>
                    <strong>Supervisor:</strong> <?= $rechazo->supervisor_nombre ?><br>
                    <strong>Fecha:</strong> <?= date('d/m/Y', strtotime($rechazo->fecha_rechazo)) ?>
                </div>
                <div>
                    <strong>Motivo:</strong><br>
                    <div style="background: white; padding: 5px; border-radius: 2px; margin-top: 3px; font-size: 9px;">
                        <?= nl2br(htmlspecialchars(substr($rechazo->justificacion, 0, 120))) ?>
                        <?= strlen($rechazo->justificacion) > 120 ? '...' : '' ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Evidencias Fotográficas -->
    <?php if (!empty($imgs)): ?>
    <div class="seccion">
        <h3>EVIDENCIAS (<?= count($imgs) ?>)</h3>
        <div class="evidencias-grid">
            <?php foreach($imgs as $index => $im): ?>
                <div>
                    <img src="<?= $im->ruta ?>" class="evidencia-img" alt="Evidencia <?= $index + 1 ?>">
                    <div style="text-align: center; font-size: 9px; margin-top: 3px;">
                        <?= date('d/m/Y', strtotime($im->fecha_subida)) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Firmas con líneas alineadas -->
    <div class="firma-section">
        <h3>FIRMAS</h3>
        <div class="firma-container">
            <div class="firma-box">
                <strong>REPORTADO POR</strong>
                <div class="firma-img-container">
                    <?php if ($firmaRutaSesion): ?>
                        <img src="<?= $firmaRutaSesion ?>" alt="Firma" class="firma-img">
                    <?php else: ?>
                        <div style="height: 40px;"></div>
                    <?php endif; ?>
                    <div class="firma-line"></div>
                </div>
                <div class="nombre-firma">
                    <?= $inc->creado_por ?? $nombreUsuario ?>
                </div>
            </div>
            
            <div class="firma-box">
                <strong>TÉCNICO RESPONSABLE</strong>
                <div class="firma-img-container">
                    <?php if (!empty($inc->trabajador_firma)): ?>
                        <img src="<?= $inc->trabajador_firma ?>" alt="Firma Técnico" class="firma-img">
                    <?php else: ?>
                        <div style="height: 40px;"></div>
                    <?php endif; ?>
                    <div class="firma-line"></div>
                </div>
                <div class="nombre-firma">
                    <?= !empty($inc->trabajador_nombre) ? $inc->trabajador_nombre . ' ' . $inc->trabajador_apellido : 'No asignado' ?>
                </div>
            </div>
            
            <div class="firma-box">
                <strong>SUPERVISOR</strong>
                <div class="firma-img-container">
                    <div style="height: 40px;"></div>
                    <div class="firma-line"></div>
                </div>
                <div class="nombre-firma">
                    __________________________
                </div>
            </div>
        </div>
    </div>

    <!-- Pie de página -->
    <div class="pie-pagina">
        <div>Documento generado automáticamente por el Sistema de Gestión</div>
        <div>Fecha de generación: <?= date('d/m/Y H:i:s') ?></div>
    </div>

</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    setTimeout(() => {
        window.print();
        setTimeout(() => { window.close(); }, 500);
    }, 300);
});
</script>
</body>
</html>