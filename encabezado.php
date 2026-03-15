<?php
require_once "user/gestorsesion.php";
require_once "incidencias/incidencia.php";
require_once "conex.php";
require_once "session_manager.php";


GestorSesiones::iniciar();

$sessionManager = new SessionManager();

// OBTENER LAS VARIABLES DE SESIÓN
$nivel = isset($_SESSION['nivel']) ? $_SESSION['nivel'] : '';
$username = isset($_SESSION['username']) ? $_SESSION['username'] : '';
$id_usuario = isset($_SESSION['id_usuario']) ? $_SESSION['id_usuario'] : null;

// VERIFICAR SESIÓN
if (empty($nivel) || empty($username)) {
    header("Location: /lugopata/index.php");
    exit;
}

$_SESSION['LAST_ACTIVITY'] = time();

$tiempo_restante = $sessionManager->getRemainingTime();
$minutos_restantes = floor($tiempo_restante / 60);
$segundos_restantes = $tiempo_restante % 60;

$incObj = new Incidencia($conexion);
$pendientes = $incObj->contarPendientes();
$en_espera = $incObj->contarEnEspera();

$nombre_usuario = GestorSesiones::get('nombre_completo') ?: $username;

// CONTAR INCIDENCIAS PENDIENTES ASIGNADAS (SOLO PARA obmantenimiento)
$pendientes_asignadas = 0;
$detalle_incidencias_asignadas = [];
$trabajador_actual = null;

if ($nivel === 'obmantenimiento' && $id_usuario) {
    try {
        $sql_trabajador = "SELECT t.id_trabajador 
                          FROM trabajadores t
                          JOIN usuarios u ON t.id_trabajador = u.id_trabajador
                          WHERE u.id_usuario = ?";
        $stmt_trabajador = $conexion->prepare($sql_trabajador);
        $stmt_trabajador->execute([$id_usuario]);
        $trabajador_actual = $stmt_trabajador->fetch(PDO::FETCH_OBJ);
        
        if ($trabajador_actual && isset($trabajador_actual->id_trabajador)) {
            $sql_contar = "SELECT COUNT(*) as total 
                          FROM incidencias 
                          WHERE estado = 'Pendiente' 
                          AND id_trabajador_asignado = ?";
            $stmt_contar = $conexion->prepare($sql_contar);
            $stmt_contar->execute([$trabajador_actual->id_trabajador]);
            $resultado = $stmt_contar->fetch(PDO::FETCH_OBJ);
            $pendientes_asignadas = $resultado ? (int)$resultado->total : 0;
            
            if ($pendientes_asignadas > 0) {
                $sql_detalle = "SELECT i.id_incidencia, i.descripcion, i.fecha, i.ubicacion,
                                       d.nombre as departamento
                                FROM incidencias i
                                JOIN departamentos d ON i.departamento_emisor = d.id_departamento
                                WHERE i.estado = 'Pendiente' 
                                AND i.id_trabajador_asignado = ?
                                ORDER BY i.fecha DESC
                                LIMIT 5";
                $stmt_detalle = $conexion->prepare($sql_detalle);
                $stmt_detalle->execute([$trabajador_actual->id_trabajador]);
                $detalle_incidencias_asignadas = $stmt_detalle->fetchAll(PDO::FETCH_OBJ);
            }
        }
    } catch (Exception $e) {
        error_log("Error al contar incidencias asignadas: " . $e->getMessage());
        $pendientes_asignadas = 0;
    }
}

// Obtener alertas de stock
require_once "insumos/insumo.php";
$insumoObj = new Insumo($conexion);
$alertas_stock = $insumoObj->contarAlertasStock();
$detalle_alertas = $insumoObj->verificarAlertasStock();

// Obtener solicitudes pendientes
$sentencia = $conexion->query("SELECT COUNT(*) FROM solicitud_materiales WHERE estado='Pendiente'");
$pendientes_insumos = $sentencia->fetchColumn();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Panel · Lugopata</title>
    <link rel="icon" type="image/png" href="/lugopata/assets/resources/logo.png">
    
    <!-- Tus CSS existentes (importantes para el resto del sistema) -->
    <link rel="stylesheet" href="/lugopata/assets/css/2.css">
    <link rel="stylesheet" href="/lugopata/assets/css/estilo.css">
    <link rel="stylesheet" href="/lugopata/assets/css.css">
    <link rel="stylesheet" href="css/luguito.css">
    
    <!-- NUEVO CSS DEL ENCABEZADO (con !important para sobrescribir) -->
    <style>
         
       /* ===== MODO OSCURO CORREGIDO ===== */
:root {
    --dark-bg: #1a1a1a;
    --dark-surface: #2d2d2d;
    --dark-surface-2: #363636;
    --dark-border: #404040;
    --dark-text-primary: #ecf0f1;    /* Texto principal - casi blanco */
    --dark-text-secondary: #b0b0b0;  /* Texto secundario - gris claro */
    --shadow-dark: 0 4px 6px rgba(0,0,0,0.3);
}

/* Fondo general */
body.dark-mode {
    background-color: var(--dark-bg) !important;
    color: var(--dark-text-primary) !important; /* Texto base claro */
}

/* ===== TEXTO GENERAL - FORZAR CLARO ===== */
body.dark-mode,
body.dark-mode * {
    color-scheme: dark;
}

/* Todos los textos que no sean botones o inputs específicos */
body.dark-mode p,
body.dark-mode span:not(.topbar-badge):not(.btn):not(button span),
body.dark-mode div:not([class*="btn"]):not(.vp_menu),
body.dark-mode label,
body.dark-mode h1,
body.dark-mode h2,
body.dark-mode h3,
body.dark-mode h4,
body.dark-mode h5,
body.dark-mode h6,
body.dark-mode .vp_item,
body.dark-mode .title-card,
body.dark-mode .welcome-card,
body.dark-mode .card-title,
body.dark-mode .card-header,
body.dark-mode .form-label {
    color: var(--dark-text-primary) !important;
}

/* Textos secundarios/muted */
body.dark-mode .text-muted,
body.dark-mode small,
body.dark-mode .insumo-id,
body.dark-mode .section-title {
    color: var(--dark-text-secondary) !important;
}

/* ===== CONTENEDORES (solo fondos, NO colores de texto) ===== */
body.dark-mode .vp_sidebar,
body.dark-mode .vp_topbar,
body.dark-mode .card,
body.dark-mode .form-container,
body.dark-mode .welcome-card,
body.dark-mode .title-card,
body.dark-mode .dashboard-card,
body.dark-mode .panel,
body.dark-mode .container-box,
body.dark-mode [class*="card"],
body.dark-mode [class*="Card"],
body.dark-mode [class*="container"]:not(.vp_main_content),
body.dark-mode [class*="Container"],
body.dark-mode .grid-botones a {
    background: var(--dark-surface) !important;
    border-color: var(--dark-border) !important;
    /* NO incluimos color de texto aquí */
}

/* Contenido principal transparente */
body.dark-mode .vp_main_content {
    background: transparent !important;
}

/* ===== BOTONES - CONSERVAR COLORES ORIGINALES ===== */
/* NO sobrescribimos button, .btn, ni clases de botones */
/* Solo ajustamos fondos específicos si son blancos puros */

/* Botones del menú lateral - mantener como están */
body.dark-mode .vp_menu button,
body.dark-mode .vp_menu a:not(.vp_dropdown_content a) {
    /* Mantener estilos originales - NO !important aquí */
    background: rgba(255,255,255,0.1) !important;
    color: white !important;
}

/* Botones de la topbar - NO tocar */
body.dark-mode .topbar-icon {
    /* Mantener estilos originales */
    background: transparent !important;
}

/* Botones de acción (solicitar, ver, etc) - NO tocar */
body.dark-mode .btn-solicitar,
body.dark-mode .btn-ver-incidencia,
body.dark-mode .btn-inventory,
body.dark-mode .btn-primary,
body.dark-mode .btn-success,
body.dark-mode .btn-warning,
body.dark-mode .btn-danger,
body.dark-mode .btn-info {
    /* Conservar colores originales definidos en otras hojas CSS */
    color: white !important; /* Pero asegurar texto legible */
}

