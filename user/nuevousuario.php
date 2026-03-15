<?php
session_start();
require_once "auditoria.php";
require_once "usuario.php";
require_once "../conex.php";

if (!isset($_POST["usuario"], $_POST["contrasena"], $_POST["correo"], $_POST["nivel"], $_POST["id_trabajador"])) {
    exit("Faltan datos del formulario");
}

// Validar que las contraseñas coincidan
if ($_POST['contrasena'] !== $_POST['confirmar_contrasena']) {
    header("Location: formcrearusuario.php?mensaje=Las contraseñas no coinciden");
    exit;
}

$contrasena = $_POST['contrasena'] ?? '';
if (!preg_match('/^(?=.*[A-Z])(?=.*[!@#$%^&*(),.?":{}|<>]).{8,}$/', $contrasena)) {
    header("Location: formcrearusuario.php?mensaje=La contraseña no cumple los requisitos: mínimo 8 caracteres, una mayúscula y un carácter especial.");
    exit;
}

// Verificar si el trabajador ya tiene usuario
if (Usuario::trabajadorTieneUsuario($conexion, $_POST["id_trabajador"])) {
    header("Location: formcrearusuario.php?mensaje=Este trabajador ya tiene un usuario");
    exit;
}

// Verificar si el username ya existe
if (Usuario::usernameExiste($conexion, $_POST["usuario"])) {
    header("Location: formcrearusuario.php?mensaje=Ese nombre de usuario ya existe");
    exit;
}

// Verificar si el email ya existe
if (Usuario::emailExiste($conexion, $_POST["correo"])) {
    header("Location: formcrearusuario.php?mensaje=El email ya está registrado");
    exit;
}

$usuario = new Usuario(
    $_POST["usuario"],
    $_POST["contrasena"],
    $_POST["correo"],
    $_POST["nivel"],
    $_POST["id_trabajador"]
);

if ($usuario->guardar($conexion)) {
    $auditoria = new Auditoria($conexion);
    $auditoria->registrar(
        'Creacion de usuario',
        $_POST["usuario"],
        "Usuario creado con ID trabajador: " . $_POST["id_trabajador"]
    );

    header("Location: formcrearusuario.php?exito=1");
    exit;
} else {
    header("Location: formcrearusuario.php?mensaje=Error al guardar el usuario");
    exit;
}
?>