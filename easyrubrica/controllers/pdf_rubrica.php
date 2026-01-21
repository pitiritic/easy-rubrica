<?php
// controllers/pdf_rubrica.php

if (!isset($currentUser)) {
    die("Acceso denegado.");
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
if (!$id) {
    die("Error: ID de rúbrica no proporcionado.");
}

// Obtener la rúbrica y autor 
$stmt = $pdo->prepare("
    SELECT r.*, u.nombre as autor_nombre 
    FROM rubricas r 
    LEFT JOIN usuarios u ON r.autor_id = u.id 
    WHERE r.id = ?
");
$stmt->execute(array($id));
$rubrica = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$rubrica) {
    die("Rúbrica no encontrada.");
}

// Obtener Criterios 
$stmtC = $pdo->prepare("SELECT * FROM criterios WHERE rubrica_id = ? ORDER BY id ASC");
$stmtC->execute(array($id));
$criterios = $stmtC->fetchAll(PDO::FETCH_ASSOC);

// Obtener Niveles para cada Criterio 
foreach ($criterios as $key => $c) {
    $stmtN = $pdo->prepare("
        SELECT valor, descriptor, etiqueta 
        FROM niveles 
        WHERE criterio_id = ? 
        ORDER BY valor DESC
    ");
    $stmtN->execute(array($c['id']));
    $criterios[$key]['niveles'] = $stmtN->fetchAll(PDO::FETCH_ASSOC);
}

require 'views/pdf_rubrica.view.php';
