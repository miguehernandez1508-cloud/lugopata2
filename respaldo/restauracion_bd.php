<?php
session_start();
require_once "../conex.php";
require_once "restauracionBD.php";

$mensaje = "";
$tipoMensaje = "";

// Crear instancia de restauración
$restauracionObj = new RestauracionBD($conexion, $host, $usuario, $clave, $bd);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    
    if ($accion === 'subir_respaldo') {
        if (isset($_FILES['archivo_respaldo']) && $_FILES['archivo_respaldo']['error'] === UPLOAD_ERR_OK) {
            $archivoTemporal = $_FILES['archivo_respaldo']['tmp_name'];
            $nombreArchivo = $_FILES['archivo_respaldo']['name'];
            
            $resultado = $restauracionObj->restaurarDesdeArchivo($archivoTemporal, $nombreArchivo);
            $mensaje = $resultado['mensaje'];
            $tipoMensaje = $resultado['success'] ? 'success' : 'error';
        } else {
            $mensaje = "Error al subir el archivo. Asegúrate de seleccionar un archivo SQL válido.";
            $tipoMensaje = 'error';
        }
        
    } elseif ($accion === 'restaurar_lista' && isset($_POST['archivo'])) {
        $archivo = $_POST['archivo'];
        $resultado = $restauracionObj->restaurarDesdeLista($archivo);
        $mensaje = $resultado['mensaje'];
        $tipoMensaje = $resultado['success'] ? 'success' : 'error';
        
    } elseif ($accion === 'eliminar_respaldo' && isset($_POST['archivo'])) {
        $archivo = $_POST['archivo'];
        $resultado = $restauracionObj->eliminarRespaldo($archivo);
        $mensaje = $resultado['mensaje'];
        $tipoMensaje = $resultado['success'] ? 'success' : 'error';
    }
}

// Obtener información
$infoBD = $restauracionObj->obtenerInfoBD();
$respaldos = $restauracionObj->listarRespaldosDisponibles();

