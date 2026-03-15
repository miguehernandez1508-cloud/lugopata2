<?php
session_start();
include_once "../encabezado.php";
require_once __DIR__ . "/../conex.php";
require_once __DIR__ . "/insumo.php";
require_once __DIR__ . "/categoria.php";

$mensaje = "";

$categoriaObj = new Categoria($conexion);
$categorias = $categoriaObj->obtenerTodas();

$codigo_numerico = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $abreviatura_categoria = $_POST['abreviatura_categoria'];
    $codigo_numerico = $_POST['codigo_numerico'];
    $id_insumo = $abreviatura_categoria . '-' . str_pad($codigo_numerico, 3, '0', STR_PAD_LEFT);
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $unidad_medida = $_POST['unidad_medida'];
    $id_categoria = $_POST['id_categoria'];
    $stock_minimo = $_POST['stock_minimo'];
    $stock_maximo = $_POST['stock_maximo'];
    $imagenNombre = NULL;

    $existe_codigo = $conexion->prepare("SELECT COUNT(*) FROM insumos WHERE codigo_numerico = ?");
    $existe_codigo->execute([$codigo_numerico]);
    if ($existe_codigo->fetchColumn() > 0) {
        $mensaje = "Error: El código numérico ya existe. Por favor use otro número.";
    } else {
        if ($stock_maximo <= $stock_minimo) {
            $mensaje = "Error: El stock máximo debe ser mayor que el stock mínimo.";
        } else {
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
                $carpetaDestino = "../assets/imagenes/insumos/";
                if (!is_dir($carpetaDestino)) {
                    mkdir($carpetaDestino, 0777, true);
                }
                $imagenNombre = time() . "_" . $_FILES['imagen']['name'];
                move_uploaded_file($_FILES['imagen']['tmp_name'], $carpetaDestino . $imagenNombre);
            }

            // CAMBIO 1: Inicializar cantidad en 1 en lugar de 0
            $insumo = new Insumo($conexion, $id_insumo, $nombre, $descripcion, $unidad_medida, 1, $stock_minimo, $stock_maximo, $imagenNombre, $id_categoria);
            $insumo->abreviatura_categoria = $abreviatura_categoria;
            $insumo->codigo_numerico = $codigo_numerico;

            if ($insumo->crear()) {
                $mensaje = "Insumo registrado correctamente. ID: " . $id_insumo;
                $codigo_numerico = "";
                $abreviatura_categoria = "";
                $id_categoria = "";
                $nombre = "";
                $descripcion = "";
                $unidad_medida = "";
                $stock_minimo = "";
                $stock_maximo = "";
            } else {
                $mensaje = "Error al registrar insumo.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Agregar Insumo</title>
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
            max-width: 700px;
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
            font-size: clamp(1.2rem, 4vw, 1.8rem);
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

        .form-group input[readonly] {
            background-color: #f3f4f6;
            color: #6b7280;
        }

        textarea {
            resize: vertical;
            min-height: 80px;
            max-height: 150px;
        }

        /* INFO DE STOCK */
        .stock-info {
            margin-top: 6px;
            font-size: 12px;
            color: #6b7280;
            font-style: italic;
        }

        /* CAMPOS EN LÍNEA */
        .inline-fields {
            display: flex;
            gap: 15px;
        }

        .inline-fields .form-group {
            flex: 1;
        }

        /* INPUT FILE */
        input[type="file"] {
            padding: 10px;
            border: 2px dashed #e0e0e0;
            background-color: #f9fafb;
            cursor: pointer;
        }

        input[type="file"]:hover {
            border-color: #0d6efd;
            background-color: #eff6ff;
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
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 20px;
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
            min-width: 140px;
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

        .btn-warning {
            background-color: #0d6efd !important;
            color: white;
        }

        .btn-warning:hover {
            background-color: #d97706;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
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
            width: 20px;
            height: 20px;
            filter: brightness(0) invert(1);
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

            input[type="file"] {
                font-size: 14px;
            }
        }

        @media (min-width: 1200px) {
            .form-wrapper {
                max-width: 650px;
            }
        }
    </style>
</head>
<body>
<div class="form-wrapper">
    <!-- TARJETA DE TÍTULO -->
    <div class="title-card">
        <h1>
            <img src="../assets/resources/cinsumo.png" alt="Insumo">
            REGISTRAR NUEVO INSUMO
        </h1>
        <p>Complete todos los campos requeridos</p>
    </div>

    <!-- TARJETA DEL FORMULARIO -->
    <div class="form-card">
        
        <?php if($mensaje) { ?>
            <div class="<?= strpos($mensaje, 'Error') !== false ? 'alert alert-error' : 'alert alert-success' ?>">
                <?= $mensaje; ?>
            </div>
        <?php } ?>
        
        <form method="post" action="" enctype="multipart/form-data" id="formInsumo">
            <div class="form-group">
                <label for="id_categoria">Categoría: *</label>
                <select name="id_categoria" id="id_categoria" required onchange="actualizarAbreviatura();">
                    <option value="">Seleccione categoría...</option>
                    <?php foreach($categorias as $cat): ?>
                        <option value="<?= $cat['id_categoria'] ?>" 
                                data-abreviatura="<?= htmlspecialchars($cat['abreviatura'] ?? '') ?>">
                            <?= htmlspecialchars($cat['nombre']) ?> (<?= htmlspecialchars($cat['abreviatura'] ?? '') ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="inline-fields">
                <div class="form-group">
                    <label for="abreviatura_categoria">Abreviatura:</label>
                    <input type="text" name="abreviatura_categoria" id="abreviatura_categoria" readonly 
                           value="<?= htmlspecialchars($abreviatura_categoria ?? '') ?>" placeholder="Auto">
                </div>
                
                <div class="form-group">
                    <label for="codigo_numerico">Código Numérico: *</label>
                    <input type="number" name="codigo_numerico" id="codigo_numerico" min="1" 
                           value="<?= htmlspecialchars($codigo_numerico) ?>" required 
                           oninput="actualizarIDCompleto()" onkeyup="actualizarIDCompleto()" placeholder="Ej: 001">
                </div>
            </div>

            <div class="form-group">
                <label for="id_insumo_completo">ID Completo (automático):</label>
                <input type="text" id="id_insumo_completo" readonly placeholder="Ej: FE-001">
            </div>

            <div class="form-group">
                <label for="nombre">Nombre del Insumo: *</label>
                <input type="text" name="nombre" id="nombre" 
                       value="<?= htmlspecialchars($nombre ?? '') ?>" required placeholder="Nombre del insumo">
            </div>

            <div class="form-group">
                <label for="descripcion">Descripción:</label>
                    <textarea name="descripcion" id="descripcion" rows="3"><?= htmlspecialchars($descripcion ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="unidad_medida">Unidad de Medida:</label>
                    <select name="unidad_medida" id="unidad_medida" required>
                        <option value="">Seleccione...</option>
                        <option value="Litro(s)" <?= ($unidad_medida ?? '') == 'Litro(s)' ? 'selected' : '' ?>>Litro(s)</option>
                        <option value="Kilo(s)" <?= ($unidad_medida ?? '') == 'Kilo(s)' ? 'selected' : '' ?>>Kilo(s)</option>
                        <option value="Metro(s)" <?= ($unidad_medida ?? '') == 'Metro(s)' ? 'selected' : '' ?>>Metro(s)</option>
                        <option value="Unidad(es)" <?= ($unidad_medida ?? '') == 'Unidad(es)' ? 'selected' : '' ?>>Unidad(es)</option>
                    </select>
                </div>

                <!-- Campos en línea para stock mínimo y máximo -->
                <div class="inline-fields">
                    <div class="form-group">
                        <label for="stock_minimo">Stock Mínimo:</label>
                        <input type="number" name="stock_minimo" id="stock_minimo" min="1" value="<?= htmlspecialchars($stock_minimo ?? '1') ?>" required>
                        <div class="stock-info">Alerta cuando el stock esté en o por debajo de este valor</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="stock_maximo">Stock Máximo:</label>
                        <input type="number" name="stock_maximo" id="stock_maximo" min="5" value="<?= htmlspecialchars($stock_maximo ?? '5') ?>" required>
                        <div class="stock-info">Límite máximo recomendado para este insumo</div>
                    </div>
                </div>

                <!-- Campo para subir imagen del insumo -->
                <div class="form-group">
                    <label for="imagen">Imagen del Insumo:</label>
                    <input type="file" name="imagen" id="imagen" accept="image/*">
                </div>

                <!-- BOTONES CORREGIDOS - IGUAL QUE FORMCREARUSUARIO -->
                <div class="btn-container">
                    <button type="submit" class="btn btn-success">
                        <img src="../assets/resources/disco2.png" alt="Guardar">
                        Guardar
                    </button>
                    <a href="formcrearcategoria.php" class="btn btn-warning">
                        <img src="../assets/resources/agregar1.png" alt="Categoría">
                        Crear Categoría
                    </a>
                    <a href="listarinsumos.php" class="btn btn-primary">
                        <img src="../assets/resources/volver2.png" alt="Ver">
                        Regresar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // CAMBIO 2: Función para actualizar la abreviatura cuando se selecciona una categoría
        function actualizarAbreviatura() {
            const select = document.getElementById('id_categoria');
            const selectedOption = select.options[select.selectedIndex];
            const abreviatura = selectedOption ? selectedOption.getAttribute('data-abreviatura') : '';
            document.getElementById('abreviatura_categoria').value = abreviatura || '';
            actualizarIDCompleto(); // Actualizar ID completo cuando cambia la categoría
        }

        // CAMBIO 3: Función para actualizar el ID completo en tiempo real
        function actualizarIDCompleto() {
            const abreviatura = document.getElementById('abreviatura_categoria').value;
            const codigo = document.getElementById('codigo_numerico').value;
            const idCompletoInput = document.getElementById('id_insumo_completo');
            
            if (abreviatura && codigo) {
                // Formatear el código a 3 dígitos
                const codigoFormateado = String(codigo).padStart(3, '0');
                idCompletoInput.value = abreviatura + '-' + codigoFormateado;
            } else if (abreviatura) {
                idCompletoInput.value = abreviatura + '-';
            } else if (codigo) {
                const codigoFormateado = String(codigo).padStart(3, '0');
                idCompletoInput.value = '?-' + codigoFormateado;
            } else {
                idCompletoInput.value = '';
            }
        }

        // CAMBIO 4: Función para validar que el stock mínimo sea al menos 1
        function validarStock() {
            const stockMinimo = document.getElementById('stock_minimo').value;
            const stockMaximo = document.getElementById('stock_maximo').value;
            
            if (parseInt(stockMinimo) < 1) {
                alert('El stock mínimo no puede ser 0. Debe ser al menos 1.');
                document.getElementById('stock_minimo').focus();
                return false;
            }
            
            if (parseInt(stockMaximo) <= parseInt(stockMinimo)) {
                alert('El stock máximo debe ser mayor que el stock mínimo.');
                document.getElementById('stock_maximo').focus();
                return false;
            }
            
            return true;
        }

        // Previene la entrada de números negativos
        document.querySelectorAll('input[type="number"]').forEach(input => {
            input.addEventListener('keydown', function(e) {
                if (e.key === '-' || e.key === 'Subtract') {
                    e.preventDefault();
                }
            });
            
            // Validar que no se puedan poner valores negativos
            input.addEventListener('change', function() {
                if (this.value < 0) {
                    this.value = 0;
                }
            });
        });

        // Validar el formulario antes de enviar
        document.getElementById('formInsumo').addEventListener('submit', function(e) {
            if (!validarStock()) {
                e.preventDefault();
            }
        });

        // Inicializa las funciones al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            actualizarAbreviatura();
            actualizarIDCompleto();
        });
    </script>
</body>
</html>