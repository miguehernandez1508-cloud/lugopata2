<?php
// Incluye archivos necesarios para conexión a BD y gestión de sesiones
require_once "../conex.php";
require_once "../user/gestorsesion.php";

// Valida que el usuario tenga sesión activa
GestorSesiones::iniciar();

// Obtiene la clave y ID de solicitud enviados por POST
$clave = $_POST['clave'] ?? '';
$id_solicitud = $_POST['id_solicitud'] ?? '';

// Valida que se hayan proporcionado ambos parámetros
if (!$clave || !$id_solicitud) {
    echo json_encode(['success' => false]);
    exit;
}

// Buscar al usuario que tiene nivel 'admin' en la base de datos
$sql = "SELECT u.*, t.nombre, t.apellido 
        FROM usuarios u
        JOIN trabajadores t ON u.id_trabajador = t.id_trabajador
        WHERE u.nivel = 'admin' 
        LIMIT 1";

$sentencia = $conexion->prepare($sql);
$sentencia->execute();
$admin = $sentencia->fetch(PDO::FETCH_OBJ);

// Verifica si se encontró un admin y si la contraseña coincide
if ($admin && password_verify($clave, $admin->password)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}