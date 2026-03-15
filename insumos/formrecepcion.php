<?php
session_start();
ob_start();
require_once "../encabezado.php";
require_once "../conex.php";
require_once "solicitud.php";
require_once "insumo.php";
require_once "../user/gestorsesion.php";

GestorSesiones::iniciar();

$solicitud = new Solicitud($conexion);

if (!isset($_GET['id_solicitud'])) {
    die("No se especificó solicitud.");
}

$id_solicitud = $_GET['id_solicitud'];
$detalle = $solicitud->obtenerDetalle($id_solicitud);
$mensaje_error = "";
$exito = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $materiales = [];
    $firma_usuario = GestorSesiones::get('firma');
    
    // Validación ANTES de procesar: verificar que ninguna entrega deje stock en 0
    $errorStockCero = false;
    $insumoError = "";
    
    foreach ($_POST['cantidad_recibida'] as $id_insumo => $cantidad) {
        $cantidad = floatval($cantidad);
        if ($cantidad > 0) {
            // Obtener stock actual del insumo
            $stmtStock = $conexion->prepare("SELECT cantidad FROM stock_almacen WHERE id_insumo = ?");
            $stmtStock->execute([$id_insumo]);
            $stockActual = floatval($stmtStock->fetchColumn() ?: 0);
            
            // Obtener nombre del insumo para el mensaje de error
            $stmtNombre = $conexion->prepare("SELECT nombre FROM insumos WHERE id_insumo = ?");
            $stmtNombre->execute([$id_insumo]);
            $nombreInsumo = $stmtNombre->fetchColumn();
            
            // Si después de entregar, el stock sería 0 o negativo
            if ($stockActual - $cantidad <= 0) {
                $errorStockCero = true;
                $insumoError = $nombreInsumo;
                break;
            }
        }
    }
    
    if ($errorStockCero) {
        $mensaje_error = "No se puede tener un stock de 0 en el almacen para el insumo '$insumoError'. Por favor, solicite la compra de mas productos antes de realizar la entrega.";
    } else {
        // Continuar con el proceso normal si no hay error
        foreach ($_POST['cantidad_recibida'] as $id_insumo => $cantidad) {
            if (floatval($cantidad) > 0) {
                $materiales[] = [
                    'id_insumo' => $id_insumo,
                    'cantidad_recibida' => $cantidad,
                    'firma_usuario' => $firma_usuario
                ];
            }
        }

        if (!empty($materiales)) {
            $resultado = $solicitud->registrarRecepcion($id_solicitud, $materiales);
            if ($resultado === true) {
                header("Location: listarsolicitudes.php?mensaje=recepcion_exitosa");
                exit;
            } else {
                $mensaje_error = $resultado;
            }
        } else {
            $mensaje_error = "Debe ingresar al menos una cantidad para registrar la recepcion.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Recepción de Solicitud #<?= htmlspecialchars($id_solicitud) ?></title>
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

        /* ALERTAS */
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

        .alert-warning {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }

        /* CONTENEDOR DE TABLA RESPONSIVE */
        .table-container {
            width: 100%;
            overflow-x: auto;
            margin-top: 20px;
            border-radius: 12px;
            border: 1px solid #e0e0e0;
        }

        /* TABLA CON ESTILO listartrabajador.php */
        table {
            width: 100%;
            border-collapse: collapse !important;
            border-spacing: 0;
            min-width: 1000px;
            font-size: 14px;
        }

        /* TH ESTILO listartrabajador.php */
        th {
            background-color: #cfe2ff !important;
            font-weight: bold !important;
            color: #333 !important;
            padding: 15px 12px !important;
            text-align: center;
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

        /* ID INSUMO */
        .id-insumo {
            font-weight: 700;
            color: #0d6efd;
            font-size: 13px;
        }

        /* BADGE UNIDAD */
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            background-color: #6c757d;
            color: white;
        }

        /* IMÁGENES EN TABLA */
        .imagen-container {
            display: inline-block;
            overflow: hidden;
            border-radius: 8px;
            border: 2px solid #e0e0e0;
            width: 60px;
            height: 60px;
            position: relative;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .imagen-insumo {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .imagen-insumo:hover {
            transform: scale(1.2);
        }

        .no-image {
            color: #999;
            font-style: italic;
            font-size: 12px;
        }

        /* CANTIDADES DESTACADAS */
        .cantidad-stock {
            font-weight: 600;
            color: #0d6efd;
            font-size: 15px;
        }

        .cantidad-pedida {
            font-weight: 600;
            color: #333;
            font-size: 15px;
        }

        .cantidad-faltante {
            font-weight: 700;
            color: #dc2626;
            font-size: 15px;
        }

        .cantidad-completa {
            font-weight: 700;
            color: #059669;
            font-size: 15px;
        }

        /* INPUT CANTIDAD */
        .input-group {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            flex-wrap: wrap;
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

        input[type="number"].stock-peligro {
            border-color: #dc2626;
            background-color: #fee2e2;
        }

        .unidad-texto {
            font-size: 13px;
            color: #6b7280;
            font-weight: 500;
        }

        .stock-limitado {
            display: block;
            font-size: 11px;
            color: #dc2626;
            margin-top: 4px;
            font-weight: 500;
        }

        .stock-advertencia {
            display: block;
            font-size: 11px;
            color: #856404;
            margin-top: 4px;
            font-weight: 500;
            background-color: #fff3cd;
            padding: 4px 8px;
            border-radius: 4px;
        }

        /* BOTONES MINIMALISTAS FLAT */
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

        .btn-danger {
            background-color: #dc2626 !important;
            color: white;
        }

        .btn-danger:hover {
            background-color: #b91c1c;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
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
                font-size: 13px;
            }

            .imagen-container {
                width: 50px;
                height: 50px;
            }

            input[type="number"] {
                width: 80px;
                padding: 8px;
                font-size: 14px;
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

            .input-group {
                flex-direction: column;
                gap: 5px;
            }
        }

        @media (max-width: 480px) {
            .title-card {
                padding: 15px;
            }

            .form-card {
                padding: 15px;
            }

            .btn {
                padding: 14px 20px;
                font-size: 16px;
            }

            .badge {
                font-size: 11px;
                padding: 3px 8px;
            }

            .id-insumo {
                font-size: 12px;
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
            <img src="../assets/resources/campana.png" alt="Campana">
            RECEPCION DE SOLICITUD #<?= htmlspecialchars($id_solicitud) ?>
        </h1>
        <p>Registro de materiales recibidos</p>
    </div>

    <!-- TARJETA DEL FORMULARIO -->
    <div class="form-card">
        
        <?php if (!empty($mensaje_error)): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($mensaje_error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" id="recepcionForm">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Insumo</th>
                            <th>Unidad</th>
                            <th>Imagen</th>
                            <th>Stock</th>
                            <th>Pedida</th>
                            <th>Faltante</th>
                            <th>A Entregar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($detalle as $d):
                            $faltante = $d->cantidad_pedida - $d->cantidad_recibida;

                            $stmtStock = $conexion->prepare("SELECT cantidad FROM stock_almacen WHERE id_insumo = ?");
                            $stmtStock->execute([$d->id_insumo]);
                            $stockActual = floatval($stmtStock->fetchColumn() ?: 0);
                            
                            $stmtUnidad = $conexion->prepare("SELECT unidad_medida FROM insumos WHERE id_insumo = ?");
                            $stmtUnidad->execute([$d->id_insumo]);
                            $unidadMedida = $stmtUnidad->fetchColumn() ?: 'No especificado';
                            
                            $maxPermitido = max(0, min($faltante, $stockActual));
                            $completo = $faltante <= 0;
                            
                            // Verificar si entregar todo dejaría stock en 0
                            $stockPeligro = ($stockActual - $maxPermitido <= 0 && $maxPermitido > 0);
                        ?>
                            <tr>
                                <td class="id-insumo"><?= htmlspecialchars($d->id_insumo) ?></td>
                                <td><?= htmlspecialchars($d->nombre) ?></td>
                                <td><span class="badge"><?= htmlspecialchars($unidadMedida) ?></span></td>
                                <td>
                                    <?php if (!empty($d->imagen)): ?>
                                        <div class="imagen-container">
                                            <img src="/lugopata/assets/imagenes/insumos/<?= htmlspecialchars($d->imagen) ?>" 
                                                 alt="<?= htmlspecialchars($d->nombre) ?>" 
                                                 class="imagen-insumo">
                                        </div>
                                    <?php else: ?>
                                        <span class="no-image">Sin imagen</span>
                                    <?php endif; ?>
                                </td>
                                <td class="cantidad-stock <?= $stockPeligro ? 'stock-peligro' : '' ?>"><?= number_format($stockActual, 2) ?></td>
                                <td class="cantidad-pedida"><?= $d->cantidad_pedida ?></td>
                                <td class="<?= $completo ? 'cantidad-completa' : 'cantidad-faltante' ?>">
                                    <?= $faltante ?>
                                </td>
                                <td>
                                    <div class="input-group">
                                        <input type="number" 
                                               name="cantidad_recibida[<?= $d->id_insumo ?>]" 
                                               value="0" 
                                               min="0" 
                                               max="<?= $maxPermitido ?>" 
                                               step="0.01"
                                               required
                                               <?= $completo ? 'disabled' : '' ?>
                                               data-stock-actual="<?= $stockActual ?>"
                                               class="<?= $stockPeligro ? 'stock-peligro' : '' ?>">
                                        <span class="unidad-texto"><?= htmlspecialchars($unidadMedida) ?></span>
                                    </div>
                                    <?php if ($stockPeligro && !$completo): ?>

                                    <?php endif; ?>
                                    <?php if ($maxPermitido < $faltante && !$completo): ?>
                                        <span class="stock-limitado">Max: <?= $maxPermitido ?> (stock limitado)</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Botones de accion -->
            <div class="btn-container">
                <button type="submit" class="btn btn-success">
                    <img src="../assets/resources/disco2.png" alt="Guardar">
                    Guardar Recepcion
                </button>
                <button type="button" onclick="history.back()" class="btn btn-danger">
                    <img src="../assets/resources/prohibicion.png" alt="Cancelar">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('recepcionForm');
    const inputs = document.querySelectorAll('input[type="number"]:not([disabled])');

    // Validacion en tiempo real
    inputs.forEach(input => {
        input.addEventListener('keydown', function(e) {
            if (e.key === '-' || e.key === 'Subtract') {
                e.preventDefault();
            }
        });

        input.addEventListener('input', function() {
            const max = parseFloat(this.getAttribute('max'));
            const value = parseFloat(this.value);
            const stockActual = parseFloat(this.getAttribute('data-stock-actual'));

            if (value > max) {
                this.value = max;
                alert('La cantidad no puede exceder el maximo permitido: ' + max);
            }
            if (value < 0) {
                this.value = 0;
            }
            
            // Verificar si con esta cantidad el stock quedaria en 0
            if (stockActual - value <= 0 && value > 0) {
                this.classList.add('stock-peligro');
            } else {
                this.classList.remove('stock-peligro');
            }
        });
    });

    // Validacion del formulario
    form.addEventListener('submit', function(e) {
        let totalCantidad = 0;
        let hayCantidades = false;
        let stockCeroWarning = false;
        let insumosConStockCero = [];

        inputs.forEach(input => {
            const valor = parseFloat(input.value) || 0;
            const stockActual = parseFloat(input.getAttribute('data-stock-actual'));
            
            if (valor > 0) {
                hayCantidades = true;
                totalCantidad += valor;
                
                // Verificar si esta entrega dejaria stock en 0
                if (stockActual - valor <= 0) {
                    stockCeroWarning = true;
                    // Buscar el nombre del insumo en la fila correspondiente
                    const fila = input.closest('tr');
                    const nombreInsumo = fila.querySelector('td:nth-child(2)').textContent.trim();
                    insumosConStockCero.push(nombreInsumo);
                }
            }
        });

        if (!hayCantidades) {
            e.preventDefault();
            alert('Debe ingresar al menos una cantidad para registrar la recepcion.');
            return false;
        }

        if (stockCeroWarning) {
            const mensaje = 'ADVERTENCIA\n\nLos siguientes insumos quedaran con stock 0 en el almacen:\n- ' + 
                           insumosConStockCero.join('\n- ') + 
                           '\n\nNo se puede tener stock 0. Por favor, solicite la compra de mas productos antes de realizar la entrega.\n\n¿Desea continuar de todas formas?';
            
            if (!confirm(mensaje)) {
                e.preventDefault();
                return false;
            }
        }

        if (!confirm('Confirmar recepcion?\n\nTotal de materiales a registrar: ' + totalCantidad.toFixed(2))) {
            e.preventDefault();
        }
    });
});
</script>

</body>
</html>
<?php ob_end_flush(); ?>