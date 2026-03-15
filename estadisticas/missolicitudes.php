<?php 
// /misSolicitudes.php - Archivo para mostrar las solicitudes del usuario actual
session_start();
include_once "../encabezado.php";
require_once "../conex.php";
require_once "../user/gestorsesion.php";

GestorSesiones::iniciar();

$usuario_id = GestorSesiones::get('id_usuario');
$nivel_usuario = GestorSesiones::get('nivel');
$mensaje = "";

$sentencia_insumos = $conexion->prepare("
    SELECT sm.id_solicitud AS id, sm.fecha, sm.descripcion, 'Insumo' AS tipo, sm.estado
    FROM solicitud_materiales sm
    WHERE sm.emisor = (
        SELECT CONCAT(nombre,' ',apellido)
        FROM trabajadores
        WHERE id_trabajador = (
            SELECT id_trabajador FROM usuarios WHERE id_usuario = ?
        )
    )
");

$sentencia_incidencias = $conexion->prepare("
    SELECT i.id_incidencia AS id, i.fecha, i.descripcion, 'Incidencia' AS tipo, i.estado,
           i.id_firma_usuario, i.id_trabajador_asignado
    FROM incidencias i
    WHERE i.id_firma_usuario = ?
");

$sentencia_insumos->execute([$usuario_id]);
$solicitudes_insumos = $sentencia_insumos->fetchAll(PDO::FETCH_OBJ);

$sentencia_incidencias->execute([$usuario_id]);
$incidencias = $sentencia_incidencias->fetchAll(PDO::FETCH_OBJ);

$solicitudes = array_merge($solicitudes_insumos, $incidencias);

usort($solicitudes, function($a, $b) {
    return strtotime($b->fecha) - strtotime($a->fecha);
});
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
<title>Mis Solicitudes</title>
<style>
    * {
        box-sizing: border-box !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    body {
        background: url("/lugopata/assets/resources/fondoR.png") no-repeat center center fixed !important;
        background-size: cover !important;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
        padding: 15px !important;
        min-height: 100vh !important;
    }

    .solicitudes-wrapper {
        max-width: 1200px !important;
        margin: 0 auto !important;
        width: 100% !important;
    }

    .title-card {
            background: white !important;
            padding: 20px !important;
            border-radius: 15px !important;
            border: 2px solid #ccc !important;
            margin-bottom: 20px !important;
            width: 100% !important;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1) !important;
        }

        .title-card h1 {
            color: #333 !important;
            font-weight: bold !important;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.2) !important;
            margin: 0 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 15px !important;
            flex-wrap: wrap !important;
            font-size: clamp(1.3rem, 4vw, 2rem) !important;
            text-align: center !important;
        }

        .title-card h1 img {
            width: clamp(35px, 8vw, 45px) !important;
            height: auto !important;
        }

        .title-card p {
            color: #6c757d !important;
            margin-top: 10px !important;
            font-size: clamp(14px, 2vw, 16px) !important;
            text-align: center !important;
        }


    /* ===== BUSCADOR SIMPLE ===== */
    .search-box-simple {
        width: 100% !important;
        padding: 12px 15px 12px 40px !important;
        border: 1px solid #ccc !important;
        border-radius: 25px !important;
        font-size: 15px !important;
        margin-bottom: 15px !important;
        background: white url("../assets/resources/busqueda.png") no-repeat 15px center !important;
        background-size: 18px 18px !important;
        transition: border-color 0.2s !important;
    }

    .search-box-simple:focus {
        outline: none !important;
        border-color: #0d6efd !important;
    }

    .content-card {
        background: white !important;
        border-radius: 12px !important;
        padding: 20px !important;
        border: 1px solid #ddd !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
    }

    .contador-solicitudes {
        color: #666 !important;
        font-size: 14px !important;
        margin-bottom: 15px !important;
        display: block !important;
    }

    .mensaje-exito {
        background: #d4edda !important;
        color: #155724 !important;
        padding: 12px !important;
        border-radius: 8px !important;
        margin-bottom: 15px !important;
        text-align: center !important;
        font-size: 14px !important;
    }

    .mensaje-sin-datos {
        text-align: center !important;
        padding: 40px 20px !important;
        color: #666 !important;
    }

    .mensaje-sin-datos img {
        width: 60px !important;
        opacity: 0.4 !important;
        margin-bottom: 15px !important;
    }

    /* ===== VISTA MÓVIL (TARJETAS) ===== */
    .solicitudes-grid {
        display: grid !important;
        gap: 12px !important;
    }

    .solicitud-card {
        background: #fafafa !important;
        border: 1px solid #e0e0e0 !important;
        border-radius: 10px !important;
        padding: 15px !important;
        position: relative !important;
        transition: transform 0.2s, box-shadow 0.2s !important;
    }

    .solicitud-card:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
    }

    .solicitud-card::before {
        content: '' !important;
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        bottom: 0 !important;
        width: 4px !important;
        background: #0d6efd !important;
        border-radius: 10px 0 0 10px !important;
    }

    .solicitud-card.hidden {
        display: none !important;
    }

    .solicitud-header {
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        margin-bottom: 10px !important;
        flex-wrap: wrap !important;
        gap: 8px !important;
    }

    .solicitud-id {
        font-weight: bold !important;
        color: #333 !important;
        font-size: 16px !important;
    }

    .solicitud-fecha {
        font-size: 12px !important;
        color: #888 !important;
    }

    /* ===== COLORES NEUTROS (SIN ARCOÍRIS) ===== */
    .badge {
        display: inline-block !important;
        padding: 4px 10px !important;
        border-radius: 12px !important;
        font-size: 11px !important;
        font-weight: 600 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.3px !important;
    }

    .badge-tipo {
        background: #e9ecef !important;
        color: #495057 !important;
        border: 1px solid #dee2e6 !important;
    }

    .badge-estado {
        background: #f8f9fa !important;
        color: #495057 !important;
        border: 1px solid #dee2e6 !important;
    }

    /* Solo el estado activo tiene color sutil */
    .badge-estado.activo {
        background: #fff3cd !important;
        color: #856404 !important;
        border-color: #ffeaa7 !important;
    }

    .solicitud-descripcion {
        color: #555 !important;
        font-size: 14px !important;
        line-height: 1.4 !important;
        margin: 10px 0 !important;
        padding: 10px !important;
        background: white !important;
        border-radius: 6px !important;
        border: 1px solid #eee !important;
    }

    .solicitud-footer {
        margin-top: 12px !important;
        padding-top: 12px !important;
        border-top: 1px solid #e0e0e0 !important;
    }

    /* ===== ESTILOS DE BOTONES CON COLORES PERSONALIZADOS ===== */
    .btn {
        padding: 10px 16px !important;
        color: white !important;
        border: none !important;
        border-radius: 6px !important;
        cursor: pointer !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        text-decoration: none !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 6px !important;
        transition: background 0.2s !important;
    }

    .btn-ver {
        background: #0d6efd !important; /* Azul para "Ver" */
    }

    .btn-ver:hover {
        background: #0b5ed7 !important;
    }

    .btn-monitorear {
        background: #198754 !important; /* Verde para "Monitorear" */
    }

    .btn-monitorear:hover {
        background: #157347 !important;
    }

    .btn-icon {
        width: 16px !important;
        height: 16px !important;
    }

    /* ===== TABLA ESCRITORIO ===== */
    .table-container {
        display: none !important;
        width: 100% !important;
        overflow-x: auto !important;
        margin-top: 20px !important;
        border: 2px solid #dee2e6 !important;
        border-radius: 12px !important;
        background: white !important;
    }

    table {
        width: 100% !important;
        border-collapse: collapse !important;
        min-width: 1000px !important;
    }

    th, td {
        border: 1px solid #dee2e6 !important;
        padding: 12px !important;
        text-align: center !important;
        background-color: #fff !important;
        vertical-align: middle !important;
        font-size: 14px !important;
    }

    th {
        background-color: #cfe2ff !important;
        font-weight: bold !important;
        color: #333 !important;
        position: sticky !important;
        top: 0 !important;
        white-space: nowrap !important;
    }
    
    td {
        white-space: nowrap !important;
    }

    tr:hover {
        background-color: #e9ecef !important;
    }

    tbody tr:nth-child(even) {
        background-color: #f8f9fa !important;
    }

    tbody tr:hover {
        background-color: #e9ecef !important;
    }

    tr.hidden {
        display: none !important;
    }

    .no-results {
        text-align: center !important;
        padding: 30px !important;
        color: #666 !important;
        display: none !important;
    }

    .no-results.show {
        display: block !important;
    }

    /* ===== RESUMEN LIMPIO ===== */
    .resumen-container {
        margin-top: 20px !important;
        padding: 15px !important;
        background: #f8f9fa !important;
        border-radius: 10px !important;
        border: 1px solid #e0e0e0 !important;
    }

    .resumen-container h4 {
        color: #555 !important;
        margin-bottom: 12px !important;
        font-size: 14px !important;
        font-weight: 600 !important;
    }

    .resumen-flex {
        display: flex !important;
        gap: 8px !important;
        flex-wrap: wrap !important;
    }

    .resumen-item {
        font-size: 13px !important;
        color: #666 !important;
        padding: 6px 12px !important;
        background: white !important;
        border-radius: 15px !important;
        border: 1px solid #ddd !important;
    }

    .resumen-item strong {
        color: #333 !important;
    }

    .boton-regresar-container {
        text-align: center !important;
        margin-top: 25px !important;
        padding-top: 20px !important;
        border-top: 1px solid #e0e0e0 !important;
    }

    .btn-regresar {
        max-width: 200px !important;
        background: #6c757d !important;
    }

    .btn-regresar:hover {
        background: #5a6268 !important;
    }

    /* Ajuste para botón en tabla */
    .table-container .btn {
        width: auto !important;
        padding: 6px 12px !important;
        font-size: 12px !important;
    }

    /* ===== RESPONSIVE ===== */
    @media (min-width: 768px) {
        body {
            padding: 20px !important;
        }

        .solicitudes-grid {
            display: none !important;
        }

        .table-container {
            display: block !important;
        }

        .btn {
            width: auto !important;
        }

        .search-box-simple {
            max-width: 400px !important;
        }
    }

    @media (max-width: 480px) {
        .title-card {
            padding: 15px !important;
        }

        .content-card {
            padding: 15px !important;
        }

        .solicitud-header {
            flex-direction: column !important;
            align-items: flex-start !important;
        }
    }
