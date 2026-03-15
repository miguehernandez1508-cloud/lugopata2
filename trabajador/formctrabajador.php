<?php
session_start();
include_once "../encabezado.php";
require_once "trabajador.php";
require_once "../departamentos/departamento.php";
require_once "../conex.php"; 

$mensaje = "";

$departamentoObj = new Departamento($conexion);
$departamentos = $departamentoObj->listar(0, 1000); 

// Obtener tipos de incidencia disponibles DINAMICAMENTE de la base de datos
$tipos_incidencia = $conexion->query(
    "SELECT DISTINCT tipo_incidencia FROM fases_incidencia WHERE tipo_incidencia != 'General' ORDER BY tipo_incidencia"
)->fetchAll(PDO::FETCH_COLUMN);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firmaBase64 = $_POST['firma'] ?? '';
    $firmaRuta = null;

    if($firmaBase64){
        $data = explode(',', $firmaBase64);
        $data = base64_decode($data[1]);
        $nombreArchivo = "../assets/imagenes/firmas/firma_" . time() . ".png";
        file_put_contents($nombreArchivo, $data);
        $firmaRuta = $nombreArchivo;
    }

    $telefono = $_POST['telefono_prefijo'] . $_POST['telefono_num'];    
    
    $tipo_cedula = $_POST['tipo_cedula'] ?? 'V';
    $numero_cedula = trim($_POST['cedula']);
    $cedula = $tipo_cedula . '-' . $numero_cedula;
    
    $sentencia = $conexion->prepare("SELECT COUNT(*) FROM trabajadores WHERE cedula = ?");
    $sentencia->execute([$cedula]);
    if ($sentencia->fetchColumn() > 0) {
        $mensaje = "Ya existe un trabajador con esa cédula.";
    } else {
        try {
            $conexion->beginTransaction();
            
            $trabajadorObj = new Trabajador(
                $conexion,
                $cedula,
                trim($_POST['nombre']),
                trim($_POST['apellido']),
                $telefono,  
                trim($_POST['direccion']),
                $firmaRuta,
                $_POST['id_departamento']
            );

            $id_trabajador = $trabajadorObj->crear();

            if ($id_trabajador) {
                if(!empty($_POST['aptitudes'])){
                    $aptitudes = $_POST['aptitudes'];
                    $niveles = $_POST['niveles_experiencia'] ?? [];
                    $sentencia = $conexion->prepare("INSERT INTO detalle_trabajador (id_trabajador, aptitud, nivel_experiencia) VALUES (?, ?, ?)");
                    
                    foreach($aptitudes as $index => $aptitud){
                        $nivel = $niveles[$index] ?? 'basico';
                        $resultado_aptitud = $sentencia->execute([$id_trabajador, $aptitud, $nivel]);
                        if (!$resultado_aptitud) {
                            throw new Exception("Error insertando aptitud: $aptitud");
                        }
                    }
                }

                $conexion->commit();
                $mensaje = "Trabajador agregado exitosamente con perfil tecnico configurado.";
                
            } else {
                throw new Exception("Error al crear el trabajador");
            }
            
        } catch (Exception $e) {
            $conexion->rollBack();
            $mensaje = "Error al agregar trabajador: " . $e->getMessage();
            
            if ($firmaRuta && file_exists($firmaRuta)) {
                unlink($firmaRuta);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Crear Trabajador</title>
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

        /* CAMPOS EN LÍNEA */
        .inline-fields {
            display: flex;
            gap: 15px;
        }

        .inline-fields .form-group {
            flex: 1;
        }

        /* GRUPO DE CÉDULA */
        .cedula-group {
            display: flex;
            gap: 10px;
        }

        .cedula-group select {
            flex: 0 0 80px;
            min-width: 80px;
        }

        .cedula-group input {
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

        /* FIRMA DIGITAL */
        .firma-container {
            text-align: center;
            margin-bottom: 20px;
        }

        .firma-container canvas {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            background-color: white;
            max-width: 100%;
            height: auto;
            touch-action: none; /* Importante para touch */
        }

        .btn-firma {
            background-color: #6b7280;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 10px;
            font-weight: 600;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
        }

        .btn-firma:hover {
            background-color: #4b5563;
        }

        .btn-firma img {
            width: 18px;
            height: 18px;
            filter: brightness(0) invert(1);
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

        .aptitud-item {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
            padding: 12px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            flex-wrap: wrap;
        }

        .aptitud-item select {
            flex: 2;
            min-width: 120px;
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 14px;
        }

        .aptitud-item select:last-of-type {
            flex: 1;
            min-width: 100px;
        }

        .remove-aptitud {
            background: #ef4444;
            color: white;
            border: none;
            border-radius: 6px;
            padding: 10px 14px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.2s ease;
        }

        .remove-aptitud:hover:not(:disabled) {
            background: #dc2626;
        }

        .remove-aptitud:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .btn-agregar-aptitud {
            background-color: #198754 !important;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 10px;
            font-weight: 600;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
        }

        .btn-agregar-aptitud:hover {
            background-color: #059669;
        }

        .small-text {
            font-size: 12px;
            color: #6b7280;
            font-style: italic;
            margin-top: 10px;
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

            .cedula-group {
                flex-direction: column;
                gap: 10px;
            }

            .cedula-group select {
                flex: none;
                width: 100%;
            }

            .telefono-group {
                flex-direction: column;
                gap: 10px;
            }

            .telefono-group select {
                flex: none;
                width: 100%;
            }

            .aptitud-item {
                flex-direction: column;
                align-items: stretch;
            }

            .aptitud-item select {
                width: 100%;
                flex: none !important;
            }

            .remove-aptitud {
                align-self: flex-end;
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

            .firma-container canvas {
                width: 100% !important;
                height: 150px !important;
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

            .aptitudes-section {
                padding: 15px;
            }

            .btn {
                padding: 14px 20px;
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
            REGISTRAR NUEVO TRABAJADOR
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
                <label for="cedula">Cédula: *</label>
                <div class="cedula-group">
                    <select name="tipo_cedula" id="tipo_cedula" required>
                        <option value="V" selected>V</option>
                        <option value="E">E</option>
                    </select>
                    <input name="cedula" min="0" required type="number" id="cedula" placeholder="Número de cédula" inputmode="numeric">
                </div>
            </div>

            <div class="inline-fields">
                <div class="form-group">
                    <label for="nombre">Nombre: *</label>
                    <input name="nombre" required type="text" id="nombre" placeholder="Nombre">
                </div>
                <div class="form-group">
                    <label for="apellido">Apellido: *</label>
                    <input name="apellido" required type="text" id="apellido" placeholder="Apellido">
                </div>
            </div>

            <div class="form-group">
                <label>Teléfono: *</label>
                <div class="telefono-group">
                    <select name="telefono_prefijo" required>
                        <option value="">Prefijo</option>
                        <option value="0412">0412</option>
                        <option value="0422">0422</option>
                        <option value="0424">0424</option>
                        <option value="0414">0414</option>
                        <option value="0416">0416</option>
                        <option value="0426">0426</option>
                    </select>
                    <input type="tel" name="telefono_num" required placeholder="1234567" maxlength="7" inputmode="numeric">
                </div>
            </div>

            <div class="form-group">
                <label for="direccion">Dirección: *</label>
                <textarea required id="direccion" name="direccion" rows="3" placeholder="Dirección completa"></textarea>
            </div>

            <div class="form-group">
                <label for="id_departamento">Departamento: *</label>
                <select name="id_departamento" id="id_departamento" required>
                    <option value="">Seleccione un departamento</option>
                    <?php foreach($departamentos as $d): ?>
                        <option value="<?= $d->id_departamento ?>"><?= $d->nombre ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Firma digital:</label>
                <div class="firma-container">
                    <canvas id="canvasFirma" width="400" height="150"></canvas>
                    <br>
                    <button type="button" onclick="limpiarFirma()" class="btn-firma">
                        <img src="../assets/resources/borrador.png" alt="">
                        Limpiar Firma
                    </button>
                    <input type="hidden" name="firma" id="firma">
                </div>
            </div>

            <div class="aptitudes-section" id="seccionAptitudes" style="display: none;">
                <h3><img src="../assets/resources/herramientas.png" alt="" width="24" height="24"> Perfil Técnico del Trabajador</h3>
                <div id="aptitudes-container">
                    <!-- Las aptitudes se agregarán dinámicamente aquí -->
                </div>
                <button type="button" id="agregar-aptitud" class="btn-agregar-aptitud">
                    <img src="../assets/resources/maso.png" alt="Agregar" width="15" height="15" style="filter: brightness(0) invert(1) !important;"> Agregar Aptitud
                </button>
                <div class="small-text">Configure las habilidades técnicas para asignación automática de incidencias</div>
            </div>

            <div class="btn-container">
                <button type="submit" class="btn btn-success">
                    <img src="../assets/resources/disco2.png" alt="">
                    Guardar
                </button>
                <a href="listartrabajador.php" class="btn btn-primary">
                    <img src="../assets/resources/volver2.png" alt="">
                    Regresar
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const canvas = document.getElementById("canvasFirma");
    const ctx = canvas.getContext("2d");
    ctx.strokeStyle = "black";
    ctx.lineWidth = 2;
    ctx.lineCap = "round";
    let dibujando = false;

    // Función para obtener coordenadas del mouse/touch
    function getPos(e) {
        const rect = canvas.getBoundingClientRect();
        const clientX = e.touches ? e.touches[0].clientX : e.clientX;
        const clientY = e.touches ? e.touches[0].clientY : e.clientY;
        return {
            x: clientX - rect.left,
            y: clientY - rect.top
        };
    }

    // Eventos de mouse
    canvas.addEventListener("mousedown", e => {
        dibujando = true;
        const pos = getPos(e);
        ctx.beginPath();
        ctx.moveTo(pos.x, pos.y);
    });

    canvas.addEventListener("mousemove", e => {
        if(dibujando){
            const pos = getPos(e);
            ctx.lineTo(pos.x, pos.y);
            ctx.stroke();
        }
    });

    canvas.addEventListener("mouseup", () => { dibujando = false; });
    canvas.addEventListener("mouseout", () => { dibujando = false; });

    // Eventos de touch para móviles
    canvas.addEventListener("touchstart", e => {
        e.preventDefault();
        dibujando = true;
        const pos = getPos(e);
        ctx.beginPath();
        ctx.moveTo(pos.x, pos.y);
    }, { passive: false });

    canvas.addEventListener("touchmove", e => {
        e.preventDefault();
        if(dibujando){
            const pos = getPos(e);
            ctx.lineTo(pos.x, pos.y);
            ctx.stroke();
        }
    }, { passive: false });

    canvas.addEventListener("touchend", () => { dibujando = false; });

    function limpiarFirma(){
        ctx.clearRect(0, 0, canvas.width, canvas.height);
    }

    document.querySelector("form").addEventListener("submit", function(){
        const dataURL = canvas.toDataURL("image/png");
        document.getElementById("firma").value = dataURL;
    });

    window.limpiarFirma = limpiarFirma;

    // Configuración de aptitudes dinámicas
    const selectDepartamento = document.getElementById('id_departamento');
    const seccionAptitudes = document.getElementById('seccionAptitudes');
    const aptitudesContainer = document.getElementById('aptitudes-container');
    const agregarAptitudBtn = document.getElementById('agregar-aptitud');

    const tiposIncidencia = <?= json_encode($tipos_incidencia) ?>;

    function actualizarVisibilidadAptitudes() {
        const seleccionado = selectDepartamento.options[selectDepartamento.selectedIndex].text.toLowerCase();
        if (seleccionado.includes('mantenimiento')) {
            seccionAptitudes.style.display = 'block';
            if (aptitudesContainer.children.length === 0) {
                agregarAptitud();
            }
        } else {
            seccionAptitudes.style.display = 'none';
            aptitudesContainer.innerHTML = '';
        }
    }

    function agregarAptitud() {
        const index = aptitudesContainer.children.length;
        const aptitudDiv = document.createElement('div');
        aptitudDiv.className = 'aptitud-item';
        aptitudDiv.innerHTML = `
            <select name="aptitudes[]" required>
                <option value="">Seleccione aptitud</option>
                ${tiposIncidencia.map(tipo => `<option value="${tipo}">${tipo}</option>`).join('')}
            </select>
            <select name="niveles_experiencia[]" required>
                <option value="basico">Básico</option>
                <option value="intermedio">Intermedio</option>
                <option value="avanzado">Avanzado</option>
            </select>
            <button type="button" class="remove-aptitud" ${index === 0 ? 'disabled' : ''}>✕</button>
        `;
        aptitudesContainer.appendChild(aptitudDiv);

        const removeBtn = aptitudDiv.querySelector('.remove-aptitud');
        removeBtn.addEventListener('click', function() {
            if (aptitudesContainer.children.length > 1) {
                aptitudDiv.remove();
            }
        });
    }

    agregarAptitudBtn.addEventListener('click', agregarAptitud);
    selectDepartamento.addEventListener('change', actualizarVisibilidadAptitudes);
    
    // Validaciones de teléfono
    const telefonoInput = document.querySelector('input[name="telefono_num"]');
    telefonoInput.addEventListener('keydown', function(e) {
        if (['e', 'E', '+', '-', '.'].includes(e.key)) {
            e.preventDefault();
        }
    });
    telefonoInput.addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, '').slice(0, 7);
    });

    // Validación para campo de cédula
const cedulaInput = document.getElementById('cedula');
cedulaInput.addEventListener('keydown', function(e) {
    // Prevenir letras y caracteres especiales
    if (['e', 'E', '+', '-', '.'].includes(e.key)) {
        e.preventDefault();
    }
});

cedulaInput.addEventListener('input', function() {
    // Eliminar cualquier caracter que no sea número
    this.value = this.value.replace(/\D/g, '');
    
    // Limitar a 8 dígitos
    if (this.value.length > 8) {
        this.value = this.value.slice(0, 8);
    }
});

    cedulaInput.addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, '');
    });

    // Auto-ocultar mensaje después de 5 segundos
    setTimeout(function() {
        const alert = document.querySelector('.alert');
        if (alert) {
            alert.style.display = 'none';
        }
    }, 5000);
});

// Validación para campo de nombre - SOLO LETRAS, MÁXIMO 15 CARACTERES
const nombreInput = document.getElementById('nombre');
nombreInput.addEventListener('keydown', function(e) {
    // Permitir teclas de control (backspace, tab, enter, etc.)
    const teclasPermitidas = ['Backspace', 'Tab', 'Enter', 'Delete', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Home', 'End'];
    if (teclasPermitidas.includes(e.key)) {
        return; // Permitir estas teclas sin validación
    }
    
    // Prevenir números y caracteres especiales (solo letras y espacios)
    if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]$/.test(e.key)) {
        e.preventDefault();
    }
});

