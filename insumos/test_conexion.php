<?php
session_start();
require_once "../conex.php"; // Misma ruta que buscar_insumos_ajax.php

header('Content-Type: application/json');

try {
    // Verificar si $conexion existe
    if (!isset($conexion)) {
        echo json_encode([
            'success' => false,
            'error' => 'Variable $conexion no definida'
        ]);
        exit;
    }
    
    // Probar consulta simple
    $sql = "SELECT 
                (SELECT COUNT(*) FROM categorias_insumo) as total_categorias,
                (SELECT COUNT(*) FROM insumos) as total_insumos,
                (SELECT COUNT(*) FROM stock_almacen) as total_stock";
    
    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'conexion' => 'OK',
        'datos' => $result,
        'tablas' => [
            'categorias_insumo' => $result['total_categorias'],
            'insumos' => $result['total_insumos'],
            'stock_almacen' => $result['total_stock']
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Error en test_conexion.php: ' . $e->getMessage()
    ]);
}
?>