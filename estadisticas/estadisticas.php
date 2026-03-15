<?php
session_start();
include_once "../encabezado.php";
include_once "../conex.php";
require_once "../user/gestorsesion.php";
require_once "../incidencias/incidencia.php";

GestorSesiones::iniciar();

// ================== INSUMOS ================== //
$sentenciaCat = $conexion->query("SELECT id_categoria, nombre FROM categorias_insumo ORDER BY nombre ASC");
$categorias = $sentenciaCat->fetchAll(PDO::FETCH_OBJ);

$sentenciaIns = $conexion->query("SELECT id_insumo, nombre FROM insumos ORDER BY nombre ASC");
$insumos = $sentenciaIns->fetchAll(PDO::FETCH_OBJ);

$categoria_id = $_GET['categoria'] ?? 'todas';
$producto_id = $_GET['producto'] ?? 'todos';
$fecha_desde = $_GET['fecha_desde'] ?? '';
$fecha_hasta = $_GET['fecha_hasta'] ?? '';

$params = [];
$sql = "SELECT SUM(d.cantidad_pedida) AS total_pedidos,
               SUM(d.cantidad_recibida) AS total_recibidos
        FROM detalle_solicitud_material d
        INNER JOIN solicitud_materiales s ON d.id_solicitud = s.id_solicitud
        INNER JOIN insumos i ON d.id_insumo = i.id_insumo
        WHERE 1=1";

if ($categoria_id !== 'todas') { $sql .= " AND i.id_categoria = ?"; $params[] = $categoria_id; }
if ($producto_id !== 'todos') { $sql .= " AND d.id_insumo = ?"; $params[] = $producto_id; }
if ($fecha_desde) { $sql .= " AND s.fecha >= ?"; $params[] = $fecha_desde; }
if ($fecha_hasta) { $sql .= " AND s.fecha <= ?"; $params[] = $fecha_hasta; }

$sentencia = $conexion->prepare($sql);
$sentencia->execute($params);
$totales = $sentencia->fetch(PDO::FETCH_OBJ);

$totalPedidos = $totales->total_pedidos ?? 0;
$totalRecibidos = $totales->total_recibidos ?? 0;

// ================== INCIDENCIAS ================== //
$fecha_desde_inc = $_GET['fecha_desde_inc'] ?? '';
$fecha_hasta_inc = $_GET['fecha_hasta_inc'] ?? '';
$trabajador_id = $_GET['trabajador'] ?? 'todos';

$incObj = new Incidencia($conexion);

$paramsInc = [];
$sqlInc = "SELECT estado, COUNT(*) as total FROM incidencias WHERE 1=1";
if ($fecha_desde_inc) { $sqlInc .= " AND fecha >= ?"; $paramsInc[] = $fecha_desde_inc; }
if ($fecha_hasta_inc) { $sqlInc .= " AND fecha <= ?"; $paramsInc[] = $fecha_hasta_inc; }
$sqlInc .= " GROUP BY estado";

$sentenciaInc = $conexion->prepare($sqlInc);
$sentenciaInc->execute($paramsInc);
$resultadosInc = $sentenciaInc->fetchAll(PDO::FETCH_ASSOC);

$totalFinalizadas = 0;
$totalNoFinalizadas = 0;
$totalRechazadas = 0;
foreach ($resultadosInc as $fila) {
    if ($fila['estado'] === 'Finalizada') $totalFinalizadas = $fila['total'];
    elseif ($fila['estado'] === 'En espera' || $fila['estado'] === 'Pendiente') $totalNoFinalizadas += $fila['total'];
    elseif ($fila['estado'] === 'Rechazada') $totalRechazadas = $fila['total'];
}

// ================== ESTADÍSTICAS AVANZADAS ================== //

$sql_insumos_criticos = "
    SELECT 
        i.id_insumo,
        i.nombre,
        i.cantidad as stock_actual,
        i.stock_minimo,
        i.stock_maximo,
        CASE 
            WHEN i.cantidad <= i.stock_minimo THEN 'CRITICO'
            WHEN i.cantidad <= (i.stock_maximo * 0.3) THEN 'BAJO'
            WHEN i.cantidad <= (i.stock_maximo * 0.7) THEN 'MODERADO'
            ELSE 'OPTIMO'
        END as estado_stock,
        c.nombre as categoria
    FROM insumos i
    LEFT JOIN categorias_insumo c ON i.id_categoria = c.id_categoria
    WHERE i.cantidad <= (i.stock_maximo * 0.3) OR i.cantidad <= i.stock_minimo
    ORDER BY i.cantidad ASC
    LIMIT 10
