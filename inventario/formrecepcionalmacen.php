<?php
session_start();
ob_start();
require_once "../encabezado.php";
require_once "../conex.php";
require_once "salidaalmacen.php";
require_once "../user/gestorsesion.php";

// Validación de sesión
GestorSesiones::iniciar();

$salidaObj = new SalidaAlmacen($conexion);

// Verifica que se haya proporcionado un ID de solicitud
if (!isset($_GET['id_solicitud'])) {
    die("No se especificó solicitud de salida.");
}

$id_solicitud = $_GET['id_solicitud'];
$detalle = $salidaObj->obtenerDetalle($id_solicitud);

// Procesa el envío del formulario POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST['cantidad_recibida'] as $id_insumo => $cantidad) {
        $cantidad = floatval($cantidad);
        if ($cantidad <= 0) continue;

        // Actualizar cantidad entregada
        $sentencia = $conexion->prepare("UPDATE detalle_salida_almacen 
                                     SET cantidad_entregada = cantidad_entregada + ?
                                     WHERE id_solicitud = ? AND id_insumo = ?");
        $sentencia->execute([$cantidad, $id_solicitud, $id_insumo]);

        // Actualizar stock
        $sentencia2 = $conexion->prepare("INSERT INTO stock_almacen (id_insumo, cantidad) 
                                     VALUES (?, ?) 
                                     ON DUPLICATE KEY UPDATE cantidad = cantidad + VALUES(cantidad)");
        $sentencia2->execute([$id_insumo, $cantidad]);
    }

    // Verificar si todos los insumos fueron entregados
    $sentenciaCheck = $conexion->prepare("SELECT SUM(cantidad_solicitada - cantidad_entregada) AS faltantes
                                     FROM detalle_salida_almacen
                                     WHERE id_solicitud = ?");
    $sentenciaCheck->execute([$id_solicitud]);
    $faltantes = $sentenciaCheck->fetch(PDO::FETCH_ASSOC)['faltantes'];

    if ($faltantes <= 0) {
        $sentenciaFinal = $conexion->prepare("UPDATE solicitud_salida_almacen 
                                         SET estado = 'Finalizada' 
                                         WHERE id_solicitud = ?");
        $sentenciaFinal->execute([$id_solicitud]);
    }

    header("Location: listarsolicitudstock.php?mensaje=recepcion_exitosa");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Recepción de Solicitud de Almacén</title>
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
            max-width: 1200px;
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

        /* TARJETA DEL FORMULARIO */
        .form-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            border: 2px solid #ccc;
            width: 100%;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        /* NOTA INFORMATIVA */
        .info-note {
            margin: 20px 0;
            padding: 15px 20px;
            background-color: #e7f1ff;
            border-radius: 8px;
            border-left: 4px solid #0d6efd;
            font-size: 14px;
            color: #084298;
        }

        .info-note strong {
            color: #0d6efd;
        }

        /* CONTENEDOR DE TABLA RESPONSIVE */
        .table-container {
            width: 100% !important;
            overflow-x: auto !important;
            margin: 15px 0 !important;
            border-radius: 10px !important;
            border: 1px solid #ddd !important;
            background: white !important;
        }

        /* TABLA CON ESTILO listartrabajador.php */
        table {
            width: 100% !important;
            border-collapse: collapse !important;
            min-width: 700px !important;
            font-size: 14px !important;
        }

        /* TH ESTILO listartrabajador.php */
        th {
            background-color: #cfe2ff !important;
            font-weight: bold !important;
            color: #333 !important;
            padding: 15px 12px;
            text-align: center;
            border: 1px solid #dee2e6 !important;
            position: sticky;
            top: 0;
            white-space: nowrap;
        }

        th:first-child {
            border-radius: 12px 0 0 0;
        }

        th:last-child {
            border-radius: 0 12px 0 0;
        }

        td {
            padding: 15px 12px;
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

        /* IMÁGENES EN TABLA */
        .insumo-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #e0e0e0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .no-image {
            color: #999;
            font-style: italic;
            font-size: 12px;
        }

        /* CANTIDADES DESTACADAS */
        .cantidad-solicitada {
            font-weight: 600;
            color: #0d6efd;
            font-size: 15px;
        }

        .cantidad-faltante {
            font-weight: 600;
            color: #dc2626;
            font-size: 15px;
        }

        /* INPUT CANTIDAD */
        .input-group {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        input[type="number"] {
            padding: 10px 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            width: 100px;
            text-align: center;
            font-size: 15px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        input[type="number"]:focus {
            border-color: #0d6efd;
            outline: none;
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
        }

        .unidad-label {
            font-size: 13px;
            color: #6b7280;
            font-weight: 500;
        }

        /* BOTONES MINIMALISTAS FLAT */
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

        .btn-success {
            background-color: #198754 !important;
            color: white;
        }

        .btn-success:hover {
            background-color: #059669;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
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

        /* INPUT NUMBER - QUITAR FLECHAS */
        input[type="number"]::-webkit-outer-spin-button,
        input[type="number"]::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        input[type="number"] {
            -moz-appearance: textfield;
        }

        /* MEDIA QUERIES PARA MÓVILES */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }

            .form-card {
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

            th {
                padding: 12px 8px;
                font-size: 12px;
            }

            td {
                padding: 12px 8px;
            }

            .insumo-img {
                width: 50px;
                height: 50px;
            }

            input[type="number"] {
                width: 80px;
                padding: 8px;
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

            .form-card {
                padding: 15px;
            }

            .info-note {
                padding: 12px 15px;
                font-size: 13px;
            }

            input[type="number"] {
                width: 70px;
                font-size: 14px;
            }

            .btn {
                padding: 14px 20px;
                font-size: 16px;
            }
        }

        /* SCROLLBAR PERSONALIZADA PARA TABLA */
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
            <img src="../assets/resources/inventario.png" alt="Inventario">
            RECEPCIÓN DE SOLICITUD #<?= htmlspecialchars($id_solicitud) ?>
        </h1>
        <p>Registrar recepción de materiales solicitados</p>
    </div>

    <!-- TARJETA DEL FORMULARIO -->
    <div class="form-card">
        <form method="POST" id="recepcionForm">
            
            <div class="info-note">
                <strong>Instrucción:</strong> Ingrese la cantidad recibida para cada insumo. 
                El sistema actualizará automáticamente el stock del almacén.
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Insumo</th>
                            <th>Unidad</th>
                            <th>Imagen</th>
                            <th>Solicitada</th>
                            <th>Faltante</th>
                            <th>A Recibir</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($detalle as $d): 
                            $faltante = $d->cantidad_solicitada - $d->cantidad_entregada;
                            
                            $stmtUnidad = $conexion->prepare("SELECT unidad_medida FROM insumos WHERE id_insumo = ?");
                            $stmtUnidad->execute([$d->id_insumo]);
                            $unidadMedida = $stmtUnidad->fetchColumn() ?: 'No especificado';
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($d->nombre) ?></td>
                            <td><?= htmlspecialchars($unidadMedida) ?></td>
                            <td>
                                <?php if (!empty($d->imagen)): ?>
                                    <img src="/lugopata/assets/imagenes/insumos/<?= htmlspecialchars($d->imagen) ?>" 
                                         alt="<?= htmlspecialchars($d->nombre) ?>" 
                                         class="insumo-img">
                                <?php else: ?>
                                    <span class="no-image">Sin imagen</span>
                                <?php endif; ?>
                            </td>
                            <td class="cantidad-solicitada"><?= $d->cantidad_solicitada ?></td>
                            <td class="cantidad-faltante"><?= $faltante ?></td>
                            <td>
                                <div class="input-group">
                                    <input type="number" 
                                           name="cantidad_recibida[<?= $d->id_insumo ?>]" 
                                           value="0" 
                                           min="0" 
                                           max="<?= $faltante ?>" 
                                           step="0.01"
                                           required>
                                    <span class="unidad-label"><?= htmlspecialchars($unidadMedida) ?></span>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Botones de acción -->
            <div class="btn-container">
                <button type="submit" class="btn btn-success">
                    <img src="../assets/resources/disco2.png" alt="Guardar">
                    Guardar Recepción
                </button>
                <a href="listarsolicitudstock.php" class="btn btn-primary">
                    <img src="../assets/resources/volver2.png" alt="Volver">
                    Regresar
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('recepcionForm');
    const inputs = document.querySelectorAll('input[type="number"]');

    // Validación de cantidades
    inputs.forEach(input => {
        // Prevenir números negativos
        input.addEventListener('keydown', function(e) {
            if (e.key === '-' || e.key === 'Subtract') {
                e.preventDefault();
            }
        });

        // Validar al cambiar
        input.addEventListener('change', function() {
            const max = parseFloat(this.getAttribute('max'));
            const value = parseFloat(this.value);
            
            if (value < 0) {
                this.value = 0;
                alert('La cantidad no puede ser negativa.');
            } else if (value > max) {
                this.value = max;
                alert('La cantidad no puede exceder la cantidad faltante (' + max + ').');
            }
        });
    });

    // Confirmación antes de enviar
    form.addEventListener('submit', function(e) {
        let totalRecibido = 0;
        inputs.forEach(input => {
            totalRecibido += parseFloat(input.value) || 0;
        });

        if (totalRecibido === 0) {
            e.preventDefault();
            alert('Debe ingresar al menos una cantidad para registrar la recepción.');
            return;
        }

        if (!confirm('¿Está seguro de registrar esta recepción?\n\nTotal a recibir: ' + totalRecibido + ' unidades.')) {
            e.preventDefault();
        }
    });
});
</script>

</body>
</html>
<?php ob_end_flush(); ?>