<?php
// /incidencias/gestionarfases.php - Gestión de tipos y fases de incidencias
session_start();
include_once __DIR__ . "/../encabezado.php";
require_once __DIR__ . "/../user/gestorsesion.php";

// Valida que el usuario tenga sesión activa y sea supervisor
GestorSesiones::iniciar();

// Verificar que el usuario tenga permisos de supervisor
$nivel_usuario = GestorSesiones::get('nivel');
$niveles_permitidos = ['admin', 'supmantenimiento', 'superadministrador'];

if (!in_array($nivel_usuario, $niveles_permitidos)) {
    header("Location: /lugopata/dashboard.php");
    exit;
}

$mensaje = "";

// Procesar creación de nueva fase
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_fase'])) {
    $nombre_fase = trim($_POST['nombre_fase']);
    $descripcion = trim($_POST['descripcion']);
    $orden = (int)$_POST['orden'];
    
    // Manejar el tipo de incidencia (puede ser existente o nuevo)
    if (!empty($_POST['nuevo_tipo_incidencia'])) {
        $tipo_incidencia = trim($_POST['nuevo_tipo_incidencia']);
        // Para nuevo tipo, obtener el valor del checkbox
        $seguimiento_secuencial = isset($_POST['seguimiento_secuencial']) ? 1 : 0;
    } else {
        $tipo_incidencia = $_POST['tipo_incidencia'];
        // Para tipo existente, obtener el valor de seguimiento_secuencial de la primera fase de ese tipo
        $sql_tipo = "SELECT seguimiento_secuencial FROM fases_incidencia WHERE tipo_incidencia = ? LIMIT 1";
        $stmt_tipo = $conexion->prepare($sql_tipo);
        $stmt_tipo->execute([$tipo_incidencia]);
        $tipo_info = $stmt_tipo->fetch(PDO::FETCH_OBJ);
        $seguimiento_secuencial = $tipo_info ? $tipo_info->seguimiento_secuencial : 1;
        $stmt_tipo->closeCursor();
    }
    
    $requiere_evidencia = isset($_POST['requiere_evidencia']) ? 1 : 0;
    
    try {
        $sql = "INSERT INTO fases_incidencia (nombre_fase, descripcion, orden, tipo_incidencia, requiere_evidencia, seguimiento_secuencial) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $sentencia = $conexion->prepare($sql);
        $sentencia->execute([$nombre_fase, $descripcion, $orden, $tipo_incidencia, $requiere_evidencia, $seguimiento_secuencial]);
        
        $mensaje = "<div class='alert alert-success'>Fase creada correctamente.</div>";
    } catch (PDOException $e) {
        $mensaje = "<div class='alert alert-error'>Error al crear la fase: " . $e->getMessage() . "</div>";
    }
}

// Procesar eliminación de fase
if (isset($_GET['eliminar_fase'])) {
    $id_fase = (int)$_GET['eliminar_fase'];
    
    try {
        // Verificar si la fase está en uso
        $sql_check = "SELECT COUNT(*) FROM incidencia_fases WHERE id_fase = ?";
        $sentencia_check = $conexion->prepare($sql_check);
        $sentencia_check->execute([$id_fase]);
        $en_uso = $sentencia_check->fetchColumn();
        
        if ($en_uso > 0) {
            $mensaje = "<div class='alert alert-error'>No se puede eliminar la fase porque está en uso en $en_uso incidencia(s).</div>";
        } else {
            $sql = "DELETE FROM fases_incidencia WHERE id_fase = ?";
            $sentencia = $conexion->prepare($sql);
            $sentencia->execute([$id_fase]);
            $mensaje = "<div class='alert alert-success'>Fase eliminada correctamente.</div>";
        }
    } catch (PDOException $e) {
        $mensaje = "<div class='alert alert-error'>Error al eliminar la fase: " . $e->getMessage() . "</div>";
    }
}