";

$insumos_criticos = $conexion->query($sql_insumos_criticos)->fetchAll(PDO::FETCH_OBJ);

$sql_prioridades = "
    SELECT 
        prioridad,
        COUNT(*) as total,
        SUM(CASE WHEN estado = 'Finalizada' THEN 1 ELSE 0 END) as finalizadas,
        SUM(CASE WHEN estado IN ('En espera', 'Pendiente') THEN 1 ELSE 0 END) as pendientes,
        SUM(CASE WHEN estado = 'Rechazada' THEN 1 ELSE 0 END) as rechazadas,
        ROUND(AVG(TIMESTAMPDIFF(HOUR, fecha, COALESCE(fecha_finalizacion, NOW()))), 1) as tiempo_promedio_horas
    FROM incidencias
    WHERE 1=1
";

$params_pri = [];
if ($fecha_desde_inc) { 
    $sql_prioridades .= " AND fecha >= ?"; 
    $params_pri[] = $fecha_desde_inc; 
}
if ($fecha_hasta_inc) { 
    $sql_prioridades .= " AND fecha <= ?"; 
    $params_pri[] = $fecha_hasta_inc; 
}

$sql_prioridades .= " GROUP BY prioridad ORDER BY 
    CASE prioridad 
        WHEN 'Urgente' THEN 1 
        WHEN 'Moderada' THEN 2 
        WHEN 'Leve' THEN 3 
    END";

$sentencia_pri = $conexion->prepare($sql_prioridades);
$sentencia_pri->execute($params_pri);
$estadisticas_prioridad = $sentencia_pri->fetchAll(PDO::FETCH_OBJ);

$sql_kpis = "
    SELECT 
        (SELECT COUNT(*) FROM incidencias WHERE estado = 'Finalizada' AND fecha >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)) as incidencias_finalizadas_mes,
        (SELECT COUNT(*) FROM incidencias WHERE estado IN ('En espera', 'Pendiente')) as incidencias_pendientes,
        (SELECT ROUND(AVG(TIMESTAMPDIFF(HOUR, fecha, fecha_finalizacion)), 1) FROM incidencias WHERE estado = 'Finalizada' AND fecha >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)) as tiempo_promedio_resolucion,
        (SELECT COUNT(*) FROM insumos WHERE cantidad <= stock_minimo) as insumos_criticos,
        (SELECT COUNT(*) FROM solicitud_materiales WHERE estado = 'Finalizada' AND fecha >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)) as solicitudes_completadas_mes
";

$kpis = $conexion->query($sql_kpis)->fetch(PDO::FETCH_OBJ);

$sql_conformidad = "
    SELECT 
        c.calificacion,
        COUNT(*) as total,
        ROUND(AVG(TIMESTAMPDIFF(HOUR, i.fecha, i.fecha_finalizacion)), 1) as tiempo_promedio_horas
    FROM incidencia_conformidad c
    INNER JOIN incidencias i ON c.id_incidencia = i.id_incidencia
    WHERE c.confirmada = 1 AND c.calificacion IS NOT NULL
    GROUP BY c.calificacion
    ORDER BY c.calificacion DESC
";

$estadisticas_conformidad = $conexion->query($sql_conformidad)->fetchAll(PDO::FETCH_OBJ);

