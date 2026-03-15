<?php
session_start();
// Verifica que se haya proporcionado un ID de solicitud
if (!isset($_GET['id'])) exit("No hay id de solicitud");
$id_solicitud = (int)$_GET['id'];

include_once "../conex.php";
require_once "SalidaAlmacen.php";

// Obtiene los datos de la solicitud de salida
$salidaObj = new SalidaAlmacen($conexion);
$solicitud = $salidaObj->obtener($id_solicitud);
if (!$solicitud) exit("No existe la solicitud");

// Obtiene los detalles de los materiales de la solicitud
$detalles = $salidaObj->obtenerDetalle($id_solicitud);

// Calcular estadísticas
$total_materiales = count($detalles);
$cantidad_total_solicitada = 0;

foreach($detalles as $d) {
    $cantidad_total_solicitada += $d->cantidad_solicitada;
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Informe de Salida #<?= $id_solicitud ?></title>
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
        min-width: 150px;
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
        min-height: 40px;
    }
    .tabla-materiales {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 15px;
        font-size: 10px;
    }
    .tabla-materiales th {
        background: #f0f0f0;
        color: #333;
        padding: 6px;
        text-align: left;
        font-weight: bold;
        border: 1px solid #ccc;
    }
    .tabla-materiales td {
        padding: 6px;
        border: 1px solid #ddd;
        vertical-align: top;
    }
    .tabla-materiales tr:nth-child(even) {
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
    
    .firma-section {
        margin-top: 20px;
        margin-bottom: 20px;
    }
    .firma-container {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
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
        grid-template-columns: repeat(2, 1fr);
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
        <h1>INFORME DE SALIDA DE ALMACÉN #<?= str_pad($id_solicitud, 6, '0', STR_PAD_LEFT) ?></h1>
        <div class="subtitulo">Fecha de Emisión: <?= date('d/m/Y H:i') ?></div>
    </div>

    <!-- Información Básica Compacta -->
    <div class="seccion">
        <h3>INFORMACIÓN GENERAL</h3>
        <div class="compact-grid">
            <div>
                <div class="info-item">
                    <strong>Fecha Salida:</strong> <?= date('d/m/Y', strtotime($solicitud->fecha)) ?>
                </div>
                <div class="info-item">
                    <strong>Estado:</strong> 
                    <span class="estado-badge estado-<?= strtolower(str_replace(' ', '-', $solicitud->estado ?? 'pendiente')) ?>">
                        <?= $solicitud->estado ?? 'Pendiente' ?>
                    </span>
                </div>
                <div class="info-item">
                    <strong>Emisor:</strong> <?= htmlspecialchars($solicitud->emisor) ?>
                </div>
                <div class="info-item">
                    <strong>Depto. Emisor:</strong> <?= $solicitud->nombre_departamento_emisor ?>
                </div>
            </div>
            <div>
                <div class="info-item">
                    <strong>Receptor:</strong> <?= htmlspecialchars($solicitud->receptor) ?>
                </div>
                <div class="info-item">
                    <strong>Depto. Destino:</strong> <?= $solicitud->nombre_departamento_destino ?>
                </div>
                <?php if (isset($solicitud->responsable_almacen) && $solicitud->responsable_almacen): ?>
                <div class="info-item">
                    <strong>Responsable Almacén:</strong> <?= htmlspecialchars($solicitud->responsable_almacen) ?>
                </div>
                <?php endif; ?>
                <?php if (isset($solicitud->fecha_finalizacion) && $solicitud->fecha_finalizacion): ?>
                <div class="info-item">
                    <strong>Fecha Entrega:</strong> <?= date('d/m/Y H:i', strtotime($solicitud->fecha_finalizacion)) ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Descripción Compacta -->
    <?php if ($solicitud->descripcion): ?>
    <div class="seccion">
        <h3>DESCRIPCIÓN</h3>
        <div class="descripcion">
            <?= nl2br(htmlspecialchars($solicitud->descripcion)) ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Estadísticas Compactas -->
    <?php if ($total_materiales > 0): ?>
    <div class="seccion">
        <h3>RESUMEN DE MATERIALES</h3>
        <div class="estadisticas">
            <div class="estadistica-item">
                <div class="estadistica-numero"><?= $total_materiales ?></div>
                <div class="estadistica-texto">Materiales Solicitados</div>
            </div>
            <div class="estadistica-item">
                <div class="estadistica-numero"><?= $cantidad_total_solicitada ?></div>
                <div class="estadistica-texto">Cantidad Total</div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Tabla de Materiales Compacta -->
    <?php if (!empty($detalles)): ?>
    <div class="seccion">
        <h3>DETALLE DE MATERIALES</h3>
        <table class="tabla-materiales">
            <thead>
                <tr>
                    <th style="width: 5%">#</th>
                    <th style="width: 50%">Material</th>
                    <th style="width: 15%">Unidad</th>
                    <th style="width: 15%">Cant. Solicitada</th>
                    <th style="width: 15%">Cant. Entregada</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($detalles as $index => $d): ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td>
                        <strong><?= htmlspecialchars($d->id_insumo) ?></strong> - 
                        <?= htmlspecialchars(substr($d->nombre, 0, 40)) ?>
                        <?= strlen($d->nombre) > 40 ? '...' : '' ?>
                    </td>
                    <td><?= htmlspecialchars($d->unidad) ?></td>
                    <td>
                        <?= $d->cantidad_solicitada ?>
                        <?php if (isset($d->cantidad_entregada) && $d->cantidad_entregada < $d->cantidad_solicitada): ?>
                            <span class="estado-badge estado-pendiente" style="margin-left: 3px;">Parcial</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?= $d->cantidad_entregada ?? '0' ?>
                        <?php if (isset($d->cantidad_entregada) && $d->cantidad_entregada == $d->cantidad_solicitada): ?>
                            <span class="estado-badge estado-completada" style="margin-left: 3px;">Completo</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <!-- Firmas con líneas alineadas -->
    <div class="firma-section">
        <h3>FIRMAS</h3>
        <div class="firma-container">
            <div class="firma-box">
                <strong>EMISOR / AUTORIZA</strong>
                <div class="firma-img-container">
                    <?php if (!empty($detalles[0]->firma_emisor)): ?>
                        <img src="<?= $detalles[0]->firma_emisor ?>" alt="Firma Emisor" class="firma-img">
                    <?php else: ?>
                        <div style="height: 40px;"></div>
                    <?php endif; ?>
                    <div class="firma-line"></div>
                </div>
                <div class="nombre-firma">
                    <?= htmlspecialchars($solicitud->emisor) ?>
                </div>
            </div>
            
            <div class="firma-box">
                <strong>RECEPTOR / RECIBE CONFORME</strong>
                <div class="firma-img-container">
                    <!-- Espacio para firma física del receptor -->
                    <div style="height: 40px;"></div>
                    <div class="firma-line"></div>
                </div>
                <div class="nombre-firma">
                    __________________________<br>
                    <span style="margin-top: 5px; display: block;">
                        <?= htmlspecialchars($solicitud->receptor) ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Observaciones adicionales si existen -->
    <?php if (isset($solicitud->observaciones) && $solicitud->observaciones): ?>
    <div class="seccion">
        <h3>OBSERVACIONES</h3>
        <div class="descripcion">
            <?= nl2br(htmlspecialchars($solicitud->observaciones)) ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Pie de página -->
    <div class="pie-pagina">
        <div>Documento generado automáticamente por el Sistema de Gestión de Almacén</div>
        <div>Fecha de generación: <?= date('d/m/Y H:i:s') ?></div>
        <div style="margin-top: 5px; font-size: 8px;">
            Este documento sirve como comprobante de salida de materiales del almacén
        </div>
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