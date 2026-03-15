<?php
// /incidencias/monitorearincidencia.php
session_start();
include_once __DIR__ . "/../encabezado.php";
require_once "incidencia.php";
require_once __DIR__ . "/../user/gestorsesion.php";
require_once __DIR__ . "/../trabajador/trabajador.php";

GestorSesiones::iniciar();

if (!isset($_GET['id'])) exit("No hay id de incidencia");
$id = (int)$_GET['id'];

$incObj = new Incidencia($conexion);
$inc = $incObj->obtener($id);
if (!$inc) exit("Incidencia no encontrada.");

// Obtener imágenes
$imagenes = $incObj->obtenerImagenes($id);

// Obtener información del rechazo si la incidencia está rechazada
$infoRechazo = null;
if ($inc->estado === 'Rechazada') {
    $infoRechazo = $incObj->obtenerJustificacionRechazo($id);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Información de Incidencia #<?= $id ?></title>
    <style>
        /* ===== RESET Y VARIABLES ===== */
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

        /* ===== CONTENEDOR PRINCIPAL ===== */
        .form-wrapper {
            max-width: 1000px;
            margin: 0 auto;
            width: 100%;
        }

        /* ===== TARJETA DE TÍTULO ===== */
        .title-card {
            background: white;
            padding: 20px;
            border-radius: 15px;
            border: 2px solid #ccc;
            margin-bottom: 20px;
            width: 100%;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            text-align: center;
        }

        .title-card h1 {
            color: #333;
            font-weight: bold;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
            margin: 0;
            font-size: clamp(1.1rem, 4vw, 1.6rem);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .title-card h1 img {
            width: 40px;
            height: 40px;
        }

        /* ===== TARJETA DE CONTENIDO ===== */
        .content-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            border: 2px solid #ccc;
            width: 100%;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        /* ===== INFO GRID ===== */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }

        .info-item {
            padding: 15px;
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .info-item:hover {
            background: #e9ecef;
            transform: translateY(-2px);
        }

        .info-item strong {
            color: #495057;
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
        }

        /* ===== BADGES ===== */
        .estado-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            display: inline-block;
        }

        .estado-en-espera { background: #6c757d; color: white; }
        .estado-pendiente { background: #f59e0b; color: white; }
        .estado-finalizada { background: #059669; color: white; }
        .estado-rechazada { background: #dc2626; color: white; }

        .prioridad-badge {
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            display: inline-block;
        }

        .prioridad-urgente { background: #dc2626; color: white; }
        .prioridad-moderada { background: #f59e0b; color: white; }
        .prioridad-leve { background: #059669; color: white; }

        /* ===== CONTENEDORES ESPECIALES ===== */
        .descripcion-container,
        .ubicacion-container {
            padding: 20px;
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            min-height: 100px;
            line-height: 1.6;
        }

        /* ===== RECHAZO CONTAINER ===== */
        .rechazo-container {
            background: #fef3c7;
            border: 2px solid #f59e0b;
            border-radius: 12px;
            padding: 25px;
            margin: 25px 0;
        }

        .rechazo-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #f59e0b;
            flex-wrap: wrap;
            gap: 10px;
        }

        .rechazo-title {
            color: #92400e;
            font-weight: bold;
            font-size: 18px;
        }

        .rechazo-justificacion {
            background: white;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #e9ecef;
            margin-top: 10px;
            line-height: 1.6;
        }

        /* ===== IMÁGENES ===== */
        .imagenes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }

        .img-thumb {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 5px;
            background: white;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .img-thumb:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
            border-color: #0d6efd;
        }

        /* ===== BOTONES MINIMALISTAS FLAT ===== */
        .btn-container {
            display: flex;
            gap: 12px;
            justify-content: center;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s ease;
            min-width: 180px;
        }

        .btn-primary {
            background-color: #6c757d !important;
            color: white;
        }

        .btn-primary:hover {
            background-color: #2563eb;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .btn:active {
            transform: translateY(0);
        }

        .btn img {
            width: 18px;
            height: 18px;
            filter: brightness(0) invert(1);
        }

        /* ===== TÍTULOS DE SECCIÓN ===== */
        h4 {
            color: #333;
            margin: 25px 0 15px 0;
            padding-bottom: 10px;
            border-bottom: 2px solid #e9ecef;
            font-size: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* ===== MEDIA QUERIES ===== */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }

            .content-card {
                padding: 20px;
            }

            .title-card h1 {
                flex-direction: column;
                gap: 5px;
            }

            .title-card h1 img {
                width: 35px;
                height: 35px;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .rechazo-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .imagenes-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            }

            .btn-container {
                flex-direction: column;
                gap: 10px;
            }

            .btn {
                width: 100%;
                min-width: auto;
            }
        }

        @media (max-width: 480px) {
            .title-card {
                padding: 15px;
            }

            .content-card {
                padding: 15px;
            }

            .info-item {
                padding: 12px;
            }

            .descripcion-container,
            .ubicacion-container {
                padding: 15px;
            }

            .rechazo-container {
                padding: 15px;
            }

            .img-thumb {
                height: 120px;
            }

            .btn {
                padding: 14px 20px;
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
<div class="form-wrapper">
    <!-- TARJETA DE TÍTULO -->
    <div class="title-card">
        <h1>
            <img src="../assets/resources/incidencia.png" alt="Incidencia">
            Información de Incidencia #<?= $id ?>
        </h1>
    </div>

    <!-- CONTENIDO -->
    <div class="content-card">
        <div class="info-grid">
            <div class="info-item">
                <strong>Fecha de creación:</strong> <?= $inc->fecha ?>
            </div>
            <div class="info-item">
                <strong>Creado por:</strong> <?= htmlspecialchars($inc->creado_por) ?>
            </div>
            <div class="info-item">
                <strong>Departamento Emisor:</strong> <?= $inc->depto_emisor ?>
            </div>
            <div class="info-item">
                <strong>Departamento Receptor:</strong> <?= $inc->depto_receptor ?>
            </div>
            <div class="info-item">
                <strong>Prioridad:</strong> 
                <span class="prioridad-badge prioridad-<?= strtolower($inc->prioridad) ?>">
                    <?= $inc->prioridad ?>
                </span>
            </div>
            <div class="info-item">
                <strong>Estado:</strong> 
                <span class="estado-badge estado-<?= strtolower(str_replace(' ', '-', $inc->estado)) ?>">
                    <?= $inc->estado ?>
                </span>
            </div>
            <div class="info-item">
                <strong>Trabajador Asignado:</strong> 
                <?= $inc->trabajador_nombre ? "{$inc->trabajador_nombre} {$inc->trabajador_apellido}" : "No asignado" ?>
            </div>
            <div class="info-item">
                <strong>Fecha estimada de finalización:</strong> 
                <?= $inc->fecha_estimada_finalizacion ?? 'No definida' ?>
            </div>
            <?php if ($inc->fecha_finalizacion): ?>
            <div class="info-item">
                <strong>Fecha real de finalización:</strong> <?= $inc->fecha_finalizacion ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Mostrar ubicación -->
        <?php if (!empty($inc->ubicacion)): ?>
        <div style="margin-bottom: 25px;">
            <h4>Ubicación</h4>
            <div class="ubicacion-container">
                <?= htmlspecialchars($inc->ubicacion) ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Mostrar información del rechazo si la incidencia está rechazada -->
        <?php if ($inc->estado === 'Rechazada' && $infoRechazo): ?>
        <div class="rechazo-container">
            <div class="rechazo-header">
                <div class="rechazo-title">Incidencia Rechazada</div>
                <div style="color: #666; font-size: 14px;">
                    Rechazada el: <?= date('d/m/Y H:i', strtotime($infoRechazo->fecha_rechazo)) ?>
                </div>
            </div>
            
            <div>
                <strong>Supervisor que rechazó:</strong> 
                <?= htmlspecialchars($infoRechazo->supervisor_nombre ?? 'N/A') ?>
            </div>
            
            <div style="margin-top: 10px;">
                <strong>Razón del rechazo:</strong>
                <div class="rechazo-justificacion">
                    <?= nl2br(htmlspecialchars($infoRechazo->justificacion)) ?>
                </div>
            </div>
        </div>
        <?php elseif ($inc->estado === 'Rechazada'): ?>
        <div class="rechazo-container">
            <div class="rechazo-header">
                <div class="rechazo-title">Incidencia Rechazada</div>
            </div>
            <div style="color: #856404; text-align: center; padding: 20px;">
                <strong>Información del rechazo no disponible.</strong>
            </div>
        </div>
        <?php endif; ?>

        <div style="margin-bottom: 25px;">
            <h4>Descripción de la Incidencia</h4>
            <div class="descripcion-container">
                <?= nl2br(htmlspecialchars($inc->descripcion)) ?>
            </div>
        </div>

        <?php if(!empty($imagenes)): ?>
        <h4>Imágenes de Evidencia</h4>
        <div class="imagenes-grid">
            <?php foreach($imagenes as $img): ?>
                <a href="<?= $img->ruta ?>" target="_blank">
                    <img src="<?= $img->ruta ?>" class="img-thumb" alt="Imagen de incidencia" title="Clic para ver en tamaño completo">
                </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="btn-container">
            <a href="../incidencias/listarincidencias.php" class="btn btn-primary">
                <img src="../assets/resources/volver2.png" alt="Volver">
                Volver al Listado
            </a>
        </div>
    </div>
</div>
</body>
</html>