/* ===== CAMPOS DE FORMULARIO ===== */
body.dark-mode input:not([type="submit"]):not([type="button"]):not([type="reset"]),
body.dark-mode textarea,
body.dark-mode select,
body.dark-mode .form-control,
body.dark-mode .form-select {
    background: var(--dark-surface-2) !important;
    border-color: var(--dark-border) !important;
    color: var(--dark-text-primary) !important;
}

body.dark-mode input:focus,
body.dark-mode textarea:focus,
body.dark-mode select:focus {
    border-color: #0d6efd !important;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25) !important;
}

/* ===== TABLAS ===== */
body.dark-mode table,
body.dark-mode .table {
    background: transparent !important;
    border-color: var(--dark-border) !important;
    color: var(--dark-text-primary) !important;
}

body.dark-mode th,
body.dark-mode td {
    border-color: var(--dark-border) !important;
    color: var(--dark-text-primary) !important;
}

body.dark-mode thead {
    background: var(--dark-surface-2) !important;
}

/* ===== DROPDOWNS Y NOTIFICACIONES ===== */
body.dark-mode .vp_dropdown_content,
body.dark-mode .notification-dropdown,
body.dark-mode .dropdown-menu,
body.dark-mode .modal-content {
    background: var(--dark-surface) !important;
    border-color: var(--dark-border) !important;
    color: var(--dark-text-primary) !important;
}

body.dark-mode .notification-header,
body.dark-mode .notification-footer {
    background: var(--dark-surface-2) !important;
    border-color: var(--dark-border) !important;
}

body.dark-mode .notification-content,
body.dark-mode .notification-dropdown * {
    color: var(--dark-text-primary) !important;
}

/* ===== TEMPORIZADOR ===== */
body.dark-mode #sessionTimer {
    background: var(--dark-surface) !important;
    border-color: var(--dark-border) !important;
    color: var(--dark-text-primary) !important;
}

/* ===== SCROLLBAR ===== */
body.dark-mode ::-webkit-scrollbar {
    width: 10px;
    height: 10px;
}

body.dark-mode ::-webkit-scrollbar-track {
    background: var(--dark-bg);
}

body.dark-mode ::-webkit-scrollbar-thumb {
    background: var(--dark-border);
    border-radius: 5px;
}

body.dark-mode ::-webkit-scrollbar-thumb:hover {
    background: #555;
}

/* Fuerza de emergencia - solo si los botones siguen mal */
body.dark-mode [class*="btn-"] {
    color: white !important;
    text-shadow: none !important;
}

