<?php
session_start();
require_once "../conex.php";

$token = $_GET['token'] ?? '';
$mensaje = '';
$tipoMensaje = '';
$mostrarFormulario = true; //  NUEVA VARIABLE PARA CONTROLAR SI SE MUESTRA EL FORM

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tokenPost = $_POST['token'];
    $nuevaClave = $_POST['password'];
    $confirmarClave = $_POST['confirm_password'];

    try {
        
        if ($nuevaClave !== $confirmarClave) {
            throw new Exception("Las contraseñas no coinciden.");
        }

        if (strlen($nuevaClave) < 8) {
            throw new Exception("La contraseña debe tener al menos 8 caracteres.");
        }

        // OBTENER DATOS DEL USUARIO INCLUYENDO CONTRASEÑA ACTUAL
        $sql = "SELECT r.usuario_id, u.username, u.password as password_actual, u.password_anterior
                FROM recuperacion r 
                JOIN usuarios u ON r.usuario_id = u.id_usuario 
                WHERE r.token = ? AND r.expiracion >= NOW() 
                LIMIT 1";
        $sentencia = $conexion->prepare($sql);
        $sentencia->execute([$tokenPost]);
        $fila = $sentencia->fetch(PDO::FETCH_OBJ);

        if ($fila) {
            //  VERIFICAR QUE NO SEA LA MISMA CONTRASEÑA ANTERIOR
            $esPasswordAnterior = false;
            
            // Verificar contra contraseña actual
            if (password_verify($nuevaClave, $fila->password_actual)) {
                $esPasswordAnterior = true;
            }
            
            // Verificar contra contraseña anterior guardada
            if (!empty($fila->password_anterior) && password_verify($nuevaClave, $fila->password_anterior)) {
                $esPasswordAnterior = true;
            }
            
            if ($esPasswordAnterior) {
                throw new Exception("No puedes usar una contraseña que ya has utilizado anteriormente.");
            }
           
            $passwordHash = password_hash($nuevaClave, PASSWORD_DEFAULT);
            
            //  ACTUALIZAR CONTRASEÑA Y DESBLOQUEAR USUARIO CORRECTAMENTE
            $sqlUpdate = "UPDATE usuarios 
                         SET password_anterior = password,  -- Primero guardar la contraseña actual como anterior
                             password = ?,                 -- Luego establecer la nueva contraseña
                             intentos_fallidos = 0,        -- Reiniciar intentos
                             bloqueado = 0                 -- Desbloquear usuario (0 = false)
                         WHERE id_usuario = ?";
            
            $sentenciaUpdate = $conexion->prepare($sqlUpdate);
            
            if ($sentenciaUpdate->execute([$passwordHash, $fila->usuario_id])) {
                //  ELIMINAR TOKEN USADO
                $sqlDel = "DELETE FROM recuperacion WHERE token = ?";
                $sentenciaDel = $conexion->prepare($sqlDel);
                $sentenciaDel->execute([$tokenPost]);

                //  REGISTRAR EN AUDITORÍA
                require_once "Auditoria.php";
                $auditoria = new Auditoria($conexion);
                $auditoria->registrar('Contrasena recuperada', $fila->username, 
                                    "Contraseña recuperada");

                $mensaje = " Contraseña actualizada correctamente. Ya puedes iniciar sesión.";
                $tipoMensaje = "success";
                $mostrarFormulario = false; //  NO MOSTRAR FORMULARIO PORQUE YA SE CAMBIÓ
            } else {
                $errorInfo = $sentenciaUpdate->errorInfo();
                throw new Exception("Error al actualizar la contraseña: " . $errorInfo[2]);
            }
            
        } else {
            throw new Exception("Token inválido o expirado.");
        }

    } catch (Exception $e) {
        $mensaje = " " . $e->getMessage();
        $tipoMensaje = "error";
        error_log("Error en recuperarcontrasena.php: " . $e->getMessage());
        //  IMPORTANTE: NO CAMBIAMOS $mostrarFormulario - PERMITE SEGUIR INTENTANDO
    }
} else {
    // VERIFICAR TOKEN VÁLIDO (MÉTODO GET)
    if (!empty($token)) {
        $sql = "SELECT r.* FROM recuperacion r WHERE r.token = ? AND r.expiracion >= NOW() LIMIT 1";
        $sentencia = $conexion->prepare($sql);
        $sentencia->execute([$token]);
        $tokenValido = $sentencia->fetch(PDO::FETCH_OBJ);
        
        if (!$tokenValido) {
            $mensaje = " El enlace de recuperación es inválido o ha expirado.";
            $tipoMensaje = "error";
            $mostrarFormulario = false; //  NO MOSTRAR FORMULARIO SI TOKEN INVÁLIDO
        }
    } else {
        $mensaje = " Token no proporcionado.";
        $tipoMensaje = "error";
        $mostrarFormulario = false; //  NO MOSTRAR FORMULARIO SIN TOKEN
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cambiar Contraseña</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/estilo.css">
    <style>
        body {
            background: url("../assets/resources/fondo.png") no-repeat center center fixed;
            background-size: cover;
            margin: 0;
            padding: 0;
        }
        .card {
            margin-top: 100px;
            border: 3px solid powderblue;
        }
    </style>
</head>
<body>
    <div class="container-fluid d-flex justify-content-center align-items-start">
        <div class="card shadow-lg rounded-4 p-4" style="max-width: 500px; width: 100%; background-color:#ececec;">
            <div class="d-flex justify-content-end">
                <img src="../assets/resources/logo.png" width="70" height="70" alt="Logo">
            </div>
            <div class="text-center mb-3">
                <img src="../assets/resources/recuperar.png" width="100" height="100" alt="Cambiar Contraseña">
            </div>
            
            <div align="center">
                <h1>Cambiar Contraseña</h1>
                
                <?php if (!empty($mensaje)): ?>
                    <div class="alert alert-<?php echo $tipoMensaje === 'success' ? 'success' : 'danger'; ?> mt-3">
                        <?php echo $mensaje; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($mostrarFormulario): ?>
                <form method="post" class="mt-3">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Nueva contraseña:</label>
                        <input type="password" class="form-control" name="password" id="password" required 
                               placeholder="Mínimo 8 caracteres" style="width: 300px;">
                        <small class="form-text text-muted">No puede ser una contraseña que hayas usado antes</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirmar contraseña:</label>
                        <input type="password" class="form-control" name="confirm_password" id="confirm_password" required 
                               placeholder="Repite la contraseña" style="width: 300px;">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Cambiar contraseña</button>
                </form>
                <?php elseif ($tipoMensaje === 'success'): ?>
                    <div class="mt-3">
                        <a href="Formlogin.php" class="btn btn-success">Iniciar Sesión</a>
                    </div>
                <?php else: ?>
                    <div class="mt-3">
                        <a href="formrecuperar.php" class="btn btn-secondary">Solicitar nuevo enlace</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Las contraseñas no coinciden. Por favor, verifica.');
                return false;
            }

            const regex = /^(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*(),.?":{}|<>]).{8,}$/;
            if (!regex.test(password)) {
                e.preventDefault();
                alert('La contraseña debe tener al menos 8 caracteres, una letra mayúscula, un número y un carácter especial.');
                return false;
            }
        });
    }
});
</script>  
</body>
</html>