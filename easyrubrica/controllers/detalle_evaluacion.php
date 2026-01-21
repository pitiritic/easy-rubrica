<?php
// controllers/detalle_evaluacion.php
if (!isset($_SESSION['user_id']) || ($currentUser['rol'] !== 'profesor' && $currentUser['rol'] !== 'admin')) {
    header("Location: index.php?action=home");
    exit;
}

$tarea_id = $_GET['tarea_id'] ?? null;
$alumno_id = $_GET['alumno_id'] ?? null;

if (!$tarea_id || !$alumno_id) {
    die("Error: Parámetros insuficientes.");
}

// Obtener información del alumno y la tarea
$stmtInfo = $pdo->prepare("
    SELECT u.nombre as alumno_nombre, cr.titulo as tarea_titulo 
    FROM usuarios u
    JOIN clase_rubrica cr ON 1=1
    WHERE u.id = ? AND cr.id = ?
");
$stmtInfo->execute([$alumno_id, $tarea_id]);
$info = $stmtInfo->fetch();

$nombre_evaluado = $info['alumno_nombre'] ?? 'Alumno';
$titulo_tarea = $info['tarea_titulo'] ?? 'Tarea';

// Obtener el listado de quién evaluó
$stmtDetalle = $pdo->prepare("
    SELECT 
        u.nombre as evaluador_nombre,
        e.tipo,
        e.calificacion_final,
        e.fecha
    FROM evaluaciones e
    JOIN usuarios u ON e.evaluador_id = u.id
    WHERE e.tarea_id = ? AND e.evaluado_id = ?
    ORDER BY e.tipo ASC, e.fecha DESC
");
$stmtDetalle->execute([$tarea_id, $alumno_id]);
$detalles = $stmtDetalle->fetchAll();

require 'views/detalle_evaluacion.view.php';
