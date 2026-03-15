<?php
session_start();
require_once "../conex.php";
require_once "respaldoBD.php";

$mensaje = "";
$tipoMensaje = "";

// Crear instancia del respaldo
$respaldoObj = new RespaldoBD($conexion, $host, $usuario, $clave, $bd);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    
    if ($accion === 'crear_respaldo') {
        $resultado = $respaldoObj->crearRespaldo();
        $mensaje = $resultado['mensaje'];
        $tipoMensaje = $resultado['success'] ? 'success' : 'error';
        
    } elseif ($accion === 'descargar' && isset($_POST['archivo'])) {
        $archivo = $_POST['archivo'];
        $resultado = $respaldoObj->descargarRespaldo($archivo);
        
        if ($resultado['success']) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($resultado['ruta']) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($resultado['ruta']));
            readfile($resultado['ruta']);
            exit;
        } else {
            $mensaje = $resultado['mensaje'];
            $tipoMensaje = 'error';
        }
        
    } elseif ($accion === 'eliminar' && isset($_POST['archivo'])) {
        $archivo = $_POST['archivo'];
        $resultado = $respaldoObj->eliminarRespaldo($archivo);
        $mensaje = $resultado['mensaje'];
        $tipoMensaje = $resultado['success'] ? 'success' : 'error';
    }
}

// Obtener información (DESPUÉS de procesar POST)
$infoBD = $respaldoObj->obtenerInfoBD();
$respaldos = $respaldoObj->listarRespaldos();

