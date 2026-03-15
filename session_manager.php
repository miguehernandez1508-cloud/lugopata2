<?php
require_once "conex.php";

class SessionManager {
    private $timeout = 3000; // 5 minutos
    
    public function __construct() {
        $this->checkTimeout();
    }
    
    private function checkTimeout() {
        if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $this->timeout)) {
            $this->registrarTimeout();
            session_unset();
            session_destroy();
            header("Location: /lugopata/index.php?error=session_expired");
            exit();
        }
    }
    
    private function registrarTimeout() {
        try {
            // Usar la conexión global que ya tienes
            global $conexion;
            $usuario = $_SESSION['username'] ?? 'Desconocido';
            $sql = "INSERT INTO auditoria (accion, usuario, detalle) VALUES (?, ?, ?)";
            $sentencia = $conexion->prepare($sql);
            $sentencia->execute(['SESSION_TIMEOUT', $usuario, 'Sesión cerrada por inactividad']);
        } catch (Exception $e) {
            // Silenciar error
        }
    }
    
    public function getRemainingTime() {
        if (isset($_SESSION['LAST_ACTIVITY'])) {
            $elapsed = time() - $_SESSION['LAST_ACTIVITY'];
            return max(0, $this->timeout - $elapsed);
        }
        return 0;
    }
}
?>