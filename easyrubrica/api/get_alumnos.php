<?php
// easyrubrica/api/get_alumnos.php
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');

// Aseguramos que la sesión esté iniciada para obtener el evaluador_id
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$userId = $_SESSION['user_id'] ?? 0;

// Validamos parámetros mínimos
if (!isset($_GET['tarea_id']) || $userId == 0) {
    echo json_encode(['error' => 'Sesión no válida o falta tarea_id']);
    exit;
}

$tarea_id = (int)$_GET['tarea_id'];

try {
    // Consulta para obtener alumnos de la clase que NO han sido evaluados aún por este usuario
    $stmt = $pdo->prepare("
        SELECT u.id, u.nombre 
        FROM usuarios u 
        JOIN clase_usuario cu ON u.id = cu.usuario_id 
        JOIN clase_rubrica cr ON cu.clase_id = cr.clase_id 
        WHERE cr.id = ? 
        AND u.rol = 'alumno'
        AND u.id NOT IN (
            SELECT evaluado_id FROM evaluaciones WHERE tarea_id = ? AND evaluador_id = ?
        )
        ORDER BY u.nombre ASC
    ");
    $stmt->execute([$tarea_id, $tarea_id, $userId]);
    $alumnos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($alumnos);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de base de datos: ' . $e->getMessage()]);
}