/* Si usas Bootstrap o similar, preservar sus colores */
body.dark-mode .btn-primary { background-color: #0d6efd !important; border-color: #0d6efd !important; }
body.dark-mode .btn-success { background-color: #198754 !important; border-color: #198754 !important; }
body.dark-mode .btn-warning { background-color: #ffc107 !important; border-color: #ffc107 !important; color: #000 !important; }
body.dark-mode .btn-danger { background-color: #dc3545 !important; border-color: #dc3545 !important; }
        /* ===== RESET Y VARIABLES ===== */
        :root {
            --sidebar-width: 260px;
            --sidebar-mini-width: 70px;
            --topbar-height: 60px;
            --primary-dark: #1a2634;
            --primary: #2c3e50;
            --primary-light: #34495e;
            --accent: #3498db;
            --success: #27ae60;
            --warning: #f39c12;
            --danger: #e74c3c;
            --light: #ecf0f1;
            --text-light: #ecf0f1;
            --text-dark: #2c3e50;
            --shadow: 0 2px 10px rgba(0,0,0,0.1);
            --shadow-lg: 0 5px 20px rgba(0,0,0,0.15);
            --transition: all 0.3s ease;
        }

        body {
            background: url("/lugopata/assets/resources/fondoR.png") no-repeat center center fixed !important;
            background-size: cover !important;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif !important;
            margin: 0 !important;
            padding: 0 !important;
            overflow-x: hidden !important;
        }

        /* ===== SIDEBAR ===== */
        .vp_sidebar {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: var(--sidebar-width) !important;
            height: 100vh !important;
            background: linear-gradient(180deg, var(--primary-dark), var(--primary)) !important;
            box-shadow: 4px 0 15px rgba(0,0,0,0.3) !important;
            z-index: 9999 !important;
            transition: width 0.3s ease, transform 0.3s ease !important;
            overflow-y: auto !important;
            overflow-x: hidden !important;
            display: flex !important;
            flex-direction: column !important;
        }

        /* Sidebar minimizado */
        .vp_sidebar.minimized {
            width: var(--sidebar-mini-width) !important;
        }

        /* Sidebar oculto (para móvil) */
        .vp_sidebar.hidden {
            transform: translateX(-100%) !important;
        }

        /* ===== LOGO ===== */
        .vp_logo {
            text-align: center !important;
            padding: 20px 0 !important;
            border-bottom: 1px solid rgba(255,255,255,0.1) !important;
            transition: padding 0.3s ease !important;
        }

        .vp_logo img {
            max-width: 120px !important;
            height: auto !important;
            transition: all 0.3s ease !important;
        }

        .minimized .vp_logo img {
            max-width: 40px !important;
        }

        /* ===== USUARIO ===== */
        .vp_item {
            padding: 15px !important;
            text-align: center !important;
            color: rgba(255,255,255,0.9) !important;
            font-weight: 600 !important;
            border-bottom: 1px solid rgba(255,255,255,0.1) !important;
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            transition: all 0.3s ease !important;
        }

        .minimized .vp_item {
            font-size: 0 !important;
            padding: 10px 0 !important;
        }

        .minimized .vp_item::first-letter {
            font-size: 16px !important;
            display: block !important;
            text-align: center !important;
        }

        /* ===== MENÚ ===== */
        .vp_menu {
            flex: 1 !important;
            padding: 15px !important;
            display: flex !important;
            flex-direction: column !important;
            gap: 5px !important;
        }

        .vp_menu .separator {
            height: 1px !important;
            background: rgba(255,255,255,0.1) !important;
            margin: 10px 0 !important;
        }

        /* Botones del menú */
        .vp_menu button,
        .vp_menu a:not(.vp_dropdown_content a) {
            background: rgba(255,255,255,0.1) !important;
            color: white !important;
            width: 100% !important;
            min-height: 45px !important;
            padding: 10px 15px !important;
            border: none !important;
            border-radius: 8px !important;
            font-weight: 500 !important;
            font-size: 14px !important;
            display: flex !important;
            align-items: center !important;
            gap: 12px !important;
            cursor: pointer !important;
            text-decoration: none !important;
            transition: background 0.2s !important;
            white-space: nowrap !important;
            overflow: hidden !important;
        }

        .minimized .vp_menu button,
        .minimized .vp_menu a:not(.vp_dropdown_content a) {
            padding: 10px 0 !important;
            justify-content: center !important;
        }

        .minimized .vp_menu button span,
        .minimized .vp_menu a:not(.vp_dropdown_content a) span {
            display: none !important;
        }

        .vp_menu button:hover,
        .vp_menu a:not(.vp_dropdown_content a):hover {
            background: rgba(255,255,255,0.2) !important;
        }

        .vp_menu img {
            width: 24px !important;
            height: 24px !important;
            filter: brightness(0) invert(1) !important;
            flex-shrink: 0 !important;
        }

        /* ===== DROPDOWN ===== */
        .vp_dropdown {
            position: relative !important;
        }

        .vp_dropdown_content {
            display: none !important;
            position: fixed !important;
            background: linear-gradient(180deg, var(--primary-dark), var(--primary)) !important;
            border: 1px solid #ccc !important;
            border-radius: 8px !important;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2) !important;
            min-width: 220px !important;
            z-index: 999999 !important;
            padding: 8px 0 !important;
        }

        .vp_dropdown_content[style*="display: block"] {
            display: block !important;
       }

       .vp_dropdown_content a {
           display: flex !important;
           align-items: center !important;
           gap: 12px !important;
           padding: 10px 16px !important;
           color: white !important;
           text-decoration: none !important;
           font-size: 14px !important;
           transition: background 0.2s !important;
        }

        .vp_dropdown_content a:hover {
            background: #959595 !important;
        }

        .vp_dropdown_content img {
            width: 20px !important;
            height: 20px !important;
            object-fit: contain !important;
      }
        /* ===== FOOTER DEL SIDEBAR ===== */
        .header-right {
            padding: 20px !important;
            text-align: center !important;
            border-top: 1px solid rgba(255,255,255,0.1) !important;
            transition: all 0.3s ease !important;
        }

        .minimized .header-right {
            padding: 15px 0 !important;
        }

        .header-right a {
            color: white !important;
            text-decoration: none !important;
            display: inline-block !important;
        }

        .header-right img {
            width: 31px !important;
            height: 31px !important;
            transition: all 0.3s ease !important;
        }

        .minimized .header-right span {
            display: none !important;
        }

        /* ===== TOPBAR ===== */
        .vp_topbar {
            position: fixed !important;
            top: 10px !important;
            left: calc(var(--sidebar-width) + 10px) !important;
            right: 10px !important;
            height: var(--topbar-height) !important;
            background: white !important;
            border-radius: 30px !important;
            box-shadow: var(--shadow) !important;
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            padding: 0 15px !important;
            z-index: 9998 !important;
            transition: left 0.3s ease !important;
        }

        .vp_topbar.expanded {
            left: 10px !important;
        }

        .vp_topbar.minimized {
            left: calc(var(--sidebar-mini-width) + 10px) !important;
        }

        .vp_topbar_left {
            display: flex !important;
            align-items: center !important;
            gap: 15px !important;
        }

        .vp_topbar_right {
            display: flex !important;
            align-items: center !important;
            gap: 5px !important;
        }

        /* ===== BOTONES DE TOGGLE ===== */
        #sidebarToggle,
        #sidebarMiniToggle {
            width: 40px !important;
            height: 40px !important;
            border-radius: 50% !important;
            border: none !important;
            background: transparent !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            cursor: pointer !important;
            transition: background 0.2s !important;
        }

        #sidebarToggle:hover,
        #sidebarMiniToggle:hover {
            background: rgba(0,0,0,0.05) !important;
        }

        #sidebarToggle img,
        #sidebarMiniToggle img {
            width: 24px !important;
            height: 24px !important;
            object-fit: contain !important;
        }

        #sidebarMiniToggle {
            margin-left: auto !important;
            display: none !important;
        }

        /* Mostrar botón de mini en desktop */
        @media (min-width: 769px) {
            #sidebarMiniToggle {
                display: flex !important;
            }
        }

        /* ===== ICONOS DE NOTIFICACIÓN ===== */
        .topbar-icon {
            position: relative !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 40px !important;
            height: 40px !important;
            border-radius: 50% !important;
            transition: background 0.2s !important;
            text-decoration: none !important;
        }

        .topbar-icon:hover {
            background: rgba(0,0,0,0.05) !important;
        }

        .topbar-icon img {
            width: 22px !important;
            height: 22px !important;
            object-fit: contain !important;
        }

        .topbar-badge {
            position: absolute !important;
            top: -4px !important;
            right: -4px !important;
            min-width: 18px !important;
            height: 18px !important;
            background: var(--danger) !important;
            color: white !important;
            font-size: 11px !important;
            font-weight: bold !important;
            border-radius: 9px !important;
            padding: 0 4px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            border: 2px solid white !important;
        }

        .topbar-badge.blue-badge { background: var(--accent) !important; }
        .topbar-badge.green-badge { background: var(--success) !important; }
        .topbar-badge.orange-badge { background: var(--warning) !important; }
        .topbar-badge.red-badge { background: var(--danger) !important; }

        /* ===== NOTIFICATION DROPDOWNS ===== */
        .notification-container {
            position: relative !important;
            display: inline-block !important;
        }

        .notification-dropdown {
            position: absolute !important;
            top: 100% !important;
            right: 0 !important;
            width: 500px !important;
            max-width: 90vw !important;
            background: white !important;
            border-radius: 12px !important;
            box-shadow: var(--shadow-lg) !important;
            z-index: 10000 !important;
            display: none !important;
            max-height: 80vh !important;
            overflow-y: auto !important;
            margin-top: 5px !important;
        }

        .notification-dropdown.show {
            display: block !important;
        }

        .notification-header {
            padding: 15px 20px !important;
            border-bottom: 1px solid #eee !important;
            background: linear-gradient(135deg, #f8f9fa, #e9ecef) !important;
            border-radius: 12px 12px 0 0 !important;
        }

        .notification-header h4 {
            margin: 0 !important;
            color: var(--text-dark) !important;
            font-size: 16px !important;
        }

        .notification-content {
            padding: 15px !important;
        }

        .notification-footer {
            padding: 15px !important;
            border-top: 1px solid #eee !important;
            text-align: center !important;
            background: #f8f9fa !important;
            border-radius: 0 0 12px 12px !important;
        }

        /* ===== STOCK GRID ===== */
        .stock-grid-container {
            display: grid !important;
            grid-template-columns: 35% 15% 15% 15% 20% !important;
            width: 100% !important;
        }

        .stock-grid-header {
            padding: 8px !important;
            font-weight: 600 !important;
            font-size: 11px !important;
            color: #666 !important;
            background: #f8f9fa !important;
            border-bottom: 1px solid #dee2e6 !important;
            text-align: center !important;
        }

        .stock-grid-cell {
            padding: 8px !important;
            border-bottom: 1px solid #f0f0f0 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-size: 12px !important;
        }

        .stock-grid-cell:first-child {
            justify-content: flex-start !important;
        }

        .insumo-info {
            text-align: left !important;
        }

        .insumo-name {
            font-weight: 500 !important;
            color: var(--text-dark) !important;
        }

        .insumo-id {
            font-size: 10px !important;
            color: #999 !important;
        }

        .stock-amount.critical { color: var(--danger) !important; font-weight: bold !important; }
        .stock-amount.warning { color: var(--warning) !important; font-weight: bold !important; }

        .btn-solicitar,
        .btn-ver-incidencia,
        .btn-inventory {
            padding: 5px 10px !important;
            border: none !important;
            border-radius: 5px !important;
            font-size: 11px !important;
            cursor: pointer !important;
            text-decoration: none !important;
            color: white !important;
            transition: all 0.2s !important;
        }

        .btn-solicitar { background: var(--success) !important; }
        .btn-ver-incidencia { background: var(--accent) !important; }
        .btn-inventory { background: var(--accent) !important; }

        .btn-solicitar:hover,
        .btn-ver-incidencia:hover,
        .btn-inventory:hover {
            transform: translateY(-1px) !important;
            box-shadow: var(--shadow) !important;
        }

        /* ===== TABLAS EN DROPDOWNS ===== */
        #incidenciasDropdown table {
            width: 100% !important;
            border-collapse: collapse !important;
        }

        #incidenciasDropdown th,
        #incidenciasDropdown td {
            padding: 8px !important;
            border: 1px solid #dee2e6 !important;
            text-align: center !important;
            font-size: 12px !important;
        }

        #incidenciasDropdown th {
            background: #f8f9fa !important;
            font-weight: 600 !important;
        }

        /* ===== CONTENIDO PRINCIPAL ===== */
        .vp_main_content {
            margin-left: var(--sidebar-width) !important;
            margin-top: calc(var(--topbar-height) + 10px) !important;
            padding: 20px !important;
            transition: margin-left 0.3s ease !important;
            min-height: calc(100vh - var(--topbar-height) - 30px) !important;
        }

        .vp_main_content.expanded {
            margin-left: 20px !important;
        }

        .vp_main_content.minimized {
            margin-left: var(--sidebar-mini-width) !important;
        }

        /* ===== OVERLAY PARA MÓVIL ===== */
        .sidebar-overlay {
            display: none !important;
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            background: rgba(0,0,0,0.5) !important;
            z-index: 9998 !important;
            opacity: 0 !important;
            transition: opacity 0.3s ease !important;
        }

        .sidebar-overlay.active {
            display: block !important;
            opacity: 1 !important;
        }

        /* ===== TEMPORIZADOR ===== */
        #sessionTimer {
            position: fixed !important;
            bottom: 20px !important;
            right: 20px !important;
            background: var(--primary-dark) !important;
            color: white !important;
            padding: 8px 15px !important;
            border-radius: 25px !important;
            font-size: 13px !important;
            font-weight: bold !important;
            z-index: 10000 !important;
            box-shadow: var(--shadow-lg) !important;
            border: 2px solid var(--primary-light) !important;
        }

        /* ===== MEDIA QUERIES ===== */
        @media (max-width: 768px) {
            .vp_sidebar {
                transform: translateX(-100%) !important;
            }

            .vp_sidebar:not(.hidden) {
                transform: translateX(0) !important;
            }

            .vp_sidebar.minimized {
                width: var(--sidebar-width) !important;
                transform: translateX(-100%) !important;
            }

            .vp_sidebar.minimized:not(.hidden) {
                transform: translateX(0) !important;
            }

            .vp_topbar {
                left: 10px !important;
            }

            .vp_main_content {
                margin-left: 10px !important;
            }

            #sidebarMiniToggle {
                display: none !important;
            }

            .notification-dropdown {
                width: 95vw !important;
                right: 2.5vw !important;
            }
        }

        @media (max-width: 480px) {
            .topbar-icon {
                width: 36px !important;
                height: 36px !important;
            }

            .topbar-icon img {
                width: 20px !important;
                height: 20px !important;
            }

            .vp_main_content {
                padding: 15px !important;
            }

            .stock-grid-container {
                grid-template-columns: 1fr 1fr !important;
            }

            .stock-grid-header:nth-child(n+3),
            .stock-grid-cell:nth-child(n+3) {
                display: none !important;
            }
        }
        /* ===== FIX PARA VISTA MÓVIL ===== */
