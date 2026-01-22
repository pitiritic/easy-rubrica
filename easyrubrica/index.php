<?php
/**
 * Easy Rúbrica - Index Principal con Migración Silenciosa
 */

ob_start(); 
session_start();

// 1. CARGA DE CONFIGURACIÓN
require_once 'config/db.php';

// 2. PROTECCIÓN ANTI-NULL
if (!isset($pdo)) {
    if (isset($db)) { 
        $pdo = $db; 
    } else {
        die("<div style='font-family:sans-serif; padding:50px; text-align:center;'>
                <h2>⚠️ Error de Configuración</h2>
                <p>No se detecta la conexión en <b>config/db.php</b>.</p>
             </div>");
    }
}

// --- GESTIÓN DE DESCARGA DIRECTA ---
if (isset($_GET['action']) && $_GET['action'] === 'descargar_plantilla_rubrica') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=plantilla_rubrica.csv');
    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    fputcsv($output, array('Nombre Rubrica', 'Descripcion', 'Asignatura', 'Competencias', 'Criterio', 'Nivel 1 (Insuficiente)', 'Nivel 2 (Aceptable)', 'Nivel 3 (Bueno)', 'Nivel 4 (Excelente)'));
    fclose($output);
    exit;
}

// 3. DETECCIÓN DE INSTALACIÓN Y MIGRACIÓN SILENCIOSA
$needsInstall = false;
try {
    $check = $pdo->query("SELECT 1 FROM usuarios LIMIT 1");
    
    if ($check) {
        $stmtAdmin = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol = 'admin'");
        if ($stmtAdmin->fetchColumn() == 0) { 
            $needsInstall = true; 
        } else {
            // --- INICIO MIGRACIÓN SILENCIOSA ---
            $version_requerida = 2; // Incrementa este número al añadir tablas nuevas
            try {
                $v_db = $pdo->query("SELECT valor FROM ajustes WHERE clave = 'db_version'")->fetchColumn();
            } catch (Exception $e) { $v_db = 0; }

            if ($v_db < $version_requerida) {
                require_once 'controllers/install.php';
                actualizarEsquemaSilencioso($pdo, $v_db, $version_requerida);
            }
            // --- FIN MIGRACIÓN SILENCIOSA ---
        }
    } else { 
        $needsInstall = true; 
    }
} catch (Exception $e) { 
    $needsInstall = true; 
}

// 4. CARGA DE USUARIO ACTUAL
$currentUser = null;
if (!$needsInstall && isset($_SESSION['user_id'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $currentUser = $stmt->fetch();
    } catch (Exception $e) { $currentUser = null; }
}

// 5. ENRUTAMIENTO
$action = $_GET['action'] ?? 'home';

if ($needsInstall) {
    $action = 'install';
} elseif (!$currentUser && !in_array($action, ['login', 'install', 'recover'])) {
    header("Location: ?action=login");
    exit;
}

$is_dashboard = false;
switch ($action) {
    case 'install':               require 'controllers/install.php'; break;
    case 'login':                require 'controllers/auth.php'; break;
    case 'recover':              require 'controllers/recover.php'; break;
    case 'logout':               session_destroy(); header("Location: ?action=login"); exit;
    case 'usuarios':             require 'controllers/admin_users.php'; break;
    case 'gestion_clases_lista': require 'controllers/admin_classes.php'; break;
    case 'rubricas':             require 'controllers/rubricas.php'; break;
    case 'evaluar':              require 'controllers/evaluar.php'; break;
    case 'notas':                require 'controllers/notas.php'; break;
    case 'ajustes':              require 'controllers/ajustes.php'; break;
    case 'home':
    default:                     $is_dashboard = true; break;
}

$clean_content = ob_get_clean();

// 6. RENDERIZADO
if (in_array($action, ['login', 'install', 'recover'])) {
    echo $clean_content;
} else {
    if (file_exists('views/layout/header.php')) { require 'views/layout/header.php'; }
    if ($is_dashboard) {
        // ... (Tu código de Dashboard HTML proporcionado anteriormente se mantiene igual)
        echo "<div class='container py-5'><h2>Panel de Gestión</h2><p>Bienvenido al sistema.</p></div>";
    } else { 
        echo $clean_content; 
    }
    if (file_exists('views/layout/footer.php')) { require 'views/layout/footer.php'; }
}