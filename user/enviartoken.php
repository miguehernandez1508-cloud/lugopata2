<?php
session_start();
require_once "../conex.php";


require_once "../assets/phpmailer/src/PHPMailer.php";
require_once "../assets/phpmailer/src/SMTP.php";
require_once "../assets/phpmailer/src/Exception.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['email'])) {
    $email = trim($_POST['email']);

    try {
       
        $sql = "SELECT id_usuario, username, email FROM usuarios WHERE email = ? LIMIT 1";
        $sentencia = $conexion->prepare($sql);
        $sentencia->execute([$email]);
        $usuario = $sentencia->fetch(PDO::FETCH_OBJ);

        if ($usuario) {
            
            $token = bin2hex(random_bytes(32));
            $expiracion = date("Y-m-d H:i:s", strtotime("+1 hour"));

           
            $sqlDelete = "DELETE FROM recuperacion WHERE usuario_id = ?";
            $sentenciaDelete = $conexion->prepare($sqlDelete);
            $sentenciaDelete->execute([$usuario->id_usuario]);

         
            $sqlInsert = "INSERT INTO recuperacion (usuario_id, token, expiracion) VALUES (?, ?, ?)";
            $sentenciaInsert = $conexion->prepare($sqlInsert);
            $sentenciaInsert->execute([$usuario->id_usuario, $token, $expiracion]);

           
            if (enviarCorreoPHPMailer($usuario->email, $usuario->username, $token)) {
                $_SESSION['mensaje'] = " <strong>Correo enviado exitosamente</strong><br>Se ha enviado un enlace de recuperación a tu correo electrónico. <strong>Por favor, cierra esta pestaña y revisa tu bandeja de entrada.</strong>";
                $_SESSION['tipo_mensaje'] = 'success';
                $_SESSION['mostrar_cerrar'] = true;
            } else {
                $_SESSION['mensaje'] = " Error al enviar el correo. Por favor, intenta más tarde.";
                $_SESSION['tipo_mensaje'] = 'error';
            }
        } else {
           
            $_SESSION['mensaje'] = " <strong>Correo no registrado</strong><br>El correo electrónico ingresado no existe en nuestro sistema. Verifica que esté escrito correctamente.";
            $_SESSION['tipo_mensaje'] = 'error';
        }

    } catch (Exception $e) {
        $_SESSION['mensaje'] = " Error en el sistema. Por favor, intenta más tarde.";
        $_SESSION['tipo_mensaje'] = 'error';
    }
} else {
    $_SESSION['mensaje'] = " Por favor, ingresa un correo electrónico válido.";
    $_SESSION['tipo_mensaje'] = 'error';
}

header("Location: formrecuperar.php");
exit;


function enviarCorreoPHPMailer($destinatario, $nombre, $token) {
    try {
        $mail = new PHPMailer(true);
        
        
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'operacionesmant170@gmail.com';
        $mail->Password = 'igbl ripw wzif hloe';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
       
        $mail->setFrom('operacionesmant170@gmail.com', 'Sistema Lugopata');
        $mail->addAddress($destinatario, $nombre);
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        
       
        $enlace = "http://localhost/Lugopata/user/cambiarcontrasena.php?token=$token";
        
        $mail->Subject = 'Recuperación de Contraseña - Lugopata';
        $mail->Body = obtenerTemplateCorreo($nombre, $enlace);
        $mail->AltBody = "Hola $nombre,\n\nPara recuperar tu contraseña, visita: $enlace\n\nEl enlace expira en 1 hora.";
        
        return $mail->send();
        
    } catch (Exception $e) {
        error_log("Error PHPMailer: " . $e->getMessage());
        return false;
    }
}


function obtenerTemplateCorreo($nombre, $enlace) {
    return "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { 
                font-family: Arial, sans-serif; 
                color: #333; 
                line-height: 1.6;
                margin: 0;
                padding: 0;
                background-color: #f4f4f4;
            }
            .container { 
                max-width: 600px; 
                margin: 20px auto; 
                background: white;
                border-radius: 10px;
                overflow: hidden;
                box-shadow: 0 0 20px rgba(0,0,0,0.1);
            }
            .header { 
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white; 
                padding: 30px; 
                text-align: center; 
            }
            .header h1 {
                margin: 0;
                font-size: 24px;
            }
            .content { 
                padding: 30px; 
            }
            .button { 
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white; 
                padding: 15px 30px; 
                text-decoration: none; 
                border-radius: 25px; 
                display: inline-block;
                margin: 20px 0;
                font-weight: bold;
                text-align: center;
            }
            .footer { 
                text-align: center; 
                padding: 20px; 
                font-size: 12px; 
                color: #666;
                background: #f8f9fa;
                border-top: 1px solid #e9ecef;
            }
            .warning {
                background: #fff3cd;
                border: 1px solid #ffeaa7;
                padding: 15px;
                border-radius: 8px;
                margin: 20px 0;
                color: #856404;
            }
            .link-box {
                background: #f8f9fa;
                padding: 15px;
                border-radius: 8px;
                margin: 15px 0;
                word-break: break-all;
                font-size: 14px;
                border: 1px solid #e9ecef;
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1> Recuperación de Contraseña</h1>
            </div>
            <div class='content'>
                <h2>Hola $nombre,</h2>
                <p>Has solicitado restablecer tu contraseña en <strong>Lugopata</strong>.</p>
                <p>Haz clic en el siguiente botón para cambiar tu contraseña:</p>
                
                <div style='text-align: center;'>
                    <a href='$enlace' class='button'> Cambiar Contraseña</a>
                </div>
                
                <p>O copia y pega este enlace en tu navegador:</p>
                <div class='link-box'>$enlace</div>
                
                <div class='warning'>
                    <strong> Importante:</strong> Este enlace expirará en 1 hora por seguridad.
                </div>
                
                <p>Si no solicitaste este cambio, puedes ignorar este mensaje de forma segura.</p>
            </div>
            <div class='footer'>
                <p><strong>Equipo Lugopata</strong><br>
                <a href='mailto:operacionesmant170@gmail.com'>operacionesmant170@gmail.com</a></p>
                <p> " . date('Y') . " Lugopata. Todos los derechos reservados.</p>
            </div>
        </div>
    </body>
    </html>
    ";
}
?>