@media (max-width: 768px) {
    /* Ajustar dropdowns en móvil */
    .vp_dropdown_content {
        position: fixed !important;
        top: auto !important;
        bottom: 0 !important;
        left: 0 !important;
        right: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
        max-height: 80vh !important;
        overflow-y: auto !important;
        border-radius: 20px 20px 0 0 !important;
        box-shadow: 0 -5px 20px rgba(0,0,0,0.2) !important;
        animation: slideUp 0.3s ease !important;
    }

    /* Animación para dropdowns móvil */
    @keyframes slideUp {
        from {
            transform: translateY(100%);
        }
        to {
            transform: translateY(0);
        }
    }

    /* Ajustar contenido del dropdown en móvil */
    .vp_dropdown_content a {
        padding: 15px 20px !important;
        font-size: 16px !important;
        border-bottom: 1px solid #eee !important;
    }

    .vp_dropdown_content a:last-child {
        border-bottom: none !important;
    }

    /* Asegurar que el sidebar no tape el contenido */
    .vp_sidebar:not(.hidden) {
        width: 85% !important;
        max-width: 300px !important;
    }

    /* Ajustar topbar en móvil */
    .vp_topbar {
        height: 55px !important;
        padding: 0 10px !important;
    }

    /* Ajustar badges en móvil */
    .topbar-badge {
        min-width: 20px !important;
        height: 20px !important;
        font-size: 11px !important;
        top: -5px !important;
        right: -5px !important;
    }

    /* Ajustar contenedor de notificaciones en móvil */
    .notification-dropdown {
        position: fixed !important;
        top: auto !important;
        bottom: 0 !important;
        left: 0 !important;
        right: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
        max-height: 80vh !important;
        border-radius: 20px 20px 0 0 !important;
        margin: 0 !important;
    }

    /* Grid de stock en móvil */
    .stock-grid-container {
        grid-template-columns: 1fr 1fr !important;
        gap: 10px !important;
    }

    .stock-grid-header:nth-child(n+3),
    .stock-grid-cell:nth-child(n+3) {
        display: none !important;
    }

    /* Tablas en móvil */
    #incidenciasDropdown table {
        font-size: 12px !important;
    }

    #incidenciasDropdown th,
    #incidenciasDropdown td {
        padding: 8px 5px !important;
    }

    /* Botones más grandes para touch */
    .vp_menu button,
    .vp_menu a:not(.vp_dropdown_content a) {
        min-height: 50px !important;
        font-size: 15px !important;
    }

    .topbar-icon {
        width: 44px !important;
        height: 44px !important;
    }

    .topbar-icon img {
        width: 24px !important;
        height: 24px !important;
    }

    #sidebarToggle,
    #sidebarMiniToggle {
        width: 44px !important;
        height: 44px !important;
    }

    /* Ajustar temporizador en móvil */
    #sessionTimer {
        bottom: 10px !important;
        right: 10px !important;
        padding: 6px 12px !important;
        font-size: 12px !important;
        z-index: 10001 !important;
    }

    /* Overlay más oscuro */
    .sidebar-overlay.active {
        background: rgba(0, 0, 0, 0.7) !important;
    }
}

/* Móviles muy pequeños */
@media (max-width: 480px) {
    .vp_sidebar:not(.hidden) {
        width: 90% !important;
    }

    .vp_topbar_right {
        gap: 2px !important;
    }

    .topbar-icon {
        width: 40px !important;
        height: 40px !important;
    }

    .topbar-icon img {
        width: 22px !important;
        height: 22px !important;
    }

    .grid-botones {
        grid-template-columns: 1fr !important;
    }

    /* Ajustar textos largos */
    .vp_item {
        font-size: 14px !important;
        padding: 10px 5px !important;
    }

    .vp_menu button span {
        font-size: 14px !important;
    }
}
    </style>
