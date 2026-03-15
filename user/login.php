<?php
require_once "gestorsesion.php";

class Login {
    private $conexion; 
    private $max_intentos = 3;

    public function __construct($conexion) {
        $this->conexion = $conexion;
        GestorSesiones::iniciar();
    }

    public function validarUsuario($nombre, $clave) {
        $sql = "SELECT u.*, t.nombre, t.apellido, t.firma, u.password_anterior
                FROM usuarios u
                JOIN trabajadores t ON u.id_trabajador = t.id_trabajador
                WHERE u.username = ? 
                LIMIT 1";
        
        $sentencia = $this->conexion->prepare($sql);
        $sentencia->execute([$nombre]);
        $usuario = $sentencia->fetch(PDO::FETCH_OBJ);

        if (!$usuario) {
            // Usuario no existe
            GestorSesiones::set("status", 1);
            return false;
        }

        // 1. PRIMERO verificar si el usuario está BLOQUEADO
        if ($usuario->bloqueado) {
            GestorSesiones::set("status", 4); // Usuario bloqueado
            return false;
        }

        // 2. LUEGO verificar si la contraseña es la misma que la anterior
        if ($this->esPasswordAnterior($clave, $usuario->password, $usuario->password_anterior)) {
            GestorSesiones::set("status", 5); // Misma contraseña que la anterior
            $this->incrementarIntentosFallidos($usuario->id_usuario);
            return false;
        }

        // 3. FINALMENTE verificar contraseña actual
        if (password_verify($clave, $usuario->password)) {
            //  LOGIN EXITOSO - REINICIAR INTENTOS FALLIDOS
            $this->reiniciarIntentosFallidos($usuario->id_usuario);
            
            GestorSesiones::set("id_usuario", $usuario->id_usuario); 
            GestorSesiones::set("username", $usuario->username);
            GestorSesiones::set("nivel", $usuario->nivel);
            GestorSesiones::set("id_trabajador", $usuario->id_trabajador);
            GestorSesiones::set("nombre_completo", $usuario->nombre . " " . $usuario->apellido);
            GestorSesiones::set('firma', $usuario->firma ?? null);
            GestorSesiones::set("status", 0);

            require_once "Auditoria.php";
            $auditoria = new Auditoria($this->conexion);
            $auditoria->registrar('Inicio de Sesion', $usuario->username);

            return true;
        } else {
            //  CONTRASEÑA INCORRECTA - INCREMENTAR INTENTOS FALLIDOS
            $this->incrementarIntentosFallidos($usuario->id_usuario);
            GestorSesiones::set("status", 1);
            return false;
        }
    }

    private function esPasswordAnterior($clave_actual, $password_actual, $password_anterior) {
        // Verificar contra contraseña actual
        if (password_verify($clave_actual, $password_actual)) {
            return false; // No es anterior, es la actual
        }
        
        // Verificar contra contraseña anterior (si existe)
        if (!empty($password_anterior) && password_verify($clave_actual, $password_anterior)) {
            return true; // Es la contraseña anterior
        }
        
        return false;
    }

    private function incrementarIntentosFallidos($id_usuario) {
        // Primero obtener los intentos actuales
        $sql_select = "SELECT intentos_fallidos FROM usuarios WHERE id_usuario = ?";
        $stmt_select = $this->conexion->prepare($sql_select);
        $stmt_select->execute([$id_usuario]);
        $usuario = $stmt_select->fetch(PDO::FETCH_OBJ);
        
        $nuevos_intentos = ($usuario->intentos_fallidos ?? 0) + 1;
        $bloqueado = false;

        // Si alcanza el máximo de intentos, BLOQUEAR PERMANENTEMENTE
        if ($nuevos_intentos >= $this->max_intentos) {
            $bloqueado = true;
        }

        $sql_update = "UPDATE usuarios 
                      SET intentos_fallidos = ?, 
                          bloqueado = ? 
                      WHERE id_usuario = ?";
        
        $sentencia = $this->conexion->prepare($sql_update);
        $sentencia->execute([$nuevos_intentos, $bloqueado, $id_usuario]);

        // Registrar en auditoría si se bloqueó
        if ($bloqueado) {
            require_once "Auditoria.php";
            $auditoria = new Auditoria($this->conexion);
            
            // Obtener username para la auditoría
            $sql_user = "SELECT username FROM usuarios WHERE id_usuario = ?";
            $stmt_user = $this->conexion->prepare($sql_user);
            $stmt_user->execute([$id_usuario]);
            $user_data = $stmt_user->fetch(PDO::FETCH_OBJ);
            
            $auditoria->registrar('USUARIO_BLOQUEADO', $user_data->username, 
                                "Usuario bloqueado permanentemente después de {$this->max_intentos} intentos fallidos");
        }
    }

    private function reiniciarIntentosFallidos($id_usuario) {
        $sql = "UPDATE usuarios 
                SET intentos_fallidos = 0, 
                    bloqueado = false 
                WHERE id_usuario = ?";
        
        $sentencia = $this->conexion->prepare($sql);
        $sentencia->execute([$id_usuario]);
    }
    public function estaBloqueado($username) {
    try {
        $sql = "SELECT bloqueado, intentos_fallidos FROM usuarios WHERE username = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$username]);
        $usuario = $stmt->fetch(PDO::FETCH_OBJ);
        
        if ($usuario) {
            // Si está bloqueado (bloqueado = 1) O si tiene 3 o más intentos fallidos
            return ($usuario->bloqueado == 1 || $usuario->intentos_fallidos >= 3);
        }
        
        return false;
    } catch (Exception $e) {
        error_log("Error al verificar bloqueo: " . $e->getMessage());
        return false;
    }
}

}
?>