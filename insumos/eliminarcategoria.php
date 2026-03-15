<?php
session_start();
require_once "../conex.php";
require_once "categoria.php";

// Verificar que se recibió un ID
$id_categoria = $_GET['id'] ?? null;
if (!$id_categoria) {
    header("Location: formcrearcategoria.php");
    exit;
}

try {
    // Verificar si la categoría tiene insumos asociados
    $sql_check = "SELECT COUNT(*) as total FROM insumos WHERE id_categoria = ?";
    $sentencia_check = $conexion->prepare($sql_check);
    $sentencia_check->execute([$id_categoria]);
    $resultado = $sentencia_check->fetch(PDO::FETCH_ASSOC);

    if ($resultado['total'] > 0) {
        // Tiene insumos asociados, no se puede eliminar
        $_SESSION['mensaje'] = "No se puede eliminar la categoría porque tiene insumos asociados.";
        $_SESSION['tipo_mensaje'] = "error";
    } else {
        // Eliminar la categoría
        $sql_delete = "DELETE FROM categorias_insumo WHERE id_categoria = ?";
        $sentencia_delete = $conexion->prepare($sql_delete);
        $resultado_delete = $sentencia_delete->execute([$id_categoria]);

        if ($resultado_delete) {
            $_SESSION['mensaje'] = "Categoría eliminada correctamente.";
            $_SESSION['tipo_mensaje'] = "exito";
        } else {
            $_SESSION['mensaje'] = "Error al eliminar la categoría.";
            $_SESSION['tipo_mensaje'] = "error";
        }
    }
} catch (PDOException $e) {
    $_SESSION['mensaje'] = "Error en la base de datos: " . $e->getMessage();
    $_SESSION['tipo_mensaje'] = "error";
}

// Redireccionar de vuelta al formulario de categorías
header("Location: formcrearcategoria.php");
exit;
?>