</head>
<body>
    <!-- Overlay para móvil -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- SIDEBAR -->
    <aside class="vp_sidebar" id="sidebar">
        <div class="vp_logo">
            <a href="/lugopata/dashboard.php">
                <img src="/lugopata/assets/resources/logo.png" alt="Logo Lugopata">
            </a>
        </div>

        <div class="vp_item">
            <?= htmlspecialchars($nombre_usuario) ?>
        </div>
        
        <nav class="vp_menu">
            <div class="separator"></div>

            <!-- MENÚ DE USUARIO -->
            <?php if($nivel === 'admin' || $nivel === 'sistemas' || $nivel === 'superadministrador'): ?>
            <div class="vp_dropdown">
                <button data-vp-toggle data-dropdown="userDropdown">
                    <img src="/lugopata/assets/resources/Rcusuario.png" alt="">
                    <span>Usuario</span>
                </button>
                <div class="vp_dropdown_content" id="userDropdown">
                    <a href="/lugopata/user/formcrearusuario.php">
                        <img src="/lugopata/assets/resources/Rnusuario.png"> Crear Usuario
                    </a>
                    <a href="/lugopata/user/listarusuario.php">
                        <img src="/lugopata/assets/resources/listadecontactos.png"> Listar Usuarios
                    </a>
                    <a href="/lugopata/user/listauditoria.php">
                        <img src="/lugopata/assets/resources/verificacion-de-antecedentes.png"> Auditoría
                    </a>
                </div>
            </div>
            <?php endif; ?>
                   
            <!-- MENÚ DE TRABAJADORES -->
            <?php if($nivel === 'admin' || $nivel === 'supmantenimiento' || $nivel === 'sistemas' || $nivel === 'superadministrador'): ?>
            <div class="vp_dropdown">
                <button data-vp-toggle data-dropdown="trabajadoresDropdown">
                    <img src="/lugopata/assets/resources/casco4.png" alt="">
                    <span>Trabajadores</span>
                </button>
                <div class="vp_dropdown_content" id="trabajadoresDropdown">
                    <a href="/lugopata/trabajador/formctrabajador.php">
                        <img src="/lugopata/assets/resources/trabajadorR.png"> Crear Trabajador
                    </a>
                    <a href="/lugopata/trabajador/listartrabajador.php">
                        <img src="/lugopata/assets/resources/Rltrabajadores.png"> Listar Trabajadores
                    </a>
                </div>
            </div>
            <?php endif; ?>
                  
            <!-- MENÚ DE INSUMOS -->
            <?php if($nivel === 'admin' || $nivel === 'supmantenimiento' || $nivel === 'sistemas' || $nivel === 'almacenista' || $nivel === 'superadministrador'): ?>
            <div class="vp_dropdown">
                <button data-vp-toggle data-dropdown="insumosDropdown">
                    <img src="/lugopata/assets/resources/produccion1.png" alt="">
                    <span>Insumos</span>
                </button>
                <div class="vp_dropdown_content" id="insumosDropdown">
                    <?php if($nivel === 'admin' || $nivel === 'sistemas' || $nivel === 'supmantenimiento' || $nivel === 'superadministrador'): ?>
                    <a href="/lugopata/insumos/formsolicitudinsumos.php">
                        <img src="/lugopata/assets/resources/engranaje1.png"> Solicitar Insumos
                    </a>
                    <?php endif; ?>
                    <a href="/lugopata/insumos/listarsolicitudes.php">
                        <img src="/lugopata/assets/resources/demanda.png"> Listar Solicitudes
                    </a>
                    <?php if($nivel === 'admin' || $nivel === 'sistemas' || $nivel === 'supmantenimiento' || $nivel === 'superadministrador'): ?>
                    <a href="/lugopata/insumos/listarinsumos.php">
                        <img src="/lugopata/assets/resources/caracteristicas.png"> Listar Insumos
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
                   
            <!-- MENÚ DE DEPARTAMENTOS -->
            <?php if($nivel === 'admin' || $nivel === 'supmantenimiento' || $nivel === 'sistemas' || $nivel === 'superadministrador'): ?>
            <div class="vp_dropdown">
                <button data-vp-toggle data-dropdown="departamentosDropdown">
                    <img src="/lugopata/assets/resources/departamentosR.png" alt="">
                    <span>Departamentos</span>
                </button>
                <div class="vp_dropdown_content" id="departamentosDropdown">
                    <a href="/lugopata/departamentos/formcreardepartamentos.php">
                        <img src="/lugopata/assets/resources/equipo.png"> Crear departamento
                    </a>
                    <a href="/lugopata/departamentos/listardepartamento.php">
                        <img src="/lugopata/assets/resources/listdepa.png"> Listar Departamentos
                    </a>
                </div>
            </div>
            <?php endif; ?>

            <!-- MENÚ DE INCIDENCIAS -->
            <?php if($nivel === 'admin' || $nivel === 'sistemas' || $nivel === 'supmantenimiento' || $nivel === 'almacenista' || $nivel === 'solicitante' || $nivel === 'superadministrador' || $nivel === 'obmantenimiento'): ?>
            <div class="vp_dropdown">
                <button data-vp-toggle data-dropdown="incidenciasDropdown">
                    <img src="/lugopata/assets/resources/incidente5.png" alt="">
                    <span>Incidencias</span>
                </button>
                <div class="vp_dropdown_content" id="incidenciasDropdown">
                    <?php if($nivel === 'admin' || $nivel === 'sistemas' || $nivel === 'supmantenimiento' || $nivel === 'solicitante' || $nivel === 'almacenista' || $nivel === 'superadministrador'): ?>
                    <a href="/lugopata/incidencias/formcrearincidencia.php">
                        <img src="/lugopata/assets/resources/incidente2.png"> Nueva incidencia
                    </a>
                    <?php endif; ?>

                    <?php if($nivel === 'obmantenimiento'): ?>
                    <a href="/lugopata/incidencias/misincidencias.php">
                        <img src="/lugopata/assets/resources/nincidencia.png"> Incidencias asignadas
                    </a>
                    <?php endif; ?>

                    <?php if($nivel === 'admin' || $nivel === 'supmantenimiento' || $nivel === 'superadministrador'): ?>
                    <a href="/lugopata/incidencias/listarincidencias.php">
                        <img src="/lugopata/assets/resources/identificacionR.png"> Listar Incidencias
                    </a>
                    <a href="/lugopata/incidencias/gestionarfases.php">
                        <img src="/lugopata/assets/resources/incidente.png"> Gestionar tipos
                    </a>
                    <a href="/lugopata/incidencias/aprobarincidencia.php">
                        <img src="/lugopata/assets/resources/caducado5.png"> En espera
                        <?php if ($en_espera > 0): ?>
                            <span style="background: #dc3545; color: white; padding: 2px 6px; border-radius: 10px; font-size: 12px; margin-left: auto;">
                                <?= $en_espera ?>
                            </span>
                        <?php endif; ?>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- MENÚ DE INVENTARIO -->
            <?php if( $nivel === 'sistemas' || $nivel === 'almacenista' || $nivel === 'superadministrador'): ?>
            <div class="vp_dropdown">
                <button data-vp-toggle data-dropdown="inventarioDropdown">
                    <img src="/lugopata/assets/resources/inventario2.png" alt="">
                    <span>Inventario</span>
                </button>
                <div class="vp_dropdown_content" id="inventarioDropdown">
                    <a href="/lugopata/inventario/listarstock.php">
                        <img src="/lugopata/assets/resources/stock4.png"> Stock Inventario
                    </a>
                    <a href="/lugopata/insumos/formcrearinsumo.php">
                        <img src="/lugopata/assets/resources/productoR.png"> Crear Insumos
                    </a>
                    <a href="/lugopata/inventario/formsolicitudsalida.php">
                        <img src="/lugopata/assets/resources/carrito.png"> Solicitar Compra
                    </a>
                    <a href="/lugopata/inventario/listarsolicitudstock.php">
                        <img src="/lugopata/assets/resources/listcompra.png"> Listar Solicitudes
                    </a>
                </div>
            </div>
            <?php endif; ?>

            <!-- MENÚ DE ESTADÍSTICAS -->
            <?php if($nivel === 'admin' || $nivel === 'sistemas' || $nivel === 'superadministrador'|| $nivel === 'supmantenimiento'): ?>
            <div class="vp_dropdown">
                <button data-vp-toggle data-dropdown="estadisticasDropdown">
                    <img src="/lugopata/assets/resources/estadisticasR.png" alt="">
                    <span>Estadísticas</span>
                </button>
                <div class="vp_dropdown_content" id="estadisticasDropdown">
                    <a href="/lugopata/estadisticas/estadisticas.php">
                        <img src="/lugopata/assets/resources/estadisticasR.png"> Estadísticas
                    </a>
                </div>
            </div>
            <?php endif; ?>

            <!-- MENÚ DE RESPALDOS -->
            <?php if($nivel === 'superadministrador'): ?>
            <div class="vp_dropdown">
                <button data-vp-toggle data-dropdown="respaldosDropdown">
                    <img src="/lugopata/assets/resources/respaldoR.png" alt="">
                    <span>Respaldos</span>
                </button>
                <div class="vp_dropdown_content" id="respaldosDropdown">
                    <a href="/lugopata/respaldo/respaldo_bd.php">
                        <img src="/lugopata/assets/resources/respaldoR2.png"> Respaldo
                    </a>
                    <a href="/lugopata/respaldo/restauracion_bd.php">
                        <img src="/lugopata/assets/resources/restauracionR.png"> Restauración
                    </a>
                    <a href="/lugopata/respaldo/restablecimiento_bd.php">
                        <img src="/lugopata/assets/resources/restablecimientoR.png"> Restablecimiento
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </nav>

        <!-- FOOTER DEL SIDEBAR -->
        <div class="header-right">
            <a href="/lugopata/user/cerrarsesion.php" onclick="return confirm('¿Seguro que quieres cerrar sesión?');">
                <img src="/lugopata/assets/resources/cerrar-sesion.png" alt="Cerrar Sesión">
                <br><span>Cerrar sesión</span>
            </a>
        </div>
    </aside>

    <!-- TOPBAR -->
    <div class="vp_topbar" id="topbar">
        <div class="vp_topbar_left">
            <!-- Botón para mostrar/ocultar sidebar (móvil/escritorio) -->
            <a href="javascript:void(0)" id="sidebarToggle" class="topbar-icon" title="Mostrar/ocultar menú">
                <img src="/lugopata/assets/resources/menu-icon1.png" alt="Menú" id="toggleIcon">
            </a>
            
            <!-- Botón para minimizar sidebar (solo escritorio) -->
            <a href="javascript:void(0)" id="sidebarMiniToggle" class="topbar-icon" title="Minimizar menú">
                <img src="/lugopata/assets/resources/minimizar.png" alt="Minimizar" id="miniIcon">
            </a>
        </div>
        
        <div class="vp_topbar_right">
            <!-- Solicitudes de Insumos -->
            <?php if ($nivel === 'admin' || $nivel === 'almacenista' || $nivel === 'superadministrador'): ?>
            <div class="notification-container">
                <a href="/lugopata/insumos/listarsolicitudes.php" class="topbar-icon">
                    <img src="/lugopata/assets/resources/produccion1.png" alt="Solicitudes">
                    <?php if ($pendientes_insumos > 0): ?>
                        <span class="topbar-badge green-badge"><?= $pendientes_insumos ?></span>
                    <?php endif; ?>
                </a>
            </div>
            <?php endif; ?>
  <!-- Botón Modo Oscuro (versión con emoji si no tienes imagen) 
             <div class="notification-container">
             <button class="topbar-icon dark-mode-toggle" id="darkModeToggle" title="Cambiar modo oscuro/claro" style="font-size: 20px;">
              <span id="darkModeEmoji">🌙</span>
               </button>
             </div> -->
            <!-- Incidencias Generales -->
            <?php if ($nivel === 'admin' || $nivel === 'supmantenimiento' || $nivel === 'superadministrador'): ?>
            <div class="notification-container">
                <a href="/lugopata/incidencias/listarincidencias.php" class="topbar-icon">
                    <img src="/lugopata/assets/resources/identificacionR.png" alt="Incidencias">
                    <?php if ($pendientes > 0): ?>
                        <span class="topbar-badge orange-badge"><?= $pendientes ?></span>
                    <?php endif; ?>
                </a>
            </div>
            <?php endif; ?>

            <!-- Mis Incidencias (obmantenimiento) -->
            <?php if ($nivel === 'obmantenimiento'): ?>
            <div class="notification-container">
                <a href="javascript:void(0)" class="topbar-icon notification-trigger" id="incidenciasTrigger">
                    <img src="/lugopata/assets/resources/incidente.png" alt="Mis Incidencias">
                    <?php if ($pendientes_asignadas > 0): ?>
                        <span class="topbar-badge blue-badge"><?= $pendientes_asignadas ?></span>
                    <?php endif; ?>
                </a>
                
                <div class="notification-dropdown" id="incidenciasDropdown">
                    <div class="notification-header">
                        <h4>Mis Incidencias Pendientes</h4>
                    </div>
                    <div class="notification-content">
                        <?php if (count($detalle_incidencias_asignadas) > 0): ?>
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Descripción</th>
                                        <th>Ubicación</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($detalle_incidencias_asignadas as $incidencia): ?>
                                    <tr>
                                        <td>#<?= $incidencia->id_incidencia ?></td>
                                        <td style="text-align: left;">
                                            <?= htmlspecialchars(substr($incidencia->descripcion, 0, 30)) ?>...
                                            <div style="font-size: 10px; color: #666;"><?= $incidencia->departamento ?></div>
                                        </td>
                                        <td><?= htmlspecialchars($incidencia->ubicacion) ?></td>
                                        <td>
                                            <a href="/lugopata/incidencias/seguimientofases.php?id=<?= $incidencia->id_incidencia ?>" 
                                               class="btn-ver-incidencia">Ver</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="no-alerts">No tienes incidencias pendientes</div>
                        <?php endif; ?>
                    </div>
                    <div class="notification-footer">
                        <a href="/lugopata/incidencias/misincidencias.php?estado=Pendiente&mias=1" class="btn-inventory">
                            Ver todas
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Alertas de Stock -->
            <?php if ($nivel === 'almacenista' || $nivel === 'admin' || $nivel === 'superadministrador'): ?>
            <div class="notification-container">
                <a href="javascript:void(0)" class="topbar-icon notification-trigger" id="notificationTrigger">
                    <img src="/lugopata/assets/resources/stock4.png" alt="Alertas de Stock">
                    <?php if ($alertas_stock > 0): ?>
                        <span class="topbar-badge red-badge"><?= $alertas_stock ?></span>
                    <?php endif; ?>
                </a>
                <div class="notification-dropdown" id="notificationDropdown">
                    <div class="notification-header">
                        <h4>Alertas de Stock</h4>
                    </div>
                    <div class="notification-content">
                        <?php if (count($detalle_alertas) > 0): ?>
                            <?php 
                            $stock_critico = array_filter($detalle_alertas, function($a) {
                                return $a->cantidad <= $a->stock_minimo;
                            });
                            $stock_bajo = array_filter($detalle_alertas, function($a) {
                                return $a->cantidad > $a->stock_minimo && $a->cantidad <= ($a->stock_minimo + 5);
                            });
                            ?>
                            
                            <?php if (count($stock_critico) > 0): ?>
                            <div class="alert-section">
                                <div class="section-title critical">Stock Crítico</div>
                                <div class="stock-grid-container">
                                    <div class="stock-grid-header">Insumo</div>
                                    <div class="stock-grid-header">Actual</div>
                                    <div class="stock-grid-header">Mín</div>
                                    <div class="stock-grid-header">Máx</div>
                                    <div class="stock-grid-header">Acción</div>
                                    
                                    <?php foreach ($stock_critico as $alerta): ?>
                                    <div class="stock-grid-cell">
                                        <div class="insumo-info">
                                            <div class="insumo-name"><?= htmlspecialchars($alerta->nombre) ?></div>
                                            <div class="insumo-id">ID: <?= $alerta->id_insumo ?></div>
                                        </div>
                                    </div>
                                    <div class="stock-grid-cell"><span class="stock-amount critical"><?= $alerta->cantidad ?></span></div>
                                    <div class="stock-grid-cell"><?= $alerta->stock_minimo ?></div>
                                    <div class="stock-grid-cell"><?= $alerta->stock_maximo ?></div>
                                    <div class="stock-grid-cell">
                                        <button class="btn-solicitar" onclick="solicitarCompra('<?= $alerta->id_insumo ?>', '<?= htmlspecialchars($alerta->nombre) ?>', '<?= $alerta->stock_maximo ?>', '<?= $alerta->unidad_medida ?>', '<?= $alerta->cantidad ?>')">
                                            Solicitar
                                        </button>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if (count($stock_bajo) > 0): ?>
                            <div class="alert-section">
                                <div class="section-title warning">Stock Bajo</div>
                                <div class="stock-grid-container">
                                    <div class="stock-grid-header">Insumo</div>
                                    <div class="stock-grid-header">Actual</div>
                                    <div class="stock-grid-header">Mín</div>
                                    <div class="stock-grid-header">Máx</div>
                                    <div class="stock-grid-header">Acción</div>
                                    
                                    <?php foreach ($stock_bajo as $alerta): ?>
                                    <div class="stock-grid-cell">
                                        <div class="insumo-info">
                                            <div class="insumo-name"><?= htmlspecialchars($alerta->nombre) ?></div>
                                            <div class="insumo-id">ID: <?= $alerta->id_insumo ?></div>
                                        </div>
                                    </div>
                                    <div class="stock-grid-cell"><span class="stock-amount warning"><?= $alerta->cantidad ?></span></div>
                                    <div class="stock-grid-cell"><?= $alerta->stock_minimo ?></div>
                                    <div class="stock-grid-cell"><?= $alerta->stock_maximo ?></div>
                                    <div class="stock-grid-cell">
                                        <button class="btn-solicitar" onclick="solicitarCompra('<?= $alerta->id_insumo ?>', '<?= htmlspecialchars($alerta->nombre) ?>', '<?= $alerta->stock_maximo ?>', '<?= $alerta->unidad_medida ?>', '<?= $alerta->cantidad ?>')">
                                            Solicitar
                                        </button>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="no-alerts">Todo el stock está en niveles óptimos</div>
                        <?php endif; ?>
                    </div>
                    <div class="notification-footer">
                        <a href="/lugopata/inventario/listarstock.php" class="btn-inventory">Ver Inventario</a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- CONTENIDO PRINCIPAL (se abre aquí, se cierra en cada página) -->
    <main class="vp_main_content" id="mainContent">

    <!-- TEMPORIZADOR DE SESIÓN -->
    <div id="sessionTimer">
        <span id="timeRemaining"><?php echo sprintf("%02d:%02d", $minutos_restantes, $segundos_restantes); ?></span>
    </div>

    <script>