// INCLUIR ENCABEZADO DESPUÉS de procesar todo lo crítico
include_once "../encabezado.php";
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
<title>Restauración Base de Datos</title>
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
    .restauracion-wrapper {
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
        border-bottom: 3px solid #17a2b8 !important;
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
        border-left: 5px solid #17a2b8 !important;
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
        color: #17a2b8 !important;
        font-size: clamp(18px, 4vw, 22px) !important;
        font-weight: bold !important;
        display: block !important;
        word-break: break-word !important;
    }

    /* ===== WARNING CARD ===== */
    .warning-card {
        background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%) !important;
        border: 2px solid #f39c12 !important;
        padding: 25px !important;
        border-radius: 12px !important;
        margin-bottom: 25px !important;
        box-shadow: 0 4px 12px rgba(243, 156, 18, 0.2) !important;
        border-left: 5px solid #f39c12 !important;
    }

    .warning-card h3 {
        color: #856404 !important;
        margin-bottom: 15px !important;
        font-size: clamp(18px, 4vw, 22px) !important;
        text-align: center !important;
    }

    .warning-card ul {
        padding-left: 20px !important;
        margin: 15px 0 !important;
        color: #856404 !important;
    }

    .warning-card li {
        margin-bottom: 8px !important;
        line-height: 1.5 !important;
    }

    .warning-card strong {
        color: #856404 !important;
    }

    /* ===== UPLOAD AREA ===== */
    .upload-area {
        border: 3px dashed #17a2b8 !important;
        border-radius: 12px !important;
        padding: 40px 20px !important;
        text-align: center !important;
        margin-bottom: 25px !important;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1) !important;
        transition: all 0.3s ease !important;
    }

    .upload-area:hover {
        border-color: #138496 !important;
        background: linear-gradient(135deg, #e9ecef 0%, #dde2e6 100%) !important;
        box-shadow: 0 6px 16px rgba(0,0,0,0.15) !important;
    }

    .upload-area label {
        font-weight: bold !important;
        display: block !important;
        margin-bottom: 15px !important;
        color: #333 !important;
        font-size: clamp(14px, 3vw, 16px) !important;
    }

    .upload-area label img {
        width: clamp(35px, 8vw, 45px) !important;
        height: auto !important;
        vertical-align: middle !important;
        margin-right: 10px !important;
    }

    /* ===== FILE INPUT ===== */
    .file-input {
        padding: 15px !important;
        border: 2px solid #666 !important;
        border-radius: 8px !important;
        width: 100% !important;
        max-width: 400px !important;
        margin: 20px auto !important;
        background-color: #fff !important;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1) !important;
        transition: all 0.3s ease !important;
        font-weight: bold !important;
        display: block !important;
    }

    .file-input:focus {
        outline: none !important;
        border-color: #17a2b8 !important;
        box-shadow: 0 0 0 4px rgba(23, 162, 184, 0.2) !important;
        transform: scale(1.02) !important;
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

    .btn-subir {
        background: linear-gradient(135deg, #17a2b8 0%, #0c5460 100%) !important;
        border: 2px solid #0c5460 !important;
        padding: 18px 35px !important;
        font-size: clamp(16px, 4vw, 18px) !important;
        margin-top: 15px !important;
    }

    .btn-subir:hover {
        background: linear-gradient(135deg, #138496 0%, #0a3d46 100%) !important;
    }

    .btn-restaurar {
        background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%) !important;
        border: 2px solid #1e7e34 !important;
    }

    .btn-restaurar:hover {
        background: linear-gradient(135deg, #218838 0%, #155724 100%) !important;
    }

    .btn-eliminar {
        background: linear-gradient(135deg, #dc3545 0%, #a71e2a 100%) !important;
        border: 2px solid #a71e2a !important;
    }

    .btn-eliminar:hover {
        background: linear-gradient(135deg, #c82333 0%, #8a1a23 100%) !important;
    }

    .btn-secondary {
        background: linear-gradient(135deg, #6c757d 0%, #495057 100%) !important;
        border: 2px solid #495057 !important;
    }

    .btn-secondary:hover {
        background: linear-gradient(135deg, #545b62 0%, #343a40 100%) !important;
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
      /*  max-height: 500px !important; ===== REVISAR ===== */
        overflow-y: auto !important;
    }

    table {
        width: 100% !important;
        border-collapse: collapse !important;
        min-width: 800px !important;
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
        width: 120px !important;
        height: 40px !important;
        padding: 10px 20px !important;
        font-size: 13px !important;
    }

    /* ===== ENLACE RESPALDO ===== */
    .enlace-respaldo {
        text-align: center !important;
        margin-top: 35px !important;
        padding-top: 25px !important;
        border-top: 2px solid #dee2e6 !important;
    }

    .enlace-respaldo h4 {
        color: #333 !important;
        margin-bottom: 20px !important;
        font-weight: bold !important;
        font-size: clamp(16px, 3vw, 18px) !important;
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

        .restauracion-wrapper {
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

        .btn {
            width: auto !important;
        }

        .file-input {
            width: 80% !important;
        }

        .respaldo-acciones {
            grid-template-columns: 1fr 1fr !important;
        }
    }

    @media (min-width: 1024px) {
        .restauracion-wrapper {
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

        .warning-card {
            padding: 20px 15px !important;
        }

        .upload-area {
            padding: 30px 15px !important;
        }

        .respaldo-card {
            padding: 15px !important;
        }

        .respaldo-acciones {
            grid-template-columns: 1fr !important;
        }
    }
</style>
</head>
<body>
    <div class="restauracion-wrapper">
        <!-- Título -->
        <div class="title-card">
            <h1>
                <img src="../assets/resources/database.png" alt="Database">
                RESTAURACIÓN DE BASE DE DATOS
            </h1>
            <p>Recuperación del sistema desde respaldos - Use con extrema precaución</p>
        </div>
        
        <?php if($mensaje) { ?>
        <div class="alert alert-<?php echo $tipoMensaje === 'success' ? 'success' : 'error'; ?>">
            <?php echo $mensaje; ?>
        </div>
        <?php } ?>

        <!-- Advertencia importante -->
        <div class="content-card">
            <div class="warning-card">
                <h3>¡ ADVERTENCIA IMPORTANTE !</h3>
                <p><strong>La restauración sobreescribirá todos los datos actuales de la base de datos.</strong></p>
                <ul>
                    <li>Se recomienda crear un respaldo antes de realizar cualquier restauración</li>
                    <li>Esta acción no se puede deshacer</li>
                    <li>Asegúrate de tener permisos de administrador</li>
                    <li>El sistema se reiniciará durante el proceso</li>
                </ul>
            </div>
        </div>

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
                        <strong>Respaldos disponibles</strong>
                        <span><?php echo $infoBD['total_respaldos']; ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección 1: Subir nuevo respaldo -->
        <div class="content-card">
            <h2 style="text-align: center; color: #333; margin-bottom: 20px;">
                <img src="../assets/resources/cargando.png" alt="Subir" style="width: 40px; vertical-align: middle; margin-right: 10px;">
                SUBIR RESPALDO DESDE ARCHIVO
            </h2>
            <div class="upload-area">
                <form method="post" enctype="multipart/form-data" id="formSubirRespaldo">
                    <input type="hidden" name="accion" value="subir_respaldo">
                    <div>
                        <label for="archivo_respaldo">
                            <img src="../assets/resources/sql2.png" alt="SQL">
                            Selecciona un archivo de respaldo (.sql):
                        </label>
                        <input type="file" name="archivo_respaldo" id="archivo_respaldo" 
                               accept=".sql" class="file-input" required>
                    </div>
                    <button type="submit" class="btn btn-subir">
                        <img src="../assets/resources/girar-cuadrado.png" alt="Restaurar"  style="filter: brightness(0) invert(1) !important;">
                        RESTAURAR DESDE ARCHIVO
                    </button>
                </form>
            </div>
        </div>

        <!-- Sección 2: Restaurar desde lista existente -->
        <div class="content-card respaldos-section">
            <h2>
                <img src="../assets/resources/intercambio-de-archivos.gif" alt="respaldo">
                RESTAURAR DESDE RESPALDOS EXISTENTES
            </h2>
            
            <?php if (empty($respaldos)): ?>
                <div class="mensaje-sin-datos">
                    <h3>No hay respaldos disponibles para restaurar</h3>
                    <p>Sube un archivo de respaldo usando la sección de arriba.</p>
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
                                <input type="hidden" name="accion" value="restaurar_lista">
                                <input type="hidden" name="archivo" value="<?php echo htmlspecialchars($respaldo['nombre']); ?>">
                                <button type="submit" class="btn btn-restaurar" onclick="return confirmRestaurar('<?php echo htmlspecialchars($respaldo['nombre']); ?>')">
                                    <img src="../assets/resources/girar-cuadrado.png" alt="Restaurar" style="filter: brightness(0) invert(1) !important;"> Restaurar
                                </button>
                            </form>
                            <form method="post" style="display: contents;">
                                <input type="hidden" name="accion" value="eliminar_respaldo">
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
                                <th>Fecha</th>
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
                                            <input type="hidden" name="accion" value="restaurar_lista">
                                            <input type="hidden" name="archivo" value="<?php echo htmlspecialchars($respaldo['nombre']); ?>">
                                            <button type="submit" class="btn btn-restaurar" onclick="return confirmRestaurar('<?php echo htmlspecialchars($respaldo['nombre']); ?>')">
                                                <img src="../assets/resources/girar-cuadrado.png" alt="Restaurar"  style="filter: brightness(0) invert(1) !important;"> Restaurar
                                            </button>
                                        </form>
                                        <form method="post" style="display: inline;">
                                            <input type="hidden" name="accion" value="eliminar_respaldo">
                                            <input type="hidden" name="archivo" value="<?php echo htmlspecialchars($respaldo['nombre']); ?>">
                                            <button type="submit" class="btn btn-eliminar" onclick="return confirmEliminar('<?php echo htmlspecialchars($respaldo['nombre']); ?>')">
                                                <img src="../assets/resources/eliminar2.png" alt="Eliminar" style="filter: brightness(0) invert(1) !important;"> Eliminar
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

        <!-- Enlace a respaldo -->
        <div class="content-card enlace-respaldo">
            <h4>ACCIONES RELACIONADAS</h4>
            <a href="respaldo_bd.php">
                <button type="button" class="btn btn-secondary">
                    <img src="../assets/resources/databaseN.png" alt="Disco" style="filter: brightness(0) invert(1) !important;"> Ir a Crear Respaldo
                </button>
            </a>
        </div>
    </div>

    <script>
    // Función para confirmar restauración
    function confirmRestaurar(nombreArchivo) {
        return confirm('¿ESTÁS SEGURO?\n\nEsta acción RESTAURARÁ la base de datos desde:\n ' + nombreArchivo + '\n\n TODOS los datos actuales se perderán permanentemente.\n\n¿Continuar con la restauración?');
    }

    // Función para confirmar eliminación
    function confirmEliminar(nombreArchivo) {
        return confirm('¿ESTÁS SEGURO?\n\nEsta acción ELIMINARÁ permanentemente el respaldo:\n ' + nombreArchivo + '\n\n Esta acción no se puede deshacer.\n\n¿Continuar con la eliminación?');
    }

    // Confirmación para subir respaldo
    document.getElementById('formSubirRespaldo').addEventListener('submit', function(e) {
        const archivo = document.getElementById('archivo_respaldo').files[0];
        
        if (!archivo) {
            alert('Debes seleccionar un archivo primero.');
            e.preventDefault();
            return;
        }
        
        if (!confirm('¿ESTÁS SEGURO?\n\nEsta acción RESTAURARÁ la base de datos desde:\n ' + archivo.name + '\n\n TODOS los datos actuales se perderán permanentemente.\n\n¿Continuar con la restauración?')) {
            e.preventDefault();
            return;
        }
    });

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

    // Mostrar nombre de archivo seleccionado con mejor feedback
    document.getElementById('archivo_respaldo').addEventListener('change', function(e) {
        const fileName = e.target.files[0] ? e.target.files[0].name : 'No se seleccionó archivo';
        
        // Feedback visual
        this.style.borderColor = '#28a745';
        this.style.boxShadow = '0 0 0 4px rgba(40, 167, 69, 0.2)';
        
        setTimeout(() => {
            this.style.borderColor = '#666';
            this.style.boxShadow = '0 2px 6px rgba(0,0,0,0.1)';
        }, 2000);
        
        console.log('Archivo seleccionado:', fileName);
    });

    // Efecto hover para elementos interactivos
    document.querySelectorAll('button, input').forEach(element => {
        element.addEventListener('mouseenter', function() {
            if (this.tagName === 'INPUT') {
                this.style.transform = 'scale(1.02)';
            }
        });
        element.addEventListener('mouseleave', function() {
            if (this.tagName === 'INPUT' && document.activeElement !== this) {
                this.style.transform = 'scale(1)';
            }
        });
    });
    </script>
</body>
</html>