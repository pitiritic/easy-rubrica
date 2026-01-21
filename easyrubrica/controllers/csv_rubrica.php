<?php
// easyrubrica/controllers/csv_rubrica.php

if (!isset($currentUser)) {
    header("Location: ?action=login");
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) die("Error: Falta el ID.");

// 1. OBTENER DATOS DE LA RÚBRICA
$stmt = $pdo->prepare("SELECT * FROM rubricas WHERE id = ?");
$stmt->execute([$id]);
$rubrica = $stmt->fetch();

if (!$rubrica) die("Rúbrica no encontrada.");

// Seguridad: Verificar si el usuario tiene permiso (opcional, aquí abierto para profes/admin)
// if ($currentUser['rol'] !== 'admin' && $rubrica['autor_id'] != $currentUser['id']) { ... }

// Procesar competencias (JSON -> Texto separado por comas)
$tags_array = json_decode($rubrica['competencias'] ?? '[]', true);
$tags_string = is_array($tags_array) ? implode(',', $tags_array) : '';

// 2. OBTENER CRITERIOS
$stmtC = $pdo->prepare("SELECT * FROM criterios WHERE rubrica_id = ? ORDER BY id");
$stmtC->execute([$id]);
$criterios = $stmtC->fetchAll();

// 3. GENERAR CSV
if (ob_get_level()) ob_end_clean(); // Limpiar buffer

$filename = 'Rubrica_' . preg_replace('/[^a-zA-Z0-9]/', '_', $rubrica['nombre']) . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

$output = fopen('php://output', 'w');
// Añadir BOM para Excel
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Cabeceras (Coinciden con la plantilla de importación v2)
fputcsv($output, ['Nombre Rubrica', 'Descripcion', 'Asignatura', 'Competencias', 'Criterio', 'Nivel 1 (Insuficiente)', 'Nivel 2 (Aceptable)', 'Nivel 3 (Bueno)', 'Nivel 4 (Excelente)']);

// Recorrer criterios
foreach ($criterios as $c) {
    // Obtener niveles
    $stmtN = $pdo->prepare("SELECT valor, descriptor FROM niveles WHERE criterio_id = ?");
    $stmtN->execute([$c['id']]);
    $niveles_raw = $stmtN->fetchAll();
    
    // Mapear niveles 1-4
    $n = [1 => '', 2 => '', 3 => '', 4 => ''];
    foreach ($niveles_raw as $l) {
        $n[$l['valor']] = $l['descriptor'];
    }

    // Escribir fila
    fputcsv($output, [
        $rubrica['nombre'],
        $rubrica['descripcion'],
        $rubrica['asignatura'],
        $tags_string,
        $c['nombre'],
        $n[1],
        $n[2],
        $n[3],
        $n[4]
    ]);
}

fclose($output);
exit;
?>
