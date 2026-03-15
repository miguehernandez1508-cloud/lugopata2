<?php
// /incidencias/listarincidencias.php
session_start();
include_once __DIR__ . "/../encabezado.php";
require_once __DIR__ . "/Incidencia.php";
require_once __DIR__ . "/../user/gestorsesion.php";

GestorSesiones::iniciar();

// paginación
$registrosPorPagina = 10;
$pagina = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$inicio = ($pagina - 1) * $registrosPorPagina;

$filtro = isset($_GET['estado']) ? $_GET['estado'] : 'pendiente_espera';

$incObj = new Incidencia($conexion);
$total = $conexion->query("SELECT COUNT(*) FROM incidencias")->fetchColumn();
$totalPaginas = ceil($total / $registrosPorPagina);

$incidencias = $incObj->listar($inicio, $registrosPorPagina, $filtro);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
<title>Listado de Incidencias</title>
<style>
    /* ===== RESET Y VARIABLES ===== */
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

    .incidencias-wrapper {
        max-width: 1600px !important;
        margin: 0 auto !important;
        width: 100% !important;
    }

    /* ===== TARJETA DE TÍTULO ===== */
    .title-card {
        background: white !important;
        padding: 20px !important;
        border-radius: 15px !important;
        border: 2px solid #ccc !important;
        margin-bottom: 20px !important;
        width: 100% !important;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1) !important;
        text-align: center !important;
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
    }

    .title-card h1 img {
        width: clamp(35px, 8vw, 45px) !important;
        height: auto !important;
    }

    .title-card p {
        color: #6c757d !important;
        margin-top: 10px !important;
        font-size: clamp(14px, 2vw, 16px) !important;
    }

    /* ===== BOTONES MINIMALISTA FLAT ===== */
    .btn {
        padding: 10px 20px !important;
        border: none !important;
        border-radius: 6px !important;
        font-size: 13px !important;
        font-weight: 500 !important;
        text-decoration: none !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 8px !important;
        cursor: pointer !important;
        transition: all 0.2s ease !important;
        background: #495057 !important;
        color: white !important;
    }

    .btn:hover {
        background: #343a40 !important;
        transform: translateY(-1px) !important;
    }

    .btn-primary {
        background: #0d6efd !important;
    }
    .btn-primary:hover {
        background: #0b5ed7 !important;
    }

    .btn-success {
        background: #198754 !important;
    }
    .btn-success:hover {
        background: #157347 !important;
    }

    .btn-warning {
        background: #ffc107 !important;
        color: #212529 !important;
    }
    .btn-warning:hover {
        background: #e0a800 !important;
    }

    .btn-danger {
        background: #dc3545 !important;
    }
    .btn-danger:hover {
        background: #bb2d3b !important;
    }

    .btn-secondary {
        background: #6c757d !important;
    }
    .btn-secondary:hover {
        background: #5c636a !important;
    }

    .btn-sm {
        padding: 6px 12px !important;
        font-size: 12px !important;
    }

    .btn-xs {
        padding: 4px 8px !important;
        font-size: 11px !important;
    }

    .btn-icon {
        width: 16px !important;
        height: 16px !important;
        flex-shrink: 0 !important;
    }

    /* ===== TARJETA DE CONTENIDO ===== */
    .content-card {
        background: white !important;
        border-radius: 12px !important;
        padding: 20px !important;
        border: 1px solid #ddd !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
    }

    /* ===== FILTROS ===== */
    .filtros-container {
        margin-bottom: 20px !important;
        padding-bottom: 20px !important;
        border-bottom: 1px solid #e0e0e0 !important;
    }

    .filtro-group {
        display: flex !important;
        flex-direction: column !important;
        gap: 8px !important;
        max-width: 400px !important;
        margin: 0 auto !important;
    }

    .filtro-group label {
        font-weight: 600 !important;
        color: #333 !important;
        font-size: 14px !important;
    }

    .filtro-group select {
        padding: 10px 15px !important;
        border: 1px solid #ccc !important;
        border-radius: 6px !important;
        font-size: 14px !important;
        background: white !important;
        cursor: pointer !important;
        width: 100% !important;
    }

    /* ===== GRID DE TARJETAS (VISTA MÓVIL) ===== */
    .incidencias-grid {
        display: grid !important;
        grid-template-columns: 1fr !important;
        gap: 12px !important;
        margin-top: 20px !important;
    }

    .incidencia-card {
        background: #fafafa !important;
        border: 1px solid #e0e0e0 !important;
        border-radius: 10px !important;
        padding: 15px !important;
        position: relative !important;
        transition: all 0.2s ease !important;
    }

    .incidencia-card:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
        border-color: #0d6efd !important;
    }

    .incidencia-card::before {
        content: '' !important;
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        bottom: 0 !important;
        width: 4px !important;
        background: #0d6efd !important;
        border-radius: 10px 0 0 10px !important;
    }

    .incidencia-header {
        display: flex !important;
        justify-content: space-between !important;
        align-items: flex-start !important;
        margin-bottom: 12px !important;
        flex-wrap: wrap !important;
        gap: 8px !important;
    }

    .incidencia-id {
        font-size: 18px !important;
        font-weight: 600 !important;
        color: #333 !important;
    }

    .incidencia-fecha {
        font-size: 12px !important;
        color: #888 !important;
        background: white !important;
        padding: 4px 10px !important;
        border-radius: 15px !important;
        border: 1px solid #e0e0e0 !important;
    }

    .incidencia-body {
        margin-bottom: 12px !important;
    }

    .info-row {
        display: flex !important;
        justify-content: space-between !important;
        padding: 6px 0 !important;
        border-bottom: 1px solid #eee !important;
        flex-wrap: wrap !important;
        gap: 4px !important;
        font-size: 13px !important;
    }

    .info-row:last-child {
        border-bottom: none !important;
    }

    .info-label {
        font-weight: 600 !important;
        color: #555 !important;
    }

    .info-value {
        color: #333 !important;
        text-align: right !important;
        word-break: break-word !important;
        max-width: 60% !important;
    }

    .info-value.descripcion {
        text-align: left !important;
        max-width: 100% !important;
        width: 100% !important;
        margin-top: 4px !important;
        padding: 8px !important;
        background: white !important;
        border-radius: 4px !important;
        border: 1px solid #e9ecef !important;
    }

    .incidencia-footer {
        margin-top: 12px !important;
        padding-top: 12px !important;
        border-top: 1px solid #e0e0e0 !important;
    }

    /* ===== ESTADOS SIMPLIFICADOS ===== */
    .estado-badge {
        display: inline-block !important;
        padding: 4px 10px !important;
        border-radius: 12px !important;
        font-size: 11px !important;
        font-weight: 600 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.3px !important;
    }

    .estado-finalizada {
        background: #d1e7dd !important;
        color: #0f5132 !important;
        border: 1px solid #badbcc !important;
    }

    .estado-pendiente {
        background: #fff3cd !important;
        color: #664d03 !important;
        border: 1px solid #ffecb5 !important;
    }

    .estado-espera {
        background: #cfe2ff !important;
        color: #084298 !important;
        border: 1px solid #b6d4fe !important;
    }

    .estado-rechazada {
        background: #f8d7da !important;
        color: #842029 !important;
        border: 1px solid #f5c2c7 !important;
    }

    /* ===== TABLA (VISTA ESCRITORIO) ===== */
    .table-container {
        display: none !important;
        width: 100% !important;
        overflow-x: auto !important;
        margin-top: 20px !important;
        border: 2px solid #dee2e6 !important;
        border-radius: 12px !important;
        background: white !important;
    }

    table {
        width: 100% !important;
        border-collapse: collapse !important;
        min-width: 1000px !important;
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
        background-color: #cfe2ff !important;
        font-weight: bold !important;
        color: #333 !important;
        position: sticky !important;
        top: 0 !important;
        white-space: nowrap !important;
    }

    td {
        white-space: nowrap !important;
    }

    /* Columna descripción más ancha */
    td:nth-child(5) {
        max-width: 250px !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        white-space: nowrap !important;
    }
    
    tbody tr:nth-child(even) {
        background-color: #f8f9fa !important;
    }

    tbody tr:hover {
        background-color: #e9ecef !important;
    }

    /* ===== PAGINACIÓN ===== */
    .paginacion-container {
        margin-top: 25px !important;
        padding-top: 20px !important;
        border-top: 1px solid #e0e0e0 !important;
        text-align: center !important;
    }

    .paginacion-flex {
        display: flex !important;
        justify-content: center !important;
        align-items: center !important;
        flex-wrap: wrap !important;
        gap: 10px !important;
    }

    .paginacion-info {
        color: #666 !important;
        font-size: 14px !important;
        margin: 0 10px !important;
    }

    .btn-paginacion {
        padding: 8px 16px !important;
        background: #f8f9fa !important;
        color: #495057 !important;
        text-decoration: none !important;
        border-radius: 6px !important;
        font-weight: 500 !important;
        font-size: 13px !important;
        border: 1px solid #dee2e6 !important;
        cursor: pointer !important;
        transition: all 0.2s ease !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 5px !important;
    }

    .btn-paginacion:hover {
        background: #e9ecef !important;
        border-color: #adb5bd !important;
    }

    .btn-paginacion.disabled {
        background: #f8f9fa !important;
        color: #adb5bd !important;
        pointer-events: none !important;
        border-color: #e9ecef !important;
    }

    /* ===== MENSAJE SIN DATOS ===== */
    .mensaje-sin-datos {
        text-align: center !important;
        padding: 40px 20px !important;
        color: #666 !important;
        background: #f8f9fa !important;
        border-radius: 10px !important;
        border: 1px dashed #dee2e6 !important;
        margin: 20px 0 !important;
    }

    .mensaje-sin-datos h3 {
        margin-bottom: 10px !important;
        color: #495057 !important;
    }

    /* ===== MEDIA QUERIES ===== */
    @media (min-width: 768px) {
        body {
            padding: 20px !important;
        }

        .incidencias-grid {
            display: none !important;
        }

        .table-container {
            display: block !important;
        }

        .filtro-group {
            flex-direction: row !important;
            align-items: center !important;
        }

        .filtro-group select {
            width: auto !important;
            min-width: 250px !important;
        }

        .btn {
            width: auto !important;
        }
    }

    @media (min-width: 1024px) {
        .incidencias-wrapper {
            max-width: 1800px !important;
        }

        .content-card {
            padding: 30px !important;
        }
    }

    @media (max-width: 480px) {
        body {
            padding: 10px !important;
        }

        .title-card {
            padding: 15px !important;
        }

        .content-card {
            padding: 15px !important;
        }

        .incidencia-card {
            padding: 12px !important;
        }

        .incidencia-header {
            flex-direction: column !important;
            align-items: flex-start !important;
        }

        .info-row {
            flex-direction: column !important;
            align-items: flex-start !important;
        }

        .info-value {
            max-width: 100% !important;
            text-align: left !important;
        }

        .paginacion-flex {
            flex-direction: column !important;
            width: 100% !important;
        }

        .btn-paginacion {
            width: 100% !important;
            justify-content: center !important;
        }

        .btn {
            width: 100% !important;
            justify-content: center !important;
        }
    }
