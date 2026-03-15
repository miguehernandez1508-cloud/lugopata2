<?php
session_start();
require_once "gestorsesion.php";
require_once "../conex.php"; 

// Iniciar gestión de sesiones
GestorSesiones::iniciar();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_button'])) {
    $nombre = $_POST['username'] ?? '';
    $clave = $_POST['password'] ?? '';
    $captcha_input = $_POST['captcha'] ?? '';

    // 1. PRIMERO validar el CAPTCHA
    if (empty($captcha_input)) {
        GestorSesiones::set("status", 3); // CAPTCHA vacío
        header("Location: Formlogin.php");
        exit;
    }

    // Verificar que el CAPTCHA existe en sesión
    if (empty($_SESSION['captcha'])) {
        GestorSesiones::set("status", 2); // CAPTCHA no generado
        header("Location: Formlogin.php");
        exit;
    }

    // Comparar el CAPTCHA (case-insensitive)
    $user_captcha = strtoupper(trim($captcha_input));
    $session_captcha = strtoupper(trim($_SESSION['captcha']));

    if ($user_captcha !== $session_captcha) {
        GestorSesiones::set("status", 2); // CAPTCHA incorrecto
        // IMPORTANTE: Regenerar CAPTCHA después de fallo
        unset($_SESSION['captcha']);
        header("Location: Formlogin.php");
        exit;
    }

    // 2. LUEGO validar usuario y contraseña
    require_once "login.php";
    $login = new Login($conexion);

    $resultado = $login->validarUsuario($nombre, $clave);
    
    if ($resultado === true) {
        // Login exitoso - limpiar CAPTCHA
        unset($_SESSION['captcha']);
        session_regenerate_id(true);
        header("Location: ../dashboard.php");
        exit;
    } else {
        // Credenciales incorrectas - limpiar CAPTCHA para forzar nuevo
        unset($_SESSION['captcha']);
        header("Location: Formlogin.php");
        exit;
    }
} else {
    header("Location: Formlogin.php");
    exit;
}
?>