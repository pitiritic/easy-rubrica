<?php
// controllers/notas.php

if (!isset($_SESSION['user_id'])) {
    header("Location: ?action=login");
    exit;
}

$mensaje = ""; 
$error = "";
$vista_actual = $_GET['vista'] ?? 'por_tarea';
$userId = (int)$_SESSION['user_id'];
$is_print_mode = isset($_GET['export_pdf']);

// --- 1. LÓGICA DE EXPORTACIÓN CSV ---
if (isset($_GET['export_csv']) && $currentUser['rol'] !== 'alumno') {
    $tipo_export = $_GET['tipo_export']; 
    $id_export = (int)$_GET['export_csv'];
    $sql = "SELECT e.fecha, cr.titulo as tarea, r.nombre as rubrica, c.nombre as clase,
                   u_evaluado.nombre as alumno, u_evaluador.nombre as evaluador, e.tipo, e.calificacion_final as nota
            FROM evaluaciones e
            JOIN clase_rubrica cr ON e.tarea_id = cr.id
            JOIN rubricas r ON e.rubrica_id = r.id
            JOIN clases c ON cr.clase_id = c.id
            JOIN usuarios u_evaluado ON e.evaluado_id = u_evaluado.id
            JOIN usuarios u_evaluador ON e.evaluador_id = u_evaluador.id
            WHERE cr.autor_id = ? AND " . ($tipo_export === 'tarea' ? "e.tarea_id = ?" : "e.evaluado_id = ?");
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId, $id_export]);
    $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (ob_get_level()) ob_end_clean();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="notas.csv"');
    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); 
    if (!empty($datos)) { fputcsv($output, array_keys($datos[0])); foreach ($datos as $f) fputcsv($output, $f); }
    fclose($output); exit;
}

// --- 2. OBTENCIÓN DE DATOS ---
$notas_agrupadas = []; 

if ($currentUser['rol'] === 'alumno') {
    $sql = "SELECT e.*, cr.titulo as tarea_nombre, r.nombre as rubrica_nombre, c.nombre as clase_nombre
            FROM evaluaciones e
            LEFT JOIN clase_rubrica cr ON e.tarea_id = cr.id
            LEFT JOIN rubricas r ON e.rubrica_id = r.id
            LEFT JOIN clases c ON cr.clase_id = c.id
            WHERE e.evaluado_id = ?
            ORDER BY e.fecha DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    $raw_notas = $stmt->fetchAll();

    foreach ($raw_notas as $n) {
        $key = "T" . $n['tarea_id'];
        if (!isset($notas_agrupadas[$key])) {
            $notas_agrupadas[$key] = [
                'nombre' => $n['tarea_nombre'] ?? 'Tarea Evaluada',
                'extra'  => $n['clase_nombre'] ?? '',
                'icono'  => 'fa-file-lines',
                'subbloques' => []
            ];
        }
        $sub_key = "R" . $n['rubrica_id'];
        if (!isset($notas_agrupadas[$key]['subbloques'][$sub_key])) {
            $notas_agrupadas[$key]['subbloques'][$sub_key] = [
                'titulo_principal' => $n['rubrica_nombre'] ?? 'Rúbrica',
                'evaluaciones' => [],
                'suma_notas' => 0,
                'total_evals' => 0
            ];
        }
        $n['evaluador_nombre'] = "Evaluación recibida"; 
        $notas_agrupadas[$key]['subbloques'][$sub_key]['evaluaciones'][] = $n;
        $notas_agrupadas[$key]['subbloques'][$sub_key]['suma_notas'] += $n['calificacion_final'];
        $notas_agrupadas[$key]['subbloques'][$sub_key]['total_evals']++;
    }
} else {
    $sql = "SELECT e.*, cr.titulo as tarea_nombre, r.nombre as rubrica_nombre, c.nombre as clase_nombre,
                   u_evaluado.nombre as alumno_nombre, u_evaluador.nombre as evaluador_nombre
            FROM evaluaciones e
            JOIN clase_rubrica cr ON e.tarea_id = cr.id
            JOIN rubricas r ON e.rubrica_id = r.id
            JOIN clases c ON cr.clase_id = c.id
            JOIN usuarios u_evaluado ON e.evaluado_id = u_evaluado.id
            JOIN usuarios u_evaluador ON e.evaluador_id = u_evaluador.id
            WHERE cr.autor_id = ?";
    
    $params = [$userId];

    if ($is_print_mode) {
        $id_target = (int)$_GET['export_pdf'];
        $tipo_target = $_GET['tipo_export'];
        $sql .= ($tipo_target === 'tarea') ? " AND e.tarea_id = ?" : " AND e.evaluado_id = ?";
        $params[] = $id_target;
    }

    $sql .= " ORDER BY u_evaluado.nombre ASC, cr.titulo ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $raw_notas = $stmt->fetchAll();
    
    foreach ($raw_notas as $n) {
        $key = ($vista_actual === 'por_alumno') ? "A".$n['evaluado_id'] : "T".$n['tarea_id'];
        if (!isset($notas_agrupadas[$key])) {
            $notas_agrupadas[$key] = [
                'nombre' => ($vista_actual === 'por_alumno') ? $n['alumno_nombre'] : $n['tarea_nombre'],
                'extra'  => ($vista_actual === 'por_alumno') ? "" : $n['clase_nombre'],
                'icono'  => ($vista_actual === 'por_alumno') ? "fa-user-graduate" : "fa-file-lines",
                'subbloques' => []
            ];
        }
        $sub_key = "SUB_".$n['tarea_id']."_".$n['evaluado_id'];
        if (!isset($notas_agrupadas[$key]['subbloques'][$sub_key])) {
            $notas_agrupadas[$key]['subbloques'][$sub_key] = [
                'titulo_principal' => ($vista_actual === 'por_alumno') ? $n['tarea_nombre'] : $n['alumno_nombre'],
                'rubrica_nombre' => $n['rubrica_nombre'],
                'evaluaciones' => [],
                'suma_notas' => 0,
                'total_evals' => 0
            ];
        }
        $notas_agrupadas[$key]['subbloques'][$sub_key]['evaluaciones'][] = $n;
        $notas_agrupadas[$key]['subbloques'][$sub_key]['suma_notas'] += $n['calificacion_final'];
        $notas_agrupadas[$key]['subbloques'][$sub_key]['total_evals']++;
    }
}

require 'views/notas.view.php';