<?php 
session_start();
require_once "gestorsesion.php"; 
require_once "auditoria.php";
require_once "../conex.php";    


    $usuario = GestorSesiones::get("username");
    if($usuario){
        $auditoria = new Auditoria($conexion);
        $auditoria->registrar('Cierre de Sesion', $usuario);
    
    GestorSesiones::destruir();
    header("Location: /lugopata/index.php");
}
exit;

?>