</style>
</head>
<body>
    <div class="incidencias-wrapper">
        <!-- Título -->
        <div class="title-card">
            <h1>
                <img src="../assets/resources/lincidencia.png" alt="Incidencias">
                LISTA DE INCIDENCIAS
            </h1>
            <p>Gestión de incidencias del sistema</p>
            
            <div style="margin-top: 15px;">
                <a href="formcrearincidencia.php" class="btn btn-success">
                    <img src="../assets/resources/maso.png" alt="Agregar" class="btn-icon" style="filter: brightness(0) invert(0.8) !important;">
                    Nueva Incidencia
                </a>
            </div>
        </div>

        <!-- Contenido -->
        <div class="content-card">
            <!-- Filtros -->
            <div class="filtros-container">
                <form method="get" class="filtro-group">
                    <label for="estado">Filtrar por estado:</label>
                    <select name="estado" id="estado" onchange="this.form.submit()">
                        <option value="pendiente_espera" <?= $filtro === 'pendiente_espera' ? 'selected' : '' ?>>Pendiente + En espera</option>
                        <option value="En espera" <?= $filtro === 'En espera' ? 'selected' : '' ?>>En espera</option>
                        <option value="Pendiente" <?= $filtro === 'Pendiente' ? 'selected' : '' ?>>Pendiente</option>
                        <option value="Finalizada" <?= $filtro === 'Finalizada' ? 'selected' : '' ?>>Finalizada</option>
                        <option value="Rechazada" <?= $filtro === 'Rechazada' ? 'selected' : '' ?>>Rechazada</option>
                        <option value="todos" <?= $filtro === 'todos' ? 'selected' : '' ?>>Todos</option>
                    </select>
                </form>
            </div>

            <?php if (empty($incidencias)): ?>
                <div class="mensaje-sin-datos">
                    <h3>No hay incidencias para mostrar</h3>
                    <p>Intenta ajustar los filtros o crea nuevas incidencias.</p>
                </div>
            <?php else: ?>
                <!-- Vista Grid para Móvil -->
                <div class="incidencias-grid">
                    <?php foreach ($incidencias as $i): ?>
                    <div class="incidencia-card">
                        <div class="incidencia-header">
                            <div>
                                <div class="incidencia-id">#<?= $i->id_incidencia ?></div>
                                <div style="margin-top: 4px;">
                                    <?php 
                                    $estadoClass = '';
                                    switch($i->estado) {
                                        case 'Finalizada': $estadoClass = 'estado-finalizada'; break;
                                        case 'Pendiente': $estadoClass = 'estado-pendiente'; break;
                                        case 'En espera': $estadoClass = 'estado-espera'; break;
                                        case 'Rechazada': $estadoClass = 'estado-rechazada'; break;
                                    }
                                    ?>
                                    <span class="estado-badge <?= $estadoClass ?>"><?= $i->estado ?></span>
                                </div>
                            </div>
                            <div class="incidencia-fecha"><?= date('d/m/Y', strtotime($i->fecha)) ?></div>
                        </div>
                        
                        <div class="incidencia-body">
                            <div class="info-row">
                                <span class="info-label">Emisor:</span>
                                <span class="info-value"><?= htmlspecialchars($i->depto_emisor) ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Receptor:</span>
                                <span class="info-value"><?= htmlspecialchars($i->depto_receptor) ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Prioridad:</span>
                                <span class="info-value"><?= htmlspecialchars($i->prioridad) ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Asignado a:</span>
                                <span class="info-value">
                                    <?= $i->trabajador_nombre ? htmlspecialchars($i->trabajador_nombre . ' ' . $i->trabajador_apellido) : '-' ?>
                                </span>
                            </div>
                            <div class="info-row" style="flex-direction: column; align-items: flex-start;">
                                <span class="info-label">Descripción:</span>
                                <span class="info-value descripcion">
                                    <?= htmlspecialchars(substr($i->descripcion, 0, 100)) ?><?= strlen($i->descripcion) > 100 ? '...' : '' ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="incidencia-footer">
                            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                <a href="monitorearincidencia.php?id=<?= $i->id_incidencia ?>" class="btn btn-primary" style="flex: 1; min-width: 80px;">
                                    <img src="../assets/resources/ojo.png" alt="Monitorear" class="btn-icon" style="filter: brightness(0) invert(1) !important;">
                                    Ver
                                </a>
                                
                                <a href="seguimientofases.php?id=<?= $i->id_incidencia ?>" class="btn btn-warning" style="flex: 1; min-width: 80px;">
                                    <img src="../assets/resources/vital2.png" alt="Seguimiento" class="btn-icon">
                                    Seguimiento
                                </a>

                                <a href="imprimirincidencia.php?id=<?= $i->id_incidencia ?>" target="_blank" class="btn btn-secondary" style="flex: 1; min-width: 80px;">
                                     <img src="../assets/resources/documento.png" alt="PDF" class="btn-icon" style="filter: brightness(0) invert(1) !important;">
                                    PDF
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Vista Tabla para Escritorio -->
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Fecha</th>
                                <th>Emisor</th>
                                <th>Receptor</th>
                                <th>Descripción</th>
                                <th>Prioridad</th>
                                <th>Estado</th>
                                <th>Asignado a</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($incidencias as $i): ?>
                            <tr>
                                <td><strong>#<?= $i->id_incidencia ?></strong></td>
                                <td><?= date('d/m/Y', strtotime($i->fecha)) ?></td>
                                <td><?= htmlspecialchars($i->depto_emisor) ?></td>
                                <td><?= htmlspecialchars($i->depto_receptor) ?></td>
                                <td title="<?= htmlspecialchars($i->descripcion) ?>">
                                    <?= htmlspecialchars(substr($i->descripcion, 0, 50)) ?><?= strlen($i->descripcion) > 50 ? '...' : '' ?>
                                </td>
                                <td><?= htmlspecialchars($i->prioridad) ?></td>
                                <td>
                                    <?php 
                                    $estadoClass = '';
                                    switch($i->estado) {
                                        case 'Finalizada': $estadoClass = 'estado-finalizada'; break;
                                        case 'Pendiente': $estadoClass = 'estado-pendiente'; break;
                                        case 'En espera': $estadoClass = 'estado-espera'; break;
                                        case 'Rechazada': $estadoClass = 'estado-rechazada'; break;
                                    }
                                    ?>
                                    <span class="estado-badge <?= $estadoClass ?>"><?= $i->estado ?></span>
                                </td>
                                <td>
                                    <?= $i->trabajador_nombre ? htmlspecialchars($i->trabajador_nombre . ' ' . $i->trabajador_apellido) : '-' ?>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                                        <a href="monitorearincidencia.php?id=<?= $i->id_incidencia ?>" class="btn btn-primary btn-xs">
                                            <img src="../assets/resources/ojo.png" alt="Monitorear" class="btn-icon" style="filter: brightness(0) invert(1) !important;">
                                            Ver
                                        </a>
                                        
                                        <a href="seguimientofases.php?id=<?= $i->id_incidencia ?>" class="btn btn-warning btn-xs">
                                            <img src="../assets/resources/vital2.png" alt="Seguimiento" class="btn-icon">
                                            Seguimiento
                                        </a>

                                        <a href="imprimirincidencia.php?id=<?= $i->id_incidencia ?>" target="_blank" class="btn btn-secondary btn-xs">
                                            <img src="../assets/resources/documento.png" alt="PDF" class="btn-icon" style="filter: brightness(0) invert(1) !important;">
                                            PDF
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <!-- Paginación -->
            <?php if ($totalPaginas > 1): ?>
            <div class="paginacion-container">
                <div class="paginacion-flex">
                    <?php if ($pagina > 1): ?>
                        <a href="?pagina=<?= $pagina - 1 ?>&estado=<?= urlencode($filtro) ?>" class="btn-paginacion">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="15 18 9 12 15 6"></polyline>
                            </svg>
                            Anterior
                        </a>
                    <?php else: ?>
                        <span class="btn-paginacion disabled">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="15 18 9 12 15 6"></polyline>
                            </svg>
                            Anterior
                        </span>
                    <?php endif; ?>
                    
                    <span class="paginacion-info">
                        Página <?= $pagina ?> de <?= max(1, $totalPaginas) ?>
                    </span>
                    
                    <?php if ($pagina < $totalPaginas): ?>
                        <a href="?pagina=<?= $pagina + 1 ?>&estado=<?= urlencode($filtro) ?>" class="btn-paginacion">
                            Siguiente
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </a>
                    <?php else: ?>
                        <span class="btn-paginacion disabled">
                            Siguiente
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </span>
                    <?php endif; ?>
                </div>
                <div class="paginacion-info" style="margin-top: 10px; font-size: 12px; color: #888;">
                    Mostrando <?= (($pagina - 1) * $registrosPorPagina) + 1 ?> - <?= min($pagina * $registrosPorPagina, $total) ?> de <?= $total ?> incidencias
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>