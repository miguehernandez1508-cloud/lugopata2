<?php
session_start();
include_once "../encabezado.php";
require_once "departamento.php";
require_once "../conex.php";
require_once "../user/gestorsesion.php";

GestorSesiones::iniciar();

$registrosPorPagina = 10;
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
if ($pagina < 1) $pagina = 1;
$inicio = ($pagina - 1) * $registrosPorPagina;

$departamentoObj = new Departamento($conexion);
$totalRegistros = $departamentoObj->contar();
$totalPaginas = ceil($totalRegistros / $registrosPorPagina);
$departamentos = $departamentoObj->listar($inicio, $registrosPorPagina);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
<title>Lista de Departamentos</title>
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

    .departamentos-wrapper {
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

    .departamentos-grid {
        display: grid !important;
        grid-template-columns: 1fr !important;
        gap: 12px !important;
        margin-top: 20px !important;
    }

    .departamento-card {
        background: #fafafa !important;
        border: 1px solid #e0e0e0 !important;
        border-radius: 10px !important;
        padding: 15px !important;
        position: relative !important;
        transition: all 0.2s ease !important;
    }

    .departamento-card:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
        border-color: #0d6efd !important;
    }

    .departamento-card::before {
        content: '' !important;
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        bottom: 0 !important;
        width: 4px !important;
        background: #0d6efd !important;
        border-radius: 10px 0 0 10px !important;
    }

    .departamento-header {
        display: flex !important;
        justify-content: space-between !important;
        align-items: flex-start !important;
        margin-bottom: 12px !important;
        flex-wrap: wrap !important;
        gap: 8px !important;
    }

    .departamento-nombre {
        font-size: 18px !important;
        font-weight: 600 !important;
        color: #333 !important;
    }

    .departamento-id {
        font-size: 12px !important;
        color: #888 !important;
        background: white !important;
        padding: 4px 10px !important;
        border-radius: 15px !important;
        border: 1px solid #e0e0e0 !important;
        font-family: monospace !important;
    }

    .departamento-body {
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

    .info-value.descripcion,
    .info-value.ubicacion {
        text-align: left !important;
        max-width: 100% !important;
        width: 100% !important;
        margin-top: 4px !important;
        padding: 8px !important;
        background: white !important;
        border-radius: 4px !important;
        border: 1px solid #e9ecef !important;
    }

    .departamento-footer {
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

    td:nth-child(3) {
        max-width: 250px !important;
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

        .departamentos-grid {
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
        .departamentos-wrapper {
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

        .departamento-card {
            padding: 12px !important;
        }

        .departamento-header {
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
    <div class="departamentos-wrapper">
        <div class="title-card">
            <h1>
                <img src="../assets/resources/departamentosR.png" alt="Departamentos">
                LISTA DE DEPARTAMENTOS
            </h1>
            <p>Gestión de departamentos del sistema</p>
            
            <div style="margin-top: 15px;">
                <a href="formcreardepartamentos.php" class="btn btn-success">
                    <img src="../assets/resources/maso.png" alt="Agregar" class="btn-icon" style="filter: brightness(0) invert(0.8) !important;">
                    Agregar Departamento
                </a>
            </div>
        </div>

        <div class="content-card">
            <?php if (empty($departamentos)): ?>
                <div class="mensaje-sin-datos">
                    <h3>No hay departamentos registrados</h3>
                    <p>Agrega nuevos departamentos para comenzar.</p>
                </div>
            <?php else: ?>
                <div class="departamentos-grid">
                    <?php foreach ($departamentos as $d): ?>
                    <div class="departamento-card">
                        <div class="departamento-header">
                            <div>
                                <div class="departamento-nombre"><?= htmlspecialchars($d->nombre) ?></div>
                                <div style="margin-top: 4px; color: #666; font-size: 13px;">
                                    Responsable: <?= htmlspecialchars($d->responsable ?: 'No asignado') ?>
                                </div>
                            </div>
                            <div class="departamento-id">#<?= $d->id_departamento ?></div>
                        </div>
                        
                        <div class="departamento-body">
                            <div class="info-row">
                                <span class="info-label">Ubicación:</span>
                                <span class="info-value ubicacion"><?= htmlspecialchars($d->ubicacion ?: 'No especificada') ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Teléfono:</span>
                                <span class="info-value"><?= $d->telefono ?: 'N/A' ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Email:</span>
                                <span class="info-value"><?= $d->email ?: 'N/A' ?></span>
                            </div>
                            <div class="info-row" style="flex-direction: column; align-items: flex-start;">
                                <span class="info-label">Descripción:</span>
                                <span class="info-value descripcion"><?= htmlspecialchars($d->descripcion ?: 'Sin descripción') ?></span>
                            </div>
                        </div>
                        
                        <div class="departamento-footer">
                            <div style="display: flex; gap: 8px;">
                                <a href="formeditardepartamento.php?id=<?= $d->id_departamento ?>" class="btn btn-warning" style="flex: 1;">
                                    <img src="/lugopata/assets/resources/editarU.png" alt="Editar" class="btn-icon">
                                    Editar
                                </a>
                                <a href="eliminardepartamento.php?id=<?= $d->id_departamento ?>" 
                                   class="btn btn-danger" 
                                   style="flex: 1;"
                                   onclick="return confirm('Esta acción no se puede deshacer. ¿Está seguro?')">
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
                                <th>Ubicación</th>
                                <th>Teléfono</th>
                                <th>Email</th>
                                <th>Responsable</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($departamentos as $d): ?>
                            <tr>
                                <td><strong>#<?= $d->id_departamento ?></strong></td>
                                <td><?= htmlspecialchars($d->nombre) ?></td>
                                <td><?= htmlspecialchars($d->descripcion) ?></td>
                                <td><?= htmlspecialchars($d->ubicacion) ?></td>
                                <td><?= $d->telefono ?: 'N/A' ?></td>
                                <td><?= $d->email ?: 'N/A' ?></td>
                                <td><?= htmlspecialchars($d->responsable ?: 'No asignado') ?></td>
                                <td>
                                    <div style="display: flex; gap: 6px;">
                                        <a href="formeditardepartamento.php?id=<?= $d->id_departamento ?>" class="btn btn-warning btn-sm">
                                            <img src="/lugopata/assets/resources/editarU.png" alt="Editar" class="btn-icon">
                                            Editar
                                        </a>
                                        <a href="eliminardepartamento.php?id=<?= $d->id_departamento ?>" 
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Esta acción no se puede deshacer. ¿Está seguro?')">
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
                    Mostrando <?= (($pagina - 1) * $registrosPorPagina) + 1 ?> - <?= min($pagina * $registrosPorPagina, $totalRegistros) ?> de <?= $totalRegistros ?> departamentos
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>