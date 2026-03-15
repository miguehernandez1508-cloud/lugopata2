<?php
session_start();
require_once "../conex.php";

header('Content-Type: application/json');

try {
    // 1. Verificar conexión
    if (!isset($conexion)) {
        throw new Exception("ERROR: Variable \$conexion no esta definida");
    }
    
    // 2. Probar consulta directa
    $sql = "SELECT id_categoria, nombre FROM categorias_insumo ORDER BY nombre";
    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 3. Verificar resultados
    $resultado = [
        'success' => true,
        'diagnostico' => 'Prueba de conexion y consulta',
        'conexion' => 'OK - Variable $conexion existe',
        'consulta_sql' => $sql,
        'total_categorias' => count($categorias),
        'categorias' => $categorias,
        'nota' => 'Si total_categorias es 0, la tabla esta vacia. Si ves error, hay problema de conexion.'
    ];
    
    // 4. Ver estructura de la tabla
    $sql_estructura = "DESCRIBE categorias_insumo";
    $stmt_estructura = $conexion->prepare($sql_estructura);
    $stmt_estructura->execute();
    $estructura = $stmt_estructura->fetchAll(PDO::FETCH_ASSOC);
    
    $resultado['estructura_tabla'] = $estructura;
    
    echo json_encode($resultado);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Error en diagnostico: ' . $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
?>