document.addEventListener('DOMContentLoaded', function() {
    // ===== DROPDOWNS DEL SIDEBAR =====
    // Seleccionar TODOS los botones que tienen data-vp-toggle
    document.querySelectorAll('[data-vp-toggle]').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            e.preventDefault();
            
            // Obtener el ID del dropdown desde el atributo data-dropdown
            const dropdownId = this.getAttribute('data-dropdown');
            const dropdown = document.getElementById(dropdownId);
            
            if (!dropdown) {
                console.log('Dropdown no encontrado:', dropdownId);
                return;
            }
            
            console.log('Clic en dropdown:', dropdownId); // Para debugging
            
            // Cerrar TODOS los otros dropdowns
            document.querySelectorAll('.vp_dropdown_content').forEach(d => {
                if (d.id !== dropdownId) {
                    d.style.display = 'none';
                }
            });
            
            // Alternar el dropdown actual
            if (dropdown.style.display === 'block' || dropdown.style.display === 'flex') {
                dropdown.style.display = 'none';
            } else {
                // Posicionar el dropdown cerca del botón
                const rect = this.getBoundingClientRect();
                dropdown.style.position = 'fixed';
                dropdown.style.top = rect.top + 'px';
                dropdown.style.left = (rect.right + 5) + 'px';
                dropdown.style.display = 'block';
                dropdown.style.zIndex = '20000';
            }
        });
    });

    // Cerrar dropdowns al hacer clic fuera
    document.addEventListener('click', function(e) {
        // Verificar si el clic fue dentro de un dropdown o en un botón
        if (!e.target.closest('.vp_dropdown_content') && !e.target.closest('[data-vp-toggle]')) {
            document.querySelectorAll('.vp_dropdown_content').forEach(d => {
                d.style.display = 'none';
            });
        }
    });

    // Prevenir que los clics dentro del dropdown lo cierren
    document.querySelectorAll('.vp_dropdown_content').forEach(dropdown => {
        dropdown.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    });

    // ===== MANEJO DEL SIDEBAR =====
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const topbar = document.getElementById('topbar');
    const toggleBtn = document.getElementById('sidebarToggle');
    const miniBtn = document.getElementById('sidebarMiniToggle');
    const toggleIcon = document.getElementById('toggleIcon');
    const miniIcon = document.getElementById('miniIcon');
    const overlay = document.getElementById('sidebarOverlay');
    
    // Crear overlay si no existe
    if (!overlay) {
        const newOverlay = document.createElement('div');
        newOverlay.className = 'sidebar-overlay';
        newOverlay.id = 'sidebarOverlay';
        document.body.appendChild(newOverlay);
    }
    
    // Configurar íconos por defecto si no existen
    if (miniIcon && !miniIcon.src.includes('minimizar')) {
        miniIcon.src = "/lugopata/assets/resources/menu-mini.png";
        miniIcon.alt = "Minimizar";
    }

    // Estado inicial
    const isMobile = window.innerWidth <= 768;
    const savedState = localStorage.getItem('sidebarState');
    
    if (isMobile) {
        // Modo móvil: sidebar oculto por defecto
        sidebar.classList.add('hidden');
        sidebar.classList.remove('minimized');
        mainContent.classList.add('expanded');
        topbar.classList.add('expanded');
        if (toggleIcon) toggleIcon.src = "/lugopata/assets/resources/menu-show.png";
    } else {
        // Modo escritorio: restaurar estado guardado
        if (savedState === 'minimized') {
            sidebar.classList.add('minimized');
            sidebar.classList.remove('hidden');
            mainContent.classList.add('minimized');
            topbar.classList.add('minimized');
            if (miniIcon) miniIcon.src = "/lugopata/assets/resources/menu-maximizar.png";
        } else if (savedState === 'hidden') {
            sidebar.classList.add('hidden');
            mainContent.classList.add('expanded');
            topbar.classList.add('expanded');
            if (toggleIcon) toggleIcon.src = "/lugopata/assets/resources/menu-show.png";
        } else {
            sidebar.classList.remove('minimized', 'hidden');
            mainContent.classList.remove('minimized', 'expanded');
            topbar.classList.remove('minimized', 'expanded');
            if (toggleIcon) toggleIcon.src = "/lugopata/assets/resources/menu-icon1.png";
            if (miniIcon) miniIcon.src = "/lugopata/assets/resources/menu-mini.png";
        }
    }

    // Toggle mostrar/ocultar sidebar
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            const currentIsMobile = window.innerWidth <= 768;
            
            if (sidebar.classList.contains('hidden')) {
                // Mostrar sidebar
                sidebar.classList.remove('hidden');
                if (!currentIsMobile) {
                    sidebar.classList.remove('minimized');
                    mainContent.classList.remove('minimized');
                    topbar.classList.remove('minimized');
                    if (miniIcon) miniIcon.src = "/lugopata/assets/resources/menu-mini.png";
                }
                mainContent.classList.remove('expanded');
                topbar.classList.remove('expanded');
                toggleIcon.src = "/lugopata/assets/resources/menu-icon1.png";
                
                if (currentIsMobile) {
                    document.getElementById('sidebarOverlay').classList.add('active');
                    document.body.style.overflow = 'hidden';
                }
                
                localStorage.setItem('sidebarState', 'visible');
            } else {
                // Ocultar sidebar
                sidebar.classList.add('hidden');
                mainContent.classList.add('expanded');
                topbar.classList.add('expanded');
                toggleIcon.src = "/lugopata/assets/resources/menu-show.png";
                
                if (currentIsMobile) {
                    document.getElementById('sidebarOverlay').classList.remove('active');
                    document.body.style.overflow = '';
                }
                
                localStorage.setItem('sidebarState', 'hidden');
            }
        });
    }

    // Toggle minimizar/expandir sidebar
    if (miniBtn) {
        miniBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            if (window.innerWidth <= 768) return;
            
            if (sidebar.classList.contains('minimized')) {
                // Expandir
                sidebar.classList.remove('minimized');
                mainContent.classList.remove('minimized');
                topbar.classList.remove('minimized');
                miniIcon.src = "/lugopata/assets/resources/menu-mini.png";
                localStorage.setItem('sidebarState', 'visible');
            } else {
                // Minimizar
                sidebar.classList.add('minimized');
                sidebar.classList.remove('hidden');
                mainContent.classList.add('minimized');
                topbar.classList.add('minimized');
                mainContent.classList.remove('expanded');
                topbar.classList.remove('expanded');
                toggleIcon.src = "/lugopata/assets/resources/menu-icon1.png";
                miniIcon.src = "/lugopata/assets/resources/menu-maximizar.png";
                localStorage.setItem('sidebarState', 'minimized');
            }
        });
    }

    // Cerrar sidebar con overlay
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', function() {
            if (window.innerWidth <= 768 && !sidebar.classList.contains('hidden')) {
                toggleBtn.click();
            }
        });
    }

    // ===== NOTIFICACIONES =====
    function setupNotification(triggerId, dropdownId) {
        const trigger = document.getElementById(triggerId);
        const dropdown = document.getElementById(dropdownId);
        
        if (trigger && dropdown) {
            trigger.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // Cerrar otros dropdowns de notificaciones
                document.querySelectorAll('.notification-dropdown.show').forEach(d => {
                    if (d.id !== dropdownId) d.classList.remove('show');
                });
                
                dropdown.classList.toggle('show');
            });
            
            document.addEventListener('click', function(e) {
                if (!trigger.contains(e.target) && !dropdown.contains(e.target)) {
                    dropdown.classList.remove('show');
                }
            });
            
            dropdown.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        }
    }

    setupNotification('incidenciasTrigger', 'incidenciasDropdown');
    setupNotification('notificationTrigger', 'notificationDropdown');

    // ===== TEMPORIZADOR =====
    let timeRemaining = <?= $tiempo_restante ?>;
    const timerElement = document.getElementById('timeRemaining');
    const timerContainer = document.getElementById('sessionTimer');
    
    if (timerElement && timerContainer) {
        function updateTimer() {
            if (timeRemaining <= 0) {
                window.location.href = '/lugopata/index.php?error=session_expired';
                return;
            }
            
            timeRemaining--;
            const minutes = Math.floor(timeRemaining / 60);
            const seconds = timeRemaining % 60;
            timerElement.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            
            if (timeRemaining <= 60) {
                timerContainer.style.background = '#e74c3c';
            } else if (timeRemaining <= 120) {
                timerContainer.style.background = '#f39c12';
            }
        }
        
        setInterval(updateTimer, 1000);
    }

    // ===== RESIZE HANDLER =====
    let resizeTimeout;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(function() {
            const nowMobile = window.innerWidth <= 768;
            
            if (nowMobile) {
                // Cambió a móvil
                sidebar.classList.add('hidden');
                sidebar.classList.remove('minimized');
                mainContent.classList.add('expanded');
                mainContent.classList.remove('minimized');
                topbar.classList.add('expanded');
                topbar.classList.remove('minimized');
                toggleIcon.src = "/lugopata/assets/resources/menu-show.png";
                if (sidebarOverlay) sidebarOverlay.classList.remove('active');
                document.body.style.overflow = '';
            } else {
                // Cambió a escritorio, restaurar estado guardado
                const saved = localStorage.getItem('sidebarState');
                if (saved === 'minimized') {
                    sidebar.classList.add('minimized');
                    sidebar.classList.remove('hidden');
                    mainContent.classList.add('minimized');
                    mainContent.classList.remove('expanded');
                    topbar.classList.add('minimized');
                    topbar.classList.remove('expanded');
                    toggleIcon.src = "/lugopata/assets/resources/menu-icon1.png";
                    if (miniIcon) miniIcon.src = "/lugopata/assets/resources/menu-maximizar.png";
                } else if (saved === 'hidden') {
                    sidebar.classList.add('hidden');
                    mainContent.classList.add('expanded');
                    topbar.classList.add('expanded');
                    toggleIcon.src = "/lugopata/assets/resources/menu-show.png";
                } else {
                    sidebar.classList.remove('minimized', 'hidden');
                    mainContent.classList.remove('minimized', 'expanded');
                    topbar.classList.remove('minimized', 'expanded');
                    toggleIcon.src = "/lugopata/assets/resources/menu-icon1.png";
                    if (miniIcon) miniIcon.src = "/lugopata/assets/resources/menu-mini.png";
                }
            }
        }, 250);
    });

    // ===== DEBUG: Verificar que los botones tienen los atributos correctos =====
    console.log('Botones con data-vp-toggle:', document.querySelectorAll('[data-vp-toggle]').length);
    document.querySelectorAll('[data-vp-toggle]').forEach(btn => {
        console.log('Botón:', btn, 'data-dropdown:', btn.getAttribute('data-dropdown'));
    });
});

