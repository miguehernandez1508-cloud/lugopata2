<?php
session_start();
include_once "../encabezado.php";
require_once "../conex.php";
require_once "../user/gestorsesion.php";
require_once "solicitud.php";

// Validación de sesión
GestorSesiones::iniciar();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar'])) {
    $id_solicitud = $_POST['id_solicitud'];
    $solicitudObj = new Solicitud($conexion);
    if ($solicitudObj->eliminarSolicitud($id_solicitud)) {
        echo "<script>alert('Solicitud eliminada'); window.location='listarsolicitudes.php';</script>";
        exit;
    }
}

$usuario_actual = GestorSesiones::get('nombre_completo'); 
$nivel_actual = GestorSesiones::get('nivel');
 
// Paginación
$registrosPorPagina = 10;
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
if ($pagina < 1) $pagina = 1;
$inicio = ($pagina - 1) * $registrosPorPagina;

$solicitudObj = new Solicitud($conexion);
$totalRegistros = $solicitudObj->contar();
$totalPaginas = ceil($totalRegistros / $registrosPorPagina);

$filtro = isset($_GET['estado']) ? $_GET['estado'] : 'todos';

$solicitudes = $solicitudObj->listar($inicio, $registrosPorPagina);

$solicitudes = array_filter($solicitudes, function($s) use ($filtro, $nivel_actual) {
    $estado = $s->estado;

    if ($nivel_actual === "almacenista" && $estado !== "Pendiente") {
        return false;
    }

    switch($filtro) {
        case 'todos': return true;
        case 'pendiente_espera': return $estado === 'Pendiente' || $estado === 'En espera';
        case 'pendiente': return $estado === 'Pendiente';
        case 'espera': return $estado === 'En espera';
        case 'finalizada': return $estado === 'Finalizada';
        default: return true;
    }
});
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
<title>Lista de Solicitudes de Insumos</title>
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
    .solicitudes-wrapper {
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

    /* ===== BOTÓN AGREGAR ===== */
    .btn-agregar-container {
        margin-top: 15px !important;
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
        transform: translateY(-2px) !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2) !important;
        opacity: 0.95 !important;
    }

    .btn-success {
        background-color: #198754 !important;
    }

    .btn-icon {
        width: 20px !important;
        height: 20px !important;
        flex-shrink: 0 !important;
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

    /* ===== MENSAJE ÉXITO ===== */
    .mensaje-exito {
        background-color: #238f3c !important;
        color: #ecf3ed !important;
        padding: 15px 20px !important;
        border-radius: 10px !important;
        margin-bottom: 20px !important;
        text-align: center !important;
        font-weight: bold !important;
        font-size: clamp(14px, 3vw, 16px) !important;
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
    .solicitudes-grid {
        display: grid !important;
        grid-template-columns: 1fr !important;
        gap: 15px !important;
        margin-top: 20px !important;
    }

    /* CORRECCIÓN: Tarjeta con fondo fijo, sin color de estado */
    .solicitud-card {
        background: #f8f9fa !important;
        border-radius: 12px !important;
        padding: 20px !important;
        border: 2px solid #dee2e6 !important;
        transition: all 0.3s ease !important;
        position: relative !important;
        overflow: hidden !important;
    }

    /* ELIMINADO: Las clases de estado ya no aplican fondo de color */
    .solicitud-card:hover {
        transform: translateY(-3px) !important;
        box-shadow: 0 8px 20px rgba(0,0,0,0.1) !important;
    }

    .solicitud-header {
        display: flex !important;
        justify-content: space-between !important;
        align-items: flex-start !important;
        margin-bottom: 15px !important;
        flex-wrap: wrap !important;
        gap: 10px !important;
    }

    .solicitud-id {
        font-size: clamp(20px, 4vw, 24px) !important;
        font-weight: bold !important;
        color: #333 !important;
        font-family: monospace !important;
    }

    .solicitud-fecha {
        font-size: 12px !important;
        color: #6c757d !important;
        background: white !important;
        padding: 5px 10px !important;
        border-radius: 20px !important;
        border: 1px solid #dee2e6 !important;
    }

    .solicitud-body {
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

    .info-value.descripcion {
        text-align: left !important;
        max-width: 100% !important;
        width: 100% !important;
        margin-top: 5px !important;
        padding: 10px !important;
        background: white !important;
        border-radius: 6px !important;
        border: 1px solid #e9ecef !important;
    }

    /* ===== ESTADOS MEJORADOS ===== */
    .estado-badge {
        padding: 6px 12px !important;
        border-radius: 20px !important;
        font-weight: bold !important;
        font-size: 12px !important;
        display: inline-block !important;
        width: fit-content !important;
        min-width: auto !important;
        max-width: 100% !important;
        white-space: nowrap !important;
    }

    @media (max-width: 480px) {
        .solicitud-header .estado-badge {
            margin-top: 5px !important;
        }
    }

    .estado-finalizada {
        background-color: #28a745 !important;
        color: white !important;
    }

    .estado-pendiente {
        background-color: #ffc107 !important;
        color: #000 !important;
    }

    .estado-espera {
        background-color: #17a2b8 !important;
        color: white !important;
    }

    /* ===== ACCIONES MEJORADAS PARA MÓVIL ===== */
    .acciones-container {
        margin-top: 15px !important;
        padding-top: 15px !important;
        border-top: 2px solid #dee2e6 !important;
    }

    /* Botón principal de acciones - más atractivo */
    .btn-acciones-principal {
        width: 100% !important;
        padding: 14px !important;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
        border: none !important;
        border-radius: 12px !important;
        cursor: pointer !important;
        font-size: 16px !important;
        font-weight: 600 !important;
        color: white !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 10px !important;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3) !important;
        position: relative !important;
        overflow: hidden !important;
    }

    .btn-acciones-principal::before {
        content: '' !important;
        position: absolute !important;
        top: 0 !important;
        left: -100% !important;
        width: 100% !important;
        height: 100% !important;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent) !important;
        transition: left 0.6s ease !important;
    }

    .btn-acciones-principal:hover {
        transform: translateY(-3px) !important;
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4) !important;
    }

    .btn-acciones-principal:hover::before {
        left: 100% !important;
    }

    .btn-acciones-principal.active {
        background: linear-gradient(135deg, #764ba2 0%, #667eea 100%) !important;
        transform: scale(0.98) !important;
    }

    .btn-acciones-principal .flecha {
        font-size: 14px !important;
        transition: transform 0.3s ease !important;
    }

    .btn-acciones-principal.active .flecha {
        transform: rotate(180deg) !important;
    }

    /* Grid de acciones - más moderno */
    .acciones-grid-moderno {
        display: none !important;
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 10px !important;
        margin-top: 15px !important;
        padding: 15px !important;
        background: white !important;
        border-radius: 16px !important;
        border: 1px solid #e0e0e0 !important;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1) !important;
        animation: slideDown 0.3s ease-out !important;
    }

    .acciones-grid-moderno.show {
        display: grid !important;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Botones de acción mejorados */
    .btn-accion-moderno {
        padding: 15px 8px !important;
        border-radius: 12px !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        text-decoration: none !important;
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 8px !important;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
        border: none !important;
        cursor: pointer !important;
        min-height: 85px !important;
        color: white !important;
        position: relative !important;
        overflow: hidden !important;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1) !important;
    }

    .btn-accion-moderno::after {
        content: '' !important;
        position: absolute !important;
        top: 50% !important;
        left: 50% !important;
        width: 0 !important;
        height: 0 !important;
        border-radius: 50% !important;
        background: rgba(255,255,255,0.3) !important;
        transform: translate(-50%, -50%) !important;
        transition: width 0.3s ease, height 0.3s ease !important;
    }

    .btn-accion-moderno:hover {
        transform: translateY(-5px) !important;
        box-shadow: 0 8px 15px rgba(0,0,0,0.2) !important;
    }

    .btn-accion-moderno:hover::after {
        width: 150px !important;
        height: 150px !important;
    }

    .btn-accion-moderno:active {
        transform: translateY(-2px) !important;
    }

    .btn-accion-moderno img {
        width: 24px !important;
        height: 24px !important;
        filter: brightness(0) invert(1) !important;
        transition: transform 0.3s ease !important;
    }

    .btn-accion-moderno:hover img {
        transform: scale(1.1) !important;
    }

    /* Colores específicos para cada acción */
    .btn-recepcion-moderno {
        background: linear-gradient(135deg, #0d6efd, #0a58ca) !important;
    }

    .btn-revisar-moderno {
        background: linear-gradient(135deg, #fd7e14, #e66a02) !important;
    }

    .btn-pdf-moderno {
        background: linear-gradient(135deg, #8a929c, #5d646e) !important;
    }

    .btn-editar-moderno {
        background: linear-gradient(135deg, #ffc107, #e0a800) !important;
    }

    .btn-editar-moderno img {
        filter: brightness(0) !important;
    }

    .btn-eliminar-moderno {
        background: linear-gradient(135deg, #dc3545, #b02a37) !important;
    }

    /* Para móvil muy pequeño */
    @media (max-width: 380px) {
        .acciones-grid-moderno {
            grid-template-columns: 1fr !important;
        }
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

    tbody tr:nth-child(even) {
        background-color: #f8f9fa !important;
    }

    tbody tr:hover {
        background-color: #e9ecef !important;
    }

    /* Menú desplegable en tabla */
    .dropdown-acciones {
        position: relative !important;
        display: inline-block !important;
    }

    .btn-dropdown {
        background: #f8f9fa !important;
        border: 2px solid #dee2e6 !important;
        padding: 8px 15px !important;
        border-radius: 8px !important;
        cursor: pointer !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        color: #495057 !important;
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
        transition: all 0.2s ease !important;
        white-space: nowrap !important;
    }

    .btn-dropdown:hover {
        background: #e9ecef !important;
        border-color: #adb5bd !important;
    }

    .btn-dropdown .arrow {
        font-size: 10px !important;
        transition: transform 0.2s ease !important;
    }

    .btn-dropdown.active .arrow {
        transform: rotate(180deg) !important;
    }

    .dropdown-menu {
        display: none !important;
        position: absolute !important;
        right: 0 !important;
        top: 100% !important;
        margin-top: 5px !important;
        background: white !important;
        border: 2px solid #dee2e6 !important;
        border-radius: 10px !important;
        box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
        min-width: 160px !important;
        z-index: 1000 !important;
        overflow: hidden !important;
    }

    .dropdown-menu.show {
        display: block !important;
    }

    .dropdown-item {
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
        padding: 10px 15px !important;
        text-decoration: none !important;
        color: #333 !important;
        font-size: 13px !important;
        background: none !important;
        border: none !important;
        width: 100% !important;
        text-align: left !important;
        cursor: pointer !important;
        transition: background 0.2s ease !important;
        border-bottom: 1px solid #f0f0f0 !important;
    }

    .dropdown-item:last-child {
        border-bottom: none !important;
    }

    .dropdown-item:hover {
        background: #f8f9fa !important;
    }

    .dropdown-item.text-danger {
        color: #dc3545 !important;
    }

    .dropdown-item.text-danger:hover {
        background: #f8d7da !important;
    }

    .dropdown-item .btn-icon {
        width: 16px !important;
        height: 16px !important;
        flex-shrink: 0 !important;
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

    .paginacion-info {
        color: #666 !important;
        font-size: 14px !important;
        margin-top: 10px !important;
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

    /* ===== CORRECCIÓN: MODAL ADMIN SIMPLIFICADO ===== */
    #modalAdmin {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }

    /* Contenedor del modal - diseño simple y limpio */
    #modalAdmin > div {
        background: white;
        padding: 30px;
        border-radius: 12px;
        width: 90%;
        max-width: 400px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        position: relative;
        text-align: center;
    }

    /* Título del modal */
    #modalAdmin h2 {
        margin: 0 0 20px 0;
        font-size: 1.5rem;
        font-weight: 600;
        color: #333;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }

    /* Campo de contraseña */
    #claveAdmin {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #dee2e6;
        border-radius: 8px;
        font-size: 16px;
        margin-bottom: 20px;
        box-sizing: border-box;
        transition: border-color 0.3s ease;
    }

    #claveAdmin:focus {
        outline: none;
        border-color: #0d6efd;
    }

    #claveAdmin.error {
        border-color: #dc3545;
        animation: shake 0.3s ease;
    }

    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-5px); }
        75% { transform: translateX(5px); }
    }

    /* Contenedor de botones */
    #modalAdmin > div > div:first-of-type {
        display: flex;
        gap: 10px;
        justify-content: center;
        margin-bottom: 10px;
    }

    /* Botones del modal */
    #modalAdmin button {
        padding: 10px 25px;
        border: none;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        min-width: 100px;
    }

    /* Botón Aceptar */
    #modalAdmin .btn-success {
        background-color: #0d6efd;
        color: white;
    }

    #modalAdmin .btn-success:hover {
        background-color: #0b5ed7;
    }

    /* Botón Cancelar */
    #modalAdmin button[style*="background-color: #6c757d"] {
        background-color: #6c757d !important;
        color: white;
    }

    #modalAdmin button[style*="background-color: #6c757d"]:hover {
        background-color: #5a6268 !important;
    }

    /* Mensaje de error */
    #mensajeError {
        color: #dc3545;
        font-size: 14px;
        margin-top: 10px;
        font-weight: 500;
    }

    /* ===== MEDIA QUERIES ===== */
    @media (min-width: 768px) {
        body {
            padding: 20px !important;
        }

        .solicitudes-wrapper {
            padding: 15px !important;
        }

        .solicitudes-grid {
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
        .solicitudes-wrapper {
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

        .solicitud-card {
            padding: 15px !important;
        }

        .solicitud-header {
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
    }
</style>
</head>
<body>
    <div class="solicitudes-wrapper">
        <!-- Título -->
        <div class="title-card">
            <h1>
                <img src="../assets/resources/insumos.png" alt="Solicitudes">
                LISTA DE SOLICITUDES DE INSUMOS
            </h1>
            <p>Seguimiento de solicitudes realizadas</p>
            
            <div class="btn-agregar-container">
                <a href="formsolicitudinsumos.php" class="btn btn-success">
                    <img src="../assets/resources/maso.png" alt="Solicitar" class="btn-icon" style="filter: brightness(0) invert(0.8) !important;">
                    Solicitar Insumos
                </a>
            </div>
        </div>

        <!-- Contenido -->
        <div class="content-card">
            <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] === 'recepcion_exitosa'): ?>
                <div class="mensaje-exito">
                    Recepción registrada exitosamente.
                </div>
            <?php endif; ?>
            
            <!-- Filtros -->
            <div class="filtros-container">
                <form method="get" class="filtro-group">
                    <label for="estado">Estado:</label>
                    <select name="estado" id="estado" onchange="this.form.submit()">
                        <option value="pendiente_espera" <?= $filtro === 'pendiente_espera' ? 'selected' : '' ?>>Pendiente + En espera</option>
                        <option value="pendiente" <?= $filtro === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                        <option value="espera" <?= $filtro === 'espera' ? 'selected' : '' ?>>En espera</option>
                        <option value="finalizada" <?= $filtro === 'finalizada' ? 'selected' : '' ?>>Finalizada</option>
                        <option value="todos" <?= $filtro === 'todos' ? 'selected' : '' ?>>Todos</option>
                    </select>
                </form>
            </div>

            <?php if (empty($solicitudes)): ?>
                <!-- Sin datos -->
                <div class="mensaje-sin-datos">
                    <h3>No hay solicitudes para estos filtros</h3>
                    <p>Intenta seleccionar otro estado o crea una nueva solicitud.</p>
                </div>
            <?php else: ?>
                <!-- Vista Grid para Móvil -->
                <div class="solicitudes-grid">
                    <?php foreach ($solicitudes as $s): 
                        $estado_class = '';
                        if($s->estado === "Finalizada") { $estado_class = 'estado-finalizada'; }
                        elseif($s->estado === "Pendiente") { $estado_class = 'estado-pendiente'; }
                        else { $estado_class = 'estado-espera'; }
                    ?>
                    <!-- CORRECCIÓN: Eliminadas las clases estado-finalizada, estado-pendiente, estado-espera de la tarjeta -->
                    <div class="solicitud-card">
                        <div class="solicitud-header">
                            <div>
                                <div class="solicitud-id">#<?= $s->id_solicitud ?></div>
                                <span class="estado-badge <?= $estado_class ?>" style="margin-top: 8px;">
                                    <?= $s->estado ?>
                                </span>
                            </div>
                            <div class="solicitud-fecha"><?= date('d/m/Y', strtotime($s->fecha)) ?></div>
                        </div>
                        
                        <div class="solicitud-body">
                            <div class="info-row">
                                <span class="info-label">Receptor:</span>
                                <span class="info-value"><?= htmlspecialchars($s->receptor) ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">De:</span>
                                <span class="info-value"><?= htmlspecialchars($s->departamento_emisor) ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Para:</span>
                                <span class="info-value"><?= htmlspecialchars($s->departamento_destino) ?></span>
                            </div>
                            <div class="info-row" style="flex-direction: column; align-items: flex-start;">
                                <span class="info-label">Descripción:</span>
                                <span class="info-value descripcion"><?= htmlspecialchars($s->descripcion) ?></span>
                            </div>
                        </div>
                        
                        <!-- ACCIONES MEJORADAS PARA MÓVIL -->
                        <div class="acciones-container">
                            <button type="button" class="btn-acciones-principal" onclick="toggleAccionesModerno('acciones-<?= $s->id_solicitud ?>', this)">
                                <span><img src="../assets/resources/Configuraciones.png" alt="Configuraciones" class="btn-icon" style="filter: brightness(0) invert(1) !important;"> Acciones disponibles</span>
                                <span class="flecha">▼</span>
                            </button>
                            
                            <div id="acciones-<?= $s->id_solicitud ?>" class="acciones-grid-moderno">
                                <?php if ($s->estado !== "Finalizada" && ($nivel_actual === "almacenista" || $nivel_actual === "superadministrador")): ?>
                                    <a href="formrecepcion.php?id_solicitud=<?= $s->id_solicitud ?>" class="btn-accion-moderno btn-recepcion-moderno">
                                        <img src="../assets/resources/campana.png" alt="Recepción">
                                        <span>Recepción</span>
                                    </a>
                                <?php endif; ?>

                                <?php if ($nivel_actual === "admin" || $nivel_actual === "superadministrador"): ?>
                                    <a href="revisarsolicitud.php?id=<?= $s->id_solicitud ?>" class="btn-accion-moderno btn-revisar-moderno">
                                        <img src="../assets/resources/auditoria.png" alt="Revisar">
                                        <span>Revisar</span>
                                    </a>
                                <?php endif; ?>

                                <a href="imprimirsolicitud.php?id=<?= $s->id_solicitud ?>" target="_blank" class="btn-accion-moderno btn-pdf-moderno">
                                    <img src="../assets/resources/pdf.png" alt="PDF">
                                    <span>PDF</span>
                                </a>

                                <?php if ($s->estado === 'En espera' && ($s->emisor === $usuario_actual || $nivel_actual === 'superadministrador' || $nivel_actual === 'admin')): ?>
                                    <button type="button" class="btn-accion-moderno btn-editar-moderno" onclick="abrirModalAdmin(<?= $s->id_solicitud ?>)">
                                        <img src="../assets/resources/editarU.png" alt="Editar" style="filter: brightness(0) invert(1) !important;">
                                        <span>Editar</span>
                                    </button>
                                <?php endif; ?>

                                <?php if (($nivel_actual === "admin" || $nivel_actual === "superadministrador") && $s->estado !== "Finalizada"): ?>
                                    <form method="post" style="display: contents;" onsubmit="return confirm('¿Seguro que deseas eliminar esta solicitud?');">
                                        <input type="hidden" name="id_solicitud" value="<?= $s->id_solicitud ?>">
                                        <button type="submit" name="eliminar" class="btn-accion-moderno btn-eliminar-moderno">
                                            <img src="../assets/resources/eliminar2.png" alt="Eliminar">
                                            <span>Eliminar</span>
                                        </button>
                                    </form>
                                <?php endif; ?>
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
                                <th>Fecha</th>
                                <th>Receptor</th>
                                <th>Dept. Emisor</th>
                                <th>Dept. Destino</th>
                                <th>Descripción</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($solicitudes as $s): 
                                $menuId = 'menu-' . $s->id_solicitud;
                            ?>
                            <tr>
                                <td><?= $s->id_solicitud ?></td>
                                <td><?= $s->fecha ?></td>
                                <td><?= htmlspecialchars($s->receptor) ?></td>
                                <td><?= htmlspecialchars($s->departamento_emisor) ?></td>
                                <td><?= htmlspecialchars($s->departamento_destino) ?></td>
                                <td><?= htmlspecialchars($s->descripcion) ?></td>
                                <td>
                                    <span class="estado-badge <?= $s->estado === 'Finalizada' ? 'estado-finalizada' : ($s->estado === 'Pendiente' ? 'estado-pendiente' : 'estado-espera') ?>">
                                        <?= $s->estado ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="dropdown-acciones">
                                        <button type="button" class="btn-dropdown" onclick="toggleMenu('<?= $menuId ?>', event)">
                                            <span><img src="../assets/resources/Configuraciones.png" alt="Configuraciones" class="btn-icon"> Acciones</span>
                                            <span class="arrow">▼</span>
                                        </button>
                                        
                                        <div id="<?= $menuId ?>" class="dropdown-menu">
                                            <?php if ($s->estado !== "Finalizada" && ($nivel_actual === "almacenista" || $nivel_actual === "superadministrador")): ?>
                                                <a href="formrecepcion.php?id_solicitud=<?= $s->id_solicitud ?>" class="dropdown-item">
                                                    <img src="../assets/resources/campana2.png" class="btn-icon"> Recepción
                                                </a>
                                            <?php endif; ?>

                                            <?php if ($nivel_actual === "admin" || $nivel_actual === "superadministrador"): ?>
                                                <a href="revisarsolicitud.php?id=<?= $s->id_solicitud ?>" class="dropdown-item">
                                                    <img src="../assets/resources/revi.png" class="btn-icon"> Revisar
                                                </a>
                                            <?php endif; ?>

                                            <a href="imprimirsolicitud.php?id=<?= $s->id_solicitud ?>" target="_blank" class="dropdown-item">
                                                <img src="../assets/resources/documento.png" class="btn-icon"> PDF
                                            </a>

                                            <?php if ($s->estado === 'En espera' && ($s->emisor === $usuario_actual || $nivel_actual === 'superadministrador')): ?>
                                                <button type="button" class="dropdown-item" onclick="abrirModalAdmin(<?= $s->id_solicitud ?>); cerrarTodosMenus();">
                                                    <img src="../assets/resources/editarU.png" class="btn-icon"> Editar
                                                </button>
                                            <?php endif; ?>

                                            <?php if (($nivel_actual === "admin" || $nivel_actual === "superadministrador") && $s->estado !== "Finalizada"): ?>
                                                <form method="post" style="margin: 0;" onsubmit="return confirm('¿Seguro que deseas eliminar esta solicitud?');">
                                                    <input type="hidden" name="id_solicitud" value="<?= $s->id_solicitud ?>">
                                                    <button type="submit" name="eliminar" class="dropdown-item text-danger">
                                                        <img src="../assets/resources/eliminar2.png" class="btn-icon"> Eliminar
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>
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
                        <a href="?pagina=<?= $pagina - 1 ?>&estado=<?= urlencode($filtro) ?>" class="btn-paginacion">← Anterior</a>
                    <?php else: ?>
                        <span class="btn-paginacion disabled">← Anterior</span>
                    <?php endif; ?>
                    
                    <span class="paginacion-info">
                        Página <?= $pagina ?> de <?= $totalPaginas ?>
                    </span>
                    
                    <?php if ($pagina < $totalPaginas): ?>
                        <a href="?pagina=<?= $pagina + 1 ?>&estado=<?= urlencode($filtro) ?>" class="btn-paginacion">Siguiente →</a>
                    <?php else: ?>
                        <span class="btn-paginacion disabled">Siguiente →</span>
                    <?php endif; ?>
                </div>
                <div class="paginacion-info" style="margin-top: 5px;">
                    Mostrando <?= (($pagina - 1) * $registrosPorPagina) + 1 ?> - <?= min($pagina * $registrosPorPagina, $totalRegistros) ?> de <?= $totalRegistros ?> solicitudes
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal para ingresar clave de admin (CORREGIDO) -->
    <div id="modalAdmin">
        <div>
            <h2>Clave de Admin Requerida</h2>
            <input type="password" id="claveAdmin" placeholder="Ingrese contraseña">
            <input type="hidden" id="solicitudId">
            <div>
                <button onclick="verificarClave()" class="btn-success">Aceptar</button>
                <button onclick="cerrarModal()" style="background-color: #6c757d; color: white;">Cancelar</button>
            </div>
            <div id="mensajeError"></div>
        </div>
    </div>

    <script>
        // Toggle acciones moderno para móvil
        function toggleAccionesModerno(gridId, btn) {
            const grid = document.getElementById(gridId);
            
            if (grid.classList.contains('show')) {
                grid.classList.remove('show');
                btn.classList.remove('active');
            } else {
                // Cerrar otros grids
                document.querySelectorAll('.acciones-grid-moderno').forEach(g => g.classList.remove('show'));
                document.querySelectorAll('.btn-acciones-principal').forEach(b => b.classList.remove('active'));
                
                grid.classList.add('show');
                btn.classList.add('active');
                
                // Scroll suave hacia el grid
                setTimeout(() => {
                    grid.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }, 100);
            }
        }

        // Toggle menú desplegable en escritorio
        function toggleMenu(menuId, event) {
            document.querySelectorAll('.dropdown-menu').forEach(menu => {
                if (menu.id !== menuId) {
                    menu.classList.remove('show');
                }
            });
            
            document.querySelectorAll('.btn-dropdown').forEach(btn => {
                btn.classList.remove('active');
            });
            
            const menu = document.getElementById(menuId);
            const btn = event.currentTarget;
            
            if (menu.classList.contains('show')) {
                menu.classList.remove('show');
                btn.classList.remove('active');
            } else {
                menu.classList.add('show');
                btn.classList.add('active');
            }
            
            event.stopPropagation();
        }

        function cerrarTodosMenus() {
            document.querySelectorAll('.dropdown-menu').forEach(menu => {
                menu.classList.remove('show');
            });
            document.querySelectorAll('.btn-dropdown').forEach(btn => {
                btn.classList.remove('active');
            });
        }

        document.addEventListener('click', function(e) {
            if (!e.target.closest('.dropdown-acciones')) {
                cerrarTodosMenus();
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                cerrarTodosMenus();
            }
        });

        // Modal mejorado
        function abrirModalAdmin(idSolicitud) {
            const modal = document.getElementById('modalAdmin');
            modal.style.display = 'flex';
            document.getElementById('solicitudId').value = idSolicitud;
            
            const inputClave = document.getElementById('claveAdmin');
            inputClave.value = '';
            inputClave.classList.remove('error');
            
            document.getElementById('mensajeError').innerText = '';
            
            setTimeout(() => inputClave.focus(), 100);
        }

        function cerrarModal() {
            document.getElementById('modalAdmin').style.display = 'none';
        }

        function verificarClave() {
            const clave = document.getElementById('claveAdmin').value;
            const idSolicitud = document.getElementById('solicitudId').value;
            const inputClave = document.getElementById('claveAdmin');
            const mensajeError = document.getElementById('mensajeError');

            if (clave === '') {
                mensajeError.innerText = 'Ingrese la clave de admin';
                inputClave.classList.add('error');
                inputClave.focus();
                return;
            }

            const btnAceptar = document.querySelector('#modalAdmin .btn-success');
            const textoOriginal = btnAceptar.innerText;
            btnAceptar.innerText = 'Verificando...';
            btnAceptar.disabled = true;

            fetch('verificaradmin.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `clave=${encodeURIComponent(clave)}&id_solicitud=${encodeURIComponent(idSolicitud)}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    window.location.href = `formeditarsolicitud.php?id=${idSolicitud}`;
                } else {
                    mensajeError.innerText = 'Clave incorrecta';
                    inputClave.classList.add('error');
                    inputClave.value = '';
                    inputClave.focus();
                }
            })
            .catch(error => {
                mensajeError.innerText = 'Error de conexión';
                console.error('Error:', error);
            })
            .finally(() => {
                btnAceptar.innerText = textoOriginal;
                btnAceptar.disabled = false;
            });
        }

        // Efectos hover
        document.querySelectorAll('.btn, .btn-paginacion, .btn-accion-moderno').forEach(btn => {
            btn.addEventListener('mouseenter', function() {
                if (!this.classList.contains('disabled')) {
                    this.style.transform = 'translateY(-2px)';
                }
            });
            
            btn.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
    </script>
</body>
</html>