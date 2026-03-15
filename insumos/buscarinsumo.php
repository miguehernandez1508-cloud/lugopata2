<?php
// Incluye el archivo de conexión a la base de datos
require_once "../conex.php";

// Obtiene el parámetro 'query' de la URL o usa cadena vacía por defecto
$query = $_GET['query'] ?? '';
// Agrega comodines para búsqueda parcial con LIKE
$query = "%$query%";

// Prepara y ejecuta consulta para buscar insumos por ID o nombre
$sentencia = $conexion->prepare("SELECT id_insumo, nombre, unidad_medida, imagen FROM insumos WHERE id_insumo LIKE ? OR nombre LIKE ? LIMIT 1");
$sentencia->execute([$query, $query]);
// Obtiene el primer resultado como array asociativo
$insumo = $sentencia->fetch(PDO::FETCH_ASSOC);

// Establece cabecera para respuesta JSON
header('Content-Type: application/json');
// Devuelve los datos del insumo en formato JSON
echo json_encode($insumo);
?>