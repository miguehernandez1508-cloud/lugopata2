<?php
session_start();
include_once "../encabezado.php";
require_once "insumo.php";
require_once "../conex.php";
require_once "../user/gestorsesion.php";

GestorSesiones::iniciar();

// Parámetros de paginación
$registrosPorPagina = 10;
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
if ($pagina < 1) $pagina = 1;
$inicio = ($pagina - 1) * $registrosPorPagina;

// Filtros
$filtro_unidad = isset($_GET['unidad']) ? trim($_GET['unidad']) : 'todos';
$filtro_stock = isset($_GET['stock']) ? trim($_GET['stock']) : 'todos';

$insumoObj = new Insumo($conexion);

// Obtener unidades únicas para el filtro
$unidades = $conexion->query("SELECT DISTINCT unidad_medida FROM insumos WHERE unidad_medida IS NOT NULL AND unidad_medida != '' ORDER BY unidad_medida")->fetchAll(PDO::FETCH_COLUMN);

// Construir consulta con filtros
$sql = "SELECT * FROM insumos";
$where = [];
$params = [];
$types = [];

if ($filtro_unidad !== 'todos') {
    $where[] = "unidad_medida = ?";
    $params[] = $filtro_unidad;
    $types[] = PDO::PARAM_STR;
}

if ($filtro_stock !== 'todos') {
    if ($filtro_stock === 'bajo') {
        $where[] = "cantidad <= 10";
    } elseif ($filtro_stock === 'medio') {
        $where[] = "cantidad > 10 AND cantidad <= 50";
    } elseif ($filtro_stock === 'alto') {
        $where[] = "cantidad > 50";
    }
}

if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY id_insumo DESC";

// Contar total con filtros
$sql_count = "SELECT COUNT(*) as total FROM insumos" . 
             (!empty($where) ? " WHERE " . implode(" AND ", $where) : "");
$stmt_count = $conexion->prepare($sql_count);
$stmt_count->execute($params);
$total = $stmt_count->fetch(PDO::FETCH_OBJ)->total;

// Obtener datos paginados
$sql .= " LIMIT ? OFFSET ?";
$params[] = $registrosPorPagina;
$params[] = $inicio;
$types[] = PDO::PARAM_INT;
$types[] = PDO::PARAM_INT;

$sentencia = $conexion->prepare($sql);

foreach ($params as $i => $param) {
    $type = isset($types[$i]) ? $types[$i] : PDO::PARAM_STR;
    $sentencia->bindValue($i + 1, $param, $type);
}

$sentencia->execute();
$insumos = $sentencia->fetchAll(PDO::FETCH_OBJ);