// Función para solicitar compra
function solicitarCompra(idInsumo, nombreInsumo, stockMaximo, unidadMedida, stockActual) {
    const cantidadSugerida = Math.max(1, stockMaximo - stockActual);
    const url = new URL('/lugopata/inventario/formsolicitudsalida.php', window.location.origin);
    url.searchParams.append('insumo_id', idInsumo);
    url.searchParams.append('insumo_nombre', nombreInsumo);
    url.searchParams.append('cantidad_sugerida', cantidadSugerida);
    url.searchParams.append('unidad_medida', unidadMedida);
    url.searchParams.append('origen', 'alerta_stock');
    window.location.href = url.toString();
}

// ===== MODO OSCURO MEJORADO =====
(function() {
    const darkModeToggle = document.getElementById('darkModeToggle');
    const darkModeIcon = document.getElementById('darkModeIcon');
    const darkModeEmoji = document.getElementById('darkModeEmoji');
    
    // Configuración
    const CONFIG = {
        storageKey: 'darkMode',
        transitionDuration: 300, // ms
        defaultMode: false, // false = claro, true = oscuro
    };
    
    // Verificar preferencia guardada o del sistema
    function getInitialMode() {
        const saved = localStorage.getItem(CONFIG.storageKey);
        if (saved !== null) {
            return saved === 'enabled';
        }
        // Si no hay preferencia guardada, usar la del sistema
        return window.matchMedia('(prefers-color-scheme: dark)').matches;
    }
    
    const isDarkMode = getInitialMode();
    
    // Función para actualizar el ícono
    function updateDarkModeIcon(isDark) {
        if (darkModeIcon) {
            darkModeIcon.src = isDark 
                ? '/lugopata/assets/resources/modo-claro.png' 
                : '/lugopata/assets/resources/modo-oscuro.png';
            darkModeIcon.alt = isDark ? 'Modo Claro' : 'Modo Oscuro';
        } else if (darkModeEmoji) {
            darkModeEmoji.textContent = isDark ? '☀️' : '🌙';
        }
        
        if (darkModeToggle) {
            darkModeToggle.title = isDark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro';
        }
    }
    
    // Función para aplicar modo con transición
    function setDarkMode(isDark) {
        if (isDark) {
            document.body.classList.add('dark-mode');
        } else {
            document.body.classList.remove('dark-mode');
        }
        
        localStorage.setItem(CONFIG.storageKey, isDark ? 'enabled' : 'disabled');
        updateDarkModeIcon(isDark);
        
        // Disparar evento personalizado para otros componentes
        document.dispatchEvent(new CustomEvent('darkModeChange', { 
            detail: { isDark } 
        }));
        
        // Opcional: recargar estilos específicos si es necesario
        // Esto ayuda con componentes que no responden bien a !important
        if (window.reloadDarkModeStyles) {
            window.reloadDarkModeStyles(isDark);
        }
    }
    
    // Aplicar modo inicial con un pequeño retraso para evitar parpadeos
    setTimeout(() => {
        setDarkMode(isDarkMode);
    }, 10);
    
    // Evento click para toggle
    if (darkModeToggle) {
        darkModeToggle.addEventListener('click', function() {
            const isDark = !document.body.classList.contains('dark-mode');
            setDarkMode(isDark);
        });
    }
    
    // Escuchar cambios en el sistema operativo
    const prefersDarkScheme = window.matchMedia('(prefers-color-scheme: dark)');
    prefersDarkScheme.addEventListener('change', (e) => {
        // Solo aplicar si NO hay preferencia guardada
        if (!localStorage.getItem(CONFIG.storageKey)) {
            setDarkMode(e.matches);
        }
    });
    
    // Función de utilidad para que otros scripts puedan consultar el estado
    window.isDarkMode = function() {
        return document.body.classList.contains('dark-mode');
    };
})();

// DEBUG TEMPORAL
setTimeout(() => {
    console.log('=== VERIFICACIÓN DE DROPDOWNS ===');
    document.querySelectorAll('[data-vp-toggle]').forEach((btn, i) => {
        console.log(`Botón ${i}:`, {
            elemento: btn,
            texto: btn.innerText,
            dropdownId: btn.dataset.dropdown,
            existeDropdown: document.getElementById(btn.dataset.dropdown) ? 'SÍ' : 'NO'
        });
    });
}, 1000);

</script>