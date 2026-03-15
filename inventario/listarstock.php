<?php
session_start();
include_once "../encabezado.php";
require_once "../conex.php";
require_once "../user/gestorsesion.php";
require_once "../insumos/insumo.php";
require_once "../insumos/categoria.php";    

GestorSesiones::iniciar();

$registrosPorPagina = 10;
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
if ($pagina < 1) $pagina = 1;
$inicio = ($pagina - 1) * $registrosPorPagina;

$totalRegistros = $conexion->query("SELECT COUNT(*) FROM stock_almacen")->fetchColumn();
$totalPaginas = ceil($totalRegistros / $registrosPorPagina);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_id'])) {
    $insumoObj = new Insumo($conexion);
    $id_insumo = $_POST['eliminar_id'];
    if ($insumoObj->eliminar($id_insumo)) {
        echo "<script>alert('Insumo eliminado correctamente.'); window.location.href=window.location.href;</script>";
    } else {
        echo "<script>alert('Error al eliminar el insumo.');</script>";
    }
}

$sql = "SELECT s.id_stock, s.id_insumo, s.cantidad, s.fecha_actualizacion, 
               i.nombre, i.descripcion, i.unidad_medida, i.imagen
        FROM stock_almacen s
        JOIN insumos i ON s.id_insumo = i.id_insumo
        ORDER BY s.id_stock DESC
        LIMIT :inicio, :registros";
