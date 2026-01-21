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

// --- 1. LÓGICA DE EXPORTACIÓN CSV (Protegida) ---
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

// --- 2. OBTENCIÓN DE DATOS SEGÚN ROL ---

$notas_agrupadas = []; // Variable que espera la vista

if ($currentUser['rol'] === 'alumno') {
    /**
     * VISTA ALUMNO: Adaptamos los datos al formato de la vista (bloques)
     * pero anonimizamos los evaluadores.
     */
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
                'evaluaciones' => []
            ];
        }
        
        // PRIVACIDAD: Sobrescribimos datos sensibles antes de enviarlos a la vista
        $n['evaluador_id'] = 0; // Para que la vista no lo reconozca como "Tú mismo"
        $n['evaluador_nombre'] = "Evaluación recibida"; 
        
        $notas_agrupadas[$key]['subbloques'][$sub_key]['evaluaciones'][] = $n;
    }

} else {
    /**
     * VISTA PROFESOR / ADMIN: Solo ven sus propias tareas (cr.autor_id).
     */
    $sql = "SELECT e.*, cr.titulo as tarea_nombre, r.nombre as rubrica_nombre, c.nombre as clase_nombre,
                   u_evaluado.nombre as alumno_nombre, u_evaluador.nombre as evaluador_nombre
            FROM evaluaciones e
            JOIN clase_rubrica cr ON e.tarea_id = cr.id
            JOIN rubricas r ON e.rubrica_id = r.id
            JOIN clases c ON cr.clase_id = c.id
            JOIN usuarios u_evaluado ON e.evaluado_id = u_evaluado.id
            JOIN usuarios u_evaluador ON e.evaluador_id = u_evaluador.id
            WHERE cr.autor_id = ?
            ORDER BY u_evaluado.nombre ASC, cr.titulo ASC";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
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
                'evaluaciones' => []
            ];
        }
        $notas_agrupadas[$key]['subbloques'][$sub_key]['evaluaciones'][] = $n;
    }
}

require 'views/notas.view.php';
