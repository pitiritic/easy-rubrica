<?php
// controllers/pdf_notas.php
$tipo_export = $_GET['tipo_export']; 
$id_export = (int)$_GET['export_pdf'];

// Obtener los mismos datos que el CSV
$sql = "SELECT e.fecha, cr.titulo as tarea, r.nombre as rubrica, c.nombre as clase,
               u_evaluado.nombre as alumno, u_evaluador.nombre as evaluador, e.tipo, e.calificacion_final as nota
        FROM evaluaciones e
        JOIN clase_rubrica cr ON e.tarea_id = cr.id
        JOIN rubricas r ON e.rubrica_id = r.id
        JOIN clases c ON cr.clase_id = c.id
        JOIN usuarios u_evaluado ON e.evaluado_id = u_evaluado.id
        JOIN usuarios u_evaluador ON e.evaluador_id = u_evaluador.id
        WHERE cr.autor_id = ? AND " . ($tipo_export === 'tarea' ? "e.tarea_id = ?" : "e.evaluado_id = ?");

$stmt = $pdo->prepare($sql);
$stmt->execute([$userId, $id_export]);
$datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Generación de vista de impresión (sin header/footer de la app)
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Calificaciones</title>
    <style>
        body { font-family: sans-serif; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .header { text-align: center; border-bottom: 2px solid #0d6efd; padding-bottom: 10px; }
    </style>
</head>
<body onload="window.print()">
    <div class="header">
        <h2>Reporte de Calificaciones - EasyRúbrica</h2>
        <p>Generado el: <?= date('d/m/Y H:i') ?></p>
    </div>
    <table>
        <thead>
            <tr>
                <th>Alumno</th>
                <th>Tarea</th>
                <th>Tipo</th>
                <th>Nota</th>
                <th>Fecha</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($datos as $f): ?>
            <tr>
                <td><?= htmlspecialchars($f['alumno']) ?></td>
                <td><?= htmlspecialchars($f['tarea']) ?></td>
                <td><?= ucfirst($f['tipo']) ?></td>
                <td><strong><?= number_format($f['nota'], 2) ?></strong></td>
                <td><?= date('d/m/y', strtotime($f['fecha'])) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>