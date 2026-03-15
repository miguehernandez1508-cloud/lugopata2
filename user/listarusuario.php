<?php
// /user/listarusuario.php
session_start();
include_once __DIR__ . "/../encabezado.php";
require_once __DIR__ . "/gestorsesion.php";

GestorSesiones::iniciar();

// Verificar permisos
$nivel_usuario = GestorSesiones::get('nivel');
$niveles_permitidos = ['admin', 'sistemas', 'superadministrador'];

if (!in_array($nivel_usuario, $niveles_permitidos)) {
    header("Location: /lugopata/dashboard.php");
    exit;
}

// Configuración de paginación
$registrosPorPagina = 10;
$pagina = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$inicio = ($pagina - 1) * $registrosPorPagina;

// Filtros
$filtro_nivel = isset($_GET['nivel']) ? trim($_GET['nivel']) : 'todos';
$filtro_estado = isset($_GET['estado']) ? trim($_GET['estado']) : 'todos';

// Obtener usuarios
$sql = "SELECT u.*, t.nombre, t.apellido, t.cedula, d.nombre as departamento
        FROM usuarios u 
        JOIN trabajadores t ON u.id_trabajador = t.id_trabajador 
        LEFT JOIN departamentos d ON t.id_departamento = d.id_departamento";

$where = [];
$params = [];
$types = [];

if ($filtro_nivel !== 'todos') {
    $where[] = "u.nivel = ?";
    $params[] = $filtro_nivel;
    $types[] = PDO::PARAM_STR;
}

if ($filtro_estado !== 'todos') {
    $where[] = "u.bloqueado = ?";
    $params[] = ($filtro_estado === 'bloqueado') ? 1 : 0;
    $types[] = PDO::PARAM_INT;
}

if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY u.id_usuario DESC";

// Contar total
$sql_count = "SELECT COUNT(*) as total FROM usuarios u" . 
             (!empty($where) ? " WHERE " . implode(" AND ", $where) : "");
$stmt_count = $conexion->prepare($sql_count);
$stmt_count->execute($params);
$total = $stmt_count->fetch(PDO::FETCH_OBJ)->total;

// Obtener datos paginados
$sql .= " LIMIT ? OFFSET ?";
$params[] = $registrosPorPagina;
$params[] = $inicio;
$types[] = PDO::PARAM_INT;
$types[] = PDO::PARAM_INT;

$stmt = $conexion->prepare($sql);

foreach ($params as $i => $param) {
    $type = isset($types[$i]) ? $types[$i] : PDO::PARAM_STR;
    $stmt->bindValue($i + 1, $param, $type);
}

$stmt->execute();
$usuarios = $stmt->fetchAll(PDO::FETCH_OBJ);

$totalPaginas = ceil($total / $registrosPorPagina);

