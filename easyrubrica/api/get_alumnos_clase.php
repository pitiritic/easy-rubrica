<?php
// api/get_alumnos_clase.php
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');

$tarea_id = $_GET['tarea_id'] ?? null;

if (!$tarea_id) {
    echo json_encode([]);
    exit;
}

try {
    // Buscamos los alumnos que pertenecen a la clase de la tarea seleccionada
    $stmt = $pdo->prepare("
        SELECT u.id, u.nombre 
        FROM usuarios u
        JOIN clase_usuario cu ON u.id = cu.usuario_id
        JOIN clase_rubrica cr ON cu.clase_id = cr.clase_id
        WHERE cr.id = ? AND u.rol = 'alumno'
        ORDER BY u.nombre ASC
    ");
    $stmt->execute([$tarea_id]);
    $alumnos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($alumnos);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
