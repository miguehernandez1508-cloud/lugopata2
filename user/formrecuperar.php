<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Recuperar contraseña</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css"> 
    <link rel="stylesheet" href="../assets/css/estilo.css">
    <meta charset="utf-8">
    <style>
        body {
            background: url("../assets/resources/fondo.png") no-repeat center center fixed;
            background-size: cover;
            margin: 0;
            padding: 0;
        }
        .alert ul {
            margin-bottom: 0;
            padding-left: 20px;
        }
        .alert li {
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="container-fluid d-flex justify-content-center align-items-start" style="margin-top: 120px;">
        <div class="card shadow-lg rounded-4 p-4" style="max-width: 500px; width: 100%; background-color:#ececec; border: 3px solid powderblue;">
            <div class="d-flex justify-content-end">
                <img src="../assets/resources/logo.png" width="70" height="70" alt="Logo">
            </div>
            <div class="text-center mb-3">
                <img src="../assets/resources/recuperar.png" width="100" height="100" alt="Recuperar Contraseña">
            </div>
            
            <?php if (isset($_SESSION['mensaje'])): ?>
                <div class="alert alert-<?php echo $_SESSION['tipo_mensaje'] === 'success' ? 'success' : 'danger'; ?>">
                    <?php 
                        echo $_SESSION['mensaje'];
                        unset($_SESSION['mensaje']);
                        unset($_SESSION['tipo_mensaje']);
                    ?>
                    
                    <?php if (isset($_SESSION['mostrar_cerrar'])): ?>
                        <hr>
                        <div class="text-center mt-3">
                            <button onclick="cerrarPestana()" class="btn btn-outline-primary me-2">
                                 Cerrar y revisar correo
                            </button>
                            <a href="Formlogin.php" class="btn btn-secondary">
                                 Volver al Login
                            </a>
                        </div>
                        <?php unset($_SESSION['mostrar_cerrar']); ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <div align="center">
                <h1>Recuperar Contraseña</h1>
                <br>
                <form method="post" action="enviartoken.php">
                    <div class="mb-3">
                        <label for="email" class="form-label">Correo electrónico:</label>
                        <input type="email" class="form-control" name="email" id="email" required 
                               placeholder="Ingresa tu correo registrado" style="width: 300px;">
                    </div>
                    <button type="submit" class="btn btn-primary">Enviar enlace de recuperación</button> 
                    <br><br>
                    <a href="Formlogin.php" class="btn btn-secondary">Volver al Login</a>
                </form>
            </div>
        </div>
    </div>

    <script>
        function cerrarPestana() {
           
            if (window.history.length > 1) {
                
                window.history.back();
            } else {
               
                window.location.href = 'Formlogin.php';
            }
        }

        
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('email').focus();
        });
    </script>
</body>
</html>