// Obtener todas las fases ordenadas por tipo y orden
$fases = $conexion->query("
    SELECT * FROM fases_incidencia 
    ORDER BY tipo_incidencia, orden
")->fetchAll(PDO::FETCH_OBJ);

// Obtener tipos de incidencia únicos
$tipos_incidencia = $conexion->query(
    "SELECT DISTINCT tipo_incidencia FROM fases_incidencia ORDER BY tipo_incidencia"
)->fetchAll(PDO::FETCH_COLUMN);

// Función para obtener el próximo orden disponible para un tipo de incidencia
function obtenerProximoOrden($conexion, $tipo_incidencia) {
    if ($tipo_incidencia === 'nuevo_tipo') {
        return 1;
    }
    
    $sql = "SELECT MAX(orden) as max_orden FROM fases_incidencia WHERE tipo_incidencia = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$tipo_incidencia]);
    $resultado = $stmt->fetch(PDO::FETCH_OBJ);
    $stmt->closeCursor();
    
    return $resultado && $resultado->max_orden ? $resultado->max_orden + 1 : 1;
}

// Obtener el tipo seleccionado por defecto (si viene por GET o el primero)
$tipo_seleccionado = $_GET['tipo'] ?? 'General';
$orden_sugerido = obtenerProximoOrden($conexion, $tipo_seleccionado);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Gestión de Tipos y Fases</title>
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
            max-width: 1200px;
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

        .title-card p {
            color: #666;
            margin-top: 10px;
            font-size: clamp(13px, 2vw, 15px);
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

        /* ===== ALERTAS ===== */
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 500;
            font-size: 14px;
        }

        .alert-error {
            background-color: #fee2e2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        .alert-success {
            background-color: #d1fae5;
            color: #059669;
            border: 1px solid #a7f3d0;
        }

        /* ===== BOTONES MINIMALISTAS FLAT ===== */
        .btn {
            padding: 12px 24px;
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
            min-width: 90px;
        }

        .btn-success {
            background-color: #198754 !important;
            color: white;
        }

        .btn-success:hover {
            background-color: #059669;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .btn-danger {
            background-color: #dc2626 !important;
            color: white;
        }

        .btn-danger:hover {
            background-color: #b91c1c;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
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

        .btn-container {
            display: flex;
            gap: 12px;
            justify-content: center;
            margin-top: 30px;
            flex-wrap: wrap;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 12px;
            border: 1px solid #e9ecef;
        }

        /* ===== SECCIONES ===== */
        .section-header {
            color: #333;
            margin-top: 25px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e9ecef;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 18px;
            font-weight: 600;
        }

        .section-header img {
            width: 32px;
            height: 32px;
        }

        /* ===== FORMULARIO CREAR FASE ===== */
        .form-crear-fase {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 30px;
            border: 1px solid #e9ecef;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        .form-group input, 
        .form-group select, 
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            background-color: #fff;
            transition: all 0.3s ease;
        }

        .form-group input:focus, 
        .form-group select:focus, 
        .form-group textarea:focus {
            border-color: #0d6efd;
            outline: none;
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
        }

        .form-group textarea {
            height: 80px;
            resize: vertical;
            min-height: 60px;
        }

        /* ===== SELECT PERSONALIZADO ===== */
        select {
            appearance: none;
            background-image: url('data:image/svg+xml;utf8,<svg fill="%23666" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><path d="M7 10l5 5 5-5z"/><path d="M0 0h24v24H0z" fill="none"/></svg>');
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 20px;
            padding-right: 40px;
        }

        /* ===== FORM GRID ===== */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        /* ===== CHECKBOX GROUP ===== */
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .checkbox-group input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }

        .checkbox-group label {
            margin: 0;
            font-weight: 500;
            cursor: pointer;
        }

        .info-checkbox {
            font-size: 12px;
            color: #6b7280;
            margin-left: 30px;
            display: block;
            margin-top: 5px;
        }

        /* ===== CAMPOS CONDICIONALES ===== */
        .campo-nuevo-tipo,
        .campo-secuencial-nuevo-tipo,
        .info-tipo-existente {
            margin-top: 10px;
            padding: 15px;
            background: #e7f1ff;
            border-radius: 8px;
            border: 1px solid #b3d9ff;
            display: none;
        }

        .campo-secuencial-nuevo-tipo {
            background: #e7f8ff;
            border-color: #17a2b8;
        }

        .info-tipo-existente {
            background: #fff3cd;
            border-color: #ffc107;
            color: #856404;
        }

        .orden-info {
            font-size: 12px;
            color: #6b7280;
            margin-top: 6px;
            font-style: italic;
        }

        /* ===== SECCIONES POR TIPO ===== */
        .seccion-tipo {
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 12px;
            border-left: 4px solid #0d6efd;
        }

        .seccion-tipo h3 {
            color: #0d6efd;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            font-size: 16px;
        }

        .seccion-tipo h3 img {
            width: 28px;
            height: 28px;
        }

        /* ===== BADGES ===== */
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-tipo {
            background-color: #0d6efd;
            color: white;
            font-size: 14px;
        }

        .badge-evidencia {
            background-color: #059669;
            color: white;
        }

        .badge-no-evidencia {
            background-color: #6c757d;
            color: white;
        }

        .badge-secuencial {
            background-color: #0dcaf0;
            color: #000;
        }

        .badge-paralelo {
            background-color: #6c757d;
            color: white;
        }

        /* ===== TABLA  ===== */
        .table-container {
            width: 100% !important;
            overflow-x: auto !important;
            margin: 15px 0 !important;
            background: white !important;
            border-radius: 10px !important;
            border: 1px solid #ddd !important;
        }

        table {
            width: 100% !important;
            border-collapse: collapse !important;
            min-width: 700px !important;
            font-size: 14px !important;
        }

        th {
            background-color: #cfe2ff !important;
            font-weight: bold !important;
            color: #333 !important;
            padding: 15px 12px !important;
            text-align: center !important;
            border: 1px solid #dee2e6 !important;
            position: sticky !important;
            top: 0 !important;
            white-space: nowrap !important;
        }

        th:first-child {
            border-radius: 12px 0 0 0;
        }

        th:last-child {
            border-radius: 0 12px 0 0;
        }

        td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #dee2e6;
            background-color: #fff;
            vertical-align: middle;
            border: 1px solid #dee2e6;
        }

        tbody tr:nth-child(even) {
            background-color: #f8f9fa !important;
        }

        tbody tr:hover {
            background-color: #e9ecef !important;
        }

        tbody tr:last-child td:first-child {
            border-radius: 0 0 0 12px;
        }

        tbody tr:last-child td:last-child {
            border-radius: 0 0 12px 0;
        }

        /* Descripción más ancha */
        td:nth-child(3) {
            max-width: 300px;
            text-align: left;
        }

        /* ===== MENSAJE SIN DATOS ===== */
        .mensaje-sin-datos {
            text-align: center;
            padding: 40px 20px;
            color: #6c757d;
            font-size: 16px;
            font-style: italic;
            background-color: #f8f9fa;
            border-radius: 12px;
            border: 2px dashed #dee2e6;
            margin: 20px 0;
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

            .form-grid {
                grid-template-columns: 1fr;
            }

            .seccion-tipo {
                padding: 15px;
            }

            .seccion-tipo h3 {
                font-size: 15px;
            }

            th {
                padding: 12px 8px;
                font-size: 12px;
            }

            td {
                padding: 10px 8px;
                font-size: 13px;
            }

            .btn-container {
                flex-direction: column;
                gap: 10px;
                padding: 15px;
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

            .form-crear-fase {
                padding: 15px;
            }

            .form-group input, 
            .form-group select, 
            .form-group textarea {
                padding: 10px 12px;
                font-size: 16px;
            }

            .btn {
                padding: 14px 20px;
                font-size: 16px;
            }

            .section-header {
                font-size: 16px;
            }

            .section-header img {
                width: 28px;
                height: 28px;
            }
        }

        /* ===== SCROLLBAR PERSONALIZADA ===== */
        .table-container::-webkit-scrollbar {
            height: 8px;
        }

        .table-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .table-container::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }

        .table-container::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
    </style>
