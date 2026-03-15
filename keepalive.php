<?php
session_start();
require_once "conex.php";

header('Content-Type: application/json');

if (isset($_SESSION['LAST_ACTIVITY'])) {
    // Actualizar la actividad
    $_SESSION['LAST_ACTIVITY'] = time();
    
    // Calcular tiempo restante
    $timeout = 300; // 5 minutos
    $elapsed = time() - $_SESSION['LAST_ACTIVITY'];
    $remaining = max(0, $timeout - $elapsed);
    
    echo json_encode([
        'success' => true,
        'remaining_time' => $remaining
    ]);
} else {
    echo json_encode([
        'success' => false,
        'remaining_time' => 0
    ]);
}
?>