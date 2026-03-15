<?php

require_once 'phpmailer/src/PHPMailer.php';
require_once 'phpmailer/src/SMTP.php';
require_once 'phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Mailer {
    private $mail;
    
    public function __construct() {
        $this->mail = new PHPMailer(true);
        $this->configurar();
    }
    
    private function configurar() {
     
        $this->mail->isSMTP();
        $this->mail->Host = 'smtp.gmail.com';
        $this->mail->SMTPAuth = true;
        $this->mail->Username = 'operacionesmant170@gmail.com';
        $this->mail->Password = 'igbl ripw wzif hloe';
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mail->Port = 587;
        
       
        $this->mail->setFrom('operacionesmant170@gmail.com', 'Sistema Lugopata');
        $this->mail->isHTML(true);
        $this->mail->CharSet = 'UTF-8';
    }
    
    public function enviarTokenRecuperacion($destinatario, $nombre, $token) {
        try {
            $this->mail->addAddress($destinatario, $nombre);
            $this->mail->Subject = 'Recuperación de Contraseña - Lugopata';
            
            $enlace = "http://localhost/lugopata/cambiarcontrasena.php?token=$token";
            
            $this->mail->Body = $this->getTemplateRecuperacion($nombre, $enlace);
            $this->mail->AltBody = "Hola $nombre,\n\nPara recuperar tu contraseña, visita: $enlace\n\nEl enlace expira en 1 hora.";
            
            $this->mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Error enviando correo: " . $this->mail->ErrorInfo);
            return false;
        }
    }
    
    private function getTemplateRecuperacion($nombre, $enlace) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #007bff; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { padding: 20px; background: #f9f9f9; }
                .button { background: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 4px; display: inline-block; }
                .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Recuperación de Contraseña</h2>
                </div>
                <div class='content'>
                    <h3>Hola $nombre,</h3>
                    <p>Has solicitado restablecer tu contraseña en <strong>Lugopata</strong>.</p>
                    <p>Haz clic en el siguiente botón para cambiar tu contraseña:</p>
                    <p style='text-align: center;'>
                        <a href='$enlace' class='button'>Cambiar Contraseña</a>
                    </p>
                    <p><strong>El enlace expirará en 1 hora.</strong></p>
                    <p>Si no solicitaste este cambio, puedes ignorar este mensaje.</p>
                </div>
                <div class='footer'>
                    <p>Equipo Lugopata<br>operacionesmant170@gmail.com</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
}
?>