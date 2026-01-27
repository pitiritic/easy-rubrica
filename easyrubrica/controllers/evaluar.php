<?php
// controllers/evaluar.php
require_once 'config/db.php';
require_once 'libs/Audit.php';

if (!isset($_SESSION['user_id'])) { header("Location: ?action=login"); exit; }
$mensaje = $_GET['success'] ?? ""; $error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_evaluacion'])) {
    $tarea_id = $_POST['tarea_id'] ?? null; $rubrica_id = $_POST['rubrica_id'] ?? null; $evaluado_id = $_POST['evaluado_id'] ?? null; $puntos = $_POST['criterios'] ?? $_POST['notas'] ?? [];
    if ($tarea_id && $rubrica_id && $evaluado_id) {
        try {
            $stmtCheck = $pdo->prepare("SELECT id FROM evaluaciones WHERE tarea_id = ? AND evaluador_id = ? AND evaluado_id = ?"); $stmtCheck->execute([$tarea_id, $_SESSION['user_id'], $evaluado_id]);
            if ($stmtCheck->fetch()) throw new Exception("Ya has realizado una evaluación para este alumno.");
            $pdo->beginTransaction();
            $tipo = 'hetero'; if ($currentUser['rol'] === 'alumno') $tipo = ($evaluado_id == $_SESSION['user_id']) ? 'auto' : 'coeval';
            $stmt = $pdo->prepare("INSERT INTO evaluaciones (tarea_id, rubrica_id, evaluador_id, evaluado_id, tipo, fecha) VALUES (?, ?, ?, ?, ?, NOW())"); $stmt->execute([$tarea_id, $rubrica_id, $_SESSION['user_id'], $evaluado_id, $tipo]); $eval_id = $pdo->lastInsertId();
            $suma_puntos = 0; if (!empty($puntos)) { $stmtDetalle = $pdo->prepare("INSERT INTO puntuaciones (evaluacion_id, criterio_id, valor_obtenido) VALUES (?, ?, ?)"); foreach ($puntos as $crit_id => $valor) { $stmtDetalle->execute([$eval_id, $crit_id, $valor]); $suma_puntos += (float)$valor; } }
            $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM criterios WHERE rubrica_id = ?"); $stmtCount->execute([$rubrica_id]); $num_crit = $stmtCount->fetchColumn();
            $nota_final = ($num_crit > 0) ? ($suma_puntos / ($num_crit * 4)) * 10 : 0; $nota_formateada = round($nota_final, 2);
            $pdo->prepare("UPDATE evaluaciones SET calificacion_final = ? WHERE id = ?")->execute([$nota_formateada, $eval_id]);
            $stName = $pdo->prepare("SELECT nombre FROM usuarios WHERE id = ?"); $stName->execute([$evaluado_id]); $nombre_alumno = $stName->fetchColumn() ?: "Alumno";
            $pdo->commit();
            Audit::log($pdo, "Evaluación Realizada", "Se evaluó al alumno ID: $evaluado_id en la tarea ID: $tarea_id (Nota: $nota_formateada)");
            header("Location: index.php?action=evaluar&success=Evaluación guardada con éxito.&nota=" . $nota_formateada . "&alumno=" . urlencode($nombre_alumno)); exit;
        } catch (Exception $e) { if ($pdo->inTransaction()) $pdo->rollBack(); $error = $e->getMessage(); }
    } else { $error = "Faltan datos obligatorios."; }
}

$userId = $_SESSION['user_id'];
if ($currentUser['rol'] === 'alumno') { $sql = "SELECT cr.id as tarea_id, cr.titulo, cr.estado, r.id as rubrica_id, r.nombre as r_nombre, c.nombre as clase_nombre FROM clase_rubrica cr JOIN rubricas r ON cr.rubrica_id = r.id JOIN clases c ON cr.clase_id = c.id JOIN clase_usuario cu ON c.id = cu.clase_id WHERE cu.usuario_id = ? AND (cr.estado = '0' OR cr.estado = 'activa') ORDER BY cr.id DESC"; $stmt = $pdo->prepare($sql); $stmt->execute([$userId]); }
else { $sql = "SELECT cr.id as tarea_id, cr.titulo, cr.estado, r.id as rubrica_id, r.nombre as r_nombre, c.nombre as clase_nombre FROM clase_rubrica cr JOIN rubricas r ON cr.rubrica_id = r.id JOIN clases c ON cr.clase_id = c.id WHERE cr.autor_id = ? AND (cr.estado = '0' OR cr.estado = 'activa') ORDER BY cr.id DESC"; $stmt = $pdo->prepare($sql); $stmt->execute([$userId]); }
$tareas_disponibles = $stmt->fetchAll();
require 'views/evaluar.view.php';