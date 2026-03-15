<?php
session_start();
include_once "../encabezado.php";
require_once "trabajador.php";
require_once "../departamentos/departamento.php";
require_once "../conex.php";

$mensaje = "";
$id_trabajador = $_GET['id_trabajador'] ?? null;

if (!$id_trabajador) {
    header("Location: listartrabajador.php");
    exit();
}

$trabajadorObj = new Trabajador($conexion);
$departamentoObj = new Departamento($conexion);

$trabajador = $trabajadorObj->obtener($id_trabajador);
$departamentos = $departamentoObj->listar(0, 1000);

$sentencia = $conexion->prepare("SELECT aptitud FROM detalle_trabajador WHERE id_trabajador = ?");
$sentencia->execute([$id_trabajador]);
$aptitudesActuales = array_column($sentencia->fetchAll(PDO::FETCH_ASSOC), 'aptitud');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $telefono = $_POST['telefono_prefijo'] . $_POST['telefono_num'];

    $trabajadorObj->cedula = trim($_POST['cedula']);
    $trabajadorObj->nombre = trim($_POST['nombre']);
    $trabajadorObj->apellido = trim($_POST['apellido']);
    $trabajadorObj->telefono = $telefono;
    $trabajadorObj->direccion = trim($_POST['direccion']);
    $trabajadorObj->id_departamento = $_POST['id_departamento'];

    if ($trabajadorObj->actualizar($id_trabajador)) {
        $conexion->prepare("DELETE FROM detalle_trabajador WHERE id_trabajador = ?")->execute([$id_trabajador]);
        if (!empty($_POST['aptitudes']) && $_POST['id_departamento'] == 2) {
            $sentencia = $conexion->prepare("INSERT INTO detalle_trabajador (id_trabajador, aptitud) VALUES (?, ?)");
            foreach ($_POST['aptitudes'] as $apt) {
                $sentencia->execute([$id_trabajador, $apt]);
            }
        }

        $mensaje = "Trabajador actualizado correctamente.";
        $trabajador = $trabajadorObj->obtener($id_trabajador);
    } else {
        $mensaje = "Error al actualizar trabajador.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Editar Trabajador</title>
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

        textarea {
            resize: vertical;
            min-height: 80px;
            max-height: 150px;
        }

        /* Texto en mayúsculas */
        input[type="text"] {
            text-transform: uppercase;
        }

        /* CAMPOS EN LÍNEA */
        .inline-fields {
            display: flex;
            gap: 15px;
        }

        .inline-fields .form-group {
            flex: 1;
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

        /* OCULTAR FLECHAS EN NÚMEROS */
        input[type="number"] {
            -moz-appearance: textfield;
        }

        input[type="number"]::-webkit-outer-spin-button,
        input[type="number"]::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        /* SECCIÓN APTITUDES */
        .aptitudes-section {
            margin-top: 20px;
            padding: 20px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            background-color: #f9fafb;
        }

        .aptitudes-section h3 {
            margin-bottom: 15px;
            color: #374151;
            font-size: 16px;
        }

        .aptitudes-section .form-group {
            margin-bottom: 0;
        }

        select[multiple] {
            min-height: 150px;
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            background-color: white;
        }

        select[multiple] option {
            padding: 10px 12px;
            margin: 2px 0;
            border-radius: 4px;
            cursor: pointer;
        }

        select[multiple] option:checked {
            background-color: #0d6efd;
            color: white;
        }

        select[multiple] option:hover {
            background-color: #e5e7eb;
        }

        .small-text {
            font-size: 12px;
            color: #6b7280;
            font-style: italic;
            margin-top: 8px;
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

            .telefono-group {
                flex-direction: column;
                gap: 10px;
            }

            .telefono-group select {
                flex: none;
                width: 100%;
            }

            .aptitudes-section {
                padding: 15px;
            }

            select[multiple] {
                min-height: 120px;
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

            select[multiple] option {
                padding: 12px;
                font-size: 16px;
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
            <img src="../assets/resources/ltrabajadores2.png" alt="Trabajador">
            EDITAR TRABAJADOR
        </h1>
        <p>Modifique los campos que desee actualizar</p>
    </div>

    <!-- TARJETA DEL FORMULARIO -->
    <div class="form-card">
        
        <?php if($mensaje): ?>
            <div class="<?= strpos($mensaje, 'Error') !== false ? 'alert alert-error' : 'alert alert-success' ?>">
                <?= htmlspecialchars($mensaje) ?>
            </div>
        <?php endif; ?>

        <form method="post" action="">
            <div class="form-group">
                <label for="cedula">Cédula: *</label>
                <input name="cedula" required type="number" id="cedula" value="<?= htmlspecialchars($trabajador->cedula) ?>" placeholder="Número de cédula" inputmode="numeric">
            </div>

            <div class="inline-fields">
                <div class="form-group">
                    <label for="nombre">Nombre: *</label>
                    <input name="nombre" required type="text" id="nombre" value="<?= htmlspecialchars($trabajador->nombre) ?>" placeholder="Nombre">
                </div>
                <div class="form-group">
                    <label for="apellido">Apellido: *</label>
                    <input name="apellido" required type="text" id="apellido" value="<?= htmlspecialchars($trabajador->apellido) ?>" placeholder="Apellido">
                </div>
            </div>

            <div class="form-group">
                <label>Teléfono: *</label>
                <div class="telefono-group">
                    <?php
                    $prefijo = substr($trabajador->telefono, 0, 4);
                    $numero = substr($trabajador->telefono, 4);
                    ?>
                    <select name="telefono_prefijo" required>
                        <option value="">Prefijo</option>
                        <?php foreach (["0412","0422","0424","0414","0416","0426"] as $p): ?>
                            <option value="<?= $p ?>" <?= ($prefijo == $p) ? "selected" : "" ?>><?= $p ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="tel" name="telefono_num" required value="<?= htmlspecialchars($numero) ?>" placeholder="1234567" maxlength="7" inputmode="numeric">
                </div>
            </div>

            <div class="form-group">
                <label for="direccion">Dirección: *</label>
                <textarea required id="direccion" name="direccion" rows="3" placeholder="Dirección completa"><?= htmlspecialchars($trabajador->direccion) ?></textarea>
            </div>

            <div class="form-group">
                <label for="id_departamento">Departamento: *</label>
                <select name="id_departamento" id="id_departamento" required>
                    <option value="">Seleccione un departamento</option>
                    <?php foreach($departamentos as $d): ?>
                        <option value="<?= $d->id_departamento ?>" <?= ($trabajador->id_departamento == $d->id_departamento) ? "selected" : "" ?>>
                            <?= $d->nombre ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="aptitudes-section" id="seccionAptitudes" style="display: none;">
                <h3><img src="../assets/resources/herramientas.png" alt="" width="24" height="24"> Aptitudes / Conocimientos</h3>
                <div class="form-group">
                    <label for="aptitudes">Seleccione las aptitudes:</label>
                    <select name="aptitudes[]" id="aptitudes" multiple size="5">
                        <?php
                        $listaAptitudes = ["Electricidad","Carpintería","Plomería","Aires acondicionados","Computadoras","Otros"];
                        foreach($listaAptitudes as $apt): ?>
                            <option value="<?= $apt ?>" <?= in_array($apt, $aptitudesActuales) ? 'selected' : '' ?>><?= $apt ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="small-text">Mantén presionada la tecla Ctrl (o Cmd en Mac) para seleccionar varias aptitudes</div>
                </div>
            </div>

            <div class="btn-container">
                <button type="submit" class="btn btn-success">
                    <img src="../assets/resources/disco2.png" alt="">
                    Guardar
                </button>
                <button type="button" onclick="history.back()" class="btn btn-primary">
                    <img src="../assets/resources/volver2.png" alt="">
                    Regresar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const selectDepartamento = document.getElementById('id_departamento');
const seccionAptitudes = document.getElementById('seccionAptitudes');
const aptitudesSelect = document.getElementById('aptitudes');

function actualizarSeccion() {
    const seleccionado = selectDepartamento.options[selectDepartamento.selectedIndex].text.toLowerCase();
    if (seleccionado.includes('mantenimiento')) {
        seccionAptitudes.style.display = 'block';
        aptitudesSelect.required = true;
    } else {
        seccionAptitudes.style.display = 'none';
        aptitudesSelect.required = false;
    }
}
actualizarSeccion();
selectDepartamento.addEventListener('change', actualizarSeccion);

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

document.getElementById('nombre').addEventListener('input', function(e) {
    e.target.value = e.target.value.toUpperCase();
});

document.getElementById('apellido').addEventListener('input', function(e) {
    e.target.value = e.target.value.toUpperCase();
});

setTimeout(function() {
    const alert = document.querySelector('.alert');
    if (alert) {
        alert.style.display = 'none';
    }
}, 5000);
</script>

<?php include_once "../pie.php"; ?>
</body>
</html>