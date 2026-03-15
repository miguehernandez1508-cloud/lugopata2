<?php
session_start();
require_once "../encabezado.php";
require_once "../conex.php";
require_once "../user/gestorsesion.php";
require_once "../trabajador/trabajador.php";

GestorSesiones::iniciar();

if (!isset($_GET['id'])) exit("No se especificó la solicitud.");
$id = (int)$_GET['id'];

// Verificar sesión
if (!isset($_SESSION['nivel'])) {
    header("Location: /lugopata/index.php");
    exit;
}

// Obtener solicitud principal
$sentencia = $conexion->prepare("
    SELECT s.*, 
           de.nombre AS depto_emisor, 
           dr.nombre AS depto_destino
    FROM solicitud_materiales s
    INNER JOIN departamentos de ON s.departamento_emisor = de.id_departamento
    INNER JOIN departamentos dr ON s.departamento_destino = dr.id_departamento
    WHERE s.id_solicitud = ?
");
$sentencia->execute([$id]);
$solicitud = $sentencia->fetch(PDO::FETCH_OBJ);
if (!$solicitud) exit("Solicitud no encontrada.");

// Detalle de insumos
$sentenciaDet = $conexion->prepare("
    SELECT d.*, i.nombre, i.descripcion, i.unidad_medida, i.imagen
    FROM detalle_solicitud_material d
    INNER JOIN insumos i ON d.id_insumo = i.id_insumo
    WHERE d.id_solicitud = ?
");
$sentenciaDet->execute([$id]);
$detalles = $sentenciaDet->fetchAll(PDO::FETCH_OBJ);

// Aprobador
$sentenciaA = $conexion->prepare("
    SELECT CONCAT(t.nombre, ' ', t.apellido) AS nombre_aprobador
    FROM solicitud_materiales s
    LEFT JOIN usuarios u ON s.id_aprobador = u.id_usuario
    LEFT JOIN trabajadores t ON u.id_trabajador = t.id_trabajador
    WHERE s.id_solicitud = ?
");
$sentenciaA->execute([$id]);
$aprobador = $sentenciaA->fetch(PDO::FETCH_OBJ);

$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $idUsuario   = $_SESSION['id_usuario'] ?? null;
        $usuarioNom  = $_SESSION['username']   ?? 'desconocido';
        $firma       = $_SESSION['firma']      ?? null;
        $razon       = trim($_POST['razon']    ?? '');

        /* ---------- APROBAR ---------- */
        if (isset($_POST['aprobar'])) {
            if ($idUsuario && ($solicitud->estado === 'En espera' || !$solicitud->estado)) {
                $upd = $conexion->prepare("
                    UPDATE solicitud_materiales
                    SET estado = 'Pendiente',
                        id_aprobador = ?,
                        razon_solicitud = ?
                    WHERE id_solicitud = ?
                ");
                $upd->execute([$idUsuario, $razon, $id]);

                // Firma en detalles
                $conexion->prepare("
                    UPDATE detalle_solicitud_material
                    SET firma_aprobador = ?
                    WHERE id_solicitud = ?
                ")->execute([$firma, $id]);

                // Auditoría
                $conexion->prepare("
                    INSERT INTO auditoria (accion, usuario, detalle)
                    VALUES (?, ?, ?)
                ")->execute(['Aprobación de solicitud', $usuarioNom,
                             "$usuarioNom aprobó solicitud $id: $razon"]);

                $mensaje = "Solicitud aprobada exitosamente.";
            } else {
                $mensaje = "La solicitud ya fue aprobada o no está en espera.";
            }
        }

        /* ---------- RECHAZAR ---------- */
        if (isset($_POST['rechazar'])) {
            if ($idUsuario && ($solicitud->estado === 'En espera' || !$solicitud->estado)) {
                $upd = $conexion->prepare("
                    UPDATE solicitud_materiales
                    SET estado = 'Rechazada',
                        id_aprobador = ?,
                        razon_solicitud = ?
                    WHERE id_solicitud = ?
                ");
                $upd->execute([$idUsuario, $razon, $id]);

                // Auditoría
                $conexion->prepare("
                    INSERT INTO auditoria (accion, usuario, detalle)
                    VALUES (?, ?, ?)
                ")->execute(['Rechazo de solicitud', $usuarioNom,
                             "$usuarioNom rechazó solicitud $id: $razon"]);

                $mensaje = "Solicitud rechazada.";
            } else {
                $mensaje = "La solicitud ya fue gestionada.";
            }
        }

        // Recargar datos
        $sentencia->execute([$id]);
        $solicitud = $sentencia->fetch(PDO::FETCH_OBJ);
        $sentenciaDet->execute([$id]);
        $detalles  = $sentenciaDet->fetchAll(PDO::FETCH_OBJ);
        $sentenciaA->execute([$id]);
        $aprobador = $sentenciaA->fetch(PDO::FETCH_OBJ);

    } catch (PDOException $e) {
        $mensaje = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
<title>Revisar Solicitud #<?= $id ?></title>
<style>
    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    body {
        background: url("/lugopata/assets/resources/fondoR.png") no-repeat center center fixed;
        background-size: cover;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        padding: 15px;
        min-height: 100vh;
    }

    .detalle-wrapper {
        max-width: 1200px;
        margin: 0 auto;
        width: 100%;
    }

    .title-card {
        background: white;
        padding: 20px;
        border-radius: 15px;
        border: 2px solid #ccc;
        margin-bottom: 20px;
        width: 100%;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    .title-card h1 {
        color: #333;
        font-weight: bold;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
        margin: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 15px;
        flex-wrap: wrap;
        font-size: clamp(1.3rem, 4vw, 2rem);
        text-align: center;
    }

    .title-card h1 img {
        width: clamp(35px, 8vw, 45px);
        height: auto;
    }

    .title-card p {
        color: #6c757d;
        margin-top: 10px;
        font-size: clamp(14px, 2vw, 16px);
        text-align: center;
    }

    .content-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        border: 1px solid #ddd;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .mensaje-exito {
        background: #d4edda;
        color: #155724;
        padding: 12px;
        border-radius: 8px;
        margin-bottom: 15px;
        text-align: center;
        font-size: 14px;
        border: 1px solid #c3e6cb;
    }

    .mensaje-error {
        background: #f8d7da;
        color: #721c24;
        padding: 12px;
        border-radius: 8px;
        margin-bottom: 15px;
        text-align: center;
        font-size: 14px;
        border: 1px solid #f5c6cb;
    }

    /* Información de la solicitud */
    .info-solicitud {
        background: #f8f9fa;
        border: 1px solid #e0e0e0;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 25px;
    }

    .info-item {
        margin-bottom: 12px;
        display: flex;
        flex-direction: column;
        padding: 8px 0;
        border-bottom: 1px dashed #dee2e6;
    }

    .info-item:last-child {
        border-bottom: none;
    }

    .info-label {
        font-weight: 600;
        color: #495057;
        font-size: 13px;
        margin-bottom: 4px;
    }

    .info-value {
        color: #212529;
        font-size: 15px;
        line-height: 1.5;
        word-break: break-word;
    }

    /* Badges y etiquetas */
    .badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .badge-primary {
        background: #cfe2ff;
        color: #084298;
        border: 1px solid #b6d4fe;
    }

    .badge-warning {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffeaa7;
    }

    .badge-success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .badge-danger {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .estado-badge {
        background: #f8f9fa;
        color: #495057;
        border: 1px solid #dee2e6;
    }

    .estado-finalizada {
        background: #d4edda;
        color: #155724;
        border-color: #c3e6cb;
    }

    .estado-pendiente {
        background: #cfe2ff;
        color: #084298;
        border-color: #b6d4fe;
    }

    .estado-espera {
        background: #fff3cd;
        color: #856404;
        border-color: #ffeaa7;
    }

    .estado-rechazada {
        background: #f8d7da;
        color: #721c24;
        border-color: #f5c6cb;
    }

    /* Sección de materiales */
    .section-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 25px 0 15px 0;
        padding-bottom: 10px;
        border-bottom: 2px solid #e0e0e0;
    }

    .section-header img {
        width: 25px;
        height: 25px;
    }

    .section-header h3 {
        color: #333;
        font-size: 18px;
        font-weight: 600;
        margin: 0;
    }

    /* Tabla responsive */
    .table-container {
        overflow-x: auto !important;
        border: 1px solid #ddd !important;
        border-radius: 10px !important;
        margin: 15px 0 !important;
        background: white !important;
    }

    table {
        width: 100% !important;
        border-collapse: collapse !important;
        min-width: 700px !important;
        font-size: 14px !important;
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
        background: #cfe2ff !important;
        font-weight: bold !important;
        color: #333 !important;
        position: sticky !important;
        top: 0 !important;
        text-align: center !important;
        white-space: nowrap !important;
    }

    td {
        text-align: center;
    }

    td:first-child {
        text-align: left;
    }

    tr:hover {
        background: #f8f9fa;
    }

    /* Imágenes de insumos */
    .imagen-insumo {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid #ddd;
        transition: transform 0.3s ease;
        cursor: pointer;
    }

    .imagen-insumo:hover {
        transform: scale(1.2);
    }

    /* Sección de firmas */
    .firmas-section {
        margin-top: 30px;
    }

    .firmas-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 15px;
        margin-top: 15px;
    }

    .firma-item {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 10px;
        padding: 15px;
        text-align: center;
    }

    .firma-item strong {
        display: block;
        margin-bottom: 10px;
        color: #495057;
        font-size: 14px;
    }

    .firma-imagen {
        max-width: 100%;
        max-height: 120px;
        border: 1px solid #ddd;
        border-radius: 6px;
        margin: 10px auto;
        display: block;
    }

    .firma-vacia {
        color: #6c757d;
        font-style: italic;
        font-size: 13px;
        padding: 20px;
    }

    .firma-nombre {
        font-size: 12px;
        color: #666;
        margin-top: 5px;
    }

    /* Botones de acción */
    .botones-accion {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-top: 25px;
        padding: 20px;
        background: #f8f9fa;
        border-radius: 10px;
        border: 1px solid #e0e0e0;
    }

    .btn {
        padding: 12px 20px;
        color: white;
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
        transition: all 0.2s;
        width: 100%;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }

    .btn-aprobar {
        background: #0d6efd;
    }

    .btn-aprobar:hover {
        background: #0b5ed7;
    }

    .btn-rechazar {
        background: #dc3545;
    }

    .btn-rechazar:hover {
        background: #bb2d3b;
    }

    .btn-secondary {
        background: #6c757d;
    }

    .btn-secondary:hover {
        background: #5a6268;
    }

    .btn-icon {
        width: 18px;
        height: 18px;
    }

    .boton-regresar-container {
        text-align: center;
        margin-top: 25px;
        padding-top: 20px;
        border-top: 1px solid #e0e0e0;
    }

    /* Modal - CORREGIDO */
    .modal-overlay {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
        backdrop-filter: blur(4px);
        justify-content: center;
        align-items: flex-start;
        padding-top: 50px;
    }

    .modal-overlay.active {
        display: flex;
    }

    .modal-content {
        background-color: #fff;
        padding: 25px;
        border-radius: 12px;
        width: 90%;
        max-width: 500px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        position: relative;
        margin: 0 auto;
        animation: modalSlideDown 0.3s ease;
    }

    @keyframes modalSlideDown {
        from {
            opacity: 0;
            transform: translateY(-50px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .modal-header {
        font-weight: 600;
        margin-bottom: 15px;
        font-size: 18px;
        color: #333;
        text-align: center;
        padding-bottom: 15px;
        border-bottom: 2px solid #e0e0e0;
    }

    .modal-textarea {
        width: 100%;
        padding: 12px;
        border: 2px solid #ddd;
        border-radius: 8px;
        resize: vertical;
        min-height: 120px;
        font-size: 14px;
        font-family: inherit;
        transition: border-color 0.2s;
    }

    .modal-textarea:focus {
        outline: none;
        border-color: #0d6efd;
    }

    .modal-footer {
        display: flex;
        gap: 10px;
        margin-top: 20px;
    }

    .modal-footer .btn {
        flex: 1;
    }

    /* Responsive */
    @media (min-width: 576px) {
        .info-item {
            flex-direction: row;
            align-items: flex-start;
        }

        .info-label {
            min-width: 200px;
            margin-bottom: 0;
            font-size: 14px;
        }

        .info-value {
            flex: 1;
        }

        .botones-accion {
            flex-direction: row;
            justify-content: center;
        }

        .btn {
            width: auto;
            min-width: 180px;
        }

        .firmas-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 480px) {
        .title-card {
            padding: 15px;
        }

        .content-card {
            padding: 15px;
        }

        .section-header h3 {
            font-size: 16px;
        }

        .modal-content {
            margin: 20px auto;
        }
    }
</style>
</head>
<body>
    <div class="detalle-wrapper">
        <!-- Encabezado -->
        <div class="title-card">
            <h1>
                <img src="../assets/resources/auditoria.png" alt="Revisión">
                Revisión de Solicitud #<?= $id ?>
            </h1>
            <p>Gestión de aprobación o rechazo de materiales</p>
        </div>

        <!-- Contenido principal -->
        <div class="content-card">
            <?php if ($mensaje): ?>
                <?php 
                    $claseMensaje = (strpos($mensaje, 'aprobada') !== false || strpos($mensaje, 'exitosamente') !== false) ? 'mensaje-exito' : 
                                   ((strpos($mensaje, 'Error') !== false || strpos($mensaje, 'rechazada') !== false) ? 'mensaje-error' : 'mensaje-exito');
                ?>
                <div class="<?= $claseMensaje ?>">
                    <?= htmlspecialchars($mensaje) ?>
                </div>
            <?php endif; ?>

            <!-- Información de la solicitud -->
            <div class="info-solicitud">
                <div class="info-item">
                    <span class="info-label">ID Solicitud:</span>
                    <span class="info-value"><strong>#<?= $id ?></strong></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Fecha:</span>
                    <span class="info-value"><?= htmlspecialchars($solicitud->fecha) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Solicitante:</span>
                    <span class="info-value"><?= htmlspecialchars($solicitud->emisor) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Departamento Emisor:</span>
                    <span class="info-value"><?= htmlspecialchars($solicitud->depto_emisor) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Departamento Destino:</span>
                    <span class="info-value"><?= htmlspecialchars($solicitud->depto_destino) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Estado:</span>
                    <span class="info-value">
                        <?php 
                            $estadoClass = '';
                            $estado = $solicitud->estado ?? 'En espera';
                            switch($estado) {
                                case 'En espera': $estadoClass = 'estado-espera'; break;
                                case 'Pendiente': $estadoClass = 'estado-pendiente'; break;
                                case 'Finalizada': $estadoClass = 'estado-finalizada'; break;
                                case 'Rechazada': $estadoClass = 'estado-rechazada'; break;
                                default: $estadoClass = 'estado-espera';
                            }
                        ?>
                        <span class="badge estado-badge <?= $estadoClass ?>"><?= $estado ?></span>
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">Descripción:</span>
                    <span class="info-value"><?= nl2br(htmlspecialchars($solicitud->descripcion)) ?></span>
                </div>
            </div>

            <!-- Detalle de materiales -->
            <div class="section-header">
                <img src="../assets/resources/la-gestion-del-inventario.png" alt="Materiales">
                <h3>Detalle de Materiales</h3>
            </div>
            
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Insumo</th>
                            <th>Descripción</th>
                            <th>Unidad</th>
                            <th>Cantidad Pedida</th>
                            <th>Cantidad Recibida</th>
                            <th>Imagen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($detalles as $d): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($d->nombre) ?></strong></td>
                                <td><?= htmlspecialchars($d->descripcion) ?></td>
                                <td><span class="badge badge-primary"><?= htmlspecialchars($d->unidad_medida) ?></span></td>
                                <td><span class="badge badge-warning"><?= htmlspecialchars($d->cantidad_pedida) ?></span></td>
                                <td>
                                    <?php if ($d->cantidad_recibida > 0): ?>
                                        <span class="badge badge-success"><?= htmlspecialchars($d->cantidad_recibida) ?></span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">0</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($d->imagen): ?>
                                        <img src="/lugopata/assets/imagenes/insumos/<?= htmlspecialchars($d->imagen) ?>" 
                                             alt="<?= htmlspecialchars($d->nombre) ?>" 
                                             class="imagen-insumo"
                                             title="<?= htmlspecialchars($d->nombre) ?>">
                                    <?php else: ?>
                                        <span style="color: #6c757d; font-size: 12px;">Sin imagen</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Firmas -->
            <div class="firmas-section">
                <div class="section-header">
                    <img src="../assets/resources/firma.png" alt="Firmas" onerror="this.style.display='none'">
                    <h3>Firmas</h3>
                </div>
                
                <div class="firmas-grid">
                    <div class="firma-item">
                        <strong>Firma del Emisor</strong>
                        <?php if (!empty($detalles[0]->firma_emisor)): ?>
                            <img src="<?= htmlspecialchars($detalles[0]->firma_emisor) ?>" 
                                 alt="Firma Emisor" class="firma-imagen">
                            <div class="firma-nombre"><?= htmlspecialchars($solicitud->emisor) ?></div>
                        <?php else: ?>
                            <div class="firma-vacia">No hay firma registrada</div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="firma-item">
                        <strong>Firma del Receptor</strong>
                        <?php if (!empty($detalles[0]->firma_receptor)): ?>
                            <img src="<?= htmlspecialchars($detalles[0]->firma_receptor) ?>" 
                                 alt="Firma Receptor" class="firma-imagen">
                            <div class="firma-nombre"><?= htmlspecialchars($solicitud->receptor) ?></div>
                        <?php else: ?>
                            <div class="firma-vacia">No hay firma registrada</div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="firma-item">
                        <strong>Firma del Aprobador</strong>
                        <?php if (!empty($detalles[0]->firma_aprobador)): ?>
                            <img src="<?= htmlspecialchars($detalles[0]->firma_aprobador) ?>" 
                                 alt="Firma Aprobador" class="firma-imagen">
                            <div class="firma-nombre"><?= htmlspecialchars($aprobador->nombre_aprobador ?? 'Desconocido') ?></div>
                        <?php else: ?>
                            <div class="firma-vacia">No hay firma registrada</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Botones de acción solo si está en espera -->
            <?php if ($solicitud->estado === 'En espera' || !$solicitud->estado): ?>
                <div class="botones-accion">
                    <button type="button" onclick="mostrarModal('aprobar')" class="btn btn-aprobar">
                        <img src="../assets/resources/caja.png" alt="Aprobar" class="btn-icon" style="filter: brightness(0) invert(1)" onerror="this.style.display='none'">
                        APROBAR SOLICITUD
                    </button>
                    <button type="button" onclick="mostrarModal('rechazar')" class="btn btn-rechazar">
                        <img src="../assets/resources/prohibicion.png" alt="Rechazar" class="btn-icon" style="filter: brightness(0) invert(1)" onerror="this.style.display='none'">
                        RECHAZAR SOLICITUD
                    </button>
                </div>
            <?php endif; ?>

            <!-- Botón de regresar -->
            <div class="boton-regresar-container">
                <a href="listarsolicitudes.php" class="btn btn-secondary">
                    <img src="../assets/resources/volver2.png" alt="Regresar" class="btn-icon" style="filter: brightness(0) invert(1)" onerror="this.style.display='none'">
                    REGRESAR A SOLICITUDES
                </a>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div id="modalRazon" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header" id="tituloModal">Razón de la acción</div>
            <form method="POST" id="formModal">
                <textarea name="razon" id="razon" class="modal-textarea" 
                          placeholder="Escriba la razón para esta acción..." required></textarea>
                <div class="modal-footer">
                    <button type="button" onclick="cerrarModal()" class="btn btn-secondary">
                        CANCELAR
                    </button>
                    <button type="submit" id="btnModal" class="btn btn-aprobar">
                        CONFIRMAR
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function mostrarModal(accion){
        const modal = document.getElementById('modalRazon');
        const titulo = document.getElementById('tituloModal');
        const btn = document.getElementById('btnModal');
        const form = document.getElementById('formModal');
        
        // Limpiar inputs hidden previos
        const hiddenInputs = form.querySelectorAll('input[type="hidden"]');
        hiddenInputs.forEach(input => input.remove());
        
        // Limpiar textarea
        document.getElementById('razon').value = '';
        
        if(accion === 'aprobar'){
            titulo.textContent = 'Razón de Aprobación';
            btn.textContent = 'APROBAR';
            btn.className = 'btn btn-aprobar';
            
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'aprobar';
            input.value = '1';
            form.appendChild(input);
        } else {
            titulo.textContent = 'Razón de Rechazo';
            btn.textContent = 'RECHAZAR';
            btn.className = 'btn btn-rechazar';
            
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'rechazar';
            input.value = '1';
            form.appendChild(input);
        }
        
        // Mostrar modal usando clase active
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
        
        // Enfocar textarea
        setTimeout(() => {
            document.getElementById('razon').focus();
        }, 100);
    }
    
    function cerrarModal(){
        const modal = document.getElementById('modalRazon');
        modal.classList.remove('active');
        document.body.style.overflow = 'auto';
        
        // Limpiar formulario
        document.getElementById('razon').value = '';
        
        const form = document.getElementById('formModal');
        const hiddenInputs = form.querySelectorAll('input[type="hidden"]');
        hiddenInputs.forEach(input => input.remove());
    }
    
    // Cerrar modal al hacer clic fuera del contenido
    document.getElementById('modalRazon').addEventListener('click', function(e) {
        if (e.target === this) {
            cerrarModal();
        }
    });
    
    // Cerrar modal con tecla ESC
    document.addEventListener('keydown', function(e) {
        if(e.key === 'Escape') {
            const modal = document.getElementById('modalRazon');
            if(modal.classList.contains('active')) {
                cerrarModal();
            }
        }
    });
    
    // Validar formulario antes de enviar
    document.getElementById('formModal').addEventListener('submit', function(e) {
        const razon = document.getElementById('razon').value.trim();
        if (!razon) {
            e.preventDefault();
            alert('Por favor, ingrese una razón para continuar.');
            document.getElementById('razon').focus();
            return false;
        }
    });
    
    // Efecto hover para imágenes de insumos
    document.querySelectorAll('.imagen-insumo').forEach(img => {
        img.addEventListener('click', function() {
            if(this.style.transform === 'scale(2)') {
                this.style.transform = 'scale(1)';
                this.style.zIndex = '1';
                this.style.position = 'static';
            } else {
                this.style.transform = 'scale(2)';
                this.style.zIndex = '1000';
                this.style.position = 'relative';
            }
        });
    });
    </script>
</body>
</html>