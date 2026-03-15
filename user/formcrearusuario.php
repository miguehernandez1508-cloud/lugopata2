<?php
session_start();
include_once "../encabezado.php";
require_once "../conex.php";

$trabajadores = $conexion->query("
    SELECT t.id_trabajador, t.nombre, t.apellido
    FROM trabajadores t
    LEFT JOIN usuarios u ON t.id_trabajador = u.id_trabajador
    WHERE u.id_trabajador IS NULL
")->fetchAll(PDO::FETCH_OBJ);

$mensaje = $_GET['mensaje'] ?? '';
$exito = $_GET['exito'] ?? '';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Crear Usuario</title>
    <style>
        /* RESET Y VARIABLES */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background: url("/lugopata/assets/resources/fondoR.png") no-repeat center center fixed;
            background-size: cover;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            min-height: 100vh;
            padding: 15px;
        }

        /* CONTENEDOR PRINCIPAL */
        .form-wrapper {
            max-width: 600px;
            margin: 0 auto;
            width: 100%;
        }

        /* TARJETA DE TÍTULO */
        .title-card {
            background: white;
            padding: 20px;
            border-radius: 15px;
            border: 2px solid #ccc;
            margin-bottom: 20px;
            width: 100%;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            text-align: center;
        }

        .title-card h1 {
            color: #333;
            font-weight: bold;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
            margin: 0;
            font-size: clamp(1.1rem, 4vw, 1.6rem);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .title-card h1 img {
            width: 40px;
            height: 40px;
        }

        .title-card p {
            color: #666;
            margin-top: 10px;
            font-size: clamp(13px, 2vw, 15px);
        }

        /* TARJETA DEL FORMULARIO */
        .form-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            border: 2px solid #ccc;
            width: 100%;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        /* GRUPOS DE FORMULARIO */
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        .form-group input, 
        .form-group select, 
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            background-color: #fff;
            transition: all 0.3s ease;
        }

        .form-group input:focus, 
        .form-group select:focus, 
        .form-group textarea:focus {
            border-color: #0d6efd;
            outline: none;
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
        }

        /* Texto en minúsculas para usuario y correo */
        input[type="text"], input[type="email"] {
            text-transform: lowercase;
        }

        /* CAMPOS EN LÍNEA (Contraseñas) */
        .inline-fields {
            display: flex;
            gap: 15px;
        }

        .inline-fields .form-group {
            flex: 1;
        }

        /* MENSAJES DE VALIDACIÓN DE CONTRASEÑA */
        .password-match {
            color: #059669;
            font-size: 12px;
            margin-top: 6px;
            display: none;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .password-match::before {
            content: "✓";
            font-weight: bold;
        }

        .password-mismatch {
            color: #dc2626;
            font-size: 12px;
            margin-top: 6px;
            display: none;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .password-mismatch::before {
            content: "✕";
            font-weight: bold;
        }

        .password-requirements {
            font-size: 12px;
            color: #6b7280;
            margin-top: 6px;
            font-style: italic;
            line-height: 1.4;
        }

        /* ALERTAS */
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 500;
            font-size: 14px;
        }

        .alert-error {
            background-color: #fee2e2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        .alert-success {
            background-color: #d1fae5;
            color: #059669;
            border: 1px solid #a7f3d0;
        }

        /* BOTONES MINIMALISTAS FLAT */
        .btn-container {
            display: flex;
            gap: 12px;
            justify-content: center;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s ease;
            min-width: 160px;
        }

        .btn-success {
            background-color: #198754 !important;
            color: white;
        }

        .btn-success:hover {
            background-color: #059669;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .btn-primary {
            background-color: #6c757d !important;
            color: white;
        }

        .btn-primary:hover {
            background-color: #2563eb;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .btn:active {
            transform: translateY(0);
        }

        .btn img {
            width: 18px;
            height: 18px;
            filter: brightness(0) invert(1);
        }

        /* MEDIA QUERIES PARA MÓVILES */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }

            .form-card {
                padding: 20px;
            }

            .inline-fields {
                flex-direction: column;
                gap: 0;
            }

            .btn-container {
                flex-direction: column;
                gap: 10px;
            }

            .btn {
                width: 100%;
                min-width: auto;
            }

            .title-card h1 {
                flex-direction: column;
                gap: 5px;
            }

            .title-card h1 img {
                width: 35px;
                height: 35px;
            }
        }

        @media (max-width: 480px) {
            .title-card {
                padding: 15px;
            }

            .form-card {
                padding: 15px;
            }

            .form-group input, 
            .form-group select, 
            .form-group textarea {
                padding: 10px 12px;
                font-size: 16px;
            }

            .btn {
                padding: 14px 20px;
                font-size: 16px;
            }

            .password-requirements {
                font-size: 11px;
            }
        }

        @media (min-width: 1200px) {
            .form-wrapper {
                max-width: 550px;
            }
        }
    </style>
