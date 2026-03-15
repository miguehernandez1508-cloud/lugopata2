<?php
session_start();
include_once "../encabezado.php";
require_once "gestorsesion.php";
require_once "../conex.php";

GestorSesiones::iniciar();

$registrosPorPagina = 10;

$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
if ($pagina < 1) $pagina = 1;

$inicio = ($pagina - 1) * $registrosPorPagina;

$sqlTotal = "
    SELECT COUNT(*) 
    FROM auditoria a
    JOIN usuarios u ON a.usuario = u.username
    WHERE u.nivel <> 'superadministrador'
";
$totalRegistros = $conexion->query($sqlTotal)->fetchColumn();
$totalPaginas = ceil($totalRegistros / $registrosPorPagina);

$sentencia = $conexion->prepare("
    SELECT a.* 
    FROM auditoria a
    JOIN usuarios u ON a.usuario = u.username
    WHERE u.nivel <> 'superadministrador'
    ORDER BY a.fecha DESC
    LIMIT :inicio, :registros
");
$sentencia->bindValue(':inicio', $inicio, PDO::PARAM_INT);
$sentencia->bindValue(':registros', $registrosPorPagina, PDO::PARAM_INT);
$sentencia->execute();
$auditoria = $sentencia->fetchAll(PDO::FETCH_OBJ);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Registro de Auditoría</title>
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
        .auditoria-wrapper {
            max-width: 1200px !important;
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

        /* ===== TARJETA DE CONTENIDO ===== */
        .content-card {
            background: white !important;
            border-radius: 15px !important;
            padding: 25px !important;
            border: 2px solid #ccc !important;
            width: 100% !important;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1) !important;
        }

        /* ===== CONTADOR ===== */
        .contador-registros {
            background-color: #e7f1ff !important;
            color: #0d6efd !important;
            padding: 10px 20px !important;
            border-radius: 30px !important;
            font-weight: bold !important;
            display: inline-block !important;
            margin-bottom: 20px !important;
            border: 2px solid #cfe2ff !important;
            font-size: clamp(14px, 2vw, 16px) !important;
        }

        /* ===== GRID DE TARJETAS (VISTA MÓVIL) ===== */
        .auditoria-grid {
            display: grid !important;
            grid-template-columns: 1fr !important;
            gap: 15px !important;
            margin-top: 20px !important;
        }

        .auditoria-card {
            background: #f8f9fa !important;
            border-radius: 12px !important;
            padding: 20px !important;
            border: 2px solid #dee2e6 !important;
            transition: all 0.3s ease !important;
            position: relative !important;
            overflow: hidden !important;
        }

        .auditoria-card:hover {
            transform: translateY(-3px) !important;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1) !important;
            border-color: #0d6efd !important;
        }

        .auditoria-card::before {
            content: '' !important;
            position: absolute !important;
            left: 0 !important;
            top: 0 !important;
            bottom: 0 !important;
            width: 5px !important;
            background: #0d6efd !important;
        }

        .auditoria-header {
            display: flex !important;
            justify-content: space-between !important;
            align-items: flex-start !important;
            margin-bottom: 15px !important;
            flex-wrap: wrap !important;
            gap: 10px !important;
        }

        .auditoria-id {
            font-size: clamp(18px, 4vw, 22px) !important;
            font-weight: bold !important;
            color: #0d6efd !important;
            font-family: monospace !important;
        }

        .auditoria-fecha {
            font-size: 12px !important;
            color: #6c757d !important;
            background: white !important;
            padding: 5px 10px !important;
            border-radius: 20px !important;
            border: 1px solid #dee2e6 !important;
            font-family: monospace !important;
        }

        .auditoria-body {
            margin-bottom: 15px !important;
        }

        .badge-usuario {
            display: inline-block !important;
            padding: 6px 12px !important;
            border-radius: 20px !important;
            font-weight: bold !important;
            font-size: 12px !important;
            margin-bottom: 10px !important;
            background-color: #0d6efd !important;
            color: white !important;
        }

        .auditoria-accion {
            color: #555 !important;
            font-size: clamp(14px, 3vw, 15px) !important;
            line-height: 1.5 !important;
            margin-top: 10px !important;
            padding: 15px !important;
            background: white !important;
            border-radius: 8px !important;
            border: 1px solid #e9ecef !important;
            word-wrap: break-word !important;
            text-align: left !important;
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
            min-width: 900px !important;
        }

        th, td {
            border: 1px solid #dee2e6 !important;
            padding: 12px !important;
            text-align: center !important;
            background-color: #fff !important;
            white-space: nowrap !important;
            vertical-align: middle !important;
            font-size: 14px !important;
        }

        th {
            background-color: #cfe2ff !important;
            font-weight: bold !important;
            color: #333 !important;
            position: sticky !important;
            top: 0 !important;
        }

        tbody tr:nth-child(even) {
            background-color: #f8f9fa !important;
        }

        tbody tr:hover {
            background-color: #e9ecef !important;
        }

        .id-auditoria {
            font-weight: bold !important;
            color: #0d6efd !important;
            font-family: monospace !important;
        }

        .fecha-auditoria {
            font-size: 12px !important;
            color: #666 !important;
            font-family: monospace !important;
        }

        .accion-auditoria {
            font-weight: 500 !important;
            color: #333 !important;
            max-width: 400px !important;
            white-space: normal !important;
            text-align: left !important;
            padding: 5px !important;
        }

        /* ===== PAGINACIÓN ===== */
        .paginacion-container {
            margin-top: 25px !important;
            padding-top: 20px !important;
            border-top: 2px solid #dee2e6 !important;
            text-align: center !important;
        }

        .paginacion-flex {
            display: flex !important;
            justify-content: center !important;
            align-items: center !important;
            flex-wrap: wrap !important;
            gap: 10px !important;
            margin-bottom: 10px !important;
        }

        .btn-paginacion {
            padding: 10px 20px !important;
            background-color: #0d6efd !important;
            color: white !important;
            text-decoration: none !important;
            border-radius: 8px !important;
            font-weight: bold !important;
            font-size: 14px !important;
            border: none !important;
            cursor: pointer !important;
            transition: all 0.3s ease !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 5px !important;
        }

        .btn-paginacion:hover {
            background-color: #0056b3 !important;
            transform: translateY(-2px) !important;
        }

        .btn-paginacion.disabled {
            background-color: #6c757d !important;
            pointer-events: none !important;
            opacity: 0.6 !important;
        }

        .paginacion-numerica {
            display: flex !important;
            gap: 5px !important;
            flex-wrap: wrap !important;
            justify-content: center !important;
        }

        .pagina-activa {
            background-color: #0d6efd !important;
            color: white !important;
            padding: 8px 14px !important;
            border-radius: 6px !important;
            font-weight: bold !important;
            font-size: 14px !important;
        }

        .pagina-inactiva {
            background-color: #f8f9fa !important;
            color: #666 !important;
            padding: 8px 14px !important;
            border-radius: 6px !important;
            text-decoration: none !important;
            border: 1px solid #dee2e6 !important;
            font-size: 14px !important;
            transition: all 0.2s ease !important;
        }

        .pagina-inactiva:hover {
            background-color: #e9ecef !important;
            border-color: #0d6efd !important;
            color: #0d6efd !important;
        }

        .paginacion-info {
            color: #666 !important;
            font-size: 14px !important;
            margin-top: 10px !important;
        }

        .paginacion-ellipsis {
            color: #666 !important;
            padding: 8px 5px !important;
            font-weight: bold !important;
        }

        /* ===== BOTONES ===== */
        .btn {
            padding: 12px 25px !important;
            color: white !important;
            border: none !important;
            border-radius: 8px !important;
            cursor: pointer !important;
            font-size: clamp(14px, 3vw, 15px) !important;
            font-weight: bold !important;
            text-decoration: none !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 8px !important;
            transition: all 0.3s ease !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
        }

        .btn:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2) !important;
            opacity: 0.95 !important;
        }

        .btn-secondary {
            background-color: #6c757d !important;
        }

        .btn-icon {
            width: 20px !important;
            height: 20px !important;
            flex-shrink: 0 !important;
            filter: brightness(0) invert(1) !important;
        }

        /* ===== BOTÓN REGRESAR ===== */
        .boton-regresar-container {
            text-align: center !important;
            margin-top: 30px !important;
            padding-top: 20px !important;
            border-top: 2px solid #dee2e6 !important;
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

            .auditoria-wrapper {
                padding: 15px !important;
            }

            .auditoria-grid {
                display: none !important;
            }

            .table-container {
                display: block !important;
            }

            .btn {
                width: auto !important;
            }

            .auditoria-card {
                padding: 25px !important;
            }
        }

        @media (min-width: 1024px) {
            .auditoria-wrapper {
                max-width: 1400px !important;
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

            .auditoria-card {
                padding: 15px !important;
            }

            .auditoria-header {
                flex-direction: column !important;
                align-items: flex-start !important;
            }

            .paginacion-flex {
                flex-direction: column !important;
                width: 100% !important;
            }

            .btn-paginacion {
                width: 100% !important;
                justify-content: center !important;
            }

            .paginacion-numerica {
                order: -1 !important;
                width: 100% !important;
            }
        }
    </style>
</head>
<body>
    <div class="auditoria-wrapper">
        <!-- Título -->
        <div class="title-card">
            <h1>
                <img src="../assets/resources/cuaderno.png" alt="Auditoría">
                REGISTRO DE AUDITORÍA
            </h1>
            <p>Historial completo de actividades realizadas en el sistema</p>
        </div>

        <!-- Contenido -->
        <div class="content-card">
            <!-- Contador de registros -->
            <div style="text-align: center;">
                <span class="contador-registros">
                    Total de registros: <?= $totalRegistros ?>
                </span>
            </div>

            <?php if (empty($auditoria)): ?>
                <!-- Sin datos -->
                <div class="mensaje-sin-datos">
                    <h3>No hay registros de auditoría disponibles</h3>
                    <p>No se encontraron actividades registradas en el sistema.</p>
                </div>
            <?php else: ?>
                <!-- Vista Grid para Móvil -->
                <div class="auditoria-grid">
                    <?php foreach ($auditoria as $a): ?>
                    <div class="auditoria-card">
                        <div class="auditoria-header">
                            <div>
                                <div class="auditoria-id">#<?= $a->id_auditoria ?></div>
                                <span class="badge-usuario" style="margin-top: 8px; display: inline-block;">
                                    <?= htmlspecialchars($a->usuario) ?>
                                </span>
                            </div>
                            <div class="auditoria-fecha"><?= date('d/m/Y H:i', strtotime($a->fecha)) ?></div>
                        </div>
                        
                        <div class="auditoria-body">
                            <div class="auditoria-accion">
                                <?= htmlspecialchars($a->accion) ?>
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
                                <th>Acción</th>
                                <th>Usuario</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($auditoria as $a): ?>
                            <tr>
                                <td class="id-auditoria">#<?= $a->id_auditoria ?></td>
                                <td class="accion-auditoria"><?= htmlspecialchars($a->accion) ?></td>
                                <td><span class="badge-usuario"><?= htmlspecialchars($a->usuario) ?></span></td>
                                <td><span class="fecha-auditoria"><?= date('d-m-Y H:i:s', strtotime($a->fecha)) ?></span></td>
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
                        <a href="?pagina=<?= $pagina - 1 ?>" class="btn-paginacion">← Anterior</a>
                    <?php else: ?>
                        <span class="btn-paginacion disabled">← Anterior</span>
                    <?php endif; ?>
                    
                    <div class="paginacion-numerica">
                        <?php 
                        $inicioPaginas = max(1, $pagina - 2);
                        $finPaginas = min($totalPaginas, $pagina + 2);
                        
                        if ($inicioPaginas > 1) {
                            echo '<a href="?pagina=1" class="pagina-inactiva">1</a>';
                            if ($inicioPaginas > 2) echo '<span class="paginacion-ellipsis">...</span>';
                        }
                        
                        for ($i = $inicioPaginas; $i <= $finPaginas; $i++): 
                            if ($pagina == $i): ?>
                                <span class="pagina-activa"><?= $i ?></span>
                            <?php else: ?>
                                <a href="?pagina=<?= $i ?>" class="pagina-inactiva"><?= $i ?></a>
                            <?php endif;
                        endfor;
                        
                        if ($finPaginas < $totalPaginas) {
                            if ($finPaginas < $totalPaginas - 1) echo '<span class="paginacion-ellipsis">...</span>';
                            echo '<a href="?pagina=' . $totalPaginas . '" class="pagina-inactiva">' . $totalPaginas . '</a>';
                        }
                        ?>
                    </div>
                    
                    <?php if ($pagina < $totalPaginas): ?>
                        <a href="?pagina=<?= $pagina + 1 ?>" class="btn-paginacion">Siguiente →</a>
                    <?php else: ?>
                        <span class="btn-paginacion disabled">Siguiente →</span>
                    <?php endif; ?>
                </div>
                
                <div class="paginacion-info">
                    Página <?= $pagina ?> de <?= $totalPaginas ?> | Mostrando <?= (($pagina - 1) * $registrosPorPagina) + 1 ?> - <?= min($pagina * $registrosPorPagina, $totalRegistros) ?> de <?= $totalRegistros ?> registros
                </div>
            </div>
            <?php endif; ?>

            <!-- Botón de regresar -->
            <div class="boton-regresar-container">
                <button onclick="history.back()" type="button" class="btn btn-secondary">
                    <img src="../assets/resources/volver2.png" alt="Regresar" class="btn-icon">
                    Regresar
                </button>
            </div>
        </div>
    </div>

    <script>
    // Efectos hover para botones
    document.querySelectorAll('.btn, .btn-paginacion').forEach(btn => {
        btn.addEventListener('mouseenter', function() {
            if (!this.classList.contains('disabled')) {
                this.style.transform = 'translateY(-2px)';
            }
        });
        
        btn.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
    
    // Click en acción para expandir/contraer si es muy larga (solo en móvil)
    document.querySelectorAll('.auditoria-accion').forEach(accion => {
        if (accion.textContent.length > 80) {
            accion.style.cursor = 'pointer';
            accion.title = 'Toca para expandir/contraer';
            
            let expanded = false;
            
            accion.addEventListener('click', function() {
                if (!expanded) {
                    this.style.maxHeight = 'none';
                    expanded = true;
                } else {
                    this.style.maxHeight = '200px';
                    expanded = false;
                }
            });
        }
    });
    </script>
</body>
</html>