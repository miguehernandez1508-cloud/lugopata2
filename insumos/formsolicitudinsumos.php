<?php
session_start();
ob_start();
include_once "../encabezado.php";
require_once "../conex.php";
require_once "../user/gestorsesion.php";
require_once "solicitud.php";
require_once "insumo.php";

GestorSesiones::iniciar();

$mensaje = "";
$tipo_mensaje = ""; 

$departamentos = $conexion->query("SELECT id_departamento, nombre FROM departamentos")->fetchAll(PDO::FETCH_ASSOC);

$incidencias = $conexion->query("
    SELECT i.id_incidencia, i.descripcion, d.nombre as departamento, i.fecha, i.prioridad
    FROM incidencias i 
    LEFT JOIN departamentos d ON i.departamento_receptor = d.id_departamento 
    WHERE i.estado IN ('En espera', 'Pendiente')
    ORDER BY i.fecha DESC
")->fetchAll(PDO::FETCH_ASSOC);

$emisorNombre = $_SESSION['nombre_completo'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $solicitud = new Solicitud($conexion);
    $solicitud->fecha = $_POST['fecha'];
    $solicitud->emisor = $emisorNombre;
    $solicitud->receptor = $_POST['receptor'];
    $solicitud->departamento_emisor = $_POST['emisor_dep'];
    $solicitud->departamento_destino = $_POST['receptor_dep'];
    $solicitud->descripcion = $_POST['descripcion'];
    
    $solicitud->id_incidencia = ($_POST['tipo_solicitud'] == 'incidencia' && !empty($_POST['id_incidencia'])) ? $_POST['id_incidencia'] : NULL;
    $solicitud->razon_manual = ($_POST['tipo_solicitud'] == 'manual' && !empty($_POST['razon_manual'])) ? $_POST['razon_manual'] : NULL;

    $materiales = $_POST['materiales'] ?? [];

    if (empty($materiales)) {
        $mensaje = "Debes agregar al menos un insumo a la solicitud.";
        $tipo_mensaje = "error";
    } else {
        $id_solicitud = $solicitud->crearSolicitud();
        if ($id_solicitud) {
            $solicitud->agregarDetalle($id_solicitud, $materiales);
            $mensaje = "Solicitud registrada exitosamente.";
            $tipo_mensaje = "exito";
            ?>
            <script>
                window.open('imprimirsolicitud.php?id=<?= $id_solicitud ?>', '_blank');
            </script>
            <?php
        } else {
            $mensaje = "Error al guardar la solicitud.";
            $tipo_mensaje = "error";
        }
    }       
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Nueva Solicitud de Material</title>
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
            padding: 15px;
        }

        /* CONTENEDOR PRINCIPAL */
        .form-wrapper {
            max-width: 900px;
            margin: 0 auto;
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
            text-align: center;
        }

        .title-card h1 {
            color: #333;
            font-weight: bold;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
            margin: 0;
            font-size: clamp(1.1rem, 4vw, 1.6rem);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .title-card h1 img {
            width: 45px;
            height: 45px;
        }

        .title-card p {
            color: #666;
            margin-top: 10px;
            font-size: clamp(13px, 2vw, 15px);
        }

        /* TARJETA DEL FORMULARIO */
        .form-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            border: 2px solid #ccc;
            width: 100%;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        /* GRUPOS DE FORMULARIO */
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
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-group label img {
            width: 24px;
            height: 24px;
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

        .form-group input[readonly] {
            background-color: #f3f4f6;
            color: #6b7280;
        }

        textarea {
            resize: vertical;
            min-height: 80px;
            max-height: 150px;
        }

        /* CAMPOS EN LÍNEA */
        .inline-fields {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }

        .inline-fields .form-group {
            flex: 1;
            margin-bottom: 0;
        }

        /* SECCIÓN MOTIVO */
        .motivo-solicitud {
            background-color: #f9fafb;
            padding: 20px;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
            margin-bottom: 25px;
        }

        .motivo-solicitud > strong {
            display: block;
            margin-bottom: 15px;
            color: #374151;
            font-size: 15px;
        }

        .radio-group {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .radio-option {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px 16px;
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            flex: 1;
            min-width: 150px;
        }

        .radio-option:hover {
            border-color: #0d6efd;
            background-color: #eff6ff;
        }

        .radio-option input[type="radio"] {
            width: auto;
            margin: 0;
            accent-color: #0d6efd;
        }

        .radio-option:has(input:checked) {
            border-color: #0d6efd;
            background-color: #eff6ff;
        }

        .campo-dinamico {
            display: none;
            animation: fadeIn 0.3s ease;
        }

        .campo-dinamico.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .info-incidencia {
            background-color: #fef3c7;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #f59e0b;
            margin-top: 15px;
            font-size: 14px;
        }

        .info-incidencia > div {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 8px 15px;
            align-items: center;
        }

        .prioridad-urgente { 
            color: #dc2626; 
            font-weight: 600;
            background-color: #fee2e2;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 12px;
        }

        .prioridad-moderada { 
            color: #d97706; 
            font-weight: 600;
            background-color: #fef3c7;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 12px;
        }

        .prioridad-leve { 
            color: #059669; 
            font-weight: 600;
            background-color: #d1fae5;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 12px;
        }

        /* TABLA DE INSUMOS */
        .insumos-section {
            margin-top: 25px;
        }

        .insumos-section > strong {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
            color: #374151;
            font-size: 16px;
        }

        .insumos-section > strong img {
            width: 32px;
            height: 32px;
        }

        .table-container {
            width: 100%;
            overflow-x: auto;
            margin-top: 15px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }

        th, td {
            border: 1px solid #e5e7eb;
            padding: 12px;
            text-align: center;
            background-color: #fff;
        }

        th {
            background-color: #f9fafb;
            font-weight: 600;
            color: #374151;
            font-size: 13px;
            text-transform: uppercase;
        }

        td input[type="text"],
        td input[type="number"] {
            width: 100%;
            padding: 8px 10px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 14px;
        }

        td input[type="text"]:focus,
        td input[type="number"]:focus {
            border-color: #0d6efd;
            outline: none;
        }

        .insumo-img {
            object-fit: cover;
            border: 2px solid #e5e7eb;
            border-radius: 6px;
            max-width: 50px;
            max-height: 50px;
        }

        /* BOTONES */
        .btn-container {
            display: flex;
            gap: 12px;
            justify-content: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
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
        }

        .btn-primary {
            background-color: #198754 !important;
            color: white;
        }

        .btn-primary:hover {
            background-color: #2563eb;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .btn-success {
            background-color: #0d6efd !important;
            color: white;
        }

        .btn-success:hover {
            background-color: #0d6efd;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .btn-danger {
            background-color: #ef4444;
            color: white;
            padding: 8px 16px;
            font-size: 13px;
        }

        .btn-danger:hover {
            background-color: #dc2626;
        }

        .btn img {
            width: 20px;
            height: 20px;
            filter: brightness(0) invert(1);
        }

        /* ALERTAS */
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 500;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
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

        /* MODAL */
        #modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            z-index: 9998;
            backdrop-filter: blur(3px);
        }

        #modal-busqueda {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 90%;
            max-width: 800px;
            max-height: 85vh;
            background: white;
            border-radius: 12px;
            box-shadow: 0 15px 50px rgba(0,0,0,0.3);
            z-index: 9999;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .modal-header {
            padding: 20px;
            border-bottom: 1px solid #e5e7eb;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%) !important;
            color: white;
        }

        .modal-header h2 {
            margin: 0;
            font-size: 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-body {
            padding: 20px;
            overflow-y: auto;
            flex-grow: 1;
        }

        .modal-footer {
            padding: 15px 20px;
            border-top: 1px solid #e5e7eb;
            background: #f9fafb;
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            flex-wrap: wrap;
        }

        .busqueda-container {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .busqueda-input {
            flex: 1;
            min-width: 200px;
            padding: 10px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
        }

        .busqueda-filtro {
            padding: 10px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            min-width: 150px;
            background: white;
            font-size: 14px;
        }

        .resultados-container {
            max-height: 350px;
            overflow-y: auto;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: white;
        }

        .resultados-header {
            background: #f9fafb;
            padding: 12px;
            font-weight: 600;
            border-bottom: 1px solid #e5e7eb;
            display: grid;
            grid-template-columns: 60px 2fr 1fr 1fr 80px 100px;
            gap: 10px;
            font-size: 13px;
            color: #374151;
        }

        .resultado-item {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
            display: grid;
            grid-template-columns: 60px 2fr 1fr 1fr 80px 100px;
            gap: 10px;
            align-items: center;
            transition: all 0.2s ease;
        }

        .resultado-item:hover {
            background-color: #f9fafb;
        }

        .imagen-insumo-modal {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 6px;
            border: 2px solid #e5e7eb;
        }

        .imagen-placeholder {
            width: 50px;
            height: 50px;
            border-radius: 6px;
            border: 2px solid #e5e7eb;
            background-color: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            color: #9ca3af;
        }

        .stock-critico { 
            color: #dc2626; 
            font-weight: 600;
            background-color: #fee2e2;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 12px;
        }

        .stock-bajo { 
            color: #d97706; 
            font-weight: 600;
            background-color: #fef3c7;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 12px;
        }

        .stock-normal { 
            color: #059669; 
            font-weight: 600;
            background-color: #d1fae5;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 12px;
        }

        .paginacion-container {
            margin-top: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px;
            background: #f9fafb;
            border-radius: 8px;
        }

        .contador-resultados {
            color: #6b7280;
            font-size: 14px;
        }

        /* DESENFOQUE */
        .form-wrapper.blurred {
            filter: blur(3px);
            pointer-events: none;
            user-select: none;
        }

        /* MEDIA QUERIES */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }

            .form-card {
                padding: 15px;
            }

            .inline-fields {
                flex-direction: column;
                gap: 15px;
            }

            .radio-group {
                flex-direction: column;
            }

            .radio-option {
                width: 100%;
            }

            .table-container {
                overflow-x: scroll;
            }

            table {
                min-width: 500px;
            }

            th, td {
                padding: 8px;
                font-size: 13px;
            }

            .btn-container {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }

            #modal-busqueda {
                width: 95%;
                max-height: 90vh;
            }

            .resultados-header,
            .resultado-item {
                grid-template-columns: 50px 1fr 80px 90px;
                gap: 8px;
            }

            .resultados-header > div:nth-child(3),
            .resultados-header > div:nth-child(4),
            .resultado-item > div:nth-child(3),
            .resultado-item > div:nth-child(4) {
                display: none;
            }

            .busqueda-container {
                flex-direction: column;
            }

            .busqueda-input,
            .busqueda-filtro {
                width: 100%;
            }

            .modal-footer {
                flex-direction: column;
            }

            .modal-footer .btn {
                width: 100%;
            }

            .info-incidencia > div {
                grid-template-columns: 1fr;
                gap: 5px;
            }
        }

        @media (max-width: 480px) {
            .title-card h1 img {
                width: 35px;
                height: 35px;
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

            .resultados-header,
            .resultado-item {
                grid-template-columns: 40px 1fr 70px;
            }

            .resultados-header > div:nth-child(5),
            .resultado-item > div:nth-child(5) {
                display: none;
            }
        }
    </style>
</head>
<body>
<div class="form-wrapper" id="formContainer">
    <!-- TARJETA DE TÍTULO -->
    <div class="title-card">
        <h1>
            <img src="../assets/resources/ssolicitudi.png" alt="Solicitud">
            NUEVA SOLICITUD DE MATERIAL
        </h1>
        <p>Complete el formulario para crear una nueva solicitud</p>
    </div>

    <!-- TARJETA DEL FORMULARIO -->
    <div class="form-card">
        
        <?php if($mensaje): ?>
            <div class="alert alert-<?= $tipo_mensaje ?>">
                <?= $tipo_mensaje === 'error' ? '¡!' : '✓' ?> <?= $mensaje ?>
            </div>
        <?php endif; ?>

        <form method="post" action="" id="solicitudForm">
            <div class="inline-fields">
                <div class="form-group">
                    <label for="fecha">Fecha:</label>
                    <input type="date" name="fecha" id="fecha" value="<?= date('Y-m-d') ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="receptor">Receptor: *</label>
                    <input type="text" name="receptor" id="receptor" required placeholder="Nombre del receptor">
                </div>
            </div>

<?php
// Buscar el ID del departamento de Almacén
$stmt = $conexion->prepare("SELECT id_departamento FROM departamentos WHERE nombre LIKE '%Almacén%' OR nombre LIKE '%Almacen%' LIMIT 1");
$stmt->execute();
$deptoAlmacen = $stmt->fetch(PDO::FETCH_ASSOC);
$idAlmacen = $deptoAlmacen ? $deptoAlmacen['id_departamento'] : 2; // Fallback a 2 si no encuentra
?>

<div class="inline-fields">
    <div class="form-group">
        <label for="emisor_dep">Departamento emisor: *</label>
        <select name="emisor_dep" id="emisor_dep" required>
            <option value="">Seleccione...</option>
            <?php foreach ($departamentos as $dep): ?>
                <option value="<?= $dep['id_departamento'] ?>"><?= htmlspecialchars($dep['nombre']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group">
        <label for="receptor_dep">Departamento destino: *</label>
        <select name="receptor_dep_display" id="receptor_dep" required disabled>
            <option value="<?= $idAlmacen ?>" selected>Almacén</option>
        </select>
        <input type="hidden" name="receptor_dep" value="<?= $idAlmacen ?>">
    </div>
</div>

            <!-- Motivo de la solicitud -->
            <div class="motivo-solicitud">
                <strong> <img src="../assets/resources/linsumos.png" alt="Agregar" width="25" height="25"> Motivo de la Solicitud</strong>
                <div class="radio-group">
                    <label class="radio-option">
                        <input type="radio" id="tipo_incidencia" name="tipo_solicitud" value="incidencia" checked onchange="mostrarCampoSolicitud()">
                        <span>Asociar a Incidencia</span>
                    </label>
                    <label class="radio-option">
                        <input type="radio" id="tipo_manual" name="tipo_solicitud" value="manual" onchange="mostrarCampoSolicitud()">
                        <span>Otra Razón</span>
                    </label>
                </div>

                <div class="campo-dinamico active" id="campo-incidencia">
                    <div class="form-group">
                        <label for="id_incidencia">Seleccionar Incidencia:</label>
                        <select name="id_incidencia" id="id_incidencia" onchange="mostrarInfoIncidencia()">
                            <option value="">Seleccione una incidencia...</option>
                            <?php foreach ($incidencias as $inc): ?>
                                <option value="<?= $inc['id_incidencia'] ?>" 
                                        data-descripcion="<?= htmlspecialchars($inc['descripcion']) ?>" 
                                        data-departamento="<?= $inc['departamento'] ?>" 
                                        data-fecha="<?= $inc['fecha'] ?>" 
                                        data-prioridad="<?= $inc['prioridad'] ?>">
                                    #<?= $inc['id_incidencia'] ?> - <?= $inc['departamento'] ?> (<?= $inc['prioridad'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="info-incidencia" id="info-incidencia"></div>
                </div>

                <div class="campo-dinamico" id="campo-manual">
                    <div class="form-group">
                        <label for="razon_manual">Justificar Solicitud:</label>
                        <textarea name="razon_manual" id="razon_manual" rows="3" placeholder="Explique el motivo de esta solicitud..."></textarea>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="descripcion">
                    <img src="../assets/resources/caracteristicasC.png" alt="">
                    Descripción general:
                </label>
                <textarea name="descripcion" id="descripcion" rows="3" placeholder="Describa brevemente la solicitud..."></textarea>
            </div>

            <!-- Sección de insumos -->
            <div class="insumos-section">
                <strong>
                    <img src="../assets/resources/la-gestion-del-inventario.png" alt="">
                    Insumos Solicitados
                </strong>
                <div class="table-container">
                    <table id="tablaMateriales">
                        <thead>
                            <tr>
                                <th>Código/Nombre</th>
                                <th>Unidad</th>
                                <th>Cantidad</th>
                                <th>Imagen</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            
            <div class="btn-container">
                <button type="button" class="btn btn-success" onclick="agregarFila()">
                    <img src="../assets/resources/anadir-caja.png" alt="">
                    Agregar Material
                </button>
                <button type="submit" class="btn btn-primary">
                    <img src="../assets/resources/disco2.png" alt="">
                    Guardar Solicitud
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let modalAbierto = false;
let filaActualIndex = null;
let paginaActual = 1;
const insumosPorPagina = 10;

function mostrarCampoSolicitud() {
    const tipoIncidencia = document.getElementById('tipo_incidencia').checked;
    document.getElementById('campo-incidencia').classList.toggle('active', tipoIncidencia);
    document.getElementById('campo-manual').classList.toggle('active', !tipoIncidencia);
}

function mostrarInfoIncidencia() {
    const select = document.getElementById('id_incidencia');
    const infoDiv = document.getElementById('info-incidencia');
    const option = select.options[select.selectedIndex];
    
    if (option.value) {
        const prioridad = option.getAttribute('data-prioridad');
        const prioridadClass = 'prioridad-' + prioridad.toLowerCase();
        
        infoDiv.innerHTML = `
            <div>
                <div><strong>ID:</strong></div><div>#${option.value}</div>
                <div><strong>Departamento:</strong></div><div>${option.getAttribute('data-departamento')}</div>
                <div><strong>Fecha:</strong></div><div>${option.getAttribute('data-fecha')}</div>
                <div><strong>Prioridad:</strong></div><div><span class="${prioridadClass}">${prioridad}</span></div>
                <div><strong>Descripción:</strong></div><div>${option.getAttribute('data-descripcion')}</div>
            </div>
        `;
    } else {
        infoDiv.innerHTML = '';
    }
}

function agregarFila() {
    const tbody = document.querySelector("#tablaMateriales tbody");
    const index = tbody.children.length;
    filaActualIndex = index;
    
    const fila = document.createElement("tr");
    fila.id = `fila-temp-${index}`;
    fila.innerHTML = `
        <td colspan="5" style="text-align: center; padding: 30px; color: #6b7280; background: #f9fafb;">
            <div>Seleccionando insumo...</div>
            <small>Por favor, seleccione un insumo del catálogo</small>
        </td>
    `;
    tbody.appendChild(fila);
    
    abrirModalBusqueda();
}

function abrirModalBusqueda() {
    modalAbierto = true;
    document.getElementById('formContainer').classList.add('blurred');
    
    const overlay = document.createElement('div');
    overlay.id = 'modal-overlay';
    document.body.appendChild(overlay);
    
    const modal = document.createElement('div');
    modal.id = 'modal-busqueda';
    modal.innerHTML = `
        <div class="modal-header">
            <h2>
                <span><img src="../assets/resources/busqueda.png" alt="Agregar" width="25" height="25" style="filter: brightness(0) invert(1) !important;"> Buscar Insumos</span>
                <button onclick="cerrarModal()" style="background: none; border: none; color: white; font-size: 24px; cursor: pointer;">&times;</button>
            </h2>
        </div>
        <div class="modal-body">
            <div class="busqueda-container">
                <input type="text" id="buscar-insumo" placeholder="Buscar por nombre, código..." class="busqueda-input">
                <select id="filtro-categoria" class="busqueda-filtro">
                    <option value="">Todas las categorías</option>
                </select>
                <button onclick="buscarInsumos()" class="btn btn-success">Buscar</button>
            </div>
            <div class="resultados-container">
                <div class="resultados-header">
                    <div></div>
                    <div>Nombre</div>
                    <div>Categoría</div>
                    <div>Unidad</div>
                    <div>Stock</div>
                    <div>Acción</div>
                </div>
                <div id="resultados-insumos">
                    <div style="padding: 40px; text-align: center; color: #6b7280;">
                        <div>Realice una búsqueda</div>
                        <small>Ingrese un término para ver insumos</small>
                    </div>
                </div>
            </div>
            <div class="paginacion-container">
                <div id="contador-resultados" class="contador-resultados">0 resultados</div>
                <div style="display: flex; gap: 10px;">
                    <button onclick="cambiarPagina(-1)" id="btn-anterior" class="btn btn-primary" disabled style="padding: 8px 16px; font-size: 13px;">Anterior</button>
                    <button onclick="cambiarPagina(1)" id="btn-siguiente" class="btn btn-primary" disabled style="padding: 8px 16px; font-size: 13px;">Siguiente</button>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button onclick="cerrarModal()" class="btn btn-danger">Cancelar</button>
        </div>
    `;
    
    document.body.appendChild(modal);
    setTimeout(() => document.getElementById('buscar-insumo')?.focus(), 100);
    cargarCategorias();
    
    document.getElementById('buscar-insumo').addEventListener('keypress', e => {
        if (e.key === 'Enter') buscarInsumos();
    });
}

function cerrarModal() {
    document.getElementById('modal-overlay')?.remove();
    document.getElementById('modal-busqueda')?.remove();
    modalAbierto = false;
    document.getElementById('formContainer').classList.remove('blurred');
    
    if (filaActualIndex !== null) {
        document.getElementById(`fila-temp-${filaActualIndex}`)?.remove();
        filaActualIndex = null;
    }
}

function cargarCategorias() {
    fetch('buscar_insumos_ajax.php?accion=categorias')
        .then(res => res.json())
        .then(categorias => {
            const select = document.getElementById('filtro-categoria');
            categorias.forEach(cat => {
                const option = document.createElement('option');
                option.value = cat.id_categoria;
                option.textContent = cat.nombre;
                select.appendChild(option);
            });
        });
}

function buscarInsumos() {
    const termino = document.getElementById('buscar-insumo').value;
    const categoria = document.getElementById('filtro-categoria').value;
    
    document.getElementById('resultados-insumos').innerHTML = `
        <div style="padding: 40px; text-align: center;">
            <div style="width: 40px; height: 40px; border: 4px solid #f3f3f3; border-top: 4px solid #3b82f6; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 15px;"></div>
            <div style="color: #6b7280;">Buscando...</div>
        </div>
    `;
    
    fetch(`buscar_insumos_ajax.php?accion=buscar&pagina=${paginaActual}&termino=${encodeURIComponent(termino)}&categoria=${categoria}`)
        .then(res => res.json())
        .then(data => mostrarResultados(data))
        .catch(err => {
            document.getElementById('resultados-insumos').innerHTML = `
                <div style="padding: 30px; text-align: center; color: #dc2626;">
                    <div>Error: ${err.message}</div>
                </div>
            `;
        });
}

function mostrarResultados(data) {
    const resultadosDiv = document.getElementById('resultados-insumos');
    
    if (!data.insumos || data.insumos.length === 0) {
        resultadosDiv.innerHTML = `
            <div style="padding: 40px; text-align: center; color: #6b7280;">
                <div>No se encontraron resultados</div>
                <small>Intente con otros términos</small>
            </div>
        `;
        document.getElementById('contador-resultados').textContent = '0 resultados';
        document.getElementById('btn-anterior').disabled = true;
        document.getElementById('btn-siguiente').disabled = true;
        return;
    }
    
    const total = data.total || 0;
    const desde = (paginaActual - 1) * insumosPorPagina;
    const hasta = Math.min(desde + insumosPorPagina, total);
    
    document.getElementById('contador-resultados').textContent = `${desde + 1} - ${hasta} de ${total}`;
    document.getElementById('btn-anterior').disabled = paginaActual <= 1;
    document.getElementById('btn-siguiente').disabled = hasta >= total;
    
    let html = '';
    data.insumos.forEach(insumo => {
        const stockClass = insumo.cantidad <= insumo.stock_minimo ? 'stock-critico' : 
                          insumo.cantidad <= (insumo.stock_minimo + 5) ? 'stock-bajo' : 'stock-normal';
        
        const imagenHTML = insumo.imagen ? 
            `<img src="../assets/imagenes/insumos/${encodeURIComponent(insumo.imagen)}" class="imagen-insumo-modal" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';" style="display: block;">
             <div class="imagen-placeholder" style="display: none;">Sin img</div>` :
            '<div class="imagen-placeholder">Sin img</div>';
        
        html += `
            <div class="resultado-item">
                <div style="display: flex; justify-content: center;">${imagenHTML}</div>
                <div>
                    <div style="font-weight: 600; font-size: 14px;">${insumo.nombre}</div>
                    <small style="color: #6b7280;">ID: ${insumo.id_insumo}</small>
                </div>
                <div style="font-size: 13px; color: #4b5563;">${insumo.categoria_nombre || 'Sin cat'}</div>
                <div style="font-size: 13px; color: #4b5563;">${insumo.unidad_medida || '-'}</div>
                <div><span class="${stockClass}">${insumo.cantidad}</span></div>
                <div>
                    <button onclick="seleccionarInsumo('${insumo.id_insumo}', '${insumo.nombre.replace(/'/g, "\\'")}', '${insumo.unidad_medida.replace(/'/g, "\\'")}', '${insumo.imagen.replace(/'/g, "\\'")}')" 
                            class="btn btn-success" style="padding: 6px 12px; font-size: 12px;">Agregar</button>
                </div>
            </div>
        `;
    });
    
    resultadosDiv.innerHTML = html;
}

function cambiarPagina(direccion) {
    paginaActual += direccion;
    buscarInsumos();
}

function seleccionarInsumo(id, nombre, unidad, imagen) {
    const tbody = document.querySelector("#tablaMateriales tbody");
    const filaTemp = document.getElementById(`fila-temp-${filaActualIndex}`);
    if (!filaTemp) return;
    
    const nuevaFila = document.createElement("tr");
    
    const imagenHTML = imagen ? 
        `<img src="../assets/imagenes/insumos/${encodeURIComponent(imagen)}" class="insumo-img" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';" style="display: block;">
         <div style="display: none; color: #6b7280; font-size: 11px;">Sin img</div>` :
        '<div style="color: #6b7280; font-size: 11px;">Sin img</div>';
    
    nuevaFila.innerHTML = `
        <td>
            <input type="text" name="materiales[${filaActualIndex}][codigo]" value="${id} - ${nombre}" readonly style="background: #f3f4f6;">
            <input type="hidden" name="materiales[${filaActualIndex}][id_insumo]" value="${id}">
        </td>
        <td><input type="text" name="materiales[${filaActualIndex}][unidad]" value="${unidad}" readonly style="background: #f3f4f6;"></td>
        <td><input type="number" name="materiales[${filaActualIndex}][cantidad_pedida]" step="0.01" min="0.01" required placeholder="Cant"></td>
        <td>${imagenHTML}<input type="hidden" name="materiales[${filaActualIndex}][cantidad_recibida]" value="0"></td>
        <td><button type="button" class="btn btn-danger" onclick="this.closest('tr').remove()">Eliminar</button></td>
    `;
    
    filaTemp.replaceWith(nuevaFila);
    cerrarModal();
    
    setTimeout(() => {
        const input = document.querySelector(`input[name="materiales[${filaActualIndex}][cantidad_pedida]"]`);
        if (input) {
            input.focus();
            input.select();
        }
    }, 50);
}

function seleccionarManual() {
    const tbody = document.querySelector("#tablaMateriales tbody");
    const filaTemp = document.getElementById(`fila-temp-${filaActualIndex}`);
    if (!filaTemp) return;
    
    const nuevaFila = document.createElement("tr");
    nuevaFila.innerHTML = `
        <td>
            <input type="text" name="materiales[${filaActualIndex}][codigo]" placeholder="Nombre insumo" required>
            <input type="hidden" name="materiales[${filaActualIndex}][id_insumo]" value="">
        </td>
        <td><input type="text" name="materiales[${filaActualIndex}][unidad]" placeholder="Unidad" required></td>
        <td><input type="number" name="materiales[${filaActualIndex}][cantidad_pedida]" step="0.01" min="0.01" required placeholder="Cant"></td>
        <td><div style="color: #6b7280; font-size: 11px;">(Manual)</div><input type="hidden" name="materiales[${filaActualIndex}][cantidad_recibida]" value="0"></td>
        <td><button type="button" class="btn btn-danger" onclick="this.closest('tr').remove()">Eliminar</button></td>
    `;
    
    filaTemp.replaceWith(nuevaFila);
    cerrarModal();
    
    setTimeout(() => {
        const input = document.querySelector(`input[name="materiales[${filaActualIndex}][codigo]"]`);
        if (input) {
            input.focus();
            input.select();
        }
    }, 50);
}

// Validaciones del formulario
document.getElementById('solicitudForm').addEventListener('submit', function(e) {
    const tbody = document.querySelector("#tablaMateriales tbody");
    if (tbody.children.length === 0) {
        e.preventDefault();
        alert("Debe agregar al menos un insumo.");
        return;
    }
    
    let cantidadValida = true;
    document.querySelectorAll('input[name$="[cantidad_pedida]"]').forEach(input => {
        if (!input.value || parseFloat(input.value) <= 0) cantidadValida = false;
    });
    
    if (!cantidadValida) {
        e.preventDefault();
        alert("Todos los insumos deben tener cantidad válida > 0.");
        return;
    }
    
    if (!confirm("¿Está seguro de crear esta solicitud?")) {
        e.preventDefault();
    }
});

// Prevenir números negativos
document.addEventListener('input', function(e) {
    if (e.target.type === 'number' && e.target.name?.includes('cantidad_pedida')) {
        if (parseFloat(e.target.value) < 0) e.target.value = '';
    }
});

// Inicializar
document.addEventListener('DOMContentLoaded', function() {
    mostrarCampoSolicitud();
    const selectIncidencia = document.getElementById('id_incidencia');
    if (selectIncidencia.options.length > 1) {
        selectIncidencia.selectedIndex = 1;
        mostrarInfoIncidencia();
    }
});

// Validación para campo de receptor - SOLO LETRAS, MÁXIMO 30 CARACTERES
const receptorInput = document.getElementById('receptor');

receptorInput.addEventListener('keydown', function(e) {
    // Permitir teclas de control (backspace, tab, enter, flechas, etc.)
    const teclasPermitidas = ['Backspace', 'Tab', 'Enter', 'Delete', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Home', 'End'];
    if (teclasPermitidas.includes(e.key)) {
        return; // Permitir estas teclas sin validación
    }
    
    // Prevenir números y caracteres especiales (solo letras, espacios y puntos)
    if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s.]$/.test(e.key)) {
        e.preventDefault();
    }
});

receptorInput.addEventListener('input', function() {
    // Eliminar cualquier caracter que no sea letra, espacio o punto
    this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s.]/g, '');
    
    // Limitar a 30 caracteres
    if (this.value.length > 30) {
        this.value = this.value.slice(0, 30);
    }
    
    // Capitalizar primera letra de cada palabra (opcional)
    this.value = this.value.replace(/\b\w/g, function(l) { return l.toUpperCase(); });
});
</script>

</body>
</html>
<?php ob_end_flush(); ?>