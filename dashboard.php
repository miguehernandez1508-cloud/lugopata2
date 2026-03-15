<?php
session_start();
include_once "user/gestorsesion.php";
GestorSesiones::iniciar();
include_once "encabezado.php";
require_once "conex.php";

$nivelesVisibles = [
    'admin' => 'Gerente',
    'supmantenimiento' => 'Supervisor Mantenimiento',
    'obmantenimiento' => 'Obrero Mantenimiento',
    'almacenista' => 'Almacenista',
    'solicitante' => 'Solicitante'
];

$nivelVisible = isset($_SESSION['nivel']) && isset($nivelesVisibles[$_SESSION['nivel']])
    ? $nivelesVisibles[$_SESSION['nivel']]
    : $_SESSION['nivel'];
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
<title>Dashboard</title>
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
    }

    /* CONTENEDOR PRINCIPAL - VERSIÓN CORREGIDA */
    .dashboard-wrapper {
        max-width: 1200px;
        margin: 0 auto;
        padding: 15px;
        width: 100%;
    }

    /* TARJETA DE TÍTULO */
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
        font-size: clamp(1.5rem, 4vw, 2.5rem);
    }

    .title-card h1 img {
        width: clamp(35px, 5vw, 45px);
        height: auto;
    }

    .title-card p {
        color: #666;
        margin-top: 10px;
        font-size: clamp(14px, 2vw, 16px);
    }

    /* TARJETA DE BIENVENIDA */
    .welcome-card {
        background: white;
        border-radius: 15px;
        padding: 30px;
        border: 2px solid #ccc;
        width: 100%;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    .welcome-card h1 {
        color: #333;
        margin-bottom: 20px;
        font-weight: bold;
        font-size: clamp(1.3rem, 3vw, 2rem);
        word-break: break-word;
    }

    .welcome-card h1 b {
        color: #0d6efd;
    }

    .badge-nivel {
        background-color: #0d6efd;
        color: white;
        padding: 8px 20px;
        border-radius: 30px;
        font-weight: bold;
        display: inline-block;
        margin-bottom: 30px;
        font-size: clamp(14px, 2vw, 16px);
        white-space: nowrap;
    }

    /* GRID DE BOTONES - VERSIÓN MEJORADA */
    .grid-botones {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 20px;
        margin-top: 20px;
        width: 100%;
    }

    .grid-botones a {
        margin: 0 !important;
        padding: 20px 25px !important;
        font-size: 16px !important;
        font-weight: 500 !important;
        transition: all 0.3s ease !important;
        border-radius: 12px !important;
        background: white !important;
        display: flex !important;
        align-items: center;
        justify-content: flex-start;
        gap: 15px;
        text-decoration: none;
        border: 1px solid rgba(0,0,0,0.1) !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02) !important;
        position: relative;
        overflow: hidden;
        width: 100%;
        min-height: 80px;
    }

    /* Línea de color en el borde izquierdo */
    .grid-botones a.btn-primary {
        color: #0d6efd !important;
        border-left: 5px solid #F57327 !important;
    }

    .grid-botones a.btn-success {
        color: #0d6efd !important;
        border-left: 5px solid #F57327 !important;
    }

    .grid-botones a.btn-warning {
        color: #0d6efd !important;
        border-left: 5px solid #F57327 !important;
    }

    /* Efecto hover */
    .grid-botones a:hover {
        transform: translateX(5px) translateY(-3px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
        background: #ffffff !important;
    }

    /* Iconos */
    .grid-botones a img {
        width: 32px !important;
        height: 32px !important;
        object-fit: contain;
        transition: transform 0.3s ease;
        flex-shrink: 0;
    }

    .grid-botones a:hover img {
        transform: scale(1.1);
    }

    /* Texto del botón */
    .grid-botones a span {
        flex: 1;
        text-align: left;
        word-break: break-word;
        line-height: 1.4;
    }

    /* MEDIA QUERIES ESPECÍFICAS */
    @media (max-width: 1024px) {
        .dashboard-wrapper {
            padding: 15px;
        }
        
        .grid-botones {
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }
    }

    @media (max-width: 768px) {
        .dashboard-wrapper {
            padding: 12px;
        }
        
        .title-card {
            padding: 15px;
        }
        
        .welcome-card {
            padding: 20px;
        }
        
        .grid-botones {
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }
        
        .grid-botones a {
            padding: 15px 18px !important;
            font-size: 14px !important;
            min-height: 70px;
        }
        
        .grid-botones a img {
            width: 28px !important;
            height: 28px !important;
        }
        
        .badge-nivel {
            padding: 6px 15px;
            font-size: 13px;
        }
    }

    @media (max-width: 600px) {
        .grid-botones {
            grid-template-columns: 1fr;
            gap: 10px;
        }
        
        .grid-botones a {
            padding: 18px 20px !important;
            font-size: 15px !important;
            min-height: 75px;
        }
        
        .title-card h1 {
            flex-direction: column;
            text-align: center;
            gap: 10px;
        }
        
        .badge-nivel {
            white-space: normal;
            word-break: break-word;
            font-size: 14px;
            padding: 8px 15px;
        }
    }

    @media (max-width: 480px) {
        .dashboard-wrapper {
            padding: 10px;
        }
        
        .title-card {
            padding: 12px;
        }
        
        .welcome-card {
            padding: 15px;
        }
        
        .grid-botones a {
            padding: 15px !important;
            font-size: 14px !important;
            gap: 12px;
        }
        
        .grid-botones a img {
            width: 24px !important;
            height: 24px !important;
        }
        
        .badge-nivel {
            font-size: 13px;
            padding: 6px 12px;
        }
    }

    /* Para pantallas muy grandes */
    @media (min-width: 1400px) {
        .dashboard-wrapper {
            max-width: 1400px;
        }
        
        .grid-botones {
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        }
        
        .grid-botones a {
            padding: 25px 30px !important;
            font-size: 18px !important;
        }
        
        .grid-botones a img {
            width: 36px !important;
            height: 36px !important;
        }
    }

    /* UTILIDADES */
    .text-center {
        text-align: center;
    }
    
    .mt-3 {
        margin-top: 15px;
    }
    
    .mb-3 {
        margin-bottom: 15px;
    }
    
    /* CORRECCIÓN PARA EL FORM-CONTAINER ORIGINAL */
    .form-container {
        width: 100% !important;
        max-width: 700px !important;
        margin: 10px auto !important;
        background: white !important;
        padding: 12px !important;
        border-radius: 10px !important;
        border: 2px solid #ccc !important;
    }
</style>
</head>
<body>
<div class="dashboard-wrapper">
    <div class="title-card">
        <center>
            <h1>
                <img src="assets/resources/campamento-de-bomberos.gif" alt="Inicio">
            PANEL  PRINCIPAL
            <img src="assets/resources/campamento-de-bomberos.gif" alt="Inicio">
            </h1>
            <p class="text-muted">-Área de Mantenimiento-</p>
        </center>
    </div>

    <div class="welcome-card">
        <div style="text-align: center;">
            <h1>
                <img src="assets/resources/inicio1.png" alt="Bienvenido" style="width: 40px; height: 40px; margin-right: 10px;">
                ¡BIENVENID@ <b><?php echo isset($_SESSION['name']) ? $_SESSION['name'] : htmlspecialchars($_SESSION['username']); ?>!</b>
            </h1>
            <div class="badge-nivel">
                <?php echo $nivelVisible; ?>
            </div>
             <p class="text-muted">Acciones Rápidas</p>
            <!-- Grid de botones -->
            <div class="grid-botones">
                <a href="estadisticas/missolicitudes.php" class="btn btn-primary">
                    <img src="assets/resources/demanda.png" alt="Mis Solicitudes">
                    <span>Mis Solicitudes</span>
                </a>
                
                <a href="estadisticas/estadisticas.php" class="btn btn-success">
                    <img src="assets/resources/estadisticasR.png" alt="Estadísticas">
                    <span>Estadísticas</span>
                </a>

                <?php if (in_array($_SESSION['nivel'], ['admin', 'sistemas', 'superadministrador'])): ?>
                    <a href="user/listarusuario.php" class="btn btn-warning">
                        <img src="assets/resources/Rcusuario.png" alt="Usuarios">
                        <span>Gestión de Usuarios</span>
                    </a>
                <?php endif; ?>
                
                <?php if (in_array($_SESSION['nivel'], ['admin', 'sistemas', 'almacenista', 'superadministrador'])): ?>
                    <a href="insumos/listarinsumos.php" class="btn btn-primary">
                        <img src="assets/resources/inventario2.png" alt="Insumos">
                        <span>Gestión de Insumos</span>
                    </a>
                <?php endif; ?>
                
                <?php if (in_array($_SESSION['nivel'], ['admin', 'sistemas', 'superadministrador'])): ?>
                    <a href="user/listauditoria.php" class="btn btn-success">
                        <img src="assets/resources/ojo-de-lupa.png" alt="Auditoría">
                        <span>Registro de Auditoría</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Script para asegurar que el contenido se ajuste correctamente
document.addEventListener('DOMContentLoaded', function() {
    // Función para ajustar elementos si es necesario
    function adjustLayout() {
        // Aquí puedes agregar lógica adicional si es necesaria
        console.log('Layout ajustado para:', window.innerWidth + 'px');
    }
    
    // Ejecutar al cargar y al redimensionar
    adjustLayout();
    window.addEventListener('resize', adjustLayout);
});
</script>

</body>
</html>