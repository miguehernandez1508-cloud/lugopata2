        <?php 
        class GestorSesiones {
            public static function iniciar() {
                if (session_status() == PHP_SESSION_NONE) {
                    session_start();
                }
            }

            public static function set($clave, $valor) {
                $_SESSION[$clave] = $valor;
            }

            public static function get($clave) {
                return $_SESSION[$clave] ?? null;
            }

            public static function destruir() {
                session_destroy();
                $_SESSION = [];
            }
        }

        ?>