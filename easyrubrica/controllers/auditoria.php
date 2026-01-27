<?php
// controllers/auditoria.php
require_once 'libs/Audit.php';

if ($currentUser['rol'] != 'admin') die("Acceso denegado.");

$mensaje = ""; $error = "";

// A. EXPORTAR CSV (Mantiene filtros de búsqueda)
if (isset($_GET['do']) && $_GET['do'] === 'exportar_csv') {
    if (ob_get_level()) ob_end_clean();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="auditoria_'.date('Ymd').'.csv"');
    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    fputcsv($output, ['Fecha', 'Usuario', 'Evento', 'Detalles', 'IP'], ';');

    $where_ex = " WHERE 1=1 ";
    $params_ex = [];
    if (!empty($_GET['q'])) {
        $where_ex .= " AND (usuario_nombre LIKE ? OR detalles LIKE ? OR ip LIKE ?) ";
        $params_ex = array_merge($params_ex, ["%{$_GET['q']}%", "%{$_GET['q']}%", "%{$_GET['q']}%"]);
    }
    if (!empty($_GET['evento'])) {
        $where_ex .= " AND evento = ? ";
        $params_ex[] = $_GET['evento'];
    }

    $stmt = $pdo->prepare("SELECT * FROM auditoria $where_ex ORDER BY fecha DESC");
    $stmt->execute($params_ex);
    while ($row = $stmt->fetch()) {
        fputcsv($output, [$row['fecha'], $row['usuario_nombre'], $row['evento'], $row['detalles'], $row['ip']], ';');
    }
    fclose($output); exit;
}

// B. BORRAR INFORMES
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['borrar_todo'])) {
    $pdo->exec("TRUNCATE TABLE auditoria");
    Audit::log($pdo, "Limpieza de Auditoría", "El administrador vació el registro de eventos.");
    $mensaje = "Historial de auditoría eliminado correctamente.";
}

// C. BÚSQUEDA Y PAGINACIÓN
$busqueda = $_GET['q'] ?? '';
$filtro_evento = $_GET['evento'] ?? '';
$where = " WHERE 1=1 ";
$params = [];

if (!empty($busqueda)) {
    $where .= " AND (usuario_nombre LIKE ? OR detalles LIKE ? OR ip LIKE ?) ";
    $params = array_merge($params, ["%$busqueda%", "%$busqueda%", "%$busqueda%"]);
}
if (!empty($filtro_evento)) {
    $where .= " AND evento = ? ";
    $params[] = $filtro_evento;
}

$limite = 20;
$pagina = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$offset = ($pagina - 1) * $limite;

$stmt_count = $pdo->prepare("SELECT COUNT(*) FROM auditoria $where");
$stmt_count->execute($params);
$total = $stmt_count->fetchColumn();
$paginas_totales = ceil($total / $limite);

$stmt_logs = $pdo->prepare("SELECT * FROM auditoria $where ORDER BY fecha DESC LIMIT $limite OFFSET $offset");
$stmt_logs->execute($params);
$logs = $stmt_logs->fetchAll();

$eventos_disponibles = $pdo->query("SELECT DISTINCT evento FROM auditoria ORDER BY evento ASC")->fetchAll(PDO::FETCH_COLUMN);

require 'views/auditoria.view.php';