</head>
<body>
<div class="form-wrapper">
    <!-- TARJETA DE TÍTULO -->
    <div class="title-card">
        <h1>
            <img src="../assets/resources/gincidencia.png" alt="Gestión">
            GESTIÓN DE TIPOS Y FASES
        </h1>
        <p>Configuración de tipos de incidencias y sus fases de seguimiento</p>
    </div>

    <!-- CONTENIDO -->
    <div class="content-card">
        <?php echo $mensaje; ?>

        <!-- Formulario para crear nueva fase -->
        <div class="form-crear-fase">
            <div class="section-header">
                <img src="../assets/resources/repetir.png" alt="Crear">
                <h2 style="margin: 0; font-size: 18px;">Crear Nueva Fase</h2>
            </div>
            
            <form method="post" id="formFase">
                <input type="hidden" name="crear_fase" value="1">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="nombre_fase">Nombre de la Fase:</label>
                        <input type="text" name="nombre_fase" id="nombre_fase" required 
                            placeholder="Ej: Diagnóstico Inicial, Reparación, Pruebas...">
                    </div>
                    
                    <div class="form-group">
                        <label for="tipo_incidencia">Tipo de Incidencia:</label>
                        <select name="tipo_incidencia" id="tipo_incidencia" required onchange="actualizarOrdenYSecuencial(this)">
                            <option value="General" <?= $tipo_seleccionado === 'General' ? 'selected' : '' ?>>General (para todos los tipos)</option>
                            <?php foreach($tipos_incidencia as $tipo): ?>
                                <?php if ($tipo !== 'General'): ?>
                                    <option value="<?= $tipo ?>" <?= $tipo_seleccionado === $tipo ? 'selected' : '' ?>>
                                        <?= $tipo ?>
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                            <option value="nuevo_tipo">+ Crear nuevo tipo...</option>
                        </select>
                        
                        <div id="campo_nuevo_tipo" class="campo-nuevo-tipo">
                            <label for="nuevo_tipo_incidencia" style="color: #0056b3; font-weight: 600;">Nuevo Tipo:</label>
                            <input type="text" name="nuevo_tipo_incidencia" id="nuevo_tipo_incidencia" 
                                placeholder="Ej: Eléctrica, Plomería, etc." style="margin-top: 8px;">
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="descripcion">Descripción:</label>
                    <textarea name="descripcion" id="descripcion" 
                            placeholder="Describa qué incluye esta fase..." required></textarea>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="orden">Orden de Ejecución:</label>
                        <input type="number" name="orden" id="orden" min="1" max="20" required 
                            value="<?= $orden_sugerido ?>">
                        <div class="orden-info" id="info_orden">
                            Siguiente orden disponible
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <div class="checkbox-group">
                            <input type="checkbox" name="requiere_evidencia" id="requiere_evidencia" value="1" checked>
                            <label for="requiere_evidencia">
                                <img src="../assets/resources/imagen.png" alt="Evidencia" style="width: 20px; vertical-align: middle; margin-right: 5px;">
                                Requiere evidencia fotográfica
                            </label>
                        </div>
                    </div>
                    
                    <!-- Campo de seguimiento secuencial SOLO para nuevos tipos -->
                    <div id="campo_seguimiento_nuevo_tipo" class="campo-secuencial-nuevo-tipo">
                        <div class="checkbox-group">
                            <input type="checkbox" name="seguimiento_secuencial" id="seguimiento_secuencial" value="1" checked>
                            <label for="seguimiento_secuencial" style="color: #17a2b8; font-weight: 600;">
                                Seguimiento secuencial para este nuevo tipo
                            </label>
                        </div>
                        <div class="info-checkbox">
                            (Esta configuración aplicará a TODAS las fases de este nuevo tipo)
                        </div>
                    </div>
                </div>
                
                <!-- Información para tipos existentes -->
                <div id="info_tipo_existente" class="info-tipo-existente">
                    <strong>ℹ Nota:</strong> Para el tipo "<span id="nombre_tipo_existente"></span>", 
                    el seguimiento está configurado como <span id="estado_seguimiento_existente"></span>.
                </div>
                
                <div style="text-align: center; margin-top: 25px;">
                    <button type="submit" class="btn btn-success">
                        <img src="../assets/resources/controlar.png" alt="Crear">
                        CREAR FASE
                    </button>
                </div>
            </form>
        </div>

        <!-- Lista de fases existentes agrupadas por tipo -->
        <div class="section-header">
            <img src="../assets/resources/etapas.png" alt="Fases">
            <h2 style="margin: 0; font-size: 18px;">Fases Existentes</h2>
        </div>
        
        <?php if (empty($fases)): ?>
            <div class="mensaje-sin-datos">
                <p>No hay fases configuradas. Crea la primera fase usando el formulario superior.</p>
            </div>
        <?php else: ?>
            <?php
            // Agrupar fases por tipo
            $fases_por_tipo = [];
            foreach ($fases as $fase) {
                $fases_por_tipo[$fase->tipo_incidencia][] = $fase;
            }
            
            // Mostrar por tipo
            foreach ($fases_por_tipo as $tipo => $fases_tipo): 
            ?>
            <div class="seccion-tipo">
                <h3>
                    <img src="../assets/resources/mantenimient0.png" alt="Tipo">
                    <?= $tipo ?> 
                    <span class="badge badge-tipo"><?= count($fases_tipo) ?> fases</span>
                    <?php 
                    $primer_fase = $fases_tipo[0];
                    if ($primer_fase->seguimiento_secuencial): 
                    ?>
                        <span class="badge badge-secuencial">Secuencial</span>
                    <?php else: ?>
                        <span class="badge badge-paralelo">Paralelo</span>
                    <?php endif; ?>
                </h3>
                
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Orden</th>
                                <th>Nombre</th>
                                <th>Descripción</th>
                                <th>Evidencia</th>
                                <th>Seguimiento</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($fases_tipo as $fase): ?>
                            <tr>
                                <td><strong><?= $fase->orden ?></strong></td>
                                <td><strong><?= htmlspecialchars($fase->nombre_fase) ?></strong></td>
                                <td><?= htmlspecialchars($fase->descripcion) ?></td>
                                <td>
                                    <?php if ($fase->requiere_evidencia): ?>
                                        <span class="badge badge-evidencia">
                                            <img src="../assets/resources/imagen.png" alt="Evidencia" style="width: 16px; vertical-align: middle; margin-right: 5px;">
                                            Requerida
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-no-evidencia">Opcional</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($fase->seguimiento_secuencial): ?>
                                        <span class="badge badge-secuencial">
                                            <img src="../assets/resources/flecha-de-prioridad.png" alt="Secuencial" style="width: 16px; vertical-align: middle; margin-right: 5px;">
                                            Secuencial
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-paralelo">
                                            <img src="../assets/resources/herramientas.png" alt="Paralelo" style="width: 16px; vertical-align: middle; margin-right: 5px;">
                                            Paralelo
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="?eliminar_fase=<?= $fase->id_fase ?>" 
                                    class="btn btn-danger"
                                    onclick="return confirm('¿Está seguro de eliminar la fase \"<?= $fase->nombre_fase ?>\"?')">
                                        <img src="../assets/resources/eliminar2.png" alt="Eliminar">
                                        Eliminar
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- Botones de navegación -->
        <div class="btn-container">
            <a href="/lugopata/incidencias/aprobarincidencia.php" class="btn btn-primary">
                <img src="../assets/resources/volver2.png" alt="Volver">
                Regresar
            </a>
        </div>
    </div>
