<?php
session_start();
require_once "../conex.php";
require_once "../insumos/insumo.php";
require_once "../insumos/categoria.php";
include_once "../encabezado.php";

// Verifica que se haya proporcionado un ID de insumo
if (!isset($_GET['id'])) {
    exit("No se especificó el insumo.");
}

// Obtiene el insumo específico por ID
$insumoObj = new Insumo($conexion);
$insumo = $insumoObj->obtener($_GET['id']);

// Valida que el insumo exista
if (!$insumo) {
    exit("Insumo no encontrado.");
}

// Obtener la cantidad REAL de stock_almacen (fuente de verdad para el inventario)
$sqlStock = "SELECT cantidad FROM stock_almacen WHERE id_insumo = ?";
$sentenciaStock = $conexion->prepare($sqlStock);
$sentenciaStock->execute([$insumo->id_insumo]);
$stockActual = $sentenciaStock->fetch(PDO::FETCH_OBJ);
$cantidadStock = $stockActual ? $stockActual->cantidad : 0;

// Obtener categorías para el select
$categoriaObj = new Categoria($conexion);
$categorias = $categoriaObj->obtenerTodas();

$mensaje = "";
$exito = false;

// Procesa la actualización del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $unidad_medida = $_POST['unidad_medida'];
    $cantidad = $_POST['cantidad'];
    $stock_minimo = $_POST['stock_minimo'];
    $stock_maximo = $_POST['stock_maximo'];
    $id_categoria = $_POST['id_categoria'];
    $imagenNombre = $insumo->imagen;

    // Validar que stock máximo sea mayor que stock mínimo
    if ($stock_maximo <= $stock_minimo) {
        $mensaje = "Error: El stock máximo debe ser mayor que el stock mínimo.";
    } else {
        // Manejo de imagen nueva si se subió
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
            $carpetaDestino = "../assets/imagenes/insumos/";
            if (!is_dir($carpetaDestino)) mkdir($carpetaDestino, 0777, true);
            $imagenNombre = time() . "_" . $_FILES['imagen']['name'];
            move_uploaded_file($_FILES['imagen']['tmp_name'], $carpetaDestino . $imagenNombre);
        }

        // Actualizar insumo
        $insumoActualizar = new Insumo($conexion, $insumo->id_insumo, $nombre, $descripcion, $unidad_medida, 0, $stock_minimo, $stock_maximo, $imagenNombre, $id_categoria);
        
        if ($insumoActualizar->actualizarSinCantidad($insumo->id_insumo)) {
            // Actualizar la cantidad REAL en stock_almacen
            $sqlStock = "UPDATE stock_almacen SET cantidad = ? WHERE id_insumo = ?";
            $sentenciaStock = $conexion->prepare($sqlStock);
            $resultadoStock = $sentenciaStock->execute([$cantidad, $insumo->id_insumo]);
            
            if ($resultadoStock) {
                $mensaje = "Insumo actualizado correctamente.";
                $exito = true;
                $insumo = $insumoActualizar->obtener($insumo->id_insumo);
                $cantidadStock = $cantidad;
            } else {
                $mensaje = "Error al actualizar la cantidad en stock.";
            }
        } else {
            $mensaje = "Error al actualizar el insumo.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Editar Insumo</title>
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
            max-width: 800px;
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

        /* INFO ADICIONAL */
        .info-adicional {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            border: 1px solid #e9ecef;
            margin-bottom: 25px;
        }

        .info-adicional h4 {
            margin-top: 0;
            margin-bottom: 15px;
            color: #495057;
            font-size: 16px;
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
            padding-bottom: 10px;
        }

        .info-adicional p {
            margin: 8px 0;
            font-size: 14px;
            color: #495057;
        }

        .info-adicional strong {
            color: #333;
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

        /* CAMPOS SOLO LECTURA */
        .form-group input[readonly] {
            background-color: #f8f9fa;
            border-color: #dee2e6;
            color: #6c757d;
            cursor: not-allowed;
        }

        /* SELECT PERSONALIZADO */
        select {
            appearance: none;
            background-image: url('data:image/svg+xml;utf8,<svg fill="%23666" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><path d="M7 10l5 5 5-5z"/><path d="M0 0h24v24H0z" fill="none"/></svg>');
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 20px;
            padding-right: 40px;
        }

        /* CAMPOS EN LÍNEA */
        .inline-fields {
            display: flex;
            gap: 15px;
        }

        .inline-fields .form-group {
            flex: 1;
        }

        /* TEXTAREA */
        textarea {
            resize: vertical;
            min-height: 80px;
            font-family: inherit;
        }

        /* INPUT FILE */
        input[type="file"] {
            padding: 10px;
            border: 2px dashed #ced4da;
            background-color: #f8f9fa;
            cursor: pointer;
        }

        input[type="file"]:hover {
            border-color: #0d6efd;
            background-color: #e7f1ff;
        }

        /* ALERTA CANTIDAD IMPORTANTE */
        .cantidad-alerta {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 8px;
            padding: 15px;
            margin-top: 10px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .cantidad-alerta img {
            width: 24px;
            height: 24px;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .cantidad-alerta strong {
            color: #856404;
            display: block;
            margin-bottom: 4px;
        }

        .cantidad-alerta span {
            color: #856404;
            font-size: 13px;
            line-height: 1.4;
        }

        /* INFO STOCK */
        .stock-info {
            font-size: 12px;
            color: #6b7280;
            margin-top: 6px;
            font-style: italic;
        }

        /* IMAGEN ACTUAL */
        .current-image {
            text-align: center;
            margin: 10px 0;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 8px;
            border: 2px dashed #dee2e6;
        }

        .current-image img {
            border-radius: 8px;
            max-width: 120px;
            max-height: 120px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .no-image {
            color: #999;
            font-style: italic;
            padding: 20px;
        }

        .image-name {
            margin-top: 8px;
            font-size: 12px;
            color: #666;
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

        .alert-success {
            background-color: #d1fae5;
            color: #059669;
            border: 1px solid #a7f3d0;
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

        /* INPUT NUMBER */
        input[type="number"] {
            -moz-appearance: textfield;
        }

        input[type="number"]::-webkit-outer-spin-button,
        input[type="number"]::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        /* MEDIA QUERIES PARA MÓVILES */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }

            .form-card {
                padding: 20px;
            }

            .info-adicional {
                padding: 15px;
            }

            .inline-fields {
                flex-direction: column;
                gap: 0;
            }

            .btn-container {
                flex-direction: column;
                gap: 10px;
            }

            .btn {
                width: 100%;
                min-width: auto;
            }

            .title-card h1 {
                flex-direction: column;
                gap: 5px;
            }

            .title-card h1 img {
                width: 35px;
                height: 35px;
            }

            .cantidad-alerta {
                flex-direction: column;
                text-align: center;
            }

            .cantidad-alerta img {
                margin: 0 auto;
            }
        }

        @media (max-width: 480px) {
            .title-card {
                padding: 15px;
            }

            .form-card {
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

            .info-adicional h4 {
                font-size: 15px;
            }
        }

        @media (min-width: 1200px) {
            .form-wrapper {
                max-width: 750px;
            }
        }
    </style>
</head>
<body>
<div class="form-wrapper">
    <!-- TARJETA DE TÍTULO -->
    <div class="title-card">
        <h1>
            <img src="../assets/resources/insumos.png" alt="Insumos">
            EDITAR INSUMO
        </h1>
        <p>Modifique los campos que desee actualizar</p>
    </div>

    <!-- TARJETA DEL FORMULARIO -->
    <div class="form-card">
        
        <?php if($mensaje): ?>
            <div class="alert <?= $exito ? 'alert-success' : 'alert-error' ?>">
                <?= htmlspecialchars($mensaje) ?>
            </div>
        <?php endif; ?>

        <!-- Información adicional del insumo -->
        <div class="info-adicional">
            <h4>Información del Insumo</h4>
            <p><strong>ID:</strong> <?= htmlspecialchars($insumo->id_insumo) ?></p>
            <p><strong>Stock actual:</strong> <?= htmlspecialchars($cantidadStock) . ' ' . htmlspecialchars($insumo->unidad_medida) ?></p>
            <p><strong>Categoría actual:</strong> 
                <?php 
                $catNombre = 'Sin categoría';
                foreach($categorias as $cat) {
                    if ($cat['id_categoria'] == $insumo->id_categoria) {
                        $catNombre = htmlspecialchars($cat['nombre']);
                        break;
                    }
                }
                echo $catNombre;
                ?>
            </p>
        </div>

        <!-- Formulario para editar insumo -->
        <form method="post" action="" enctype="multipart/form-data" id="stockForm">
            <!-- Campo ID del insumo (solo lectura) -->
            <div class="form-group">
                <label for="id_insumo">ID del Insumo:</label>
                <input type="text" id="id_insumo" value="<?= htmlspecialchars($insumo->id_insumo) ?>" readonly>
            </div>

            <!-- Campo nombre del insumo -->
            <div class="form-group">
                <label for="nombre">Nombre del Insumo:</label>
                <input type="text" name="nombre" id="nombre" value="<?= htmlspecialchars($insumo->nombre) ?>" required>
            </div>

            <!-- Campo descripción -->
            <div class="form-group">
                <label for="descripcion">Descripción:</label>
                <textarea name="descripcion" id="descripcion" rows="3"><?= htmlspecialchars($insumo->descripcion) ?></textarea>
            </div>

            <!-- Campos en línea para unidad de medida y categoría -->
            <div class="inline-fields">
                <div class="form-group">
                    <label for="unidad_medida">Unidad de Medida:</label>
                    <select name="unidad_medida" id="unidad_medida" required>
                        <option value="">Seleccione...</option>
                        <option value="Litro(s)" <?= $insumo->unidad_medida == "Litro(s)" ? "selected" : "" ?>>Litro(s)</option>
                        <option value="Kilo(s)" <?= $insumo->unidad_medida == "Kilo(s)" ? "selected" : "" ?>>Kilo(s)</option>
                        <option value="Metro(s)" <?= $insumo->unidad_medida == "Metro(s)" ? "selected" : "" ?>>Metro(s)</option>
                        <option value="Unidad(es)" <?= $insumo->unidad_medida == "Unidad(es)" ? "selected" : "" ?>>Unidad(es)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="id_categoria">Categoría:</label>
                    <select name="id_categoria" id="id_categoria" required>
                        <option value="">Seleccione categoría</option>
                        <?php foreach($categorias as $cat): ?>
                            <option value="<?= $cat['id_categoria'] ?>" 
                                    <?= $insumo->id_categoria == $cat['id_categoria'] ? "selected" : "" ?>>
                                <?= htmlspecialchars($cat['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Campo CRÍTICO: Cantidad real en almacén -->
            <div class="form-group">
                <label for="cantidad">Cantidad en Stock (Almacén):</label>
                <input type="number" name="cantidad" id="cantidad" step="0.01" min="0" value="<?= htmlspecialchars($cantidadStock) ?>" required>
                <div class="cantidad-alerta">
                    <img src="../assets/resources/advertencia-de-triangulo.png" alt="Advertencia">
                    <div>
                        <strong>Importante:</strong>
                        <span>Este campo modifica la cantidad real en el almacén (stock_almacen). Use con cuidado para ajustes de inventario.</span>
                    </div>
                </div>
            </div>

            <!-- Campos en línea para stock mínimo y máximo -->
            <div class="inline-fields">
                <div class="form-group">
                    <label for="stock_minimo">Stock Mínimo:</label>
                    <input type="number" name="stock_minimo" id="stock_minimo" min="1" value="<?= htmlspecialchars($insumo->stock_minimo) ?>" required>
                    <div class="stock-info">Alerta cuando el stock esté en o por debajo de este valor</div>
                </div>
                
                <div class="form-group">
                    <label for="stock_maximo">Stock Máximo:</label>
                    <input type="number" name="stock_maximo" id="stock_maximo" min="5" value="<?= htmlspecialchars($insumo->stock_maximo) ?>" required>
                    <div class="stock-info">Límite máximo recomendado para este insumo</div>
                </div>
            </div>

            <!-- Muestra la imagen actual del insumo -->
            <div class="form-group">
                <label>Imagen Actual:</label>
                <div class="current-image">
                    <?php if($insumo->imagen): ?>
                        <img src="../assets/imagenes/insumos/<?= $insumo->imagen ?>" alt="Imagen actual">
                        <div class="image-name"><?= $insumo->imagen ?></div>
                    <?php else: ?>
                        <div class="no-image">Sin imagen</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Campo para subir nueva imagen (opcional) -->
            <div class="form-group">
                <label for="imagen">Nueva Imagen (opcional):</label>
                <input type="file" name="imagen" id="imagen" accept="image/*">
                <div class="stock-info">Dejar vacío para mantener la imagen actual</div>
            </div>

            <!-- Botones de acción -->
            <div class="btn-container">
                <button type="submit" class="btn btn-success">
                    <img src="../assets/resources/disco2.png" alt="Guardar">
                    Actualizar
                </button>
                <a href="listarstock.php" class="btn btn-primary">
                    <img src="../assets/resources/volver2.png" alt="Volver">
                    Regresar
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('stockForm');
    const stockMinimo = document.getElementById('stock_minimo');
    const stockMaximo = document.getElementById('stock_maximo');
    const cantidadInput = document.getElementById('cantidad');

    // Previene números negativos en todos los campos numéricos
    document.querySelectorAll('input[type="number"]').forEach(input => {
        input.addEventListener('keydown', function(e) {
            if (e.key === '-' || e.key === 'Subtract') {
                e.preventDefault();
            }
        });
    });

    // Validación del formulario
    form.addEventListener('submit', function(e) {
        const min = parseInt(stockMinimo.value);
        const max = parseInt(stockMaximo.value);
        const cantidad = parseFloat(cantidadInput.value);

        // Validar stock máximo > stock mínimo
        if (max <= min) {
            e.preventDefault();
            alert('Error: El stock máximo debe ser mayor que el stock mínimo.');
            stockMaximo.focus();
            return;
        }

        // Validar cantidad no negativa
        if (isNaN(cantidad) || cantidad < 0) {
            e.preventDefault();
            alert('La cantidad no puede ser negativa.');
            cantidadInput.focus();
            return;
        }

        // Confirmación
        if (!confirm('¿Está seguro de actualizar este insumo?')) {
            e.preventDefault();
        }
    });

    // Auto-ocultar mensajes después de 5 segundos
    setTimeout(function() {
        const alert = document.querySelector('.alert');
        if (alert) {
            alert.style.display = 'none';
        }
    }, 5000);
});
</script>

<?php include_once "../pie.php"; ?>
</body>
</html>