nombreInput.addEventListener('input', function() {
    // Eliminar cualquier caracter que no sea letra o espacio
    this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
    
    // Limitar a 15 caracteres
    if (this.value.length > 15) {
        this.value = this.value.slice(0, 15);
    }
});

// Validación para campo de apellido - SOLO LETRAS, MÁXIMO 15 CARACTERES
const apellidoInput = document.getElementById('apellido');
apellidoInput.addEventListener('keydown', function(e) {
    // Permitir teclas de control
    const teclasPermitidas = ['Backspace', 'Tab', 'Enter', 'Delete', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Home', 'End'];
    if (teclasPermitidas.includes(e.key)) {
        return;
    }
    
    // Prevenir números y caracteres especiales
    if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]$/.test(e.key)) {
        e.preventDefault();
    }
});

apellidoInput.addEventListener('input', function() {
    // Eliminar cualquier caracter que no sea letra o espacio
    this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
    
    // Limitar a 15 caracteres
    if (this.value.length > 15) {
        this.value = this.value.slice(0, 15);
    }
});

// Validación adicional al enviar el formulario
document.querySelector("form").addEventListener("submit", function(e) {
    const nombre = document.getElementById('nombre').value;
    const apellido = document.getElementById('apellido').value;
    
    if (nombre.length < 2) {
        e.preventDefault();
        alert('El nombre debe tener al menos 2 letras');
        return;
    }
    
    if (apellido.length < 2) {
        e.preventDefault();
        alert('El apellido debe tener al menos 2 letras');
        return;
    }
});
</script>

<?php include_once "../pie.php"; ?>
</body>
</html>