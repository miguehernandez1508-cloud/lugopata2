<?php
class Restablecimiento {
    private $conexion;
    private $bd;

    public function __construct($conexion, $bd) {
        $this->conexion = $conexion;
        $this->bd = $bd;
        
        // Configurar zona horaria
        date_default_timezone_set('America/Caracas');
    }

    public function validarSuperadmin($password) {
        try {
            $sql = "SELECT password FROM usuarios WHERE username = 'superadmin' AND nivel = 'superadministrador'";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            $usuario = $stmt->fetch(PDO::FETCH_OBJ);
            
            if ($usuario && password_verify($password, $usuario->password)) {
                return true;
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Error al validar superadmin: " . $e->getMessage());
            return false;
        }
    }

    public function ejecutarRestablecimiento() {
        try {
            // Desactivar verificación de claves foráneas temporalmente
            $this->conexion->exec("SET FOREIGN_KEY_CHECKS = 0");

            // Eliminar datos de todas las tablas excepto fases_incidencia
            $this->eliminarDatosCompletos();

            // Insertar datos básicos del sistema
            $this->insertarDatosBasicos();

            // Reactivar verificación de claves foráneas
            $this->conexion->exec("SET FOREIGN_KEY_CHECKS = 1");

            // Registrar en auditoría
            $this->registrarAuditoria("superadmin", "RESTABLECIMIENTO_COMPLETO");

            return [
                'success' => true,
                'mensaje' => "Restablecimiento completado exitosamente.<br><br>" .
                           "Sistema reiniciado a estado inicial.<br>" .
                           "Datos básicos insertados.<br>" .
                           "Tabla 'fases_incidencia' conservada."
            ];

        } catch (Exception $e) {
            // Reactivar foreign keys siempre
            $this->conexion->exec("SET FOREIGN_KEY_CHECKS = 1");
            
            error_log("Error en restablecimiento: " . $e->getMessage());
            return [
                'success' => false,
                'mensaje' => "Error durante el restablecimiento: " . $e->getMessage()
            ];
        }
    }

    private function eliminarDatosCompletos() {
        // Lista de tablas en ORDEN CORRECTO para evitar errores de FK
        $tablas = [
            // Tablas hijas (con FK a otras)
            'detalle_salida_almacen',
            'detalle_solicitud_material',
            'incidencia_conformidad',
            'incidencia_rechazos',
            'incidencia_fases',
            'incidencia_imagenes',
            'detalle_trabajador',
            'recuperacion',
            'stock_almacen',
            
            // Tablas principales
            'insumos',
            'usuarios',
            'solicitud_salida_almacen',
            'solicitud_materiales',
            'incidencias',
            'trabajadores',
            'categorias_insumo',
            'departamentos',
            
            // Auditoría
            'auditoria'
        ];

        foreach ($tablas as $tabla) {
            // Saltar la tabla fases_incidencia (NO se elimina)
            if ($tabla === 'fases_incidencia') {
                continue;
            }
            $this->vaciarTabla($tabla);
        }
    }

    private function vaciarTabla($tabla) {
        try {
            // Verificar si la tabla existe
            $stmt = $this->conexion->query("SHOW TABLES LIKE '{$tabla}'");
            if ($stmt->rowCount() === 0) {
                return; // La tabla no existe, continuar
            }

            // Usar DELETE
            $this->conexion->exec("DELETE FROM `{$tabla}`");
            
            // Resetear auto_increment
            $this->resetAutoIncrement($tabla);
            
        } catch (Exception $e) {
            error_log("Error al vaciar tabla {$tabla}: " . $e->getMessage());
            throw new Exception("No se pudo vaciar la tabla {$tabla}");
        }
    }

    private function resetAutoIncrement($tabla) {
        try {
            // Verificar si la tabla tiene columna auto_increment
            $stmt = $this->conexion->query("SHOW COLUMNS FROM `{$tabla}` WHERE `Extra` = 'auto_increment'");
            if ($stmt->fetch()) {
                $this->conexion->exec("ALTER TABLE `{$tabla}` AUTO_INCREMENT = 1");
            }
        } catch (Exception $e) {
            // Ignorar error si no se puede resetear
            error_log("Advertencia: No se pudo resetear auto_increment en {$tabla}: " . $e->getMessage());
        }
    }

    private function insertarDatosBasicos() {
        // 1. Insertar departamento básico (Mantenimiento)
        $this->insertarDepartamento();

        // 2. Insertar trabajador base (Johander Hernández) - SIN aptitudes
        $idTrabajador = $this->insertarTrabajador();

        // 3. Insertar usuario superadmin
        $this->insertarSuperadmin($idTrabajador);

        // 4. Insertar categorías de insumos
        $this->insertarCategorias();
        
        // NOTA: NO se insertan aptitudes para el trabajador
    }

    private function insertarDepartamento() {
        $sql = "INSERT INTO departamentos (nombre, descripcion, ubicacion, telefono, email, responsable) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([
            'Mantenimiento',
            'Departamento encargado del mantenimiento general de las instalaciones',
            'Sótano',
            '0212-555-1234',
            'mantenimiento@clinica.com',
            'Johander Hernández'
        ]);
    }

    private function insertarTrabajador() {
        $sql = "INSERT INTO trabajadores (cedula, nombre, apellido, telefono, direccion, firma, id_departamento)
                VALUES (?, ?, ?, ?, ?, NULL, ?)";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([
            '31.583.133',
            'Johander',
            'Hernández',
            '04243031431',
            'Caracas, Venezuela',
            1
        ]);
        
        return $this->conexion->lastInsertId();
    }

    private function insertarSuperadmin($idTrabajador) {
        // Hash para la contraseña 'Admin1234'
        $hashedPassword = '$2y$10$UoeA6QBkdo6fM3Ms8XdTNe7xWtVvecDcq3MXqE/0mU.ivZEej8SvK';
        
        $sql = "INSERT INTO usuarios (username, password, email, nivel, id_trabajador, intentos_fallidos, bloqueado, password_anterior)
                VALUES (?, ?, ?, ?, ?, 0, 0, NULL)";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([
            'superadmin',
            $hashedPassword,
            'Miguehernandez1508@gmail.com',
            'superadministrador',
            $idTrabajador
        ]);
    }

    private function insertarCategorias() {
        $categorias = [
            ['Materiales Eléctricos', 'Materiales para instalaciones eléctricas', 'ELEC'],
            ['Herramientas', 'Herramientas de trabajo general', 'HERR'],
            ['Materiales de Plomería', 'Materiales para reparaciones de plomería', 'PLOM'],
            ['Materiales de Carpintería', 'Materiales para trabajos de carpintería', 'CARP'],
            ['Limpieza', 'Productos de limpieza y aseo', 'LIMP']
        ];

        $sql = "INSERT INTO categorias_insumo (nombre, descripcion, abreviatura) VALUES (?, ?, ?)";
        $stmt = $this->conexion->prepare($sql);
        
        foreach ($categorias as $categoria) {
            $stmt->execute($categoria);
        }
    }

    private function registrarAuditoria($usuario, $accion) {
        try {
            $sql = "INSERT INTO auditoria (accion, usuario, detalle) VALUES (?, ?, ?)";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([
                $accion, 
                $usuario, 
                "Restablecimiento completo ejecutado el " . date('Y-m-d H:i:s')
            ]);
        } catch (Exception $e) {
            error_log("Error al registrar auditoría: " . $e->getMessage());
        }
    }

    public function obtenerEstadisticas() {
        $estadisticas = [];
        
        // Tablas a contar
        $tablas = [
            'usuarios', 'trabajadores', 'departamentos', 'categorias_insumo',
            'insumos', 'stock_almacen', 'solicitud_materiales', 'detalle_solicitud_material',
            'incidencias', 'incidencia_imagenes', 'solicitud_salida_almacen', 'detalle_salida_almacen',
            'fases_incidencia', 'incidencia_fases', 'incidencia_rechazos', 'auditoria',
            'recuperacion', 'detalle_trabajador'
        ];
        
        foreach ($tablas as $tabla) {
            try {
                // Verificar si la tabla existe
                $check = $this->conexion->query("SHOW TABLES LIKE '{$tabla}'");
                if ($check->rowCount() > 0) {
                    $stmt = $this->conexion->query("SELECT COUNT(*) as total FROM `{$tabla}`");
                    $fila = $stmt->fetch(PDO::FETCH_OBJ);
                    $estadisticas[$tabla] = (int)$fila->total;
                } else {
                    $estadisticas[$tabla] = 0;
                }
            } catch (Exception $e) {
                $estadisticas[$tabla] = 0;
            }
        }
        
        // Formatear nombres para la vista
        $estadisticasFormateadas = [
            'usuarios' => $estadisticas['usuarios'] ?? 0,
            'trabajadores' => $estadisticas['trabajadores'] ?? 0,
            'insumos' => $estadisticas['insumos'] ?? 0,
            'solicitud_materiales' => $estadisticas['solicitud_materiales'] ?? 0,
            'incidencias' => $estadisticas['incidencias'] ?? 0,
            'solicitud_salida_almacen' => $estadisticas['solicitud_salida_almacen'] ?? 0,
            'departamentos' => $estadisticas['departamentos'] ?? 0,
            'categorias' => $estadisticas['categorias_insumo'] ?? 0
        ];
        
        return $estadisticasFormateadas;
    }
}
?>