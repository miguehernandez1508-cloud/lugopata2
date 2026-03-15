<?php
session_start();
require_once "../conex.php";

// FORZAR UTF-8 en todas las respuestas
header('Content-Type: application/json; charset=utf-8');

// Configurar conexión para usar UTF-8
if (isset($conexion) && $conexion instanceof PDO) {
    $conexion->exec("SET NAMES 'utf8'");
    $conexion->exec("SET CHARACTER SET utf8");
}

try {
    if (!isset($conexion)) {
        throw new Exception("No se pudo establecer conexion con la base de datos");
    }
    
    $accion = $_GET['accion'] ?? '';
    
    if ($accion === 'categorias') {
        $sql = "SELECT id_categoria, nombre FROM categorias_insumo ORDER BY nombre";
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode($categorias, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    if ($accion === 'buscar') {
        $termino = $_GET['termino'] ?? '';
        $categoria = $_GET['categoria'] ?? '';
        $pagina = intval($_GET['pagina'] ?? 1);
        $porPagina = 10;
        
        if ($pagina < 1) $pagina = 1;
        
        $offset = ($pagina - 1) * $porPagina;
        
        // CONSULTA MODIFICADA - Incluyendo el campo 'imagen' que se necesita
        $sql = "SELECT SQL_CALC_FOUND_ROWS 
                    i.id_insumo,
                    i.nombre,
                    i.descripcion,
                    i.unidad_medida,
                    IFNULL(s.cantidad, i.cantidad) as cantidad,
                    i.stock_minimo,
                    i.stock_maximo,
                    i.imagen,  -- ¡IMPORTANTE! Este campo debe estar incluido
                    c.nombre as categoria_nombre,
                    c.id_categoria
                FROM insumos i
                LEFT JOIN categorias_insumo c ON i.id_categoria = c.id_categoria
                LEFT JOIN stock_almacen s ON i.id_insumo = s.id_insumo
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($termino)) {
            $sql .= " AND (i.nombre LIKE ? OR i.id_insumo LIKE ? OR i.descripcion LIKE ?)";
            $likeTermino = "%$termino%";
            $params[] = $likeTermino;
            $params[] = $likeTermino;
            $params[] = $likeTermino;
        }
        
        if (!empty($categoria) && is_numeric($categoria)) {
            $sql .= " AND i.id_categoria = ?";
            $params[] = $categoria;
        }
        
        $sql .= " ORDER BY i.nombre ASC LIMIT " . intval($porPagina) . " OFFSET " . intval($offset);
        
        // Depuración: Ver la consulta SQL (solo para desarrollo)
        // error_log("Consulta SQL: " . $sql);
        // error_log("Parámetros: " . json_encode($params));
        
        $stmt = $conexion->prepare($sql);
        
        if (!empty($params)) {
            $stmt->execute($params);
        } else {
            $stmt->execute();
        }
        
        $insumos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Depuración: Ver resultados obtenidos
        // error_log("Resultados encontrados: " . count($insumos));
        
        // Obtener total de resultados
        $stmtTotal = $conexion->query("SELECT FOUND_ROWS() as total");
        $total = $stmtTotal->fetch(PDO::FETCH_ASSOC)['total'];
        
        $desde = $offset;
        $hasta = min($offset + $porPagina, $total);
        
        echo json_encode([
            'success' => true,
            'insumos' => $insumos,
            'total' => $total,
            'pagina' => $pagina,
            'porPagina' => $porPagina,
            'desde' => $desde,
            'hasta' => $hasta
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    echo json_encode([
        'success' => false,
        'error' => 'Accion no valida'
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    error_log("Error en buscar_insumos_ajax.php: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'error' => 'Error en el servidor',
        'debug' => $e->getMessage()  // Solo para desarrollo
    ], JSON_UNESCAPED_UNICODE);
}
?>