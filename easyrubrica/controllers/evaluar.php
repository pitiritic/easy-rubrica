<?php
/**
 * Easy Rúbrica - Controlador Evaluar
 */
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ?action=login");
    exit;
}

$mensaje = $_GET['success'] ?? "";
$error = "";

// 1. PROCESAR EL GUARDADO
// Verificamos 'guardar_evaluacion' que es el nombre del botón submit en tu vista
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_evaluacion'])) {
    
    // Capturamos los datos del formulario
    $tarea_id   = $_POST['tarea_id']   ?? null;
    $rubrica_id = $_POST['rubrica_id'] ?? null;
    $evaluado_id = $_POST['evaluado_id'] ?? null;
    
    // IMPORTANTE: Probamos ambos nombres comunes ('criterios' o 'notas') para los puntos
    $puntos = $_POST['criterios'] ?? $_POST['notas'] ?? [];

    if ($tarea_id && $rubrica_id && $evaluado_id) {
        try {
            $pdo->beginTransaction();
            
            // Determinar tipo de evaluación
            $tipo = 'hetero';
            if ($currentUser['rol'] === 'alumno') {
                $tipo = ($evaluado_id == $_SESSION['user_id']) ? 'auto' : 'coeval';
            }

            // 1. Insertar en la tabla 'evaluaciones'
            $stmt = $pdo->prepare("INSERT INTO evaluaciones (tarea_id, rubrica_id, evaluador_id, evaluado_id, tipo, fecha) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$tarea_id, $rubrica_id, $_SESSION['user_id'], $evaluado_id, $tipo]);
            $eval_id = $pdo->lastInsertId();

            // 2. Insertar detalles en la tabla 'puntuaciones' (según tu db.php)
            $suma_puntos = 0;
            if (!empty($puntos)) {
                // Usamos 'valor_obtenido' según la estructura de tu db.php
                $stmtDetalle = $pdo->prepare("INSERT INTO puntuaciones (evaluacion_id, criterio_id, valor_obtenido) VALUES (?, ?, ?)");
                foreach ($puntos as $crit_id => $valor) {
                    $stmtDetalle->execute([$eval_id, $crit_id, $valor]);
                    $suma_puntos += (float)$valor;
                }
            }

            // 3. Calcular nota final (Base 10)
            $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM criterios WHERE rubrica_id = ?");
            $stmtCount->execute([$rubrica_id]);
            $num_crit = $stmtCount->fetchColumn();
            
            // Asumiendo máximo de 4 puntos por criterio
            $nota_final = ($num_crit > 0) ? ($suma_puntos / ($num_crit * 4)) * 10 : 0;

            // Actualizar la calificación final
            $pdo->prepare("UPDATE evaluaciones SET calificacion_final = ? WHERE id = ?")
                ->execute([round($nota_final, 2), $eval_id]);
            
            $pdo->commit();
            
            // Redirección con éxito
            header("Location: index.php?action=evaluar&success=Evaluación guardada con éxito (Nota: ".round($nota_final, 2).")");
            exit;

        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $error = "Error al guardar en base de datos: " . $e->getMessage();
        }
    } else {
        $error = "Faltan datos obligatorios para procesar la evaluación.";
    }
}

// 2. OBTENER TAREAS (Mantenemos tu lógica de filtrado por autor/inscripción)
$userId = $_SESSION['user_id'];

if ($currentUser['rol'] === 'alumno') {
    $sql = "SELECT cr.id as tarea_id, cr.titulo, cr.estado, r.id as rubrica_id, r.nombre as r_nombre, c.nombre as clase_nombre 
            FROM clase_rubrica cr 
            JOIN rubricas r ON cr.rubrica_id = r.id 
            JOIN clases c ON cr.clase_id = c.id
            JOIN clase_usuario cu ON c.id = cu.clase_id 
            WHERE cu.usuario_id = ? AND (cr.estado = '0' OR cr.estado = 'activa') 
            ORDER BY cr.id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
} else {
    $sql = "SELECT cr.id as tarea_id, cr.titulo, cr.estado, r.id as rubrica_id, r.nombre as r_nombre, c.nombre as clase_nombre 
            FROM clase_rubrica cr 
            JOIN rubricas r ON cr.rubrica_id = r.id 
            JOIN clases c ON cr.clase_id = c.id
            WHERE cr.autor_id = ? AND (cr.estado = '0' OR cr.estado = 'activa') 
            ORDER BY cr.id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
}

$tareas_disponibles = $stmt->fetchAll();

require 'views/evaluar.view.php';
