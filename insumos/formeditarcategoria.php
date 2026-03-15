<?php
session_start();
include_once "../encabezado.php";
require_once "../conex.php";
require_once "categoria.php";

$mensaje = "";
$categoriaObj = new Categoria($conexion);

// Obtener ID de la categoría a editar
$id_categoria = $_GET['id'] ?? null;
if (!$id_categoria) {
    header("Location: formcrearcategoria.php");
    exit;
}

// Obtener datos de la categoría
$categoria = $categoriaObj->obtenerPorId($id_categoria);
if (!$categoria) {
    echo "<script>alert('Categoría no encontrada'); window.location='formcrearcategoria.php';</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $abreviatura = strtoupper($_POST['abreviatura']);

    // Actualizar categoría
    $sql = "UPDATE categorias_insumo SET nombre = ?, descripcion = ?, abreviatura = ? WHERE id_categoria = ?";
    $sentencia = $conexion->prepare($sql);
    $resultado = $sentencia->execute([$nombre, $descripcion, $abreviatura, $id_categoria]);

    if ($resultado) {
        $mensaje = "Categoría actualizada correctamente.";
        // Recargar datos actualizados
        $categoria = $categoriaObj->obtenerPorId($id_categoria);
    } else {
        $mensaje = "Error al actualizar categoría.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Editar Categoría</title>
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
            max-width: 600px;
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

        /* Solo abreviatura en mayúsculas */
        #abreviatura {
            text-transform: uppercase;
        }

        textarea {
            resize: vertical;
            min-height: 80px;
            max-height: 150px;
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
            min-width: 160px;
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

        /* MEDIA QUERIES PARA MÓVILES */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }

            .form-card {
                padding: 20px;
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
            .title-card, .form-card {
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
        }

        @media (min-width: 1200px) {
            .form-wrapper {
                max-width: 550px;
            }
        }
    </style>
</head>
<body>
<div class="form-wrapper">
    <!-- TARJETA DE TÍTULO -->
    <div class="title-card">
        <h1>
            <img src="../assets/resources/cdepartamento.png" alt="Editar">
            EDITAR CATEGORÍA #<?= $id_categoria ?>
        </h1>
        <p>Modifique los campos que desee actualizar</p>
    </div>

    <!-- TARJETA DEL FORMULARIO -->
    <div class="form-card">
        
        <?php if($mensaje) { ?>
            <div class="<?= strpos($mensaje, 'Error') !== false ? 'alert alert-error' : 'alert alert-success' ?>">
                <?= $mensaje; ?>
            </div>
        <?php } ?>

        <form method="post" action="">
            <div class="form-group">
                <label for="nombre">Nombre de la categoría: *</label>
                <input type="text" name="nombre" id="nombre" required 
                       value="<?= htmlspecialchars($categoria['nombre']) ?>"
                       placeholder="Ej: Ferretería, Materiales Eléctricos">
            </div>

            <div class="form-group">
                <label for="descripcion">Descripción: *</label>
                <textarea name="descripcion" id="descripcion" required 
                          placeholder="Describe la categoría..."><?= htmlspecialchars($categoria['descripcion']) ?></textarea>
            </div>

            <div class="form-group">
                <label for="abreviatura">Abreviatura: *</label>
                <input type="text" name="abreviatura" id="abreviatura" maxlength="10" required 
                       value="<?= htmlspecialchars($categoria['abreviatura']) ?>"
                       placeholder="Ej: FE, HR, MT">
            </div>

            <div class="btn-container">
                <button type="submit" class="btn btn-success">
                    <img src="../assets/resources/disco2.png" alt="">
                    Actualizar
                </button>
                <a href="formcrearcategoria.php" class="btn btn-primary">
                    <img src="../assets/resources/volver2.png" alt="">
                    Regresar
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// Convertir a mayúsculas solo la abreviatura
document.getElementById('abreviatura').addEventListener('input', function(e) {
    e.target.value = e.target.value.toUpperCase();
});

// Auto-ocultar mensaje después de 5 segundos
setTimeout(function() {
    const alert = document.querySelector('.alert');
    if (alert) {
        alert.style.display = 'none';
    }
}, 5000);
</script>
</body>
</html>