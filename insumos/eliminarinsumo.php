<?php
session_start();
require_once "../conex.php";
require_once "insumo.php";

if (!isset($_GET['id'])) exit("No se especificó el insumo.");

$insumoObj = new Insumo($conexion);

// Obtener insumo antes de eliminar para borrar imagen
$insumo = $insumoObj->obtener($_GET['id']);
if (!$insumo) exit("Insumo no encontrado.");

// Borrar imagen del servidor si existe
if ($insumo->imagen && file_exists("../assets/imagenes/insumos/".$insumo->imagen)) {
    unlink("../assets/imagenes/insumos/".$insumo->imagen);
}

// Eliminar registro de la base de datos
if ($insumoObj->eliminar($insumo->id_insumo)) {
    header("Location: listarInsumos.php?mensaje=Insumo eliminado correctamente");
} else {
    exit("Error al eliminar el insumo.");
}
