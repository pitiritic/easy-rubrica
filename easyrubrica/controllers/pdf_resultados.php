<?php
// controllers/pdf_resultados.php
require_once 'config/db.php';

// Verificar que el usuario esté logueado
if (!isset($_SESSION['user_id'])) {
    die("Acceso denegado.");
}

$tarea_id = $_GET['id'] ?? null;
if (!$tarea_id) {
    die("Error: No se proporcionó el ID de la tarea.");
}

// 1. Obtener la información técnica de la tarea, rúbrica y clase
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
    die("Error: La tarea no existe.");
}

// 2. Obtener la lista de alumnos de la clase asignada a la tarea
$stmtAl = $pdo->prepare("SELECT id, nombre FROM usuarios WHERE clase_id = ? AND rol = 'alumno' ORDER BY nombre ASC");
$stmtAl->execute([$tarea['clase_id']]);
$alumnos_lista = $stmtAl->fetchAll();

$alumnos_notas = [];

// 3. Procesar las notas (Misma lógica que el controlador de resultados visual)
foreach ($alumnos_lista as $alumno) {
    $id_al = $alumno['id'];
    $medias = [];
    
    // Calculamos las medias para cada tipo de evaluación
    foreach (['hetero', 'co', 'auto'] as $t) {
        $s = $pdo->prepare("SELECT AVG(calificacion_final) FROM evaluaciones WHERE tarea_id = ? AND evaluado_id = ? AND tipo = ?");
        $s->execute([$tarea_id, $id_al, $t]);
        $res = $s->fetchColumn();
        $medias[$t] = $res !== null ? (float)$res : 0;
    }

    // Aplicar la ponderación configurada en la tarea
    $nota_final = ($medias['hetero'] * ($tarea['peso_hetero'] / 100)) + 
                  ($medias['co']     * ($tarea['peso_coeval'] / 100)) + 
                  ($medias['auto']   * ($tarea['peso_auto'] / 100));

    $alumnos_notas[] = [
        'nombre'      => $alumno['nombre'],
        'nota_hetero' => $medias['hetero'],
        'nota_coeval' => $medias['co'],
        'nota_auto'   => $medias['auto'],
        'nota_final'  => $nota_final
    ];
}

// 4. Cargar la vista específica del PDF (se abrirá en pestaña nueva por el target="_blank")
require 'views/pdf_resultados.view.php';
exit;
