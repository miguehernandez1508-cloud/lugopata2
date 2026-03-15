<?php
session_start();
require_once "../conex.php";
require_once "restablecimiento.php";

$mensaje = "";
$tipoMensaje = "";

// Crear instancia de restablecimiento
$restablecimientoObj = new Restablecimiento($conexion, $bd);

// Obtener estadísticas actuales
$estadisticas = $restablecimientoObj->obtenerEstadisticas();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    
    if ($accion === 'verificar_clave') {
        $password_superadmin = $_POST['password_superadmin'] ?? '';
        
        if (empty($password_superadmin)) {
            echo json_encode(['success' => false, 'mensaje' => 'Debes ingresar la contraseña del superadministrador.']);
            exit;
        }
        
        // Validar contraseña del superadministrador
        if ($restablecimientoObj->validarSuperadmin($password_superadmin)) {
            echo json_encode(['success' => true]);
            exit;
        } else {
            echo json_encode(['success' => false, 'mensaje' => 'Contraseña del superadministrador incorrecta.']);
            exit;
        }
        
    } elseif ($accion === 'ejecutar_restablecimiento') {
        $confirmacion = $_POST['confirmacion'] ?? '';
        $password_superadmin = $_POST['password_superadmin'] ?? '';
        
        if ($confirmacion === 'RESTABLECER') {
            if (empty($password_superadmin)) {
                $mensaje = "Debes ingresar la contraseña del superadministrador.";
                $tipoMensaje = 'error';
            } else {
                // Validar contraseña del superadministrador
                if ($restablecimientoObj->validarSuperadmin($password_superadmin)) {
                    try {
                        // Ejecutar restablecimiento
                        $resultado = $restablecimientoObj->ejecutarRestablecimiento();
                        
                        // Solo mostrar el mensaje del resultado (sin $mensajeRespaldo)
                        $mensaje = $resultado['mensaje'];
                        $tipoMensaje = $resultado['success'] ? 'success' : 'error';
                        
                        // Actualizar estadísticas
                        $estadisticas = $restablecimientoObj->obtenerEstadisticas();
                        
                    } catch (Exception $e) {
                        $mensaje = "Error: " . $e->getMessage();
                        $tipoMensaje = 'error';
                    }
                } else {
                    $mensaje = "Contraseña del superadministrador incorrecta.";
                    $tipoMensaje = 'error';
                }
            }
        } else {
            $mensaje = "La palabra de confirmación es incorrecta.";
            $tipoMensaje = 'error';
        }
    }
}