</div>

<script>
// Datos de tipos existentes para consulta AJAX
const tiposConfiguracion = {};

// Inicializar con los tipos cargados
<?php if(isset($fases_por_tipo)): ?>
    <?php foreach($fases_por_tipo as $tipo => $fases_tipo): ?>
        tiposConfiguracion['<?= $tipo ?>'] = {
            seguimiento_secuencial: <?= $fases_tipo[0]->seguimiento_secuencial ? 'true' : 'false' ?>,
            cantidad_fases: <?= count($fases_tipo) ?>
        };
    <?php endforeach; ?>
<?php endif; ?>

function actualizarOrdenYSecuencial(select) {
    const valorSeleccionado = select.value;
    const campoNuevoTipo = document.getElementById('campo_nuevo_tipo');
    const campoTipoInput = document.getElementById('nuevo_tipo_incidencia');
    const campoSecuencialNuevoTipo = document.getElementById('campo_seguimiento_nuevo_tipo');
    const infoTipoExistente = document.getElementById('info_tipo_existente');
    const nombreTipoExistente = document.getElementById('nombre_tipo_existente');
    const estadoSeguimientoExistente = document.getElementById('estado_seguimiento_existente');
    const campoOrden = document.getElementById('orden');
    const infoOrden = document.getElementById('info_orden');

    if (valorSeleccionado === 'nuevo_tipo') {
        campoNuevoTipo.style.display = 'block';
        campoTipoInput.required = true;
        campoSecuencialNuevoTipo.style.display = 'block';
        infoTipoExistente.style.display = 'none';
        
        campoOrden.value = 1;
        infoOrden.textContent = 'Nuevo tipo: se iniciará en 1';
        
    } else {
        campoNuevoTipo.style.display = 'none';
        campoTipoInput.required = false;
        campoTipoInput.value = "";
        campoSecuencialNuevoTipo.style.display = 'none';
        
        if (tiposConfiguracion[valorSeleccionado]) {
            const config = tiposConfiguracion[valorSeleccionado];
            const nuevoOrden = config.cantidad_fases + 1;
            campoOrden.value = nuevoOrden;
            infoOrden.textContent = `${valorSeleccionado}: siguiente orden disponible`;
            
            nombreTipoExistente.textContent = valorSeleccionado;
            estadoSeguimientoExistente.textContent = config.seguimiento_secuencial ? 'Secuencial' : 'Paralelo';
            estadoSeguimientoExistente.style.color = config.seguimiento_secuencial ? '#0dcaf0' : '#6c757d';
            infoTipoExistente.style.display = 'block';
        } else {
            campoOrden.value = 1;
            infoOrden.textContent = 'Siguiente orden disponible';
            infoTipoExistente.style.display = 'none';
        }
    }
}

