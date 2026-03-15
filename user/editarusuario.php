<?php
// /user/editarusuario.php

// Iniciar sesión y procesar lógica ANTES de cualquier salida
session_start();
require_once __DIR__ . "/gestorsesion.php";
require_once __DIR__ . "/usuario.php";
require_once __DIR__ . "/auditoria.php";
require_once "../conex.php";

GestorSesiones::iniciar();

// Verificar permisos
$nivel_usuario = GestorSesiones::get('nivel');
$niveles_permitidos = ['admin', 'sistemas', 'superadministrador'];

if (!in_array($nivel_usuario, $niveles_permitidos)) {
    header("Location: /lugopata/dashboard.php");
    exit;
}

if (!isset($_GET['id_usuario'])) {
    header("Location: listarusuario.php");
    exit;
}

$id_usuario = (int)$_GET['id_usuario'];

// Obtener datos del usuario usando la clase
$usuario = Usuario::obtenerPorId($conexion, $id_usuario);

if (!$usuario) {
    header("Location: listarusuario.php?mensaje=Usuario no encontrado");
    exit;
}

// Obtener lista de trabajadores disponibles
$trabajadores = $conexion->query("
    SELECT t.id_trabajador, t.nombre, t.apellido
    FROM trabajadores t
    LEFT JOIN usuarios u ON t.id_trabajador = u.id_trabajador
    WHERE u.id_trabajador IS NULL OR u.id_trabajador = " . $usuario->id_trabajador
)->fetchAll(PDO::FETCH_OBJ);

// Procesar actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['usuario']);
    $email = trim($_POST['correo']);
    $nivel = $_POST['nivel'];
    $id_trabajador = (int)$_POST['id_trabajador'];
    $bloqueado = isset($_POST['bloqueado']) ? 1 : 0;

    // Verificar si el username ya existe (excluyendo el usuario actual)
    if (Usuario::usernameExiste($conexion, $username, $id_usuario)) {
        header("Location: editarusuario.php?id_usuario=$id_usuario&mensaje=El nombre de usuario ya existe");
        exit;
    }

    // Verificar si el email ya existe (excluyendo el usuario actual)
    if (Usuario::emailExiste($conexion, $email, $id_usuario)) {
        header("Location: editarusuario.php?id_usuario=$id_usuario&mensaje=El email ya está registrado");
        exit;
    }

    // Verificar si el trabajador ya tiene usuario (excluyendo el usuario actual)
    if (Usuario::trabajadorTieneUsuario($conexion, $id_trabajador, $id_usuario)) {
        header("Location: editarusuario.php?id_usuario=$id_usuario&mensaje=Este trabajador ya tiene un usuario asignado");
        exit;
    }

    // Actualizar usuario usando el método de la clase
    if (Usuario::actualizar($conexion, $id_usuario, $username, $email, $nivel, $id_trabajador, $bloqueado)) {
        // Registrar auditoría
        $auditoria = new Auditoria($conexion);
        $auditoria->registrar(
            'Actualizacion de usuario',
            $username,
            "Usuario actualizado: " . $username . " (ID: $id_usuario)"
        );
        
        header("Location: editarusuario.php?id_usuario=$id_usuario&exito=1");
        exit;
    } else {
        header("Location: editarusuario.php?id_usuario=$id_usuario&mensaje=Error al actualizar el usuario");
        exit;
    }
}

// SOLO DESPUÉS de toda la lógica anterior, incluimos el encabezado
$mensaje = $_GET['mensaje'] ?? '';
$exito = $_GET['exito'] ?? '';

