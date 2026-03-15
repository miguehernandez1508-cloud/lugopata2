<?php
// Inicia o reanuda una sesión existente
session_start();
// Incluye archivos necesarios para conexión a BD y clase Departamento
require_once "../conex.php";
require_once "departamento.php";

// Verifica si se ha pasado un ID por GET (para eliminar departamento)
if (isset($_GET['id'])) {
    // Crea una instancia de la clase Departamento
    $departamento = new Departamento($conexion);
    
    // Intenta eliminar el departamento con el ID recibido
    if ($departamento->eliminar($_GET['id'])) {
        // Redirecciona con mensaje de éxito si la eliminación fue exitosa
        header("Location: listardepartamentos.php?mensaje=Departamento eliminado");
    } else {
        // Redirecciona con mensaje de error si falló la eliminación
        header("Location: listardepartamentos.php?error=No se pudo eliminar");
    }
    // Termina la ejecución del script después de la redirección
    exit();
}
?>