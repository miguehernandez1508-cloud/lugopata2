<?php
require_once "../conex.php";

class Usuario {
    private $username;
    private $password;
    private $email;
    private $nivel;
    private $id_trabajador;
    private $bloqueado;
    private $intentos_fallidos;

    public function __construct($username, $password, $email, $nivel, $id_trabajador, $bloqueado = 0, $intentos_fallidos = 0) {
        $this->username = $username;
        $this->password = !empty($password) ? password_hash($password, PASSWORD_DEFAULT) : null;
        $this->email = $email;
        $this->nivel = $nivel;
        $this->id_trabajador = $id_trabajador;
        $this->bloqueado = $bloqueado;
        $this->intentos_fallidos = $intentos_fallidos;
    }

    public function guardar($conexion) {
        $sql = "INSERT INTO usuarios (username, password, email, nivel, id_trabajador, bloqueado, intentos_fallidos) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $sentencia = $conexion->prepare($sql);
        return $sentencia->execute([
            $this->username,
            $this->password,
            $this->email,
            $this->nivel,
            $this->id_trabajador,
            $this->bloqueado,
            $this->intentos_fallidos
        ]);
    }

    // Método estático para obtener usuario por ID
    public static function obtenerPorId($conexion, $id_usuario) {
        $sql = "SELECT * FROM usuarios WHERE id_usuario = ?";
        $sentencia = $conexion->prepare($sql);
        $sentencia->execute([$id_usuario]);
        return $sentencia->fetch(PDO::FETCH_OBJ);
    }

    // Método estático para actualizar usuario
    public static function actualizar($conexion, $id_usuario, $username, $email, $nivel, $id_trabajador, $bloqueado) {
        $sql = "UPDATE usuarios SET username = ?, email = ?, nivel = ?, id_trabajador = ?, bloqueado = ? WHERE id_usuario = ?";
        $sentencia = $conexion->prepare($sql);
        return $sentencia->execute([
            $username,
            $email,
            $nivel,
            $id_trabajador,
            $bloqueado,
            $id_usuario
        ]);
    }

    // Método estático para eliminar usuario
    public static function eliminar($conexion, $id_usuario) {
        $sql = "DELETE FROM usuarios WHERE id_usuario = ?";
        $sentencia = $conexion->prepare($sql);
        return $sentencia->execute([$id_usuario]);
    }

    // Método estático para listar todos los usuarios
    public static function listar($conexion, $inicio = 0, $limite = 10, $filtro_nivel = 'todos', $filtro_estado = 'todos') {
        $where = [];
        $params = [];

        if ($filtro_nivel !== 'todos') {
            $where[] = "u.nivel = ?";
            $params[] = $filtro_nivel;
        }

        if ($filtro_estado !== 'todos') {
            $where[] = "u.bloqueado = ?";
            $params[] = ($filtro_estado === 'bloqueado') ? 1 : 0;
        }

        $sql = "SELECT u.*, t.nombre, t.apellido, t.cedula, d.nombre as departamento
                FROM usuarios u 
                JOIN trabajadores t ON u.id_trabajador = t.id_trabajador 
                LEFT JOIN departamentos d ON t.id_departamento = d.id_departamento";

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY u.id_usuario DESC LIMIT ? OFFSET ?";
        $params[] = $limite;
        $params[] = $inicio;

        $sentencia = $conexion->prepare($sql);
        $sentencia->execute($params);
        return $sentencia->fetchAll(PDO::FETCH_OBJ);
    }

    // Método estático para contar usuarios
    public static function contar($conexion, $filtro_nivel = 'todos', $filtro_estado = 'todos') {
        $where = [];
        $params = [];

        if ($filtro_nivel !== 'todos') {
            $where[] = "nivel = ?";
            $params[] = $filtro_nivel;
        }

        if ($filtro_estado !== 'todos') {
            $where[] = "bloqueado = ?";
            $params[] = ($filtro_estado === 'bloqueado') ? 1 : 0;
        }

        $sql = "SELECT COUNT(*) as total FROM usuarios";
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sentencia = $conexion->prepare($sql);
        $sentencia->execute($params);
        return $sentencia->fetch(PDO::FETCH_OBJ)->total;
    }

    // Método para actualizar contraseña
    public static function actualizarPassword($conexion, $id_usuario, $nueva_password) {
        $password_hash = password_hash($nueva_password, PASSWORD_DEFAULT);
        $sql = "UPDATE usuarios SET password = ?, password_anterior = password WHERE id_usuario = ?";
        $sentencia = $conexion->prepare($sql);
        return $sentencia->execute([$password_hash, $id_usuario]);
    }

    // Método para verificar si un trabajador ya tiene usuario
    public static function trabajadorTieneUsuario($conexion, $id_trabajador, $excluir_usuario_id = null) {
        $sql = "SELECT id_usuario FROM usuarios WHERE id_trabajador = ?";
        $params = [$id_trabajador];
        
        if ($excluir_usuario_id) {
            $sql .= " AND id_usuario != ?";
            $params[] = $excluir_usuario_id;
        }
        
        $sentencia = $conexion->prepare($sql);
        $sentencia->execute($params);
        return $sentencia->rowCount() > 0;
    }

    // Método para verificar si un username ya existe
    public static function usernameExiste($conexion, $username, $excluir_usuario_id = null) {
        $sql = "SELECT id_usuario FROM usuarios WHERE username = ?";
        $params = [$username];
        
        if ($excluir_usuario_id) {
            $sql .= " AND id_usuario != ?";
            $params[] = $excluir_usuario_id;
        }
        
        $sentencia = $conexion->prepare($sql);
        $sentencia->execute($params);
        return $sentencia->rowCount() > 0;
    }

    // Método para verificar si un email ya existe
    public static function emailExiste($conexion, $email, $excluir_usuario_id = null) {
        $sql = "SELECT id_usuario FROM usuarios WHERE email = ?";
        $params = [$email];
        
        if ($excluir_usuario_id) {
            $sql .= " AND id_usuario != ?";
            $params[] = $excluir_usuario_id;
        }
        
        $sentencia = $conexion->prepare($sql);
        $sentencia->execute($params);
        return $sentencia->rowCount() > 0;
    }
}
?>