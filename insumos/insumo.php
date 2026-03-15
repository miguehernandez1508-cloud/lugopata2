    <?php
    require_once __DIR__ . "/../conex.php";
    require_once __DIR__ . "/../user/auditoria.php";

    // Clase para gestionar insumos en el sistema
    class Insumo {
        private $conexion;  // Objeto de conexión a la base de datos
        private $auditoria; // Objeto para registrar auditoría

        // Propiedades públicas que representan los campos de un insumo
        public $id_insumo;
        public $abreviatura_categoria;
        public $codigo_numerico;
        public $nombre;
        public $descripcion;
        public $unidad_medida;
        public $cantidad;
        public $stock_minimo;
        public $stock_maximo;
        public $imagen;
        public $id_categoria;

        // Constructor - inicializa las propiedades del insumo
        public function __construct($conexion, $id_insumo="", $nombre="", $descripcion="", $unidad_medida="", $cantidad=0, $stock_minimo=1, $stock_maximo=5, $imagen=NULL, $id_categoria=NULL) {
            $this->conexion = $conexion;
            $this->auditoria = new Auditoria($conexion);
            $this->id_insumo = $id_insumo;
            $this->nombre = $nombre;
            $this->descripcion = $descripcion;
            $this->unidad_medida = $unidad_medida;
            $this->cantidad = $cantidad;
            $this->stock_minimo = $stock_minimo;
            $this->stock_maximo = $stock_maximo;
            $this->imagen = $imagen;
            $this->id_categoria = $id_categoria;
        }

        // Método para crear un nuevo insumo en la base de datos
        public function crear() {
            try {
                // Insertar el insumo con los nuevos campos en la tabla insumos
                $sql = "INSERT INTO insumos (id_insumo, abreviatura_categoria, codigo_numerico, nombre, descripcion, unidad_medida, cantidad, stock_minimo, stock_maximo, imagen, id_categoria) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $sentencia = $this->conexion->prepare($sql);
                $resultado = $sentencia->execute([
                    $this->id_insumo, 
                    $this->abreviatura_categoria,
                    $this->codigo_numerico,
                    $this->nombre, 
                    $this->descripcion, 
                    $this->unidad_medida, 
                    1, // Cantidad siempre en 1 al crear
                    $this->stock_minimo,
                    $this->stock_maximo,
                    $this->imagen, 
                    $this->id_categoria
                ]);

                // Insertar también en stock_almacen con cantidad 0 para mantener consistencia
                $sqlStock = "INSERT INTO stock_almacen (id_insumo, cantidad) VALUES (?, ?)";
                $sentenciaStock = $this->conexion->prepare($sqlStock);
                $sentenciaStock->execute([$this->id_insumo, 0]);

                // Registrar auditoría
                if ($resultado) {
                    $usuario = $_SESSION['username'] ?? 'sistema';
                    $this->auditoria->registrar("crear insumo", $usuario, "ID: {$this->id_insumo}, Nombre: {$this->nombre}, Categoría: {$this->id_categoria}");
                }

                return $resultado;
            } catch (PDOException $e) {
                echo "Error SQL: " . $e->getMessage();
                return false;
            }
        }

        // Método para obtener todos los insumos ordenados por ID descendente
        public function listar() {
            $sentencia = $this->conexion->query("SELECT * FROM insumos ORDER BY id_insumo DESC");
            return $sentencia->fetchAll(PDO::FETCH_OBJ);
        }

        // Método para obtener un insumo específico por su ID
        public function obtener($id_insumo) {
            $sentencia = $this->conexion->prepare("SELECT * FROM insumos WHERE id_insumo = ?");
            $sentencia->execute([$id_insumo]);
            return $sentencia->fetch(PDO::FETCH_OBJ);
        }

        // Método para actualizar todos los campos de un insumo (incluyendo cantidad)
        public function actualizar($id_insumo) {
            try {
                // Actualizar todos los campos en la tabla insumos
                $sql = "UPDATE insumos 
                        SET nombre=?, descripcion=?, unidad_medida=?, cantidad=?, stock_minimo=?, stock_maximo=?, imagen=?, id_categoria=? 
                        WHERE id_insumo=?";
                $sentencia = $this->conexion->prepare($sql);
                $resultado = $sentencia->execute([
                    $this->nombre,
                    $this->descripcion,
                    $this->unidad_medida,
                    $this->cantidad,
                    $this->stock_minimo,
                    $this->stock_maximo,
                    $this->imagen,
                    $this->id_categoria,
                    $id_insumo
                ]);

                // Actualizar también la tabla stock_almacen para mantener consistencia
                $sqlStock = "UPDATE stock_almacen s
                            JOIN insumos i ON s.id_insumo = i.id_insumo
                            SET s.id_insumo = i.id_insumo
                            WHERE s.id_insumo = ?";
                $sentenciaStock = $this->conexion->prepare($sqlStock);
                $sentenciaStock->execute([$id_insumo]);

                // Registrar auditoría
                if ($resultado) {
                    $usuario = $_SESSION['username'] ?? 'sistema';
                    $this->auditoria->registrar("editar insumo", $usuario, "ID: $id_insumo, Nombre: {$this->nombre}, Cantidad: {$this->cantidad}");
                }

                return $resultado;
            } catch (PDOException $e) {
                echo "Error SQL: " . $e->getMessage();
                return false;
            }
        }

        // Método para actualizar un insumo sin modificar la cantidad
        public function actualizarSinCantidad($id_insumo) {
            try {
                $sql = "UPDATE insumos 
                        SET nombre = ?, descripcion = ?, unidad_medida = ?, stock_minimo = ?, stock_maximo = ?, imagen = ?, id_categoria = ? 
                        WHERE id_insumo = ?";
                $sentencia = $this->conexion->prepare($sql);
                $resultado = $sentencia->execute([
                    $this->nombre,
                    $this->descripcion,
                    $this->unidad_medida,
                    $this->stock_minimo,
                    $this->stock_maximo,
                    $this->imagen,
                    $this->id_categoria,
                    $id_insumo
                ]);

                // Registrar auditoría
                if ($resultado) {
                    $usuario = $_SESSION['username'] ?? 'sistema';
                    $this->auditoria->registrar("editar insumo", $usuario, "ID: $id_insumo, Nombre: {$this->nombre} (sin modificar cantidad)");
                }

                return $resultado;
            } catch (PDOException $e) {
                echo "Error SQL: " . $e->getMessage();
                return false;
            }
        }

        // Método para eliminar un insumo por su ID
        public function eliminar($id_insumo) {
            try {
                // Obtener información del insumo antes de eliminar para auditoría
                $insumo = $this->obtener($id_insumo);
                $nombreInsumo = $insumo ? $insumo->nombre : 'Desconocido';
                
                $sentencia = $this->conexion->prepare("DELETE FROM insumos WHERE id_insumo=?");
                $resultado = $sentencia->execute([$id_insumo]);

                // Registrar auditoría
                if ($resultado) {
                    $usuario = $_SESSION['username'] ?? 'sistema';
                    $this->auditoria->registrar("eliminar insumo", $usuario, "ID: $id_insumo, Nombre: $nombreInsumo");
                }

                return $resultado;
            } catch (PDOException $e) {
                echo "Error SQL: " . $e->getMessage();
                return false;
            }
        }

    // Método para verificar alertas de stock bajo
    public function verificarAlertasStock() {
        try {
            $sql = "SELECT 
                        i.id_insumo,
                        i.nombre,
                        i.descripcion, 
                        i.unidad_medida,
                        s.cantidad,
                        i.stock_minimo, 
                        i.stock_maximo,
                        i.id_categoria,
                        c.nombre as categoria_nombre
                    FROM insumos i 
                    LEFT JOIN stock_almacen s ON i.id_insumo = s.id_insumo 
                    LEFT JOIN categorias_insumo c ON i.id_categoria = c.id_categoria
                    WHERE s.cantidad <= (i.stock_minimo + 5) 
                    ORDER BY 
                        CASE 
                            WHEN s.cantidad <= i.stock_minimo THEN 1  -- Prioridad alta: stock por debajo del mínimo
                            WHEN s.cantidad <= (i.stock_minimo + 5) THEN 2  -- Prioridad media: stock cerca del mínimo
                            ELSE 3
                        END,
                        s.cantidad ASC";  // Ordenar por cantidad ascendente
            
            $sentencia = $this->conexion->prepare($sql);
            $sentencia->execute();
            return $sentencia->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log("Error verificar alertas stock: " . $e->getMessage());
            return [];
        }
    }

        // Método para contar el total de alertas de stock
        public function contarAlertasStock() {
            $sql = "SELECT COUNT(*) as total
                    FROM insumos i 
                    LEFT JOIN stock_almacen s ON i.id_insumo = s.id_insumo 
                    LEFT JOIN categorias_insumo c ON i.id_categoria = c.id_categoria
                    WHERE s.cantidad <= (i.stock_minimo + 5)";
            
            $sentencia = $this->conexion->prepare($sql);
            $sentencia->execute();
            return $sentencia->fetchColumn();
        }
    }
    ?>