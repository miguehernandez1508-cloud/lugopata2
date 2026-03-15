<?php
// /trabajadores/listartrabajador.php
session_start();
include_once __DIR__ . "/../encabezado.php";
require_once __DIR__ . "/trabajador.php";
require_once __DIR__ . "/../user/gestorsesion.php";

GestorSesiones::iniciar();

$registrosPorPagina = 10;
$pagina = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$inicio = ($pagina - 1) * $registrosPorPagina;

$filtro = isset($_GET['departamento']) ? trim($_GET['departamento']) : 'todos';

$trabajadorObj = new Trabajador($conexion);
$todos = $trabajadorObj->listar();

if ($filtro !== 'todos') {
    $trabajadoresFiltrados = array_filter($todos, function($t) use ($filtro) {
        return strtolower($t->departamento) === strtolower($filtro);
    });
} else {
    $trabajadoresFiltrados = $todos;
}

$total = count($trabajadoresFiltrados);
$totalPaginas = ceil($total / $registrosPorPagina);
$trabajadores = array_slice($trabajadoresFiltrados, $inicio, $registrosPorPagina);

$departamentos = array_unique(array_map(function($t) {
    return $t->departamento;
}, $todos));
sort($departamentos);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
<title>Listado de Trabajadores</title>
<style>
    /* ===== RESET COMPLETO Y VARIABLES ===== */
    * {
        box-sizing: border-box !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    body {
        background: url("/lugopata/assets/resources/fondoR.png") no-repeat center center fixed !important;
        background-size: cover !important;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif !important;
        margin: 0 !important;
        padding: 15px !important;
        min-height: 100vh !important;
        overflow-x: hidden !important;
    }

    /* ===== CONTENEDOR PRINCIPAL ===== */
    .trabajadores-wrapper {
        max-width: 1400px !important;
        margin: 0 auto !important;
        padding: 10px !important;
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

    .btn-sm {
        padding: 6px 12px !important;
        font-size: 12px !important;
    }

    .btn-icon {
        width: 16px !important;
        height: 16px !important;
        flex-shrink: 0 !important;
    }

    .btn-agregar-container {
        margin-top: 15px !important;
    }

    /* ===== TARJETA DE CONTENIDO ===== */
    .content-card {
        background: white !important;
        border-radius: 15px !important;
        padding: 25px !important;
        border: 2px solid #ccc !important;
        width: 100% !important;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1) !important;
    }

    /* ===== FILTROS ===== */
    .filtros-container {
        margin-bottom: 20px !important;
        padding-bottom: 20px !important;
        border-bottom: 2px solid #dee2e6 !important;
    }

    .filtro-group {
        display: flex !important;
        flex-direction: column !important;
        gap: 8px !important;
        max-width: 400px !important;
        margin: 0 auto !important;
    }

    .filtro-group label {
        font-weight: bold !important;
        color: #333 !important;
        font-size: clamp(14px, 3vw, 16px) !important;
    }

    .filtro-group select {
        padding: 12px 15px !important;
        border: 2px solid #ccc !important;
        border-radius: 8px !important;
        font-size: 16px !important;
        background: white !important;
        cursor: pointer !important;
        width: 100% !important;
    }

    /* ===== GRID DE TARJETAS (VISTA MÓVIL) ===== */
    .trabajadores-grid {
        display: grid !important;
        grid-template-columns: 1fr !important;
        gap: 15px !important;
        margin-top: 20px !important;
    }

    .trabajador-card {
        background: #f8f9fa !important;
        border-radius: 12px !important;
        padding: 20px !important;
        border: 2px solid #dee2e6 !important;
        transition: all 0.3s ease !important;
        position: relative !important;
        overflow: hidden !important;
    }

    .trabajador-card:hover {
        transform: translateY(-3px) !important;
        box-shadow: 0 8px 20px rgba(0,0,0,0.1) !important;
        border-color: #28a745 !important;
    }

    .trabajador-card::before {
        content: '' !important;
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        bottom: 0 !important;
        width: 5px !important;
        background: #28a745 !important;
    }

    .trabajador-header {
        display: flex !important;
        justify-content: space-between !important;
        align-items: flex-start !important;
        margin-bottom: 15px !important;
        flex-wrap: wrap !important;
        gap: 10px !important;
    }

    .trabajador-nombre {
        font-size: clamp(18px, 4vw, 22px) !important;
        font-weight: bold !important;
        color: #333 !important;
    }

    .trabajador-id {
        font-size: 14px !important;
        color: #6c757d !important;
        background: white !important;
        padding: 5px 10px !important;
        border-radius: 20px !important;
        border: 1px solid #dee2e6 !important;
        font-family: monospace !important;
    }

    .trabajador-body {
        margin-bottom: 15px !important;
    }

    .info-row {
        display: flex !important;
        justify-content: space-between !important;
        padding: 8px 0 !important;
        border-bottom: 1px solid #e9ecef !important;
        flex-wrap: wrap !important;
        gap: 5px !important;
    }

    .info-row:last-child {
        border-bottom: none !important;
    }

    .info-label {
        font-weight: bold !important;
        color: #555 !important;
        font-size: 14px !important;
    }

    .info-value {
        color: #333 !important;
        font-size: 14px !important;
        text-align: right !important;
        word-break: break-word !important;
        max-width: 60% !important;
    }

    .info-value.direccion,
    .info-value.aptitudes {
        text-align: left !important;
        max-width: 100% !important;
        width: 100% !important;
        margin-top: 5px !important;
        padding: 10px !important;
        background: white !important;
        border-radius: 6px !important;
        border: 1px solid #e9ecef !important;
    }

    .no-aplica {
        color: #6c757d !important;
        font-style: italic !important;
    }

    .trabajador-footer {
        margin-top: 15px !important;
        padding-top: 15px !important;
        border-top: 2px solid #dee2e6 !important;
    }

    .btn-editar {
        width: 100% !important;
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

    /* Columnas de texto largo en tabla */
    td:nth-child(6),
    td:nth-child(8) {
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
        color: #6c757d !important;
        font-size: clamp(16px, 3vw, 18px) !important;
        font-style: italic !important;
        background-color: #f8f9fa !important;
        border-radius: 12px !important;
        border: 2px dashed #dee2e6 !important;
        margin: 20px 0 !important;
    }

    /* ===== MEDIA QUERIES ===== */
    @media (min-width: 768px) {
        body {
            padding: 20px !important;
        }

        .trabajadores-wrapper {
            padding: 15px !important;
        }

        .trabajadores-grid {
            display: none !important;
        }

        .table-container {
            display: block !important;
        }

        .btn {
            width: auto !important;
        }

        .filtro-group {
            flex-direction: row !important;
            align-items: center !important;
        }

        .filtro-group select {
            width: auto !important;
            min-width: 250px !important;
        }
    }

    @media (min-width: 1024px) {
        .trabajadores-wrapper {
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

        .trabajador-card {
            padding: 15px !important;
        }

        .trabajador-header {
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
    <div class="trabajadores-wrapper">
        <!-- Título -->
        <div class="title-card">
            <h1>
                <img src="../assets/resources/ltrabajadores2.png" alt="Trabajadores">
                LISTA DE TRABAJADORES
            </h1>
            <p>Gestión de trabajadores del sistema</p>
            
            <div class="btn-agregar-container">
               <a href="formctrabajador.php" class="btn btn-success">
                 <img src="../assets/resources/maso.png" alt="Agregar" class="btn-icon" style="filter: brightness(0) invert(0.8) !important;">
                    Agregar Trabajador
             </a>
            </div>
        </div>

        <!-- Contenido -->
        <div class="content-card">
            <!-- Filtros -->
            <div class="filtros-container">
                <form method="get" class="filtro-group">
                    <label for="departamento">Departamento:</label>
                    <select name="departamento" id="departamento" onchange="this.form.submit()">
                        <option value="todos" <?= $filtro === 'todos' ? 'selected' : '' ?>>Todos los departamentos</option>
                        <?php foreach ($departamentos as $dep): ?>
                            <option value="<?= htmlspecialchars($dep) ?>" <?= strtolower($filtro) === strtolower($dep) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($dep) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>

            <?php if (empty($trabajadores)): ?>
                <!-- Sin datos -->
                <div class="mensaje-sin-datos">
                    <h3>No hay trabajadores para estos filtros</h3>
                    <p>Intenta seleccionar otro departamento o agrega nuevos trabajadores.</p>
                </div>
            <?php else: ?>
                <!-- Vista Grid para Móvil -->
                <div class="trabajadores-grid">
                    <?php foreach ($trabajadores as $t): ?>
                    <div class="trabajador-card">
                        <div class="trabajador-header">
                            <div>
                                <div class="trabajador-nombre"><?= htmlspecialchars($t->nombre . ' ' . $t->apellido) ?></div>
                                <div style="margin-top: 5px; color: #6c757d; font-size: 14px;">
                                    <?= htmlspecialchars($t->departamento) ?>
                                </div>
                            </div>
                            <div class="trabajador-id">#<?= $t->id_trabajador ?></div>
                        </div>
                        
                        <div class="trabajador-body">
                            <div class="info-row">
                                <span class="info-label">Cédula:</span>
                                <span class="info-value"><?= htmlspecialchars($t->cedula) ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Teléfono:</span>
                                <span class="info-value"><?= htmlspecialchars($t->telefono) ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Dirección:</span>
                                <span class="info-value direccion"><?= htmlspecialchars($t->direccion) ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Aptitudes:</span>
                                <span class="info-value aptitudes">
                                    <?php if (strtolower($t->departamento) === "mantenimiento"): ?>
                                        <?= $t->aptitudes ? htmlspecialchars($t->aptitudes) : '<span class="no-aplica">Sin aptitudes registradas</span>' ?>
                                    <?php else: ?>
                                        <span class="no-aplica">No aplica</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="trabajador-footer">
                            <a href="editartrabajador.php?id_trabajador=<?= $t->id_trabajador ?>" class="btn btn-warning btn-editar">
                              <img src="/lugopata/assets/resources/editarU.png" alt="Editar" class="btn-icon">
                                 Editar
                            </a>
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
                                <th>Cédula</th>
                                <th>Nombre</th>
                                <th>Apellido</th>
                                <th>Teléfono</th>
                                <th>Dirección</th>
                                <th>Departamento</th>
                                <th>Aptitudes</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($trabajadores as $t): ?>
                            <tr>
                                <td><strong>#<?= $t->id_trabajador ?></strong></td>
                                <td><?= htmlspecialchars($t->cedula) ?></td>
                                <td><?= htmlspecialchars($t->nombre) ?></td>
                                <td><?= htmlspecialchars($t->apellido) ?></td>
                                <td><?= htmlspecialchars($t->telefono) ?></td>
                                <td><?= htmlspecialchars($t->direccion) ?></td>
                                <td><?= htmlspecialchars($t->departamento) ?></td>
                                <td>
                                    <?php if (strtolower($t->departamento) === "mantenimiento"): ?>
                                        <?= $t->aptitudes ? htmlspecialchars($t->aptitudes) : '<i>Sin aptitudes registradas</i>' ?>
                                    <?php else: ?>
                                        <i>No aplica</i>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="editartrabajador.php?id_trabajador=<?= $t->id_trabajador ?>" class="btn btn-warning btn-editar">
                                     <img src="/lugopata/assets/resources/editarU.png" alt="Editar" class="btn-icon">
                                       Editar
                                    </a>
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
                        <a href="?pagina=<?= $pagina - 1 ?>&departamento=<?= urlencode($filtro) ?>" class="btn-paginacion">
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
                        <a href="?pagina=<?= $pagina + 1 ?>&departamento=<?= urlencode($filtro) ?>" class="btn-paginacion">
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
                    Mostrando <?= (($pagina - 1) * $registrosPorPagina) + 1 ?> - <?= min($pagina * $registrosPorPagina, $total) ?> de <?= $total ?> trabajadores
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>