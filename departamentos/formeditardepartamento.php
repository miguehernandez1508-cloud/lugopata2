<?php
// Inicia o reanuda la sesión actual
session_start();
// Incluye archivos necesarios para conexión a BD y funcionalidades
require_once "../conex.php";
require_once "departamento.php";
include_once "../encabezado.php";

// Crea objeto Departamento y obtiene los datos del departamento a editar
$departamentoObj = new Departamento($conexion);
// Obtiene los datos del departamento usando el ID pasado por GET
$d = $departamentoObj->obtener($_GET['id']);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Editar Departamento</title>
    <style>
        /* RESET Y VARIABLES - Mismo enfoque que dashboard.php */
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
            font-size: clamp(1.2rem, 4vw, 1.8rem);
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

        /* Campos más compactos */
        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="number"],
        .form-group select {
            max-width: 100%;
        }

        textarea {
            resize: vertical;
            min-height: 80px;
            max-height: 150px;
        }

        /* Texto en mayúsculas para ciertos campos */
        input[type="text"] {
            text-transform: uppercase;
        }

        /* CAMPOS EN LÍNEA (Teléfono y Email) */
        .inline-fields {
            display: flex;
            gap: 15px;
        }

        .inline-fields .form-group {
            flex: 1;
        }

        /* Ocultar flechas en inputs numéricos */
        input[type="number"] {
            -moz-appearance: textfield;
        }

        input[type="number"]::-webkit-outer-spin-button,
        input[type="number"]::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
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

        /* Botón Actualizar - Flat Success */
        .btn-success {
            background-color: #198754 !important;
            color: white;
        }

        .btn-success:hover {
            background-color: #059669;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        /* Botón Regresar - Flat Primary */
        .btn-primary {
            background-color: #6c757d !important;
            color: white;
        }

        .btn-primary:hover {
            background-color: #2563eb;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        /* Efecto active para feedback táctil */
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

            .form-group {
                margin-bottom: 15px;
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
                font-size: 16px; /* Previene zoom en iOS */
            }

            .btn {
                padding: 14px 20px;
                font-size: 16px;
            }
        }

        /* Para pantallas muy grandes */
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
            <img src="../assets/resources/departamentosR.png" alt="Departamento">
            EDITAR DEPARTAMENTO
        </h1>
        <p>Modifique los campos que desee actualizar</p>
    </div>

    <!-- TARJETA DEL FORMULARIO -->
    <div class="form-card">
        
        <form method="post" action="actualizardepartamento.php">
            <input type="hidden" name="id" value="<?= $d->id_departamento ?>">

            <div class="form-group">
                <label for="nombre">Nombre: *</label>
                <input name="nombre" required type="text" value="<?= htmlspecialchars($d->nombre) ?>" placeholder="Nombre del departamento">
            </div>

            <div class="form-group">
                <label for="descripcion">Descripción:</label>
                <textarea name="descripcion" rows="3" placeholder="Describe las funciones del departamento"><?= htmlspecialchars($d->descripcion) ?></textarea>
            </div>

            <div class="form-group">
                <label for="ubicacion">Ubicación:</label>
                <input name="ubicacion" type="text" value="<?= htmlspecialchars($d->ubicacion) ?>" placeholder="Edificio, piso, oficina...">
            </div>

            <div class="inline-fields">
                <div class="form-group">
                    <label for="telefono">Teléfono:</label>
                    <input name="telefono" type="number" value="<?= htmlspecialchars($d->telefono) ?>" placeholder="Número de contacto" min="0">
                </div>
                
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input name="email" type="email" value="<?= htmlspecialchars($d->email) ?>" placeholder="correo@empresa.com">
                </div>
            </div>

            <div class="form-group">
                <label for="responsable">Responsable:</label>
                <input name="responsable" type="text" value="<?= htmlspecialchars($d->responsable) ?>" placeholder="Nombre del encargado">
            </div>

            <div class="btn-container">
                <button type="submit" class="btn btn-success">
                    <img src="../assets/resources/disco2.png" alt="">
                    Actualizar
                </button>
                <button type="button" class="btn btn-primary" onclick="history.back()">
                    <img src="../assets/resources/volver2.png" alt="">
                    Regresar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Prevenir números negativos
    document.querySelectorAll('input[type="number"]').forEach(input => {
        input.addEventListener('keydown', function(e) {
            if (e.key === '-' || e.key === 'Subtract') {
                e.preventDefault();
            }
        });
    });

    // Convertir texto a mayúsculas automáticamente
    document.querySelector('input[name="nombre"]').addEventListener('input', function(e) {
        e.target.value = e.target.value.toUpperCase();
    });

    document.querySelector('input[name="responsable"]').addEventListener('input', function(e) {
        e.target.value = e.target.value.toUpperCase();
    });

    document.querySelector('input[name="ubicacion"]').addEventListener('input', function(e) {
        e.target.value = e.target.value.toUpperCase();
    });

    // Prevenir flechas en campos numéricos
    document.querySelectorAll('input[type="number"]').forEach(input => {
        input.addEventListener('keydown', function(e) {
            if (['ArrowUp', 'ArrowDown'].includes(e.key)) {
                e.preventDefault();
            }
        });
    });
</script>
</body>
</html>