<?php
// easyrubrica/api/get_rubrica.php

// 1. Conectar a la base de datos usando una ruta absoluta
require_once __DIR__ . '/../config/db.php';

// Indicamos que la respuesta será JSON
header('Content-Type: application/json');

// Validar que recibimos el ID por parámetro GET
if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Falta el ID de la rúbrica']);
    exit;
}

$rubrica_id = $_GET['id'];

try {
    // 2. Obtener los CRITERIOS de esa rúbrica
    $stmt = $pdo->prepare("SELECT * FROM criterios WHERE rubrica_id = ? ORDER BY id");
    $stmt->execute([$rubrica_id]);
    $criterios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Para cada criterio, obtener sus NIVELES
    // Al usar SELECT *, traerá id, valor, etiqueta y descriptor automáticamente
    foreach ($criterios as &$criterio) {
        $stmtNiv = $pdo->prepare("SELECT * FROM niveles WHERE criterio_id = ? ORDER BY valor ASC");
        $stmtNiv->execute([$criterio['id']]);
        $criterio['niveles'] = $stmtNiv->fetchAll(PDO::FETCH_ASSOC);
    }

    // 4. Devolver todo el paquete estructurado como JSON
    echo json_encode($criterios);

} catch (Exception $e) {
    // Manejo de errores del servidor
    http_response_code(500);
    echo json_encode(['error' => 'Error de servidor: ' . $e->getMessage()]);
}
?>