$sentencia = $conexion->prepare($sql);
$sentencia->bindValue(':inicio', $inicio, PDO::PARAM_INT);
$sentencia->bindValue(':registros', $registrosPorPagina, PDO::PARAM_INT);
$sentencia->execute();
$stock = $sentencia->fetchAll(PDO::FETCH_OBJ);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
<title>Stock del Almacén</title>
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

    .stock-wrapper {
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

    .stock-grid {
        display: grid !important;
        grid-template-columns: 1fr !important;
        gap: 12px !important;
        margin-top: 20px !important;
    }

    .stock-card {
        background: #fafafa !important;
        border: 1px solid #e0e0e0 !important;
        border-radius: 10px !important;
        padding: 15px !important;
        position: relative !important;
        transition: all 0.2s ease !important;
    }

    .stock-card:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
        border-color: #0d6efd !important;
    }

    .stock-card::before {
        content: '' !important;
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        bottom: 0 !important;
        width: 4px !important;
        background: #0d6efd !important;
        border-radius: 10px 0 0 10px !important;
    }

    .stock-header {
        display: flex !important;
        justify-content: space-between !important;
        align-items: flex-start !important;
        margin-bottom: 12px !important;
        flex-wrap: wrap !important;
        gap: 8px !important;
    }

    .stock-nombre {
        font-size: 18px !important;
        font-weight: 600 !important;
        color: #333 !important;
    }

    .stock-id {
        font-size: 12px !important;
        color: #888 !important;
        background: white !important;
        padding: 4px 10px !important;
        border-radius: 15px !important;
        border: 1px solid #e0e0e0 !important;
        font-family: monospace !important;
    }

    .stock-body {
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

    .stock-imagen {
        width: 80px !important;
        height: 80px !important;
        object-fit: contain !important;
        border-radius: 6px !important;
        border: 1px solid #e0e0e0 !important;
        background: white !important;
    }

    .stock-footer {
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

        .stock-grid {
            display: none !important;
        }

        .table-container {
            display: block !important;
        }

        .btn {
            width: auto !important;
        }
    }

    @media (min-width: 1024px) {
        .stock-wrapper {
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

        .stock-card {
            padding: 12px !important;
        }

        .stock-header {
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
    <div class="stock-wrapper">
        <div class="title-card">
            <h1>
                <img src="../assets/resources/inventario.png" alt="Stock">
                STOCK DEL ALMACÉN
            </h1>
            <p>Monitoreo de existencias de insumos</p>
            
            <div style="margin-top: 15px;">
                <a href="../insumos/formcrearinsumo.php" class="btn btn-success">
                    <img src="../assets/resources/maso.png" alt="Agregar" class="btn-icon" style="filter: brightness(0) invert(0.8) !important;">
                    Agregar Insumo
                </a>
            </div>
        </div>

        <div class="content-card">
            <?php if (empty($stock)): ?>
                <div class="mensaje-sin-datos">
                    <h3>No hay stock registrado</h3>
                    <p>Agrega nuevos insumos para comenzar.</p>
                </div>
            <?php else: ?>
                <div class="stock-grid">
                    <?php foreach ($stock as $s): ?>
                    <div class="stock-card">
                        <div class="stock-header">
                            <div>
                                <div class="stock-nombre"><?= htmlspecialchars($s->nombre) ?></div>
                                <div style="margin-top: 4px; color: #666; font-size: 13px;">
                                    Cantidad: <strong><?= $s->cantidad ?> <?= htmlspecialchars($s->unidad_medida) ?></strong>
                                </div>
                            </div>
                            <div class="stock-id">#<?= $s->id_stock ?></div>
                        </div>
                        
                        <div class="stock-body">
                            <div class="info-row">
                                <span class="info-label">ID Insumo:</span>
                                <span class="info-value">#<?= $s->id_insumo ?></span>
                            </div>
                            <div class="info-row" style="flex-direction: column; align-items: flex-start;">
                                <span class="info-label">Descripción:</span>
                                <span class="info-value descripcion"><?= htmlspecialchars($s->descripcion) ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Última actualización:</span>
                                <span class="info-value"><?= date('d/m/Y H:i', strtotime($s->fecha_actualizacion)) ?></span>
                            </div>
                            <div class="info-row" style="justify-content: center; padding-top: 10px;">
                                <?php if($s->imagen): ?>
                                    <img src="../assets/imagenes/insumos/<?= $s->imagen ?>" alt="Imagen" class="stock-imagen">
                                <?php else: ?>
                                    <span style="color: #888; font-style: italic;">Sin imagen</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="stock-footer">
                            <div style="display: flex; gap: 8px;">
                                <a href="formeditarstock.php?id=<?= $s->id_insumo ?>" class="btn btn-warning" style="flex: 1;">
                                    <img src="/lugopata/assets/resources/editarU.png" alt="Actualizar" class="btn-icon">
                                    Actualizar
                                </a>
                                <form method="post" style="flex: 1; margin: 0;" onsubmit="return confirm('ADVERTENCIA: Esta acción es irreversible.\n¿Está seguro que desea eliminar este stock permanentemente?')">
                                    <input type="hidden" name="eliminar_id" value="<?= $s->id_insumo ?>">
                                    <button type="submit" class="btn btn-danger" style="width: 100%;">
                                        <img src="/lugopata/assets/resources/eliminar2.png" alt="Eliminar" class="btn-icon" style="filter: brightness(0) invert(1) !important;">
                                        Eliminar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID Stock</th>
                                <th>ID Insumo</th>
                                <th>Nombre</th>
                                <th>Descripción</th>
                                <th>Unidad</th>
                                <th>Cantidad</th>
                                <th>Última actualización</th>
                                <th>Imagen</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stock as $s): ?>
                            <tr>
                                <td><strong>#<?= $s->id_stock ?></strong></td>
                                <td>#<?= $s->id_insumo ?></td>
                                <td><?= htmlspecialchars($s->nombre) ?></td>
                                <td><?= htmlspecialchars($s->descripcion) ?></td>
                                <td><?= htmlspecialchars($s->unidad_medida) ?></td>
                                <td><?= $s->cantidad ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($s->fecha_actualizacion)) ?></td>
                                <td>
                                    <?php if($s->imagen): ?>
                                        <img src="../assets/imagenes/insumos/<?= $s->imagen ?>" alt="Imagen" class="img-small">
                                    <?php else: ?>
                                        <span style="color: #888; font-style: italic;">Sin imagen</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 6px;">
                                        <a href="formeditarstock.php?id=<?= $s->id_insumo ?>" class="btn btn-warning btn-sm">
                                              <img src="/lugopata/assets/resources/editarU.png" alt="Actualizar" class="btn-icon">
                                            Actualizar
                                        </a>
                                        <form method="post" style="display: inline; margin: 0;" onsubmit="return confirm('ADVERTENCIA: Esta acción es irreversible.\n¿Está seguro que desea eliminar este stock permanentemente?')">
                                            <input type="hidden" name="eliminar_id" value="<?= $s->id_insumo ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                  <img src="/lugopata/assets/resources/eliminar2.png" alt="Eliminar" class="btn-icon" style="filter: brightness(0) invert(1) !important;">
                                                Eliminar
                                            </button>
                                        </form>
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
                        <a href="?pagina=<?= $pagina - 1 ?>" class="btn-paginacion">
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
                        <a href="?pagina=<?= $pagina + 1 ?>" class="btn-paginacion">
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
                    Mostrando <?= (($pagina - 1) * $registrosPorPagina) + 1 ?> - <?= min($pagina * $registrosPorPagina, $totalRegistros) ?> de <?= $totalRegistros ?> registros
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>