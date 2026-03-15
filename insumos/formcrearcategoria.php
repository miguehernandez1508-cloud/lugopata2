<?php
session_start();
include_once "../encabezado.php";
require_once "../conex.php";
require_once "categoria.php";

$mensaje = "";
$mensaje_tipo = "";

// Configuración de paginación
$registrosPorPagina = 6;
$pagina = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$inicio = ($pagina - 1) * $registrosPorPagina;

$categoriaObj = new Categoria($conexion);

// Procesar eliminación si se solicita
if (isset($_GET['eliminar'])) {
    $id_eliminar = $_GET['eliminar'];
    
    // Verificar si la categoría tiene insumos asociados
    if ($categoriaObj->tieneInsumos($id_eliminar)) {
        $mensaje = "No se puede eliminar la categoría porque tiene insumos asociados.";
        $mensaje_tipo = "error";
    } else {
        if ($categoriaObj->eliminar($id_eliminar)) {
            $mensaje = "Categoría eliminada correctamente.";
            $mensaje_tipo = "exito";
        } else {
            $mensaje = "Error al eliminar la categoría.";
            $mensaje_tipo = "error";
        }
    }
}

// Obtener total de categorías para la paginación
$totalCategorias = $categoriaObj->contarTotal();
$totalPaginas = ceil($totalCategorias / $registrosPorPagina);

