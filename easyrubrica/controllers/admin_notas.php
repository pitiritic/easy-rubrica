<?php
// easyrubrica/controllers/admin_notas.php

if (!isset($currentUser)) {
    die("Acceso denegado.");
}

$mensaje = ""; 
$error = "";
$tarea_id = isset($_GET['tarea_id']) ? (int)$_GET['tarea_id'] : 0;

// --- ACCIÓN 1: VER DETALLE INDIVIDUAL (Quién evaluó a quién) ---
if (isset($_GET['ver_detalle']) && $tarea_id > 0 && isset($_GET['alumno_id'])) {
    $alumno_id = (int)$_GET['alumno_id'];

    // Obtener información de la tarea y la rúbrica
    $stmtT = $pdo->prepare("SELECT cr.titulo, r.nombre as rubrica_nombre 
                            FROM clase_rubrica cr 
                            JOIN rubricas r ON cr.rubrica_id = r.id 
                            WHERE cr.id = ?");
    $stmtT->execute([$tarea_id]);
    $tarea_info = $stmtT->fetch();

    // Obtener todas las evaluaciones recibidas por este alumno en esta tarea
    $sql = "SELECT e.*, u.nombre as evaluador_nombre, u.rol as evaluador_rol 
            FROM evaluaciones e
            JOIN usuarios u ON e.evaluador_id = u.id
            WHERE e.tarea_id = ? AND e.evaluado_id = ?
            ORDER BY e.tipo ASC, u.nombre ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$tarea_id, $alumno_id]);
    $detalles = $stmt->fetchAll();

    // Obtener el nombre del alumno evaluado
    $stmtA = $pdo->prepare("SELECT nombre FROM usuarios WHERE id = ?");
    $stmtA->execute([$alumno_id]);
    $nombre_evaluado = $stmtA->fetchColumn();

    require 'views/detalle_notas.view.php';
    exit;
}

// --- ACCIÓN 2: VISTA GENERAL DE NOTAS DE UNA TAREA ---
if ($tarea_id > 0) {
    // 1. Obtener datos de la tarea y sus pesos
    $stmt = $pdo->prepare("
        SELECT cr.*, r.nombre as rubrica_nombre, c.nombre as clase_nombre 
        FROM clase_rubrica cr
        JOIN rubricas r ON cr.rubrica_id = r.id
        JOIN clases c ON cr.clase_id = c.id
        WHERE cr.id = ?
    ");
    $stmt->execute([$tarea_id]);
    $tarea = $stmt->fetch();

    if (!$tarea) {
        die("La tarea no existe.");
    }

    // 2. Obtener lista de alumnos de esa clase
    $stmtAl = $pdo->prepare("SELECT id, nombre, usuario, email FROM usuarios WHERE clase_id = ? AND rol = 'alumno' ORDER BY nombre ASC");
    $stmtAl->execute([$tarea['clase_id']]);
    $alumnos = $stmtAl->fetchAll();

    $alumnos_notas = [];

    foreach ($alumnos as $alumno) {
        $id_al = $alumno['id'];

        // Media Heteroevaluación (Profesor/Admin)
        $stH = $pdo->prepare("SELECT AVG(calificacion_final) FROM evaluaciones WHERE tarea_id = ? AND evaluado_id = ? AND tipo = 'hetero'");
        $stH->execute([$tarea_id, $id_al]);
        $m_hetero = $stH->fetchColumn() ?: 0;

        // Media Coevaluación (Compañeros)
        $stC = $pdo->prepare("SELECT AVG(calificacion_final) FROM evaluaciones WHERE tarea_id = ? AND evaluado_id = ? AND tipo = 'co'");
        $stC->execute([$tarea_id, $id_al]);
        $m_coeval = $stC->fetchColumn() ?: 0;

        // Media Autoevaluación
        $stA = $pdo->prepare("SELECT AVG(calificacion_final) FROM evaluaciones WHERE tarea_id = ? AND evaluado_id = ? AND tipo = 'auto'");
        $stA->execute([$tarea_id, $id_al]);
        $m_auto = $stA->fetchColumn() ?: 0;

        // Cálculo Nota Final Ponderada según los pesos de la tarea
        $nota_final = ($m_hetero * ($tarea['peso_hetero'] / 100)) + 
                      ($m_coeval * ($tarea['peso_coeval'] / 100)) + 
                      ($m_auto   * ($tarea['peso_auto'] / 100));

        $alumnos_notas[] = [
            'id'          => $id_al,
            'nombre'      => $alumno['nombre'],
            'usuario'     => $alumno['usuario'],
            'email'       => $alumno['email'],
            'nota_hetero' => number_format($m_hetero, 2),
            'nota_coeval' => number_format($m_coeval, 2),
            'nota_auto'   => number_format($m_auto, 2),
            'nota_final'  => number_format($nota_final, 2)
        ];
    }

    require 'views/resultados.view.php';

} else {
    // Si no hay tarea_id, redirigir al listado general de notas
    header("Location: ?action=notas");
    exit;
}