// Obtener niveles únicos para el filtro
$niveles = $conexion->query("SELECT DISTINCT nivel FROM usuarios ORDER BY nivel")->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
<title>Listado de Usuarios</title>
<style>
    /* ===== RESET Y VARIABLES ===== */
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

    .usuarios-wrapper {
        max-width: 1400px !important;
        margin: 0 auto !important;
        width: 100% !important;
    }

    /* ===== TARJETA DE TÍTULO ===== */
   .title-card {
        background: white !important;
        padding: 20px !important;
        border-radius: 15px !important;
        border: 2px solid #ccc !important;
        margin-bottom: 20px !important;
        width: 100% !important;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1) !important;
        text-align: center !important;
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
    }

    .title-card h1 img {
        width: clamp(35px, 8vw, 45px) !important;
        height: auto !important;
    }

    .title-card p {
        color: #6c757d !important;
        margin-top: 10px !important;
        font-size: clamp(14px, 2vw, 16px) !important;
    }

    /* ===== BOTONES MINIMALISTA FLAT ===== */
    .btn {
        padding: 10px 20px !important;
        border: none !important;
        border-radius: 6px !important;
        font-size: 13px !important;
        font-weight: 500 !important;
        text-decoration: none !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 8px !important;
        cursor: pointer !important;
        transition: all 0.2s ease !important;
        background: #495057 !important;
        color: white !important;
    }

    .btn:hover {
        background: #343a40 !important;
        transform: translateY(-1px) !important;
    }

    .btn-primary {
        background: #0d6efd !important;
    }
    .btn-primary:hover {
        background: #0b5ed7 !important;
    }

    .btn-success {
        background: #198754 !important;
    }
    .btn-success:hover {
        background: #157347 !important;
    }

    .btn-warning {
        background: #ffc107 !important;
        color: #212529 !important;
    }
    .btn-warning:hover {
        background: #e0a800 !important;
    }

    .btn-danger {
        background: #dc3545 !important;
    }
    .btn-danger:hover {
        background: #bb2d3b !important;
    }

    .btn-sm {
        padding: 6px 12px !important;
        font-size: 12px !important;
    }

    .btn-icon {
        width: 16px !important;
        height: 16px !important;
        flex-shrink: 0 !important;
    }

    /* ===== TARJETA DE CONTENIDO ===== */
    .content-card {
        background: white !important;
        border-radius: 12px !important;
        padding: 20px !important;
        border: 1px solid #ddd !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
    }

    /* ===== FILTROS ===== */
    .filtros-container {
        margin-bottom: 20px !important;
        padding-bottom: 20px !important;
        border-bottom: 1px solid #e0e0e0 !important;
    }

    .filtro-group {
        display: flex !important;
        flex-direction: column !important;
        gap: 8px !important;
        max-width: 400px !important;
        margin: 0 auto !important;
    }

    .filtro-group label {
        font-weight: 600 !important;
        color: #333 !important;
        font-size: 14px !important;
    }

    .filtro-group select {
        padding: 10px 15px !important;
        border: 1px solid #ccc !important;
        border-radius: 6px !important;
        font-size: 14px !important;
        background: white !important;
        cursor: pointer !important;
        width: 100% !important;
    }

    /* ===== GRID DE TARJETAS (VISTA MÓVIL) ===== */
    .usuarios-grid {
        display: grid !important;
        grid-template-columns: 1fr !important;
        gap: 12px !important;
        margin-top: 20px !important;
    }

    .usuario-card {
        background: #fafafa !important;
        border: 1px solid #e0e0e0 !important;
        border-radius: 10px !important;
        padding: 15px !important;
        position: relative !important;
        transition: all 0.2s ease !important;
    }

    .usuario-card:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
        border-color: #0d6efd !important;
    }

    .usuario-card::before {
        content: '' !important;
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        bottom: 0 !important;
        width: 4px !important;
        background: #0d6efd !important;
        border-radius: 10px 0 0 10px !important;
    }

    .usuario-header {
        display: flex !important;
        justify-content: space-between !important;
        align-items: flex-start !important;
        margin-bottom: 12px !important;
        flex-wrap: wrap !important;
        gap: 8px !important;
    }

    .usuario-nombre {
        font-size: 18px !important;
        font-weight: 600 !important;
        color: #333 !important;
    }

    .usuario-id {
        font-size: 12px !important;
        color: #888 !important;
        background: white !important;
        padding: 4px 10px !important;
        border-radius: 15px !important;
        border: 1px solid #e0e0e0 !important;
        font-family: monospace !important;
    }

    .usuario-body {
        margin-bottom: 12px !important;
    }

    .info-row {
        display: flex !important;
        justify-content: space-between !important;
        padding: 6px 0 !important;
        border-bottom: 1px solid #eee !important;
        flex-wrap: wrap !important;
        gap: 4px !important;
        font-size: 13px !important;
    }

    .info-row:last-child {
        border-bottom: none !important;
    }

    .info-label {
        font-weight: 600 !important;
        color: #555 !important;
    }

    .info-value {
        color: #333 !important;
        text-align: right !important;
        word-break: break-word !important;
        max-width: 60% !important;
    }

    .usuario-footer {
        margin-top: 12px !important;
        padding-top: 12px !important;
        border-top: 1px solid #e0e0e0 !important;
    }

    /* ===== ESTADOS ===== */
    .estado-badge {
        display: inline-block !important;
        padding: 4px 10px !important;
        border-radius: 12px !important;
        font-size: 11px !important;
        font-weight: 600 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.3px !important;
    }

    .estado-activo {
        background: #d1e7dd !important;
        color: #0f5132 !important;
        border: 1px solid #badbcc !important;
    }

    .estado-bloqueado {
        background: #f8d7da !important;
        color: #842029 !important;
        border: 1px solid #f5c2c7 !important;
    }

    /* ===== TABLA (VISTA ESCRITORIO) ===== */
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

    /* Columnas de texto largo en tabla */
    td:nth-child(6),
    td:nth-child(8) {
        max-width: 200px !important;
        min-width: 120px !important;
        word-wrap: break-word !important;
        overflow-wrap: break-word !important;
        text-align: left !important;
        white-space: normal !important;
    }

    tbody tr:nth-child(even) {
        background-color: #f8f9fa !important;
    }

    tbody tr:hover {
        background-color: #e9ecef !important;
    }

    /* ===== PAGINACIÓN ===== */
    .paginacion-container {
        margin-top: 25px !important;
        padding-top: 20px !important;
        border-top: 1px solid #e0e0e0 !important;
        text-align: center !important;
    }

    .paginacion-flex {
        display: flex !important;
        justify-content: center !important;
        align-items: center !important;
        flex-wrap: wrap !important;
        gap: 10px !important;
    }

    .paginacion-info {
        color: #666 !important;
        font-size: 14px !important;
        margin: 0 10px !important;
    }

    .btn-paginacion {
        padding: 8px 16px !important;
        background: #f8f9fa !important;
        color: #495057 !important;
        text-decoration: none !important;
        border-radius: 6px !important;
        font-weight: 500 !important;
        font-size: 13px !important;
        border: 1px solid #dee2e6 !important;
        cursor: pointer !important;
        transition: all 0.2s ease !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 5px !important;
    }

    .btn-paginacion:hover {
        background: #e9ecef !important;
        border-color: #adb5bd !important;
    }

    .btn-paginacion.disabled {
        background: #f8f9fa !important;
        color: #adb5bd !important;
        pointer-events: none !important;
        border-color: #e9ecef !important;
    }

    /* ===== MENSAJE SIN DATOS ===== */
    .mensaje-sin-datos {
        text-align: center !important;
        padding: 40px 20px !important;
        color: #666 !important;
        background: #f8f9fa !important;
        border-radius: 10px !important;
        border: 1px dashed #dee2e6 !important;
        margin: 20px 0 !important;
    }

    .mensaje-sin-datos h3 {
        margin-bottom: 10px !important;
        color: #495057 !important;
    }

    /* ===== MEDIA QUERIES ===== */
    @media (min-width: 768px) {
        body {
            padding: 20px !important;
        }

        .usuarios-grid {
            display: none !important;
        }

        .table-container {
            display: block !important;
        }

        .filtro-group {
            flex-direction: row !important;
            align-items: center !important;
        }

        .filtro-group select {
            width: auto !important;
            min-width: 250px !important;
        }

        .btn {
            width: auto !important;
        }
    }

    @media (min-width: 1024px) {
        .usuarios-wrapper {
            max-width: 1600px !important;
        }

        .content-card {
            padding: 30px !important;
        }
    }

    @media (max-width: 480px) {
        body {
            padding: 10px !important;
        }

        .title-card {
            padding: 15px !important;
        }

        .content-card {
            padding: 15px !important;
        }

        .usuario-card {
            padding: 12px !important;
        }

        .usuario-header {
            flex-direction: column !important;
            align-items: flex-start !important;
        }

        .info-row {
            flex-direction: column !important;
            align-items: flex-start !important;
        }

        .info-value {
            max-width: 100% !important;
            text-align: left !important;
        }

        .paginacion-flex {
            flex-direction: column !important;
            width: 100% !important;
        }

        .btn-paginacion {
            width: 100% !important;
            justify-content: center !important;
        }

        .btn {
            width: 100% !important;
            justify-content: center !important;
        }
    }