include_once "../encabezado.php";
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
<title>Restablecimiento del Sistema</title>
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
    .restablecimiento-wrapper {
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
        border-bottom: 3px solid #dc3545 !important;
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

    /* ===== STATS CARD ===== */
    .stats-card {
        background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%) !important;
        padding: 20px !important;
        border-radius: 12px !important;
        margin-bottom: 25px !important;
        border: 2px solid #ccc !important;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1) !important;
    }

    .stats-card h3 {
        color: #333 !important;
        margin-bottom: 20px !important;
        text-align: center !important;
        font-weight: bold !important;
        font-size: clamp(16px, 3vw, 20px) !important;
    }

    /* ===== STATS GRID ===== */
    .stats-grid {
        display: grid !important;
        grid-template-columns: 1fr !important;
        gap: 15px !important;
    }

    .stat-item {
        background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%) !important;
        padding: 20px !important;
        border-radius: 10px !important;
        text-align: center !important;
        border: 2px solid #dee2e6 !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
        transition: all 0.3s ease !important;
    }

    .stat-item:hover {
        transform: translateY(-5px) !important;
        box-shadow: 0 6px 12px rgba(0,0,0,0.15) !important;
    }

    .stat-item strong {
        color: #333 !important;
        font-size: 14px !important;
        display: block !important;
        margin-bottom: 10px !important;
    }

    .stat-item span {
        color: #dc3545 !important;
        font-size: clamp(24px, 6vw, 32px) !important;
        font-weight: bold !important;
        display: block !important;
    }

    /* ===== DANGER CARD ===== */
    .danger-card {
        background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%) !important;
        border: 2px solid #dc3545 !important;
        padding: 25px !important;
        border-radius: 12px !important;
        margin-bottom: 25px !important;
        box-shadow: 0 4px 12px rgba(220, 53, 69, 0.2) !important;
        border-left: 5px solid #dc3545 !important;
    }

    .danger-card h3 {
        color: #a71e2a !important;
        margin-bottom: 20px !important;
        font-size: clamp(16px, 3vw, 20px) !important;
        text-align: center !important;
    }

    .danger-card ul {
        padding-left: 20px !important;
        margin: 15px 0 !important;
        color: #333 !important;
    }

    .danger-card li {
        margin-bottom: 8px !important;
        line-height: 1.5 !important;
    }

    .danger-card strong {
        color: #333 !important;
    }

    /* ===== FORMULARIO ===== */
    .form-restablecimiento {
        text-align: center !important;
        margin: 30px 0 !important;
    }

    .form-restablecimiento h3 {
        color: #333 !important;
        margin-bottom: 20px !important;
        font-weight: bold !important;
        font-size: clamp(16px, 3vw, 18px) !important;
    }

    .confirm-input {
        font-size: clamp(16px, 4vw, 20px) !important;
        font-weight: bold !important;
        padding: 15px !important;
        text-align: center !important;
        border: 3px solid #dc3545 !important;
        border-radius: 10px !important;
        width: 100% !important;
        max-width: 300px !important;
        margin: 15px auto !important;
        background-color: #fff !important;
        box-shadow: 0 4px 8px rgba(220, 53, 69, 0.2) !important;
        transition: all 0.3s ease !important;
        display: block !important;
    }

    .confirm-input:focus {
        outline: none !important;
        border-color: #a71e2a !important;
        box-shadow: 0 0 0 4px rgba(220, 53, 69, 0.3) !important;
        transform: scale(1.02) !important;
    }

    /* ===== BOTONES ===== */
    .btn {
        padding: 15px 30px !important;
        color: white !important;
        border: none !important;
        border-radius: 10px !important;
        cursor: pointer !important;
        font-size: clamp(14px, 3vw, 16px) !important;
        font-weight: bold !important;
        text-decoration: none !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 10px !important;
        transition: all 0.3s ease !important;
        box-shadow: 0 4px 8px rgba(0,0,0,0.2) !important;
        text-transform: uppercase !important;
        letter-spacing: 0.5px !important;
        width: 100% !important;
    }

    .btn:hover {
        transform: translateY(-3px) !important;
        box-shadow: 0 8px 16px rgba(0,0,0,0.25) !important;
        opacity: 0.95 !important;
    }

    .btn:active {
        transform: translateY(-1px) !important;
    }

    .btn-restablecer {
        background: linear-gradient(135deg, #dc3545 0%, #a71e2a 100%) !important;
        border: 2px solid #a71e2a !important;
        padding: 20px 40px !important;
        font-size: clamp(16px, 4vw, 20px) !important;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.3) !important;
    }

    .btn-restablecer:hover {
        background: linear-gradient(135deg, #c82333 0%, #8a1a23 100%) !important;
    }

    .btn-success {
        background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%) !important;
        border: 2px solid #1e7e34 !important;
    }

    .btn-success:hover {
        background: linear-gradient(135deg, #218838 0%, #155724 100%) !important;
    }

    .btn-info {
        background: linear-gradient(135deg, #17a2b8 0%, #0c5460 100%) !important;
        border: 2px solid #0c5460 !important;
    }

    .btn-info:hover {
        background: linear-gradient(135deg, #138496 0%, #0a3d46 100%) !important;
    }

    .btn-secondary {
        background: linear-gradient(135deg, #6c757d 0%, #495057 100%) !important;
        border: 2px solid #495057 !important;
    }

    .btn-secondary:hover {
        background: linear-gradient(135deg, #545b62 0%, #343a40 100%) !important;
    }

    /* ===== ENLACES DE SEGURIDAD ===== */
    .enlaces-seguridad {
        text-align: center !important;
        margin-top: 35px !important;
        padding-top: 25px !important;
        border-top: 2px solid #dee2e6 !important;
    }

    .enlaces-seguridad h4 {
        color: #333 !important;
        margin-bottom: 20px !important;
        font-weight: bold !important;
        font-size: clamp(16px, 3vw, 18px) !important;
    }

    .enlaces-grid {
        display: grid !important;
        grid-template-columns: 1fr !important;
        gap: 15px !important;
        max-width: 400px !important;
        margin: 0 auto !important;
    }

    /* ===== MODAL (MANTENIDO ORIGINAL) ===== */
    .modal-admin {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.7);
        justify-content: center;
        align-items: center;
        z-index: 1000;
    }

    .modal-content {
        background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
        padding: 35px;
        border-radius: 12px;
        width: 90%;
        max-width: 380px;
        text-align: center;
        border: 3px solid #dc3545;
        box-shadow: 0 10px 25px rgba(0,0,0,0.3);
        animation: modalAppear 0.3s ease-out;
        margin: 20px;
    }

    @keyframes modalAppear {
        from {
            opacity: 0;
            transform: scale(0.8) translateY(-20px);
        }
        to {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }

    .modal-content h4 {
        color: #333;
        margin-bottom: 15px;
        font-weight: bold;
    }

    .modal-content p {
        color: #666;
        margin-bottom: 20px;
    }

    .modal-input {
        width: 100%;
        padding: 12px;
        margin: 20px 0;
        border: 2px solid #666;
        border-radius: 8px;
        font-size: 16px;
        font-weight: bold;
        background-color: #fff;
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }

    .modal-input:focus {
        outline: none;
        border-color: #dc3545;
        box-shadow: 0 0 0 4px rgba(220, 53, 69, 0.2);
    }

    .modal-buttons {
        margin-top: 25px;
        display: flex;
        gap: 15px;
        justify-content: center;
        flex-wrap: wrap;
    }

    .modal-message {
        color: #dc3545;
        margin-top: 15px;
        min-height: 20px;
        font-weight: bold;
        font-size: 14px;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
    }

    /* ===== MEDIA QUERIES ===== */
    @media (min-width: 768px) {
        body {
            padding: 20px !important;
        }

        .restablecimiento-wrapper {
            padding: 15px !important;
        }

        .stats-grid {
            grid-template-columns: repeat(3, 1fr) !important;
        }

        .enlaces-grid {
            grid-template-columns: 1fr 1fr !important;
            max-width: 600px !important;
        }

        .btn {
            width: auto !important;
        }

        .confirm-input {
            width: 300px !important;
        }
    }

    @media (min-width: 1024px) {
        .restablecimiento-wrapper {
            max-width: 1400px !important;
        }

        .content-card {
            padding: 30px !important;
        }

        .stats-grid {
            grid-template-columns: repeat(6, 1fr) !important;
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

        .stats-card {
            padding: 15px !important;
        }

        .danger-card {
            padding: 20px 15px !important;
        }

        .modal-content {
            padding: 25px 20px !important;
        }

        .modal-buttons {
            flex-direction: column !important;
        }

        .modal-buttons .btn {
            width: 100% !important;
        }
    }
</style>
</head>
<body>
    <div class="restablecimiento-wrapper">
        <!-- Título -->
        <div class="title-card">
            <h1>
                <img src="../assets/resources/warning.png" alt="Advertencia">
                RESTABLECIMIENTO DEL SISTEMA
            </h1>
            <p>Acción crítica e irreversible - Use con extrema precaución</p>
        </div>
        
        <?php if($mensaje) { ?>
        <div class="alert alert-<?php echo $tipoMensaje === 'success' ? 'success' : 'error'; ?>">
            <?php echo $mensaje; ?>
        </div>
        <?php } ?>

        <!-- Estadísticas actuales -->
        <div class="content-card">
            <div class="stats-card">
                <h3>ESTADO ACTUAL DEL SISTEMA</h3>
                <div class="stats-grid">
                    <div class="stat-item">
                        <strong>Usuarios</strong>
                        <span><?php echo $estadisticas['usuarios'] ?? 0; ?></span>
                    </div>
                    <div class="stat-item">
                        <strong>Trabajadores</strong>
                        <span><?php echo $estadisticas['trabajadores'] ?? 0; ?></span>
                    </div>
                    <div class="stat-item">
                        <strong>Insumos</strong>
                        <span><?php echo $estadisticas['insumos'] ?? 0; ?></span>
                    </div>
                    <div class="stat-item">
                        <strong>Solicitudes Material</strong>
                        <span><?php echo $estadisticas['solicitud_materiales'] ?? 0; ?></span>
                    </div>
                    <div class="stat-item">
                        <strong>Incidencias</strong>
                        <span><?php echo $estadisticas['incidencias'] ?? 0; ?></span>
                    </div>
                    <div class="stat-item">
                        <strong>Salidas Almacén</strong>
                        <span><?php echo $estadisticas['solicitud_salida_almacen'] ?? 0; ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Advertencia CRÍTICA -->
        <div class="content-card">
            <div class="danger-card">
                <h3>¡ ADVERTENCIA CRÍTICA - ACCIÓN IRREVERSIBLE !</h3>
                <p><strong>Esta acción NO se puede deshacer y eliminará:</strong></p>
                <ul>
                    <li>Todos los usuarios excepto superadministrador</li>
                    <li>Todos los trabajadores excepto el base</li>
                    <li>Todos los insumos e inventarios</li>
                    <li>Todas las solicitudes de materiales</li>
                    <li>Todas las incidencias de mantenimiento</li>
                    <li>Todas las salidas de almacén</li>
                    <li>Todo el historial de auditoría</li>
                    <li>Todas las configuraciones personalizadas</li>
                </ul>
                <p><strong>Se mantendrá:</strong></p>
                <ul>
                    <li>Usuario superadministrador (contraseña se reseteará)</li>
                    <li>Trabajador base para el superadmin (sin aptitudes)</li>
                    <li>Estructura de la base de datos</li>
                    <li>Departamentos y categorías básicas</li>
                    <li>Configuraciones básicas del sistema</li>
                    <li>Tabla de fases de incidencias</li>
                </ul>
            </div>

            <!-- Formulario de restablecimiento -->
            <div class="form-restablecimiento">
                <form method="post" id="formRestablecimiento">
                    <input type="hidden" name="accion" value="ejecutar_restablecimiento">
                    <input type="hidden" name="password_superadmin" id="hiddenPassword">
                    
                    <h3>Para proceder, escribe <span style="color: #dc3545; font-weight: bold; text-shadow: 1px 1px 2px rgba(0,0,0,0.2);">RESTABLECER</span> en el siguiente campo:</h3>
                    
                    <input type="text" name="confirmacion" id="confirmacionInput" class="confirm-input" 
                           placeholder="RESTABLECER" required 
                           pattern="RESTABLECER" title="Debes escribir exactamente: RESTABLECER"
                           autocomplete="off">
                    
                    <button type="button" class="btn btn-restablecer" onclick="iniciarRestablecimiento()">
                        EJECUTAR RESTABLECIMIENTO COMPLETO
                    </button>
                </form>
            </div>
        </div>

        <!-- Enlaces de seguridad -->
        <div class="content-card enlaces-seguridad">
            <h4>ACCIONES DE SEGURIDAD</h4>
            <div class="enlaces-grid">
                <a href="respaldo_bd.php">
                    <button type="button" class="btn btn-info">
                        Crear Respaldo de Seguridad
                    </button>
                </a>
                <a href="restauracion_bd.php">
                    <button type="button" class="btn btn-success">
                        Restaurar desde Respaldo
                    </button>
                </a>
            </div>
        </div>
    </div>

    <!-- Modal para clave de superadministrador (MANTENIDO ORIGINAL) -->
    <div id="modalAdmin" class="modal-admin">
        <div class="modal-content">
            <h4>Clave de Superadministrador Requerida</h4>
            <p>Para continuar con el restablecimiento, ingrese su contraseña de superadministrador:</p>
            
            <input type="password" id="claveAdmin" class="modal-input" placeholder="Ingrese contraseña" autocomplete="off">
            
            <div class="modal-message" id="mensajeError"></div>
            
            <div class="modal-buttons">
                <button onclick="verificarClave()" class="btn btn-success">Aceptar</button>
                <button onclick="cerrarModal()" class="btn btn-secondary">Cancelar</button>
            </div>
        </div>
    </div>

    <script>
    function iniciarRestablecimiento() {
        const confirmacion = document.getElementById('confirmacionInput').value;
        
        if (confirmacion !== 'RESTABLECER') {
            alert('Debes escribir exactamente: RESTABLECER');
            document.getElementById('confirmacionInput').focus();
            document.getElementById('confirmacionInput').style.borderColor = '#ff0000';
            document.getElementById('confirmacionInput').style.boxShadow = '0 0 0 4px rgba(255, 0, 0, 0.2)';
            setTimeout(() => {
                document.getElementById('confirmacionInput').style.borderColor = '#dc3545';
                document.getElementById('confirmacionInput').style.boxShadow = '0 4px 8px rgba(220, 53, 69, 0.2)';
            }, 2000);
            return false;
        }
        
        if (!confirm('¿ESTÁS ABSOLUTAMENTE SEGURO?\n\nESTA ACCIÓN ES IRREVERSIBLE\n\n Se eliminarán TODOS los datos del sistema\n Solo se mantendrá el usuario superadministrador\n Se creará un respaldo automático antes del proceso\n\n¿Continuar con el restablecimiento completo?')) {
            return false;
        }
        
        // Mostrar modal para clave de superadmin
        document.getElementById('modalAdmin').style.display = 'flex';
        document.getElementById('claveAdmin').focus();
        document.getElementById('mensajeError').textContent = '';
    }

    function verificarClave() {
        const clave = document.getElementById('claveAdmin').value;
        const mensajeError = document.getElementById('mensajeError');
        
        if (!clave) {
            mensajeError.textContent = 'Debe ingresar la contraseña';
            document.getElementById('claveAdmin').style.borderColor = '#ff0000';
            document.getElementById('claveAdmin').style.boxShadow = '0 0 0 4px rgba(255, 0, 0, 0.2)';
            return;
        }
        
        // Verificar clave via AJAX
        const formData = new FormData();
        formData.append('accion', 'verificar_clave');
        formData.append('password_superadmin', clave);
        
        fetch('<?php echo $_SERVER['PHP_SELF']; ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Clave correcta, proceder con el restablecimiento
                document.getElementById('hiddenPassword').value = clave;
                document.getElementById('formRestablecimiento').submit();
            } else {
                mensajeError.textContent = data.mensaje || 'Contraseña incorrecta';
                document.getElementById('claveAdmin').value = '';
                document.getElementById('claveAdmin').focus();
                document.getElementById('claveAdmin').style.borderColor = '#ff0000';
                document.getElementById('claveAdmin').style.boxShadow = '0 0 0 4px rgba(255, 0, 0, 0.2)';
                setTimeout(() => {
                    document.getElementById('claveAdmin').style.borderColor = '#666';
                    document.getElementById('claveAdmin').style.boxShadow = 'inset 0 2px 4px rgba(0,0,0,0.1)';
                }, 2000);
            }
        })
        .catch(error => {
            mensajeError.textContent = 'Error al verificar la contraseña';
            console.error('Error:', error);
        });
    }

    function cerrarModal() {
        document.getElementById('modalAdmin').style.display = 'none';
        document.getElementById('claveAdmin').value = '';
        document.getElementById('mensajeError').textContent = '';
    }

    // Cerrar modal con ESC
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            cerrarModal();
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

    // Efecto hover para inputs
    document.querySelectorAll('input').forEach(input => {
        input.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.02)';
        });
        input.addEventListener('mouseleave', function() {
            if (document.activeElement !== this) {
                this.style.transform = 'scale(1)';
            }
        });
    });
    </script>
</body>
</html>