</style>
</head>
<body>
    <div class="solicitudes-wrapper">
        <div class="title-card">
            <h1>
                <img src="../assets/resources/ssolicitudi.png" alt="Mis Solicitudes">
                MIS SOLICITUDES
            </h1>
            <p>Historial de solicitudes e incidencias</p>
        </div>

        <div class="content-card">
            <?php if($mensaje): ?>
                <div class="mensaje-exito"><?= htmlspecialchars($mensaje) ?></div>
            <?php endif; ?>
            
            <span class="contador-solicitudes">
                Total: <strong id="contador-total"><?= count($solicitudes) ?></strong> solicitudes
            </span>

            <?php if(count($solicitudes) === 0): ?>
                <div class="mensaje-sin-datos">
                    <img src="../assets/resources/vacio.png" alt="Sin datos">
                    <p>No tienes solicitudes registradas</p>
                </div>
            <?php else: ?>
                
                <!-- Buscador simple -->
                <input type="text" 
                       id="buscador" 
                       class="search-box-simple" 
                       placeholder="Buscar por ID, tipo, estado o descripción..."
                       autocomplete="off">

                <div class="no-results" id="no-results">
                    <p>No se encontraron resultados</p>
                </div>

                <!-- Vista Móvil -->
                <div class="solicitudes-grid" id="grid-view">
                    <?php foreach($solicitudes as $sol): 
                        $activo = ($sol->estado === 'Pendiente' || $sol->estado === 'En espera') ? 'activo' : '';
                        
                        $boton_texto = ($sol->tipo === 'Insumo') ? "Ver Detalle" : "Monitorear";
                        $boton_url = ($sol->tipo === 'Insumo') 
                            ? "detallesolicitud.php?id={$sol->id}&tipo=Insumo" 
                            : "/lugopata/incidencias/seguimientofases.php?id={$sol->id}";
                        $boton_icono = ($sol->tipo === 'Insumo') 
                            ? "../assets/resources/ojo.png" 
                            : "../assets/resources/monitor-de-tablero.png";
                        $boton_clase = ($sol->tipo === 'Insumo') ? 'btn-ver' : 'btn-monitorear';
                    ?>
                    <div class="solicitud-card" 
                         data-id="<?= $sol->id ?>" 
                         data-tipo="<?= strtolower($sol->tipo) ?>" 
                         data-estado="<?= strtolower($sol->estado) ?>"
                         data-texto="<?= strtolower($sol->id . ' ' . $sol->tipo . ' ' . $sol->estado . ' ' . $sol->descripcion) ?>">
                        
                        <div class="solicitud-header">
                            <div>
                                <div class="solicitud-id">#<?= $sol->id ?></div>
                                <div style="margin-top: 6px;">
                                    <span class="badge badge-tipo"><?= $sol->tipo ?></span>
                                    <span class="badge badge-estado <?= $activo ?>"><?= $sol->estado ?></span>
                                </div>
                            </div>
                            <div class="solicitud-fecha"><?= date('d/m/Y', strtotime($sol->fecha)) ?></div>
                        </div>
                        
                        <div class="solicitud-descripcion">
                            <?= htmlspecialchars($sol->descripcion) ?>
                        </div>
                        
                        <div class="solicitud-footer">
                            <a href="<?= $boton_url ?>" class="btn <?= $boton_clase ?>">
                                <img src="<?= $boton_icono ?>" alt="" class="btn-icon">
                                <?= $boton_texto ?>
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Vista Escritorio -->
                <div class="table-container">
                    <table id="tabla-solicitudes">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Fecha</th>
                                <th>Tipo</th>
                                <th>Estado</th>
                                <th>Descripción</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($solicitudes as $sol): 
                                $activo = ($sol->estado === 'Pendiente' || $sol->estado === 'En espera') ? 'activo' : '';
                                
                                $boton_texto = ($sol->tipo === 'Insumo') ? "Ver" : "Monitorear";
                                $boton_url = ($sol->tipo === 'Insumo') 
                                    ? "detallesolicitud.php?id={$sol->id}&tipo=Insumo" 
                                    : "/lugopata/incidencias/seguimientofases.php?id={$sol->id}";
                                $boton_icono = ($sol->tipo === 'Insumo') 
                                    ? "../assets/resources/ojo.png" 
                                    : "../assets/resources/monitor-de-tablero.png";
                                $boton_clase = ($sol->tipo === 'Insumo') ? 'btn-ver' : 'btn-monitorear';
                            ?>
                            <tr data-id="<?= $sol->id ?>" 
                                data-tipo="<?= strtolower($sol->tipo) ?>" 
                                data-estado="<?= strtolower($sol->estado) ?>"
                                data-texto="<?= strtolower($sol->id . ' ' . $sol->tipo . ' ' . $sol->estado . ' ' . $sol->descripcion) ?>">
                                <td><strong>#<?= $sol->id ?></strong></td>
                                <td><?= date('d/m/Y', strtotime($sol->fecha)) ?></td>
                                <td><span class="badge badge-tipo"><?= $sol->tipo ?></span></td>
                                <td><span class="badge badge-estado <?= $activo ?>"><?= $sol->estado ?></span></td>
                                <td><?= htmlspecialchars(substr($sol->descripcion, 0, 60)) ?><?= strlen($sol->descripcion) > 60 ? '...' : '' ?></td>
                                <td>
                                    <a href="<?= $boton_url ?>" class="btn <?= $boton_clase ?>" style="display: inline-flex; align-items: center; gap: 5px;">
                                        <img src="<?= $boton_icono ?>" alt="" style="width: 14px; height: 14px;">
                                        <?= $boton_texto ?>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Resumen limpio -->
                <div class="resumen-container">
                    <h4>Resumen</h4>
                    <div class="resumen-flex" id="resumen-estados">
                        <?php
                            $conteos = ['Finalizada' => 0, 'Pendiente' => 0, 'En espera' => 0, 'Rechazada' => 0, 'Insumo' => 0, 'Incidencia' => 0];
                            foreach($solicitudes as $sol) {
                                if(isset($conteos[$sol->estado])) $conteos[$sol->estado]++;
                                if(isset($conteos[$sol->tipo])) $conteos[$sol->tipo]++;
                            }
                        ?>
                        <span class="resumen-item">Insumos: <strong><?= $conteos['Insumo'] ?></strong></span>
                        <span class="resumen-item">Incidencias: <strong><?= $conteos['Incidencia'] ?></strong></span>
                        <span class="resumen-item">Pendientes: <strong><?= $conteos['Pendiente'] + $conteos['En espera'] ?></strong></span>
                        <span class="resumen-item">Finalizadas: <strong><?= $conteos['Finalizada'] ?></strong></span>
                    </div>
                </div>
            <?php endif; ?>

            <div class="boton-regresar-container">
                <button type="button" onclick="history.back()" class="btn btn-regresar">
                    <img src="../assets/resources/volver2.png" alt="" class="btn-icon">
                    Regresar
                </button>
            </div>
        </div>
    </div>

    <script>
    document.getElementById('buscador').addEventListener('input', function() {
        const termino = this.value.toLowerCase().trim();
        const cards = document.querySelectorAll('.solicitud-card');
        const rows = document.querySelectorAll('#tabla-solicitudes tbody tr');
        const noResults = document.getElementById('no-results');
        const contador = document.getElementById('contador-total');
        
        let visibles = 0;

        // Filtrar tarjetas móvil
        cards.forEach(card => {
            const texto = card.getAttribute('data-texto');
            if (texto.includes(termino)) {
                card.classList.remove('hidden');
                visibles++;
            } else {
                card.classList.add('hidden');
            }
        });

        // Filtrar tabla escritorio
        rows.forEach(row => {
            const texto = row.getAttribute('data-texto');
            if (texto.includes(termino)) {
                row.classList.remove('hidden');
            } else {
                row.classList.add('hidden');
            }
        });

        // Mostrar/ocultar mensaje sin resultados
        noResults.classList.toggle('show', visibles === 0 && termino !== '');
        
        // Actualizar contador
        if (contador) contador.textContent = visibles;
    });
    </script>
</body>
</html>