// Inicializar al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    const selectTipo = document.getElementById('tipo_incidencia');
    if (selectTipo) {
        actualizarOrdenYSecuencial(selectTipo);
    }
    
    // Validar formulario antes de enviar
    document.getElementById('formFase').addEventListener('submit', function(e) {
        const selectTipo = document.getElementById('tipo_incidencia');
        const campoTipoInput = document.getElementById('nuevo_tipo_incidencia');

        if (selectTipo.value === 'nuevo_tipo' && campoTipoInput.value.trim() === '') {
            e.preventDefault();
            alert('Por favor, ingrese un nombre para el nuevo tipo de incidencia.');
            campoTipoInput.focus();
            return false;
        }

        if (selectTipo.value === 'nuevo_tipo' && campoTipoInput.value.trim() !== '') {
            const nombreTipo = campoTipoInput.value.trim();
            const regex = /^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s\-]+$/;
            if (!regex.test(nombreTipo)) {
                e.preventDefault();
                alert('El nombre del tipo solo puede contener letras, números, espacios y guiones.');
                campoTipoInput.focus();
                return false;
            }
            
            if (nombreTipo.length > 50) {
                e.preventDefault();
                alert('El nombre del tipo no puede exceder los 50 caracteres.');
                campoTipoInput.focus();
                return false;
            }
        }

        return true;
    });

    // Limpiar el campo nuevo tipo cuando se cambia a una opción diferente
    document.getElementById('tipo_incidencia').addEventListener('change', function() {
        if (this.value !== 'nuevo_tipo') {
            document.getElementById('nuevo_tipo_incidencia').value = '';
        }
    });

    // Actualizar orden cuando se cambia manualmente
    document.getElementById('orden').addEventListener('change', function() {
        const valor = parseInt(this.value);
        if (isNaN(valor) || valor < 1) {
            this.value = 1;
        }
        if (valor > 50) {
            this.value = 50;
            alert('El orden máximo permitido es 50.');
        }
    });
});
</script>

</body>
</html>