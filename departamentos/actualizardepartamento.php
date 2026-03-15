<?php
session_start();
require_once "../conex.php";
require_once "departamento.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar que el ID existe
    if (!isset($_POST['id']) || empty($_POST['id'])) {
        header("Location: listardepartamento.php?error=ID no válido");
        exit();
    }
    
    // Crear el objeto Departamento CON todos los campos, incluyendo responsable
    $departamento = new Departamento(
        $conexion,
        trim($_POST['nombre']),
        trim($_POST['descripcion']),
        trim($_POST['ubicacion']),
        trim($_POST['telefono']),
        trim($_POST['email']),
        trim($_POST['responsable'])  // ← ¡ESTO ES LO QUE FALTA!
    );
    
    if ($departamento->actualizar($_POST['id'])) {
        header("Location: listardepartamento.php?mensaje=Departamento actualizado correctamente");
    } else {
        header("Location: formeditardepartamento.php?id=".$_POST['id']."&error=No se pudo actualizar el departamento");
    }
    exit();
}
?>