// Obtener categorías paginadas y ordenadas por ID descendente
$categorias = $categoriaObj->obtenerTodasPaginadas($inicio, $registrosPorPagina);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $abreviatura = strtoupper($_POST['abreviatura']); // Solo abreviatura en mayúsculas
    
    $categoria = new Categoria($conexion, null, $nombre, $descripcion, $abreviatura);
    if ($categoria->crear()) {
        $mensaje = "Categoría creada correctamente.";
        $mensaje_tipo = "exito";
        // Recargar la lista de categorías después de crear
        $totalCategorias = $categoriaObj->contarTotal();
        $totalPaginas = ceil($totalCategorias / $registrosPorPagina);
        $categorias = $categoriaObj->obtenerTodasPaginadas($inicio, $registrosPorPagina);
    } else {
        $mensaje = "Error al crear categoría.";
        $mensaje_tipo = "error";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Agregar Categoría</title>
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
            margin-bottom: 25px;
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

        .btn-warning {
            background-color: #ffc107 !important;
            color: #212529 !important;
        }
        .btn-warning:hover {
            background-color: #e0a800 !important;
        }

        .btn-danger {
            background-color: #dc3545 !important;
            color: white;
        }
        .btn-danger:hover {
            background-color: #bb2d3b !important;
        }

        .btn:active {
            transform: translateY(0);
        }

        .btn img {
            width: 18px;
            height: 18px;
            filter: brightness(0) invert(1);
        }

        .btn-warning img {
            filter: brightness(0) !important;
        }

        /* TABLA DE CATEGORÍAS */
        .table-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            border: 2px solid #ccc;
            width: 100%;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .table-card h2 {
            color: #333;
            font-size: 1.5rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 15px;
        }

        .table-card h2 img {
            width: 32px;
            height: 32px;
        }

        .table-container {
            width: 100%;
            overflow-x: auto;
            border: 2px solid #dee2e6;
            border-radius: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }

        th, td {
            border: 1px solid #dee2e6;
            padding: 12px;
            text-align: center;
            background-color: #fff;
            vertical-align: middle;
            font-size: 14px;
        }

        th {
            background-color: #cfe2ff;
            font-weight: bold;
            color: #333;
            position: sticky;
            top: 0;
            white-space: nowrap;
        }

        td {
            word-break: break-word;
        }

        /* Columna descripción más ancha */
        td:nth-child(4) {
            max-width: 300px;
            text-align: left;
        }

        tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        tbody tr:hover {
            background-color: #e9ecef;
        }

        /* Acciones en tabla */
        .acciones {
            display: flex;
            gap: 6px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-accion {
            padding: 6px 12px;
            font-size: 12px;
            min-width: 70px;
        }

        /* PAGINACIÓN */
        .paginacion-container {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            text-align: center;
        }

        .paginacion-flex {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .paginacion-info {
            color: #666;
            font-size: 14px;
            margin: 0 10px;
        }

        .btn-paginacion {
            padding: 8px 16px;
            background: #f8f9fa;
            color: #495057;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            font-size: 13px;
            border: 1px solid #dee2e6;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-paginacion:hover:not(.disabled) {
            background: #e9ecef;
            border-color: #adb5bd;
        }

        .btn-paginacion.disabled {
            background: #f8f9fa;
            color: #adb5bd;
            pointer-events: none;
            border-color: #e9ecef;
        }

        /* Mensaje sin datos */
        .mensaje-sin-datos {
            text-align: center;
            padding: 40px 20px;
            color: #666;
            background: #f8f9fa;
            border-radius: 10px;
            border: 1px dashed #dee2e6;
            margin: 20px 0;
        }

        /* MEDIA QUERIES PARA MÓVILES */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }

            .form-card, .table-card {
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

            .acciones {
                flex-direction: column;
            }

            .btn-accion {
                width: 100%;
            }

            .paginacion-flex {
                flex-direction: column;
                width: 100%;
            }

            .btn-paginacion {
                width: 100%;
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .title-card, .form-card, .table-card {
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

            th, td {
                padding: 8px;
                font-size: 13px;
            }
        }

        @media (min-width: 1200px) {
            .form-wrapper {
                max-width: 1400px;
            }
        }
    </style>
</head>
<body>
<div class="form-wrapper">
    <!-- TARJETA DE TÍTULO -->
    <div class="title-card">
        <h1>
            <img src="../assets/resources/cdepartamento.png" alt="Categoría">
            REGISTRAR NUEVA CATEGORÍA
        </h1>
        <p>Complete todos los campos requeridos</p>
    </div>

    <!-- TARJETA DEL FORMULARIO -->
    <div class="form-card">
        
        <?php if($mensaje): ?>
            <div class="alert alert-<?= $mensaje_tipo ?>">
                <?= $mensaje_tipo === 'error' ? '⚠️' : '✓' ?> <?= $mensaje ?>
            </div>
        <?php endif; ?>

        <form method="post" action="">
            <div class="form-group">
                <label for="nombre">Nombre de la categoría: *</label>
                <input type="text" name="nombre" id="nombre" required placeholder="Ej: Ferretería, Materiales Eléctricos">
            </div>

            <div class="form-group">
                <label for="descripcion">Descripción: *</label>
                <textarea name="descripcion" id="descripcion" required placeholder="Describe la categoría..."></textarea>
            </div>

            <div class="form-group">
                <label for="abreviatura">Abreviatura: *</label>
                <input type="text" name="abreviatura" id="abreviatura" maxlength="10" required 
                       placeholder="Ej: FE, HR, MT">
                <small style="color: #666; display: block; margin-top: 5px;">Se convertirá automáticamente a mayúsculas</small>
            </div>

            <div class="btn-container">
                <button type="submit" class="btn btn-success">
                    <img src="../assets/resources/disco2.png" alt="">
                    Guardar
                </button>
                <a href="formcrearinsumo.php" class="btn btn-primary">
                    <img src="../assets/resources/volver2.png" alt="">
                    Regresar
                </a>
            </div>
        </form>
    </div>

    <!-- TARJETA DE TABLA DE CATEGORÍAS -->
    <div class="table-card">
        <h2>
            <img src="../assets/resources/linsumos.png" alt="Lista">
            LISTA DE CATEGORÍAS
        </h2>

        <?php if (empty($categorias)): ?>
            <div class="mensaje-sin-datos">
                <h3>No hay categorías registradas</h3>
                <p>Comienza creando una nueva categoría usando el formulario de arriba.</p>
            </div>
        <?php else: ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Abreviatura</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categorias as $cat): ?>
                        <tr>
                            <td><strong>#<?= $cat['id_categoria'] ?></strong></td>
                            <td><?= htmlspecialchars($cat['abreviatura']) ?></td>
                            <td><?= htmlspecialchars($cat['nombre']) ?></td>
                            <td><?= htmlspecialchars($cat['descripcion']) ?></td>
                            <td>
                                <div class="acciones">
                                    <a href="formeditarCategoria.php?id=<?= $cat['id_categoria'] ?>" class="btn btn-warning btn-accion">
                                        <img src="../assets/resources/editarU.png" alt="Editar" style="width: 14px; height: 14px;">
                                        Editar
                                    </a>
                                    <a href="?eliminar=<?= $cat['id_categoria'] ?>&pagina=<?= $pagina ?>" 
                                       class="btn btn-danger btn-accion"
                                       onclick="return confirm('¿Está seguro de eliminar esta categoría?')">
                                        <img src="../assets/resources/eliminar2.png" alt="Eliminar" style="width: 14px; height: 14px; filter: brightness(0) invert(1);">
                                        Eliminar
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- PAGINACIÓN -->
            <?php if ($totalPaginas > 1): ?>
            <div class="paginacion-container">
                <div class="paginacion-flex">
                    <?php if ($pagina > 1): ?>
                        <a href="?pagina=<?= $pagina - 1 ?>" class="btn-paginacion">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="15 18 9 12 15 6"></polyline>
                            </svg>
                            Anterior
                        </a>
                    <?php else: ?>
                        <span class="btn-paginacion disabled">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="15 18 9 12 15 6"></polyline>
                            </svg>
                            Anterior
                        </span>
                    <?php endif; ?>
                    
                    <span class="paginacion-info">
                        Página <?= $pagina ?> de <?= $totalPaginas ?>
                    </span>
                    
                    <?php if ($pagina < $totalPaginas): ?>
                        <a href="?pagina=<?= $pagina + 1 ?>" class="btn-paginacion">
                            Siguiente
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </a>
                    <?php else: ?>
                        <span class="btn-paginacion disabled">
                            Siguiente
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </span>
                    <?php endif; ?>
                </div>
                <div class="paginacion-info" style="margin-top: 10px; font-size: 12px; color: #888;">
                    Mostrando <?= (($pagina - 1) * $registrosPorPagina) + 1 ?> - <?= min($pagina * $registrosPorPagina, $totalCategorias) ?> de <?= $totalCategorias ?> categorías
                </div>
            </div>
            <?php endif; ?>
        <?php endif; ?>
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