$trabajadores = $conexion->query("
    SELECT t.id_trabajador, CONCAT(t.nombre, ' ', t.apellido) as nombre_completo 
    FROM trabajadores t
    INNER JOIN detalle_trabajador dt ON t.id_trabajador = dt.id_trabajador
    ORDER BY t.nombre
")->fetchAll(PDO::FETCH_OBJ);

$estadisticas_fases = $incObj->obtenerEstadisticasFases(
    $trabajador_id !== 'todos' ? $trabajador_id : null,
    $fecha_desde_inc ?: null,
    $fecha_hasta_inc ?: null
);

$sql_trabajadores = "
    SELECT 
        t.id_trabajador,
        CONCAT(t.nombre, ' ', t.apellido) as nombre_completo,
        COUNT(i.id_incidencia) as total_incidencias,
        AVG(TIMESTAMPDIFF(HOUR, i.fecha, i.fecha_finalizacion)) as tiempo_promedio_horas,
        SUM(CASE WHEN i.estado = 'Finalizada' THEN 1 ELSE 0 END) as incidencias_finalizadas
    FROM trabajadores t
    LEFT JOIN incidencias i ON t.id_trabajador = i.id_trabajador_asignado
    WHERE 1=1
";

$params_trab = [];
if ($fecha_desde_inc) { 
    $sql_trabajadores .= " AND i.fecha >= ?"; 
    $params_trab[] = $fecha_desde_inc; 
}
if ($fecha_hasta_inc) { 
    $sql_trabajadores .= " AND i.fecha <= ?"; 
    $params_trab[] = $fecha_hasta_inc; 
}

$sql_trabajadores .= " GROUP BY t.id_trabajador, t.nombre, t.apellido HAVING total_incidencias > 0 ORDER BY incidencias_finalizadas DESC";

$sentencia_trab = $conexion->prepare($sql_trabajadores);
$sentencia_trab->execute($params_trab);
$estadisticas_trabajadores = $sentencia_trab->fetchAll(PDO::FETCH_OBJ);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
<title>Estadísticas - Dashboard</title>
<style>
    /* RESET Y VARIABLES */
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

    /* CONTENEDOR PRINCIPAL */
    .dashboard-wrapper {
        max-width: 1200px;
        margin: 0 auto;
        width: 100%;
    }

    /* TARJETAS DE SECCIÓN */
    .card {
        background: white;
        border-radius: 15px;
        border: 2px solid #ccc;
        padding: 25px;
        margin-bottom: 20px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    .card-header {
        text-align: center;
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 2px solid #e5e7eb;
    }

    .card-header h1 {
        color: #333;
        font-weight: bold;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        margin: 0;
        font-size: clamp(1.2rem, 4vw, 1.8rem);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .card-header h1 img {
        width: 40px;
        height: 40px;
    }

    .card-header p {
        color: #666;
        margin-top: 10px;
        font-size: clamp(13px, 2vw, 15px);
    }

    /* SECCIÓN DE ESTADÍSTICAS */
    .stat-section {
        background: white;
        border-radius: 15px;
        border: 2px solid #ccc;
        padding: 25px;
        margin-bottom: 20px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    .section-title {
        color: #333;
        border-bottom: 3px solid #3b82f6;
        padding-bottom: 12px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: bold;
        font-size: clamp(1rem, 3vw, 1.3rem);
    }

    .section-title img {
        width: 28px;
        height: 28px;
    }

    /* FILTROS */
    .filtros {
        display: flex;
        gap: 15px;
        margin-bottom: 25px;
        flex-wrap: wrap;
        justify-content: center;
    }

    .filtro-group {
        display: flex;
        align-items: center;
        gap: 8px;
        flex: 1;
        min-width: 200px;
    }

    .filtro-group label {
        font-weight: 600;
        color: #374151;
        font-size: 14px;
        white-space: nowrap;
    }

    .filtro-group select, 
    .filtro-group input {
        flex: 1;
        padding: 10px 12px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 14px;
        background: white;
        transition: all 0.3s ease;
    }

    .filtro-group select:focus, 
    .filtro-group input:focus {
        border-color: #3b82f6;
        outline: none;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    /* BOTONES */
    .btn {
        padding: 10px 20px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all 0.2s ease;
    }

    .btn-filter {
        background-color: #0d6efd;
        color: white;
    }

    .btn-filter:hover {
        background-color: #0b5ed7;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
    }

    /* KPI CARDS */
    .kpi-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 15px;
        margin: 20px 0;
    }

    .kpi-box {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%) !important;
        color: white;
        padding: 20px;
        border-radius: 12px;
        text-align: center;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
    }

    .kpi-box:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 15px rgba(0,0,0,0.15);
    }

    .kpi-valor {
        font-size: 2.2em;
        font-weight: bold;
        margin: 10px 0;
    }

    .kpi-label {
        font-size: 0.85em;
        opacity: 0.9;
        line-height: 1.3;
    }

    /* TABLAS */
    .table-container {
        width: 100%;
        overflow-x: auto;
        margin-top: 15px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        min-width: 600px;
    }

    th, td {
        border: 1px solid #e5e7eb;
        padding: 12px;
        text-align: center;
        background-color: #fff;
        font-size: 14px;
    }

    th {
        background-color: #cfe2ff !important;
        font-weight: 600;
        color: #374151;
        text-transform: uppercase;
        font-size: 12px;
    }

    tr:hover td {
        background-color: #f9fafb;
    }

    /* BADGES */
    .badge {
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        display: inline-block;
    }

    .badge-critico {
        background-color: #fee2e2;
        color: #dc2626;
    }

    .badge-bajo {
        background-color: #ffedd5;
        color: #ea580c;
    }

    .badge-moderado {
        background-color: #fef3c7;
        color: #d97706;
    }

    .badge-optimo {
        background-color: #d1fae5;
        color: #059669;
    }

    /* ALERTAS */
    .alert-critical {
        background: #fef2f2;
        border-left: 4px solid #dc2626;
        color: #991b1b;
        padding: 15px;
        border-radius: 8px;
        margin: 15px 0;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    /* EFICIENCIA */
    .eficiencia-alta { 
        color: #059669; 
        font-weight: 600; 
        background: #d1fae5;
        padding: 2px 8px;
        border-radius: 4px;
    }

    .eficiencia-media { 
        color: #d97706; 
        font-weight: 600; 
        background: #fef3c7;
        padding: 2px 8px;
        border-radius: 4px;
    }

    .eficiencia-baja { 
        color: #dc2626; 
        font-weight: 600; 
        background: #fee2e2;
        padding: 2px 8px;
        border-radius: 4px;
    }

    /* GRÁFICOS */
    .chart-container {
        position: relative;
        height: 350px;
        margin: 20px 0;
        background: white;
        border-radius: 8px;
        padding: 15px;
    }

    .graficos-container {
        height: 300px;
        margin: 20px 0;
    }

    /* MEDIA QUERIES */
    @media (max-width: 768px) {
        body {
            padding: 10px;
        }

        .card, .stat-section {
            padding: 15px;
        }

        .filtros {
            flex-direction: column;
        }

        .filtro-group {
            min-width: 100%;
        }

        .kpi-container {
            grid-template-columns: repeat(2, 1fr);
        }

        .kpi-valor {
            font-size: 1.8em;
        }

        table {
            min-width: 500px;
        }

        th, td {
            padding: 8px;
            font-size: 12px;
        }

        .chart-container {
            height: 250px;
        }

        .graficos-container {
            height: 200px;
        }
    }

    @media (max-width: 480px) {
        .card-header h1 img {
            width: 32px;
            height: 32px;
        }

        .section-title img {
            width: 24px;
            height: 24px;
        }

        .kpi-container {
            grid-template-columns: 1fr;
        }

        .btn {
            width: 100%;
        }

        .filtro-group {
            flex-direction: column;
            align-items: stretch;
        }

        .filtro-group label {
            margin-bottom: 4px;
        }
    }
</style>
</head>
<body>

<div class="dashboard-wrapper">

    <!-- ================= INSUMOS ================= -->
    <div class="card">
        <div class="card-header">
            <h1>
                <img src="../assets/resources/insumos.png" alt="Insumos">
                ESTADÍSTICAS DE INSUMOS
            </h1>
            <p>Comparativa de cantidad pedida vs recibida</p>
        </div>

        <form method="get" class="filtros">
            <div class="filtro-group">
                <label for="categoria">Categoría:</label>
                <select name="categoria" id="categoria">
                    <option value="todas" <?= $categoria_id === 'todas' ? 'selected' : '' ?>>Todas</option>
                    <?php foreach ($categorias as $c): ?>
                        <option value="<?= $c->id_categoria ?>" <?= $categoria_id == $c->id_categoria ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c->nombre) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filtro-group">
                <label for="producto">Insumo:</label>
                <select name="producto" id="producto">
                    <option value="todos" <?= $producto_id === 'todos' ? 'selected' : '' ?>>Todos</option>
                    <?php foreach ($insumos as $i): ?>
                        <option value="<?= $i->id_insumo ?>" <?= $producto_id === $i->id_insumo ? 'selected' : '' ?>>
                            <?= htmlspecialchars($i->nombre) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filtro-group">
                <label>Desde:</label>
                <input type="date" name="fecha_desde" value="<?= htmlspecialchars($fecha_desde) ?>">
            </div>
            <div class="filtro-group">
                <label>Hasta:</label>
                <input type="date" name="fecha_hasta" value="<?= htmlspecialchars($fecha_hasta) ?>">
            </div>
            <button type="submit" class="btn btn-filter">Filtrar</button>
        </form>

        <div class="graficos-container">
            <canvas id="graficoInsumos"></canvas>
        </div>
    </div>

    <!-- ================= INCIDENCIAS ================= -->
    <div class="card">
        <div class="card-header">
            <h1>
                <img src="../assets/resources/estadisticas.png" alt="Incidencias">
                ESTADÍSTICAS DE INCIDENCIAS
            </h1>
            <p>Comparativa de Finalizadas, No finalizadas y Rechazadas</p>
        </div>

        <form method="get" class="filtros">
            <div class="filtro-group">
                <label>Desde:</label>
                <input type="date" name="fecha_desde_inc" value="<?= htmlspecialchars($fecha_desde_inc) ?>">
            </div>
            <div class="filtro-group">
                <label>Hasta:</label>
                <input type="date" name="fecha_hasta_inc" value="<?= htmlspecialchars($fecha_hasta_inc) ?>">
            </div>
            <div class="filtro-group">
                <label for="trabajador">Trabajador:</label>
                <select name="trabajador" id="trabajador">
                    <option value="todos" <?= $trabajador_id === 'todos' ? 'selected' : '' ?>>Todos</option>
                    <?php foreach ($trabajadores as $t): ?>
                        <option value="<?= $t->id_trabajador ?>" <?= $trabajador_id == $t->id_trabajador ? 'selected' : '' ?>>
                            <?= htmlspecialchars($t->nombre_completo) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-filter">Filtrar</button>
        </form>

        <div class="graficos-container">
            <canvas id="graficoIncidencias"></canvas>
        </div>
    </div>

    <!-- ================= KPI's ================= -->
    <div class="stat-section">
        <h2 class="section-title">
            <img src="../assets/resources/resumen.png" alt="Resumen">
            RESUMEN DEL SISTEMA
        </h2>
        
        <div class="kpi-container">
            <div class="kpi-box">
                <div class="kpi-valor"><?= $kpis->incidencias_finalizadas_mes ?? 0 ?></div>
                <div class="kpi-label">Incidencias Finalizadas<br>(30 días)</div>
            </div>
            <div class="kpi-box">
                <div class="kpi-valor"><?= $kpis->incidencias_pendientes ?? 0 ?></div>
                <div class="kpi-label">Incidencias Pendientes</div>
            </div>
            <div class="kpi-box">
                <div class="kpi-valor"><?= $kpis->tiempo_promedio_resolucion ?? 'N/A' ?>h</div>
                <div class="kpi-label">Tiempo Promedio<br>Resolución</div>
            </div>
            <div class="kpi-box">
                <div class="kpi-valor"><?= $kpis->insumos_criticos ?? 0 ?></div>
                <div class="kpi-label">Insumos en<br>Estado Crítico</div>
            </div>
            <div class="kpi-box">
                <div class="kpi-valor"><?= $kpis->solicitudes_completadas_mes ?? 0 ?></div>
                <div class="kpi-label">Solicitudes<br>Completadas</div>
            </div>
        </div>
    </div>

    <!-- ================= ALERTAS CRÍTICAS ================= -->
    <?php if (!empty($insumos_criticos)): ?>
    <div class="stat-section">
        <h2 class="section-title">
            <img src="../assets/resources/advertencia.png" alt="Alertas">
            ALERTAS DE INSUMOS CRÍTICOS
        </h2>
        
        <div class="alert-critical">
            ⚠️ <strong>ATENCIÓN:</strong> Se han detectado <?= count($insumos_criticos) ?> insumo(s) que requieren atención inmediata.
        </div>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Insumo</th>
                        <th>Categoría</th>
                        <th>Stock Actual</th>
                        <th>Stock Mínimo</th>
                        <th>Stock Máximo</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($insumos_criticos as $insumo): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($insumo->nombre) ?></strong></td>
                        <td><?= htmlspecialchars($insumo->categoria) ?></td>
                        <td><?= $insumo->stock_actual ?></td>
                        <td><?= $insumo->stock_minimo ?></td>
                        <td><?= $insumo->stock_maximo ?></td>
                        <td>
                            <span class="badge badge-<?= 
                                $insumo->estado_stock == 'CRITICO' ? 'critico' : 
                                ($insumo->estado_stock == 'BAJO' ? 'bajo' : 
                                ($insumo->estado_stock == 'MODERADO' ? 'moderado' : 'optimo')) 
                            ?>">
                                <?= $insumo->estado_stock ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- ================= PRIORIDADES ================= -->
    <div class="stat-section">
        <h2 class="section-title">
            <img src="../assets/resources/prioridad.png" alt="Prioridades">
            ANÁLISIS DE PRIORIDADES
        </h2>
        
        <div class="chart-container">
            <canvas id="graficoPrioridades"></canvas>
        </div>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Prioridad</th>
                        <th>Total</th>
                        <th>Finalizadas</th>
                        <th>Pendientes</th>
                        <th>Rechazadas</th>
                        <th>Tiempo Promedio</th>
                        <th>Eficiencia</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($estadisticas_prioridad)): ?>
                        <tr><td colspan="7" style="text-align: center;">No hay datos para mostrar</td></tr>
                    <?php else: ?>
                        <?php foreach ($estadisticas_prioridad as $pri): 
                            $eficiencia = $pri->total > 0 ? ($pri->finalizadas / $pri->total) * 100 : 0;
                            $clase_eficiencia = '';
                            if ($eficiencia >= 90) $clase_eficiencia = 'eficiencia-alta';
                            elseif ($eficiencia >= 75) $clase_eficiencia = 'eficiencia-media';
                            else $clase_eficiencia = 'eficiencia-baja';
                        ?>
                        <tr>
                            <td>
                                <span class="badge badge-<?= 
                                    $pri->prioridad == 'Urgente' ? 'critico' : 
                                    ($pri->prioridad == 'Moderada' ? 'bajo' : 'moderado')
                                ?>">
                                    <?= $pri->prioridad ?>
                                </span>
                            </td>
                            <td><?= $pri->total ?></td>
                            <td><?= $pri->finalizadas ?></td>
                            <td><?= $pri->pendientes ?></td>
                            <td><?= $pri->rechazadas ?></td>
                            <td><?= $pri->tiempo_promedio_horas ?>h</td>
                            <td class="<?= $clase_eficiencia ?>"><?= round($eficiencia, 1) ?>%</td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ================= CONFORMIDAD ================= -->
    <?php if (!empty($estadisticas_conformidad)): ?>
    <div class="stat-section">
        <h2 class="section-title">
            <img src="../assets/resources/satisfaccion.png" alt="Satisfacción">
            NIVEL DE SATISFACCIÓN
        </h2>
        
        <div class="chart-container">
            <canvas id="graficoConformidad"></canvas>
        </div>
    </div>
    <?php endif; ?>

    <!-- ================= RENDIMIENTO TRABAJADORES ================= -->
    <div class="stat-section">
        <h2 class="section-title">
            <img src="../assets/resources/creartrabajador2.png" alt="Trabajadores">
            RENDIMIENTO POR TRABAJADOR
        </h2>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Trabajador</th>
                        <th>Total</th>
                        <th>Finalizadas</th>
                        <th>Tiempo Promedio</th>
                        <th>Eficiencia</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($estadisticas_trabajadores)): ?>
                        <tr><td colspan="5" style="text-align: center;">No hay datos para mostrar</td></tr>
                    <?php else: ?>
                        <?php foreach ($estadisticas_trabajadores as $trab): 
                            if ($trab->total_incidencias > 0) {
                                $eficiencia = ($trab->incidencias_finalizadas / $trab->total_incidencias) * 100;
                                if ($eficiencia >= 90) $clase_eficiencia = 'eficiencia-alta';
                                elseif ($eficiencia >= 75) $clase_eficiencia = 'eficiencia-media';
                                else $clase_eficiencia = 'eficiencia-baja';
                            } else {
                                $eficiencia = 0;
                                $clase_eficiencia = '';
                            }
                        ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($trab->nombre_completo) ?></strong></td>
                            <td><?= $trab->total_incidencias ?></td>
                            <td><?= $trab->incidencias_finalizadas ?></td>
                            <td><?= $trab->tiempo_promedio_horas ? round($trab->tiempo_promedio_horas, 1) . 'h' : 'N/A' ?></td>
                            <td class="<?= $clase_eficiencia ?>">
                                <?= $trab->total_incidencias > 0 ? round($eficiencia, 1) . '%' : 'N/A' ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script src="/lugopata/assets/lib/chartjs/dist/chart.umd.min.js"></script>