// INCLUIR ENCABEZADO DESPUÉS de procesar todo lo crítico
include_once "../encabezado.php";
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
<title>Respaldo Base de Datos</title>
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
    .respaldo-wrapper {
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
        filter: drop-shadow(1px 1px 2px rgba(0,0,0,0.3)) !important;
    }

    .title-card p {
        color: #666 !important;
        margin-top: 10px !important;
        font-size: clamp(14px, 2vw, 16px) !important;
    }

    /* ===== ALERTAS ===== */
    .alert {
        padding: 18px !important;
        border-radius: 10px !important;
        margin-bottom: 25px !important;
        text-align: center !important;
        font-weight: bold !important;
        font-size: clamp(14px, 3vw, 16px) !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
        animation: slideIn 0.5s ease-out !important;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .alert-success {
        background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%) !important;
        color: white !important;
        border: 2px solid #1e7e34 !important;
    }

    .alert-error {
        background: linear-gradient(135deg, #dc3545 0%, #a71e2a 100%) !important;
        color: white !important;
        border: 2px solid #a71e2a !important;
    }

    /* ===== TARJETAS DE CONTENIDO ===== */
    .content-card {
        background: white !important;
        border-radius: 15px !important;
        padding: 25px !important;
        border: 2px solid #ccc !important;
        width: 100% !important;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1) !important;
        margin-bottom: 20px !important;
    }

    /* ===== INFO CARD ===== */
    .info-card {
        background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%) !important;
        padding: 20px !important;
        border-radius: 12px !important;
        margin-bottom: 25px !important;
        border: 2px solid #ddd !important;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1) !important;
        border-left: 5px solid #28a745 !important;
    }

    .info-card h3 {
        color: #333 !important;
        margin-bottom: 20px !important;
        font-size: clamp(16px, 3vw, 20px) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 10px !important;
        flex-wrap: wrap !important;
    }

    .info-card h3 img {
        width: clamp(30px, 6vw, 40px) !important;
        height: auto !important;
    }

    /* ===== INFO GRID ===== */
    .info-grid {
        display: grid !important;
        grid-template-columns: 1fr !important;
        gap: 15px !important;
    }

    .info-item {
        background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%) !important;
        padding: 20px !important;
        border-radius: 10px !important;
        text-align: center !important;
        border: 2px solid #dee2e6 !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
        transition: all 0.3s ease !important;
    }

    .info-item:hover {
        transform: translateY(-5px) !important;
        box-shadow: 0 6px 12px rgba(0,0,0,0.15) !important;
    }

    .info-item strong {
        color: #333 !important;
        font-size: 14px !important;
        display: block !important;
        margin-bottom: 10px !important;
    }

    .info-item span {
        color: #28a745 !important;
        font-size: clamp(18px, 4vw, 22px) !important;
        font-weight: bold !important;
        display: block !important;
        word-break: break-word !important;
    }

    /* ===== BACKUP HEADER ===== */
    .backup-header {
        text-align: center !important;
        margin: 30px 0 !important;
        padding: 25px !important;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
        border-radius: 12px !important;
        border: 2px solid #28a745 !important;
    }

    .backup-header h2 {
        color: #333 !important;
        margin-bottom: 20px !important;
        font-size: clamp(18px, 4vw, 24px) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 10px !important;
        flex-wrap: wrap !important;
    }

    .backup-header h2 img {
        width: clamp(30px, 6vw, 40px) !important;
        height: auto !important;
    }

    .backup-header p {
        color: #666 !important;
        font-size: 14px !important;
        font-style: italic !important;
        margin-top: 15px !important;
    }

    /* ===== BOTONES ===== */
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
        transform: translateY(-3px) !important;
        box-shadow: 0 8px 16px rgba(0,0,0,0.25) !important;
        opacity: 0.95 !important;
    }

    .btn:active {
        transform: translateY(-1px) !important;
    }

    .btn-crear {
        background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%) !important;
        border: 2px solid #1e7e34 !important;
        padding: 18px 40px !important;
        font-size: clamp(16px, 4vw, 20px) !important;
    }

    .btn-crear:hover {
        background: linear-gradient(135deg, #218838 0%, #155724 100%) !important;
    }

    .btn-descargar {
        background: linear-gradient(135deg, #0d6efd, #0a58ca) !important;
        border: 2px solid #0c5460 !important;
    }

    .btn-descargar:hover {
        background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%) !important;
    }

    .btn-eliminar {
        background: linear-gradient(135deg, #dc3545 0%, #a71e2a 100%) !important;
        border: 2px solid #a71e2a !important;
    }

    .btn-eliminar:hover {
        background: linear-gradient(135deg, #c82333 0%, #8a1a23 100%) !important;
    }

    .btn img {
        width: 20px !important;
        height: 20px !important;
        flex-shrink: 0 !important;
    }

    /* ===== SECCIÓN RESPALDOS ===== */
    .respaldos-section h2 {
        color: #333 !important;
        margin-bottom: 25px !important;
        font-size: clamp(18px, 4vw, 24px) !important;
        text-align: center !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 10px !important;
        flex-wrap: wrap !important;
    }

    .respaldos-section h2 img {
        width: clamp(30px, 6vw, 40px) !important;
        height: auto !important;
    }

    /* ===== GRID DE TARJETAS (VISTA MÓVIL) ===== */
    .respaldos-grid {
        display: grid !important;
        grid-template-columns: 1fr !important;
        gap: 15px !important;
        margin-top: 20px !important;
    }

    .respaldo-card {
        background: #f8f9fa !important;
        border-radius: 12px !important;
        padding: 20px !important;
        border: 2px solid #dee2e6 !important;
        transition: all 0.3s ease !important;
        position: relative !important;
        overflow: hidden !important;
    }

    .respaldo-card:hover {
        transform: translateY(-3px) !important;
        box-shadow: 0 8px 20px rgba(0,0,0,0.1) !important;
        border-color: #17a2b8 !important;
    }

    .respaldo-card::before {
        content: '' !important;
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        bottom: 0 !important;
        width: 5px !important;
        background: #17a2b8 !important;
    }

    .respaldo-header {
        margin-bottom: 15px !important;
    }

    .respaldo-nombre {
        font-size: clamp(14px, 3vw, 16px) !important;
        font-weight: bold !important;
        color: #333 !important;
        word-break: break-all !important;
        margin-bottom: 10px !important;
    }

    .respaldo-meta {
        display: flex !important;
        justify-content: space-between !important;
        flex-wrap: wrap !important;
        gap: 10px !important;
        font-size: 13px !important;
        color: #666 !important;
    }

    .respaldo-fecha {
        color: #666 !important;
    }

    .respaldo-tamano {
        color: #17a2b8 !important;
        font-weight: bold !important;
    }

    .respaldo-acciones {
        display: grid !important;
        grid-template-columns: 1fr 1fr !important;
        gap: 10px !important;
        margin-top: 15px !important;
        padding-top: 15px !important;
        border-top: 2px solid #dee2e6 !important;
    }

    .respaldo-acciones .btn {
        padding: 12px 8px !important;
        font-size: 12px !important;
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
       /* max-height: 500px !important;  ===== REVISAR ===== */
        overflow-y: auto !important;
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

    tr:hover {
        background-color: #f8f9fa !important;
    }

    .action-buttons {
        display: flex !important;
        gap: 10px !important;
        justify-content: center !important;
        flex-wrap: wrap !important;
    }

    .action-buttons .btn {
        width: 130px !important;
        height: 40px !important;
        padding: 10px 20px !important;
        font-size: 13px !important;
    }

    /* ===== ESTADÍSTICAS ===== */
    .stats-container {
        display: grid !important;
        grid-template-columns: 1fr !important;
        gap: 20px !important;
        margin: 40px 0 !important;
    }

    .stats-card {
        background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%) !important;
        padding: 25px !important;
        border-radius: 12px !important;
        text-align: center !important;
        border: 2px solid #28a745 !important;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1) !important;
        transition: all 0.3s ease !important;
    }

    .stats-card:hover {
        transform: translateY(-8px) !important;
        box-shadow: 0 8px 16px rgba(0,0,0,0.2) !important;
    }

    .stats-card h4 {
        color: #333 !important;
        margin-bottom: 15px !important;
        font-weight: bold !important;
        font-size: clamp(14px, 3vw, 16px) !important;
    }

    .stats-card p {
        font-size: clamp(24px, 6vw, 32px) !important;
        font-weight: bold !important;
        margin: 0 !important;
        color: #28a745 !important;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.1) !important;
    }

    .stats-card .date {
        font-size: clamp(14px, 3vw, 16px) !important;
        color: #666 !important;
        font-weight: normal !important;
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

        .respaldo-wrapper {
            padding: 15px !important;
        }

        .info-grid {
            grid-template-columns: repeat(3, 1fr) !important;
        }

        .respaldos-grid {
            display: none !important;
        }

        .table-container {
            display: block !important;
        }

        .stats-container {
            grid-template-columns: repeat(3, 1fr) !important;
        }

        .btn {
            width: auto !important;
        }

        .respaldo-acciones {
            grid-template-columns: 1fr 1fr !important;
        }
    }

    @media (min-width: 1024px) {
        .respaldo-wrapper {
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

        .info-card {
            padding: 15px !important;
        }

        .backup-header {
            padding: 20px 15px !important;
        }

        .respaldo-card {
            padding: 15px !important;
        }

        .respaldo-acciones {
            grid-template-columns: 1fr !important;
        }

        .stats-card {
            padding: 20px !important;
        }
    }
</style>
</head>
<body>
    <div class="respaldo-wrapper">
        <!-- Título -->
        <div class="title-card">
            <h1>
                <img src="../assets/resources/backup.png" alt="Respaldo">
                RESPALDO DE BASE DE DATOS
            </h1>
            <p>Protección de datos del sistema - Creación y gestión de copias de seguridad</p>
        </div>
        
        <?php if($mensaje) { ?>
        <div class="alert alert-<?php echo $tipoMensaje === 'success' ? 'success' : 'error'; ?>">
            <?php echo $mensaje; ?>
        </div>
        <?php } ?>

        <!-- Información de la BD -->
        <div class="content-card">
            <div class="info-card">
                <h3>
                    <img src="../assets/resources/panel.png" alt="Panel">
                    INFORMACIÓN DEL SISTEMA
                </h3>
                <div class="info-grid">
                    <div class="info-item">
                        <strong>Base de datos</strong>
                        <span><?php echo htmlspecialchars($infoBD['nombre']); ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Servidor</strong>
                        <span><?php echo htmlspecialchars($infoBD['servidor']); ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Total de respaldos</strong>
                        <span><?php echo count($respaldos); ?></span>
                    </div>
                </div>
            </div>

            <!-- Crear nuevo respaldo -->
            <div class="backup-header">
                <h2>
                    <img src="../assets/resources/guardar2.png" alt="Salvar">
                    CREAR NUEVO RESPALDO
                </h2>
                <form method="post" id="formCrearRespaldo">
                    <input type="hidden" name="accion" value="crear_respaldo">
                    <button type="submit" class="btn btn-crear">
                        CREAR NUEVO RESPALDO
                    </button>
                    <p>Se generará una copia de seguridad completa de la base de datos</p>
                </form>
            </div>
        </div>

        <!-- Lista de respaldos -->
        <div class="content-card respaldos-section">
            <h2>
                <img src="../assets/resources/intercambio-de-archivos.gif" alt="respaldo">
                RESPALDOS DISPONIBLES
            </h2>
            
            <?php if (empty($respaldos)): ?>
                <div class="mensaje-sin-datos">
                    <h3>No hay respaldos creados aún</h3>
                    <p>Crea el primero ahora usando el botón de arriba.</p>
                </div>
            <?php else: ?>
                <!-- Vista Grid para Móvil -->
                <div class="respaldos-grid">
                    <?php foreach ($respaldos as $respaldo): ?>
                    <div class="respaldo-card">
                        <div class="respaldo-header">
                            <div class="respaldo-nombre"><?php echo htmlspecialchars($respaldo['nombre']); ?></div>
                            <div class="respaldo-meta">
                                <span class="respaldo-fecha"><?php echo $respaldo['fecha']; ?></span>
                                <span class="respaldo-tamano"><?php echo number_format($respaldo['tamano'] / 1024, 2); ?> KB</span>
                            </div>
                        </div>
                        
                        <div class="respaldo-acciones">
                            <form method="post" style="display: contents;">
                                <input type="hidden" name="accion" value="descargar">
                                <input type="hidden" name="archivo" value="<?php echo htmlspecialchars($respaldo['nombre']); ?>">
                                <button type="submit" class="btn btn-descargar" onclick="return confirmDescargar('<?php echo htmlspecialchars($respaldo['nombre']); ?>')">
                                    <img src="../assets/resources/download1.png" alt="Descargar" style="filter: brightness(0) invert(1) !important;"> Descargar
                                </button>
                            </form>
                            
                            <form method="post" style="display: contents;">
                                <input type="hidden" name="accion" value="eliminar">
                                <input type="hidden" name="archivo" value="<?php echo htmlspecialchars($respaldo['nombre']); ?>">
                                <button type="submit" class="btn btn-eliminar" onclick="return confirmEliminar('<?php echo htmlspecialchars($respaldo['nombre']); ?>')">
                                    <img src="../assets/resources/eliminar2.png" alt="Eliminar" style="filter: brightness(0) invert(1) !important;"> Eliminar
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Vista Tabla para Escritorio -->
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Nombre del Archivo</th>
                                <th>Fecha de Creación</th>
                                <th>Tamaño</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($respaldos as $respaldo): ?>
                            <tr>
                                <td style="font-weight: bold; color: #333;"><?php echo htmlspecialchars($respaldo['nombre']); ?></td>
                                <td style="color: #666;"><?php echo $respaldo['fecha']; ?></td>
                                <td style="color: #17a2b8; font-weight: bold;"><?php echo number_format($respaldo['tamano'] / 1024, 2); ?> KB</td>
                                <td>
                                    <div class="action-buttons">
                                        <form method="post" style="display: inline;">
                                            <input type="hidden" name="accion" value="descargar">
                                            <input type="hidden" name="archivo" value="<?php echo htmlspecialchars($respaldo['nombre']); ?>">
                                            <button type="submit" class="btn btn-descargar" onclick="return confirmDescargar('<?php echo htmlspecialchars($respaldo['nombre']); ?>')">
                                                <img src="../assets/resources/download1.png" alt="Descargar"  style="filter: brightness(0) invert(1) !important;"> Descargar
                                            </button>
                                        </form>
                                        
                                        <form method="post" style="display: inline;">
                                            <input type="hidden" name="accion" value="eliminar">
                                            <input type="hidden" name="archivo" value="<?php echo htmlspecialchars($respaldo['nombre']); ?>">
                                            <button type="submit" class="btn btn-eliminar" onclick="return confirmEliminar('<?php echo htmlspecialchars($respaldo['nombre']); ?>')">
                                                <img src="../assets/resources/eliminar2.png" alt="Eliminar"  style="filter: brightness(0) invert(1) !important;"> Eliminar
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
        </div>

        <!-- Estadísticas -->
        <div class="content-card">
            <div class="stats-container">
                <div class="stats-card">
                    <h4>Total Respaldos</h4>
                    <p><?php echo count($respaldos); ?></p>
                </div>
                <div class="stats-card">
                    <h4>Último Respaldo</h4>
                    <p class="date"><?php echo !empty($respaldos) ? $respaldos[0]['fecha'] : 'N/A'; ?></p>
                </div>
                <div class="stats-card">
                    <h4>Tamaño Total</h4>
                    <p>
                        <?php 
                        $totalSize = 0;
                        foreach ($respaldos as $respaldo) {
                            $totalSize += $respaldo['tamano'];
                        }
                        echo number_format($totalSize / (1024*1024), 2) . ' MB'; 
                        ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Función para confirmar creación de respaldo
    document.getElementById('formCrearRespaldo').addEventListener('submit', function(e) {
        if (!confirm('¿ESTÁS SEGURO DE CREAR UN NUEVO RESPALDO?\n\n Esta acción puede tomar algunos minutos dependiendo del tamaño de la base de datos.\n\n Se generará una copia de seguridad completa del sistema.')) {
            e.preventDefault();
            return;
        }
    });

    // Función para confirmar descarga
    function confirmDescargar(nombreArchivo) {
        return confirm('¿Descargar el respaldo?\n\n Archivo: ' + nombreArchivo + '\n\n El archivo se descargará a tu computadora en formato SQL.');
    }

    // Función para confirmar eliminación
    function confirmEliminar(nombreArchivo) {
        return confirm('¿ESTÁS SEGURO?\n\nEsta acción ELIMINARÁ permanentemente el respaldo:\n ' + nombreArchivo + '\n\n Esta acción no se puede deshacer.\n\n¿Continuar con la eliminación?');
    }

    // Auto-ocultar mensaje después de 8 segundos
    setTimeout(function() {
        const alert = document.querySelector('.alert');
        if (alert) {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.5s ease';
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.style.display = 'none';
                }
            }, 500);
        }
    }, 8000);

    // Efecto hover para botones
    document.querySelectorAll('button').forEach(element => {
        element.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-3px)';
        });
        element.addEventListener('mouseleave', function() {
            if (document.activeElement !== this) {
                this.style.transform = 'translateY(0)';
            }
        });
    });

    // Feedback visual al crear respaldo
    document.getElementById('formCrearRespaldo').addEventListener('submit', function() {
        const btn = this.querySelector('.btn-crear');
        
        btn.innerHTML = ' CREANDO RESPALDO...';
        btn.disabled = true;
        btn.style.opacity = '0.7';
        btn.style.cursor = 'wait';
    });
    </script>
</body>
</html>