</style>
</head>
<body>
    <div class="usuarios-wrapper">
        <!-- Título -->
        <div class="title-card">
            <h1>
                <img src="../assets/resources/inicio1.png" alt="Usuarios">
            LISTADO DE USUARIOS
            </h1>
            <p>Gestión de usuarios del sistema</p>
            
            <div style="margin-top: 15px;">
                <a href="formcrearusuario.php" class="btn btn-success">
                     <img src="../assets/resources/maso.png" alt="Agregar" class="btn-icon" style="filter: brightness(0) invert(0.8) !important;">
                    Agregar Usuario
                </a>
            </div>
        </div>

        <!-- Contenido -->
        <div class="content-card">
            <!-- Filtros -->
            <div class="filtros-container">
                <form method="get" class="filtro-group">
                    <label for="nivel">Nivel:</label>
                    <select name="nivel" id="nivel" onchange="this.form.submit()">
                        <option value="todos" <?= $filtro_nivel === 'todos' ? 'selected' : '' ?>>Todos los niveles</option>
                        <?php foreach ($niveles as $nivel): ?>
                            <option value="<?= $nivel ?>" <?= $filtro_nivel === $nivel ? 'selected' : '' ?>>
                                <?= ucfirst($nivel) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
                
                <form method="get" class="filtro-group" style="margin-top: 15px;">
                    <label for="estado">Estado:</label>
                    <select name="estado" id="estado" onchange="this.form.submit()">
                        <option value="todos" <?= $filtro_estado === 'todos' ? 'selected' : '' ?>>Todos</option>
                        <option value="activo" <?= $filtro_estado === 'activo' ? 'selected' : '' ?>>Activos</option>
                        <option value="bloqueado" <?= $filtro_estado === 'bloqueado' ? 'selected' : '' ?>>Bloqueados</option>
                    </select>
                </form>
            </div>

            <?php if (empty($usuarios)): ?>
                <div class="mensaje-sin-datos">
                    <h3>No hay usuarios para mostrar</h3>
                    <p>Intenta ajustar los filtros o agrega nuevos usuarios.</p>
                </div>
            <?php else: ?>
                <!-- Vista Grid para Móvil -->
                <div class="usuarios-grid">
                    <?php foreach ($usuarios as $u): ?>
                    <div class="usuario-card">
                        <div class="usuario-header">
                            <div>
                                <div class="usuario-nombre"><?= htmlspecialchars($u->nombre . ' ' . $u->apellido) ?></div>
                                <div style="margin-top: 4px; color: #666; font-size: 13px;">
                                    <?= htmlspecialchars($u->username) ?> • <?= ucfirst($u->nivel) ?>
                                </div>
                            </div>
                            <div class="usuario-id">#<?= $u->id_usuario ?></div>
                        </div>
                        
                        <div class="usuario-body">
                            <div class="info-row">
                                <span class="info-label">Email:</span>
                                <span class="info-value"><?= htmlspecialchars($u->email) ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Departamento:</span>
                                <span class="info-value"><?= $u->departamento ?: 'N/A' ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Estado:</span>
                                <span class="info-value">
                                    <?php if ($u->bloqueado): ?>
                                        <span class="estado-badge estado-bloqueado">Bloqueado</span>
                                    <?php else: ?>
                                        <span class="estado-badge estado-activo">Activo</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="usuario-footer">
                            <div style="display: flex; gap: 8px;">
                                <a href="editarusuario.php?id_usuario=<?= $u->id_usuario ?>" class="btn btn-warning" style="flex: 1;">
                                      <img src="/lugopata/assets/resources/editarU.png" alt="Editar" class="btn-icon">
                                        Editar
                                </a>
                                <?php if ($u->username !== 'superadmin'): ?>
                                    <a href="eliminarusuario.php?id_usuario=<?= $u->id_usuario ?>" 
                                       class="btn btn-danger" 
                                       style="flex: 1;"
                                       onclick="return confirm('¿Está seguro de eliminar este usuario?')">
                                        <img src="/lugopata/assets/resources/eliminar2.png" alt="Eliminar" class="btn-icon" style="filter: brightness(0) invert(1) !important;">
                                        Eliminar
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Vista Tabla para Escritorio -->
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Usuario</th>
                                <th>Email</th>
                                <th>Nivel</th>
                                <th>Trabajador</th>
                                <th>Departamento</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $u): ?>
                            <tr>
                                <td><strong>#<?= $u->id_usuario ?></strong></td>
                                <td><?= htmlspecialchars($u->username) ?></td>
                                <td><?= htmlspecialchars($u->email) ?></td>
                                <td><?= ucfirst($u->nivel) ?></td>
                                <td><?= htmlspecialchars($u->nombre . ' ' . $u->apellido) ?></td>
                                <td><?= $u->departamento ?: 'N/A' ?></td>
                                <td>
                                    <?php if ($u->bloqueado): ?>
                                        <span class="estado-badge estado-bloqueado">Bloqueado</span>
                                    <?php else: ?>
                                        <span class="estado-badge estado-activo">Activo</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 6px;">
                                        <a href="editarusuario.php?id_usuario=<?= $u->id_usuario ?>" class="btn btn-warning btn-sm">
                                            <img src="/lugopata/assets/resources/editarU.png" alt="Editar" class="btn-icon">
                                            Editar
                                        </a>
                                        <?php if ($u->username !== 'superadmin'): ?>
                                            <a href="eliminarusuario.php?id_usuario=<?= $u->id_usuario ?>" 
                                               class="btn btn-danger btn-sm"
                                               onclick="return confirm('¿Está seguro de eliminar este usuario?')">
                                                <img src="/lugopata/assets/resources/eliminar2.png" alt="Eliminar" class="btn-icon" style="filter: brightness(0) invert(1) !important;">
                                                Eliminar
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <!-- Paginación -->
            <?php if ($totalPaginas > 1): ?>
            <div class="paginacion-container">
                <div class="paginacion-flex">
                    <?php if ($pagina > 1): ?>
                        <a href="?pagina=<?= $pagina - 1 ?>&nivel=<?= urlencode($filtro_nivel) ?>&estado=<?= urlencode($filtro_estado) ?>" class="btn-paginacion">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="15 18 9 12 15 6"></polyline>
                            </svg>
                            Anterior
                        </a>
                    <?php else: ?>
                        <span class="btn-paginacion disabled">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="15 18 9 12 15 6"></polyline>
                            </svg>
                            Anterior
                        </span>
                    <?php endif; ?>
                    
                    <span class="paginacion-info">
                        Página <?= $pagina ?> de <?= max(1, $totalPaginas) ?>
                    </span>
                    
                    <?php if ($pagina < $totalPaginas): ?>
                        <a href="?pagina=<?= $pagina + 1 ?>&nivel=<?= urlencode($filtro_nivel) ?>&estado=<?= urlencode($filtro_estado) ?>" class="btn-paginacion">
                            Siguiente
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </a>
                    <?php else: ?>
                        <span class="btn-paginacion disabled">
                            Siguiente
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </span>
                    <?php endif; ?>
                </div>
                <div class="paginacion-info" style="margin-top: 10px; font-size: 12px; color: #888;">
                    Mostrando <?= (($pagina - 1) * $registrosPorPagina) + 1 ?> - <?= min($pagina * $registrosPorPagina, $total) ?> de <?= $total ?> usuarios
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>