<script>
// ====== GRÁFICO DE INSUMOS ======
const canvas = document.getElementById('graficoInsumos');
new Chart(canvas, {
    type: 'pie',
    data: {
        labels: ['Cantidad Pedida', 'Cantidad Recibida'],
        datasets: [{
            data: [<?= $totalPedidos ?>, <?= $totalRecibidos ?>],
            backgroundColor: ['rgba(59, 130, 246, 0.8)', 'rgba(16, 185, 129, 0.8)'],
            borderColor: ['rgba(59, 130, 246, 1)', 'rgba(16, 185, 129, 1)'],
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { 
            legend: { position: 'bottom' }, 
            title: { display: true, text: 'Pedidos vs Recibidos' } 
        }
    }
});

// ====== GRÁFICO DE INCIDENCIAS ======
const canvasInc = document.getElementById('graficoIncidencias');
new Chart(canvasInc, {
    type: 'pie',
    data: {
        labels: ['Finalizadas','No finalizadas','Rechazadas'],
        datasets: [{
            data: [<?= $totalFinalizadas ?>,<?= $totalNoFinalizadas ?>,<?= $totalRechazadas ?>],
            backgroundColor: [
                'rgba(16, 185, 129, 0.8)',
                'rgba(245, 158, 11, 0.8)',
                'rgba(239, 68, 68, 0.8)'
            ],
            borderColor: [
                'rgba(16, 185, 129, 1)',
                'rgba(245, 158, 11, 1)',
                'rgba(239, 68, 68, 1)'
            ],
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { 
            legend: { position: 'bottom' }, 
            title: { display: true, text: 'Estado de las Incidencias' } 
        }
    }
});

// ====== GRÁFICO DE PRIORIDADES ======
const ctxPrioridades = document.getElementById('graficoPrioridades');
if (ctxPrioridades) {
    new Chart(ctxPrioridades, {
        type: 'doughnut',
        data: {
            labels: [<?= !empty($estadisticas_prioridad) ? '"' . implode('","', array_map(function($p) { return $p->prioridad; }, $estadisticas_prioridad)) . '"' : '' ?>],
            datasets: [{
                data: [<?= !empty($estadisticas_prioridad) ? implode(',', array_map(function($p) { return $p->total; }, $estadisticas_prioridad)) : '' ?>],
                backgroundColor: [
                    'rgba(239, 68, 68, 0.7)',
                    'rgba(245, 158, 11, 0.7)',
                    'rgba(59, 130, 246, 0.7)'
                ],
                borderColor: [
                    'rgba(239, 68, 68, 1)',
                    'rgba(245, 158, 11, 1)',
                    'rgba(59, 130, 246, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: { display: true, text: 'Distribución por Prioridad' }
            }
        }
    });
}

// ====== GRÁFICO DE CONFORMIDAD ======
const ctxConformidad = document.getElementById('graficoConformidad');
if (ctxConformidad) {
    new Chart(ctxConformidad, {
        type: 'bar',
        data: {
            labels: [<?= !empty($estadisticas_conformidad) ? '"' . implode('","', array_map(function($c) { return $c->calificacion . ' ★'; }, $estadisticas_conformidad)) . '"' : '' ?>],
            datasets: [{
                label: 'Evaluaciones',
                data: [<?= !empty($estadisticas_conformidad) ? implode(',', array_map(function($c) { return $c->total; }, $estadisticas_conformidad)) : '' ?>],
                backgroundColor: 'rgba(245, 158, 11, 0.7)',
                borderColor: 'rgba(245, 158, 11, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    title: { display: true, text: 'Cantidad' }
                }
            },
            plugins: {
                title: { display: true, text: 'Distribución de Calificaciones' }
            }
        }
    });
}

// Lógica para evitar selección simultánea
const categoriaSelect = document.getElementById('categoria');
const insumoSelect = document.getElementById('producto');
if (categoriaSelect && insumoSelect) {
    categoriaSelect.addEventListener('change', () => { 
        if(categoriaSelect.value !== 'todas') insumoSelect.value = 'todos'; 
    });
    insumoSelect.addEventListener('change', () => { 
        if(insumoSelect.value !== 'todos') categoriaSelect.value = 'todas'; 
    });
}
</script>

</body>
</html>
<?php include_once "../pie.php"; ?>