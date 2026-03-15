<?php
session_start();
include_once "../encabezado.php";
require_once "../conex.php";
require_once "../user/gestorsesion.php";
require_once "salidaalmacen.php";

GestorSesiones::iniciar();

$usuario_actual = GestorSesiones::get('nombre_completo'); 
$nivel_actual = GestorSesiones::get('nivel'); 

$registrosPorPagina = 10;
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
if ($pagina < 1) $pagina = 1;
$inicio = ($pagina - 1) * $registrosPorPagina;

$filtro = isset($_GET['estado']) ? $_GET['estado'] : 'todos';

$whereSQL = "";
$params = [];
if ($filtro === "pendiente_espera") {
    $whereSQL = "WHERE estado IN ('Pendiente','En espera')";
} elseif ($filtro !== "todos") {
    $whereSQL = "WHERE estado = ?";
    $params[] = $filtro;
}

$totalRegistrossentencia = $conexion->prepare("SELECT COUNT(*) FROM solicitud_salida_almacen $whereSQL");
$totalRegistrossentencia->execute($params);
$totalRegistros = $totalRegistrossentencia->fetchColumn();
$totalPaginas = ceil($totalRegistros / $registrosPorPagina);

$sql = "SELECT s.*, d1.nombre AS nombre_departamento_emisor, d2.nombre AS nombre_departamento_destino
        FROM solicitud_salida_almacen s
        INNER JOIN departamentos d1 ON s.departamento_emisor = d1.id_departamento
        INNER JOIN departamentos d2 ON s.departamento_destino = d2.id_departamento
        $whereSQL
        ORDER BY s.id_solicitud DESC
        LIMIT ?, ?";
