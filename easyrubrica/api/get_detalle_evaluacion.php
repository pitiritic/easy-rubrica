<?php
require_once '../config/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['error' => 'No autorizado']));
}

$eval_id = (int)($_GET['id'] ?? 0);

// 1. Obtener datos de la evaluación, rúbrica y nombre del evaluador
$stmt = $pdo->prepare("
    SELECT e.rubrica_id, e.calificacion_final, e.tipo, u.nombre as evaluador_nombre 
    FROM evaluaciones e
    JOIN usuarios u ON e.evaluador_id = u.id
    WHERE e.id = ?
");
$stmt->execute([$eval_id]);
$eval_data = $stmt->fetch();

if (!$eval_data) {
    die(json_encode(['error' => 'Evaluación no encontrada']));
}

$rubrica_id = $eval_data['rubrica_id'];

// 2. Obtener las puntuaciones marcadas
$stmtP = $pdo->prepare("SELECT criterio_id, valor_obtenido FROM puntuaciones WHERE evaluacion_id = ?");
$stmtP->execute([$eval_id]);
$puntos = $stmtP->fetchAll(PDO::FETCH_KEY_PAIR);

// 3. Obtener la estructura de la rúbrica
$stmtC = $pdo->prepare("SELECT id, nombre FROM criterios WHERE rubrica_id = ?");
$stmtC->execute([$rubrica_id]);
$criterios = $stmtC->fetchAll();

foreach ($criterios as &$c) {
    $stmtN = $pdo->prepare("SELECT id, etiqueta, descriptor, valor FROM niveles WHERE criterio_id = ? ORDER BY valor ASC");
    $stmtN->execute([$c['id']]);
    $c['niveles'] = $stmtN->fetchAll();
    $c['valor_seleccionado'] = $puntos[$c['id']] ?? null;
}

// Mapeo de tipos para mostrar etiquetas legibles
$labels = ['hetero' => 'Hetero', 'co' => 'Coeval', 'auto' => 'Auto'];
$tipo_label = $labels[$eval_data['tipo']] ?? $eval_data['tipo'];

header('Content-Type: application/json');
echo json_encode([
    'evaluador_nombre' => $eval_data['evaluador_nombre'],
    'calificacion_final' => $eval_data['calificacion_final'],
    'tipo_evaluacion' => $tipo_label,
    'criterios' => $criterios
]);