</head>
<body>
<div class="form-wrapper">
    <!-- TARJETA DE TÍTULO -->
    <div class="title-card">
        <h1>
            <img src="../assets/resources/inicio1.png" alt="Usuario">
            REGISTRAR NUEVO USUARIO
        </h1>
        <p>Complete todos los campos requeridos</p>
    </div>

    <!-- TARJETA DEL FORMULARIO -->
    <div class="form-card">
        
        <?php if ($exito == 1): ?>
            <div class="alert alert-success">
                Usuario creado con éxito.
            </div>
        <?php endif; ?>

        <?php if ($mensaje): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($mensaje) ?>
            </div>
        <?php endif; ?>

        <form method="post" action="nuevousuario.php" id="usuarioForm">
            <div class="form-group">
                <label for="usuario">Usuario: *</label>
                <input name="usuario" required type="text" id="usuario" placeholder="nombre_usuario">
                <div class="password-requirements">El usuario debe ser único y fácil de recordar</div>
            </div>

            <div class="inline-fields">
                <div class="form-group">
                    <label for="contrasena">Contraseña: *</label>
                    <input name="contrasena" required type="password" id="contrasena" placeholder="••••••••">
                    <div class="password-match" id="passwordMatch">Las contraseñas coinciden</div>
                    <div class="password-mismatch" id="passwordMismatch">Las contraseñas no coinciden</div>
                </div>

                <div class="form-group">
                    <label for="confirmar_contrasena">Confirmar Contraseña: *</label>
                    <input name="confirmar_contrasena" required type="password" id="confirmar_contrasena" placeholder="••••••••">
                </div>
            </div>

            <div class="form-group">
                <div class="password-requirements">
                    <strong>Requisitos:</strong> Mínimo 8 caracteres, una mayúscula y un carácter especial (!@#$%^&*)
                </div>
            </div>

            <div class="form-group">
                <label for="correo">Correo electrónico: *</label>
                <input name="correo" required type="email" id="correo" placeholder="usuario@empresa.com">
            </div>

            <div class="form-group">
                <label for="nivel">Nivel de usuario: *</label>
                <select name="nivel" id="nivel" required>
                    <option value="">Seleccione nivel</option>
                    <option value="admin">Gerente</option>
                    <option value="sistemas">Sistemas</option>
                    <option value="supmantenimiento">Supervisor Mantenimiento</option>
                    <option value="obmantenimiento">Obrero Mantenimiento</option>
                    <option value="almacenista">Almacenista</option>
                    <option value="solicitante">Solicitante</option>
                </select>
            </div>

            <div class="form-group">
                <label for="trabajador">Trabajador: *</label>
                <select name="id_trabajador" id="trabajador" required>
                    <option value="">Seleccione un trabajador</option>
                    <?php foreach ($trabajadores as $t): ?>
                        <option value="<?= $t->id_trabajador ?>"><?= $t->nombre . " " . $t->apellido ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="btn-container">
                <button type="submit" class="btn btn-success">
                    <img src="../assets/resources/disco2.png" alt="">
                    Guardar
                </button>
                <a href="listarusuario.php" class="btn btn-primary">
                    <img src="../assets/resources/volver2.png" alt="">
                    Regresar
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const password = document.getElementById('contrasena');
    const confirmPassword = document.getElementById('confirmar_contrasena');
    const matchMessage = document.getElementById('passwordMatch');
    const mismatchMessage = document.getElementById('passwordMismatch');
    const form = document.getElementById('usuarioForm');
    const usuarioInput = document.getElementById('usuario');

    // Convertir usuario a minúsculas automáticamente
    usuarioInput.addEventListener('input', function(e) {
        e.target.value = e.target.value.toLowerCase();
    });

    function validatePasswords() {
        if (password.value === '' || confirmPassword.value === '') {
            matchMessage.style.display = 'none';
            mismatchMessage.style.display = 'none';
            return;
        }

        if (password.value === confirmPassword.value) {
            matchMessage.style.display = 'flex';
            mismatchMessage.style.display = 'none';
        } else {
            matchMessage.style.display = 'none';
            mismatchMessage.style.display = 'flex';
        }
    }

    password.addEventListener('input', validatePasswords);
    confirmPassword.addEventListener('input', validatePasswords);

    form.addEventListener('submit', function(e) {
        const passwordVal = document.getElementById('contrasena').value;
        const confirmPasswordVal = document.getElementById('confirmar_contrasena').value;

        if (passwordVal !== confirmPasswordVal) {
            e.preventDefault();
            alert('Las contraseñas no coinciden. Por favor, verifica.');
            return;
        }

        const regex = /^(?=.*[A-Z])(?=.*[!@#$%^&*(),.?":{}|<>]).{8,}$/;
        if (!regex.test(passwordVal)) {
            e.preventDefault();
            alert('La contraseña debe tener al menos 8 caracteres, una letra mayúscula y un carácter especial (!@#$%^&*).');
        }
    });

    setTimeout(function() {
        const alert = document.querySelector('.alert');
        if (alert) {
            alert.style.display = 'none';
        }
    }, 5000);
});
</script>

<?php include_once "../pie.php"; ?>
</body>
</html>