$totalPaginas = ceil($total / $registrosPorPagina);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
<title>Lista de Insumos</title>
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

    .insumos-wrapper {
        max-width: 1400px !important;
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

    .filtros-container {
        margin-bottom: 20px !important;
        padding-bottom: 20px !important;
        border-bottom: 1px solid #e0e0e0 !important;
    }

    .filtros-form {
        display: flex !important;
        flex-wrap: wrap !important;
        gap: 20px !important;
        justify-content: center !important;
    }

    .filtro-group {
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
    }

    .filtro-group label {
        font-weight: 600 !important;
        color: #333 !important;
        font-size: 14px !important;
    }

    .filtro-group select {
        padding: 8px 12px !important;
        border: 1px solid #ccc !important;
        border-radius: 6px !important;
        font-size: 14px !important;
        background: white !important;
        cursor: pointer !important;
        min-width: 180px !important;
    }

    .filtro-group select:focus {
        outline: none !important;
        border-color: #0d6efd !important;
    }

    .insumos-grid {
        display: grid !important;
        grid-template-columns: 1fr !important;
        gap: 12px !important;
        margin-top: 20px !important;
    }

    .insumo-card {
        background: #fafafa !important;
        border: 1px solid #e0e0e0 !important;
        border-radius: 10px !important;
        padding: 15px !important;
        position: relative !important;
        transition: all 0.2s ease !important;
    }

    .insumo-card:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
        border-color: #0d6efd !important;
    }

    .insumo-card::before {
        content: '' !important;
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        bottom: 0 !important;
        width: 4px !important;
        background: #0d6efd !important;
        border-radius: 10px 0 0 10px !important;
    }

    .insumo-header {
        display: flex !important;
        justify-content: space-between !important;
        align-items: flex-start !important;
        margin-bottom: 12px !important;
        flex-wrap: wrap !important;
        gap: 8px !important;
    }

    .insumo-nombre {
        font-size: 18px !important;
        font-weight: 600 !important;
        color: #333 !important;
    }

    .insumo-id {
        font-size: 12px !important;
        color: #888 !important;
        background: white !important;
        padding: 4px 10px !important;
        border-radius: 15px !important;
        border: 1px solid #e0e0e0 !important;
        font-family: monospace !important;
    }

    .insumo-body {
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

    .stock-badge {
        display: inline-block !important;
        padding: 4px 10px !important;
        border-radius: 12px !important;
        font-size: 12px !important;
        font-weight: 600 !important;
    }

    .stock-bajo {
        background: #f8d7da !important;
        color: #842029 !important;
        border: 1px solid #f5c2c7 !important;
    }

    .stock-medio {
        background: #fff3cd !important;
        color: #664d03 !important;
        border: 1px solid #ffecb5 !important;
    }

    .stock-alto {
        background: #d1e7dd !important;
        color: #0f5132 !important;
        border: 1px solid #badbcc !important;
    }

    .insumo-imagen {
        width: 60px !important;
        height: 60px !important;
        object-fit: contain !important;
        border-radius: 6px !important;
        border: 1px solid #e0e0e0 !important;
        background: white !important;
    }

    .insumo-footer {
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

    td:nth-child(4) {
        max-width: 200px !important;
        min-width: 120px !important;
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

    th:last-child,
    td:last-child{
      width:1%;
      white-space:nowrap;
   }

     td:last-child div{
        justify-content:flex-start;
    }

    .img-small {
        width: 50px !important;
        height: 50px !important;
        object-fit: contain !important;
        border-radius: 6px !important;
        border: 1px solid #e0e0e0 !important;
        background: white !important;
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

        .insumos-grid {
            display: none !important;
        }

        .table-container {
            display: block !important;
        }

        .filtros-form {
            justify-content: flex-start !important;
        }

        .btn {
            width: auto !important;
        }
    }

    @media (min-width: 1024px) {
        .insumos-wrapper {
            max-width: 1600px !important;
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

        .insumo-card {
            padding: 12px !important;
        }

        .insumo-header {
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

        .filtros-form {
            flex-direction: column !important;
            align-items: stretch !important;
        }

        .filtro-group {
            flex-direction: column !important;
            align-items: flex-start !important;
        }

        .filtro-group select {
            width: 100% !important;
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
    <div class="insumos-wrapper">
        <div class="title-card">
            <h1>
                <img src="../assets/resources/insumos.png" alt="Insumos">
                LISTA DE INSUMOS
            </h1>
            <p>Gestión de insumos de mantenimiento</p>
            
            <div style="margin-top: 15px;">
                <a href="formcrearinsumo.php" class="btn btn-success">
                     <img src="../assets/resources/maso.png" alt="Agregar" class="btn-icon" style="filter: brightness(0) invert(0.8) !important;">
                    Agregar Insumo
                </a>
            </div>
        </div>

        <div class="content-card">
            <div class="filtros-container">
                <form method="get" class="filtros-form">
                    <div class="filtro-group">
                        <label for="unidad">Unidad:</label>
                        <select name="unidad" id="unidad" onchange="this.form.submit()">
                            <option value="todos" <?= $filtro_unidad === 'todos' ? 'selected' : '' ?>>Todas las unidades</option>
                            <?php foreach ($unidades as $unidad): ?>
                                <option value="<?= $unidad ?>" <?= $filtro_unidad === $unidad ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($unidad) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filtro-group">
                        <label for="stock">Nivel de Stock:</label>
                        <select name="stock" id="stock" onchange="this.form.submit()">
                            <option value="todos" <?= $filtro_stock === 'todos' ? 'selected' : '' ?>>Todos</option>
                            <option value="bajo" <?= $filtro_stock === 'bajo' ? 'selected' : '' ?>>Stock Bajo (≤10)</option>
                            <option value="medio" <?= $filtro_stock === 'medio' ? 'selected' : '' ?>>Stock Medio (11-50)</option>
                            <option value="alto" <?= $filtro_stock === 'alto' ? 'selected' : '' ?>>Stock Alto (>50)</option>
                        </select>
                    </div>
                </form>
            </div>

            <?php if (empty($insumos)): ?>
                <div class="mensaje-sin-datos">
                    <h3>No hay insumos registrados</h3>
                    <p>Intenta ajustar los filtros o agrega nuevos insumos.</p>
                </div>
            <?php else: ?>
                <div class="insumos-grid">
                    <?php foreach ($insumos as $i): 
                        $clase_stock = 'stock-alto';
                        if ($i->cantidad <= 10) {
                            $clase_stock = 'stock-bajo';
                        } elseif ($i->cantidad <= 50) {
                            $clase_stock = 'stock-medio';
                        }
                    ?>
                    <div class="insumo-card">
                        <div class="insumo-header">
                            <div>
                                <div class="insumo-nombre"><?= htmlspecialchars($i->nombre) ?></div>
                                <div style="margin-top: 4px;">
                                    <span class="stock-badge <?= $clase_stock ?>">Stock: <?= $i->cantidad ?></span>
                                </div>
                            </div>
                            <div class="insumo-id">#<?= $i->id_insumo ?></div>
                        </div>
                        
                        <div class="insumo-body">
                            <div class="info-row">
                                <span class="info-label">Unidad:</span>
                                <span class="info-value"><?= htmlspecialchars($i->unidad_medida) ?></span>
                            </div>
                            <div class="info-row" style="flex-direction: column; align-items: flex-start;">
                                <span class="info-label">Descripción:</span>
                                <span class="info-value descripcion"><?= htmlspecialchars($i->descripcion) ?></span>
                            </div>
                            <div class="info-row" style="justify-content: center; padding-top: 10px;">
                                <?php if($i->imagen): ?>
                                    <img src="../assets/imagenes/insumos/<?= $i->imagen ?>" alt="Imagen" class="insumo-imagen">
                                <?php else: ?>
                                    <span style="color: #888; font-style: italic;">Sin imagen</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="insumo-footer">
                            <div style="display: flex; gap: 8px;">
                                <a href="formeditarinsumo.php?id=<?= $i->id_insumo ?>" class="btn btn-warning" style="flex: 1;">
                                    <img src="/lugopata/assets/resources/editarU.png" alt="Editar" class="btn-icon">
                                    Editar
                                </a>
                                <a href="eliminarinsumo.php?id=<?= $i->id_insumo ?>" 
                                   class="btn btn-danger" 
                                   style="flex: 1;"
                                   onclick="return confirm('¿Está seguro de eliminar este insumo?')">
                                    <img src="/lugopata/assets/resources/eliminar2.png" alt="Eliminar" class="btn-icon" style="filter: brightness(0) invert(1) !important;">
                                    Eliminar
                                </a>
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
                                <th>Nombre</th>
                                <th>Descripción</th>
                                <th>Unidad</th>
                                <th>Cantidad</th>
                                <th>Imagen</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($insumos as $i): 
                                $clase_stock = 'stock-alto';
                                if ($i->cantidad <= 10) {
                                    $clase_stock = 'stock-bajo';
                                } elseif ($i->cantidad <= 50) {
                                    $clase_stock = 'stock-medio';
                                }
                            ?>
                            <tr>
                                <td><strong>#<?= $i->id_insumo ?></strong></td>
                                <td><?= htmlspecialchars($i->nombre) ?></td>
                                <td title="<?= htmlspecialchars($i->descripcion) ?>">
                                    <?= htmlspecialchars(substr($i->descripcion, 0, 50)) ?><?= strlen($i->descripcion) > 50 ? '...' : '' ?>
                                </td>
                                <td><?= htmlspecialchars($i->unidad_medida) ?></td>
                                <td><span class="stock-badge <?= $clase_stock ?>"><?= $i->cantidad ?></span></td>
                                <td>
                                    <?php if($i->imagen): ?>
                                        <img src="../assets/imagenes/insumos/<?= $i->imagen ?>" alt="Imagen" class="img-small">
                                    <?php else: ?>
                                        <span style="color: #888; font-style: italic;">Sin imagen</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 8px;">
                                        <a href="formeditarinsumo.php?id=<?= $i->id_insumo ?>" class="btn btn-warning btn-sm">
                                            <img src="/lugopata/assets/resources/editarU.png" alt="Editar" class="btn-icon">
                                            Editar
                                        </a>
                                        <a href="eliminarinsumo.php?id=<?= $i->id_insumo ?>" 
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('¿Está seguro de eliminar este insumo?')">
                                            <img src="/lugopata/assets/resources/eliminar2.png" alt="Eliminar" class="btn-icon" style="filter: brightness(0) invert(1) !important;">
                                            Eliminar
                                        </a>
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
                        <a href="?pagina=<?= $pagina - 1 ?>&unidad=<?= urlencode($filtro_unidad) ?>&stock=<?= urlencode($filtro_stock) ?>" class="btn-paginacion">
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
                        <a href="?pagina=<?= $pagina + 1 ?>&unidad=<?= urlencode($filtro_unidad) ?>&stock=<?= urlencode($filtro_stock) ?>" class="btn-paginacion">
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
                    Mostrando <?= (($pagina - 1) * $registrosPorPagina) + 1 ?> - <?= min($pagina * $registrosPorPagina, $total) ?> de <?= $total ?> insumos
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>