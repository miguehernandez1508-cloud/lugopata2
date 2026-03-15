<?php
class RespaldoBD {
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
        
        // Configurar zona horaria de venezuela
        date_default_timezone_set('America/Caracas');
        
        // Crear directorio si no existe
        if (!is_dir($this->directorioRespaldos)) {
            mkdir($this->directorioRespaldos, 0755, true);
        }
    }

    public function crearRespaldo() {
        try {
            // Usar formato Y-m-d para el nombre del archivo (para ordenamiento)
            $fechaArchivo = date("Y-m-d_H-i-s");
            $nombreArchivo = "respaldo_{$this->bd}_{$fechaArchivo}.sql";
            $rutaCompleta = $this->directorioRespaldos . $nombreArchivo;

            // RUTA CORRECTA DE mysqldump EN XAMPP
            $mysqldumpPath = '"C:\\xampp\\mysql\\bin\\mysqldump.exe"';
            
            // Comando mysqldump con ruta completa
            $comando = "{$mysqldumpPath} --host={$this->host} --user={$this->usuario} --password={$this->clave} {$this->bd} > \"{$rutaCompleta}\"";
            
            $output = [];
            $returnCode = 0;
            exec($comando . " 2>&1", $output, $returnCode);

            if ($returnCode === 0 && file_exists($rutaCompleta)) {
                $tamano = filesize($rutaCompleta);
                
                return [
                    'success' => true,
                    'mensaje' => "Respaldo creado exitosamente: {$nombreArchivo}",
                    'archivo' => $nombreArchivo,
                    'ruta' => $rutaCompleta,
                    'tamano' => $tamano
                ];
            } else {
                // Si falla, intentar método alternativo
                return $this->crearRespaldoAlternativo($nombreArchivo, $rutaCompleta);
            }
            
        } catch (Exception $e) {
            error_log("Error en respaldo BD: " . $e->getMessage());
            return [
                'success' => false,
                'mensaje' => "Error al crear respaldo: " . $e->getMessage()
            ];
        }
    }

    // Método alternativo si mysqldump no funciona
    private function crearRespaldoAlternativo($nombreArchivo, $rutaCompleta) {
        try {
            // Obtener todas las tablas
            $tablas = [];
            $stmt = $this->conexion->query("SHOW TABLES");
            while ($fila = $stmt->fetch(PDO::FETCH_NUM)) {
                $tablas[] = $fila[0];
            }

            $contenido = "-- Respaldo de BD: {$this->bd}\n";
            $contenido .= "-- Fecha: " . date('d-m-Y H:i:s') . " (Hora de Venezuela)\n\n";

            foreach ($tablas as $tabla) {
                // Obtener estructura de la tabla
                $contenido .= "-- Estructura para tabla: {$tabla}\n";
                $stmt = $this->conexion->query("SHOW CREATE TABLE `{$tabla}`");
                $createTable = $stmt->fetch(PDO::FETCH_NUM);
                $contenido .= $createTable[1] . ";\n\n";

                // Obtener datos de la tabla
                $contenido .= "-- Volcado de datos para tabla: {$tabla}\n";
                $stmt = $this->conexion->query("SELECT * FROM `{$tabla}`");
                
                while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $columnas = implode('`, `', array_keys($fila));
                    $valores = array_map(function($valor) {
                        if ($valor === null) return 'NULL';
                        return "'" . str_replace("'", "''", $valor) . "'";
                    }, array_values($fila));
                    
                    $valoresStr = implode(', ', $valores);
                    $contenido .= "INSERT INTO `{$tabla}` (`{$columnas}`) VALUES ({$valoresStr});\n";
                }
                $contenido .= "\n";
            }

            // Guardar archivo
            if (file_put_contents($rutaCompleta, $contenido) !== false) {
                $tamano = filesize($rutaCompleta);
                return [
                    'success' => true,
                    'mensaje' => "Respaldo creado (método alternativo): {$nombreArchivo}",
                    'archivo' => $nombreArchivo,
                    'ruta' => $rutaCompleta,
                    'tamano' => $tamano
                ];
            } else {
                throw new Exception("No se pudo crear el archivo de respaldo");
            }

        } catch (Exception $e) {
            return [
                'success' => false,
                'mensaje' => "Error en método alternativo: " . $e->getMessage()
            ];
        }
    }

    public function listarRespaldos() {
        $respaldos = [];
        
        if (is_dir($this->directorioRespaldos)) {
            $archivos = scandir($this->directorioRespaldos);
            foreach ($archivos as $archivo) {
                if ($archivo !== '.' && $archivo !== '..' && pathinfo($archivo, PATHINFO_EXTENSION) === 'sql') {
                    $rutaCompleta = $this->directorioRespaldos . $archivo;
                    
                    // Extraer fecha del nombre del archivo en lugar de usar filemtime()
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

    public function descargarRespaldo($nombreArchivo) {
        $rutaArchivo = $this->directorioRespaldos . $nombreArchivo;
        
        if (file_exists($rutaArchivo)) {
            return [
                'success' => true,
                'ruta' => $rutaArchivo,
                'nombre' => $nombreArchivo
            ];
        } else {
            return [
                'success' => false,
                'mensaje' => "Archivo no encontrado: {$nombreArchivo}"
            ];
        }
    }

    public function eliminarRespaldo($nombreArchivo) {
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
    }

    public function obtenerInfoBD() {
        return [
            'nombre' => $this->bd,
            'servidor' => $this->host,
            'directorio_respaldos' => $this->directorioRespaldos,
            'zona_horaria' => date_default_timezone_get()
        ];
    }
}
?>