<?php
// Inicia o reanuda la sesión actual
session_start();
// Incluye el encabezado común y archivos necesarios
include_once "../encabezado.php";
require_once "departamento.php";
require_once "../conex.php"; 

// Variable para almacenar mensajes de éxito/error
$mensaje = "";

// Manejar envío del formulario POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Combina prefijo y número de teléfono en un solo string
    $telefono = $_POST['telefono_prefijo'] . $_POST['telefono_num'];

    // Crea objeto Departamento con datos del formulario
    $departamentoObj = new Departamento(
        $conexion,
        trim($_POST['nombre']),
        trim($_POST['descripcion']),
        trim($_POST['ubicacion']),
        $telefono,
        trim($_POST['email']),
        trim($_POST['responsable'])
    );

    // Intenta crear el departamento y muestra mensaje correspondiente
    if ($departamentoObj->crear()) {
        $mensaje = "Departamento agregado exitosamente.";
    } else {
        $mensaje = "Error al agregar departamento.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Crear Departamento</title>
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
        }

        .title-card h1 {
            color: #333;
            font-weight: bold;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
            margin: 0;
            font-size: clamp(1.2rem, 4vw, 1.8rem);
            text-align: center;
        }

        .title-card p {
            color: #666;
            margin-top: 10px;
            font-size: clamp(13px, 2vw, 15px);
            text-align: center;
        }

        .title-with-icon {
            align-items: left;
            justify-content: left;
            gap: 10px;
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

        /* Campos más compactos - no tan largos */
        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group select {
            max-width: 100%;
        }

        textarea {
            resize: vertical;
            min-height: 80px;
            max-height: 150px;
        }

        /* GRUPO DE TELÉFONO */
        .telefono-group {
            display: flex;
            gap: 10px;
        }

        .telefono-group select {
            flex: 0 0 100px;
            min-width: 100px;
        }

        .telefono-group input {
            flex: 1;
        }

        /* CAMPOS EN LÍNEA (Email y Responsable) */
        .inline-fields {
            display: flex;
            gap: 15px;
        }

        .inline-fields .form-group {
            flex: 1;
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

        /* Botón Guardar - Flat Success */
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
            background-color: #6c757d;
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

            .telefono-group {
                flex-direction: column;
                gap: 10px;
            }

            .telefono-group select {
                flex: none;
                width: 100%;
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
    <h1 class="title-with-icon">
        <img src="../assets/resources/departamentosR.png" width="45" alt="Departamento">
        REGISTRAR DEPARTAMENTO
    </h1>
    <p>Complete todos los campos requeridos</p>
</div>

    <!-- TARJETA DEL FORMULARIO -->
    <div class="form-card">
        
        <?php if($mensaje) { ?>
            <div class="<?= strpos($mensaje, 'Error') !== false ? 'alert alert-error' : 'alert alert-success' ?>">
                <?= $mensaje; ?>
            </div>
        <?php } ?>

        <form method="post" action="">
            <div class="form-group">
                <label for="nombre">Nombre del departamento: *</label>
                <input name="nombre" required type="text" id="nombre" placeholder="Ej: Recursos Humanos">
            </div>

            <div class="form-group">
                <label for="descripcion">Descripción:</label>
                <textarea name="descripcion" id="descripcion" rows="3" placeholder="Describe las funciones principales del departamento"></textarea>
            </div>

            <div class="form-group">
                <label>Teléfono: *</label>
                <div class="telefono-group">
                    <select name="telefono_prefijo" required>
                        <option value="">Prefijo</option>
                        <option value="0243">0243</option>
                        <option value="0212">0212</option>
                    </select>
                    <input type="tel" name="telefono_num" required placeholder="1234567" maxlength="7" inputmode="numeric">
                </div>
            </div>

            <div class="inline-fields">
                <div class="form-group">
                    <label for="email">Correo electrónico:</label>
                    <input name="email" type="email" id="email" placeholder="ejemplo@empresa.com">
                </div>
                <div class="form-group">
                    <label for="responsable">Responsable:</label>
                    <input name="responsable" type="text" id="responsable" placeholder="Nombre del encargado">
                </div>
            </div>

            <div class="form-group">
                <label for="ubicacion">Ubicación:</label>
                <input name="ubicacion" type="text" id="ubicacion" placeholder="Edificio A, Piso 2, Oficina 201">
            </div>

            <div class="btn-container">
                <button type="submit" class="btn btn-success">
                    <img src="../assets/resources/disco2.png" alt="">
                    Guardar
                </button>
                <a href="listardepartamento.php" class="btn btn-primary">
                    <img src="../assets/resources/volver2.png" alt="">
                    Regresar
                </a>
            </div>
        </form>
    </div>
</div>

<script> 
// Validación del campo de teléfono
const telefonoInput = document.querySelector('input[name="telefono_num"]');

telefonoInput.addEventListener('keydown', function(e) {
    if (['e', 'E', '+', '-', '.'].includes(e.key)) {
        e.preventDefault();
    }
});

telefonoInput.addEventListener('input', function() {
    this.value = this.value.replace(/\D/g, '').slice(0, 7);
});

document.querySelector('form').addEventListener('submit', function(e){
    if(telefonoInput.value.length < 7){
        alert('Error: El número de teléfono debe tener 7 dígitos.');
        e.preventDefault();
    }
});

// Auto-ocultar mensaje después de 5 segundos
setTimeout(function() {
    const alert = document.querySelector('.alert');
    if (alert) {
        alert.style.display = 'none';
    }
}, 5000);

// Validación para campo de responsable - SOLO LETRAS, MÁXIMO 15 CARACTERES
const responsableInput = document.getElementById('responsable');

responsableInput.addEventListener('keydown', function(e) {
    // Permitir teclas de control (backspace, tab, enter, flechas, etc.)
    const teclasPermitidas = ['Backspace', 'Tab', 'Enter', 'Delete', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Home', 'End'];
    if (teclasPermitidas.includes(e.key)) {
        return; // Permitir estas teclas sin validación
    }
    
    // Prevenir números y caracteres especiales (solo letras, espacios y puntos)
    if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s.]$/.test(e.key)) {
        e.preventDefault();
    }
});

responsableInput.addEventListener('input', function() {
    // Eliminar cualquier caracter que no sea letra, espacio o punto
    this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s.]/g, '');
    
    // Limitar a 15 caracteres
    if (this.value.length > 15) {
        this.value = this.value.slice(0, 30);
    }
});
</script>

<?php include_once "../pie.php"; ?>
</body>
</html>