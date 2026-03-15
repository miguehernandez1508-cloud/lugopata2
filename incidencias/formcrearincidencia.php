<?php
// /incidencias/formcrearincidencia.php - Formulario para crear nuevas incidencias
session_start();
include_once __DIR__ . "/../encabezado.php";
require_once __DIR__ . "/Incidencia.php";
require_once __DIR__ . "/../departamentos/departamento.php";
require_once __DIR__ . "/../user/gestorsesion.php";

// Valida que el usuario tenga sesión activa
GestorSesiones::iniciar();

$mensaje = "";
$exito = false;

// Obtener lista de departamentos para los select options
$departamentoObj = new Departamento($conexion);
$departamentos = $departamentoObj->listar(0,1000);  // Obtiene hasta 1000 departamentos

// Procesa el envío del formulario POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $incidencia = new Incidencia($conexion);
    // Asigna los valores del formulario al objeto incidencia
    $incidencia->fecha = $_POST['fecha'];
    $incidencia->departamento_emisor = $_POST['departamento_emisor'];
    $incidencia->departamento_receptor = $_POST['departamento_receptor'];
    $incidencia->descripcion = $_POST['descripcion'];
    $incidencia->prioridad = $_POST['prioridad'];
    $incidencia->ubicacion = $_POST['ubicacion'];
    
    // Obtiene el ID del usuario desde la sesión
    $idUsuario = GestorSesiones::get('id_usuario');
    
    // Valida que el usuario esté identificado
    if (!$idUsuario) {
        $mensaje = "Error: usuario no identificado. Reingresa sesión.";
    } else {
        $incidencia->id_firma_usuario = $idUsuario;
        $incidencia->id_trabajador_asignado = null;
        
        // Intenta crear la incidencia en la base de datos
        if ($incidencia->crear()) {
            $mensaje = "Incidencia registrada correctamente.";
            $exito = true;
        } else {
            $mensaje = "Error al registrar incidencia.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Crear Incidencia</title>
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
            max-width: 800px;
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

        /* SECCIONES DEL FORMULARIO */
        .form-section {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            border: 1px solid #e9ecef;
        }

        .form-section h3 {
            margin-top: 0;
            margin-bottom: 20px;
            color: #333;
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
        }

        .form-section h3 img {
            width: 28px;
            height: 28px;
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

        .required-field::after {
            content: " *";
            color: #dc2626;
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

        /* SELECT PERSONALIZADO */
        select {
            appearance: none;
            background-image: url('data:image/svg+xml;utf8,<svg fill="%23666" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><path d="M7 10l5 5 5-5z"/><path d="M0 0h24v24H0z" fill="none"/></svg>');
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 20px;
            padding-right: 40px;
        }

        /* CAMPOS EN LÍNEA */
        .inline-fields {
            display: flex;
            gap: 15px;
        }

        .inline-fields .form-group {
            flex: 1;
        }

        /* TEXTAREA */
        textarea {
            resize: vertical;
            min-height: 100px;
            font-family: inherit;
        }

        /* INFORMACIÓN AUXILIAR */
        .info-text {
            font-size: 12px;
            color: #6b7280;
            margin-top: 6px;
            font-style: italic;
        }

        /* PRIORIDAD */
        .prioridad-info {
            padding: 10px 15px;
            border-radius: 8px;
            margin-top: 10px;
            font-size: 13px;
            font-weight: 500;
            display: none;
        }

        .prioridad-urgente {
            background-color: #fee2e2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        .prioridad-moderada {
            background-color: #fef3c7;
            color: #d97706;
            border: 1px solid #fde68a;
        }

        .prioridad-leve {
            background-color: #d1fae5;
            color: #059669;
            border: 1px solid #a7f3d0;
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
            min-width: 180px;
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

            .form-section {
                padding: 15px;
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

            .form-section h3 {
                font-size: 15px;
            }
        }

        @media (min-width: 1200px) {
            .form-wrapper {
                max-width: 750px;
            }
        }
    </style>
</head>
<body>
<div class="form-wrapper">
    <!-- TARJETA DE TÍTULO -->
    <div class="title-card">
        <h1>
            <img src="../assets/resources/incidencia.png" alt="Incidencia">
            REGISTRAR NUEVA INCIDENCIA
        </h1>
        <p>Complete todos los campos requeridos para reportar una incidencia</p>
    </div>

    <!-- TARJETA DEL FORMULARIO -->
    <div class="form-card">
        
        <?php if($mensaje): ?>
            <div class="alert <?= $exito ? 'alert-success' : 'alert-error' ?>">
                <?= htmlspecialchars($mensaje) ?>
            </div>
        <?php endif; ?>

        <form method="post" action="" id="incidenciaForm">
            <!-- Sección de información básica -->
            <div class="form-section">
                <h3>
                    <img src="../assets/resources/info.png" alt="Información">
                    Información Básica
                </h3>
                
                <div class="form-group">
                    <label for="fecha" class="required-field">Fecha:</label>
                    <input type="date" name="fecha" id="fecha" required readonly>
                    <div class="info-text">Fecha en la que se detectó la incidencia</div>
                </div>
                
                <div class="form-group">
                    <label for="ubicacion" class="required-field">Ubicación:</label>
                    <input type="text" name="ubicacion" id="ubicacion" placeholder="Ej: Edificio A, Piso 2, Oficina 201" required>
                    <div class="info-text">Especifique la ubicación exacta donde se encuentra la incidencia</div>
                </div>
            </div>

            <!-- Sección de departamentos -->
            <div class="form-section">
                <h3>
                    <img src="../assets/resources/equipo.png" alt="Departamentos">
                    Departamentos Involucrados
                </h3>
                
                <div class="inline-fields">
                    <div class="form-group">
                        <label for="departamento_emisor" class="required-field">Departamento Emisor:</label>
                        <select name="departamento_emisor" id="departamento_emisor" required>
                            <option value="">Seleccione departamento</option>
                            <?php foreach($departamentos as $d): ?>
                                <option value="<?= $d->id_departamento ?>"><?= htmlspecialchars($d->nombre) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="info-text">Departamento que reporta la incidencia</div>
                    </div>
                    
<?php
// Buscar el ID del departamento de Mantenimiento
$stmt = $conexion->prepare("SELECT id_departamento FROM departamentos WHERE nombre LIKE '%Mantenimiento%' LIMIT 1");
$stmt->execute();
$deptoMantenimiento = $stmt->fetch(PDO::FETCH_ASSOC);
$idMantenimiento = $deptoMantenimiento ? $deptoMantenimiento['id_departamento'] : 1; // Fallback a 1 si no encuentra
?>

<div class="form-group">
    <label for="departamento_receptor" class="required-field">Departamento Receptor:</label>
    <select name="departamento_receptor" id="departamento_receptor" required disabled>
        <option value="<?= $idMantenimiento ?>" selected>Mantenimiento</option>
    </select>
    <input type="hidden" name="departamento_receptor" value="<?= $idMantenimiento ?>">
    <div class="info-text">Departamento responsable de resolver</div>
</div>
                </div>
            </div>

            <!-- Sección de descripción y prioridad -->
            <div class="form-section">
                <h3>
                    <img src="../assets/resources/informes.png" alt="Descripción">
                    Detalles de la Incidencia
                </h3>
                
                <div class="form-group">
                    <label for="descripcion" class="required-field">Descripción:</label>
                    <textarea name="descripcion" id="descripcion" rows="4" 
                        placeholder="Describa detalladamente la incidencia, incluya síntomas, efectos y cualquier información relevante..." 
                        required></textarea>
                    <div class="info-text">Sea lo más específico posible para una mejor atención</div>
                </div>
                
                <div class="form-group">
                    <label for="prioridad" class="required-field">Prioridad:</label>
                    <select name="prioridad" id="prioridad" required>
                        <option value="">Seleccione prioridad</option>
                        <option value="Urgente">Urgente - Alto impacto</option>
                        <option value="Moderada">Moderada - Impacto medio</option>
                        <option value="Leve">Leve - Bajo impacto</option>
                    </select>
                    
                    <div id="infoUrgente" class="prioridad-info prioridad-urgente">
                        <img src="../assets/resources/fuego2.png" alt="Urgente" style="width: 20px; height: 20px;"> Atención inmediata requerida - Impacta operaciones críticas
                    </div>
                    <div id="infoModerada" class="prioridad-info prioridad-moderada">
                        <img src="../assets/resources/warning.png" alt="Moderada" style="width: 20px; height: 20px;"> Resolución en las próximas 24-48 horas - Impacto moderado
                    </div>
                    <div id="infoLeve" class="prioridad-info prioridad-leve">
                        <img src="../assets/resources/mantenimient0.png" alt="Leve" style="width: 20px; height: 20px;"> Resolución en horario normal - Mínimo impacto operacional
                    </div>
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="btn-container">
                <button type="submit" class="btn btn-success">
                    <img src="../assets/resources/disco2.png" alt="Guardar">
                    Registrar Incidencia
                </button>
                <a href="/lugopata/dashboard.php" class="btn btn-primary">
                    <img src="../assets/resources/volver2.png" alt="Volver">
                    Regresar
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('incidenciaForm');
    const fechaInput = document.getElementById('fecha');
    const prioridadSelect = document.getElementById('prioridad');
    
    // Configurar fecha actual como valor por defecto
    const hoy = new Date().toISOString().split('T')[0];
    fechaInput.value = hoy;
    fechaInput.max = hoy;
    
    // Manejar cambio de prioridad
    prioridadSelect.addEventListener('change', function() {
        // Ocultar todos los mensajes
        document.querySelectorAll('.prioridad-info').forEach(el => {
            el.style.display = 'none';
        });
        
        // Mostrar el mensaje correspondiente
        const valor = this.value;
        if (valor === 'Urgente') {
            document.getElementById('infoUrgente').style.display = 'block';
        } else if (valor === 'Moderada') {
            document.getElementById('infoModerada').style.display = 'block';
        } else if (valor === 'Leve') {
            document.getElementById('infoLeve').style.display = 'block';
        }
    });
    
    // Validar formulario antes de enviar
    form.addEventListener('submit', function(e) {
        const descripcion = document.getElementById('descripcion').value.trim();
        const ubicacion = document.getElementById('ubicacion').value.trim();
        const deptoEmisor = document.getElementById('departamento_emisor').value;
        const deptoReceptor = document.getElementById('departamento_receptor').value;
        
        // Validar que los departamentos sean diferentes
        if (deptoEmisor === deptoReceptor && deptoEmisor !== '') {
            e.preventDefault();
            alert('Los departamentos emisor y receptor no pueden ser el mismo.');
            document.getElementById('departamento_receptor').focus();
            return false;
        }
        
        // Validar descripción mínima
        if (descripcion.length < 10) {
            e.preventDefault();
            alert('La descripción debe tener al menos 10 caracteres. Sea más específico.');
            document.getElementById('descripcion').focus();
            return false;
        }
        
        // Validar ubicación
        if (ubicacion.length < 5) {
            e.preventDefault();
            alert('Por favor, especifique una ubicación más detallada.');
            document.getElementById('ubicacion').focus();
            return false;
        }
        
        // Confirmar antes de enviar
        if (!confirm('¿Está seguro de registrar esta incidencia?\n\nUna vez enviada, será revisada por el equipo correspondiente.')) {
            e.preventDefault();
            return false;
        }
        
        return true;
    });
    
    // Auto-ocultar mensajes después de 5 segundos
    setTimeout(function() {
        const alert = document.querySelector('.alert');
        if (alert) {
            alert.style.display = 'none';
        }
    }, 5000);
});
</script>

<?php include_once __DIR__ . "/../pie.php"; ?>
</body>
</html>