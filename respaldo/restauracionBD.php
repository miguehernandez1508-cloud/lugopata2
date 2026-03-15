<?php
class RestauracionBD {
    private $conexion;
    private $host;
    private $usuario;
    private $clave;
    private $bd;
    private $directorioRespaldos;

    public function __construct($conexion, $host, $usuario, $clave, $bd) {
        $this->conexion = $conexion;
        $this->host = $host;
        $this->usuario = $usuario;
        $this->clave = $clave;
        $this->bd = $bd;
        $this->directorioRespaldos = __DIR__ . "/respaldos/";
        
        // Configurar zona horaria de Venezuela
        date_default_timezone_set('America/Caracas');
    }

    public function restaurarDesdeArchivo($archivoTemporal, $nombreArchivo) {
        try {
            // Verificar que es un archivo SQL
            if (pathinfo($nombreArchivo, PATHINFO_EXTENSION) !== 'sql') {
                throw new Exception("El archivo debe ser de tipo SQL");
            }

            // Leer el contenido del archivo
            $contenido = file_get_contents($archivoTemporal);
            if ($contenido === false) {
                throw new Exception("No se pudo leer el archivo de respaldo");
            }

            // Verificar que no esté vacío
            if (empty(trim($contenido))) {
                throw new Exception("El archivo de respaldo está vacío");
            }

            // Obtener lista de tablas existentes para eliminarlas
            $tablasExistentes = $this->obtenerTablasExistentes();

            // Desactivar foreign keys temporalmente
            $this->conexion->exec("SET FOREIGN_KEY_CHECKS = 0");

            // Eliminar tablas existentes en orden inverso (para evitar problemas de FK)
            $this->eliminarTablasExistentes(array_reverse($tablasExistentes));

            // Ejecutar el SQL del respaldo (filtrando comandos problemáticos)
            $this->ejecutarSQL($contenido);

            // Reactivar foreign keys
            $this->conexion->exec("SET FOREIGN_KEY_CHECKS = 1");

            return [
                'success' => true,
                'mensaje' => "Base de datos restaurada exitosamente desde: {$nombreArchivo}"
            ];

        } catch (Exception $e) {
            // Asegurarse de reactivar foreign keys en caso de error
            $this->conexion->exec("SET FOREIGN_KEY_CHECKS = 1");
            
            error_log("Error en restauración BD: " . $e->getMessage());
            return [
                'success' => false,
                'mensaje' => "Error al restaurar: " . $e->getMessage()
            ];
        }
    }

    public function restaurarDesdeLista($nombreArchivo) {
        try {
            $rutaArchivo = $this->directorioRespaldos . $nombreArchivo;

            if (!file_exists($rutaArchivo)) {
                throw new Exception("Archivo no encontrado: {$nombreArchivo}");
            }

            // Leer el contenido del archivo
            $contenido = file_get_contents($rutaArchivo);
            if ($contenido === false) {
                throw new Exception("No se pudo leer el archivo de respaldo");
            }

            // Obtener lista de tablas existentes para eliminarlas
            $tablasExistentes = $this->obtenerTablasExistentes();

            // Desactivar foreign keys temporalmente
            $this->conexion->exec("SET FOREIGN_KEY_CHECKS = 0");

            // Eliminar tablas existentes en orden inverso (para evitar problemas de FK)
            $this->eliminarTablasExistentes(array_reverse($tablasExistentes));

            // Ejecutar el SQL
            $this->ejecutarSQL($contenido);

            // Reactivar foreign keys
            $this->conexion->exec("SET FOREIGN_KEY_CHECKS = 1");

            return [
                'success' => true,
                'mensaje' => "Base de datos restaurada exitosamente desde: {$nombreArchivo}"
            ];

        } catch (Exception $e) {
            // Asegurarse de reactivar foreign keys en caso de error
            $this->conexion->exec("SET FOREIGN_KEY_CHECKS = 1");
            
            error_log("Error en restauración BD: " . $e->getMessage());
            return [
                'success' => false,
                'mensaje' => "Error al restaurar: " . $e->getMessage()
            ];
        }
    }

    private function obtenerTablasExistentes() {
        $tablas = [];
        $stmt = $this->conexion->query("SHOW TABLES");
        while ($fila = $stmt->fetch(PDO::FETCH_NUM)) {
            $tablas[] = $fila[0];
        }
        return $tablas;
    }

    private function eliminarTablasExistentes($tablas) {
        foreach ($tablas as $tabla) {
            try {
                $this->conexion->exec("DROP TABLE IF EXISTS `{$tabla}`");
            } catch (Exception $e) {
                error_log("Error al eliminar tabla {$tabla}: " . $e->getMessage());
                // Continuar con las demás tablas aunque falle una
            }
        }
    }

    private function ejecutarSQL($sql) {
        // Dividir el SQL en consultas individuales
        $consultas = $this->dividirConsultas($sql);
        
        $errores = [];
        
        foreach ($consultas as $consulta) {
            $consulta = trim($consulta);
            
            // Saltar líneas vacías, comentarios o comandos SET problemáticos
            if (empty($consulta) || 
                strpos($consulta, '--') === 0 ||
                strpos($consulta, '/*!') === 0 ||
                $this->esComandoProblematico($consulta)) {
                continue;
            }

            try {
                $this->conexion->exec($consulta);
            } catch (Exception $e) {
                $errorMsg = $e->getMessage();
                
                // Solo registrar errores que no sean de tablas/registros duplicados
                if (strpos($errorMsg, 'already exists') === false && 
                    strpos($errorMsg, 'Duplicate entry') === false &&
                    strpos($errorMsg, 'Base table') === false) {
                    $errores[] = "Error en consulta: " . substr($consulta, 0, 100) . "... - " . $errorMsg;
                }
            }
        }

        if (!empty($errores)) {
            // Solo mostrar los primeros 3 errores para no saturar
            $erroresMostrar = array_slice($errores, 0, 3);
            throw new Exception("Se encontraron algunos errores durante la restauración:\n" . implode("\n", $erroresMostrar));
        }
    }

