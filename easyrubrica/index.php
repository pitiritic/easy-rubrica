<?php
/**
 * Easy Rúbrica - Index Principal
 */

ob_start(); 
session_start();
require_once 'config/db.php';

// --- GESTIÓN DE DESCARGA DIRECTA ---
if (isset($_GET['action']) && $_GET['action'] === 'descargar_plantilla_rubrica') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=plantilla_rubrica.csv');
    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8
    fputcsv($output, array('Nombre Rubrica', 'Descripcion', 'Asignatura', 'Competencias', 'Criterio', 'Nivel 1 (Insuficiente)', 'Nivel 2 (Aceptable)', 'Nivel 3 (Bueno)', 'Nivel 4 (Excelente)'));
    $ejemplos = array(
        array('Resolución de Problemas', 'Evaluación del proceso lógico', 'Matemáticas', 'Matemática,Científica', 'Comprensión del enunciado', 'No entiende el problema.', 'Entiende parte.', 'Identifica la mayoría.', 'Identifica todos los datos.'),
        array('Resolución de Problemas', 'Evaluación del proceso lógico', 'Matemáticas', 'Matemática,Científica', 'Estrategia de resolución', 'Sin plan claro.', 'Plan incompleto.', 'Estrategia lógica.', 'Estrategia eficiente.')
    );
    foreach ($ejemplos as $fila) { fputcsv($output, $fila); }
    fclose($output);
    exit;
}

$needsInstall = false;
try {
    $check = $pdo->query("SHOW TABLES LIKE 'usuarios'");
    if ($check && $check->rowCount() > 0) {
        $stmtAdmin = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol = 'admin'");
        if ($stmtAdmin->fetchColumn() == 0) { $needsInstall = true; }
    } else { $needsInstall = true; }
} catch (Exception $e) { $needsInstall = true; }

$currentUser = null;
if (!$needsInstall && isset($_SESSION['user_id'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $currentUser = $stmt->fetch();
    } catch (Exception $e) { $currentUser = null; }
}

$action = $_GET['action'] ?? 'home';

if ($needsInstall) {
    $action = 'install';
} elseif (!$currentUser && !in_array($action, ['login', 'install', 'recover'])) {
    header("Location: ?action=login");
    exit;
}

$is_dashboard = false;
switch ($action) {
    case 'install':              require 'controllers/install.php'; break;
    case 'login':                require 'controllers/auth.php'; break;
    case 'recover':              require 'controllers/recover.php'; break;
    case 'exportar_pdf':         require 'controllers/pdf_rubrica.php'; break; // Acción corregida
    case 'logout':               session_destroy(); header("Location: ?action=login"); exit;
    case 'usuarios':             require 'controllers/admin_users.php'; break;
    case 'gestion_clases_lista': require 'controllers/admin_classes.php'; break;
    case 'asignar_rubricas':     require 'controllers/admin.php'; break;
    case 'rubricas':             require 'controllers/rubricas.php'; break;
    case 'evaluar':              require 'controllers/evaluar.php'; break;
    case 'notas':                require 'controllers/notas.php'; break;
    case 'ajustes':              require 'controllers/ajustes.php'; break;
    case 'home':
    default:                     $is_dashboard = true; break;
}

$clean_content = ob_get_clean();

if (in_array($action, ['login', 'install', 'recover'])) {
    echo $clean_content;
} else {
    if (file_exists('views/layout/header.php')) { require 'views/layout/header.php'; }
    if ($is_dashboard) {
        $rol = $currentUser['rol'] ?? 'alumno';
        ?>
        <div class="container py-5">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Panel de Gestión</h2>
                <p class="text-muted">Bienvenido, <?= htmlspecialchars($currentUser['nombre'] ?? 'Usuario') ?>. Acceso rápido a las funciones de EasyRúbrica.</p>
            </div>
            <div class="row g-4 justify-content-center">
                <?php if($rol === 'admin'): ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <a href="?action=usuarios" class="text-decoration-none text-center d-block">
                        <div class="card h-100 shadow-sm border-0 p-4 hover-card border-top border-4" style="border-color: #ff8c00 !important;">
                            <i class="fa-solid fa-users fa-3x mb-3" style="color: #ff8c00;"></i>
                            <h5 class="text-dark fw-bold">Usuarios</h5>
                        </div>
                    </a>
                </div>
                <?php endif; ?>
                <?php if($rol !== 'alumno'): ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <a href="?action=gestion_clases_lista" class="text-decoration-none text-center d-block">
                        <div class="card h-100 shadow-sm border-0 p-4 hover-card border-top border-4" style="border-color: #198754 !important;">
                            <i class="fa-solid fa-chalkboard-user fa-3x mb-3" style="color: #198754;"></i>
                            <h5 class="text-dark fw-bold">Clases</h5>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-3">
                    <a href="?action=rubricas" class="text-decoration-none text-center d-block">
                        <div class="card h-100 shadow-sm border-0 p-4 hover-card border-top border-4" style="border-color: #d63384 !important;">
                            <i class="fa-solid fa-list fa-3x mb-3" style="color: #d63384;"></i>
                            <h5 class="text-dark fw-bold">Rúbricas</h5>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-3">
                    <a href="?action=asignar_rubricas" class="text-decoration-none text-center d-block">
                        <div class="card h-100 shadow-sm border-0 p-4 hover-card border-top border-4" style="border-color: #ffc107 !important;">
                            <i class="fa-solid fa-user-plus fa-3x mb-3" style="color: #ffc107;"></i>
                            <h5 class="text-dark fw-bold">Asignar</h5>
                        </div>
                    </a>
                </div>
                <?php endif; ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <a href="?action=evaluar" class="text-decoration-none text-center d-block">
                        <div class="card h-100 shadow-sm border-0 p-4 hover-card border-top border-4" style="border-color: #0dcaf0 !important;">
                            <i class="fa-solid fa-pen-to-square fa-3x mb-3" style="color: #0dcaf0;"></i>
                            <h5 class="text-dark fw-bold">Evaluar</h5>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-3">
                    <a href="?action=notas" class="text-decoration-none text-center d-block">
                        <div class="card h-100 shadow-sm border-0 p-4 hover-card border-top border-4" style="border-color: #6610f2 !important;">
                            <i class="fa-solid fa-graduation-cap fa-3x mb-3" style="color: #6610f2;"></i>
                            <h5 class="text-dark fw-bold">Notas</h5>
                        </div>
                    </a>
                </div>
                <?php if($rol === 'admin'): ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <a href="?action=ajustes" class="text-decoration-none text-center d-block">
                        <div class="card h-100 shadow-sm border-0 p-4 hover-card border-top border-4" style="border-color: #212529 !important;">
                            <i class="fa-solid fa-gear fa-3x mb-3" style="color: #212529;"></i>
                            <h5 class="text-dark fw-bold">Ajustes</h5>
                        </div>
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    } else { echo $clean_content; }
    if (file_exists('views/layout/footer.php')) { require 'views/layout/footer.php'; }
}
