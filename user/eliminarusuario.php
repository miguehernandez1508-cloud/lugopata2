<?php
// /user/eliminarusuario.php
session_start();
require_once __DIR__ . "/gestorsesion.php";
require_once __DIR__ . "/usuario.php";
require_once __DIR__ . "/auditoria.php";
require_once "../conex.php";

GestorSesiones::iniciar();

// Verificar permisos
$nivel_usuario = GestorSesiones::get('nivel');
$niveles_permitidos = ['admin', 'sistemas', 'superadministrador'];

if (!in_array($nivel_usuario, $niveles_permitidos)) {
    header("Location: /lugopata/dashboard.php");
    exit;
}

if (!isset($_GET['id_usuario'])) {
    header("Location: listarusuario.php");
    exit;
}

$id_usuario = (int)$_GET['id_usuario'];

// Obtener información del usuario antes de eliminar
$usuario = Usuario::obtenerPorId($conexion, $id_usuario);

if (!$usuario) {
    header("Location: listarusuario.php?mensaje=Usuario no encontrado");
    exit;
}

// No permitir eliminar al superadmin
if ($usuario->username === 'superadmin') {
    header("Location: listarusuario.php?mensaje=No se puede eliminar al usuario superadministrador");
    exit;
}

// Eliminar el usuario
if (Usuario::eliminar($conexion, $id_usuario)) {
    // Registrar auditoría
    $auditoria = new Auditoria($conexion);
    $auditoria->registrar(
        'Eliminacion de usuario',
        $usuario->username,
        "Usuario eliminado: " . $usuario->username . " (ID: $id_usuario)"
    );
    
    header("Location: listarusuario.php?exito=1");
    exit;
} else {
    header("Location: listarusuario.php?mensaje=Error al eliminar el usuario");
    exit;
}
?>