    private function esComandoProblematico($consulta) {
        $comandosProblematicos = [
            'SET @',
            'SET CHARACTER_SET',
            'SET character_set',
            'SET NAMES',
            'SET time_zone',
            'SET FOREIGN_KEY_CHECKS',
            'SET SQL_MODE',
            'SET AUTOCOMMIT'
        ];

        $consultaUpper = strtoupper($consulta);
        
        foreach ($comandosProblematicos as $comando) {
            if (strpos($consultaUpper, strtoupper($comando)) === 0) {
                return true;
            }
        }

        return false;
    }

    private function dividirConsultas($sql) {
        // Dividir por punto y coma, pero ignorar los que están dentro de strings
        $consultas = [];
        $consultaActual = '';
        $enString = false;
        $caracterString = '';
        
        for ($i = 0; $i < strlen($sql); $i++) {
            $caracter = $sql[$i];
            
            if (($caracter === "'" || $caracter === '"') && !$enString) {
                $enString = true;
                $caracterString = $caracter;
            } elseif ($caracter === $caracterString && $enString) {
                $enString = false;
            } elseif ($caracter === ';' && !$enString) {
                $consultas[] = $consultaActual;
                $consultaActual = '';
                continue;
            }
            
            $consultaActual .= $caracter;
        }
        
        // Agregar la última consulta si existe
        if (!empty(trim($consultaActual))) {
            $consultas[] = $consultaActual;
        }
        
        return $consultas;
    }

    public function listarRespaldosDisponibles() {
        $respaldos = [];
        
        if (is_dir($this->directorioRespaldos)) {
            $archivos = scandir($this->directorioRespaldos);
            foreach ($archivos as $archivo) {
                if ($archivo !== '.' && $archivo !== '..' && pathinfo($archivo, PATHINFO_EXTENSION) === 'sql') {
                    $rutaCompleta = $this->directorioRespaldos . $archivo;
                    
                    // Extraer fecha del nombre del archivo
                    $fechaDelArchivo = $this->extraerFechaDelNombre($archivo);
                    
                    $respaldos[] = [
                        'nombre' => $archivo,
                        'ruta' => $rutaCompleta,
                        'tamano' => filesize($rutaCompleta),
                        'fecha' => $fechaDelArchivo
                    ];
                }
            }
            
            // Ordenar por fecha (más reciente primero)
            usort($respaldos, function($a, $b) {
                return strtotime($b['fecha']) - strtotime($a['fecha']);
            });
        }
        
        return $respaldos;
    }

    private function extraerFechaDelNombre($nombreArchivo) {
        // El formato es: respaldo_clinica_YYYY-mm-dd_HH-ii-ss.sql
        // Ejemplo: respaldo_clinica_2024-01-15_14-30-25.sql
        
        // Buscar el patrón de fecha en el nombre del archivo
        if (preg_match('/(\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2})/', $nombreArchivo, $matches)) {
            $fechaHora = $matches[1];
            // Convertir de "2024-01-15_14-30-25" a "2024-01-15 14:30:25"
            $fechaFormateada = str_replace('_', ' ', $fechaHora);
            
            // Reemplazar solo los guiones en la parte de la hora por dos puntos
            $partes = explode(' ', $fechaFormateada);
            if (count($partes) == 2) {
                $partes[1] = str_replace('-', ':', $partes[1]);
                // Reordenar la fecha a día-mes-año
                $fechaPartes = explode('-', $partes[0]); // Separa año, mes, día
                $fechaReordenada = $fechaPartes[2] . '-' . $fechaPartes[1] . '-' . $fechaPartes[0]; // día-mes-año
                $fechaFormateada = $fechaReordenada . ' ' . $partes[1];
            }
            
            return $fechaFormateada;
        }
        
        // Si no se puede extraer la fecha del nombre, usar filemtime como fallback
        $rutaCompleta = $this->directorioRespaldos . $nombreArchivo;
        return date("d-m-Y H:i:s", filemtime($rutaCompleta));
    }

    public function obtenerInfoBD() {
        return [
            'nombre' => $this->bd,
            'servidor' => $this->host,
            'total_respaldos' => count($this->listarRespaldosDisponibles()),
            'zona_horaria' => date_default_timezone_get(),
            'hora_actual' => date('d-m-Y H:i:s') // Cambiado a formato día-mes-año
        ];
    }
    
    public function eliminarRespaldo($nombreArchivo) {
        try {
            $rutaArchivo = $this->directorioRespaldos . $nombreArchivo;
            
            if (file_exists($rutaArchivo) && unlink($rutaArchivo)) {
                return [
                    'success' => true,
                    'mensaje' => "Respaldo eliminado: {$nombreArchivo}"
                ];
            } else {
                return [
                    'success' => false,
                    'mensaje' => "Error al eliminar respaldo: {$nombreArchivo}"
                ];
            }
        } catch (Exception $e) {
            error_log("Error al eliminar respaldo: " . $e->getMessage());
            return [
                'success' => false,
                'mensaje' => "Error al eliminar respaldo: " . $e->getMessage()
            ];
        }
    }
}
?>