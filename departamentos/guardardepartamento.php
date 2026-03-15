<?php
// Inicia o reanuda la sesión actual
session_start();
// Incluye archivos necesarios para conexión a BD y clase Departamento
require_once "../conex.php";
require_once "departamento.php";

// Verifica si la solicitud es de tipo POST (envío de formulario)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Crea una nueva instancia de Departamento con los datos del formulario
    $departamento = new Departamento(
        $conexion,
        trim($_POST['nombre']),        // Nombre del departamento (sin espacios al inicio/fin)
        trim($_POST['descripcion']),   // Descripción del departamento (sin espacios al inicio/fin)
        trim($_POST['ubicacion']),     // Ubicación del departamento (sin espacios al inicio/fin)
        trim($_POST['telefono']),      // Teléfono del departamento (sin espacios al inicio/fin)
        trim($_POST['email']),         // Email del departamento (sin espacios al inicio/fin)
        trim($_POST['responsable'])    // Responsable del departamento (sin espacios al inicio/fin)
    );

    // Intenta crear el departamento en la base de datos
    if ($departamento->crear()) {
        // Redirecciona con mensaje de éxito si la creación fue exitosa
        header("Location: listardepartamentos.php?mensaje=Departamento creado correctamente");
    } else {
        // Redirecciona con mensaje de error si falló la creación
        header("Location: formcrearDepartamento.php?error=No se pudo crear el departamento");
    }
    // Termina la ejecución del script después de la redirección
    exit();
}
?>