$sentencia = $conexion->prepare($sql);
$i = 1;
foreach ($params as $p) {
    $sentencia->bindValue($i++, $p);
}
$sentencia->bindValue($i++, $inicio, PDO::PARAM_INT);
$sentencia->bindValue($i++, $registrosPorPagina, PDO::PARAM_INT);
$sentencia->execute();
$solicitudes = $sentencia->fetchAll(PDO::FETCH_OBJ);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
<title>Solicitudes de Compra de Almacén</title>
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

    .solicitudes-wrapper {
        max-width: 1600px !important;
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

    .btn-success {
        background: #198754 !important;
    }
    .btn-success:hover {
        background: #157347 !important;
    }

    .btn-primary {
        background: #0d6efd !important;
    }
    .btn-primary:hover {
        background: #0b5ed7 !important;
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

    .btn-icon {
        width: 16px !important;
        height: 16px !important;
        flex-shrink: 0 !important;
    }

    .content-card {
        background: white !important;
        border-radius: 12px !important;
        padding: 20px !important;
        border: 1px solid #ddd !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
    }

    .mensaje-exito {
        background: #d1e7dd !important;
        color: #0f5132 !important;
        padding: 12px !important;
        border-radius: 8px !important;
        margin-bottom: 15px !important;
        text-align: center !important;
        font-size: 14px !important;
        border: 1px solid #badbcc !important;
    }

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

    .solicitudes-grid {
        display: grid !important;
        grid-template-columns: 1fr !important;
        gap: 12px !important;
        margin-top: 20px !important;
    }

    .solicitud-card {
        background: #fafafa !important;
        border: 1px solid #e0e0e0 !important;
        border-radius: 10px !important;
        padding: 15px !important;
        position: relative !important;
        transition: all 0.2s ease !important;
    }

    .solicitud-card:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
        border-color: #0d6efd !important;
    }

    .solicitud-card::before {
        content: '' !important;
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        bottom: 0 !important;
        width: 4px !important;
        background: #0d6efd !important;
        border-radius: 10px 0 0 10px !important;
    }

    .solicitud-header {
        display: flex !important;
        justify-content: space-between !important;
        align-items: flex-start !important;
        margin-bottom: 12px !important;
        flex-wrap: wrap !important;
        gap: 8px !important;
    }

    .solicitud-id {
        font-size: 18px !important;
        font-weight: 600 !important;
        color: #333 !important;
    }

    .solicitud-fecha {
        font-size: 12px !important;
        color: #888 !important;
        background: white !important;
        padding: 4px 10px !important;
        border-radius: 15px !important;
        border: 1px solid #e0e0e0 !important;
    }

    .solicitud-body {
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

    .solicitud-footer {
        margin-top: 12px !important;
        padding-top: 12px !important;
        border-top: 1px solid #e0e0e0 !important;
    }

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

    td:nth-child(7) {
        max-width: 250px !important;
        min-width: 150px !important;
        word-wrap: break-word !important;
        overflow-wrap: break-word !important;
        text-align: left !important;
        white-space: normal !important;
    }

    tbody tr:nth-child(even) {
        background-color: #f8f9fa !important;
    }

    tbody tr:hover {
        background-color: #e9ecef !important;
    }

    .acciones-cell {
        display: flex !important;
        flex-wrap: wrap !important;
        gap: 6px !important;
        align-items: center !important;
    }

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

    @media (min-width: 768px) {
        body {
            padding: 20px !important;
        }

        .solicitudes-grid {
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
        .solicitudes-wrapper {
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

        .solicitud-card {
            padding: 12px !important;
        }

        .solicitud-header {
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
    <div class="solicitudes-wrapper">
        <div class="title-card">
            <h1>
                <img src="../assets/resources/inventario.png" alt="Solicitudes">
                SOLICITUDES DE COMPRA PARA ALMACÉN
            </h1>
            <p>Seguimiento de solicitudes realizadas desde almacén</p>
            
            <div style="margin-top: 15px;">
                <a href="formsolicitudsalida.php" class="btn btn-success">
                    <img src="../assets/resources/carrito1.png" alt="Agregar" class="btn-icon" style="filter: brightness(0) invert(1) !important;">
                    Solicitar Compra
                </a>
            </div>
        </div>

        <div class="content-card">
            <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] === 'recepcion_exitosa'): ?>
                <div class="mensaje-exito">
                    Recepción registrada correctamente.
                </div>
            <?php endif; ?>
            
            <div class="filtros-container">
                <form method="get" class="filtro-group">
                    <label for="estado">Filtrar por estado:</label>
                    <select name="estado" id="estado" onchange="this.form.submit()">
                        <option value="pendiente_espera" <?= $filtro === 'pendiente_espera' ? 'selected' : '' ?>>Pendiente + En espera</option>
                        <option value="Pendiente" <?= $filtro === 'Pendiente' ? 'selected' : '' ?>>Pendiente</option>
                        <option value="En espera" <?= $filtro === 'En espera' ? 'selected' : '' ?>>En espera</option>
                        <option value="Finalizada" <?= $filtro === 'Finalizada' ? 'selected' : '' ?>>Finalizada</option>
                        <option value="todos" <?= $filtro === 'todos' ? 'selected' : '' ?>>Todos</option>
                    </select>
                </form>
            </div>

            <?php if (empty($solicitudes)): ?>
                <div class="mensaje-sin-datos">
                    <h3>No hay solicitudes para mostrar</h3>
                    <p>Intenta ajustar los filtros o crea nuevas solicitudes.</p>
                </div>
            <?php else: ?>
                <div class="solicitudes-grid">
                    <?php foreach ($solicitudes as $s): 
                        $estadoClass = '';
                        switch($s->estado) {
                            case 'Finalizada': $estadoClass = 'estado-finalizada'; break;
                            case 'Pendiente': $estadoClass = 'estado-pendiente'; break;
                            case 'En espera': $estadoClass = 'estado-espera'; break;
                        }
                    ?>
                    <div class="solicitud-card">
                        <div class="solicitud-header">
                            <div>
                                <div class="solicitud-id">#<?= $s->id_solicitud ?></div>
                                <div style="margin-top: 4px;">
                                    <span class="estado-badge <?= $estadoClass ?>"><?= $s->estado ?></span>
                                </div>
                            </div>
                            <div class="solicitud-fecha"><?= date('d/m/Y', strtotime($s->fecha)) ?></div>
                        </div>
                        
                        <div class="solicitud-body">
                            <div class="info-row">
                                <span class="info-label">Emisor:</span>
                                <span class="info-value"><?= htmlspecialchars($s->emisor) ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Dept. Emisor:</span>
                                <span class="info-value"><?= htmlspecialchars($s->nombre_departamento_emisor) ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Receptor:</span>
                                <span class="info-value"><?= htmlspecialchars($s->receptor) ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Dept. Destino:</span>
                                <span class="info-value"><?= htmlspecialchars($s->nombre_departamento_destino) ?></span>
                            </div>
                            <div class="info-row" style="flex-direction: column; align-items: flex-start;">
                                <span class="info-label">Descripción:</span>
                                <span class="info-value descripcion"><?= htmlspecialchars($s->descripcion) ?></span>
                            </div>
                        </div>
                        
                        <div class="solicitud-footer">
                            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                <a href="imprimirsalida.php?id=<?= $s->id_solicitud ?>" target="_blank" class="btn btn-secondary" style="flex: 1; min-width: 80px;">
                                    <img src="../assets/resources/documento.png" alt="PDF" class="btn-icon" style="filter: brightness(0) invert(1) !important;">
                                    PDF
                                </a>
                                
                                <?php if ($s->estado !== "Finalizada" && ($nivel_actual === "almacenista" || $nivel_actual === "superadministrador")): ?>
                                    <a href="formrecepcionalmacen.php?id_solicitud=<?= $s->id_solicitud ?>" class="btn btn-primary" style="flex: 1; min-width: 80px;">
                                        <img src="../assets/resources/campana.png" alt="Recepción" class="btn-icon" style="filter: brightness(0) invert(1) !important;">
                                        Recepción
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Fecha</th>
                                <th>Emisor</th>
                                <th>Dept. Emisor</th>
                                <th>Receptor</th>
                                <th>Dept. Destino</th>
                                <th>Descripción</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($solicitudes as $s): 
                                $estadoClass = '';
                                switch($s->estado) {
                                    case 'Finalizada': $estadoClass = 'estado-finalizada'; break;
                                    case 'Pendiente': $estadoClass = 'estado-pendiente'; break;
                                    case 'En espera': $estadoClass = 'estado-espera'; break;
                                }
                            ?>
                            <tr>
                                <td><strong>#<?= $s->id_solicitud ?></strong></td>
                                <td><?= date('d/m/Y', strtotime($s->fecha)) ?></td>
                                <td><?= htmlspecialchars($s->emisor) ?></td>
                                <td><?= htmlspecialchars($s->nombre_departamento_emisor) ?></td>
                                <td><?= htmlspecialchars($s->receptor) ?></td>
                                <td><?= htmlspecialchars($s->nombre_departamento_destino) ?></td>
                                <td><?= htmlspecialchars($s->descripcion) ?></td>
                                <td><span class="estado-badge <?= $estadoClass ?>"><?= $s->estado ?></span></td>
                                <td>
                                    <div class="acciones-cell">
                                        <a href="imprimirsalida.php?id=<?= $s->id_solicitud ?>" target="_blank" class="btn btn-secondary btn-sm">
                                            <img src="../assets/resources/documento.png" alt="PDF" class="btn-icon" style="filter: brightness(0) invert(1) !important;">
                                            PDF
                                        </a>
                                        
                                        <?php if ($s->estado !== "Finalizada" && ($nivel_actual === "almacenista" || $nivel_actual === "superadministrador")): ?>
                                            <a href="formrecepcionalmacen.php?id_solicitud=<?= $s->id_solicitud ?>" class="btn btn-primary btn-sm">
                                                <img src="../assets/resources/campana.png" alt="Recepción" class="btn-icon" style="filter: brightness(0) invert(1) !important;">
                                                Recepción
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

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
                    Mostrando <?= (($pagina - 1) * $registrosPorPagina) + 1 ?> - <?= min($pagina * $registrosPorPagina, $totalRegistros) ?> de <?= $totalRegistros ?> solicitudes
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>