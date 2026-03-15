<?php
session_start();
require_once "../conex.php";
require_once "insumo.php";
require_once "categoria.php";
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

// Obtener categorías para mostrar (solo lectura)
$categoriaObj = new Categoria($conexion);
$categorias = $categoriaObj->obtenerTodas();

$mensaje = "";
$exito = false;

// Procesa la actualización de la cantidad
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cantidad = $_POST['cantidad'];

    // Solo actualizar la cantidad en la tabla insumos
    $sql = "UPDATE insumos SET cantidad = ? WHERE id_insumo = ?";
    $sentencia = $conexion->prepare($sql);
    
    if ($sentencia->execute([$cantidad, $insumo->id_insumo])) {
        $mensaje = "Cantidad actualizada correctamente.";
        $exito = true;
        $insumo = $insumoObj->obtener($insumo->id_insumo); // refrescar datos
    } else {
        $mensaje = "Error al actualizar la cantidad.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Editar Cantidad de Insumo</title>
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

        /* NOTA INFORMATIVA */
        .readonly-note {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            font-size: 14px;
            color: #856404;
            text-align: center;
            font-weight: 500;
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
        .form-group input[readonly],
        .form-group select[disabled],
        .form-group textarea[readonly] {
            background-color: #f8f9fa;
            border-color: #dee2e6;
            color: #6c757d;
            cursor: not-allowed;
        }

        /* CAMPO CANTIDAD DESTACADO */
        .cantidad-field {
            background-color: #fff3cd !important;
            border-color: #ffc107 !important;
            font-weight: bold;
            font-size: 16px;
        }

        .cantidad-field:focus {
            border-color: #ffc107 !important;
            box-shadow: 0 0 0 3px rgba(255, 193, 7, 0.3) !important;
        }

        /* CAMPOS EN LÍNEA */
        .inline-fields {
            display: flex;
            gap: 15px;
        }

        .inline-fields .form-group {
            flex: 1;
        }

        /* INFORMACIÓN DE STOCK */
        .stock-info {
            margin-top: 6px;
            font-size: 12px;
            color: #6b7280;
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
            max-width: 150px;
            max-height: 150px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .no-image {
            color: #999;
            font-style: italic;
            padding: 20px;
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

        /* TEXTAREA */
        textarea {
            resize: vertical;
            min-height: 80px;
            font-family: inherit;
        }

        /* INPUT NUMBER - QUITAR FLECHAS */
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

            .readonly-note {
                font-size: 13px;
                padding: 12px;
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
            <img src="../assets/resources/inventario.png" alt="Inventario">
            ACTUALIZAR CANTIDAD DE INSUMO
        </h1>
        <p>Modifique solo el campo de cantidad</p>
    </div>

    <!-- TARJETA DEL FORMULARIO -->
    <div class="form-card">
        
        <!-- Nota informativa sobre campos editables -->
        <div class="readonly-note">
            <strong>NOTA:</strong> Solo el campo "Cantidad" es editable. Los demás campos son de solo lectura.
        </div>
        
        <?php if($mensaje): ?>
            <div class="alert <?= $exito ? 'alert-success' : 'alert-error' ?>">
                <?= htmlspecialchars($mensaje) ?>
            </div>
        <?php endif; ?>
        
        <!-- Formulario para editar solo la cantidad del insumo -->
        <form method="post" action="" id="insumoForm">
            <!-- Campo ID del insumo (solo lectura) -->
            <div class="form-group">
                <label for="id_insumo">ID del Insumo:</label>
                <input type="text" id="id_insumo" value="<?= htmlspecialchars($insumo->id_insumo) ?>" readonly>
            </div>

            <!-- Campo nombre del insumo (solo lectura) -->
            <div class="form-group">
                <label for="nombre">Nombre del Insumo:</label>
                <input type="text" id="nombre" value="<?= htmlspecialchars($insumo->nombre) ?>" readonly>
            </div>

            <!-- Campo descripción (solo lectura) -->
            <div class="form-group">
                <label for="descripcion">Descripción:</label>
                <textarea id="descripcion" rows="3" readonly><?= htmlspecialchars($insumo->descripcion) ?></textarea>
            </div>

            <!-- Campos en línea para unidad de medida y categoría (solo lectura) -->
            <div class="inline-fields">
                <div class="form-group">
                    <label for="unidad_medida">Unidad de Medida:</label>
                    <select id="unidad_medida" disabled>
                        <option value="">Seleccione...</option>
                        <option value="Litro(s)" <?= $insumo->unidad_medida == "Litro(s)" ? "selected" : "" ?>>Litro(s)</option>
                        <option value="Kilo(s)" <?= $insumo->unidad_medida == "Kilo(s)" ? "selected" : "" ?>>Kilo(s)</option>
                        <option value="Metro(s)" <?= $insumo->unidad_medida == "Metro(s)" ? "selected" : "" ?>>Metro(s)</option>
                        <option value="Unidad(es)" <?= $insumo->unidad_medida == "Unidad(es)" ? "selected" : "" ?>>Unidad(es)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="id_categoria">Categoría:</label>
                    <select id="id_categoria" disabled>
                        <option value="">Seleccione categoría...</option>
                        <?php foreach($categorias as $cat): ?>
                            <option value="<?= $cat['id_categoria'] ?>" 
                                    <?= $insumo->id_categoria == $cat['id_categoria'] ? "selected" : "" ?>>
                                <?= htmlspecialchars($cat['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Campos en línea para stock mínimo y máximo (solo lectura) -->
            <div class="inline-fields">
                <div class="form-group">
                    <label for="stock_minimo">Stock Mínimo:</label>
                    <input type="number" id="stock_minimo" value="<?= htmlspecialchars($insumo->stock_minimo) ?>" readonly>
                    <div class="stock-info">Alerta cuando el stock esté en o por debajo de este valor</div>
                </div>
                
                <div class="form-group">
                    <label for="stock_maximo">Stock Máximo:</label>
                    <input type="number" id="stock_maximo" value="<?= htmlspecialchars($insumo->stock_maximo) ?>" readonly>
                    <div class="stock-info">Límite máximo recomendado para este insumo</div>
                </div>
            </div>

            <!-- Único campo editable: cantidad actual del insumo -->
            <div class="form-group">
                <label for="cantidad">Cantidad (Editable):</label>
                <input type="number" name="cantidad" id="cantidad" class="cantidad-field" step="0.01" min="0" value="<?= htmlspecialchars($insumo->cantidad) ?>" required>
                <div class="stock-info">Este es el único campo que puede modificar</div>
            </div>

            <!-- Muestra la imagen actual del insumo (solo lectura) -->
            <div class="form-group">
                <label>Imagen Actual:</label>
                <div class="current-image">
                    <?php if($insumo->imagen): ?>
                        <img src="../assets/imagenes/insumos/<?= $insumo->imagen ?>" alt="Imagen actual">
                        <div style="margin-top: 8px; font-size: 12px; color: #666;">
                            <?= $insumo->imagen ?>
                        </div>
                    <?php else: ?>
                        <div class="no-image">Sin imagen</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="btn-container">
                <button type="submit" class="btn btn-success">
                    <img src="../assets/resources/disco2.png" alt="Guardar">
                    Actualizar Cantidad
                </button>
                <a href="listarinsumos.php" class="btn btn-primary">
                    <img src="../assets/resources/volver2.png" alt="Volver">
                    Regresar
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Previene la entrada de números negativos
    const cantidadInput = document.getElementById('cantidad');
    
    cantidadInput.addEventListener('keydown', function(e) {
        if (e.key === '-' || e.key === 'Subtract') {
            e.preventDefault();
        }
    });

    // Prevenir flechas en campo numérico
    cantidadInput.addEventListener('keydown', function(e) {
        if (['ArrowUp', 'ArrowDown'].includes(e.key)) {
            e.preventDefault();
        }
    });

    // Validación del formulario
    const form = document.getElementById('insumoForm');
    form.addEventListener('submit', function(e) {
        const cantidad = parseFloat(cantidadInput.value);
        
        if (isNaN(cantidad) || cantidad < 0) {
            e.preventDefault();
            alert('La cantidad debe ser un número mayor o igual a 0.');
            cantidadInput.focus();
        }
    });

    // Auto-ocultar mensaje después de 5 segundos
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