include_once __DIR__ . "/../encabezado.php";
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Editar Usuario</title>
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
            max-width: 650px;
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

        /* INFO ADICIONAL */
        .info-adicional {
            background-color: #f9fafb;
            padding: 20px;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
            margin-bottom: 25px;
        }

        .info-adicional h4 {
            margin-top: 0;
            color: #374151;
            border-bottom: 1px solid #d1d5db;
            padding-bottom: 10px;
            margin-bottom: 15px;
            font-size: 16px;
        }

        .info-adicional p {
            margin: 10px 0;
            font-size: 14px;
            color: #4b5563;
        }

        .estado-activo {
            color: #059669;
            font-weight: 600;
            background-color: #d1fae5;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 12px;
        }

        .estado-bloqueado {
            color: #dc2626;
            font-weight: 600;
            background-color: #fee2e2;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 12px;
        }

        .alert-superadmin {
            color: #dc2626;
            font-size: 13px;
            margin-top: 10px;
            padding: 10px;
            background-color: #fef2f2;
            border-radius: 6px;
            border-left: 3px solid #dc2626;
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

        .form-group input[readonly] {
            background-color: #f3f4f6;
            border-color: #d1d5db;
            color: #6b7280;
        }

        .form-group select:disabled {
            background-color: #f3f4f6;
            color: #6b7280;
            cursor: not-allowed;
        }

        /* Texto en minúsculas */
        input[type="text"], input[type="email"] {
            text-transform: lowercase;
        }

        small {
            font-size: 12px;
            color: #6b7280;
            display: block;
            margin-top: 6px;
            font-style: italic;
        }

        /* CHECKBOX */
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 15px;
            background-color: #f9fafb;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }

        .checkbox-group input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
            accent-color: #dc2626;
        }

        .checkbox-group label {
            margin: 0 !important;
            cursor: pointer;
            font-weight: 600 !important;
        }

        .checkbox-group input[type="checkbox"]:disabled {
            cursor: not-allowed;
        }

        /* SECCIÓN CAMBIAR PASSWORD */
        .seccion-cambiar-password {
            margin-top: 30px;
            padding-top: 25px;
            border-top: 2px solid #e5e7eb;
        }

        .seccion-cambiar-password h3 {
            color: #374151;
            margin-bottom: 15px;
            font-size: 16px;
        }

        .seccion-cambiar-password p {
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 15px;
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

        .btn-password {
            background-color: #F57327;
            color: white;
        }

        .btn-password:hover {
            background-color: #4b5563;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(107, 114, 128, 0.3);
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

            .info-adicional {
                padding: 15px;
            }

            .checkbox-group {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
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

            .seccion-cambiar-password {
                text-align: center;
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

            .info-adicional p {
                font-size: 13px;
            }
        }

        @media (min-width: 1200px) {
            .form-wrapper {
                max-width: 600px;
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
            EDITAR USUARIO
        </h1>
        <p>Modifique los campos que desee actualizar</p>
    </div>

    <!-- TARJETA DEL FORMULARIO -->
    <div class="form-card">
        
        <?php if ($exito == 1): ?>
            <div class="alert alert-success">
                Usuario actualizado con éxito.
            </div>
        <?php endif; ?>

        <?php if ($mensaje): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($mensaje) ?>
            </div>
        <?php endif; ?>

        <!-- Información adicional del usuario -->
        <div class="info-adicional">
            <h4><img src="/lugopata/assets/resources/revi.png" width="24" height="24" alt="Información del Usuario"> Información del Usuario</h4>
            <p><strong>ID:</strong> <?= $usuario->id_usuario ?></p>
            <p><strong>Estado:</strong> 
                <?= $usuario->bloqueado ? 
                    '<span class="estado-bloqueado">Bloqueado</span>' : 
                    '<span class="estado-activo">Activo</span>' ?>
            </p>
            <p><strong>Intentos fallidos:</strong> <?= $usuario->intentos_fallidos ?></p>
            <?php if ($usuario->username === 'superadmin'): ?>
                <div class="alert-superadmin">
                    ¡! Este es el usuario superadministrador. Algunas opciones están restringidas.
                </div>
            <?php endif; ?>
        </div>

        <form method="post">
            <div class="form-group">
                <label for="usuario">Usuario: *</label>
                <input name="usuario" required type="text" id="usuario" 
                       value="<?= htmlspecialchars($usuario->username) ?>" 
                       placeholder="nombre_usuario"
                       <?= $usuario->username === 'superadmin' ? 'readonly' : '' ?>>    
                <?php if ($usuario->username === 'superadmin'): ?>
                    <small>El usuario superadmin no puede ser modificado</small>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="correo">Correo electrónico: *</label>
                <input name="correo" required type="email" id="correo" 
                       value="<?= htmlspecialchars($usuario->email) ?>" 
                       placeholder="usuario@empresa.com">
            </div>

            <div class="form-group">
                <label for="nivel">Nivel de usuario: *</label>
                <select name="nivel" id="nivel" required <?= $usuario->username === 'superadmin' ? 'disabled' : '' ?>>
                    <option value="">Seleccione nivel</option>
                    <option value="admin" <?= $usuario->nivel === 'admin' ? 'selected' : '' ?>>Gerente</option>
                    <option value="sistemas" <?= $usuario->nivel === 'sistemas' ? 'selected' : '' ?>>Sistemas</option>
                    <option value="supmantenimiento" <?= $usuario->nivel === 'supmantenimiento' ? 'selected' : '' ?>>Supervisor Mantenimiento</option>
                    <option value="obmantenimiento" <?= $usuario->nivel === 'obmantenimiento' ? 'selected' : '' ?>>Obrero Mantenimiento</option>
                    <option value="almacenista" <?= $usuario->nivel === 'almacenista' ? 'selected' : '' ?>>Almacenista</option>
                    <option value="solicitante" <?= $usuario->nivel === 'solicitante' ? 'selected' : '' ?>>Solicitante</option>
                </select>
                <?php if ($usuario->username === 'superadmin'): ?>
                    <input type="hidden" name="nivel" value="<?= $usuario->nivel ?>">
                    <small>El nivel del superadmin no puede ser modificado</small>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="trabajador">Trabajador: *</label>
                <select name="id_trabajador" id="trabajador" required>
                    <option value="">Seleccione un trabajador</option>
                    <?php foreach ($trabajadores as $t): ?>
                        <option value="<?= $t->id_trabajador ?>" 
                                <?= $t->id_trabajador == $usuario->id_trabajador ? 'selected' : '' ?>>
                            <?= htmlspecialchars($t->nombre . " " . $t->apellido) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <div class="checkbox-group">
                    <input type="checkbox" name="bloqueado" id="bloqueado" value="1" 
                           <?= $usuario->bloqueado ? 'checked' : '' ?>
                           <?= $usuario->username === 'superadmin' ? 'disabled' : '' ?>>
                    <label for="bloqueado">Usuario bloqueado</label>
                    <?php if ($usuario->username === 'superadmin'): ?>
                        <input type="hidden" name="bloqueado" value="0">
                        <small>El superadmin no puede ser bloqueado</small>
                    <?php endif; ?>
                </div>
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
// Validación adicional para el formulario
document.querySelector('form').addEventListener('submit', function(e) {
    const username = document.getElementById('usuario').value;
    const email = document.getElementById('correo').value;
    
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        e.preventDefault();
        alert('Por favor, ingresa un email válido.');
        return;
    }
    
    const usernameRegex = /^[a-zA-Z0-9_]+$/;
    if (!usernameRegex.test(username)) {
        e.preventDefault();
        alert('El usuario solo puede contener letras, números y guiones bajos.');
        return;
    }
    
    const trabajador = document.getElementById('trabajador').value;
    if (!trabajador) {
        e.preventDefault();
        alert('Por favor, selecciona un trabajador.');
        return;
    }

    if (!confirm('¿Estás seguro de que deseas actualizar los datos del usuario?')) {
        e.preventDefault();
    }
});

// Convertir usuario a minúsculas automáticamente
document.getElementById('usuario').addEventListener('input', function(e) {
    if (!this.readOnly) {
        this.value = this.value.toLowerCase();
    }
});

// Auto-ocultar mensajes después de 5 segundos
setTimeout(function() {
    const alert = document.querySelector('.alert');
    if (alert) {
        alert.style.display = 'none';
    }
}, 5000);
</script>
</body>
</html>