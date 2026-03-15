<?php 
session_start();
$_SESSION['status'] = null;

header('Location: user/Formlogin.php');
?>